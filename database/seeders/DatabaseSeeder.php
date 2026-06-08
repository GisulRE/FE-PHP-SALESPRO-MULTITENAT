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
            // CreateCompanyAndPruebaSeeder::class,
            OptionsSeeder::class,
            // Primero clientes y proveedores (billers los referencian)
            CustomersSeeder::class,
            SuppliersSeeder::class,
            UnitsSeeder::class,
            // SIAT: sucursales y puntos de venta (necesarios antes de crear warehouses y billers)
            SucursalSiatSeeder::class,
            PuntoVentaSeeder::class,
            WarehousesSeeder::class,
            AccountsSeeder::class,
            SiatParametricasVariosSeeder::class,
            // Facturadores (necesitan warehouse, customer, SIAT)
            BillersSeeder::class,
            ProductsSeeder::class,
        ]);
    }
}
