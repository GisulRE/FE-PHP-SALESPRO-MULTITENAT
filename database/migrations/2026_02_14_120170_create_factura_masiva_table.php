<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacturaMasivaTable extends Migration
{
    public function up()
    {
        Schema::create('factura_masiva', function (Blueprint $table) {
            $table->increments('id');
            $table->string('glosa')->nullable();
            $table->string('cuis')->nullable();
            $table->string('sucursal')->nullable();
            $table->string('codigo_punto_venta')->nullable();
            $table->string('codigo_documento_sector')->nullable();
            $table->integer('created_by');
            $table->timestamp('fecha_inicio')->nullable();
            $table->timestamp('fecha_fin')->nullable();
            $table->string('tipo_factura', 50)->nullable();
            $table->string('estado')->nullable();
            $table->unsignedTinyInteger('cantidad_paquetes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('factura_masiva');
    }
}
