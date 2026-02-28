<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddCompanyIdToPayrollsHolidaysTable extends Migration
{
    public function up()
    {
        $defaultCompanyId = DB::table('companies')->value('id');

        foreach (['payrolls', 'holidays'] as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'company_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
                });

                if (Schema::hasTable('companies')) {
                    try {
                        Schema::table($tableName, function (Blueprint $table) {
                            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
                        });
                    } catch (\Exception $e) {
                        // ignorar errores de FK
                    }
                }

                if ($defaultCompanyId) {
                    DB::table($tableName)->whereNull('company_id')->update(['company_id' => $defaultCompanyId]);
                }
            }
        }
    }

    public function down()
    {
        foreach (['payrolls', 'holidays'] as $tableName) {
            if (Schema::hasColumn($tableName, 'company_id')) {
                try {
                    Schema::table($tableName, function (Blueprint $table) {
                        $table->dropForeign(['company_id']);
                    });
                } catch (\Exception $e) {
                    // ignorar
                }
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('company_id');
                });
            }
        }
    }
}
