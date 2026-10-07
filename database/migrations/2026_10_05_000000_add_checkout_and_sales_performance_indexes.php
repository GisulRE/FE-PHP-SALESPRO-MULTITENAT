<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddCheckoutAndSalesPerformanceIndexes extends Migration
{
    /**
     * Define the target indexes to manage.
     *
     * @var array
     */
    protected $indexes = [
        'sales' => [
            ['columns' => ['reference_no'], 'name' => 'sales_reference_no_index'],
            ['columns' => ['customer_id'], 'name' => 'sales_customer_id_index'],
            ['columns' => ['warehouse_id'], 'name' => 'sales_warehouse_id_index'],
            ['columns' => ['sale_status', 'date_sell'], 'name' => 'sales_sale_status_date_sell_index'],
            ['columns' => ['created_at'], 'name' => 'sales_created_at_index'],
        ],
        'product_purchases' => [
            ['columns' => ['product_id', 'status', 'id'], 'name' => 'product_purchases_product_id_status_id_index'],
        ],
        'product_sales' => [
            ['columns' => ['sale_id'], 'name' => 'product_sales_sale_id_index'],
            ['columns' => ['product_id'], 'name' => 'product_sales_product_id_index'],
        ],
        'product_lot' => [
            ['columns' => ['idproduct', 'idwarehouse', 'status'], 'name' => 'product_lot_idproduct_idwarehouse_status_index'],
        ],
        'payments' => [
            ['columns' => ['sale_id'], 'name' => 'payments_sale_id_index'],
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
