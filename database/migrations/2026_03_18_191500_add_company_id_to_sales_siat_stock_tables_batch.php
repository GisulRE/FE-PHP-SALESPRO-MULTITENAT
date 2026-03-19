<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCompanyIdToSalesSiatStockTablesBatch extends Migration
{
    public function up()
    {
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
            if (!Schema::hasTable($table) || Schema::hasColumn($table, 'company_id')) {
                continue;
            }

            $afterColumn = null;
            if (Schema::hasColumn($table, 'id')) {
                $afterColumn = 'id';
            } else {
                $columns = Schema::getColumnListing($table);
                $afterColumn = !empty($columns) ? $columns[0] : null;
            }

            Schema::table($table, function (Blueprint $t) use ($afterColumn) {
                $column = $t->unsignedBigInteger('company_id')->nullable()->index();
                if ($afterColumn !== null) {
                    $column->after($afterColumn);
                }
            });
        }

        if (
            Schema::hasTable('sales_import_temp')
            && Schema::hasColumn('sales_import_temp', 'company_id')
            && Schema::hasColumn('sales_import_temp', 'user_id')
            && Schema::hasTable('users')
            && Schema::hasColumn('users', 'company_id')
        ) {
            DB::statement('UPDATE sales_import_temp sit INNER JOIN users u ON u.id = sit.user_id SET sit.company_id = u.company_id WHERE sit.company_id IS NULL AND u.company_id IS NOT NULL');
        }

        if (
            Schema::hasTable('shift_employee')
            && Schema::hasColumn('shift_employee', 'company_id')
            && Schema::hasColumn('shift_employee', 'employee_id')
            && Schema::hasTable('employees')
            && Schema::hasColumn('employees', 'company_id')
        ) {
            DB::statement('UPDATE shift_employee se INNER JOIN employees e ON e.id = se.employee_id SET se.company_id = e.company_id WHERE se.company_id IS NULL AND e.company_id IS NOT NULL');
        }

        $siatTables = [
            'siat_actividades_economicas',
            'siat_cufd',
            'siat_documento_sector',
            'siat_leyendas_facturas',
            'siat_parametricas_varios',
            'siat_producto_servicios',
            'sucursal_siat',
        ];

        foreach ($siatTables as $table) {
            if (
                Schema::hasTable($table)
                && Schema::hasColumn($table, 'company_id')
                && Schema::hasColumn($table, 'id_empresa')
                && Schema::hasTable('companies')
            ) {
                DB::statement("UPDATE {$table} t INNER JOIN companies c ON c.id = t.id_empresa SET t.company_id = c.id WHERE t.company_id IS NULL");
            }

            if (
                Schema::hasTable($table)
                && Schema::hasColumn($table, 'company_id')
                && Schema::hasColumn($table, 'usuario_alta')
                && Schema::hasTable('users')
                && Schema::hasColumn('users', 'company_id')
            ) {
                DB::statement("UPDATE {$table} t INNER JOIN users u ON u.id = t.usuario_alta SET t.company_id = u.company_id WHERE t.company_id IS NULL AND u.company_id IS NOT NULL");
            }
        }

        if (
            Schema::hasTable('stock_counts')
            && Schema::hasColumn('stock_counts', 'company_id')
            && Schema::hasColumn('stock_counts', 'warehouse_id')
            && Schema::hasTable('warehouses')
            && Schema::hasColumn('warehouses', 'company_id')
        ) {
            DB::statement('UPDATE stock_counts sc INNER JOIN warehouses w ON w.id = sc.warehouse_id SET sc.company_id = w.company_id WHERE sc.company_id IS NULL AND w.company_id IS NOT NULL');
        }

        if (
            Schema::hasTable('stock_counts')
            && Schema::hasColumn('stock_counts', 'company_id')
            && Schema::hasColumn('stock_counts', 'user_id')
            && Schema::hasTable('users')
            && Schema::hasColumn('users', 'company_id')
        ) {
            DB::statement('UPDATE stock_counts sc INNER JOIN users u ON u.id = sc.user_id SET sc.company_id = u.company_id WHERE sc.company_id IS NULL AND u.company_id IS NOT NULL');
        }

        $defaultCompanyId = null;
        if (Schema::hasTable('companies')) {
            $defaultCompanyId = DB::table('companies')->orderBy('id')->value('id');
        }

        if ($defaultCompanyId !== null) {
            foreach ($tables as $table) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, 'company_id')) {
                    DB::table($table)->whereNull('company_id')->update(['company_id' => $defaultCompanyId]);
                }
            }
        }

        foreach ($tables as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'company_id') || !Schema::hasTable('companies')) {
                continue;
            }

            try {
                Schema::table($table, function (Blueprint $t) {
                    $t->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
                });
            } catch (\Exception $e) {
                // Ignorar problemas de FK en datos legacy.
            }
        }
    }

    public function down()
    {
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
