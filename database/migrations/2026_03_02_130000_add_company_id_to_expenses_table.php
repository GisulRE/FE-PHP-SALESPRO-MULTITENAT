<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompanyIdToExpensesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('expenses', 'company_id')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
                if (Schema::hasTable('companies')) {
                    try {
                        $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
                    } catch (\Exception $e) {
                        // ignorar errores FK
                    }
                }
            });
        }
    }

    public function down()
    {
        Schema::table('expenses', function (Blueprint $table) {
            try {
                $table->dropForeign(['company_id']);
            } catch (\Exception $e) {}
            if (Schema::hasColumn('expenses', 'company_id')) {
                $table->dropColumn('company_id');
            }
        });
    }
}
