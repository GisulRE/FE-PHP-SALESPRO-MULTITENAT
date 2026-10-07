<?php

namespace App\Http\Controllers\Api;

use App\Attendance;
use App\AttentionShift;
use App\Category;
use App\Customer;
use App\CustomerGroup;
use App\Employee;
use App\GeneralSetting;
use App\HrmSetting;
use App\PreSale;
use App\Product;
use App\Product_Presale;
use App\ShiftEmployee;
use App\Warehouse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MobilePreSaleController extends Controller
{
    /**
     * Calcula la distancia entre dos coordenadas geográficas usando la fórmula de Haversine.
     * Retorna la distancia en metros.
     */
    private function calculateHaversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Radio de la Tierra en metros
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    /**
     * Listado de sucursales activas con configuración de geocerca y WiFi.
     * GET /api/v1/mobile/warehouses
     */
    public function getWarehouses()
    {
        $warehouses = Warehouse::where('is_active', true)
            ->select([
                'id', 'name', 'phone', 'address',
                'latitude', 'longitude', 'geofence_radius',
                'wifi_ssid', 'wifi_bssid', 'attendance_validation_mode'
            ])
            ->get();

        return response()->json([
            'success' => true,
            'warehouses' => $warehouses
        ]);
    }

    /**
     * Listado de empleados activos habilitados para pre-venta y turnos.
     * GET /api/v1/mobile/employees
     */
    public function getEmployees(Request $request)
    {
        $query = Employee::where('is_active', true);

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        $employees = $query->orderBy('name', 'asc')->get();

        $data = $employees->map(function ($emp) {
            return [
                'id' => $emp->id,
                'name' => $emp->name,
                'phone_number' => $emp->phone_number,
                'warehouse_id' => $emp->warehouse_id,
                'has_pin' => !empty($emp->attendance_pin),
                'pre_sale' => (bool)$emp->pre_sale,
                'image' => $emp->image ? url('public/images/employee', $emp->image) : null,
            ];
        });

        return response()->json([
            'success' => true,
            'employees' => $data
        ]);
    }

    /**
     * Autenticación / Verificación rápida de empleado mediante PIN numérico.
     * POST /api/v1/mobile/auth/login-pin
     */
    public function loginPin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pin' => 'nullable|string',
            'employee_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Parámetros de acceso no válidos.',
                'errors' => $validator->errors()
            ], 422);
        }

        $pin = (string)$request->input('pin', '');
        $employeeId = $request->input('employee_id');

        $employee = null;
        if ($employeeId) {
            $employee = Employee::where('is_active', true)->find($employeeId);
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Empleado no encontrado o inactivo.'
                ], 404);
            }
            if (!empty($employee->attendance_pin)) {
                if (empty($pin) || !Hash::check($pin, $employee->attendance_pin)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Código PIN incorrecto o requerido.'
                    ], 401);
                }
            }
        } else {
            if (empty($pin)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El código PIN es obligatorio cuando no se selecciona empleado.'
                ], 422);
            }

            // Buscar empleado que coincida con el PIN
            $activeEmployees = Employee::where('is_active', true)
                ->whereNotNull('attendance_pin')
                ->get();

            foreach ($activeEmployees as $emp) {
                if (Hash::check($pin, $emp->attendance_pin)) {
                    $employee = $emp;
                    break;
                }
            }

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'PIN incorrecto o no asociado a ningún empleado activo.'
                ], 401);
            }
        }

        // Obtener estado de turno y asistencia de hoy
        $today = date('Y-m-d');
        $activeAttendance = Attendance::whereDate('date', $today)
            ->where('employee_id', $employee->id)
            ->whereNull('checkout')
            ->first();

        $shiftEmployee = ShiftEmployee::whereDate('created_at', $today)
            ->where('employee_id', $employee->id)
            ->first();

        $warehouse = Warehouse::find($employee->warehouse_id) ?: Warehouse::where('is_active', true)->first();

        return response()->json([
            'success' => true,
            'message' => 'Acceso autorizado con éxito.',
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'phone_number' => $employee->phone_number,
                'warehouse_id' => $employee->warehouse_id,
                'image' => $employee->image ? url('public/images/employee', $employee->image) : null,
                'pre_sale' => (bool)$employee->pre_sale,
            ],
            'warehouse' => $warehouse ? [
                'id' => $warehouse->id,
                'name' => $warehouse->name,
                'address' => $warehouse->address,
                'latitude' => $warehouse->latitude,
                'longitude' => $warehouse->longitude,
                'geofence_radius' => $warehouse->geofence_radius ?: 50,
                'wifi_ssid' => $warehouse->wifi_ssid,
                'wifi_bssid' => $warehouse->wifi_bssid,
                'attendance_validation_mode' => $warehouse->attendance_validation_mode ?: 'any',
            ] : null,
            'attendance_status' => [
                'is_checked_in' => $activeAttendance !== null,
                'checkin_time' => $activeAttendance ? $activeAttendance->checkin : null,
                'is_on_shift' => $shiftEmployee !== null,
                'shift_status' => $shiftEmployee ? $shiftEmployee->status : null,
                'queue_position' => $shiftEmployee ? $shiftEmployee->position : null,
            ]
        ]);
    }

    /**
     * Obtener el estado actual de asistencia y turno del empleado hoy.
     * GET /api/v1/mobile/attendance/status/{employeeId}
     */
    public function attendanceStatus($employeeId)
    {
        $today = date('Y-m-d');
        $employee = Employee::find($employeeId);
        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Empleado no encontrado'], 404);
        }

        $activeAttendance = Attendance::whereDate('date', $today)
            ->where('employee_id', $employeeId)
            ->whereNull('checkout')
            ->first();

        $lastAttendance = Attendance::whereDate('date', $today)
            ->where('employee_id', $employeeId)
            ->latest()
            ->first();

        $shiftEmployee = ShiftEmployee::whereDate('created_at', $today)
            ->where('employee_id', $employeeId)
            ->first();

        return response()->json([
            'success' => true,
            'employee_id' => (int)$employeeId,
            'employee_name' => $employee->name,
            'is_checked_in' => $activeAttendance !== null,
            'checkin_time' => $activeAttendance ? $activeAttendance->checkin : ($lastAttendance ? $lastAttendance->checkin : null),
            'checkout_time' => $lastAttendance ? $lastAttendance->checkout : null,
            'is_on_shift' => $shiftEmployee !== null,
            'shift_status' => $shiftEmployee ? $shiftEmployee->status : null, // 1: Disponible en cola, 0: En atención
            'queue_position' => $shiftEmployee ? $shiftEmployee->position : null,
        ]);
    }

    /**
     * Marcaje de asistencia (Check-in / Check-out) con validación de geocerca GPS y WiFi.
     * POST /api/v1/mobile/attendance/toggle
     */
    public function toggleAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|integer|exists:employees,id',
            'pin' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_mocked' => 'nullable|boolean',
            'wifi_ssid' => 'nullable|string',
            'wifi_bssid' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de marcaje inválidos.',
                'errors' => $validator->errors()
            ], 422);
        }

        $employee = Employee::find($request->employee_id);
        if (!$employee || !$employee->is_active) {
            return response()->json(['success' => false, 'message' => 'Empleado inactivo o no encontrado.'], 404);
        }

        // 1. Verificación de PIN estricta si el empleado tiene PIN configurado
        if (!empty($employee->attendance_pin)) {
            if (!$request->filled('pin') || !Hash::check($request->pin, $employee->attendance_pin)) {
                return response()->json([
                    'success' => false,
                    'error_code' => 'INVALID_PIN',
                    'message' => 'Código PIN incorrecto o no proporcionado.'
                ], 403);
            }
        }

        // 2. Detección Anti-Mocking (Fake GPS)
        if ($request->boolean('is_mocked')) {
            Log::warning('[MobilePreSaleController] Intento de marcaje con GPS simulado', [
                'employee_id' => $employee->id,
                'lat' => $request->latitude,
                'lng' => $request->longitude,
            ]);

            return response()->json([
                'success' => false,
                'error_code' => 'MOCK_LOCATION_DETECTED',
                'message' => 'Se ha detectado una ubicación GPS simulada (Fake GPS). Por seguridad el marcaje no es permitido.'
            ], 403);
        }

        // 3. Validación de Presencia (Geocerca GPS y Red WiFi)
        $warehouse = Warehouse::find($employee->warehouse_id) ?: Warehouse::where('is_active', true)->first();
        $validationMode = $warehouse ? ($warehouse->attendance_validation_mode ?: 'any') : 'none';
        $geofenceRadius = $warehouse ? ($warehouse->geofence_radius ?: 50) : 50;

        $distance = null;
        $isWithinRadius = true;
        if ($warehouse && $warehouse->latitude !== null && $warehouse->longitude !== null) {
            if ($request->filled('latitude') && $request->filled('longitude')) {
                $distance = $this->calculateHaversineDistance(
                    (float)$request->latitude,
                    (float)$request->longitude,
                    (float)$warehouse->latitude,
                    (float)$warehouse->longitude
                );
                $isWithinRadius = ($distance <= $geofenceRadius);
            } else {
                // No se proporcionó GPS cuando la sucursal tiene coordenadas
                $isWithinRadius = false;
            }
        }

        $wifiMatched = false;
        if ($warehouse && ($warehouse->wifi_ssid || $warehouse->wifi_bssid)) {
            $clientSsid = trim($request->input('wifi_ssid', ''));
            $clientBssid = trim($request->input('wifi_bssid', ''));
            if ($warehouse->wifi_ssid && strcasecmp(trim($warehouse->wifi_ssid), $clientSsid) === 0) {
                $wifiMatched = true;
            }
            if ($warehouse->wifi_bssid && strcasecmp(trim($warehouse->wifi_bssid), $clientBssid) === 0) {
                $wifiMatched = true;
            }
        }

        // Aplicar reglas según el modo de validación configurado
        if ($validationMode === 'gps') {
            if (!$isWithinRadius) {
                return response()->json([
                    'success' => false,
                    'error_code' => 'OUTSIDE_GEOFENCE',
                    'message' => "Fuera del rango permitido de la sucursal. Distancia actual: " . round($distance ?? 0, 1) . "m (máximo {$geofenceRadius}m).",
                    'distance' => $distance !== null ? round($distance, 1) : null,
                    'geofence_radius' => $geofenceRadius
                ], 422);
            }
        } elseif ($validationMode === 'wifi') {
            if (!$wifiMatched) {
                return response()->json([
                    'success' => false,
                    'error_code' => 'WIFI_NOT_MATCHED',
                    'message' => "Debe estar conectado a la red WiFi autorizada de la sucursal ({$warehouse->wifi_ssid})."
                ], 422);
            }
        } elseif ($validationMode === 'any') {
            $hasRules = $warehouse && (($warehouse->latitude && $warehouse->longitude) || $warehouse->wifi_ssid);
            if ($hasRules && !$isWithinRadius && !$wifiMatched) {
                return response()->json([
                    'success' => false,
                    'error_code' => 'PRESENCE_VALIDATION_FAILED',
                    'message' => "No se pudo validar la presencia en la sucursal. Distancia GPS: " . ($distance !== null ? round($distance, 1) . "m" : "desconocida") . ", y red WiFi no coincide.",
                    'distance' => $distance !== null ? round($distance, 1) : null,
                    'geofence_radius' => $geofenceRadius
                ], 422);
            }
        }

        // 4. Proceso de Check-in o Check-out
        try {
            DB::beginTransaction();

            $today = date('Y-m-d');
            $activeAttendance = Attendance::whereDate('date', $today)
                ->where('employee_id', $employee->id)
                ->whereNull('checkout')
                ->first();

            $hrmSetting = HrmSetting::latest()->first() ?: (object)['checkin' => '09:00'];
            $standardCheckin = $hrmSetting->checkin ?? '09:00';
            $companyId = $employee->company_id ?: ($warehouse && $warehouse->company_id ? $warehouse->company_id : null);

            if (!$activeAttendance) {
                // Registrar Entrada (Check-in)
                $timeNow = date('h:ia');
                $diff = strtotime($standardCheckin) - strtotime($timeNow);

                $attendanceData = [
                    'date' => $today,
                    'employee_id' => $employee->id,
                    'user_id' => $employee->user_id ?: 1,
                    'checkin' => $timeNow,
                    'checkout' => null,
                    'company_id' => $companyId,
                    'status' => ($diff >= 0) ? 1 : 0, // 1: a tiempo, 0: tarde
                    'note' => 'Mobile App. ' . ($distance !== null ? 'Distancia: ' . round($distance, 1) . 'm' : ''),
                ];

                $attendance = Attendance::create($attendanceData);

                // Añadir a la cola de turnos de estilistas/peluqueros
                $lastPosition = ShiftEmployee::whereDate('created_at', $today)->max('position');
                $newPosition = $lastPosition ? $lastPosition + 1 : 1;

                ShiftEmployee::create([
                    'employee_id' => $employee->id,
                    'status' => 1, // 1: Disponible en cola
                    'position' => $newPosition,
                    'company_id' => $companyId,
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'type' => 'checkin',
                    'message' => '¡Entrada registrada con éxito!',
                    'time' => $timeNow,
                    'status' => $attendance->status == 1 ? 'Puntual' : 'Retardo',
                    'queue_position' => $newPosition,
                    'distance' => $distance !== null ? round($distance, 1) : null
                ]);
            } else {
                // Registrar Salida (Check-out)
                $timeNow = date('h:ia');
                $activeAttendance->checkout = $timeNow;
                $activeAttendance->save();

                // Quitar de la cola de turnos
                ShiftEmployee::whereDate('created_at', $today)
                    ->where('employee_id', $employee->id)
                    ->delete();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'type' => 'checkout',
                    'message' => '¡Salida registrada con éxito!',
                    'time' => $timeNow,
                    'distance' => $distance !== null ? round($distance, 1) : null
                ]);
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[MobilePreSaleController@toggleAttendance] Error', [
                'employee_id' => $employee->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno al registrar asistencia: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Catálogo táctil de servicios / cortes activos organizados por categorías.
     * GET /api/v1/mobile/catalog
     */
    public function getCatalog(Request $request)
    {
        // Categorías activas
        $categories = Category::where('is_active', true)
            ->select(['id', 'name', 'image', 'parent_id'])
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($cat) {
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'image' => $cat->image ? url('public/images/category', $cat->image) : null,
                ];
            });

        // Productos / Cortes activos no insumos
        $productsQuery = Product::where('is_active', true)
            ->whereIn('type', ['service', 'standard', 'digital']);

        if ($request->filled('category_id')) {
            $productsQuery->where('category_id', $request->category_id);
        }

        $products = $productsQuery->orderBy('name', 'asc')
            ->get(['id', 'name', 'code', 'price', 'type', 'category_id', 'image', 'is_variant'])
            ->map(function ($prod) {
                return [
                    'id' => $prod->id,
                    'name' => $prod->name,
                    'code' => $prod->code,
                    'price' => (float)$prod->price,
                    'type' => $prod->type,
                    'category_id' => $prod->category_id,
                    'is_variant' => (bool)$prod->is_variant,
                    'image' => $prod->image ? url('public/images/product', $prod->image) : null,
                ];
            });

        return response()->json([
            'success' => true,
            'categories' => $categories,
            'products' => $products,
        ]);
    }

    /**
     * Búsqueda rápida de clientes.
     * GET /api/v1/mobile/customers/search
     */
    public function searchCustomers(Request $request)
    {
        $q = trim($request->input('q', ''));
        $query = Customer::where('is_active', true);

        if (!empty($q)) {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'LIKE', "%{$q}%")
                    ->orWhere('phone_number', 'LIKE', "%{$q}%")
                    ->orWhere('tax_no', 'LIKE', "%{$q}%")
                    ->orWhere('valor_documento', 'LIKE', "%{$q}%");
            });
        }

        $customers = $query->orderBy('name', 'asc')->limit(30)->get([
            'id', 'name', 'phone_number', 'tax_no', 'valor_documento', 'email'
        ]);

        return response()->json([
            'success' => true,
            'customers' => $customers
        ]);
    }

    /**
     * Creación exprés de cliente desde la comanda móvil.
     * POST /api/v1/mobile/customers/quick-create
     */
    public function quickCreateCustomer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:191',
            'phone_number' => 'nullable|string|max:50',
            'tax_no' => 'nullable|string|max:50',
            'valor_documento' => 'nullable|string|max:50',
            'tipo_documento' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de cliente no válidos.',
                'errors' => $validator->errors()
            ], 422);
        }

        $defaultGroup = CustomerGroup::where('is_active', true)->first();
        $taxNo = $request->input('tax_no') ?: $request->input('valor_documento');

        $customer = Customer::create([
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'tax_no' => $taxNo,
            'valor_documento' => $request->input('valor_documento') ?: $taxNo,
            'tipo_documento' => $request->input('tipo_documento', 1),
            'customer_group_id' => $defaultGroup ? $defaultGroup->id : 1,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cliente registrado exitosamente.',
            'customer' => $customer
        ]);
    }

    /**
     * Guardar Pre-Venta con numeración PRV-... y asignación de empleado y turno.
     * POST /api/v1/mobile/presales
     */
    public function storePreSale(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|integer|exists:customers,id',
            'employee_id' => 'nullable|integer|exists:employees,id',
            'warehouse_id' => 'nullable|integer|exists:warehouses,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.net_unit_price' => 'required|numeric|min:0',
            'items.*.total' => 'required|numeric|min:0',
            'order_discount' => 'nullable|numeric|min:0',
            'tips' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de comanda incompletos.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $today = date('Y-m-d');
            $employeeId = $request->input('employee_id');
            $employee = $employeeId ? Employee::find($employeeId) : null;
            $warehouseId = $request->input('warehouse_id') ?: ($employee ? $employee->warehouse_id : 1);

            // Generar correlativo PRV-0000000X
            $lastPresale = PreSale::orderBy('id', 'desc')->first();
            if ($lastPresale && !empty($lastPresale->reference_no)) {
                $parts = explode("-", $lastPresale->reference_no);
                $num = isset($parts[1]) ? intval(ltrim($parts[1], "0")) : $lastPresale->id;
                $num++;
                $refNo = 'PRV-' . str_pad($num, 8, "0", STR_PAD_LEFT);
            } else {
                $refNo = 'PRV-' . str_pad(1, 8, "0", STR_PAD_LEFT);
            }

            // Buscar turno activo del empleado
            $turnoData = null;
            if ($request->filled('attentionshift_id')) {
                $turnoData = AttentionShift::find($request->attentionshift_id);
            } elseif ($employeeId) {
                $turnoData = AttentionShift::where([
                    ['employee_id', $employeeId],
                    ['status', 1]
                ])->whereDate('created_at', $today)->first();
            }

            $items = $request->input('items', []);
            $totalQty = array_sum(array_column($items, 'qty'));
            $grandTotal = $request->input('grand_total', array_sum(array_column($items, 'total')));
            $orderDiscount = (float)$request->input('order_discount', 0);
            $tips = (float)$request->input('tips', 0);

            $companyId = ($employee && $employee->company_id)
                ? $employee->company_id
                : ($warehouse && $warehouse->company_id ? $warehouse->company_id : null);

            $presale = PreSale::create([
                'reference_no' => $refNo,
                'user_id' => $employee ? ($employee->user_id ?: 1) : 1,
                'employee_id' => $employeeId,
                'customer_id' => $request->customer_id,
                'warehouse_id' => $warehouseId,
                'attentionshift_id' => $turnoData ? $turnoData->id : null,
                'item' => count($items),
                'total_qty' => $totalQty,
                'grand_total' => $grandTotal,
                'order_discount' => $orderDiscount,
                'total_discount' => $orderDiscount,
                'shipping_cost' => 0,
                'tips' => $tips,
                'status' => 1,
                'company_id' => $companyId,
            ]);

            $fallbackCategoryId = Category::where('is_active', true)->value('id') ?: 1;

            foreach ($items as $item) {
                $prod = Product::find($item['product_id']);
                $saleUnitId = ($prod && $prod->sale_unit_id) ? $prod->sale_unit_id : (($prod && $prod->unit_id) ? $prod->unit_id : 0);
                Product_Presale::create([
                    'presale_id' => $presale->id,
                    'product_id' => $item['product_id'],
                    'category_id' => ($prod && $prod->category_id) ? $prod->category_id : $fallbackCategoryId,
                    'variant_id' => $item['variant_id'] ?? null,
                    'employee_id' => $item['employee_id'] ?? $employeeId,
                    'qty' => $item['qty'],
                    'sale_unit_id' => $saleUnitId,
                    'net_unit_price' => $item['net_unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'tax_rate' => 0,
                    'tax' => 0,
                    'total' => $item['total'],
                    'company_id' => $companyId,
                ]);
            }

            // Liberación y rotación de cola de turnos
            if ($turnoData) {
                if ($turnoData->employee_id) {
                    $last = ShiftEmployee::whereDate('created_at', $today)->max('position');
                    $newPosition = $last ? $last + 1 : 1;

                    $empShift = ShiftEmployee::where([
                        ['status', 0],
                        ['employee_id', $turnoData->employee_id]
                    ])->whereDate('created_at', $today)->first();

                    if ($empShift) {
                        $empShift->status = 1; // Vuelve a estar libre
                        $empShift->position = $newPosition;
                        $empShift->save();
                    }
                }
                $turnoData->status = 3; // Finalizado
                $turnoData->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pre-venta registrada exitosamente.',
                'presale_id' => $presale->id,
                'reference_no' => $presale->reference_no,
                'grand_total' => (float)$presale->grand_total,
                'created_at' => $presale->created_at->format('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[MobilePreSaleController@storePreSale] Error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar pre-venta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Endpoint de payload para impresión térmica POS (Bluetooth y Red ESC/POS).
     * GET /api/v1/mobile/presales/{id}/ticket
     */
    public function getTicketPayload($id)
    {
        $presale = PreSale::with(['customer', 'warehouse', 'attentionshift'])->find($id);

        if (!$presale) {
            return response()->json([
                'success' => false,
                'message' => 'Pre-venta no encontrada.'
            ], 404);
        }

        $generalSetting = GeneralSetting::latest()->first();
        $businessName = $generalSetting ? $generalSetting->site_title : 'SISTEMA POS';

        $warehouse = $presale->warehouse;
        $customer = $presale->customer;
        $employee = $presale->employee_id ? Employee::find($presale->employee_id) : null;

        $productPresales = Product_Presale::where('presale_id', $id)
            ->with('product')
            ->get();

        $items = [];
        foreach ($productPresales as $item) {
            $prodName = $item->product ? $item->product->name : 'Servicio';
            $items[] = [
                'name' => $prodName,
                'qty' => (float)$item->qty,
                'price' => (float)$item->net_unit_price,
                'total' => (float)$item->total,
                'formatted_price' => number_format((float)$item->net_unit_price, 2),
                'formatted_total' => number_format((float)$item->total, 2),
            ];
        }

        $formattedDate = $presale->created_at ? $presale->created_at->format('d/m/Y') : date('d/m/Y');
        $formattedTime = $presale->created_at ? $presale->created_at->format('H:i:s') : date('H:i:s');
        $customerName = $customer ? $customer->name : 'Cliente General';
        $customerDoc = $customer ? ($customer->tax_no ?: $customer->valor_documento ?: $customer->phone_number) : '';
        $employeeName = $employee ? $employee->name : 'Sin Empleado Asignado';
        $shiftRef = $presale->attentionshift ? $presale->attentionshift->reference_nro : '';

        // Formato para QR: comanda, total, empleado y fecha
        $qrPayload = sprintf(
            "%s|TOTAL:%.2f|EMP:%s|FECHA:%s %s",
            $presale->reference_no,
            $presale->grand_total,
            $employeeName,
            $formattedDate,
            $formattedTime
        );

        return response()->json([
            'success' => true,
            'ticket' => [
                'presale_id' => $presale->id,
                'reference_no' => $presale->reference_no,
                'business_name' => $businessName,
                'warehouse_name' => $warehouse ? $warehouse->name : 'Sucursal Principal',
                'warehouse_address' => $warehouse ? $warehouse->address : '',
                'warehouse_phone' => $warehouse ? $warehouse->phone : '',
                'date' => $formattedDate,
                'time' => $formattedTime,
                'shift_ref' => $shiftRef,
                'customer_name' => $customerName,
                'customer_doc' => $customerDoc,
                'employee_name' => $employeeName,
                'items' => $items,
                'item_count' => count($items),
                'order_discount' => (float)$presale->order_discount,
                'tips' => (float)$presale->tips,
                'grand_total' => (float)$presale->grand_total,
                'formatted_grand_total' => number_format((float)$presale->grand_total, 2),
                'barcode_data' => $presale->reference_no,
                'qr_data' => $qrPayload,
            ]
        ]);
    }
}
