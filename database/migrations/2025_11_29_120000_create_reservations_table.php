<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservationsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('reservations', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('name');
      $table->string('phone')->index();
      $table->string('email')->nullable();
      // product/service being reserved (nullable to allow free-text in the future)
      $table->unsignedBigInteger('product_id')->nullable()->index();
      // sucursal/branch where the reservation will take place
      $table->unsignedBigInteger('sucursal_id')->nullable()->index();
      // date and time of reservation
      $table->date('reserved_date')->nullable();
      $table->time('reserved_time')->nullable();
      // optional status/notes
      $table->string('status')->default('pending');
      $table->text('notes')->nullable();

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('reservations');
  }
}
