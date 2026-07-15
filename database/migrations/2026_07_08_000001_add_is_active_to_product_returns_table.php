<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsActiveToProductReturnsTable extends Migration
{
    public function up()
    {
        Schema::table('product_returns', function (Blueprint $table) {
            if (!Schema::hasColumn('product_returns', 'is_active')) {
                $table->boolean('is_active')->default(1);
            }
        });
    }

    public function down()
    {
        Schema::table('product_returns', function (Blueprint $table) {
            if (Schema::hasColumn('product_returns', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
}
