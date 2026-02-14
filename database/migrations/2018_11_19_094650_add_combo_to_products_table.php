<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddComboToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('products', 'product_list') || !Schema::hasColumn('products', 'qty_list') || !Schema::hasColumn('products', 'price_list')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'product_list')) {
                    $table->string('product_list')->after('featured')->nullable();
                }
                if (!Schema::hasColumn('products', 'qty_list')) {
                    $table->string('qty_list')->after('product_list')->nullable();
                }
                if (!Schema::hasColumn('products', 'price_list')) {
                    $table->string('price_list')->after('qty_list')->nullable();
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
        if (Schema::hasColumn('products', 'product_list') || Schema::hasColumn('products', 'qty_list') || Schema::hasColumn('products', 'price_list')) {
            Schema::table('products', function (Blueprint $table) {
                if (Schema::hasColumn('products', 'product_list')) {
                    $table->dropColumn('product_list');
                }
                if (Schema::hasColumn('products', 'qty_list')) {
                    $table->dropColumn('qty_list');
                }
                if (Schema::hasColumn('products', 'price_list')) {
                    $table->dropColumn('price_list');
                }
            });
        }
    }
}
