<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\CustomerGroup;
use App\Customer;
use App\Company;

class CustomersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Verificar si ya existen customer groups
        if (CustomerGroup::count() == 0) {
            $this->command->info('Creando Customer Groups...');
            
            $groups = [
                ['name' => 'General', 'percentage' => 0, 'is_active' => 1],
                ['name' => 'VIP', 'percentage' => 10, 'is_active' => 1],
                ['name' => 'Mayorista', 'percentage' => 15, 'is_active' => 1],
            ];

            foreach ($groups as $group) {
                CustomerGroup::create($group);
                $this->command->info('Customer Group creado: ' . $group['name']);
            }
        }

        // Verificar si ya existen customers
        if (Customer::withoutGlobalScope('company_id')->count() > 0) {
            $this->command->info('Customers ya existen, omitiendo seeder...');
            return;
        }

        $this->command->info('Creando Customers...');

        // Obtener el primer customer group
        $customerGroup = CustomerGroup::first();

        // Obtener todas las compañías
        $companies = Company::all();

        foreach ($companies as $company) {
            $this->command->info('Creando customers para: ' . $company->name);

            $customers = [
                [
                    'customer_group_id' => $customerGroup->id,
                    'name' => 'Cliente General',
                    'company_name' => 'Cliente General S.A.',
                    'email' => 'general@cliente.com',
                    'phone_number' => '+591 4 1111111',
                    'address' => 'Av. General #100',
                    'city' => 'La Paz',
                    'state' => 'La Paz',
                    'postal_code' => '0000',
                    'country' => 'Bolivia',
                    'tipo_documento' => 5, // NIT
                    'valor_documento' => '1234567890',
                    'razon_social' => 'Cliente General S.A.',
                    'is_active' => 1,
                    'company_id' => $company->id,
                ],
                [
                    'customer_group_id' => $customerGroup->id,
                    'name' => 'Juan Pérez',
                    'email' => 'juan.perez@email.com',
                    'phone_number' => '+591 4 2222222',
                    'address' => 'Calle Falsa #123',
                    'city' => 'Cochabamba',
                    'state' => 'Cochabamba',
                    'tipo_documento' => 1, // CI
                    'valor_documento' => '1234567',
                    'razon_social' => 'Juan Pérez',
                    'is_active' => 1,
                    'company_id' => $company->id,
                ],
                [
                    'customer_group_id' => $customerGroup->id,
                    'name' => 'María García',
                    'company_name' => 'Comercial García',
                    'email' => 'maria.garcia@email.com',
                    'phone_number' => '+591 3 3333333',
                    'address' => 'Av. Principal #456',
                    'city' => 'Santa Cruz',
                    'state' => 'Santa Cruz',
                    'tipo_documento' => 1, // CI
                    'valor_documento' => '7654321',
                    'razon_social' => 'Comercial García',
                    'is_active' => 1,
                    'company_id' => $company->id,
                ],
            ];

            foreach ($customers as $customerData) {
                Customer::create($customerData);
            }

            $this->command->info('3 customers creados para ' . $company->name);
        }

        $this->command->info('Customers creados exitosamente');
    }
}
