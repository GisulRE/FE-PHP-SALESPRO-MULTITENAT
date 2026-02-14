<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserCategoryTable extends Migration
{
    public function up()
    {
        Schema::create('user_category', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name',150)->nullable();
            $table->text('description')->nullable();
            $table->json('permissions')->nullable();
            $table->tinyInteger('active')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_category');
    }
}
