<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddCompanyIdToUsers extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('users', 'company_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
            });

            // Add foreign key if companies table exists
            if (Schema::hasTable('companies')) {
                try {
                    Schema::table('users', function (Blueprint $table) {
                        $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
                    });

                    // Set default company for existing users
                    $defaultCompanyId = DB::table('companies')->first()->id ?? null;
                    if ($defaultCompanyId) {
                        DB::table('users')->whereNull('company_id')->update(['company_id' => $defaultCompanyId]);
                    }
                } catch (\Exception $e) {
                    // ignore FK errors
                }
            }
        }
    }

    public function down()
    {
        if (Schema::hasColumn('users', 'company_id')) {
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropForeign(['company_id']);
                });
            } catch (\Exception $e) {
                // ignore
            }

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('company_id');
            });
        }
    }
}
