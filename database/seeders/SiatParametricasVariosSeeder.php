<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SiatParametricasVariosSeeder extends Seeder
{
    /**
     * Catalogo completo de parametricas SIAT (tipoDocumentoIdentidad, tipoMoneda,
     * tipoMetodoPago, unidadMedida, etc.), reconstruido a partir de los backups
     * en CSV ubicados en la raiz del proyecto.
     */
    private const SOURCE_FILES = [
        'backup_invoices.csv',
        'data-1783175915106.csv',
    ];

    public function run()
    {
        if (!Schema::hasTable('siat_parametricas_varios')) {
            return;
        }

        $rows = [];
        foreach (self::SOURCE_FILES as $file) {
            $this->readCsv(base_path($file), $rows);
        }

        if (empty($rows)) {
            $this->command->warn('No se encontraron filas validas en los CSV de parametricas SIAT.');
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('TRUNCATE TABLE siat_parametricas_varios RESTART IDENTITY');
        } else {
            DB::table('siat_parametricas_varios')->delete();
        }

        foreach (array_chunk(array_values($rows), 200) as $chunk) {
            DB::table('siat_parametricas_varios')->insert($chunk);
        }

        if ($driver === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('siat_parametricas_varios', 'id'), COALESCE((SELECT MAX(id) FROM siat_parametricas_varios), 1), true)");
        }

        $this->command->info(count($rows) . ' registros de parametricas SIAT importados.');
    }

    /**
     * Lee un CSV (con o sin cabecera) con columnas:
     * id, codigo_clasificador, descripcion, tipo_clasificador, datos,
     * usuario_alta, usuario_modificacion, id_empresa, estado, sucursal,
     * codigo_punto_venta, created_at, updated_at, deleted_at
     *
     * Se deduplica por (tipo_clasificador, codigo_clasificador), quedandose
     * con la version mas reciente cuando el mismo dato aparece en varios backups.
     */
    private function readCsv(string $path, array &$rows): void
    {
        if (!is_file($path)) {
            return;
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return;
        }

        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) < 13) {
                continue;
            }

            // Salta la fila de cabecera si el CSV la trae.
            if (!is_numeric($data[0])) {
                continue;
            }

            [$id, $codigo_clasificador, $descripcion, $tipo_clasificador, $datos,
             $usuario_alta, $usuario_modificacion, $id_empresa, $estado, $sucursal,
             $codigo_punto_venta, $created_at, $updated_at] = array_pad($data, 13, null);

            if ($tipo_clasificador === null || $tipo_clasificador === '') {
                continue;
            }

            $key = $tipo_clasificador . '|' . $codigo_clasificador;

            if (!isset($rows[$key]) || strtotime((string) $created_at) >= strtotime((string) $rows[$key]['created_at'])) {
                $rows[$key] = [
                    'codigo_clasificador' => $codigo_clasificador,
                    'descripcion' => $descripcion,
                    'tipo_clasificador' => $tipo_clasificador,
                    'datos' => ($datos === '' || $datos === 'NULL') ? null : $datos,
                    'usuario_alta' => is_numeric($usuario_alta) ? (int) $usuario_alta : null,
                    'usuario_modificacion' => is_numeric($usuario_modificacion) ? (int) $usuario_modificacion : null,
                    'id_empresa' => is_numeric($id_empresa) ? (int) $id_empresa : null,
                    'estado' => is_numeric($estado) ? (int) $estado : 1,
                    'sucursal' => ($sucursal === '' || $sucursal === null) ? '0' : $sucursal,
                    'codigo_punto_venta' => ($codigo_punto_venta === '' || $codigo_punto_venta === null) ? '0' : $codigo_punto_venta,
                    'created_at' => $created_at ?: now(),
                    'updated_at' => $updated_at ?: now(),
                ];
            }
        }

        fclose($handle);
    }
}
