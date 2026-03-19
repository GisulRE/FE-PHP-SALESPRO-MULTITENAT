<?php

namespace App\Http\Controllers;

use App\Company;
use App\ImportJob;
use App\Jobs\ProcessCompanyImportJob;
use App\Services\Import\DumpSplitService;
use App\Services\Import\ImportProgressService;
use App\Services\Import\ImportSchemaService;
use App\Services\Import\SqlInsertParserService;
use Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class CompanyDataImportController extends Controller
{
    protected function buildQueueOverview()
    {
        $pendingByQueue = DB::table('jobs')
            ->select('queue', DB::raw('COUNT(*) as total'), DB::raw('SUM(CASE WHEN attempts > 0 THEN 1 ELSE 0 END) as with_attempts'))
            ->groupBy('queue')
            ->orderBy('queue')
            ->get();

        $pendingJobs = DB::table('jobs')
            ->select('id', 'queue', 'attempts', 'created_at', 'available_at')
            ->orderBy('id', 'asc')
            ->limit(25)
            ->get();

        return [
            'default_driver' => config('queue.default'),
            'pending_total' => (int) DB::table('jobs')->count(),
            'failed_total' => (int) DB::table('failed_jobs')->count(),
            'import_jobs' => [
                'queued' => (int) ImportJob::where('status', 'queued')->count(),
                'running' => (int) ImportJob::where('status', 'running')->count(),
                'cancelling' => (int) ImportJob::where('status', 'cancelling')->count(),
                'cancel_requested' => (int) ImportJob::where('status', 'cancel_requested')->count(),
                'cancelled' => (int) ImportJob::where('status', 'cancelled')->count(),
                'failed' => (int) ImportJob::where('status', 'failed')->count(),
                'partial' => (int) ImportJob::where('status', 'partial')->count(),
            ],
            'pending_by_queue' => $pendingByQueue,
            'pending_jobs' => $pendingJobs,
            'workers_should_stop' => (bool) cache('illuminate:queue:restart'),
            'server_now' => now()->toDateTimeString(),
        ];
    }

    protected function filterImportableTables(array $parsedTables, ImportSchemaService $schemaService)
    {
        $filteredTables = [];
        $omittedExcludedTables = [];
        $omittedNoRowsTables = [];

        foreach ($parsedTables as $tableName => $payload) {
            if (in_array($tableName, $schemaService->getExcludedTables(), true)) {
                $omittedExcludedTables[] = $tableName;
                continue;
            }

            $rowCount = isset($payload['row_count']) ? (int) $payload['row_count'] : count($payload['rows']);
            if ($rowCount <= 0) {
                $omittedNoRowsTables[] = $tableName;
                continue;
            }

            $filteredTables[$tableName] = $payload;
        }

        sort($omittedExcludedTables);
        sort($omittedNoRowsTables);

        return [$filteredTables, $omittedExcludedTables, $omittedNoRowsTables];
    }

    protected function ensurePermission()
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role || !$role->hasPermissionTo('backup_database')) {
            abort(403, 'No autorizado.');
        }
    }

    public function index(Request $request, ImportSchemaService $schemaService)
    {
        $this->ensurePermission();

        $activeJob = null;
        if ($request->filled('job_id')) {
            $activeJob = ImportJob::with(['details', 'logs', 'company', 'user'])->find($request->input('job_id'));
        }

        return view('setting.restore_company_data', [
            'companies' => Company::orderBy('name')->get(),
            'companyId' => null,
            'comparison' => [],
            'tempPath' => null,
            'previewWarnings' => [],
            'migrationOrder' => [],
            'rootTables' => [],
            'totalDetectedTables' => 0,
            'totalImportableTables' => 0,
            'omittedExcludedTables' => [],
            'omittedNoRowsTables' => [],
            'detectedTablesPreview' => [],
            'globalRootTables' => $schemaService->getRootTables(),
            'recentJobs' => ImportJob::with(['company', 'user'])->latest('id')->limit(15)->get(),
            'activeJob' => $activeJob,
            'queueOverview' => $this->buildQueueOverview(),
        ]);
    }

    public function preview(Request $request, SqlInsertParserService $parserService, ImportSchemaService $schemaService, DumpSplitService $dumpSplitService)
    {
        $this->ensurePermission();

        $this->validate($request, [
            'company_id' => 'required|exists:companies,id',
            'restore_file' => 'required|file|mimes:sql,txt|max:204800',
        ]);

        // Solo sube el límite; no lo restaura para evitar excepciones cuando el uso actual ya lo supera.
        ini_set('memory_limit', '1024M');

        $companyId = (int) $request->input('company_id');
        $tempPath = $request->file('restore_file')->store('restore_preview', 'local');
        $parsed = $parserService->parseFileSummary(storage_path('app/' . $tempPath));
        list($filteredTables, $omittedExcludedTables, $omittedNoRowsTables) = $this->filterImportableTables($parsed['tables'], $schemaService);
        $preview = $schemaService->buildPreview($filteredTables);
        $warnings = array_merge($preview['warnings'], $parsed['issues']);

        $detectedTablesPreview = [];
        $orderPosition = [];
        foreach ($preview['migration_order'] as $index => $tableName) {
            $orderPosition[$tableName] = $index + 1;
        }

        $splitFolder = storage_path('app/restore_preview_split/' . md5($tempPath . microtime(true)));
        try {
            $splitSummary = $dumpSplitService->split(storage_path('app/' . $tempPath), $splitFolder, true);
            foreach ($splitSummary['tables'] as $tableInfo) {
                $tableName = $tableInfo['table'];
                $rows = (int) $tableInfo['rows'];

                $status = 'se_importa';
                if (in_array($tableName, $omittedExcludedTables, true)) {
                    $status = 'excluida';
                } elseif ($rows <= 0) {
                    $status = 'sin_filas';
                } elseif (!isset($filteredTables[$tableName])) {
                    $status = 'omitida';
                }

                $detectedTablesPreview[] = [
                    'table' => $tableName,
                    'rows' => $rows,
                    'status' => $status,
                    'order' => isset($orderPosition[$tableName]) ? $orderPosition[$tableName] : null,
                ];
            }
        } catch (\Throwable $exception) {
            $warnings[] = 'No se pudo construir el preview completo de tablas detectadas: ' . $exception->getMessage();
        } finally {
            if (File::exists($splitFolder)) {
                File::deleteDirectory($splitFolder);
            }
        }

        if (!empty($omittedExcludedTables)) {
            $warnings[] = 'Se omitieron tablas excluidas de importación: ' . implode(', ', $omittedExcludedTables) . '.';
        }
        if (!empty($omittedNoRowsTables)) {
            $warnings[] = 'Se omitieron tablas sin filas INSERT en el dump: ' . implode(', ', $omittedNoRowsTables) . '.';
        }

        return view('setting.restore_company_data', [
            'companies' => Company::orderBy('name')->get(),
            'companyId' => $companyId,
            'comparison' => $preview['comparison'],
            'tempPath' => $tempPath,
            'previewWarnings' => $warnings,
            'migrationOrder' => $preview['migration_order'],
            'rootTables' => $preview['root_tables'],
            'totalDetectedTables' => !empty($detectedTablesPreview) ? count($detectedTablesPreview) : count($parsed['tables']),
            'totalImportableTables' => count($filteredTables),
            'omittedExcludedTables' => $omittedExcludedTables,
            'omittedNoRowsTables' => $omittedNoRowsTables,
            'detectedTablesPreview' => $detectedTablesPreview,
            'globalRootTables' => $schemaService->getRootTables(),
            'recentJobs' => ImportJob::with(['company', 'user'])->latest('id')->limit(15)->get(),
            'activeJob' => null,
            'queueOverview' => $this->buildQueueOverview(),
        ]);
    }

    public function store(Request $request, SqlInsertParserService $parserService, ImportSchemaService $schemaService, ImportProgressService $progressService)
    {
        $this->ensurePermission();

        $this->validate($request, [
            'company_id' => 'required|exists:companies,id',
        ]);

        // Solo sube el límite; no lo restaura para evitar excepciones cuando el uso actual ya lo supera.
        ini_set('memory_limit', '1024M');

        $companyId = (int) $request->input('company_id');
        $sourceName = null;
        $sourcePath = null;

            if ($request->hasFile('restore_file')) {
                $this->validate($request, [
                    'restore_file' => 'required|file|mimes:sql,txt|max:204800',
                ]);
                $sourceName = $request->file('restore_file')->getClientOriginalName();
                $sourcePath = $request->file('restore_file')->store('imports/pending', 'local');
            } elseif ($request->filled('temp_file')) {
                $tempPath = $request->input('temp_file');
                if (!preg_match('/^restore_preview\/[a-zA-Z0-9_\-\.\/]+$/', $tempPath) || !Storage::disk('local')->exists($tempPath)) {
                    return redirect()->route('setting.restoreCompanyData')->with('not_permitted', 'El archivo temporal ya no existe. Analiza el SQL nuevamente.');
                }

                $sourceName = basename($tempPath);
                $sourcePath = 'imports/pending/' . date('YmdHis') . '_' . basename($tempPath);
                Storage::disk('local')->copy($tempPath, $sourcePath);
            } else {
                return redirect()->route('setting.restoreCompanyData')->with('not_permitted', 'Debes subir un archivo SQL o venir desde el análisis previo.');
            }

        $parsed = $parserService->parseFileSummary(storage_path('app/' . $sourcePath));
        list($filteredTables) = $this->filterImportableTables($parsed['tables'], $schemaService);

        $tableNames = array_keys($filteredTables);
        $order = $schemaService->resolveMigrationOrder($tableNames);
        $roots = $schemaService->getRootTables($tableNames);

        $job = ImportJob::create([
            'company_id' => $companyId,
            'user_id' => Auth::id(),
            'source_name' => $sourceName,
            'source_path' => $sourcePath,
            'status' => 'queued',
            'root_tables' => $roots,
            'migration_order' => $order,
            'options' => [
                'queue_driver' => config('queue.default'),
                'parser_issues' => $parsed['issues'],
                'split_before_import' => true,
            ],
        ]);

        $progressService->initializeJob($job, $order, $filteredTables);
        ProcessCompanyImportJob::dispatch($job->id)->onQueue('imports');

        $message = 'Importación encolada. Job #' . $job->id . '. '; 
        if (config('queue.default') === 'sync') {
            $message .= 'El proyecto sigue usando QUEUE_DRIVER=sync; cambia a database para ver progreso en tiempo real.';
        } else {
            $message .= 'Puedes seguir el progreso y logs en esta misma pantalla.';
        }

        return redirect()->route('setting.restoreCompanyData', ['job_id' => $job->id])->with('message', $message);
    }

    public function status(ImportJob $importJob)
    {
        $this->ensurePermission();
        $importJob->load(['details', 'logs', 'company', 'user']);

        $percentage = $importJob->total_rows > 0
            ? round((($importJob->processed_rows + $importJob->failed_rows) / $importJob->total_rows) * 100, 2)
            : 0;

        return response()->json([
            'job' => [
                'id' => $importJob->id,
                'status' => $importJob->status,
                'company_id' => $importJob->company_id,
                'company_name' => $importJob->company ? $importJob->company->name : null,
                'source_name' => $importJob->source_name,
                'total_tables' => $importJob->total_tables,
                'processed_tables' => $importJob->processed_tables,
                'total_rows' => $importJob->total_rows,
                'processed_rows' => $importJob->processed_rows,
                'failed_rows' => $importJob->failed_rows,
                'retries' => $importJob->retries,
                'started_at' => $importJob->started_at ? $importJob->started_at->toDateTimeString() : null,
                'finished_at' => $importJob->finished_at ? $importJob->finished_at->toDateTimeString() : null,
                'percentage' => $percentage,
                'root_tables' => $importJob->root_tables ?: [],
                'migration_order' => $importJob->migration_order ?: [],
                'last_error' => $importJob->last_error,
            ],
            'details' => $importJob->details->map(function ($detail) {
                return [
                    'table_name' => $detail->table_name,
                    'status' => $detail->status,
                    'sort_order' => $detail->sort_order,
                    'total_rows' => $detail->total_rows,
                    'processed_rows' => $detail->processed_rows,
                    'failed_rows' => $detail->failed_rows,
                    'deferred_rows' => $detail->deferred_rows,
                    'retries' => $detail->retries,
                    'error_message' => $detail->error_message,
                ];
            })->values(),
            'logs' => $importJob->logs->take(80)->map(function ($log) {
                return [
                    'id' => $log->id,
                    'level' => $log->level,
                    'message' => $log->message,
                    'context' => $log->context,
                    'created_at' => $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : null,
                ];
            })->values(),
            'queue_overview' => $this->buildQueueOverview(),
        ]);
    }

    public function queuesStatus()
    {
        $this->ensurePermission();

        return response()->json($this->buildQueueOverview());
    }

    public function stopWorkers()
    {
        $this->ensurePermission();

        Artisan::call('queue:restart');

        return redirect()->route('setting.restoreCompanyData')->with('message', 'Se envio la senal para detener workers de cola en ejecucion.');
    }

    public function cancel(Request $request, ImportJob $importJob, ImportProgressService $progressService)
    {
        $this->ensurePermission();

        $mode = $request->input('mode', 'hard');
        if (!in_array($mode, ['soft', 'hard'], true)) {
            $mode = 'hard';
        }

        if (in_array($importJob->status, ['completed', 'failed', 'partial', 'cancelled'], true)) {
            return redirect()->route('setting.restoreCompanyData', ['job_id' => $importJob->id])
                ->with('not_permitted', 'El job ya finalizó y no se puede cancelar.');
        }

        $options = is_array($importJob->options) ? $importJob->options : [];
        $options['cancel_requested'] = true;
        $options['cancel_mode'] = $mode;
        $options['cancel_requested_at'] = now()->toDateTimeString();
        $options['cancel_requested_by'] = Auth::id();

        $queuedDeleted = 0;
        if ($mode === 'hard') {
            $queuedDeleted = DB::table('jobs')
                ->where('queue', 'imports')
                ->where('payload', 'like', '%importJobId";i:'.$importJob->id.';%')
                ->delete();
        }

        $newStatus = in_array($importJob->status, ['running', 'cancelling'], true)
            ? 'cancelling'
            : ($mode === 'hard' ? 'cancelled' : 'cancel_requested');

        $importJob->update([
            'status' => $newStatus,
            'options' => $options,
            'last_error' => $mode === 'hard'
                ? 'Cancelado por usuario (en seco).'
                : 'Cancelacion solicitada por usuario.',
            'finished_at' => $newStatus === 'cancelled' ? now() : null,
        ]);

        if ($newStatus === 'cancelled') {
            $progressService->cancelJob($importJob->fresh(), 'Cancelado por usuario (en seco).');
            $progressService->log($importJob->fresh(), 'warning', 'Job cancelado en seco por usuario.', [
                'mode' => $mode,
                'deleted_queue_rows' => $queuedDeleted,
            ]);

            return redirect()->route('setting.restoreCompanyData')
                ->with('message', 'Job #'.$importJob->id.' cancelado en seco. Filas removidas de la cola: '.$queuedDeleted.'.');
        }

        $progressService->log($importJob->fresh(), 'warning', 'Se solicito cancelacion del job por usuario.', [
            'mode' => $mode,
            'deleted_queue_rows' => $queuedDeleted,
        ]);

        return redirect()->route('setting.restoreCompanyData', ['job_id' => $importJob->id])
            ->with('message', 'Cancelacion solicitada para Job #'.$importJob->id.'. Modo: '.$mode.'.');
    }

    public function retry(ImportJob $importJob, ImportProgressService $progressService, SqlInsertParserService $parserService, ImportSchemaService $schemaService)
    {
        $this->ensurePermission();

        if (!Storage::disk('local')->exists($importJob->source_path)) {
            return redirect()->route('setting.restoreCompanyData')->with('not_permitted', 'El archivo fuente original ya no está disponible para reintentar.');
        }

        ini_set('memory_limit', '1024M');
        $parsed = $parserService->parseFileSummary(storage_path('app/' . $importJob->source_path));
        list($filteredTables) = $this->filterImportableTables($parsed['tables'], $schemaService);

        $tableNames = array_keys($filteredTables);
        $order = $schemaService->resolveMigrationOrder($tableNames);
        $roots = $schemaService->getRootTables($tableNames);

        $retryJob = ImportJob::create([
            'company_id' => $importJob->company_id,
            'user_id' => Auth::id(),
            'source_name' => $importJob->source_name,
            'source_path' => $importJob->source_path,
            'status' => 'queued',
            'root_tables' => $roots,
            'migration_order' => $order,
            'options' => [
                'retry_of' => $importJob->id,
                'queue_driver' => config('queue.default'),
                'split_before_import' => true,
            ],
        ]);

        $progressService->initializeJob($retryJob, $order, $filteredTables);
        ProcessCompanyImportJob::dispatch($retryJob->id)->onQueue('imports');

        return redirect()->route('setting.restoreCompanyData', ['job_id' => $retryJob->id])->with('message', 'Reintento encolado. Job #' . $retryJob->id . '.');
    }
}