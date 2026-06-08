<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAttendancePinToEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('employees') && !Schema::hasColumn('employees', 'attendance_pin')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('attendance_pin')->nullable()->after('is_public')
                    ->comment('Hash bcrypt del código PIN para autorizar acciones de asistencia');
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
        if (Schema::hasTable('employees') && Schema::hasColumn('employees', 'attendance_pin')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('attendance_pin');
            });
        }
    }
}
