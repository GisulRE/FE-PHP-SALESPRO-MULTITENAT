<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddCompanyIdToBillersTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('billers') && !Schema::hasColumn('billers', 'company_id')) {
            Schema::table('billers', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
            });

            if (Schema::hasTable('companies')) {
                try {
                    Schema::table('billers', function (Blueprint $table) {
                        $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
                    });
                } catch (\Exception $e) {
                    // ignorar errores de FK
                }
            }

            $defaultCompanyId = DB::table('companies')->value('id');
            if ($defaultCompanyId) {
                DB::table('billers')->whereNull('company_id')->update(['company_id' => $defaultCompanyId]);
            }
        }
    }

    public function down()
    {
        if (Schema::hasColumn('billers', 'company_id')) {
            try {
                Schema::table('billers', function (Blueprint $table) {
                    $table->dropForeign(['company_id']);
                });
            } catch (\Exception $e) {
            }
            Schema::table('billers', function (Blueprint $table) {
                $table->dropColumn('company_id');
            });
        }
    }
}
