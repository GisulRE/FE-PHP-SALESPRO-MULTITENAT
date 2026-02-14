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
    public function index()
    {
        if (Auth::user()->role_id <= 2) {
            $lims_role_all = Roles::where('is_active', true)->get();
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
        $this->validate($request, [
            'name' => [
                'max:255',
                Rule::unique('roles')->where(function ($query) {
                    return $query->where('is_active', 1);
                }),
            ],
        ]);

        $data = $request->all();
        Roles::create($data);
        return redirect('role')->with('message', 'Data inserted successfully');
    }

    public function edit($id)
    {
        if (Auth::user()->role_id <= 2) {
            $lims_role_data = Roles::find($id);
            return $lims_role_data;
        } else {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }

    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => [
                'max:255',
                Rule::unique('roles')->ignore($request->role_id)->where(function ($query) {
                    return $query->where('is_active', 1);
                }),
            ],
        ]);

        $input = $request->all();
        $lims_role_data = Roles::where('id', $input['role_id'])->first();
        $lims_role_data->update($input);
        return redirect('role')->with('message', 'Data updated successfully');
    }

    public function permission($id)
    {
        if (Auth::user()->role_id <= 2) {
            $lims_role_data = Roles::find($id);
            $permissions = Role::findByName($lims_role_data->name)->permissions;
            foreach ($permissions as $permission) {
                $all_permission[] = $permission->name;
            }

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
        // 1) Reset de cache de permisos (clave para evitar PermissionDoesNotExist por cache viejo)
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // 2) Guard fijo y consistente (según tu tabla roles, es 'web')
        $guard = 'web';

        // 3) No crees roles con firstOrCreate por id. Debe existir.
        $role = Role::findOrFail($request->role_id);

        // 4) Lista de permisos (agrega/quita según tu sistema)
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

            // Transfer acceptance (tenías este con guard_name, lo incluimos igual)
            'accept-transfers',

            // Otros (si los tienes)
            'module_qr',
            'salebiller-report','salecustomer-report','only-commision-report','service-commission-report',
            'sale-renueve-report','attendance-employee-report','holiday-employee-report',
        ];

        // 5) Sincronizador: crea permiso con guard web y asigna / revoca según request
        foreach (array_unique($permissions) as $permName) {
            // Crea el permiso SIEMPRE con guard_name para evitar inconsistencias
            Permission::firstOrCreate([
                'name' => $permName,
                'guard_name' => $guard,
            ]);

            if ($request->has($permName)) {
                $role->givePermissionTo($permName);
            } else {
                // revokePermissionTo también busca por guard; ya existe porque lo creamos arriba
                $role->revokePermissionTo($permName);
            }
        }

        // 6) Guardar módulos bloqueados (tu lógica original)
        $blocked_modules = $request->input('blocked_modules', []);
        $lims_role_data = Roles::find($request->role_id);

        Log::info('=== GUARDANDO MÓDULOS BLOQUEADOS ===');
        Log::info('Role ID: ' . $request->role_id);
        Log::info('Blocked Modules Array: ' . print_r($blocked_modules, true));
        Log::info('JSON: ' . json_encode($blocked_modules));

        if ($lims_role_data) {
            $lims_role_data->blocked_modules = json_encode($blocked_modules);
            $lims_role_data->save();

            $lims_role_data->refresh();
            Log::info('Guardado en BD: ' . $lims_role_data->blocked_modules);
        } else {
            Log::warning('No se encontró Roles::find(role_id) para guardar blocked_modules. role_id=' . $request->role_id);
        }

        // 7) Limpia cache al final también (opcional, pero ayuda)
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect('role')->with('message', 'Permisos actualizados con éxito');
    }

    public function destroy($id)
    {
        $lims_role_data = Roles::find($id);
        $lims_role_data->is_active = false;
        $lims_role_data->save();
        return redirect('role')->with('not_permitted', 'Datos eliminado con éxito');
    }
}