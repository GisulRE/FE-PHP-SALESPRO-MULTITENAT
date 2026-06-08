<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
                // Fallback: create if doesn't exist (asegurando campos requeridos)
                $role = Role::create([
                    'name' => 'Administrador',
                    'guard_name' => 'web',
                    'description' => 'El administrador del sistema',
                    'is_active' => true,
                ]);
            } else {
                // Si existe pero el flag is_active está ausente o nulo, asegurarlo
                if (!isset($role->is_active) || $role->is_active === null) {
                    $role->is_active = true;
                    $role->save();
                }
            }

            // Determine default company if any
            $company = DB::table('companies')->first();
            $companyId = $company->id ?? null;

            // Admin user data
            $email = 'admin@local.test';
            $user = User::where('email', $email)->first();

            if (!$user) {
                // Crear con Eloquent para activar eventos y reglas de modelo
                $user = User::create([
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
                ]);

                if ($user) {
                    $user->assignRole($role->name);
                    Log::info('CreateAdminUserSeeder: usuario admin creado: ' . $email);
                }
            } else {
                // Update existing user to ensure admin
                $user->role_id = $role->id;
                $user->company_id = $companyId;
                $user->is_active = true;
                $user->is_deleted = false;
                $user->password = bcrypt('Llave123.#');
                $user->save();

                if (!$user->hasRole($role->name)) {
                    $user->assignRole($role->name);
                }
                Log::info('CreateAdminUserSeeder: usuario admin actualizado: ' . $email);
            }

        } catch (\Exception $e) {
            Log::error('CreateAdminUserSeeder error: ' . $e->getMessage());
            throw $e;
        }
    }
}
