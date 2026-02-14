<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAccountIdToPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('payments', 'account_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->integer('account_id')->after('sale_id');
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
        if (Schema::hasColumn('payments', 'account_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropColumn('account_id');
            });
        }
    }
}
