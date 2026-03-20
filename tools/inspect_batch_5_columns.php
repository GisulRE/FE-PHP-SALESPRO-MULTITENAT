<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tables = [
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
];

foreach ($tables as $table) {
    if (!Illuminate\Support\Facades\Schema::hasTable($table)) {
        echo '== ' . $table . ' (MISSING TABLE)' . PHP_EOL;
        continue;
    }

    echo '== ' . $table . PHP_EOL;
    echo implode(',', Illuminate\Support\Facades\Schema::getColumnListing($table)) . PHP_EOL;
}
