<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCompanyIdToInventoryAndSiatTablesBatch extends Migration
{
    public function up()
    {
        $tables = [
            'product_variants',
            'product_warehouse',
            'puntos_venta',
            'registros_sincronizacion_siat',
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
            Schema::hasTable('product_variants')
            && Schema::hasColumn('product_variants', 'company_id')
            && Schema::hasColumn('product_variants', 'product_id')
            && Schema::hasTable('products')
            && Schema::hasColumn('products', 'company_id')
        ) {
            DB::statement('UPDATE product_variants pv INNER JOIN products p ON p.id = pv.product_id SET pv.company_id = p.company_id WHERE pv.company_id IS NULL AND p.company_id IS NOT NULL');
        }

        if (
            Schema::hasTable('product_warehouse')
            && Schema::hasColumn('product_warehouse', 'company_id')
            && Schema::hasColumn('product_warehouse', 'product_id')
            && Schema::hasTable('products')
            && Schema::hasColumn('products', 'company_id')
        ) {
            DB::statement('UPDATE product_warehouse pw INNER JOIN products p ON p.id = pw.product_id SET pw.company_id = p.company_id WHERE pw.company_id IS NULL AND p.company_id IS NOT NULL');
        }

        if (
            Schema::hasTable('puntos_venta')
            && Schema::hasColumn('puntos_venta', 'company_id')
            && Schema::hasColumn('puntos_venta', 'id_empresa')
            && Schema::hasTable('companies')
        ) {
            DB::statement('UPDATE puntos_venta pv INNER JOIN companies c ON c.id = pv.id_empresa SET pv.company_id = c.id WHERE pv.company_id IS NULL');
        }

        if (
            Schema::hasTable('puntos_venta')
            && Schema::hasColumn('puntos_venta', 'company_id')
            && Schema::hasColumn('puntos_venta', 'usuario_alta')
            && Schema::hasTable('users')
            && Schema::hasColumn('users', 'company_id')
        ) {
            DB::statement('UPDATE puntos_venta pv INNER JOIN users u ON u.id = pv.usuario_alta SET pv.company_id = u.company_id WHERE pv.company_id IS NULL AND u.company_id IS NOT NULL');
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
            'product_variants',
            'product_warehouse',
            'puntos_venta',
            'registros_sincronizacion_siat',
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
