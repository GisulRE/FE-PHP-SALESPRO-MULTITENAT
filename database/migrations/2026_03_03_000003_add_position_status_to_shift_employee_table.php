<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPositionStatusToShiftEmployeeTable extends Migration
{
    public function up()
    {
        Schema::table('shift_employee', function (Blueprint $table) {
            if (!Schema::hasColumn('shift_employee', 'position')) {
                $table->integer('position')->nullable()->after('employee_id');
            }
            if (!Schema::hasColumn('shift_employee', 'status')) {
                $table->integer('status')->nullable()->after('position');
            }
        });
    }

    public function down()
    {
        Schema::table('shift_employee', function (Blueprint $table) {
            if (Schema::hasColumn('shift_employee', 'position')) {
                $table->dropColumn('position');
            }
            if (Schema::hasColumn('shift_employee', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
}
