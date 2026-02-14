<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use DB;
use Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Sale;
use App\MethodPayment;
use App\Customer;
use App\Payment;
use App\GiftCard;
use App\PaymentWithGiftCard;
use App\PaymentWithCreditCard;
use App\PaymentWithCheque;
use App\Biller;
use App\Account;
use App\Receivable;

use App\Mail\UserNotification;
use Illuminate\Support\Facades\Mail;
use GeniusTS\HijriDate\Date;
use Illuminate\Support\Facades\Validator;

class ReceivableController extends Controller
{

    public function index(Request $request)
    {
        $start_date = date('Y-m-d', strtotime(' -7 day'));
        $end_date = date('Y-m-d');
        $end_date_temp = $end_date;
        $end_date = $end_date . " 23:59:59";

        if (!is_null($request->start_date) && !empty($request->start_date) && !is_null($request->end_date) || !empty($request->end_date)) {
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $end_date_temp = $end_date;
            $end_date = $end_date . " 23:59:59";
        }

        if (Auth::user()->role_id > 2)
            $lims_sale_all = Sale::orderBy('id', 'desc')->where([['user_id', Auth::id()], ['sale_status', 4], ['payment_status', '!=', 4]])->whereBetween('date_sell', [$start_date, $end_date])->get();
        else
            $lims_sale_all = Sale::orderBy('id', 'desc')->where([['sale_status', 4], ['payment_status', '!=', 4]])->whereBetween('date_sell', [$start_date, $end_date])->get();

        $lims_methodpay_list = MethodPayment::select('id', 'name')->where('cbx', true)->get();
        $payment = MethodPayment::select('id', 'name')->find(1);
        foreach ($lims_methodpay_list as $key => $method) {
            if ($method->name == 'Vales' || $method->name == 'Tarjeta Regalo') {
                unset($lims_methodpay_list[$key]);
            }
        }
        $payment_id = $payment->id;
        $end_date = $end_date_temp;
        return view('receivable.index', compact('lims_sale_all', 'start_date', 'end_date', 'lims_methodpay_list', 'payment_id'));
    }

    public function processing_payment(Request $request)
    {
        $data = $request->all();
        $sales = $data['saleIdArray'];
        $amounts = $data['salePayArray'];
        $user = Auth::user();
        $payment_note = "Pago procesado con cuentas por cobrar";
        $cont = 0;
        $sales_id = [];
        $totalpayment = 0;
        if ($user->biller_id != null) {
            $biller_data = Biller::find($user->biller_id);
            $lims_account_data = Account::select('id')->find($biller_data->account_id_receivable);
        } else {
            $lims_account_data = Account::select('id')->where('is_default', true)->first();
        }
        $account_id = $lims_account_data->id;

        foreach ($sales as $key => $sale) {
            $lims_sale_data = Sale::find($sale);
            $lims_customer_data = Customer::find($lims_sale_data->customer_id);
            $lims_sale_data->paid_amount += $amounts[$key];
            $balance = $lims_sale_data->grand_total - $lims_sale_data->paid_amount;
            if ($balance > 0 || $balance < 0) {
                $lims_sale_data->payment_status = 3;
            } elseif ($balance == 0) {
                $lims_sale_data->payment_status = 4;
                $lims_sale_data->sale_status = 1;
            }
            $lims_sale_data->save();

            if ($data['methodpay'] == 1)
                $paying_method = 'Efectivo';
            elseif ($data['methodpay'] == 3) {
                $paying_method = 'Tarjeta_Regalo';
            } elseif ($data['methodpay'] == 4)
                $paying_method = 'Tarjeta_Credito_Debito';
            elseif ($data['methodpay'] == 5)
                $paying_method = 'Cheque';
            elseif ($data['methodpay'] == 8)
                $paying_method = 'Paypal';
            elseif ($data['methodpay'] == 6)
                $paying_method = 'Qr_Simple';
            else
                $paying_method = 'Deposito';

            $lims_payment_data = new Payment();
            $lims_payment_data->user_id = Auth::id();
            $lims_payment_data->sale_id = $lims_sale_data->id;
            $lims_payment_data->account_id = $account_id;
            $data['payment_reference'] = 'cpc-' . date("Ymd") . '-' . date("his");
            $lims_payment_data->payment_reference = $data['payment_reference'];
            $lims_payment_data->amount = $amounts[$key];
            $lims_payment_data->change = 0;
            $lims_payment_data->paying_method = $paying_method;
            $lims_payment_data->payment_note = $payment_note;
            $lims_payment_data->save();

            $lims_payment_data = Payment::latest()->first();
            $data['payment_id'] = $lims_payment_data->id;
            $totalpayment = $totalpayment + $lims_payment_data->amount;
            $sales_id[] = $lims_sale_data->id;

            if ($paying_method == 'Tarjeta_Regalo') {
                $lims_gift_card_data = GiftCard::find($data['gift_card_id']);
                $lims_gift_card_data->expense += $amounts[$key];
                $lims_gift_card_data->save();
                PaymentWithGiftCard::create($data);
            } elseif ($paying_method == 'Tarjeta_Credito_Debito') {
                $amount = $lims_sale_data->grand_total;

                $lims_payment_with_credit_card_data = PaymentWithCreditCard::where('customer_id', $lims_sale_data->customer_id)->first();

                if (!$lims_payment_with_credit_card_data) {
                    $data['customer_stripe_id'] = "POSEXT-" . uniqid();
                } else {
                    $customer_id = $lims_payment_with_credit_card_data->customer_stripe_id;
                    $data['customer_stripe_id'] = $customer_id;
                }
                $data['customer_id'] = $lims_sale_data->customer_id;
                $data['charge_id'] = uniqid();
                PaymentWithCreditCard::create($data);
            } elseif ($paying_method == 'Cheque') {
                PaymentWithCheque::create($data);
            } elseif ($paying_method == 'Paypal') {
                //$provider = new ExpressCheckout;
                $paypal_data['items'] = [];
                $paypal_data['items'][] = [
                    'name' => 'Paid Amount',
                    'price' => $amounts[$key],
                    'qty' => 1
                ];
                $paypal_data['invoice_id'] = $lims_payment_data->payment_reference;
                $paypal_data['invoice_description'] = "Reference: {$paypal_data['invoice_id']}";
                $paypal_data['return_url'] = url('/sale/paypalPaymentSuccess/' . $lims_payment_data->id);
                $paypal_data['cancel_url'] = url('/sale');

                $total = 0;
                foreach ($paypal_data['items'] as $item) {
                    $total += $item['price'] * $item['qty'];
                }

                $paypal_data['total'] = $total;
                //$response = $provider->setExpressCheckout($paypal_data);
                $response['paypal_link'] = "#";
                return redirect($response['paypal_link']);
            } elseif ($paying_method == 'Deposito') {
                $lims_customer_data->expense += $amounts[$key];
                $lims_customer_data->save();
            }
            $message = 'Pagos procesados con éxito';
            if ($lims_customer_data->email) {
                $mail_data['email'] = $lims_customer_data->email;
                $mail_data['sale_reference'] = $lims_sale_data->reference_no;
                $mail_data['payment_reference'] = $lims_payment_data->payment_reference;
                $mail_data['payment_method'] = $lims_payment_data->paying_method;
                $mail_data['grand_total'] = $lims_sale_data->grand_total;
                $mail_data['paid_amount'] = $amounts[$key];
                try {
                    Mail::send('mail.payment_details', $mail_data, function ($message) use ($mail_data) {
                        $message->to($mail_data['email'])->subject('Payment Details');
                    });
                } catch (\Exception $e) {
                    $message = 'Pagos procesados con éxito. Por favor configure en Ajustes de E-mail para enviar correo electronico.';
                }

            }
            $cont = $cont + 1;
        }

        //** Register Receivable */
        $datapro['user_id'] = Auth::id();
        $datapro['account_id'] = $account_id;
        $datapro['status'] = 1;
        $datapro['sales'] = implode(",", $sales_id);
        $datapro['amount'] = $totalpayment;
        Receivable::create($datapro);
        $lims_receivable_data = Receivable::select('id')->latest()->first();
        //$report = $this->report($lims_receivable_data->id);

        //** Return Data Operation Results */
        $total = sizeof($sales);
        $result = array(
            'message' => $message,
            'totalreq' => $total,
            'totalprocess' => $cont,
            'report_id' => $lims_receivable_data->id,
            'status' => true
        );
        return json_encode($result);
    }

    public function report($id)
    {
        $lims_receivable_data = Receivable::find($id);
        $sales = explode(",", $lims_receivable_data->sales);
        $data = [];
        foreach ($sales as $key => $sale) {
            $sale_data = Sale::select(
                'sales.date_sell',
                'sales.reference_no',
                'billers.name',
                'customers.name As customer',
                'sales.sale_status',
                'sales.payment_status',
                'sales.grand_total',
                'sales.paid_amount',
                'sales.paid_amount As balance'
            )
                ->join('billers', 'sales.biller_id', '=', 'billers.id')
                ->join('customers', 'sales.customer_id', '=', 'customers.id')->find($sale);
            $sale_data->balance = $sale_data->grand_total - $sale_data->paid_amount;
            $data[] = $sale_data;
        }

        //return $data;
        return view('receivable.invoice', compact('data', 'lims_receivable_data'));

    }

    public function dueCustomer($id)
    {
        $query1 = array(
            "grand_total",
            "paid_amount"
        );
        $totaldue = 0;
        $lims_sale_all = Sale::where([['customer_id', $id], ['sale_status', 4], ['payment_status', '!=', 4]])->selectRaw(implode(',', $query1))->get();
        foreach ($lims_sale_all as $sale) {
            if ($sale->paid_amount != null)
                $totaldue = $sale->grand_total - $sale->paid_amount;
            else
                $totaldue += $sale->grand_total;
        }
        return $totaldue;
    }

    public function payDueCustomer(Request $request)
    {
        $data = $request->all();
        $customer_id = $data['customer_id'];
        $amountotal = $data['amount_pay'];
        $methodpay = $data['payment_method'];
        $lims_customer_data = Customer::find($customer_id);
        $user = Auth::user();
        $payment_note = "Pago procesado con cuentas por cobrar del cliente: " . $lims_customer_data->name;
        $cont = 0;
        $sales_id = [];
        $totalpayment = 0;
        if ($user->biller_id != null) {
            $biller_data = Biller::find($user->biller_id);
            $lims_account_data = Account::select('id')->find($biller_data->account_id_receivable);
        } else {
            $lims_account_data = Account::select('id')->where('is_default', true)->first();
        }
        $account_id = $lims_account_data->id;
        $lims_sale_all = Sale::where([['customer_id', $lims_customer_data->id], ['sale_status', 4], ['payment_status', '!=', 4]])->orderBy('id', 'ASC')->get();

        foreach ($lims_sale_all as $key => $sale) {
            $amount = $amountotal - $sale->grand_total;
            $paid = 0;
            if ($amountotal > $sale->grand_total) {
                $sale->paid_amount += $sale->grand_total;
                $paid = $sale->grand_total;
                $amountotal = $amount;
            } else {
                if ($amountotal > 0) {
                    $sale->paid_amount += $amountotal;
                    $paid = $amountotal;
                    $amountotal = $amount;
                } else
                    break;
            }
            $balance = $sale->grand_total - $sale->paid_amount;
            if ($balance > 0 || $balance < 0) {
                $sale->payment_status = 3;
            } elseif ($balance == 0) {
                $sale->payment_status = 4;
                $sale->sale_status = 1;
            }
            $sale->save();

            if ($methodpay == 1)
                $paying_method = 'Efectivo';
            elseif ($methodpay == 3) {
                $paying_method = 'Tarjeta_Regalo';
            } elseif ($methodpay == 4)
                $paying_method = 'Tarjeta_Credito_Debito';
            elseif ($methodpay == 5)
                $paying_method = 'Cheque';
            elseif ($methodpay == 8)
                $paying_method = 'Paypal';
            elseif ($methodpay == 6)
                $paying_method = 'Qr_Simple';
            else
                $paying_method = 'Deposito';

            $lims_payment_data = new Payment();
            $lims_payment_data->user_id = Auth::id();
            $lims_payment_data->sale_id = $sale->id;
            $lims_payment_data->account_id = $account_id;
            $data['payment_reference'] = 'cpc-' . date("Ymd") . '-' . date("his");
            $lims_payment_data->payment_reference = $data['payment_reference'];
            $lims_payment_data->amount = $paid;
            $lims_payment_data->change = 0;
            $lims_payment_data->paying_method = $paying_method;
            $lims_payment_data->payment_note = $payment_note;
            $lims_payment_data->save();

            $lims_payment_data = Payment::latest()->first();
            $data['payment_id'] = $lims_payment_data->id;
            $totalpayment = $totalpayment + $lims_payment_data->amount;
            $sales_id[] = $sale->id;

            if ($paying_method == 'Tarjeta_Regalo') {
                $lims_gift_card_data = GiftCard::find($data['gift_card_id']);
                $lims_gift_card_data->expense += $paid;
                $lims_gift_card_data->save();
                PaymentWithGiftCard::create($data);
            } elseif ($paying_method == 'Tarjeta_Credito_Debito') {

                $lims_payment_with_credit_card_data = PaymentWithCreditCard::where('customer_id', $lims_customer_data->id)->first();

                if (!$lims_payment_with_credit_card_data) {
                    $data['customer_stripe_id'] = "POSEXT-" . uniqid();
                } else {
                    $customer_id = $lims_payment_with_credit_card_data->customer_stripe_id;
                    $data['customer_stripe_id'] = $customer_id;
                }
                $data['customer_id'] = $sale->customer_id;
                $data['charge_id'] = uniqid();
                PaymentWithCreditCard::create($data);
            } elseif ($paying_method == 'Cheque') {
                PaymentWithCheque::create($data);
            } elseif ($paying_method == 'Paypal') {
                //$provider = new ExpressCheckout;
                $paypal_data['items'] = [];
                $paypal_data['items'][] = [
                    'name' => 'Paid Amount',
                    'price' => $paid,
                    'qty' => 1
                ];
                $paypal_data['invoice_id'] = $lims_payment_data->payment_reference;
                $paypal_data['invoice_description'] = "Reference: {$paypal_data['invoice_id']}";
                $paypal_data['return_url'] = url('/sale/paypalPaymentSuccess/' . $lims_payment_data->id);
                $paypal_data['cancel_url'] = url('/sale');

                $total = 0;
                foreach ($paypal_data['items'] as $item) {
                    $total += $item['price'] * $item['qty'];
                }

                $paypal_data['total'] = $total;
                //$response = $provider->setExpressCheckout($paypal_data);
                $response['paypal_link'] = "#";
                return redirect($response['paypal_link']);
            } elseif ($paying_method == 'Deposito') {
                $lims_customer_data->expense += $paid;
                $lims_customer_data->save();
            }
            $message = 'Pagos procesados con éxito';
            if ($lims_customer_data->email) {
                $mail_data['email'] = $lims_customer_data->email;
                $mail_data['sale_reference'] = $sale->reference_no;
                $mail_data['payment_reference'] = $lims_payment_data->payment_reference;
                $mail_data['payment_method'] = $lims_payment_data->paying_method;
                $mail_data['grand_total'] = $sale->grand_total;
                $mail_data['paid_amount'] = $paid;
                try {
                    Mail::send('mail.payment_details', $mail_data, function ($message) use ($mail_data) {
                        $message->to($mail_data['email'])->subject('Payment Details');
                    });
                } catch (\Exception $e) {
                    $message = 'Pagos procesados con éxito. Por favor configure en Ajustes de E-mail para enviar correo electronico.';
                }

            }
            $cont = $cont + 1;
        }

        //** Register Receivable */
        $datapro['user_id'] = Auth::id();
        $datapro['account_id'] = $account_id;
        $datapro['status'] = 1;
        $datapro['sales'] = implode(",", $sales_id);
        $datapro['amount'] = $totalpayment;
        Receivable::create($datapro);
        $lims_receivable_data = Receivable::select('id')->latest()->first();
        //$report = $this->report($lims_receivable_data->id);

        //** Return Data Operation Results */
        $total = sizeof($lims_sale_all);
        $result = array(
            'message' => $message,
            'totalreq' => $total,
            'totalprocess' => $cont,
            'report_id' => $lims_receivable_data->id,
            'status' => true
        );
        return json_encode($result);
    }
}