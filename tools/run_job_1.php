<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Jobs\ProcessCompanyImportJob;
use Illuminate\Support\Facades\DB;

$job = DB::table('import_jobs')->where('id', 1)->first();
if (!$job) {
    echo "NOT_FOUND\n";
    exit(1);
}

ProcessCompanyImportJob::dispatch(1)->onQueue('imports');
echo "DISPATCHED_JOB_1\n";
