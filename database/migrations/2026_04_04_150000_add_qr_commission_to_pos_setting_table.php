<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQrCommissionToPosSettingTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('pos_setting')) {
            return;
        }

        Schema::table('pos_setting', function (Blueprint $table) {
            if (!Schema::hasColumn('pos_setting', 'qr_commission')) {
                $table->decimal('qr_commission', 10, 2)->default(0);
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('pos_setting')) {
            return;
        }

        Schema::table('pos_setting', function (Blueprint $table) {
            if (Schema::hasColumn('pos_setting', 'qr_commission')) {
                $table->dropColumn('qr_commission');
            }
        });
    }
}
