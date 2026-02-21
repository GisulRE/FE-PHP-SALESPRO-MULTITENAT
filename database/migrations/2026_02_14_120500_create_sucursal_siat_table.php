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
            $table->string('codigo',100)->nullable();
            $table->string('nombre',200)->nullable();
            $table->string('direccion',255)->nullable();
            $table->string('telefono',100)->nullable();
            $table->string('ciudad',100)->nullable();
            $table->tinyInteger('estado')->default(1);
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sucursal_siat');
    }
}
