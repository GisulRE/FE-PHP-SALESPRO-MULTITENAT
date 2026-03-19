<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$jobId = isset($argv[1]) ? (int) $argv[1] : 2;

$job = DB::table('import_jobs')->where('id', $jobId)->first();
if (!$job) {
    echo "ERROR: job {$jobId} no existe\n";
    exit(1);
}

$companyId = (int) $job->company_id;
$defaultUnitId = DB::table('units')->where('id', 1)->value('id');
if (!$defaultUnitId) {
    $defaultUnitId = DB::table('units')->orderBy('id')->value('id');
}
if (!$defaultUnitId) {
    echo "ERROR: no existe ninguna unidad en tabla units\n";
    exit(1);
}

$now = now();

$payload = [
    'import_job_id' => $jobId,
    'company_id' => $companyId,
    'table_name' => 'units',
    'old_id' => 0,
    'new_id' => (int) $defaultUnitId,
    'source_payload' => json_encode(['forced_map' => 'units.0', 'note' => 'temporary fix from job 1 retry']),
    'created_at' => $now,
    'updated_at' => $now,
];

$existing = DB::table('migration_map')
    ->where('import_job_id', $jobId)
    ->where('company_id', $companyId)
    ->where('table_name', 'units')
    ->where('old_id', 0)
    ->first();

if ($existing) {
    DB::table('migration_map')->where('id', $existing->id)->update([
        'new_id' => (int) $defaultUnitId,
        'source_payload' => $payload['source_payload'],
        'updated_at' => $now,
    ]);
    echo "UPDATED_MAP job={$jobId} units:0->{$defaultUnitId}\n";
} else {
    DB::table('migration_map')->insert($payload);
    echo "CREATED_MAP job={$jobId} units:0->{$defaultUnitId}\n";
}

echo "done\n";
