<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$importJobId = isset($argv[1]) ? (int) $argv[1] : 1;

$rows = DB::table('jobs')->orderBy('id')->get();
$deleted = 0;

foreach ($rows as $r) {
    $payload = json_decode($r->payload, true);
    if (!is_array($payload) || !isset($payload['data']['command'])) {
        continue;
    }

    $cmd = @unserialize($payload['data']['command']);
    if (!is_object($cmd) || get_class($cmd) !== 'App\\Jobs\\ProcessCompanyImportJob') {
        continue;
    }

    $foundId = null;
    foreach ((array) $cmd as $k => $v) {
        if (is_string($k) && strpos($k, 'importJobId') !== false) {
            $foundId = (int) $v;
            break;
        }
    }

    if ($foundId === $importJobId) {
        DB::table('jobs')->where('id', $r->id)->delete();
        $deleted++;
        echo "deleted jobs.id={$r->id} for importJobId={$importJobId}\n";
    }
}

echo "deleted_total={$deleted}\n";
