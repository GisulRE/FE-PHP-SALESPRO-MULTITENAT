<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$jobId = isset($argv[1]) ? (int) $argv[1] : 0;
if ($jobId <= 0) {
    fwrite(STDERR, "Usage: php tools/requeue_import_job.php <job_id>\n");
    exit(1);
}

App\Jobs\ProcessCompanyImportJob::dispatch($jobId)->onQueue('imports');
echo "dispatched_job_id={$jobId}\n";
