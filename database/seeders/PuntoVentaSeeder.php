<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PuntoVentaSeeder extends Seeder
{
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
                $exists = DB::table('puntos_venta')
                    ->where('id_empresa', $company->id)
                    ->where('sucursal', 0)
                    ->where('codigo_punto_venta', '0')
                    ->exists();

                if (!$exists) {
                    DB::table('puntos_venta')->insert([
                        'codigo_punto_venta'            => '0',
                        'nombre_punto_venta'            => 'Punto de Venta Principal',
                        'descripcion'                   => 'PV Principal - ' . $company->name,
                        'tipo_punto_venta'              => '0',
                        'codigo_cuis'                   => 'CUIS-PENDIENTE',
                        'fecha_vigencia_cuis'           => now()->addYear(),
                        'usuario_alta'                  => $usuarioAlta,
                        'id_empresa'                    => $company->id,
                        'sucursal'                      => 0,
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
                    ]);
                    $this->command->info("  Punto de Venta 0 creado para [{$company->id}] {$company->name}");
                } else {
                    $this->command->line("  Punto de Venta 0 ya existe para [{$company->id}] {$company->name}");
                }
            }

            $this->command->info('PuntoVentaSeeder completado.');
        } catch (\Exception $e) {
            $this->command->error('PuntoVentaSeeder Error: ' . $e->getMessage());
        }
    }
}
