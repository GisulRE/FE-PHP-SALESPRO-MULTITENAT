<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SucursalSiatSeeder extends Seeder
{
    /**
     * Crea una Sucursal SIAT (Casa Matriz, código 0) por cada empresa.
     * Tabla: sucursal_siat
     */
    public function run()
    {
        try {
            if (!Schema::hasTable('sucursal_siat')) {
                $this->command->warn('SucursalSiatSeeder: tabla sucursal_siat no existe, omitiendo.');
                return;
            }

            $companies   = DB::table('companies')->orderBy('id')->get();
            $adminUser   = DB::table('users')->orderBy('id')->first();
            $usuarioAlta = $adminUser ? $adminUser->id : 1;

            if ($companies->isEmpty()) {
                $this->command->warn('SucursalSiatSeeder: no hay empresas registradas.');
                return;
            }

            foreach ($companies as $company) {
                $exists = DB::table('sucursal_siat')
                    ->where('id_empresa', $company->id)
                    ->where('sucursal', '0')
                    ->exists();

                if (!$exists) {
                    DB::table('sucursal_siat')->insert([
                        'sucursal'                    => '0',
                        'nombre'                      => 'CASA MATRIZ - ' . strtoupper($company->name),
                        'descripcion_sucursal'        => 'Casa Matriz de ' . $company->name,
                        'domicilio_tributario'        => 'Av. Principal #1',
                        'ciudad_municipio'            => 'La Paz',
                        'telefono'                    => '591-2-2222222',
                        'email'                       => null,
                        'id_autorizacion_facturacion' => null,
                        'departamento'                => 'La Paz',
                        'estado'                      => 'ACTIVO',
                        'usuario_alta'                => $usuarioAlta,
                        'id_empresa'                  => $company->id,
                        'created_at'                  => now(),
                        'updated_at'                  => now(),
                    ]);
                    $this->command->info("  Sucursal 0 creada para [{$company->id}] {$company->name}");
                } else {
                    $this->command->line("  Sucursal 0 ya existe para [{$company->id}] {$company->name}");
                }
            }

            $this->command->info('SucursalSiatSeeder completado.');
        } catch (\Exception $e) {
            $this->command->error('SucursalSiatSeeder Error: ' . $e->getMessage());
        }
    }
}
