<?php
require __DIR__ . '/../bootstrap/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== ERRORES EN IMPORT JOB 3 ===\n\n";

// Get error summary by table
$errors = DB::table('import_job_errors')
    ->where('import_job_id', 3)
    ->select('table_name', 'error_message', DB::raw('COUNT(*) as error_count'))
    ->groupBy('table_name', 'error_message')
    ->orderByDesc('error_count')
    ->limit(30)
    ->get();

if ($errors->isEmpty()) {
    echo "No errors found.\n";
} else {
    foreach ($errors as $error) {
        echo "Tabla: {$error->table_name} | Errores: {$error->error_count}\n";
        echo "Mensaje: " . substr($error->error_message, 0, 150) . (strlen($error->error_message) > 150 ? "..." : "") . "\n\n";
    }
}

// Get total error count by table
echo "\n=== TOTAL DE ERRORES POR TABLA ===\n\n";
$totals = DB::table('import_job_errors')
    ->where('import_job_id', 3)
    ->select('table_name', DB::raw('COUNT(*) as total'))
    ->groupBy('table_name')
    ->orderByDesc('total')
    ->get();

foreach ($totals as $total) {
    echo "{$total->table_name}: {$total->total}\n";
}
