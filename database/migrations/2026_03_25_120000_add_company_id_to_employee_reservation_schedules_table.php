<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCompanyIdToEmployeeReservationSchedulesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('employee_reservation_schedules')) {
            return;
        }

        if (!Schema::hasColumn('employee_reservation_schedules', 'company_id')) {
            Schema::table('employee_reservation_schedules', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('employee_id')->index();
            });
        }

        if (Schema::hasColumn('employee_reservation_schedules', 'company_id')) {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement('UPDATE employee_reservation_schedules ers SET company_id = e.company_id FROM employees e WHERE e.id = ers.employee_id AND ers.company_id IS NULL');
            } else {
                DB::table('employee_reservation_schedules as ers')
                    ->leftJoin('employees as e', 'e.id', '=', 'ers.employee_id')
                    ->whereNull('ers.company_id')
                    ->update(['ers.company_id' => DB::raw('e.company_id')]);
            }
        }

        if (Schema::hasTable('companies')) {
            try {
                Schema::table('employee_reservation_schedules', function (Blueprint $table) {
                    $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
                });
            } catch (\Exception $e) {
                // ignore FK creation issues
            }
        }
    }

    public function down()
    {
        if (!Schema::hasTable('employee_reservation_schedules') || !Schema::hasColumn('employee_reservation_schedules', 'company_id')) {
            return;
        }

        try {
            Schema::table('employee_reservation_schedules', function (Blueprint $table) {
                $table->dropForeign(['company_id']);
            });
        } catch (\Exception $e) {
            // ignore
        }

        Schema::table('employee_reservation_schedules', function (Blueprint $table) {
            $table->dropColumn('company_id');
        });
    }
}
