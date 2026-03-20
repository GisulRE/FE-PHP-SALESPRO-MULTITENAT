<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\ImportJob;
use App\Jobs\ProcessCompanyImportJob;
use App\Services\Import\ImportProgressService;
use App\Services\Import\ImportSchemaService;
use App\Services\Import\SqlInsertParserService;
use Illuminate\Support\Facades\Storage;

$sourceJobId = isset($argv[1]) ? (int) $argv[1] : 1;

$sourceJob = ImportJob::with('details')->find($sourceJobId);
if (!$sourceJob) {
    echo "ERROR: job {$sourceJobId} no existe\n";
    exit(1);
}

if (!Storage::disk('local')->exists($sourceJob->source_path)) {
    echo "ERROR: no existe archivo fuente {$sourceJob->source_path}\n";
    exit(1);
}

$failedOrPartial = $sourceJob->details
    ->filter(function ($d) {
        return in_array($d->status, ['failed', 'partial'], true) || ((int) $d->failed_rows > 0);
    })
    ->pluck('table_name')
    ->unique()
    ->values()
    ->all();

if (empty($failedOrPartial)) {
    echo "INFO: job {$sourceJobId} no tiene tablas failed/partial\n";
    exit(0);
}

$parser = app(SqlInsertParserService::class);
$schema = app(ImportSchemaService::class);
$progress = app(ImportProgressService::class);

$parsed = $parser->parseFileSummary(storage_path('app/' . $sourceJob->source_path));
$parsedTables = $parsed['tables'];

$filtered = [];
foreach ($failedOrPartial as $tableName) {
    if (!isset($parsedTables[$tableName])) {
        continue;
    }

    if (in_array($tableName, $schema->getExcludedTables(), true)) {
        continue;
    }

    $payload = $parsedTables[$tableName];
    $rowCount = isset($payload['row_count']) ? (int) $payload['row_count'] : count($payload['rows']);
    if ($rowCount <= 0) {
        continue;
    }

    $filtered[$tableName] = $payload;
}

if (empty($filtered)) {
    echo "INFO: no quedaron tablas importables para reintentar\n";
    exit(0);
}

$tableNames = array_keys($filtered);
$order = $schema->resolveMigrationOrder($tableNames);
$roots = $schema->getRootTables($tableNames);

$newJob = ImportJob::create([
    'company_id' => $sourceJob->company_id,
    'user_id' => $sourceJob->user_id,
    'source_name' => $sourceJob->source_name,
    'source_path' => $sourceJob->source_path,
    'status' => 'queued',
    'root_tables' => $roots,
    'migration_order' => $order,
    'options' => [
        'retry_of' => $sourceJob->id,
        'retry_mode' => 'failed_or_partial_tables_only',
        'retry_tables' => $order,
        'queue_driver' => config('queue.default'),
        'split_before_import' => true,
    ],
]);

$progress->initializeJob($newJob, $order, $filtered);
ProcessCompanyImportJob::dispatch($newJob->id)->onQueue('imports');

echo "RETRY_JOB_CREATED={$newJob->id}\n";
echo "SOURCE_JOB={$sourceJob->id}\n";
echo "TABLES=" . implode(',', $order) . "\n";
