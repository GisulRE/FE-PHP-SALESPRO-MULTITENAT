<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTaxNoToCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('customers', 'tax_no')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('tax_no')->nullable()->after('phone_number');
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
        if (Schema::hasColumn('customers', 'tax_no')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('tax_no');
            });
        }
    }
}
