<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SuppliersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('suppliers')) {
                return;
            }
            
            if (!DB::getSchemaBuilder()->hasTable('companies')) {
                return;
            }

            // Obtener todas las companies
            $companies = DB::table('companies')->get();
            
            $suppliers = [
                [
                    'name' => 'Distribuidora ABC S.R.L.',
                    'company_name' => 'Distribuidora ABC S.R.L.',
                    'vat_number' => '1234567890',
                    'email' => 'ventas@distribuidoraabc.com',
                    'phone_number' => '591-2-2111111',
                    'address' => 'Av. Comercio #100',
                    'city' => 'La Paz',
                    'state' => 'La Paz',
                    'postal_code' => '00000',
                    'country' => 'Bolivia',
                    'is_active' => true,
                ],
                [
                    'name' => 'Importadora XYZ Ltda.',
                    'company_name' => 'Importadora XYZ Ltda.',
                    'vat_number' => '0987654321',
                    'email' => 'contacto@importadoraxyz.com',
                    'phone_number' => '591-2-2222222',
                    'address' => 'Calle Principal #200',
                    'city' => 'La Paz',
                    'state' => 'La Paz',
                    'postal_code' => '00000',
                    'country' => 'Bolivia',
                    'is_active' => true,
                ],
                [
                    'name' => 'Proveedor Central S.A.',
                    'company_name' => 'Proveedor Central S.A.',
                    'vat_number' => '1122334455',
                    'email' => 'ventas@proveedorcentral.com',
                    'phone_number' => '591-2-2333333',
                    'address' => 'Zona Central, Calle 21 de Calacoto',
                    'city' => 'La Paz',
                    'state' => 'La Paz',
                    'postal_code' => '00000',
                    'country' => 'Bolivia',
                    'is_active' => true,
                ],
                [
                    'name' => 'Mayorista del Sur',
                    'company_name' => 'Mayorista del Sur S.R.L.',
                    'vat_number' => '5544332211',
                    'email' => 'info@mayoristadelsur.com',
                    'phone_number' => '591-2-2444444',
                    'address' => 'Av. del Ejército #500',
                    'city' => 'La Paz',
                    'state' => 'La Paz',
                    'postal_code' => '00000',
                    'country' => 'Bolivia',
                    'is_active' => true,
                ],
                [
                    'name' => 'Comercial Norte Ltda.',
                    'company_name' => 'Comercial Norte Ltda.',
                    'vat_number' => '9988776655',
                    'email' => 'ventas@comercialnorte.com',
                    'phone_number' => '591-2-2555555',
                    'address' => 'Zona Norte, Av. Blanco Galindo',
                    'city' => 'La Paz',
                    'state' => 'La Paz',
                    'postal_code' => '00000',
                    'country' => 'Bolivia',
                    'is_active' => true,
                ],
            ];
            
            foreach ($companies as $company) {
                foreach ($suppliers as $supplier) {
                    // Verificar si ya existe un supplier similar para esta company
                    $exists = DB::table('suppliers')
                        ->where('company_id', $company->id)
                        ->where('name', $supplier['name'])
                        ->exists();
                    
                    if (!$exists) {
                        DB::table('suppliers')->insert([
                            'company_id'    => $company->id,
                            'name'          => $supplier['name'],
                            'image'         => null,
                            'company_name'  => $supplier['company_name'],
                            'vat_number'    => $supplier['vat_number'],
                            'email'         => $supplier['email'],
                            'phone_number'  => $supplier['phone_number'],
                            'address'       => $supplier['address'],
                            'city'          => $supplier['city'],
                            'state'         => $supplier['state'],
                            'postal_code'   => $supplier['postal_code'],
                            'country'       => $supplier['country'],
                            'is_active'     => $supplier['is_active'],
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
