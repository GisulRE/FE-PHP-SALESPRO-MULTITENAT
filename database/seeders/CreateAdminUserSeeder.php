<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\User;
use Spatie\Permission\Models\Role;

class CreateAdminUserSeeder extends Seeder
{
    public function run()
    {
        try {
            // Use the Administrador role (ID 1) which has all permissions
            $role = Role::where('name', 'Administrador')->first();
            
            if (!$role) {
                // Fallback: create if doesn't exist
                $role = Role::create(['name' => 'Administrador', 'guard_name' => 'web', 'description' => 'El administrador del sistema']);
            }

            // Determine default company if any
            $company = DB::table('companies')->first();
            $companyId = $company->id ?? null;

            // Admin user data
            $email = 'admin@local.test';
            $user = DB::table('users')->where('email', $email)->first();

            if (!$user) {
                $id = DB::table('users')->insertGetId([
                    'name' => 'admin',
                    'email' => $email,
                    'password' => bcrypt('Llave123.#'),
                    'phone' => null,
                    'company_name' => null,
                    'company_id' => $companyId,
                    'role_id' => $role->id,
                    'biller_id' => null,
                    'is_active' => true,
                    'is_deleted' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $userModel = User::find($id);
                if ($userModel) {
                    $userModel->assignRole($role->name);
                }
            } else {
                // Update existing user to ensure admin
                DB::table('users')->where('id', $user->id)->update([
                    'role_id' => $role->id,
                    'company_id' => $companyId,
                    'is_active' => true,
                    'is_deleted' => false,
                    'password' => bcrypt('Llave123.#'),
                    'updated_at' => now(),
                ]);
                $userModel = User::where('email', $email)->first();
                if ($userModel && !$userModel->hasRole($role->name)) {
                    $userModel->assignRole($role->name);
                }
            }

        } catch (\Exception $e) {
            // ignore on failure
        }
    }
}
