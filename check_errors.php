<?php
// Script to check import job 3 errors
require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n=== ERRORES EN IMPORT JOB 3 ===\n\n";

// Get error summary by table and message
$errors = DB::table('import_job_errors')
    ->where('import_job_id', 3)
    ->select('table_name', 'error_message', DB::raw('COUNT(*) as error_count'))
    ->groupBy('table_name', 'error_message')
    ->orderByDesc('error_count')
    ->get();

if ($errors->isEmpty()) {
    echo "No errors found.\n";
} else {
    foreach ($errors as $error) {
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "Tabla: " . $error->table_name . " | Errores: " . $error->error_count . "\n";
        echo "Mensaje: " . substr($error->error_message, 0, 200) . (strlen($error->error_message) > 200 ? "..." : "") . "\n";
    }
}

// Get total error count by table
echo "\n\n=== TOTALES POR TABLA ===\n\n";
$totals = DB::table('import_job_errors')
    ->where('import_job_id', 3)
    ->select('table_name', DB::raw('COUNT(*) as total'))
    ->groupBy('table_name')
    ->orderByDesc('total')
    ->get();

foreach ($totals as $total) {
    echo $total->table_name . ": " . $total->total . " errores\n";
}

// Get grand total
$grand_total = DB::table('import_job_errors')
    ->where('import_job_id', 3)
    ->count();
    
echo "\nTOTAL: " . $grand_total . " errores en job 3\n\n";
