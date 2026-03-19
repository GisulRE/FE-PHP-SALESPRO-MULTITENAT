<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tables = [
    'product_sales',
    'product_pre_sale',
    'units',
    'printers',
    'categories',
    'customers',
    'employee_reservation_schedules',
];

echo "=== EXISTENCIA DE TABLAS ===\n";
foreach ($tables as $t) {
    echo "{$t}: " . (Schema::hasTable($t) ? 'YES' : 'NO') . "\n";
}

echo "\n=== COLUMNAS CLAVE ===\n";
foreach (['product_sales', 'product_pre_sale', 'printers', 'customers'] as $t) {
    if (!Schema::hasTable($t)) {
        continue;
    }
    echo "\n[{$t}]\n";
    $cols = DB::select("SHOW COLUMNS FROM {$t}");
    foreach ($cols as $c) {
        if (in_array($c->Field, ['sale_unit_id', 'category_id', 'address', 'presale_id', 'product_id', 'sale_id'])) {
            echo "- {$c->Field} | Type={$c->Type} | Null={$c->Null} | Default=" . ($c->Default === null ? 'NULL' : $c->Default) . "\n";
        }
    }
}

echo "\n=== UNITS id=0 e IDs disponibles ===\n";
if (Schema::hasTable('units')) {
    $id0 = DB::table('units')->where('id', 0)->exists();
    echo "units.id=0 exists: " . ($id0 ? 'YES' : 'NO') . "\n";
    $sample = DB::table('units')->orderBy('id')->limit(10)->pluck('id')->toArray();
    echo "units sample ids: " . implode(',', $sample) . "\n";
}

echo "\n=== CATEGORIES ids referenciados por printers ===\n";
if (Schema::hasTable('categories') && Schema::hasTable('printers')) {
    $refs = DB::table('printers')->select('category_id')->distinct()->pluck('category_id')->toArray();
    echo "printers.category_id distinct: " . implode(',', $refs) . "\n";
    foreach ($refs as $cid) {
        if ($cid === null || $cid === '') {
            continue;
        }
        $exists = DB::table('categories')->where('id', $cid)->exists();
        echo "category {$cid} exists: " . ($exists ? 'YES' : 'NO') . "\n";
    }
}
