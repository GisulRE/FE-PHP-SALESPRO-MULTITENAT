<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddSoftDeletesToAllTables extends Migration
{
    public function up()
    {
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            $tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_type = 'BASE TABLE'");
        } else {
            $tables = DB::select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE='BASE TABLE'");
        }

        foreach ($tables as $t) {
            $name = $t->TABLE_NAME ?? $t->table_name ?? null;
            if (!$name) {
                continue;
            }
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
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            $tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_type = 'BASE TABLE'");
        } else {
            $tables = DB::select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE='BASE TABLE'");
        }

        foreach ($tables as $t) {
            $name = $t->TABLE_NAME ?? $t->table_name ?? null;
            if (!$name) {
                continue;
            }
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
