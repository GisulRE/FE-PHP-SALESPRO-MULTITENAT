<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerNitTable extends Migration
{
    public function up()
    {
        Schema::create('customer_nit', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedTinyInteger('tipo_documento')->nullable();
            $table->string('valor_documento', 100)->nullable();
            $table->string('complemento_documento', 100)->nullable();
            $table->unsignedTinyInteger('tipo_caso_especial')->default(1);
            $table->string('razon_social', 191)->nullable();
            $table->string('email', 191)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_nit');
    }
}
