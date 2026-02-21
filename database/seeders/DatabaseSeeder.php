<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            PosSettingsSeeder::class,
            GeneralSettingsSeeder::class,
            ReservationsPermissionSeeder::class,
            RolesPermissionsSeeder::class,
            CreateAdminUserSeeder::class,
            CreateCompanyAndPruebaSeeder::class,
            OptionsSeeder::class,
        ]);
    }
}
