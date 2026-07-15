<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RandomProductsSeeder extends Seeder
{
    private const PRODUCTS_PER_COMPANY = 20;

    private const PRODUCT_TYPES = [
        'Laptop', 'Monitor', 'Teclado', 'Mouse', 'Impresora', 'Silla de Oficina',
        'Escritorio', 'Cuaderno', 'Bolígrafo', 'Mochila', 'Auriculares', 'Cámara Web',
        'Router', 'Disco Duro Externo', 'Memoria USB', 'Proyector', 'Parlante Bluetooth',
        'Cargador', 'Cable HDMI', 'Silla Gamer', 'Tablet', 'Smartwatch', 'Micrófono',
    ];

    private const ADJECTIVES = [
        'Pro', 'Plus', 'Lite', 'Max', 'Ultra', 'Basic', 'Premium', 'Compact', 'Advance', 'Ecco',
    ];

    private const DEFAULT_CATEGORIES = ['Electrónica', 'Hogar', 'Oficina', 'Accesorios'];
    private const DEFAULT_BRANDS = ['Genérico', 'Samsung', 'HP', 'Logitech', 'Sony'];

    public function run(): void
    {
        $companies = DB::table('companies')->get();

        if ($companies->isEmpty()) {
            $this->command->warn('No hay companies registradas, no se generaron productos de prueba.');
            return;
        }

        $this->resyncSequences();

        foreach ($companies as $company) {
            $this->seedForCompany($company->id);
        }
    }

    private function resyncSequences(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach (['units', 'taxes', 'categories', 'brands', 'products'] as $table) {
            DB::statement("SELECT setval(pg_get_serial_sequence('{$table}','id'), COALESCE((SELECT MAX(id) FROM {$table}), 1), true)");
        }
    }

    private function seedForCompany(int $companyId): void
    {
        $this->command->info("Generando " . self::PRODUCTS_PER_COMPANY . " productos de prueba para company_id {$companyId}...");

        $unitId = $this->ensureUnit($companyId);
        $taxId = $this->ensureTax($companyId);
        $categories = $this->ensureCategories($companyId);
        $brands = $this->ensureBrands($companyId);

        $inserted = 0;

        for ($i = 1; $i <= self::PRODUCTS_PER_COMPANY; $i++) {
            $category = $categories[array_rand($categories)];
            $brand = $brands[array_rand($brands)];

            $tipo = self::PRODUCT_TYPES[array_rand(self::PRODUCT_TYPES)];
            $adjetivo = self::ADJECTIVES[array_rand(self::ADJECTIVES)];
            $modelo = strtoupper(Str::random(4));
            $name = "{$tipo} {$adjetivo} {$modelo}";

            $cost = round(mt_rand(1000, 50000) / 100, 2);
            $markup = mt_rand(120, 180) / 100;
            $price = round($cost * $markup, 2);
            $priceA = $price;
            $priceB = round($price * 0.95, 2);
            $priceC = round($price * 0.90, 2);

            $qty = mt_rand(0, 200);
            $isPromotion = mt_rand(1, 100) <= 15;
            $isFeatured = mt_rand(1, 100) <= 20;

            $code = 'TEST-' . $companyId . '-' . strtoupper(Str::random(8));

            DB::table('products')->insert([
                'company_id'               => $companyId,
                'name'                     => $name,
                'code'                     => $code,
                'type'                     => 'standard',
                'barcode_symbology'        => 'C128',
                'brand_id'                 => $brand->id,
                'category_id'              => $category->id,
                'unit_id'                  => $unitId,
                'purchase_unit_id'         => $unitId,
                'sale_unit_id'             => $unitId,
                'cost'                     => (string) $cost,
                'price'                    => (string) $price,
                'price_a'                  => $priceA,
                'price_b'                  => $priceB,
                'price_c'                  => $priceC,
                'qty'                      => $qty,
                'alert_quantity'           => mt_rand(0, 10),
                'promotion'                => $isPromotion ? 1 : 0,
                'promotion_price'          => $isPromotion ? (string) round($price * 0.8, 2) : null,
                'tax_id'                   => $taxId,
                'tax_method'               => 1,
                'image'                    => 'zummXD2dvAtI.png',
                'file'                     => null,
                'is_variant'               => 0,
                'featured'                 => $isFeatured ? 1 : 0,
                'product_list'             => null,
                'qty_list'                 => null,
                'price_list'               => null,
                'is_pricelist'             => 0,
                'product_details'          => "Producto de prueba generado automáticamente: {$name}",
                'is_active'                => 1,
                'courtesy'                 => 'FALSE',
                'permanent'                => 'TRUE',
                'starting_date_courtesy'   => null,
                'ending_date_courtesy'     => null,
                'courtesy_clearance_price' => 0.00,
                'commission_percentage'    => 0.00,
                'codigo_actividad'         => $category->codigo_actividad,
                'codigo_producto_servicio' => $category->codigo_producto_servicio,
                'is_basicservice'          => 0,
                'account_id'               => null,
                'created_at'               => now(),
                'updated_at'               => now(),
            ]);

            $inserted++;
        }

        $this->command->info("  [OK] {$inserted} productos de prueba creados para company_id {$companyId}.");
    }

    private function ensureUnit(int $companyId): int
    {
        $unit = DB::table('units')->where('company_id', $companyId)->where('is_active', true)->first();
        if ($unit) {
            return $unit->id;
        }

        $unit = DB::table('units')->whereNull('company_id')->where('is_active', true)->first();
        if ($unit) {
            return $unit->id;
        }

        return DB::table('units')->insertGetId([
            'company_id'               => $companyId,
            'unit_code'                => 'UNI',
            'unit_name'                => 'Unidad',
            'base_unit'                => null,
            'operator'                 => '*',
            'operation_value'          => 1,
            'codigo_clasificador_siat' => null,
            'is_active'                => 1,
            'created_at'               => now(),
            'updated_at'               => now(),
        ]);
    }

    private function ensureTax(int $companyId): int
    {
        $tax = DB::table('taxes')->where('company_id', $companyId)->where('is_active', true)->first();
        if ($tax) {
            return $tax->id;
        }

        $tax = DB::table('taxes')->whereNull('company_id')->where('is_active', true)->first();
        if ($tax) {
            return $tax->id;
        }

        return DB::table('taxes')->insertGetId([
            'company_id' => $companyId,
            'name'       => 'IVA 13%',
            'rate'       => 13,
            'is_active'  => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function ensureCategories(int $companyId): array
    {
        $categories = DB::table('categories')->where('company_id', $companyId)->where('is_active', true)->get();
        if ($categories->isNotEmpty()) {
            return $categories->all();
        }

        foreach (self::DEFAULT_CATEGORIES as $name) {
            DB::table('categories')->insert([
                'company_id'               => $companyId,
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

        return DB::table('categories')->where('company_id', $companyId)->where('is_active', true)->get()->all();
    }

    private function ensureBrands(int $companyId): array
    {
        $brands = DB::table('brands')->where('company_id', $companyId)->where('is_active', true)->get();
        if ($brands->isNotEmpty()) {
            return $brands->all();
        }

        foreach (self::DEFAULT_BRANDS as $title) {
            DB::table('brands')->insert([
                'company_id' => $companyId,
                'title'      => $title,
                'image'      => null,
                'is_active'  => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return DB::table('brands')->where('company_id', $companyId)->where('is_active', true)->get()->all();
    }
}
