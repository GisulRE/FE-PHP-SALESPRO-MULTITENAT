<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingFieldsToGeneralSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('general_settings', 'currency')) {
                $table->string('currency')->default('USD')->after('site_logo');
            }
            if (!Schema::hasColumn('general_settings', 'staff_access')) {
                $table->string('staff_access')->default('all')->after('currency');
            }
            if (!Schema::hasColumn('general_settings', 'date_format')) {
                $table->string('date_format')->default('Y-m-d')->after('staff_access');
            }
            if (!Schema::hasColumn('general_settings', 'theme')) {
                $table->string('theme')->default('default.css')->after('date_format');
            }
            if (!Schema::hasColumn('general_settings', 'alert_expiration')) {
                $table->integer('alert_expiration')->default(30)->after('theme');
            }
            if (!Schema::hasColumn('general_settings', 'currency_position')) {
                $table->string('currency_position')->default('prefix')->after('updated_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn([
                'currency',
                'staff_access',
                'date_format',
                'theme',
                'alert_expiration',
                'currency_position'
            ]);
        });
    }
}
