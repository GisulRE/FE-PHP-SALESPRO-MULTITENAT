<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BillersSeeder extends Seeder
{
    /**
     * Crea un Facturador por empresa, vinculado a sucursal 0 y punto de venta 0.
     */
    public function run()
    {
        try {
            if (!Schema::hasTable('billers') || !Schema::hasTable('companies')) return;

            $companies = DB::table('companies')->orderBy('id')->get();

            foreach ($companies as $company) {
                $this->crearBillerParaEmpresa($company);
            }

            $this->command->info('BillersSeeder completado.');
        } catch (\Exception $e) {
            $this->command->error('BillersSeeder Error: ' . $e->getMessage());
        }
    }

    private function crearBillerParaEmpresa(object $company): void
    {
        $billerName = 'Facturador ' . $company->name;

        if (DB::table('billers')->where('company_id', $company->id)->where('name', $billerName)->exists()) {
            $this->command->line("  Biller ya existe para [{$company->id}] {$company->name}");
            return;
        }

        // Cuentas de la empresa (fallback: cualquier cuenta del sistema)
        $accounts = Schema::hasTable('accounts')
            ? DB::table('accounts')->where('company_id', $company->id)->get()
            : collect();

        $allAccounts = ($accounts->isEmpty() && Schema::hasTable('accounts'))
            ? DB::table('accounts')->get()
            : $accounts;

        $findAcc = function (string ...$words) use ($accounts, $allAccounts) {
            // Primero buscar en cuentas de la empresa
            foreach ($words as $w) {
                $found = $accounts->first(function ($a) use ($w) {
                    return stripos($a->name, $w) !== false;
                });
                if ($found) return $found->id;
            }
            // Fallback: cualquier cuenta del sistema
            $first = $allAccounts->first();
            return $first ? $first->id : null;
        };

        $defaultAccountId = $findAcc('efectivo', 'caja', 'cash');

        // Si no hay ninguna cuenta, no podemos crear el biller
        if ($defaultAccountId === null) {
            $this->command->warn("  Sin cuentas disponibles para empresa [{$company->id}] {$company->name}, omitiendo biller.");
            return;
        }

        // Almacén de la empresa
        $warehouse = Schema::hasTable('warehouses')
            ? DB::table('warehouses')->where('company_id', $company->id)->where('is_active', true)->orderBy('id')->first()
            : null;

        // Cliente General de la empresa
        $customer = Schema::hasTable('customers')
            ? DB::table('customers')->where('company_id', $company->id)->where('name', 'Cliente General')->first()
            : null;

        $slug  = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $company->name));
        $email = 'facturador.' . $slug . '@empresa.local';

        DB::table('billers')->insert([
            'name'                             => $billerName,
            'company_name'                     => $company->name,
            'vat_number'                       => null,
            'email'                            => $email,
            'phone_number'                     => '591-2-2222222',
            'address'                          => 'Av. Principal #1',
            'city'                             => 'La Paz',
            'state'                            => 'La Paz',
            'postal_code'                      => null,
            'country'                          => 'Bolivia',
            'image'                            => null,
            'is_active'                        => true,
            'company_id'                       => $company->id,
            // SIAT: sucursal 0 y punto de venta 0
            'sucursal'                         => '0',
            'punto_venta_siat'                 => '0',
            'warehouse_id'                     => $warehouse ? $warehouse->id : null,
            'customer_id'                      => $customer ? $customer->id : null,
            'account_id'                       => $defaultAccountId,
            'account_id_tarjeta'               => $findAcc('tarjeta', 'credito', 'debito'),
            'account_id_cheque'                => $findAcc('cheque'),
            'account_id_deposito'              => $findAcc('deposito', 'qr', 'banco'),
            'account_id_qr'                    => $findAcc('qr', 'deposito'),
            'account_id_giftcard'              => $findAcc('gift', 'regalo'),
            'account_id_vale'                  => $findAcc('vale', 'voucher'),
            'account_id_otros'                 => $findAcc('otro', 'miscelaneo'),
            'account_id_pagoposterior'         => $findAcc('cobrar', 'credito', 'posterior'),
            'account_id_transferenciabancaria' => $findAcc('transferencia', 'banco'),
            'account_id_swift'                 => $findAcc('swift', 'transferencia'),
            'account_id_receivable'            => $findAcc('cobrar', 'receivable'),
            'created_at'                       => now(),
            'updated_at'                       => now(),
        ]);

        $this->command->info("  Biller '{$billerName}' creado para empresa [{$company->id}]");
    }
}
