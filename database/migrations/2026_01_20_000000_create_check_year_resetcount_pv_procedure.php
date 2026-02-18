<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // SI ya existe el procedimiento, lo eliminamos para evitar errores al crear uno nuevo
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
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // También comentamos esto en el down() por si acaso necesitas hacer rollback
        // DB::unprepared('DROP PROCEDURE IF EXISTS check_year_resetcount_pv');
    }
};