<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoteSalesTable extends Migration
{
    public function up()
    {
        Schema::create('lote_sales', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sale_id');
            $table->integer('lote_id');
            $table->double('qty');
            $table->string('data', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lote_sales');
    }
}
