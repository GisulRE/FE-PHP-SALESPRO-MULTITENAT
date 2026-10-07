<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGeofenceColumnsToWarehousesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('warehouses')) {
            Schema::table('warehouses', function (Blueprint $table) {
                if (!Schema::hasColumn('warehouses', 'latitude')) {
                    $table->decimal('latitude', 10, 8)->nullable()->after('address')->comment('Latitud GPS de la sucursal');
                }
                if (!Schema::hasColumn('warehouses', 'longitude')) {
                    $table->decimal('longitude', 11, 8)->nullable()->after('latitude')->comment('Longitud GPS de la sucursal');
                }
                if (!Schema::hasColumn('warehouses', 'geofence_radius')) {
                    $table->integer('geofence_radius')->default(50)->after('longitude')->comment('Radio de geocerca en metros');
                }
                if (!Schema::hasColumn('warehouses', 'wifi_ssid')) {
                    $table->string('wifi_ssid', 100)->nullable()->after('geofence_radius')->comment('SSID de la red WiFi autorizada');
                }
                if (!Schema::hasColumn('warehouses', 'wifi_bssid')) {
                    $table->string('wifi_bssid', 100)->nullable()->after('wifi_ssid')->comment('BSSID (MAC) del router WiFi autorizado');
                }
                if (!Schema::hasColumn('warehouses', 'attendance_validation_mode')) {
                    $table->string('attendance_validation_mode', 30)->default('any')->after('wifi_bssid')->comment('Modo de validacion: any, gps, wifi, none');
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
        if (Schema::hasTable('warehouses')) {
            Schema::table('warehouses', function (Blueprint $table) {
                $columns = ['latitude', 'longitude', 'geofence_radius', 'wifi_ssid', 'wifi_bssid', 'attendance_validation_mode'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('warehouses', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
}
