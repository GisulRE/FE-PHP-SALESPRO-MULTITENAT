<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('pos_setting', 'cufd_centralizado')) {
            Schema::table('pos_setting', function (Blueprint $table) {
                $table->boolean('cufd_centralizado')->default(false);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pos_setting', 'cufd_centralizado')) {
            Schema::table('pos_setting', function (Blueprint $table) {
                $table->dropColumn('cufd_centralizado');
            });
        }
    }
};
