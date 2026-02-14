<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDurationToReservationsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table('reservations', function (Blueprint $table) {
      // duration in minutes for the reservation (nullable)
      $table->unsignedInteger('duration_minutes')->nullable()->after('reserved_time');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('reservations', function (Blueprint $table) {
      $table->dropColumn('duration_minutes');
    });
  }
}
