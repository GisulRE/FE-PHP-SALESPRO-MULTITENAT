<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tables = [
    'sales',
    'sales_import_temp',
    'shift_employee',
    'siat_actividades_economicas',
    'siat_cufd',
    'siat_documento_sector',
    'siat_leyendas_facturas',
    'siat_parametricas_varios',
    'siat_producto_servicios',
    'stock_counts',
    'sucursal_siat',
    'suppliers',
];

foreach ($tables as $table) {
    $hasTable = Illuminate\Support\Facades\Schema::hasTable($table) ? 'YES' : 'NO';
    $hasCompanyId = (
        Illuminate\Support\Facades\Schema::hasTable($table)
        && Illuminate\Support\Facades\Schema::hasColumn($table, 'company_id')
    ) ? 'YES' : 'NO';

    echo $table . ' table=' . $hasTable . ' company_id=' . $hasCompanyId . PHP_EOL;
}
