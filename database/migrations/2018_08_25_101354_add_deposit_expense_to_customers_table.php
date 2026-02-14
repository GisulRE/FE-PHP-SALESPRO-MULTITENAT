<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDepositExpenseToCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('customers', 'deposit') || !Schema::hasColumn('customers', 'expense')) {
            Schema::table('customers', function (Blueprint $table) {
                if (!Schema::hasColumn('customers', 'deposit')) {
                    $table->double('deposit')->nullable();
                }
                if (!Schema::hasColumn('customers', 'expense')) {
                    $table->double('expense')->nullable();
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
        if (Schema::hasColumn('customers', 'deposit') || Schema::hasColumn('customers', 'expense')) {
            Schema::table('customers', function (Blueprint $table) {
                if (Schema::hasColumn('customers', 'deposit')) {
                    $table->dropColumn('deposit');
                }
                if (Schema::hasColumn('customers', 'expense')) {
                    $table->dropColumn('expense');
                }
            });
        }
    }
}
