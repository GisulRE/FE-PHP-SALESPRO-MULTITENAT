<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiatDocumentoSectorTable extends Migration
{
    public function up()
    {
        Schema::create('siat_documento_sector', function (Blueprint $table) {
            $table->increments('id');
            $table->string('codigo_actividad', 100);
            $table->string('codigo_documento_sector', 100);
            $table->string('tipo_documento_sector', 10);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedInteger('usuario_alta');
            $table->unsignedInteger('usuario_modificacion');
            $table->unsignedInteger('id_empresa')->nullable();
            $table->string('sucursal', 50);
            $table->string('codigo_punto_venta', 100);
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('siat_documento_sector');
    }
}
