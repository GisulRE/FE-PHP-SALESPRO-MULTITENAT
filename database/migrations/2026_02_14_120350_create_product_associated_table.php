<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductAssociatedTable extends Migration
{
    public function up()
    {
        Schema::create('product_associated', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_courtesy_id');
            $table->unsignedInteger('product_associated_id');
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_associated');
    }
}
