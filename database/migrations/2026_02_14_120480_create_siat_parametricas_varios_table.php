<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiatParametricasVariosTable extends Migration
{
    public function up()
    {
        Schema::create('siat_parametricas_varios', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('codigo_clasificador',150)->nullable();
            $table->string('descripcion',255)->nullable();
            $table->string('tipo_clasificador',200)->nullable();
            $table->text('datos')->nullable();
            $table->integer('usuario_alta')->nullable();
            $table->integer('usuario_modificacion')->nullable();
            $table->integer('id_empresa')->nullable();
            $table->tinyInteger('estado')->default(1);
            $table->string('sucursal', 100)->default(1);
            $table->string('codigo_punto_venta', 100)->default(1);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('siat_parametricas_varios');
    }
}
