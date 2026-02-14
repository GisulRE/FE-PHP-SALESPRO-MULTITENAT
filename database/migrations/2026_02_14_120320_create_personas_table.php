<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonasTable extends Migration
{
    public function up()
    {
        Schema::create('personas', function (Blueprint $table) {
            $table->increments('id_persona');
            $table->string('nombres', 100);
            $table->string('apellido_paterno', 100);
            $table->string('apellido_materno', 100);
            $table->string('ci', 10);
            $table->date('fecha_nac');
            $table->string('tel_cel', 10);
            $table->string('direccion', 250);
            $table->string('zona', 200);
            $table->string('contextura', 200);
            $table->string('estatura', 10);
            $table->string('nro_seguro', 20);
            $table->string('seg_vida', 20);
            $table->integer('id_garante1');
            $table->integer('id_garante2');
            $table->string('foto', 250)->nullable();
            $table->string('estado_civil', 50);
            $table->string('afp', 100);
            $table->tinyInteger('estado');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('personas');
    }
}
