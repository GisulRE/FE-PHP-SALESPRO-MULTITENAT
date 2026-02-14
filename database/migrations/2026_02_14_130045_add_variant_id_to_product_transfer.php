<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVariantIdToProductTransfer extends Migration
{
    public function up()
    {
        Schema::table('product_transfer', function (Blueprint $table) {
            if (!Schema::hasColumn('product_transfer', 'variant_id')) {
                $table->integer('variant_id')->nullable()->after('product_id');
            }
        });
    }

    public function down()
    {
        Schema::table('product_transfer', function (Blueprint $table) {
            if (Schema::hasColumn('product_transfer', 'variant_id')) {
                $table->dropColumn('variant_id');
            }
        });
    }
}
