<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('products')) {
                return;
            }
            
            if (!DB::getSchemaBuilder()->hasTable('companies')) {
                return;
            }

            // Obtener todas las companies
            $companies = DB::table('companies')->get();
            
            foreach ($companies as $company) {
                // Crear categorías si no existen
                $categoryElectronica = DB::table('categories')
                    ->where('company_id', $company->id)
                    ->where('name', 'Electrónica')
                    ->first();
                
                if (!$categoryElectronica) {
                    DB::table('categories')->insert([
                        'company_id' => $company->id,
                        'name' => 'Electrónica',
                        'is_active' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $categoryElectronica = DB::table('categories')
                        ->where('company_id', $company->id)
                        ->where('name', 'Electrónica')
                        ->first();
                }
                
                $categoryOficina = DB::table('categories')
                    ->where('company_id', $company->id)
                    ->where('name', 'Oficina')
                    ->first();
                
                if (!$categoryOficina) {
                    DB::table('categories')->insert([
                        'company_id' => $company->id,
                        'name' => 'Oficina',
                        'is_active' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $categoryOficina = DB::table('categories')
                        ->where('company_id', $company->id)
                        ->where('name', 'Oficina')
                        ->first();
                }
                
                // Crear marcas si no existen
                $brandSamsung = DB::table('brands')
                    ->where('company_id', $company->id)
                    ->where('title', 'Samsung')
                    ->first();
                
                if (!$brandSamsung) {
                    DB::table('brands')->insert([
                        'company_id' => $company->id,
                        'title' => 'Samsung',
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $brandSamsung = DB::table('brands')
                        ->where('company_id', $company->id)
                        ->where('title', 'Samsung')
                        ->first();
                }
                
                $brandGenerico = DB::table('brands')
                    ->where('company_id', $company->id)
                    ->where('title', 'Genérico')
                    ->first();
                
                if (!$brandGenerico) {
                    DB::table('brands')->insert([
                        'company_id' => $company->id,
                        'title' => 'Genérico',
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $brandGenerico = DB::table('brands')
                        ->where('company_id', $company->id)
                        ->where('title', 'Genérico')
                        ->first();
                }
                
                // Obtener unit_id (la primera disponible)
                $unit = DB::table('units')->first();
                $tax = DB::table('taxes')->first();
                
                // Crear Producto 1: Mouse Inalámbrico
                $existsProduct1 = DB::table('products')
                    ->where('company_id', $company->id)
                    ->where('code', 'MOUSE-001')
                    ->exists();
                
                if (!$existsProduct1) {
                    DB::table('products')->insert([
                        'company_id' => $company->id,
                        'name' => 'Mouse Inalámbrico Samsung',
                        'code' => 'MOUSE-001',
                        'type' => 'standard',
                        'barcode_symbology' => 'C128',
                        'brand_id' => $brandSamsung->id,
                        'category_id' => $categoryElectronica->id,
                        'unit_id' => $unit->id,
                        'purchase_unit_id' => $unit->id,
                        'sale_unit_id' => $unit->id,
                        'cost' => '50.00',
                        'price' => '75.00',
                        'price_a' => 75.00,
                        'price_b' => 70.00,
                        'price_c' => 65.00,
                        'qty' => 100,
                        'alert_quantity' => 10,
                        'promotion' => 0,
                        'promotion_price' => null,
                        'tax_id' => $tax ? $tax->id : null,
                        'tax_method' => 1,
                        'image' => null,
                        'file' => null,
                        'is_variant' => 0,
                        'featured' => 1,
                        'product_list' => null,
                        'qty_list' => null,
                        'price_list' => null,
                        'is_pricelist' => 0,
                        'product_details' => 'Mouse inalámbrico ergonómico con batería de larga duración',
                        'is_active' => 1,
                        'courtesy' => 'FALSE',
                        'permanent' => 'TRUE',
                        'starting_date_courtesy' => null,
                        'ending_date_courtesy' => null,
                        'courtesy_clearance_price' => 0.00,
                        'commission_percentage' => 0.00,
                        'codigo_actividad' => null,
                        'codigo_producto_servicio' => null,
                        'is_basicservice' => 0,
                        'account_id' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                
                // Crear Producto 2: Cuaderno Universitario
                $existsProduct2 = DB::table('products')
                    ->where('company_id', $company->id)
                    ->where('code', 'CUAD-001')
                    ->exists();
                
                if (!$existsProduct2) {
                    DB::table('products')->insert([
                        'company_id' => $company->id,
                        'name' => 'Cuaderno Universitario 100 hojas',
                        'code' => 'CUAD-001',
                        'type' => 'standard',
                        'barcode_symbology' => 'C128',
                        'brand_id' => $brandGenerico->id,
                        'category_id' => $categoryOficina->id,
                        'unit_id' => $unit->id,
                        'purchase_unit_id' => $unit->id,
                        'sale_unit_id' => $unit->id,
                        'cost' => '10.00',
                        'price' => '15.00',
                        'price_a' => 15.00,
                        'price_b' => 14.00,
                        'price_c' => 13.00,
                        'qty' => 250,
                        'alert_quantity' => 20,
                        'promotion' => 0,
                        'promotion_price' => null,
                        'tax_id' => $tax ? $tax->id : null,
                        'tax_method' => 1,
                        'image' => null,
                        'file' => null,
                        'is_variant' => 0,
                        'featured' => 0,
                        'product_list' => null,
                        'qty_list' => null,
                        'price_list' => null,
                        'is_pricelist' => 0,
                        'product_details' => 'Cuaderno universitario con espiral, hojas cuadriculadas',
                        'is_active' => 1,
                        'courtesy' => 'FALSE',
                        'permanent' => 'TRUE',
                        'starting_date_courtesy' => null,
                        'ending_date_courtesy' => null,
                        'courtesy_clearance_price' => 0.00,
                        'commission_percentage' => 0.00,
                        'codigo_actividad' => null,
                        'codigo_producto_servicio' => null,
                        'is_basicservice' => 0,
                        'account_id' => null,
                        'created_at' => now(),
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
