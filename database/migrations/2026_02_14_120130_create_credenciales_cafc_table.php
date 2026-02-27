<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCredencialesCafcTable extends Migration
{
    public function up()
    {
        Schema::create('credenciales_cafc', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('año')->nullable();
            $table->string('tipo_factura', 50)->nullable();
            $table->unsignedTinyInteger('codigo_documento_sector')->nullable();
            $table->string('codigo_cafc', 50)->nullable();
            $table->string('sucursal')->nullable();
            $table->string('codigo_punto_venta')->nullable();
            $table->timestamp('fecha_emision')->nullable();
            $table->timestamp('fecha_vigencia')->nullable();
            $table->tinyInteger('is_active')->notNull();
            $table->unsignedInteger('nro_min')->nullable();
            $table->unsignedInteger('nro_max')->nullable();
            $table->unsignedInteger('correlativo_factura')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('credenciales_cafc');
    }
}
