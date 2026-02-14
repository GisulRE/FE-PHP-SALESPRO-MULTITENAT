<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Warehouse;
use App\Product_Warehouse;
use App\Product;
use App\ProductVariant;
use App\Adjustment;
use App\ProductAdjustment;
use DB;
use App\StockCount;
use Auth;
use Log;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdjustmentController extends Controller
{
    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('adjustment')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if (empty($all_permission))
                $all_permission[] = 'dummy text';

            if (Auth::user()->role_id > 2)
                $lims_adjustment_all = Adjustment::orderBy('id', 'desc')->where('user_id', Auth::id())->get();
            else
                $lims_adjustment_all = Adjustment::orderBy('id', 'desc')->get();
            return view('adjustment.index', compact('lims_adjustment_all', 'all_permission'));
        } else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function getProduct($id)
    {
        $products = Product::select('code', 'id', 'name')->where('is_active', 1)->where('type', 'standard')->orwhere('type', 'insumo')->get();
        $product_code = [];
        $product_name = [];
        $product_qty = [];
        $product_data = [];
        foreach ($products as $product) {
            $product_name[] = $product->name;
            $product_warehouse = Product_Warehouse::where(['product_id' => $product->id], ['warehouse_id', $id])->first();
            if ($product_warehouse) {
                if ($product->variant_id) {
                    $lims_product_variant_data = ProductVariant::select('item_code')->FindExactProduct($product_warehouse->id, $product_warehouse->variant_id)->first();
                    $product_code[] = $lims_product_variant_data->item_code;
                } else {
                    $product_code[] = $product->code;
                }
                $product_qty[] = $product_warehouse->qty;
            } else {
                if ($product->variant_id) {
                    $lims_product_variant_data = ProductVariant::select('item_code')->FindExactProduct($product_warehouse->id, $product_warehouse->variant_id)->first();
                    $product_code[] = $lims_product_variant_data->item_code;
                } else {
                    $product_code[] = $product->code;
                }
                $product_qty[] = 0;
            }
        }

        $product_data[] = $product_code;
        $product_data[] = $product_name;
        $product_data[] = $product_qty;
        return $product_data;
    }


    public function getInfoProduct($id_warehouse, $product_code)
    {
        $result = array();
        $product = Product::where([["code", $product_code], ["is_active", true]])->first();
        if ($product) {
            if ($product->is_variant) {
                if ($product->is_variant) {
                    $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')->FindExactProductWithCode($product->id, $product_code)->first();
                    $lims_product_warehouse_data = Product_Warehouse::FindProductWithVariant($product->id, $lims_product_variant_data->variant_id, $id_warehouse)->first();
                }
            } else {
                $lims_product_warehouse_data = Product_Warehouse::FindProductWithoutVariant($product->id, $id_warehouse)->first();
            }
            if ($lims_product_warehouse_data) {
                $result = array("status" => true, "id" => $lims_product_warehouse_data->product_id, "qty" => $lims_product_warehouse_data->qty);
            } else {
                $result = array("status" => true, "id" => $product->id, "qty" => 0);
                //$result = array("status" => false, "message" => "produ data not found");
            }
        } else {
            $result = array("status" => false, "message" => "product not found");
        }
        return $result;
    }


    public function limsProductSearch(Request $request)
    {
        $product_variant_id = null;
        $product_code = explode(" ", $request['data']);
        $lims_product_data = Product::where('code', $product_code[0])->first();
        if (!$lims_product_data) {
            $lims_product_data = Product::join('product_variants', 'products.id', 'product_variants.product_id')
                ->select('products.*', 'product_variants.id as product_variant_id', 'product_variants.item_code', 'product_variants.additional_price')
                ->where('product_variants.item_code', $product_code[0])->where('products.is_active', true)
                ->first();
            $product_variant_id = $lims_product_data->product_variant_id;
            $code = $lims_product_data->item_code;
        }

        $product[] = $lims_product_data->name;
        $product[] = $lims_product_data->code;
        $product[] = $lims_product_data->id;
        if ($product_variant_id != null) {
            $product[] = $product_variant_id;
            $product[] = $code;
        } else {
            $product[] = null;
            $product[] = null;
        }
        return $product;
    }

    public function create()
    {
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        return view('adjustment.create', compact('lims_warehouse_list'));
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->except('document');
            if (isset ($data['stock_count_id'])) {
                $lims_stock_count_data = StockCount::find($data['stock_count_id']);
                $lims_stock_count_data->is_adjusted = true;
                $lims_stock_count_data->save();
            }
            $data['reference_no'] = 'adr-' . date("Ymd") . '-' . date("his");
            $document = $request->document;
            if ($document) {
                $documentName = $document->getClientOriginalName();
                $document->move('public/documents/adjustment', $documentName);
                $data['document'] = $documentName;
            }
            $lims_adjustment_data = Adjustment::create($data);

            $product_id = $data['product_id'];
            $product_code = $data['product_code'];
            $qty = $data['qty'];
            $action = $data['action'];

            foreach ($product_id as $key => $pro_id) {
                $lims_product_data = Product::find($pro_id);
                if ($lims_product_data->is_variant) {
                    $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')->FindExactProductWithCode($pro_id, $product_code[$key])->first();
                } else {
                    $lims_product_variant_data = null;
                }
                if ($lims_product_data->is_variant) {
                    $lims_product_warehouse_data = Product_Warehouse::FindProductWithVariant($pro_id, $lims_product_variant_data->variant_id, $data['warehouse_id'])->first();
                } else {
                    $lims_product_warehouse_data = Product_Warehouse::FindProductWithoutVariant($pro_id, $data['warehouse_id'])->first();
                }

                if ($action[$key] == '-') {
                    if ($lims_product_variant_data) {
                        $lims_product_variant_data->qty -= $qty[$key];
                        $lims_product_variant_data->save();
                    }
                    $lims_product_data->qty -= $qty[$key];
                    $lims_product_warehouse_data->qty -= $qty[$key];
                } elseif ($action[$key] == '+') {
                    if ($lims_product_warehouse_data) {
                        if ($lims_product_variant_data) {
                            $lims_product_variant_data->qty += $qty[$key];
                            $lims_product_variant_data->save();
                        }
                        $lims_product_warehouse_data->qty += $qty[$key];
                    } else {
                        $lims_product_warehouse_data = new Product_Warehouse();
                        $lims_product_warehouse_data->warehouse_id = $data['warehouse_id'];
                        $lims_product_warehouse_data->product_id = $lims_product_data->id;
                        $lims_product_warehouse_data->qty += $qty[$key];
                    }
                    $lims_product_data->qty += $qty[$key];
                }
                $lims_product_data->save();
                $lims_product_warehouse_data->save();

                $product_adjustment['product_id'] = $pro_id;
                $product_adjustment['code'] = $product_code[$key];
                $product_adjustment['adjustment_id'] = $lims_adjustment_data->id;
                $product_adjustment['qty'] = $qty[$key];
                $product_adjustment['action'] = $action[$key];
                $lims_product_variant_data = null;
                ProductAdjustment::create($product_adjustment);
            }
            DB::commit();
            Log::info("ProductAdjustment created successfully");
            return redirect('qty_adjustment')->with('message', 'Datos registrado con éxito');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("error save adjustment product: " . $th->getMessage());
            return redirect('qty_adjustment')->with('not_permitted', 'Fallo al crear Ajuste Producto, Intente de nuevo!');
        }
    }

    public function edit($id)
    {
        $lims_adjustment_data = Adjustment::find($id);
        $lims_product_adjustment_data = ProductAdjustment::where('adjustment_id', $id)->get();
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        return view('adjustment.edit', compact('lims_adjustment_data', 'lims_warehouse_list', 'lims_product_adjustment_data'));
    }

    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $data = $request->except('document');
            $document = $request->document;
            if ($document) {
                $documentName = $document->getClientOriginalName();
                $document->move('public/documents/adjustment', $documentName);
                $data['document'] = $documentName;
            }

            $lims_adjustment_data = Adjustment::find($id);
            $lims_product_adjustment_data = ProductAdjustment::where('adjustment_id', $id)->get();
            $product_id = $data['product_id'];
            $qty = $data['qty'];
            $product_code = $data['product_code'];
            $action = $data['action'];

            foreach ($lims_product_adjustment_data as $key => $product_adjustment_data) {
                $old_product_id[] = $product_adjustment_data->product_id;
                $lims_product_data = Product::find($product_adjustment_data->product_id);
                if ($lims_product_data->is_variant) {
                    $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')->FindExactProductWithCode($product_adjustment_data->product_id, $product_adjustment_data->code)->first();
                } else {
                    $lims_product_variant_data = null;
                }
                if ($lims_product_data->is_variant) {
                    $lims_product_warehouse_data = Product_Warehouse::FindProductWithVariant($product_adjustment_data->product_id, $lims_product_variant_data->variant_id, $lims_adjustment_data->warehouse_id)->first();
                } else {
                    $lims_product_warehouse_data = Product_Warehouse::FindProductWithoutVariant($product_adjustment_data->product_id, $lims_adjustment_data->warehouse_id)->first();
                }

                if ($product_adjustment_data->action == '-') {
                    if ($lims_product_variant_data) {
                        $lims_product_variant_data->qty += $product_adjustment_data->qty;
                        $lims_product_variant_data->save();
                    }
                    $lims_product_data->qty += $product_adjustment_data->qty;
                    $lims_product_warehouse_data->qty += $product_adjustment_data->qty;
                } elseif ($product_adjustment_data->action == '+') {
                    if ($lims_product_variant_data) {
                        $lims_product_variant_data->qty -= $product_adjustment_data->qty;
                        $lims_product_variant_data->save();
                    }
                    $lims_product_data->qty -= $product_adjustment_data->qty;
                    $lims_product_warehouse_data->qty -= $product_adjustment_data->qty;
                }
                $lims_product_data->save();
                $lims_product_warehouse_data->save();
                $lims_product_variant_data = null;
                if (!(in_array($old_product_id[$key], $product_id)))
                    $product_adjustment_data->delete();
            }

            foreach ($product_id as $key => $pro_id) {
                $lims_product_data = Product::find($pro_id);
                if ($lims_product_data->is_variant) {
                    $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')->FindExactProductWithCode($pro_id, $product_code[$key])->first();
                }
                if ($lims_product_data->is_variant) {
                    $lims_product_warehouse_data = Product_Warehouse::FindProductWithVariant($pro_id, $lims_product_variant_data->variant_id, $data['warehouse_id'])->first();
                } else {
                    $lims_product_warehouse_data = Product_Warehouse::FindProductWithoutVariant($pro_id, $data['warehouse_id'])->first();
                }

                if ($action[$key] == '-') {
                    if ($lims_product_variant_data) {
                        $lims_product_variant_data->qty -= $qty[$key];
                        $lims_product_variant_data->save();
                    }
                    $lims_product_data->qty -= $qty[$key];
                    $lims_product_warehouse_data->qty -= $qty[$key];
                } elseif ($action[$key] == '+') {
                    if ($lims_product_warehouse_data) {
                        if ($lims_product_variant_data) {
                            $lims_product_variant_data->qty += $qty[$key];
                            $lims_product_variant_data->save();
                        }
                        $lims_product_warehouse_data->qty += $qty[$key];
                    } else {
                        $lims_product_warehouse_data = new Product_Warehouse();
                        $lims_product_warehouse_data->warehouse_id = $data['warehouse_id'];
                        $lims_product_warehouse_data->product_id = $lims_product_data->id;
                        $lims_product_warehouse_data->qty += $qty[$key];
                    }
                    $lims_product_data->qty += $qty[$key];
                }
                $lims_product_data->save();
                $lims_product_warehouse_data->save();

                $product_adjustment['product_id'] = $pro_id;
                $product_adjustment['code'] = $product_code[$key];
                $product_adjustment['adjustment_id'] = $lims_adjustment_data->id;
                $product_adjustment['qty'] = $qty[$key];
                $product_adjustment['action'] = $action[$key];
                $lims_product_variant_data = null;

                if (in_array($pro_id, $old_product_id)) {
                    ProductAdjustment::where([
                        ['adjustment_id', $id],
                        ['product_id', $pro_id]
                    ])->update($product_adjustment);
                } else
                    ProductAdjustment::create($product_adjustment);
            }
            $lims_adjustment_data->update($data);
            DB::commit();
            Log::info("ProductAdjustment updated successfully");
            return redirect('qty_adjustment')->with('message', 'Datos actualizados con éxito');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("error save adjustment product: " . $th->getMessage());
            return redirect('qty_adjustment')->with('not_permitted', 'Fallo al actualizar Ajuste Producto, Intente de nuevo!');
        }
    }

    public function deleteBySelection(Request $request)
    {
        $adjustment_id = $request['adjustmentIdArray'];
        foreach ($adjustment_id as $id) {
            $lims_adjustment_data = Adjustment::find($id);
            $lims_product_adjustment_data = ProductAdjustment::where('adjustment_id', $id)->get();
            foreach ($lims_product_adjustment_data as $key => $product_adjustment_data) {
                $lims_product_data = Product::find($product_adjustment_data->product_id);
                if ($lims_product_data->is_variant) {
                    $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')->FindExactProductWithCode($product_adjustment_data->product_id, $product_adjustment_data->code)->first();
                } else {
                    $lims_product_variant_data = null;
                }
                if ($lims_product_data->is_variant) {
                    $lims_product_warehouse_data = Product_Warehouse::FindProductWithVariant($product_adjustment_data->product_id, $lims_product_variant_data->variant_id, $lims_adjustment_data->warehouse_id)->first();
                } else {
                    $lims_product_warehouse_data = Product_Warehouse::FindProductWithoutVariant($product_adjustment_data->product_id, $lims_adjustment_data->warehouse_id)->first();
                }

                if ($product_adjustment_data->action == '-') {
                    if ($lims_product_variant_data) {
                        $lims_product_variant_data->qty += $product_adjustment_data->qty;
                        $lims_product_variant_data->save();
                    }
                    $lims_product_data->qty += $product_adjustment_data->qty;
                    $lims_product_warehouse_data->qty += $product_adjustment_data->qty;
                } elseif ($product_adjustment_data->action == '+') {
                    if ($lims_product_variant_data) {
                        $lims_product_variant_data->qty -= $product_adjustment_data->qty;
                        $lims_product_variant_data->save();
                    }
                    $lims_product_data->qty -= $product_adjustment_data->qty;
                    $lims_product_warehouse_data->qty -= $product_adjustment_data->qty;
                }
                $lims_product_data->save();
                $lims_product_warehouse_data->save();
                $product_adjustment_data->delete();
            }
            $lims_adjustment_data->delete();
        }
        return 'Datos eliminado con éxito';
    }

    public function destroy($id)
    {
        $lims_adjustment_data = Adjustment::find($id);
        $lims_product_adjustment_data = ProductAdjustment::where('adjustment_id', $id)->get();
        foreach ($lims_product_adjustment_data as $key => $product_adjustment_data) {
            $lims_product_data = Product::find($product_adjustment_data->product_id);
            if ($lims_product_data->is_variant) {
                $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')->FindExactProductWithCode($product_adjustment_data->product_id, $product_adjustment_data->code)->first();
            } else {
                $lims_product_variant_data = null;
            }
            if ($lims_product_data->is_variant) {
                $lims_product_warehouse_data = Product_Warehouse::FindProductWithVariant($product_adjustment_data->product_id, $lims_product_variant_data->variant_id, $lims_adjustment_data->warehouse_id)->first();
            } else {
                $lims_product_warehouse_data = Product_Warehouse::FindProductWithoutVariant($product_adjustment_data->product_id, $lims_adjustment_data->warehouse_id)->first();
            }

            if ($product_adjustment_data->action == '-') {
                if ($lims_product_variant_data) {
                    $lims_product_variant_data->qty += $product_adjustment_data->qty;
                    $lims_product_variant_data->save();
                }
                $lims_product_data->qty += $product_adjustment_data->qty;
                $lims_product_warehouse_data->qty += $product_adjustment_data->qty;
            } elseif ($product_adjustment_data->action == '+') {
                if ($lims_product_variant_data) {
                    $lims_product_variant_data->qty -= $product_adjustment_data->qty;
                    $lims_product_variant_data->save();
                }
                $lims_product_data->qty -= $product_adjustment_data->qty;
                $lims_product_warehouse_data->qty -= $product_adjustment_data->qty;
            }
            $lims_product_data->save();
            $lims_product_warehouse_data->save();
            $product_adjustment_data->delete();
        }
        $lims_adjustment_data->delete();
        return redirect('qty_adjustment')->with('not_permitted', 'Datos eliminados con éxito');
    }
}
