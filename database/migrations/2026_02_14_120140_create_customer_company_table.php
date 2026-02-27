<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerCompanyTable extends Migration
{
    public function up()
    {
        Schema::create('customer_company', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('customer_id');
            $table->string('fullname', 255);
            $table->string('company_name', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('telephone', 20)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('lat', 100)->nullable();
            $table->string('lon', 100)->nullable();
            $table->string('description', 255)->nullable();
            $table->string('url_custom', 255)->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->dateTime('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_company');
    }
}
