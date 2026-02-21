<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCompanyIdToCategoriesAndBrands extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                if (!Schema::hasColumn('categories', 'company_id')) {
                    $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
                }
            });
        }

        if (Schema::hasTable('brands')) {
            Schema::table('brands', function (Blueprint $table) {
                if (!Schema::hasColumn('brands', 'company_id')) {
                    $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                if (Schema::hasColumn('categories', 'company_id')) {
                    $table->dropColumn('company_id');
                }
            });
        }

        if (Schema::hasTable('brands')) {
            Schema::table('brands', function (Blueprint $table) {
                if (Schema::hasColumn('brands', 'company_id')) {
                    $table->dropColumn('company_id');
                }
            });
        }
    }
}
