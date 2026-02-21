<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // taxes
        if (Schema::hasTable('taxes') && !Schema::hasColumn('taxes', 'company_id')) {
            Schema::table('taxes', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
            });
        }

        // units
        if (Schema::hasTable('units') && !Schema::hasColumn('units', 'company_id')) {
            Schema::table('units', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
            });
        }

        // pos_setting
        if (Schema::hasTable('pos_setting') && !Schema::hasColumn('pos_setting', 'company_id')) {
            Schema::table('pos_setting', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
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
        if (Schema::hasTable('taxes') && Schema::hasColumn('taxes', 'company_id')) {
            Schema::table('taxes', function (Blueprint $table) {
                $table->dropColumn('company_id');
            });
        }

        if (Schema::hasTable('units') && Schema::hasColumn('units', 'company_id')) {
            Schema::table('units', function (Blueprint $table) {
                $table->dropColumn('company_id');
            });
        }

        if (Schema::hasTable('pos_setting') && Schema::hasColumn('pos_setting', 'company_id')) {
            Schema::table('pos_setting', function (Blueprint $table) {
                $table->dropColumn('company_id');
            });
        }
    }
};
