<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiatCufdTable extends Migration
{
    public function up()
    {
        Schema::create('siat_cufd', function (Blueprint $table) {
            $table->increments('id');
            $table->string('codigo_cufd',150)->nullable();
            $table->string('codigo_control',150)->nullable();
            $table->string('direccion',255)->nullable();
            $table->dateTime('fecha_registro')->nullable();
            $table->dateTime('fecha_vigencia')->nullable();
            $table->string('sucursal',50)->nullable();
            $table->string('codigo_punto_venta',100)->nullable();
            $table->tinyInteger('estado')->default(1);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedInteger('usuario_alta')->nullable();
            $table->unsignedInteger('id_empresa')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('siat_cufd');
    }
}
