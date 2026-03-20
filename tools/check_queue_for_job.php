<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$jobId = isset($argv[1]) ? (int) $argv[1] : 2;

$rows = DB::table('jobs')->orderBy('id')->get();
echo "jobs_count=" . $rows->count() . "\n";

$found = 0;
foreach ($rows as $r) {
    if (strpos($r->payload, 'ProcessCompanyImportJob') !== false && (strpos($r->payload, 'importJobId') !== false || strpos($r->payload, 'import_job_id') !== false)) {
        $containsTarget = strpos($r->payload, '"importJobId";i:' . $jobId) !== false
            || strpos($r->payload, 'importJobId\";i:' . $jobId) !== false
            || strpos($r->payload, 'i:' . $jobId . ';') !== false;
        echo "jobs.id={$r->id}, queue={$r->queue}, attempts={$r->attempts}, is_target=" . ($containsTarget ? 'YES' : 'NO') . "\n";
        if ($containsTarget) {
            $found++;
        }
    }
}

echo "target_found={$found}\n";
