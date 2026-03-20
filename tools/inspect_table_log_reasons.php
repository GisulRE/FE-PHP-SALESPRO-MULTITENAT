<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$jobId = isset($argv[1]) ? (int) $argv[1] : 2;
$table = isset($argv[2]) ? $argv[2] : 'product_pre_sale';

$detail = DB::table('import_job_details')
    ->where('import_job_id', $jobId)
    ->where('table_name', $table)
    ->first();

if (!$detail) {
    echo "DETAIL_NOT_FOUND\n";
    exit(1);
}

echo "job={$jobId}, table={$table}, status={$detail->status}, processed={$detail->processed_rows}, failed={$detail->failed_rows}, retries={$detail->retries}\n\n";

$logs = DB::table('import_job_logs')
    ->where('import_job_id', $jobId)
    ->where('import_job_detail_id', $detail->id)
    ->whereIn('level', ['warning', 'error'])
    ->orderByDesc('id')
    ->limit(20)
    ->get();

foreach ($logs as $log) {
    echo "[{$log->created_at}] {$log->level} {$log->message}\n";
    $ctx = json_decode((string) $log->context, true);
    if (is_array($ctx)) {
        if (isset($ctx['reason'])) {
            echo "  reason: " . substr($ctx['reason'], 0, 280) . "\n";
        }
        if (isset($ctx['remaining_rows'])) {
            echo "  remaining_rows: {$ctx['remaining_rows']}\n";
        }
        if (isset($ctx['row']) && is_array($ctx['row'])) {
            $mini = [];
            foreach (array_slice(array_keys($ctx['row']), 0, 8) as $k) {
                $mini[$k] = $ctx['row'][$k];
            }
            echo "  row_sample=" . json_encode($mini, JSON_UNESCAPED_UNICODE) . "\n";
        }
    }
}
