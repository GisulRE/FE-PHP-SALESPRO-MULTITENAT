<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('image')->nullable();
            $table->string('company_name');
            $table->string('vat_number')->nullable();
            $table->string('email');
            $table->string('phone_number');
            $table->string('address');
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->integer('account_id');
            $table->integer('account_id_tarjeta')->nullable();
            $table->integer('account_id_cheque')->nullable();
            $table->integer('account_id_vale')->nullable();
            $table->integer('account_id_otros')->nullable();
            $table->integer('account_id_pagoposterior')->nullable();
            $table->integer('account_id_transferenciabancaria')->nullable();
            $table->integer('account_id_deposito')->nullable();
            $table->integer('account_id_swift')->nullable();
            $table->integer('account_id_giftcard')->nullable();
            $table->integer('account_id_qr')->nullable();
            $table->integer('account_id_receivable');
            $table->integer('warehouse_id');
            $table->integer('customer_id');
            $table->string('sucursal')->nullable();
            $table->string('punto_venta_siat')->nullable();
            $table->boolean('is_active')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('billers');
    }
}
