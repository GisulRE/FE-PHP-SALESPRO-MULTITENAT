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
        // Ensure general_settings table has defaults (legacy path fallback)
        try {
            if (\Schema::hasTable('general_settings')) {
                $exists = \DB::table('general_settings')->first();
                $data = [
                    'site_title' => 'SalesPro',
                    'site_logo' => null,
                    'currency' => 'USD',
                    'currency_position' => 'prefix',
                    'staff_access' => 'all',
                    'date_format' => 'Y-m-d',
                    'theme' => 'default',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                if (!$exists) {
                    \DB::table('general_settings')->insert($data);
                } else {
                    \DB::table('general_settings')->where('id', $exists->id)->update($data);
                }
            }
        } catch (\Exception $e) {
            // ignore if DB not ready
        }

        // Legacy fallback: ensure pos_settings row exists
        try {
            if (\Schema::hasTable('pos_settings')) {
                $exists = \DB::table('pos_settings')->first();
                $data = [
                    'user_siat' => null,
                    'pass_siat' => null,
                    'url_siat' => null,
                    'url_operaciones' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                if (!$exists) {
                    \DB::table('pos_settings')->insert($data);
                }
            }
        } catch (\Exception $e) {
            // ignore if DB not ready
        }

        // Permissions for reservations
        if (class_exists('\ReservationsPermissionSeeder')) {
            $this->call(\ReservationsPermissionSeeder::class);
        }

        // Legacy fallback: ensure an admin user exists with requested password
        try {
            if (\Schema::hasTable('roles') && \Schema::hasTable('users')) {
                $role = \DB::table('roles')->where('name', 'Admin')->first();
                if (!$role) {
                    $roleId = \DB::table('roles')->insertGetId([
                        'name' => 'Admin',
                        'guard_name' => 'web',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $roleId = $role->id;
                }

                $company = \DB::table('companies')->first();
                $companyId = $company->id ?? null;

                $email = 'admin@local.test';
                $user = \DB::table('users')->where('email', $email)->first();

                if (!$user) {
                    \DB::table('users')->insert([
                        'name' => 'Administrator',
                        'email' => $email,
                        'password' => bcrypt('Llave123.#'),
                        'phone' => null,
                        'company_name' => null,
                        'company_id' => $companyId,
                        'role_id' => $roleId,
                        'biller_id' => null,
                        'is_active' => true,
                        'is_deleted' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    \DB::table('users')->where('id', $user->id)->update([
                        'role_id' => $roleId,
                        'company_id' => $companyId,
                        'is_active' => true,
                        'is_deleted' => false,
                        'password' => bcrypt('Llave123.#'),
                        'updated_at' => now(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            // ignore if DB not ready
        }

        // Create a default company and a test user 'prueba' with provided password
        try {
            $companyId = null;
            if (\Schema::hasTable('companies')) {
                $company = \DB::table('companies')->where('name', 'Empresa Prueba')->first();
                if (!$company) {
                    $companyId = \DB::table('companies')->insertGetId([
                        'name' => 'Empresa Prueba',
                        'address' => null,
                        'phone' => null,
                        'email' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $companyId = $company->id;
                }
            }

            // Ensure there is a 'User' role to assign
            $userRoleId = null;
            if (\Schema::hasTable('roles')) {
                $userRole = \DB::table('roles')->where('name', 'User')->first();
                if (!$userRole) {
                    $userRoleId = \DB::table('roles')->insertGetId([
                        'name' => 'User',
                        'guard_name' => 'web',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $userRoleId = $userRole->id;
                }
            }

            if (\Schema::hasTable('users')) {
                $email = 'prueba@local.test';
                $user = \DB::table('users')->where('email', $email)->first();

                if (!$user) {
                    \DB::table('users')->insert([
                        'name' => 'prueba',
                        'email' => $email,
                        'password' => bcrypt('Llave123.#'),
                        'phone' => null,
                        'company_name' => null,
                        'company_id' => $companyId,
                        'role_id' => $userRoleId,
                        'biller_id' => null,
                        'is_active' => true,
                        'is_deleted' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    \DB::table('users')->where('id', $user->id)->update([
                        'company_id' => $companyId,
                        'role_id' => $userRoleId,
                        'is_active' => true,
                        'is_deleted' => false,
                        'password' => bcrypt('Llave123.#'),
                        'updated_at' => now(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            // ignore if DB not ready
        }
    }
}
