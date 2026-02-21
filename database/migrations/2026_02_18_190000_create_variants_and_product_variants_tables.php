<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVariantsAndProductVariantsTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('variants')) {
            Schema::create('variants', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('product_variants')) {
            Schema::create('product_variants', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('product_id');
                $table->integer('variant_id');
                $table->integer('position');
                $table->string('item_code');
                $table->double('additional_price')->nullable();
                $table->double('qty');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('variants');
    }
}
