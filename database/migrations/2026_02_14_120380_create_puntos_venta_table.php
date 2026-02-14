<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePuntosVentaTable extends Migration
{
    public function up()
    {
        Schema::create('puntos_venta', function (Blueprint $table) {
            $table->increments('id');
            $table->string('codigo_punto_venta',100);
            $table->string('nombre_punto_venta',200);
            $table->string('descripcion',200)->nullable();
            $table->string('tipo_punto_venta')->nullable();
            $table->string('codigo_cuis',100);
            $table->dateTime('fecha_vigencia_cuis');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedInteger('usuario_alta');
            $table->unsignedInteger('id_empresa')->nullable();
            $table->unsignedInteger('sucursal')->nullable();
            $table->unsignedBigInteger('correlativo_factura')->nullable();
            $table->unsignedBigInteger('correlativo_alquiler')->nullable();
            $table->unsignedBigInteger('correlativo_servicios_basicos')->nullable();
            $table->unsignedBigInteger('correlativo_nota_debcred')->nullable();
            $table->unsignedTinyInteger('modo_contingencia')->default(0);
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->string('nit_comisionista',50)->nullable();
            $table->string('numero_contrato',200)->nullable();
            $table->tinyInteger('is_siat')->default(1);
            $table->tinyInteger('is_active')->default(1);
        });
    }

    public function down()
    {
        Schema::dropIfExists('puntos_venta');
    }
}
