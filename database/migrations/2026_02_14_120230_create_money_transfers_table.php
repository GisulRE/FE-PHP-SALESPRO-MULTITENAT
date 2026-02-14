<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoneyTransfersTable extends Migration
{
    public function up()
    {
        Schema::create('money_transfers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('reference_no', 191);
            $table->integer('from_account_id');
            $table->integer('to_account_id');
            $table->double('amount');
            $table->string('note', 250)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('money_transfers');
    }
}
