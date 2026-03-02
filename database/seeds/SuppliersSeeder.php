<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SuppliersSeeder extends Seeder
{
    /**
     * Crea un proveedor genérico por cada empresa.
     */
    public function run()
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('suppliers')) {
                echo "SuppliersSeeder: tabla suppliers no existe, omitiendo." . PHP_EOL;
                return;
            }
            if (!DB::getSchemaBuilder()->hasTable('companies')) {
                echo "SuppliersSeeder: tabla companies no existe, omitiendo." . PHP_EOL;
                return;
            }

            $companies = DB::table('companies')->orderBy('id')->get();

            foreach ($companies as $company) {
                $exists = DB::table('suppliers')
                    ->where('company_id', $company->id)
                    ->where('name', 'Proveedor General')
                    ->exists();

                if (!$exists) {
                    DB::table('suppliers')->insert([
                        'name'         => 'Proveedor General',
                        'company_name' => $company->name,
                        'vat_number'   => null,
                        'email'        => null,
                        'phone_number' => '591-2-2222222',
                        'address'      => 'Sin Dirección',
                        'city'         => 'La Paz',
                        'state'        => 'La Paz',
                        'postal_code'  => null,
                        'country'      => 'Bolivia',
                        'image'        => null,
                        'is_active'    => true,
                        'company_id'   => $company->id,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                    echo "SuppliersSeeder: creado 'Proveedor General' para empresa [{$company->id}] {$company->name}" . PHP_EOL;
                } else {
                    echo "SuppliersSeeder: ya existe 'Proveedor General' para empresa [{$company->id}] {$company->name}" . PHP_EOL;
                }
            }
        } catch (\Exception $e) {
            echo "SuppliersSeeder Error: " . $e->getMessage() . PHP_EOL;
        }
    }
}
