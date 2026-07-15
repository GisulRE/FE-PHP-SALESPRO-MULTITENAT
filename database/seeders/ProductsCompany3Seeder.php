<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductsCompany3Seeder extends Seeder
{
    private const COMPANY_ID = 1;

    public function run(): void
    {
        $companyExists = DB::table('companies')->where('id', self::COMPANY_ID)->exists();
        if (!$companyExists) {
            $this->command->error('La company_id ' . self::COMPANY_ID . ' no existe en la tabla companies. Abortando.');
            return;
        }

        $this->command->info('Sembrando datos para company_id ' . self::COMPANY_ID . '...');

        if (DB::getDriverName() === 'pgsql') {
            foreach (['units', 'taxes', 'categories', 'brands', 'products'] as $table) {
                DB::statement("SELECT setval(pg_get_serial_sequence('{$table}','id'), COALESCE((SELECT MAX(id) FROM {$table}), 1), true)");
            }
        }

        $unitId = $this->ensureUnit('UNI', 'Unidad');
        $taxId  = $this->ensureTax('IVA 13%', 13);

        $catServicios    = $this->ensureCategory('Servicios IT');
        $catSoftware     = $this->ensureCategory('Software y Licencias');
        $catCapacitacion = $this->ensureCategory('Capacitación');
        $catDiseno       = $this->ensureCategory('Diseño y Creatividad');

        $brandMicrosoft = $this->ensureBrand('Microsoft');
        $brandAdobe     = $this->ensureBrand('Adobe');
        $brandGoogle    = $this->ensureBrand('Google');
        $brandGenerico  = $this->ensureBrand('Genérico');

        $products = [
            [
                'code'            => 'C3-SRV-CONSUL-001',
                'name'            => 'Consultoría IT por hora',
                'type'            => 'digital',
                'category_id'     => $catServicios,
                'brand_id'        => $brandGenerico,
                'unit_id'         => $unitId,
                'cost'            => 80.00,
                'price'           => 150.00,
                'price_a'         => 145.00,
                'price_b'         => 135.00,
                'price_c'         => 120.00,
                'qty'             => 0,
                'alert_quantity'  => 0,
                'product_details' => 'Servicio de consultoría tecnológica especializada, facturado por hora',
                'featured'        => 1,
                'is_basicservice' => 1,
            ],
            [
                'code'            => 'C3-SRV-SOPORTE-001',
                'name'            => 'Soporte Técnico Remoto',
                'type'            => 'digital',
                'category_id'     => $catServicios,
                'brand_id'        => $brandGenerico,
                'unit_id'         => $unitId,
                'cost'            => 40.00,
                'price'           => 90.00,
                'price_a'         => 85.00,
                'price_b'         => 80.00,
                'price_c'         => 70.00,
                'qty'             => 0,
                'alert_quantity'  => 0,
                'product_details' => 'Atención remota para resolución de incidencias técnicas, por sesión',
                'featured'        => 0,
                'is_basicservice' => 1,
            ],
            [
                'code'            => 'C3-SRV-MANT-001',
                'name'            => 'Mantenimiento Preventivo PC',
                'type'            => 'digital',
                'category_id'     => $catServicios,
                'brand_id'        => $brandGenerico,
                'unit_id'         => $unitId,
                'cost'            => 50.00,
                'price'           => 100.00,
                'price_a'         => 95.00,
                'price_b'         => 90.00,
                'price_c'         => 85.00,
                'qty'             => 0,
                'alert_quantity'  => 0,
                'product_details' => 'Limpieza física, actualización de drivers y optimización del sistema operativo',
                'featured'        => 0,
                'is_basicservice' => 1,
            ],
            [
                'code'            => 'C3-SRV-AUDIT-001',
                'name'            => 'Auditoría de Seguridad Web',
                'type'            => 'digital',
                'category_id'     => $catServicios,
                'brand_id'        => $brandGenerico,
                'unit_id'         => $unitId,
                'cost'            => 200.00,
                'price'           => 450.00,
                'price_a'         => 430.00,
                'price_b'         => 400.00,
                'price_c'         => 380.00,
                'qty'             => 0,
                'alert_quantity'  => 0,
                'product_details' => 'Análisis de vulnerabilidades, reporte ejecutivo y recomendaciones de seguridad',
                'featured'        => 1,
                'is_basicservice' => 0,
            ],
            [
                'code'            => 'C3-DIG-ANTIVIRUS-001',
                'name'            => 'Licencia Antivirus Anual',
                'type'            => 'digital',
                'category_id'     => $catSoftware,
                'brand_id'        => $brandGenerico,
                'unit_id'         => $unitId,
                'cost'            => 30.00,
                'price'           => 65.00,
                'price_a'         => 62.00,
                'price_b'         => 58.00,
                'price_c'         => 55.00,
                'qty'             => 0,
                'alert_quantity'  => 0,
                'product_details' => 'Licencia digital de antivirus por 1 año, 1 dispositivo, entrega por correo',
                'featured'        => 0,
                'is_basicservice' => 0,
            ],
            [
                'code'            => 'C3-DIG-M365-001',
                'name'            => 'Microsoft 365 Personal 1 año',
                'type'            => 'digital',
                'category_id'     => $catSoftware,
                'brand_id'        => $brandMicrosoft,
                'unit_id'         => $unitId,
                'cost'            => 80.00,
                'price'           => 150.00,
                'price_a'         => 145.00,
                'price_b'         => 140.00,
                'price_c'         => 135.00,
                'qty'             => 0,
                'alert_quantity'  => 0,
                'product_details' => 'Suscripción anual a Microsoft 365 Personal: Word, Excel, PowerPoint, OneDrive 1 TB',
                'featured'        => 1,
                'is_basicservice' => 0,
            ],
            [
                'code'            => 'C3-DIG-GDRIVE-001',
                'name'            => 'Google One 2TB Anual',
                'type'            => 'digital',
                'category_id'     => $catSoftware,
                'brand_id'        => $brandGoogle,
                'unit_id'         => $unitId,
                'cost'            => 50.00,
                'price'           => 100.00,
                'price_a'         => 95.00,
                'price_b'         => 90.00,
                'price_c'         => 85.00,
                'qty'             => 0,
                'alert_quantity'  => 0,
                'product_details' => 'Plan Google One 2 TB de almacenamiento en la nube, suscripción anual',
                'featured'        => 0,
                'is_basicservice' => 0,
            ],
            [
                'code'            => 'C3-DIG-ADOBE-001',
                'name'            => 'Adobe Photoshop 1 mes',
                'type'            => 'digital',
                'category_id'     => $catSoftware,
                'brand_id'        => $brandAdobe,
                'unit_id'         => $unitId,
                'cost'            => 40.00,
                'price'           => 85.00,
                'price_a'         => 82.00,
                'price_b'         => 78.00,
                'price_c'         => 75.00,
                'qty'             => 0,
                'alert_quantity'  => 0,
                'product_details' => 'Licencia mensual Adobe Photoshop CC, entrega de clave de activación digital',
                'featured'        => 0,
                'is_basicservice' => 0,
            ],
            [
                'code'            => 'C3-CAP-LARAVEL-001',
                'name'            => 'Capacitación Laravel Básico',
                'type'            => 'digital',
                'category_id'     => $catCapacitacion,
                'brand_id'        => $brandGenerico,
                'unit_id'         => $unitId,
                'cost'            => 100.00,
                'price'           => 250.00,
                'price_a'         => 240.00,
                'price_b'         => 220.00,
                'price_c'         => 200.00,
                'qty'             => 0,
                'alert_quantity'  => 0,
                'product_details' => 'Curso de 20 horas en Laravel: rutas, controladores, Eloquent y vistas Blade',
                'featured'        => 1,
                'is_basicservice' => 0,
            ],
            [
                'code'            => 'C3-DIS-LOGO-001',
                'name'            => 'Diseño de Logotipo Profesional',
                'type'            => 'digital',
                'category_id'     => $catDiseno,
                'brand_id'        => $brandGenerico,
                'unit_id'         => $unitId,
                'cost'            => 80.00,
                'price'           => 200.00,
                'price_a'         => 190.00,
                'price_b'         => 180.00,
                'price_c'         => 170.00,
                'qty'             => 0,
                'alert_quantity'  => 0,
                'product_details' => 'Diseño de logotipo con 3 propuestas, entrega en SVG, PNG y PDF',
                'featured'        => 0,
                'is_basicservice' => 0,
            ],
        ];

        $inserted = 0;
        $skipped  = 0;

        foreach ($products as $p) {
            $exists = DB::table('products')
                ->where('company_id', self::COMPANY_ID)
                ->where('code', $p['code'])
                ->exists();

            if ($exists) {
                $this->command->warn("  [SKIP] {$p['code']} — {$p['name']} ya existe.");
                $skipped++;
                continue;
            }

            DB::table('products')->insert([
                'company_id'               => self::COMPANY_ID,
                'name'                     => $p['name'],
                'code'                     => $p['code'],
                'type'                     => $p['type'],
                'barcode_symbology'        => 'C128',
                'brand_id'                 => $p['brand_id'],
                'category_id'             => $p['category_id'],
                'unit_id'                  => $p['unit_id'],
                'purchase_unit_id'         => $p['unit_id'],
                'sale_unit_id'             => $p['unit_id'],
                'cost'                     => $p['cost'],
                'price'                    => $p['price'],
                'price_a'                  => $p['price_a'],
                'price_b'                  => $p['price_b'],
                'price_c'                  => $p['price_c'],
                'qty'                      => $p['qty'],
                'alert_quantity'           => $p['alert_quantity'],
                'promotion'                => 0,
                'promotion_price'          => null,
                'tax_id'                   => $taxId,
                'tax_method'               => 1,
                'image'                    => 'zummXD2dvAtI.png',
                'file'                     => null,
                'is_variant'               => 0,
                'featured'                 => $p['featured'],
                'product_list'             => null,
                'qty_list'                 => null,
                'price_list'               => null,
                'is_pricelist'             => 0,
                'product_details'          => $p['product_details'],
                'is_active'                => 1,
                'courtesy'                 => 'FALSE',
                'permanent'                => 'TRUE',
                'starting_date_courtesy'   => null,
                'ending_date_courtesy'     => null,
                'courtesy_clearance_price' => 0.00,
                'commission_percentage'    => 0.00,
                'codigo_actividad'         => null,
                'codigo_producto_servicio' => null,
                'is_basicservice'          => $p['is_basicservice'],
                'account_id'               => null,
                'created_at'               => now(),
                'updated_at'               => now(),
            ]);

            $this->command->info("  [OK]   {$p['code']} — {$p['name']}");
            $inserted++;
        }

        $this->command->info("Listo: {$inserted} insertados, {$skipped} omitidos.");
    }

    private function ensureUnit(string $code, string $name): int
    {
        $unit = DB::table('units')
            ->where('company_id', self::COMPANY_ID)
            ->where('unit_code', $code)
            ->first();

        if ($unit) {
            return $unit->id;
        }

        return DB::table('units')->insertGetId([
            'company_id'                => self::COMPANY_ID,
            'unit_code'                 => $code,
            'unit_name'                 => $name,
            'base_unit'                 => null,
            'operator'                  => '*',
            'operation_value'           => 1,
            'is_active'                 => 1,
            'codigo_clasificador_siat'  => null,
            'created_at'                => now(),
            'updated_at'                => now(),
        ]);
    }

    private function ensureTax(string $name, float $rate): int
    {
        $tax = DB::table('taxes')
            ->where('company_id', self::COMPANY_ID)
            ->where('name', $name)
            ->first();

        if ($tax) {
            return $tax->id;
        }

        return DB::table('taxes')->insertGetId([
            'company_id' => self::COMPANY_ID,
            'name'       => $name,
            'rate'       => $rate,
            'is_active'  => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function ensureCategory(string $name): int
    {
        $cat = DB::table('categories')
            ->where('company_id', self::COMPANY_ID)
            ->where('name', $name)
            ->first();

        if ($cat) {
            return $cat->id;
        }

        return DB::table('categories')->insertGetId([
            'company_id'               => self::COMPANY_ID,
            'name'                     => $name,
            'image'                    => null,
            'parent_id'                => null,
            'is_active'                => 1,
            'codigo_actividad'         => null,
            'codigo_producto_servicio' => null,
            'created_at'               => now(),
            'updated_at'               => now(),
        ]);
    }

    private function ensureBrand(string $title): int
    {
        $brand = DB::table('brands')
            ->where('company_id', self::COMPANY_ID)
            ->where('title', $title)
            ->first();

        if ($brand) {
            return $brand->id;
        }

        return DB::table('brands')->insertGetId([
            'company_id' => self::COMPANY_ID,
            'title'      => $title,
            'image'      => null,
            'is_active'  => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
