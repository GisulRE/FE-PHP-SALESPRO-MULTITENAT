<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerSalesTable extends Migration
{
    public function up()
    {
        Schema::create('customer_sales', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sale_id');
            $table->integer('customer_id');
            $table->string('codigofijo', 50)->nullable();
            $table->string('razon_social', 191)->nullable();
            $table->string('email', 191)->nullable();
            $table->unsignedTinyInteger('tipo_documento')->nullable();
            $table->string('valor_documento', 100)->nullable();
            $table->string('complemento_documento', 100)->nullable();
            $table->string('numero_tarjeta_credito_debito', 100)->nullable();
            $table->unsignedTinyInteger('tipo_caso_especial')->default(1);
            $table->unsignedSmallInteger('tipo_metodo_pago')->nullable();
            $table->unsignedBigInteger('nro_factura')->nullable();
            $table->string('codigo_recepcion', 191)->nullable();
            $table->string('cuf', 191)->nullable();
            $table->string('codigo_cufd', 100)->nullable();
            $table->mediumText('xml')->nullable();
            $table->string('sucursal', 100);
            $table->string('codigo_punto_venta', 100);
            $table->string('estado_factura', 50)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('usuario', 191)->nullable();
            $table->unsignedBigInteger('nro_factura_manual')->nullable();
            $table->timestamp('fecha_manual')->nullable();
            $table->unsignedTinyInteger('codigo_excepcion')->nullable();
            $table->unsignedTinyInteger('codigo_documento_sector')->nullable();
            $table->string('glosa_periodo_facturado', 128)->nullable();
            $table->string('categoria', 128)->nullable();
            $table->string('numero_medidor', 100)->nullable();
            $table->string('lectura_medidor_anterior', 512)->nullable();
            $table->string('lectura_medidor_actual', 512)->nullable();
            $table->unsignedInteger('gestion')->nullable();
            $table->string('mes', 25)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('zona', 100)->nullable();
            $table->string('domicilio_cliente', 500)->nullable();
            $table->double('consumo_periodo')->unsigned()->nullable();
            $table->unsignedInteger('beneficiario_ley_1886')->nullable();
            $table->double('monto_descuento_ley_1886')->unsigned()->nullable();
            $table->double('monto_descuento_tarifa_dignidad')->unsigned()->nullable();
            $table->double('tasa_aseo')->unsigned()->nullable();
            $table->double('tasa_alumbrado')->unsigned()->nullable();
            $table->double('otras_tasas')->unsigned()->nullable();
            $table->double('ajuste_no_sujeto_iva')->unsigned()->nullable();
            $table->string('detalle_ajuste_no_sujeto_iva', 200)->nullable();
            $table->double('ajuste_sujeto_iva')->unsigned()->nullable();
            $table->string('detalle_ajuste_sujeto_iva', 200)->nullable();
            $table->double('otros_pagos_no_sujeto_iva')->unsigned()->nullable();
            $table->string('detalle_otros_pagos_no_sujeto_iva', 200)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_sales');
    }
}
