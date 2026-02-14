<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutorizacionFacturacionTable extends Migration
{
    public function up()
    {
        Schema::create('autorizacion_facturacion', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('ambiente');
            $table->string('codigo_sistema', 255);
            $table->integer('estado')->default(1);
            $table->date('fecha_solicitud');
            $table->dateTime('fecha_vencimiento_token');
            $table->string('token', 1000);
            $table->unsignedInteger('tipo_modalidad');
            $table->unsignedInteger('tipo_sistema');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedInteger('usuario_alta');
            $table->unsignedInteger('usuario_modificacion');
            $table->unsignedInteger('id_empresa')->nullable();
            $table->unsignedInteger('id_url_produccion_obtencion_codigos')->nullable();
            $table->unsignedInteger('id_url_produccion_operaciones')->nullable();
            $table->unsignedInteger('id_url_produccion_recepcion_compras')->nullable();
            $table->unsignedInteger('id_url_produccion_sincronizacion_datos')->nullable();
            $table->unsignedInteger('id_url_pruebas_obtencion_codigos')->nullable();
            $table->unsignedInteger('id_url_pruebas_operaciones')->nullable();
            $table->unsignedInteger('id_url_pruebas_recepcion_compras')->nullable();
            $table->unsignedInteger('id_url_pruebas_sincronizacion_datos')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('autorizacion_facturacion');
    }
}
