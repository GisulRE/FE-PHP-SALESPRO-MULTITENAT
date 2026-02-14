<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Expense;
use App\Account;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Auth;
use DB;

class ExpenseController extends Controller
{
    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('expenses-index')){
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if(empty($all_permission))
                $all_permission[] = 'dummy text';
            $lims_account_list = Account::where('is_active', true)->get();
            
            if(Auth::user()->role_id > 2 && config('staff_access') == 'own')
                $lims_expense_all = Expense::orderBy('id', 'desc')->where('user_id', Auth::id())->get();
            else
                $lims_expense_all = Expense::orderBy('id', 'desc')->get();
            return view('expense.index', compact('lims_account_list', 'lims_expense_all', 'all_permission'));
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $data['reference_no'] = 'er-' . date("Ymd") . '-'. date("his");
        $data['user_id'] = Auth::id();
        if($data['created_at']){
            $data['created_at'] = $data['created_at']." ".date("h:i:s");
        }else{
            $data['created_at'] = date("Y-m-d h:i:s");
        }
        Expense::create($data);
        return redirect('expenses')->with('message', 'Dato registrado con éxito');
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        $role = Role::firstOrCreate(['id' => Auth::user()->role_id]);
        if ($role->hasPermissionTo('expenses-edit')) {
            $lims_expense_data = Expense::find($id);
            return $lims_expense_data;
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $lims_expense_data = Expense::find($data['expense_id']);
        $create_at = date('Y-m-d', strtotime($lims_expense_data->created_at));
        if($data['date_expense'] != $create_at){
            $data['created_at'] = $data['date_expense']." ".date("h:i:s");
        }else{
            $data['created_at'] = $lims_expense_data->created_at;
        }
        $lims_expense_data->update($data);
        return redirect('expenses')->with('message', 'Dato actualizado con éxito');
    }

    public function deleteBySelection(Request $request)
    {
        $expense_id = $request['expenseIdArray'];
        foreach ($expense_id as $id) {
            $lims_expense_data = Expense::find($id);
            $lims_expense_data->delete();
        }
        return 'Gasto Eliminado con éxito!';
    }

    public function destroy($id)
    {
        $lims_expense_data = Expense::find($id);
        $lims_expense_data->delete();
        return redirect('expenses')->with('not_permitted', 'Dato actualizado con éxito');
    }
}
