<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductPreSaleTable extends Migration
{
    public function up()
    {
        Schema::create('product_pre_sale', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('presale_id');
            $table->integer('product_id');
            $table->integer('category_id');
            $table->integer('variant_id')->nullable();
            $table->integer('employee_id')->nullable();
            $table->double('qty');
            $table->integer('sale_unit_id');
            $table->double('net_unit_price');
            $table->double('discount')->default(0);
            $table->double('tax_rate');
            $table->double('tax');
            $table->double('total');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_pre_sale');
    }
}
