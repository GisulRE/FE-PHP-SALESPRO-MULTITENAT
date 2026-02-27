<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesImportTempTable extends Migration
{
    public function up()
    {
        Schema::create('sales_import_temp', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('raw_data')->nullable();
            $table->json('parsed_data')->nullable();
            $table->string('status',50)->default('pending');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sales_import_temp');
    }
}
