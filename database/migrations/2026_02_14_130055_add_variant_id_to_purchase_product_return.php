<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVariantIdToPurchaseProductReturn extends Migration
{
    public function up()
    {
        Schema::table('purchase_product_return', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_product_return', 'variant_id')) {
                $table->integer('variant_id')->nullable()->after('product_id');
            }
        });
    }

    public function down()
    {
        Schema::table('purchase_product_return', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_product_return', 'variant_id')) {
                $table->dropColumn('variant_id');
            }
        });
    }
}
