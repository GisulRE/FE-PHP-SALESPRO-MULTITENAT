<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PuntoVentaSeeder extends Seeder
{
    private function ensureDefaultSucursal(object $company, int $usuarioAlta): int
    {
        if (!Schema::hasTable('sucursal_siat')) {
            return 0;
        }

        $sucursal = DB::table('sucursal_siat')
            ->where('sucursal', '0')
            ->where(function ($q) use ($company) {
                $q->where('id_empresa', $company->id);
                if (Schema::hasColumn('sucursal_siat', 'company_id')) {
                    $q->orWhere('company_id', $company->id);
                }
            })
            ->first();

        if ($sucursal) {
            return (int) $sucursal->sucursal;
        }

        $insertSucursal = [
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
            'usuario_alta'                => $usuarioAlta,
            'id_empresa'                  => $company->id,
            'created_at'                  => now(),
            'updated_at'                  => now(),
        ];

        if (Schema::hasColumn('sucursal_siat', 'company_id')) {
            $insertSucursal['company_id'] = $company->id;
        }

        DB::table('sucursal_siat')->insert($insertSucursal);

        return 0;
    }

    /**
     * Crea un Punto de Venta principal (código 0, sucursal 0) por cada empresa.
     * Tabla: puntos_venta
     */
    public function run()
    {
        try {
            if (!Schema::hasTable('puntos_venta')) {
                $this->command->warn('PuntoVentaSeeder: tabla puntos_venta no existe, omitiendo.');
                return;
            }

            $companies   = DB::table('companies')->orderBy('id')->get();
            $adminUser   = DB::table('users')->orderBy('id')->first();
            $usuarioAlta = $adminUser ? $adminUser->id : 1;

            if ($companies->isEmpty()) {
                $this->command->warn('PuntoVentaSeeder: no hay empresas registradas.');
                return;
            }

            foreach ($companies as $company) {
                $sucursalCode = $this->ensureDefaultSucursal($company, $usuarioAlta);

                $existsQuery = DB::table('puntos_venta')
                    ->where('sucursal', $sucursalCode)
                    ->where('codigo_punto_venta', '0');

                if (Schema::hasColumn('puntos_venta', 'company_id')) {
                    $existsQuery->where('company_id', $company->id);
                } else {
                    $existsQuery->where('id_empresa', $company->id);
                }

                $exists = $existsQuery->exists();

                if (!$exists) {
                    $insertData = [
                        'codigo_punto_venta'            => '0',
                        'nombre_punto_venta'            => 'Punto de Venta Principal',
                        'descripcion'                   => 'PV Principal - ' . $company->name,
                        'tipo_punto_venta'              => '0',
                        'codigo_cuis'                   => 'CUIS-PENDIENTE',
                        'fecha_vigencia_cuis'           => now()->addYear(),
                        'usuario_alta'                  => $usuarioAlta,
                        'id_empresa'                    => $company->id,
                        'sucursal'                      => $sucursalCode,
                        'correlativo_factura'           => 1,
                        'correlativo_alquiler'          => 1,
                        'correlativo_servicios_basicos' => 1,
                        'correlativo_nota_debcred'      => 1,
                        'modo_contingencia'             => 0,
                        'fecha_inicio'                  => now()->toDateString(),
                        'fecha_fin'                     => null,
                        'nit_comisionista'              => null,
                        'numero_contrato'               => null,
                        'is_siat'                       => 1,
                        'is_active'                     => 1,
                        'created_at'                    => now(),
                        'updated_at'                    => now(),
                    ];

                    if (Schema::hasColumn('puntos_venta', 'company_id')) {
                        $insertData['company_id'] = $company->id;
                    }

                    DB::table('puntos_venta')->insert($insertData);
                    $this->command->info("  Punto de Venta 0 creado para [{$company->id}] {$company->name}");
                } else {
                    if (Schema::hasColumn('puntos_venta', 'company_id')) {
                        DB::table('puntos_venta')
                            ->where('sucursal', $sucursalCode)
                            ->where('codigo_punto_venta', '0')
                            ->where(function ($q) use ($company) {
                                $q->where('id_empresa', $company->id)
                                  ->orWhere('company_id', $company->id);
                            })
                            ->whereNull('company_id')
                            ->update(['company_id' => $company->id, 'updated_at' => now()]);
                    }
                    $this->command->line("  Punto de Venta 0 ya existe para [{$company->id}] {$company->name}");
                }
            }

            $this->command->info('PuntoVentaSeeder completado.');
        } catch (\Exception $e) {
            $this->command->error('PuntoVentaSeeder Error: ' . $e->getMessage());
        }
    }
}
