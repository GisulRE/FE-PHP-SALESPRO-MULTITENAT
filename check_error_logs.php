<?php
// Check the actual errors from logs
require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n=== ÚLTIMOS ERRORES DEL JOB 3 (import_job_logs) ===\n\n";

// Get recent error logs
$logs = DB::table('import_job_logs')
    ->where('import_job_id', 3)
    ->where('level', 'error')
    ->orderByDesc('created_at')
    ->limit(20)
    ->get();

foreach ($logs as $log) {
    echo "[{$log->created_at}] {$log->table_name}:\n";
    echo "  " . substr($log->message, 0, 300) . (strlen($log->message) > 300 ? "..." : "") . "\n\n";
}

echo "\n=== TIPOS DE ERRORES ÚNICOS ===\n\n";

// Get unique error patterns
$error_patterns = DB::table('import_job_logs')
    ->where('import_job_id', 3)
    ->where('level', 'error')
    ->distinct()
    ->select('message', 'table_name', DB::raw('COUNT(*) as count'))
    ->groupBy('message', 'table_name')
    ->orderByDesc('count')
    ->limit(15)
    ->get();

foreach ($error_patterns as $pattern) {
    echo "[" . $pattern->table_name . "] (x{$pattern->count})\n";
    echo "  " . substr($pattern->message, 0, 200) . (strlen($pattern->message) > 200 ? "..." : "") . "\n\n";
}
