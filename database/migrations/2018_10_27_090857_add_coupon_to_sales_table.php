<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCouponToSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('sales', 'coupon_id') || !Schema::hasColumn('sales', 'coupon_discount')) {
            Schema::table('sales', function (Blueprint $table) {
                if (!Schema::hasColumn('sales', 'coupon_id')) {
                    $table->integer('coupon_id')->after('order_discount')->nullable();
                }
                if (!Schema::hasColumn('sales', 'coupon_discount')) {
                    $table->double('coupon_discount')->after('coupon_id')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('sales', 'coupon_id') || Schema::hasColumn('sales', 'coupon_discount')) {
            Schema::table('sales', function (Blueprint $table) {
                if (Schema::hasColumn('sales', 'coupon_id')) {
                    $table->dropColumn('coupon_id');
                }
                if (Schema::hasColumn('sales', 'coupon_discount')) {
                    $table->dropColumn('coupon_discount');
                }
            });
        }
    }
}
