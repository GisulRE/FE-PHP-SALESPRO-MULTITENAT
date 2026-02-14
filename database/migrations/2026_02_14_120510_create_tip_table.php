<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTipTable extends Migration
{
    public function up()
    {
        Schema::create('tip', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre',150)->nullable();
            $table->decimal('porcentaje',8,2)->nullable();
            $table->text('descripcion')->nullable();
            $table->tinyInteger('activo')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tip');
    }
}
