<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateControlContingenciaTable extends Migration
{
    public function up()
    {
        Schema::create('control_contingencia', function (Blueprint $table) {
            $table->increments('id');
            $table->string('cuis')->nullable();
            $table->string('sucursal')->nullable();
            $table->string('codigo_punto_venta')->nullable();
            $table->string('cufd_valido')->nullable();
            $table->string('tipo_factura', 50)->nullable();
            $table->string('codigo_documento_sector', 50)->nullable();
            $table->string('codigo_evento')->nullable();
            $table->string('descripcion')->nullable();
            $table->timestamp('fecha_inicio_evento')->nullable();
            $table->timestamp('fecha_fin_evento')->nullable();
            $table->string('cufd_evento')->nullable();
            $table->string('estado')->nullable();
            $table->string('codigo_registro_evento')->nullable();
            $table->unsignedInteger('usuario_modificacion')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedTinyInteger('cantidad_paquetes')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('control_contingencia');
    }
}
