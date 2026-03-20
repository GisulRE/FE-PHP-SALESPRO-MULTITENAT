<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== REVISIÓN DE ESQUEMA pre_sale ===\n\n";

// Get column info for pre_sale
$columns = DB::select("SHOW FULL COLUMNS FROM pre_sale");

foreach ($columns as $col) {
    $nullable = ($col->Null === 'YES') ? 'NULLABLE' : 'NOT NULL';
    $default = $col->Default !== null ? " DEFAULT '{$col->Default}'" : '';
    echo "- {$col->Field} ({$col->Type}) {$nullable}{$default}\n";
}

echo "\n=== REVISIÓN DE ESQUEMA product_pre_sale ===\n\n";

$columns2 = DB::select("SHOW FULL COLUMNS FROM product_pre_sale");

foreach ($columns2 as $col) {
    $nullable = ($col->Null === 'YES') ? 'NULLABLE' : 'NOT NULL';
    $default = $col->Default !== null ? " DEFAULT '{$col->Default}'" : '';
    echo "- {$col->Field} ({$col->Type}) {$nullable}{$default}\n";
}

echo "\n=== DATOS DE EJEMPLO pre_sale (sin company_id fallidos) ===\n\n";

// Get a sample of failing rows
$sample = DB::table('pre_sale')
    ->whereNull('order_discount')
    ->select(['id', 'reference_no', 'order_discount', 'total_discount', 'grand_total', 'company_id'])
    ->limit(5)
    ->get();

if ($sample) {
    foreach ($sample as $row) {
        echo "ID: {$row->id}, Ref: {$row->reference_no}, order_discount: " . ($row->order_discount ?? 'NULL') . ", total_discount: {$row->total_discount}, has company_id: {$row->company_id}\n";
    }
}
