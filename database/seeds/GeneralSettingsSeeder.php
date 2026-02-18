<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GeneralSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $exists = DB::table('general_settings')->first();

        $data = [
            'site_title' => 'SalesPro',
            'site_logo' => null,
            'currency' => 'USD',
            'currency_position' => 'prefix',
            'staff_access' => 'all',
            'date_format' => 'Y-m-d',
            'theme' => 'default.css',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (!$exists) {
            DB::table('general_settings')->insert($data);
        } else {
            DB::table('general_settings')->where('id', $exists->id)->update($data);
        }
    }
}
