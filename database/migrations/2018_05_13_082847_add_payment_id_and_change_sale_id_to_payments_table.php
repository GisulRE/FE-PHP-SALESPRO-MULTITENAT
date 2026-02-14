<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPaymentIdAndChangeSaleIdToPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'purchase_id')) {
                $table->integer('purchase_id')->nullable()->after('id');
            }
            if (Schema::hasColumn('payments', 'sale_id')) {
                try {
                    $table->integer('sale_id')->nullable()->change();
                } catch (\Exception $e) {
                    // some DB drivers may not support change() — ignore safely
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            //
        });
    }
}
