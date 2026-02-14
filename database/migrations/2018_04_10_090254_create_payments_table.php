<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sale_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('purchase_id')->nullable();
            $table->integer('account_id')->nullable();
            $table->integer('employee_id')->nullable();
            $table->string('payment_reference');
            $table->double('amount');
            $table->double('change')->default(0);
            $table->string('paying_method');
            $table->text('payment_note')->nullable();
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
        Schema::dropIfExists('payments');
    }
}
