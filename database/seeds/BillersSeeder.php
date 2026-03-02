<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BillersSeeder extends Seeder
{
    /**
     * Crea un facturador por empresa, vinculado a la sucursal 0 y punto de venta 0.
     * Intenta asignar cuentas contables por nombre si existen.
     */
    public function run()
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('billers')) {
                echo "BillersSeeder: tabla billers no existe, omitiendo." . PHP_EOL;
                return;
            }
            if (!DB::getSchemaBuilder()->hasTable('companies')) {
                echo "BillersSeeder: tabla companies no existe, omitiendo." . PHP_EOL;
                return;
            }

            $companies    = DB::table('companies')->orderBy('id')->get();
            $hasAccounts  = DB::getSchemaBuilder()->hasTable('accounts');
            $hasWarehouses = DB::getSchemaBuilder()->hasTable('warehouses');

            foreach ($companies as $company) {
                $exists = DB::table('billers')
                    ->where('company_id', $company->id)
                    ->where('name', 'Facturador ' . $company->name)
                    ->exists();

                if ($exists) {
                    echo "BillersSeeder: ya existe facturador para empresa [{$company->id}] {$company->name}" . PHP_EOL;
                    continue;
                }

                // Buscar cuentas de la empresa por nombre (parcial)
                $accounts        = $hasAccounts
                    ? DB::table('accounts')->where('company_id', $company->id)->get()
                    : collect();

                $findAccount = function (string ...$keywords) use ($accounts) {
                    foreach ($keywords as $kw) {
                        $found = $accounts->first(function ($a) use ($kw) {
                            return stripos($a->name, $kw) !== false;
                        });
                        if ($found) return $found->id;
                    }
                    // Fallback: primera cuenta de la empresa
                    return $accounts->first() ? $accounts->first()->id : null;
                };

                $accountId               = $findAccount('efectivo', 'caja', 'cash');
                $accountIdTarjeta        = $findAccount('tarjeta', 'credito', 'debito');
                $accountIdCheque         = $findAccount('cheque');
                $accountIdDeposito       = $findAccount('deposito', 'qr', 'banco');
                $accountIdQr             = $findAccount('qr', 'deposito');
                $accountIdGiftcard       = $findAccount('gift', 'regalo');
                $accountIdVale           = $findAccount('vale', 'voucher');
                $accountIdOtros          = $findAccount('otro', 'miscelaneo');
                $accountIdPagoposterior  = $findAccount('cobrar', 'credito', 'posterior');
                $accountIdTransferencia  = $findAccount('transferencia', 'banco');
                $accountIdSwift          = $findAccount('swift', 'transferencia');
                $accountIdReceivable     = $findAccount('cobrar', 'receivable');

                // Almacén por defecto de la empresa
                $warehouseId = null;
                if ($hasWarehouses) {
                    $wh = DB::table('warehouses')
                        ->where('company_id', $company->id)
                        ->where('is_active', true)
                        ->orderBy('id')
                        ->first();
                    $warehouseId = $wh ? $wh->id : null;
                }

                // Cliente general de la empresa
                $defaultCustomer = DB::getSchemaBuilder()->hasTable('customers')
                    ? DB::table('customers')
                        ->where('company_id', $company->id)
                        ->where('name', 'Cliente General')
                        ->first()
                    : null;
                $customerId = $defaultCustomer ? $defaultCustomer->id : null;

                DB::table('billers')->insert([
                    'name'                          => 'Facturador ' . $company->name,
                    'company_name'                  => $company->name,
                    'vat_number'                    => null,
                    'email'                         => null,
                    'phone_number'                  => '591-2-2222222',
                    'address'                       => 'Av. Principal #1',
                    'city'                          => 'La Paz',
                    'state'                         => 'La Paz',
                    'postal_code'                   => null,
                    'country'                       => 'Bolivia',
                    'image'                         => null,
                    'is_active'                     => true,
                    'company_id'                    => $company->id,
                    'sucursal'                      => '0',
                    'punto_venta_siat'              => '0',
                    'warehouse_id'                  => $warehouseId,
                    'customer_id'                   => $customerId,
                    'account_id'                    => $accountId,
                    'account_id_tarjeta'            => $accountIdTarjeta,
                    'account_id_cheque'             => $accountIdCheque,
                    'account_id_deposito'           => $accountIdDeposito,
                    'account_id_qr'                 => $accountIdQr,
                    'account_id_giftcard'           => $accountIdGiftcard,
                    'account_id_vale'               => $accountIdVale,
                    'account_id_otros'              => $accountIdOtros,
                    'account_id_pagoposterior'      => $accountIdPagoposterior,
                    'account_id_transferenciabancaria' => $accountIdTransferencia,
                    'account_id_swift'              => $accountIdSwift,
                    'account_id_receivable'         => $accountIdReceivable,
                    'created_at'                    => now(),
                    'updated_at'                    => now(),
                ]);
                echo "BillersSeeder: creado facturador para empresa [{$company->id}] {$company->name}" . PHP_EOL;
            }
        } catch (\Exception $e) {
            echo "BillersSeeder Error: " . $e->getMessage() . PHP_EOL;
        }
    }
}
