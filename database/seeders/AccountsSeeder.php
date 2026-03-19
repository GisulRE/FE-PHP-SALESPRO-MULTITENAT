<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AccountsSeeder extends Seeder
{
    public function run()
    {
        try {
            if (!Schema::hasTable('accounts')) {
                $this->command->warn('AccountsSeeder: tabla accounts no existe, omitiendo.');
                return;
            }

            // If companies table exists, create one account per company
            if (Schema::hasTable('companies')) {
                $companies = DB::table('companies')->orderBy('id')->get();
                foreach ($companies as $company) {
                    $exists = DB::table('accounts')
                        ->where('company_id', $company->id)
                        ->where(function ($q) {
                            $q->where('name', 'Caja Principal')->orWhere('name', 'Efectivo');
                        })->exists();

                    if ($exists) continue;

                    DB::table('accounts')->insert([
                        'account_no'    => 'ACCT-' . $company->id . '-1',
                        'name'          => 'Caja Principal',
                        'initial_balance'=> 0,
                        'total_balance' => 0,
                        'note'          => 'Cuenta creada por seeder',
                        'is_default'    => true,
                        'is_active'     => true,
                        'type'          => 'cash',
                        'company_id'    => $company->id,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                    $this->command->info("  Cuenta 'Caja Principal' creada para empresa [{$company->id}] {$company->name}");
                }
                return;
            }

            // Fallback: crear una cuenta global si no hay companies
            $exists = DB::table('accounts')->where('name', 'Caja Principal')->exists();
            if (!$exists) {
                DB::table('accounts')->insert([
                    'account_no'    => 'ACCT-1',
                    'name'          => 'Caja Principal',
                    'initial_balance'=> 0,
                    'total_balance' => 0,
                    'note'          => 'Cuenta global creada por seeder',
                    'is_default'    => true,
                    'is_active'     => true,
                    'type'          => 'cash',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
                $this->command->info("  Cuenta 'Caja Principal' global creada.");
            }
        } catch (\Exception $e) {
            $this->command->error('AccountsSeeder Error: ' . $e->getMessage());
        }
    }
}
