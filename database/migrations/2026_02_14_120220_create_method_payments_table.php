<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMethodPaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('method_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50);
            $table->string('description', 200)->nullable();
            $table->tinyInteger('apply')->default(0);
            $table->tinyInteger('used')->default(0);
            $table->tinyInteger('cbx')->default(1);
            $table->string('codigo_clasificador_siat', 100)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('method_payments');
    }
}
