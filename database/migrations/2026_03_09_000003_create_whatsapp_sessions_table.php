<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWhatsappSessionsTable extends Migration
{
    public function up()
    {
        Schema::create('whatsapp_sessions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id')->index();
            $table->string('session_name', 64);
            $table->boolean('is_active')->default(false)->comment('Sesión principal para mensajes salientes');
            $table->timestamps();
            $table->unique(['company_id', 'session_name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('whatsapp_sessions');
    }
}
