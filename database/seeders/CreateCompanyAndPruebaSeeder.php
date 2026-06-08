<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

class CreateCompanyAndPruebaSeeder extends Seeder
{
    public function run()
    {
        try {
            $defaultCompanyId = null;
            if (Schema::hasTable('companies')) {
                $company = DB::table('companies')->orderBy('id')->first();
                if (!$company) {
                    $companyData = [
                        'name' => 'Empresa Principal',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    if (Schema::hasColumn('companies', 'nit')) {
                        $companyData['nit'] = '0';
                    }

                    $defaultCompanyId = DB::table('companies')->insertGetId($companyData);
                } else {
                    $defaultCompanyId = $company->id;
                }
            }

            $userRoleId = null;
            if (Schema::hasTable('roles')) {
                $userRole = DB::table('roles')->where('name', 'User')->first();
                if (!$userRole) {
                    $roleData = [
                        'name' => 'User',
                        'guard_name' => 'web',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if (Schema::hasColumn('roles', 'description')) {
                        $roleData['description'] = 'Rol base para usuarios de prueba';
                    }
                    if (Schema::hasColumn('roles', 'is_active')) {
                        $roleData['is_active'] = true;
                    }
                    if (Schema::hasColumn('roles', 'blocked_modules')) {
                        $roleData['blocked_modules'] = null;
                    }

                    $userRoleId = DB::table('roles')->insertGetId($roleData);
                } else {
                    $userRoleId = $userRole->id;
                }
            }

            if (Schema::hasTable('users')) {
                $email = 'prueba@local.test';
                $user = DB::table('users')->where('email', $email)->first();

                if (!$user) {
                    DB::table('users')->insert([
                        'name' => 'prueba',
                        'email' => $email,
                        'password' => Hash::make('Llave123.#'),
                        'phone' => null,
                        'company_name' => null,
                        'company_id' => $defaultCompanyId,
                        'role_id' => $userRoleId,
                        'biller_id' => null,
                        'is_active' => true,
                        'is_deleted' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('users')->where('id', $user->id)->update([
                        'company_id' => $defaultCompanyId,
                        'role_id' => $userRoleId,
                        'is_active' => true,
                        'is_deleted' => false,
                        'password' => Hash::make('Llave123.#'),
                        'updated_at' => now(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            $this->command->error('Error en CreateCompanyAndPruebaSeeder: ' . $e->getMessage());
            throw $e;
        }
    }
}
