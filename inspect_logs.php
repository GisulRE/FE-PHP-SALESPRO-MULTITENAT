<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$sample = \Illuminate\Support\Facades\DB::table('import_job_logs')
    ->where('import_job_id', 3)
    ->first();

echo "=== ESTRUCTURA DE import_job_logs ===\n\n";
print_r((array)$sample);

echo "\n=== PRIMEROS 10 ERRORES ===\n\n";

$errors = \Illuminate\Support\Facades\DB::table('import_job_logs')
    ->where('import_job_id', 3)
    ->where('level', 'error')
    ->orderByDesc('created_at')
    ->limit(10)
    ->get();

foreach ($errors as $err) {
    echo var_export((array)$err, true) . "\n";
    echo "---\n";
}
