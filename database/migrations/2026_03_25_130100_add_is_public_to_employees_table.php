<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsPublicToEmployeesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('employees') && !Schema::hasColumn('employees', 'is_public')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->boolean('is_public')->default(false)->after('is_active');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('employees') && Schema::hasColumn('employees', 'is_public')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('is_public');
            });
        }
    }
}
