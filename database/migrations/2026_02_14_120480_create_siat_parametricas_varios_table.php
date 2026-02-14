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
            $table->string('tipo',150)->nullable();
            $table->string('codigo',150)->nullable();
            $table->string('descripcion',255)->nullable();
            $table->text('datos')->nullable();
            $table->tinyInteger('activo')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('siat_parametricas_varios');
    }
}
