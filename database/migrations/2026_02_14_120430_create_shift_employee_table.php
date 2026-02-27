<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShiftEmployeeTable extends Migration
{
    public function up()
    {
        Schema::create('shift_employee', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('shift_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->date('date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('shift_employee');
    }
}
