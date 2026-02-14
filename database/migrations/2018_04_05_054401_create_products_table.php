<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('code');
            $table->string('type');
            $table->string('barcode_symbology');
            $table->integer('brand_id')->nullable();
            $table->integer('category_id');
            $table->integer('unit_id');
            $table->integer('purchase_unit_id');
            $table->integer('sale_unit_id');
            $table->string('cost');
            $table->string('price');
            $table->double('price_a')->default(0);
            $table->double('price_b')->default(0);
            $table->double('price_c')->default(0);
            $table->double('qty')->nullable();
            $table->double('alert_quantity')->nullable();
            $table->tinyInteger('promotion')->nullable();
            $table->string('promotion_price')->nullable();
            $table->string('file')->nullable();
            $table->date('starting_date')->nullable();
            $table->date('last_date')->nullable();
            $table->integer('tax_id')->nullable();
            $table->integer('tax_method')->nullable();
            $table->longText('image')->nullable();
            $table->tinyInteger('featured')->nullable();
            $table->text('product_details')->nullable();
            $table->text('product_invoice_details')->nullable();
            $table->boolean('is_variant')->nullable();
            $table->string('product_list')->nullable();
            $table->string('qty_list')->nullable();
            $table->string('price_list')->nullable();
            $table->boolean('is_pricelist')->default(false);
            $table->boolean('is_active')->nullable();
            $table->string('courtesy', 10)->default('FALSE');
            $table->string('permanent', 10)->default('TRUE');
            $table->date('starting_date_courtesy')->nullable();
            $table->date('ending_date_courtesy')->nullable();
            $table->double('courtesy_clearance_price')->default(0);
            $table->float('commission_percentage')->default(0);
            $table->string('codigo_actividad')->nullable();
            $table->string('codigo_producto_servicio')->nullable();
            $table->tinyInteger('is_basicservice')->default(0);
            $table->integer('account_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
