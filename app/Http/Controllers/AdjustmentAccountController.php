<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Auth;
use DB;
use App\Account;
use App\AdjustmentAccount;

class AdjustmentAccountController extends Controller
{
    public function index(){
        $role = Role::find(Auth::user()->role_id);
        //if($role->hasPermissionTo('adjustment-account-index')){
            $lims_adjustment_account_all = AdjustmentAccount::where('is_active', true)->get();
            return view('adjustment_account.index', compact('lims_adjustment_account_all'));
        //}
        //else
        //    return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function create(){
        $lims_accounts_list = Account::where('is_active', true)->get();
        return view('adjustment_account.create', compact('lims_accounts_list'));
    }

    public function store(Request $request)
    {
        $last_ref = AdjustmentAccount::get()->last();
        if($last_ref != null){
            $nros = explode("-", $last_ref['reference_no']);
            $nro = ltrim($nros[1], "0");
            $nro++;
            $nro = str_pad($nro, 8, "0", STR_PAD_LEFT); 
        }else{
            $nro = str_pad(1, 8, "0", STR_PAD_LEFT);
        }
        $data = $request->all();
        $data['reference_no'] = 'ADJ-'.$nro;
        $data['is_active'] = true;
        $lims_adjustment_data = AdjustmentAccount::create($data);
        //return redirect()->route('sale.pos');
        if($data['ajax'] == "false")
        return redirect('adjustment_account')->with('message', 'Dato Ingresado con éxito');
        else
            if($lims_adjustment_data)
                return json_encode(true);
            else
                return json_encode(false);
    }


    public function deleteBySelection(Request $request){
        $adjustment_id = $request['adjustmentIdArray'];
        foreach ($adjustment_id as $id) {
            $lims_adjustment_data = AdjustmentAccount::find($id);
            $lims_adjustment_data->is_active = false;
            $lims_adjustment_data->save();
        }
        return 'Ajuste Eliminado con éxito!';
    }

    public function destroy($id){
        $lims_adjustment_data = AdjustmentAccount::find($id);
        $lims_adjustment_data->is_active = false;
        $lims_adjustment_data->save();
        return redirect('adjustment_account')->with('not_permitted', 'Ajuste Eliminado con éxito');
    }

}
