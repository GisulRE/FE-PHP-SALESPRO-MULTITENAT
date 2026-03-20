<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$jobId = isset($argv[1]) ? (int) $argv[1] : 2;

$job = DB::table('import_jobs')->where('id', $jobId)->first();
if (!$job) {
    echo "NOT_FOUND\n";
    exit(1);
}

echo "job_id={$job->id}\n";
echo "status={$job->status}\n";
echo "processed_tables={$job->processed_tables}/{$job->total_tables}\n";
echo "processed_rows={$job->processed_rows}/{$job->total_rows}\n";
echo "failed_rows={$job->failed_rows}\n";

echo "\nDETAILS\n";
$details = DB::table('import_job_details')->where('import_job_id', $jobId)->orderBy('sort_order')->get();
foreach ($details as $d) {
    echo "- {$d->table_name} | {$d->status} | {$d->processed_rows}/{$d->total_rows} | failed={$d->failed_rows}\n";
}
