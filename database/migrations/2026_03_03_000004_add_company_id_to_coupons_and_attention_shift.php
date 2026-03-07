<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddCompanyIdToCouponsAndAttentionShift extends Migration
{
    public function up()
    {
        if (Schema::hasTable('coupons') && !Schema::hasColumn('coupons', 'company_id')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
            });
            // Asignar company_id=1 a registros existentes
            DB::table('coupons')->whereNull('company_id')->update(['company_id' => 1]);
        }

        if (Schema::hasTable('attention_shift') && !Schema::hasColumn('attention_shift', 'company_id')) {
            Schema::table('attention_shift', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
            });
            // Asignar company_id=1 a registros existentes
            DB::table('attention_shift')->whereNull('company_id')->update(['company_id' => 1]);
        }
    }

    public function down()
    {
        if (Schema::hasColumn('coupons', 'company_id')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->dropColumn('company_id');
            });
        }
        if (Schema::hasColumn('attention_shift', 'company_id')) {
            Schema::table('attention_shift', function (Blueprint $table) {
                $table->dropColumn('company_id');
            });
        }
    }
}
