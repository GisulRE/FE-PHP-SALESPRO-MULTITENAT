<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PuntoVentaSeeder extends Seeder
{
    /**
     * Crea un punto de venta principal (código 0, sucursal 0) por cada empresa.
     */
    public function run()
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('puntos_venta')) {
                echo "PuntoVentaSeeder: tabla puntos_venta no existe, omitiendo." . PHP_EOL;
                return;
            }
            if (!DB::getSchemaBuilder()->hasTable('companies')) {
                echo "PuntoVentaSeeder: tabla companies no existe, omitiendo." . PHP_EOL;
                return;
            }

            $companies   = DB::table('companies')->orderBy('id')->get();
            $adminUser   = DB::table('users')->orderBy('id')->first();
            $usuarioAlta = $adminUser ? $adminUser->id : 1;

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
                    echo "PuntoVentaSeeder: creado PV 0/sucursal 0 para empresa [{$company->id}] {$company->name}" . PHP_EOL;
                } else {
                    echo "PuntoVentaSeeder: ya existe PV 0 para empresa [{$company->id}] {$company->name}" . PHP_EOL;
                }
            }
        } catch (\Exception $e) {
            echo "PuntoVentaSeeder Error: " . $e->getMessage() . PHP_EOL;
        }
    }
}
