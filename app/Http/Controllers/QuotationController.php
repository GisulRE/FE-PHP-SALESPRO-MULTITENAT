<?php

namespace App\Http\Controllers;

use App\Biller;
use App\Customer;
use App\CustomerGroup;
use App\GeneralSetting;
use App\PosSetting;
use App\Product;
use App\Product_Warehouse;
use App\ProductQuotation;
use App\ProductVariant;
use App\Quotation;
use App\Supplier;
use App\Tax;
use App\Unit;
use App\Variant;
use App\Warehouse;
use Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use NumberToWords\NumberToWords;
use Spatie\Permission\Models\Role;

class QuotationController extends Controller
{
    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('quotes-index')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if (empty($all_permission))
                $all_permission[] = 'dummy text';

            return view('quotation.index', compact('all_permission'));
        } else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function listData(Request $request)
    {
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
            3 => 'biller_id',
            7 => 'grand_total',
        );
        if (Auth::user()->role_id > 2) {
            $totalData = Quotation::with('biller', 'customer', 'supplier', 'user')
                ->where('user_id', Auth::id())
                ->count();
        } else {
            $totalData = Quotation::with('biller', 'customer', 'supplier', 'user')->count();
        }

        $totalFiltered = $totalData;
        if ($request->input('length') != -1) {
            $limit = $request->input('length');
        } else {
            $limit = $totalData;
        }

        $start = $request->input('start');
        $order = 'quotations.' . $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        if (empty($request->input('search.value'))) {
            if (Auth::user()->role_id > 2) {
                $quotations = Quotation::with('biller', 'customer', 'warehouse', 'supplier', 'user')->offset($start)
                    ->where('user_id', Auth::id())
                    ->limit($limit)
                    ->orderBy($order, $dir)
                    ->get();
            } else {
                $quotations = Quotation::with('biller', 'customer', 'warehouse', 'supplier', 'user')->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)
                    ->get();
            }

        } else {
            $search = $request->input('search.value');
            if (Auth::user()->role_id > 2) {
                $quotations = Quotation::with('biller', 'customer', 'warehouse', 'supplier', 'user')
                    ->whereDate('created_at', '=', date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                    ->where('user_id', Auth::id())
                    ->orwhere('reference_no', 'LIKE', "%{$search}%")
                    ->orwhereRelation('customer', 'name', 'LIKE', "%{$search}%")
                    ->orwhereRelation('biller', 'name', 'LIKE', "%{$search}%")
                    ->orwhereRelation('supplier', 'name', 'LIKE', "%{$search}%")
                    ->orwhereRelation('warehouse', 'name', 'LIKE', "%{$search}%")
                    ->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)->get();

                $totalFiltered = $quotations->count();
            } else {
                $quotations = Quotation::with('biller', 'customer', 'warehouse', 'supplier', 'user')
                    ->whereDate('created_at', '=', date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                    ->where('user_id', Auth::id())
                    ->orwhere('reference_no', 'LIKE', "%{$search}%")
                    ->orwhereRelation('customer', 'name', 'LIKE', "%{$search}%")
                    ->orwhereRelation('biller', 'name', 'LIKE', "%{$search}%")
                    ->orwhereRelation('supplier', 'name', 'LIKE', "%{$search}%")
                    ->orwhereRelation('warehouse', 'name', 'LIKE', "%{$search}%")
                    ->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)->get();

                $totalFiltered = $quotations->count();
            }
        }
        if (!empty($quotations)) {
            foreach ($quotations as $key => $quotation) {
                $quotation->key = $key;
                $quotation->date = date(config('date_format'), strtotime($quotation->created_at));

                if ($quotation->supplier_id == null) {
                    $quotation->supplier_name = "N/A";
                } else {
                    $quotation->supplier_name = $quotation->supplier->name;
                }

                if ($quotation->quotation_status == 1) {
                    $quotation->quotation_status = '<div class="badge badge-warning">' . trans('file.Pending') . '</div>';
                    $status = trans('file.Pending');
                } else {
                    $quotation->quotation_status = '<div class="badge badge-success">' . trans('file.Sent') . '</div>';
                    $status = trans('file.Sent');
                }
                $quotation->grand_total = number_format($quotation->grand_total, 2);

                $quotation->options = '<div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . trans("file.action") . '
                            <span class="caret"></span>
                            <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                                <li><a href="' . route('quotations.invoice', $quotation->id) . '" target="_blank" class="btn btn-link"><i class="fa fa-copy"></i> ' . trans('file.Print') . '</a></li>
                                <li>
                                    <button type="button" class="btn btn-link view"><i class="fa fa-eye"></i> ' . trans('file.View') . '</button>
                                </li>';
                if (in_array("quotes-edit", $request['all_permission'])) {
                    $quotation->options .= '<li>
                    <a href="' . route('quotations.edit', $quotation->id) . '" class="btn btn-link"><i class="dripicons-document-edit"></i> ' . trans('file.edit') . '</a>
                    </li>';

                }
                //$quotation->options .= '<li> <a class="btn btn-link" href="' . route('quotation.create_sale', $quotation->id) . '"><i class="fa fa-shopping-cart"></i>' . trans('file.Create Sale') . '</a> </li>';
                //$quotation->options .= '<li> <a class="btn btn-link" href="' . route('quotation.create_purchase', $quotation->id) . '"><i class="fa fa-shopping-basket"></i>' . trans('file.Create Purchase') . '</a></li>';
                if (in_array("quotes-delete", $request['all_permission'])) {
                    $quotation->options .= \Form::open(["route" => ["quotations.destroy", $quotation->id], "method" => "DELETE"]) . '
                                <li>
                                    <button type="submit" class="btn btn-link" onclick="return confirmDelete()"><i class="dripicons-trash"></i> ' . trans("file.delete") . '</button>
                                </li>' . \Form::close() . '
                            </ul>
                        </div>';
                }

                if ($quotation->valid_date) {
                    $date_valid = date(config('date_format'), strtotime($quotation->valid_date));
                } else {
                    $date_valid = null;
                }
                $quotation->data_view = array(
                    '[ "' . date(config('date_format'), strtotime($quotation->created_at)) . '"', ' "' . $quotation->reference_no . '"', ' "' . $status . '"', ' "' . $quotation->biller->name . '"', ' "' . $quotation->biller->company_name . '"', ' "' . $quotation->biller->email . '"', ' "' . $quotation->biller->phone_number . '"', ' "' . $quotation->biller->address . '"', ' "' . $quotation->biller->city . '"', ' "' . $quotation->customer->name . '"', ' "' . $quotation->customer->phone_number . '"', ' "' . $quotation->customer->address . '"', ' "' . $quotation->customer->city . '"', ' "' . $quotation->id . '"', ' "' . $quotation->total_tax . '"', ' "' . $quotation->total_discount . '"', ' "' . $quotation->total_price . '"', ' "' . $quotation->order_tax . '"', ' "' . $quotation->order_tax_rate . '"', ' "' . $quotation->order_discount . '"', ' "' . $quotation->shipping_cost . '"', ' "' . $quotation->grand_total . '"', ' "' . $quotation->note . '"', ' "' . $quotation->user->name . '"', ' "' . $quotation->warehouse->name . '"', '"' . $date_valid . '"]',
                );
            }
        }
        $json_data = array(
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $quotations
        );

        echo json_encode($json_data);
    }


    public function create()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('quotes-add')) {
            $lims_biller_list = Biller::where('is_active', true)->get();
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_supplier_list = Supplier::where('is_active', true)->get();
            $lims_tax_list = Tax::where('is_active', true)->get();

            return view('quotation.create', compact('lims_biller_list', 'lims_warehouse_list', 'lims_supplier_list', 'lims_tax_list'));
        } else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function store(Request $request)
    {
        $data = $request->except('document');
        $last_ref = Quotation::get()->last();
        $data['user_id'] = Auth::id();
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
            if ($v->fails())
                return redirect()->back()->withErrors($v->errors());
            $documentName = $document->getClientOriginalName();
            $document->move('public/quotation/documents', $documentName);
            $data['document'] = $documentName;
        }
        if ($last_ref != null) {
            $nros = explode("-", $last_ref['reference_no']);
            $nro = ltrim($nros[1], "0");
            $nro++;
            $nro = str_pad($nro, 8, "0", STR_PAD_LEFT);
        } else {
            $nro = str_pad(1, 8, "0", STR_PAD_LEFT);
        }
        if (isset($data['prepos'])) {
            $data['reference_no'] = 'PRO-' . $nro;
        } else {
            $data['reference_no'] = 'PRO-' . $nro;
        }
        $lims_quotation_data = Quotation::create($data);
        if ($lims_quotation_data->quotation_status == 2) {
            //collecting mail data
            $lims_customer_data = Customer::find($data['customer_id']);
            $mail_data['email'] = $lims_customer_data->email;
            $mail_data['reference_no'] = $lims_quotation_data->reference_no;
            $mail_data['total_qty'] = $lims_quotation_data->total_qty;
            $mail_data['total_price'] = $lims_quotation_data->total_price;
            $mail_data['order_tax'] = $lims_quotation_data->order_tax;
            $mail_data['order_tax_rate'] = $lims_quotation_data->order_tax_rate;
            $mail_data['order_discount'] = $lims_quotation_data->order_discount;
            $mail_data['shipping_cost'] = $lims_quotation_data->shipping_cost;
            $mail_data['grand_total'] = $lims_quotation_data->grand_total;
        }

        if (is_array($data['product_id'])) {
            $product_id = $data['product_id'];
        } else {
            $product_id[] = $data['product_id'];
        }
        if (is_array($data['product_code'])) {
            $product_code = $data['product_code'];
        } else {
            $product_code[] = $data['product_code'];
        }
        if (is_array($data['qty'])) {
            $qty = $data['qty'];
        } else {
            $qty[] = $data['qty'];
        }
        if (is_array($data['sale_unit'])) {
            $sale_unit = $data['sale_unit'];
        } else {
            $sale_unit[] = $data['sale_unit'];
        }
        if (is_array($data['net_unit_price'])) {
            $net_unit_price = $data['net_unit_price'];
        } else {
            $net_unit_price[] = $data['net_unit_price'];
        }
        if (is_array($data['discount'])) {
            $discount = $data['discount'];
        } else {
            $discount[] = $data['discount'];
        }
        if (is_array($data['tax_rate'])) {
            $tax_rate = $data['tax_rate'];
        } else {
            $tax_rate[] = $data['tax_rate'];
        }
        if (is_array($data['tax'])) {
            $tax = $data['tax'];
        } else {
            $tax[] = $data['tax'];
        }
        if (is_array($data['subtotal'])) {
            $total = $data['subtotal'];
        } else {
            $total[] = $data['subtotal'];
        }
        $product_quotation = [];

        foreach ($product_id as $i => $id) {
            if ($sale_unit[$i] != 'n/a') {
                $lims_sale_unit_data = Unit::where('unit_name', $sale_unit[$i])->first();
                $sale_unit_id = $lims_sale_unit_data->id;
            } else
                $sale_unit_id = 0;
            if ($sale_unit_id)
                $mail_data['unit'][$i] = $lims_sale_unit_data->unit_code;
            else
                $mail_data['unit'][$i] = '';
            $lims_product_data = Product::find($id);
            if ($lims_product_data->is_variant) {
                $lims_product_variant_data = ProductVariant::select('variant_id')->FindExactProductWithCode($id, $product_code[$i])->first();
                $product_quotation['variant_id'] = $lims_product_variant_data->variant_id;
            } else
                $product_quotation['variant_id'] = null;
            if ($product_quotation['variant_id']) {
                $variant_data = Variant::find($product_quotation['variant_id']);
                $mail_data['products'][$i] = $lims_product_data->name . ' [' . $variant_data->name . ']';
            } else
                $mail_data['products'][$i] = $lims_product_data->name;
            $product_quotation['quotation_id'] = $lims_quotation_data->id;
            $product_quotation['product_id'] = $id;
            $product_quotation['qty'] = $mail_data['qty'][$i] = $qty[$i];
            $product_quotation['sale_unit_id'] = $sale_unit_id;
            $product_quotation['net_unit_price'] = $net_unit_price[$i];
            $product_quotation['discount'] = $discount[$i];
            $product_quotation['tax_rate'] = $tax_rate[$i];
            $product_quotation['tax'] = $tax[$i];
            $product_quotation['total'] = $mail_data['total'][$i] = $total[$i];
            ProductQuotation::create($product_quotation);
        }
        $message = 'Proforma Registrada';
        if ($lims_quotation_data->quotation_status == 2 && $mail_data['email']) {
            try {
                Mail::send('mail.quotation_details', $mail_data, function ($message) use ($mail_data) {
                    $message->to($mail_data['email'])->subject('Quotation Details');
                });
            } catch (\Exception $e) {
                $message = 'Proforma Registrada. Por favor configurar<a href="setting/mail_setting">ajustes de correo</a> para enviar email.';
            }
        }

        if (isset($data['sale_status'])) {
            return array('message' => $message, 'status' => true, 'message_code' => 'success', 'print' => true, 'id' => $lims_quotation_data->id);
        } else {
            return redirect('quotations')->with('message', $message);
        }

    }

    public function show($id)
    {
        $lims_quotation_data = Quotation::with('biller', 'customer', 'warehouse', 'supplier', 'user')->find($id);
        return $lims_quotation_data;
    }

    public function sendMail(Request $request)
    {
        $data = $request->all();
        $lims_quotation_data = Quotation::find($data['quotation_id']);
        $lims_product_quotation_data = ProductQuotation::where('quotation_id', $data['quotation_id'])->get();
        $lims_customer_data = Customer::find($lims_quotation_data->customer_id);
        if ($lims_customer_data->email) {
            //collecting male data
            $mail_data['email'] = $lims_customer_data->email;
            $mail_data['reference_no'] = $lims_quotation_data->reference_no;
            $mail_data['total_qty'] = $lims_quotation_data->total_qty;
            $mail_data['total_price'] = $lims_quotation_data->total_price;
            $mail_data['order_tax'] = $lims_quotation_data->order_tax;
            $mail_data['order_tax_rate'] = $lims_quotation_data->order_tax_rate;
            $mail_data['order_discount'] = $lims_quotation_data->order_discount;
            $mail_data['shipping_cost'] = $lims_quotation_data->shipping_cost;
            $mail_data['grand_total'] = $lims_quotation_data->grand_total;

            foreach ($lims_product_quotation_data as $key => $product_quotation_data) {
                $lims_product_data = Product::find($product_quotation_data->product_id);
                if ($product_quotation_data->variant_id) {
                    $variant_data = Variant::find($product_quotation_data->variant_id);
                    $mail_data['products'][$key] = $lims_product_data->name . ' [' . $variant_data->name . ']';
                } else
                    $mail_data['products'][$key] = $lims_product_data->name;
                if ($product_quotation_data->sale_unit_id) {
                    $lims_unit_data = Unit::find($product_quotation_data->sale_unit_id);
                    $mail_data['unit'][$key] = $lims_unit_data->unit_code;
                } else
                    $mail_data['unit'][$key] = '';

                $mail_data['qty'][$key] = $product_quotation_data->qty;
                $mail_data['total'][$key] = $product_quotation_data->qty;
            }

            try {
                Mail::send('mail.quotation_details', $mail_data, function ($message) use ($mail_data) {
                    $message->to($mail_data['email'])->subject('Quotation Details');
                });
                $message = 'Correo Electronico enviado...';
            } catch (\Exception $e) {
                $message = 'Please setup your <a href="setting/mail_setting">mail setting</a> to send mail.';
            }
        } else
            $message = 'El cliente no tiene email!';

        return redirect()->back()->with('message', $message);
    }

    public function getCustomerGroup($id)
    {
        $lims_customer_data = Customer::find($id);
        $lims_customer_group_data = CustomerGroup::find($lims_customer_data->customer_group_id);
        return $lims_customer_group_data->percentage;
    }

    public function getProduct($id, $id_customer = null)
    {
        $product_code = [];
        $product_name = [];
        $product_qty = [];
        $product_data = [];
        $product_type = [];
        $product_id = [];
        $product_list = [];
        $product_unit = [];
        $product_tax = [];
        $product_price = [];
        $qty_list = [];
        $type_price = 0;
        if ($id_customer != null) {
            $customer_data = Customer::select('id', 'price_type')->find($id_customer);
            $customer_data->price_type;
        }
        //retrieve data of product without variant
        $lims_products_data = Product::where('is_active', true)->whereNull('is_variant')->get();
        foreach ($lims_products_data as $lims_product_data) {
            $product_warehouse = Product_Warehouse::where([['product_id', $lims_product_data->id], ['warehouse_id', $id]])->first();
            if ($product_warehouse) {
                $product_qty[] = $product_warehouse->qty;
            } else {
                $product_qty[] = $lims_product_data->qty;
            }
            $product_code[] = $lims_product_data->code;
            $product_name[] = $lims_product_data->name;
            $product_type[] = $lims_product_data->type;
            $product_id[] = $lims_product_data->id;
            $product_list[] = null;
            $qty_list[] = null;
            $product_price[] = $this->getPriceByProduct($lims_product_data, null, $type_price);
            $product_tax[] = $this->getTaxByProduct($lims_product_data);
            $product_unit[] = $this->getUnitByProduct($lims_product_data);

        }
        //retrieve data of product with variant
        $lims_product_warehouse_data = Product_Warehouse::where('warehouse_id', $id)->whereNotNull('variant_id')->get();
        foreach ($lims_product_warehouse_data as $product_warehouse) {
            $product_qty[] = $product_warehouse->qty;
            $lims_product_data = Product::find($product_warehouse->product_id);
            $lims_product_variant_data = ProductVariant::select('item_code')->FindExactProduct($product_warehouse->product_id, $product_warehouse->variant_id)->first();
            $product_code[] = $lims_product_variant_data->item_code;
            $product_name[] = $lims_product_data->name;
            $product_type[] = $lims_product_data->type;
            $product_id[] = $lims_product_data->id;
            $product_list[] = null;
            $qty_list[] = null;
            $product_price[] = $this->getPriceByProduct($lims_product_data, $lims_product_variant_data, $type_price);
            $product_tax[] = $this->getTaxByProduct($lims_product_data);
            $product_unit[] = $this->getUnitByProduct($lims_product_data);
        }
        //retrieve product data of digital and combo
        $lims_product_data = Product::whereNotIn('type', ['standard'])->where('is_active', true)->get();
        foreach ($lims_product_data as $product) {
            $product_qty[] = $product->qty;
            $lims_product_data = $product->id;
            $product_code[] = $product->code;
            $product_name[] = $product->name;
            $product_type[] = $product->type;
            $product_id[] = $product->id;
            $product_list[] = $product->product_list;
            $qty_list[] = $product->qty_list;
            $product_price[] = $this->getPriceByProduct($product, null, $type_price);
            $product_tax[] = $this->getTaxByProduct($product);
            $product_unit[] = $this->getUnitByProduct($product);
        }
        $product_data = [$product_code, $product_name, $product_qty, $product_type, $product_id, $product_list, $qty_list, $product_price, $product_tax, $product_unit];
        return $product_data;
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
    public function limsProductSearch(Request $request)
    {
        $todayDate = date('Y-m-d');
        $product_code = explode(" ", $request['data']);
        $product_variant_id = null;
        $lims_product_data = Product::where('code', $product_code[0])->first();
        if (!$lims_product_data) {
            $lims_product_data = Product::join('product_variants', 'products.id', 'product_variants.product_id')
                ->select('products.*', 'product_variants.id as product_variant_id', 'product_variants.item_code', 'product_variants.additional_price')
                ->where('product_variants.item_code', $product_code)->where('products.is_active', true)
                ->first();
            $product_variant_id = $lims_product_data->product_variant_id;
            $lims_product_data->code = $lims_product_data->item_code;
            $lims_product_data->price += $lims_product_data->additional_price;
        }
        $product[] = $lims_product_data->name;
        $product[] = $lims_product_data->code;
        if ($lims_product_data->promotion && $todayDate <= $lims_product_data->last_date) {
            $product[] = $lims_product_data->promotion_price;
        } else
            $product[] = $lims_product_data->price;

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
        return $product;
    }

    public function productQuotationData($id)
    {
        $lims_product_quotation_data = ProductQuotation::where('quotation_id', $id)->get();
        foreach ($lims_product_quotation_data as $key => $product_quotation_data) {
            $product = Product::find($product_quotation_data->product_id);
            if ($product_quotation_data->variant_id) {
                $lims_product_variant_data = ProductVariant::select('item_code')->FindExactProduct($product_quotation_data->product_id, $product_quotation_data->variant_id)->first();
                $product->code = $lims_product_variant_data->item_code;
            }
            if ($product_quotation_data->sale_unit_id) {
                $unit_data = Unit::find($product_quotation_data->sale_unit_id);
                $unit = $unit_data->unit_code;
            } else
                $unit = '';

            $product_quotation[0][$key] = $product->name . ' [' . $product->code . ']';
            $product_quotation[1][$key] = $product_quotation_data->qty;
            $product_quotation[2][$key] = $unit;
            $product_quotation[3][$key] = $product_quotation_data->tax;
            $product_quotation[4][$key] = $product_quotation_data->tax_rate;
            $product_quotation[5][$key] = $product_quotation_data->discount;
            $product_quotation[6][$key] = $product_quotation_data->total;
        }
        return $product_quotation;
    }

    public function edit($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('quotes-edit')) {
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_biller_list = Biller::where('is_active', true)->get();
            $lims_supplier_list = Supplier::where('is_active', true)->get();
            $lims_tax_list = Tax::where('is_active', true)->get();
            $lims_quotation_data = Quotation::find($id);
            $lims_product_quotation_data = ProductQuotation::where('quotation_id', $id)->get();
            return view('quotation.edit', compact('lims_warehouse_list', 'lims_biller_list', 'lims_tax_list', 'lims_quotation_data', 'lims_product_quotation_data', 'lims_supplier_list'));
        } else
            return redirect()->back()->with('not_permitted', 'Lo siento! Usted no tiene acceso a este modulo');
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
            if ($v->fails())
                return redirect()->back()->withErrors($v->errors());

            $documentName = $document->getClientOriginalName();
            $document->move('public/quotation/documents', $documentName);
            $data['document'] = $documentName;
        }
        $lims_quotation_data = Quotation::find($id);
        $lims_product_quotation_data = ProductQuotation::where('quotation_id', $id)->get();
        //update quotation table
        $lims_quotation_data->update($data);
        if ($lims_quotation_data->quotation_status == 2) {
            //collecting mail data
            $lims_customer_data = Customer::find($data['customer_id']);
            $mail_data['email'] = $lims_customer_data->email;
            $mail_data['reference_no'] = $lims_quotation_data->reference_no;
            $mail_data['total_qty'] = $data['total_qty'];
            $mail_data['total_price'] = $data['total_price'];
            $mail_data['order_tax'] = $data['order_tax'];
            $mail_data['order_tax_rate'] = $data['order_tax_rate'];
            $mail_data['order_discount'] = $data['order_discount'];
            $mail_data['shipping_cost'] = $data['shipping_cost'];
            $mail_data['grand_total'] = $data['grand_total'];
        }
        $product_id = $data['product_id'];
        $product_variant_id = $data['product_variant_id'];
        $qty = $data['qty'];
        $sale_unit = $data['sale_unit'];
        $net_unit_price = $data['net_unit_price'];
        $discount = $data['discount'];
        $tax_rate = $data['tax_rate'];
        $tax = $data['tax'];
        $total = $data['subtotal'];

        foreach ($lims_product_quotation_data as $key => $product_quotation_data) {
            $old_product_id[] = $product_quotation_data->product_id;
            $lims_product_data = Product::select('id')->find($product_quotation_data->product_id);
            if ($product_quotation_data->variant_id) {
                $lims_product_variant_data = ProductVariant::select('id')->FindExactProduct($product_quotation_data->product_id, $product_quotation_data->variant_id)->first();
                $old_product_variant_id[] = $lims_product_variant_data->id;
                if (!in_array($lims_product_variant_data->id, $product_variant_id))
                    $product_quotation_data->delete();
            } else {
                $old_product_variant_id[] = null;
                if (!in_array($product_quotation_data->product_id, $product_id))
                    $product_quotation_data->delete();
            }
        }

        foreach ($product_id as $i => $pro_id) {
            if ($sale_unit[$i] != 'n/a') {
                $lims_sale_unit_data = Unit::where('unit_name', $sale_unit[$i])->first();
                $sale_unit_id = $lims_sale_unit_data->id;
            } else
                $sale_unit_id = 0;
            $lims_product_data = Product::select('id', 'name', 'is_variant')->find($pro_id);
            if ($sale_unit_id)
                $mail_data['unit'][$i] = $lims_sale_unit_data->unit_code;
            else
                $mail_data['unit'][$i] = '';
            $input['quotation_id'] = $id;
            $input['product_id'] = $pro_id;
            $input['qty'] = $mail_data['qty'][$i] = $qty[$i];
            $input['sale_unit_id'] = $sale_unit_id;
            $input['net_unit_price'] = $net_unit_price[$i];
            $input['discount'] = $discount[$i];
            $input['tax_rate'] = $tax_rate[$i];
            $input['tax'] = $tax[$i];
            $input['total'] = $mail_data['total'][$i] = $total[$i];
            $flag = 1;
            if ($lims_product_data->is_variant) {
                $lims_product_variant_data = ProductVariant::select('variant_id')->where('id', $product_variant_id[$i])->first();
                $input['variant_id'] = $lims_product_variant_data->variant_id;
                if (in_array($product_variant_id[$i], $old_product_variant_id)) {
                    ProductQuotation::where([
                        ['product_id', $pro_id],
                        ['variant_id', $input['variant_id']],
                        ['quotation_id', $id]
                    ])->update($input);
                } else {
                    ProductQuotation::create($input);
                }
                $variant_data = Variant::find($input['variant_id']);
                $mail_data['products'][$i] = $lims_product_data->name . ' [' . $variant_data->name . ']';
            } else {
                $input['variant_id'] = null;
                if (in_array($pro_id, $old_product_id)) {
                    ProductQuotation::where([
                        ['product_id', $pro_id],
                        ['quotation_id', $id]
                    ])->update($input);
                } else {
                    ProductQuotation::create($input);
                }
                $mail_data['products'][$i] = $lims_product_data->name;
            }
        }

        $message = 'Proforma actualizada';

        if ($lims_quotation_data->quotation_status == 2 && $mail_data['email']) {
            try {
                Mail::send('mail.quotation_details', $mail_data, function ($message) use ($mail_data) {
                    $message->to($mail_data['email'])->subject('Quotation Details');
                });
            } catch (\Exception $e) {
                $message = 'Proforma actualizada. Por favor configurar <a href="setting/mail_setting">ajuste de correo</a> para enviar email.';
            }
        }
        return redirect('quotations')->with('message', $message);
    }

    public function createSale($id)
    {
        $lims_customer_list = Customer::where('is_active', true)->get();
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_biller_list = Biller::where('is_active', true)->get();
        $lims_tax_list = Tax::where('is_active', true)->get();
        $lims_quotation_data = Quotation::find($id);
        $lims_product_quotation_data = ProductQuotation::where('quotation_id', $id)->get();
        $lims_pos_setting_data = PosSetting::latest()->first();
        return view('quotation.create_sale', compact('lims_customer_list', 'lims_warehouse_list', 'lims_biller_list', 'lims_tax_list', 'lims_quotation_data', 'lims_product_quotation_data', 'lims_pos_setting_data'));
    }

    public function createPurchase($id)
    {
        $lims_supplier_list = Supplier::where('is_active', true)->get();
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_tax_list = Tax::where('is_active', true)->get();
        $lims_quotation_data = Quotation::find($id);
        $lims_product_quotation_data = ProductQuotation::where('quotation_id', $id)->get();
        $lims_product_list_without_variant = $this->productWithoutVariant();
        $lims_product_list_with_variant = $this->productWithVariant();

        return view('quotation.create_purchase', compact('lims_product_list_without_variant', 'lims_product_list_with_variant', 'lims_supplier_list', 'lims_warehouse_list', 'lims_tax_list', 'lims_quotation_data', 'lims_product_quotation_data'));
    }

    public function productWithoutVariant()
    {
        return Product::ActiveStandard()->select('id', 'name', 'code')
            ->whereNull('is_variant')->get();
    }

    public function productWithVariant()
    {
        return Product::join('product_variants', 'products.id', 'product_variants.product_id')
            ->ActiveStandard()
            ->whereNotNull('is_variant')
            ->select('products.id', 'products.name', 'product_variants.item_code')
            ->orderBy('position')->get();
    }

    public function deleteBySelection(Request $request)
    {
        $quotation_id = $request['quotationIdArray'];
        foreach ($quotation_id as $id) {
            $lims_quotation_data = Quotation::find($id);
            $lims_product_quotation_data = ProductQuotation::where('quotation_id', $id)->get();
            foreach ($lims_product_quotation_data as $product_quotation_data) {
                $product_quotation_data->delete();
            }
            $lims_quotation_data->delete();
        }
        return 'Proforma(s) eliminada!';
    }

    public function destroy($id)
    {
        $lims_quotation_data = Quotation::find($id);
        $lims_product_quotation_data = ProductQuotation::where('quotation_id', $id)->get();
        foreach ($lims_product_quotation_data as $product_quotation_data) {
            $product_quotation_data->delete();
        }
        $lims_quotation_data->delete();
        return redirect('quotations')->with('not_permitted', 'Proforma eliminada!');
    }


    public function genInvoice($id)
    {
        $lims_quotation_data = Quotation::find($id);
        $lims_product_quotation_data = ProductQuotation::where('quotation_id', $id)->get();
        $lims_biller_data = Biller::find($lims_quotation_data->biller_id);
        $lims_pos_setting_data = PosSetting::latest()->first();
        $general_setting = GeneralSetting::latest()->first();

        $numberToWords = new NumberToWords();
        if (\App::getLocale() == 'ar' || \App::getLocale() == 'hi' || \App::getLocale() == 'vi' || \App::getLocale() == 'en-gb') {
            $numberTransformer = $numberToWords->getNumberTransformer('en');
        } else {
            $numberTransformer = $numberToWords->getNumberTransformer(\App::getLocale());
        }
        $numberInWords = $numberTransformer->toWords($lims_quotation_data->grand_total);
        $cadenaCentavos = $this->obtenerParteDecimalLiteral($lims_quotation_data->grand_total);

        switch ($lims_pos_setting_data->quotation_printer) {
            case 1:
                view()->share('quotation.invoicemiddle', compact('lims_quotation_data', 'lims_quotation_data', 'lims_biller_data', 'numberInWords', 'cadenaCentavos'));
                $pdf = Pdf::loadView('quotation.invoicemiddle', compact('lims_quotation_data', 'lims_product_quotation_data', 'lims_biller_data', 'numberInWords', 'cadenaCentavos'));
                $pdf->setPaper("a4", 'portrait');
                break;
            case 2:
                view()->share('quotation.invoice80mm', compact('lims_quotation_data', 'lims_quotation_data', 'lims_biller_data', 'numberInWords', 'cadenaCentavos'));
                $pdf = Pdf::loadView('quotation.invoice80mm', compact('lims_quotation_data', 'lims_product_quotation_data', 'lims_biller_data', 'numberInWords', 'cadenaCentavos'));
                //$customPaper = array(0, 0, 287, 700);
                $cantItems = $lims_product_quotation_data->count();
                $largo = (350 + ($cantItems*55));
                $customPaper = array(0,0,280,$largo);
                $pdf->setPaper($customPaper, 'portrait');
                break;
            case 3:
                $invoice = "";
                $invoice .= "<u><center><font size='bigger'>PROFORMA</font></center></u><br>";
                $invoice .= "<u><center><font size='small'>" . $lims_biller_data->company_name . "</font></center></u><br>";
                $invoice .= "<table><tbody>";
                $invoice .= "<tr>";
                $invoice .= "<td><b>" . trans('file.reference_quotation') . ": </b></td><td>" . $lims_quotation_data->reference_no . "</td>";
                $invoice .= "</tr><tr>";
                $invoice .= "<td><b>" . trans('file.Date') . ":</b></td><td>" . date($general_setting->date_format, strtotime($lims_quotation_data->created_at)) . "</td>";
                $invoice .= "</tr><tr>";
                $invoice .= "<td><b>" . trans('file.Phone Number') . ": </b></td><td>" . $lims_quotation_data->warehouse->phone . "</td>";
                $invoice .= "</tr><tr>";
                $invoice .= "<td><b>" . trans('file.Address') . ": </b></td><td>" . $lims_quotation_data->warehouse->address . "</td>";
                $invoice .= "</tr><tr>";
                $invoice .= "<td><b>" . trans('file.Biller') . ": </b></td><td>" . $lims_biller_data->name . "</td>";
                $invoice .= "</tr><tr>";
                $invoice .= "<td><b>" . trans('file.customer') . ": </b></td><td>" . $lims_quotation_data->customer->name . "</td>";
                $invoice .= "</tr>";
                if ($lims_quotation_data->valid_date) {
                    $invoice .= "<tr>";
                    $invoice .= "<td><b>" . trans('file.date_valid') . ": </b></td><td>" . date($general_setting->date_format, strtotime($lims_quotation_data->valid_date)) . "</td>";
                    $invoice .= "</tr>";
                }
                $invoice .= "</tbody></table>";
                $invoice .= "<table style='font-size:12px'>";
                $invoice .= "<thead><tr><th>Cant.</th><th >Detalle</th><th>P/U</th><th>Subtotal</th></tr></thead>";
                $invoice .= "<tbody>";
                foreach ($lims_product_quotation_data as $quotation) {
                    $lims_product_data = Product::find($quotation->product_id);
                    if ($quotation->variant_id) {
                        $variant_data = Variant::find($quotation->variant_id);
                        $product_name = $lims_product_data->name . ' [' . $variant_data->name . ']';
                    } else {
                        $product_name = $lims_product_data->name;
                    }
                    $invoice .= "<tr>";
                    $invoice .= "<td>" . $quotation->qty . "</td>";
                    $invoice .= "<td>" . $product_name . "</td>";
                    $invoice .= "<td>" . number_format((float) ($quotation->total / $quotation->qty), 2, '.', '') . "</td>";
                    $invoice .= "<td>" . number_format((float) $quotation->total, 2, '.', '') . "</td>";
                    $invoice .= "</tr>";
                }
                $invoice .= "</tbody><tfoot>";
                $invoice .= "<tr><th align='left' colspan='3'>" . trans('file.Total') . ":</th><th colspan='3'>" . number_format((float) $lims_quotation_data->total_price, 2, '.', '') . "</th></tr>";
                if ($lims_quotation_data->order_tax) {
                    $invoice .= "<tr><th align='left' colspan='3'>" . trans('file.Order Tax') . ":</th><th colspan='3'>" . number_format((float) $lims_quotation_data->order_tax, 2, '.', '') . "</th></tr>";
                }
                if ($lims_quotation_data->order_discount) {
                    $invoice .= "<tr><th align='left' colspan='3'>" . trans('file.Order Discount') . ":</th><th colspan='3'>" . number_format((float) $lims_quotation_data->order_discount, 2, '.', '') . "</th></tr>";
                }
                if ($lims_quotation_data->shipping_cost) {
                    $invoice .= "<tr><th align='left' colspan='3'>" . trans('file.Shipping Cost') . ":</th><th colspan='3'>" . number_format((float) $lims_quotation_data->shipping_cost, 2, '.', '') . "</th></tr>";
                }
                $invoice .= "<tr><th align='left' colspan='3'>" . trans('file.grand total') . ":</th><th colspan='3'>" . number_format((float) $lims_quotation_data->grand_total, 2, '.', '') . "</th></tr>";
                $invoice .= "<tr>";
                if ($general_setting->currency_position == 'prefix') {
                    $invoice .= "<th align='left' colspan='10'>" . trans('file.In Words') . ": <span>" . $general_setting->currency . " </span><span>" . str_replace('-', ' ', $numberInWords) . "</span></th>";
                } else {
                    $invoice .= "<th align='left' colspan='10'>" . trans('file.In Words') . ":<span>" . str_replace('-', ' ', $numberInWords) . " </span><span>" . $general_setting->currency . "</span></th>";
                }
                $invoice .= "</tr>";
                $invoice .= "</tfoot></table>";
                $invoice .= "<p align='center'><small>Proforma Generada por " . $general_setting->site_title .". ". trans('file.Developed By') . " Gisul S.R.L</strong></small></p>";
                $pdf = Pdf::loadHTML($invoice);
                $customPaper = array(0, 0, 287, 574);
                $pdf->setPaper($customPaper, 'portrait');
                break;
            default:
                view()->share('quotation.invoicemiddle', compact('lims_quotation_data', 'lims_quotation_data', 'lims_biller_data', 'numberInWords', 'cadenaCentavos'));
                $pdf = Pdf::loadView('quotation.invoicemiddle', compact('lims_quotation_data', 'lims_product_quotation_data', 'lims_biller_data', 'numberInWords', 'cadenaCentavos'));
                $pdf->setPaper("a4", 'portrait');
                break;
        }
        return $pdf->stream("proforma_" . $lims_quotation_data->reference_no . ".pdf", array("Attachment" => false));

    }

    function obtenerParteDecimalLiteral($numero) {        
        $parteEntera = floor($numero); 
        $centavos = round(($numero - $parteEntera) * 100); 
        return sprintf("%02d", $centavos).'/100';  
    }
}