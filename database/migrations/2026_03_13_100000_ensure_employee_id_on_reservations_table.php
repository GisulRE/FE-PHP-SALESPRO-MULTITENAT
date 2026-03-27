<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EnsureEmployeeIdOnReservationsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('reservations', 'employee_id')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->unsignedInteger('employee_id')->nullable()->after('sucursal_id');
                $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('reservations', 'employee_id')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->dropForeign(['employee_id']);
                $table->dropColumn('employee_id');
            });
        }
    }
}
