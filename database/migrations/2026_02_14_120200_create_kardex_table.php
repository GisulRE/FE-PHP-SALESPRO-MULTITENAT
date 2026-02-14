<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKardexTable extends Migration
{
    public function up()
    {
        Schema::create('kardex', function (Blueprint $table) {
            $table->integer('transaction_id')->unsigned();
            $table->integer('product_id');
            $table->string('product', 191)->nullable();
            $table->string('transaction_type', 13)->nullable();
            $table->integer('warehouse_id')->unsigned()->nullable();
            $table->string('warehouse', 191)->nullable();
            $table->integer('warehouse_qty_before')->nullable();
            $table->integer('warehouse_qty_after')->nullable();
            $table->bigInteger('entrada')->nullable();
            $table->bigInteger('salida')->nullable();
            $table->double('qty')->nullable();
            $table->string('cost', 191)->nullable();
            $table->string('total_cost', 417)->nullable();
            $table->integer('from_to')->nullable();
            $table->timestamp('date')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('kardex');
    }
}
