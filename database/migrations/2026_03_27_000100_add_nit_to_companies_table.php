<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNitToCompaniesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('companies')) {
            return;
        }

        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'nit')) {
                $table->string('nit', 30)->nullable()->after('name')->unique();
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('companies') || !Schema::hasColumn('companies', 'nit')) {
            return;
        }

        Schema::table('companies', function (Blueprint $table) {
            try {
                $table->dropUnique(['nit']);
            } catch (\Throwable $e) {
                // ignore drop index errors on legacy db states
            }
            $table->dropColumn('nit');
        });
    }
}
