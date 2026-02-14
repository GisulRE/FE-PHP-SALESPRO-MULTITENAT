<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiatActividadesEconomicasTable extends Migration
{
    public function up()
    {
        Schema::create('siat_actividades_economicas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('codigo',50)->nullable();
            $table->string('descripcion',255)->nullable();
            $table->tinyInteger('vigente')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('siat_actividades_economicas');
    }
}
