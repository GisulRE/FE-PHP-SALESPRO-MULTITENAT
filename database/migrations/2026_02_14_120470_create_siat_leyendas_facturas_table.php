<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiatLeyendasFacturasTable extends Migration
{
    public function up()
    {
        Schema::create('siat_leyendas_facturas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('codigo_actividad', 100);
            $table->string('descripcion_leyenda', 500)->nullable();
            $table->timestamps();
            $table->unsignedInteger('usuario_alta');
            $table->unsignedInteger('usuario_modificacion');
            $table->unsignedInteger('id_empresa')->nullable();
            $table->string('sucursal', 50);
            $table->string('codigo_punto_venta', 100);
        });
    }

    public function down()
    {
        Schema::dropIfExists('siat_leyendas_facturas');
    }
}
