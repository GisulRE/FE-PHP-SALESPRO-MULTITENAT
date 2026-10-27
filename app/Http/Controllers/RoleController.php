<?php

namespace App\Http\Controllers;

use App\Roles;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    private function canManageRoles()
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        if ($user->role_id <= 2) {
            return true;
        }
        $role = Role::find($user->role_id);
        return $role && in_array(strtolower($role->name ?? ''), [
            'admin', 'superadmin', 'administrador', 'owner', 'propietario', 'tienda'
        ]);
    }

    public function index()
    {
        if ($this->canManageRoles()) {
            $company_id = Auth::user()->company_id;
            $lims_role_all = Roles::where('is_active', true)
                ->when($company_id, function ($query) use ($company_id) {
                    return $query->where('company_id', $company_id);
                })
                ->get();
            return view('role.create', compact('lims_role_all'));
        } else {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }
    }

    public function create()
    {
    }

    public function store(Request $request)
    {
        if (!$this->canManageRoles()) {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }

        $company_id = Auth::user()->company_id;

        $this->validate($request, [
            'name' => [
                'max:255',
                Rule::unique('roles')->where(function ($query) use ($company_id) {
                    $q = $query->where('is_active', 1);
                    if ($company_id) {
                        $q->where('company_id', $company_id);
                    }
                    return $q;
                }),
            ],
        ]);

        $data = $request->all();
        if ($company_id && empty($data['company_id'])) {
            $data['company_id'] = $company_id;
        }
        if (empty($data['guard_name'])) {
            $data['guard_name'] = 'web';
        }
        Roles::create($data);
        return redirect('role')->with('message', 'Data inserted successfully');
    }

    public function edit($id)
    {
        if ($this->canManageRoles()) {
            $lims_role_data = Roles::find($id);
            return $lims_role_data;
        } else {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }
    }

    public function update(Request $request, $id)
    {
        if (!$this->canManageRoles()) {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }

        $company_id = Auth::user()->company_id;

        $this->validate($request, [
            'name' => [
                'max:255',
                Rule::unique('roles')->ignore($request->role_id)->where(function ($query) use ($company_id) {
                    $q = $query->where('is_active', 1);
                    if ($company_id) {
                        $q->where('company_id', $company_id);
                    }
                    return $q;
                }),
            ],
        ]);

        $input = $request->all();
        $lims_role_data = Roles::where('id', $input['role_id'])->first();
        if ($lims_role_data) {
            $lims_role_data->update($input);
            return redirect('role')->with('message', 'Data updated successfully');
        }
        return redirect('role')->with('not_permitted', 'Rol no encontrado');
    }

    public function permission($id)
    {
        if ($this->canManageRoles()) {
            $lims_role_data = Roles::find($id);
            if (!$lims_role_data) {
                return redirect()->back()->with('not_permitted', 'Rol no encontrado');
            }

            $all_permission = \DB::table('permissions')
                ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                ->where('role_id', $id)
                ->pluck('name')
                ->toArray();

            if (empty($all_permission)) {
                $all_permission[] = 'dummy text';
            }

            // Obtener módulos bloqueados
            $blocked_modules = [];
            if ($lims_role_data->blocked_modules) {
                $blocked_modules = json_decode($lims_role_data->blocked_modules, true) ?? [];
            }

            // Debug temporal
            Log::info('Role ID: ' . $id);
            Log::info('Blocked Modules JSON: ' . $lims_role_data->blocked_modules);
            Log::info('Blocked Modules Array: ' . print_r($blocked_modules, true));

            return view('role.permission', compact('lims_role_data', 'all_permission', 'blocked_modules'));
        } else {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }
    }

    public function setPermission(Request $request)
    {
        if (!$this->canManageRoles()) {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }

        // Ampliar tiempo de ejecución para evitar caídas por timeout de PHP
        ini_set('max_execution_time', 120);
        set_time_limit(120);

        // 1) Guard fijo y consistente
        $guard = 'web';

        // 2) Validar rol existente
        $role = Role::findOrFail($request->role_id);

        // 3) Lista completa de permisos gestionados en la vista (158 permisos)
        $permissions = [
            // Products
            'products-index','products-add','products-edit','products-delete',

            // Purchases
            'purchases-index','purchases-add','purchases-edit','purchases-delete',

            // Presale
            'presale-index','presale-create','presale-edit','presale-delete',

            // Sales
            'sales-index','sales-add','sales-edit','sales-delete',

            // Expenses
            'expenses-index','expenses-add','expenses-edit','expenses-delete',

            // Quotes
            'quotes-index','quotes-add','quotes-edit','quotes-delete',

            // Transfers
            'transfers-index','transfers-add','transfers-edit','transfers-delete',

            // Returns
            'returns-index','returns-add','returns-edit','returns-delete',

            // Purchase Return
            'purchase-return-index','purchase-return-add','purchase-return-edit','purchase-return-delete',

            // Qty Adjustment
            'qty_adjustment-index','qty_adjustment-add','qty_adjustment-edit','qty_adjustment-delete',

            // Adjustment Account
            'adjustment-account-index','adjustment-account-add','adjustment-account-edit','adjustment-account-delete',

            // Account / Money
            'account-index','money-transfer','balance-sheet','balance-sheet-account','close-balance-account','account-statement',

            // HRM
            'hrm-menu','department','attendance','payroll','attentionshift',

            // Reservations
            'reservations-index','reservations-add','reservations-edit','reservations-delete',

            // Employees
            'employees-index','employees-add','employees-edit','employees-delete',

            // Users
            'users-index','users-add','users-edit','users-delete',

            // Customers
            'customers-index','customers-add','customers-edit','customers-delete',

            // Billers
            'billers-index','billers-add','billers-edit','billers-delete',

            // Suppliers
            'suppliers-index','suppliers-add','suppliers-edit','suppliers-delete',

            // Reports
            'profit-loss','best-seller','product-report','product-detail-report',
            'daily-sale','monthly-sale','daily-purchase','monthly-purchase',
            'sale-report','saledetail-report','payment-report','purchase-report',
            'warehouse-report','warehouse-stock-report','product-qty-alert',
            'user-report','customer-report','supplier-report','due-report',

            // Settings
            'general_setting','mail_setting','sms_setting','create_sms','pos_setting','hrm_setting',

            // Stock / Backup
            'stock_count','adjustment','print_barcode','empty_database','backup_database',

            // Masters
            'warehouse','customer_group','brand','unit','tax','gift_card','coupon','holiday','category','delivery',

            // Modules / SIAT / QR
            'module_qr','module_siat','sale_pendingdue',

            // POS Features
            'pos_payment_card','pos_payment_cash','pos_payment_qrcash','pos_create_due','pos_payment_qr',
            'pos_payment_check','pos_payment_giftcard','pos_payment_deposit','pos_paid_due','pos_recent_sales',
            'pos_discount_gral','pos_discount_item','pos_customer_advanced',

            // Panel SIAT
            'panel_siat','sucursal_siat','urlws_siat','authfact_siat','puntoventa_siat','contingencia_siat',
            'facturamasiva_siat','notadebcred_siat','cafc_siat',

            // Libro Ventas
            'sales-list-booksale',
            'lv_arqueogralpdf','lv_arqueogral_categ','lv_reportespdf_excel',
            'lv_facturas_cobradas','lv_facturas_revertidas',

            // Transfer acceptance
            'accept-transfers',

            // Otros
            'salebiller-report','salecustomer-report','only-commision-report','service-commission-report',
            'sale-renueve-report','attendance-employee-report','holiday-employee-report',
        ];

        $uniquePermissions = array_values(array_unique($permissions));

        // 4) Obtener todos los permisos existentes para los nombres gestionados por el formulario agrupados por nombre
        $allPermissions = \DB::table('permissions')
            ->whereIn('name', $uniquePermissions)
            ->where('guard_name', $guard)
            ->select('id', 'name')
            ->get();

        $existingNames = $allPermissions->pluck('name')->unique()->toArray();
        $missingPermissions = array_diff($uniquePermissions, $existingNames);
        if (!empty($missingPermissions)) {
            $now = now();
            $bulkInsert = [];
            foreach ($missingPermissions as $missingName) {
                $bulkInsert[] = [
                    'name'       => $missingName,
                    'guard_name' => $guard,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            \DB::table('permissions')->insert($bulkInsert);

            $allPermissions = \DB::table('permissions')
                ->whereIn('name', $uniquePermissions)
                ->where('guard_name', $guard)
                ->select('id', 'name')
                ->get();
        }

        // Agrupar IDs por nombre de permiso para contemplar duplicados históricos en la tabla permissions
        $permIdsByName = [];
        foreach ($allPermissions as $p) {
            $permIdsByName[$p->name][] = $p->id;
        }

        // 5) Determinar qué nombres se otorgaron y cuáles se revocaron
        $allRevokedIds = [];
        $canonicalGrantedIds = [];

        foreach ($uniquePermissions as $permName) {
            $ids = $permIdsByName[$permName] ?? [];
            if (empty($ids)) {
                continue;
            }

            if ($request->has($permName)) {
                // Primer ID como canónico
                $canonicalGrantedIds[] = $ids[0];
                // Si la BD tiene duplicados para este nombre, revocar los IDs adicionales para limpiar
                if (count($ids) > 1) {
                    for ($i = 1; $i < count($ids); $i++) {
                        $allRevokedIds[] = $ids[$i];
                    }
                }
            } else {
                // Si fue desmarcado, revocar TODOS los IDs (incluidos duplicados en la BD)
                foreach ($ids as $id) {
                    $allRevokedIds[] = $id;
                }
            }
        }

        // 6) Actualizar role_has_permissions en masa para este rol en una transacción rápida
        \DB::transaction(function () use ($role, $canonicalGrantedIds, $allRevokedIds) {
            // Revocar todos los IDs de permisos desmarcados y duplicados excedentes
            if (!empty($allRevokedIds)) {
                \DB::table('role_has_permissions')
                    ->where('role_id', $role->id)
                    ->whereIn('permission_id', $allRevokedIds)
                    ->delete();
            }

            // Otorgar los marcados (insertando solo el canónico si no está asignado)
            if (!empty($canonicalGrantedIds)) {
                $alreadyAssigned = \DB::table('role_has_permissions')
                    ->where('role_id', $role->id)
                    ->whereIn('permission_id', $canonicalGrantedIds)
                    ->pluck('permission_id')
                    ->toArray();

                $toInsert = array_diff($canonicalGrantedIds, $alreadyAssigned);
                if (!empty($toInsert)) {
                    $insertData = [];
                    foreach ($toInsert as $pid) {
                        $insertData[] = [
                            'permission_id' => $pid,
                            'role_id'       => $role->id,
                        ];
                    }
                    \DB::table('role_has_permissions')->insert($insertData);
                }
            }
        });

        // 7) Guardar módulos bloqueados
        $blocked_modules = $request->input('blocked_modules', []);
        $lims_role_data = Roles::find($request->role_id);

        if ($lims_role_data) {
            $lims_role_data->blocked_modules = json_encode($blocked_modules);
            $lims_role_data->save();
        }

        // 8) Limpiar cache de permisos de Spatie una sola vez al final
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect('role')->with('message', 'Permisos actualizados con éxito');
    }

    public function destroy($id)
    {
        if (!$this->canManageRoles()) {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }

        $lims_role_data = Roles::find($id);
        if ($lims_role_data) {
            if (in_array(strtolower($lims_role_data->name), ['admin', 'administrador'])) {
                return redirect('role')->with('not_permitted', 'No está permitido eliminar el rol de Administrador');
            }
            $lims_role_data->is_active = false;
            $lims_role_data->save();
            return redirect('role')->with('not_permitted', 'Datos eliminado con éxito');
        }
        return redirect('role')->with('not_permitted', 'Rol no encontrado');
    }
}
