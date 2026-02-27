<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Biller;
use App\Warehouse;
use App\Account;
use App\Customer;

class BillersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Verificar si ya existen billers
        if (Biller::count() > 0) {
            $this->command->info('Billers ya existen, omitiendo seeder...');
            return;
        }

        // Obtener primer warehouse, account y customer para usar como referencia
        $warehouse = Warehouse::first();
        $account = Account::first();
        $customer = Customer::first();

        if (!$warehouse || !$account || !$customer) {
            $this->command->error('Se requieren registros en warehouses, accounts y customers antes de crear billers');
            return;
        }

        $billers = [
            [
                'name' => 'Biller Principal',
                'company_name' => 'SalesPro Principal S.A.',
                'vat_number' => '1234567890',
                'email' => 'biller1@salespro.com',
                'phone_number' => '+591 4 4444444',
                'address' => 'Av. Principal #123',
                'city' => 'La Paz',
                'state' => 'La Paz',
                'postal_code' => '0000',
                'country' => 'Bolivia',
                'account_id' => $account->id,
                'account_id_receivable' => $account->id,
                'warehouse_id' => $warehouse->id,
                'customer_id' => $customer->id,
                'is_active' => 1,
            ],
            [
                'name' => 'Biller Secundario',
                'company_name' => 'SalesPro Secundario S.R.L.',
                'vat_number' => '0987654321',
                'email' => 'biller2@salespro.com',
                'phone_number' => '+591 3 3333333',
                'address' => 'Calle Secundaria #456',
                'city' => 'Santa Cruz',
                'state' => 'Santa Cruz',
                'postal_code' => '0000',
                'country' => 'Bolivia',
                'account_id' => $account->id,
                'account_id_receivable' => $account->id,
                'warehouse_id' => $warehouse->id,
                'customer_id' => $customer->id,
                'is_active' => 1,
            ],
        ];

        foreach ($billers as $billerData) {
            Biller::create($billerData);
            $this->command->info('Biller creado: ' . $billerData['name']);
        }

        $this->command->info('Billers creados exitosamente');
    }
}
