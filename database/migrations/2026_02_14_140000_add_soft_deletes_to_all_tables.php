<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddSoftDeletesToAllTables extends Migration
{
    public function up()
    {
        // Añade deleted_at a todas las tablas base del esquema actual, excepto migrations
        $tables = DB::select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE='BASE TABLE'");

        foreach ($tables as $t) {
            $name = $t->TABLE_NAME;
            if ($name === 'migrations') {
                continue;
            }

            if (!Schema::hasColumn($name, 'deleted_at')) {
                Schema::table($name, function (Blueprint $table) use ($name) {
                    $table->softDeletes();
                });
            }
        }
    }

    public function down()
    {
        // Elimina deleted_at de las tablas que la tengan
        $tables = DB::select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE='BASE TABLE'");

        foreach ($tables as $t) {
            $name = $t->TABLE_NAME;
            if ($name === 'migrations') {
                continue;
            }

            if (Schema::hasColumn($name, 'deleted_at')) {
                Schema::table($name, function (Blueprint $table) {
                    $table->dropColumn('deleted_at');
                });
            }
        }
    }
}
