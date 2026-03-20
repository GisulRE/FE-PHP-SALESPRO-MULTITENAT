<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCompanyIdToSecondBatchTables extends Migration
{
    public function up()
    {
        $tables = [
            'customer_groups',
            'customer_nit',
            'customer_sales',
            'deliveries',
            'deposits',
            'factura_masiva',
            'factura_masiva_paquetes',
            'garantes',
            'general_settings',
            'gift_card_recharges',
            'gift_cards',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            if (!Schema::hasColumn($table, 'company_id')) {
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
        }

        if (
            Schema::hasTable('customer_sales')
            && Schema::hasTable('sales')
            && Schema::hasColumn('customer_sales', 'company_id')
            && Schema::hasColumn('sales', 'company_id')
        ) {
            DB::statement('UPDATE customer_sales cs INNER JOIN sales s ON s.id = cs.sale_id SET cs.company_id = s.company_id WHERE cs.company_id IS NULL AND s.company_id IS NOT NULL');
        }

        if (
            Schema::hasTable('customer_sales')
            && Schema::hasTable('customers')
            && Schema::hasColumn('customer_sales', 'company_id')
            && Schema::hasColumn('customers', 'company_id')
        ) {
            DB::statement('UPDATE customer_sales cs INNER JOIN customers c ON c.id = cs.customer_id SET cs.company_id = c.company_id WHERE cs.company_id IS NULL AND c.company_id IS NOT NULL');
        }

        if (
            Schema::hasTable('deliveries')
            && Schema::hasTable('sales')
            && Schema::hasColumn('deliveries', 'company_id')
            && Schema::hasColumn('sales', 'company_id')
        ) {
            DB::statement('UPDATE deliveries d INNER JOIN sales s ON s.id = d.sale_id SET d.company_id = s.company_id WHERE d.company_id IS NULL AND s.company_id IS NOT NULL');
        }

        if (
            Schema::hasTable('deposits')
            && Schema::hasTable('customers')
            && Schema::hasColumn('deposits', 'company_id')
            && Schema::hasColumn('customers', 'company_id')
        ) {
            DB::statement('UPDATE deposits d INNER JOIN customers c ON c.id = d.customer_id SET d.company_id = c.company_id WHERE d.company_id IS NULL AND c.company_id IS NOT NULL');
        }

        if (
            Schema::hasTable('deposits')
            && Schema::hasTable('users')
            && Schema::hasColumn('deposits', 'company_id')
            && Schema::hasColumn('users', 'company_id')
        ) {
            DB::statement('UPDATE deposits d INNER JOIN users u ON u.id = d.user_id SET d.company_id = u.company_id WHERE d.company_id IS NULL AND u.company_id IS NOT NULL');
        }

        if (
            Schema::hasTable('factura_masiva')
            && Schema::hasTable('users')
            && Schema::hasColumn('factura_masiva', 'company_id')
            && Schema::hasColumn('users', 'company_id')
            && Schema::hasColumn('factura_masiva', 'created_by')
        ) {
            DB::statement('UPDATE factura_masiva fm INNER JOIN users u ON u.id = fm.created_by SET fm.company_id = u.company_id WHERE fm.company_id IS NULL AND u.company_id IS NOT NULL');
        }

        if (
            Schema::hasTable('factura_masiva_paquetes')
            && Schema::hasTable('factura_masiva')
            && Schema::hasColumn('factura_masiva_paquetes', 'company_id')
            && Schema::hasColumn('factura_masiva', 'company_id')
        ) {
            DB::statement('UPDATE factura_masiva_paquetes fmp INNER JOIN factura_masiva fm ON fm.id = fmp.factura_masiva_id SET fmp.company_id = fm.company_id WHERE fmp.company_id IS NULL AND fm.company_id IS NOT NULL');
        }

        if (
            Schema::hasTable('gift_cards')
            && Schema::hasTable('customers')
            && Schema::hasColumn('gift_cards', 'company_id')
            && Schema::hasColumn('customers', 'company_id')
        ) {
            DB::statement('UPDATE gift_cards gc INNER JOIN customers c ON c.id = gc.customer_id SET gc.company_id = c.company_id WHERE gc.company_id IS NULL AND c.company_id IS NOT NULL');
        }

        if (
            Schema::hasTable('gift_cards')
            && Schema::hasTable('users')
            && Schema::hasColumn('gift_cards', 'company_id')
            && Schema::hasColumn('users', 'company_id')
            && Schema::hasColumn('gift_cards', 'user_id')
        ) {
            DB::statement('UPDATE gift_cards gc INNER JOIN users u ON u.id = gc.user_id SET gc.company_id = u.company_id WHERE gc.company_id IS NULL AND u.company_id IS NOT NULL');
        }

        if (
            Schema::hasTable('gift_card_recharges')
            && Schema::hasTable('gift_cards')
            && Schema::hasColumn('gift_card_recharges', 'company_id')
            && Schema::hasColumn('gift_cards', 'company_id')
        ) {
            DB::statement('UPDATE gift_card_recharges gcr INNER JOIN gift_cards gc ON gc.id = gcr.gift_card_id SET gcr.company_id = gc.company_id WHERE gcr.company_id IS NULL AND gc.company_id IS NOT NULL');
        }

        $defaultCompanyId = null;
        if (Schema::hasTable('companies')) {
            $defaultCompanyId = DB::table('companies')->orderBy('id')->value('id');
        }

        if ($defaultCompanyId !== null) {
            foreach ($tables as $table) {
                if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'company_id')) {
                    continue;
                }

                DB::table($table)->whereNull('company_id')->update(['company_id' => $defaultCompanyId]);
            }
        }

        foreach ($tables as $table) {
            if (!Schema::hasTable($table) || !Schema::hasTable('companies') || !Schema::hasColumn($table, 'company_id')) {
                continue;
            }

            try {
                Schema::table($table, function (Blueprint $t) {
                    $t->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
                });
            } catch (\Exception $e) {
                // Ignorar errores de FK por datos legacy o constraints previos.
            }
        }
    }

    public function down()
    {
        $tables = [
            'customer_groups',
            'customer_nit',
            'customer_sales',
            'deliveries',
            'deposits',
            'factura_masiva',
            'factura_masiva_paquetes',
            'garantes',
            'general_settings',
            'gift_card_recharges',
            'gift_cards',
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
