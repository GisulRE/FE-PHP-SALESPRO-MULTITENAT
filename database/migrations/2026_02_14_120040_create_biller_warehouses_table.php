<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillerWarehousesTable extends Migration
{
    public function up()
    {
        Schema::create('biller_warehouses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('biller_id');
            $table->integer('warehouse_id');
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->useCurrent();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('biller_warehouses');
    }
}
