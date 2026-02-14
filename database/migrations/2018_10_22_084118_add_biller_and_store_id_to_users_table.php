<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBillerAndStoreIdToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('users', 'biller_id') || !Schema::hasColumn('users', 'warehouse_id')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'biller_id')) {
                    $table->integer('biller_id')->after('role_id')->nullable();
                }
                if (!Schema::hasColumn('users', 'warehouse_id')) {
                    $table->integer('warehouse_id')->after('biller_id')->nullable();
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
        if (Schema::hasColumn('users', 'biller_id') || Schema::hasColumn('users', 'warehouse_id')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'biller_id')) {
                    $table->dropColumn('biller_id');
                }
                if (Schema::hasColumn('users', 'warehouse_id')) {
                    $table->dropColumn('warehouse_id');
                }
            });
        }
    }
}
