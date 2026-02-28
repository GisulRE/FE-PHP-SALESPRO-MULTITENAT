<?php

namespace App\Http\Controllers;

use App\Attendance;
use App\Employee;
use App\HrmSetting;
use App\PosSetting;
use App\ShiftEmployee;
use App\User;
use Auth;
use DB;
use Illuminate\Http\Request;
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
        $order = $columns[$request->input('order.0.column')];
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
        $checkin = $lims_hrm_setting_data->checkin;
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

    public function checkin_out($id)
    {
        $data['date'] = date('Y-m-d');
        $data['checkout'] = null;
        $data['user_id'] = Auth::id();
        $lims_hrm_setting_data = HrmSetting::latest()->first();
        $checkin = $lims_hrm_setting_data->checkin;
        $lims_attendance_data = Attendance::whereDate('date', $data['date'])->where('employee_id', $id)->first();
        if (!$lims_attendance_data) {
            $data['employee_id'] = $id;
            $data['checkin'] = date('h:ia');
            $data['company_id'] = Auth::user()->company_id;
            $diff = strtotime($checkin) - strtotime($data['checkin']);
            if ($diff >= 0) {
                $data['status'] = 1;
            } else {
                $data['status'] = 0;
            }

            $result = Attendance::create($data);
            $data['status'] = 1;
            $last = ShiftEmployee::whereDate('created_at', $data['date'])->max('position');
            if ($last) {
                $data['position'] = $last + 1;
            } else {
                $data['position'] = 1;
            }
            ShiftEmployee::create($data);
            if ($result) {
                $status = true;
            } else {
                $status = false;
            }
            $type = "checkin";
        } else {
            $data['checkout'] = date('h:ia');
            $lims_attendance_data->checkout = $data['checkout'];
            $position = ShiftEmployee::whereDate('created_at', $data['date'])->where('employee_id', $id)->first();
            $lims_attendance_data->delete();
            if ($position)
                $position->delete();

            $status = true;
            $type = "checkout";
        }
        return array('status' => $status, 'type' => $type);
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
