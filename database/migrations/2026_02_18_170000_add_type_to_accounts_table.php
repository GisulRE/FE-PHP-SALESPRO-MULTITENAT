<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Agregar columna type a accounts si no existe
        if (!Schema::hasColumn('accounts', 'type')) {
            Schema::table('accounts', function (Blueprint $table) {
                $table->tinyInteger('type')->default(1)->nullable()->after('is_active');
            });
        }

        // Asignar company_id = 1 a todos los productos que no tengan empresa asignada
        // Asume que la empresa por defecto tiene id = 1
        if (Schema::hasColumn('products', 'company_id')) {
            DB::table('products')
                ->whereNull('company_id')
                ->update(['company_id' => 1]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('accounts', 'type')) {
            Schema::table('accounts', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }
};
