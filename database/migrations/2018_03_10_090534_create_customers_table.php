<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('customer_group_id');
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone_number');
            $table->string('address');
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->string('tax_no')->nullable();
            $table->double('deposit')->nullable();
            $table->double('expense')->nullable();
            $table->double('credit')->default(0);
            $table->boolean('is_credit')->default(false);
            $table->integer('price_type')->default(0);
            $table->boolean('is_tasadignidad')->default(false);
            $table->boolean('is_ley1886')->default(false);
            $table->double('porcentaje_tasadignidad')->default(0);
            $table->double('porcentaje_ley1886')->default(0);
            $table->string('codigofijo', 11)->nullable();
            $table->string('nro_medidor', 11)->nullable();
            $table->tinyInteger('tipo_documento')->unsigned()->nullable();
            $table->string('valor_documento', 100)->nullable();
            $table->string('complemento_documento', 100)->nullable();
            $table->string('razon_social')->nullable();
            $table->date('date_birh')->nullable();
            $table->integer('sucursal_id')->nullable();
            $table->boolean('is_active')->nullable();
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
        Schema::dropIfExists('customers');
    }
}
