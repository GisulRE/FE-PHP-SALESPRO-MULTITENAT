<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WarehousesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('warehouses')) {
                return;
            }
            
            if (!DB::getSchemaBuilder()->hasTable('companies')) {
                return;
            }

            // Obtener todas las companies
            $companies = DB::table('companies')->get();
            
            $warehouses = [
                [
                    'name' => 'Almacén Principal',
                    'phone' => '591-2-2222222',
                    'email' => 'principal@empresa.com',
                    'address' => 'Av. Principal #123',
                    'is_active' => true,
                ],
                [
                    'name' => 'Almacén Secundario',
                    'phone' => '591-2-3333333',
                    'email' => 'secundario@empresa.com',
                    'address' => 'Calle Secundaria #456',
                    'is_active' => true,
                ],
                [
                    'name' => 'Almacén Norte',
                    'phone' => '591-2-4444444',
                    'email' => 'norte@empresa.com',
                    'address' => 'Zona Norte, Av. 6 de Agosto #789',
                    'is_active' => true,
                ],
            ];
            
            foreach ($companies as $company) {
                foreach ($warehouses as $index => $warehouse) {
                    // Verificar si ya existe un warehouse similar para esta company
                    $exists = DB::table('warehouses')
                        ->where('company_id', $company->id)
                        ->where('name', $warehouse['name'])
                        ->exists();
                    
                    if (!$exists) {
                        DB::table('warehouses')->insert([
                            'company_id'    => $company->id,
                            'name'          => $warehouse['name'],
                            'phone'         => $warehouse['phone'],
                            'email'         => $warehouse['email'],
                            'address'       => $warehouse['address'],
                            'is_active'     => $warehouse['is_active'],
                            'sucursal_id'   => null,
                            'sucursal_siat' => null,
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            // ignore if table or columns don't exist yet
            echo "Error: " . $e->getMessage() . PHP_EOL;
        }
    }
}
