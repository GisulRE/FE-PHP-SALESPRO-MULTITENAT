<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmployeeIdToReservationsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    if (Schema::hasTable('reservations')) {
      Schema::table('reservations', function (Blueprint $table) {
        if (!Schema::hasColumn('reservations', 'employee_id')) {
          $table->unsignedInteger('employee_id')->nullable()->after('sucursal_id');
          $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
        }
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
    if (Schema::hasTable('reservations')) {
      Schema::table('reservations', function (Blueprint $table) {
        if (Schema::hasColumn('reservations', 'employee_id')) {
          $table->dropForeign(['employee_id']);
          $table->dropColumn('employee_id');
        }
      });
    }
  }
}
