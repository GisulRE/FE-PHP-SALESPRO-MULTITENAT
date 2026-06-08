<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
            
            $hasSucursalTable = Schema::hasTable('sucursal_siat');
            $hasSucursalCompanyIdColumn = $hasSucursalTable && Schema::hasColumn('sucursal_siat', 'company_id');

            foreach ($companies as $company) {
                // resolver sucursal por defecto si existe la tabla
                $defaultSucursalId = null;
                $defaultSucursalCode = null;
                if ($hasSucursalTable) {
                    $q = DB::table('sucursal_siat')->where('sucursal', '0');
                    if ($hasSucursalCompanyIdColumn) {
                        $q->where('company_id', $company->id);
                    } else {
                        $q->where('id_empresa', $company->id);
                    }
                    $s = $q->first();
                    if ($s) {
                        $defaultSucursalId = $s->id;
                        $defaultSucursalCode = '0';
                    }
                }

                foreach ($warehouses as $index => $warehouse) {
                    // Verificar si ya existe un warehouse similar para esta company
                    $exists = DB::table('warehouses')
                        ->where('company_id', $company->id)
                        ->where('name', $warehouse['name'])
                        ->exists();
                    
                    if (!$exists) {
                        $insert = [
                            'company_id'    => $company->id,
                            'name'          => $warehouse['name'],
                            'phone'         => $warehouse['phone'],
                            'email'         => $warehouse['email'],
                            'address'       => $warehouse['address'],
                            'is_active'     => $warehouse['is_active'],
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ];

                        if (Schema::hasColumn('warehouses', 'sucursal_id') && $defaultSucursalId) {
                            $insert['sucursal_id'] = $defaultSucursalId;
                        }
                        if (Schema::hasColumn('warehouses', 'sucursal_siat') && $defaultSucursalCode !== null) {
                            $insert['sucursal_siat'] = $defaultSucursalCode;
                        }

                        DB::table('warehouses')->insert($insert);
                    }
                }

                // backfill any remaining null sucursal fields for this company
                if ($defaultSucursalId) {
                    $backfill = DB::table('warehouses');
                    if (Schema::hasColumn('warehouses', 'company_id')) {
                        $backfill->where('company_id', $company->id);
                    }
                    $backfill->where(function($q) {
                        $q->whereNull('sucursal_id')->orWhereNull('sucursal_siat');
                    })->update([
                        'sucursal_id' => $defaultSucursalId,
                        'sucursal_siat' => $defaultSucursalCode,
                        'updated_at' => now(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            // ignore if table or columns don't exist yet
            echo "Error: " . $e->getMessage() . PHP_EOL;
        }
    }
}
