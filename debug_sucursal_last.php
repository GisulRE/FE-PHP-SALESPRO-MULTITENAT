<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

$last = DB::table('sucursal_siat')->orderBy('id','desc')->limit(1)->first();
print_r($last);
