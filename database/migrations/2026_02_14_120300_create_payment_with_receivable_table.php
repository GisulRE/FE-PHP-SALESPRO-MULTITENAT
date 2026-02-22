<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentWithReceivableTable extends Migration
{
    public function up()
    {
        Schema::create('payment_with_receivable', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('account_id');
            $table->integer('user_id');
            $table->double('amount');
            $table->text('sales');
            $table->integer('status')->default(1);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->useCurrent();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_with_receivable');
    }
}
