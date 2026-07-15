<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCufCustomerSaleIdToReturnsTable extends Migration
{
    public function up()
    {
        Schema::table('returns', function (Blueprint $table) {
            if (!Schema::hasColumn('returns', 'cuf')) {
                $table->string('cuf', 191)->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('returns', 'customer_sale_id')) {
                $table->unsignedInteger('customer_sale_id')->nullable()->after('cuf');
            }
        });
    }

    public function down()
    {
        Schema::table('returns', function (Blueprint $table) {
            $table->dropColumn(['cuf', 'customer_sale_id']);
        });
    }
}
