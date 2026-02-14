<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddCompanyIdToCriticalTables extends Migration
{
    public function up()
    {
        $tables = [
            'products',
            'product_sales',
            'product_purchases',
            'product_returns',
            'product_transfer',
            'product_adjustments',
            'purchase_product_return',
            'sales',
            'purchases',
            'returns',
            'transfers',
            'adjustments',
            'return_purchases',
            'customers',
            'suppliers',
            'payments',
            'expenses',
            'quotations',
            'reservations',
            'printers',
            'employees',
            'warehouses'
        ];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            if (!Schema::hasColumn($table, 'company_id')) {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    $t->unsignedBigInteger('company_id')->nullable()->after('id')->index();
                });

                // Add foreign key if companies table exists
                if (Schema::hasTable('companies')) {
                    try {
                        Schema::table($table, function (Blueprint $t) {
                            $t->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
                        });
                    } catch (\Exception $e) {
                        // ignore FK errors (MySQL strict modes, existing data, etc.)
                    }
                }
            }
        }
    }

    public function down()
    {
        $tables = [
            'products',
            'product_sales',
            'product_purchases',
            'product_returns',
            'product_transfer',
            'product_adjustments',
            'purchase_product_return',
            'sales',
            'purchases',
            'returns',
            'transfers',
            'adjustments',
            'return_purchases',
            'customers',
            'suppliers',
            'payments',
            'expenses',
            'quotations',
            'reservations',
            'printers',
            'employees',
            'warehouses'
        ];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            if (Schema::hasColumn($table, 'company_id')) {
                try {
                    Schema::table($table, function (Blueprint $t) {
                        $t->dropForeign([$t->getTable()."_company_id_foreign"] ?? []);
                    });
                } catch (\Exception $e) {
                    // ignore
                }

                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('company_id');
                });
            }
        }
    }
}
