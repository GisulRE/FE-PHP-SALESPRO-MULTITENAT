<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {
    public function up(): void
    {
        // Hacemos la migración compatible con varios motores.
        // Para MySQL creamos un PROCEDURE; para PostgreSQL una FUNCTION;
        // para otros motores creamos un fallback ligero (tabla marcador) para evitar errores.
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // Si ya existe el procedimiento, lo eliminamos para evitar errores al crear uno nuevo
            DB::unprepared('DROP PROCEDURE IF EXISTS check_year_resetcount_pv');

            DB::unprepared(
                'CREATE PROCEDURE check_year_resetcount_pv()
                BEGIN
                    UPDATE puntos_venta
                    SET correlativo_factura = 1,
                        correlativo_alquiler = 1,
                        correlativo_servicios_basicos = 1,
                        correlativo_nota_debcred = 1,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE is_active = true
                      AND YEAR(updated_at) != YEAR(CURRENT_DATE());
                END'
            );
        } elseif (in_array($driver, ['pgsql', 'postgres', 'postgresql'])) {
            // PostgreSQL: creamos un PROCEDURE para que pueda invocarse con CALL
            // Eliminamos posibles objetos previos (procedure o function) comprobando su tipo
            DB::unprepared(
                "DO $$ BEGIN
                    IF EXISTS (SELECT 1 FROM pg_catalog.pg_proc p JOIN pg_catalog.pg_namespace n ON p.pronamespace = n.oid
                        WHERE p.proname = 'check_year_resetcount_pv' AND p.prokind = 'p') THEN
                        EXECUTE 'DROP PROCEDURE check_year_resetcount_pv()';
                    END IF;
                END $$;"
            );

            DB::unprepared(
                "DO $$ BEGIN
                    IF EXISTS (SELECT 1 FROM pg_catalog.pg_proc p JOIN pg_catalog.pg_namespace n ON p.pronamespace = n.oid
                        WHERE p.proname = 'check_year_resetcount_pv' AND p.prokind = 'f') THEN
                        EXECUTE 'DROP FUNCTION check_year_resetcount_pv()';
                    END IF;
                END $$;"
            );

            DB::unprepared(
                "CREATE PROCEDURE check_year_resetcount_pv() LANGUAGE plpgsql AS $$
                BEGIN
                                        UPDATE puntos_venta
                                        SET correlativo_factura = 1,
                                                correlativo_alquiler = 1,
                                                correlativo_servicios_basicos = 1,
                                                correlativo_nota_debcred = 1,
                                                updated_at = CURRENT_TIMESTAMP
                                        WHERE COALESCE(CAST(is_active AS text), '0') IN ('1', 't', 'true')
                                            AND EXTRACT(YEAR FROM updated_at) <> EXTRACT(YEAR FROM CURRENT_DATE);
                END;
                $$;"
            );
        } else {
            // Fallback para otros motores (SQLite, SQL Server, etc.):
            // Creamos una tabla ligera `system_tasks` con un marcador para indicar
            // que esta migración se ejecutó. Esto evita que la migración falle
            // en motores que no soportan procedimientos almacenados.
            if (!Schema::hasTable('system_tasks')) {
                Schema::create('system_tasks', function (Blueprint $table) {
                    $table->string('name', 191)->primary();
                    $table->timestamps(0);
                });
            }

            if (!DB::table('system_tasks')->where('name', 'check_year_resetcount_pv_placeholder')->exists()) {
                DB::table('system_tasks')->insert([
                    'name' => 'check_year_resetcount_pv_placeholder',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::unprepared('DROP PROCEDURE IF EXISTS check_year_resetcount_pv');
            return;
        }

        if (in_array($driver, ['pgsql', 'postgres', 'postgresql'])) {
            // En el down() comprobamos y eliminamos procedure/function según exista y su tipo
            DB::unprepared(
                "DO $$ BEGIN
                    IF EXISTS (SELECT 1 FROM pg_catalog.pg_proc p JOIN pg_catalog.pg_namespace n ON p.pronamespace = n.oid
                        WHERE p.proname = 'check_year_resetcount_pv' AND p.prokind = 'p') THEN
                        EXECUTE 'DROP PROCEDURE check_year_resetcount_pv()';
                    END IF;
                END $$;"
            );

            DB::unprepared(
                "DO $$ BEGIN
                    IF EXISTS (SELECT 1 FROM pg_catalog.pg_proc p JOIN pg_catalog.pg_namespace n ON p.pronamespace = n.oid
                        WHERE p.proname = 'check_year_resetcount_pv' AND p.prokind = 'f') THEN
                        EXECUTE 'DROP FUNCTION check_year_resetcount_pv()';
                    END IF;
                END $$;"
            );

            return;
        }

        // Para otros motores simplemente eliminamos el marcador si existe
        if (Schema::hasTable('system_tasks')) {
            DB::table('system_tasks')->where('name', 'check_year_resetcount_pv_placeholder')->delete();
        }
    }
};