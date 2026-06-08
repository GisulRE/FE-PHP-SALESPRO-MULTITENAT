<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddMissingColumnsToRegistrosSincronizacionSiatTable extends Migration
{
    public function up()
    {
        Schema::table('registros_sincronizacion_siat', function (Blueprint $table) {
            if (!Schema::hasColumn('registros_sincronizacion_siat', 'descripcion')) {
                $table->string('descripcion', 150)->nullable();
            }
            if (!Schema::hasColumn('registros_sincronizacion_siat', 'usuario_alta')) {
                $table->unsignedBigInteger('usuario_alta')->nullable();
            }
            if (!Schema::hasColumn('registros_sincronizacion_siat', 'usuario_modificacion')) {
                $table->unsignedBigInteger('usuario_modificacion')->nullable();
            }
            if (!Schema::hasColumn('registros_sincronizacion_siat', 'orden')) {
                $table->integer('orden')->nullable();
            }
            if (!Schema::hasColumn('registros_sincronizacion_siat', 'sucursal')) {
                $table->integer('sucursal')->nullable();
            }
            if (!Schema::hasColumn('registros_sincronizacion_siat', 'codigo_punto_venta')) {
                $table->integer('codigo_punto_venta')->nullable();
            }
        });

        // Hacer updated_at nullable usando SQL nativo para evitar problemas con Doctrine DBAL en Postgres
        DB::statement('ALTER TABLE registros_sincronizacion_siat ALTER COLUMN updated_at DROP NOT NULL;');
    }

    public function down()
    {
        Schema::table('registros_sincronizacion_siat', function (Blueprint $table) {
            if (Schema::hasColumn('registros_sincronizacion_siat', 'descripcion')) {
                $table->dropColumn('descripcion');
            }
            if (Schema::hasColumn('registros_sincronizacion_siat', 'usuario_alta')) {
                $table->dropColumn('usuario_alta');
            }
            if (Schema::hasColumn('registros_sincronizacion_siat', 'usuario_modificacion')) {
                $table->dropColumn('usuario_modificacion');
            }
            if (Schema::hasColumn('registros_sincronizacion_siat', 'orden')) {
                $table->dropColumn('orden');
            }
            if (Schema::hasColumn('registros_sincronizacion_siat', 'sucursal')) {
                $table->dropColumn('sucursal');
            }
            if (Schema::hasColumn('registros_sincronizacion_siat', 'codigo_punto_venta')) {
                $table->dropColumn('codigo_punto_venta');
            }
        });

        // Opcional: revertir updated_at a NOT NULL
        DB::statement('ALTER TABLE registros_sincronizacion_siat ALTER COLUMN updated_at SET NOT NULL;');
    }
}
