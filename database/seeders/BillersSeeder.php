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

        $defaultAccountId = $this->ensureDefaultAccount($company);
        $warehouseId = $this->ensureDefaultWarehouse($company);
        $customerId = $this->ensureDefaultCustomer($company);

        if ($defaultAccountId === null || $warehouseId === null || $customerId === null) {
            $this->command->warn("  No se pudo garantizar data minima para biller en [{$company->id}] {$company->name}, omitiendo.");
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

        $defaultAccountId = $findAcc('efectivo', 'caja', 'cash') ?: $defaultAccountId;

        // Si no hay ninguna cuenta, no podemos crear el biller
        if ($defaultAccountId === null) {
            $this->command->warn("  Sin cuentas disponibles para empresa [{$company->id}] {$company->name}, omitiendo biller.");
            return;
        }

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
            'warehouse_id'                     => $warehouseId,
            'customer_id'                      => $customerId,
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

    private function ensureDefaultAccount(object $company): ?int
    {
        if (!Schema::hasTable('accounts')) {
            return null;
        }

        $query = DB::table('accounts')->where('name', 'Caja Principal');
        if (Schema::hasColumn('accounts', 'company_id')) {
            $query->where('company_id', $company->id);
        }
        $exists = $query->first();
        if ($exists) {
            return $exists->id;
        }

        $data = [
            'account_no' => 'ACCT-' . $company->id . '-1',
            'name' => 'Caja Principal',
            'initial_balance' => 0,
            'total_balance' => 0,
            'note' => 'Cuenta por defecto para facturador',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        if (Schema::hasColumn('accounts', 'company_id')) {
            $data['company_id'] = $company->id;
        }
        if (Schema::hasColumn('accounts', 'is_default')) {
            $data['is_default'] = 1;
        }
        if (Schema::hasColumn('accounts', 'type')) {
            $data['type'] = 1;
        }

        return DB::table('accounts')->insertGetId($data);
    }

    private function ensureDefaultWarehouse(object $company): ?int
    {
        if (!Schema::hasTable('warehouses')) {
            return null;
        }

        $query = DB::table('warehouses')->where('name', 'Almacen Principal');
        if (Schema::hasColumn('warehouses', 'company_id')) {
            $query->where('company_id', $company->id);
        }
        $exists = $query->first();
        if ($exists) {
            return $exists->id;
        }

        $data = [
            'name' => 'Almacen Principal',
            'phone' => '591-2-2222222',
            'email' => 'principal@empresa.com',
            'address' => 'Av. Principal #123',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        if (Schema::hasColumn('warehouses', 'company_id')) {
            $data['company_id'] = $company->id;
        }
        // intentar vincular la sucursal por defecto (sucursal '0') si existe
        if (Schema::hasTable('sucursal_siat')) {
            $sq = DB::table('sucursal_siat')->where('sucursal', '0');
            if (Schema::hasColumn('sucursal_siat', 'company_id')) {
                $sq->where('company_id', $company->id);
            } else {
                $sq->where('id_empresa', $company->id);
            }
            $s = $sq->first();
            $defaultSucursalId = $s->id ?? null;
            $defaultSucursalCode = $s ? '0' : null;
        } else {
            $defaultSucursalId = null;
            $defaultSucursalCode = null;
        }

        if (Schema::hasColumn('warehouses', 'sucursal_id')) {
            if ($defaultSucursalId) {
                $data['sucursal_id'] = $defaultSucursalId;
            }
        }
        if (Schema::hasColumn('warehouses', 'sucursal_siat')) {
            if ($defaultSucursalCode !== null) {
                $data['sucursal_siat'] = $defaultSucursalCode;
            }
        }

        return DB::table('warehouses')->insertGetId($data);
    }

    private function ensureDefaultCustomer(object $company): ?int
    {
        if (!Schema::hasTable('customers')) {
            return null;
        }

        $query = DB::table('customers')->where('name', 'Cliente General');
        if (Schema::hasColumn('customers', 'company_id')) {
            $query->where('company_id', $company->id);
        }
        $exists = $query->first();
        if ($exists) {
            return $exists->id;
        }

        $groupId = 1;
        if (Schema::hasTable('customer_groups')) {
            $group = DB::table('customer_groups')->orderBy('id')->first();
            if ($group) {
                $groupId = $group->id;
            } else {
                $groupId = DB::table('customer_groups')->insertGetId([
                    'name' => 'General',
                    'percentage' => 0,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $data = [
            'name' => 'Cliente General',
            'phone_number' => '00000000',
            'address' => 'N/A',
            'city' => 'N/A',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        if (Schema::hasColumn('customers', 'customer_group_id')) {
            $data['customer_group_id'] = $groupId;
        }
        if (Schema::hasColumn('customers', 'company_name')) {
            $data['company_name'] = 'Cliente General';
        }
        if (Schema::hasColumn('customers', 'email')) {
            $data['email'] = 'cliente.general@empresa.local';
        }
        if (Schema::hasColumn('customers', 'state')) {
            $data['state'] = 'N/A';
        }
        if (Schema::hasColumn('customers', 'postal_code')) {
            $data['postal_code'] = '0000';
        }
        if (Schema::hasColumn('customers', 'country')) {
            $data['country'] = 'Bolivia';
        }
        if (Schema::hasColumn('customers', 'tipo_documento')) {
            $data['tipo_documento'] = 5;
        }
        if (Schema::hasColumn('customers', 'valor_documento')) {
            $data['valor_documento'] = '0';
        }
        if (Schema::hasColumn('customers', 'razon_social')) {
            $data['razon_social'] = 'Cliente General';
        }
        if (Schema::hasColumn('customers', 'company_id')) {
            $data['company_id'] = $company->id;
        }

        return DB::table('customers')->insertGetId($data);
    }
}
