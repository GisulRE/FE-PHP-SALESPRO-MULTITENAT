<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Account;
use App\Employee;
use App\Payroll;
use App\Expense;
use App\ExpenseCategory;
use App\Warehouse;
use Auth;
use DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Mail\UserNotification;
use Illuminate\Support\Facades\Mail;


class PayrollController extends Controller
{

    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('payroll')) {
            $lims_account_list = Account::where('is_active', true)->get();
            $lims_employee_list = Employee::where('is_active', true)->where('company_id', Auth::user()->company_id)->get();
            return view('payroll.index', compact('lims_account_list', 'lims_employee_list'));
        } else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function payrollData(Request $request)
    {
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
            3 => 'employee_id',
            4 => 'account_id',
            5 => 'amount',
            6 => 'paying_method',
            7 => 'note',
        );

        if (Auth::user()->role_id > 2 && config('staff_access') == 'own') {
            $totalData = Payroll::where('user_id', Auth::id())->count();
        } else {
            $totalData = Payroll::count();
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
                $payrolls = Payroll::with('employee', 'account')->offset($start)
                    ->where('user_id', Auth::id())
                    ->limit($limit)
                    ->orderBy($order, $dir)
                    ->get();
            } else {
                $payrolls = Payroll::with('employee', 'account')->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)
                    ->get();
            }
        } else {
            $search = $request->input('search.value');
            if (Auth::user()->role_id > 2 && config('staff_access') == 'own') {
                $payrolls = Payroll::select('payrolls.*')
                    ->with('account')
                    ->leftJoin('employees', 'payrolls.employee_id', '=', 'employees.id')
                    ->whereDate('payrolls.created_at', '=', date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                    ->where('payrolls.user_id', Auth::id())
                    ->orwhere([
                        ['payrolls.reference_no', 'LIKE', "%{$search}%"],
                        ['payrolls.user_id', Auth::id()],
                    ])
                    ->orwhere([
                        ['employees.name', 'LIKE', "%{$search}%"],
                        ['payrolls.user_id', Auth::id()],
                    ])
                    ->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)->get();

                $totalFiltered = Payroll::leftJoin('employees', 'payrolls.employee_id', '=', 'employees.id')
                    ->whereDate('payrolls.created_at', '=', date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                    ->where('payrolls.user_id', Auth::id())
                    ->orwhere([
                        ['payrolls.reference_no', 'LIKE', "%{$search}%"],
                        ['payrolls.user_id', Auth::id()],
                    ])
                    ->orwhere([
                        ['employees.name', 'LIKE', "%{$search}%"],
                        ['payrolls.user_id', Auth::id()],
                    ])
                    ->count();
            } else {
                $payrolls = Payroll::select('payrolls.*')
                    ->with('account')
                    ->leftJoin('employees', 'payrolls.employee_id', '=', 'employees.id')
                    ->whereDate('payrolls.created_at', '=', date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                    ->orwhere('payrolls.reference_no', 'LIKE', "%{$search}%")
                    ->orwhere('employees.name', 'LIKE', "%{$search}%")
                    ->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)
                    ->get();

                $totalFiltered = Payroll::leftJoin('employees', 'payrolls.employee_id', '=', 'employees.id')
                    ->whereDate('payrolls.created_at', '=', date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                    ->orwhere('payrolls.reference_no', 'LIKE', "%{$search}%")
                    ->orwhere('employees.name', 'LIKE', "%{$search}%")
                    ->count();
            }
        }
        $data = array();
        if (!empty($payrolls)) {
            foreach ($payrolls as $key => $payroll) {
                $nestedData['id'] = $payroll->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime($payroll->created_at->toDateString()));
                $nestedData['reference_no'] = $payroll->reference_no;

                if ($payroll->employee_id) {
                    $employee = $payroll->employee;
                } else {
                    $employee = new Employee();
                }
                $nestedData['employee'] = $employee->name;
                if ($payroll->account_id) {
                    $account = $payroll->account;
                } else {
                    $account = new Account();
                }
                $nestedData['account'] = $account->name;
                if ($payroll->paying_method == 0) {
                    $nestedData['paying_method'] = 'Efectivo';
                } elseif ($payroll->paying_method == 1) {
                    $nestedData['paying_method'] = 'Cheque';
                } elseif ($payroll->paying_method == 2) {
                    $nestedData['paying_method'] = 'Transferencia';
                } else {
                    $nestedData['paying_method'] = 'Otro';
                }
                $nestedData['amount'] = number_format($payroll->amount, 2);

                $nestedData['options'] = '<div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . trans("file.action") . '
                              <span class="caret"></span>
                              <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">';
                $nestedData['options'] .=
                    '<li>
                        <button type="button" class="edit-btn btn btn-link" data-id = "' . $payroll->id . '" data-toggle="modal" data-target="#editModal" onclick="openDialog(' . $payroll->id . ')"><i class="dripicons-document-edit"></i> ' . trans('file.edit') . '</button>
                    </li>';
                $nestedData['options'] .= \Form::open(["route" => ["payroll.destroy", $payroll->id], "method" => "DELETE"]) . '
                    <li>
                      <button type="submit" class="btn btn-link" onclick="return confirmDelete()"><i class="dripicons-trash"></i> ' . trans("file.delete") . '</button>
                    </li>' . \Form::close() . '
                </ul>
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
        $data['reference_no'] = 'payroll-' . date("Ymd") . '-' . date("his");
        $data['user_id'] = Auth::id();
        $payroll = Payroll::create($data);

        // Ya no se registra automáticamente en gastos
        // El registro de nómina (payroll) es independiente de los gastos (expenses)

        $message = 'Payroll creared succesfully';
        //collecting mail data
        $lims_employee_data = Employee::find($data['employee_id']);
        $mail_data['reference_no'] = $data['reference_no'];
        $mail_data['amount'] = $data['amount'];
        $mail_data['name'] = $lims_employee_data->name;
        $mail_data['email'] = $lims_employee_data->email;
        try {
            Mail::send('mail.payroll_details', $mail_data, function ($message) use ($mail_data) {
                $message->to($mail_data['email'])->subject('Payroll Details');
            });
        } catch (\Exception $e) {
            $message = ' Payroll created successfully. Please setup your <a href="setting/mail_setting">mail setting</a> to send mail.';
        }

        return redirect('payroll')->with('message', $message);
    }

    public function edit($id)
    {
        $lims_payroll_data = Payroll::find($id);
        return $lims_payroll_data;
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $lims_payroll_data = Payroll::find($data['payroll_id']);
        $lims_payroll_data->update($data);
        return redirect('payroll')->with('message', 'Payroll updated succesfully');
    }

    public function deleteBySelection(Request $request)
    {
        $payroll_id = $request['payrollIdArray'];
        foreach ($payroll_id as $id) {
            $lims_payroll_data = Payroll::find($id);
            $lims_payroll_data->delete();
        }
        return 'Payroll deleted successfully!';
    }

    public function destroy($id)
    {
        $lims_payroll_data = Payroll::find($id);
        $lims_payroll_data->delete();
        return redirect('payroll')->with('not_permitted', 'Payroll deleted succesfully');
    }
}
