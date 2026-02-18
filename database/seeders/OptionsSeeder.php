<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class OptionsSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        // Units (example base units)
            if (Schema::hasTable('units')) {
                DB::table('units')->insertOrIgnore([
                    ['id' => 1, 'unit_code' => 'UNI', 'unit_name' => 'Unidad', 'base_unit' => null, 'operator' => '*', 'operation_value' => 1, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                    ['id' => 2, 'unit_code' => 'KG', 'unit_name' => 'Kilogramo', 'base_unit' => null, 'operator' => '*', 'operation_value' => 1, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ]);
            }

        // Taxes
            if (Schema::hasTable('taxes')) {
                DB::table('taxes')->insertOrIgnore([
                    ['id' => 1, 'name' => '0%', 'rate' => 0, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                    ['id' => 2, 'name' => 'IVA 13%', 'rate' => 13, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
                ]);
            }

        // Accounts: set type = 2 for accounts likely used as product account
            if (Schema::hasTable('accounts')) {
                DB::table('accounts')->insertOrIgnore([
                    ['id' => 100, 'account_no' => '100', 'name' => 'Caja Principal', 'initial_balance' => 0, 'total_balance' => 0, 'note' => 'Cuenta por defecto', 'is_default' => 1, 'is_active' => 1, 'type' => 2, 'created_at' => $now, 'updated_at' => $now],
                ]);
            }

        // // SIAT activities (if table exists)
        // if (Schema::hasTable('siat_actividad_economica')) {
        //     DB::table('siat_actividad_economica')->insertOrIgnore([
        //         ['id' => 1, 'codigo' => '0000', 'descripcion' => 'Actividad general', 'created_at' => $now, 'updated_at' => $now],
        //     ]);
        // }
    }
}
