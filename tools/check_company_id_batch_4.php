<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tables = [
    'product_transfer',
    'product_variants',
    'product_warehouse',
    'products',
    'puntos_venta',
    'purchase_product_return',
    'purchases',
    'quotations',
    'registros_sincronizacion_siat',
    'reservations',
    'return_purchases',
    'returns',
];

foreach ($tables as $table) {
    $hasTable = Illuminate\Support\Facades\Schema::hasTable($table) ? 'YES' : 'NO';
    $hasCompanyId = (
        Illuminate\Support\Facades\Schema::hasTable($table)
        && Illuminate\Support\Facades\Schema::hasColumn($table, 'company_id')
    ) ? 'YES' : 'NO';

    echo $table . ' table=' . $hasTable . ' company_id=' . $hasCompanyId . PHP_EOL;
}
