<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdjustmentAccountsTable extends Migration
{
    public function up()
    {
        Schema::create('adjustment_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('reference_no', 20);
            $table->integer('account_id');
            $table->string('note', 254);
            $table->double('amount')->default(0);
            $table->string('type_adjustment', 10);
            $table->tinyInteger('is_active')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('adjustment_accounts');
    }
}
