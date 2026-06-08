<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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

        $defaultCompany = DB::table('companies')
            ->where('name', 'Default Company')
            ->whereNull('nit')
            ->first();

        if ($defaultCompany && !DB::table('companies')->where('nit', '000000')->exists()) {
            DB::table('companies')
                ->where('id', $defaultCompany->id)
                ->update(['nit' => '000000']);
        }
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
