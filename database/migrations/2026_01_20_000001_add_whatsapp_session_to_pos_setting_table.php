<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWhatsappSessionToPosSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pos_setting', function (Blueprint $table) {
            if (!Schema::hasColumn('pos_setting', 'whatsapp_session_id')) {
                $table->string('whatsapp_session_id')->nullable()->after('url_whatsapp');
            }
            if (!Schema::hasColumn('pos_setting', 'whatsapp_session_last_started_at')) {
                $table->timestamp('whatsapp_session_last_started_at')->nullable()->after('whatsapp_session_id');
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
        Schema::table('pos_setting', function (Blueprint $table) {
            if (Schema::hasColumn('pos_setting', 'whatsapp_session_last_started_at')) {
                $table->dropColumn('whatsapp_session_last_started_at');
            }
            if (Schema::hasColumn('pos_setting', 'whatsapp_session_id')) {
                $table->dropColumn('whatsapp_session_id');
            }
        });
    }
}
