<?php

namespace App\Services\Import;

use App\ImportJob;
use App\ImportJobDetail;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CompanySqlImportService
{
    protected $schemaService;
    protected $parserService;
    protected $mapService;
    protected $progressService;
    protected $dumpSplitService;

    public function __construct(
        ImportSchemaService $schemaService,
        SqlInsertParserService $parserService,
        MigrationMapService $mapService,
        ImportProgressService $progressService,
        DumpSplitService $dumpSplitService
    ) {
        $this->schemaService = $schemaService;
        $this->parserService = $parserService;
        $this->mapService = $mapService;
        $this->progressService = $progressService;
        $this->dumpSplitService = $dumpSplitService;
    }

    public function run(ImportJob $job)
    {
        ini_set('memory_limit', '1024M');

        if ($this->abortIfCancelled($job)) {
            return;
        }

        // Marcar ejecucion al inicio para reflejar actividad aunque el preprocesado tome tiempo.
        $this->progressService->startJob($job);
        $this->progressService->log($job, 'info', 'Worker de importacion iniciado. Preparando archivo SQL...');

        $absolutePath = storage_path('app/' . $job->source_path);
        if (!file_exists($absolutePath)) {
            throw new \RuntimeException('No se encontró el archivo fuente para la importación.');
        }

        $splitSummary = null;
        $tablesFromSplit = [];
        $shouldSplit = true;

        if (is_array($job->options) && array_key_exists('split_before_import', $job->options)) {
            $shouldSplit = (bool) $job->options['split_before_import'];
        }

        if ($shouldSplit) {
            $splitFolder = storage_path('app/import_splits/job_' . $job->id);
            if (File::exists($splitFolder)) {
                File::deleteDirectory($splitFolder);
            }

            $splitSummary = $this->dumpSplitService->split($absolutePath, $splitFolder, true);
            foreach ($splitSummary['tables'] as $tableInfo) {
                $tablesFromSplit[$tableInfo['table']] = [
                    'rows' => (int) $tableInfo['rows'],
                    'path' => $tableInfo['path'],
                ];
            }
        }

        $parsedTables = [];
        $parseIssues = [];

        if ($splitSummary !== null) {
            foreach ($tablesFromSplit as $tableName => $tableInfo) {
                if ($tableInfo['rows'] <= 0) {
                    continue;
                }

                $parsedTableResult = $this->parserService->parseFile($tableInfo['path']);
                if (!empty($parsedTableResult['issues'])) {
                    $parseIssues = array_merge($parseIssues, $parsedTableResult['issues']);
                }

                if (isset($parsedTableResult['tables'][$tableName])) {
                    $parsedTables[$tableName] = $parsedTableResult['tables'][$tableName];
                }
            }
        } else {
            $parsed = $this->parserService->parseFile($absolutePath);
            $parsedTables = $parsed['tables'];
            $parseIssues = $parsed['issues'];
        }

        $parsedTables = $this->filterParsedTables($parsedTables);
        $tableNames = array_keys($parsedTables);
        $metadata = $this->schemaService->getTableMetadata($tableNames);
        $knownTables = array_keys($metadata);
        $unknownTables = array_values(array_diff($tableNames, $knownTables));
        $order = $this->schemaService->resolveMigrationOrder($tableNames);

        if (empty($tableNames)) {
            $this->progressService->completeJob($job, 'failed', 'No se encontraron tablas importables en el archivo SQL.');
            return;
        }

        $this->progressService->log($job, 'info', 'Orden de migración resuelto.', [
            'order' => $order,
            'tables' => $tableNames,
        ]);

        if ($splitSummary !== null) {
            $this->progressService->log($job, 'info', 'Dump SQL dividido por tablas antes de migrar.', [
                'split_folder' => storage_path('app/import_splits/job_' . $job->id),
                'tables_detected' => $splitSummary['tables_detected'],
                'generated_files' => $splitSummary['generated_files'],
                'summary_path' => $splitSummary['summary_path'],
            ]);
        }

        if (!empty($parseIssues)) {
            $this->progressService->log($job, 'warning', 'Se detectaron inconsistencias durante el parseo.', ['issues' => $parseIssues]);
        }

        $details = $job->details()->get()->keyBy('table_name');

        // Idempotencia: si una tabla no existe en el esquema destino, se omite y no se detiene el job.
        if (!empty($unknownTables)) {
            foreach ($unknownTables as $unknownTable) {
                if (isset($details[$unknownTable])) {
                    $this->progressService->completeTable(
                        $details[$unknownTable],
                        'cancelled',
                        [
                            'processed_rows' => 0,
                            'failed_rows' => 0,
                        ],
                        'Tabla omitida: no existe en el esquema destino.'
                    );
                }

                $this->progressService->log($job, 'warning', 'Tabla omitida durante importacion por no existir en destino.', [
                    'table' => $unknownTable,
                ]);

                unset($parsedTables[$unknownTable]);
            }

            $tableNames = array_values(array_intersect($tableNames, $knownTables));
            $order = array_values(array_intersect($order, $tableNames));
        }

        foreach ($order as $index => $tableName) {
            if ($this->abortIfCancelled($job)) {
                return;
            }

            if (!isset($parsedTables[$tableName]) || !isset($details[$tableName])) {
                continue;
            }

            $pendingTables = array_slice($order, $index + 1);

            $shouldContinue = $this->processTable(
                $job,
                $details[$tableName],
                $tableName,
                $parsedTables[$tableName],
                isset($metadata[$tableName]) ? $metadata[$tableName] : null,
                $tableNames,
                $pendingTables
            );

            if (!$shouldContinue) {
                return;
            }
        }

        $job->refresh();
        $hasFailures = $job->details()->whereIn('status', ['failed', 'partial'])->exists();
        $this->progressService->completeJob($job, $hasFailures ? 'partial' : 'completed');
        $this->progressService->log($job, 'info', 'Importación finalizada.', [
            'status' => $hasFailures ? 'partial' : 'completed',
            'processed_rows' => $job->fresh()->processed_rows,
            'failed_rows' => $job->fresh()->failed_rows,
        ]);
    }

    protected function isCancellationRequested(ImportJob $job)
    {
        $job->refresh();
        $options = is_array($job->options) ? $job->options : [];

        return !empty($options['cancel_requested'])
            || in_array($job->status, ['cancel_requested', 'cancelling', 'cancelled'], true);
    }

    protected function abortIfCancelled(ImportJob $job, ImportJobDetail $detail = null, $tableName = null)
    {
        if (!$this->isCancellationRequested($job)) {
            return false;
        }

        $reason = 'Importación cancelada por usuario.';
        $options = is_array($job->options) ? $job->options : [];
        if (!empty($options['cancel_mode']) && $options['cancel_mode'] === 'hard') {
            $reason = 'Importación cancelada en seco por usuario.';
        }

        if ($detail && in_array($detail->status, ['running', 'queued'], true)) {
            $this->progressService->completeTable(
                $detail,
                'cancelled',
                [
                    'processed_rows' => $detail->processed_rows,
                    'failed_rows' => $detail->failed_rows,
                ],
                $reason
            );
        }

        $this->progressService->cancelJob($job, $reason);
        $this->progressService->log($job->fresh(), 'warning', 'Se detuvo la importación por cancelación solicitada.', [
            'table' => $tableName,
            'mode' => !empty($options['cancel_mode']) ? $options['cancel_mode'] : 'soft',
        ]);

        return true;
    }

    protected function filterParsedTables(array $tables)
    {
        $excluded = $this->schemaService->getExcludedTables();
        $filtered = [];

        foreach ($tables as $tableName => $payload) {
            if (in_array($tableName, $excluded, true)) {
                continue;
            }

            if (empty($payload['rows'])) {
                continue;
            }

            $filtered[$tableName] = $payload;
        }

        return $filtered;
    }

    protected function processTable(ImportJob $job, ImportJobDetail $detail, $tableName, array $tablePayload, $tableMeta, array $importedTables, array $pendingTables = [])
    {
        if ($this->abortIfCancelled($job, $detail, $tableName)) {
            return false;
        }

        $this->progressService->startTable($detail);
        $this->progressService->log($job, 'info', 'Procesando tabla ' . $tableName . '.', [
            'table' => $tableName,
            'rows' => count($tablePayload['rows']),
            'pending_dependencies' => $pendingTables,
        ], $detail);

        $pendingRows = $tablePayload['rows'];
        $attempt = 0;
        $hardFailures = 0;
        $deferredReasonsLogged = 0;

        while (!empty($pendingRows) && $attempt <= $job->max_retries) {
            if ($this->abortIfCancelled($job, $detail, $tableName)) {
                return false;
            }

            $nextPending = [];
            $processedThisRound = 0;

            if ($attempt > 0) {
                $detail->increment('retries');
                $this->progressService->log($job, 'warning', 'Reintentando tabla ' . $tableName . '.', [
                    'table' => $tableName,
                    'retry' => $attempt,
                    'pending_rows' => count($pendingRows),
                ], $detail);
            }

            foreach ($pendingRows as $row) {
                if ($this->abortIfCancelled($job, $detail, $tableName)) {
                    return false;
                }

                $result = $this->persistRow($job, $tableName, $row, $tableMeta, $importedTables, $pendingTables);

                if ($result['status'] === 'processed') {
                    $processedThisRound++;
                    $this->progressService->incrementTable($detail, 1, 0, count($nextPending));
                    continue;
                }

                if ($result['status'] === 'deferred') {
                    $nextPending[] = $row;

                    // Registramos una muestra acotada de diferimientos para facilitar el diagnostico.
                    if ($deferredReasonsLogged < 5) {
                        $deferredReasonsLogged++;
                        $this->progressService->log($job, 'warning', 'Fila diferida en ' . $tableName . '.', [
                            'table' => $tableName,
                            'reason' => isset($result['reason']) ? $result['reason'] : 'Fila diferida sin motivo reportado.',
                            'row' => $row,
                        ], $detail);
                    }

                    continue;
                }

                $hardFailures++;
                $this->progressService->incrementTable($detail, 0, 1, count($nextPending));
                $this->progressService->log($job, 'error', 'Fila rechazada en ' . $tableName . '.', [
                    'table' => $tableName,
                    'reason' => $result['reason'],
                    'row' => $row,
                ], $detail);
            }

            $pendingRows = $nextPending;
            $detail->deferred_rows = count($pendingRows);
            $detail->save();

            if (empty($pendingRows)) {
                break;
            }

            if ($processedThisRound === 0) {
                $attempt++;
            } else {
                $attempt++;
            }
        }

        if (!empty($pendingRows)) {
            $remaining = count($pendingRows);
            $hardFailures += $remaining;
            $this->progressService->incrementTable($detail, 0, $remaining, 0);
            $this->progressService->log($job, 'error', 'Quedaron filas sin resolver en ' . $tableName . '.', [
                'table' => $tableName,
                'remaining_rows' => $remaining,
            ], $detail);
        }

        $detail->refresh();
        $status = 'completed';
        if ($detail->failed_rows > 0 && $detail->processed_rows > 0) {
            $status = 'partial';
        } elseif ($detail->failed_rows > 0) {
            $status = 'failed';
        }

        $this->progressService->completeTable($detail, $status, [
            'processed_rows' => $detail->processed_rows,
            'failed_rows' => $detail->failed_rows,
        ], $status === 'failed' ? 'No fue posible completar la tabla ' . $tableName . '.' : null);

        return true;
    }

    protected function persistRow(ImportJob $job, $tableName, array $row, $tableMeta, array $importedTables, array $pendingTables = [])
    {
        $oldId = array_key_exists('id', $row) ? $row['id'] : null;

        if ($oldId !== null) {
            $mappedId = $this->mapService->findMappedId($job->id, $job->company_id, $tableName, $oldId);
            if ($mappedId !== null) {
                return ['status' => 'processed', 'new_id' => $mappedId];
            }
        }

        $transform = $this->transformRow($job, $tableName, $row, $tableMeta, $importedTables, $pendingTables);
        if ($transform['status'] !== 'ready') {
            return $transform;
        }

        $row = $transform['row'];
        $existingId = $this->resolveExistingRecord($job, $tableName, $row, $tableMeta, $oldId);
        if ($existingId !== null) {
            if ($oldId !== null) {
                $this->mapService->remember($job->id, $job->company_id, $tableName, $oldId, $existingId, $row);
            }
            return ['status' => 'processed', 'new_id' => $existingId, 'action' => 'matched_existing'];
        }

        try {
            $newId = $this->insertRow($tableName, $row, $tableMeta);
            if ($oldId !== null && $newId !== null) {
                $this->mapService->remember($job->id, $job->company_id, $tableName, $oldId, $newId, $row);
            }

            return ['status' => 'processed', 'new_id' => $newId, 'action' => 'inserted'];
        } catch (QueryException $exception) {
            if ($this->isForeignKeyError($exception)) {
                return ['status' => 'deferred', 'reason' => $exception->getMessage()];
            }

            if ($this->isDuplicateKeyError($exception)) {
                $existingId = $this->resolveExistingRecord($job, $tableName, $row, $tableMeta, $oldId);
                if ($existingId !== null) {
                    if ($oldId !== null) {
                        $this->mapService->remember($job->id, $job->company_id, $tableName, $oldId, $existingId, $row);
                    }

                    return ['status' => 'processed', 'new_id' => $existingId, 'action' => 'matched_after_duplicate'];
                }
            }

            return ['status' => 'failed', 'reason' => $exception->getMessage()];
        }
    }

    protected function transformRow(ImportJob $job, $tableName, array $row, $tableMeta, array $importedTables, array $pendingTables = [])
    {
        if (!$tableMeta) {
            return ['status' => 'failed', 'reason' => 'No hay metadata disponible para la tabla ' . $tableName . '.'];
        }

        // Evita errores por columnas presentes en el dump pero no existentes en el esquema destino.
        if (isset($tableMeta['columns']) && is_array($tableMeta['columns'])) {
            foreach (array_keys($row) as $columnName) {
                if (!isset($tableMeta['columns'][$columnName])) {
                    unset($row[$columnName]);
                }
            }
        }

        if ($tableMeta['has_company_id']) {
            $row['company_id'] = $job->company_id;
        }

        if (array_key_exists('user_id', $row) && $job->user_id) {
            $row['user_id'] = $job->user_id;
        }

        foreach ($tableMeta['foreign_keys'] as $foreignKey) {
            $column = $foreignKey['column'];
            $referencedTable = $foreignKey['referenced_table'];

            if (!array_key_exists($column, $row)) {
                continue;
            }

            $value = $row[$column];
            if ($value === null || $value === '') {
                continue;
            }

            $columnMeta = isset($tableMeta['columns'][$column]) ? $tableMeta['columns'][$column] : null;
            $isNullableColumn = $columnMeta ? (bool) $columnMeta['nullable'] : false;

            if ($referencedTable === 'companies') {
                $row[$column] = $job->company_id;
                continue;
            }

            if ($referencedTable === 'users') {
                if ($job->user_id) {
                    $row[$column] = $job->user_id;
                }
                continue;
            }

            $mappedId = $this->mapService->findMappedId($job->id, $job->company_id, $referencedTable, $value);
            if ($mappedId !== null) {
                $row[$column] = $mappedId;
                continue;
            }

            if (in_array($referencedTable, $pendingTables, true)) {
                if ($isNullableColumn) {
                    $row[$column] = null;
                    continue;
                }

                return ['status' => 'deferred', 'reason' => 'Dependencia pendiente: ' . $referencedTable . '.' . $value];
            }

            if (!$this->referenceExists($referencedTable, $value, $job->company_id)) {
                if ($isNullableColumn) {
                    $row[$column] = null;
                    continue;
                }

                return ['status' => 'deferred', 'reason' => 'Referencia inexistente: ' . $referencedTable . '.' . $value];
            }
        }

        foreach ($tableMeta['auto_increment_columns'] as $columnName) {
            if (array_key_exists($columnName, $row)) {
                unset($row[$columnName]);
            }
        }

        $row = $this->normalizeRequiredColumns($row, $tableMeta);

        return ['status' => 'ready', 'row' => $row];
    }

    protected function normalizeRequiredColumns(array $row, $tableMeta)
    {
        if (!isset($tableMeta['columns']) || !is_array($tableMeta['columns'])) {
            return $row;
        }

        $autoIncrementColumns = isset($tableMeta['auto_increment_columns']) && is_array($tableMeta['auto_increment_columns'])
            ? $tableMeta['auto_increment_columns']
            : [];

        foreach ($tableMeta['columns'] as $columnName => $columnMeta) {
            if (!array_key_exists($columnName, $row)) {
                continue;
            }

            if (in_array($columnName, $autoIncrementColumns, true)) {
                continue;
            }

            $value = $row[$columnName];
            $isNullLike = $value === null || $value === '';

            if (!$isNullLike) {
                continue;
            }

            if (!empty($columnMeta['nullable'])) {
                continue;
            }

            // Si tiene DEFAULT en DB, eliminamos la columna para que MySQL aplique ese default.
            if (array_key_exists('default', $columnMeta) && $columnMeta['default'] !== null) {
                unset($row[$columnName]);
                continue;
            }

            $dataType = strtolower(isset($columnMeta['data_type']) ? $columnMeta['data_type'] : '');

            if (in_array($dataType, ['tinyint', 'smallint', 'mediumint', 'int', 'integer', 'bigint', 'decimal', 'double', 'float', 'numeric', 'real', 'bit'], true)) {
                $row[$columnName] = 0;
                continue;
            }

            if ($dataType === 'date') {
                $row[$columnName] = '1970-01-01';
                continue;
            }

            if (in_array($dataType, ['datetime', 'timestamp'], true)) {
                $row[$columnName] = now()->toDateTimeString();
                continue;
            }

            if ($dataType === 'time') {
                $row[$columnName] = '00:00:00';
                continue;
            }

            // Fallback para strings/text required (ej. customers.address).
            $row[$columnName] = '';
        }

        return $row;
    }

    protected function resolveExistingRecord(ImportJob $job, $tableName, array $row, $tableMeta, $oldId)
    {
        if (!$tableMeta) {
            return null;
        }

        if ($oldId !== null) {
            $mappedId = $this->mapService->findMappedId(null, $job->company_id, $tableName, $oldId);
            if ($mappedId !== null) {
                return $mappedId;
            }
        }

        $primaryKey = $tableMeta['primary_key'];
        if ($primaryKey && !in_array($primaryKey, $tableMeta['auto_increment_columns'], true) && array_key_exists($primaryKey, $row)) {
            $query = DB::table($tableName)->where($primaryKey, $row[$primaryKey]);
            if ($tableMeta['has_company_id'] && array_key_exists('company_id', $row)) {
                $query->where('company_id', $row['company_id']);
            }
            $existing = $query->first();
            if ($existing) {
                return isset($existing->{$primaryKey}) ? $existing->{$primaryKey} : $row[$primaryKey];
            }
        }

        foreach ($tableMeta['natural_keys'] as $naturalKey) {
            if (!array_key_exists($naturalKey, $row) || $row[$naturalKey] === null || $row[$naturalKey] === '') {
                continue;
            }

            $query = DB::table($tableName)->where($naturalKey, $row[$naturalKey]);
            if ($tableMeta['has_company_id'] && array_key_exists('company_id', $row)) {
                $query->where('company_id', $row['company_id']);
            }

            $existing = $query->first();
            if ($existing) {
                $identifier = $primaryKey && isset($existing->{$primaryKey}) ? $existing->{$primaryKey} : null;
                if ($identifier !== null) {
                    return $identifier;
                }
            }
        }

        return null;
    }

    protected function insertRow($tableName, array $row, $tableMeta)
    {
        $primaryKey = $tableMeta['primary_key'];
        $usesAutoIncrement = $primaryKey && in_array($primaryKey, $tableMeta['auto_increment_columns'], true);

        // Remove NULL values for columns with DEFAULT to allow DB defaults to apply
        foreach (array_keys($row) as $columnName) {
            if ($row[$columnName] === null && isset($tableMeta['columns'][$columnName])) {
                $columnMeta = $tableMeta['columns'][$columnName];
                // If column has a default and is NOT nullable, remove the NULL value
                // so MySQL applies the DEFAULT instead of rejecting with NOT NULL error
                if (!$columnMeta['nullable'] && isset($columnMeta['default'])) {
                    unset($row[$columnName]);
                }
            }
        }

        if ($usesAutoIncrement) {
            return DB::table($tableName)->insertGetId($row);
        }

        DB::table($tableName)->insert($row);
        return $primaryKey && array_key_exists($primaryKey, $row) ? $row[$primaryKey] : null;
    }

    protected function referenceExists($tableName, $id, $companyId)
    {
        $metadata = $this->schemaService->getTableMetadata([$tableName]);
        if (!isset($metadata[$tableName])) {
            return false;
        }

        $tableMeta = $metadata[$tableName];
        $primaryKey = $tableMeta['primary_key'] ?: 'id';
        $query = DB::table($tableName)->where($primaryKey, $id);

        if ($tableMeta['has_company_id']) {
            $query->where('company_id', $companyId);
        }

        return $query->exists();
    }

    protected function isForeignKeyError(QueryException $exception)
    {
        $message = $exception->getMessage();
        return strpos($message, 'foreign key constraint fails') !== false || strpos($message, 'Cannot add or update a child row') !== false;
    }

    protected function isDuplicateKeyError(QueryException $exception)
    {
        return strpos($exception->getMessage(), 'Duplicate entry') !== false;
    }
}