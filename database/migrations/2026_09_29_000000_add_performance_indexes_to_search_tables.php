<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddPerformanceIndexesToSearchTables extends Migration
{
    /**
     * Define the target indexes to manage.
     *
     * @var array
     */
    protected $indexes = [
        'products' => [
            ['columns' => ['code', 'is_active'], 'name' => 'products_code_is_active_index'],
            ['columns' => ['name', 'is_active'], 'name' => 'products_name_is_active_index'],
            ['columns' => ['category_id', 'is_active'], 'name' => 'products_category_id_is_active_index'],
            ['columns' => ['type', 'is_active'], 'name' => 'products_type_is_active_index'],
        ],
        'product_warehouse' => [
            ['columns' => ['warehouse_id', 'product_id', 'qty'], 'name' => 'product_warehouse_warehouse_id_product_id_qty_index'],
            ['columns' => ['product_id', 'warehouse_id'], 'name' => 'product_warehouse_product_id_warehouse_id_index'],
        ],
        'product_variants' => [
            ['columns' => ['product_id', 'variant_id'], 'name' => 'product_variants_product_id_variant_id_index'],
            ['columns' => ['item_code'], 'name' => 'product_variants_item_code_index'],
        ],
        'customers' => [
            ['columns' => ['name', 'is_active'], 'name' => 'customers_name_is_active_index'],
            ['columns' => ['phone_number', 'is_active'], 'name' => 'customers_phone_number_is_active_index'],
            ['columns' => ['tax_no', 'is_active'], 'name' => 'customers_tax_no_is_active_index'],
            ['columns' => ['valor_documento', 'is_active'], 'name' => 'customers_valor_documento_is_active_index'],
        ],
        'customer_nit' => [
            ['columns' => ['tipo_documento', 'valor_documento'], 'name' => 'customer_nit_tipo_documento_valor_documento_index'],
        ],
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->indexes as $table => $definitions) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach ($definitions as $index) {
                // Ensure all columns exist before attempting to create the index
                if (!Schema::hasColumns($table, $index['columns'])) {
                    continue;
                }

                if (!$this->hasIndex($table, $index['name'])) {
                    Schema::table($table, function (Blueprint $blueprint) use ($index) {
                        $blueprint->index($index['columns'], $index['name']);
                    });
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->indexes as $table => $definitions) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach ($definitions as $index) {
                if ($this->hasIndex($table, $index['name'])) {
                    Schema::table($table, function (Blueprint $blueprint) use ($index) {
                        $blueprint->dropIndex($index['name']);
                    });
                }
            }
        }
    }

    /**
     * Check if an index exists on a table safely.
     *
     * @param string $table
     * @param string $indexName
     * @return bool
     */
    protected function hasIndex($table, $indexName)
    {
        try {
            $connection = Schema::getConnection();
            $driver = $connection->getDriverName();
            if ($driver === 'sqlite') {
                $indexes = DB::select("PRAGMA index_list('{$table}')");
                foreach ($indexes as $idx) {
                    if (strcasecmp($idx->name, $indexName) === 0) {
                        return true;
                    }
                }
                return false;
            }

            $dbName = $connection->getDatabaseName();
            $prefix = $connection->getTablePrefix();
            $fullTable = $prefix . $table;

            $result = DB::select(
                "SELECT 1 FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1",
                [$dbName, $fullTable, $indexName]
            );

            return !empty($result);
        } catch (\Throwable $e) {
            try {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes($table);
                return array_key_exists(strtolower($indexName), array_change_key_case($indexes, CASE_LOWER));
            } catch (\Throwable $ex) {
                return false;
            }
        }
    }
}
