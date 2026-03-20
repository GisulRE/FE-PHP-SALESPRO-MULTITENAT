<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::table('jobs')->orderBy('id')->get();
echo "jobs_count=" . $rows->count() . "\n";

foreach ($rows as $r) {
    $importJobId = null;
    $jobClass = null;

    $payload = json_decode($r->payload, true);
    if (is_array($payload)) {
        if (isset($payload['displayName'])) {
            $jobClass = $payload['displayName'];
        }

        if (isset($payload['data']['command'])) {
            try {
                $cmd = @unserialize($payload['data']['command']);
                if (is_object($cmd)) {
                    $jobClass = get_class($cmd);

                    $vars = (array) $cmd;
                    foreach ($vars as $k => $v) {
                        if (is_string($k) && strpos($k, 'importJobId') !== false) {
                            $importJobId = $v;
                            break;
                        }
                    }
                }
            } catch (\Throwable $e) {
                // ignore invalid payloads
            }
        }
    }

    echo "jobs.id={$r->id} queue={$r->queue} attempts={$r->attempts} reserved_at={$r->reserved_at} class=" . ($jobClass ?: 'N/A') . " importJobId=" . ($importJobId === null ? 'N/A' : $importJobId) . "\n";
}
