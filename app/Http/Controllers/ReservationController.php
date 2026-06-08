<?php

namespace App\Http\Controllers;

use App\Product;
use App\Reservation;
use App\Warehouse;
use App\Employee;
use App\EmployeeReservationSchedule;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Services\WhatsAppService;
use App\PosSetting;

class ReservationController extends Controller
{
  // Agregar métodos para marcar ausencia y cancelar reserva

  private function getEmployeeDaySchedules($employeeId, $date)
  {
    $day = Carbon::parse($date)->dayOfWeek;
    $schedules = EmployeeReservationSchedule::where('employee_id', $employeeId)
      ->where('day_of_week', $day)
      ->where('is_enabled', true)
      ->orderBy('start_time', 'asc')
      ->get();

    $result = [];
    foreach ($schedules as $schedule) {
      if (empty($schedule->start_time) || empty($schedule->end_time)) {
        continue;
      }
      $interval = intval($schedule->interval_minutes ?: 30);
      $start = Carbon::parse($date . ' ' . $schedule->start_time);
      $end = Carbon::parse($date . ' ' . $schedule->end_time);
      if ($start->gte($end)) {
        continue;
      }
      $result[] = [
        'start' => $start,
        'end' => $end,
        'interval' => max($interval, 5),
      ];
    }

    return $result;
  }

  private function validateEmployeeScheduleWindow($employeeId, Carbon $start, Carbon $end)
  {
    $schedules = $this->getEmployeeDaySchedules($employeeId, $start->toDateString());
    if (empty($schedules)) {
      return [
        'ok' => false,
        'message' => 'El empleado no tiene horario habilitado para ese día',
      ];
    }

    foreach ($schedules as $schedule) {
      if ($start->lt($schedule['start']) || $end->gt($schedule['end'])) {
        continue;
      }
      $minutesFromStart = $schedule['start']->diffInMinutes($start, false);
      if ($minutesFromStart < 0) {
        continue;
      }
      if (($minutesFromStart % $schedule['interval']) !== 0) {
        continue;
      }
      return ['ok' => true, 'schedule' => $schedule];
    }

    return [
      'ok' => false,
      'message' => 'El horario solicitado está fuera de los turnos o intervalos configurados del empleado',
    ];
  }

  private function buildEmployeeSlots($employeeId, $date, $duration)
  {
    $schedules = $this->getEmployeeDaySchedules($employeeId, $date);
    if (empty($schedules)) {
      return [
        'available' => false,
        'message' => 'El empleado no tiene horarios habilitados para ese día',
        'hora_inicio' => null,
        'hora_fin' => null,
        'intervalo_minutos' => null,
        'slots' => [],
      ];
    }

    $slots = [];
    foreach ($schedules as $schedule) {
      // Regla solicitada: la duración efectiva de cada slot es igual al intervalo.
      $effectiveDuration = intval($schedule['interval']);
      $slot = $schedule['start']->copy();
      while ($slot->lte($schedule['end']->copy()->subMinutes($effectiveDuration))) {
        $slotEnd = $slot->copy()->addMinutes($effectiveDuration);
        $conflict = Reservation::where('employee_id', $employeeId)
          ->where('reserved_date', $date)
          ->whereNotIn('status', ['cancelled', 'canceled', 'expired', 'completed', 'absent'])
          ->get()
          ->filter(function ($r) use ($slot, $slotEnd) {
            $rStart = Carbon::parse($r->reserved_date . ' ' . $r->reserved_time);
            $rEnd = $rStart->copy()->addMinutes($r->duration_minutes ?? 30);
            return $slot->lt($rEnd) && $slotEnd->gt($rStart);
          });

        $slots[$slot->format('H:i')] = ['time' => $slot->format('H:i'), 'available' => $conflict->isEmpty()];
        $slot->addMinutes($schedule['interval']);
      }
    }

    $slots = array_values($slots);
    usort($slots, function ($a, $b) {
      return strcmp($a['time'], $b['time']);
    });

    $horaInicio = $schedules[0]['start']->format('H:i');
    $horaFin = $schedules[count($schedules) - 1]['end']->format('H:i');
    $intervalMin = $schedules[0]['interval'];

    $hasAvailable = collect($slots)->contains(function ($s) {
      return !empty($s['available']);
    });

    return [
      'available' => $hasAvailable,
      'message' => $hasAvailable ? 'Hay horarios disponibles para el empleado' : 'No hay horarios disponibles para el empleado en ese día',
      'duration_minutes' => $intervalMin,
      'hora_inicio' => $horaInicio,
      'hora_fin' => $horaFin,
      'intervalo_minutos' => $intervalMin,
      'slots' => $slots,
    ];
  }

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

      try {
        $phone = preg_replace('/[^0-9+]/', '', $r->phone);
        $phoneDigits = preg_replace('/[^0-9]/', '', $phone);
        if (substr($phoneDigits, 0, 3) === '591') {
          $to = $phoneDigits;
        } else {
          $to = '591' . ltrim($phoneDigits, '0');
        }

        try {
          $dt = Carbon::parse($r->reserved_date . ' ' . $r->reserved_time)->locale('es');
          $dateFormatted = $dt->isoFormat('dddd, D [de] MMMM');
          $timeFormatted = $dt->format('H:i');
        } catch (\Exception $e) {
          $dateFormatted = $r->reserved_date;
          $timeFormatted = $r->reserved_time;
        }

        $serviceName = $r->product ? $r->product->name : '-';
        $appName = config('app.name') ?: 'H2O';

        $message = "Hola, {$r->name}:\n\n";
        $message .= "Lamentamos informarle que su reserva de los servicios {$serviceName} para el {$dateFormatted} a las {$timeFormatted} ha sido cancelada por el proveedor {$appName}. Para obtener más detalles o ayuda adicional, no dude en ponerse en contacto con nosotros.";

        $waService = app(WhatsAppService::class);
        $sent = $waService->sendMessage($to, $message);
        if (!$sent) {
          \Log::warning('No se pudo enviar WA al cancelar reserva', ['reservation_id' => $r->id, 'to' => $to]);
        }
        // Enviar también al encargado configurado en pos_setting (si existe)
        try {
          $posSetting = PosSetting::latest()->first();
          if ($posSetting && !empty($posSetting->nro_encargado)) {
            $mgrPhone = preg_replace('/[^0-9+]/', '', $posSetting->nro_encargado);
            $mgrDigits = preg_replace('/[^0-9]/', '', $mgrPhone);
            if (!empty($mgrDigits)) {
              if (substr($mgrDigits, 0, 3) === '591') {
                $toMgr = $mgrDigits;
              } else {
                $toMgr = '591' . ltrim($mgrDigits, '0');
              }
              $mgrMsg = "👨‍💼 Encargado/a, reserva cancelada:\n\n";
              $mgrMsg .= "👤 Cliente: {$r->name}\n";
              $mgrMsg .= "📝 Servicio: {$serviceName}\n";
              $mgrMsg .= "🏢 Sucursal: " . ($r->warehouse ? $r->warehouse->name : '-') . "\n";
              $mgrMsg .= "🗓️ Fecha: {$dateFormatted}\n";
              $mgrMsg .= "🕑 Hora: {$timeFormatted}\n\n";
              $mgrMsg .= "Estado: Cancelada. Por favor, coordine según corresponda.";
              try {
                $sentMgr = $waService->sendMessage($toMgr, $mgrMsg);
                if (!$sentMgr) {
                  \Log::warning('No se pudo enviar WA al encargado (cancelReservation)', ['reservation_id' => $r->id, 'to' => $toMgr]);
                }
              } catch (\Exception $e) {
                \Log::error('Excepción enviando WA al encargado (cancelReservation)', ['reservation_id' => $r->id, 'error' => $e->getMessage()]);
              }
            }
          }
        } catch (\Exception $e) {
          \Log::error('Error obteniendo posSetting para enviar al encargado (cancelReservation)', ['error' => $e->getMessage()]);
        }
      } catch (\Exception $e) {
        \Log::error('Excepción enviando WA (cancelReservation)', ['reservation_id' => $r->id, 'error' => $e->getMessage()]);
      }

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

    // Detectar driver de base de datos para usar la función de diferencia de tiempo adecuada
    $driver = 'mysql';
    try {
      $driver = \DB::getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);
    } catch (\Exception $e) {
      $driver = config('database.default');
    }
    if (empty($request->input('search.value'))) {
      // If no explicit ordering provided, order by proximity to now (closest reservations first)
      if (empty($request->input('order.0.column'))) {
        // Use DB-specific expression: MySQL uses TIMESTAMPDIFF, Postgres uses EXTRACT(EPOCH FROM ...)
        if (strtolower($driver) === 'pgsql' || strtolower($driver) === 'postgresql' || stripos($driver, 'pgsql') !== false) {
          $reservations = $query->orderByRaw("ABS(EXTRACT(EPOCH FROM (NOW() - ((reserved_date || ' ' || reserved_time)::timestamp)))) ASC")
            ->offset($start)
            ->limit($limit)
            ->get();
        } else {
          $reservations = $query->orderByRaw("ABS(TIMESTAMPDIFF(SECOND, CONCAT(reserved_date,' ',reserved_time), NOW())) ASC")
            ->offset($start)
            ->limit($limit)
            ->get();
        }
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
      $employees = \App\Employee::where('is_active', true)->orderBy('name', 'asc')->get();
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
      $employeeName = $reservation->employee ? strtoupper($reservation->employee->name) : strtoupper('nuestro equipo');

      try {
        $dt = Carbon::parse($reservation->reserved_date . ' ' . $reservation->reserved_time)->locale('es');
        $dateFormatted = $dt->isoFormat('dddd, D [de] MMMM');
        $timeFormatted = $dt->format('H:i');
      } catch (\Exception $e) {
        $dateFormatted = $reservation->reserved_date;
        $timeFormatted = $reservation->reserved_time;
      }

      $appName = config('app.name') ?: 'H2O';
      $message = "¡Hola, {$reservation->name}! 👋\n\n";
      $message .= "Tu reserva ha sido confirmada exitosamente. ✅\n";
      $message .= "Aquí tienes los detalles:\n\n";
      $message .= "📝 Servicios solicitados: {$serviceName}\n";
      $message .= "🗓️ Fecha: {$dateFormatted}\n";
      $message .= "🕑 Hora: {$timeFormatted}\n";
      $message .= "👤 Colaborador: {$employeeName}\n";
      $message .= "🏢 Sucursal: {$warehouseName}\n\n";
      $message .= "Puedes hacer tu reserva también por nuestra app:\n\n";
      $message .= "🔗 https://reservas.gisulsrl.com/\n\n";
      $message .= "¡Gracias por confiar en nosotros, {$appName}! 😊";

      $waService = app(WhatsAppService::class);
      $sentOk = $waService->sendMessage($to, $message);
      if (!$sentOk) {
        \Log::warning('No se pudo enviar WA via WhatsAppService (store)', ['reservation_id' => $reservation->id, 'to' => $to]);
      }

      // Enviar también notificación al empleado asignado (si existe y tiene phone_number)
      if (!empty($reservation->employee_id) && $reservation->employee) {
        $empPhone = preg_replace('/[^0-9+]/', '', $reservation->employee->phone_number ?? '');
        $empDigits = preg_replace('/[^0-9]/', '', $empPhone);
        if (!empty($empDigits)) {
          if (substr($empDigits, 0, 3) === '591') {
            $toEmp = $empDigits;
          } else {
            $toEmp = '591' . ltrim($empDigits, '0');
          }
          $empAssignedName = $reservation->employee ? $reservation->employee->name : '';
          $empAssignedNameU = strtoupper($empAssignedName ?: '');
          try {
            $dtEmp = Carbon::parse($reservation->reserved_date . ' ' . $reservation->reserved_time)->locale('es');
            $dateEmp = $dtEmp->isoFormat('dddd, D [de] MMMM');
            $timeEmp = $dtEmp->format('H:i');
          } catch (\Exception $e) {
            $dateEmp = $reservation->reserved_date;
            $timeEmp = $reservation->reserved_time;
          }
          $empMsg = "📌 Hola {$empAssignedNameU},\n\n";
          $empMsg .= "🆕 Nueva reserva asignada ✅\n\n";
          $empMsg .= "👤 Cliente: {$reservation->name}\n";
          $empMsg .= "📝 Servicio: {$serviceName}\n";
          $empMsg .= "🏢 Sucursal: {$warehouseName}\n";
          $empMsg .= "🗓️ Fecha: {$dateEmp}\n";
          $empMsg .= "🕑 Hora: {$timeEmp}\n\n";
          $empMsg .= "Por favor, prepare el espacio y esté presente a la hora indicada. Gracias.";
          try {
            $sentEmp = $waService->sendMessage($toEmp, $empMsg);
            if (!$sentEmp) {
              \Log::warning('No se pudo enviar WA al empleado (store)', ['reservation_id' => $reservation->id, 'employee_id' => $reservation->employee_id, 'to' => $toEmp]);
            }
          } catch (\Exception $e) {
            \Log::error('Excepción enviando WA al empleado (store)', ['reservation_id' => $reservation->id, 'employee_id' => $reservation->employee_id, 'error' => $e->getMessage()]);
          }
        }
      }
      // Enviar también al encargado configurado en pos_setting (si existe)
      try {
        $posSetting = PosSetting::latest()->first();
        if ($posSetting && !empty($posSetting->nro_encargado)) {
          $mgrPhone = preg_replace('/[^0-9+]/', '', $posSetting->nro_encargado);
          $mgrDigits = preg_replace('/[^0-9]/', '', $mgrPhone);
          if (!empty($mgrDigits)) {
            if (substr($mgrDigits, 0, 3) === '591') {
              $toMgr = $mgrDigits;
            } else {
              $toMgr = '591' . ltrim($mgrDigits, '0');
            }
            $mgrMsg = "👨‍💼 Encargado/a, nueva reserva registrada:\n\n";
            $mgrMsg .= "👤 Cliente: {$reservation->name}\n";
            $mgrMsg .= "📝 Servicio: {$serviceName}\n";
            $mgrMsg .= "🏢 Sucursal: {$warehouseName}\n";
            $mgrMsg .= "🗓️ Fecha: {$dateFormatted}\n";
            $mgrMsg .= "🕑 Hora: {$timeFormatted}\n";
            $mgrMsg .= "👤 Colaborador: {$employeeName}\n\n";
            $mgrMsg .= "Por favor, tome nota. Gracias.";
            try {
              $sentMgr = $waService->sendMessage($toMgr, $mgrMsg);
              if (!$sentMgr) {
                \Log::warning('No se pudo enviar WA al encargado (store)', ['reservation_id' => $reservation->id, 'to' => $toMgr]);
              }
            } catch (\Exception $e) {
              \Log::error('Excepción enviando WA al encargado (store)', ['reservation_id' => $reservation->id, 'error' => $e->getMessage()]);
            }
          }
        }
      } catch (\Exception $e) {
        \Log::error('Error obteniendo posSetting para enviar al encargado (store)', ['error' => $e->getMessage()]);
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
      $employees = Employee::where('is_active', true)->orderBy('name', 'asc')->get();
      return view('reservation.edit', compact('reservation', 'products', 'warehouses', 'employees'));
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
   * Envía a reservas con fecha >= hoy. Omite las que tienen estado 'expired' o 'absent'.
   */
  public function sendReminders(Request $request)
  {
    $ids = $request->input('reservationIdArray', []);
    \Log::info('sendReminders: Iniciando proceso', ['ids_recibidos' => $ids]);

    if (empty($ids)) {
      \Log::warning('sendReminders: No hay reservas seleccionadas');
      return response()->json('No hay reservas seleccionadas.', 422);
    }

    $now = Carbon::now();
    $today = $now->toDateString();
    $sent = [];
    $skipped = [];
    $skippedReasons = [];

    \Log::info('sendReminders: Fecha y hora actual', ['today' => $today, 'now' => $now->toDateTimeString()]);

    $reservations = Reservation::whereIn('id', $ids)->get();
    \Log::info('sendReminders: Reservas encontradas', ['count' => $reservations->count()]);

    foreach ($reservations as $r) {
      \Log::info('sendReminders: Procesando reserva', [
        'id' => $r->id,
        'name' => $r->name,
        'phone' => $r->phone,
        'status' => $r->status,
        'reserved_date' => $r->reserved_date,
        'reserved_time' => $r->reserved_time
      ]);

      // Omitir reservas con estado 'expired' o 'absent'
      $status = strtolower($r->status);
      if (in_array($status, ['expired', 'absent'])) {
        \Log::info('sendReminders: Reserva omitida - estado es expired o absent', ['id' => $r->id, 'status' => $r->status]);
        $skipped[] = $r->id;
        $skippedReasons[$r->id] = 'Estado es ' . $r->status;
        continue;
      }

      // Solo enviar a reservas con fecha >= hoy (hoy y futuras)
      if ($r->reserved_date < $today) {
        \Log::info('sendReminders: Reserva omitida - fecha es pasada', ['id' => $r->id, 'reserved_date' => $r->reserved_date, 'today' => $today]);
        $skipped[] = $r->id;
        $skippedReasons[$r->id] = 'Fecha es pasada';
        continue;
      }

      // Enviamos recordatorio a reservas de hoy y futuras con estado válido

      $phone = preg_replace('/[^0-9+]/', '', $r->phone);
      $phoneDigits = preg_replace('/[^0-9]/', '', $phone);
      if (substr($phoneDigits, 0, 3) === '591') {
        $to = $phoneDigits;
      } else {
        $to = '591' . ltrim($phoneDigits, '0');
      }

      $serviceName = $r->product ? $r->product->name : '-';
      $warehouseName = $r->warehouse ? $r->warehouse->name : '-';
      $employeeName = $r->employee ? strtoupper($r->employee->name) : strtoupper('nuestro equipo');
      try {
        $dtR = Carbon::parse($r->reserved_date . ' ' . $r->reserved_time)->locale('es');
        $dateFormatted = $dtR->isoFormat('dddd, D [de] MMMM');
        $timeFormatted = $dtR->format('H:i');
      } catch (\Exception $e) {
        $dateFormatted = $r->reserved_date;
        $timeFormatted = $r->reserved_time;
      }
      $appName = config('app.name') ?: 'H2O';
      $message = "¡Hola, {$r->name}! 👋\n\n";
      $message .= "Te recordamos tu reserva. ✅\n\n";
      $message .= "📝 Servicio: {$serviceName}\n";
      $message .= "🗓️ Fecha: {$dateFormatted}\n";
      $message .= "🕑 Hora: {$timeFormatted}\n";
      $message .= "👤 Colaborador: {$employeeName}\n";
      $message .= "🏢 Sucursal: {$warehouseName}\n\n";
      $message .= "Por favor preséntese 10 minutos antes. Si necesita reprogramar o cancelar, contáctenos o use https://reservas.gisulsrl.com/\n\n";
      $message .= "¡Gracias por confiar en nosotros, {$appName}! 😊";

      \Log::info('sendReminders: Intentando enviar WhatsApp', ['id' => $r->id, 'to' => $to, 'message' => $message]);

      try {
        $waService = app(WhatsAppService::class);
        $sentOk = $waService->sendMessage($to, $message);
        if ($sentOk) {
          \Log::info('sendReminders: WhatsApp enviado correctamente', ['id' => $r->id, 'to' => $to]);
          $sent[] = $r->id;
        } else {
          \Log::warning('sendReminders: WhatsAppService retornó false', ['id' => $r->id, 'to' => $to]);
          $skipped[] = $r->id;
          $skippedReasons[$r->id] = 'WhatsApp no enviado';
        }
        // Enviar también al empleado asignado si tiene phone_number
        if ($r->employee && !empty($r->employee->phone_number)) {
          $empPhone = preg_replace('/[^0-9+]/', '', $r->employee->phone_number);
          $empDigits = preg_replace('/[^0-9]/', '', $empPhone);
          if (!empty($empDigits)) {
            if (substr($empDigits, 0, 3) === '591') {
              $toEmp = $empDigits;
            } else {
              $toEmp = '591' . ltrim($empDigits, '0');
            }
            $empAssignedName = $r->employee ? $r->employee->name : '';
            $empAssignedNameU = strtoupper($empAssignedName ?: '');
            try {
              $dtEmpR = Carbon::parse($r->reserved_date . ' ' . $r->reserved_time)->locale('es');
              $dateEmpR = $dtEmpR->isoFormat('dddd, D [de] MMMM');
              $timeEmpR = $dtEmpR->format('H:i');
            } catch (\Exception $e) {
              $dateEmpR = $r->reserved_date;
              $timeEmpR = $r->reserved_time;
            }
            $empMsg = "⏰ Recordatorio - Reserva asignada\n\n";
            $empMsg .= "👤 Cliente: {$r->name}\n";
            $empMsg .= "📝 Servicio: {$serviceName}\n";
            $empMsg .= "🏢 Sucursal: {$warehouseName}\n";
            $empMsg .= "🗓️ Fecha: {$dateEmpR}\n";
            $empMsg .= "🕑 Hora: {$timeEmpR}\n\n";
            $empMsg .= "Por favor, organice su agenda y esté presente a la hora indicada. Gracias.";
            try {
              $sentEmp = $waService->sendMessage($toEmp, $empMsg);
              if ($sentEmp) {
                \Log::info('sendReminders: WhatsApp enviado al empleado', ['id' => $r->id, 'employee_id' => $r->employee->id, 'to' => $toEmp]);
              } else {
                \Log::warning('sendReminders: WhatsAppService retornó false al enviar al empleado', ['id' => $r->id, 'employee_id' => $r->employee->id, 'to' => $toEmp]);
              }
            } catch (\Exception $e) {
              \Log::error('sendReminders: Excepción enviando WA al empleado', ['id' => $r->id, 'employee_id' => $r->employee->id, 'error' => $e->getMessage()]);
            }
          }
        }
      } catch (\Exception $e) {
        \Log::error('sendReminders: Excepción enviando WhatsApp', ['id' => $r->id, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        $skipped[] = $r->id;
        $skippedReasons[$r->id] = 'Error: ' . $e->getMessage();
      }
    }

    \Log::info('sendReminders: Proceso finalizado', ['sent' => $sent, 'skipped' => $skipped, 'skippedReasons' => $skippedReasons]);

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

    if ($employee) {
      $scheduleValidation = $this->validateEmployeeScheduleWindow($employee, $start, $end);
      if (!$scheduleValidation['ok']) {
        return response()->json(['available' => false, 'message' => $scheduleValidation['message']], 200);
      }
    }

    // Traer reservas en la misma fecha. Si se especificó empleado, filtrar por empleado;
    // en caso contrario filtrar por sucursal.
    if ($employee) {
      $existing = Reservation::where('employee_id', $employee)
        ->where('reserved_date', $date)
        ->whereNotIn('status', ['cancelled', 'canceled', 'expired', 'completed', 'absent'])
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
        ->whereNotIn('status', ['cancelled', 'canceled', 'expired', 'completed', 'absent'])
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
        ->whereNotIn('status', ['cancelled', 'canceled', 'expired', 'completed', 'absent'])
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
      'sucursal_id' => 'required|integer',
      'employee_id' => 'nullable|integer'
    ]);

    $date = $request->input('reserved_date');
    $time = $request->input('reserved_time');
    $duration = intval($request->input('duration_minutes', 30));
    $sucursal = $request->input('sucursal_id');
    $requestedEmployeeId = $request->input('employee_id');

    try {
      $start = Carbon::parse($date . ' ' . $time);
    } catch (\Exception $e) {
      return response()->json(['error' => 'Fecha u hora inválida'], 422);
    }

    if ($start->lte(Carbon::now())) {
      return response()->json(['error' => 'Sólo se permiten reservas para fechas y horas futuras'], 422);
    }

    $end = $start->copy()->addMinutes($duration);

    $assignedEmployee = null;

    // Si se especificó un empleado, verificar disponibilidad de ese empleado
    if ($requestedEmployeeId) {
      $emp = \App\Employee::where('id', $requestedEmployeeId)->where('is_active', true)->first();
      if (!$emp) {
        return response()->json(['error' => 'Empleado no encontrado o no está activo'], 404);
      }

      // Verificar si el empleado tiene conflicto en ese horario
      $scheduleValidation = $this->validateEmployeeScheduleWindow($emp->id, $start, $end);
      if (!$scheduleValidation['ok']) {
        return response()->json(['error' => $scheduleValidation['message']], 200);
      }

      $conflict = Reservation::where('employee_id', $emp->id)
        ->where('reserved_date', $date)
        ->whereNotIn('status', ['cancelled', 'canceled', 'expired', 'completed', 'absent'])
        ->get()
        ->filter(function ($r) use ($start, $end) {
          $rStart = Carbon::parse($r->reserved_date . ' ' . $r->reserved_time);
          $rEnd = $rStart->copy()->addMinutes($r->duration_minutes ?? 30);
          return $start->lt($rEnd) && $end->gt($rStart);
        });

      if ($conflict->isNotEmpty()) {
        return response()->json(['error' => 'El empleado seleccionado no está disponible en este horario'], 200);
      }

      $assignedEmployee = $emp;
    } else {
      // Round-robin: asignar al empleado con menos reservas del día que esté disponible
      $employees = \App\Employee::where([['is_active', true], ['warehouse_id', $sucursal]])->get();
      if ($employees->isEmpty()) {
        $employees = \App\Employee::where('is_active', true)->get();
      }

      // Contar reservas del día por empleado y ordenar por cantidad (menor primero)
      $employeesWithCount = $employees->map(function ($emp) use ($date) {
        $count = Reservation::where('employee_id', $emp->id)
          ->where('reserved_date', $date)
          ->count();
        return ['employee' => $emp, 'count' => $count];
      })->sortBy('count');

      // Buscar el empleado con menos reservas que esté disponible en el horario
      foreach ($employeesWithCount as $item) {
        $emp = $item['employee'];
        $scheduleValidation = $this->validateEmployeeScheduleWindow($emp->id, $start, $end);
        if (!$scheduleValidation['ok']) {
          continue;
        }
        $conflict = Reservation::where('employee_id', $emp->id)
          ->where('reserved_date', $date)
          ->whereNotIn('status', ['cancelled', 'canceled', 'expired', 'completed', 'absent'])
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
      try {
        $dtPub = Carbon::parse($reservation->reserved_date . ' ' . $reservation->reserved_time)->locale('es');
        $datePub = $dtPub->isoFormat('dddd, D [de] MMMM');
        $timePub = $dtPub->format('H:i');
      } catch (\Exception $e) {
        $datePub = $reservation->reserved_date;
        $timePub = $reservation->reserved_time;
      }
      $appName = config('app.name') ?: 'H2O';
      $message = "¡Hola, {$reservation->name}! 👋\n\n";
      $message .= "Tu reserva ha sido programada ✅\n\n";
      $message .= "📝 Servicio: {$serviceName}\n";
      $message .= "🗓️ Fecha: {$datePub}\n";
      $message .= "🕑 Hora: {$timePub}\n";
      $message .= "🏢 Sucursal: {$warehouseName}\n\n";
      $message .= "Puedes gestionar tu reserva también en https://reservas.gisulsrl.com/\n\n";
      $message .= "¡Gracias por confiar en Urban Fashion! 😊";

      $waService = app(WhatsAppService::class);
      $sentOk = $waService->sendMessage($to, $message);
      if (!$sentOk) {
        \Log::warning('No se pudo enviar WA via WhatsAppService (publicCreateReservation)', ['reservation_id' => $reservation->id, 'to' => $to]);
      }

      // Enviar también al empleado asignado (public API flow)
      if ($assignedEmployee && !empty($assignedEmployee->phone_number)) {
        $empPhone = preg_replace('/[^0-9+]/', '', $assignedEmployee->phone_number);
        $empDigits = preg_replace('/[^0-9]/', '', $empPhone);
        if (!empty($empDigits)) {
          if (substr($empDigits, 0, 3) === '591') {
            $toEmp = $empDigits;
          } else {
            $toEmp = '591' . ltrim($empDigits, '0');
          }
          $empAssignedName = $assignedEmployee ? $assignedEmployee->name : '';
          $empAssignedNameU = strtoupper($empAssignedName ?: '');
          try {
            $dtEmpPub = Carbon::parse($reservation->reserved_date . ' ' . $reservation->reserved_time)->locale('es');
            $dateEmpPub = $dtEmpPub->isoFormat('dddd, D [de] MMMM');
            $timeEmpPub = $dtEmpPub->format('H:i');
          } catch (\Exception $e) {
            $dateEmpPub = $reservation->reserved_date;
            $timeEmpPub = $reservation->reserved_time;
          }
          $empMsg = "📌 Hola {$empAssignedNameU},\n\n";
          $empMsg .= "🆕 Nueva reserva asignada ✅\n\n";
          $empMsg .= "👤 Cliente: {$reservation->name}\n";
          $empMsg .= "📝 Servicio: {$serviceName}\n";
          $empMsg .= "🏢 Sucursal: {$warehouseName}\n";
          $empMsg .= "🗓️ Fecha: {$dateEmpPub}\n";
          $empMsg .= "🕑 Hora: {$timeEmpPub}\n\n";
          $empMsg .= "Por favor, organice su agenda y esté presente a la hora indicada. Gracias.";
          try {
            $sentEmp = $waService->sendMessage($toEmp, $empMsg);
            if (!$sentEmp) {
              \Log::warning('No se pudo enviar WA al empleado (publicCreateReservation)', ['reservation_id' => $reservation->id, 'employee_id' => $assignedEmployee->id, 'to' => $toEmp]);
            }
          } catch (\Exception $e) {
            \Log::error('Excepción enviando WA al empleado (publicCreateReservation)', ['reservation_id' => $reservation->id, 'employee_id' => $assignedEmployee->id, 'error' => $e->getMessage()]);
          }
        }
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
    $employeeId = $request->query('employee_id');

    if (!$sucursal && !$employeeId) {
      return response()->json(['error' => 'sucursal_id o employee_id requerido'], 422);
    }

    if ($employeeId) {
      $employee = \App\Employee::find($employeeId);
      if (!$employee) {
        return response()->json(['error' => 'Empleado no encontrado'], 404);
      }

      $employeeSlots = $this->buildEmployeeSlots($employee->id, $date, $duration);
      $duration = intval($employeeSlots['duration_minutes'] ?? $duration);
      return response()->json([
        'date' => $date,
        'employee_id' => $employee->id,
        'employee_name' => $employee->name,
        'duration_minutes' => $duration,
        'hora_inicio' => $employeeSlots['hora_inicio'],
        'hora_fin' => $employeeSlots['hora_fin'],
        'intervalo_minutos' => $employeeSlots['intervalo_minutos'],
        'available' => $employeeSlots['available'],
        'message' => $employeeSlots['message'],
        'slots' => $employeeSlots['slots'],
      ]);
    }

    // Obtener horarios de atención desde pos_setting
    $posSetting = \App\PosSetting::first();
    $horaInicio = $posSetting && $posSetting->hora_inicio_atencion ? $posSetting->hora_inicio_atencion : '08:00:00';
    $horaFin = $posSetting && $posSetting->hora_fin_atencion ? $posSetting->hora_fin_atencion : '21:00:00';
    $intervalo = $posSetting && $posSetting->intervalo_reserva_minutos ? intval($posSetting->intervalo_reserva_minutos) : 30;

    // Usar el intervalo de la configuración si no se especifica duración
    if (!$request->has('duration_minutes')) {
      $duration = $intervalo;
    }

    // Rango desde hora_inicio_atencion hasta hora_fin_atencion
    $start = Carbon::parse($date . ' ' . $horaInicio);
    $endLimit = Carbon::parse($date . ' ' . $horaFin);

    $slots = [];
    $slot = $start->copy();
    // If employee_id provided, validate employee and compute availability per that employee.
    $employee = null;
    if ($employeeId) {
      $employee = \App\Employee::find($employeeId);
      if (!$employee) {
        return response()->json(['error' => 'Empleado no encontrado'], 404);
      }
    }

    while ($slot->lte($endLimit->copy()->subMinutes($duration))) {
      $slotEnd = $slot->copy()->addMinutes($duration);

      // comprobar solapamiento con reservas existentes
      if ($employee) {
        $conflict = Reservation::where('employee_id', $employee->id)
          ->where('reserved_date', $date)->get()
          ->filter(function ($r) use ($slot, $slotEnd) {
            $rStart = Carbon::parse($r->reserved_date . ' ' . $r->reserved_time);
            $rEnd = $rStart->copy()->addMinutes($r->duration_minutes ?? 30);
            return $slot->lt($rEnd) && $slotEnd->gt($rStart);
          });

        $slotAvailable = $conflict->isEmpty();
      } else {
        // ahora comprobamos si al menos un empleado está libre en ese slot (comportamiento previo)
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
      }

      $entry = ['time' => $slot->format('H:i'), 'available' => $slotAvailable];
      if ($employee) {
        $entry['employee_id'] = $employee->id;
      }
      $slots[] = $entry;
      $slot->addMinutes($intervalo);
    }

    return response()->json([
      'date' => $date,
      'duration_minutes' => $duration,
      'hora_inicio' => Carbon::parse($horaInicio)->format('H:i'),
      'hora_fin' => Carbon::parse($horaFin)->format('H:i'),
      'intervalo_minutos' => $intervalo,
      'slots' => $slots
    ]);
  }

  /**
   * Endpoint público para consultar disponibilidad por barbero al seleccionarlo.
   * GET /api/reservations/employee-availability?employee_id=1&date=YYYY-MM-DD&duration_minutes=30
   */
  public function publicEmployeeAvailability(Request $request)
  {
    $request->validate([
      'employee_id' => 'required|integer',
      'date' => 'required|date',
      'duration_minutes' => 'nullable|integer|min:1',
    ]);

    $employee = Employee::where('id', $request->query('employee_id'))
      ->where('is_active', true)
      ->first();

    if (!$employee) {
      return response()->json(['error' => 'Empleado no encontrado o no está activo'], 404);
    }

    $date = $request->query('date');
    $duration = intval($request->query('duration_minutes', 30));
    $employeeSlots = $this->buildEmployeeSlots($employee->id, $date, $duration);
    $duration = intval($employeeSlots['duration_minutes'] ?? $duration);

    return response()->json([
      'employee' => ['id' => $employee->id, 'name' => $employee->name],
      'date' => $date,
      'duration_minutes' => $duration,
      'has_available_slots' => $employeeSlots['available'],
      'message' => $employeeSlots['message'],
      'hora_inicio' => $employeeSlots['hora_inicio'],
      'hora_fin' => $employeeSlots['hora_fin'],
      'intervalo_minutos' => $employeeSlots['intervalo_minutos'],
      'slots' => $employeeSlots['slots'],
    ]);
  }

  /**
   * Endpoint público para obtener espacios libres/ocupados por empleado y fecha.
   * GET /api/reservations/employee-slots?employee_id=1&sucursal_id=1&date=2026-03-13&duration_minutes=30
   */
  public function publicEmployeeSlots(Request $request)
  {
    $request->validate([
      'employee_id' => 'required|integer',
      'sucursal_id' => 'nullable|integer',
      'date' => 'required|date',
      'duration_minutes' => 'nullable|integer|min:1',
    ]);

    $sucursalId = $request->query('sucursal_id');

    $employee = Employee::where('id', $request->query('employee_id'))
      ->where('is_active', true)
      ->first();

    if (!$employee) {
      return response()->json(['error' => 'Empleado no encontrado o no está activo'], 404);
    }

    if (!empty($sucursalId) && intval($employee->warehouse_id) !== intval($sucursalId)) {
      return response()->json(['error' => 'El empleado no pertenece a la sucursal indicada'], 422);
    }

    $date = Carbon::parse($request->query('date'))->toDateString();
    $duration = intval($request->query('duration_minutes', 30));
    $slotsData = $this->buildEmployeeSlots($employee->id, $date, $duration);
    $duration = intval($slotsData['duration_minutes'] ?? $duration);

    return response()->json([
      'employee_id' => $employee->id,
      'sucursal_id' => $sucursalId ? intval($sucursalId) : null,
      'date' => $date,
      'duration_minutes' => $duration,
      'hora_inicio' => $slotsData['hora_inicio'],
      'hora_fin' => $slotsData['hora_fin'],
      'intervalo_minutos' => $slotsData['intervalo_minutos'],
      'slots' => $slotsData['slots'],
    ]);
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
