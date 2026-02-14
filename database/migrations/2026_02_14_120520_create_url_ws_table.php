<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUrlWsTable extends Migration
{
    public function up()
    {
        Schema::create('url_ws', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre',150)->nullable();
            $table->string('url',500)->nullable();
            $table->string('tipo',100)->nullable();
            $table->text('descripcion')->nullable();
            $table->tinyInteger('activo')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('url_ws');
    }
}
