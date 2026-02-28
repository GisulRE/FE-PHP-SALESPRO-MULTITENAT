<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddCompanyIdToHrmSettingsAttendances extends Migration
{
    public function up()
    {
        $defaultCompanyId = DB::table('companies')->value('id');

        if (Schema::hasTable('hrm_settings') && !Schema::hasColumn('hrm_settings','company_id')) {
            Schema::table('hrm_settings', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
            });
            if (Schema::hasTable('companies')) {
                try {
                    Schema::table('hrm_settings', function (Blueprint $table) {
                        $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
                    });
                } catch (\Exception $e) {
                }
            }
            if ($defaultCompanyId) {
                DB::table('hrm_settings')->whereNull('company_id')->update(['company_id' => $defaultCompanyId]);
            }
        }

        if (Schema::hasTable('attendances') && !Schema::hasColumn('attendances','company_id')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
            });
            if (Schema::hasTable('companies')) {
                try {
                    Schema::table('attendances', function (Blueprint $table) {
                        $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
                    });
                } catch (\Exception $e) {
                }
            }
            if ($defaultCompanyId) {
                DB::table('attendances')->whereNull('company_id')->update(['company_id' => $defaultCompanyId]);
            }
        }
    }

    public function down()
    {
        if (Schema::hasColumn('hrm_settings','company_id')) {
            try { Schema::table('hrm_settings', function (Blueprint $table) { $table->dropForeign(['company_id']); }); } catch (\Exception $e) {}
            Schema::table('hrm_settings', function (Blueprint $table) { $table->dropColumn('company_id'); });
        }
        if (Schema::hasColumn('attendances','company_id')) {
            try { Schema::table('attendances', function (Blueprint $table) { $table->dropForeign(['company_id']); }); } catch (\Exception $e) {}
            Schema::table('attendances', function (Blueprint $table) { $table->dropColumn('company_id'); });
        }
    }
}
