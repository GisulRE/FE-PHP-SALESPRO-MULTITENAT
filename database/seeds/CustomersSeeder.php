<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomersSeeder extends Seeder
{
    /**
     * Crea un cliente genérico por cada empresa.
     */
    public function run()
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('customers')) {
                echo "CustomersSeeder: tabla customers no existe, omitiendo." . PHP_EOL;
                return;
            }
            if (!DB::getSchemaBuilder()->hasTable('companies')) {
                echo "CustomersSeeder: tabla companies no existe, omitiendo." . PHP_EOL;
                return;
            }

            $companies = DB::table('companies')->orderBy('id')->get();

            // Obtener grupo de clientes por defecto (si existe)
            $defaultGroup = DB::getSchemaBuilder()->hasTable('customer_groups')
                ? DB::table('customer_groups')->first()
                : null;
            $groupId = $defaultGroup ? $defaultGroup->id : null;

            foreach ($companies as $company) {
                $exists = DB::table('customers')
                    ->where('company_id', $company->id)
                    ->where('name', 'Cliente General')
                    ->exists();

                if (!$exists) {
                    DB::table('customers')->insert([
                        'name'             => 'Cliente General',
                        'company_name'     => $company->name,
                        'email'            => null,
                        'phone_number'     => '591-2-2222222',
                        'address'          => 'Sin Dirección',
                        'city'             => 'La Paz',
                        'state'            => 'La Paz',
                        'postal_code'      => null,
                        'country'          => 'Bolivia',
                        'tax_no'           => null,
                        'is_active'        => true,
                        'customer_group_id'=> $groupId,
                        'company_id'       => $company->id,
                        'tipo_documento'   => 5,   // NIT o CF según parametricas SIAT
                        'valor_documento'  => '0',
                        'razon_social'     => 'CLIENTE GENERAL',
                        'credit'           => 0,
                        'deposit'          => 0,
                        'expense'          => 0,
                        'price_type'       => 0,
                        'is_credit'        => 0,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                    echo "CustomersSeeder: creado 'Cliente General' para empresa [{$company->id}] {$company->name}" . PHP_EOL;
                } else {
                    echo "CustomersSeeder: ya existe 'Cliente General' para empresa [{$company->id}] {$company->name}" . PHP_EOL;
                }
            }
        } catch (\Exception $e) {
            echo "CustomersSeeder Error: " . $e->getMessage() . PHP_EOL;
        }
    }
}
