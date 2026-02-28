<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompanyIdToExpenseCategoriesAndAccounts extends Migration
{
    public function up()
    {
        foreach (['expense_categories', 'accounts'] as $table) {
            if (!Schema::hasColumn($table, 'company_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->unsignedBigInteger('company_id')->nullable()->after('id')->index();
                    if (Schema::hasTable('companies')) {
                        try {
                            $t->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
                        } catch (\Exception $e) {
                            // ignorar errores FK
                        }
                    }
                });
            }
        }
    }

    public function down()
    {
        foreach (['expense_categories', 'accounts'] as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                try {
                    $t->dropForeign([$table . '_company_id_foreign']);
                } catch (\Exception $e) {}
                if (Schema::hasColumn($table, 'company_id')) {
                    $t->dropColumn('company_id');
                }
            });
        }
    }
}
