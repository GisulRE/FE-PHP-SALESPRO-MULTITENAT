<?php

namespace App\Services\Import;

use App\ImportJob;
use App\ImportJobDetail;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

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
        try {
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
        } catch (\Throwable $e) {
            Log::error("[Import Job #{$job->id}] Error crítico e inesperado durante la importación: " . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
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
                    $oldId = isset($row['id']) ? $row['id'] : 'unknown';

                    // Loggear en laravel.log siempre para debuguear sin saturar la BD
                    Log::warning("[Import Job #{$job->id}] Fila diferida en tabla '{$tableName}' (ID original: {$oldId}): " . (isset($result['reason']) ? $result['reason'] : 'Fila diferida sin motivo reportado.'), [
                        'table' => $tableName,
                        'row_data' => $row,
                    ]);

                    // Registramos una muestra acotada de diferimientos para facilitar el diagnostico en la BD/UI.
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
                $oldId = isset($row['id']) ? $row['id'] : 'unknown';

                // Loggear en laravel.log con severidad error
                Log::error("[Import Job #{$job->id}] Fila rechazada definitivamente en tabla '{$tableName}' (ID original: {$oldId}): " . $result['reason'], [
                    'table' => $tableName,
                    'row_data' => $row,
                ]);

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

            // Analizar cada una de las filas que no pudieron resolverse al finalizar los intentos
            $unresolvedDetails = [];
            foreach ($pendingRows as $row) {
                $oldId = isset($row['id']) ? $row['id'] : 'unknown';
                $transform = $this->transformRow($job, $tableName, $row, $tableMeta, $importedTables, $pendingTables);
                $reason = ($transform['status'] !== 'ready') ? $transform['reason'] : 'Error desconocido de persistencia en base de datos';
                
                $unresolvedDetails[] = [
                    'id_original' => $oldId,
                    'motivo' => $reason,
                    // Filtramos datos clave para no guardar un log gigantesco en la BD, pero guardamos lo esencial
                    'datos_clave' => array_intersect_key($row, array_flip(['id', 'name', 'code', 'reference', 'parent_id', 'user_id', 'product_id', 'category_id', 'tax_id'])),
                ];

                Log::error("[Import Job #{$job->id}] Fila sin resolver permanentemente en tabla '{$tableName}' (ID original: {$oldId}): {$reason}", [
                    'table' => $tableName,
                    'row_data' => $row,
                ]);
            }

            $this->progressService->log($job, 'error', 'Quedaron filas sin resolver en ' . $tableName . '.', [
                'table' => $tableName,
                'remaining_rows' => $remaining,
                'unresolved_details' => $unresolvedDetails,
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

            // Registrar error de consulta detallado en los logs de Laravel
            Log::error("[Import Job #{$job->id}] Error de consulta al insertar fila en la tabla '{$tableName}': " . $exception->getMessage(), [
                'table' => $tableName,
                'row_data' => $row,
                'sql' => $exception->getSql(),
                'bindings' => $exception->getBindings(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine()
            ]);

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
            $isPhysical = isset($foreignKey['is_physical']) ? (bool) $foreignKey['is_physical'] : false;

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

            // Si es un valor de ID especial indicando "sin relación" (0 o '0')
            if ($value === 0 || $value === '0') {
                if ($isNullableColumn) {
                    $row[$column] = null;
                } elseif ($isPhysical) {
                    // Si tiene constraint físico en PostgreSQL, debemos poner un ID válido
                    $fallbackId = $this->getFallbackId($referencedTable, $job->company_id);
                    if ($fallbackId !== null) {
                        $row[$column] = $fallbackId;
                    }
                }
                // Si no es físico, lo dejamos en 0 / '0' y continuamos sin validar
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
                } else {
                    // La referencia no existe en el dump.
                    if ($isPhysical) {
                        // Si hay constraint físico en BD, asignamos un fallback válido para evitar que PostgreSQL rechace la inserción
                        $fallbackId = $this->getFallbackId($referencedTable, $job->company_id);
                        if ($fallbackId !== null) {
                            $row[$column] = $fallbackId;
                        } else {
                            return ['status' => 'deferred', 'reason' => 'Referencia inexistente física sin fallback disponible: ' . $referencedTable . '.' . $value];
                        }
                    } else {
                        // Si NO hay constraint físico en BD, mantenemos el valor original (ej. id huérfano) sin diferir el registro
                        // para que la fila no se quede atascada.
                        Log::warning("[Import Job #{$job->id}] Mapeo de relación lógica huérfana en tabla '{$tableName}', columna '{$column}'. Se mantiene ID original {$value} ya que no existe restricción física FK en base de datos para la tabla {$referencedTable}.");
                    }
                }
                continue;
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

    protected function getFallbackId($tableName, $companyId)
    {
        $metadata = $this->schemaService->getTableMetadata([$tableName]);
        if (!isset($metadata[$tableName])) {
            return null;
        }

        $tableMeta = $metadata[$tableName];
        $primaryKey = $tableMeta['primary_key'] ?: 'id';
        
        $query = DB::table($tableName);
        if ($tableMeta['has_company_id']) {
            $query->where('company_id', $companyId);
        }

        return $query->value($primaryKey);
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

            if (in_array($dataType, ['tinyint', 'smallint', 'mediumint', 'int', 'integer', 'bigint', 'decimal', 'double', 'float', 'numeric', 'real', 'bit', 'double precision'], true)) {
                $row[$columnName] = 0;
                continue;
            }

            if ($dataType === 'date') {
                $row[$columnName] = '1970-01-01';
                continue;
            }

            // Soporta tipos timestamp de MySQL y PostgreSQL ('timestamp with time zone', 'timestamp without time zone')
            if (in_array($dataType, ['datetime', 'timestamp'], true) || strpos($dataType, 'timestamp') !== false) {
                $row[$columnName] = now()->toDateTimeString();
                continue;
            }

            if ($dataType === 'time' || strpos($dataType, 'time') !== false) {
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

        // Eliminar valores NULL en columnas con DEFAULT para que la BD aplique el default en lugar de rechazar
        foreach (array_keys($row) as $columnName) {
            if ($row[$columnName] === null && isset($tableMeta['columns'][$columnName])) {
                $columnMeta = $tableMeta['columns'][$columnName];
                if (!$columnMeta['nullable'] && isset($columnMeta['default'])) {
                    unset($row[$columnName]);
                }
            }
        }

        if ($usesAutoIncrement) {
            // En PostgreSQL, insertGetId necesita el nombre de la PK para leer el valor de la sequence.
            // En MySQL el segundo argumento es ignorado pero no causa errores.
            return DB::table($tableName)->insertGetId($row, $primaryKey);
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
        return strpos($message, 'foreign key constraint fails') !== false            // MySQL
            || strpos($message, 'Cannot add or update a child row') !== false       // MySQL (alternativo)
            || strpos($message, 'violates foreign key constraint') !== false;        // PostgreSQL
    }

    protected function isDuplicateKeyError(QueryException $exception)
    {
        $message = $exception->getMessage();
        return strpos($message, 'Duplicate entry') !== false                                      // MySQL
            || strpos($message, 'duplicate key value violates unique constraint') !== false;     // PostgreSQL
    }
}