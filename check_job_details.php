<?php
// Check what tables exist related to import_jobs
require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n=== TABLAS DISPONIBLES CON 'import' ===\n\n";

// Check what database tables exist
$tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");

foreach ($tables as $table) {
    echo "- " . $table->table_name . "\n";
}

try {
    echo "\n=== REVISAR IMPORT_JOB 3 ===\n\n";

    // Get import job 3 info
    $job = DB::table('import_jobs')->where('id', 3)->first();

    if ($job) {
        echo "Job ID: " . $job->id . "\n";
        echo "Status: " . $job->status . "\n";
        echo "Total Tables: " . $job->total_tables . "\n";
        echo "Total Rows: " . $job->total_rows . "\n";
        echo "Processed Tables: " . $job->processed_tables . "\n";
        echo "Processed Rows: " . $job->processed_rows . "\n";
        echo "Failed Rows: " . $job->failed_rows . "\n";
    }

    // Check import_job_details
    echo "\n=== DETALLES POR TABLA DEL JOB ===\n\n";

    $details = DB::table('import_job_details')
        ->where('import_job_id', 3)
        ->orderByDesc('failed_rows')
        ->get();

    foreach ($details as $detail) {
        $status_badge = "[" . strtoupper($detail->status) . "]";
        echo "{$status_badge} {$detail->table_name}: {$detail->total_rows} rows | {$detail->processed_rows} processed | {$detail->failed_rows} FAILED\n";
    }
} catch (\Exception $e) {
    echo "\nError al consultar jobs: " . $e->getMessage() . "\n";
}
