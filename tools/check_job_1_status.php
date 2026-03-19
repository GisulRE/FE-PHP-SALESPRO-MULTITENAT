<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$job = DB::table('import_jobs')->where('id', 1)->first();

echo "=== import_jobs id=1 ===\n";
if (!$job) {
    echo "NOT_FOUND\n";
    exit(0);
}

echo "status={$job->status}\n";
echo "processed_tables={$job->processed_tables}\n";
echo "total_tables={$job->total_tables}\n";
echo "processed_rows={$job->processed_rows}\n";
echo "total_rows={$job->total_rows}\n";
echo "failed_rows={$job->failed_rows}\n";

echo "\n=== jobs queue entry for import job 1 ===\n";
$queueJobs = DB::table('jobs')->get();
$found = 0;
foreach ($queueJobs as $qj) {
    if (strpos($qj->payload, 'ProcessCompanyImportJob') !== false && strpos($qj->payload, '"importJobId";i:1') !== false) {
        $found++;
        echo "jobs.id={$qj->id}, queue={$qj->queue}, attempts={$qj->attempts}\n";
    }
}

if ($found === 0) {
    echo "NO_QUEUE_ENTRY_FOR_JOB_1\n";
}
