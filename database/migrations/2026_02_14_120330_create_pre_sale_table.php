<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePreSaleTable extends Migration
{
    public function up()
    {
        Schema::create('pre_sale', function (Blueprint $table) {
            $table->increments('id');
            $table->string('reference_no', 50);
            $table->integer('user_id');
            $table->integer('employee_id')->nullable();
            $table->integer('customer_id');
            $table->integer('warehouse_id');
            $table->integer('attentionshift_id')->nullable();
            $table->integer('item');
            $table->double('total_qty');
            $table->double('grand_total');
            $table->double('order_discount')->default(0);
            $table->double('total_discount')->nullable();
            $table->double('shipping_cost')->nullable();
            $table->double('tips')->default(0);
            $table->integer('status');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->useCurrent();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pre_sale');
    }
}
