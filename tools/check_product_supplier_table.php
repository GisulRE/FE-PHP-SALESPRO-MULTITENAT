<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach (['product_supplier', 'product_suppliers'] as $table) {
    $hasTable = Illuminate\Support\Facades\Schema::hasTable($table);
    echo $table . ' hasTable=' . ($hasTable ? 'YES' : 'NO') . PHP_EOL;
    if ($hasTable) {
        $hasCompany = Illuminate\Support\Facades\Schema::hasColumn($table, 'company_id');
        echo $table . ' company_id=' . ($hasCompany ? 'YES' : 'NO') . PHP_EOL;
        $cols = Illuminate\Support\Facades\Schema::getColumnListing($table);
        echo $table . ' columns=' . implode(',', $cols) . PHP_EOL;
    }
}
