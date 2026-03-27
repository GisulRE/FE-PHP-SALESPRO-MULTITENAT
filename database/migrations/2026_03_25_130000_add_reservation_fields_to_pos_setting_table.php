<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReservationFieldsToPosSettingTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('pos_setting')) {
            return;
        }

        Schema::table('pos_setting', function (Blueprint $table) {
            if (!Schema::hasColumn('pos_setting', 'nro_encargado')) {
                $table->string('nro_encargado')->nullable();
            }
            if (!Schema::hasColumn('pos_setting', 'hora_inicio_atencion')) {
                $table->time('hora_inicio_atencion')->nullable();
            }
            if (!Schema::hasColumn('pos_setting', 'hora_fin_atencion')) {
                $table->time('hora_fin_atencion')->nullable();
            }
            if (!Schema::hasColumn('pos_setting', 'intervalo_reserva_minutos')) {
                $table->unsignedSmallInteger('intervalo_reserva_minutos')->nullable();
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('pos_setting')) {
            return;
        }

        Schema::table('pos_setting', function (Blueprint $table) {
            if (Schema::hasColumn('pos_setting', 'intervalo_reserva_minutos')) {
                $table->dropColumn('intervalo_reserva_minutos');
            }
            if (Schema::hasColumn('pos_setting', 'hora_fin_atencion')) {
                $table->dropColumn('hora_fin_atencion');
            }
            if (Schema::hasColumn('pos_setting', 'hora_inicio_atencion')) {
                $table->dropColumn('hora_inicio_atencion');
            }
            if (Schema::hasColumn('pos_setting', 'nro_encargado')) {
                $table->dropColumn('nro_encargado');
            }
        });
    }
}
