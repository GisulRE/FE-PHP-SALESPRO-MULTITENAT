<?php

namespace App\Http\Controllers;

use App\AttentionShift;
use App\Customer;
use App\CustomerGroup;
use App\Employee;
use App\PreSale;
use App\Product_Presale;
use App\ShiftEmployee;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Pusher\Pusher;
use App\Http\Traits\EmployeeImageTrait;

class AttentionShiftController extends Controller
{
    use EmployeeImageTrait;

    private $date;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('attentionshift')) {
            $this->date = date('Y-m-d');
            $lims_customer_list = Customer::select('id', 'name', 'phone_number')->where('is_active', true)->orderBy('name', 'asc')->get();
            $lims_customer_group_all = CustomerGroup::where('is_active', true)->orderBy('name', 'asc')->get();
            return view('shift.index', compact('lims_customer_list', 'lims_customer_group_all'));
        } else {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }

    }

    public function list_Data(Request $request)
    {
        $this->date = date('Y-m-d');
        $totalData = AttentionShift::whereDate('created_at', $this->date)->count();
        $start = $request->input('start');
        $totalFiltered = $totalData;
        if ($request->input('length') != -1) {
            $limit = $request->input('length');
        } else {
            $limit = $totalData;
        }
        $lims_turno_all = AttentionShift::with('employee')
            ->whereDate('created_at', $this->date)
            ->orderBy('created_at', 'desc')
            ->offset($start)
            ->limit($limit)
            ->get();
        $data = array();
        if (!empty($lims_turno_all) && count($lims_turno_all) > 0) {
            $shift_ids = $lims_turno_all->pluck('id')->toArray();
            $presale_shift_ids = PreSale::whereIn('attentionshift_id', $shift_ids)
                ->pluck('attentionshift_id')
                ->flip()
                ->toArray();

            foreach ($lims_turno_all as $key => $turno) {
                $nestedData['id'] = $turno->id;
                $nestedData['key'] = $key + 1;
                $nestedData['reference_nro'] = $turno->reference_nro;
                $nestedData['customer'] = $turno->customer_name;
                $employeeName = ($turno->employee_id && $turno->employee) ? $turno->employee->name : null;
                if ($employeeName) {
                    $nestedData['employee'] = $employeeName;
                } else {
                    $nestedData['employee'] = 'Sin Asignar <button class="btn btn-success choose-emp"
                    onclick="choose_emp(' . $turno->id . ')" data-turno="' . $turno->id . '"><i class="dripicons-plus"></i></button>';
                }
                if ($turno->status == 1) {
                    $nestedData['status'] = '<div class="badge badge-success">En Atencion</div>';
                } else if ($turno->status == 3) {
                    $nestedData['status'] = '<div class="badge badge-info">Finalizado</div>';
                } else {
                    $nestedData['status'] = '<div class="badge badge-warning">En Espera</div>';
                }
                $has_presale = isset($presale_shift_ids[$turno->id]);
                if ($turno->status == 2 || $turno->status == 1) {
                    $employeeId   = $turno->employee_id ?? 0;
                    $empDisplayName = $employeeName ?: 'Sin Asignar';
                    $safeEmpJs = htmlspecialchars(json_encode($empDisplayName), ENT_QUOTES, 'UTF-8');
                    $nestedData['options'] = '<div class="btn-group">';
                    if ($has_presale) {
                        $nestedData['options'] .= '<button type="button" class="btn btn-sm btn-danger" disabled><i class="dripicons-trash"></i> ' . trans("file.delete") . '</button>';
                    } else {
                        $nestedData['options'] .= '<button type="button" class="btn btn-sm btn-danger"
                            onclick="openDeleteWithPin(' . $turno->id . ', ' . $employeeId . ', ' . $safeEmpJs . ')">
                            <i class="dripicons-trash"></i> ' . trans("file.delete") . '</button>';
                    }
                    $nestedData['options'] .= '</div>';

                } else {
                    $nestedData['options'] = '';
                }
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $data,
        );
        return $json_data;
    }

    /**
     * Show the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function list_cbx(Request $request)
    {
        $this->date = date('Y-m-d');
        $query = AttentionShift::select('id', 'reference_nro', 'customer_name', 'customer_id', 'employee_id', 'status')
            ->whereDate('created_at', $this->date)
            ->orderBy('created_at', 'desc');

        if ($request->filled('employee_id')) {
            $query->where('status', 1)
                ->whereNotNull('employee_id')
                ->where('employee_id', $request->employee_id);
        } else {
            $query->where('status', '<', 3);
        }

        $lims_turno_all = $query->get();
        return $lims_turno_all;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $data['user_id'] = Auth::id();
        $this->date = date('Y-m-d');
        $last_ref = AttentionShift::orderBy('id', 'desc')->first(['reference_nro']);
        if ($last_ref != null && !empty($last_ref->reference_nro)) {
            $nros = explode("-", $last_ref->reference_nro);
            $nro = isset($nros[1]) ? (int) ltrim($nros[1], "0") : 0;
            $nro++;
            $nro = str_pad($nro, 8, "0", STR_PAD_LEFT);
        } else {
            $nro = str_pad(1, 8, "0", STR_PAD_LEFT);
        }
        if (!empty($data['employee_id'])) {
            $statusEmployee = $this->findEmpShift($data['employee_id']);
            if ($statusEmployee['enabled'] == true) {
                return redirect('attentionshift')->with('not_permitted', 'Fallido al asignar Turno, Empleado con turno activo, Intente de Nuevo');
            }
        }
        $data['reference_nro'] = 'TRA-' . $nro;
        if (!empty($data['customer_id'])) {
            $customer = Customer::find($data['customer_id']);
            if ($customer) {
                $data['customer_name'] = $customer->name;
            }
        } elseif (!empty($data['customer_name'])) {
            $customer = Customer::where('name', $data['customer_name'])->first();
            if ($customer) {
                $data['customer_id'] = $customer->id;
                $data['customer_name'] = $customer->name;
            }
        }
        if (empty($data['employee_id'])) {
            $data['status'] = 2;
        } else {
            $data['status'] = 1;
            $employee_position = ShiftEmployee::where([['status', 1], ['employee_id', $data['employee_id']]])
                ->whereDate('created_at', $this->date)->first();
            if ($employee_position) {
                $employee_position->status = 0;
                $employee_position->save();
            }
        }
        $result = AttentionShift::create($data);
        if ($result) {
            return redirect('attentionshift')->with('message', 'Turno creado con éxito');
        } else {
            return redirect('attentionshift')->with('not_permitted', 'Fallido al asignar Turno, Intente de Nuevo');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();
        $this->date = date('Y-m-d');
        $turno_data = AttentionShift::find($data['id']);
        if ($data['employee']) {
            $turno_data->employee_id = $data['employee'];
            $turno_data->status = 1;
            $employee_position = ShiftEmployee::where([['status', 1], ['employee_id', $data['employee']]])
                ->whereDate('created_at', $this->date)->first();
            $employee_position->status = 0;
            $employee_position->save();
        }
        $options = array(
            'cluster' => 'sa1',
            'useTLS' => true,
        );

        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            $options
        );

        $turno_data->save();
        $data = ['from' => $turno_data];
        //$pusher->trigger('my-channel', 'my-event', $data);
        \Session::flash('message', 'Turno actualizado con éxito');
        //return array('message' => 'Turno actualizado con éxito', 'status' => true);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $url = url()->previous();
        $this->date = date('Y-m-d');
        $turno_data = AttentionShift::find($id);
        //$presale_data = PreSale::where('attentionshift_id', $id)->whereDate('created_at', $this->date)->first();
        if ($turno_data->employee_id != null) {
            $employee_position = ShiftEmployee::where([['status', 0], ['employee_id', $turno_data->employee_id]])
                ->whereDate('created_at', $this->date)->first();
            $employee_position->status = 1;
            $employee_position->save();
        }
        $turno_data->delete();
        return Redirect::to($url)->with('not_permitted', "Turno Eliminado con éxito, Empleado liberado");
    }

    /**
     * Eliminar un turno directamente liberando al empleado y limpiando preventas asociadas.
     *
     * DELETE /attentionshift/{id}/secure
     */
     public function destroyWithPin(Request $request, $id)
     {
         $this->date = date('Y-m-d');
         $turno_data = AttentionShift::find($id);

         if (!$turno_data) {
             return response()->json(['success' => false, 'message' => 'Turno no encontrado'], 404);
         }

         // Liberar posición del empleado asociado si existe
         if ($turno_data->employee_id) {
             $employee_position = ShiftEmployee::where([['status', 0], ['employee_id', $turno_data->employee_id]])
                 ->whereDate('created_at', $this->date)->first();
             if ($employee_position) {
                 $employee_position->status = 1;
                 $employee_position->save();
             }
         }

         // Eliminar preventa asociada si existe
         $presale = PreSale::where('attentionshift_id', $id)->first();
         if ($presale) {
             Product_Presale::where('presale_id', $presale->id)->delete();
             $presale->delete();
         }

         $turno_data->delete();

         return response()->json([
             'success' => true,
             'message' => 'Turno eliminado con éxito, empleado liberado.',
         ]);
     }

    /**
     * Show the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function employeeFirst()
    {
        $this->date = date('Y-m-d');
        $active_emp_ids = AttentionShift::where('status', 1)
            ->whereDate('created_at', $this->date)
            ->whereNotNull('employee_id')
            ->pluck('employee_id')
            ->toArray();

        $query = ShiftEmployee::select('shift_employee.id', 'shift_employee.employee_id', 'employees.name')
            ->join('employees', 'shift_employee.employee_id', '=', 'employees.id')
            ->where('shift_employee.status', 1)
            ->whereDate('shift_employee.created_at', $this->date);

        if (!empty($active_emp_ids)) {
            $query->whereNotIn('shift_employee.employee_id', $active_emp_ids);
        }

        $lims_employee = $query->orderBy('shift_employee.position', 'asc')->first();
        return $lims_employee;
    }

    /**
     * Show the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function employeeAll()
    {
        $this->date = date('Y-m-d');
        $active_emp_ids = AttentionShift::where('status', 1)
            ->whereDate('created_at', $this->date)
            ->whereNotNull('employee_id')
            ->pluck('employee_id')
            ->toArray();

        $query = ShiftEmployee::select('shift_employee.employee_id', 'employees.name')
            ->join('employees', 'shift_employee.employee_id', '=', 'employees.id')
            ->where('shift_employee.status', 1)
            ->whereDate('shift_employee.created_at', $this->date);

        if (!empty($active_emp_ids)) {
            $query->whereNotIn('shift_employee.employee_id', $active_emp_ids);
        }

        $lims_employee = $query->orderBy('shift_employee.position', 'asc')->get();
        return $lims_employee;
    }

    public function findEmpShift($id)
    {
        if (empty($id)) {
            return array('enabled' => false);
        }
        $this->date = date('Y-m-d');
        $turno_data = AttentionShift::where([['employee_id', $id], ['status', 1]])
            ->whereDate('created_at', $this->date)
            ->first();
        if ($turno_data) {
            return array('enabled' => true);
        } else {
            return array('enabled' => false);
        }
    }
    public function listemployeEnable()
    {
        $this->date = date('Y-m-d');
        $active_emp_ids = AttentionShift::where('status', 1)
            ->whereDate('created_at', $this->date)
            ->whereNotNull('employee_id')
            ->pluck('employee_id')
            ->toArray();

        $query = ShiftEmployee::with('employee')
            ->where('status', 1)
            ->whereDate('created_at', $this->date);

        if (!empty($active_emp_ids)) {
            $query->whereNotIn('employee_id', $active_emp_ids);
        }

        $lims_employee_list = $query->orderBy('position', 'asc')->get();
        $totalFiltered = 0;
        $data = array();
        if ($lims_employee_list->isNotEmpty()) {
            foreach ($lims_employee_list as $key => $employee_turno) {
                if (!$employee_turno->employee) {
                    continue;
                }
                $totalFiltered = $totalFiltered + 1;
                $key = $key + 1;
                $empId = $employee_turno->employee->id;
                $empName = $employee_turno->employee->name;
                $safeNameJs = htmlspecialchars(json_encode($empName), ENT_QUOTES, 'UTF-8');
                $nestedData['div'] = '<div class="col-md-3 attendance-img text-center" onclick="attendance(' . $empId . ', ' . $safeNameJs . ')">';
                if ($employee_turno->employee->image) {
                    $imgUrl = $this->getEmployeeThumbnailUrl($employee_turno->employee->image);
                } else {
                    $imgUrl = url('public/images/product/zummXD2dvAtI.png');
                }
                $nestedData['div'] .= '<img src="' . $imgUrl . '" alt="' . htmlspecialchars($empName, ENT_QUOTES, 'UTF-8') . '" width="70" height="70" loading="lazy" decoding="async" style="border-style: double; width: 70px; height: 70px; object-fit: cover;" />';
                $nestedData['div'] .= '<p class="text-center">' . $key . '.-' . htmlspecialchars($empName, ENT_QUOTES, 'UTF-8') . '</p></div>';
                $data[] = $nestedData;
            }
            if (empty($data)) {
                $nestedData['div'] = '<div class="col-12 text-center text-muted py-3">No hay Empleados Disponibles</div>';
                $data[] = $nestedData;
            }
        } else {
            $nestedData['div'] = '<div class="col-12 text-center text-muted py-3">No hay Empleados Disponibles</div>';
            $data[] = $nestedData;
        }
        $json_data = array(
            "recordsFiltered" => intval($totalFiltered),
            "data" => $data,
        );
        return $json_data;
    }

    public function testEvent()
    {
        $options = array(
            'cluster' => 'sa1',
            'useTLS' => true,
        );

        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            $options
        );
        $data = ['from' => "Test"];
        $pusher->trigger('my-channel', 'my-event', $data);
    }

    public function verifyBirthday(Request $request)
    {
        $data = $request->all();
        $customerName = isset($data['customer_name']) ? trim($data['customer_name']) : '';
        if (empty($customerName) || strcasecmp($customerName, 'Clientes Varios') === 0 || strcasecmp($customerName, 'Cliente Varios') === 0) {
            return array('birthday' => false);
        }

        setlocale(LC_TIME, 'es_ES.UTF-8');
        setlocale(LC_TIME, 'spanish');
        $customer = Customer::where('name', $customerName)->first();
        if ($customer && $customer->date_birh != null) {
            $birthday = date('Y-') . date('m-d', strtotime($customer->date_birh));
            $diff = abs(strtotime($birthday) - strtotime(date('Y-m-d')));
            $years = floor($diff / (365 * 60 * 60 * 24));
            $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
            $days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));
            if ($years == 0 && ($months <= 1 && $days >= 0)) {
                //alertar
                if ($months == 0 && $days == 0) {
                    return array(
                        'birthday' => true,
                        'message' => 'Felicidades esta de cumpleaños, Hoy!',
                        'date' => date('m-d', strtotime($customer->date_birh))
                    );
                } else {
                    return array(
                        'birthday' => true,
                        'message' => 'Faltan ' . $months . ' mes y ' . $days . ' días para su cumpleaños! '.PHP_EOL.' Fecha de Cumpleaños: ' . date('d/m/Y', strtotime($customer->date_birh)),
                        'date' => date('m-d', strtotime($customer->date_birh))
                    );
                }
            }
        }
        return array('birthday' => false);
    }
}