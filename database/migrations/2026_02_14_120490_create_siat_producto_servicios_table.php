<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiatProductoServiciosTable extends Migration
{
    public function up()
    {
        Schema::create('siat_producto_servicios', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('codigo',100)->nullable();
            $table->string('descripcion',255)->nullable();
            $table->string('unidad_medida',50)->nullable();
            $table->decimal('precio_unitario',20,6)->nullable();
            $table->tinyInteger('activo')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('siat_producto_servicios');
    }
}
