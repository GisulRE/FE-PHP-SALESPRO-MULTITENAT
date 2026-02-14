<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiatLeyendasFacturasTable extends Migration
{
    public function up()
    {
        Schema::create('siat_leyendas_facturas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('codigo',100)->nullable();
            $table->string('texto',500)->nullable();
            $table->tinyInteger('orden')->default(0);
            $table->tinyInteger('activo')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('siat_leyendas_facturas');
    }
}
