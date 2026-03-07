<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddKeybordPresaleToPosSettingTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('pos_setting')) {
            return;
        }

        if (!Schema::hasColumn('pos_setting', 'keybord_presale')) {
            Schema::table('pos_setting', function (Blueprint $table) {
                $table->tinyInteger('keybord_presale')->default(0)->after('user_category');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('pos_setting', 'keybord_presale')) {
            Schema::table('pos_setting', function (Blueprint $table) {
                $table->dropColumn('keybord_presale');
            });
        }
    }
}
