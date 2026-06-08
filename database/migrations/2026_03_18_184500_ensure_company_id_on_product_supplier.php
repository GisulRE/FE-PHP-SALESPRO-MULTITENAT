<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnsureCompanyIdOnProductSupplier extends Migration
{
    public function up()
    {
        $driver = DB::getDriverName();

        foreach (['product_supplier', 'product_suppliers'] as $table) {
            if (!Schema::hasTable($table) || Schema::hasColumn($table, 'company_id')) {
                continue;
            }

            $columns = Schema::getColumnListing($table);
            $afterColumn = Schema::hasColumn($table, 'id') ? 'id' : (!empty($columns) ? $columns[0] : null);

            Schema::table($table, function (Blueprint $t) use ($afterColumn) {
                $column = $t->unsignedBigInteger('company_id')->nullable()->index();
                if ($afterColumn !== null) {
                    $column->after($afterColumn);
                }
            });

            if (
                Schema::hasColumn($table, 'supplier_id')
                && Schema::hasTable('suppliers')
                && Schema::hasColumn('suppliers', 'company_id')
            ) {
                if ($driver === 'pgsql') {
                    DB::statement("UPDATE {$table} ps SET company_id = s.company_id FROM suppliers s WHERE s.id = ps.supplier_id AND ps.company_id IS NULL AND s.company_id IS NOT NULL");
                } else {
                    DB::statement("UPDATE {$table} ps INNER JOIN suppliers s ON s.id = ps.supplier_id SET ps.company_id = s.company_id WHERE ps.company_id IS NULL AND s.company_id IS NOT NULL");
                }
            }

            $defaultCompanyId = Schema::hasTable('companies')
                ? DB::table('companies')->orderBy('id')->value('id')
                : null;

            if ($defaultCompanyId !== null) {
                DB::table($table)->whereNull('company_id')->update(['company_id' => $defaultCompanyId]);
            }

            if (Schema::hasTable('companies')) {
                try {
                    Schema::table($table, function (Blueprint $t) {
                        $t->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
                    });
                } catch (\Exception $e) {
                    // Ignorar si no es posible crear FK con datos legacy.
                }
            }
        }
    }

    public function down()
    {
        foreach (['product_supplier', 'product_suppliers'] as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'company_id')) {
                continue;
            }

            try {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropForeign(['company_id']);
                });
            } catch (\Exception $e) {
                // Ignorar si no existe FK.
            }

            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn('company_id');
            });
        }
    }
}
