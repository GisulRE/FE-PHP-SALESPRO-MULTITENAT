<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeReservationSchedulesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('employee_reservation_schedules')) {
            Schema::create('employee_reservation_schedules', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('employee_id');
                $table->unsignedBigInteger('company_id')->nullable()->index();
                $table->unsignedTinyInteger('day_of_week');
                $table->boolean('is_enabled')->default(false);
                $table->time('start_time')->nullable();
                $table->time('end_time')->nullable();
                $table->unsignedSmallInteger('interval_minutes')->default(30);
                $table->timestamps();

                $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
                $table->unique(['employee_id', 'day_of_week'], 'uniq_employee_day_schedule');
            });

            if (Schema::hasTable('companies')) {
                try {
                    Schema::table('employee_reservation_schedules', function (Blueprint $table) {
                        $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
                    });
                } catch (\Exception $e) {
                    // ignore FK creation issues on legacy schemas
                }
            }
        }
    }

    public function down()
    {
      Schema::dropIfExists('employee_reservation_schedules');
    }
}
