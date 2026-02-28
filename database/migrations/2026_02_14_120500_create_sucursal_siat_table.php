<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSucursalSiatTable extends Migration
{
    public function up()
    {
        Schema::create('sucursal_siat', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('sucursal', 50);
            $table->string('nombre', 100);
            $table->string('descripcion_sucursal', 200);
            $table->string('domicilio_tributario', 200);
            $table->string('ciudad_municipio', 200);
            $table->string('telefono', 100);
            $table->string('email', 100)->nullable();
            $table->unsignedInteger('id_autorizacion_facturacion')->nullable();
            $table->string('departamento', 100);
            $table->string('estado', 20);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedInteger('usuario_alta');
            $table->unsignedInteger('id_empresa')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sucursal_siat');
    }
}
