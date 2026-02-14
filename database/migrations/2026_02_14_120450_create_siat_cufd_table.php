<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiatCufdTable extends Migration
{
    public function up()
    {
        Schema::create('siat_cufd', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('codigo_cufd',150)->nullable();
            $table->string('codigo_sucursal',50)->nullable();
            $table->string('codigo_punto_venta',50)->nullable();
            $table->dateTime('fecha_hora')->nullable();
            $table->string('tipo',50)->nullable();
            $table->string('estado',50)->nullable();
            $table->text('respuesta')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('siat_cufd');
    }
}
