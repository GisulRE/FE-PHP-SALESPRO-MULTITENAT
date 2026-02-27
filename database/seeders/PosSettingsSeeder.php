<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PosSettingsSeeder extends Seeder
{
    public function run()
    {
        try {
            // La tabla real es pos_setting (sin 's')
            if (!DB::getSchemaBuilder()->hasTable('pos_setting')) {
                return;
            }
            
            if (!DB::getSchemaBuilder()->hasTable('companies')) {
                return;
            }

            // Obtener todas las companies
            $companies = DB::table('companies')->get();
            
            foreach ($companies as $company) {
                // Verificar si ya existe un pos_setting para esta company
                $exists = DB::table('pos_setting')
                    ->where('company_id', $company->id)
                    ->exists();
                
                if (!$exists) {
                    DB::table('pos_setting')->insert([
                        'company_id'                => $company->id,
                        'customer_id'               => 1,
                        'warehouse_id'              => 1,
                        'biller_id'                 => 1,
                        'product_number'            => 10,
                        'keybord_active'            => 0,
                        'stripe_public_key'         => null,
                        'stripe_secret_key'         => '',
                        'user_category'             => 0,
                        'cant_decimal'              => 2,
                        'user_siat'                 => null,
                        'pass_siat'                 => null,
                        'url_siat'                  => null,
                        'url_operaciones'           => null,
                        'created_at'                => now(),
                        'updated_at'                => now(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            // ignore if table or columns don't exist yet
        }
    }
}
