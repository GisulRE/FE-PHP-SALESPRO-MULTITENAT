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
            $createdCompanies = [];
            if (Schema::hasTable('companies')) {
                $companyNames = ['Empresa Prueba 1', 'Empresa Prueba 2'];
                foreach ($companyNames as $cname) {
                    $company = DB::table('companies')->where('name', $cname)->first();
                    if (!$company) {
                        $id = DB::table('companies')->insertGetId([
                            'name' => $cname,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $createdCompanies[] = $id;
                    } else {
                        $createdCompanies[] = $company->id;
                    }
                }
            }

            $userRoleId = null;
            if (Schema::hasTable('roles')) {
                $userRole = DB::table('roles')->where('name', 'Administrador')->first();
                if (!$userRole) {
                    $userRoleId = DB::table('roles')->insertGetId([
                        'name' => 'User',
                        'guard_name' => 'web',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $userRoleId = $userRole->id;
                }
            }

            if (Schema::hasTable('users')) {
                $users = [
                    ['name' => 'prueba1', 'email' => 'prueba1@local.test', 'company_index' => 0],
                    ['name' => 'prueba2', 'email' => 'prueba2@local.test', 'company_index' => 1],
                ];

                foreach ($users as $u) {
                    $email = $u['email'];
                    $user = DB::table('users')->where('email', $email)->first();
                    $companyId = $createdCompanies[$u['company_index']] ?? null;

                    if (!$user) {
                        DB::table('users')->insert([
                            'name' => $u['name'],
                            'email' => $email,
                            'password' => Hash::make('Llave123.#'),
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
                        DB::table('users')->where('id', $user->id)->update([
                            'company_id' => $companyId,
                            'role_id' => $userRoleId,
                            'is_active' => true,
                            'is_deleted' => false,
                            'password' => Hash::make('Llave123.#'),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->command->error('Error en CreateCompanyAndPruebaSeeder: ' . $e->getMessage());
            throw $e;
        }
    }
}
