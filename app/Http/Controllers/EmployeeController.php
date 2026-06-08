<?php

namespace App\Http\Controllers;

use App\Biller;
use App\Department;
use App\Employee;
use App\EmployeeReservationSchedule;
use App\PosSetting;
use App\User;
use App\Warehouse;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{

    private function getReservationWindowLimits()
    {
        $posSetting = PosSetting::first();

        $startTime = $posSetting && $posSetting->hora_inicio_atencion
            ? substr((string) $posSetting->hora_inicio_atencion, 0, 5)
            : '08:00';

        $endTime = $posSetting && $posSetting->hora_fin_atencion
            ? substr((string) $posSetting->hora_fin_atencion, 0, 5)
            : '21:00';

        $interval = $posSetting && $posSetting->intervalo_reserva_minutos
            ? intval($posSetting->intervalo_reserva_minutos)
            : 30;

        return [
            'start_time' => $startTime,
            'end_time' => $endTime,
            'interval_minutes' => max(5, $interval),
        ];
    }

    private function isTimeWithinReservationWindow($startTime, $endTime, $windowStart, $windowEnd)
    {
        return ($startTime >= $windowStart) && ($endTime <= $windowEnd);
    }

    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('employees-index')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if (empty($all_permission))
                $all_permission[] = 'dummy text';
            $lims_employee_all = Employee::where('is_active', true)->get();
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_department_list = Department::where('is_active', true)->get();
            $lims_users_all = User::where('is_active', true)->where('company_id', Auth::user()->company_id)->limit(100)->get();
            return view('employee.index', compact('lims_employee_all', 'lims_department_list', 'lims_warehouse_list', 'all_permission', 'lims_users_all'));
        } else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function create()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('employees-add')) {
            $lims_role_list = Role::where('is_active', true)->get();
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_biller_list = Biller::where('is_active', true)->get();
            $lims_department_list = Department::where('is_active', true)->get();

            return view('employee.create', compact('lims_role_list', 'lims_warehouse_list', 'lims_biller_list', 'lims_department_list'));
        } else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function store(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $data = $request->except('image');
        $message = 'Empleado registrado con éxito';
        if (isset($data['user'])) {
            $this->validate($request, [
                'name' => [
                    'max:255',
                    Rule::unique('users')->where(function ($query) use ($companyId) {
                        return $query->where('is_deleted', false)->where('company_id', $companyId);
                    }),
                ],
                'email' => [
                    'email',
                    'max:255',
                    Rule::unique('users')->where(function ($query) {
                        return $query->where('is_deleted', false);
                    }),
                ],
            ]);

            $data['is_active'] = true;
            $data['is_deleted'] = false;
            $data['password'] = bcrypt($data['password']);
            $data['phone'] = $data['phone_number'];
            $data['company_id'] = $companyId;
            User::create($data);
            $user = User::latest()->first();
            $data['user_id'] = $user->id;
            $message = 'Empleado registrado con éxito y añadido a la lista de usuarios';
        }
        //validation in employee table
        $this->validate($request, [
            'email' => [
                'max:255',
                Rule::unique('employees')->where(function ($query) use ($companyId) {
                    return $query->where('is_active', true)->where('company_id', $companyId);
                }),
            ],
            'image' => 'image|mimes:jpg,jpeg,png,gif|max:100000',
        ]);

        $image = $request->image;
        if ($image) {
            $ext = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
            $imageName = preg_replace('/[^a-zA-Z0-9]/', '', $request['email']);
            $imageName = $imageName . '.' . $ext;
            $image->move('public/images/employee', $imageName);
            $data['image'] = $imageName;
        }

        if (!isset($data['check_presale']))
            $data['pre_sale'] = false;
        else
            $data['pre_sale'] = true;

        $data['name'] = $data['employee_name'];
        $data['is_active'] = true;
        $data['warehouse_id'] = $data['warehouse_id_sale'];
        $data['company_id'] = $companyId;
        Employee::create($data);

        return redirect('employees')->with('message', $message);
    }

    public function update(Request $request, $id)
    {
        $companyId = Auth::user()->company_id;
        $lims_employee_data = Employee::find($request['employee_id']);
        if ($lims_employee_data->user_id) {
            $this->validate($request, [
                'name' => [
                    'max:255',
                    Rule::unique('users')->ignore($lims_employee_data->user_id)->where(function ($query) use ($companyId) {
                        return $query->where('is_deleted', false)->where('company_id', $companyId);
                    }),
                ],
                'email' => [
                    'email',
                    'max:255',
                    Rule::unique('users')->ignore($lims_employee_data->user_id)->where(function ($query) {
                        return $query->where('is_deleted', false);
                    }),
                ],
            ]);
        }
        //validation in employee table
        $this->validate($request, [
            'email' => [
                'email',
                'max:255',
                Rule::unique('employees')->ignore($lims_employee_data->id)->where(function ($query) use ($companyId) {
                    return $query->where('is_active', true)->where('company_id', $companyId);
                }),
            ],
            'image' => 'image|mimes:jpg,jpeg,png,gif|max:100000',
        ]);

        $data = $request->except('image');
        $image = $request->image;
        if ($image) {
            $ext = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
            $imageName = preg_replace('/[^a-zA-Z0-9]/', '', $request['email']);
            $imageName = $imageName . '.' . $ext;
            $image->move('public/images/employee', $imageName);
            $data['image'] = $imageName;
        }
        if (!isset($data['pay_commission'])) {
            if (!isset($data['check_presale'])) {
                $lims_employee_data->pre_sale = false;
                $lims_employee_data->warehouse_id = null;
            } else
                $lims_employee_data->pre_sale = true;
        }
        $lims_employee_data->update($data);
        return redirect('employees')->with('message', 'Empleado actualizado con éxito');
    }

    public function deleteBySelection(Request $request)
    {
        $employee_id = $request['employeeIdArray'];
        foreach ($employee_id as $id) {
            $lims_employee_data = Employee::find($id);
            if ($lims_employee_data->user_id) {
                $lims_user_data = User::find($lims_employee_data->user_id);
                $lims_user_data->is_deleted = true;
                $lims_user_data->save();
            }
            $lims_employee_data->is_active = false;
            $lims_employee_data->save();
        }
        return 'Empleados eliminados con éxito!';
    }
    public function destroy($id)
    {
        $lims_employee_data = Employee::find($id);
        if ($lims_employee_data->user_id) {
            $lims_user_data = User::find($lims_employee_data->user_id);
            $lims_user_data->is_deleted = true;
            $lims_user_data->save();
        }
        $lims_employee_data->is_active = false;
        $lims_employee_data->save();
        return redirect('employees')->with('not_permitted', 'Empleado eliminado con éxito');
    }

    public function togglePublic(Request $request, $id)
    {
        $role = Role::find(Auth::user()->role_id);
        if (! $role->hasPermissionTo('employees-edit')) {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }

        $employee = Employee::find($id);
        if (! $employee) {
            return redirect()->back()->with('not_permitted', 'Empleado no encontrado');
        }

        $employee->is_public = ! $employee->is_public;
        $employee->save();

        $message = $employee->is_public ? 'Empleado marcado como público' : 'Empleado marcado como privado';
        return redirect('employees')->with('message', $message);
    }

    /**
     * Devuelve horarios de reserva configurados por día para un empleado.
     */
    public function getReservationSchedules($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('employees-edit')) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json(['message' => 'Empleado no encontrado'], 404);
        }

        $result = EmployeeReservationSchedule::where('employee_id', $employee->id)
            ->orderBy('day_of_week', 'asc')
            ->orderBy('start_time', 'asc')
            ->get()
            ->map(function ($row) {
                return [
                    'id' => $row->id,
                    'day_of_week' => intval($row->day_of_week),
                    'is_enabled' => (bool) $row->is_enabled,
                    'start_time' => $row->start_time ? substr((string) $row->start_time, 0, 5) : null,
                    'end_time' => $row->end_time ? substr((string) $row->end_time, 0, 5) : null,
                    'interval_minutes' => intval($row->interval_minutes ?: 30),
                ];
            })
            ->values();

        return response()->json([
            'employee' => ['id' => $employee->id, 'name' => $employee->name],
            'schedules' => $result,
            'pos_window' => $this->getReservationWindowLimits(),
        ]);
    }

    /**
     * Guarda horarios por día (rango e intervalo) para un empleado.
     */
    public function saveReservationSchedules(Request $request, $id)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('employees-edit')) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json(['message' => 'Empleado no encontrado'], 404);
        }

        $reservationWindow = $this->getReservationWindowLimits();
        $windowStart = $reservationWindow['start_time'];
        $windowEnd = $reservationWindow['end_time'];

        if ($request->has('slots')) {
            $slots = $request->input('slots', []);
            if (!is_array($slots)) {
                return response()->json(['message' => 'Formato inválido: slots debe ser un arreglo'], 422);
            }

            $dayInput = $request->input('day_of_week');
            $dateInput = $request->input('date');

            if ($dayInput === null && empty($dateInput)) {
                return response()->json(['message' => 'date o day_of_week es requerido'], 422);
            }

            if ($dayInput !== null) {
                $day = intval($dayInput);
            } else {
                try {
                    $day = \Carbon\Carbon::parse($dateInput)->dayOfWeek;
                } catch (\Exception $e) {
                    return response()->json(['message' => 'date inválida, use YYYY-MM-DD'], 422);
                }
            }

            if ($day < 0 || $day > 6) {
                return response()->json(['message' => 'day_of_week inválido (0-6)'], 422);
            }

            $interval = intval($request->input('intervalo_minutos', $request->input('interval_minutes', 30)));
            if ($interval < 5) {
                return response()->json(['message' => 'El intervalo mínimo es 5 minutos'], 422);
            }

            $normalized = [];
            $slotTimes = [];
            foreach (array_values($slots) as $idx => $slot) {
                $time = isset($slot['time']) ? trim((string) $slot['time']) : '';
                $available = !array_key_exists('available', $slot) || !empty($slot['available']);

                if (!$available) {
                    continue;
                }

                if (!preg_match('/^(2[0-3]|[01][0-9]):[0-5][0-9]$/', $time)) {
                    return response()->json(['message' => 'Formato de hora inválido en fila ' . ($idx + 1) . ' (use HH:mm)'], 422);
                }

                if (isset($slotTimes[$time])) {
                    continue;
                }
                $slotTimes[$time] = true;

                $start = \Carbon\Carbon::createFromFormat('H:i', $time);
                $end = $start->copy()->addMinutes($interval);

                if (!$this->isTimeWithinReservationWindow($start->format('H:i'), $end->format('H:i'), $windowStart, $windowEnd)) {
                    return response()->json([
                        'message' => 'La hora ' . $time . ' está fuera del rango permitido (' . $windowStart . ' - ' . $windowEnd . ')',
                    ], 422);
                }

                $normalized[] = [
                    'employee_id' => $employee->id,
                    'company_id' => $employee->company_id ?: Auth::user()->company_id,
                    'day_of_week' => $day,
                    'is_enabled' => true,
                    'start_time' => $start->format('H:i:s'),
                    'end_time' => $end->format('H:i:s'),
                    'interval_minutes' => $interval,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::transaction(function () use ($employee, $day, $normalized) {
                EmployeeReservationSchedule::where('employee_id', $employee->id)
                    ->where('day_of_week', $day)
                    ->delete();

                if (!empty($normalized)) {
                    EmployeeReservationSchedule::insert($normalized);
                }
            });

            $sortedSlots = collect($slots)
                ->filter(function ($s) {
                    return !empty($s['available']) && !empty($s['time']);
                })
                ->map(function ($s) {
                    return ['time' => substr((string) $s['time'], 0, 5), 'available' => true];
                })
                ->unique('time')
                ->sortBy('time')
                ->values();

            return response()->json([
                'message' => 'Horarios de reserva guardados con éxito',
                'employee_id' => $employee->id,
                'date' => $dateInput,
                'day_of_week' => $day,
                'duration_minutes' => $interval,
                'hora_inicio' => $sortedSlots->isNotEmpty() ? $sortedSlots->first()['time'] : null,
                'hora_fin' => $sortedSlots->isNotEmpty() ? \Carbon\Carbon::createFromFormat('H:i', $sortedSlots->last()['time'])->addMinutes($interval)->format('H:i') : null,
                'intervalo_minutos' => $interval,
                'slots' => $sortedSlots,
            ]);
        }

        $rows = $request->input('schedules', []);
        if (!is_array($rows)) {
            return response()->json(['message' => 'Formato inválido de horarios'], 422);
        }

        $normalized = [];
        $groupedByDay = [];

        foreach (array_values($rows) as $idx => $row) {
            if (!isset($row['day_of_week'])) {
                return response()->json(['message' => 'day_of_week es requerido'], 422);
            }

            $day = intval($row['day_of_week']);
            if ($day < 0 || $day > 6) {
                return response()->json(['message' => 'day_of_week inválido (0-6)'], 422);
            }

            $isEnabled = !empty($row['is_enabled']);
            $start = $row['start_time'] ?? null;
            $end = $row['end_time'] ?? null;
            $interval = intval($row['interval_minutes'] ?? 30);

            if ($interval < 5) {
                return response()->json(['message' => 'El intervalo mínimo es 5 minutos (fila ' . ($idx + 1) . ')'], 422);
            }

            if ($isEnabled) {
                if (empty($start) || empty($end)) {
                    return response()->json(['message' => 'Debe definir hora inicio y fin para turnos habilitados (fila ' . ($idx + 1) . ')'], 422);
                }
                if (!preg_match('/^(2[0-3]|[01][0-9]):[0-5][0-9]$/', (string) $start) || !preg_match('/^(2[0-3]|[01][0-9]):[0-5][0-9]$/', (string) $end)) {
                    return response()->json(['message' => 'Formato de hora inválido, use HH:mm (fila ' . ($idx + 1) . ')'], 422);
                }
                if ($start >= $end) {
                    return response()->json(['message' => 'La hora fin debe ser mayor a la hora inicio (fila ' . ($idx + 1) . ')'], 422);
                }
                if (!$this->isTimeWithinReservationWindow($start, $end, $windowStart, $windowEnd)) {
                    return response()->json([
                        'message' => 'El turno debe estar dentro del horario de atención (' . $windowStart . ' - ' . $windowEnd . ') (fila ' . ($idx + 1) . ')',
                    ], 422);
                }

                $normalized[] = [
                    'employee_id' => $employee->id,
                    'company_id' => $employee->company_id ?: Auth::user()->company_id,
                    'day_of_week' => $day,
                    'is_enabled' => true,
                    'start_time' => $start,
                    'end_time' => $end,
                    'interval_minutes' => $interval,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (!isset($groupedByDay[$day])) {
                    $groupedByDay[$day] = [];
                }
                $groupedByDay[$day][] = ['start_time' => $start, 'end_time' => $end];
            }
        }

        foreach ($groupedByDay as $day => $turnos) {
            usort($turnos, function ($a, $b) {
                return strcmp($a['start_time'], $b['start_time']);
            });
            for ($i = 1; $i < count($turnos); $i++) {
                if ($turnos[$i]['start_time'] < $turnos[$i - 1]['end_time']) {
                    return response()->json(['message' => 'Existen turnos solapados en el día ' . $day], 422);
                }
            }
        }

        DB::transaction(function () use ($employee, $normalized) {
            EmployeeReservationSchedule::where('employee_id', $employee->id)->delete();
            if (!empty($normalized)) {
                EmployeeReservationSchedule::insert($normalized);
            }
        });

        return response()->json(['message' => 'Horarios de reserva guardados con éxito']);
    }

    /**
     * Endpoint público: listar empleados activos.
     * GET /api/employees-public?sucursal_id=1 (opcional)
     */
    public function apiPublicList(Request $request)
    {
        $sucursalId = $request->query('sucursal_id');

        $query = Employee::where('is_active', true)->where('is_public', true);

        if ($sucursalId) {
            $query->where('warehouse_id', $sucursalId);
        }

        $employees = $query->get()->map(function ($emp) {
            return [
                'id' => $emp->id,
                'name' => $emp->name,
                'phone' => $emp->phone_number,
                'image' => $emp->image ? asset('public/images/employee/' . $emp->image) : null,
                'warehouse_id' => $emp->warehouse_id,
                'warehouse_name' => $emp->warehouse ? $emp->warehouse->name : null,
            ];
        });

        return response()->json($employees);
    }

    /**
     * Genera un nuevo código PIN de asistencia para un empleado.
     * El PIN se retorna en texto plano UNA SOLA VEZ y se guarda hasheado.
     *
     * POST /employees/{id}/generate-pin
     */
    public function generateAttendancePin($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if (!$role->hasPermissionTo('employees-edit')) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json(['message' => 'Empleado no encontrado'], 404);
        }

        // Generar PIN numérico de 4 dígitos
        $pin = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);

        // Guardar hasheado
        $employee->attendance_pin = Hash::make($pin);
        $employee->save();

        return response()->json([
            'success'  => true,
            'pin'      => $pin,
            'employee' => $employee->name,
            'message'  => 'PIN generado correctamente. Guárdalo en un lugar seguro, no se mostrará nuevamente.',
        ]);
    }

    /**
     * Verifica si el PIN proporcionado es válido para un empleado.
     *
     * POST /employees/{id}/verify-pin
     */
    public function verifyAttendancePin(Request $request, $id)
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return response()->json(['valid' => false, 'message' => 'Empleado no encontrado'], 404);
        }

        $pin = $request->input('pin');
        if (empty($pin)) {
            return response()->json(['valid' => false, 'message' => 'Código PIN requerido'], 422);
        }

        if (!$employee->attendance_pin) {
            // Sin PIN configurado: se permite la acción (compatibilidad)
            return response()->json(['valid' => true, 'no_pin' => true]);
        }

        $isValid = Hash::check($pin, $employee->attendance_pin);
        return response()->json([
            'valid'   => $isValid,
            'message' => $isValid ? 'PIN correcto' : 'PIN incorrecto',
        ]);
    }
}
