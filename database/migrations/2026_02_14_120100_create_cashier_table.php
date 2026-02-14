<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashierTable extends Migration
{
    public function up()
    {
        Schema::create('cashier', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('account_id');
            $table->string('note', 200)->nullable();
            $table->double('amount_start')->default(0);
            $table->double('amount_end')->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cashier');
    }
}
