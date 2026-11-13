<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCompanyIdToLoteSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('lote_sales') && !Schema::hasColumn('lote_sales', 'company_id')) {
            Schema::table('lote_sales', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });

            $driver = DB::getDriverName();
            if (Schema::hasTable('sales')) {
                if ($driver === 'pgsql') {
                    DB::statement("UPDATE lote_sales ls SET company_id = s.company_id FROM sales s WHERE ls.sale_id = s.id AND ls.company_id IS NULL");
                } else {
                    DB::statement("UPDATE lote_sales ls INNER JOIN sales s ON s.id = ls.sale_id SET ls.company_id = s.company_id WHERE ls.company_id IS NULL");
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
        if (Schema::hasTable('lote_sales') && Schema::hasColumn('lote_sales', 'company_id')) {
            Schema::table('lote_sales', function (Blueprint $table) {
                $table->dropColumn('company_id');
            });
        }
    }
}
