<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecordTable extends Migration
{
    public function up()
    {
        Schema::create('record', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('transaction_id');
            $table->integer('warehouse_id')->nullable();
            $table->integer('product_id')->nullable();
            $table->string('reference_no', 50)->nullable();
            $table->smallInteger('transaction_type');
            $table->integer('product_qty_before')->nullable();
            $table->integer('product_qty_after')->nullable();
            $table->integer('warehouse_qty_before')->nullable();
            $table->integer('warehouse_qty_after')->nullable();
            $table->decimal('cb_cost', 10, 0)->default(0);
            $table->timestamp('action_taken_at')->nullable()->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('record');
    }
}
