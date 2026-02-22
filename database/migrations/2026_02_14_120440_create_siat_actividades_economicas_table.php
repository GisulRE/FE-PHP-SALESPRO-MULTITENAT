<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiatActividadesEconomicasTable extends Migration
{
    public function up()
    {
        Schema::create('siat_actividades_economicas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('codigo_caeb', 100);
            $table->string('descripcion', 200)->nullable();
            $table->string('tipo_actividad', 10);
            $table->timestamps('created_at')->nullable();
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
        Schema::dropIfExists('siat_actividades_economicas');
    }
}
