<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PosSettingsSeeder extends Seeder
{
    public function run()
    {
        try {
            if (DB::table('pos_settings')->exists()) {
                return;
            }

            DB::table('pos_settings')->insert([
                'user_siat' => null,
                'pass_siat' => null,
                'url_siat' => null,
                'url_operaciones' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // ignore if table doesn't exist yet
        }
    }
}
