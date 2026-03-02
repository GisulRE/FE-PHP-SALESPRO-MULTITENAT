<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddValidDateToQuotationsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('quotations') && !Schema::hasColumn('quotations', 'valid_date')) {
            Schema::table('quotations', function (Blueprint $table) {
                $table->date('valid_date')->nullable()->after('quotation_status');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('quotations') && Schema::hasColumn('quotations', 'valid_date')) {
            Schema::table('quotations', function (Blueprint $table) {
                $table->dropColumn('valid_date');
            });
        }
    }
}
