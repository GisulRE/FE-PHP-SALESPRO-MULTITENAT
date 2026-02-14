<?php

namespace App\Http\Controllers;

use App\Account;
use App\AttentionShift;
use App\Biller;
use App\Brand;
use App\Category;
use App\Coupon;
use App\Customer;
use App\CustomerGroup;
use App\Employee;
use App\PosSetting;
use App\PreSale;
use App\Product;
use App\ProductVariant;
use App\Product_Presale;
use App\ShiftEmployee;
use App\Tax;
use App\Unit;
use App\Warehouse;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Log;
use Spatie\Permission\Models\Role;

class PreSaleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function preSale()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('presale-create')) {
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
            $lims_customer_list = Customer::select('id', 'name', 'phone_number', 'is_credit', 'credit')->where('is_active', true)->get();
            $lims_customer_group_all = CustomerGroup::select('id', 'name')->where('is_active', true)->get();
            $lims_warehouse_list = Warehouse::select('id', 'name')->where('is_active', true)->get();
            $lims_biller_list = Biller::select('id', 'name', 'company_name')->where('is_active', true)->get();
            $lims_tax_list = Tax::where('is_active', true)->get();
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
            $lims_coupon_list = Coupon::where('is_active', true)->get();
            $lims_employee_list = Employee::select('id', 'name', 'image', 'warehouse_id')->where([['is_active', true], ['pre_sale', true]])->orderBy('name', 'asc')->get();
            $flag = 0;
            return view('pre_sale.create', compact('all_permission', 'lims_customer_list', 'lims_customer_group_all', 'lims_warehouse_list', 'lims_product_list', 'product_number', 'lims_tax_list', 'lims_biller_list', 'lims_pos_setting_data', 'lims_brand_list', 'lims_category_list', 'lims_employee_list', 'lims_coupon_list', 'flag', 'biller_data', 'account_data'));
        } else {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }

    }

    public function listPresale($filter)
    {
        $datenow = date('Y-m-d');
        $data = array();
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('presale-index')) {
            $lims_presale_list = PreSale::where("status", 1)->whereDate('created_at', $datenow)->get();
            $totalData = PreSale::where("status", 1)->whereDate('created_at', $datenow)->count();
            $totalFiltered = $totalData;
            if (!empty($lims_presale_list)) {
                foreach ($lims_presale_list as $key => $presale) {
                    $nestedData['id'] = $presale->id;
                    $nestedData['key'] = $key + 1;
                    if ($role->hasPermissionTo('attentionshift')) {
                        if ($presale->attentionshift_id) {
                            $nestedData['attentionshift'] = $presale->attentionshift->reference_nro;
                            $nestedData['customer'] = $presale->attentionshift->customer_name;
                            if ($presale->attentionshift->employee_id) {
                                $nestedData['employee'] = $presale->attentionshift->employee->name;
                            } else {
                                $nestedData['employee'] = "Sin Asignar";
                            }
                        } else {
                            $nestedData['attentionshift'] = "Sin Turno";
                            $nestedData['customer'] = $presale->customer->name;
                            $nestedData['employee'] = "Sin Asignar";
                        }

                    } else {
                        $nestedData['customer'] = $presale->customer->name;
                    }
                    $nestedData['date'] = date(config('date_format'), strtotime($presale->created_at));
                    $nestedData['reference_no'] = $presale->reference_no;

                    $nestedData['grand_total'] = number_format($presale->grand_total, 2);

                    $nestedData['options'] = '<div class="btn-group">';
                    if ($role->hasPermissionTo('presale-edit')) {
                        $nestedData['options'] .= '<button id="btnpresale_' . $presale->id . '" class="btn btn-link" onclick="this.disabled=true;loadPresale(' . $presale->id . ')"><i class="dripicons-document-edit" data-toggle="tooltip" data-placement="bottom" title="Editar o Crear Venta"></i></button>';
                    }
                    if ($role->hasPermissionTo('presale-delete')) {
                        $nestedData['options'] .= \Form::open(["route" => ["presales.destroy", $presale->id], "method" => "DELETE"]) . '
                              <button type="submit" class="btn btn-link" onclick="return confirmDelete()" data-toggle="tooltip" data-placement="bottom" title="Borrar"><i class="dripicons-trash"></i> </button>' . \Form::close() . '</div>';
                    }
                    $data[] = $nestedData;
                }
            }
            $json_data = array(
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $data,
            );
            return $json_data;
        } else {
            $json_data = array(
                "message" => "Sorry! You are not allowed to access this module",
                "status" => 404,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => $data,
            );
            return $json_data;
        }
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
        Log::info('Registrando pre-venta...');
        $data = $request->all();
        $data['user_id'] = Auth::id();
        $datenow = date('Y-m-d');
        $last_ref = PreSale::get()->last();
        if (isset($data['sale_status'])) {
            $turno_data = AttentionShift::find($data['attentionshift_id']);
            Log::info('Turno id: ' . $turno_data->id);
            if ($turno_data) {
                $presale_data = PreSale::where('attentionshift_id', $turno_data->id)->first();
                if ($presale_data) {
                    $request->request->add(['presale_id' => $presale_data->id]);
                    $result = $this->update($request, $presale_data->id);
                    $message = 'Pre-Venta actualizado con éxito';
                    return $result;
                }
            }
        } else {
            $turno_data = AttentionShift::where([['employee_id', $data['employee_id']], ['status', 1]])->whereDate('created_at', $datenow)->first();
            if ($turno_data) {
                $presale_data = PreSale::where('attentionshift_id', $turno_data->id)->first();
                if ($presale_data) {
                    $request->request->add(['presale_id' => $presale_data->id]);
                    $result = $this->update($request, $presale_data->id);
                    $message = 'Pre-Venta actualizado con éxito';
                    return $result;
                }
            }
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
            $data['reference_no'] = 'PRV-' . $nro;
        } else {
            $data['reference_no'] = 'PRV-' . $nro;
        }

        if ($turno_data) {
            $data['attentionshift_id'] = $turno_data->id;
        }
        $employeeid = $data['employee_id'];
        if (is_array($employeeid)) {
            $data['employee_id'] = null;
        }
        $lims_sale_data = PreSale::create($data);

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
        if (is_array($data['employee'])) {
            $employee = $data['employee'];
        } else {
            $employee[] = $data['employee'];
        }
        $product_sale = [];

        foreach ($product_id as $i => $id) {
            $lims_product_data = Product::where('id', $id)->first();
            $product_sale['variant_id'] = null;
            if ($sale_unit[$i] != 'n/a') {
                $lims_sale_unit_data = Unit::where('unit_name', $sale_unit[$i])->first();
                $sale_unit_id = $lims_sale_unit_data->id;
                if ($lims_product_data->is_variant) {
                    $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')->FindExactProductWithCode($id, $product_code[$i])->first();
                    $product_sale['variant_id'] = $lims_product_variant_data->variant_id;
                }
            } else {
                $sale_unit_id = 0;
            }

            $product_sale['presale_id'] = $lims_sale_data->id;
            $product_sale['product_id'] = $id;
            $product_sale['category_id'] = $lims_product_data->category_id;
            $product_sale['qty'] = $mail_data['qty'][$i] = $qty[$i];
            $product_sale['sale_unit_id'] = $sale_unit_id;
            $product_sale['net_unit_price'] = $net_unit_price[$i];
            $product_sale['discount'] = $discount[$i];
            $product_sale['tax_rate'] = $tax_rate[$i];
            $product_sale['tax'] = $tax[$i];
            if ($employee[$i] == 0) {
                $product_sale['employee_id'] = null;
            } else {
                $product_sale['employee_id'] = $employee[$i];
            }

            $product_sale['total'] = $total[$i];
            Product_Presale::create($product_sale);
        }
        $lims_pos_setting_data = PosSetting::latest()->first();
        /** Find and Update AttentionShift and ShiftEmployee */
        $error = "";
        if ($turno_data) {
            $datenow = date('Y-m-d');
            if ($turno_data->employee_id != null) {
                $last = ShiftEmployee::whereDate('created_at', $datenow)->max('position');
                if ($last) {
                    $position = $last + 1;
                } else {
                    $position = 1;
                }
                $employee_position = ShiftEmployee::where([['status', 0], ['employee_id', $turno_data->employee_id]])->whereDate('created_at', $datenow)->first();
                if ($employee_position) {
                    $employee_position->status = 1;
                    $employee_position->position = $position;
                    $employee_position->save();
                } else {
                    $error = ", No se encontró Empleado para liberar";
                }
            }
            $turno_data->status = 3;
            $turno_data->save();
        }
        Log::info('pre-venta registrado id: ' . $lims_sale_data->id);
        /** end  */
        if ($lims_sale_data->status == 1) {
            $message = 'Pre-Venta creada con éxito' . $error;
        }
        if ($lims_sale_data->status == '1' && isset($data['sale_status'])) {
            $data['prepos'] = $lims_pos_setting_data->print_presale;
            if ($data['prepos']) {
                //return redirect('presales/gen_invoice/' . $lims_sale_data->id)->with('message', $message);
                return array('message' => $message, 'status' => true, 'message_code' => 'success', 'print' => true, 'id' => $presale_data->id);
            } else {
                //return redirect('prepos')->with('message', $message);
                return array('message' => $message, 'status' => true, 'message_code' => 'success', 'print' => false);
            }

        } elseif ($lims_sale_data->status == '1') {
            $data['prepos'] = $lims_pos_setting_data->print_presale;
            if ($data['prepos']) {
                return redirect('presales/gen_invoice/' . $lims_sale_data->id)->with('message', $message);
            } else {
                return redirect('prepos')->with('message', $message);
            }
        }

    }

    public function genInvoice($id)
    {
        $lims_sale_data = PreSale::find($id);
        $lims_product_sale_data = Product_Presale::where('presale_id', $id)->get();
        $lims_warehouse_data = Warehouse::find($lims_sale_data->warehouse_id);
        $lims_customer_data = Customer::find($lims_sale_data->customer_id);
        return view('pre_sale.invoice', compact('lims_sale_data', 'lims_product_sale_data', 'lims_warehouse_data', 'lims_customer_data'));
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
        $lims_sale_data = PreSale::find($id);
        $lims_product_sale_data = Product_Presale::select('product_pre_sale.*', 'products.code')
            ->join('products', 'product_pre_sale.product_id', '=', 'products.id')->where('product_pre_sale.presale_id', $id)->get();
        return array('head' => $lims_sale_data, 'body' => $lims_product_sale_data);
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
        $data['user_id'] = Auth::id();
        $datenow = date('Y-m-d');
        $presale_data = PreSale::find($data['presale_id']);
        $presale_data->grand_total = $data['grand_total'];
        $presale_data->item = $data['item'];
        $presale_data->total_qty = $data['total_qty'];
        $presale_data->customer_id = $data['customer_id'];
        $presale_data->warehouse_id = $data['warehouse_id'];
        $presale_data->user_id = $data['user_id'];
        if (isset($data['employee_id']) && $data['employee_id'] != "0") {
            $employeeid = $data['employee_id'];
            if (is_array($employeeid)) {
                $data['employee_id'] = null;
            }
            $presale_data->employee_id = $data['employee_id'];
        }

        $presale_data->save();

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
        if (is_array($data['employee'])) {
            $employee = $data['employee'];
        } else {
            $employee[] = $data['employee'];
        }
        $product_presale = [];
        $total_grand = 0;
        foreach ($product_id as $i => $id) {
            /** if found data save than update $product_presale_data*/
            $product_presale_data = Product_Presale::where([['presale_id', $presale_data->id], ['product_id', $id]])->first();
            $lims_product_data = Product::where('id', $id)->first();
            $product_presale['variant_id'] = null;
            if ($sale_unit[$i] != 'n/a') {
                $lims_sale_unit_data = Unit::where('unit_name', $sale_unit[$i])->first();
                $sale_unit_id = $lims_sale_unit_data->id;
                if ($lims_product_data->is_variant) {
                    $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')->FindExactProductWithCode($id, $product_code[$i])->first();
                    if ($product_presale_data) {
                        $product_presale_data->variant_id = $lims_product_variant_data->variant_id;
                    } else {
                        $product_presale['variant_id'] = $lims_product_variant_data->variant_id;
                    }

                }
            } else {
                $sale_unit_id = 0;
            }
            if ($product_presale_data) {
                $product_presale_data->category_id = $lims_product_data->category_id;
                $product_presale_data->qty = $qty[$i];
                $product_presale_data->sale_unit_id = $sale_unit_id;
                $product_presale_data->net_unit_price = $net_unit_price[$i];
                $product_presale_data->discount = $discount[$i];
                $product_presale_data->tax_rate = $tax_rate[$i];
                $product_presale_data->tax = $tax[$i];
                if ($employee[$i] == 0) {
                    $product_presale_data->employee_id = null;
                } else {
                    $product_presale_data->employee_id = $employee[$i];
                }
                $product_presale_data->total = $total[$i];
                $product_presale_data->save();
            } else {
                $product_presale['presale_id'] = $presale_data->id;
                $product_presale['product_id'] = $id;
                $product_presale['category_id'] = $lims_product_data->category_id;
                $product_presale['qty'] = $mail_data['qty'][$i] = $qty[$i];
                $product_presale['sale_unit_id'] = $sale_unit_id;
                $product_presale['net_unit_price'] = $net_unit_price[$i];
                $product_presale['discount'] = $discount[$i];
                $product_presale['tax_rate'] = $tax_rate[$i];
                $product_presale['tax'] = $tax[$i];
                if ($employee[$i] == 0) {
                    $product_presale['employee_id'] = null;
                } else {
                    $product_presale['employee_id'] = $employee[$i];
                }
                $product_presale['total'] = $total[$i];
                Product_Presale::create($product_presale);
            }
        }
        $total_grand = Product_Presale::where([['presale_id', $presale_data->id]])->sum('total');
        $presale_data->grand_total = $total_grand;
        $presale_data->save();
        $lims_pos_setting_data = PosSetting::latest()->first();
        if ($presale_data->status == 1) {
            $message = 'Pre-Venta actualizado con éxito';
        }
        if ($presale_data->status == '1' && isset($data['sale_status'])) {
            $data['prepos'] = $lims_pos_setting_data->print_presale;
            if ($data['prepos']) {
                //return redirect('presales/gen_invoice/' . $lims_sale_data->id)->with('message', $message);
                return array('message' => $message, 'status' => true, 'message_code' => 'success', 'print' => true, 'id' => $presale_data->id);
            } else {
                //return redirect('prepos')->with('message', $message);
                return array('message' => $message, 'status' => true, 'message_code' => 'success', 'print' => false);
            }

        } elseif ($presale_data->status == '1') {
            $data['prepos'] = $lims_pos_setting_data->print_presale;
            if ($data['prepos']) {
                return redirect('presales/gen_invoice/' . $presale_data->id)->with('message', $message);
            } else {
                return redirect('prepos')->with('message', $message);
            }
        }
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
        $lims_sale_data = PreSale::find($id);
        $lims_product_sale_data = Product_Presale::where('presale_id', $id)->get();
        foreach ($lims_product_sale_data as $value) {
            $value->delete();
        }
        $lims_sale_data->delete();
        return Redirect::to($url)->with('not_permitted', "Pre-Venta eliminado con éxito");
    }
}
