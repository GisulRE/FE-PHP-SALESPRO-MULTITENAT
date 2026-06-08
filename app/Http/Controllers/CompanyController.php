<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Company;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class CompanyController extends Controller
{
    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('users-index')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if (empty($all_permission))
                $all_permission[] = 'dummy';

            $companies = Company::orderBy('id', 'desc')->get();
            return view('company.index', compact('companies', 'all_permission'));
        } else {
            return redirect()->back()->with('not_permitted', '¡Lo sentimos! No tienes permiso para acceder a este módulo');
        }
    }

    public function create()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('users-add')) {
            return view('company.create');
        } else {
            return redirect()->back()->with('not_permitted', '¡Lo sentimos! No tienes permiso para acceder a este módulo');
        }
    }

    public function store(Request $request)
    {
        $request->merge([
            'nit' => trim((string) $request->input('nit')),
        ]);

        $this->validate($request, [
            'name' => 'required|max:150|unique:companies,name',
            'nit' => 'required|max:30|unique:companies,nit',
        ]);

        $company = Company::create([
            'name' => $request->name,
            'nit' => $request->nit,
        ]);

        $adminUser = $this->provisionDefaultDataForCompany($company);

        $message = 'Empresa creada exitosamente';
        if ($adminUser) {
            $message .= '. Usuario administrador: ' . $adminUser['name'] . ' / ' . $adminUser['email'] . ' / Clave: Llave123.#';
        }

        return redirect('companies')->with('create_message', $message);
    }

    public function edit($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('users-edit')) {
            $company = Company::findOrFail($id);
            return view('company.edit', compact('company'));
        } else {
            return redirect()->back()->with('not_permitted', '¡Lo sentimos! No tienes permiso para acceder a este módulo');
        }
    }

    public function update(Request $request, $id)
    {
        $request->merge([
            'nit' => trim((string) $request->input('nit')),
        ]);

        $this->validate($request, [
            'name' => 'required|max:150|unique:companies,name,' . $id,
            'nit' => 'required|max:30|unique:companies,nit,' . $id,
        ]);

        $company = Company::findOrFail($id);
        $company->update([
            'name' => $request->name,
            'nit' => $request->nit,
        ]);

        return redirect('companies')->with('edit_message', 'Empresa actualizada exitosamente');
    }

    public function destroy($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('users-delete')) {
            $company = Company::findOrFail($id);
            $company->delete();
            return redirect('companies')->with('delete_message', 'Empresa eliminada exitosamente');
        } else {
            return redirect()->back()->with('not_permitted', '¡Lo sentimos! No tienes permiso para acceder a este módulo');
        }
    }

    private function provisionDefaultDataForCompany(Company $company): ?array
    {
        $adminUserData = null;

        DB::transaction(function () use ($company, &$adminUserData) {
            $adminUserData = $this->createCompanyAdminUser($company);
            $accountId = $this->createDefaultAccount($company);
            $this->createSecondaryAccount($company);
            $defaultSucursalCode = $this->createDefaultSucursal($company, $adminUserData['id'] ?? null);
            $defaultSucursalId = $this->findSucursalIdByCode($company, $defaultSucursalCode);
            $warehouseId = $this->createDefaultWarehouse($company, $defaultSucursalId, $defaultSucursalCode);
            $customerId = $this->createDefaultCustomer($company);
            $this->createDefaultSupplier($company);
            $this->createDefaultPuntoVenta($company, $adminUserData['id'] ?? null, $defaultSucursalCode);
            $this->createDefaultBiller($company, $accountId, $warehouseId, $customerId);
        });

        return $adminUserData;
    }

    private function createCompanyAdminUser(Company $company): ?array
    {
        if (!Schema::hasTable('users')) {
            return null;
        }

        $role = null;
        if (Schema::hasTable('roles')) {
            $role = Role::where('name', 'Administrador')->first();
            if (!$role) {
                $role = Role::first();
            }
        }
        if (!$role) {
            return null;
        }

        $username = 'admin';
        $email = 'admin.company' . $company->id . '@local.test';

        $user = User::where('company_id', $company->id)
            ->where('name', $username)
            ->first();
        if (!$user) {
            $user = User::create([
                'name' => $username,
                'email' => $email,
                'password' => Hash::make('Llave123.#'),
                'phone' => null,
                'company_name' => null,
                'company_id' => $company->id,
                'role_id' => $role->id,
                'biller_id' => null,
                'is_active' => true,
                'is_deleted' => false,
            ]);
        } else {
            $user->update([
                'name' => $username,
                'email' => $email,
                'password' => Hash::make('Llave123.#'),
                'company_id' => $company->id,
                'role_id' => $role->id,
                'is_active' => true,
                'is_deleted' => false,
            ]);
        }

        if (!$user->hasRole($role->name)) {
            $user->assignRole($role->name);
        }

        return ['id' => $user->id, 'name' => $username, 'email' => $email];
    }

    private function createDefaultAccount(Company $company): ?int
    {
        if (!Schema::hasTable('accounts')) {
            return null;
        }

        $exists = DB::table('accounts')
            ->where('company_id', $company->id)
            ->where('name', 'Caja Principal')
            ->first();

        if ($exists) {
            return $exists->id;
        }

        $data = [
            'account_no' => 'ACCT-' . $company->id . '-1',
            'name' => 'Caja Principal',
            'initial_balance' => 0,
            'total_balance' => 0,
            'note' => 'Cuenta por defecto',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('accounts', 'is_default')) {
            $data['is_default'] = 1;
        }
        if (Schema::hasColumn('accounts', 'type')) {
            $data['type'] = 1;
        }
        if (Schema::hasColumn('accounts', 'company_id')) {
            $data['company_id'] = $company->id;
        }

        return DB::table('accounts')->insertGetId($data);
    }

    private function createSecondaryAccount(Company $company): ?int
    {
        if (!Schema::hasTable('accounts')) {
            return null;
        }

        $exists = DB::table('accounts')
            ->where('company_id', $company->id)
            ->where('name', 'Banco Principal')
            ->first();

        if ($exists) {
            return $exists->id;
        }

        $data = [
            'account_no' => 'ACCT-' . $company->id . '-2',
            'name' => 'Banco Principal',
            'initial_balance' => 0,
            'total_balance' => 0,
            'note' => 'Cuenta bancaria por defecto',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('accounts', 'is_default')) {
            $data['is_default'] = 0;
        }
        if (Schema::hasColumn('accounts', 'type')) {
            $data['type'] = 2;
        }
        if (Schema::hasColumn('accounts', 'company_id')) {
            $data['company_id'] = $company->id;
        }

        return DB::table('accounts')->insertGetId($data);
    }

    private function createDefaultWarehouse(Company $company, ?int $sucursalId = null, int $sucursalCode = 0): ?int
    {
        if (!Schema::hasTable('warehouses')) {
            return null;
        }

        $exists = DB::table('warehouses')
            ->where('company_id', $company->id)
            ->where('name', 'Almacen Principal')
            ->first();

        if ($exists) {
            $warehouseUpdate = ['updated_at' => now()];
            if (Schema::hasColumn('warehouses', 'sucursal_id')) {
                $warehouseUpdate['sucursal_id'] = $sucursalId;
            }
            if (Schema::hasColumn('warehouses', 'sucursal_siat')) {
                $warehouseUpdate['sucursal_siat'] = (string) $sucursalCode;
            }

            if (count($warehouseUpdate) > 1) {
                DB::table('warehouses')
                    ->where('id', $exists->id)
                    ->update($warehouseUpdate);
            }

            return $exists->id;
        }

        $warehouseInsert = [
            'company_id' => $company->id,
            'name' => 'Almacen Principal',
            'phone' => '591-2-2222222',
            'email' => 'principal@empresa.com',
            'address' => 'Av. Principal #123',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('warehouses', 'sucursal_id')) {
            $warehouseInsert['sucursal_id'] = $sucursalId;
        }
        if (Schema::hasColumn('warehouses', 'sucursal_siat')) {
            $warehouseInsert['sucursal_siat'] = (string) $sucursalCode;
        }

        return DB::table('warehouses')->insertGetId($warehouseInsert);
    }

    private function findSucursalIdByCode(Company $company, int $sucursalCode = 0): ?int
    {
        if (!Schema::hasTable('sucursal_siat')) {
            return null;
        }

        $query = DB::table('sucursal_siat')->where('sucursal', (string) $sucursalCode);

        if (Schema::hasColumn('sucursal_siat', 'company_id')) {
            $query->where('company_id', $company->id);
        } else {
            $query->where('id_empresa', $company->id);
        }

        return $query->value('id');
    }

    private function createDefaultCustomer(Company $company): ?int
    {
        if (!Schema::hasTable('customers')) {
            return null;
        }

        $exists = DB::table('customers')
            ->where('company_id', $company->id)
            ->where('name', 'Cliente General')
            ->first();

        if ($exists) {
            return $exists->id;
        }

        $groupId = 1;
        if (Schema::hasTable('customer_groups')) {
            $group = DB::table('customer_groups')->orderBy('id')->first();
            if ($group) {
                $groupId = $group->id;
            }
        }

        $data = [
            'name' => 'Cliente General',
            'phone_number' => '00000000',
            'address' => 'N/A',
            'city' => 'N/A',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('customers', 'customer_group_id')) {
            $data['customer_group_id'] = $groupId;
        }
        if (Schema::hasColumn('customers', 'company_name')) {
            $data['company_name'] = 'Cliente General';
        }
        if (Schema::hasColumn('customers', 'email')) {
            $data['email'] = 'cliente.general@empresa.local';
        }
        if (Schema::hasColumn('customers', 'state')) {
            $data['state'] = 'N/A';
        }
        if (Schema::hasColumn('customers', 'postal_code')) {
            $data['postal_code'] = '0000';
        }
        if (Schema::hasColumn('customers', 'country')) {
            $data['country'] = 'Bolivia';
        }
        if (Schema::hasColumn('customers', 'tipo_documento')) {
            $data['tipo_documento'] = 5;
        }
        if (Schema::hasColumn('customers', 'valor_documento')) {
            $data['valor_documento'] = '0';
        }
        if (Schema::hasColumn('customers', 'razon_social')) {
            $data['razon_social'] = 'Cliente General';
        }
        if (Schema::hasColumn('customers', 'company_id')) {
            $data['company_id'] = $company->id;
        }

        return DB::table('customers')->insertGetId($data);
    }

    private function createDefaultSupplier(Company $company): ?int
    {
        if (!Schema::hasTable('suppliers')) {
            return null;
        }

        $exists = DB::table('suppliers')
            ->where('company_id', $company->id)
            ->where('name', 'Proveedor General')
            ->first();

        if ($exists) {
            return $exists->id;
        }

        $data = [
            'name' => 'Proveedor General',
            'image' => null,
            'company_name' => 'Proveedor General S.R.L.',
            'vat_number' => '0',
            'email' => 'proveedor.general@empresa.local',
            'phone_number' => '00000000',
            'address' => 'N/A',
            'city' => 'N/A',
            'state' => null,
            'postal_code' => '0000',
            'country' => 'Bolivia',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('suppliers', 'company_id')) {
            $data['company_id'] = $company->id;
        }

        return DB::table('suppliers')->insertGetId($data);
    }

    private function createDefaultSucursal(Company $company, ?int $userId): int
    {
        if (!Schema::hasTable('sucursal_siat')) {
            return 0;
        }

        $existingSucursal = DB::table('sucursal_siat')
            ->where('id_empresa', $company->id)
            ->where('sucursal', '0')
            ->first();

        if ($existingSucursal) {
            return (int) $existingSucursal->sucursal;
        }

        $insertSucursal = [
            'sucursal' => '0',
            'nombre' => 'CASA MATRIZ - ' . strtoupper($company->name),
            'descripcion_sucursal' => 'Casa Matriz de ' . $company->name,
            'domicilio_tributario' => 'Av. Principal #1',
            'ciudad_municipio' => 'La Paz',
            'telefono' => '591-2-2222222',
            'email' => null,
            'id_autorizacion_facturacion' => null,
            'departamento' => 'La Paz',
            'estado' => 'ACTIVO',
            'usuario_alta' => $userId ?? 1,
            'id_empresa' => $company->id,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('sucursal_siat', 'company_id')) {
            $insertSucursal['company_id'] = $company->id;
        }

        DB::table('sucursal_siat')->insert($insertSucursal);

        return 0;
    }

    private function createDefaultPuntoVenta(Company $company, ?int $userId, int $sucursalCode = 0): void
    {
        if (!Schema::hasTable('puntos_venta')) {
            return;
        }

        if (!Schema::hasTable('sucursal_siat')) {
            return;
        }

        $sucursalExists = DB::table('sucursal_siat')
            ->where('id_empresa', $company->id)
            ->where('sucursal', (string) $sucursalCode)
            ->exists();

        if (!$sucursalExists) {
            $sucursalCode = $this->createDefaultSucursal($company, $userId);
        }

        $exists = DB::table('puntos_venta')
            ->where('id_empresa', $company->id)
            ->where('sucursal', $sucursalCode)
            ->where('codigo_punto_venta', '0')
            ->exists();

        if ($exists) {
            return;
        }

        $insertPuntoVenta = [
            'codigo_punto_venta' => '0',
            'nombre_punto_venta' => 'Punto de Venta Principal',
            'descripcion' => 'PV Principal - ' . $company->name,
            'tipo_punto_venta' => '0',
            'codigo_cuis' => 'CUIS-PENDIENTE',
            'fecha_vigencia_cuis' => now()->addYear(),
            'usuario_alta' => $userId ?? 1,
            'id_empresa' => $company->id,
            'sucursal' => $sucursalCode,
            'correlativo_factura' => 1,
            'correlativo_alquiler' => 1,
            'correlativo_servicios_basicos' => 1,
            'correlativo_nota_debcred' => 1,
            'modo_contingencia' => 0,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => null,
            'nit_comisionista' => null,
            'numero_contrato' => null,
            'is_siat' => 1,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('puntos_venta', 'company_id')) {
            $insertPuntoVenta['company_id'] = $company->id;
        }

        DB::table('puntos_venta')->insert($insertPuntoVenta);
    }

    private function createDefaultBiller(Company $company, ?int $accountId, ?int $warehouseId, ?int $customerId): void
    {
        if (!Schema::hasTable('billers')) {
            return;
        }

        $billerName = 'Facturador ' . $company->name;
        $exists = DB::table('billers')
            ->where('company_id', $company->id)
            ->where('name', $billerName)
            ->exists();

        if ($exists) {
            return;
        }

        if (!$accountId || !$customerId) {
            return;
        }

        DB::table('billers')->insert([
            'name' => $billerName,
            'company_name' => $company->name,
            'vat_number' => null,
            'email' => 'facturador.company' . $company->id . '@empresa.local',
            'phone_number' => '591-2-2222222',
            'address' => 'Av. Principal #1',
            'city' => 'La Paz',
            'state' => 'La Paz',
            'postal_code' => null,
            'country' => 'Bolivia',
            'image' => null,
            'is_active' => true,
            'company_id' => $company->id,
            'sucursal' => '0',
            'punto_venta_siat' => '0',
            'warehouse_id' => $warehouseId,
            'customer_id' => $customerId,
            'account_id' => $accountId,
            'account_id_tarjeta' => $accountId,
            'account_id_cheque' => $accountId,
            'account_id_deposito' => $accountId,
            'account_id_qr' => $accountId,
            'account_id_giftcard' => $accountId,
            'account_id_vale' => $accountId,
            'account_id_otros' => $accountId,
            'account_id_pagoposterior' => $accountId,
            'account_id_transferenciabancaria' => $accountId,
            'account_id_swift' => $accountId,
            'account_id_receivable' => $accountId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
