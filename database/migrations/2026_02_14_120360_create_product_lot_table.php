<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductLotTable extends Migration
{
    public function up()
    {
        Schema::create('product_lot', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('purchase_id');
            $table->integer('idwarehouse');
            $table->integer('idproduct');
            $table->double('qty');
            $table->double('stock');
            $table->date('expiration')->nullable();
            $table->string('name', 100)->nullable();
            $table->integer('supplier');
            $table->date('fabrication_date');
            $table->string('status', 10);
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->dateTime('low_date')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_lot');
    }
}
