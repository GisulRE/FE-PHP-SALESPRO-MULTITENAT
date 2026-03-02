<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVariantIdToProductQuotation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_quotation', function (Blueprint $table) {
            if (!Schema::hasColumn('product_quotation', 'variant_id')) {
                $table->integer('variant_id')->nullable()->after('product_id');
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
        Schema::table('product_quotation', function (Blueprint $table) {
            if (Schema::hasColumn('product_quotation', 'variant_id')) {
                $table->dropColumn('variant_id');
            }
        });
    }
}
