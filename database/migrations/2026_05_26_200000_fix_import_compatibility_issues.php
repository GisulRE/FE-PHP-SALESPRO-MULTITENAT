<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixImportCompatibilityIssues extends Migration
{
    public function up()
    {
        // Fix 1: Ampliar categories.name de varchar(191) a TEXT
        // Las categorías SIAT tienen nombres de hasta 350+ caracteres.
        if (Schema::hasColumn('categories', 'name')) {
            DB::statement('ALTER TABLE categories ALTER COLUMN name TYPE TEXT');
        }

        // Fix 2: Crear vista/alias migration_maps -> migration_map
        // El script de diagnóstico y algunos queries usan 'migration_maps' (plural),
        // pero la tabla real es 'migration_map'.
        if (Schema::hasTable('migration_map') && !Schema::hasTable('migration_maps')) {
            DB::statement('CREATE VIEW migration_maps AS SELECT * FROM migration_map');
        }

        // Fix 3: Ampliar products.name a TEXT
        // La vista 'kardex' depende de products.name en PostgreSQL; hay que recrearla.
        if (Schema::hasColumn('products', 'name')) {
            // Obtener la definición actual de la vista kardex para recrearla después
            $kardexDef = DB::selectOne(
                "SELECT pg_get_viewdef('kardex', true) AS def FROM pg_views WHERE viewname = 'kardex'"
            );

            if ($kardexDef) {
                // Eliminar la vista para poder alterar la columna
                DB::statement('DROP VIEW IF EXISTS kardex CASCADE');
            }

            DB::statement('ALTER TABLE products ALTER COLUMN name TYPE TEXT');

            // Recrear la vista con su definición original
            if ($kardexDef && $kardexDef->def) {
                DB::statement('CREATE VIEW kardex AS ' . $kardexDef->def);
            }
        }
    }

    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS migration_maps');

        // Revertir categorías
        DB::statement("ALTER TABLE categories ALTER COLUMN name TYPE VARCHAR(191) USING name::VARCHAR(191)");

        // Revertir products — requiere el mismo proceso con la vista kardex
        $kardexDef = DB::selectOne(
            "SELECT pg_get_viewdef('kardex', true) AS def FROM pg_views WHERE viewname = 'kardex'"
        );
        if ($kardexDef) {
            DB::statement('DROP VIEW IF EXISTS kardex CASCADE');
        }
        DB::statement("ALTER TABLE products ALTER COLUMN name TYPE VARCHAR(191) USING name::VARCHAR(191)");
        if ($kardexDef && $kardexDef->def) {
            DB::statement('CREATE VIEW kardex AS ' . $kardexDef->def);
        }
    }
}
