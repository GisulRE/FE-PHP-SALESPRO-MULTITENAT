<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAutoFillAmountToPosSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('pos_setting') && !Schema::hasColumn('pos_setting', 'auto_fill_amount')) {
            Schema::table('pos_setting', function (Blueprint $table) {
                $table->tinyInteger('auto_fill_amount')->default(1);
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
        if (Schema::hasTable('pos_setting') && Schema::hasColumn('pos_setting', 'auto_fill_amount')) {
            Schema::table('pos_setting', function (Blueprint $table) {
                $table->dropColumn('auto_fill_amount');
            });
        }
    }
}
