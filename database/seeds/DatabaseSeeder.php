<?php

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
        // Permissions for reservations
        if (class_exists('\ReservationsPermissionSeeder')) {
            $this->call(\ReservationsPermissionSeeder::class);
        }
    }
}
