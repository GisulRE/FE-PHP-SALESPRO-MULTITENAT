<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Unit;
use App\Company;
use Illuminate\Support\Facades\DB;

class UnitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Creando unidades para cada compañía...');

        if (DB::getDriverName() === 'pgsql') {
            // Evita colisiones de PK cuando la secuencia quedó desfasada por inserts previos.
            DB::statement("SELECT setval(pg_get_serial_sequence('units','id'), COALESCE((SELECT MAX(id) FROM units), 1), true)");
        }

        // Obtener todas las compañías
        $companies = Company::all();

        $baseUnits = [
            [
                'unit_code' => 'UNI',
                'unit_name' => 'Unidad',
                'base_unit' => null,
                'operator' => '*',
                'operation_value' => 1,
                'is_active' => 1,
            ],
            [
                'unit_code' => 'KG',
                'unit_name' => 'Kilogramo',
                'base_unit' => null,
                'operator' => '*',
                'operation_value' => 1,
                'is_active' => 1,
            ],
            [
                'unit_code' => 'LT',
                'unit_name' => 'Litro',
                'base_unit' => null,
                'operator' => '*',
                'operation_value' => 1,
                'is_active' => 1,
            ],
            [
                'unit_code' => 'MTS',
                'unit_name' => 'Metro',
                'base_unit' => null,
                'operator' => '*',
                'operation_value' => 1,
                'is_active' => 1,
            ],
        ];

        foreach ($companies as $company) {
            $this->command->info('Creando unidades para: ' . $company->name);

            foreach ($baseUnits as $unitData) {
                // Verificar si ya existe esta unidad para esta compañía
                $exists = Unit::withoutGlobalScope('company')
                    ->where('company_id', $company->id)
                    ->where('unit_code', $unitData['unit_code'])
                    ->exists();

                if (!$exists) {
                    $unitData['company_id'] = $company->id;
                    Unit::create($unitData);
                    $this->command->info('  - ' . $unitData['unit_name'] . ' creada');
                }
            }
        }

        $this->command->info('Unidades creadas exitosamente');
    }
}
