<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateCompaniesAndSeedDefault extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('companies')) {
            Schema::create('companies', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name', 150);
                $table->timestamps();
            });
        }

        // Ensure at least one company exists and get its id
        $defaultId = DB::table('companies')->insertGetId([
            'name' => 'Default Company',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

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
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'company_id')) {
                continue;
            }

            try {
                DB::table($table)->whereNull('company_id')->update(['company_id' => $defaultId]);
            } catch (\Exception $e) {
                // ignore update errors
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
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'company_id')) {
                continue;
            }

            try {
                DB::table($table)->update(['company_id' => null]);
            } catch (\Exception $e) {
                // ignore
            }
        }

        Schema::dropIfExists('companies');
    }
}
