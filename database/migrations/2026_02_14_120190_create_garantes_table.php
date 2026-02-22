<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGarantesTable extends Migration
{
    public function up()
    {
        Schema::create('garantes', function (Blueprint $table) {
            $table->increments('id_garante');
            $table->string('nombres', 100);
            $table->string('apellido_paterno', 100);
            $table->string('apellido_materno', 100)->nullable();
            $table->date('fecha_garantia');
            $table->string('ci', 20);
            $table->string('tel_cel', 50);
            $table->string('direccion', 250);
            $table->string('zona', 200);
            $table->string('lugar_trabajo', 200);
            $table->integer('telefono');
            $table->tinyInteger('estado');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('garantes');
    }
}
