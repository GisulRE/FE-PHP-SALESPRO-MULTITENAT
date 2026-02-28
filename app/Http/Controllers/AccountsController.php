<?php

namespace App\Http\Controllers;

use App\Biller;
use Illuminate\Http\Request;
use App\Account;
use App\Payment;
use App\Sale;
use App\Product_Sale;
use App\Purchase;
use App\Returns;
use App\ReturnPurchase;
use App\Expense;
use App\Payroll;
use App\MoneyTransfer;
use App\MethodPayment;
use App\AccountPayment;
use App\AdjustmentAccount;
use App\Cashier;
use DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Auth;


class AccountsController extends Controller
{
    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('account-index')) {
            $lims_account_all = Account::where('is_active', true)->get();
            return view('account.index', compact('lims_account_all'));
        } else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function create()
    {
        $lims_methods_list = MethodPayment::where([['apply', true], ['used', false]])->get();
        $data = array('list_method' => $lims_methods_list);
        return $data;
    }

    public function store(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $this->validate($request, [
            'account_no' => [
                'max:255',
                Rule::unique('accounts')->where(function ($query) use ($companyId) {
                    return $query->where('is_active', 1)->where('company_id', $companyId);
                }),
            ],
        ]);

        $lims_account_data = Account::where('is_active', true)->first();
        $data = $request->all();
        if ($data['initial_balance'])
            $data['total_balance'] = $data['initial_balance'];
        else
            $data['total_balance'] = 0;
        if (!$lims_account_data)
            $data['is_default'] = 1;
        $data['is_active'] = true;
        $res = Account::create($data);
        if (array_key_exists('methodpaynew', $data)) {
            $methods = $data['methodpaynew'];
            foreach ($methods as $method) {
                $data_m['account_id'] = $res->id;
                $data_m['methodpay_id'] = $method;
                $data_m['is_active'] = true;
                AccountPayment::create($data_m);
                $data_method = MethodPayment::find($method);
                $data_method->used = true;
                $data_method->save();
            }
        }
        return redirect('accounts')->with('message', 'Cuenta creado exitosamente');
    }

    public function makeDefault($id)
    {
        $lims_account_data = Account::where('is_default', true)->first();
        $lims_account_data->is_default = false;
        $lims_account_data->save();

        $lims_account_data = Account::find($id);
        $lims_account_data->is_default = true;
        $lims_account_data->save();

        return 'La cuenta a sido seleccionado por defecto.';
    }

    public function edit($id)
    {
        $lims_account_data = Account::findOrFail($id);
        $lims_accountpay_list = AccountPayment::where([['is_active', true], ['account_id', $id]])->get();
        $lims_methods_list = MethodPayment::where([['apply', true], ['used', false]])->get();
        for ($i = 0; $i < count($lims_accountpay_list); $i++) {
            $data_method = MethodPayment::find($lims_accountpay_list[$i]->methodpay_id);
            $lims_methods_list[] = $data_method;
        }
        $lims_accountpaysel_list = AccountPayment::select('methodpay_id')->where([['is_active', true], ['account_id', $id]])->get();
        $data = array('account' => $lims_account_data, 'list_method' => $lims_methods_list, 'methods' => $lims_accountpaysel_list);
        return $data;
    }

    public function update(Request $request, $id)
    {
        $companyId = Auth::user()->company_id;
        $this->validate($request, [
            'account_no' => [
                'max:255',
                Rule::unique('accounts')->ignore($request->account_id)->where(function ($query) use ($companyId) {
                    return $query->where('is_active', 1)->where('company_id', $companyId);
                }),
            ],
        ]);

        $data = $request->all();
        $lims_account_data = Account::find($data['account_id']);
        if ($data['initial_balance'])
            $data['total_balance'] = $data['initial_balance'];
        else
            $data['total_balance'] = 0;
        $lims_account_data->update($data);
        if ($lims_account_data == null) {
            if (array_key_exists('methodpays', $data)) {
                $methods = $data['methodpays'];
                $lims_accountpaysel_list = AccountPayment::select('methodpay_id')->where([['is_active', true], ['account_id', $data['account_id']]])->get();

                if (count($lims_accountpaysel_list) != count($methods)) {
                    foreach ($lims_accountpaysel_list as $ant) {
                        $data_ant = AccountPayment::where([['account_id', $data['account_id']], ['methodpay_id', $ant->methodpay_id]])->first();
                        $data_ant->is_active = false;
                        $data_ant->save();
                        $data_method = MethodPayment::find($data_ant->methodpay_id);
                        $data_method->used = false;
                        $data_method->save();
                    }
                }
                foreach ($methods as $method) {
                    $data_ant = AccountPayment::where([['account_id', $data['account_id']], ['methodpay_id', $method]])->first();
                    if ($data_ant != null && $data_ant->methodpay_id == $method) {
                        $data_ant->is_active = true;
                        $data_ant->save();
                        $data_method = MethodPayment::find($data_ant->methodpay_id);
                        $data_method->used = true;
                        $data_method->save();
                    } else {
                        $data_m['account_id'] = $data['account_id'];
                        $data_m['methodpay_id'] = $method;
                        $data_m['is_active'] = true;
                        AccountPayment::create($data_m);
                        $data_method = MethodPayment::find($method);
                        $data_method->used = true;
                        $data_method->save();
                    }
                }
            } else {
                $lims_accountpay_list = AccountPayment::where([['is_active', true], ['account_id', $data['account_id']]])->get();
                foreach ($lims_accountpay_list as $ant) {
                    $data_ant = AccountPayment::where([['account_id', $data['account_id']], ['methodpay_id', $ant->methodpay_id]])->first();
                    $data_ant->is_active = false;
                    $data_ant->save();
                    $data_method = MethodPayment::find($data_ant->methodpay_id);
                    $data_method->used = false;
                    $data_method->save();
                }
            }
            return redirect('accounts')->with('message', 'Cuenta actualizado con éxito');
        } else {
            return redirect('accounts')->with('message', 'Cuenta actualizado con éxito');

            //return redirect('accounts')->with('error', 'Error al actualizar cuenta');
        }
    }

    public function balanceSheet()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('balance-sheet')) {
            $lims_account_list = Account::where('is_active', true)->get();
            $debit = [];
            $credit = [];
            foreach ($lims_account_list as $account) {
                $payment_recieved = Payment::whereNotNull('sale_id')->where('account_id', $account->id)->sum('amount');
                $payment_sent = Payment::whereNotNull('purchase_id')->where('account_id', $account->id)->sum('amount');
                $returns = DB::table('returns')->where('account_id', $account->id)->sum('grand_total');
                $return_purchase = DB::table('return_purchases')->where('account_id', $account->id)->sum('grand_total');
                $expenses = DB::table('expenses')->where('account_id', $account->id)->sum('amount');
                $payrolls = DB::table('payrolls')->where('account_id', $account->id)->sum('amount');
                $sent_money_via_transfer = MoneyTransfer::where('from_account_id', $account->id)->sum('amount');
                $recieved_money_via_transfer = MoneyTransfer::where('to_account_id', $account->id)->sum('amount');
                $adjustment_account_ing = AdjustmentAccount::where([['account_id', $account->id], ['is_active', true], ['type_adjustment', 'ING']])->sum('amount');
                $adjustment_account_egr = AdjustmentAccount::where([['account_id', $account->id], ['is_active', true], ['type_adjustment', 'EGR']])->sum('amount');

                $credit[] = $payment_recieved + $return_purchase + $recieved_money_via_transfer + $adjustment_account_ing + $account->initial_balance;
                $debit[] = $payment_sent + $returns + $expenses + $payrolls + $sent_money_via_transfer + $adjustment_account_egr;

                /*$credit[] = $payment_recieved + $return_purchase + $account->initial_balance;
                $debit[] = $payment_sent + $returns + $expenses + $payrolls;*/
            }
            return view('account.balance_sheet', compact('lims_account_list', 'debit', 'credit'));
        } else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function balanceSheetAccount(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = date("Y-m-d");
        $end_date_temp = $start_date;
        $end_date = $start_date . " 23:59:59";
        $startbef_date = date("Y-m-d", strtotime($start_date . "- 5 year"));
        $endafter_date = date("Y-m-d", strtotime($start_date . "- 1 days"));
        $cashier_open = false;
        $lims_biller_list = array();
        if (is_null($request->biller_id)) {
            $biller = Biller::where('is_active', true)->first();
        } else {
            $biller = Biller::find($request->biller_id);
        }
        if (Auth::user()->role_id > 2 && Auth::user()->biller) {
            $lims_biller_list[] = Auth::user()->biller;
        } else {
            $lims_biller_list = Biller::where('is_active', true)->get();
        }

        $lims_account_list = array();
        $lims_account_list[] = $biller->account_id;
        $lims_account_list[] = $biller->account_id_cheque;
        $lims_account_list[] = $biller->account_id_tarjeta;
        $lims_account_list[] = $biller->account_id_qr;
        $lims_account_list[] = $biller->account_id_deposito;
        $lims_account_list[] = $biller->account_id_receivable;
        $lims_account_list[] = $biller->account_id_giftcard;
        $lims_account_list[] = $biller->account_id_vale;
        $lims_account_list[] = $biller->account_id_otros;
        $lims_account_list[] = $biller->account_id_pagoposterior;
        $lims_account_list[] = $biller->account_id_transferenciabancaria;
        $lims_account_list[] = $biller->account_id_swift;

        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('balance-sheet-account')) {
            $lims_account_data = Account::find($biller->account_id);
            if ($lims_account_data != null) {
                /* Ingreso */
                $lims_sales_list = Sale::select('sales.id', 'sales.reference_no', 'sales.grand_total')
                    ->where('sales.biller_id', $biller->id)
                    ->whereDate('sales.created_at', '>=', $start_date)->whereDate('sales.created_at', '<=', $end_date)
                    ->groupBy('sales.id')->get();
                $accountfull = [];

                foreach ($lims_sales_list as $sale) {
                    $listPayments = Payment::select('id', 'sale_id', 'amount', 'paying_method')->where("sale_id", $sale->id)->whereIn('account_id', $lims_account_list)->get();
                    foreach ($listPayments as $pay) {
                        $details = [];
                        if ($pay->paying_method == "Efectivo") {
                            $details[] = array(
                                'id' => $sale->id,
                                'reference' => $sale->reference_no,
                                'account_id' => $sale->account_id,
                                'method' => "Efectivo_Ing",
                                'amount' => $pay->amount,
                                'sale' => true
                            );
                            $details[] = array(
                                'id' => $sale->id,
                                'reference' => $sale->reference_no,
                                'account_id' => $sale->account_id,
                                'method' => "Efectivo_Egr",
                                'amount' => 0,
                                'sale' => true
                            );
                        } else {
                            $details[] = array(
                                'id' => $sale->id,
                                'reference' => $sale->reference_no,
                                'account_id' => $sale->account_id,
                                'method' => "Efectivo_Ing",
                                'amount' => 0,
                                'sale' => true
                            );
                            $details[] = array(
                                'id' => $sale->id,
                                'reference' => $sale->reference_no,
                                'account_id' => $sale->account_id,
                                'method' => "Efectivo_Egr",
                                'amount' => 0,
                                'sale' => true
                            );
                        }
                        if ($pay->paying_method == "Cheque") {
                            $details[] = array(
                                'id' => $sale->id,
                                'reference' => $sale->reference_no,
                                'account_id' => $sale->account_id,
                                'method' => "Cheque",
                                'amount' => $pay->amount,
                                'sale' => true
                            );
                        } else {
                            $details[] = array(
                                'id' => $sale->id,
                                'reference' => $sale->reference_no,
                                'account_id' => $sale->account_id,
                                'method' => "Cheque",
                                'amount' => 0,
                                'sale' => true
                            );
                        }
                        if ($pay->paying_method == "Tarjeta_Credito_Debito") {
                            $details[] = array(
                                'id' => $sale->id,
                                'reference' => $sale->reference_no,
                                'account_id' => $sale->account_id,
                                'method' => "Tarjeta_Credito_Debito",
                                'amount' => $pay->amount,
                                'sale' => true
                            );
                        } else {
                            $details[] = array(
                                'id' => $sale->id,
                                'reference' => $sale->reference_no,
                                'account_id' => $sale->account_id,
                                'method' => "Tarjeta_Credito_Debito",
                                'amount' => 0,
                                'sale' => true
                            );
                        }
                        if ($pay->paying_method == "Deposito") {
                            $details[] = array(
                                'id' => $sale->id,
                                'reference' => $sale->reference_no,
                                'account_id' => $sale->account_id,
                                'method' => "Deposito",
                                'amount' => $pay->amount,
                                'sale' => true
                            );
                        } else {
                            $details[] = array(
                                'id' => $sale->id,
                                'reference' => $sale->reference_no,
                                'account_id' => $sale->account_id,
                                'method' => "Deposito",
                                'amount' => 0,
                                'sale' => true
                            );
                        }
                        if ($pay->paying_method == "Qr_simple") {
                            $details[] = array(
                                'id' => $sale->id,
                                'reference' => $sale->reference_no,
                                'account_id' => $sale->account_id,
                                'method' => "Qr_simple",
                                'amount' => $pay->amount,
                                'sale' => true
                            );
                        } else {
                            $details[] = array(
                                'id' => $sale->id,
                                'reference' => $sale->reference_no,
                                'account_id' => $sale->account_id,
                                'method' => "Qr_simple",
                                'amount' => 0,
                                'sale' => true
                            );
                        }
                        if ($pay->paying_method == "Tarjeta_Regalo") {
                            $details[] = array(
                                'id' => $sale->id,
                                'reference' => $sale->reference_no,
                                'account_id' => $sale->account_id,
                                'method' => "Tarjeta_Regalo",
                                'amount' => $pay->amount,
                                'sale' => true
                            );
                        } else {
                            $details[] = array(
                                'id' => $sale->id,
                                'reference' => $sale->reference_no,
                                'account_id' => $sale->account_id,
                                'method' => "Tarjeta_Regalo",
                                'amount' => 0,
                                'sale' => true
                            );
                        }
                        if ($pay->paying_method == "Otros") {
                            $details[] = array(
                                'id' => $sale->id,
                                'reference' => $sale->reference_no,
                                'account_id' => $sale->account_id,
                                'method' => "Otros",
                                'amount' => $pay->amount,
                                'sale' => true
                            );
                        } else {
                            $details[] = array(
                                'id' => $sale->id,
                                'reference' => $sale->reference_no,
                                'account_id' => $sale->account_id,
                                'method' => "Otros",
                                'amount' => 0,
                                'sale' => true
                            );
                        }
                        $accountfull[] = $details;
                    }
                }
                $lims_return_purchases_list = ReturnPurchase::select('id', 'reference_no', 'grand_total', 'account_id')
                    ->whereIn('account_id', $lims_account_list)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->get();
                foreach ($lims_return_purchases_list as $return) {
                    $details = [];
                    $details[] = array(
                        'id' => $return->id,
                        'reference' => $return->reference_no,
                        'account_id' => $return->account_id,
                        'method' => "Efectivo_Ing",
                        'amount' => $return->grand_total
                    );
                    $details[] = array(
                        'id' => $return->id,
                        'reference' => $return->reference_no,
                        'account_id' => $return->account_id,
                        'method' => "Efectivo_Egr",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $return->id,
                        'reference' => $return->reference_no,
                        'account_id' => $return->account_id,
                        'method' => "Cheque",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $return->id,
                        'reference' => $return->reference_no,
                        'account_id' => $return->account_id,
                        'method' => "Tarjeta_Credito_Debito",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $return->id,
                        'reference' => $return->reference_no,
                        'account_id' => $return->account_id,
                        'method' => "Deposito",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $return->id,
                        'reference' => $return->reference_no,
                        'account_id' => $return->account_id,
                        'method' => "Qr_simple",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $return->id,
                        'reference' => $return->reference_no,
                        'account_id' => $return->account_id,
                        'method' => "Tarjeta_Regalo",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $return->id,
                        'reference' => $return->reference_no,
                        'account_id' => $return->account_id,
                        'method' => "Otros",
                        'amount' => 0
                    );
                    $accountfull[] = $details;
                }
                $lims_recieved_money_via_transfers_list = MoneyTransfer::select('id', 'reference_no', 'amount', 'to_account_id')
                    ->whereIn('to_account_id', $lims_account_list)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->get();
                foreach ($lims_recieved_money_via_transfers_list as $recieved_money_via_transfer) {
                    $details = [];
                    $details[] = array(
                        'id' => $recieved_money_via_transfer->id,
                        'reference' => $recieved_money_via_transfer->reference_no,
                        'account_id' => $recieved_money_via_transfer->to_account_id,
                        'method' => "Efectivo_Ing",
                        'amount' => $recieved_money_via_transfer->amount
                    );
                    $details[] = array(
                        'id' => $recieved_money_via_transfer->id,
                        'reference' => $recieved_money_via_transfer->reference_no,
                        'account_id' => $recieved_money_via_transfer->to_account_id,
                        'method' => "Efectivo_Egr",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $recieved_money_via_transfer->id,
                        'reference' => $recieved_money_via_transfer->reference_no,
                        'account_id' => $recieved_money_via_transfer->to_account_id,
                        'method' => "Cheque",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $recieved_money_via_transfer->id,
                        'reference' => $recieved_money_via_transfer->reference_no,
                        'account_id' => $recieved_money_via_transfer->to_account_id,
                        'method' => "Tarjeta_Credito_Debito",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $recieved_money_via_transfer->id,
                        'reference' => $recieved_money_via_transfer->reference_no,
                        'account_id' => $recieved_money_via_transfer->to_account_id,
                        'method' => "Deposito",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $recieved_money_via_transfer->id,
                        'reference' => $recieved_money_via_transfer->reference_no,
                        'account_id' => $recieved_money_via_transfer->to_account_id,
                        'method' => "Qr_simple",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $recieved_money_via_transfer->id,
                        'reference' => $recieved_money_via_transfer->reference_no,
                        'account_id' => $recieved_money_via_transfer->to_account_id,
                        'method' => "Tarjeta_Regalo",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $recieved_money_via_transfer->id,
                        'reference' => $recieved_money_via_transfer->reference_no,
                        'account_id' => $recieved_money_via_transfer->to_account_id,
                        'method' => "Otros",
                        'amount' => 0
                    );
                    $accountfull[] = $details;
                }
                $lims_adjustment_account_list = AdjustmentAccount::select('id', 'reference_no', 'amount', 'account_id')
                    ->whereIn('account_id', $lims_account_list)->where([['is_active', true], ['type_adjustment', 'ING']])->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->get();
                foreach ($lims_adjustment_account_list as $adjustment_account) {
                    $details = [];
                    $details[] = array(
                        'id' => $adjustment_account->id,
                        'reference' => $adjustment_account->reference_no,
                        'account_id' => $adjustment_account->account_id,
                        'method' => "Efectivo_Ing",
                        'amount' => $adjustment_account->amount,
                        'ajustement' => true,
                    );
                    $details[] = array(
                        'id' => $adjustment_account->id,
                        'reference' => $adjustment_account->reference_no,
                        'account_id' => $adjustment_account->account_id,
                        'method' => "Efectivo_Egr",
                        'amount' => 0,
                        'ajustement' => true,
                    );
                    $details[] = array(
                        'id' => $adjustment_account->id,
                        'reference' => $adjustment_account->reference_no,
                        'account_id' => $adjustment_account->account_id,
                        'method' => "Cheque",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $adjustment_account->id,
                        'reference' => $adjustment_account->reference_no,
                        'account_id' => $adjustment_account->account_id,
                        'method' => "Tarjeta_Credito_Debito",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $adjustment_account->id,
                        'reference' => $adjustment_account->reference_no,
                        'account_id' => $adjustment_account->account_id,
                        'method' => "Deposito",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $adjustment_account->id,
                        'reference' => $adjustment_account->reference_no,
                        'account_id' => $adjustment_account->account_id,
                        'method' => "Qr_simple",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $adjustment_account->id,
                        'reference' => $adjustment_account->reference_no,
                        'account_id' => $adjustment_account->account_id,
                        'method' => "Tarjeta_Regalo",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $adjustment_account->id,
                        'reference' => $adjustment_account->reference_no,
                        'account_id' => $adjustment_account->account_id,
                        'method' => "Otros",
                        'amount' => 0
                    );
                    $accountfull[] = $details;
                }

                /* Ventas por Cobrar */
                $lims_sale_dues = Sale::orderBy('id', 'desc')->where([['sale_status', 4], ['payment_status', '!=', 4]])->whereBetween('date_sell', [$start_date, $end_date])->get();

                /* Egreso */

                $lims_purchases_list = Purchase::select('purchases.id', 'purchases.reference_no', 'purchases.grand_total', 'payments.paying_method', 'payments.account_id')
                    ->join('payments', 'purchases.id', '=', 'payments.purchase_id')->whereIn('payments.account_id', $lims_account_list)->whereDate('purchases.created_at', '>=', $start_date)->whereDate('purchases.created_at', '<=', $end_date)->get();

                foreach ($lims_purchases_list as $purchase) {
                    $details = [];
                    if ($purchase->paying_method == "Efectivo") {
                        $details[] = array(
                            'id' => $purchase->id,
                            'reference' => $purchase->reference_no,
                            'account_id' => $purchase->account_id,
                            'method' => "Efectivo_Ing",
                            'amount' => 0
                        );
                        $details[] = array(
                            'id' => $purchase->id,
                            'reference' => $purchase->reference_no,
                            'account_id' => $purchase->account_id,
                            'method' => "Efectivo_Egr",
                            'amount' => $purchase->grand_total
                        );
                    } else {
                        $details[] = array(
                            'id' => $purchase->id,
                            'reference' => $purchase->reference_no,
                            'account_id' => $purchase->account_id,
                            'method' => "Efectivo_Ing",
                            'amount' => 0
                        );
                        $details[] = array(
                            'id' => $purchase->id,
                            'reference' => $purchase->reference_no,
                            'account_id' => $purchase->account_id,
                            'method' => "Efectivo_Egr",
                            'amount' => 0
                        );
                    }
                    if ($purchase->paying_method == "Cheque") {
                        $details[] = array(
                            'id' => $purchase->id,
                            'reference' => $purchase->reference_no,
                            'account_id' => $purchase->account_id,
                            'method' => "Cheque",
                            'amount' => $purchase->grand_total
                        );
                    } else {
                        $details[] = array(
                            'id' => $purchase->id,
                            'reference' => $purchase->reference_no,
                            'account_id' => $purchase->account_id,
                            'method' => "Cheque",
                            'amount' => 0
                        );
                    }
                    if ($purchase->paying_method == "Tarjeta_Credito_Debito") {
                        $details[] = array(
                            'id' => $purchase->id,
                            'reference' => $purchase->reference_no,
                            'account_id' => $purchase->account_id,
                            'method' => "Tarjeta_Credito_Debito",
                            'amount' => $purchase->grand_total
                        );
                    } else {
                        $details[] = array(
                            'id' => $purchase->id,
                            'reference' => $purchase->reference_no,
                            'account_id' => $purchase->account_id,
                            'method' => "Tarjeta_Credito_Debito",
                            'amount' => 0
                        );
                    }
                    if ($purchase->paying_method == "Deposito") {
                        $details[] = array(
                            'id' => $purchase->id,
                            'reference' => $purchase->reference_no,
                            'account_id' => $purchase->account_id,
                            'method' => "Deposito",
                            'amount' => $purchase->grand_total
                        );
                    } else {
                        $details[] = array(
                            'id' => $purchase->id,
                            'reference' => $purchase->reference_no,
                            'account_id' => $purchase->account_id,
                            'method' => "Deposito",
                            'amount' => 0
                        );
                    }
                    if ($purchase->paying_method == "Qr_simple") {
                        $details[] = array(
                            'id' => $purchase->id,
                            'reference' => $purchase->reference_no,
                            'account_id' => $purchase->account_id,
                            'method' => "Qr_simple",
                            'amount' => $purchase->grand_total
                        );
                    } else {
                        $details[] = array(
                            'id' => $purchase->id,
                            'reference' => $purchase->reference_no,
                            'account_id' => $purchase->account_id,
                            'method' => "Qr_simple",
                            'amount' => 0
                        );
                    }
                    if ($purchase->paying_method == "Tarjeta_Regalo") {
                        $details[] = array(
                            'id' => $purchase->id,
                            'reference' => $purchase->reference_no,
                            'account_id' => $purchase->account_id,
                            'method' => "Tarjeta_Regalo",
                            'amount' => $purchase->grand_total
                        );
                    } else {
                        $details[] = array(
                            'id' => $purchase->id,
                            'reference' => $purchase->reference_no,
                            'account_id' => $purchase->account_id,
                            'method' => "Tarjeta_Regalo",
                            'amount' => 0
                        );
                    }
                    if ($purchase->paying_method == "Paypal") {
                        $details[] = array(
                            'id' => $purchase->id,
                            'reference' => $purchase->reference_no,
                            'account_id' => $purchase->account_id,
                            'method' => "Otros",
                            'amount' => $purchase->grand_total
                        );
                    } else {
                        $details[] = array(
                            'id' => $purchase->id,
                            'reference' => $purchase->reference_no,
                            'account_id' => $purchase->account_id,
                            'method' => "Otros",
                            'amount' => 0
                        );
                    }
                    $accountfull[] = $details;
                }
                $lims_returns_list = Returns::select('id', 'reference_no', 'grand_total', 'account_id')->whereIn('account_id', $lims_account_list)
                    ->where('biller_id', $biller->id)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->get();
                foreach ($lims_returns_list as $return) {
                    $details = [];
                    $details[] = array(
                        'id' => $return->id,
                        'reference' => $return->reference_no,
                        'account_id' => $return->account_id,
                        'method' => "Efectivo_Ing",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $return->id,
                        'reference' => $return->reference_no,
                        'account_id' => $return->account_id,
                        'method' => "Efectivo_Egr",
                        'amount' => $return->grand_total
                    );
                    $details[] = array(
                        'id' => $return->id,
                        'reference' => $return->reference_no,
                        'account_id' => $return->account_id,
                        'method' => "Cheque",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $return->id,
                        'reference' => $return->reference_no,
                        'account_id' => $return->account_id,
                        'method' => "Tarjeta_Credito_Debito",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $return->id,
                        'reference' => $return->reference_no,
                        'account_id' => $return->account_id,
                        'method' => "Deposito",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $return->id,
                        'reference' => $return->reference_no,
                        'account_id' => $return->account_id,
                        'method' => "Qr_simple",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $return->id,
                        'reference' => $return->reference_no,
                        'account_id' => $return->account_id,
                        'method' => "Tarjeta_Regalo",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $return->id,
                        'reference' => $return->reference_no,
                        'account_id' => $return->account_id,
                        'method' => "Otros",
                        'amount' => 0
                    );
                    $accountfull[] = $details;
                }

                $lims_send_money_via_transfers_list = MoneyTransfer::select('id', 'reference_no', 'amount', 'from_account_id')
                    ->whereIn('from_account_id', $lims_account_list)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->get();
                foreach ($lims_send_money_via_transfers_list as $send_money_via_transfer) {
                    $details = [];
                    $details[] = array(
                        'id' => $send_money_via_transfer->id,
                        'reference' => $send_money_via_transfer->reference_no,
                        'account_id' => $send_money_via_transfer->from_account_id,
                        'method' => "Efectivo_Ing",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $send_money_via_transfer->id,
                        'reference' => $send_money_via_transfer->reference_no,
                        'account_id' => $send_money_via_transfer->from_account_id,
                        'method' => "Efectivo_Egr",
                        'amount' => $send_money_via_transfer->amount
                    );
                    $details[] = array(
                        'id' => $send_money_via_transfer->id,
                        'reference' => $send_money_via_transfer->reference_no,
                        'account_id' => $send_money_via_transfer->from_account_id,
                        'method' => "Cheque",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $send_money_via_transfer->id,
                        'reference' => $send_money_via_transfer->reference_no,
                        'account_id' => $send_money_via_transfer->from_account_id,
                        'method' => "Tarjeta_Credito_Debito",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $send_money_via_transfer->id,
                        'reference' => $send_money_via_transfer->reference_no,
                        'account_id' => $send_money_via_transfer->from_account_id,
                        'method' => "Deposito",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $send_money_via_transfer->id,
                        'reference' => $send_money_via_transfer->reference_no,
                        'account_id' => $send_money_via_transfer->from_account_id,
                        'method' => "Qr_simple",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $send_money_via_transfer->id,
                        'reference' => $send_money_via_transfer->reference_no,
                        'account_id' => $send_money_via_transfer->from_account_id,
                        'method' => "Tarjeta_Regalo",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $send_money_via_transfer->id,
                        'reference' => $send_money_via_transfer->reference_no,
                        'account_id' => $send_money_via_transfer->from_account_id,
                        'method' => "Otros",
                        'amount' => 0
                    );
                    $accountfull[] = $details;
                }
                $lims_expenses_list = Expense::select('id', 'reference_no', 'amount', 'account_id')
                    ->whereIn('account_id', $lims_account_list)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->get();
                foreach ($lims_expenses_list as $expense) {
                    $details = [];
                    $details[] = array(
                        'id' => $expense->id,
                        'reference' => $expense->reference_no,
                        'account_id' => $expense->account_id,
                        'method' => "Efectivo_Ing",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $expense->id,
                        'reference' => $expense->reference_no,
                        'account_id' => $expense->account_id,
                        'method' => "Efectivo_Egr",
                        'amount' => $expense->amount
                    );
                    $details[] = array(
                        'id' => $expense->id,
                        'reference' => $expense->reference_no,
                        'account_id' => $expense->account_id,
                        'method' => "Cheque",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $expense->id,
                        'reference' => $expense->reference_no,
                        'account_id' => $expense->account_id,
                        'method' => "Tarjeta_Credito_Debito",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $expense->id,
                        'reference' => $expense->reference_no,
                        'account_id' => $expense->account_id,
                        'method' => "Deposito",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $expense->id,
                        'reference' => $expense->reference_no,
                        'account_id' => $expense->account_id,
                        'method' => "Qr_simple",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $expense->id,
                        'reference' => $expense->reference_no,
                        'account_id' => $expense->account_id,
                        'method' => "Tarjeta_Regalo",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $expense->id,
                        'reference' => $expense->reference_no,
                        'account_id' => $expense->account_id,
                        'method' => "Otros",
                        'amount' => 0
                    );
                    $accountfull[] = $details;
                }

                $lims_payrolls_list = Payroll::select('id', 'reference_no', 'amount', 'account_id')
                    ->whereIn('account_id', $lims_account_list)->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->get();
                foreach ($lims_payrolls_list as $payroll) {
                    $details = [];
                    $details[] = array(
                        'id' => $payroll->id,
                        'reference' => $payroll->reference_no,
                        'account_id' => $payroll->account_id,
                        'method' => "Efectivo_Ing",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $payroll->id,
                        'reference' => $payroll->reference_no,
                        'account_id' => $payroll->account_id,
                        'method' => "Efectivo_Egr",
                        'amount' => $payroll->amount
                    );
                    $details[] = array(
                        'id' => $payroll->id,
                        'reference' => $payroll->reference_no,
                        'account_id' => $payroll->account_id,
                        'method' => "Cheque",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $payroll->id,
                        'reference' => $payroll->reference_no,
                        'account_id' => $payroll->account_id,
                        'method' => "Tarjeta_Credito_Debito",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $payroll->id,
                        'reference' => $payroll->reference_no,
                        'account_id' => $payroll->account_id,
                        'method' => "Deposito",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $payroll->id,
                        'reference' => $payroll->reference_no,
                        'account_id' => $payroll->account_id,
                        'method' => "Qr_simple",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $payroll->id,
                        'reference' => $payroll->reference_no,
                        'account_id' => $payroll->account_id,
                        'method' => "Tarjeta_Regalo",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $payroll->id,
                        'reference' => $payroll->reference_no,
                        'account_id' => $payroll->account_id,
                        'method' => "Paypal",
                        'amount' => 0
                    );
                    $accountfull[] = $details;
                }

                $lims_adjustment_account_list = AdjustmentAccount::select('id', 'reference_no', 'amount', 'account_id')
                    ->whereIn('account_id', $lims_account_list)->where([['is_active', true], ['type_adjustment', 'EGR']])->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->get();
                foreach ($lims_adjustment_account_list as $adjustment_account) {
                    $details = [];
                    $details[] = array(
                        'id' => $adjustment_account->id,
                        'reference' => $adjustment_account->reference_no,
                        'account_id' => $adjustment_account->account_id,
                        'method' => "Efectivo_Ing",
                        'amount' => 0,
                        'ajustement' => true,
                    );
                    $details[] = array(
                        'id' => $adjustment_account->id,
                        'reference' => $adjustment_account->reference_no,
                        'account_id' => $adjustment_account->account_id,
                        'method' => "Efectivo_Egr",
                        'amount' => $adjustment_account->amount,
                        'ajustement' => true,
                    );
                    $details[] = array(
                        'id' => $adjustment_account->id,
                        'reference' => $adjustment_account->reference_no,
                        'account_id' => $adjustment_account->account_id,
                        'method' => "Cheque",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $adjustment_account->id,
                        'reference' => $adjustment_account->reference_no,
                        'account_id' => $adjustment_account->account_id,
                        'method' => "Tarjeta_Credito_Debito",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $adjustment_account->id,
                        'reference' => $adjustment_account->reference_no,
                        'account_id' => $adjustment_account->account_id,
                        'method' => "Deposito",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $adjustment_account->id,
                        'reference' => $adjustment_account->reference_no,
                        'account_id' => $adjustment_account->account_id,
                        'method' => "Qr_simple",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $adjustment_account->id,
                        'reference' => $adjustment_account->reference_no,
                        'account_id' => $adjustment_account->account_id,
                        'method' => "Tarjeta_Regalo",
                        'amount' => 0
                    );
                    $details[] = array(
                        'id' => $adjustment_account->id,
                        'reference' => $adjustment_account->reference_no,
                        'account_id' => $adjustment_account->account_id,
                        'method' => "Otros",
                        'amount' => 0
                    );
                    $accountfull[] = $details;
                }

                /**** Totales Egresos - Ingresos - Saldo Ant */
                $saldoant = 0;
                $credit = 0;
                $debit = 0;

                $payment_recieved = Payment::whereNotNull('sale_id')->whereIn('account_id', $lims_account_list)
                    ->whereDate('created_at', '>=', $startbef_date)->whereDate('created_at', '<=', $endafter_date)->sum('amount');
                $payment_sent = Payment::whereNotNull('purchase_id')->whereIn('account_id', $lims_account_list)
                    ->whereDate('created_at', '>=', $startbef_date)->whereDate('created_at', '<=', $endafter_date)->sum('amount');
                $returns = Returns::whereIn('account_id', $lims_account_list)->where('biller_id', $biller->id)
                    ->whereDate('created_at', '>=', $startbef_date)->whereDate('created_at', '<=', $endafter_date)->sum('grand_total');
                $return_purchase = DB::table('return_purchases')->whereIn('account_id', $lims_account_list)
                    ->whereDate('created_at', '>=', $startbef_date)->whereDate('created_at', '<=', $endafter_date)->sum('grand_total');
                $expenses = DB::table('expenses')->whereIn('account_id', $lims_account_list)
                    ->whereDate('created_at', '>=', $startbef_date)->whereDate('created_at', '<=', $endafter_date)->sum('amount');
                $payrolls = DB::table('payrolls')->whereIn('account_id', $lims_account_list)
                    ->whereDate('created_at', '>=', $startbef_date)->whereDate('created_at', '<=', $endafter_date)->sum('amount');
                $sent_money_via_transfer = MoneyTransfer::whereIn('from_account_id', $lims_account_list)
                    ->whereDate('created_at', '>=', $startbef_date)->whereDate('created_at', '<=', $endafter_date)->sum('amount');
                $recieved_money_via_transfer = MoneyTransfer::whereIn('to_account_id', $lims_account_list)
                    ->whereDate('created_at', '>=', $startbef_date)->whereDate('created_at', '<=', $endafter_date)->sum('amount');
                $adjustment_account_ing = AdjustmentAccount::whereIn('account_id', $lims_account_list)->where([['is_active', true], ['type_adjustment', 'ING']])
                    ->whereDate('created_at', '>=', $startbef_date)->whereDate('created_at', '<=', $endafter_date)->sum('amount');
                $adjustment_account_egr = AdjustmentAccount::whereIn('account_id', $lims_account_list)->where([['is_active', true], ['type_adjustment', 'EGR']])
                    ->whereDate('created_at', '>=', $startbef_date)->whereDate('created_at', '<=', $endafter_date)->sum('amount');

                $credit = $payment_recieved + $return_purchase + $recieved_money_via_transfer + $adjustment_account_ing + $lims_account_data->initial_balance;
                $debit = $payment_sent + $returns + $expenses + $payrolls + $sent_money_via_transfer + $adjustment_account_egr;
                $saldoant = $credit - $debit;

                /*** Totales Egresos Actual */

                $compras = Payment::whereNotNull('purchase_id')->whereIn('account_id', $lims_account_list)
                    ->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('amount');
                $devolucion = Returns::whereIn('account_id', $lims_account_list)->where('biller_id', $biller->id)->whereDate('created_at', '>=', $start_date)
                    ->whereDate('created_at', '<=', $end_date)->sum('grand_total');
                $gastos = DB::table('expenses')->whereIn('account_id', $lims_account_list)
                    ->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('amount');
                $nominas = DB::table('payrolls')->whereIn('account_id', $lims_account_list)
                    ->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('amount');
                $transferencias = MoneyTransfer::whereIn('from_account_id', $lims_account_list)
                    ->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('amount');
                $ajustegr = AdjustmentAccount::whereIn('account_id', $lims_account_list)->where([['is_active', true], ['type_adjustment', 'EGR']])
                    ->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('amount');



                $lims_cashier_data = Cashier::where('account_id', $biller->account_id)->where([['is_active', true]])->first();
                if ($lims_cashier_data)
                    $cashier_open = true;
                else
                    $cashier_open = false;
            } else {
                $gastos = 0;
                $compras = 0;
                $nominas = 0;
                $devolucion = 0;
                $transferencias = 0;
                $saldoant = 0;
                $ajustegr = 0;
                $accountfull = [];
            }
            $end_date = $end_date_temp;
            return view('account.balance_sheet_account', compact('start_date', 'biller', 'lims_biller_list', 'accountfull', 'saldoant', 'compras', 'nominas', 'transferencias', 'gastos', 'devolucion', 'ajustegr', 'cashier_open', 'lims_sale_dues'));
        } else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }


    public function accountStatement(Request $request)
    {
        $data = $request->all();
        $lims_account_data = Account::find($data['account_id']);
        $credit_list = [];
        $debit_list = [];
        $expense_list = [];
        $return_list = [];
        $purchase_return_list = [];
        $payroll_list = [];
        $recieved_money_transfer_list = [];
        $sent_money_transfer_list = [];

        if ($data['type'] == '0' || $data['type'] == '2') {
            $credit_list = Payment::whereNotNull('sale_id')->where('account_id', $data['account_id'])->whereDate('created_at', '>=', $data['start_date'])->whereDate('created_at', '<=', $data['end_date'])->get();

            $recieved_money_transfer_list = MoneyTransfer::where('to_account_id', $data['account_id'])->get();
        }
        if ($data['type'] == '0' || $data['type'] == '1') {
            $debit_list = Payment::whereNotNull('purchase_id')->where('account_id', $data['account_id'])->whereDate('created_at', '>=', $data['start_date'])->whereDate('created_at', '<=', $data['end_date'])->get();

            $expense_list = Expense::where('account_id', $data['account_id'])->whereDate('created_at', '>=', $data['start_date'])->whereDate('created_at', '<=', $data['end_date'])->get();

            $return_list = Returns::where('account_id', $data['account_id'])->whereDate('created_at', '>=', $data['start_date'])->whereDate('created_at', '<=', $data['end_date'])->get();

            $purchase_return_list = ReturnPurchase::where('account_id', $data['account_id'])->whereDate('created_at', '>=', $data['start_date'])->whereDate('created_at', '<=', $data['end_date'])->get();

            $payroll_list = Payroll::where('account_id', $data['account_id'])->whereDate('created_at', '>=', $data['start_date'])->whereDate('created_at', '<=', $data['end_date'])->get();

            $sent_money_transfer_list = MoneyTransfer::where('from_account_id', $data['account_id'])->get();
        }
        $balance = 0;
        return view('account.account_statement', compact('lims_account_data', 'credit_list', 'debit_list', 'expense_list', 'return_list', 'purchase_return_list', 'payroll_list', 'recieved_money_transfer_list', 'sent_money_transfer_list', 'balance'));
    }

    public function destroy($id)
    {
        $lims_account_data = Account::find($id);
        if (!$lims_account_data->is_default) {
            $lims_account_data->is_active = false;
            $lims_account_data->save();
            $lims_accountpay_list = AccountPayment::where([['is_active', true], ['account_id', $id]])->get();
            foreach ($lims_accountpay_list as $ant) {
                $data_ant = AccountPayment::where([['account_id', $id], ['methodpay_id', $ant->methodpay_id]])->first();
                $data_ant->is_active = false;
                $data_ant->save();
                $data_method = MethodPayment::find($data_ant->methodpay_id);
                $data_method->used = false;
                $data_method->save();
            }
            return redirect('accounts')->with('not_permitted', 'Cuenta eliminado exitosamente!');
        } else
            return redirect('accounts')->with('not_permitted', 'Por favor seleccione otra cuenta por defecto primero!');
    }

    public function listaccounts()
    {
        return $lims_accounts_list = Account::select('id', 'name', 'account_no')->get();
        ;

    }

    public function total_caja($id)
    {
        $account_id = $id;
        $totalefectivo = 0;
        $lims_cashier_data = Cashier::select('start_date')->where([['account_id', $account_id], ['is_active', true], ['end_date', null]])->first();
        $lims_account_data = Account::select('id', 'name', 'account_no', 'initial_balance')->find($account_id);
        if ($lims_cashier_data != null) {
            $start_date = $lims_cashier_data->start_date;
            $end_date = date('Y-m-d H:i:s');
            $startbef_date = date("Y-m-d", strtotime($start_date . "- 5 year"));
            $endafter_date = date("Y-m-d", strtotime($start_date . "- 1 days"));

            $ventas = Payment::whereNotNull('sale_id')->where([['account_id', $account_id], ['paying_method', 'Efectivo']])
                ->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('amount');
            $compra_retornada = DB::table('return_purchases')->where('account_id', $account_id)
                ->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('grand_total');
            $tranferencia_recibida = MoneyTransfer::where('to_account_id', $account_id)
                ->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('amount');
            $ajuste_ingreso = AdjustmentAccount::where([['account_id', $account_id], ['is_active', true], ['type_adjustment', 'ING']])
                ->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('amount');

            $compras = Payment::whereNotNull('purchase_id')->where('account_id', $account_id)
                ->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('amount');
            $devolucion = Returns::where('account_id', $account_id)->whereDate('created_at', '>=', $start_date)
                ->whereDate('created_at', '<=', $end_date)->sum('grand_total');
            $gastos = DB::table('expenses')->where('account_id', $account_id)
                ->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('amount');
            $nominas = DB::table('payrolls')->where('account_id', $account_id)
                ->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('amount');
            $transferencias = MoneyTransfer::where('from_account_id', $account_id)
                ->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('amount');
            $ajustegr = AdjustmentAccount::where([['account_id', $account_id], ['is_active', true], ['type_adjustment', 'EGR']])
                ->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date)->sum('amount');


            /**** Totales Egresos - Ingresos - Saldo Ant */
            $saldoant = 0;
            $credit = 0;
            $debit = 0;

            $payment_recieved = Payment::whereNotNull('sale_id')->where('account_id', $account_id)
                ->whereDate('created_at', '>=', $startbef_date)->whereDate('created_at', '<=', $endafter_date)->sum('amount');
            $payment_sent = Payment::whereNotNull('purchase_id')->where('account_id', $account_id)
                ->whereDate('created_at', '>=', $startbef_date)->whereDate('created_at', '<=', $endafter_date)->sum('amount');
            $returns = Returns::where('account_id', $account_id)->whereDate('created_at', '>=', $startbef_date)
                ->whereDate('created_at', '<=', $endafter_date)->sum('grand_total');
            $return_purchase = DB::table('return_purchases')->where('account_id', $account_id)
                ->whereDate('created_at', '>=', $startbef_date)->whereDate('created_at', '<=', $endafter_date)->sum('grand_total');
            $expenses = DB::table('expenses')->where('account_id', $account_id)
                ->whereDate('created_at', '>=', $startbef_date)->whereDate('created_at', '<=', $endafter_date)->sum('amount');
            $payrolls = DB::table('payrolls')->where('account_id', $account_id)
                ->whereDate('created_at', '>=', $startbef_date)->whereDate('created_at', '<=', $endafter_date)->sum('amount');
            $sent_money_via_transfer = MoneyTransfer::where('from_account_id', $account_id)
                ->whereDate('created_at', '>=', $startbef_date)->whereDate('created_at', '<=', $endafter_date)->sum('amount');
            $recieved_money_via_transfer = MoneyTransfer::where('to_account_id', $account_id)
                ->whereDate('created_at', '>=', $startbef_date)->whereDate('created_at', '<=', $endafter_date)->sum('amount');
            $adjustment_account_ing = AdjustmentAccount::where([['account_id', $account_id], ['is_active', true], ['type_adjustment', 'ING']])
                ->whereDate('created_at', '>=', $startbef_date)->whereDate('created_at', '<=', $endafter_date)->sum('amount');
            $adjustment_account_egr = AdjustmentAccount::where([['account_id', $account_id], ['is_active', true], ['type_adjustment', 'EGR']])
                ->whereDate('created_at', '>=', $startbef_date)->whereDate('created_at', '<=', $endafter_date)->sum('amount');

            $credit = $payment_recieved + $return_purchase + $recieved_money_via_transfer + $adjustment_account_ing + $lims_account_data->initial_balance;
            $debit = $payment_sent + $returns + $expenses + $payrolls + $sent_money_via_transfer + $adjustment_account_egr;
            $saldoant = $credit - $debit;

            /**** Totales Egresos + (Ingresos + SaldoAnt)*/

            $totaling = $ventas + $compra_retornada + $tranferencia_recibida + $ajuste_ingreso;
            $totalegr = $compras + $devolucion + $gastos + $nominas + $transferencias + $ajustegr;
            $totalefectivo = $saldoant + $totaling - $totalegr;
            return $result = array('start_date' => date('d-m-Y H:i:s', strtotime($start_date)), 'totalbalance' => $totalefectivo, 'account' => $lims_account_data);
        } else {
            return $result = array('date' => date('d-m-Y H:i:s'), 'message' => 'Cajero no encontrado');
        }
    }
}