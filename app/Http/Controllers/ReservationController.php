<?php

namespace App\Http\Controllers;

use App\Product;
use App\Reservation;
use App\Warehouse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Services\WhatsAppService;

class ReservationController extends Controller
{
  // Agregar métodos para marcar ausencia y cancelar reserva

  /**
   * Marcar una reserva como ausencia (no-show).
   */
  public function markAbsence($id)
  {
    $r = Reservation::find($id);
    if ($r) {
      $r->status = 'absent';
      $r->save();
      return redirect('reservations')->with('message', 'Reserva marcada como ausencia');
    }
    return redirect('reservations')->with('not_permitted', 'Reserva no encontrada');
  }

  /**
   * Cancelar una reserva.
   */
  public function cancelReservation($id)
  {
    $r = Reservation::find($id);
    if ($r) {
      $r->status = 'cancelled';
      $r->save();
      return redirect('reservations')->with('message', 'Reserva cancelada');
    }
    return redirect('reservations')->with('not_permitted', 'Reserva no encontrada');
  }
  public function index()
  {
    $role = Role::find(Auth::user()->role_id);
    if ($role->hasPermissionTo('reservations-index')) {
      $permissions = Role::findByName($role->name)->permissions;
      foreach ($permissions as $permission)
        $all_permission[] = $permission->name;
      if (empty($all_permission))
        $all_permission[] = 'dummy text';
      $employees = \App\Employee::where('is_active', true)->get();
      return view('reservation.index', compact('all_permission', 'employees'));
    } else {
      return redirect()->back()->with('not_permitted', '¡Lo siento! No tienes permiso para acceder a este módulo.');
    }
  }

  public function listData(Request $request)
  {
    $all_permission = $request->input('all_permission', []);
    $filterEmployee = $request->input('employee_id');
    $filterStatus = $request->input('status');
    $columns = [
      1 => 'name',
      2 => 'phone',
      3 => 'reserved_date',
    ];

    $totalData = Reservation::count();
    $totalFiltered = $totalData;

    $limit = $request->input('length') != -1 ? $request->input('length') : $totalData;
    $start = $request->input('start');
    $order = $columns[$request->input('order.0.column')] ?? 'id';
    $dir = $request->input('order.0.dir') ?? 'asc';

    $query = Reservation::query();
    if ($filterEmployee) {
      $query->where('employee_id', $filterEmployee);
    }
    if ($filterStatus) {
      $query->where('status', $filterStatus);
    }
    // Filtrado por fecha: today, tomorrow, custom
    $dateFilter = $request->input('date_filter');
    $customDate = $request->input('custom_date');
    if ($dateFilter) {
      try {
        if ($dateFilter === 'today') {
          $dateToFilter = Carbon::now()->toDateString();
        } elseif ($dateFilter === 'tomorrow') {
          $dateToFilter = Carbon::now()->addDay()->toDateString();
        } elseif ($dateFilter === 'custom' && $customDate) {
          // validate date format
          $dateToFilter = Carbon::parse($customDate)->toDateString();
        }
        if (!empty($dateToFilter)) {
          $query->where('reserved_date', $dateToFilter);
        }
      } catch (\Exception $e) {
        // ignore invalid custom date
      }
    }

    if (empty($request->input('search.value'))) {
      // If no explicit ordering provided, order by proximity to now (closest reservations first)
      if (empty($request->input('order.0.column'))) {
        // Order by absolute seconds difference between reservation datetime and now
        $reservations = $query->orderByRaw("ABS(TIMESTAMPDIFF(SECOND, CONCAT(reserved_date,' ',reserved_time), NOW())) ASC")
          ->offset($start)
          ->limit($limit)
          ->get();
      } else {
        $reservations = $query->offset($start)
          ->limit($limit)
          ->orderBy($order, $dir)
          ->get();
      }
    } else {
      $search = $request->input('search.value');
      $reservations = $query->where(function ($q) use ($search) {
        $q->where('name', 'LIKE', "%{$search}%")
          ->orWhere('phone', 'LIKE', "%{$search}%")
          ->orWhere('status', 'LIKE', "%{$search}%");
      })
        ->offset($start)
        ->limit($limit)
        ->orderBy($order, $dir)
        ->get();

      $totalFiltered = $query->where(function ($q) use ($search) {
        $q->where('name', 'LIKE', "%{$search}%")
          ->orWhere('phone', 'LIKE', "%{$search}%")
          ->orWhere('status', 'LIKE', "%{$search}%");
      })->count();
    }

    $data = [];
    foreach ($reservations as $key => $r) {
      // Si la reserva está pendiente y la fecha/hora ya pasó, marcarla como expirada en BD
      try {
        $rReservedAt = Carbon::parse($r->reserved_date . ' ' . $r->reserved_time);
        if (strtolower($r->status) === 'pending' && $rReservedAt->lt(Carbon::now())) {
          $r->status = 'expired';
          $r->save();
        }
      } catch (\Exception $e) {
        // ignore parse errors here
      }

      $nested = [];
      $nested['key'] = $key;
      $nested['id'] = $r->id;
      $nested['name'] = $r->name;
      $nested['phone'] = $r->phone;
      $nested['service'] = $r->product ? $r->product->name : '-';
      $nested['employee'] = $r->employee ? $r->employee->name : '-';
      $nested['warehouse'] = $r->warehouse ? $r->warehouse->name : '-';
      $nested['reserved_date'] = $r->reserved_date;
      $nested['reserved_time'] = $r->reserved_time;
      $nested['duration'] = $r->duration_minutes;
      // Mostrar estado como badge en español con colores personalizados
      $s = strtolower($r->status);
      switch ($s) {
        case 'pending':
          $statusLabel = 'Pendiente';
          $badgeClass = 'badge badge-warning';
          break;
        case 'confirmed':
          $statusLabel = 'Confirmada';
          $badgeClass = 'badge badge-success';
          break;
        case 'cancelled':
        case 'canceled':
          $statusLabel = 'Cancelada';
          $badgeClass = 'badge badge-danger';
          break;
        case 'completed':
          $statusLabel = 'Completada';
          $badgeClass = 'badge badge-secondary';
          break;
        case 'expired':
          $statusLabel = 'Expirada';
          $badgeClass = 'badge badge-dark';
          break;
        case 'absent':
          $statusLabel = 'Ausente';
          $badgeClass = 'badge badge-danger';
          break;
        default:
          $statusLabel = ucfirst($r->status);
          $badgeClass = 'badge badge-info';
      }
      $nested['status'] = '<span class="' . $badgeClass . '">' . $statusLabel . '</span>';

      $nested['options'] = '<div class="btn-group">'
        . '<button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . trans('file.action') . '<span class="caret"></span></button>'
        . '<ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default">';

      if (in_array('reservations-edit', $all_permission)) {
        $nested['options'] .= '<li><a href="' . route('reservations.edit', $r->id) . '" class="btn btn-link"><i class="dripicons-document-edit"></i> ' . trans('file.edit') . '</a></li>';
      }
      if (in_array('reservations-delete', $all_permission)) {
        $nested['options'] .= \Form::open(['route' => ['reservations.destroy', $r->id], 'method' => 'DELETE'])
          . '<li><button type="submit" class="btn btn-link" onclick="return confirmDelete()"><i class="dripicons-trash"></i> ' . trans('file.delete') . '</button></li>'
          . \Form::close();
      }
      // Marcar ausencia (no-show)
      if (in_array('reservations-edit', $all_permission)) {
        $nested['options'] .= \Form::open(['url' => url('reservations/' . $r->id . '/mark-absence'), 'method' => 'POST'])
          . '<li><button type="submit" class="btn btn-link" onclick="return confirm(\'Marcar como ausencia?\')"><i class="fa fa-user-times"></i> Marcar como ausencia</button></li>'
          . \Form::close();
      }
      // Marcar asistencia (abrir POS o confirmar)
      if (in_array('reservations-edit', $all_permission)) {
        $pId = $r->product_id ?? '';
        $nameEnc = rawurlencode($r->name);
        $phoneEnc = rawurlencode($r->phone);
        $nested['options'] .= '<li><a href="#" class="btn btn-link mark-attendance" data-reservation-id="' . $r->id . '" data-product-id="' . $pId . '" data-customer-name="' . $nameEnc . '" data-customer-phone="' . $phoneEnc . '"><i class="fa fa-check-circle"></i> Marcar asistencia</a></li>';
      }
      // Cancelar reserva
      if (in_array('reservations-edit', $all_permission) || in_array('reservations-delete', $all_permission)) {
        $nested['options'] .= \Form::open(['url' => url('reservations/' . $r->id . '/cancel'), 'method' => 'POST'])
          . '<li><button type="submit" class="btn btn-link" onclick="return confirm(\'¿Cancelar reserva?\')"><i class="dripicons-trash"></i> Cancelar reserva</button></li>'
          . \Form::close();
      }
      $nested['options'] .= '</ul></div>';

      $data[] = $nested;
    }

    $json_data = [
      'draw' => intval($request->input('draw')),
      'recordsTotal' => intval($totalData),
      'recordsFiltered' => intval($totalFiltered),
      'data' => $data,
    ];

    return response()->json($json_data);
  }

  public function create()
  {
    $role = Role::find(Auth::user()->role_id);
    if ($role->hasPermissionTo('reservations-add')) {
      $products = Product::where('is_active', true)->get();
      $warehouses = Warehouse::where('is_active', true)->get();
      $employees = \App\Employee::where('is_active', true)->get();
      return view('reservation.create', compact('products', 'warehouses', 'employees'));
    }
    return redirect()->back()->with('not_permitted', '¡Lo siento! No tienes permiso para acceder a este módulo.');
  }

  public function store(Request $request)
  {
    $this->validate($request, [
      'name' => 'required|max:255',
      'phone' => 'required|max:50',
      'reserved_date' => 'required|date',
      'reserved_time' => 'required',
      'duration_minutes' => 'nullable|integer|min:1',
    ]);
    // Validar que la reserva sea en el futuro
    $reservedDate = $request->input('reserved_date');
    $reservedTime = $request->input('reserved_time');
    try {
      $reservedAt = Carbon::parse($reservedDate . ' ' . $reservedTime);
    } catch (\Exception $e) {
      return redirect()->back()->with('not_permitted', 'Fecha u hora inválida');
    }
    if ($reservedAt->lte(Carbon::now())) {
      return redirect()->back()->with('not_permitted', 'Sólo se permiten reservas para fechas y horas futuras');
    }

    $employeeId = $request->input('employee_id');
    if ($employeeId) {
      $start = $reservedAt;
      $end = $start->copy()->addMinutes(intval($request->input('duration_minutes', 30)));
      $conflict = Reservation::where('employee_id', $employeeId)
        ->where('reserved_date', $request->input('reserved_date'))
        ->get()
        ->filter(function ($r) use ($start, $end) {
          $rStart = Carbon::parse($r->reserved_date . ' ' . $r->reserved_time);
          $rEnd = $rStart->copy()->addMinutes($r->duration_minutes ?? 30);
          return $start->lt($rEnd) && $end->gt($rStart);
        });
      if ($conflict->isNotEmpty()) {
        return redirect()->back()->with('not_permitted', 'El empleado seleccionado no está disponible en ese horario');
      }
    }

    $data = $request->all();
    $data['status'] = $data['status'] ?? 'pending';
    $reservation = Reservation::create($data);

    // Enviar notificación por WhatsApp al crear reserva (si está configurado)
    try {
      $phone = preg_replace('/[^0-9+]/', '', $reservation->phone);
      $phoneDigits = preg_replace('/[^0-9]/', '', $phone);
      // Enviar sin signo '+'; el gateway espera el prefijo de país sin '+'
      if (substr($phoneDigits, 0, 3) === '591') {
        $to = $phoneDigits;
      } else {
        $to = '591' . ltrim($phoneDigits, '0');
      }
      $serviceName = $reservation->product ? $reservation->product->name : '-';
      $warehouseName = $reservation->warehouse ? $reservation->warehouse->name : '-';
      $message = "Hola {$reservation->name}, su reserva para el servicio: {$serviceName} en {$warehouseName} ha sido programada para el {$reservation->reserved_date} a las {$reservation->reserved_time}.";

      $waService = app(WhatsAppService::class);
      $sentOk = $waService->sendMessage($to, $message);
      if (!$sentOk) {
        \Log::warning('No se pudo enviar WA via WhatsAppService (store)', ['reservation_id' => $reservation->id, 'to' => $to]);
      }
    } catch (\Exception $e) {
      \Log::error('Error preparando WA (store)', ['reservation_id' => $reservation->id, 'error' => $e->getMessage()]);
    }

    // Enviar email de confirmación si hay email del cliente
    try {
      if (!empty($reservation->email)) {
        $mailData = [
          'name' => $reservation->name,
          'service' => $reservation->product ? $reservation->product->name : '-',
          'warehouse' => $reservation->warehouse ? $reservation->warehouse->name : '-',
          'date' => $reservation->reserved_date,
          'time' => $reservation->reserved_time,
        ];
        Mail::send('mail.reservation_confirmation', $mailData, function ($m) use ($reservation) {
          $m->to($reservation->email, $reservation->name)->subject('Confirmación de reserva');
        });
      }
    } catch (\Exception $e) {
      \Log::error('Error enviando email de confirmación (store)', ['reservation_id' => $reservation->id, 'error' => $e->getMessage()]);
    }

    return redirect('reservations')->with('create_message', 'Reserva creada con éxito');
  }

  public function edit($id)
  {
    $role = Role::find(Auth::user()->role_id);
    if ($role->hasPermissionTo('reservations-edit')) {
      $reservation = Reservation::findOrFail($id);
      $products = Product::where('is_active', true)->get();
      $warehouses = Warehouse::where('is_active', true)->get();
      return view('reservation.edit', compact('reservation', 'products', 'warehouses'));
    }
    return redirect()->back()->with('not_permitted', '¡Lo siento! No tienes permiso para acceder a este módulo.');
  }

  public function update(Request $request, $id)
  {
    $this->validate($request, [
      'name' => 'required|max:255',
      'phone' => 'required|max:50',
      'reserved_date' => 'required|date',
      'reserved_time' => 'required',
      'duration_minutes' => 'nullable|integer|min:1',
    ]);
    // Validar fecha/hora futura al actualizar
    $reservedDate = $request->input('reserved_date');
    $reservedTime = $request->input('reserved_time');
    try {
      $reservedAt = Carbon::parse($reservedDate . ' ' . $reservedTime);
    } catch (\Exception $e) {
      return redirect()->back()->with('not_permitted', 'Fecha u hora inválida');
    }
    if ($reservedAt->lte(Carbon::now())) {
      return redirect()->back()->with('not_permitted', 'Sólo se permiten reservas para fechas y horas futuras');
    }

    $reservation = Reservation::findOrFail($id);
    $reservation->update($request->all());
    return redirect('reservations')->with('edit_message', 'Reserva actualizada con éxito');
  }

  /**
   * Enviar recordatorios por WhatsApp a reservas seleccionadas.
   * Sólo envía a reservas con estado 'pending', fecha = hoy y hora > ahora.
   */
  public function sendReminders(Request $request)
  {
    $ids = $request->input('reservationIdArray', []);
    if (empty($ids))
      return response()->json('No hay reservas seleccionadas.', 422);

    $now = Carbon::now();
    $today = $now->toDateString();
    $sent = [];
    $skipped = [];

    $reservations = Reservation::whereIn('id', $ids)->get();
    foreach ($reservations as $r) {
      if (strtolower($r->status) !== 'pending' || $r->reserved_date != $today) {
        $skipped[] = $r->id;
        continue;
      }
      try {
        $reservedAt = Carbon::parse($r->reserved_date . ' ' . $r->reserved_time);
      } catch (\Exception $e) {
        $skipped[] = $r->id;
        continue;
      }
      if ($reservedAt->lte($now)) {
        $skipped[] = $r->id;
        continue;
      }

      $phone = preg_replace('/[^0-9+]/', '', $r->phone);
      $phoneDigits = preg_replace('/[^0-9]/', '', $phone);
      if (substr($phoneDigits, 0, 3) === '591') {
        $to = $phoneDigits;
      } else {
        $to = '591' . ltrim($phoneDigits, '0');
      }

      $serviceName = $r->product ? $r->product->name : '-';
      $warehouseName = $r->warehouse ? $r->warehouse->name : '-';
      $message = "Hola {$r->name}, le recordamos su reserva para el servicio: {$serviceName} en la sucursal: {$warehouseName} el {$r->reserved_date} a las {$r->reserved_time}.";

      try {
        $waService = app(WhatsAppService::class);
        $sentOk = $waService->sendMessage($to, $message);
        if ($sentOk) {
          $sent[] = $r->id;
        } else {
          \Log::warning('No se pudo enviar WA via WhatsAppService (sendReminders)', ['id' => $r->id, 'to' => $to]);
          $skipped[] = $r->id;
        }
      } catch (\Exception $e) {
        \Log::error('Error enviando WA en sendReminders', ['id' => $r->id, 'error' => $e->getMessage()]);
        $skipped[] = $r->id;
      }
    }

    return response()->json(['sent' => $sent, 'skipped' => $skipped, 'message' => 'Proceso terminado.']);
  }

  /**
   * Endpoint público: comprobar disponibilidad para una fecha/hora y duración en una sucursal.
   * POST /api/reservations/check-availability
   * Parámetros: reserved_date (Y-m-d), reserved_time (H:i), duration_minutes (int), sucursal_id (int), exclude_id (opcional)
   */
  public function publicCheckAvailability(Request $request)
  {
    $request->validate([
      'reserved_date' => 'required|date',
      'reserved_time' => 'required',
      'duration_minutes' => 'nullable|integer|min:1',
      'sucursal_id' => 'nullable|integer',
      'employee_id' => 'nullable|integer'
    ]);

    $date = $request->input('reserved_date');
    $time = $request->input('reserved_time');
    $duration = intval($request->input('duration_minutes', 30));
    $sucursal = $request->input('sucursal_id');
    $employee = $request->input('employee_id');
    $exclude = $request->input('exclude_id');

    try {
      // Be tolerant with formats (time may include seconds). Use parse() which accepts several formats.
      $start = Carbon::parse($date . ' ' . $time);
    } catch (\Exception $e) {
      return response()->json(['available' => false, 'message' => 'Fecha u hora inválida'], 422);
    }
    $end = $start->copy()->addMinutes($duration);

    // Traer reservas en la misma fecha. Si se especificó empleado, filtrar por empleado;
    // en caso contrario filtrar por sucursal.
    if ($employee) {
      $existing = Reservation::where('employee_id', $employee)
        ->where('reserved_date', $date)
        ->when($exclude, function ($q) use ($exclude) {
          return $q->where('id', '!=', $exclude);
        })
        ->get();
    } else {
      // If no sucursal provided, return unavailable
      if (empty($sucursal)) {
        return response()->json(['available' => false, 'message' => 'sucursal_id o employee_id requerido'], 422);
      }
      $existing = Reservation::where('sucursal_id', $sucursal)
        ->where('reserved_date', $date)
        ->when($exclude, function ($q) use ($exclude) {
          return $q->where('id', '!=', $exclude);
        })
        ->get();
    }

    foreach ($existing as $r) {
      // If reservation has employee assigned, check per-employee overlap
      // Use parse() to accept times with or without seconds
      $rStart = Carbon::parse($r->reserved_date . ' ' . $r->reserved_time);
      $rEnd = $rStart->copy()->addMinutes($r->duration_minutes ?? 30);
      // overlap if start < rEnd && end > rStart
      if ($start->lt($rEnd) && $end->gt($rStart)) {
        // conflict; return with conflicting interval and employee info
        $empName = $r->employee ? $r->employee->name : null;
        $msg = $empName ? "El empleado {$empName} está ocupado de {$rStart->format('H:i')} a {$rEnd->format('H:i')}" : "Hay una reserva existente de {$rStart->format('H:i')} a {$rEnd->format('H:i')}";
        return response()->json([
          'available' => false,
          'message' => $msg,
          'conflict' => [
            'id' => $r->id,
            'from' => $rStart->format('H:i'),
            'to' => $rEnd->format('H:i'),
            'name' => $r->name,
            'employee_id' => $r->employee_id
          ]
        ], 200);
      }
    }

    // If checking for a specific employee and no conflict, calculate until when the employee is free
    if ($employee) {
      $emp = \App\Employee::find($employee);
      // Find the next reservation for this employee on the same date that starts after the requested start
      $next = Reservation::where('employee_id', $employee)
        ->where('reserved_date', $date)
        ->whereRaw("CONCAT(reserved_date,' ',reserved_time) > ?", [$start->toDateTimeString()])
        ->orderBy('reserved_time', 'asc')
        ->first();

      if ($next) {
        $nextStart = Carbon::parse($next->reserved_date . ' ' . $next->reserved_time);
        $msg = $emp ? "El empleado {$emp->name} está disponible hasta las {$nextStart->format('H:i')}" : "Disponible hasta las {$nextStart->format('H:i')}";
        return response()->json(['available' => true, 'message' => $msg, 'until' => $nextStart->format('H:i')], 200);
      } else {
        // No more reservations that day
        $msg = $emp ? "El empleado {$emp->name} no tiene más reservas ese día (disponible el resto del día)." : "Disponible el resto del día.";
        return response()->json(['available' => true, 'message' => $msg], 200);
      }
    }

    return response()->json(['available' => true, 'message' => 'Disponible'], 200);
  }

  /**
   * Crear reserva pública: asigna el primer empleado libre en la sucursal.
   * POST /api/reservations/book
   * Parámetros JSON: name, phone, reserved_date (Y-m-d), reserved_time (H:i), duration_minutes, product_id (opcional), sucursal_id
   */
  public function publicCreateReservation(Request $request)
  {
    $request->validate([
      'name' => 'required|max:255',
      'phone' => 'required|max:50',
      'email' => 'nullable|email|max:255',
      'reserved_date' => 'required|date',
      'reserved_time' => 'required',
      'duration_minutes' => 'nullable|integer|min:1',
      'sucursal_id' => 'required|integer'
    ]);

    $date = $request->input('reserved_date');
    $time = $request->input('reserved_time');
    $duration = intval($request->input('duration_minutes', 30));
    $sucursal = $request->input('sucursal_id');

    try {
      $start = Carbon::parse($date . ' ' . $time);
    } catch (\Exception $e) {
      return response()->json(['error' => 'Fecha u hora inválida'], 422);
    }
    $end = $start->copy()->addMinutes($duration);

    // Obtener empleados activos asignados a la sucursal; si ninguno, tomar todos activos
    $employees = \App\Employee::where([['is_active', true], ['warehouse_id', $sucursal]])->get();
    if ($employees->isEmpty()) {
      $employees = \App\Employee::where('is_active', true)->get();
    }

    // Revisar disponibilidad por empleado
    $assignedEmployee = null;
    foreach ($employees as $emp) {
      $conflict = Reservation::where('employee_id', $emp->id)
        ->where('reserved_date', $date)
        ->get()
        ->filter(function ($r) use ($start, $end) {
          $rStart = Carbon::parse($r->reserved_date . ' ' . $r->reserved_time);
          $rEnd = $rStart->copy()->addMinutes($r->duration_minutes ?? 30);
          return $start->lt($rEnd) && $end->gt($rStart);
        });
      if ($conflict->isEmpty()) {
        $assignedEmployee = $emp;
        break;
      }
    }

    if (!$assignedEmployee) {
      return response()->json(['error' => 'No hay empleados disponibles en este horario'], 200);
    }

    // Crear reserva y asignar empleado
    $data = $request->only(['name', 'phone', 'email', 'product_id', 'sucursal_id', 'duration_minutes', 'notes']);
    $data['reserved_date'] = $date;
    $data['reserved_time'] = $time;
    $data['employee_id'] = $assignedEmployee->id;
    $data['status'] = 'pending';

    $reservation = Reservation::create($data);
    // Intentar enviar notificación por WhatsApp al cliente usando WhatsAppService
    try {
      $phone = preg_replace('/[^0-9+]/', '', $reservation->phone);
      $phoneDigits = preg_replace('/[^0-9]/', '', $phone);
      // Enviar sin signo '+'; el gateway espera el prefijo de país sin '+'
      if (substr($phoneDigits, 0, 3) === '591') {
        $to = $phoneDigits;
      } else {
        $to = '591' . ltrim($phoneDigits, '0');
      }
      $serviceName = $reservation->product ? $reservation->product->name : '-';
      $warehouseName = $reservation->warehouse ? $reservation->warehouse->name : '-';
      $message = "Hola {$reservation->name}, su reserva para el servicio: {$serviceName} en {$warehouseName} ha sido programada para el {$reservation->reserved_date} a las {$reservation->reserved_time}.";

      $waService = app(WhatsAppService::class);
      $sentOk = $waService->sendMessage($to, $message);
      if (!$sentOk) {
        \Log::warning('No se pudo enviar WA via WhatsAppService (publicCreateReservation)', ['reservation_id' => $reservation->id, 'to' => $to]);
      }
    } catch (\Exception $e) {
      \Log::error('Error preparando WA para publicCreateReservation', ['reservation_id' => $reservation->id, 'error' => $e->getMessage()]);
    }

    // Enviar email de confirmación si hay email del cliente
    try {
      if (!empty($reservation->email)) {
        $mailData = [
          'name' => $reservation->name,
          'service' => $reservation->product ? $reservation->product->name : '-',
          'warehouse' => $reservation->warehouse ? $reservation->warehouse->name : '-',
          'date' => $reservation->reserved_date,
          'time' => $reservation->reserved_time,
        ];
        Mail::send('mail.reservation_confirmation', $mailData, function ($m) use ($reservation) {
          $m->to($reservation->email, $reservation->name)->subject('Confirmación de reserva');
        });
      }
    } catch (\Exception $e) {
      \Log::error('Error enviando email de confirmación', ['reservation_id' => $reservation->id, 'error' => $e->getMessage()]);
    }

    return response()->json(['reservation' => $reservation, 'assigned_employee' => ['id' => $assignedEmployee->id, 'name' => $assignedEmployee->name]], 201);
  }

  /**
   * Endpoint público: devolver franjas horarias de un día con disponibilidad.
   * GET /api/reservations/timeslots?date=YYYY-MM-DD&duration_minutes=30&sucursal_id=1
   */
  public function publicTimeSlots(Request $request)
  {
    $date = $request->query('date', Carbon::now()->toDateString());
    $duration = intval($request->query('duration_minutes', 30));
    $sucursal = $request->query('sucursal_id');

    if (!$sucursal) {
      return response()->json(['error' => 'sucursal_id requerido'], 422);
    }

    // Rango de 08:00 a 21:00
    $start = Carbon::parse($date . ' 08:00');
    $endLimit = Carbon::parse($date . ' 21:00');

    $slots = [];
    $slot = $start->copy();
    while ($slot->lte($endLimit->copy()->subMinutes($duration))) {
      $slotEnd = $slot->copy()->addMinutes($duration);

      // comprobar solapamiento con reservas existentes
      // ahora comprobamos si al menos un empleado está libre en ese slot
      $employees = \App\Employee::where([['is_active', true], ['warehouse_id', $sucursal]])->get();
      if ($employees->isEmpty()) {
        $employees = \App\Employee::where('is_active', true)->get();
      }
      $slotAvailable = false;
      foreach ($employees as $emp) {
        $conflict = Reservation::where('employee_id', $emp->id)
          ->where('reserved_date', $date)->get()
          ->filter(function ($r) use ($slot, $slotEnd) {
            $rStart = Carbon::parse($r->reserved_date . ' ' . $r->reserved_time);
            $rEnd = $rStart->copy()->addMinutes($r->duration_minutes ?? 30);
            return $slot->lt($rEnd) && $slotEnd->gt($rStart);
          });
        if ($conflict->isEmpty()) {
          $slotAvailable = true;
          break;
        }
      }

      $slots[] = ['time' => $slot->format('H:i'), 'available' => $slotAvailable];
      $slot->addMinutes(30);
    }

    return response()->json(['date' => $date, 'duration_minutes' => $duration, 'slots' => $slots]);
  }

  public function deleteBySelection(Request $request)
  {
    $ids = $request['reservationIdArray'] ?? [];
    foreach ($ids as $id) {
      $r = Reservation::find($id);
      if ($r)
        $r->delete();
    }
    return 'Reserva(s) eliminada(s) con éxito!';
  }

  public function destroy($id)
  {
    $r = Reservation::find($id);
    if ($r) {
      $r->delete();
      return redirect('reservations')->with('not_permitted', 'Dato eliminado con éxito');
    }
    return redirect('reservations')->with('not_permitted', 'Error al eliminar, no encontrado');
  }

  /**
   * Marcar asistencia (check-in) desde UI.
   * Endpoint: POST /reservations/{id}/mark-attendance
   * Body (optional): sale_id
   */
  public function markAttendance(Request $request, $id)
  {
    $r = Reservation::find($id);
    if (!$r) {
      return response()->json(['success' => false, 'message' => 'Reserva no encontrada'], 404);
    }
    // marcar como completada/asistida
    $r->status = 'completed';
    if ($request->has('sale_id')) {
      // si existe columna sale_id la guardamos, otherwise ignore
      if (Schema::hasColumn('reservations', 'sale_id')) {
        $r->sale_id = $request->input('sale_id');
      }
    }
    $r->save();

    return response()->json(['success' => true, 'message' => 'Reserva marcada como asistida']);
  }
}
