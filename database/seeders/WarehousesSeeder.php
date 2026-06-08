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

            $hasSucursalTable = Schema::hasTable('sucursal_siat');
            $defaultUserId = DB::table('users')->orderBy('id')->value('id') ?? 1;
            $hasSucursalCompanyIdColumn = $hasSucursalTable && Schema::hasColumn('sucursal_siat', 'company_id');

            $resolveDefaultSucursal = function ($company) use ($hasSucursalTable, $defaultUserId, $hasSucursalCompanyIdColumn) {
                if (!$hasSucursalTable) {
                    return [null, null];
                }

                $defaultSucursalQuery = DB::table('sucursal_siat')
                    ->where('sucursal', '0');

                if ($hasSucursalCompanyIdColumn) {
                    $defaultSucursalQuery->where('company_id', $company->id);
                } else {
                    $defaultSucursalQuery->where('id_empresa', $company->id);
                }

                $defaultSucursal = $defaultSucursalQuery->first();

                if (!$defaultSucursal) {
                    $insertData = [
                        'sucursal'                    => '0',
                        'nombre'                      => 'CASA MATRIZ - ' . strtoupper($company->name),
                        'descripcion_sucursal'        => 'Casa Matriz de ' . $company->name,
                        'domicilio_tributario'        => 'Av. Principal #1',
                        'ciudad_municipio'            => 'La Paz',
                        'telefono'                    => '591-2-2222222',
                        'email'                       => null,
                        'id_autorizacion_facturacion' => null,
                        'departamento'                => 'La Paz',
                        'estado'                      => 'ACTIVO',
                        'usuario_alta'                => $defaultUserId,
                        'id_empresa'                  => $company->id,
                        'created_at'                  => now(),
                        'updated_at'                  => now(),
                    ];

                    if ($hasSucursalCompanyIdColumn) {
                        $insertData['company_id'] = $company->id;
                    }

                    $newId = DB::table('sucursal_siat')->insertGetId($insertData);

                    return [$newId, '0'];
                }

                if ($hasSucursalCompanyIdColumn && empty($defaultSucursal->company_id)) {
                    DB::table('sucursal_siat')
                        ->where('id', $defaultSucursal->id)
                        ->update([
                            'company_id' => $company->id,
                            'updated_at' => now(),
                        ]);
                }

                return [$defaultSucursal->id, '0'];
            };
            
            $warehouses = [
                [
                    'name' => 'Almacen Principal',
                    'phone' => '591-2-2222222',
                    'email' => 'principal@empresa.com',
                    'address' => 'Av. Principal #123',
                    'is_active' => true,
                ],
            ];
            
            foreach ($companies as $company) {
                [$defaultSucursalId, $defaultSucursalCode] = $resolveDefaultSucursal($company);

                if ($defaultSucursalId) {
                    $warehouseBackfill = DB::table('warehouses');

                    if (Schema::hasColumn('warehouses', 'company_id')) {
                        $warehouseBackfill->where('company_id', $company->id);
                    }

                    $warehouseBackfill
                        ->where(function ($query) {
                            $query->whereNull('sucursal_id')
                                ->orWhereNull('sucursal_siat');
                        })
                        ->update([
                            'sucursal_id'   => $defaultSucursalId,
                            'sucursal_siat' => $defaultSucursalCode,
                            'updated_at'    => now(),
                        ]);
                }

                foreach ($warehouses as $index => $warehouse) {
                    // Verificar si ya existe un warehouse similar para esta company
                    $exists = DB::table('warehouses')
                        ->where('company_id', $company->id)
                        ->where('name', $warehouse['name'])
                        ->first();
                    
                    if (!$exists) {
                        DB::table('warehouses')->insert([
                            'company_id'    => $company->id,
                            'name'          => $warehouse['name'],
                            'phone'         => $warehouse['phone'],
                            'email'         => $warehouse['email'],
                            'address'       => $warehouse['address'],
                            'is_active'     => $warehouse['is_active'],
                            'sucursal_id'   => $defaultSucursalId,
                            'sucursal_siat' => $defaultSucursalCode,
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ]);
                    } elseif ($defaultSucursalId && (empty($exists->sucursal_id) || empty($exists->sucursal_siat))) {
                        DB::table('warehouses')
                            ->where('id', $exists->id)
                            ->update([
                                'sucursal_id'   => $defaultSucursalId,
                                'sucursal_siat' => $defaultSucursalCode,
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
