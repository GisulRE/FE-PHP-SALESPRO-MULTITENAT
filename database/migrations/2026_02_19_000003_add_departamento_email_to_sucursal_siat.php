<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('sucursal_siat')) {
            Schema::table('sucursal_siat', function (Blueprint $table) {
                if (!Schema::hasColumn('sucursal_siat', 'departamento')) {
                    $table->string('departamento', 100)->nullable();
                }
                if (!Schema::hasColumn('sucursal_siat', 'email')) {
                    $table->string('email', 100)->nullable();
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('sucursal_siat')) {
            Schema::table('sucursal_siat', function (Blueprint $table) {
                if (Schema::hasColumn('sucursal_siat', 'email')) {
                    $table->dropColumn('email');
                }
                if (Schema::hasColumn('sucursal_siat', 'departamento')) {
                    $table->dropColumn('departamento');
                }
            });
        }
    }
};
