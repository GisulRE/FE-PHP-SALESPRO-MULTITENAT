<?php

namespace App\Http\Controllers;

use App\Attendance;
use App\Employee;
use App\HrmSetting;
use App\PosSetting;
use App\ShiftEmployee;
use App\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AttendanceController extends Controller
{
    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('attendance')) {
            $lims_employee_list = Employee::where('is_active', true)->get();
            $lims_hrm_setting_data = HrmSetting::latest()->first();

            // Si no existe configuración HRM para la empresa, crear un valor por defecto
            if (! $lims_hrm_setting_data) {
                $lims_hrm_setting_data = HrmSetting::create([
                    'checkin' => '09:00',
                    'checkout' => '18:00',
                ]);
            }

            return view('attendance.index', compact('lims_employee_list', 'lims_hrm_setting_data'));
        } else {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }

    }

    public function attendanceData(Request $request)
    {
        $columns = array(
            1 => 'date',
            2 => 'employee_id',
            3 => 'checkin',
            4 => 'checkout',
            5 => 'status',
            6 => 'user_id',
        );

        if (Auth::user()->role_id > 2 && config('staff_access') == 'own') {
            $totalData = Attendance::where('user_id', Auth::id())->count();
        } else {
            $totalData = Attendance::count();
        }

        $totalFiltered = $totalData;

        if ($request->input('length') != -1) {
            $limit = $request->input('length');
        } else {
            $limit = $totalData;
        }

        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')] ?? $columns[1];
        $dir = $request->input('order.0.dir');
        if (empty($request->input('search.value'))) {
            if (Auth::user()->role_id > 2 && config('staff_access') == 'own') {
                $attendances = Attendance::with('employee')->offset($start)
                    ->where('user_id', Auth::id())
                    ->limit($limit)
                    ->orderBy($order, $dir)
                    ->get();
            } else {
                $attendances = Attendance::with('employee')->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)
                    ->get();
            }
        } else {
            $search = $request->input('search.value');
            if (Auth::user()->role_id > 2 && config('staff_access') == 'own') {
                $attendances = Attendance::select('attendances .*')
                    ->leftJoin('employees', 'attendances.employee_id', '=', 'employees.id')
                    ->whereDate('attendances.date', '=', date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                    ->where('attendances.user_id', Auth::id())
                    ->orwhere([
                        ['employees.name', 'LIKE', "%{$search}%"],
                        ['attendances.user_id', Auth::id()],
                    ])
                    ->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)->get();

                $totalFiltered = Attendance::leftJoin('employees', 'attendances.employee_id', '=', 'employees.id')
                    ->whereDate('attendances.date', '=', date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                    ->where('attendances.user_id', Auth::id())
                    ->orwhere([
                        ['employees.name', 'LIKE', "%{$search}%"],
                        ['attendances.user_id', Auth::id()],
                    ])
                    ->count();
            } else {
                $attendances = Attendance::select('attendances.*')
                    ->leftJoin('employees', 'attendances.employee_id', '=', 'employees.id')
                    ->whereDate('attendances.date', '=', date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                    ->orwhere('employees.name', 'LIKE', "%{$search}%")
                    ->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)
                    ->get();

                $totalFiltered = Attendance::leftJoin('employees', 'attendances.employee_id', '=', 'employees.id')
                    ->whereDate('attendances.date', '=', date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                    ->orwhere('employees.name', 'LIKE', "%{$search}%")
                    ->count();
            }
        }
        $data = array();
        if (!empty($attendances)) {
            foreach ($attendances as $key => $attendance) {
                $nestedData['id'] = $attendance->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime($attendance->date));
                if ($attendance->employee_id) {
                    $employee = $attendance->employee;
                } else {
                    $employee = new Employee();
                }
                $nestedData['employee'] = $employee->name;
                if ($attendance->user_id) {
                    $user = $attendance->user;
                } else {
                    $user = new User();
                }
                $nestedData['user'] = $user->name;
                $nestedData['checkin'] = $attendance->checkin;
                $nestedData['checkout'] = $attendance->checkout;
                if ($attendance->status == 0) {
                    $nestedData['status'] = '<div class="badge badge-success">' . trans('file.Present') . '</div>';
                } else {
                    $nestedData['status'] = '<div class="badge badge-danger">' . trans('file.Late') . '</div>';
                }

                $nestedData['options'] = '<div class="btn-group">';
                $nestedData['options'] .= \Form::open(["route" => ["attendance.destroy", $attendance->id], "method" => "DELETE"]) . '
                      <button type="submit" class="btn btn-sm btn-danger" onclick="return confirmDelete()"><i class="dripicons-trash"></i> ' . trans("file.delete") . '</button>
                        ' . \Form::close() . '
                </div>';

                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $data,
        );

        echo json_encode($json_data);
    }




    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $employee_id = $data['employee_id'];
        $lims_hrm_setting_data = HrmSetting::latest()->first();
        $checkin = $lims_hrm_setting_data ? $lims_hrm_setting_data->checkin : '09:00am';
            foreach ($employee_id as $id) {
            $data['date'] = date('Y-m-d', strtotime(str_replace('/', '-', $data['date'])));
            $data['user_id'] = Auth::id();
                $data['company_id'] = Auth::user()->company_id;
            $lims_attendance_data = Attendance::whereDate('date', $data['date'])->where('employee_id', $id)->first();
            if (!$lims_attendance_data) {
                $data['employee_id'] = $id;
                $diff = strtotime($checkin) - strtotime($data['checkin']);
                if ($diff >= 0) {
                    $data['status'] = 1;
                } else {
                    $data['status'] = 0;
                }

                Attendance::create($data);
            }
        }
        return redirect()->back()->with('message', 'Asistencia creado con éxito');
        //return date('h:i:s a', strtotime($data['from_time']));
    }

    public function checkin_out(Request $request, $id)
    {
        try {
            $today = date('Y-m-d');
            $user_id = Auth::id();
            $lims_hrm_setting_data = HrmSetting::latest()->first();

            // Evita fallo de marcaje cuando aún no existe configuración HRM.
            if (!$lims_hrm_setting_data) {
                $lims_hrm_setting_data = HrmSetting::create([
                    'checkin' => '09:00',
                    'checkout' => '18:00',
                ]);
            }
            $standard_checkin = $lims_hrm_setting_data->checkin;

            // --- Validar PIN si el empleado lo tiene configurado ---
            $employee = Employee::find($id);
            if ($employee && $employee->attendance_pin) {
                $pin = $request->input('pin');
                if (empty($pin)) {
                    return response()->json([
                        'status'       => false,
                        'requires_pin' => true,
                        'message'      => 'Se requiere el código PIN del empleado.',
                    ]);
                }
                if (!Hash::check($pin, $employee->attendance_pin)) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Código PIN incorrecto. Intente nuevamente.',
                    ]);
                }
            }
            // --- FIN validación PIN ---

            // Buscamos si el empleado tiene una entrada activa hoy (sin hora de salida registrada)
            $active_attendance = Attendance::whereDate('date', $today)
                ->where('employee_id', $id)
                ->whereNull('checkout')
                ->first();

            if (!$active_attendance) {
                // --- REGISTRAR ENTRADA (Check-in) ---
                $data = [
                    'date'        => $today,
                    'employee_id' => $id,
                    'user_id'     => $user_id,
                    'checkin'     => date('h:ia'),
                    'checkout'    => null,
                    'company_id'  => Auth::user()->company_id,
                ];

                $diff = strtotime($standard_checkin) - strtotime($data['checkin']);
                $data['status'] = ($diff >= 0) ? 1 : 0;

                $result = Attendance::create($data);

                $data_shift = [
                    'employee_id' => $id,
                    'status'      => 1,
                    'company_id'  => Auth::user()->company_id,
                ];
                $last = ShiftEmployee::whereDate('created_at', $today)->max('position');
                $data_shift['position'] = $last ? $last + 1 : 1;
                ShiftEmployee::create($data_shift);

                $status = (bool) $result;
                $type   = 'checkin';
            } else {
                // --- REGISTRAR SALIDA (Check-out) ---
                $active_attendance->checkout = date('h:ia');
                $result = $active_attendance->save();

                $position = ShiftEmployee::whereDate('created_at', $today)
                    ->where('employee_id', $id)
                    ->first();
                if ($position) {
                    $position->delete();
                }

                $status = (bool) $result;
                $type   = 'checkout';
            }

            return response()->json(['status' => $status, 'type' => $type]);
        } catch (\Throwable $e) {
            Log::error('Error al marcar asistencia de empleado', [
                'employee_id' => $id,
                'user_id' => Auth::id(),
                'company_id' => optional(Auth::user())->company_id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'status' => false,
                'type' => 'exception',
                'message' => 'Error interno al marcar asistencia. Revise logs.',
            ]);
        }
    }

    public function reset()
    {
        $hour = date('H:i');
        $date = date('Y-m-d');
        $pos_setting = PosSetting::latest()->first() ?? new PosSetting();
        if ($pos_setting->hour_resetshift != null) {
            $diff = strtotime($pos_setting->hour_resetshift) - strtotime($hour);
            if ($diff <= 0) {
                $dateend = $date . " " . $pos_setting->hour_resetshift;
                $positions = ShiftEmployee::whereBetween('created_at', [$date, $dateend])->get();
                foreach ($positions as $position) {
                    $checkin = Attendance::whereDate('date', $date)->where('employee_id', $position->employee_id)->first();
                    $checkin->delete();
                    $position->delete();
                }
                $data['status'] = 1;
            } else {
                $data['status'] = 0;
            }
        }
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function deleteBySelection(Request $request)
    {
        $attendance_id = $request['attendanceIdArray'];
        foreach ($attendance_id as $id) {
            $lims_attendance_data = Attendance::find($id);
            $lims_attendance_data->delete();
        }
        return 'Asistencias eliminados con éxito!';
    }

    public function destroy($id)
    {
        $lims_attendance_data = Attendance::find($id);
        $lims_attendance_data->delete();
        return redirect()->back()->with('not_permitted', 'Asistencia eliminado con éxito');
    }
}
