<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWhatsappAuthTokensToPosSettingTable extends Migration
{
    public function up()
    {
        Schema::table('pos_setting', function (Blueprint $table) {
            if (!Schema::hasColumn('pos_setting', 'whatsapp_access_token')) {
                $table->text('whatsapp_access_token')->nullable()->after('whatsapp_session_last_started_at');
            }
            if (!Schema::hasColumn('pos_setting', 'whatsapp_refresh_token')) {
                $table->text('whatsapp_refresh_token')->nullable()->after('whatsapp_access_token');
            }
        });
    }

    public function down()
    {
        Schema::table('pos_setting', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_access_token', 'whatsapp_refresh_token']);
        });
    }
}
