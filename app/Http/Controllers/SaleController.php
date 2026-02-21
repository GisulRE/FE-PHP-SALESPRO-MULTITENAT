<?php

namespace App\Http\Controllers;

use App\Account;
use App\AccountPayment;
use App\Biller;
use App\Biller_Warehouses;
use App\Brand;
use App\Cashier;
use App\Category;
use App\ControlContingencia;
use App\Coupon;
use App\CredencialCafc;
use App\Customer;
use App\CustomerGroup;
use App\CustomerNit;
use App\CustomerSale;
use App\Delivery;
use App\Employee;
use App\GeneralSetting;
use App\GiftCard;
use App\Http\Traits\SiatTrait;
use App\Http\Traits\CufdTrait;
use App\LoteSale;
use App\MethodPayment;
use App\Payment;
use App\PaymentWithCheque;
use App\PaymentWithCreditCard;
use App\PaymentWithGiftCard;
use App\PaymentWithPaypal;
use App\PosSetting;
use App\PreSale;
use App\PrinterConfig;
use App\Product;
use App\Product_Sale;
use App\Product_Warehouse;
use App\ProductAssociated;
use App\ProductLote;
use App\ProductPurchase;
use App\ProductVariant;
use App\Sale;
use App\SiatCufd;
use App\SiatParametricaVario;
use App\SiatPuntoVenta;
use App\SiatSucursal;
use App\Tax;
use App\Tip;
use App\Unit;
use App\User;
use App\UserCategory;
use App\Variant;
use App\Warehouse;
use Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use DB;
use Error;
use Exception;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB as FacadesDB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use NumberToWords\NumberToWords;
use Spatie\Permission\Models\Role;
use Stripe\Stripe;

use App\Helpers\WhatsAppHelper;

class SaleController extends Controller
{
    use SiatTrait, CufdTrait;

    public function index()
    {
        $start_date = date('Y-m-d', strtotime(' -7 day'));
        $end_date = date('Y-m-d');
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('sales-index')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission) {
                $all_permission[] = $permission->name;
            }

            if (empty($all_permission)) {
                $all_permission[] = 'dummy text';
            }

            $lims_gift_card_list = GiftCard::where("is_active", true)->get();
            $lims_pos_setting_data = PosSetting::latest()->first();
            $lims_account_list = Account::where('is_active', true)->get();
            $lims_methodpay_list = MethodPayment::where('name', '!=', 'Guardar Mas Tarde')->get();
            $lista_documentos = SiatParametricaVario::where('tipo_clasificador', 'tipoDocumentoIdentidad')->get();

            return view('sale.index', compact('lims_gift_card_list', 'lims_pos_setting_data', 'lims_account_list', 'all_permission', 'start_date', 'end_date', 'lims_methodpay_list', 'lista_documentos'));
        } else {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }
    }

    public function saleData(Request $request)
    {
        $start_date = date('Y-m-d', strtotime(' -7 day'));
        $end_date = date('Y-m-d');
        $end_date_temp = $end_date;
        $end_date = $end_date . " 23:59:59";
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
            7 => 'grand_total',
            8 => 'paid_amount',
        );

        if (!is_null($request->start_date) && !empty($request->start_date) && !is_null($request->end_date) || !empty($request->end_date)) {
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $end_date_temp = $end_date;
            $end_date = $end_date . " 23:59:59";
        }

        if (Auth::user()->role_id > 2) {
            $totalData = Sale::where('user_id', Auth::id())
                ->whereDate('date_sell', ">=", $start_date)
                ->whereDate('date_sell', "<=", $end_date)
                ->count();
        } else {
            $totalData = Sale::whereDate('date_sell', ">=", $start_date)
                ->whereDate('date_sell', "<=", $end_date)->count();
        }

        $totalFiltered = $totalData;
        if ($request->input('length') != -1) {
            $limit = $request->input('length');
        } else {
            $limit = $totalData;
        }

        $start = $request->input('start');
        $order = 'sales.' . $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        if (empty($request->input('search.value'))) {
            if (Auth::user()->role_id > 2) {
                $sales = Sale::with('biller', 'customer', 'warehouse', 'user')->offset($start)
                    ->where('user_id', Auth::id())
                    ->whereDate('date_sell', ">=", $start_date)
                    ->whereDate('date_sell', "<=", $end_date)
                    ->limit($limit)
                    ->orderBy($order, $dir)
                    ->get();
            } else {
                $sales = Sale::with('biller', 'customer', 'warehouse', 'user')->offset($start)
                    ->limit($limit)
                    ->whereDate('date_sell', ">=", $start_date)
                    ->whereDate('date_sell', "<=", $end_date)
                    ->orderBy($order, $dir)
                    ->get();
            }
        } else {
            $search = $request->input('search.value');
            if (Auth::user()->role_id > 2) {
                $sales = Sale::select('sales.*')
                    ->with('biller', 'customer', 'warehouse', 'user')
                    ->join('customers', 'sales.customer_id', '=', 'customers.id')
                    //->join('customer_sales', 'sales.id', '=', 'customer_sales.sale_id')
                    ->whereDate('sales.date_sell', '=', date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                    ->whereDate('date_sell', ">=", $start_date)
                    ->whereDate('date_sell', "<=", $end_date)
                    ->where('sales.user_id', Auth::id())
                    ->orwhere([
                        ['sales.reference_no', 'LIKE', "%{$search}%"],
                        ['sales.user_id', Auth::id()],
                    ])
                    ->orwhere([
                        ['customers.name', 'LIKE', "%{$search}%"],
                        ['sales.user_id', Auth::id()],
                    ])
                    /*->orwhere([
                        ['customer_sales.codigofijo', $search],
                        ['sales.user_id', Auth::id()],
                    ])
                    ->orwhere([
                        ['customer_sales.numero_medidor', 'LIKE', "%{$search}%"],
                        ['sales.user_id', Auth::id()],
                    ])*/
                    ->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)->get();

                $totalFiltered = $sales->count();
            } else {
                $sales = Sale::select('sales.*')
                    ->with('biller', 'customer', 'warehouse', 'user')
                    ->join('customers', 'sales.customer_id', '=', 'customers.id')
                    ->join('billers', 'sales.biller_id', '=', 'billers.id')
                    ->whereDate('sales.date_sell', '=', date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                    ->whereDate('date_sell', ">=", $start_date)
                    ->whereDate('date_sell', "<=", $end_date)
                    ->orwhere('sales.reference_no', 'LIKE', "%{$search}%")
                    ->orwhere('customers.name', 'LIKE', "%{$search}%")
                    ->orwhere('billers.name', 'LIKE', "%{$search}%")
                    ->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)->get();

                $totalFiltered = $sales->count();
            }
        }
        $data = array();
        if (!empty($sales)) {
            foreach ($sales as $key => $sale) {
                $nestedData['id'] = $sale->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime($sale->date_sell));
                $nestedData['reference_no'] = $sale->reference_no . $this->getEstadoVentaFacturada($sale->id);
                $lims_product_sale_data = Product_Sale::select('id', 'employee_id')->where('sale_id', $sale->id)->get();
                $employes_names = "";
                foreach ($lims_product_sale_data as $sale_data) {
                    if ($sale_data->employee_id != null) {
                        $employee = Employee::select('name')->find($sale_data->employee_id);
                        $employes_names = $employes_names . " " . $employee->name;
                    }
                }
                $nestedData['biller'] = '<div>' . $sale->biller->name . '</div><div>' . $employes_names . '</div>';
                if ($sale->customer) {
                    if ($sale->codigoCliente != null || $sale->codigoCliente != '')
                        $nestedData['customer'] = $sale->customer->name . "|" . $sale->codigoCliente . "|" . $sale->numero_medidor;
                    else
                        $nestedData['customer'] = $sale->customer->name;
                } else {
                    $nestedData['customer'] = "Sin Nombre Cliente";
                }

                if ($sale->sale_status == 1) {
                    $nestedData['sale_status'] = '<div class="badge badge-success">' . trans('file.Completed') . '</div>';
                    $sale_status = trans('file.Completed');
                } elseif ($sale->sale_status == 2) {
                    $nestedData['sale_status'] = '<div class="badge badge-danger">' . trans('file.Pending') . '</div>';
                    $sale_status = trans('file.Pending');
                } elseif ($sale->sale_status == 4) {
                    $nestedData['sale_status'] = '<div class="badge badge-info">' . trans('file.Receivable') . '</div>';
                    $sale_status = trans('file.Receivable');
                } else {
                    $nestedData['sale_status'] = '<div class="badge badge-warning">' . trans('file.Draft') . '</div>';
                    $sale_status = trans('file.Draft');
                }

                if ($sale->payment_status == 1) {
                    $nestedData['payment_status'] = '<div class="badge badge-danger">' . trans('file.Pending') . '</div>';
                } elseif ($sale->payment_status == 2) {
                    $nestedData['payment_status'] = '<div class="badge badge-danger">' . trans('file.Due') . '</div>';
                } elseif ($sale->payment_status == 3) {
                    $nestedData['payment_status'] = '<div class="badge badge-warning">' . trans('file.Partial') . '</div>';
                } else {
                    $nestedData['payment_status'] = '<div class="badge badge-success">' . trans('file.Paid') . '</div>';
                }

                $nestedData['grand_total'] = number_format($sale->grand_total, 2);
                $nestedData['paid_amount'] = number_format($sale->paid_amount, 2);
                $nestedData['due'] = number_format($sale->grand_total - $sale->paid_amount, 2);
                $payments_list = Payment::where('sale_id', $sale->id)->get();
                $method = "";
                foreach ($payments_list as $payment) {
                    if ($payment->paying_method == "Efectivo") {
                        $method = $method . "<i style='font-size: x-large;' class='fa fa-money' aria-hidden='true' title='Efectivo'></i> ";
                    }
                    if ($payment->paying_method == "Tarjeta_Credito_Debito") {
                        $method = $method . "<i style='font-size: x-large;' class='fa fa-credit-card' aria-hidden='true' title='Tarjeta Debito/Credito'></i> ";
                    }
                    if ($payment->paying_method == "Qr_Simple" || $payment->paying_method == "Qr_simple") {
                        $method = $method . "<i style='font-size: x-large;' class='fa fa-qrcode' aria-hidden='true' title='QR'></i> ";
                    }
                    if ($payment->paying_method == "Cheque") {
                        $method = $method . "<i style='font-size: x-large;' class='fa fa-ticket' aria-hidden='true' title='Cheque'></i> ";
                    }
                    if ($payment->paying_method == "Deposito") {
                        $method = $method . "<i style='font-size: x-large;' class='fa fa-university' aria-hidden='true' title='Deposito'></i> ";
                    }
                    if ($payment->paying_method == "Tarjeta_Regalo") {
                        $method = $method . "<i style='font-size: x-large;' class='fa fa-gift' aria-hidden='true' title='Tarjeta de Regalo'></i> ";
                    }
                    if ($payment->paying_method == "Vale") {
                        $method = $method . "<i style='font-size: x-large;' class='fa fa-address-card-o' aria-hidden='true' title='Vale'></i> ";
                    }
                    if ($payment->paying_method == "Otros") {
                        $method = $method . "<i style='font-size: x-large;' class='fa fa-address-card' aria-hidden='true' title='Otros'></i> ";
                    }
                    if ($payment->paying_method == "Pago_Posterior") {
                        $method = $method . "<i style='font-size: x-large;' class='fa fa-ravelry' aria-hidden='true' title='Pago_Posterior'></i> ";
                    }
                    if ($payment->paying_method == "Transferencia_Bancaria") {
                        $method = $method . "<i style='font-size: x-large;' class='fa fa-superpowers' aria-hidden='true' title='Transferencia_Bancaria'></i> ";
                    }
                    if ($payment->paying_method == "Swift") {
                        $method = $method . "<i style='font-size: x-large;' class='fa fa-meetup' aria-hidden='true' title='Swift'></i> ";
                    }
                    if ($payment->paying_method == "Canal_Pago") {
                        $method = $method . "<i style='font-size: x-large;' class='fa fa-university' aria-hidden='true' title='Canal_Pago'></i> ";
                    }
                    if ($payment->paying_method == "Billetera_Movil") {
                        $method = $method . "<i style='font-size: x-large;' class='fa fa-university' aria-hidden='true' title='Billetera_Movil'></i> ";
                    }
                    if ($payment->paying_method == "Pago_Online") {
                        $method = $method . "<i style='font-size: x-large;' class='fa fa-university' aria-hidden='true' title='Pago_Online'></i> ";
                    }
                    if ($payment->paying_method == "Debito_Automatico") {
                        $method = $method . "<i style='font-size: x-large;' class='fa fa-university' aria-hidden='true' title='Debito_Automatico'></i> ";
                    }
                }
                if ($method == "") {
                    $nestedData['paymethod'] = "Sin Pagar";
                } else {
                    $nestedData['paymethod'] = $method;
                }

                //////////////////////////////////////////////////////////////////////////
                //////////////////////////////////////
                // Se insertará botón para AnularFactura solo a casos que la venta esté facturada.

                $venta_facturada = CustomerSale::where('sale_id', $sale->id)->first(['estado_factura', 'cuf', 'nro_factura']);
                
                // Log para debugging - Solo en desarrollo
                if (config('app.debug')) {
                    \Log::debug('[SaleController::index] Construyendo array de venta', [
                        'sale_id' => $sale->id,
                        'venta_facturada_existe' => $venta_facturada ? 'SÍ' : 'NO',
                        'cuf' => $venta_facturada ? ($venta_facturada->cuf ?? 'NULL') : 'NO_FACTURADA',
                        'nro_factura' => $venta_facturada ? ($venta_facturada->nro_factura ?? 'NULL') : 'NO_FACTURADA'
                    ]);
                }
                
                $nestedData['options'] = '<div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . trans("file.action") . '
                            <span class="caret"></span>
                            <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                                <li><a href="' . route('sale.invoice', $sale->id) . '" target="_blank" class="btn btn-link"><i class="fa fa-copy"></i> ' . trans('file.Print Sale') . '</a></li>
                                <li>
                                    <button type="button" class="btn btn-link view"><i class="fa fa-eye"></i> ' . trans('file.View') . '</button>
                                </li>';
                if (in_array("sales-edit", $request['all_permission']) && $venta_facturada == null) {
                    if ($sale->sale_status != 3) {
                        $nestedData['options'] .= '<li>
                            <a href="' . route('sales.edit', $sale->id) . '" class="btn btn-link"><i class="dripicons-document-edit"></i> ' . trans('file.edit') . '</a>
                            </li>';
                    } else {
                        $nestedData['options'] .= '<li>
                            <a href="' . url('sales/' . $sale->id . '/create') . '" class="btn btn-link"><i class="dripicons-document-edit"></i> ' . trans('file.edit') . '</a>
                        </li>';
                    }
                }

                if ($sale->sale_status == 4) {
                    $nestedData['options'] .=
                        '<li>
                        <button type="button" class="get-payment btn btn-link" data-id = "' . $sale->id . '"><i class="fa fa-money"></i> ' . trans('file.View Payment') . '</button>
                    </li>
                    <li>
                        <button type="button" class="add-delivery btn btn-link" data-id = "' . $sale->id . '"><i class="fa fa-truck"></i> ' . trans('file.Add Delivery') . '</button>
                    </li>';
                } else {
                    $nestedData['options'] .=
                        '<li>
                        <button type="button" class="add-payment btn btn-link" data-id = "' . $sale->id . '" data-toggle="modal" data-target="#add-payment"><i class="fa fa-plus"></i> ' . trans('file.Add Payment') . '</button>
                    </li>
                    <li>
                        <button type="button" class="get-payment btn btn-link" data-id = "' . $sale->id . '"><i class="fa fa-money"></i> ' . trans('file.View Payment') . '</button>
                    </li>
                    <li>
                        <button type="button" class="add-delivery btn btn-link" data-id = "' . $sale->id . '"><i class="fa fa-truck"></i> ' . trans('file.Add Delivery') . '</button>
                    </li>';
                }

                if (!empty($venta_facturada)) {

                    if ($venta_facturada->estado_factura == 'ANULADO') {
                        // Factura anulada: ofrecer re-facturar (CUF=null, el modal detecta y genera nueva)
                        $nestedData['options'] .=
                            '<li>
                            <button type="button" class="imprimir-factura-modal btn btn-link" data-id = "' . $sale->id . '" data-toggle="modal" data-target="#imprimir-factura-modal"><i class="fa fa-refresh"></i> Re-Facturar</button>
                        </li>';
                    } else {
                        // Vigente, Contingencia, Masivo: mostrar botón imprimir
                        $nestedData['options'] .=
                            '<li>
                            <button type="button" class="imprimir-factura-modal btn btn-link" data-id = "' . $sale->id . '" data-toggle="modal" data-target="#imprimir-factura-modal"><i class="fa fa-print"></i> ' . 'Imprimir Factura' . '</button>
                        </li>';
                    }

                    if (in_array("sales-delete", $request['all_permission']) && ($venta_facturada->estado_factura == 'VIGENTE' || $venta_facturada->estado_factura == 'FACTURADA')) {
                        // Vigente: mostrar botón anular
                        $nestedData['options'] .=
                            '<li>
                                <button type="button" class="anular-factura-modal btn btn-link" data-id = "' . $sale->id . '" data-toggle="modal" data-target="#anular-factura-modal"><i class="fa fa-file-excel-o"></i> ' . __("file.Cancel Invoice") . '</button>
                            </li>';
                    }

                    // Cerrar siempre el dropdown cuando existe registro de factura
                    $nestedData['options'] .= '
                            </ul>
                        </div>';
                } else {
                    if (in_array("sales-delete", $request['all_permission'])) {
                        $nestedData['options'] .= \Form::open(["route" => ["sales.destroy", $sale->id], "method" => "DELETE"]) . '
                                    <li>
                                        <button type="submit" class="btn btn-link" onclick="return confirmDelete()"><i class="dripicons-trash"></i> ' . trans("file.delete") . '</button>
                                    </li>' . \Form::close() . '
                                </ul>
                            </div>';
                    }
                }
                //////////////////////////////////////
                //////////////////////////////////////////////////////////////////////////

                // data for sale details by one click
                $coupon = Coupon::find($sale->coupon_id);
                if ($coupon) {
                    $coupon_code = $coupon->code;
                } else {
                    $coupon_code = null;
                }

                $nestedData['sale'] = array(
                    '[ "' . date(config('date_format'), strtotime($sale->date_sell)) . '"',
                    ' "' . $sale->reference_no . '"',
                    ' "' . $sale_status . '"',
                    ' "' . $sale->biller->name . '"',
                    ' "' . $sale->biller->company_name . '"',
                    ' "' . $sale->biller->email . '"',
                    ' "' . $sale->biller->phone_number . '"',
                    ' "' . $sale->biller->address . '"',
                    ' "' . $sale->biller->city . '"',
                    ' "' . $sale->customer->name . '"',
                    ' "' . $sale->customer->phone_number . '"',
                    ' "' . $sale->customer->address . '"',
                    ' "' . $sale->customer->city . '"',
                    ' "' . $sale->id . '"',
                    ' "' . $sale->total_tax . '"',
                    ' "' . $sale->total_discount . '"',
                    ' "' . $sale->total_price . '"',
                    ' "' . $sale->order_tax . '"',
                    ' "' . $sale->order_tax_rate . '"',
                    ' "' . $sale->order_discount . '"',
                    ' "' . $sale->shipping_cost . '"',
                    ' "' . $sale->grand_total . '"',
                    ' "' . $sale->paid_amount . '"',
                    ' "' . $sale->sale_note . '"',
                    ' "' . $sale->staff_note . '"',
                    ' "' . $sale->user->name . '"',
                    ' "' . $sale->user->email . '"',
                    ' "' . $sale->warehouse->name . '"',
                    ' "' . $coupon_code . '"',
                    ' "' . $sale->coupon_discount . '"',
                    ' "' . $sale->total_tips . '"',
                    ' "' . ($venta_facturada ? ($venta_facturada->cuf ?? '') : '') . '"',
                    '"' . ($venta_facturada ? ($venta_facturada->nro_factura ?? '') : '') . '"]',
                );                
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $data,
            "start_date" => $start_date,
            "end_date" => $end_date_temp,
        );

        echo json_encode($json_data);
    }

    public function create()
    {

        $role = Role::find(Auth::user()->role_id);

        if ($role->hasPermissionTo('sales-add')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission) {
                $all_permission[] = $permission->name;
            }

            if (empty($all_permission)) {
                $all_permission[] = 'dummy text';
            }

            $lims_pos_setting_data = PosSetting::latest()->first();

            if (Auth::user()->biller_id != null) {
                $user = Auth::user();
                $biller_data = Biller::select('id', 'account_id', 'warehouse_id')->find($user->biller_id);
                $lims_account_data = Account::select('id', 'name', 'account_no')->find($biller_data->account_id);
            } else {
                $biller_data = $lims_pos_setting_data;
                $lims_account_data = Account::select('id', 'name', 'account_no')->where('is_default', true)->first();
            }

            $account_data = $lims_account_data->name . " [" . $lims_account_data->account_no . "]";
            $lims_cashier_data = Cashier::select('id', 'end_date')->where([['account_id', $lims_account_data->id], ['is_active', true]])->first();
            $lims_customer_list = Customer::select('id', 'name', 'phone_number', 'is_credit', 'credit')->where('is_active', true)->get();
            $lims_customer_group_all = CustomerGroup::select('id', 'name')->where('is_active', true)->get();
            $lims_warehouse_list = Warehouse::select('id', 'name')->where('is_active', true)->get();
            $lims_biller_list = Biller::select('id', 'name', 'company_name')->where('is_active', true)->get();
            $lims_tax_list = Tax::where('is_active', true)->get();
            $lims_methodpay_list = MethodPayment::select('id', 'name')->where('cbx', true)->get();
            $lims_product_list = Product::select('id', 'name', 'code', 'image')->ActiveFeatured()->where('type', '!=', 'insumo')->whereNull('is_variant')->get();
            foreach ($lims_product_list as $key => $product) {
                $images = explode(",", $product->image);
                $product->base_image = $images[0];
                if ($product->type == 'insumo') {
                    unset($lims_product_list[$key]);
                }
            }
            $lims_product_list_with_variant = Product::select('id', 'name', 'code', 'image')->ActiveFeatured()->whereNotNull('is_variant')->get();

            foreach ($lims_product_list_with_variant as $product) {
                $images = explode(",", $product->image);
                $product->base_image = $images[0];
                $lims_product_variant_data = $product->variant()->orderBy('position')->get();
                $main_name = $product->name;
                $temp_arr = [];
                foreach ($lims_product_variant_data as $key => $variant) {
                    $product->name = $main_name . ' [' . $variant->name . ']';
                    $product->code = $variant->pivot['item_code'];
                    $lims_product_list[] = clone ($product);
                }
            }

            $product_number = count($lims_product_list);
            $lims_brand_list = Brand::select('id', 'title', 'image')->where('is_active', true)->get();
            $lims_category_list = Category::select('id', 'name', 'image')->where('is_active', true)->get();

            if (Auth::user()->role_id > 2 && config('staff_access') == 'own') {
                $recent_sale = Sale::select('id', 'reference_no', 'grand_total', 'customer_id', 'created_at')->where([
                    ['sale_status', 1],
                    ['user_id', Auth::id()],
                ])->orderBy('id', 'desc')->take(20)->get();
                $recent_draft = Sale::where([
                    ['sale_status', 3],
                    ['user_id', Auth::id()],
                ])->orderBy('id', 'desc')->take(20)->get();
            } else {
                $recent_sale = Sale::select('id', 'reference_no', 'grand_total', 'customer_id', 'created_at')
                    ->where('sale_status', 1)->orderBy('id', 'desc')->take(20)->get();
                $recent_draft = Sale::where('sale_status', 3)->orderBy('id', 'desc')->take(20)->get();
            }
            $lims_coupon_list = Coupon::where('is_active', true)->get();
            $flag = 0;

            $lista_documentos = SiatParametricaVario::where('tipo_clasificador', 'tipoDocumentoIdentidad')->get();
            return view('sale.create', compact('all_permission', 'lims_customer_list', 'lims_customer_group_all', 'lims_warehouse_list', 'lims_product_list', 'product_number', 'lims_tax_list', 'lims_biller_list', 'lims_pos_setting_data', 'lims_brand_list', 'lims_category_list', 'recent_sale', 'recent_draft', 'lims_coupon_list', 'flag', 'lims_methodpay_list', 'biller_data', 'account_data', 'lista_documentos'));
        } else {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            //Log::info(json_encode($request->all()));
            $data = $request->all();
            // return dd($request->all());
            $data['user_id'] = Auth::id();
            $datesell = date('Y-m-d', strtotime($data['date_sell']));
            $time = date('H:i:s');
            $data['date_sell'] = $datesell . " " . $time;
            $last_ref = Sale::get()->last();

            if ($last_ref != null) {
                $nros = explode("-", $last_ref['reference_no']);
                $nro = ltrim($nros[1], "0");
                $nro++;
                $nro = str_pad($nro, 8, "0", STR_PAD_LEFT);
            } else {
                $nro = str_pad(1, 8, "0", STR_PAD_LEFT);
            }

            if ($data['pos']) {
                $data['reference_no'] = 'NRV-' . $nro;
                $balance = $data['grand_total'] - $data['paid_amount'];

                if ($balance > 0 || $balance < 0) {
                    $data['payment_status'] = 2;
                } else {
                    $data['payment_status'] = 4;
                }

                if ($data['draft']) {
                    $lims_sale_data = Sale::find($data['sale_id']);
                    $lims_product_sale_data = Product_Sale::where('sale_id', $data['sale_id'])->get();
                    foreach ($lims_product_sale_data as $product_sale_data) {
                        $product_sale_data->delete();
                    }
                    $lims_sale_data->delete();
                }
            } else {
                $data['reference_no'] = 'NRV-' . $nro;
            }

            $document = $request->document;
            if ($document) {
                $v = Validator::make(
                    [
                        'extension' => strtolower($request->document->getClientOriginalExtension()),
                    ],
                    [
                        'extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt',
                    ]
                );
                if ($v->fails()) {
                    return redirect()->back()->withErrors($v->errors());
                }

                $documentName = $document->getClientOriginalName();
                $document->move('public/sale/documents', $documentName);
                $data['document'] = $documentName;
            }
            // verificar si tiene cupones
            if ($data['coupon_active']) {
                $lims_coupon_data = Coupon::find($data['coupon_id']);
                $lims_coupon_data->used += 1;
                $lims_coupon_data->save();
            }

            /** Get customer by document or else by id */
            if (isset($data['sales_valor_documento']) && $data['sales_valor_documento'] != null) {
                $lims_customer_data = Customer::where([['valor_documento', $data['sales_valor_documento']], ['is_active', true]])->first();
                if ($lims_customer_data)
                    $data['customer_id'] = $lims_customer_data->id;
                else
                    $lims_customer_data = Customer::find($data['customer_id']);
            } else {
                $lims_customer_data = Customer::find($data['customer_id']);
            }
            if (!$lims_customer_data) {
                $lims_customer_data = Customer::find($data['customer_id']);
            }

            // Normalizar/Redondear montos a 2 decimales antes de guardar la venta
            $round_keys = ['total_price', 'order_tax', 'order_discount', 'shipping_cost', 'grand_total', 'paid_amount'];
            foreach ($round_keys as $k) {
                if (isset($data[$k])) {
                    $data[$k] = number_format((float) $data[$k], 2, '.', '');
                }
            }

            Log::info('=== VENTA POS - DATOS RECIBIDOS ===');
            Log::info('Sale Data:', [
                'customer_id' => $data['customer_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'grand_total' => $data['grand_total'] ?? null,
            ]);

            $lims_sale_data = Sale::create($data);

            //collecting mail data
            $mail_data['email'] = $lims_customer_data->email;
            $mail_data['reference_no'] = $lims_sale_data->reference_no;
            $mail_data['sale_status'] = $lims_sale_data->sale_status;
            $mail_data['payment_status'] = $lims_sale_data->payment_status;
            $mail_data['total_qty'] = $lims_sale_data->total_qty;
            $mail_data['total_price'] = $lims_sale_data->total_price;
            $mail_data['order_tax'] = $lims_sale_data->order_tax;
            $mail_data['order_tax_rate'] = $lims_sale_data->order_tax_rate;
            $mail_data['order_discount'] = $lims_sale_data->order_discount;
            $mail_data['shipping_cost'] = $lims_sale_data->shipping_cost;
            $mail_data['grand_total'] = $lims_sale_data->grand_total;
            $mail_data['paid_amount'] = $lims_sale_data->paid_amount;

            $product_id = $data['product_id'];
            $product_code = $data['product_code'];
            $qty = $data['qty'];
            $sale_unit = $data['sale_unit'];
            $net_unit_price = $data['net_unit_price'];
            $discount = $data['discount'];
            $tax_rate = $data['tax_rate'];
            $tax = $data['tax'];
            $total = $data['subtotal'];
            $employee = $data['employee'];
            $presale = $data['presale'];
            $basicservice = $data['basicservice'];
            $product_description = $data['product_description'];
            $product_sale = [];

            $user = Auth::user();
            $stock_outsale = false;

            foreach ($product_id as $i => $id) {
                $lims_product_data = Product::where('id', $id)->first();
                $product_sale['variant_id'] = null;
                if (
                    ($lims_product_data->type == 'combo' || $lims_product_data->type == 'producto_terminado')
                    && ($data['sale_status'] == 1 || $data['sale_status'] == 4)
                ) {
                    $product_list = explode(",", $lims_product_data->product_list);
                    $qty_list = explode(",", $lims_product_data->qty_list);
                    $price_list = explode(",", $lims_product_data->price_list);
                    $cost_total = 0;
                    foreach ($product_list as $key => $child_id) {
                        $child_data = Product::find($child_id);
                        $cost_total += ($this->costoActualizadoProducto($child_data->id, null) * $qty_list[$key]);
                        if ($child_data->unit_id != 0 && $child_data->type != 'digital') {
                            $child_warehouse_data = Product_Warehouse::where([
                                ['product_id', $child_id],
                                ['warehouse_id', $data['warehouse_id']],
                            ])->first();
                            if ($child_warehouse_data && $child_warehouse_data->qty >= ($qty[$i] * $qty_list[$key])) {
                                $child_data->qty -= $qty[$i] * $qty_list[$key];
                                $child_warehouse_data->qty -= $qty[$i] * $qty_list[$key];
                                $qtytotal = $qty[$i] * $qty_list[$key];
                                $child_data->save();
                                $child_warehouse_data->save();
                                DB::table('record')->insert([
                                    'transaction_id' => $lims_sale_data->id,
                                    'warehouse_id' => $data['warehouse_id'],
                                    'product_id' => $child_id,
                                    'reference_no' => $data['reference_no'],
                                    'transaction_type' => 1,
                                    'product_qty_before' => 0,
                                    'product_qty_after' => 0,
                                    'warehouse_qty_before' => $child_warehouse_data->qty + $qty[$i] * $qty_list[$key],
                                    'warehouse_qty_after' => $child_warehouse_data->qty,
                                    'cb_cost' => $child_data->cost,
                                ]);
                                $this->updateLote($lims_sale_data->id, $child_id, $data['warehouse_id'], $qtytotal);
                            } else {
                                $stock_outsale = true;
                            }
                        }
                    }
                    $product_sale['cost'] = $cost_total;
                } else {
                    if ($lims_product_data->is_variant) {
                        $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')->FindExactProductWithCode($id, $product_code[$i])->first();
                        $product_sale['cost'] = $this->costoActualizadoProducto($lims_product_data->id, $lims_product_variant_data->variant_id);
                    } else {
                        $product_sale['cost'] = $this->costoActualizadoProducto($lims_product_data->id, null);
                    }
                }

                if ($sale_unit[$i] != 'n/a' && $lims_product_data->type == 'standard') {
                    $lims_sale_unit_data = Unit::where('unit_name', $sale_unit[$i])->first();
                    $sale_unit_id = $lims_sale_unit_data->id;
                    if ($lims_product_data->is_variant) {
                        $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')->FindExactProductWithCode($id, $product_code[$i])->first();
                        $product_sale['variant_id'] = $lims_product_variant_data->variant_id;
                    }

                    if ($data['sale_status'] == 1 || $data['sale_status'] == 4) {
                        $quantity = $qty[$i] * 1;

                        //deduct product variant quantity if exist
                        if ($lims_product_data->is_variant) {
                            $lims_product_warehouse_data = Product_Warehouse::FindProductWithVariant($id, $lims_product_variant_data->variant_id, $data['warehouse_id'])->first();
                        } else {
                            $lims_product_warehouse_data = Product_Warehouse::FindProductWithoutVariant($id, $data['warehouse_id'])->first();
                        }
                        //deduct quantity from warehouse
                        $qtytotal = $quantity;
                        if ($lims_product_warehouse_data->qty >= $quantity) {
                            $this->updateLote($lims_sale_data->id, $id, $data['warehouse_id'], $qtytotal);
                        } else {
                            $stock_outsale = true;
                        }
                    }
                } else {
                    $sale_unit_id = 0;
                }

                if ($product_sale['variant_id']) {
                    $variant_data = Variant::select('name')->find($product_sale['variant_id']);
                    $mail_data['products'][$i] = $lims_product_data->name . ' [' . $variant_data->name . ']';
                } else {
                    $mail_data['products'][$i] = $lims_product_data->name;
                }

                if ($lims_product_data->type == 'digital') {
                    $mail_data['file'][$i] = url('/public/product/files') . '/' . $lims_product_data->file;
                } else {
                    $mail_data['file'][$i] = '';
                }

                if ($sale_unit_id) {
                    $mail_data['unit'][$i] = $lims_sale_unit_data->unit_code;
                } else {
                    $mail_data['unit'][$i] = '';
                }

                $product_sale['sale_id'] = $lims_sale_data->id;
                $product_sale['product_id'] = $id;
                $product_sale['category_id'] = $lims_product_data->category_id;
                $product_sale['qty'] = $mail_data['qty'][$i] = $qty[$i];
                
                if ($sale_unit_id == 0) {
                    $lims_sale_unit_data = Unit::find($lims_product_data->sale_unit_id);
                    if ($lims_sale_unit_data)
                        $sale_unit_id = $lims_sale_unit_data->id;
                }
                $product_sale['sale_unit_id'] = $sale_unit_id;
                
                // --- CORRECCIÓN DE CÁLCULO DE PRECIOS Y TOTALES ---
                
                // 1. Reconstruimos el precio unitario original (Base) sumando el descuento unitario al precio neto recibido
                $product_sale['net_unit_price'] = number_format($net_unit_price[$i] + ($discount[$i] / $qty[$i]), 2, '.', '');
                
                // 2. Guardamos el descuento total de la línea
                $product_sale['discount'] = number_format($discount[$i], 2, '.', '');
                
                // 3. Guardamos los impuestos
                $product_sale['tax_rate'] = $tax_rate[$i];
                $product_sale['tax'] = number_format($tax[$i], 2, '.', '');
                
                $product_sale['description'] = $product_description[$i];

                if ($employee[$i] == 0) {
                    $product_sale['employee_id'] = null;
                } else {
                    $product_sale['employee_id'] = $employee[$i];
                }
                
                $role = Role::find(Auth::user()->role_id);
                if ($role->hasPermissionTo('presale-edit') && $product_sale['employee_id'] == null) {
                    $lims_presale_data = PreSale::where('status', 1)->find($presale[$i]);
                    if ($lims_presale_data) {
                        $product_sale['employee_id'] = $lims_presale_data->employee_id;
                    }
                }

                // 4. CÁLCULO DEL TOTAL CORREGIDO
                // Total = (Cantidad * Precio Base) - Descuento + Impuesto
                $total_bruto = $product_sale['qty'] * $product_sale['net_unit_price'];
                $total_neto = $total_bruto - $product_sale['discount'] + $product_sale['tax'];
                
                $product_sale['total'] = number_format($total_neto, 2, '.', '');
                
                // --- FIN CORRECCIÓN ---

                $mail_data['total'][$i] = $product_sale['total'];
                
                Log::info("=== PRODUCTO #{$i} GUARDADO ===", [
                    'product_id' => $product_sale['product_id'] ?? null,
                    'qty' => $product_sale['qty'] ?? null,
                    'net_unit_price (Base)' => $product_sale['net_unit_price'] ?? null,
                    'discount (Total)' => $product_sale['discount'] ?? null,
                    'tax' => $product_sale['tax'] ?? null,
                    'total (Final)' => $product_sale['total'] ?? null,
                ]);
                
                if (!$stock_outsale) {
                    Product_Sale::create($product_sale);
                }
            } // Fin del foreach product

            Log::info('=== RESUMEN VENTA POS ===', [
                'sale_id' => $lims_sale_data->id,
                'reference_no' => $lims_sale_data->reference_no,
                'total_productos' => count($product_id),
                'total_discount' => $lims_sale_data->total_discount,
                'grand_total' => $lims_sale_data->grand_total,
            ]);

            if ($stock_outsale) {
                //$lims_sale_data->delete();
                $this->destroy($lims_sale_data->id);
                return redirect()->to('pos')->with('not_permitted', "Venta Anulada, Revertido por insuficiente stock en uno o mas productos!");
            }
            $account_id = null;
            $lims_pos_setting_data = PosSetting::latest()->first();

            if ($data['sale_status'] == 2) {
                $message = 'Venta agregada con éxito al borrador';
                $lims_account_data = Account::where('is_default', true)->first();
                $account_id = $lims_account_data->id;
            } else if ($data['sale_status'] == 4) {
                $lims_account_data = Account::where('name', 'Cuenta por Cobrar')->first();
                $account_id = $lims_account_data->id;
                $message = 'Venta agregada con éxito a cuentas por cobrar';
                if ($user->biller_id != null) {
                    $biller_data = Biller::find($user->biller_id);
                    $lims_account_data = Account::select('id')->find($biller_data->account_id_receivable);
                }
                $account_id = $lims_account_data->id;
            } else {
                $message = ' Venta creada con éxito';
            }

            if ($mail_data['email'] && $data['sale_status'] == 1) {
                try {
                    Mail::send('mail.sale_details', $mail_data, function ($message) use ($mail_data) {
                        $message->to($mail_data['email'])->subject('Sale Details');
                    });
                } catch (\Exception $e) {
                    $message = ' Venta creada con éxito. Por favor configure su <a href="setting/mail_setting">configuración de</a> correo electrónico.';
                }
            }

            $role = Role::find(Auth::user()->role_id);
            /** Update PreSale */
            if ($role->hasPermissionTo('presale-edit')) {
                if ($data['presale_id'] != '0' && $lims_sale_data->sale_status == '1') {
                    $lims_presale_data = PreSale::find($data['presale_id']);
                    if ($lims_presale_data && ($lims_presale_data->tips != null && $lims_presale_data->tips > 0)) {
                        $tip_data['sale_id'] = $lims_sale_data->id;
                        $tip_data['presale_id'] = $lims_presale_data->id;
                        $tip_data['employee_id'] = $lims_presale_data->employee_id;
                        $tip_data['amount'] = $lims_presale_data->tips;
                        Tip::create($tip_data);
                    }
                    $lims_presale_data->status = 0;
                    $lims_presale_data->save();
                    foreach ($presale as $pre) {
                        if ($pre > 0) {
                            $lims_presale_data = PreSale::where('status', 1)->find($pre);
                            if ($lims_presale_data) {
                                if ($lims_presale_data->tips != null && $lims_presale_data->tips > 0) {
                                    $tip_data['sale_id'] = $lims_sale_data->id;
                                    $tip_data['presale_id'] = $lims_presale_data->id;
                                    $tip_data['employee_id'] = $lims_presale_data->employee_id;
                                    $tip_data['amount'] = $lims_presale_data->tips;
                                    Tip::create($tip_data);
                                }
                                $lims_presale_data->status = 0;
                                $lims_presale_data->save();
                            }
                        }
                    }
                    $message = $message . ' Y Preventa(s) fue dado de baja';
                }
            }

            if ($data['payment_status'] == 3 || $data['payment_status'] == 4 || ($data['payment_status'] == 2 && $data['pos'] && $data['paid_amount'] > 0)) {
                // ... (Se mantiene igual toda la lógica de pagos) ...
                if ($data['monto_tarjeta'] != null) {
                    $paying_method = 'Tarjeta_Credito_Debito';
                    if ($user->biller_id != null) {
                        $biller_data = Biller::find($user->biller_id);
                        $data_ant = Account::select('id as account_id')->find($biller_data->account_id_tarjeta);
                    } else {
                        $data_ant = AccountPayment::where([['is_active', true], ['methodpay_id', $data['paid_by_id']]])->first();
                    }
                    $account_id = $data_ant->account_id;

                    $pago_card_cred_deb = new Payment();
                    $pago_card_cred_deb->user_id = Auth::id();
                    $pago_card_cred_deb->account_id = $account_id;
                    $pago_card_cred_deb->sale_id = $lims_sale_data->id;
                    $data['payment_reference'] = 'spr-' . date("Ymd") . '-' . date("his");
                    $pago_card_cred_deb->payment_reference = $data['payment_reference'];

                    $pago_card_cred_deb->amount = $data['monto_tarjeta'];
                    $pago_card_cred_deb->change = 0;
                    $pago_card_cred_deb->paying_method = $paying_method;
                    $pago_card_cred_deb->payment_note = $paying_method . ', monto aplicado  de: ' . $data['monto_tarjeta'];
                    $pago_card_cred_deb->save();

                    $data_last_payment = Payment::latest()->first();
                    $data_card = new PaymentWithCreditCard();
                    $data_card->payment_id = $data_last_payment->id;
                    $data_card->customer_id = $data['customer_id'];
                    $lims_payment_with_credit_card_data = PaymentWithCreditCard::where('customer_id', $data['customer_id'])->first();
                    if (!$lims_payment_with_credit_card_data) {
                        $data_card->customer_stripe_id = "POSEXT-" . uniqid();
                    } else {
                        $customer_id = $lims_payment_with_credit_card_data->customer_stripe_id;
                        $data_card->customer_stripe_id = $customer_id;
                    }
                    $data_card->charge_id = uniqid();
                    $data_card->number_card = $data['number_card'];
                    $data_card->save();
                }
                if ($data['monto_cheque'] != null) {
                    $paying_method = 'Cheque';
                    if ($user->biller_id != null) {
                        $biller_data = Biller::find($user->biller_id);
                        $data_ant = Account::select('id as account_id')->find($biller_data->account_id_cheque);
                    } else {
                        $data_ant = AccountPayment::where([['is_active', true], ['methodpay_id', $data['paid_by_id']]])->first();
                    }
                    $account_id = $data_ant->account_id;

                    $pago_cheque = new Payment();
                    $pago_cheque->user_id = Auth::id();
                    $pago_cheque->account_id = $account_id;
                    $pago_cheque->sale_id = $lims_sale_data->id;
                    $data['payment_reference'] = 'spr-' . date("Ymd") . '-' . date("his");
                    $pago_cheque->payment_reference = $data['payment_reference'];

                    $pago_cheque->amount = $data['monto_cheque'];
                    $pago_cheque->change = 0;
                    $pago_cheque->paying_method = $paying_method;
                    $pago_cheque->payment_note = $paying_method . ', monto aplicado  de: ' . $data['monto_cheque'];
                    $pago_cheque->save();

                    $data_last_payment = Payment::latest()->first();
                    $data_cheque = new PaymentWithCheque();
                    $data_cheque->payment_id = $data_last_payment->id;
                    $data_cheque->cheque_no = $data['cheque_no'];
                    $data_cheque->save();
                }
                if ($data['monto_vale'] != null) {
                    $paying_method = 'Vale';
                    if ($user->biller_id != null) {
                        $biller_data = Biller::find($user->biller_id);
                        $data_ant = Account::select('id as account_id')->find($biller_data->account_id_vale);
                    } else {
                        $data_ant = AccountPayment::where([['is_active', true], ['methodpay_id', $data['paid_by_id']]])->first();
                    }
                    $account_id = $data_ant->account_id;

                    $pago_vale = new Payment();
                    $pago_vale->user_id = Auth::id();
                    $pago_vale->account_id = $account_id;
                    $pago_vale->sale_id = $lims_sale_data->id;
                    $data['payment_reference'] = 'spr-' . date("Ymd") . '-' . date("his");
                    $pago_vale->payment_reference = $data['payment_reference'];

                    $pago_vale->amount = $data['monto_vale'];
                    $pago_vale->change = 0;
                    $pago_vale->paying_method = $paying_method;
                    $pago_vale->payment_note = $paying_method . ', monto aplicado  de: ' . $data['monto_vale'];
                    $pago_vale->save();
                }
                if ($data['monto_otros'] != null) {
                    $paying_method = 'Otros';
                    if ($user->biller_id != null) {
                        $biller_data = Biller::find($user->biller_id);
                        $data_ant = Account::select('id as account_id')->find($biller_data->account_id_otros);
                    } else {
                        $data_ant = AccountPayment::where([['is_active', true], ['methodpay_id', $data['paid_by_id']]])->first();
                    }
                    $account_id = $data_ant->account_id;

                    $pago_otros = new Payment();
                    $pago_otros->user_id = Auth::id();
                    $pago_otros->account_id = $account_id;
                    $pago_otros->sale_id = $lims_sale_data->id;
                    $data['payment_reference'] = 'spr-' . date("Ymd") . '-' . date("his");
                    $pago_otros->payment_reference = $data['payment_reference'];

                    $pago_otros->amount = $data['monto_otros'];
                    $pago_otros->change = 0;
                    $pago_otros->paying_method = $paying_method;
                    $pago_otros->payment_note = $paying_method . ', monto aplicado  de: ' . $data['monto_otros'];
                    $pago_otros->save();
                }
                if ($data['monto_pago_posterior'] != null) {
                    $paying_method = 'Pago_Posterior';
                    if ($user->biller_id != null) {
                        $biller_data = Biller::find($user->biller_id);
                        $data_ant = Account::select('id as account_id')->find($biller_data->account_id_pagoposterior);
                    } else {
                        $data_ant = AccountPayment::where([['is_active', true], ['methodpay_id', $data['paid_by_id']]])->first();
                    }
                    $account_id = $data_ant->account_id;

                    $pago_posterior = new Payment();
                    $pago_posterior->user_id = Auth::id();
                    $pago_posterior->account_id = $account_id;
                    $pago_posterior->sale_id = $lims_sale_data->id;
                    $data['payment_reference'] = 'spr-' . date("Ymd") . '-' . date("his");
                    $pago_posterior->payment_reference = $data['payment_reference'];

                    $pago_posterior->amount = $data['monto_pago_posterior'];
                    $pago_posterior->change = 0;
                    $pago_posterior->paying_method = $paying_method;
                    $pago_posterior->payment_note = $paying_method . ', monto aplicado  de: ' . $data['monto_pago_posterior'];
                    $pago_posterior->save();
                }
                if ($data['monto_transferencia_bancaria'] != null) {
                    $paying_method = 'Transferencia_Bancaria';
                    if ($user->biller_id != null) {
                        $biller_data = Biller::find($user->biller_id);
                        $data_ant = Account::select('id as account_id')->find($biller_data->account_id_transferenciabancaria);
                    } else {
                        $data_ant = AccountPayment::where([['is_active', true], ['methodpay_id', $data['paid_by_id']]])->first();
                    }
                    $account_id = $data_ant->account_id;

                    $pago_tranferencia_bancaria = new Payment();
                    $pago_tranferencia_bancaria->user_id = Auth::id();
                    $pago_tranferencia_bancaria->account_id = $account_id;
                    $pago_tranferencia_bancaria->sale_id = $lims_sale_data->id;
                    $data['payment_reference'] = 'spr-' . date("Ymd") . '-' . date("his");
                    $pago_tranferencia_bancaria->payment_reference = $data['payment_reference'];

                    $pago_tranferencia_bancaria->amount = $data['monto_transferencia_bancaria'];
                    $pago_tranferencia_bancaria->change = 0;
                    $pago_tranferencia_bancaria->paying_method = $paying_method;
                    $pago_tranferencia_bancaria->payment_note = $paying_method . ', monto aplicado  de: ' . $data['monto_transferencia_bancaria'];
                    $pago_tranferencia_bancaria->save();
                }
                if ($data['monto_deposito'] != null) {
                    $paying_method = 'Deposito';
                    if ($data['bandera_factura_hidden']) {
                        if ($user->biller_id != null) {
                            $biller_data = Biller::find($user->biller_id);
                            $data_ant = Account::select('id as account_id')->find($biller_data->account_id_deposito);
                        } else {
                            $data_ant = AccountPayment::where([['is_active', true], ['methodpay_id', $data['paid_by_id']]])->first();
                        }
                    } else {
                        if ($data['paid_by_id'] == 7) {
                            $paying_method = 'Deposito';
                        } else if ($data['paid_by_id'] == 6 || $data['paid_by_id'] == 11) {
                            $paying_method = 'Qr_simple';
                        }
                        if ($user->biller_id != null) {
                            $biller_data = Biller::find($user->biller_id);
                            $data_ant = Account::select('id as account_id')->find($biller_data->account_id_qr);
                        } else {
                            $data_ant = AccountPayment::where([['is_active', true], ['methodpay_id', $data['paid_by_id']]])->first();
                        }
                    }

                    $account_id = $data_ant->account_id;

                    $lims_customer_data = Customer::find($data['customer_id']);
                    $lims_customer_data->expense += $data['monto_deposito'];
                    $lims_customer_data->save();

                    $pago_deposito = new Payment();
                    $pago_deposito->user_id = Auth::id();
                    $pago_deposito->account_id = $account_id;
                    $pago_deposito->sale_id = $lims_sale_data->id;
                    $data['payment_reference'] = 'spr-' . date("Ymd") . '-' . date("his");
                    $pago_deposito->payment_reference = $data['payment_reference'];

                    $pago_deposito->amount = $data['monto_deposito'];
                    $pago_deposito->change = 0;
                    $pago_deposito->paying_method = $paying_method;
                    $pago_deposito->payment_note = $paying_method . ', monto aplicado  de: ' . $data['monto_deposito'];
                    $pago_deposito->save();
                }
                if ($data['monto_swift'] != null) {
                    $paying_method = 'Swift';
                    if ($user->biller_id != null) {
                        $biller_data = Biller::find($user->biller_id);
                        $data_ant = Account::select('id as account_id')->find($biller_data->account_id_swift);
                    } else {
                        $data_ant = AccountPayment::where([['is_active', true], ['methodpay_id', $data['paid_by_id']]])->first();
                    }
                    $account_id = $data_ant->account_id;

                    $pago_swift = new Payment();
                    $pago_swift->user_id = Auth::id();
                    $pago_swift->account_id = $account_id;
                    $pago_swift->sale_id = $lims_sale_data->id;
                    $data['payment_reference'] = 'spr-' . date("Ymd") . '-' . date("his");
                    $pago_swift->payment_reference = $data['payment_reference'];

                    $pago_swift->amount = $data['monto_swift'];
                    $pago_swift->change = 0;
                    $pago_swift->paying_method = $paying_method;
                    $pago_swift->payment_note = $paying_method . ', monto aplicado  de: ' . $data['monto_swift'];
                    $pago_swift->save();
                }
                if ($data['monto_canal_pago'] != null) {
                    $paying_method = 'Canal_Pago';
                    if ($user->biller_id != null) {
                        $biller_data = Biller::find($user->biller_id);
                        $data_ant = Account::select('id as account_id')->find($biller_data->account_id_deposito);
                    } else {
                        $data_ant = AccountPayment::where([['is_active', true], ['methodpay_id', $data['paid_by_id']]])->first();
                    }
                    $account_id = $data_ant->account_id;

                    $pago_swift = new Payment();
                    $pago_swift->user_id = Auth::id();
                    $pago_swift->account_id = $account_id;
                    $pago_swift->sale_id = $lims_sale_data->id;
                    $data['payment_reference'] = 'spr-' . date("Ymd") . '-' . date("his");
                    $pago_swift->payment_reference = $data['payment_reference'];

                    $pago_swift->amount = $data['monto_canal_pago'];
                    $pago_swift->change = 0;
                    $pago_swift->paying_method = $paying_method;
                    $pago_swift->payment_note = $paying_method . ', monto aplicado  de: ' . $data['monto_canal_pago'];
                    $pago_swift->save();
                }
                if ($data['monto_billetera'] != null) {
                    $paying_method = 'Billetera_Movil';
                    if ($user->biller_id != null) {
                        $biller_data = Biller::find($user->biller_id);
                        $data_ant = Account::select('id as account_id')->find($biller_data->account_id_deposito);
                    } else {
                        $data_ant = AccountPayment::where([['is_active', true], ['methodpay_id', $data['paid_by_id']]])->first();
                    }
                    $account_id = $data_ant->account_id;

                    $pago_swift = new Payment();
                    $pago_swift->user_id = Auth::id();
                    $pago_swift->account_id = $account_id;
                    $pago_swift->sale_id = $lims_sale_data->id;
                    $data['payment_reference'] = 'spr-' . date("Ymd") . '-' . date("his");
                    $pago_swift->payment_reference = $data['payment_reference'];

                    $pago_swift->amount = $data['monto_billetera'];
                    $pago_swift->change = 0;
                    $pago_swift->paying_method = $paying_method;
                    $pago_swift->payment_note = $paying_method . ', monto aplicado  de: ' . $data['monto_billetera'];
                    $pago_swift->save();
                }
                if ($data['monto_pago_online'] != null) {
                    $paying_method = 'Pago_Online';
                    if ($user->biller_id != null) {
                        $biller_data = Biller::find($user->biller_id);
                        $data_ant = Account::select('id as account_id')->find($biller_data->account_id_deposito);
                    } else {
                        $data_ant = AccountPayment::where([['is_active', true], ['methodpay_id', $data['paid_by_id']]])->first();
                    }
                    $account_id = $data_ant->account_id;

                    $pago_swift = new Payment();
                    $pago_swift->user_id = Auth::id();
                    $pago_swift->account_id = $account_id;
                    $pago_swift->sale_id = $lims_sale_data->id;
                    $data['payment_reference'] = 'spr-' . date("Ymd") . '-' . date("his");
                    $pago_swift->payment_reference = $data['payment_reference'];

                    $pago_swift->amount = $data['monto_pago_online'];
                    $pago_swift->change = 0;
                    $pago_swift->paying_method = $paying_method;
                    $pago_swift->payment_note = $paying_method . ', monto aplicado  de: ' . $data['monto_pago_online'];
                    $pago_swift->save();
                }
                if ($data['monto_debito_automatico'] != null) {
                    $paying_method = 'Debito_Automatico';
                    if ($user->biller_id != null) {
                        $biller_data = Biller::find($user->biller_id);
                        $data_ant = Account::select('id as account_id')->find($biller_data->account_id_deposito);
                    } else {
                        $data_ant = AccountPayment::where([['is_active', true], ['methodpay_id', $data['paid_by_id']]])->first();
                    }
                    $account_id = $data_ant->account_id;

                    $pago_d_automatico = new Payment();
                    $pago_d_automatico->user_id = Auth::id();
                    $pago_d_automatico->account_id = $account_id;
                    $pago_d_automatico->sale_id = $lims_sale_data->id;
                    $data['payment_reference'] = 'spr-' . date("Ymd") . '-' . date("his");
                    $pago_d_automatico->payment_reference = $data['payment_reference'];

                    $pago_d_automatico->amount = $data['monto_debito_automatico'];
                    $pago_d_automatico->change = 0;
                    $pago_d_automatico->paying_method = $paying_method;
                    $pago_d_automatico->payment_note = $paying_method . ', monto aplicado  de: ' . $data['monto_debito_automatico'];
                    $pago_d_automatico->save();
                }
                if ($data['balance_gift_card'] > 0) {
                    $paying_method = 'Tarjeta_Regalo';
                    if ($user->biller_id != null) {
                        $biller_data = Biller::find($user->biller_id);
                        $data_ant = Account::select('id as account_id')->find($biller_data->account_id_giftcard);
                    } else {
                        $data_ant = AccountPayment::where([['is_active', true], ['methodpay_id', $data['paid_by_id']]])->first();
                    }
                    $account_id = $data_ant->account_id;

                    $pago_gift_card = new Payment();
                    $pago_gift_card->user_id = Auth::id();
                    $pago_gift_card->account_id = $account_id;
                    $pago_gift_card->sale_id = $lims_sale_data->id;
                    $data['payment_reference'] = 'spr-' . date("Ymd") . '-' . date("his");
                    $pago_gift_card->payment_reference = $data['payment_reference'];

                    $pago_gift_card->amount = $data['balance_gift_card'];
                    $pago_gift_card->change = 0;
                    $pago_gift_card->paying_method = $paying_method;
                    $pago_gift_card->payment_note = $paying_method . ', descuento aplicado  de: ' . $data['balance_gift_card'];
                    $pago_gift_card->save();

                    //reducimos el balance de la gift card usada
                    $update_gift_card = GiftCard::find($data['tarjeta_regalo_hidden_id']);
                    $update_gift_card->expense += $data['balance_gift_card'];
                    $update_gift_card->save();

                    $data_last_payment = Payment::latest()->first();
                    $data_gift_card = new PaymentWithGiftCard();
                    $data_gift_card->payment_id = $data_last_payment->id;
                    $data_gift_card->gift_card_id = $data['tarjeta_regalo_hidden_id'];
                    $data_gift_card->save();
                }
                if ($data['monto_efectivo'] != null) {
                    $paying_method = 'Efectivo';
                    if ($user->biller_id != null) {
                        $biller_data = Biller::find($user->biller_id);
                        $data_ant = Account::select('id as account_id')->find($biller_data->account_id);
                    } else {
                        $data['paid_by_id'] = 1;
                        $data_ant = AccountPayment::where([['is_active', true], ['methodpay_id', $data['paid_by_id']]])->first();
                    }
                    $account_id = $data_ant->account_id;

                    $pago_efectivo = new Payment();
                    $pago_efectivo->user_id = Auth::id();
                    $pago_efectivo->account_id = $account_id;
                    $pago_efectivo->sale_id = $lims_sale_data->id;
                    $data['payment_reference'] = 'spr-' . date("Ymd") . '-' . date("his");
                    $pago_efectivo->payment_reference = $data['payment_reference'];

                    $pago_efectivo->amount = $data['monto_efectivo'];
                    $pago_efectivo->change = $data['monto_cambio'];
                    $pago_efectivo->paying_method = $paying_method;
                    $pago_efectivo->payment_note = $paying_method . ', monto entregado  de: ' . $data['monto_efectivo'] . ', Cambio: ' . $data['monto_cambio'];
                    $pago_efectivo->save();
                }
            }

            if ($lims_pos_setting_data->print_order != null || $lims_pos_setting_data->print_order != 0) {
                //$this->printPre_Order($lims_pos_setting_data->print_order, $lims_sale_data->id);
            }

            //////////////////////////////////////////////////////
            // guardamos datos del cliente, porque provienen tabs facturar
            // If caller requested an AJAX preview, return printable sale HTML before SIAT/final invoicing
            if (isset($data['ajax_preview']) && $data['ajax_preview']) {
                // prepare data similar to genInvoice
                $lims_product_sale_data = Product_Sale::where('sale_id', $lims_sale_data->id)->get();
                $lims_biller_data = Biller::find($lims_sale_data->biller_id);
                $lims_warehouse_data = Warehouse::find($lims_sale_data->warehouse_id);
                $lims_customer_data = Customer::find($lims_sale_data->customer_id);
                $lims_payment_data = Payment::where('sale_id', $lims_sale_data->id)->get();
                $lims_pos_setting_data = PosSetting::latest()->first();
                $formato_fecha = GeneralSetting::first()->date_format;
                $lims_sale_data->setAttribute('formato_fecha', "$formato_fecha H:i:s");
                $numberToWords = new NumberToWords();
                if (\App::getLocale() == 'ar' || \App::getLocale() == 'hi' || \App::getLocale() == 'vi' || \App::getLocale() == 'en-gb') {
                    $numberTransformer = $numberToWords->getNumberTransformer('en');
                } else {
                    $numberTransformer = $numberToWords->getNumberTransformer(\App::getLocale());
                }
                $cadenaCentavos = $this->obtenerParteDecimalLiteral($lims_sale_data->grand_total);
                $numberInWords = $numberTransformer->toWords($lims_sale_data->grand_total);
                $is_preview_mode = true; // Indica que es vista previa, no debe auto-imprimir ni redirigir
                $print_html = view('sale.invoice', compact('lims_sale_data', 'lims_product_sale_data', 'lims_biller_data', 'lims_warehouse_data', 'lims_customer_data', 'lims_payment_data', 'numberInWords', 'cadenaCentavos', 'is_preview_mode'))->render();
                DB::commit();
                return response()->json(['status' => true, 'sale_id' => $lims_sale_data->id, 'print_html' => $print_html, 'message' => $message]);
            }

            if ($data['bandera_factura_hidden'] && $data['sale_status'] != 4) {

                if ($data['sales_tipo_documento_hidden'] != 1) {
                    $text_complemento_documento = null;
                } else {
                    $text_complemento_documento = $data['sales_complemento_documento'];
                }

                // Tabla CustomerNIT
                if ($data['sales_caso_especial_hidden'] == 1) { // Válido solo cuando no exista casos especial
                    $nit_data = CustomerNit::where('tipo_documento', $data['sales_tipo_documento_hidden'])
                        ->where('valor_documento', $data['sales_valor_documento'])->first();

                    $fecha_hora_actual = new Carbon();
                    if ($nit_data != null) {
                        // Existe alguna coincidencia en la base de datos
                        $nit_data = DB::table('customer_nit')
                            ->where('tipo_documento', $data['sales_tipo_documento_hidden'])
                            ->where('valor_documento', $data['sales_valor_documento'])
                            ->update([
                                'razon_social' => $data['sales_razon_social'],
                                'email' => $data['sales_email'],
                                'complemento_documento' => $text_complemento_documento,
                                'updated_at' => $fecha_hora_actual,
                            ]);
                    } else {

                        DB::table('customer_nit')->insert(
                            [
                                'tipo_documento' => $data['sales_tipo_documento_hidden'],
                                'valor_documento' => $data['sales_valor_documento'],
                                'complemento_documento' => $text_complemento_documento,
                                'razon_social' => $data['sales_razon_social'],
                                'email' => $data['sales_email'],
                                'created_at' => $fecha_hora_actual,
                                'updated_at' => $fecha_hora_actual,
                            ]
                        );
                    }
                }
                // Fin tabla CustomerNit

                $obj_cliente = new CustomerSale();
                $obj_cliente->sale_id = $lims_sale_data->id;
                $obj_cliente->customer_id = $data['customer_id'];
                $obj_cliente->razon_social = $data['sales_razon_social'];
                $obj_cliente->email = $data['sales_email'];
                if ($data['codigo_fijo'] != null || $data['codigo_fijo'] != '')
                    $obj_cliente->codigofijo = $data['codigo_fijo'];
                else
                    $obj_cliente->codigofijo = $data['customer_id'];

                $obj_cliente->tipo_documento = $data['sales_tipo_documento_hidden'];
                $obj_cliente->valor_documento = $data['sales_valor_documento'];
                $obj_cliente->complemento_documento = $text_complemento_documento;
                $obj_cliente->codigo_excepcion = $data['bandera_codigo_excepcion_hidden'];
                $obj_cliente->codigo_documento_sector = $data['bandera_codigo_documento_sector_hidden'];
                $obj_cliente->glosa_periodo_facturado = $data['glosa_periodo_facturado'];
                $obj_cliente->usuario = Auth::user()->name;

                // En caso de tarjeta de crédito/débito se procede enmascarar.
                if ($data['number_card'] != null) {
                    $nro_tarjeta = Str::of($data['number_card'])->replaceMatches('/[^A-Za-z0-9]++/', '');
                    $primeros_cuatro = Str::substr($nro_tarjeta, 0, 4);
                    $relleno = "00000000";
                    $ultimos_cuatro = Str::substr($nro_tarjeta, 12, 4);
                    $nro_completo = $primeros_cuatro . $relleno . $ultimos_cuatro;

                    $obj_cliente->numero_tarjeta_credito_debito = $nro_completo;
                }
                // Setear nro. factura y aumentamos el correlativo
                $data_biller = Biller::where('id', $data['biller_id'])->first();
                $data_p_venta = SiatPuntoVenta::where([
                    'sucursal' => $data_biller->sucursal,
                    'codigo_punto_venta' => $data_biller->punto_venta_siat
                ])->first();
                $update_p_venta = SiatPuntoVenta::where([
                    'sucursal' => $data_biller->sucursal,
                    'codigo_punto_venta' => $data_biller->punto_venta_siat
                ])->first();

                if ($data['codigo_emision_hidden'] == 1 && $data['nro_factura_manual'] != null) {
                    $obj_cliente->nro_factura_manual = $data['nro_factura_manual'];
                    $obj_cliente->fecha_manual = $data['fecha_manual'];
                    $data_credencial_cafc = CredencialCafc::where('sucursal', $data_p_venta->sucursal)
                        ->where('codigo_punto_venta', $data_p_venta->codigo_punto_venta)
                        ->where('codigo_documento_sector', $obj_cliente->codigo_documento_sector)
                        ->where('is_active', true)->first();
                    $data_credencial_cafc->correlativo_factura += 1;
                    $data_credencial_cafc->save();
                } else {
                    if ($obj_cliente->codigo_documento_sector == 1) {
                        $obj_cliente->nro_factura = $data_p_venta->correlativo_factura;
                        $update_p_venta->correlativo_factura += 1;
                    }
                    if ($obj_cliente->codigo_documento_sector == 2) {
                        $obj_cliente->nro_factura = $data_p_venta->correlativo_alquiler;
                        $update_p_venta->correlativo_alquiler += 1;
                    }
                    if ($obj_cliente->codigo_documento_sector == 13) {
                        $obj_cliente->nro_factura = $data_p_venta->correlativo_servicios_basicos;
                        $update_p_venta->correlativo_servicios_basicos += 1;

                        $obj_cliente->gestion = $data['gestion'];
                        $obj_cliente->mes = $data['mes'];
                        $obj_cliente->ciudad = $data['ciudad'];
                        $obj_cliente->zona = $data['zona'];
                        $obj_cliente->domicilio_cliente = $data['domicilio_cliente'];
                        $obj_cliente->consumo_periodo = $data['consumo_periodo'];
                        $obj_cliente->numero_medidor = $data['numero_medidor'];
                        $obj_cliente->lectura_medidor_actual = $data['lectura_medidor_actual'];
                        $obj_cliente->lectura_medidor_anterior = $data['lectura_medidor_anterior'];
                        $obj_cliente->tasa_aseo = $data['tasa_aseo'];
                        $obj_cliente->tasa_alumbrado = $data['tasa_alumbrado'];
                        $obj_cliente->otras_tasas = $data['otras_tasas'];
                        $ajusteDetalleIva = [];
                        $ajusteDetalleNoIva = [];
                        $otrosPagosDetallesNoIva = [];
                        $ajusteNoSujetoIva = 0;
                        $ajusteSujetoIva = 0;
                        $otrosPagosNoSujetoIva = 0;
                        foreach ($data['montoItemIva'] as $key => $montoIva) {
                            if ($montoIva > 0) {
                                $ajusteSujetoIva = $ajusteSujetoIva + $montoIva;
                                $ajusteDetalleIva[] = array($data["descripcionItemIva"][$key] => floatval($montoIva));
                            }
                        }
                        foreach ($data['montoItemNoIva'] as $key => $montoNoIva) {
                            if ($montoNoIva > 0) {
                                $ajusteNoSujetoIva = $ajusteNoSujetoIva + $montoNoIva;
                                $ajusteDetalleNoIva[] = array($data["descripcionItemNoIva"][$key] => floatval($montoNoIva));
                            }
                        }
                        foreach ($data['otrosMontoItemNoIva'] as $key => $otroMontoNoIva) {
                            if ($otroMontoNoIva > 0) {
                                $otrosPagosNoSujetoIva = $otrosPagosNoSujetoIva + $otroMontoNoIva;
                                $otrosPagosDetallesNoIva[] = array($data["descripcionOtroItemNoIva"][$key] => floatval($otroMontoNoIva));
                            }
                        }
                        /** Formated json Ajuste Detalle IVA */
                        $ajusteDetalleIva = json_encode($ajusteDetalleIva);
                        $ajusteDetalleIva = trim($ajusteDetalleIva, "[");
                        $ajusteDetalleIva = trim($ajusteDetalleIva, "]");
                        $ajusteDetalleIva = str_replace("},{", ",", $ajusteDetalleIva);
                        /** Formated json Ajuste Detalle No IVA */
                        $ajusteDetalleNoIva = json_encode($ajusteDetalleNoIva);
                        $ajusteDetalleNoIva = trim($ajusteDetalleNoIva, "[");
                        $ajusteDetalleNoIva = trim($ajusteDetalleNoIva, "]");
                        $ajusteDetalleNoIva = str_replace("},{", ",", $ajusteDetalleNoIva);
                        /** Formated json Otros Pagos Detalle No IVA */
                        $otrosPagosDetallesNoIva = json_encode($otrosPagosDetallesNoIva);
                        $otrosPagosDetallesNoIva = trim($otrosPagosDetallesNoIva, "[");
                        $otrosPagosDetallesNoIva = trim($otrosPagosDetallesNoIva, "]");
                        $otrosPagosDetallesNoIva = str_replace("},{", ",", $otrosPagosDetallesNoIva);

                        $obj_cliente->ajuste_sujeto_iva = (float) $ajusteSujetoIva;
                        $obj_cliente->detalle_ajuste_sujeto_iva = $ajusteDetalleIva;
                        $obj_cliente->ajuste_no_sujeto_iva = (float) $ajusteNoSujetoIva;
                        $obj_cliente->detalle_ajuste_no_sujeto_iva = $ajusteDetalleNoIva;
                        $obj_cliente->otros_pagos_no_sujeto_iva = (float) $otrosPagosNoSujetoIva;
                        $obj_cliente->detalle_otros_pagos_no_sujeto_iva = $otrosPagosDetallesNoIva;
                        
                        if ($data['montoLey1886_hidden'] > 0) {
                            $obj_cliente->monto_descuento_ley_1886 = $data['montoLey1886_hidden'];
                            $obj_cliente->beneficiario_ley_1886 = $data['sales_valor_documento'];
                        } else {
                            $obj_cliente->monto_descuento_ley_1886 = 0;
                            $obj_cliente->beneficiario_ley_1886 = 0;
                        }
                        if ($data['montoTasaDignidad_hidden'] > 0)
                            $obj_cliente->monto_descuento_tarifa_dignidad = $data['montoTasaDignidad_hidden'];
                        else
                            $obj_cliente->monto_descuento_tarifa_dignidad = 0;
                    }
                }

                $obj_cliente->tipo_caso_especial = $data['sales_caso_especial_hidden'];
                $obj_cliente->tipo_metodo_pago = $data['paid_by_id'];
                if ($data['codigo_emision_hidden'] == 1) {
                    // Emisión ONLINE
                    $obj_cliente->estado_factura = "CONTINGENCIA";
                }

                if ($data['codigo_emision_hidden'] == 3) {
                    // Emisión MASIVA
                    $obj_cliente->estado_factura = "MASIVO";
                }


                $obj_cliente->sucursal = $data_p_venta->sucursal;
                $obj_cliente->codigo_punto_venta = $data_p_venta->codigo_punto_venta;
                $obj_cliente->save();

                if ($data['codigo_emision_hidden'] == 3) {
                    $message = $message . '\n Venta facturada en modo masiva. ';
                } else {
                    // Caso Modo Contingencia
                    if ($data_p_venta->modo_contingencia == true) {
                        $codigoEvento = $this->getTipoEventoContingenciaPuntoVenta($data['biller_id']);
                        if ($codigoEvento && $obj_cliente->codigo_documento_sector == 1) {
                            $respuesta = $this->generarFacturaIndividualOffline($lims_sale_data->id, $codigoEvento);
                        }
                        if ($codigoEvento && $obj_cliente->codigo_documento_sector == 13) {
                            $respuesta = $this->generarFacturaServicioBasicoOffline($lims_sale_data->id, $codigoEvento);
                        }
                        if ($codigoEvento && $obj_cliente->codigo_documento_sector == 2) {
                            $respuesta = $this->generarFacturaAlquilerOffline($lims_sale_data->id, $codigoEvento);
                        }
                        if ($respuesta['status']) {
                            $update_p_venta->save();
                            DB::commit();
                            $data['pos'] = $lims_pos_setting_data->print;
                            if ($data['pos']) {
                                return redirect('sales/imprimir_factura/' . $lims_sale_data->id)->with('message', $respuesta['mensaje']);
                            } else {
                                return redirect()->to('pos')->with('message', $respuesta['mensaje']);
                            }
                        } else {
                            $this->destroy($lims_sale_data->id);
                            $obj_cliente->delete();
                            $message .= " Venta Eliminada, Intente de Nuevo";
                            return redirect()->to('pos')->with('message', $message)->with('message_error', $respuesta['mensaje']);
                        }
                    } else {
                        // Procedemos a llamar la funcion, para generar la factura
                        if ($obj_cliente->codigo_documento_sector == 1) {
                            // Toggle por Ajustes -> POS -> "CUFD Centralizado"
                            if (($lims_pos_setting_data->cufd_centralizado ?? 0) == 1) {
                                \Log::info('═══════════════════════════════════════════════════════');
                                \Log::info('FACTURACIÓN: Usando endpoint COMISIONISTA (CUFD Centralizado = 1)');
                                \Log::info('Sale ID: ' . $lims_sale_data->id);
                                \Log::info('═══════════════════════════════════════════════════════');
                                $respuesta = $this->generarFacturaIndividualComisionista($lims_sale_data->id);
                            } else {
                                \Log::info('═══════════════════════════════════════════════════════');
                                \Log::info('FACTURACIÓN: Usando endpoint ESTÁNDAR (CUFD Centralizado = 0)');
                                \Log::info('Sale ID: ' . $lims_sale_data->id);
                                \Log::info('═══════════════════════════════════════════════════════');
                                $respuesta = $this->generarFacturaIndividual($lims_sale_data->id);
                            }
                        }
                        if ($obj_cliente->codigo_documento_sector == 2) {
                            $respuesta = $this->generarFacturaIndividualAlquiler($lims_sale_data->id);
                        }
                        if ($obj_cliente->codigo_documento_sector == 13) {
                            $respuesta = $this->generarFacturaServicioBasico($lims_sale_data->id);
                        }
                        if ($respuesta['status']) {
                            $update_p_venta->save();
                            DB::commit();
                            // fin dFe correlativo factura
                            $data['pos'] = $lims_pos_setting_data->print;
                            if ($data['pos']) {
                                return redirect('sales/imprimir_factura/' . $lims_sale_data->id)->with('message', $respuesta['mensaje']);
                            } else {
                                return redirect()->to('pos')->with('message', $respuesta['mensaje']);
                            }
                        } else {
                            $this->destroy($lims_sale_data->id);
                            $obj_cliente->delete();
                            $message .= " Venta Eliminada, Intente de Nuevo";
                            return redirect()->to('pos')->with('message', $message)->with('message_error', $respuesta['mensaje']);
                        }
                    }
                }
            }
            DB::commit();
            if ($lims_sale_data->sale_status == '1' || $lims_sale_data->sale_status == '4') {
                $data['pos'] = $lims_pos_setting_data->print;
                if ($data['pos']) {
                    if ($lims_pos_setting_data->type_print == 4 || $lims_pos_setting_data->type_print == 5 || $lims_pos_setting_data->type_print == 7) {
                        return redirect()->to('pos')->with(['message' => $message, 'printsale' => true, 'saleid' => $lims_sale_data->id]);
                    } else {
                        return redirect('sales/gen_invoice/' . $lims_sale_data->id)->with('message', $message);
                    }
                } else {
                    return redirect()->to('pos')->with('message', $message);
                }
            } elseif ($data['pos']) {
                return redirect()->to('pos')->with('message', $message);
            } else {
                return redirect()->to('sales')->with('message', $message);
            }
        } catch (\Throwable $e) {
            DB::rollback();
            Log::error("error save sale message: " . $e->getMessage());
            Log::error("error save sale line: " . $e->getLine());
            Log::error("error save sale code: " . $e->getCode());
            return redirect()->to('sales')->with('message', 'Falló al registrar la venta');
        }
    }

    public function printPre_Order($id_print, $id_sale)
    {
        $lims_sale_data = Sale::find($id_sale);
        $lims_product_sale_data = Product_Sale::select('id', 'sale_id', 'product_id', 'variant_id', 'qty', 'total')->where('sale_id', $id_sale)->get();
        $lims_biller_data = Biller::select('name')->find($lims_sale_data->biller_id);

        $tabla = "\r\n" . "PRE-ORDEN" . "\r\n";
        $strdate = date("d/m/Y H:i");
        $tabla .= "Fecha:" . "\x1F \x1F" . $strdate . "\r\n";
        foreach ($lims_product_sale_data as $row) {
            $product_data = Product::select('name')->find($row->product_id);
            if ($row->variant_id) {
                $variant_data = Variant::select('name')->find($row->variant_id);
                $product_name = $product_data->name . ' [' . $variant_data->name . ']';
            } else {
                $product_name = $product_data->name;
            }

            $tabla .= "Producto:" . "\x1F \x1F" . $product_name . "\r\n";
            $tabla .= "Cantidad:" . "\x1F \x1F" . number_format($row->qty, 2, '.', '') . "\r\n";
        }
        $tabla .= "\r\n";
        $tabla .= "Nro Venta:" . "\x1F \x1F" . $lims_sale_data->reference_no . "\r\n";
        $tabla .= "Vendedor:" . "\x1F \x1F" . $lims_biller_data->name . "\r\n";
        $tabla .= "\r\n";
        $tabla .= "Nota:" . "\x1F \x1F" . $lims_sale_data->sale_note . "\r\n";
        $tabla .= "\r\n";
        $tabla .= "\r\n";
        $strprint = $tabla;

        $printer = PrinterConfig::select('id', 'printer')->find($id_print);
        if ($printer != null) {
            $printer_name = $printer->printer;
            //file_put_contents("order.txt", $strprint);
            //$file = fopen("order.txt", "w") or die("Unable to open file!");
            //fwrite($file, $strprint);
            //fclose($file);
            $filename = "order-" . date('d-m-Y') . ".txt";
            $file_path = public_path() . '/downloads/' . $filename;
            $file_url = url('/') . '/downloads/' . $filename;
            $file = fopen($file_path, "w");
            fwrite($file, $strprint);
            fclose($file);
            //$file = fopen("test.txt","r");
            /*try {
            $enlace=printer_open($printer_name);
            printer_set_option($enlace, PRINTER_MODE, "RAW");
            printer_write($enlace, $strprint);
            printer_close($enlace);
            return json_encode(true);
            }
            catch(Exception $e) {
            $arr['message'] = 'Mensaje: ' .$e->getMessage();
            return json_encode($arr);
            }*/
            try {

                ///$filer = fopen($file_path, "r") or die("Unable to open file!");
                //$data = fread($filer,filesize($file_path));
                //fclose($filer);
                //$printer_name = "//JCCM-17/".$printer_name;
                //copy($file_path, $printer_name);
                //exec("copy $file_path \\\\JCCM-17\\Virtual Print Test");
                /*$connector = new WindowsPrintConnector("smb://JCCM-17/".$printer_name);
                $printer_server = new Printer($connector);
                $printer_server->text("hello world");
                $printer_server->cut();
                $printer_server->close();*/
            } catch (Exception $e) {
                $arr['message'] = 'Mensaje: ' . $e->getMessage();
                return json_encode($arr);
            }
            /*try{
            $fp=pfsockopen("192.168.1.109",9100);
            fputs($fp,$strprint);
            fclose($fp);
            return json_encode(true);
            }catch (Exception $e) {
            $arr['message'] = 'Mensaje: ' .$e->getMessage();
            return json_encode($arr);
            }*/
        }
    }

    public function updateLote($id_sale, $id_pro, $id_warehouse, $qty)
    {
        $lote_list = ProductLote::select('id', 'qty', 'stock', 'name', 'expiration', 'status', 'low_date')->where([['idproduct', $id_pro], ['idwarehouse', $id_warehouse], ['status', '!=', 0]])->orderBy('expiration', 'ASC')->get();
        $id_lotes = [];
        $temp_stock = 0;
        foreach ($lote_list as $lote) {
            $temp_stock = $temp_stock + $lote->stock;
            $id_lotes[] = $lote;
            if ($temp_stock >= $qty) {
                if (sizeof($id_lotes) > 0) {
                    $desc = 0;
                    $total = 0;
                    foreach ($id_lotes as $lot) {
                        $desc = $desc + $lot->stock;
                        if ($qty >= $desc) {
                            $total = $total + $lot->stock;
                            $data['sale_id'] = $id_sale;
                            $data['lote_id'] = $lot->id;
                            $data['qty'] = $lot->stock;
                            $loteSale = LoteSale::create($data);
                            $this->updateStockLote($lot->id, 0);
                        } else {
                            $data['sale_id'] = $id_sale;
                            $data['lote_id'] = $lot->id;
                            $data['qty'] = $qty - $total;
                            $loteSale = LoteSale::create($data);
                            $stock = $lot->stock - ($qty - $total);
                            $this->updateStockLote($lot->id, $stock);
                        }
                    }
                } else {
                    $data['sale_id'] = $id_sale;
                    $data['lote_id'] = $lote->id;
                    $data['qty'] = $qty;
                    $loteSale = LoteSale::create($data);
                    $lote->stock = $lote->stock - $qty;
                    $lote->save();
                }
                break;
            }
        }
    }

    public function updateStockLote($id, $qty)
    {
        $lote = ProductLote::find($id);
        if ($qty == 0) {
            $lote->status = 0;
        } else {
            $lote->status = 2;
        }

        $lote->stock = $qty;
        $lote->save();
    }

    public function sendMail(Request $request)
    {
        $data = $request->all();
        $lims_sale_data = Sale::find($data['sale_id']);
        $lims_product_sale_data = Product_Sale::where('sale_id', $data['sale_id'])->get();
        $lims_customer_data = Customer::find($lims_sale_data->customer_id);
        if ($lims_customer_data->email) {
            //collecting male data
            $mail_data['email'] = $lims_customer_data->email;
            $mail_data['reference_no'] = $lims_sale_data->reference_no;
            $mail_data['sale_status'] = $lims_sale_data->sale_status;
            $mail_data['payment_status'] = $lims_sale_data->payment_status;
            $mail_data['total_qty'] = $lims_sale_data->total_qty;
            $mail_data['total_price'] = $lims_sale_data->total_price;
            $mail_data['order_tax'] = $lims_sale_data->order_tax;
            $mail_data['order_tax_rate'] = $lims_sale_data->order_tax_rate;
            $mail_data['order_discount'] = $lims_sale_data->order_discount;
            $mail_data['shipping_cost'] = $lims_sale_data->shipping_cost;
            $mail_data['grand_total'] = $lims_sale_data->grand_total;
            $mail_data['paid_amount'] = $lims_sale_data->paid_amount;

            foreach ($lims_product_sale_data as $key => $product_sale_data) {
                $lims_product_data = Product::find($product_sale_data->product_id);
                if ($product_sale_data->variant_id) {
                    $variant_data = Variant::select('name')->find($product_sale_data->variant_id);
                    $mail_data['products'][$key] = $lims_product_data->name . ' [' . $variant_data->name . ']';
                } else {
                    $mail_data['products'][$key] = $lims_product_data->name;
                }

                if ($lims_product_data->type == 'digital') {
                    $mail_data['file'][$key] = url('/public/product/files') . '/' . $lims_product_data->file;
                } else {
                    $mail_data['file'][$key] = '';
                }

                if ($product_sale_data->sale_unit_id) {
                    $lims_unit_data = Unit::find($product_sale_data->sale_unit_id);
                    $mail_data['unit'][$key] = $lims_unit_data->unit_code;
                } else {
                    $mail_data['unit'][$key] = '';
                }

                $mail_data['qty'][$key] = $product_sale_data->qty;
                $mail_data['total'][$key] = $product_sale_data->qty;
            }

            try {
                Mail::send('mail.sale_details', $mail_data, function ($message) use ($mail_data) {
                    $message->to($mail_data['email'])->subject('Sale Details');
                });
                $message = 'Mail sent successfully';
            } catch (\Exception $e) {
                $message = 'Please setup your <a href="setting/mail_setting">mail setting</a> to send mail.';
            }
        } else {
            $message = 'Customer doesnt have email!';
        }

        return redirect()->back()->with('message', $message);
    }

    public function paypalSuccess(Request $request)
    {
        $lims_sale_data = Sale::latest()->first();
        $lims_payment_data = Payment::latest()->first();
        $lims_product_sale_data = Product_Sale::where('sale_id', $lims_sale_data->id)->get();
        //$provider = new ExpressCheckout;
        $token = $request->token;
        $payerID = $request->PayerID;
        $paypal_data['items'] = [];
        foreach ($lims_product_sale_data as $key => $product_sale_data) {
            $lims_product_data = Product::find($product_sale_data->product_id);
            $paypal_data['items'][] = [
                'name' => $lims_product_data->name,
                'price' => ($product_sale_data->total / $product_sale_data->qty),
                'qty' => $product_sale_data->qty,
            ];
        }
        $paypal_data['items'][] = [
            'name' => 'order tax',
            'price' => $lims_sale_data->order_tax,
            'qty' => 1,
        ];
        $paypal_data['items'][] = [
            'name' => 'order discount',
            'price' => $lims_sale_data->order_discount * (-1),
            'qty' => 1,
        ];
        $paypal_data['items'][] = [
            'name' => 'shipping cost',
            'price' => $lims_sale_data->shipping_cost,
            'qty' => 1,
        ];
        if ($lims_sale_data->grand_total != $lims_sale_data->paid_amount) {
            $paypal_data['items'][] = [
                'name' => 'Due',
                'price' => ($lims_sale_data->grand_total - $lims_sale_data->paid_amount) * (-1),
                'qty' => 1,
            ];
        }

        $paypal_data['invoice_id'] = $lims_payment_data->payment_reference;
        $paypal_data['invoice_description'] = "Reference: {$paypal_data['invoice_id']}";
        $paypal_data['return_url'] = url('/sale/paypalSuccess');
        $paypal_data['cancel_url'] = url('/sale/create');

        $total = 0;
        foreach ($paypal_data['items'] as $item) {
            $total += $item['price'] * $item['qty'];
        }

        $paypal_data['total'] = $lims_sale_data->paid_amount;
        //$response = $provider->getExpressCheckoutDetails($token);
        //$response = $provider->doExpressCheckoutPayment($paypal_data, $token, $payerID);
        $response['payment_id'] = 0;
        $response['transaction_id'] = 0;
        $data['payment_id'] = $lims_payment_data->id;
        $data['transaction_id'] = $response['PAYMENTINFO_0_TRANSACTIONID'];
        PaymentWithPaypal::create($data);
        return redirect('sales')->with('message', 'Pago creado con éxito');
    }

    public function paypalPaymentSuccess(Request $request, $id)
    {
        $lims_payment_data = Payment::find($id);
        //$provider = new ExpressCheckout;
        $token = $request->token;
        $payerID = $request->PayerID;
        $paypal_data['items'] = [];
        $paypal_data['items'][] = [
            'name' => 'Paid Amount',
            'price' => $lims_payment_data->amount,
            'qty' => 1,
        ];
        $paypal_data['invoice_id'] = $lims_payment_data->payment_reference;
        $paypal_data['invoice_description'] = "Reference: {$paypal_data['invoice_id']}";
        $paypal_data['return_url'] = url('/sale/paypalPaymentSuccess');
        $paypal_data['cancel_url'] = url('/sale');

        $total = 0;
        foreach ($paypal_data['items'] as $item) {
            $total += $item['price'] * $item['qty'];
        }

        $paypal_data['total'] = $total;
        //$response = $provider->getExpressCheckoutDetails($token);
        //$response = $provider->doExpressCheckoutPayment($paypal_data, $token, $payerID);
        $data['payment_id'] = $lims_payment_data->id;
        //$data['transaction_id'] = $response['PAYMENTINFO_0_TRANSACTIONID'];
        $data['transaction_id'] = 0;
        PaymentWithPaypal::create($data);
        return redirect('sales')->with('message', 'Pago creado con éxito');
    }

    public function getProduct($id, $id_customer)
    {
        $lims_pos_setting_data = PosSetting::latest()->first();
        if ($lims_pos_setting_data->user_category) {
            $listCategories = UserCategory::where("user_id", Auth::user()->id)->pluck('category_id');
        }
        $lims_product_warehouse_data = Product_Warehouse::where([
            ['warehouse_id', $id],
            ['qty', '>', 0],
        ])->whereNull('variant_id')->get();
        $lims_product_with_variant_warehouse_data = Product_Warehouse::where([
            ['warehouse_id', $id],
            ['qty', '>', 0],
        ])->whereNotNull('variant_id')->get();

        // Inicializar customer_data con precio por defecto si no hay cliente
        if ($id_customer != null && $id_customer != '') {
            $customer_data = Customer::select('id', 'price_type')->find($id_customer);
        }
        
        // Si no hay cliente o no se encontró, usar precio por defecto (price_type = 0)
        if (!isset($customer_data) || $customer_data == null) {
            $customer_data = (object)['id' => null, 'price_type' => 0];
        }

        $product_code = [];
        $product_name = [];
        $product_qty = [];
        $product_data = [];
        $product_price = [];
        $product_tax = [];
        $product_unit = [];
        $product_type = [];
        $product_id = [];
        $product_list = [];
        $qty_list = [];
        //product without variant
        foreach ($lims_product_warehouse_data as $product_warehouse) {
            if ($lims_pos_setting_data->user_category) {
                $lims_product_data = Product::where('id', $product_warehouse->product_id)
                    ->whereIn('category_id', $listCategories)->first();
            } else {
                $lims_product_data = Product::find($product_warehouse->product_id);
            }
            if ($lims_product_data->type != 'insumo') {
                $product_qty[] = $product_warehouse->qty;
                $product_code[] = $lims_product_data->code;
                $product_name[] = $lims_product_data->name;
                $product_type[] = $lims_product_data->type;
                $product_id[] = $lims_product_data->id;
                $product_list[] = $lims_product_data->product_list;
                $qty_list[] = $lims_product_data->qty_list;
                $product_price[] = $this->getPriceByProduct($lims_product_data, null, $customer_data->price_type);
                $product_tax[] = $this->getTaxByProduct($lims_product_data);
                $product_unit[] = $this->getUnitByProduct($lims_product_data);
            }
        }
        //product with variant
        foreach ($lims_product_with_variant_warehouse_data as $product_warehouse) {
            if ($lims_pos_setting_data->user_category) {
                $lims_product_data = Product::where('id', $product_warehouse->product_id)
                    ->whereIn('category_id', $listCategories)->first();
            } else {
                $lims_product_data = Product::find($product_warehouse->product_id);
            }
            $lims_product_variant_data = ProductVariant::select('item_code', 'additional_price')->FindExactProduct($product_warehouse->product_id, $product_warehouse->variant_id)->first();
            if ($lims_product_data->type != 'insumo') {
                $product_qty[] = $product_warehouse->qty;
                $product_code[] = $lims_product_variant_data->item_code;
                $product_name[] = $lims_product_data->name;
                $product_type[] = $lims_product_data->type;
                $product_id[] = $lims_product_data->id;
                $product_list[] = $lims_product_data->product_list;
                $qty_list[] = $lims_product_data->qty_list;
                $product_price[] = $this->getPriceByProduct($lims_product_data, $lims_product_variant_data, $customer_data->price_type);
                $product_tax[] = $this->getTaxByProduct($lims_product_data);
                $product_unit[] = $this->getUnitByProduct($lims_product_data);
            }
        }
        //retrieve product with type of digital and combo
        if ($lims_pos_setting_data->user_category) {
            $lims_product_data = Product::whereNotIn('type', ['standard'])->where('is_active', true)
                ->whereIn('category_id', $listCategories)->get();
        } else {
            $lims_product_data = Product::whereNotIn('type', ['standard'])->where('is_active', true)->get();
        }
        foreach ($lims_product_data as $product) {
            if ($product->type != 'insumo') {
                $product_qty[] = $product->qty;
                $product_code[] = $product->code;
                $product_name[] = $product->name;
                $product_type[] = $product->type;
                $product_id[] = $product->id;
                $product_list[] = $product->product_list;
                $qty_list[] = $product->qty_list;
                $product_tax[] = $this->getTaxByProduct($product);
                $product_unit[] = $this->getUnitByProduct($product);

                switch ($customer_data->price_type) {
                    case 0:
                        $product_price[] = $product->price;
                        break;
                    case 1:
                        $product_price[] = $product->price_a;
                        break;
                    case 2:
                        $product_price[] = $product->price_b;
                        break;
                    case 3:
                        $product_price[] = $product->price_c;
                        break;
                    default:
                        $product_price[] = $product->price;
                }
            }
        }
        $product_data = [$product_code, $product_name, $product_qty, $product_type, $product_id, $product_list, $qty_list, $product_price, $product_tax, $product_unit];
        return $product_data;
    }

    public function searchProduct(Request $request)
    {
        $data = $request->all();
        $lims_pos_setting_data = PosSetting::latest()->first();
        // Modo proforma: true = listar todos, false = solo con stock o digitales
        $modo_proforma = isset($data['modo_proforma']) && $data['modo_proforma'] == 'true';
        
        Log::info('[searchProduct] Parámetros recibidos:', [
            'term' => $data['term'] ?? 'no term',
            'id_customer' => $data['id_customer'] ?? 'no customer',
            'id_warehouse' => $data['id_warehouse'] ?? 'no warehouse',
            'modo_proforma' => $data['modo_proforma'] ?? 'no enviado',
            'modo_proforma_bool' => $modo_proforma
        ]);
        
        if ($lims_pos_setting_data->user_category) {
            $listCategories = UserCategory::where("user_id", Auth::user()->id)->pluck('category_id');
        }
        
        // Inicializar customer_data con precio por defecto si no hay cliente
        if ($data['id_customer'] != null && $data['id_customer'] != '') {
            $customer_data = Customer::select('id', 'price_type')->find($data['id_customer']);
        }
        
        // Si no hay cliente o no se encontró, usar precio por defecto (price_type = 0)
        if (!isset($customer_data) || $customer_data == null) {
            $customer_data = (object)['id' => null, 'price_type' => 0];
        }
        
        Log::info('[searchProduct] Customer price_type:', ['price_type' => $customer_data->price_type]);

        if ($lims_pos_setting_data->user_category) {
            $query_products = Product_Warehouse::select(
                'product_warehouse.qty',
                'product_warehouse.variant_id',
                'products.code',
                'products.name',
                'products.type',
                'products.id',
                'products.product_list',
                'products.qty_list',
                'products.tax_id',
                'products.tax_method',
                'products.is_variant',
                'products.unit_id',
                'products.sale_unit_id',
                'products.promotion',
                'products.promotion_price',
                'products.last_date',
                'products.price',
                'products.price_a',
                'products.price_b',
                'products.price_c',
                'products.is_active'
            )
                ->join('products', 'products.id', '=', 'product_warehouse.product_id')
                ->where('product_warehouse.warehouse_id', $data['id_warehouse'])
                // En modo proforma no filtrar por stock, sin modo proforma sí filtrar
                ->when(!$modo_proforma, function($query) {
                    return $query->where('product_warehouse.qty', '>', 0);
                })
                ->where('products.is_active', true)
                ->where('products.type', '=', 'standard')
                ->whereIn('products.category_id', $listCategories)
                ->where(function ($query) use ($data) {
                    $query->where('products.code', 'LIKE', "%{$data['term']}%")
                        ->orWhere('products.name', 'LIKE', "%{$data['term']}%");
                })
                ->orderBy('products.name', 'ASC')
                ->limit(100);
            $lims_products = $query_products->get();
        } else {
            $query_products = Product_Warehouse::select(
                'product_warehouse.qty',
                'product_warehouse.variant_id',
                'products.code',
                'products.name',
                'products.type',
                'products.id',
                'products.product_list',
                'products.qty_list',
                'products.tax_id',
                'products.tax_method',
                'products.is_variant',
                'products.unit_id',
                'products.sale_unit_id',
                'products.promotion',
                'products.promotion_price',
                'products.last_date',
                'products.price',
                'products.price_a',
                'products.price_b',
                'products.price_c',
                'products.is_active'
            )
                ->join('products', 'products.id', '=', 'product_warehouse.product_id')
                ->where('product_warehouse.warehouse_id', $data['id_warehouse'])
                // En modo proforma no filtrar por stock, sin modo proforma sí filtrar
                ->when(!$modo_proforma, function($query) {
                    return $query->where('product_warehouse.qty', '>', 0);
                })
                ->where('products.is_active', true)
                ->where('products.type', '=', 'standard')
                ->where(function ($query) use ($data) {
                    $query->where('products.code', 'LIKE', "%{$data['term']}%")
                        ->orWhere('products.name', 'LIKE', "%{$data['term']}%");
                })
                ->orderBy('products.name', 'ASC')
                ->limit(100);
            $lims_products = $query_products->get();
        }


        if ($lims_pos_setting_data->user_category) {
            $list_products_all = Product::select(
                'qty',
                'is_variant as variant_id',
                'code',
                'name',
                'type',
                'id',
                'product_list',
                'qty_list',
                'tax_id',
                'tax_method',
                'is_variant',
                'unit_id',
                'sale_unit_id',
                'promotion',
                'promotion_price',
                'last_date',
                'price',
                'price_a',
                'price_b',
                'price_c',
                'is_active'
            )
                ->where('is_active', true)
                ->whereNotIn('type', ['insumo', 'standard'])
                ->whereIn('category_id', $listCategories)
                ->where(function ($query) use ($data) {
                    $query->where('code', 'LIKE', "%{$data['term']}%")
                        ->orWhere('name', 'LIKE', "%{$data['term']}%");
                })
                ->orderBy('name', 'ASC')
                ->limit(100)
                ->get();
        } else {
            $list_products_all = Product::select(
                'qty',
                'is_variant as variant_id',
                'code',
                'name',
                'type',
                'id',
                'product_list',
                'qty_list',
                'tax_id',
                'tax_method',
                'is_variant',
                'unit_id',
                'sale_unit_id',
                'promotion',
                'promotion_price',
                'last_date',
                'price',
                'price_a',
                'price_b',
                'price_c',
                'is_active'
            )
                ->where('is_active', true)
                ->whereNotIn('type', ['insumo', 'standard'])
                ->where(function ($query) use ($data) {
                    $query->where('code', 'LIKE', "%{$data['term']}%")
                        ->orWhere('name', 'LIKE', "%{$data['term']}%");
                })
                ->orderBy('name', 'ASC')
                ->limit(100)
                ->get();
        }
        //$query1->union($query->toBase())->groupBy('id', 'code',  'name')->orderBy('name', 'ASC')->limit(100);
        //$list_products_all = $query->union($query1)->get();
        foreach ($list_products_all as $key => $lims_product_data) {
            if ($lims_product_data->is_active == 1) {
                // Sin modo proforma: filtrar productos standard sin stock
                // Con modo proforma: permitir todos
                // Siempre permitir productos digitales sin importar stock
                if (!$modo_proforma && $lims_product_data->type == 'standard' && $lims_product_data->qty < 1) {
                    $list_products_all->forget($key);
                }
                if ($lims_product_data->is_variant) {
                    $lims_product_variant_data = ProductVariant::select('item_code', 'additional_price')->FindExactProduct($lims_product_data->product_id, $lims_product_data->variant_id)->first();
                    $lims_product_data->code = $lims_product_variant_data->item_code;
                    $lims_product_data->price_value = $this->getPriceByProduct($lims_product_data, $lims_product_variant_data, $customer_data->price_type);
                } else {
                    if ($lims_product_data->type != 'standard') {
                        switch ($customer_data->price_type) {
                            case 0:
                                $lims_product_data->price_value = $lims_product_data->price;
                                break;
                            case 1:
                                $lims_product_data->price_value = $lims_product_data->price_a;
                                break;
                            case 2:
                                $lims_product_data->price_value = $lims_product_data->price_b;
                                break;
                            case 3:
                                $lims_product_data->price_value = $lims_product_data->price_c;
                                break;
                            default:
                                $lims_product_data->price_value = $lims_product_data->price;
                        }
                    } else
                        $lims_product_data->price_value = $this->getPriceByProduct($lims_product_data, null, $customer_data->price_type);
                }
                $lims_product_data->tax_value = $this->getTaxByProduct($lims_product_data);
                $lims_product_data->unit_value = $this->getUnitByProduct($lims_product_data);
                $lims_product_data->price_value = number_format($lims_product_data->price_value, $lims_pos_setting_data->cant_decimal, '.', '');
            } else {
                $list_products_all->forget($key);
            }
        }

        foreach ($lims_products as $key => $lims_product_data) {
            if ($lims_product_data->is_active == 1) {
                // Sin modo proforma: filtrar productos standard sin stock
                // Con modo proforma: permitir todos
                if (!$modo_proforma && $lims_product_data->type == 'standard' && $lims_product_data->qty < 1) {
                    $lims_products->forget($key);
                }
                if ($lims_product_data->is_variant) {
                    $lims_product_variant_data = ProductVariant::select('item_code', 'additional_price')->FindExactProduct($lims_product_data->product_id, $lims_product_data->variant_id)->first();
                    $lims_product_data->code = $lims_product_variant_data->item_code;
                    $lims_product_data->price_value = $this->getPriceByProduct($lims_product_data, $lims_product_variant_data, $customer_data->price_type);
                } else {
                    if ($lims_product_data->type != 'standard') {
                        switch ($customer_data->price_type) {
                            case 0:
                                $lims_product_data->price_value = $lims_product_data->price;
                                break;
                            case 1:
                                $lims_product_data->price_value = $lims_product_data->price_a;
                                break;
                            case 2:
                                $lims_product_data->price_value = $lims_product_data->price_b;
                                break;
                            case 3:
                                $lims_product_data->price_value = $lims_product_data->price_c;
                                break;
                            default:
                                $lims_product_data->price_value = $lims_product_data->price;
                        }
                    } else
                        $lims_product_data->price_value = $this->getPriceByProduct($lims_product_data, null, $customer_data->price_type);
                }
                $lims_product_data->tax_value = $this->getTaxByProduct($lims_product_data);
                $lims_product_data->unit_value = $this->getUnitByProduct($lims_product_data);
                $lims_product_data->price_value = number_format($lims_product_data->price_value, $lims_pos_setting_data->cant_decimal, '.', '');
            } else {
                $lims_products->forget($key);
            }
        }
        // Unir $list_products_all con $lims_products
        $list_products_all = $list_products_all->concat($lims_products)->unique('id')->values();
        
        Log::info('[searchProduct] Resultados:', [
            'total_productos' => $list_products_all->count(),
            'modo_proforma' => $modo_proforma,
            'productos' => $list_products_all->map(function($p) {
                return [
                    'code' => $p->code,
                    'name' => $p->name,
                    'qty' => $p->qty,
                    'type' => $p->type
                ];
            })->toArray()
        ]);
        
        return $list_products_all;
    }

    public function productFinish_Stock($code, $warehouse_id)
    {
        $producto_data = Product::select('id', 'name', 'product_list', 'qty_list')->where([['code', $code], ['type', 'producto_terminado'], ['is_active', true]])->orwhere([['code', $code], ['type', 'combo'], ['is_active', true]])->first();
        $sold = false;
        $insumo = [];
        if ($producto_data != null) {
            $product_list = explode(",", $producto_data->product_list);
            $qty_list = explode(",", $producto_data->qty_list);

            foreach ($product_list as $key => $child_id) {
                $child_data = Product::select('id', 'code', 'name', 'qty', 'type')->find($child_id);
                $child_warehouse_data = Product_Warehouse::select('id', 'qty')->where([
                    ['product_id', $child_id],
                    ['warehouse_id', $warehouse_id],
                ])->first();

                if ($child_warehouse_data != null && $child_warehouse_data->qty < $qty_list[$key]) {
                    $sold = true;
                }
                if ($child_warehouse_data) {
                    $child_data->qty = $child_warehouse_data->qty;
                } else {
                    $child_data->qty = 0;
                }

                $insumo[] = $child_data;
            }
        }
        $product_data = [$sold, $insumo];
        return $product_data;
    }

    public function posSale()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('sales-add')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission) {
                $all_permission[] = $permission->name;
            }

            if (empty($all_permission)) {
                $all_permission[] = 'dummy text';
            }

            $lims_pos_setting_data = PosSetting::latest()->first();
            if (Auth::user()->biller) {
                $user = Auth::user();
                $biller_data = Biller::select('id', 'account_id', 'warehouse_id', 'customer_id')->find($user->biller_id);
                $lims_account_data = Account::select('id', 'name', 'account_no')->find($biller_data->account_id);
            } else {
                $biller_data = Biller::select('id', 'account_id', 'warehouse_id', 'customer_id')->find($lims_pos_setting_data->biller_id);
                $lims_account_data = Account::select('id', 'name', 'account_no')->where('is_default', true)->first();
            }

            $account_data = $lims_account_data->name . " [" . $lims_account_data->account_no . "]";
            $lims_cashier_data = Cashier::select('id', 'end_date')->where([['account_id', $biller_data->account_id], ['is_active', true]])->first();
            if ($lims_cashier_data != null && $lims_cashier_data->end_date == null) {
                $lims_customer_group_all = CustomerGroup::select('id', 'name')->where('is_active', true)->get();
                $lims_warehouse_list = Warehouse::select('id', 'name')->where('is_active', true)->get();
                $lims_warehouse_selects = array();
                if (Auth::user()->biller_id) {
                    $warehouse_current_biller = Warehouse::find($biller_data->warehouse_id);
                    $lims_warehouse_filter = Biller_Warehouses::where('biller_id', $biller_data->id)->get();
                    foreach ($lims_warehouse_filter as $warehouse_select) {
                        if ($warehouse_current_biller->id != $warehouse_select->warehouse_id)
                            $lims_warehouse_selects[] = $warehouse_select->warehouse;
                    }
                    $lims_warehouse_selects[] = $warehouse_current_biller;
                }
                $lims_biller_list = Biller::select('id', 'name', 'company_name')->where('is_active', true)->get();
                $lims_tax_list = Tax::where('is_active', true)->get();
                $lims_methodpay_list = MethodPayment::select('id', 'name')->where('cbx', true)->get();
                $lims_product_list = Product::select('id', 'name', 'code', 'image')->ActiveFeatured()->where('type', '!=', 'insumo')->whereNull('is_variant')->orderBy('id', 'asc')->get();
                foreach ($lims_product_list as $key => $product) {
                    $images = explode(",", $product->image);
                    $product->base_image = $images[0];
                    if ($product->type == 'insumo') {
                        unset($lims_product_list[$key]);
                    }
                }
                $lims_product_list_with_variant = Product::select('id', 'name', 'code', 'image')->ActiveFeatured()->whereNotNull('is_variant')->get();

                foreach ($lims_product_list_with_variant as $product) {
                    $images = explode(",", $product->image);
                    $product->base_image = $images[0];
                    $lims_product_variant_data = $product->variant()->orderBy('position')->get();
                    $main_name = $product->name;
                    $temp_arr = [];
                    foreach ($lims_product_variant_data as $key => $variant) {
                        $product->name = $main_name . ' [' . $variant->name . ']';
                        $product->code = $variant->pivot['item_code'];
                        $lims_product_list[] = clone ($product);
                    }
                }

                $product_number = count($lims_product_list);
                $lims_brand_list = Brand::select('id', 'title', 'image')->where('is_active', true)->get();
                if ($lims_pos_setting_data->user_category) {
                    $lims_category_list = Category::select('categories.id', 'categories.name', 'categories.image')
                        ->join('user_category', 'categories.id', '=', 'user_category.category_id')
                        ->where('user_category.user_id', '=', Auth::user()->id)
                        ->where('categories.is_active', '=', true)->get();
                } else {
                    $lims_category_list = Category::select('id', 'name', 'image')->where('is_active', true)->get();
                }
                if (Auth::user()->role_id > 2) {
                    $recent_sale = Sale::select('id', 'reference_no', 'grand_total', 'customer_id', 'created_at')->where([
                        ['sale_status', 1],
                        ['user_id', Auth::id()],
                    ])->orderBy('id', 'desc')->take(20)->get();
                    $recent_draft = Sale::where([
                        ['sale_status', 3],
                        ['user_id', Auth::id()],
                    ])->orderBy('id', 'desc')->take(20)->get();
                } else {
                    $recent_sale = Sale::select('id', 'reference_no', 'grand_total', 'customer_id', 'created_at')
                        ->where('sale_status', 1)->orderBy('id', 'desc')->take(20)->get();
                    $recent_draft = Sale::where('sale_status', 3)->orderBy('id', 'desc')->take(20)->get();
                }
                $lims_coupon_list = Coupon::where('is_active', true)->get();
                $flag = 0;

                $lista_documentos = SiatParametricaVario::where('tipo_clasificador', 'tipoDocumentoIdentidad')->get();
                $lista_metodo_pago = SiatParametricaVario::where('tipo_clasificador', 'tipoMetodoPago')->orderBy('codigo_clasificador', 'ASC')->get();
                $customer_data = Customer::select("id", "name")->find($lims_pos_setting_data->customer_id);
                $lims_sucursal_all = SiatSucursal::where('estado', 1)->get();
                return view('sale.pos', compact('all_permission', 'lims_customer_group_all', 'lims_warehouse_list', 'lims_product_list', 'product_number', 'lims_tax_list', 'lims_biller_list', 'lims_pos_setting_data', 'lims_brand_list', 'lims_category_list', 'recent_sale', 'recent_draft', 'lims_coupon_list', 'flag', 'lims_methodpay_list', 'biller_data', 'account_data', 'lista_documentos', 'lista_metodo_pago', 'customer_data', 'lims_sucursal_all', 'lims_warehouse_selects'));
            } else {
                if (Auth::user()->role_id > 2 && Auth::user()->biller) {
                    $lims_biller_list[] = Auth::user()->biller;
                } else {
                    $lims_biller_list = Biller::where('is_active', true)->get();
                }
                return view('sale.cashier_open', compact('lims_biller_list', 'lims_account_data', 'biller_data'));
            }
        } else {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }
    }

    public function getProductByFilter($category_id, $brand_id)
    {
        $data = [];
        if (($category_id != 0) && ($brand_id != 0)) {
            $lims_product_list = DB::table('products')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->where([
                    ['products.is_active', true],
                    ['products.category_id', $category_id],
                    ['brand_id', $brand_id],
                    ['products.type', '!=', 'insumo'],
                ])->orWhere([
                    ['categories.parent_id', $category_id],
                    ['products.is_active', true],
                    ['brand_id', $brand_id],
                ])->select('products.name', 'products.code', 'products.image')->orderBy('products.name', 'asc')->get();
        } elseif (($category_id != 0) && ($brand_id == 0)) {
            $lims_product_list = DB::table('products')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->where([
                    ['products.is_active', true],
                    ['products.category_id', $category_id],
                    ['products.type', '!=', 'insumo'],
                ])->orWhere([
                    ['categories.parent_id', $category_id],
                    ['products.is_active', true],
                ])->select('products.id', 'products.name', 'products.code', 'products.image', 'products.is_variant')->orderBy('products.name', 'asc')->get();
        } elseif (($category_id == 0) && ($brand_id != 0)) {
            $lims_product_list = Product::where([
                ['brand_id', $brand_id],
                ['is_active', true],
                ['type', '!=', 'insumo'],
            ])
                ->select('products.id', 'products.name', 'products.code', 'products.image', 'products.is_variant')
                ->get();
        } else {
            $lims_product_list = Product::where([['is_active', true], ['type', '!=', 'insumo']])->orderBy('products.name', 'asc')->get();
        }

        $index = 0;
        foreach ($lims_product_list as $product) {
            if ($product->is_variant) {
                $lims_product_data = Product::select('id')->find($product->id);
                $lims_product_variant_data = $lims_product_data->variant()->orderBy('position')->get();
                foreach ($lims_product_variant_data as $key => $variant) {
                    $data['name'][$index] = $product->name . ' [' . $variant->name . ']';
                    $data['code'][$index] = $variant->pivot['item_code'];
                    $images = explode(",", $product->image);
                    $data['image'][$index] = $images[0];
                    $index++;
                }
            } else {
                $data['name'][$index] = $product->name;
                $data['code'][$index] = $product->code;
                $images = explode(",", $product->image);
                $data['image'][$index] = $images[0];
                $index++;
            }
        }
        return $data;
    }

    public function getFeatured()
    {
        $data = [];
        $lims_product_list = Product::where([
            ['is_active', true],
            ['featured', true],
            ['type', '!=', 'insumo'],
        ])->select('products.id', 'products.name', 'products.code', 'products.image', 'products.is_variant')
            ->orderBy('products.name', 'asc')->get();

        $index = 0;
        foreach ($lims_product_list as $product) {
            if ($product->is_variant) {
                $lims_product_data = Product::select('id')->find($product->id);
                $lims_product_variant_data = $lims_product_data->variant()->orderBy('position')->get();
                foreach ($lims_product_variant_data as $key => $variant) {
                    $data['name'][$index] = $product->name . ' [' . $variant->name . ']';
                    $data['code'][$index] = $variant->pivot['item_code'];
                    $images = explode(",", $product->image);
                    $data['image'][$index] = $images[0];
                    $index++;
                }
            } else {
                $data['name'][$index] = $product->name;
                $data['code'][$index] = $product->code;
                $images = explode(",", $product->image);
                $data['image'][$index] = $images[0];
                $index++;
            }
        }
        return $data;
    }

    public function getCustomerGroup($id)
    {
        $lims_customer_data = Customer::find($id);
        $lims_customer_group_data = CustomerGroup::find($lims_customer_data->customer_group_id);
        return $lims_customer_group_data->percentage;
    }

    public function limsProductSearch(Request $request)
    {
        $todayDate = date('Y-m-d');
        $product_code = explode(" ", $request['data'][0]);
        $customer_id = $request['data'][1];
        $product_variant_id = null;
        
        Log::info('[limsProductSearch] Búsqueda de producto:', [
            'product_code' => $product_code[0],
            'customer_id' => $customer_id,
            'modo_proforma' => $request->input('modo_proforma', 'no enviado')
        ]);
        
        // Inicializar customer_data con precio por defecto si no hay cliente
        if ($customer_id != null && $customer_id != '') {
            $customer_data = Customer::select('id', 'price_type')->find($customer_id);
        }
        
        // Si no hay cliente o no se encontró, usar precio por defecto (price_type = 0)
        if (!isset($customer_data) || $customer_data == null) {
            $customer_data = (object)['id' => null, 'price_type' => 0];
        }

        $lims_product_data = Product::where('code', $product_code[0])->where('is_active', true)->first();
        
        if (!$lims_product_data) {
            Log::warning('[limsProductSearch] Producto no encontrado:', ['code' => $product_code[0]]);
            return null;
        }
        
        Log::info('[limsProductSearch] Producto encontrado:', [
            'name' => $lims_product_data->name,
            'code' => $lims_product_data->code,
            'type' => $lims_product_data->type
        ]);
        if (!$lims_product_data) {
            return null;
        }
        if ($lims_product_data->is_variant) {
            $lims_product_data = Product::join('product_variants', 'products.id', 'product_variants.product_id')
                ->select('products.*', 'product_variants.id as product_variant_id', 'product_variants.item_code', 'product_variants.additional_price')
                ->where('product_variants.item_code', $product_code[0])->where('products.is_active', true)
                ->first();
            if ($lims_product_data == null) {
                return null;
            }
            $product_variant_id = isset($lims_product_data->product_variant_id) ? $lims_product_data->product_variant_id : null;
        }

        $product[] = $lims_product_data->name;
        if ($lims_product_data->is_variant) {
            $product[] = $lims_product_data->item_code;
            $lims_product_data->price += $lims_product_data->additional_price;
        } else {
            $product[] = $lims_product_data->code;
        }

        if ($lims_product_data->promotion && $todayDate <= $lims_product_data->last_date) {
            $product[] = $lims_product_data->promotion_price;
        } else {
            switch ($customer_data->price_type) {
                case 0:
                    $product[] = $lims_product_data->price;
                    break;
                case 1:
                    $product[] = $lims_product_data->price_a;
                    break;
                case 2:
                    $product[] = $lims_product_data->price_b;
                    break;
                case 3:
                    $product[] = $lims_product_data->price_c;
                    break;
                default:
                    $product[] = $lims_product_data->price;
            }
        }

        if ($lims_product_data->tax_id) {
            $lims_tax_data = Tax::find($lims_product_data->tax_id);
            $product[] = $lims_tax_data->rate;
            $product[] = $lims_tax_data->name;
        } else {
            $product[] = 0;
            $product[] = 'No Tax';
        }
        $product[] = $lims_product_data->tax_method;
        if ($lims_product_data->type == 'standard') {
            $units = Unit::where("base_unit", $lims_product_data->unit_id)
                ->orWhere('id', $lims_product_data->unit_id)
                ->get();
            $unit_name = array();
            $unit_operator = array();
            $unit_operation_value = array();
            foreach ($units as $unit) {
                $unitbase = Unit::find($unit->base_unit);
                if ($lims_product_data->sale_unit_id == $unit->id) {
                    array_unshift($unit_name, $unit->unit_name);
                    array_unshift($unit_operator, $unit->operator);
                    array_unshift($unit_operation_value, $unit->operation_value);
                } else {
                    $unit_name[] = $unitbase->unit_name;
                    $unit_operator[] = $unitbase->operator;
                    $unit_operation_value[] = $unitbase->operation_value;
                }
            }
            $product[] = implode(",", $unit_name) . ',';
            $product[] = implode(",", $unit_operator) . ',';
            $product[] = implode(",", $unit_operation_value) . ',';
        } else {
            $product[] = 'n/a' . ',';
            $product[] = 'n/a' . ',';
            $product[] = 'n/a' . ',';
        }
        $product[] = $lims_product_data->id;
        $product[] = $product_variant_id;
        if ($lims_product_data->courtesy == "FALSE") {
            $lims_product_data_courtesy = ProductAssociated::join('products', 'product_associated.product_courtesy_id', 'products.id')
                ->select('products.*', 'product_associated.id AS id_asoc')->where('product_associated.product_associated_id', $lims_product_data->id)->get();
            $product[] = $lims_product_data_courtesy;
        } else {
            $product[] = null;
        }
        if ($lims_product_data->type == "digital" || ($lims_product_data->type == "combo" && $lims_product_data->commission_percentage > 0)) {
            $lims_employees_list = Employee::select('id', 'name')->where([['is_active', true], ['contract_type', 'COMISION_UNICA']])->orWhere('contract_type', 'COMISION_POR_SERVICIOS')->get();
            if ($lims_product_data->type == "combo") {
                $product_list = explode(",", $lims_product_data->product_list);
                $containService = false;
                foreach ($product_list as $id) {
                    $pro = Product::select('id', 'type')->find($id);
                    if ($pro->type == "digital") {
                        $containService = true;
                    }
                }
                if ($containService) {
                    $product[] = $lims_employees_list;
                } else {
                    $product[] = null;
                }
            } else {
                $product[] = $lims_employees_list;
            }
        } else {
            $product[] = null;
        }
        $product[] = $lims_product_data->is_basicservice;
        if ($lims_product_data->type == "combo" || $lims_product_data->type == "producto_terminado") {
            $product[] = $lims_product_data->product_list;
            $product[] = $lims_product_data->qty_list;
        } else {
            $product[] = [];
            $product[] = [];
        }
        return $product;
    }

    public function getPriceByProduct($lims_product_data, $lims_product_variant_data, $customer)
    {
        $todayDate = date('Y-m-d');
        if ($lims_product_data->promotion && $todayDate <= $lims_product_data->last_date) {
            return $lims_product_data->promotion_price;
        } else {
            if ($lims_product_variant_data) {
                switch ($customer) {
                    case 0:
                        $lims_product_data->price += $lims_product_variant_data->additional_price;
                        break;
                    case 1:
                        $lims_product_data->price_a += $lims_product_variant_data->additional_price;
                        $lims_product_data->price = $lims_product_data->price_a;
                        break;
                    case 2:
                        $lims_product_data->price_b += $lims_product_variant_data->additional_price;
                        $lims_product_data->price = $lims_product_data->price_b;
                        break;
                    case 3:
                        $lims_product_data->price_c += $lims_product_variant_data->additional_price;
                        $lims_product_data->price = $lims_product_data->price_c;
                        break;
                    default:
                        $lims_product_data->price += $lims_product_variant_data->additional_price;
                        break;
                }
            } else {
                switch ($customer) {
                    case 0:
                        $lims_product_data->price;
                        break;
                    case 1:
                        $lims_product_data->price = $lims_product_data->price_a;
                        break;
                    case 2:
                        $lims_product_data->price = $lims_product_data->price_b;
                        break;
                    case 3:
                        $lims_product_data->price = $lims_product_data->price_c;
                        break;
                    default:
                        $lims_product_data->price;
                        break;
                }
            }
            return $lims_product_data->price;
        }
    }

    public function getTaxByProduct($lims_product_data)
    {
        $product = [];
        if ($lims_product_data->tax_id) {
            $lims_tax_data = Tax::find($lims_product_data->tax_id);
            array_push($product, $lims_tax_data->rate, $lims_tax_data->name);
        } else {
            array_push($product, 0, 'No Tax');
        }
        $product[] = $lims_product_data->tax_method;
        return $product;
    }

    public function getUnitByProduct($lims_product_data)
    {
        if ($lims_product_data->type == 'standard') {
            $units = Unit::where("base_unit", $lims_product_data->unit_id)
                ->orWhere('id', $lims_product_data->unit_id)
                ->get();
            $unit_name = array();
            $unit_operator = array();
            $unit_operation_value = array();
            foreach ($units as $unit) {
                if ($lims_product_data->sale_unit_id == $unit->id) {
                    array_unshift($unit_name, $unit->unit_name);
                    array_unshift($unit_operator, $unit->operator);
                    array_unshift($unit_operation_value, $unit->operation_value);
                } else {
                    $unit_name[] = $unit->unit_name;
                    $unit_operator[] = $unit->operator;
                    $unit_operation_value[] = $unit->operation_value;
                }
            }
            return [implode(",", $unit_name) . ',', implode(",", $unit_operator) . ',', implode(",", $unit_operation_value) . ','];
        } else {
            return ['n/a' . ',', 'n/a' . ',', 'n/a' . ','];
        }
    }

    public function getGiftCard($id_customer)
    {
        $gift_card = GiftCard::where("is_active", true)->where('customer_id', $id_customer)->where('amount', '>', 'expense')->whereDate('expired_date', '>=', date("Y-m-d"))->get(['id', 'card_no', 'amount', 'expense']);
        return json_encode($gift_card);
    }

    public function productSaleData($id)
    {
        $lims_product_sale_data = Product_Sale::where('sale_id', $id)->get();
        foreach ($lims_product_sale_data as $key => $product_sale_data) {
            $product = Product::find($product_sale_data->product_id);
            if ($product_sale_data->variant_id) {
                $lims_product_variant_data = ProductVariant::select('item_code')->FindExactProduct($product_sale_data->product_id, $product_sale_data->variant_id)->first();
                $product->code = $lims_product_variant_data->item_code;
            }
            $unit_data = Unit::find($product_sale_data->sale_unit_id);
            if ($unit_data) {
                $unit = $unit_data->unit_code;
            } else {
                $unit = '';
            }

            if ($product_sale_data->employee_id != null) {
                $employee_data = Employee::find($product_sale_data->employee_id);
                $product_sale[0][$key] = $product->name . ' [' . $product->code . '] - Realizado Por: ' . $employee_data->name;
            } else {
                $product_sale[0][$key] = $product->name . ' [' . $product->code . ']';
            }
            $product_sale[1][$key] = $product_sale_data->qty;
            $product_sale[2][$key] = $unit;
            $product_sale[3][$key] = $product_sale_data->tax;
            $product_sale[4][$key] = $product_sale_data->tax_rate;
            $product_sale[5][$key] = $product_sale_data->discount;
            $product_sale[6][$key] = $product_sale_data->total;
        }
        return $product_sale;
    }

    public function saleByCsv()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('sales-add')) {
            $lims_customer_list = Customer::where('is_active', true)->get();
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_biller_list = Biller::where('is_active', true)->get();
            $lims_tax_list = Tax::where('is_active', true)->get();

            return view('sale.import', compact('lims_customer_list', 'lims_warehouse_list', 'lims_biller_list', 'lims_tax_list'));
        } else {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }
    }

    public function importSale(Request $request)
    {
        //get the file
        $upload = $request->file('file');
        $ext = pathinfo($upload->getClientOriginalName(), PATHINFO_EXTENSION);
        //checking if this is a CSV file
        if ($ext != 'csv') {
            return redirect()->back()->with('message', 'Please upload a CSV file');
        }

        $filePath = $upload->getRealPath();
        $file_handle = fopen($filePath, 'r');
        $i = 0;
        //validate the file
        while (!feof($file_handle)) {
            $current_line = fgetcsv($file_handle);
            if ($current_line && $i > 0) {
                $product_data[] = Product::where('code', $current_line[0])->first();
                if (!$product_data[$i - 1]) {
                    return redirect()->back()->with('message', 'Product does not exist!');
                }

                $unit[] = Unit::where('unit_code', $current_line[2])->first();
                if (!$unit[$i - 1] && $current_line[2] == 'n/a') {
                    $unit[$i - 1] = 'n/a';
                } elseif (!$unit[$i - 1]) {
                    return redirect()->back()->with('message', 'Sale unit does not exist!');
                }
                if (strtolower($current_line[5]) != "no tax") {
                    $tax[] = Tax::where('name', $current_line[5])->first();
                    if (!$tax[$i - 1]) {
                        return redirect()->back()->with('message', 'Tax name does not exist!');
                    }
                } else {
                    $tax[$i - 1]['rate'] = 0;
                }

                $qty[] = $current_line[1];
                $price[] = $current_line[3];
                $discount[] = $current_line[4];
            }
            $i++;
        }
        //return $unit;
        $last_ref = Sale::get()->last();
        if ($last_ref != null) {
            $nros = explode("-", $last_ref['reference_no']);
            $nro = ltrim($nros[1], "0");
            $nro++;
            $nro = str_pad($nro, 8, "0", STR_PAD_LEFT);
        } else {
            $nro = str_pad(1, 8, "0", STR_PAD_LEFT);
        }
        $data = $request->except('document');
        $data['reference_no'] = 'NVR' . $nro;
        $data['user_id'] = Auth::user()->id;
        $document = $request->document;
        if ($document) {
            $v = Validator::make(
                [
                    'extension' => strtolower($request->document->getClientOriginalExtension()),
                ],
                [
                    'extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt',
                ]
            );
            if ($v->fails()) {
                return redirect()->back()->withErrors($v->errors());
            }

            $ext = pathinfo($document->getClientOriginalName(), PATHINFO_EXTENSION);
            $documentName = $data['reference_no'] . '.' . $ext;
            $document->move('public/documents/sale', $documentName);
            $data['document'] = $documentName;
        }
        $item = 0;
        $grand_total = $data['shipping_cost'];
        Sale::create($data);
        $lims_sale_data = Sale::latest()->first();
        $lims_customer_data = Customer::find($lims_sale_data->customer_id);

        foreach ($product_data as $key => $product) {
            if ($product['tax_method'] == 1) {
                $net_unit_price = $price[$key] - $discount[$key];
                $product_tax = $net_unit_price * ($tax[$key]['rate'] / 100) * $qty[$key];
                $total = ($net_unit_price * $qty[$key]) + $product_tax;
            } elseif ($product['tax_method'] == 2) {
                $net_unit_price = (100 / (100 + $tax[$key]['rate'])) * ($price[$key] - $discount[$key]);
                $product_tax = ($price[$key] - $discount[$key] - $net_unit_price) * $qty[$key];
                $total = ($price[$key] - $discount[$key]) * $qty[$key];
            }
            if ($data['sale_status'] == 1 && $unit[$key] != 'n/a') {
                $sale_unit_id = $unit[$key]['id'];
                if ($unit[$key]['operator'] == '*') {
                    $quantity = $qty[$key] * $unit[$key]['operation_value'];
                } elseif ($unit[$key]['operator'] == '/') {
                    $quantity = $qty[$key] / $unit[$key]['operation_value'];
                }

                $product['qty'] -= $quantity;
                $product_warehouse = Product_Warehouse::where([
                    ['product_id', $product['id']],
                    ['warehouse_id', $data['warehouse_id']],
                ])->first();
                $product_warehouse->qty -= $quantity;
                $product->save();
                $product_warehouse->save();
            } else {
                $sale_unit_id = 0;
            }

            //collecting mail data
            $mail_data['products'][$key] = $product['name'];
            if ($product['type'] == 'digital') {
                $mail_data['file'][$key] = url('/public/product/files') . '/' . $product['file'];
            } else {
                $mail_data['file'][$key] = '';
            }

            if ($sale_unit_id) {
                $mail_data['unit'][$key] = $unit[$key]['unit_code'];
            } else {
                $mail_data['unit'][$key] = '';
            }

            $product_sale = new Product_Sale();
            $product_sale->sale_id = $lims_sale_data->id;
            $product_sale->product_id = $product['id'];
            $product_sale->qty = $mail_data['qty'][$key] = $qty[$key];
            $product_sale->sale_unit_id = $sale_unit_id;
            $product_sale->net_unit_price = number_format((float) $net_unit_price, 2, '.', '');
            $product_sale->discount = $discount[$key] * $qty[$key];
            $product_sale->tax_rate = $tax[$key]['rate'];
            $product_sale->tax = number_format((float) $product_tax, 2, '.', '');
            $product_sale->total = $mail_data['total'][$key] = number_format((float) $total, 2, '.', '');
            $product_sale->save();
            $lims_sale_data->total_qty += $qty[$key];
            $lims_sale_data->total_discount += $discount[$key] * $qty[$key];
            $lims_sale_data->total_tax += number_format((float) $product_tax, 2, '.', '');
            $lims_sale_data->total_price += number_format((float) $total, 2, '.', '');
        }
        $lims_sale_data->item = $key + 1;
        $lims_sale_data->order_tax = ($lims_sale_data->total_price - $lims_sale_data->order_discount) * ($data['order_tax_rate'] / 100);
        $lims_sale_data->grand_total = ($lims_sale_data->total_price + $lims_sale_data->order_tax + $lims_sale_data->shipping_cost) - $lims_sale_data->order_discount;
        $lims_sale_data->save();
        $message = 'Sale imported successfully';
        if ($lims_customer_data->email) {
            //collecting male data
            $mail_data['email'] = $lims_customer_data->email;
            $mail_data['reference_no'] = $lims_sale_data->reference_no;
            $mail_data['sale_status'] = $lims_sale_data->sale_status;
            $mail_data['payment_status'] = $lims_sale_data->payment_status;
            $mail_data['total_qty'] = $lims_sale_data->total_qty;
            $mail_data['total_price'] = $lims_sale_data->total_price;
            $mail_data['order_tax'] = $lims_sale_data->order_tax;
            $mail_data['order_tax_rate'] = $lims_sale_data->order_tax_rate;
            $mail_data['order_discount'] = $lims_sale_data->order_discount;
            $mail_data['shipping_cost'] = $lims_sale_data->shipping_cost;
            $mail_data['grand_total'] = $lims_sale_data->grand_total;
            $mail_data['paid_amount'] = $lims_sale_data->paid_amount;
            if ($mail_data['email']) {
                try {
                    Mail::send('mail.sale_details', $mail_data, function ($message) use ($mail_data) {
                        $message->to($mail_data['email'])->subject('Sale Details');
                    });
                } catch (\Exception $e) {
                    $message = 'Sale imported successfully. Please setup your <a href="setting/mail_setting">mail setting</a> to send mail.';
                }
            }
        }
        return redirect('sales')->with('message', $message);
    }

    public function createSale($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('sales-edit')) {
            $lims_biller_list = Biller::where('is_active', true)->get();
            $lims_customer_list = Customer::where('is_active', true)->get();
            $lims_customer_group_all = CustomerGroup::where('is_active', true)->get();
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_tax_list = Tax::where('is_active', true)->get();
            $lims_sale_data = Sale::find($id);
            $lims_product_sale_data = Product_Sale::where('sale_id', $id)->get();
            $lims_product_list = Product::where([
                ['featured', 1],
                ['is_active', true],
            ])->get();
            foreach ($lims_product_list as $key => $product) {
                $images = explode(",", $product->image);
                $product->base_image = $images[0];
            }
            $product_number = count($lims_product_list);
            $lims_pos_setting_data = PosSetting::latest()->first();
            $lims_brand_list = Brand::where('is_active', true)->get();
            $lims_category_list = Category::where('is_active', true)->get();
            $lims_coupon_list = Coupon::where('is_active', true)->get();

            return view('sale.create_sale', compact('lims_biller_list', 'lims_customer_list', 'lims_warehouse_list', 'lims_tax_list', 'lims_sale_data', 'lims_product_sale_data', 'lims_pos_setting_data', 'lims_brand_list', 'lims_category_list', 'lims_coupon_list', 'lims_product_list', 'product_number', 'lims_customer_group_all'));
        } else {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }
    }

    public function edit($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('sales-edit')) {
            $lims_customer_list = Customer::where('is_active', true)->get();
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_biller_list = Biller::where('is_active', true)->get();
            $lims_tax_list = Tax::where('is_active', true)->get();
            $lims_sale_data = Sale::find($id);
            $lims_product_sale_data = Product_Sale::where('sale_id', $id)->get();
            $lims_employees_list = Employee::select('id', 'name')->where([['is_active', true], ['contract_type', 'COMISION_UNICA']])->orWhere('contract_type', 'COMISION_POR_SERVICIOS')->get();
            $lims_product_list_courtesy = ProductAssociated::join('products', 'product_associated.product_courtesy_id', 'products.id')->select('products.*', 'product_associated.id AS id_asoc')->groupBy('products.id')->get();
            return view('sale.edit', compact('lims_customer_list', 'lims_warehouse_list', 'lims_biller_list', 'lims_tax_list', 'lims_sale_data', 'lims_product_sale_data', 'lims_employees_list', 'lims_product_list_courtesy'));
        } else {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }
    }

    public function update(Request $request, $id)
    {
        $data = $request->except('document');
        //return dd($data);
        $document = $request->document;
        if ($document) {
            $v = Validator::make(
                [
                    'extension' => strtolower($request->document->getClientOriginalExtension()),
                ],
                [
                    'extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt',
                ]
            );
            if ($v->fails()) {
                return redirect()->back()->withErrors($v->errors());
            }

            $documentName = $document->getClientOriginalName();
            $document->move('public/sale/documents', $documentName);
            $data['document'] = $documentName;
        }
        $balance = $data['grand_total'] - $data['paid_amount'];
        if ($balance < 0 || $balance > 0) {
            $data['payment_status'] = 2;
        } else {
            $data['payment_status'] = 4;
        }

        $lims_sale_data = Sale::find($id);
        $lims_product_sale_data = Product_Sale::where('sale_id', $id)->get();
        $product_id = $data['product_id'];
        $product_code = $data['product_code'];
        $product_variant_id = $data['product_variant_id'];
        $qty = $data['qty'];
        $sale_unit = $data['sale_unit'];
        $net_unit_price = $data['net_unit_price'];
        $discount = $data['discount'];
        $tax_rate = $data['tax_rate'];
        $tax = $data['tax'];
        $total = $data['subtotal'];
        $employee = $data['employee'];
        $old_product_id = [];
        $old_employee_id = [];
        $product_sale = [];
        $saledate_id = [];
        foreach ($lims_product_sale_data as $key => $product_sale_data) {
            $old_product_id[] = $product_sale_data->product_id;
            $old_product_variant_id[] = null;
            $saledate_id[] = $product_sale_data->id;
            $old_employee_id[] = $product_sale_data->employee_id;
            $lims_product_data = Product::find($product_sale_data->product_id);

            if (
                ($lims_sale_data->sale_status == 1 || $data['sale_status'] == 4) && ($lims_product_data->type == 'combo' ||
                    $lims_product_data->type == 'producto_terminado')
            ) {
                $product_list = explode(",", $lims_product_data->product_list);
                $qty_list = explode(",", $lims_product_data->qty_list);

                foreach ($product_list as $index => $child_id) {
                    $child_data = Product::find($child_id);
                    if ($child_data->unit_id != 0 && $child_data->type != 'digital') {
                        $child_warehouse_data = Product_Warehouse::where([
                            ['product_id', $child_id],
                            ['warehouse_id', $lims_sale_data->warehouse_id],
                        ])->first();

                        $child_data->qty += $product_sale_data->qty * $qty_list[$index];
                        $child_warehouse_data->qty += $product_sale_data->qty * $qty_list[$index];
                        $child_data->save();
                        $child_warehouse_data->save();
                    }
                }
            } elseif (($lims_sale_data->sale_status == 1) && ($product_sale_data->sale_unit_id != 0)) {
                $old_product_qty = $product_sale_data->qty;
                $lims_sale_unit_data = Unit::find($product_sale_data->sale_unit_id);
                /* Anulado por cambios en conversiones de fraccionamiento
                if ($lims_sale_unit_data->operator == '*')
                $old_product_qty = $old_product_qty * $lims_sale_unit_data->operation_value;
                else
                $old_product_qty = $old_product_qty / $lims_sale_unit_data->operation_value;
                */
                $old_product_qty = $old_product_qty * 1;
                if ($product_sale_data->variant_id) {
                    $lims_product_variant_data = ProductVariant::select('id', 'qty')->FindExactProduct($product_sale_data->product_id, $product_sale_data->variant_id)->first();
                    $lims_product_warehouse_data = Product_Warehouse::FindProductWithVariant($product_sale_data->product_id, $product_sale_data->variant_id, $lims_sale_data->warehouse_id)
                        ->first();
                    $old_product_variant_id[$key] = $lims_product_variant_data->id;
                    $lims_product_variant_data->qty += $old_product_qty;
                    $lims_product_variant_data->save();
                } else {
                    $lims_product_warehouse_data = Product_Warehouse::FindProductWithoutVariant($product_sale_data->product_id, $lims_sale_data->warehouse_id)
                        ->first();
                }

                $lims_product_data->qty += $old_product_qty;
                $lims_product_warehouse_data->qty += $old_product_qty;
                $lims_product_data->save();
                $lims_product_warehouse_data->save();
            }
            if ($product_sale_data->variant_id && !(in_array($old_product_variant_id[$key], $product_variant_id))) {
                $product_sale_data->delete();
            } elseif (!(in_array($old_product_id[$key], $product_id))) {
                $product_sale_data->delete();
            }
        }
        foreach ($product_id as $key => $pro_id) {
            $lims_product_data = Product::find($pro_id);
            $product_sale['variant_id'] = null;
            if (
                ($lims_product_data->type == 'combo' || $lims_product_data->type == 'producto_terminado')
                && $data['sale_status'] == 1
            ) {
                $product_list = explode(",", $lims_product_data->product_list);
                $qty_list = explode(",", $lims_product_data->qty_list);
                /** Start  Update Stock Product */
                foreach ($product_list as $index => $child_id) {
                    $child_data = Product::find($child_id);
                    if ($child_data->unit_id != 0 && $child_data->type != 'digital') {
                        $child_warehouse_data = Product_Warehouse::where([
                            ['product_id', $child_id],
                            ['warehouse_id', $data['warehouse_id']],
                        ])->first();

                        $child_data->qty -= $qty[$key] * $qty_list[$index];
                        $child_warehouse_data->qty -= $qty[$key] * $qty_list[$index];

                        $child_data->save();
                        $child_warehouse_data->save();
                    }
                }
                /** End  Update Stock Product */
            }
            if ($sale_unit[$key] != 'n/a') {
                $lims_sale_unit_data = Unit::where('unit_name', $sale_unit[$key])->first();
                $sale_unit_id = $lims_sale_unit_data->id;
                if ($data['sale_status'] == 1) {
                    $new_product_qty = $qty[$key];
                    /* Anulado por cambios en conversiones de fraccionamiento
                    if ($lims_sale_unit_data->operator == '*') {
                    $new_product_qty = $new_product_qty * $lims_sale_unit_data->operation_value;
                    } else {
                    $new_product_qty = $new_product_qty / $lims_sale_unit_data->operation_value;
                    }*/
                    $new_product_qty = $new_product_qty * 1;
                    if ($lims_product_data->is_variant) {
                        $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')->FindExactProductWithCode($pro_id, $product_code[$key])->first();
                        $lims_product_warehouse_data = Product_Warehouse::FindProductWithVariant($pro_id, $lims_product_variant_data->variant_id, $data['warehouse_id'])
                            ->first();

                        $product_sale['variant_id'] = $lims_product_variant_data->variant_id;
                        $lims_product_variant_data->qty -= $new_product_qty;
                        $lims_product_variant_data->save();
                    } else {
                        $lims_product_warehouse_data = Product_Warehouse::FindProductWithoutVariant($pro_id, $data['warehouse_id'])
                            ->first();
                    }
                    $lims_product_data->qty -= $new_product_qty;
                    $lims_product_warehouse_data->qty -= $new_product_qty;
                    $lims_product_data->save();
                    $lims_product_warehouse_data->save();
                }
            } else {
                $sale_unit_id = 0;
            }

            //collecting mail data
            if ($product_sale['variant_id']) {
                $variant_data = Variant::select('name')->find($product_sale['variant_id']);
                $mail_data['products'][$key] = $lims_product_data->name . ' [' . $variant_data->name . ']';
            } else {
                $mail_data['products'][$key] = $lims_product_data->name;
            }

            if ($lims_product_data->type == 'digital') {
                $mail_data['file'][$key] = url('/public/product/files') . '/' . $lims_product_data->file;
            } else {
                $mail_data['file'][$key] = '';
            }

            if ($sale_unit_id) {
                $mail_data['unit'][$key] = $lims_sale_unit_data->unit_code;
            } else {
                $mail_data['unit'][$key] = '';
            }

            $product_sale['sale_id'] = $id;
            $product_sale['product_id'] = $pro_id;
            $product_sale['category_id'] = $lims_product_data->category_id;
            $product_sale['qty'] = $mail_data['qty'][$key] = $qty[$key];
            $product_sale['sale_unit_id'] = $sale_unit_id;
            $product_sale['net_unit_price'] = $net_unit_price[$key];
            $product_sale['discount'] = $discount[$key];
            $product_sale['tax_rate'] = $tax_rate[$key];
            $product_sale['tax'] = $tax[$key];
            $product_sale['total'] = $mail_data['total'][$key] = $total[$key];
            // Redondear valores monetarios del detalle a 2 decimales antes de guardar/actualizar
            $product_sale['net_unit_price'] = number_format((float) $product_sale['net_unit_price'], 2, '.', '');
            $product_sale['discount'] = number_format((float) $product_sale['discount'], 2, '.', '');
            $product_sale['tax'] = number_format((float) $product_sale['tax'], 2, '.', '');
            $product_sale['total'] = number_format((float) $product_sale['total'], 2, '.', '');
            if ($employee[$key] == null || $employee[$key] == 0) {
                $product_sale['employee_id'] = $old_employee_id[$key];
            } else {
                $product_sale['employee_id'] = $employee[$key];
            }

            if ($product_sale['variant_id'] && in_array($product_variant_id[$key], $old_product_variant_id)) {
                Product_Sale::where([
                    ['product_id', $pro_id],
                    ['variant_id', $product_sale['variant_id']],
                    ['sale_id', $id],
                ])->update($product_sale);
            } elseif ($product_sale['variant_id'] === null && (in_array($pro_id, $old_product_id)) && ($employee[$key] == 0 && $old_employee_id[$key] == 0)) {
                Product_Sale::where([
                    ['sale_id', $id],
                    ['product_id', $pro_id],
                ])->update($product_sale);
            } elseif ($employee[$key] != null || $employee[$key] != 0) {
                Product_Sale::find($saledate_id[$key])->update($product_sale);
            } else {
                Product_Sale::create($product_sale);
            }
        }
        // Normalizar/Redondear montos a 2 decimales antes de actualizar la venta
        $round_keys = ['total_price', 'order_tax', 'order_discount', 'shipping_cost', 'grand_total', 'paid_amount'];
        foreach ($round_keys as $k) {
            if (isset($data[$k])) {
                $data[$k] = number_format((float) $data[$k], 2, '.', '');
            }
        }
        $lims_sale_data->update($data);
        $lims_customer_data = Customer::find($data['customer_id']);
        $message = 'Venta actualizada con éxito';
        //collecting mail data
        if ($lims_customer_data->email) {
            $mail_data['email'] = $lims_customer_data->email;
            $mail_data['reference_no'] = $lims_sale_data->reference_no;
            $mail_data['sale_status'] = $lims_sale_data->sale_status;
            $mail_data['payment_status'] = $lims_sale_data->payment_status;
            $mail_data['total_qty'] = $lims_sale_data->total_qty;
            $mail_data['total_price'] = $lims_sale_data->total_price;
            $mail_data['order_tax'] = $lims_sale_data->order_tax;
            $mail_data['order_tax_rate'] = $lims_sale_data->order_tax_rate;
            $mail_data['order_discount'] = $lims_sale_data->order_discount;
            $mail_data['shipping_cost'] = $lims_sale_data->shipping_cost;
            $mail_data['grand_total'] = $lims_sale_data->grand_total;
            $mail_data['paid_amount'] = $lims_sale_data->paid_amount;
            if ($mail_data['email']) {
                try {
                    Mail::send('mail.sale_details', $mail_data, function ($message) use ($mail_data) {
                        $message->to($mail_data['email'])->subject('Sale Details');
                    });
                } catch (\Exception $e) {
                    $message = 'Venta actualizada con éxito. Por favor configure su <a href="setting/mail_setting">Configuración de correo</a> para enviar correos.';
                }
            }
        }

        return redirect('sales')->with('message', $message);
    }

    public function genInvoice($id)
    {
        $lims_sale_data = Sale::find($id);
        $lims_product_sale_data = Product_Sale::where('sale_id', $id)->get();
        $lims_biller_data = Biller::find($lims_sale_data->biller_id);
        $lims_warehouse_data = Warehouse::find($lims_sale_data->warehouse_id);
        $lims_customer_data = Customer::find($lims_sale_data->customer_id);
        $lims_payment_data = Payment::where('sale_id', $id)->get();
        $lims_pos_setting_data = PosSetting::latest()->first();
        $formato_fecha = GeneralSetting::first()->date_format;
        $lims_sale_data->setAttribute('formato_fecha', "$formato_fecha H:i:s");

        $numberToWords = new NumberToWords();
        if (\App::getLocale() == 'ar' || \App::getLocale() == 'hi' || \App::getLocale() == 'vi' || \App::getLocale() == 'en-gb') {
            $numberTransformer = $numberToWords->getNumberTransformer('en');
        } else {
            $numberTransformer = $numberToWords->getNumberTransformer(\App::getLocale());
        }

        $cadenaCentavos = $this->obtenerParteDecimalLiteral($lims_sale_data->grand_total);

        $numberInWords = $numberTransformer->toWords($lims_sale_data->grand_total);
        if ($lims_pos_setting_data->type_print == 2) {
            return view('sale.invoice', compact('lims_sale_data', 'lims_product_sale_data', 'lims_biller_data', 'lims_warehouse_data', 'lims_customer_data', 'lims_payment_data', 'numberInWords', 'cadenaCentavos'));
        } else if ($lims_pos_setting_data->type_print == 3) {
            return view('sale.invoicemiddle', compact('lims_sale_data', 'lims_product_sale_data', 'lims_biller_data', 'lims_warehouse_data', 'lims_customer_data', 'lims_payment_data', 'numberInWords', 'cadenaCentavos'));
        } else if ($lims_pos_setting_data->type_print == 4) {
            //return view('sale.invoicemiddlev2', compact('lims_sale_data', 'lims_product_sale_data', 'lims_biller_data', 'lims_warehouse_data', 'lims_customer_data', 'lims_payment_data', 'numberInWords'));
            view()->share('sale.invoicemiddlev2', compact('lims_sale_data', 'lims_product_sale_data', 'lims_biller_data', 'lims_warehouse_data', 'lims_customer_data', 'lims_payment_data', 'numberInWords', 'cadenaCentavos'));
            $pdf = Pdf::loadView('sale.invoicemiddlev2', compact('lims_sale_data', 'lims_product_sale_data', 'lims_biller_data', 'lims_warehouse_data', 'lims_customer_data', 'lims_payment_data', 'numberInWords', 'cadenaCentavos'));
            $pdf->setPaper("a4", 'portrait');
            return $pdf->stream("venta_" . $lims_sale_data->reference_no . ".pdf", array("Attachment" => false));
        } else if ($lims_pos_setting_data->type_print == 5) {
            //return view('sale.invoicemiddlev2', compact('lims_sale_data', 'lims_product_sale_data', 'lims_biller_data', 'lims_warehouse_data', 'lims_customer_data', 'lims_payment_data', 'numberInWords'));
            view()->share('sale.invoicemiddlev2', compact('lims_sale_data', 'lims_product_sale_data', 'lims_biller_data', 'lims_warehouse_data', 'lims_customer_data', 'lims_payment_data', 'numberInWords', 'cadenaCentavos'));
            $pdf = Pdf::loadView('sale.invoicemiddlev2', compact('lims_sale_data', 'lims_product_sale_data', 'lims_biller_data', 'lims_warehouse_data', 'lims_customer_data', 'lims_payment_data', 'numberInWords', 'cadenaCentavos'));
            $pdf->setPaper("letter", 'portrait');
            return $pdf->stream("venta_" . $lims_sale_data->reference_no . ".pdf", array("Attachment" => false));
        } else if ($lims_pos_setting_data->type_print == 7) {
            // impresora de 80mm            
            $pdf = Pdf::loadView('sale.invoice80mm', compact('lims_sale_data', 'lims_product_sale_data', 'lims_biller_data', 'lims_warehouse_data', 'lims_customer_data', 'lims_payment_data', 'numberInWords', 'cadenaCentavos'));
            $cantItems = $lims_product_sale_data->count();
            $largo = (350 + ($cantItems * 55));
            $customPaper = array(0, 0, 280, $largo);
            $pdf->setPaper($customPaper, 'portrait');
            return $pdf->stream("invoice_" . $lims_sale_data->reference_no . ".pdf", array("Attachment" => false));
        } else {
            return view('sale.invoice', compact('lims_sale_data', 'lims_product_sale_data', 'lims_biller_data', 'lims_warehouse_data', 'lims_customer_data', 'lims_payment_data', 'numberInWords', 'cadenaCentavos'));
        }
    }

    /**
     * AJAX helper to create sale via AJAX (preview) by delegating to store with flag
     */
    public function storeAjax(Request $request)
    {
        $request->merge(['ajax_preview' => true]);
        return $this->store($request);
    }

    /**
     * Finalize (facturar) for an existing sale via AJAX. Executes SIAT/offline generation and returns print URL.
     */
    public function finalizeAjax(Request $request)
    {
        $data = $request->all();
        $id = $data['sale_id'] ?? null;
        if (!$id) return response()->json(['status' => false, 'message' => 'Sale id required']);

        $lims_sale_data = Sale::find($id);
        if (!$lims_sale_data) return response()->json(['status' => false, 'message' => 'Sale not found']);

        $obj_cliente = CustomerSale::where('sale_id', $id)->first();
        
        // Si no existe CustomerSale, crearlo con los datos del request
        if (!$obj_cliente) {
            $obj_cliente = new CustomerSale();
            $obj_cliente->sale_id = $id;
            $obj_cliente->customer_id = $data['customer_id'] ?? $lims_sale_data->customer_id;
            $obj_cliente->razon_social = $data['sales_razon_social'] ?? '';
            $obj_cliente->email = $data['sales_email'] ?? '';
            $obj_cliente->codigofijo = $data['codigo_fijo'] ?? $data['customer_id'] ?? $lims_sale_data->customer_id;
            $obj_cliente->tipo_documento = $data['sales_tipo_documento_hidden'] ?? 1;
            $obj_cliente->valor_documento = $data['sales_valor_documento'] ?? '0';
            $obj_cliente->complemento_documento = $data['sales_complemento_documento'] ?? null;
            $obj_cliente->codigo_excepcion = $data['bandera_codigo_excepcion_hidden'] ?? 0;
            $obj_cliente->codigo_documento_sector = $data['bandera_codigo_documento_sector_hidden'] ?? 1;
            $obj_cliente->glosa_periodo_facturado = $data['glosa_periodo_facturado'] ?? '';
            $obj_cliente->tipo_metodo_pago = $data['paid_by_id'] ?? 1; // Por defecto 1 = Efectivo
            $obj_cliente->usuario = Auth::user()->name;
            
            // Obtener datos del punto de venta
            $data_biller = Biller::where('id', $lims_sale_data->biller_id)->first();
            $data_p_venta = SiatPuntoVenta::where([
                'sucursal' => $data_biller->sucursal,
                'codigo_punto_venta' => $data_biller->punto_venta_siat
            ])->first();
            
            // Asignar número de factura según el tipo de documento sector
            if ($obj_cliente->codigo_documento_sector == 1) {
                $obj_cliente->nro_factura = $data_p_venta->correlativo_factura;
                $data_p_venta->correlativo_factura += 1;
            } elseif ($obj_cliente->codigo_documento_sector == 2) {
                $obj_cliente->nro_factura = $data_p_venta->correlativo_alquiler;
                $data_p_venta->correlativo_alquiler += 1;
            } elseif ($obj_cliente->codigo_documento_sector == 13) {
                $obj_cliente->nro_factura = $data_p_venta->correlativo_servicios_basicos;
                $data_p_venta->correlativo_servicios_basicos += 1;
            }
            
            $obj_cliente->sucursal = $data_p_venta->sucursal;
            $obj_cliente->codigo_punto_venta = $data_p_venta->codigo_punto_venta;
            $obj_cliente->save();
            $data_p_venta->save();
        }

        // Si el CustomerSale existe pero está ANULADO, asignar nuevo nro_factura para re-facturar
        if ($obj_cliente && $obj_cliente->estado_factura === 'ANULADO') {
            $data_biller_pv = Biller::where('id', $lims_sale_data->biller_id)->first();
            $data_p_venta_reset = SiatPuntoVenta::where([
                'sucursal' => $data_biller_pv->sucursal,
                'codigo_punto_venta' => $data_biller_pv->punto_venta_siat
            ])->first();

            if ($obj_cliente->codigo_documento_sector == 1) {
                $obj_cliente->nro_factura = $data_p_venta_reset->correlativo_factura;
                $data_p_venta_reset->correlativo_factura += 1;
            } elseif ($obj_cliente->codigo_documento_sector == 2) {
                $obj_cliente->nro_factura = $data_p_venta_reset->correlativo_alquiler;
                $data_p_venta_reset->correlativo_alquiler += 1;
            } elseif ($obj_cliente->codigo_documento_sector == 13) {
                $obj_cliente->nro_factura = $data_p_venta_reset->correlativo_servicios_basicos;
                $data_p_venta_reset->correlativo_servicios_basicos += 1;
            } elseif ($obj_cliente->codigo_documento_sector == 24) {
                $obj_cliente->nro_factura = $data_p_venta_reset->correlativo_nota_debcred;
                $data_p_venta_reset->correlativo_nota_debcred += 1;
            }

            $obj_cliente->estado_factura   = null;  // se asignará VIGENTE al facturar
            $obj_cliente->codigo_recepcion = null;
            $obj_cliente->save();
            $data_p_venta_reset->save();

            Log::info("Re-facturación tras ANULADO: nuevo nro_factura=" . $obj_cliente->nro_factura . " | sale_id=" . $id);
        }

        $lims_pos_setting_data = PosSetting::latest()->first();

        // punto de venta info
        $data_biller = Biller::where('id', $lims_sale_data->biller_id)->first();
        $data_p_venta = SiatPuntoVenta::where([
            'sucursal' => $data_biller->sucursal,
            'codigo_punto_venta' => $data_biller->punto_venta_siat,
        ])->first();
        $update_p_venta = SiatPuntoVenta::where([
            'sucursal' => $data_biller->sucursal,
            'codigo_punto_venta' => $data_biller->punto_venta_siat,
        ])->first();

        try {
            if ($data_p_venta->modo_contingencia == true) {
                $codigoEvento = $this->getTipoEventoContingenciaPuntoVenta($lims_sale_data->biller_id);
                if ($codigoEvento && $obj_cliente->codigo_documento_sector == 1) {
                    $respuesta = $this->generarFacturaIndividualOffline($id, $codigoEvento);
                }
                if ($codigoEvento && $obj_cliente->codigo_documento_sector == 13) {
                    $respuesta = $this->generarFacturaServicioBasicoOffline($id, $codigoEvento);
                }
                if ($codigoEvento && $obj_cliente->codigo_documento_sector == 2) {
                    $respuesta = $this->generarFacturaAlquilerOffline($id, $codigoEvento);
                }
            } else {
                if ($obj_cliente->codigo_documento_sector == 1) {
                    if (($lims_pos_setting_data->cufd_centralizado ?? 0) == 1) {
                        $respuesta = $this->generarFacturaIndividualComisionista($id);
                    } else {
                        $respuesta = $this->generarFacturaIndividual($id);
                    }
                }
                if ($obj_cliente->codigo_documento_sector == 2) {
                    $respuesta = $this->generarFacturaIndividualAlquiler($id);
                }
                if ($obj_cliente->codigo_documento_sector == 13) {
                    $respuesta = $this->generarFacturaServicioBasico($id);
                }
            }

            if (isset($respuesta) && $respuesta['status']) {
                // Usar la ruta de factura SIAT en lugar de la genérica
                return response()->json(['status' => true, 'message' => $respuesta['mensaje'], 'print_url' => url('sales/imprimir_factura/' . $id)]);
            } else {
                $msg = $respuesta['mensaje'] ?? 'Error generating invoice';
                return response()->json(['status' => false, 'message' => $msg]);
            }
        } catch (\Throwable $e) {
            Log::error('finalizeAjax error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Error processing invoice']);
        }
    }

    public function addPayment(Request $request)
    {
        $data = $request->all();
        if (!$data['amount']) {
            $data['amount'] = 0.00;
        }

        $lims_sale_data = Sale::find($data['sale_id']);
        $lims_customer_data = Customer::find($lims_sale_data->customer_id);
        $lims_sale_data->paid_amount += $data['amount'];
        $balance = $lims_sale_data->grand_total - $lims_sale_data->paid_amount;
        if ($balance > 0 || $balance < 0) {
            $lims_sale_data->payment_status = 2;
        } elseif ($balance == 0) {
            $lims_sale_data->payment_status = 4;
        }

        $lims_sale_data->save();

        if ($data['paid_by_id'] == 1) {
            $paying_method = 'Efectivo';
        } elseif ($data['paid_by_id'] == 3) {
            $paying_method = 'Tarjeta_Regalo';
        } elseif ($data['paid_by_id'] == 4) {
            $paying_method = 'Tarjeta_Credito_Debito';
        } elseif ($data['paid_by_id'] == 5) {
            $paying_method = 'Cheque';
        } elseif ($data['paid_by_id'] == 8) {
            $paying_method = 'Paypal';
        } elseif ($data['paid_by_id'] == 6) {
            $paying_method = 'Qr_Simple';
        } else {
            $paying_method = 'Deposito';
        }

        $lims_payment_data = new Payment();
        $lims_payment_data->user_id = Auth::id();
        $lims_payment_data->sale_id = $lims_sale_data->id;
        $lims_payment_data->account_id = $data['account_id'];
        $data['payment_reference'] = 'spr-' . date("Ymd") . '-' . date("his");
        $lims_payment_data->payment_reference = $data['payment_reference'];
        $lims_payment_data->amount = $data['amount'];
        $lims_payment_data->change = $data['paying_amount'] - $data['amount'];
        $lims_payment_data->paying_method = $paying_method;
        $lims_payment_data->payment_note = $data['payment_note'];
        $lims_payment_data->save();

        $lims_payment_data = Payment::latest()->first();
        $data['payment_id'] = $lims_payment_data->id;

        if ($paying_method == 'Tarjeta_Regalo') {
            $lims_gift_card_data = GiftCard::find($data['gift_card_id']);
            $lims_gift_card_data->expense += $data['amount'];
            $lims_gift_card_data->save();
            PaymentWithGiftCard::create($data);
        } elseif ($paying_method == 'Tarjeta_Credito_Debito') {
            $lims_pos_setting_data = PosSetting::latest()->first();
            Stripe::setApiKey($lims_pos_setting_data->stripe_secret_key);
            $token = $data['stripeToken'];
            $amount = $data['amount'];

            $lims_payment_with_credit_card_data = PaymentWithCreditCard::where('customer_id', $lims_sale_data->customer_id)->first();

            if (!$lims_payment_with_credit_card_data) {
                // Create a Customer:
                $customer = \Stripe\Customer::create([
                    'source' => $token,
                ]);

                // Charge the Customer instead of the card:
                $charge = \Stripe\Charge::create([
                    'amount' => $amount * 100,
                    'currency' => 'usd',
                    'customer' => $customer->id,
                ]);
                $data['customer_stripe_id'] = $customer->id;
            } else {
                $customer_id =
                    $lims_payment_with_credit_card_data->customer_stripe_id;

                $charge = \Stripe\Charge::create([
                    'amount' => $amount * 100,
                    'currency' => 'usd',
                    'customer' => $customer_id,
                    // Previously stored, then retrieved
                ]);
                $data['customer_stripe_id'] = $customer_id;
            }
            $data['customer_id'] = $lims_sale_data->customer_id;
            $data['charge_id'] = $charge->id;
            PaymentWithCreditCard::create($data);
        } elseif ($paying_method == 'Cheque') {
            PaymentWithCheque::create($data);
        } elseif ($paying_method == 'Paypal') {
            //$provider = new ExpressCheckout;
            $paypal_data['items'] = [];
            $paypal_data['items'][] = [
                'name' => 'Paid Amount',
                'price' => $data['amount'],
                'qty' => 1,
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
            $lims_customer_data->expense += $data['amount'];
            $lims_customer_data->save();
        }
        $message = 'Payment created successfully';
        if ($lims_customer_data->email) {
            $mail_data['email'] = $lims_customer_data->email;
            $mail_data['sale_reference'] = $lims_sale_data->reference_no;
            $mail_data['payment_reference'] = $lims_payment_data->payment_reference;
            $mail_data['payment_method'] = $lims_payment_data->paying_method;
            $mail_data['grand_total'] = $lims_sale_data->grand_total;
            $mail_data['paid_amount'] = $lims_payment_data->amount;
            try {
                Mail::send('mail.payment_details', $mail_data, function ($message) use ($mail_data) {
                    $message->to($mail_data['email'])->subject('Payment Details');
                });
            } catch (\Exception $e) {
                $message = 'Payment created successfully. Please setup your <a href="setting/mail_setting">mail setting</a> to send mail.';
            }
        }
        return redirect('sales')->with('message', $message);
    }

    public function getPayment($id)
    {
        $lims_payment_list = Payment::where('sale_id', $id)->get();
        $date = [];
        $payment_reference = [];
        $paid_amount = [];
        $paying_method = [];
        $payment_id = [];
        $payment_note = [];
        $gift_card_id = [];
        $cheque_no = [];
        $change = [];
        $paying_amount = [];
        $account_name = [];
        $account_id = [];

        foreach ($lims_payment_list as $payment) {
            $date[] = date(config('date_format'), strtotime($payment->created_at->toDateString())) . ' ' . $payment->created_at->toTimeString();
            $payment_reference[] = $payment->payment_reference;
            $paid_amount[] = $payment->amount;
            $change[] = $payment->change;
            $paying_method[] = $payment->paying_method;
            $paying_amount[] = $payment->amount + $payment->change;
            if ($payment->paying_method == 'Gift Card') {
                $lims_payment_gift_card_data = PaymentWithGiftCard::where('payment_id', $payment->id)->first();
                $gift_card_id[] = $lims_payment_gift_card_data->gift_card_id;
            } elseif ($payment->paying_method == 'Cheque') {
                $lims_payment_cheque_data = PaymentWithCheque::where('payment_id', $payment->id)->first();
                $cheque_no[] = $lims_payment_cheque_data->cheque_no;
            } else {
                $cheque_no[] = $gift_card_id[] = null;
            }
            $payment_id[] = $payment->id;
            $payment_note[] = $payment->payment_note;
            $lims_account_data = Account::find($payment->account_id);
            $account_name[] = $lims_account_data->name;
            $account_id[] = $lims_account_data->id;
        }
        $payments[] = $date;
        $payments[] = $payment_reference;
        $payments[] = $paid_amount;
        $payments[] = $paying_method;
        $payments[] = $payment_id;
        $payments[] = $payment_note;
        $payments[] = $cheque_no;
        $payments[] = $gift_card_id;
        $payments[] = $change;
        $payments[] = $paying_amount;
        $payments[] = $account_name;
        $payments[] = $account_id;

        return $payments;
    }

    public function updatePayment(Request $request)
    {
        $data = $request->all();
        $lims_payment_data = Payment::find($data['payment_id']);
        $lims_sale_data = Sale::find($lims_payment_data->sale_id);
        $lims_customer_data = Customer::find($lims_sale_data->customer_id);
        //updating sale table
        $amount_dif = $lims_payment_data->amount - $data['edit_amount'];
        $lims_sale_data->paid_amount = $lims_sale_data->paid_amount - $amount_dif;
        $balance = $lims_sale_data->grand_total - $lims_sale_data->paid_amount;
        if ($balance > 0 || $balance < 0) {
            $lims_sale_data->payment_status = 2;
        } elseif ($balance == 0) {
            $lims_sale_data->payment_status = 4;
        }

        $lims_sale_data->save();

        if ($lims_payment_data->paying_method == 'Deposito') {
            $lims_customer_data->expense -= $lims_payment_data->amount;
            $lims_customer_data->save();
        }
        if ($data['edit_paid_by_id'] == 1) {
            $lims_payment_data->paying_method = 'Efectivo';
        } elseif ($data['edit_paid_by_id'] == 3) {
            if ($lims_payment_data->paying_method == 'Tarjeta_Regalo') {
                $lims_payment_gift_card_data = PaymentWithGiftCard::where('payment_id', $data['payment_id'])->first();

                $lims_gift_card_data = GiftCard::find($lims_payment_gift_card_data->gift_card_id);
                $lims_gift_card_data->expense -= $lims_payment_data->amount;
                $lims_gift_card_data->save();

                $lims_gift_card_data = GiftCard::find($data['gift_card_id']);
                $lims_gift_card_data->expense += $data['edit_amount'];
                $lims_gift_card_data->save();

                $lims_payment_gift_card_data->gift_card_id = $data['gift_card_id'];
                $lims_payment_gift_card_data->save();
            } else {
                $lims_payment_data->paying_method = 'Tarjeta_Regalo';
                $lims_gift_card_data = GiftCard::find($data['gift_card_id']);
                $lims_gift_card_data->expense += $data['edit_amount'];
                $lims_gift_card_data->save();
                PaymentWithGiftCard::create($data);
            }
        } elseif ($data['edit_paid_by_id'] == 4) {
            $lims_pos_setting_data = PosSetting::latest()->first();
            Stripe::setApiKey($lims_pos_setting_data->stripe_secret_key);
            if ($lims_payment_data->paying_method == 'Tarjeta_Credito_Debito') {
                $lims_payment_with_credit_card_data = PaymentWithCreditCard::where('payment_id', $lims_payment_data->id)->first();

                \Stripe\Refund::create(
                    array(
                        "charge" => $lims_payment_with_credit_card_data->charge_id,
                    )
                );

                $customer_id =
                    $lims_payment_with_credit_card_data->customer_stripe_id;

                $charge = \Stripe\Charge::create([
                    'amount' => $data['edit_amount'] * 100,
                    'currency' => 'usd',
                    'customer' => $customer_id,
                ]);
                $lims_payment_with_credit_card_data->charge_id = $charge->id;
                $lims_payment_with_credit_card_data->save();
            } else {
                $token = $data['stripeToken'];
                $amount = $data['edit_amount'];
                $lims_payment_with_credit_card_data = PaymentWithCreditCard::where('customer_id', $lims_sale_data->customer_id)->first();

                if (!$lims_payment_with_credit_card_data) {
                    $customer = \Stripe\Customer::create([
                        'source' => $token,
                    ]);

                    $charge = \Stripe\Charge::create([
                        'amount' => $amount * 100,
                        'currency' => 'usd',
                        'customer' => $customer->id,
                    ]);
                    $data['customer_stripe_id'] = $customer->id;
                } else {
                    $customer_id =
                        $lims_payment_with_credit_card_data->customer_stripe_id;

                    $charge = \Stripe\Charge::create([
                        'amount' => $amount * 100,
                        'currency' => 'usd',
                        'customer' => $customer_id,
                    ]);
                    $data['customer_stripe_id'] = $customer_id;
                }
                $data['customer_id'] = $lims_sale_data->customer_id;
                $data['charge_id'] = $charge->id;
                PaymentWithCreditCard::create($data);
            }
            $lims_payment_data->paying_method = 'Tarjeta Crédito/Débito';
        } elseif ($data['edit_paid_by_id'] == 5) {
            if ($lims_payment_data->paying_method == 'Cheque') {
                $lims_payment_cheque_data = PaymentWithCheque::where('payment_id', $data['payment_id'])->first();
                $lims_payment_cheque_data->cheque_no = $data['edit_cheque_no'];
                $lims_payment_cheque_data->save();
            } else {
                $lims_payment_data->paying_method = 'Cheque';
                $data['cheque_no'] = $data['edit_cheque_no'];
                PaymentWithCheque::create($data);
            }
        } elseif ($data['edit_paid_by_id'] == 8) {
            //updating payment data
            $lims_payment_data->amount = $data['edit_amount'];
            $lims_payment_data->paying_method = 'Paypal';
            $lims_payment_data->payment_note = $data['edit_payment_note'];
            $lims_payment_data->save();

            //$provider = new ExpressCheckout;
            $paypal_data['items'] = [];
            $paypal_data['items'][] = [
                'name' => 'Paid Amount',
                'price' => $data['edit_amount'],
                'qty' => 1,
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
        } else {
            $lims_payment_data->paying_method = 'Deposito';
            $lims_customer_data->expense += $data['edit_amount'];
            $lims_customer_data->save();
        }
        //updating payment data
        $lims_payment_data->account_id = $data['account_id'];
        $lims_payment_data->amount = $data['edit_amount'];
        $lims_payment_data->change = $data['edit_paying_amount'] - $data['edit_amount'];
        $lims_payment_data->payment_note = $data['edit_payment_note'];
        $lims_payment_data->save();
        $message = 'Pago actualizado con éxito.';
        //collecting male data
        if ($lims_customer_data->email) {
            $mail_data['email'] = $lims_customer_data->email;
            $mail_data['sale_reference'] = $lims_sale_data->reference_no;
            $mail_data['payment_reference'] = $lims_payment_data->payment_reference;
            $mail_data['payment_method'] = $lims_payment_data->paying_method;
            $mail_data['grand_total'] = $lims_sale_data->grand_total;
            $mail_data['paid_amount'] = $lims_payment_data->amount;
            try {
                Mail::send('mail.payment_details', $mail_data, function ($message) use ($mail_data) {
                    $message->to($mail_data['email'])->subject('Payment Details');
                });
            } catch (\Exception $e) {
                $message = 'Pago actualizado con éxito. Please setup your <a href="setting/mail_setting">mail setting</a> to send mail.';
            }
        }
        return redirect('sales')->with('message', $message);
    }

    public function deletePayment(Request $request)
    {
        $lims_payment_data = Payment::find($request['id']);
        $lims_sale_data = Sale::where('id', $lims_payment_data->sale_id)->first();
        $lims_sale_data->paid_amount -= $lims_payment_data->amount;
        $balance = $lims_sale_data->grand_total - $lims_sale_data->paid_amount;
        if ($balance > 0 || $balance < 0) {
            $lims_sale_data->payment_status = 2;
        } elseif ($balance == 0) {
            $lims_sale_data->payment_status = 4;
        }

        $lims_sale_data->save();

        if ($lims_payment_data->paying_method == 'Tarjeta_Regalo') {
            $lims_payment_gift_card_data = PaymentWithGiftCard::where('payment_id', $request['id'])->first();
            $lims_gift_card_data = GiftCard::find($lims_payment_gift_card_data->gift_card_id);
            $lims_gift_card_data->expense -= $lims_payment_data->amount;
            $lims_gift_card_data->save();
            $lims_payment_gift_card_data->delete();
        } elseif ($lims_payment_data->paying_method == 'Tarjeta_Credito_Debito') {
            $lims_payment_with_credit_card_data = PaymentWithCreditCard::where('payment_id', $request['id'])->first();
            $lims_pos_setting_data = PosSetting::latest()->first();
            Stripe::setApiKey($lims_pos_setting_data->stripe_secret_key);
            \Stripe\Refund::create(
                array(
                    "charge" => $lims_payment_with_credit_card_data->charge_id,
                )
            );

            $lims_payment_with_credit_card_data->delete();
        } elseif ($lims_payment_data->paying_method == 'Cheque') {
            $lims_payment_cheque_data = PaymentWithCheque::where('payment_id', $request['id'])->first();
            $lims_payment_cheque_data->delete();
        } elseif ($lims_payment_data->paying_method == 'Paypal') {
            $lims_payment_paypal_data = PaymentWithPaypal::where('payment_id', $request['id'])->first();
            if ($lims_payment_paypal_data) {
                //$provider = new ExpressCheckout;
                //$response = $provider->refundTransaction($lims_payment_paypal_data->transaction_id);
                $lims_payment_paypal_data->delete();
            }
        } elseif ($lims_payment_data->paying_method == 'Deposito') {
            $lims_customer_data = Customer::find($lims_sale_data->customer_id);
            $lims_customer_data->expense -= $lims_payment_data->amount;
            $lims_customer_data->save();
        }
        $lims_payment_data->delete();
        return redirect('sales')->with('not_permitted', 'Pago eliminado con éxito');
    }

    public function deleteBySelection(Request $request)
    {
        $sale_id = $request['saleIdArray'];
        $message = 'Ventas eliminado con éxito ';
        foreach ($sale_id as $id) {
            $lims_sale_data = Sale::find($id);
            $lims_product_sale_data = Product_Sale::where('sale_id', $id)->get();
            $lims_delivery_data = Delivery::where('sale_id', $id)->first();
            if ($lims_sale_data->sale_status == 3) {
                $message .= 'Borradores eliminado con éxito';
            }

            foreach ($lims_product_sale_data as $product_sale) {
                $lims_product_data = Product::find($product_sale->product_id);
                //adjust product quantity
                if (
                    ($lims_sale_data->sale_status == 1 || $lims_sale_data->sale_status == 4) &&
                    ($lims_product_data->type == 'combo' || $lims_product_data->type == 'producto_terminado')
                ) {
                    $product_list = explode(",", $lims_product_data->product_list);
                    $qty_list = explode(",", $lims_product_data->qty_list);
                    $sale_lote_list = LoteSale::where('sale_id', $lims_sale_data->id)->get();
                    foreach ($product_list as $index => $child_id) {
                        $child_data = Product::find($child_id);
                        if ($child_data->unit_id != 0 && $child_data->type != 'digital') {
                            $child_warehouse_data = Product_Warehouse::where([
                                ['product_id', $child_id],
                                ['warehouse_id', $lims_sale_data->warehouse_id],
                            ])->first();

                            $child_data->qty += $product_sale->qty * $qty_list[$index];
                            $child_warehouse_data->qty += $product_sale->qty * $qty_list[$index];
                            //** Delete Lote of product */
                            foreach ($sale_lote_list as $lote_sale) {
                                $lote_product = ProductLote::find($lote_sale->lote_id);
                                if ($lote_product != null && $lote_product->idproduct == $child_id) {
                                    $lote_product->stock += $qty_list[$index];
                                    $lote_product->save();
                                    $lote_sale->delete();
                                }
                            }
                            $child_data->save();
                            $child_warehouse_data->save();
                        }
                    }
                } elseif (($lims_sale_data->sale_status == 1) && ($product_sale->sale_unit_id != 0)) {
                    //$lims_sale_unit_data = Unit::find($product_sale->sale_unit_id);
                    $sale_lote_list = LoteSale::where('sale_id', $lims_sale_data->id)->get();
                    /*if ($lims_sale_unit_data->operator == '*')
                    $product_sale->qty = $product_sale->qty * $lims_sale_unit_data->operation_value;
                    else
                    $product_sale->qty = $product_sale->qty / $lims_sale_unit_data->operation_value;
                    */
                    $product_sale->qty = $product_sale->qty * 1;

                    if ($product_sale->variant_id) {
                        $lims_product_variant_data = ProductVariant::select('id', 'qty')->FindExactProduct($lims_product_data->id, $product_sale->variant_id)->first();
                        $lims_product_warehouse_data = Product_Warehouse::FindProductWithVariant($lims_product_data->id, $product_sale->variant_id, $lims_sale_data->warehouse_id)->first();
                        $lims_product_variant_data->qty += $product_sale->qty;
                        $lims_product_variant_data->save();
                    } else {
                        $lims_product_warehouse_data = Product_Warehouse::FindProductWithoutVariant($lims_product_data->id, $lims_sale_data->warehouse_id)->first();
                    }
                    //** Delete Lote of product */
                    foreach ($sale_lote_list as $lote_sale) {
                        $lote_product = ProductLote::find($lote_sale->lote_id);
                        if ($lote_product != null && $lote_product->idproduct == $lims_product_data->id) {
                            $lote_product->stock += $product_sale->qty;
                            $lote_product->save();
                            $lote_sale->delete();
                        }
                    }
                    if ($lims_product_warehouse_data) {
                        $lims_product_data->qty += $product_sale->qty;
                        $lims_product_warehouse_data->qty += $product_sale->qty;
                        $lims_product_data->save();
                        $lims_product_warehouse_data->save();
                    }
                }
                $product_sale->delete();
            }
            $lims_payment_data = Payment::where('sale_id', $id)->get();
            foreach ($lims_payment_data as $payment) {
                if ($payment->paying_method == 'Tarjeta_Regalo') {
                    $lims_payment_with_gift_card_data = PaymentWithGiftCard::where('payment_id', $payment->id)->first();
                    $lims_gift_card_data = GiftCard::find($lims_payment_with_gift_card_data->gift_card_id);
                    $lims_gift_card_data->expense -= $payment->amount;
                    $lims_gift_card_data->save();
                    $lims_payment_with_gift_card_data->delete();
                } elseif ($payment->paying_method == 'Cheque') {
                    $lims_payment_cheque_data = PaymentWithCheque::where('payment_id', $payment->id)->first();
                    $lims_payment_cheque_data->delete();
                } elseif ($payment->paying_method == 'Tarjeta_Credito_Debito') {
                    $lims_payment_with_credit_card_data = PaymentWithCreditCard::where('payment_id', $payment->id)->first();
                    $lims_payment_with_credit_card_data->delete();
                } elseif ($payment->paying_method == 'Paypal') {
                    $lims_payment_paypal_data = PaymentWithPaypal::where('payment_id', $payment->id)->first();
                    if ($lims_payment_paypal_data) {
                        $lims_payment_paypal_data->delete();
                    }
                } elseif ($payment->paying_method == 'Deposito') {
                    $lims_customer_data = Customer::find($lims_sale_data->customer_id);
                    $lims_customer_data->expense -= $payment->amount;
                    $lims_customer_data->save();
                }
                $payment->delete();
            }
            if ($lims_delivery_data) {
                $lims_delivery_data->delete();
            }

            if ($lims_sale_data->coupon_id) {
                $lims_coupon_data = Coupon::find($lims_sale_data->coupon_id);
                $lims_coupon_data->used -= 1;
                $lims_coupon_data->save();
            }
            $lims_sale_data->delete();
        }
        return $message;
    }

    public function destroy($id)
    {
        $url = url()->previous();
        $lims_sale_data = Sale::find($id);
        $lims_product_sale_data = Product_Sale::where('sale_id', $id)->get();
        $lims_delivery_data = Delivery::where('sale_id', $id)->first();
        if ($lims_sale_data->sale_status == 3) {
            $message = 'Borrador eliminado con éxito';
        } else {
            $message = 'Venta eliminado con éxito';
        }

        foreach ($lims_product_sale_data as $product_sale) {
            $lims_product_data = Product::find($product_sale->product_id);
            //adjust product quantity
            if (
                ($lims_sale_data->sale_status == 1 || $lims_sale_data->sale_status == 4) && ($lims_product_data->type == 'combo' ||
                    $lims_product_data->type == 'producto_terminado')
            ) {
                $product_list = explode(",", $lims_product_data->product_list);
                $qty_list = explode(",", $lims_product_data->qty_list);
                $sale_lote_list = LoteSale::where('sale_id', $lims_sale_data->id)->get();
                foreach ($product_list as $index => $child_id) {
                    $child_data = Product::find($child_id);
                    if ($child_data->unit_id != 0 && $child_data->type != 'digital') {
                        $child_warehouse_data = Product_Warehouse::where([
                            ['product_id', $child_id],
                            ['warehouse_id', $lims_sale_data->warehouse_id],
                        ])->first();

                        $child_data->qty += $product_sale->qty * $qty_list[$index];
                        $child_warehouse_data->qty += $product_sale->qty * $qty_list[$index];
                        $qty = $product_sale->qty * $qty_list[$index];

                        //** Delete Lote of product */
                        foreach ($sale_lote_list as $lote_sale) {
                            $lote_product = ProductLote::find($lote_sale->lote_id);
                            if ($lote_product != null && $lote_product->idproduct == $child_id) {
                                $lote_product->stock += $qty;
                                $lote_product->save();
                                $lote_sale->delete();
                            }
                        }
                        $child_data->save();
                        $child_warehouse_data->save();
                    }
                }
            } elseif (($lims_sale_data->sale_status == 1) && ($product_sale->sale_unit_id != 0)) {
                //$lims_sale_unit_data = Unit::find($product_sale->sale_unit_id);
                $sale_lote_list = LoteSale::where('sale_id', $lims_sale_data->id)->get();
                /*if ($lims_sale_unit_data->operator == '*')
                $product_sale->qty = $product_sale->qty * $lims_sale_unit_data->operation_value;
                else
                $product_sale->qty = $product_sale->qty / $lims_sale_unit_data->operation_value;
                */
                $product_sale->qty = $product_sale->qty * 1;

                if ($product_sale->variant_id) {
                    $lims_product_variant_data = ProductVariant::select('id', 'qty')->FindExactProduct($lims_product_data->id, $product_sale->variant_id)->first();
                    $lims_product_warehouse_data = Product_Warehouse::FindProductWithVariant($lims_product_data->id, $product_sale->variant_id, $lims_sale_data->warehouse_id)->first();
                    $lims_product_variant_data->qty += $product_sale->qty;
                    $lims_product_variant_data->save();
                } else {
                    $lims_product_warehouse_data = Product_Warehouse::FindProductWithoutVariant($lims_product_data->id, $lims_sale_data->warehouse_id)->first();
                }
                //** Delete Lote of product */
                foreach ($sale_lote_list as $lote_sale) {
                    $lote_product = ProductLote::find($lote_sale->lote_id);
                    if ($lote_product != null && $lote_product->idproduct == $lims_product_data->id) {
                        $lote_product->stock += $product_sale->qty;
                        $lote_product->save();
                        $lote_sale->delete();
                    }
                }
                if ($lims_product_warehouse_data) {
                    $lims_product_data->qty += $product_sale->qty;
                    $lims_product_warehouse_data->qty += $product_sale->qty;
                    $lims_product_data->save();
                    $lims_product_warehouse_data->save();
                }
            }
            $product_sale->delete();
        }
        $lims_payment_data = Payment::where('sale_id', $id)->get();
        foreach ($lims_payment_data as $payment) {
            if ($payment->paying_method == 'Tarjeta_Regalo') {
                $lims_payment_with_gift_card_data = PaymentWithGiftCard::where('payment_id', $payment->id)->first();
                $lims_gift_card_data = GiftCard::find($lims_payment_with_gift_card_data->gift_card_id);
                $lims_gift_card_data->expense -= $payment->amount;
                $lims_gift_card_data->save();
                $lims_payment_with_gift_card_data->delete();
            } elseif ($payment->paying_method == 'Cheque') {
                $lims_payment_cheque_data = PaymentWithCheque::where('payment_id', $payment->id)->first();
                $lims_payment_cheque_data->delete();
            } elseif ($payment->paying_method == 'Tarjeta_Credito_Debito') {
                $lims_payment_with_credit_card_data = PaymentWithCreditCard::where('payment_id', $payment->id)->first();
                $lims_payment_with_credit_card_data->delete();
            } elseif ($payment->paying_method == 'Paypal') {
                $lims_payment_paypal_data = PaymentWithPaypal::where('payment_id', $payment->id)->first();
                if ($lims_payment_paypal_data) {
                    $lims_payment_paypal_data->delete();
                }
            } elseif ($payment->paying_method == 'Deposito') {
                $lims_customer_data = Customer::find($lims_sale_data->customer_id);
                $lims_customer_data->expense -= $payment->amount;
                $lims_customer_data->save();
            }
            $payment->delete();
        }
        if ($lims_delivery_data) {
            $lims_delivery_data->delete();
        }

        if ($lims_sale_data->coupon_id) {
            $lims_coupon_data = Coupon::find($lims_sale_data->coupon_id);
            $lims_coupon_data->used -= 1;
            $lims_coupon_data->save();
        }
        $lims_sale_data->delete();
        return Redirect::to($url)->with('not_permitted', $message);
    }

    /**
     * Display the specified sale.
     * Método requerido por Route::resource
     */
    public function show($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('sales-edit')) {
            $lims_sale_data = Sale::find($id);
            
            if (!$lims_sale_data) {
                return redirect()->back()->with('not_permitted', 'Venta no encontrada');
            }
            
            $lims_product_sale_data = Product_Sale::where('sale_id', $id)->get();
            $lims_biller_data = Biller::find($lims_sale_data->biller_id);
            $lims_warehouse_data = Warehouse::find($lims_sale_data->warehouse_id);
            $lims_customer_data = Customer::find($lims_sale_data->customer_id);
            $lims_payment_data = Payment::where('sale_id', $id)->get();
            
            return view('sale.show', compact(
                'lims_sale_data',
                'lims_product_sale_data',
                'lims_biller_data',
                'lims_warehouse_data',
                'lims_customer_data',
                'lims_payment_data'
            ));
        } else {
            return redirect()->back()->with('not_permitted', 'Lo siento! No tienes permiso para ver esta venta.');
        }
    }

    public function getCliente($id)
    {
        $data = Customer::find($id);
        
        // Validar que el cliente existe antes de acceder a sus propiedades
        if (!$data) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }
        
        $nit_data = CustomerNit::where([
            'tipo_documento' => $data->tipo_documento,
            'valor_documento' => $data->valor_documento
        ])->first();
        
        if ($nit_data != null) {
            $data->email = $nit_data->email;
        }
        
        return $data;
    }

    public function anularVentaFacturada(Request $request)
    {
        $data = $request->all();

    
    
    
        $tipo_id = false;
        $identificador = null;

        if (!empty($data['sale_anulacion_id'] ?? null)) {
            $identificador = $data['sale_anulacion_id'];
            $tipo_id = false;
        } elseif (!empty($data['cuf_anulacion_id'] ?? null)) {
            $identificador = $data['cuf_anulacion_id'];
            $tipo_id = true;
        }

        if (!$identificador) {
            Log::warning('anularVentaFacturada - Falta identificador', ['data' => $data]);
            $resp = ['estado' => false, 'mensaje' => 'Falta identificador de venta/factura.'];
            return $tipo_id ? $resp : redirect()->back()->withErrors($resp['mensaje']);
        }

        $identificador = is_string($identificador) ? trim($identificador) : $identificador;
        $identificador_clean = is_string($identificador)
            ? trim(preg_replace('/\s+/', '', $identificador))
            : $identificador;

        $motivo_anulacion_id = $data['motivo_anulacion_id'] ?? null;
        $punto_venta_id      = $data['punto_venta_id'] ?? null;
        $sucursal_id         = $data['sucursal_id'] ?? null;

        $send_whatsapp  = filter_var($data['send_whatsapp'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $whatsapp_phone = $data['whatsapp_phone'] ?? null;
        $whatsapp_phone = is_string($whatsapp_phone) ? trim($whatsapp_phone) : $whatsapp_phone;

        $req_factura_numero  = $data['factura_numero'] ?? null;
        $req_factura_cliente = $data['factura_cliente'] ?? null;
        $req_factura_nit     = $data['factura_nit'] ?? null;
        $req_factura_total   = $data['factura_total'] ?? null;
        $req_factura_fecha   = $data['factura_fecha'] ?? null;

        Log::info('anularVentaFacturada - Datos recibidos:', [
            'tipo_id' => $tipo_id,
            'identificador' => $identificador_clean,
            'motivo' => $motivo_anulacion_id,
            'punto_venta' => $punto_venta_id,
            'sucursal' => $sucursal_id,
            'send_whatsapp' => $send_whatsapp,
            'whatsapp_phone' => $whatsapp_phone,
            'req_factura_numero' => $req_factura_numero,
            'req_factura_cliente' => $req_factura_cliente,
            'req_factura_nit' => $req_factura_nit,
            'req_factura_total' => $req_factura_total,
        ]);

        // Pre-fetch ANTES de anular: anularFactura() pondrá cuf = null en la BD.
        // Necesitamos los datos para el WhatsApp ANTES de que se borren.
        $wa_customer_sale = null;
        $wa_sale          = null;
        if (!$tipo_id) {
            $wa_sale          = Sale::find($identificador_clean);
            $wa_customer_sale = CustomerSale::where('sale_id', $identificador_clean)->first();
        } else {
            $wa_customer_sale = CustomerSale::where('cuf', $identificador_clean)->first();
            if ($wa_customer_sale) {
                $wa_sale = Sale::find($wa_customer_sale->sale_id);
            }
        }

        $respuesta = $this->anularFactura($identificador_clean, $motivo_anulacion_id, $tipo_id, $punto_venta_id, $sucursal_id);
        Log::info("Response Front => " . json_encode($respuesta));

        $anulacion_ok = $send_whatsapp && !empty($respuesta['estado']);

        if ($anulacion_ok) {
            try {
                if (!\App\Helpers\WhatsAppHelper::hasActiveSession()) {
                    Log::warning('No se envía WhatsApp: no hay sesión activa');
                } else {
                    // Usar datos pre-fetchados (CUF ya fue limpiado en BD por anularFactura)
                    $sale          = $wa_sale;
                    $customer_sale = $wa_customer_sale;
                    $customer      = null;

                    if ($sale) {
                        $customer = Customer::find($sale->customer_id);
                    }

                    Log::info('Búsqueda local (para WhatsApp)', [
                        'customer_sale_found' => $customer_sale ? 'Sí' : 'No',
                        'sale_found' => $sale ? 'Sí' : 'No',
                    ]);

                
                    $phone_to_send = $whatsapp_phone ?: ($customer ? trim((string)$customer->phone_number) : null);
                    if (!$phone_to_send) {
                        Log::warning('No se puede enviar WhatsApp - Falta teléfono', [
                            'phone_param' => $whatsapp_phone,
                            'customer_phone' => $customer->phone_number ?? null,
                        ]);
                    } else {                    
                        $general_setting = GeneralSetting::first();
                        $company_name = $general_setting->site_title ?? 'Empresa';

                        $motivo = $this->obtenerDescripcionMotivo($motivo_anulacion_id) ?? 'No especificado';
                    
                        $nro_factura = $req_factura_numero
                            ?: ($customer_sale->nro_factura ?? $customer_sale->nro_factura_manual ?? null);
                    
                        $razon_social = $req_factura_cliente
                            ?: ($customer_sale->razon_social ?? ($customer->name ?? null));
                    
                        $nit = $req_factura_nit
                            ?: ($customer_sale->valor_documento ?? null);
                    
                        $cuf = $tipo_id ? $identificador_clean : ($customer_sale->cuf ?? null);

                        $monto = null;
                        if ($req_factura_total !== null && $req_factura_total !== '') {
                            $monto = (float) str_replace(',', '.', (string)$req_factura_total);
                        } else {
                            $monto = $sale->grand_total ?? 0;
                        }
                        $nro_factura = $nro_factura ?: 'N/A';
                        $razon_social = $razon_social ?: 'Cliente';
                        $nit = $nit ?: 'N/A';
                        $cuf = $cuf ?: 'N/A';

                        $fecha_anulacion = date('d/m/Y H:i');
                    
                        $mensaje  = "*" . $company_name . "*\n\n";
                        $mensaje .= "*FACTURA ANULADA*\n\n";
                        $mensaje .= "Estimado(a): *" . $razon_social . "*\n\n";
                        $mensaje .= "Le informamos que la siguiente factura fue *ANULADA*:\n\n";
                        $mensaje .= "*N° Factura:* " . $nro_factura . "\n";
                        $mensaje .= "*NIT/CI:* " . $nit . "\n";
                        $mensaje .= "*Monto:* Bs. " . number_format((float)$monto, 2) . "\n";
                        $mensaje .= "*Motivo:* " . $motivo . "\n";
                        $mensaje .= "*Fecha de anulación:* " . $fecha_anulacion . "\n\n";
                        $mensaje .= "Gracias por su preferencia.";

                        Log::info('WhatsApp - Mensaje final', [
                            'phone' => $phone_to_send,
                            'nro_factura' => $nro_factura,
                            'nit' => $nit,
                            'cuf' => $cuf,
                            'monto' => $monto,
                            'fuente' => [
                                'factura_numero_req' => (bool)$req_factura_numero,
                                'factura_cliente_req' => (bool)$req_factura_cliente,
                                'factura_nit_req' => (bool)$req_factura_nit,
                                'factura_total_req' => (bool)$req_factura_total,
                                'customer_sale' => (bool)$customer_sale,
                                'sale' => (bool)$sale,
                            ]
                        ]);
                        $wa = \App\Helpers\WhatsAppHelper::sendText($phone_to_send, $mensaje);

                        if (!empty($wa['success'])) {
                            Log::info('WhatsApp enviado exitosamente (anulación)', [
                                'tipo_id' => $tipo_id,
                                'sale_id' => $sale->id ?? null,
                                'nro_factura' => $nro_factura,
                                'cuf' => $cuf,
                                'phone' => $phone_to_send,
                                'wa_status' => $wa['status'] ?? null,
                            ]);
                        } else {
                            Log::warning('Error al enviar WhatsApp (anulación)', [
                                'tipo_id' => $tipo_id,
                                'sale_id' => $sale->id ?? null,
                                'nro_factura' => $nro_factura,
                                'cuf' => $cuf,
                                'phone' => $phone_to_send,
                                'wa_error' => $wa['error'] ?? null,
                                'wa_response' => $wa['response'] ?? null,
                            ]);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Excepción al enviar WhatsApp de anulación: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        } else {
            Log::info('No se envió WhatsApp', [
                'send_whatsapp' => $send_whatsapp,
                'estado' => $respuesta['estado'] ?? 'N/A',
            ]);
        }

        return $tipo_id ? $respuesta : redirect()->back();
    }

    /**
     * Obtiene la descripción del motivo de anulación desde SIAT
     */
    private function obtenerDescripcionMotivo($codigo_motivo)
    {
        try {
            $motivo = \App\SiatParametricaVario::where('tipo_clasificador', 'motivoAnulacion')
                ->where('codigo_clasificador', $codigo_motivo)
                ->first();
            return $motivo ? $motivo->descripcion : null;
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Obtiene el número de teléfono del cliente por sale_id
     */
    public function getCustomerPhone(Request $request)
    {
        try {
            $sale_id = $request->input('sale_id');
            $sale = Sale::find($sale_id);
            
            if ($sale && $sale->customer_id) {
                $customer = Customer::find($sale->customer_id);
                if ($customer && $customer->phone_number) {
                    return response()->json([
                        'success' => true,
                        'phone' => $customer->phone_number
                    ]);
                }
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No se encontró número de teléfono'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtiene el número de teléfono del cliente por CUF
     */
    public function getCustomerPhoneByCuf(Request $request)
    {
        try {
            $cuf = $request->input('cuf');
            $customer_sale = CustomerSale::where('cuf', $cuf)->first();
            
            if ($customer_sale) {
                $sale = Sale::find($customer_sale->sale_id);
                if ($sale && $sale->customer_id) {
                    $customer = Customer::find($sale->customer_id);
                    if ($customer && $customer->phone_number) {
                        return response()->json([
                            'success' => true,
                            'phone' => $customer->phone_number
                        ]);
                    }
                }
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No se encontró número de teléfono'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function reenviarVentaFacturada(Request $request)
    {
        $data = $request->all();
        /*$v = Validator::make($data, [
            'telefono' => 'exclude_unless:correo,nullable|numeric|digits:8',
            'correo' => 'exclude_unless:telefono,nullable|email'
        ]);
        if ($v->fails()) {
            log::info("Response Front => " . $v->errors());
            return redirect()->back()->withErrors($v->errors());
        }*/
        $cuf_factura_id = $data['cuf_factura_id'];
        $correo = $data['correo'];
        $telefono = $data['telefono'];
        $respuesta = $this->reenviarFactura($cuf_factura_id, $correo, $telefono); // SiatTrait
        log::info("Response Front => " . json_encode($respuesta));
        /*if ($respuesta['estado'] == true)
            return $respuesta;
        else
            return redirect()->back();*/
        return $respuesta;
    }


    // Devuelve texto label descripción de la venta 
    public function getEstadoVentaFacturada($sale_id)
    {
        $tipo_factura_lookup = [
            1 => 'COM-VEN',
            2 => 'ALQ',
            13 => 'SERV',
        ];

        $estado_factura = " ";
        $venta_facturada = CustomerSale::where('sale_id', $sale_id)->first();
        if (!empty($venta_facturada)) {
            if ($venta_facturada->estado_factura != null) {
                $tipo_factura = $tipo_factura_lookup[$venta_facturada->codigo_documento_sector] ?? 'COM-VEN';
                
                // Badge con color según estado
                $badge_class = 'badge-success'; // VIGENTE / CONTINGENCIA / MASIVO
                if ($venta_facturada->estado_factura == 'ANULADO') {
                    $badge_class = 'badge-danger';
                } elseif ($venta_facturada->estado_factura == 'PENDIENTE') {
                    $badge_class = 'badge-warning';
                }
                
                if ($venta_facturada->nro_factura != null) {
                    $texto_factura = ' <span class="badge ' . $badge_class . '" title="Factura SIAT"><i class="fa fa-file-text"></i> ' . $tipo_factura . ' #' . $venta_facturada->nro_factura . ' | ' . $venta_facturada->estado_factura . '</span>';
                    $estado_factura .= $texto_factura;
                } else {
                    $texto_factura = ' <span class="badge badge-info" title="Factura Manual"><i class="fa fa-file-o"></i> MANUAL-' . $tipo_factura . ' #' . $venta_facturada->nro_factura_manual . ' | ' . $venta_facturada->estado_factura . '</span>';
                    $estado_factura .= $texto_factura;
                }
            }
        } else {
            // No facturada - agregar badge indicador
            $estado_factura .= ' <span class="badge badge-secondary" title="Sin facturar"><i class="fa fa-exclamation-circle"></i> SIN FACTURA</span>';
        }
        return $estado_factura;
    }

    // llama a funcion SiatTrait, true o false, si están correcto la conexión.
    public function getEstadoServiciosSiat()
    {
        return $this->verificarServiciosSiat(); // SiatTrait
    }

    // Funcion para determinar el estado de contingencia de determinado Biller
    public function estadoContingenciaPuntoVenta($biller_id)
    {
        $data_biller = Biller::select('id', 'sucursal', 'punto_venta_siat')->where('id', $biller_id)->first();
        $data_p_venta = SiatPuntoVenta::select('modo_contingencia')->where([
            'sucursal' => $data_biller->sucursal,
            'codigo_punto_venta' => $data_biller->punto_venta_siat
        ])->first();

        return $data_p_venta->modo_contingencia;
    }

    // Función para imprimir la factura de una venta determinada por ID, renderizada en una vista
    public function getFactura($venta_id)
    {
        $data = $this->getImprimirFactura($venta_id); //SIAT Trait
        
        // Si se pide solo el contenido (para AJAX dentro del modal)
        if (request()->ajax() || request()->get('partial') == '1') {
            return view('sale.impresion-factura-partial', compact('data', 'venta_id'));
        }

        return view('sale.impresion-factura', compact('data'));
    }

    // retorna la venta facturada en formato de bytes
    public function getBytesFactura($venta_id)
    {
        // Comprobar que exista un registro de facturación (CustomerSale) para esta venta
        $data_cliente = CustomerSale::where('sale_id', $venta_id)->first();
        if (! $data_cliente) {
            return response()->json(['status' => false, 'message' => 'Factura no encontrada / no generada']);
        }

        // Llamar al trait que invoca al servicio SIAT para obtener los bytes de impresión
        $data = $this->getImprimirFactura($venta_id); //SIAT Trait

        // Agregar información adicional de la factura para verificación
        $data['cuf'] = $data_cliente->cuf;
        $data['nro_factura'] = $data_cliente->nro_factura;
        $data['estado_factura'] = $data_cliente->estado_factura;
        $data['codigo_recepcion'] = $data_cliente->codigo_recepcion;
        $data['sale_id'] = $venta_id;

        return response()->json($data);
    }

    /**
     * Descarga directa del PDF de la factura (para WhatsApp)
     * Devuelve el PDF como archivo binario, no como HTML
     */
    public function downloadFacturaPdf($venta_id)
    {
        try {
            // Comprobar que exista un registro de facturación (CustomerSale) para esta venta
            $customer_sale = CustomerSale::where('sale_id', $venta_id)->first();
            if (!$customer_sale) {
                return response()->json(['status' => false, 'message' => 'Factura no encontrada / no generada'], 404);
            }

            // Obtener los bytes del PDF
            $data = $this->getImprimirFactura($venta_id); //SIAT Trait
            
            if (!isset($data['bytes']) || empty($data['bytes'])) {
                return response()->json(['status' => false, 'message' => 'No se pudo generar el PDF de la factura'], 500);
            }

            // Decodificar el base64
            $pdfContent = base64_decode($data['bytes']);
            
            // Nombre del archivo
            $filename = 'Factura-' . $customer_sale->nro_factura . '.pdf';

            // Devolver el PDF directamente
            return response($pdfContent, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $filename . '"')
                ->header('Cache-Control', 'public, must-revalidate, max-age=0')
                ->header('Pragma', 'public')
                ->header('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT')
                ->header('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
                
        } catch (\Exception $e) {
            Log::error('Error al descargar PDF de factura', [
                'venta_id' => $venta_id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => false, 
                'message' => 'Error al generar el PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    // determinar si la hora de la venta son las 23:30 o más para renovar el cufd
    // restricción: modo contingencia desactivado
    public function getEstadoCufd($biller_id)
    {
        $data_biller = Biller::select('id', 'sucursal', 'punto_venta_siat')->where('id', $biller_id)->first();
        $data_p_venta = SiatPuntoVenta::select('modo_contingencia', 'sucursal', 'codigo_punto_venta', 'codigo_cuis')->where([
            'sucursal' => $data_biller->sucursal,
            'codigo_punto_venta' => $data_biller->punto_venta_siat
        ])->first();

        if ($data_p_venta->modo_contingencia == true) {
            return true;
        }

        if ($this->estaVigenteCUFD($data_p_venta)) { //verifica si está vigente boolean
            Log::info("CUFD esta vigente para PV: " . $data_p_venta->codigo_punto_venta);
            return true;
        } else {
            $this->taskRenovarCUFD($data_p_venta);
            return true;
        }
    }

    // Permite buscar en la BD si existe alguna coincidencia cuando se digita valor_documento en el POS
    public function searchNit(Request $request)
    {
        if ($request->ajax()) {
            $output = '';
            $query = $request->get('query');
            if ($query != '') {
                $data = DB::table('customer_nit')
                    ->where('valor_documento', 'like', '%' . $query . '%')
                    ->orWhere('complemento_documento', 'like', '%' . $query . '%')
                    ->orderBy('valor_documento', 'desc')
                    ->get();
            } else {
                $data = DB::table('customer_nit')
                    ->orderBy('valor_documento', 'desc')
                    ->get();
            }
            $total_row = $data->count();
            if ($total_row > 0) {
                foreach ($data as $row) {
                    // <option value="{{ $sucursal->sucursal }}">{{ $sucursal->sucursal}}.- {{ $sucursal->nombre}} | {{ $sucursal->direccion }}</option>
                    // '<option value="'+ data[i].codigo_punto_venta +'">'+ data[i].codigo_punto_venta +' - '+data[i].nombre_punto_venta +'</option>'
                    $output .= '
                    <option value="' . $row->valor_documento . '">' . $row->valor_documento . ' - ' . $row->razon_social . '</option>
                    ';
                }
            } else {
                $output = '
                <option value="">Información no encontrada!</option>
                ';
            }
            $data_salida = array(
                'table_data' => $data,
                'total_data' => $total_row,
            );
            echo json_encode($data_salida);
        }
    }

    public function getMotivoAnulacion()
    {
        // Cachear los motivos de anulación por 24 horas ya que son datos que casi nunca cambian
        $lista_motivo_anulacion = \Cache::remember('motivos_anulacion', 86400, function () {
            return SiatParametricaVario::where('tipo_clasificador', 'motivoAnulacion')
                ->select('codigo_clasificador', 'descripcion')
                ->get();
        });
        return $lista_motivo_anulacion;
    }

    // Consultar que tipo de evento de contingencia posee el punto de venta
    public function getTipoEventoContingenciaPuntoVenta($biller_id)
    {
        $data_biller = Biller::where('id', $biller_id)->first();

        $data_contingencia = ControlContingencia::where([
            'sucursal' => $data_biller->sucursal,
            'codigo_punto_venta' => $data_biller->punto_venta_siat,
            'estado' => 'EN_PROCESO'
        ])->first();

        if ($data_contingencia) {
            return $data_contingencia->codigo_evento;
        } else {
            return 0;
        }
    }

    // El nro de factura coincide con el correlativo del CAFC, true o false
    public function consultaNroFacturaManual(Request $request)
    {
        $data = $request->all();
        $nro_factura = $data['nro_factura_manual'];
        $biller_id = $data['biller_id'];
        $nro_documento_sector = $data['nro_documento_sector'];

        $data_biller = Biller::where('id', $biller_id)->first();
        $data_p_venta = SiatPuntoVenta::where('codigo_punto_venta', $data_biller->punto_venta_siat)->first();
        $data_credencial = CredencialCafc::where('sucursal', $data_p_venta->sucursal)->where('codigo_punto_venta', $data_p_venta->codigo_punto_venta)->where('codigo_documento_sector', $nro_documento_sector)->where('is_active', true)->first();

        if ($data_credencial->correlativo_factura == $nro_factura) {
            return true;
        }
        return false;
    }

    // Se consulta la fecha y hora de la última venta manual realizada
    public function consultaFechaManualCafc(Request $request)
    {
        $data = $request->all();

        $biller_id = $data['biller_id'];
        $nro_documento_sector = $data['nro_documento_sector'];

        $data_biller = Biller::where('id', $biller_id)->first();
        $data_p_venta = SiatPuntoVenta::where('codigo_punto_venta', $data_biller->punto_venta_siat)->first();
        $data_credencial = CredencialCafc::where('sucursal', $data_p_venta->sucursal)->where('codigo_punto_venta', $data_p_venta->codigo_punto_venta)->where('codigo_documento_sector', $nro_documento_sector)->where('is_active', true)->first();

        // obtenemos el numero correlativo anterior
        $nro_correlativo_anterior = ($data_credencial->correlativo_factura - 1);
        // lo buscamos en customer_sales
        $data_customer_sale = CustomerSale::where('nro_factura_manual', $nro_correlativo_anterior)
            ->where('codigo_documento_sector', $nro_documento_sector)
            ->first();
        // la fecha de inicio de la contingencia
        $data_contingencia = ControlContingencia::where('sucursal', $data_p_venta->sucursal)
            ->where('codigo_punto_venta', $data_p_venta->codigo_punto_venta)
            ->where('codigo_documento_sector', $nro_documento_sector)
            ->where('estado', 'EN_PROCESO')
            ->first();

        if ($data_customer_sale != null) {
            if ($data_customer_sale->fecha_manual != null) {
                if ($data_customer_sale->fecha_manual > $data_contingencia->fecha_inicio_evento) {
                    return $data_customer_sale->fecha_manual;
                }
            }
        }
        return $data_contingencia->fecha_inicio_evento;
    }


    // Obtiene los puntos de ventas pertenecientes a determinada sucursal
    public function getPuntoVentaxSucursal($sucursal)
    {
        $puntos_ventas = SiatPuntoVenta::where('sucursal', $sucursal)->get();
        return $puntos_ventas;
    }

    public function getCorreoBiller($biller_id)
    {
        $data_biller = Biller::where('id', $biller_id)->first();

        return $data_biller->email;
    }

    public function libroVentas()
    {
        $role = Role::find(Auth::user()->role_id);
        $fecha_actual = date('Y-m-d');
        $data_biller = Biller::where('id', Auth::user()->biller_id)->first();
        if ($data_biller == null) {
            $data_biller = Biller::first();
        }
        $data_p_venta = SiatPuntoVenta::where([
            'sucursal' => $data_biller->sucursal,
            'codigo_punto_venta' => $data_biller->punto_venta_siat
        ])->get();

        $usuarios = User::select('id', 'name')->where('is_active', true)->get();
        $sucursales = SiatSucursal::where('estado', true)->get();
        if ($role->hasPermissionTo('sales-list-booksale')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if (empty($all_permission))
                $all_permission[] = 'dummy text';

            return view('sale.sales-book', compact('all_permission', 'fecha_actual', 'sucursales', 'data_biller', 'data_p_venta', 'usuarios'));
        } else
            return redirect()->back()->with('not_permitted', 'Lo siento! Usted no tiene acceso a este modulo');
    }

    public function listBooksales(Request $request)
    {
        try {
            $role = Role::find(Auth::user()->role_id);

            $list_invoices = collect();
            $columns = array(
                0 => 'numeroFactura'
            );
            
            $data = $request->all();
            
            // Validar datos requeridos
            if (!isset($data['fechaInc']) || !isset($data['fechaFin'])) {
                return response()->json([
                    "draw" => intval($request->input('draw', 1)),
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    "data" => [],
                    "error" => "Fechas requeridas"
                ]);
            }
            
            $dataFilter['numeroFactura'] = 0;
            $dataFilter['documentoSector'] = isset($data['documentoSector']) ? $data['documentoSector'] : '1';
            $dataFilter['fechaInc'] = $data['fechaInc'];
            $dataFilter['fechaFin'] = $data['fechaFin'];
            $dataFilter['opcion'] = isset($data['opcion']) ? $data['opcion'] : 'razonSocial';
            $dataFilter['puntoVenta'] = isset($data['puntoVenta']) ? $data['puntoVenta'] : '0';
            $dataFilter['sucursal'] = isset($data['sucursal']) ? $data['sucursal'] : '0';
            $dataFilter['estadoFactura'] = isset($data['estado']) ? $data['estado'] : 'T';
        if ($request->input('start') > 9) {
            $dataFilter['pagina'] = substr($request->input('start'), 0, -1);
        } else {
            $dataFilter['pagina'] = $request->input('start');
        }
        $dataFilter['fila'] = $request->input('length');
        if ($data['valor'] == null) {
            $dataFilter['valor'] = "";
        } else {
            $dataFilter['valor'] = $data['valor'];
        }
        $dataFilter['cuf'] = "";
        
        Log::info('===== INICIO listBooksales =====');
        Log::info('Filtros enviados:', $dataFilter);
        
        $list_facturas = $this->buscarFacturasxLibro($dataFilter); // SiatTrait
        
        Log::info('Respuesta recibida de buscarFacturasxLibro:', [
            'status' => isset($list_facturas['status']) ? $list_facturas['status'] : 'no definido',
            'tiene_facturas' => isset($list_facturas['facturas']),
            'cantidad_facturas' => isset($list_facturas['facturas']) ? count($list_facturas['facturas']) : 0,
            'total' => isset($list_facturas['total']) ? $list_facturas['total'] : 0,
            'mensaje' => isset($list_facturas['mensaje']) ? $list_facturas['mensaje'] : 'sin mensaje'
        ]);
        
        // Manejo de errores del API
        if (!isset($list_facturas['status']) || $list_facturas['status'] == false) {
            $errorMsg = isset($list_facturas['mensaje']) ? $list_facturas['mensaje'] : 'Error al consultar facturas';
            Log::warning('Error en buscarFacturasxLibro: ' . $errorMsg);
            
            $json_data = array(
                "draw" => intval($request->input('draw')),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
                "error" => $errorMsg
            );
            return response()->json($json_data);
        }
        
        // Verificar que existan facturas
        if (!isset($list_facturas['facturas']) || !is_array($list_facturas['facturas'])) {
            Log::warning('Respuesta sin facturas válidas');
            $json_data = array(
                "draw" => intval($request->input('draw')),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => []
            );
            return response()->json($json_data);
        }
        
        $list_size = isset($list_facturas['total']) ? $list_facturas['total'] : 0;
        Session::put('total_fila', $list_size);
        
        $list_facturas = $list_facturas['facturas'];
        $totalFiltered = $list_size;
        
        Log::info("Total facturas encontradas: " . count($list_facturas));
        
        for ($i = 0; $i < count($list_facturas); $i++) {
            if (isset($list_facturas[$i])) {
                $op['options'] = '<div class="btn-group">';
                $op['options'] .= '<button type="button" class="imprimir-factura-modal btn btn-info" data-id = "' . $list_facturas[$i]['cuf'] . '" data-toggle="modal" data-target="#imprimir-factura-modal" title="Imprimir"><i class="fa fa-print"></i></button>';
                if ($list_facturas[$i]['pago'] == null && $list_facturas[$i]['estado'] != 'B') {
                    $op['options'] .= '<button type="button" class="pagar-factura-modal btn btn-warning" data-id = "' . $list_facturas[$i]['cuf'] . '" title="Pagar"><i class="fa fa-money"></i></button>';
                } else if ($list_facturas[$i]['pago'] != null && $list_facturas[$i]['estado'] != 'B') {
                    $op['options'] .= '<button type="button" class="revertir-factura-modal btn btn-secondary" style="background-color: darkorange;" data-id = "' . $list_facturas[$i]['cuf'] . '" title="Revertir Pago"><i class="fa fa-exchange"></i></button>';
                }
                if ($role->hasPermissionTo('sales-delete') && ($list_facturas[$i]['pago'] == null && $list_facturas[$i]['estado'] != 'B')) {
                    $op['options'] .= '<button type="button" class="anular-factura-modal btn btn-danger" data-id = "' . $list_facturas[$i]['cuf'] . '" data-ptoventa = "' . $list_facturas[$i]['codigoPuntoVenta'] . '" data-sucursal = "' . $list_facturas[$i]['codigoSucursal'] . '" data-toggle="modal" data-target="#anular-factura-modal" title="Anular Factura"><i class="fa fa-trash"></i></button>';
                }
                $op['options'] .= '<button type="button" class="reenviar-factura-modal btn btn-success" data-id = "' . $list_facturas[$i]['cuf'] . '" data-toggle="modal" data-target="#reenviar-factura-modal" title="Reenviar Factura"><i class="fa fa-whatsapp"></i></button>';

                $op['options'] .= '</div>';
                $list_facturas[$i]['options'] = $op['options'];
                $razonsocial = $list_facturas[$i]['nombreRazonSocial'] . "|" . $list_facturas[$i]['codigoCliente'] . "|" . $list_facturas[$i]['numeroMedidor'];
                $list_facturas[$i]['nombreRazonSocial'] = $razonsocial;
                if ($list_facturas[$i]['codigoDocumentoSector'] == 1) {
                    $list_facturas[$i]['documentoSector'] = 'Factura Compra/Venta';
                } else if ($list_facturas[$i]['codigoDocumentoSector'] == 2) {
                    $list_facturas[$i]['documentoSector'] = 'Factura Alquiler';
                } else if ($list_facturas[$i]['codigoDocumentoSector'] == 13) {
                    $list_facturas[$i]['documentoSector'] = 'Factura Servicios Basicos';
                } else if ($list_facturas[$i]['codigoDocumentoSector'] == 24) {
                    $list_facturas[$i]['documentoSector'] = 'Nota de Credito/Debito';
                }

                switch ($list_facturas[$i]['estado']) {
                    case 'A':
                        $list_facturas[$i]['estado'] = '<div class="badge badge-success">Activo</div>';
                        break;
                    case 'B':
                        $list_facturas[$i]['estado'] = '<div class="badge badge-danger">Anulado</div>';
                        break;
                }
                $list_invoices[] = $list_facturas[$i];
            } else {
                break;
            }
        }
        $order = $columns[$request->input('order.0.column', 0)];
        $dir = $request->input('order.0.dir', 'asc');
        switch ($order) {
            case 'numeroFactura': {
                    if ($dir == 'asc')
                        $list_invoices = $list_invoices->sortBy('numeroFactura');
                    else
                        $list_invoices = $list_invoices->sortByDesc('numeroFactura');
                    break;
                }
        }

        $json_data = array(
            "draw" => intval($request->input('draw', 1)),
            "recordsTotal" => intval($list_size),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $list_invoices->values()->all()
        );

        return response()->json($json_data);
        
        } catch (\Exception $e) {
            Log::error('Error en listBooksales: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                "draw" => intval($request->input('draw', 1)),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
                "error" => "Error al procesar la solicitud: " . $e->getMessage()
            ], 200); // Devolver 200 para que DataTables lo procese
        }
    }

    public function getPrintFactura($cuf)
    {

        $data = $this->getImprimirFacturaCuf($cuf);
        return $data;
    }


    public function paymentFactura(Request $request)
    {
        $dataPayment = $request->all();
        $data = $this->pagarFactura($dataPayment);
        if ($data['status'] == true)
            if ($data['factura']['ESTADO'] == 'OK')
                return array('estado' => true);
            else
                return array('estado' => false);
        else
            return array('estado' => false);
    }

    public function revertirPaymentFactura(Request $request)
    {
        $dataPayment = $request->all();
        $data = $this->revertirPagoFactura($dataPayment);
        if ($data['status'] == true)
            if ($data['factura']['ESTADO'] == 'OK')
                return array('estado' => true);
            else {
                return array('estado' => false, 'mensaje' => $data['mensaje']);
            }
        else
            return array('estado' => false, 'mensaje' => $data['mensaje']);
    }

    public function getFacturaCufd($cuf)
    {
        $data = $this->getFacturaData($cuf);
        if ($data)
            return array('estado' => true, 'factura' => $data);
        else
            return array('estado' => false);
    }

    /**
     * Endpoint para exponer datos de factura por CUF.
     * Intenta primero obtener los datos desde SIAT (getFacturaData).
     * Si no hay respuesta, intenta obtener datos locales desde `customer_sales`.
     */
    public function datosFactura(Request $request)
    {
        $cuf = $request->query('cuf');
        
        Log::info('═══════════════════════════════════════════════════════');
        Log::info('📥 [datosFactura] Request recibido');
        Log::info('CUF solicitado: ' . ($cuf ?? 'NULL'));
        Log::info('Todos los parámetros: ' . json_encode($request->all()));
        Log::info('IP Cliente: ' . $request->ip());
        Log::info('User Agent: ' . $request->userAgent());
        
        if (!$cuf) {
            Log::warning('❌ [datosFactura] CUF vacío o no proporcionado');
            return response()->json(['status' => 400, 'dateHour' => date('Y-m-d H:i:s'), 'title' => 'Parametro cuf requerido'], 400);
        }

        Log::info('🔍 [datosFactura] Consultando SIAT con CUF: ' . $cuf);
        
        // Primero intentar obtener desde SIAT
        try {
            $factura_siat = $this->getFacturaData($cuf);
            
            if ($factura_siat) {
                Log::info('✅ [datosFactura] Factura encontrada en SIAT');
                Log::info('📤 Respuesta SIAT: ' . json_encode($factura_siat, JSON_UNESCAPED_UNICODE));
                
                // Devolver en formato esperado por integración
                $response = ['ESTADO' => 'OK', 'ENTITY' => $factura_siat, 'MENSAJE' => ''];
                Log::info('📤 [datosFactura] Respuesta final enviada: ' . json_encode($response, JSON_UNESCAPED_UNICODE));
                Log::info('═══════════════════════════════════════════════════════');
                return response()->json($response);
            }
            
            Log::warning('⚠️ [datosFactura] Factura NO encontrada en SIAT, buscando en BD local');
        } catch (\Exception $e) {
            Log::error('❌ [datosFactura] Error al consultar SIAT: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
        }

        // Fallback: buscar en la BD local
        Log::info('🔍 [datosFactura] Buscando en BD local con CUF: ' . $cuf);
        $customer_sale = \App\CustomerSale::where('cuf', $cuf)->first();
        
        if (!$customer_sale) {
            Log::warning('❌ [datosFactura] Factura NO encontrada en BD local');
            Log::info('═══════════════════════════════════════════════════════');
            return response()->json(['status' => 400, 'dateHour' => date('Y-m-d H:i:s'), 'title' => 'Factura no existe!'], 400);
        }

        Log::info('✅ [datosFactura] Factura encontrada en BD local');
        Log::info('CustomerSale ID: ' . $customer_sale->id);
        Log::info('Sale ID: ' . $customer_sale->sale_id);
        
        $sale = $customer_sale->sale;

        $entity = [
            'codigo_recepcion' => $customer_sale->codigo_recepcion ?? null,
            'nro_factura' => $customer_sale->nro_factura ?? null,
            'fecha_factura' => $sale ? date('Y-m-d H:i:s', strtotime($sale->date_sell)) : null,
            'idfactura' => $sale ? $sale->id : null,
            'cufd' => $customer_sale->codigo_cufd ?? null,
            'cuf' => $customer_sale->cuf ?? $cuf,
            'xml' => $customer_sale->xml ?? null,
        ];

        $response = ['ESTADO' => 'OK', 'ENTITY' => $entity, 'MENSAJE' => ''];
        Log::info('📤 [datosFactura] Respuesta de BD local: ' . json_encode($response, JSON_UNESCAPED_UNICODE));
        Log::info('═══════════════════════════════════════════════════════');
        
        return response()->json($response);
    }


    public function reporteCobranza(Request $request)
    {
        $dataFilter = $request->all();
        $result = $this->reporteFacturaCobrada($dataFilter);
        if ($result['status'])
            return array('ESTADO' => $result['pdf']['ESTADO'], 'bytes' => $result['pdf']['bytes']);
        else
            return array('estado' => false, 'mensaje' => $result['mensaje']);
    }

    public function reporteRevertidos(Request $request)
    {
        $dataFilter = $request->all();
        $result = $this->reporteFacturaRevertida($dataFilter);
        if ($result['status'])
            return array('ESTADO' => $result['pdf']['ESTADO'], 'bytes' => $result['pdf']['bytes']);
        else
            return array('estado' => false, 'mensaje' => $result['mensaje']);
    }

    public function reporteArqueoC(Request $request)
    {
        $dataFilter = $request->all();
        $result = $this->reporteArqueoGeneral($dataFilter);
        if ($result)
            return array('ESTADO' => $result['pdf']['ESTADO'], 'bytes' => $result['pdf']['bytes']);
        else
            return array('estado' => false, 'mensaje' => $result['mensaje']);
    }

    public function reporteArqueoCateg(Request $request)
    {
        $dataFilter = $request->all();
        $result = $this->reporteArqueoCategoria($dataFilter);
        if ($result)
            return array('ESTADO' => $result['pdf']['ESTADO'], 'bytes' => $result['pdf']['bytes']);
        else
            return array('estado' => false, 'mensaje' => $result['mensaje']);
    }

    public function reporteLVPDF(Request $request)
    {
        $dataFilter = $request->all();
        $result = $this->reporteFacturasPDF($dataFilter);
        if ($result['status'] == true)
            return array('ESTADO' => $result['pdf']['ESTADO'], 'bytes' => $result['pdf']['bytes']);
        else
            return array('estado' => false, 'mensaje' => $result['mensaje']);
    }

    public function reporteLVEXCEL(Request $request)
    {
        $dataFilter = $request->all();
        $fileResponse = $this->reporteFacturasExcel($dataFilter);
        if ($fileResponse['status'] == true) {
            $filename = explode('=', $fileResponse['name']);
            return response()->download($fileResponse['file'], $filename[1]);
        } else
            return redirect()->back();
    }

    function obtenerParteDecimalLiteral($numero)
    {
        $parteEntera = floor($numero);
        $centavos = round(($numero - $parteEntera) * 100);
        return sprintf("%02d", $centavos) . '/100';
    }

    function costoActualizadoProducto($id_producto, $id_variant = null)
    {
        $costo = 0;
        if ($id_variant)
            $item = ProductPurchase::select('id', 'purchase_id', 'net_unit_cost')->where([['status', true], ['product_id', $id_producto], ['variant_id', $id_variant]])->orderBy('created_at', 'desc')->first();
        else
            $item = ProductPurchase::select('id', 'purchase_id', 'net_unit_cost')->where([['status', true], ['product_id', $id_producto]])->orderBy('created_at', 'desc')->first();
        if ($item) {
            //Log::info("Purchase get Cost id:" . $item->purchase_id);
            $costo = $item->net_unit_cost;
        } else {
            $producto = Product::select('id', 'cost')->find($id_producto);
            //Log::info("Product get Cost id:" . $producto->id);
            $costo = $producto->cost;
        }
        return $costo;
    }

    /**
     * Enviar factura por WhatsApp
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendInvoiceWhatsApp(Request $request)
    {
        try {
            Log::info('📥 Recibida petición sendInvoiceWhatsApp', [
                'request_all' => $request->all(),
                'sale_id' => $request->input('sale_id'),
                'phone' => $request->input('phone'),
            ]);

            $validator = Validator::make($request->all(), [
                'sale_id' => 'required|exists:sales,id',
                'phone' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::warning('❌ Validación fallida', [
                    'errors' => $validator->errors()->toArray()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos.',
                    'errors' => $validator->errors(),
                ], 400);
            }

            $sale_id = $request->input('sale_id');
            $phone = $request->input('phone');

            // Verificar que exista la venta
            $sale = Sale::find($sale_id);
            if (!$sale) {
                Log::warning('❌ Venta no encontrada', ['sale_id' => $sale_id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Venta no encontrada.',
                ], 404);
            }

            // Verificar que la venta esté facturada
            $customer_sale = CustomerSale::where('sale_id', $sale_id)->first();
            if (!$customer_sale) {
                Log::warning('❌ Venta no facturada', ['sale_id' => $sale_id]);
                return response()->json([
                    'success' => false,
                    'message' => 'La venta no está facturada. No se puede enviar por WhatsApp.',
                ], 400);
            }

            // Obtener datos de configuración general
            $general_setting = \App\GeneralSetting::first();
            $company_name = $general_setting ? $general_setting->site_title : 'GISUL';
            
            // Obtener NIT de la empresa desde pos_setting
            $pos_setting = \App\PosSetting::first();
            $nit_empresa = $pos_setting && isset($pos_setting->nit_representante) ? $pos_setting->nit_representante : '388615026';

            // Construir el mensaje personalizado
            $fecha_emision = $sale->created_at ? $sale->created_at->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');
            
            $mensaje = "Estimado Cliente, *" . ($customer_sale->razon_social ?? 'Cliente') . "*\n\n";
            $mensaje .= "Le enviamos como Adjunto la factura en formato PDF del documento Fiscal correspondiente.\n\n";
            $mensaje .= "📄 *Nro. de Factura:* " . $customer_sale->nro_factura . "\n";
            $mensaje .= "📅 *Fecha de Emisión:* " . $fecha_emision . "\n";
            $mensaje .= "💰 *Total:* Bs. " . number_format($sale->grand_total, 2) . "\n\n";
            
            // Agregar enlace de consulta SIN si existe CUF
            if (!empty($customer_sale->cuf)) {
                $url_consulta_sin = "https://siat.impuestos.gob.bo/consulta/QR?nit=" . $nit_empresa . 
                                   "&cuf=" . $customer_sale->cuf . 
                                   "&numero=" . $customer_sale->nro_factura . 
                                   "&t=2";
                $mensaje .= "🔍 Puede consultar el estado de su factura directamente en el SIN:\n";
                $mensaje .= $url_consulta_sin . "\n\n";
            }
            
            $mensaje .= "¡Gracias por su preferencia! 🙏\n";
            $mensaje .= "Atentamente, *" . $company_name . "*";

            // Obtener los bytes del PDF
            $data = $this->getImprimirFactura($sale_id);
            
            if (!isset($data['bytes']) || empty($data['bytes'])) {
                Log::error('❌ No se pudo obtener bytes del PDF', ['sale_id' => $sale_id]);
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo generar el PDF de la factura',
                ], 500);
            }

            // Decodificar el base64 y guardar temporalmente
            $pdfContent = base64_decode($data['bytes']);
            $tempFileName = 'factura_' . $customer_sale->nro_factura . '_' . time() . '.pdf';
            $tempFilePath = storage_path('app/temp/' . $tempFileName);
            
            // Crear directorio temp si no existe
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }
            
            // Guardar el PDF temporalmente
            file_put_contents($tempFilePath, $pdfContent);
            
            Log::info('📄 PDF guardado temporalmente', [
                'temp_path' => $tempFilePath,
                'phone' => $phone,
                'nro_factura' => $customer_sale->nro_factura,
                'file_size' => filesize($tempFilePath)
            ]);

            // Convertir el XML a TXT legible y guardarlo temporalmente
            $txtContent = null;
            $tempTxtPath = null;
            if (!empty($customer_sale->xml)) {
                try {
                    $txtContent = \App\Helpers\XmlToTextHelper::convertirFacturaSiatATexto($customer_sale->xml);
                    $tempTxtFileName = 'factura_' . $customer_sale->nro_factura . '_detalle_' . time() . '.txt';
                    $tempTxtPath = storage_path('app/temp/' . $tempTxtFileName);
                    file_put_contents($tempTxtPath, $txtContent);
                    
                    Log::info('📝 TXT de factura generado', [
                        'temp_txt_path' => $tempTxtPath,
                        'nro_factura' => $customer_sale->nro_factura,
                        'txt_size' => filesize($tempTxtPath)
                    ]);
                } catch (\Exception $e) {
                    Log::warning('⚠️ No se pudo generar TXT del XML', [
                        'error' => $e->getMessage(),
                        'sale_id' => $sale_id
                    ]);
                }
            }

            // Usar el helper para enviar el documento por upload (primero el PDF)
            $helper_result = \App\Helpers\WhatsAppHelper::sendDocumentUpload(
                $phone,
                $tempFilePath,
                'Factura-' . $customer_sale->nro_factura . '.pdf',
                $mensaje
            );

            // Si el PDF se envió correctamente y existe el TXT, enviar también el TXT
            if ($helper_result['success'] && $tempTxtPath && file_exists($tempTxtPath)) {
                try {
                    $mensaje_txt = "📋 *Detalle de la Factura en texto plano*\n\n";
                    $mensaje_txt .= "Este archivo contiene la información detallada de su factura en formato texto para su referencia.";
                    
                    $helper_result_txt = \App\Helpers\WhatsAppHelper::sendDocumentUpload(
                        $phone,
                        $tempTxtPath,
                        'Factura-' . $customer_sale->nro_factura . '-Detalle.txt',
                        $mensaje_txt
                    );
                    
                    if ($helper_result_txt['success']) {
                        Log::info('✅ Archivo TXT enviado exitosamente', [
                            'sale_id' => $sale_id,
                            'phone' => $phone,
                            'txt_file' => $tempTxtPath
                        ]);
                    } else {
                        Log::warning('⚠️ Error al enviar archivo TXT (PDF ya enviado)', [
                            'error' => $helper_result_txt['error'] ?? 'Error desconocido'
                        ]);
                    }
                    
                    // Eliminar el archivo TXT temporal
                    if (file_exists($tempTxtPath)) {
                        unlink($tempTxtPath);
                        Log::info('🗑️ Archivo TXT temporal eliminado', ['temp_path' => $tempTxtPath]);
                    }
                } catch (\Exception $e) {
                    Log::warning('⚠️ Excepción al enviar TXT', [
                        'error' => $e->getMessage(),
                        'sale_id' => $sale_id
                    ]);
                }
            }

            // Eliminar el archivo PDF temporal después de enviar
            if (file_exists($tempFilePath)) {
                unlink($tempFilePath);
                Log::info('🗑️ Archivo temporal eliminado', ['temp_path' => $tempFilePath]);
            }

            if ($helper_result['success']) {
                Log::info('✅ Factura enviada por WhatsApp exitosamente', [
                    'sale_id' => $sale_id,
                    'phone' => $phone,
                    'reference_no' => $sale->reference_no,
                    'pdf_sent' => true,
                    'txt_sent' => ($tempTxtPath && file_exists($tempTxtPath)),
                    'whatsapp_response' => $helper_result
                ]);

                $mensaje_respuesta = '✅ Factura PDF enviada exitosamente por WhatsApp';
                if ($tempTxtPath) {
                    $mensaje_respuesta .= ' junto con el detalle en formato texto';
                }

                return response()->json([
                    'success' => true,
                    'message' => $mensaje_respuesta,
                ]);
            } else {
                Log::error('❌ Error al enviar factura por WhatsApp', [
                    'sale_id' => $sale_id,
                    'phone' => $phone,
                    'error' => $helper_result['error'] ?? 'Error desconocido',
                    'full_response' => $helper_result
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Error al enviar factura: ' . ($helper_result['error'] ?? 'Error desconocido'),
                ], 500);
            }
        } catch (\Throwable $e) {
            Log::error('💥 Excepción en sendInvoiceWhatsApp', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage(),
            ], 500);
        }
    }
}
