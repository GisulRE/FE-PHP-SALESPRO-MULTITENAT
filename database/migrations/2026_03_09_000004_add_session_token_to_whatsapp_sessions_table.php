<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSessionTokenToWhatsappSessionsTable extends Migration
{
    public function up()
    {
        Schema::table('whatsapp_sessions', function (Blueprint $table) {
            $table->text('session_token')->nullable()->after('is_active')
                ->comment('JWT session token para envío de mensajes (API Key)');
        });
    }

    public function down()
    {
        Schema::table('whatsapp_sessions', function (Blueprint $table) {
            $table->dropColumn('session_token');
        });
    }
}
