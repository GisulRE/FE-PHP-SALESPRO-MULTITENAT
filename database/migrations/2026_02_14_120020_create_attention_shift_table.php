<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttentionShiftTable extends Migration
{
    public function up()
    {
        Schema::create('attention_shift', function (Blueprint $table) {
            $table->increments('id');
            $table->string('reference_nro', 50);
            $table->integer('employee_id')->nullable();
            $table->integer('user_id');
            $table->string('customer_name', 50);
            $table->integer('customer_id')->nullable();
            $table->integer('status');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->useCurrent();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attention_shift');
    }
}
