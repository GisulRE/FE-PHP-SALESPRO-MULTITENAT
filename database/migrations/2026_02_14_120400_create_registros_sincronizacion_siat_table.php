<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegistrosSincronizacionSiatTable extends Migration
{
    public function up()
    {
        Schema::create('registros_sincronizacion_siat', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tabla',150)->nullable();
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->string('operacion',50)->nullable();
            $table->dateTime('fecha_envio')->nullable();
            $table->dateTime('fecha_respuesta')->nullable();
            $table->string('estado',100)->nullable();
            $table->text('mensaje')->nullable();
            $table->string('cufd',100)->nullable();
            $table->string('cuf',100)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('registros_sincronizacion_siat');
    }
}
