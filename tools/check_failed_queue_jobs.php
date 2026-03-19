<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$failed = DB::table('failed_jobs')->orderByDesc('id')->limit(5)->get();
echo "failed_jobs_count=" . DB::table('failed_jobs')->count() . "\n";
foreach ($failed as $f) {
    echo "id={$f->id} queue={$f->queue} failed_at={$f->failed_at}\n";
    echo substr($f->exception, 0, 260) . "\n---\n";
}
