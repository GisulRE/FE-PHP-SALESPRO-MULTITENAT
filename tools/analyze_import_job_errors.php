<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$jobId = isset($argv[1]) ? (int) $argv[1] : 1;

echo "=== IMPORT JOB {$jobId} STATUS ===\n";
$job = DB::table('import_jobs')->where('id', $jobId)->first();
if (!$job) {
    echo "NOT_FOUND\n";
    exit(1);
}

echo "status={$job->status}\n";
echo "processed_tables={$job->processed_tables}/{$job->total_tables}\n";
echo "processed_rows={$job->processed_rows}/{$job->total_rows}\n";
echo "failed_rows={$job->failed_rows}\n\n";

echo "=== TABLAS CON FALLOS O PARCIAL ===\n";
$details = DB::table('import_job_details')
    ->where('import_job_id', $jobId)
    ->where(function ($q) {
        $q->where('failed_rows', '>', 0)
          ->orWhereIn('status', ['failed', 'partial']);
    })
    ->orderByDesc('failed_rows')
    ->get();

if ($details->isEmpty()) {
    echo "Sin tablas con fallos.\n";
} else {
    foreach ($details as $d) {
        echo "- {$d->table_name} | status={$d->status} | total={$d->total_rows} | processed={$d->processed_rows} | failed={$d->failed_rows} | deferred={$d->deferred_rows}\n";
    }
}

echo "\n=== TOP RAZONES DE ERROR (por tabla) ===\n";
foreach ($details as $d) {
    echo "\n[{$d->table_name}]\n";

    $logs = DB::table('import_job_logs')
        ->where('import_job_id', $jobId)
        ->where('import_job_detail_id', $d->id)
        ->whereIn('level', ['error', 'warning'])
        ->orderByDesc('id')
        ->limit(500)
        ->get();

    $reasonCount = [];
    $samples = [];

    foreach ($logs as $log) {
        $msg = (string) $log->message;
        $ctx = null;
        if (!empty($log->context)) {
            $ctx = json_decode($log->context, true);
        }

        $reason = null;

        if (is_array($ctx) && isset($ctx['reason'])) {
            $reason = (string) $ctx['reason'];
        } elseif (is_array($ctx) && isset($ctx['remaining_rows'])) {
            $reason = 'Quedaron filas sin resolver: ' . $ctx['remaining_rows'];
        } else {
            $reason = $msg;
        }

        // Normalize dynamic ids to group better
        $reason = preg_replace('/\b\d{4,}\b/', '{N}', $reason);
        $reason = preg_replace('/\s+/', ' ', trim($reason));

        if (!isset($reasonCount[$reason])) {
            $reasonCount[$reason] = 0;
            $samples[$reason] = [
                'message' => $msg,
                'context' => is_array($ctx) ? $ctx : null,
            ];
        }
        $reasonCount[$reason]++;
    }

    if (empty($reasonCount)) {
        echo "  (sin logs de error/warning para esta tabla)\n";
        continue;
    }

    arsort($reasonCount);
    $i = 0;
    foreach ($reasonCount as $reason => $count) {
        echo "  x{$count} - {$reason}\n";
        $i++;
        if ($i >= 5) {
            break;
        }
    }

    // show one concrete sample for top reason
    $topReason = array_key_first($reasonCount);
    if ($topReason !== null && isset($samples[$topReason])) {
        $sample = $samples[$topReason];
        echo "  muestra: " . substr($sample['message'], 0, 180) . "\n";
        if (!empty($sample['context']['row'])) {
            $row = $sample['context']['row'];
            if (is_array($row)) {
                $keys = array_slice(array_keys($row), 0, 8);
                $mini = [];
                foreach ($keys as $k) {
                    $mini[$k] = $row[$k];
                }
                echo "  row_sample=" . json_encode($mini, JSON_UNESCAPED_UNICODE) . "\n";
            }
        }
    }
}
