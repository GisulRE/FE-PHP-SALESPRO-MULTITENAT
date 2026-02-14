<?php

namespace App\Http\Controllers;

use App\Biller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Cashier;
use App\Account;
use App\Payment;
use App\Returns;
use App\MoneyTransfer;
use App\AdjustmentAccount;
use DB;
use Auth;

class CashierController extends Controller
{
    public function index(){
        $lims_cashier_all = Cashier::get();
        return view('cashier.index', compact('lims_cashier_all'));
    }

    public function store(Request $request){
        $data = $request->all();
        $account_id = Biller::find($data['biller_id'])->account_id; 
        if(!array_key_exists('note', $data)){
            $data['note'] = "Apertura Caja en POS";
        }
        $data['account_id'] = $account_id;
        $data['start_date'] = date('Y-m-d H:i:s');
        $data['is_active'] = true;
        $lims_cashier_data = Cashier::create($data);
        return redirect()->route('sale.pos');
        //return redirect('adjustment_account')->with('message', 'Dato Ingresado con éxito');
    }

    public function update(Request $request){
        $data = $request->all();
        $lims_cashier_data = Cashier::where([['account_id', $data['account_id_cerrar']], ['is_active', true], ['end_date', null]])->first();   
        $lims_cashier_data->end_date = date('Y-m-d H:i:s');
        $lims_cashier_data->is_active = false;
        $lims_cashier_data->amount_end = $data['amount_end'];
        $lims_cashier_data->save();
        return redirect()->route('sale.pos');
        //return redirect('adjustment_account')->with('message', 'Dato Ingresado con éxito');
        
    }

    public function close_cashier(Request $request){
        $data = $request->all();
        $lims_cashier_data = Cashier::where([['account_id', $data['account_id_cerrar']], ['is_active', true], ['end_date', null]])->first();     
        $lims_cashier_data->is_active = false;
        $lims_cashier_data->end_date = date('Y-m-d H:i:s');
        $lims_cashier_data->amount_end = $data['amount_end'];
        $lims_cashier_data->save();
        return redirect('/')->with('message', 'Cierre de Caja con exito');
    }

    public function verified_amount($id){
        $account_id = Biller::find($id)->account_id; 
        $lims_cashier_data = Cashier::select('amount_end')->where([['account_id', $account_id], ['is_active', false]])->first();     
        $lims_account_data = Account::select('id', 'name', 'account_no')->find($account_id);  
        return $result = array('cashier' => $lims_cashier_data, 'account' => $lims_account_data);     
    }

    public function old_amount($id){
        $account_id = Biller::find($id)->account_id; 
        $total_old = 0;
        $start_date = date('Y-m-d H:i:s');
        $lims_cashier_data = Cashier::select('amount_end')->where([['account_id', $account_id], ['is_active', false]])->first();   
        if($lims_cashier_data == null){
            $startbef_date = date("Y-m-d",strtotime($start_date."- 5 year")); 
            $endafter_date = date("Y-m-d",strtotime($start_date."1 days"));
            /**** Totales Egresos - Ingresos - Saldo Ant */
            $saldoant = 0;
            $credit = 0;
            $debit = 0;
            $lims_account_data = Account::select('id', 'name', 'account_no', 'initial_balance')->find($account_id);

            $payment_recieved = Payment::whereNotNull('sale_id')->where('account_id', $account_id)
            ->whereDate('created_at', '>=' , $startbef_date)->whereDate('created_at', '<=' , $endafter_date)->sum('amount');
            $payment_sent = Payment::whereNotNull('purchase_id')->where('account_id', $account_id)
            ->whereDate('created_at', '>=' , $startbef_date)->whereDate('created_at', '<=' , $endafter_date)->sum('amount');
            $returns = Returns::where('account_id', $account_id)->whereDate('created_at', '>=' , $startbef_date)
            ->whereDate('created_at', '<=' , $endafter_date)->sum('grand_total');
            $return_purchase = DB::table('return_purchases')->where('account_id', $account_id)
            ->whereDate('created_at', '>=' , $startbef_date)->whereDate('created_at', '<=' , $endafter_date)->sum('grand_total');
            $expenses = DB::table('expenses')->where('account_id', $account_id)
            ->whereDate('created_at', '>=' , $startbef_date)->whereDate('created_at', '<=' , $endafter_date)->sum('amount');
            $payrolls = DB::table('payrolls')->where('account_id', $account_id)
            ->whereDate('created_at', '>=' , $startbef_date)->whereDate('created_at', '<=' , $endafter_date)->sum('amount');
            $sent_money_via_transfer = MoneyTransfer::where('from_account_id', $account_id)
            ->whereDate('created_at', '>=' , $startbef_date)->whereDate('created_at', '<=' , $endafter_date)->sum('amount');
            $recieved_money_via_transfer = MoneyTransfer::where('to_account_id', $account_id)
            ->whereDate('created_at', '>=' , $startbef_date)->whereDate('created_at', '<=' , $endafter_date)->sum('amount');
            $adjustment_account_ing = AdjustmentAccount::where([['account_id', $account_id],['is_active', true],['type_adjustment', 'ING']])
            ->whereDate('created_at', '>=' , $startbef_date)->whereDate('created_at', '<=' , $endafter_date)->sum('amount');
            $adjustment_account_egr = AdjustmentAccount::where([['account_id', $account_id],['is_active', true],['type_adjustment', 'EGR']])
            ->whereDate('created_at', '>=' , $startbef_date)->whereDate('created_at', '<=' , $endafter_date)->sum('amount');

            $credit = $payment_recieved + $return_purchase + $recieved_money_via_transfer + $adjustment_account_ing + $lims_account_data->initial_balance;
            $debit = $payment_sent + $returns + $expenses + $payrolls + $sent_money_via_transfer + $adjustment_account_egr;
            $saldoant = $credit - $debit;
            $total_old = $saldoant;
        }else{
            $total_old = $lims_cashier_data->amount_end;
        }
        return array('amount_end' => $total_old);  
    }

    public function getdata($id){
        return $lims_cashier_data = Cashier::select('start_date')->where([['account_id', $id], ['is_active', true], ['end_date', null]])->first();   
    }
}
