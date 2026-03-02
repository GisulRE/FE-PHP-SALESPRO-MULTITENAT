<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            PosSettingsSeeder::class,
            GeneralSettingsSeeder::class,
            ReservationsPermissionSeeder::class,
            RolesPermissionsSeeder::class,
            CreateAdminUserSeeder::class,
            CreateCompanyAndPruebaSeeder::class,
            OptionsSeeder::class,
            // Primero clientes y proveedores (billers los referencian)
            CustomersSeeder::class,
            SuppliersSeeder::class,
            UnitsSeeder::class,
            WarehousesSeeder::class,
            // SIAT: sucursales y puntos de venta (billers los referencian)
            SucursalSiatSeeder::class,
            PuntoVentaSeeder::class,
            // Facturadores (necesitan warehouse, customer, SIAT)
            BillersSeeder::class,
            ProductsSeeder::class,
        ]);
    }
}
