<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuotationIdToSalesTable extends Migration
{
    /**
     * Run the migrations.
     * Agrega la columna quotation_id a la tabla sales para
     * linkear una venta registrada con su proforma de origen.
     */
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'quotation_id')) {
                $table->unsignedBigInteger('quotation_id')->nullable()->after('presale_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'quotation_id')) {
                $table->dropColumn('quotation_id');
            }
        });
    }
}
