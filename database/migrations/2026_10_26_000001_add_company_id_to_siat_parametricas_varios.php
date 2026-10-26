<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCompanyIdToSiatParametricasVarios extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('siat_parametricas_varios') && !Schema::hasColumn('siat_parametricas_varios', 'company_id')) {
            Schema::table('siat_parametricas_varios', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index()->after('id');
            });

            // Si hay registros asociados a un id_empresa que coincida con companies.id, vincularlos
            $driver = DB::getDriverName();
            if (Schema::hasTable('companies')) {
                if ($driver === 'pgsql') {
                    DB::statement("UPDATE siat_parametricas_varios t SET company_id = c.id FROM companies c WHERE c.id = t.id_empresa AND t.company_id IS NULL");
                } else {
                    DB::statement("UPDATE siat_parametricas_varios t INNER JOIN companies c ON c.id = t.id_empresa SET t.company_id = c.id WHERE t.company_id IS NULL");
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('siat_parametricas_varios') && Schema::hasColumn('siat_parametricas_varios', 'company_id')) {
            Schema::table('siat_parametricas_varios', function (Blueprint $table) {
                $table->dropColumn('company_id');
            });
        }
    }
}
