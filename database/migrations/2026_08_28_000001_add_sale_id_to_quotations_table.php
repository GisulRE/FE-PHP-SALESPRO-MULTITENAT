<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSaleIdToQuotationsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable("quotations") && !Schema::hasColumn("quotations", "sale_id")) {
            Schema::table("quotations", function (Blueprint $table) {
                $table->unsignedBigInteger("sale_id")->nullable()->after("quotation_status");
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable("quotations") && Schema::hasColumn("quotations", "sale_id")) {
            Schema::table("quotations", function (Blueprint $table) {
                $table->dropColumn("sale_id");
            });
        }
    }
}
