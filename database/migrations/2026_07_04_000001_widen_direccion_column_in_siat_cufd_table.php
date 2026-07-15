<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class WidenDireccionColumnInSiatCufdTable extends Migration
{
    public function up()
    {
        Schema::table('siat_cufd', function (Blueprint $table) {
            $table->text('direccion')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('siat_cufd', function (Blueprint $table) {
            $table->string('direccion', 255)->nullable()->change();
        });
    }
}
