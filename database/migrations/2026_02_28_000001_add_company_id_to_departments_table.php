<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddCompanyIdToDepartmentsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('departments') && !Schema::hasColumn('departments', 'company_id')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
            });

            // FK opcional
            if (Schema::hasTable('companies')) {
                try {
                    Schema::table('departments', function (Blueprint $table) {
                        $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
                    });
                } catch (\Exception $e) {
                    // ignorar errores de FK
                }
            }

            // Asignar la empresa por defecto a los registros existentes
            $defaultCompanyId = DB::table('companies')->value('id');
            if ($defaultCompanyId) {
                DB::table('departments')->whereNull('company_id')->update(['company_id' => $defaultCompanyId]);
            }
        }
    }

    public function down()
    {
        if (Schema::hasColumn('departments', 'company_id')) {
            try {
                Schema::table('departments', function (Blueprint $table) {
                    $table->dropForeign(['company_id']);
                });
            } catch (\Exception $e) {
                // ignorar
            }

            Schema::table('departments', function (Blueprint $table) {
                $table->dropColumn('company_id');
            });
        }
    }
}
