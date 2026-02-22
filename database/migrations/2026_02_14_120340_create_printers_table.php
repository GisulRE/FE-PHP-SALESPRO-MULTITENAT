<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrintersTable extends Migration
{
    public function up()
    {
        Schema::create('printers', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('company_id', 20);
            $table->string('name', 50);
            $table->string('printer', 200);
            $table->string('host_address', 50);
            $table->string('type', 50);
            $table->integer('category_id');
            $table->tinyInteger('status')->default(1);
            $table->dateTime('created_at');
            $table->timestamp('updated_at')->useCurrent();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('printers');
    }
}
