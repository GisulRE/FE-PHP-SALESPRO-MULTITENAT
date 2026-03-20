<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$jobId = isset($argv[1]) ? (int) $argv[1] : 2;

$job = DB::table('import_jobs')->where('id', $jobId)->first();
if (!$job) {
    echo "JOB_NOT_FOUND\n";
    exit(1);
}

echo "=== JOB {$jobId} ===\n";
echo "status={$job->status}\n";
echo "processed_tables={$job->processed_tables}/{$job->total_tables}\n";
echo "processed_rows={$job->processed_rows}/{$job->total_rows}\n";
echo "failed_rows={$job->failed_rows}\n";

echo "\n=== DETAILS ===\n";
$details = DB::table('import_job_details')
    ->where('import_job_id', $jobId)
    ->orderBy('sort_order')
    ->get();

foreach ($details as $d) {
    echo "- {$d->table_name} | {$d->status} | processed={$d->processed_rows}/{$d->total_rows} | failed={$d->failed_rows} | retries={$d->retries}\n";
}

echo "\n=== TABLAS customer* / *sales* ===\n";
$targetDetails = DB::table('import_job_details')
    ->where('import_job_id', $jobId)
    ->where(function ($q) {
        $q->where('table_name', 'like', 'customer%')
          ->orWhere('table_name', 'like', '%sales%')
          ->orWhere('table_name', 'customers')
          ->orWhere('table_name', 'product_sales');
    })
    ->orderBy('table_name')
    ->get();

foreach ($targetDetails as $d) {
    echo "* {$d->table_name} => {$d->status}, processed={$d->processed_rows}, failed={$d->failed_rows}\n";

    $logs = DB::table('import_job_logs')
        ->where('import_job_id', $jobId)
        ->where('import_job_detail_id', $d->id)
        ->whereIn('level', ['error', 'warning'])
        ->orderByDesc('id')
        ->limit(3)
        ->get();

    foreach ($logs as $log) {
        echo "  [{$log->level}] {$log->message}\n";
        $ctx = json_decode((string) $log->context, true);
        if (is_array($ctx) && isset($ctx['reason'])) {
            echo "    reason: " . substr($ctx['reason'], 0, 220) . "\n";
        } elseif (is_array($ctx) && isset($ctx['remaining_rows'])) {
            echo "    remaining_rows: {$ctx['remaining_rows']}\n";
        }
    }
}

echo "\n=== ULTIMOS LOGS DEL JOB ===\n";
$recent = DB::table('import_job_logs')
    ->where('import_job_id', $jobId)
    ->orderByDesc('id')
    ->limit(12)
    ->get();

foreach ($recent as $r) {
    echo "[{$r->created_at}] {$r->level} - {$r->message}\n";
}
