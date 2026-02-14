<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiatDocumentoSectorTable extends Migration
{
    public function up()
    {
        Schema::create('siat_documento_sector', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('codigo',50)->nullable();
            $table->string('descripcion',255)->nullable();
            $table->string('tipo_documento',100)->nullable();
            $table->tinyInteger('activo')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('siat_documento_sector');
    }
}
