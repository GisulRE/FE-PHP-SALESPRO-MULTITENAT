<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$cols = DB::select("SHOW COLUMNS FROM sucursal_siat");
foreach($cols as $c) {
    echo $c->Field . "\n";
}
