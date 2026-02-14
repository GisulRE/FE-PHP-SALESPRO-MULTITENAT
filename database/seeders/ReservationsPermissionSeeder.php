<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReservationsPermissionSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    $permissions = [
      ['name' => 'reservations-index', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
      ['name' => 'reservations-add', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
      ['name' => 'reservations-edit', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
      ['name' => 'reservations-delete', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
    ];

    foreach ($permissions as $perm) {
      $exists = DB::table('permissions')->where('name', $perm['name'])->first();
      if (!$exists) {
        DB::table('permissions')->insert($perm);
      }
    }
  }
}
