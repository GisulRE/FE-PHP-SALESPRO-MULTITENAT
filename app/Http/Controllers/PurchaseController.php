<?php

namespace App\Http\Controllers;

use App\Account;
use App\Payment;
use App\PaymentWithCheque;
use App\PaymentWithCreditCard;
use App\PosSetting;
use App\Product;
use App\Product_Warehouse;
use App\ProductLote;
use App\ProductPurchase;
use App\ProductVariant;
use App\Purchase;
use App\Supplier;
use App\Tax;
use App\Unit;
use App\User;
use App\Warehouse;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Log;
use DB;
use Spatie\Permission\Models\Role;
use Stripe\Stripe;

class PurchaseController extends Controller
{
    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('purchases-index')) {
            if (Auth::user()->role_id > 2 && config('staff_access') == 'own') {
                $lims_purchase_list = Purchase::orderBy('id', 'desc')->where('user_id', Auth::id())->get();
            } else {
                $lims_purchase_list = Purchase::orderBy('id', 'desc')->get();
            }

            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission) {
                $all_permission[] = $permission->name;
            }

            if (empty($all_permission)) {
                $all_permission[] = 'dummy text';
            }

            $lims_pos_setting_data = PosSetting::latest()->first();
            $lims_account_list = Account::where('is_active', true)->get();
            return view('purchase.index', compact('lims_purchase_list', 'lims_account_list', 'all_permission', 'lims_pos_setting_data'));
        } else {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }
    }

    public function purchaseData(Request $request)
    {
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
            3 => 'supplier_id',
            4 => 'status',
            5 => 'grand_total',
            6 => 'paid_amount',
            7 => 'due',
            8 => 'payment_status',
        );

        if (Auth::user()->role_id > 2 && config('staff_access') == 'own') {
            $totalData = Purchase::where('user_id', Auth::id())->count();
        } else {
            $totalData = Purchase::count();
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
                $purchases = Purchase::with('supplier', 'warehouse')->offset($start)
                    ->where('user_id', Auth::id())
                    ->limit($limit)
                    ->orderBy($order, $dir)
                    ->get();
            } else {
                $purchases = Purchase::with('supplier', 'warehouse')->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)
                    ->get();
            }
        } else {
            $search = $request->input('search.value');
            if (Auth::user()->role_id > 2 && config('staff_access') == 'own') {
                $purchases = Purchase::select('purchases.*')
                    ->with('supplier', 'warehouse')
                    ->leftJoin('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
                    ->whereDate('purchases.created_at', '=', date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                    ->where('purchases.user_id', Auth::id())
                    ->orwhere([
                        ['purchases.reference_no', 'LIKE', "%{$search}%"],
                        ['purchases.user_id', Auth::id()],
                    ])
                    ->orwhere([
                        ['suppliers.name', 'LIKE', "%{$search}%"],
                        ['purchases.user_id', Auth::id()],
                    ])
                    ->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)->get();

                $totalFiltered = Purchase::leftJoin('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
                    ->whereDate('purchases.created_at', '=', date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                    ->where('purchases.user_id', Auth::id())
                    ->orwhere([
                        ['purchases.reference_no', 'LIKE', "%{$search}%"],
                        ['purchases.user_id', Auth::id()],
                    ])
                    ->orwhere([
                        ['suppliers.name', 'LIKE', "%{$search}%"],
                        ['purchases.user_id', Auth::id()],
                    ])
                    ->count();
            } else {
                $purchases = Purchase::select('purchases.*')
                    ->with('supplier', 'warehouse')
                    ->leftJoin('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
                    ->whereDate('purchases.created_at', '=', date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                    ->orwhere('purchases.reference_no', 'LIKE', "%{$search}%")
                    ->orwhere('suppliers.name', 'LIKE', "%{$search}%")
                    ->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)
                    ->get();

                $totalFiltered = Purchase::leftJoin('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
                    ->whereDate('purchases.created_at', '=', date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                    ->orwhere('purchases.reference_no', 'LIKE', "%{$search}%")
                    ->orwhere('suppliers.name', 'LIKE', "%{$search}%")
                    ->count();
            }
        }
        $data = array();
        if (!empty($purchases)) {
            foreach ($purchases as $key => $purchase) {
                $nestedData['id'] = $purchase->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime($purchase->created_at->toDateString()));
                $nestedData['reference_no'] = $purchase->reference_no;

                if ($purchase->supplier_id) {
                    $supplier = $purchase->supplier;
                } else {
                    $supplier = new Supplier();
                }
                $nestedData['supplier'] = $supplier->name;
                if ($purchase->status == 1) {
                    $nestedData['purchase_status'] = '<div class="badge badge-success">' . trans('file.Recieved') . '</div>';
                    $purchase_status = trans('file.Recieved');
                } elseif ($purchase->status == 2) {
                    $nestedData['purchase_status'] = '<div class="badge badge-warning">' . trans('file.Partial') . '</div>';
                    $purchase_status = trans('file.Partial');
                } elseif ($purchase->status == 3) {
                    $nestedData['purchase_status'] = '<div class="badge badge-danger">' . trans('file.Pending') . '</div>';
                    $purchase_status = trans('file.Pending');
                } else {
                    $nestedData['purchase_status'] = '<div class="badge badge-danger">' . trans('file.Ordered') . '</div>';
                    $purchase_status = trans('file.Ordered');
                }

                if ($purchase->payment_status == 1) {
                    $nestedData['payment_status'] = '<div class="badge badge-danger">' . trans('file.Due') . '</div>';
                } else {
                    $nestedData['payment_status'] = '<div class="badge badge-success">' . trans('file.Paid') . '</div>';
                }

                $nestedData['grand_total'] = number_format($purchase->grand_total, 2);
                $nestedData['paid_amount'] = number_format($purchase->paid_amount, 2);
                $nestedData['due'] = number_format($purchase->grand_total - $purchase->paid_amount, 2);
                $nestedData['options'] = '<div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . trans("file.action") . '
                              <span class="caret"></span>
                              <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                                <li>
                                    <button type="button" class="btn btn-link view"><i class="fa fa-eye"></i> ' . trans('file.View') . '</button>
                                </li>';
                if (in_array("purchases-edit", $request['all_permission']) && $purchase->status != 1) {
                    $nestedData['options'] .= '<li>
                        <a href="' . route('purchases.edit', $purchase->id) . '" class="btn btn-link"><i class="dripicons-document-edit"></i> ' . trans('file.edit') . '</a>
                        </li>';
                }

                $nestedData['options'] .=
                    '<li>
                        <button type="button" class="add-payment btn btn-link" data-id = "' . $purchase->id . '" data-toggle="modal" data-target="#add-payment"><i class="fa fa-plus"></i> ' . trans('file.Add Payment') . '</button>
                    </li>
                    <li>
                        <button type="button" class="get-payment btn btn-link" data-id = "' . $purchase->id . '"><i class="fa fa-money"></i> ' . trans('file.View Payment') . '</button>
                    </li>';
                if (in_array("purchases-delete", $request['all_permission'])) {
                    $nestedData['options'] .= \Form::open(["route" => ["purchases.destroy", $purchase->id], "method" => "DELETE"]) . '
                            <li>
                              <button type="submit" class="btn btn-link" onclick="return confirmDelete()"><i class="dripicons-trash"></i> ' . trans("file.delete") . '</button>
                            </li>' . \Form::close() . '
                        </ul>
                    </div>';
                }

                // data for purchase details by one click
                $user = User::find($purchase->user_id);

                $nestedData['purchase'] = array(
                    '[ "' . date(config('date_format'), strtotime($purchase->created_at->toDateString())) . '"',
                    ' "' . $purchase->reference_no . '"',
                    ' "' . $purchase_status . '"',
                    ' "' . $purchase->id . '"',
                    ' "' . $purchase->warehouse->name . '"',
                    ' "' . $purchase->warehouse->phone . '"',
                    ' "' . $purchase->warehouse->address . '"',
                    ' "' . $supplier->name . '"',
                    ' "' . $supplier->company_name . '"',
                    ' "' . $supplier->email . '"',
                    ' "' . $supplier->phone_number . '"',
                    ' "' . $supplier->address . '"',
                    ' "' . $supplier->city . '"',
                    ' "' . $purchase->total_tax . '"',
                    ' "' . $purchase->total_discount . '"',
                    ' "' . $purchase->total_cost . '"',
                    ' "' . $purchase->order_tax . '"',
                    ' "' . $purchase->order_tax_rate . '"',
                    ' "' . $purchase->order_discount . '"',
                    ' "' . $purchase->shipping_cost . '"',
                    ' "' . $purchase->grand_total . '"',
                    ' "' . $purchase->paid_amount . '"',
                    ' "' . $purchase->note . '"',
                    ' "' . $user->name . '"',
                    ' "' . $user->email . '"]',
                );
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
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('purchases-add')) {
            $lims_supplier_list = Supplier::where('is_active', true)->get();
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_tax_list = Tax::where('is_active', true)->get();

            return view('purchase.create', compact('lims_supplier_list', 'lims_warehouse_list', 'lims_tax_list'));
        } else {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }
    }

    public function productWithoutVariant($data)
    {
        return Product::ActiveStandard()->select('id', 'name', 'code', 'type', 'is_variant')
            ->where('name', 'LIKE', "%{$data['term']}%")
            ->orWhere('code', 'LIKE', "%{$data['term']}%")
            ->whereNull('is_variant')->get();
    }

    public function productWithoutVariantInsumo($data)
    {
        return Product::ActiveInsumo()->select('id', 'name', 'code', 'type', 'is_variant')
            ->where('name', 'LIKE', "%{$data['term']}%")
            ->orWhere('code', 'LIKE', "%{$data['term']}%")
            ->whereNull('is_variant')->get();
    }

    public function productWithVariant($data)
    {
        return Product::join('product_variants', 'products.id', 'product_variants.product_id', 'products.type')
            ->ActiveStandard()
            ->whereNotNull('is_variant')
            ->where('name', 'LIKE', "%{$data['term']}%")
            ->orWhere('code', 'LIKE', "%{$data['term']}%")
            ->select('products.id', 'products.name', 'product_variants.item_code')
            ->orderBy('position')->get();
    }

    public function getProduct(Request $request)
    {
        $data = $request->all();
        $lims_product_list = [];
        $lims_product_list_without_variant = $this->productWithoutVariant($data);
        $lims_product_list_without_variant_insumo = $this->productWithoutVariantInsumo($data);
        $lims_product_list_with_variant = $this->productWithVariant($data);
        foreach ($lims_product_list_without_variant as $key => $value) {
            if ($value->type == 'standard' && $value->is_variant == null)
                $lims_product_list[] = $value->code . ' (' . $value->name . ') ';
        }
        foreach ($lims_product_list_without_variant_insumo as $key => $value) {
            if ($value->type == 'insumo')
                $lims_product_list[] = $value->code . ' (' . $value->name . ') ';
        }
        foreach ($lims_product_list_with_variant as $key => $value) {
            $lims_product_list[] = $value->item_code . ' (' . $value->name . ') ';
        }
        return $lims_product_list;
    }

    public function limsProductSearch(Request $request)
    {
        $product_code = explode(" ", $request['data']);
        $product = [];
        $lims_product_data = Product::where([['code', $product_code[0]], ['is_active', true]])->first();
        if (!$lims_product_data) {
            $lims_product_data = Product::join('product_variants', 'products.id', 'product_variants.product_id')
                ->select('products.*', 'product_variants.item_code')
                ->where([['product_variants.item_code', $product_code], ['products.is_active', true]])
                ->first();
        }

        $product[] = $lims_product_data->name;
        if ($lims_product_data->is_variant) {
            $product[] = $lims_product_data->item_code;
        } else {
            $product[] = $lims_product_data->code;
        }

        $product[] = $lims_product_data->cost;

        if ($lims_product_data->tax_id) {
            $lims_tax_data = Tax::find($lims_product_data->tax_id);
            $product[] = $lims_tax_data->rate;
            $product[] = $lims_tax_data->name;
        } else {
            $product[] = 0;
            $product[] = 'No Tax';
        }
        $product[] = $lims_product_data->tax_method;

        $units = Unit::where("base_unit", $lims_product_data->unit_id)
            ->orWhere('id', $lims_product_data->unit_id)
            ->get();
        $unit_name = array();
        $unit_operator = array();
        $unit_operation_value = array();
        foreach ($units as $unit) {
            if ($lims_product_data->purchase_unit_id == $unit->id) {
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
        $product[] = $lims_product_data->id;
        return $product;
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->except('document');
            $data['user_id'] = Auth::id();
            $last_ref = Purchase::get()->last();
            if ($last_ref != null) {
                $nros = explode("-", $last_ref['reference_no']);
                $nro = ltrim($nros[1], "0");
                $nro++;
                $nro = str_pad($nro, 8, "0", STR_PAD_LEFT);
            } else {
                $nro = str_pad(1, 8, "0", STR_PAD_LEFT);
            }
            $data['reference_no'] = 'NRC-' . $nro;
            if ($data['created_at']) {
                $data['created_at'] = $data['created_at'] . " " . date("h:i:s");
            } else {
                $data['created_at'] = date("Y-m-d h:i:s");
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
                $document->move('public/documents/purchase', $documentName);
                $data['document'] = $documentName;
            }

            $lims_purchase_data = Purchase::create($data);
            Log::info("Purchase created successfully");
            Log::info('Purchase_id: ' . $lims_purchase_data->id);

            $product_id = $data['product_id'];
            $product_code = $data['product_code'];
            $qty = $data['qty'];
            $recieved = $data['recieved'];
            $purchase_unit = $data['purchase_unit'];
            $net_unit_cost = $data['net_unit_cost'];
            $discount = $data['discount'];
            $tax_rate = $data['tax_rate'];
            $tax = $data['tax'];
            $total = $data['subtotal'];
            $lotes = $data['lote_data'];
            $product_purchase = [];
            /* Register Item Products */
            foreach ($product_id as $i => $id) {
                $lims_purchase_unit_data = Unit::where('unit_name', $purchase_unit[$i])->first();

                if ($lims_purchase_unit_data->operator == '*') {
                    $quantity = $recieved[$i] * $lims_purchase_unit_data->operation_value;
                } else {
                    $quantity = $recieved[$i] / $lims_purchase_unit_data->operation_value;
                }
                $lims_product_data = Product::find($id);
                if ($lims_product_data->is_variant) {
                    $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')->FindExactProductWithCode($lims_product_data->id, $product_code[$i])->first();
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $id],
                        ['variant_id', $lims_product_variant_data->variant_id],
                        ['warehouse_id', $data['warehouse_id']],
                    ])->first();
                    $product_purchase['variant_id'] = $lims_product_variant_data->variant_id;
                    //add quantity to product variant table
                    $lims_product_variant_data->qty += $quantity;
                    //$lims_product_variant_data->save();
                } else {
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $id],
                        ['warehouse_id', $data['warehouse_id']],
                    ])->first();
                }
                //add quantity to product table
                $lims_product_data->cost = $net_unit_cost[$i];
                $lims_product_data->save();
                //add quantity to warehouse
                if ($lims_product_warehouse_data) { //aclarar
                    // $lims_product_warehouse_data->qty = $lims_product_warehouse_data->qty + $quantity;
                } else {
                    $lims_product_warehouse_data = new Product_Warehouse();
                    $lims_product_warehouse_data->product_id = $id;
                    $lims_product_warehouse_data->warehouse_id = $data['warehouse_id'];
                    if ($lims_product_data->is_variant) {
                        $lims_product_warehouse_data->variant_id = $lims_product_variant_data->variant_id;
                    }

                }

                $lims_product_warehouse_data->save();

                $product_purchase['purchase_id'] = $lims_purchase_data->id;
                $product_purchase['product_id'] = $id;
                $product_purchase['qty'] = $qty[$i];
                $product_purchase['recieved'] = $recieved[$i];
                $product_purchase['purchase_unit_id'] = $lims_purchase_unit_data->id;
                $product_purchase['net_unit_cost'] = $net_unit_cost[$i];
                $product_purchase['discount'] = $discount[$i];
                $product_purchase['tax_rate'] = $tax_rate[$i];
                $product_purchase['tax'] = $tax[$i];
                $product_purchase['total'] = $total[$i];
                ProductPurchase::create($product_purchase);
                /* Register Lotes */
                if ($lotes != null && ($lotes[$i] != null || $lotes[$i] != "")) {
                    $lote_list = json_decode(urldecode($lotes[$i]));
                    foreach ($lote_list as $lote) {
                        $product_lote = new ProductLote();
                        $product_lote->name = $lote[0];
                        $product_lote->expiration = $lote[1];
                        $product_lote->purchase_id = $lims_purchase_data->id;
                        $product_lote->idwarehouse = $data['warehouse_id'];
                        $product_lote->idproduct = $id;
                        $product_lote->supplier = $data['supplier_id'];
                        $product_lote->qty = $lote[2];
                        $product_lote->stock = $lote[2];
                        $product_lote->status = "1";
                        $product_lote->fabrication_date = date("Ymd");
                        $product_lote->save();
                    }
                }
            }
            Log::info("Detail Purchase created successfully");
            DB::commit();
            return redirect('purchases')->with('message', 'Compra Creada con éxito');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("error save purchase: " . $th->getMessage());
            return redirect('purchases')->with('not_permitted', 'Fallo al crear Compra, Intente de nuevo!');
        }
    }

    public function productPurchaseData($id)
    {
        $product_purchase = [];
        $lims_product_purchase_data = ProductPurchase::where('purchase_id', $id)->get();
        foreach ($lims_product_purchase_data as $key => $product_purchase_data) {
            $product = Product::find($product_purchase_data->product_id);
            $unit = Unit::find($product_purchase_data->purchase_unit_id);
            if ($product_purchase_data->variant_id) {
                $lims_product_variant_data = ProductVariant::FindExactProduct($product->id, $product_purchase_data->variant_id)->select('item_code')->first();
                $product->code = $lims_product_variant_data->item_code;
            }
            $product_purchase[0][$key] = $product->name . ' [' . $product->code . ']';
            $product_purchase[1][$key] = $product_purchase_data->qty;
            $product_purchase[2][$key] = $unit->unit_code;
            $product_purchase[3][$key] = $product_purchase_data->tax;
            $product_purchase[4][$key] = $product_purchase_data->tax_rate;
            $product_purchase[5][$key] = $product_purchase_data->discount;
            $product_purchase[6][$key] = $product_purchase_data->total;
        }
        return $product_purchase;
    }

    public function purchaseByCsv()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('purchases-add')) {
            $lims_supplier_list = Supplier::where('is_active', true)->get();
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_tax_list = Tax::where('is_active', true)->get();

            return view('purchase.import', compact('lims_supplier_list', 'lims_warehouse_list', 'lims_tax_list'));
        } else {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }
    }

    public function importPurchase(Request $request)
    {
        try {
            DB::beginTransaction();
            //get the file
            $upload = $request->file('file');
            $ext = pathinfo($upload->getClientOriginalName(), PATHINFO_EXTENSION);
            //checking if this is a CSV file
            if ($ext != 'csv') {
                return redirect()->back()->with('message', 'Por favor cargar el archivo CVS');
            }

            $filePath = $upload->getRealPath();
            $file_handle = fopen($filePath, 'r');
            $i = 0;
            //validate the file
            while (!feof($file_handle)) {
                $current_line = fgetcsv($file_handle);
                if ($current_line && $i > 0) {
                    if ($current_line[0] != ";;;;") {
                        $product_data[] = Product::where([['code', $current_line[0]], ['is_active', true]])->first();
                        if (!$product_data[$i - 1]) {
                            return redirect()->back()->with('message', 'Producto no existe en fila ' . $i . '!');
                        }

                        $unit[] = Unit::where('unit_code', $current_line[2])->first();
                        if (!$unit[$i - 1]) {
                            return redirect()->back()->with('message', 'Unidad de compra no existe!');
                        }

                        /*if(strtolower($current_line[5]) != "no tax"){
                        $tax[] = Tax::where('name', $current_line[5])->first();
                        if(!$tax[$i-1])
                        return redirect()->back()->with('message', 'Tax name does not exist!');
                        }
                        else
                        $tax[$i-1]['rate'] = 0;
                        */
                        $tax[$i - 1]['rate'] = 0;
                        $qty[] = $current_line[1];
                        $discount[] = 0;
                        if (isset($current_line[4])) {
                            $exps = explode(";", $current_line[4]);
                            $expiration[] = $exps[0];
                            $cost[] = $current_line[3];
                        } else {
                            $expiration[] = null;
                            $costs = explode(";", $current_line[3]);
                            $cost[] = $costs[0];
                        }
                    }
                }
                $i++;
            }

            $data = $request->except('file');
            $data['reference_no'] = 'pr-' . date("Ymd") . '-' . date("his");
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
                $document->move('public/documents/purchase', $documentName);
                $data['document'] = $documentName;
            }
            $item = 0;
            $grand_total = $data['shipping_cost'];
            $data['user_id'] = Auth::id();
            Purchase::create($data);
            $lims_purchase_data = Purchase::latest()->first();

            foreach ($product_data as $key => $product) {
                $lims_product_data = Product::find($product['id']);
                if ($product['tax_method'] == 1) {
                    $net_unit_cost = $cost[$key] - $discount[$key];
                    $product_tax = $net_unit_cost * ($tax[$key]['rate'] / 100) * $qty[$key];
                    $total = ($net_unit_cost * $qty[$key]) + $product_tax;
                } elseif ($product['tax_method'] == 2) {
                    $net_unit_cost = (100 / (100 + $tax[$key]['rate'])) * ($cost[$key] - $discount[$key]);
                    $product_tax = ($cost[$key] - $discount[$key] - $net_unit_cost) * $qty[$key];
                    $total = ($cost[$key] - $discount[$key]) * $qty[$key];
                }
                if ($data['status'] == 1) {
                    if ($unit[$key]['operator'] == '*') {
                        $quantity = $qty[$key] * $unit[$key]['operation_value'];
                    } elseif ($unit[$key]['operator'] == '/') {
                        $quantity = $qty[$key] / $unit[$key]['operation_value'];
                    }
                    //add quantity to product table
                    $lims_product_data->cost = $net_unit_cost;
                    $lims_product_data->save();
                    $product['qty'] += $quantity;
                    $product_warehouse = Product_Warehouse::where([
                        ['product_id', $product['id']],
                        ['warehouse_id', $data['warehouse_id']],
                    ])->first();
                    if ($product_warehouse) {
                        $product_warehouse->qty += $quantity;
                        $product_warehouse->save();
                    } else {
                        $lims_product_warehouse_data = new Product_Warehouse();
                        $lims_product_warehouse_data->product_id = $product['id'];
                        $lims_product_warehouse_data->warehouse_id = $data['warehouse_id'];
                        $lims_product_warehouse_data->save();
                    }
                    $product_lote = new ProductLote();
                    $product_lote->idwarehouse = $data['warehouse_id'];
                    $product_lote->idproduct = $product['id'];
                    $product_lote->supplier = $data['supplier_id'];
                    $product_lote->qty = $quantity;
                    $product_lote->stock = $quantity;
                    if ($expiration[$key] == null) {
                        $product_lote->expiration = null;
                    } else {
                        $product_lote->expiration = $this->formatterdate($expiration[$key]);
                    }

                    $product_lote->status = "1";
                    $product_lote->fabrication_date = date("Ymd");
                    $product_lote->save();
                }

                $product_purchase = new ProductPurchase();
                $product_purchase->purchase_id = $lims_purchase_data->id;
                $product_purchase->product_id = $product['id'];
                $product_purchase->qty = $qty[$key];
                if ($data['status'] == 1) {
                    $product_purchase->recieved = $qty[$key];
                } else {
                    $product_purchase->recieved = 0;
                }

                $product_purchase->purchase_unit_id = $unit[$key]['id'];
                $product_purchase->net_unit_cost = number_format((float) $net_unit_cost, 2, '.', '');
                $product_purchase->discount = $discount[$key] * $qty[$key];
                $product_purchase->tax_rate = $tax[$key]['rate'];
                $product_purchase->tax = number_format((float) $product_tax, 2, '.', '');
                $product_purchase->total = number_format((float) $total, 2, '.', '');
                $product_purchase->save();
                $lims_purchase_data->total_qty += $qty[$key];
                $lims_purchase_data->total_discount += $discount[$key] * $qty[$key];
                $lims_purchase_data->total_tax += number_format((float) $product_tax, 2, '.', '');
                $lims_purchase_data->total_cost += number_format((float) $total, 2, '.', '');
            }
            $lims_purchase_data->item = $key + 1;
            $lims_purchase_data->order_tax = ($lims_purchase_data->total_cost - $lims_purchase_data->order_discount) * ($data['order_tax_rate'] / 100);
            $lims_purchase_data->grand_total = ($lims_purchase_data->total_cost + $lims_purchase_data->order_tax + $lims_purchase_data->shipping_cost) - $lims_purchase_data->order_discount;
            $lims_purchase_data->save();
            Log::info(" Total Import Purchase: " . $lims_purchase_data->item);
            Log::info("Import Purchase created successfully");
            DB::commit();
            return redirect('purchases')->with('message', 'Compras Importados con éxito');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("error save purchase: " . $th->getMessage());
            Log::error("error save purchase: " . $th->getLine());
            return redirect('purchases')->with('not_permitted', 'Fallo al importar Compra, Intente de nuevo!');
        }
    }

    public function formatterdate($date)
    {
        $dates = explode("/", $date);
        $day = $dates[0];
        $month = $dates[1];
        $year = $dates[2];
        $years = explode(";", $year);
        $year = $years[0];

        return $year . $month . $day;
    }

    public function edit($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('purchases-edit')) {
            $lims_supplier_list = Supplier::where('is_active', true)->get();
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_tax_list = Tax::where('is_active', true)->get();
            $lims_purchase_data = Purchase::find($id);
            $lims_product_purchase_data = ProductPurchase::where('purchase_id', $id)->get();

            return view('purchase.edit', compact('lims_warehouse_list', 'lims_supplier_list', 'lims_tax_list', 'lims_purchase_data', 'lims_product_purchase_data'));
        } else {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }
    }

    public function update(Request $request, $id)
    {       //return $request->all();
        try {
            DB::beginTransaction();
            $data = $request->except('document');
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
                $document->move('public/purchase/documents', $documentName);
                $data['document'] = $documentName;
            }
            //return dd($data);
            $balance = $data['grand_total'] - $data['paid_amount'];
            if ($balance < 0 || $balance > 0) {
                $data['payment_status'] = 1;
            } else {
                $data['payment_status'] = 2;
            }
            $lims_purchase_data = Purchase::find($id);

            $product_id = $data['product_id'];
            $product_code = $data['product_code'];
            $qty = $data['qty'];
            $recieved = $data['recieved'];
            $purchase_unit = $data['purchase_unit'];
            $net_unit_cost = $data['net_unit_cost'];
            $discount = $data['discount'];
            $tax_rate = $data['tax_rate'];
            $tax = $data['tax'];
            $total = $data['subtotal'];
            $lotes = $data['lote_data'];
            $product_purchase = [];


            $status_recieved = true;

            foreach ($product_id as $key => $pro_id) {

                $product_purchase = ProductPurchase::where('product_id', $pro_id)->where('purchase_id', $id)->first();
                if ($product_purchase == null) {
                    $product_purchase = new ProductPurchase();
                }
                Log::info("product_purchase: " . $lims_purchase_data->id);
                //Log::debug(json_encode($product_purchase));
                $lims_purchase_unit_data = Unit::where('unit_name', $purchase_unit[$key])->first();
                $lims_product_data = Product::find($pro_id);
                if ($lims_purchase_unit_data->operator == '*') {
                    $new_recieved_value = $recieved[$key] * $lims_purchase_unit_data->operation_value;
                } else {
                    $new_recieved_value = $recieved[$key] / $lims_purchase_unit_data->operation_value;
                }
                if ($new_recieved_value == $qty[$key]) {
                    $status_recieved = true;
                } else {
                    $status_recieved = false;
                }
                if ($product_purchase->recieved)
                    $new_recieved_value = $new_recieved_value - $product_purchase->recieved;

                if ($lims_product_data->is_variant) {
                    $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')->FindExactProductWithCode($pro_id, $product_code[$key])->first();
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $pro_id],
                        ['variant_id', $lims_product_variant_data->variant_id],
                        ['warehouse_id', $data['warehouse_id']],
                    ])->first();
                    $product_purchase->variant_id = $lims_product_variant_data->variant_id;
                    //add quantity to product variant table
                    $lims_product_variant_data->qty += $new_recieved_value;
                    // $lims_product_variant_data->save();
                } else {
                    $product_purchase->variant_id = null;
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $pro_id],
                        ['warehouse_id', $data['warehouse_id']],
                    ])->first();
                }


                //$lims_product_data->qty += $new_recieved_value;
                $lims_product_data->cost = $net_unit_cost[$key];
                if ($lims_product_warehouse_data) {
                    //$lims_product_warehouse_data->qty += $new_recieved_value;
                    $lims_product_warehouse_data->save();

                } else {
                    $lims_product_warehouse_data = new Product_Warehouse();
                    $lims_product_warehouse_data->product_id = $pro_id;
                    if ($lims_product_data->is_variant) {
                        $lims_product_warehouse_data->variant_id = $lims_product_variant_data->variant_id;
                    }

                    $lims_product_warehouse_data->warehouse_id = $data['warehouse_id'];
                    //$lims_product_warehouse_data->qty = $new_recieved_value;
                    $lims_product_warehouse_data->save();
                }
                $lims_product_data->save();


                $product_purchase->purchase_id = $id;
                $product_purchase->product_id = $pro_id;
                $product_purchase->qty = $qty[$key];
                $product_purchase->recieved = $recieved[$key];
                $product_purchase->purchase_unit_id = $lims_purchase_unit_data->id;
                $product_purchase->net_unit_cost = $net_unit_cost[$key];
                $product_purchase->discount = $discount[$key];
                $product_purchase->tax_rate = $tax_rate[$key];
                $product_purchase->tax = $tax[$key];
                $product_purchase->total = $total[$key];
                $product_purchase->save();

                /* Register/Update Lotes*/
                if ($lotes != null && ($lotes[$key] != null || $lotes[$key] != "")) {
                    $lote_list = json_decode(urldecode($lotes[$key]), true);
                    foreach ($lote_list as $lote) {
                        if (isset($lote['id'])) {
                            $product_lote = ProductLote::find($lote['id']);
                            $product_lote->name = $lote['name'];
                            $product_lote->expiration = $lote['expiration'];
                            $product_lote->purchase_id = $lims_purchase_data->id;
                            $product_lote->idwarehouse = $data['warehouse_id'];
                            $product_lote->idproduct = $pro_id;
                            $product_lote->supplier = $data['supplier_id'];
                            $product_lote->qty = $lote['qty'];
                            $product_lote->stock = $lote['stock'];
                            $product_lote->status = "2";
                            $product_lote->save();
                        } else {
                            $product_lote = new ProductLote();
                            $product_lote->name = $lote[0];
                            $product_lote->expiration = $lote[1];
                            $product_lote->purchase_id = $lims_purchase_data->id;
                            $product_lote->idwarehouse = $data['warehouse_id'];
                            $product_lote->idproduct = $pro_id;
                            $product_lote->supplier = $data['supplier_id'];
                            $product_lote->qty = $lote[2];
                            $product_lote->stock = $lote[2];
                            $product_lote->status = "1";
                            $product_lote->fabrication_date = date("Ymd");
                            $product_lote->save();
                        }
                    }
                }
            }

            Log::info("Details Purchase updated successfully");


            $create_at = date('Y-m-d', strtotime($lims_purchase_data->created_at));
            if ($data['date_purchase'] != $create_at) {
                $data['created_at'] = $data['date_purchase'] . " " . date("h:i:s");
            } else {
                $data['created_at'] = $lims_purchase_data->created_at;
            }
            $lims_purchase_data->update($data);
            DB::commit();

            Log::info("Purchase updated successfully");
            return redirect('purchases')->with('message', 'Compra Actualizado con éxito');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("error update purchase: " . $th->getMessage());
            return redirect('purchases')->with('not_permitted', 'Fallo al actualizar Compra, Intente de nuevo!');
        }
    }

    function revertChanges(int $id, $items)
    {
        foreach ($items as $item) {
            $old_recieved_value = $item->recieved;
            $lims_purchase_unit_data = Unit::find($item->purchase_unit_id);

            if ($lims_purchase_unit_data->operator == '*') {
                $old_recieved_value = $old_recieved_value * $lims_purchase_unit_data->operation_value;
            } else {
                $old_recieved_value = $old_recieved_value / $lims_purchase_unit_data->operation_value;
            }
            $lims_product_data = Product::find($item->product_id);
            $product_lotes = ProductLote::where([['idproduct', $item->product_id], ['purchase_id', $id]])->get();
            foreach ($product_lotes as $lote) {
                if ($lote->stock < $lote->qty && $item->status != 1) {
                    $lote_data = ProductLote::find($lote['id']);
                    $lote_data->status = "0";
                    $lote_data->save();
                }
            }
            if ($lims_product_data->is_variant) {
                $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')->FindExactProduct($lims_product_data->id, $item->variant_id)->first();
                $lims_product_warehouse_data = Product_Warehouse::where([
                    ['product_id', $lims_product_data->id],
                    ['variant_id', $item->variant_id],
                    ['warehouse_id', $item->warehouse_id],
                ])->first();
                $lims_product_variant_data->qty += $old_recieved_value;
                $lims_product_variant_data->save();
            } else {
                $lims_product_warehouse_data = Product_Warehouse::where([
                    ['product_id', $item->product_id],
                    ['warehouse_id', $item->warehouse_id],
                ])->first();
            }
            if ($lims_product_warehouse_data) {
                //$lims_product_warehouse_data->qty += $old_recieved_value;
                $lims_product_warehouse_data->save();
            } else {
                $lims_product_warehouse_data = new Product_Warehouse();
                $lims_product_warehouse_data->product_id = $lims_product_data->id;
                if ($lims_product_data->is_variant) {
                    $lims_product_warehouse_data->variant_id = $lims_product_variant_data->variant_id;
                }

                $lims_product_warehouse_data->warehouse_id = $item->warehouse_id;
                //$lims_product_warehouse_data->qty = $old_recieved_value;
                $lims_product_warehouse_data->save();
            }

            $lims_product_data->qty += $old_recieved_value;
            $lims_product_data->save();
            $product_purchase = new ProductLote();
            $product_purchase = $item;
            $product_purchase->save();
        }
    }

    public function addPayment(Request $request)
    {
        $data = $request->all();
        $lims_purchase_data = Purchase::find($data['purchase_id']);
        $lims_purchase_data->paid_amount += $data['amount'];
        $balance = $lims_purchase_data->grand_total - $lims_purchase_data->paid_amount;
        if ($balance > 0 || $balance < 0) {
            $lims_purchase_data->payment_status = 1;
        } elseif ($balance == 0) {
            $lims_purchase_data->payment_status = 2;
        }

        $lims_purchase_data->save();

        if ($data['paid_by_id'] == 1) {
            $paying_method = 'Efectivo';
        } elseif ($data['paid_by_id'] == 2) {
            $paying_method = 'Tarjeta_Regalo';
        } elseif ($data['paid_by_id'] == 3) {
            $paying_method = 'Tarjeta_Credito_Debito';
        } else {
            $paying_method = 'Cheque';
        }

        $lims_payment_data = new Payment();
        $lims_payment_data->user_id = Auth::id();
        $lims_payment_data->purchase_id = $lims_purchase_data->id;
        $lims_payment_data->account_id = $data['account_id'];
        $lims_payment_data->payment_reference = 'ppr-' . date("Ymd") . '-' . date("his");
        $lims_payment_data->amount = $data['amount'];
        $lims_payment_data->change = $data['paying_amount'] - $data['amount'];
        $lims_payment_data->paying_method = $paying_method;
        $lims_payment_data->payment_note = $data['payment_note'];
        $lims_payment_data->save();

        $lims_payment_data = Payment::latest()->first();
        $data['payment_id'] = $lims_payment_data->id;

        if ($paying_method == 'Tarjeta_Credito_Debito') {
            $lims_pos_setting_data = PosSetting::latest()->first();
            //Stripe::setApiKey($lims_pos_setting_data->stripe_secret_key);
            $token = $data['stripeToken'];
            $amount = $data['amount'];

            // Charge the Customer
            /*$charge = \Stripe\Charge::create([
            'amount' => $amount * 100,
            'currency' => 'usd',
            'source' => $token,
            ]);
            $data['charge_id'] = $charge->id;*/
            $data['charge_id'] = uniqid();
            PaymentWithCreditCard::create($data);
        } elseif ($paying_method == 'Cheque') {
            PaymentWithCheque::create($data);
        }
        return redirect('purchases')->with('message', 'Pago creado con éxito');
    }

    public function getPayment($id)
    {
        $lims_payment_list = Payment::where('purchase_id', $id)->get();
        $date = [];
        $payment_reference = [];
        $paid_amount = [];
        $paying_method = [];
        $payment_id = [];
        $payment_note = [];
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
            if ($payment->paying_method == 'Cheque') {
                $lims_payment_cheque_data = PaymentWithCheque::where('payment_id', $payment->id)->first();
                $cheque_no[] = $lims_payment_cheque_data->cheque_no;
            } else {
                $cheque_no[] = null;
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
        $lims_purchase_data = Purchase::find($lims_payment_data->purchase_id);
        //updating purchase table
        $amount_dif = $lims_payment_data->amount - $data['edit_amount'];
        $lims_purchase_data->paid_amount = $lims_purchase_data->paid_amount - $amount_dif;
        $balance = $lims_purchase_data->grand_total - $lims_purchase_data->paid_amount;
        if ($balance > 0 || $balance < 0) {
            $lims_purchase_data->payment_status = 1;
        } elseif ($balance == 0) {
            $lims_purchase_data->payment_status = 2;
        }

        $lims_purchase_data->save();

        //updating payment data
        $lims_payment_data->account_id = $data['account_id'];
        $lims_payment_data->amount = $data['edit_amount'];
        $lims_payment_data->change = $data['edit_paying_amount'] - $data['edit_amount'];
        $lims_payment_data->payment_note = $data['edit_payment_note'];
        if ($data['edit_paid_by_id'] == 1) {
            $lims_payment_data->paying_method = 'Efectivo';
        } elseif ($data['edit_paid_by_id'] == 2) {
            $lims_payment_data->paying_method = 'Tarjeta_Regalo';
        } elseif ($data['edit_paid_by_id'] == 3) {
            $lims_pos_setting_data = PosSetting::latest()->first();
            //\Stripe\Stripe::setApiKey($lims_pos_setting_data->stripe_secret_key);
            $token = $data['stripeToken'];
            $amount = $data['edit_amount'];
            if ($lims_payment_data->paying_method == 'Tarjeta_Credito_Debito') {
                $lims_payment_with_credit_card_data = PaymentWithCreditCard::where('payment_id', $lims_payment_data->id)->first();

                /*\Stripe\Refund::create(array(
                "charge" => $lims_payment_with_credit_card_data->charge_id,
                ));
                $charge = \Stripe\Charge::create([
                'amount' => $amount * 100,
                'currency' => 'usd',
                'source' => $token,
                ]);*/

                //$lims_payment_with_credit_card_data->charge_id = $charge->id;
                $lims_payment_with_credit_card_data->charge_id = uniqid();
                $lims_payment_with_credit_card_data->save();
            } else {
                // Charge the Customer
                /*$charge = \Stripe\Charge::create([
                'amount' => $amount * 100,
                'currency' => 'usd',
                'source' => $token,
                ]);
                $data['charge_id'] = $charge->id;*/
                $data['charge_id'] = uniqid();
                PaymentWithCreditCard::create($data);
            }
            $lims_payment_data->paying_method = 'Tarjeta_Credito_Debito';
        } else {
            if ($lims_payment_data->paying_method == 'Cheque') {
                $lims_payment_data->paying_method = 'Cheque';
                $lims_payment_cheque_data = PaymentWithCheque::where('payment_id', $data['payment_id'])->first();
                $lims_payment_cheque_data->cheque_no = $data['edit_cheque_no'];
                $lims_payment_cheque_data->save();
            } else {
                $lims_payment_data->paying_method = 'Cheque';
                $data['cheque_no'] = $data['edit_cheque_no'];
                PaymentWithCheque::create($data);
            }
        }
        $lims_payment_data->save();
        return redirect('purchases')->with('message', 'Pago actualizado con éxito');
    }

    public function deletePayment(Request $request)
    {
        $lims_payment_data = Payment::find($request['id']);
        $lims_purchase_data = Purchase::where('id', $lims_payment_data->purchase_id)->first();
        $lims_purchase_data->paid_amount -= $lims_payment_data->amount;
        $balance = $lims_purchase_data->grand_total - $lims_purchase_data->paid_amount;
        if ($balance > 0 || $balance < 0) {
            $lims_purchase_data->payment_status = 1;
        } elseif ($balance == 0) {
            $lims_purchase_data->payment_status = 2;
        }

        $lims_purchase_data->save();

        if ($lims_payment_data->paying_method == 'Credit Card') {
            $lims_payment_with_credit_card_data = PaymentWithCreditCard::where('payment_id', $request['id'])->first();
            $lims_pos_setting_data = PosSetting::latest()->first();
            \Stripe\Stripe::setApiKey($lims_pos_setting_data->stripe_secret_key);
            \Stripe\Refund::create(
                array(
                    "charge" => $lims_payment_with_credit_card_data->charge_id,
                )
            );

            $lims_payment_with_credit_card_data->delete();
        } elseif ($lims_payment_data->paying_method == 'Cheque') {
            $lims_payment_cheque_data = PaymentWithCheque::where('payment_id', $request['id'])->first();
            $lims_payment_cheque_data->delete();
        }
        $lims_payment_data->delete();
        return redirect('purchases')->with('not_permitted', 'Pago eliminado con éxito!');
    }

    public function deleteBySelection(Request $request)
    {
        $purchase_id = $request['purchaseIdArray'];
        foreach ($purchase_id as $id) {
            $lims_purchase_data = Purchase::find($id);
            $lims_product_purchase_data = ProductPurchase::where('purchase_id', $id)->get();
            $lims_payment_data = Payment::where('purchase_id', $id)->get();
            foreach ($lims_product_purchase_data as $product_purchase_data) {
                $lims_purchase_unit_data = Unit::find($product_purchase_data->purchase_unit_id);
                if ($lims_purchase_unit_data->operator == '*') {
                    $recieved_qty = $product_purchase_data->recieved * $lims_purchase_unit_data->operation_value;
                } else {
                    $recieved_qty = $product_purchase_data->recieved / $lims_purchase_unit_data->operation_value;
                }

                $lims_product_data = Product::find($product_purchase_data->product_id);
                if ($product_purchase_data->variant_id) {
                    $lims_product_variant_data = ProductVariant::select('id', 'qty')->FindExactProduct($lims_product_data->id, $product_purchase_data->variant_id)->first();
                    $lims_product_warehouse_data = Product_Warehouse::FindProductWithVariant($product_purchase_data->product_id, $product_purchase_data->variant_id, $lims_purchase_data->warehouse_id)
                        ->first();
                    $lims_product_variant_data->qty -= $recieved_qty;
                    $lims_product_variant_data->save();
                } else {
                    $lims_product_warehouse_data = Product_Warehouse::FindProductWithoutVariant($product_purchase_data->product_id, $lims_purchase_data->warehouse_id)
                        ->first();
                }

                $lims_product_data->qty -= $recieved_qty;
                $lims_product_warehouse_data->qty -= $recieved_qty;
                //** Delete Lote of product */
                $lote_product = ProductLote::where([['purchase_id', $lims_purchase_data->id], ['idproduct', $lims_product_data->id], ['status', '!=', 0]])->first();
                if ($lote_product) {
                    $lote_product->delete();
                }

                $lims_product_warehouse_data->save();
                $lims_product_data->save();
                $product_purchase_data->delete();
            }
            foreach ($lims_payment_data as $payment_data) {
                if ($payment_data->paying_method == "Cheque") {
                    $payment_with_cheque_data = PaymentWithCheque::where('payment_id', $payment_data->id)->first();
                    $payment_with_cheque_data->delete();
                } elseif ($payment_data->paying_method == "Credit Card") {
                    $payment_with_credit_card_data = PaymentWithCreditCard::where('payment_id', $payment_data->id)->first();
                    $lims_pos_setting_data = PosSetting::latest()->first();
                    \Stripe\Stripe::setApiKey($lims_pos_setting_data->stripe_secret_key);
                    \Stripe\Refund::create(
                        array(
                            "charge" => $payment_with_credit_card_data->charge_id,
                        )
                    );

                    $payment_with_credit_card_data->delete();
                }
                $payment_data->delete();
            }

            $lims_purchase_data->delete();
        }
        return 'Compras eliminados con éxito!';
    }

    public function destroy($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('purchases-delete')) {
            $lims_purchase_data = Purchase::find($id);
            $lims_product_purchase_data = ProductPurchase::where('purchase_id', $id)->get();
            $lims_payment_data = Payment::where('purchase_id', $id)->get();
            foreach ($lims_product_purchase_data as $product_purchase_data) {
                $lims_purchase_unit_data = Unit::find($product_purchase_data->purchase_unit_id);
                if ($lims_purchase_unit_data->operator == '*') {
                    $recieved_qty = $product_purchase_data->recieved * $lims_purchase_unit_data->operation_value;
                } else {
                    $recieved_qty = $product_purchase_data->recieved / $lims_purchase_unit_data->operation_value;
                }

                $lims_product_data = Product::find($product_purchase_data->product_id);
                if ($product_purchase_data->variant_id) {
                    $lims_product_variant_data = ProductVariant::select('id', 'qty')->FindExactProduct($lims_product_data->id, $product_purchase_data->variant_id)->first();
                    $lims_product_warehouse_data = Product_Warehouse::FindProductWithVariant($product_purchase_data->product_id, $product_purchase_data->variant_id, $lims_purchase_data->warehouse_id)
                        ->first();
                    $lims_product_variant_data->qty -= $recieved_qty;
                    $lims_product_variant_data->save();
                } else {
                    $lims_product_warehouse_data = Product_Warehouse::FindProductWithoutVariant($product_purchase_data->product_id, $lims_purchase_data->warehouse_id)
                        ->first();
                }
                $lims_product_data->qty -= $recieved_qty;
                $lims_product_warehouse_data->qty -= $recieved_qty;
                //** Delete Lote of product */
                $lote_product = ProductLote::where([['purchase_id', $lims_purchase_data->id], ['idproduct', $lims_product_data->id], ['status', '!=', 0]])->first();
                if ($lote_product) {
                    $lote_product->delete();
                }

                $lims_product_warehouse_data->save();
                $lims_product_data->save();
                $product_purchase_data->delete();
            }
            foreach ($lims_payment_data as $payment_data) {
                if ($payment_data->paying_method == "Cheque") {
                    $payment_with_cheque_data = PaymentWithCheque::where('payment_id', $payment_data->id)->first();
                    $payment_with_cheque_data->delete();
                } elseif ($payment_data->paying_method == "Credit Card") {
                    $payment_with_credit_card_data = PaymentWithCreditCard::where('payment_id', $payment_data->id)->first();
                    $lims_pos_setting_data = PosSetting::latest()->first();
                    \Stripe\Stripe::setApiKey($lims_pos_setting_data->stripe_secret_key);
                    \Stripe\Refund::create(
                        array(
                            "charge" => $payment_with_credit_card_data->charge_id,
                        )
                    );

                    $payment_with_credit_card_data->delete();
                }
                $payment_data->delete();
            }

            $lims_purchase_data->delete();
            return redirect('purchases')->with('not_permitted', 'Compra eliminado con éxito');
        }
    }

    public function destroyItem($id, $id_pro)
    {
        $item = ProductPurchase::where([['purchase_id', $id], ['product_id', $id_pro]])->first();
        if ($item != null) {
            $item->delete();
            return array('status' => true);
        } else {
            return array('status' => false);
        }
    }

    public function changeStatuItem($id, $id_pro)
    {
        $item = ProductPurchase::where([['purchase_id', $id], ['product_id', $id_pro]])->first();
        if ($item != null) {
            $item->status = 0;
            $item->save();
            return json_encode(true);
        } else {
            return json_encode(false);
        }
    }
}
