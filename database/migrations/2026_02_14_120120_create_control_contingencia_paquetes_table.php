<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateControlContingenciaPaquetesTable extends Migration
{
    public function up()
    {
        Schema::create('control_contingencia_paquetes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('control_contingencia_id');
            $table->unsignedInteger('cantidad_ventas')->nullable();
            $table->timestamp('fecha_de_envio')->nullable();
            $table->string('glosa_nro_factura_inicio_a_fin')->nullable();
            $table->text('arreglo_ventas')->nullable();
            $table->string('codigo_recepcion')->nullable();
            $table->string('respuesta_servicio')->nullable();
            $table->mediumText('log_errores')->nullable();
            $table->string('estado')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('control_contingencia_paquetes');
    }
}
