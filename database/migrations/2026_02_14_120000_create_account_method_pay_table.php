<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountMethodPayTable extends Migration
{
    public function up()
    {
        Schema::create('account_method_pay', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('account_id');
            $table->integer('methodpay_id');
            $table->tinyInteger('is_active')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('account_method_pay');
    }
}
