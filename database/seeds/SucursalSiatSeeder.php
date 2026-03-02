<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SucursalSiatSeeder extends Seeder
{
    /**
     * Crea una sucursal SIAT (Casa Matriz, código 0) por cada empresa.
     */
    public function run()
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('sucursal_siat')) {
                echo "SucursalSiatSeeder: tabla sucursal_siat no existe, omitiendo." . PHP_EOL;
                return;
            }
            if (!DB::getSchemaBuilder()->hasTable('companies')) {
                echo "SucursalSiatSeeder: tabla companies no existe, omitiendo." . PHP_EOL;
                return;
            }

            $companies   = DB::table('companies')->orderBy('id')->get();
            $adminUser   = DB::table('users')->orderBy('id')->first();
            $usuarioAlta = $adminUser ? $adminUser->id : 1;

            foreach ($companies as $company) {
                // Una sola sucursal 0 (Casa Matriz) por empresa
                $exists = DB::table('sucursal_siat')
                    ->where('id_empresa', $company->id)
                    ->where('sucursal', '0')
                    ->exists();

                if (!$exists) {
                    DB::table('sucursal_siat')->insert([
                        'sucursal'                   => '0',
                        'nombre'                     => 'CASA MATRIZ - ' . strtoupper($company->name),
                        'descripcion_sucursal'        => 'Casa Matriz de ' . $company->name,
                        'domicilio_tributario'        => 'Av. Principal #1',
                        'ciudad_municipio'            => 'La Paz',
                        'telefono'                   => '591-2-2222222',
                        'email'                      => null,
                        'id_autorizacion_facturacion' => null,
                        'departamento'               => 'La Paz',
                        'estado'                     => 'ACTIVO',
                        'usuario_alta'               => $usuarioAlta,
                        'id_empresa'                 => $company->id,
                        'created_at'                 => now(),
                        'updated_at'                 => now(),
                    ]);
                    echo "SucursalSiatSeeder: creada sucursal 0 para empresa [{$company->id}] {$company->name}" . PHP_EOL;
                } else {
                    echo "SucursalSiatSeeder: ya existe sucursal 0 para empresa [{$company->id}] {$company->name}" . PHP_EOL;
                }
            }
        } catch (\Exception $e) {
            echo "SucursalSiatSeeder Error: " . $e->getMessage() . PHP_EOL;
        }
    }
}
