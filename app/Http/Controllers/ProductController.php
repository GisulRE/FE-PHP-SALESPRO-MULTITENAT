<?php

namespace App\Http\Controllers;

use App\Account;
use App\Brand;
use App\Category;
use App\Product;
use App\Product_Warehouse;
use App\ProductAssociated;
use App\ProductLote;
use App\ProductVariant;
use App\SiatActividadEconomica;
use App\Tax;
use App\Unit;
use App\Variant;
use App\Warehouse;
use Auth;
use DB;
use DNS1D;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Keygen;
use Log;
use Spatie\Permission\Models\Role;

class ProductController extends Controller
{
    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('products-index')) {
            $lims_category_list = Category::select('id', 'name')->where('is_active', true)->get();
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if (empty($all_permission))
                $all_permission[] = 'dummy text';
            return view('product.index', compact('all_permission', 'lims_category_list'));
        } else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function productData(Request $request)
    {
        $columns = array(
            2 => 'name',
            3 => 'code',
            4 => 'brand_id',
            5 => 'category_id',
            6 => 'qty',
            7 => 'unit_id',
            8 => 'price',
            9 => 'courtesy',
        );

        $totalData = Product::where('is_active', true)->count();
        $totalFiltered = $totalData;

        if ($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = 'products.' . $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        if (empty($request->input('search.value'))) {
            $products = Product::with('category', 'brand', 'unit')->offset($start)
                ->where('is_active', true)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();
        } else {
            $search = $request->input('search.value');
            $products = Product::select('products.*')
                ->with('category', 'brand', 'unit')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->leftjoin('brands', 'products.brand_id', '=', 'brands.id')
                ->where([
                    ['products.name', 'LIKE', "%{$search}%"],
                    ['products.is_active', true]
                ])
                ->orWhere([
                    ['products.code', 'LIKE', "%{$search}%"],
                    ['products.is_active', true]
                ])
                ->orWhere([
                    ['categories.name', 'LIKE', "%{$search}%"],
                    ['categories.is_active', true],
                    ['products.is_active', true]
                ])
                ->orWhere([
                    ['brands.title', 'LIKE', "%{$search}%"],
                    ['brands.is_active', true],
                    ['products.is_active', true]
                ])
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)->get();

            $totalFiltered = Product::join('categories', 'products.category_id', '=', 'categories.id')
                ->leftjoin('brands', 'products.brand_id', '=', 'brands.id')
                ->where([
                    ['products.name', 'LIKE', "%{$search}%"],
                    ['products.is_active', true]
                ])
                ->orWhere([
                    ['products.code', 'LIKE', "%{$search}%"],
                    ['products.is_active', true]
                ])
                ->orWhere([
                    ['categories.name', 'LIKE', "%{$search}%"],
                    ['categories.is_active', true],
                    ['products.is_active', true]
                ])
                ->orWhere([
                    ['brands.title', 'LIKE', "%{$search}%"],
                    ['brands.is_active', true],
                    ['products.is_active', true]
                ])
                ->count();
        }
        $data = array();
        if (!empty($products)) {
            foreach ($products as $key => $product) {
                $nestedData['id'] = $product->id;
                $nestedData['key'] = $key;
                $product_image = explode(",", $product->image);
                $product_image = htmlspecialchars($product_image[0]);
                $nestedData['image'] = '<img src="' . url('public/images/product', $product_image) . '" height="80" width="80">';
                $nestedData['name'] = $product->name;
                $nestedData['code'] = $product->code;
                if ($product->brand_id && $product->brand)
                    $nestedData['brand'] = $product->brand->title;
                else
                    $nestedData['brand'] = "N/A";
                $nestedData['category'] = $product->category ? $product->category->name : 'N/A';
                $nestedData['qty'] = number_format($product->cost, 2, ',', ' ');
                if ($product->purchase_unit_id && $product->unit)
                    $nestedData['unit'] = $product->unit->unit_name;
                else
                    $nestedData['unit'] = 'N/A';

                $nestedData['price'] = number_format($product->price, 2, ',', ' ');
                $nestedData['options'] = '<div class="btn-group">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . trans("file.action") . '
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                            <li>
                                <button type="button" class="btn btn-link view"><i class="fa fa-eye"></i> ' . trans('file.View') . '</button>
                            </li>';
                if (in_array("products-edit", $request['all_permission'])) {
                    $nestedData['options'] .= '<li>
                            <a href="' . route('products.edit', $product->id) . '" class="btn btn-link"><i class="fa fa-edit"></i> ' . trans('file.edit') . '</a>
                        </li>';
                    $nestedData['options'] .= '<li>
                        <button type="button" class="btn btn-link" onclick="viewGallery(' . $product->id . ')"><i class="fa fa-image"></i> ' . trans('file.edit') . ' ' . trans('file.Gallery') . '</button>
                        </li>';
                }
                if (in_array("products-delete", $request['all_permission']))
                    $nestedData['options'] .= \Form::open(["route" => ["products.destroy", $product->id], "method" => "DELETE"]) . '
                            <li>
                              <button type="submit" class="btn btn-link" onclick="return confirmDelete()"><i class="fa fa-trash"></i> ' . trans("file.delete") . '</button> 
                            </li>' . \Form::close() . '
                        </ul>
                    </div>';
                // data for product details by one click
                if ($product->tax_id) {
                    $taxObj = Tax::find($product->tax_id);
                    $tax = $taxObj ? $taxObj->name : 'N/A';
                } else {
                    $tax = "N/A";
                }

                if ($product->tax_method == 1)
                    $tax_method = trans('file.Exclusive');
                else
                    $tax_method = trans('file.Inclusive');

                $nestedData['product'] = array(
                    '[ "' . $product->type . '"',
                    ' "' . $product->name . '"',
                    ' "' . $product->code . '"',
                    ' "' . $nestedData['brand'] . '"',
                    ' "' . $nestedData['category'] . '"',
                    ' "' . $nestedData['unit'] . '"',
                    ' "' . $product->cost . '"',
                    ' "' . $product->price . '"',
                    ' "' . $tax . '"',
                    ' "' . $tax_method . '"',
                    ' "' . $product->alert_quantity . '"',
                    ' "' . preg_replace('/\s+/S', " ", $product->product_details) . '"',
                    ' "' . $product->id . '"',
                    ' "' . $product->product_list . '"',
                    ' "' . $product->qty_list . '"',
                    ' "' . $product->price_list . '"',
                    ' "' . $product->qty . '"',
                    ' "' . $product->image . '"',
                    ' "' . $product->commission_percentage . '"]'
                );
                //$nestedData['imagedata'] = DNS1D::getBarcodePNG($product->code, $product->barcode_symbology);
                $nestedData['courtesy'] = $product->courtesy == 'TRUE' ? 'Si' : 'No';
                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $data
        );

        echo json_encode($json_data);
    }

    public function create()
    {
        $role = Role::firstOrCreate(['id' => Auth::user()->role_id]);
        if ($role->hasPermissionTo('products-add')) {
            $lims_product_list = [];
            $lims_product_list_std = Product::where([['is_active', true], ['type', 'standard']])->get();
            $lims_product_list_dig = Product::where([['is_active', true], ['type', 'digital']])->get();
            foreach ($lims_product_list_std as $key => $value) {
                $lims_product_list[] = $value;
            }
            foreach ($lims_product_list_dig as $key => $value) {
                $lims_product_list[] = $value;
            }
            $lims_product_list_ins = Product::where([['is_active', true], ['type', 'insumo']])->get();
            $lims_product_list_all = Product::where('is_active', true)->get();
            $lims_brand_list = Brand::where('is_active', true)->get();
            $lims_category_list = Category::where('is_active', true)->get();
            $lims_unit_list = Unit::where('is_active', true)->get();
            $lims_tax_list = Tax::where('is_active', true)->get();
            $actividades = SiatActividadEconomica::get()->sortBy('descripcion');
            $lims_account_list = Account::select('id', 'name', 'account_no')->where('is_active', true)->where('type', 2)->get();
            return view('product.create', compact('lims_product_list', 'lims_brand_list', 'lims_category_list', 'lims_unit_list', 'lims_tax_list', 'lims_product_list_all', 'lims_product_list_ins', 'actividades', 'lims_account_list'));
        } else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function store(Request $request)
    {

        $this->validate($request, [
            'code' => [
                'max:255',
                Rule::unique('products')->where(function ($query) {
                    $query->where('is_active', 1);
                    if (Auth::check()) {
                        $query->where('company_id', Auth::user()->company_id);
                    }
                    return $query;
                }),
            ],
            'name' => [
                'max:255',
                Rule::unique('products')->where(function ($query) {
                    $query->where('is_active', 1);
                    if (Auth::check()) {
                        $query->where('company_id', Auth::user()->company_id);
                    }
                    return $query;
                }),
            ],
            'file' => 'nullable|image|mimes:jpeg,jpg,png,gif'
        ]);
        try {
            $data = $request->except('image', 'file');
            if ($data['type'] == 'combo') {
                $data['product_list'] = isset($data['product_id']) ? implode(",", $data['product_id']) : '';
                $data['qty_list']     = isset($data['product_qty']) ? implode(",", $data['product_qty']) : '';
                $data['price_list']   = isset($data['unit_price']) ? implode(",", $data['unit_price']) : '';
                $data['cost'] = 0;
                $data['unit_id'] = $data['purchase_unit_id'] = $data['sale_unit_id'] = null;
            } elseif ($data['type'] == 'producto_terminado') {
                $data['product_list'] = isset($data['product_id']) ? implode(",", $data['product_id']) : '';
                $data['qty_list']     = isset($data['product_qty']) ? implode(",", $data['product_qty']) : '';
                $data['price_list']   = isset($data['unit_price']) ? implode(",", $data['unit_price']) : '';
            }

            $data['product_details'] = str_replace('"', '@', $data['product_details'] ?? '');

            if (!empty($data['starting_date']))
                $data['starting_date'] = date('Y-m-d', strtotime($data['starting_date']));
            else
                $data['starting_date'] = null;
            if (!empty($data['last_date']))
                $data['last_date'] = date('Y-m-d', strtotime($data['last_date']));
            else
                $data['last_date'] = null;

            $permanent = $data['permanent'] ?? 'TRUE';
            if ($permanent == 'FALSE') {
                if (!empty($data['starting_date_courtesy']))
                    $data['starting_date_courtesy'] = date('Y-m-d', strtotime($data['starting_date_courtesy']));
                else
                    $data['starting_date_courtesy'] = null;
                if (!empty($data['ending_date_courtesy']))
                    $data['ending_date_courtesy'] = date('Y-m-d', strtotime($data['ending_date_courtesy']));
                else
                    $data['ending_date_courtesy'] = null;
            } else {
                $data['permanent'] = 'TRUE';
                $data['starting_date_courtesy'] = null;
                $data['ending_date_courtesy'] = null;
            }
            if (isset($data['courtesy'])) {
                if ($data['courtesy'] == "FALSE") {
                    $data['courtesy_clearance_price'] = 0;
                }
            } else {
                $data['courtesy'] = 'FALSE';
                $data['courtesy_clearance_price'] = 0;
            }
            $data['is_active'] = true;
            $images = $request->image;
            $image_names = [];
            if ($images) {
                foreach ($images as $key => $image) {
                    $imageName = $image->getClientOriginalName();
                    $image->move('public/images/product', $imageName);
                    $image_names[] = $imageName;
                }
                $data['image'] = implode(",", $image_names);
            } else {
                $data['image'] = 'zummXD2dvAtI.png';
            }
            $file = $request->file;
            if ($file) {
                $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
                $fileName = strtotime(date('Y-m-d H:i:s'));
                $fileName = $fileName . '.' . $ext;
                $file->move('public/product/files', $fileName);
                $data['file'] = $fileName;
            }
            if (!isset($data['saveprice']))
                $data['is_pricelist'] = false;
            else
                $data['is_pricelist'] = true;
            if (!isset($data['featured']))
                $data['featured'] = 0;
            if (!isset($data['is_basicservice']))
                $data['is_basicservice'] = 0;
            // Assign current user's company when creating product
            if (Auth::check()) {
                $data['company_id'] = Auth::user()->company_id;
            }
            $lims_product_data = Product::create($data);
            //dealing with product variant
            if (isset($data['is_variant'])) {
                foreach ($data['variant_name'] as $key => $variant_name) {
                    $lims_variant_data = Variant::firstOrCreate(['name' => $data['variant_name'][$key]]);
                    $lims_variant_data->name = $data['variant_name'][$key];
                    $lims_variant_data->save();
                    $lims_product_variant_data = new ProductVariant;
                    $lims_product_variant_data->product_id = $lims_product_data->id;
                    $lims_product_variant_data->variant_id = $lims_variant_data->id;
                    $lims_product_variant_data->position = $key + 1;
                    $lims_product_variant_data->item_code = $data['item_code'][$key];
                    $lims_product_variant_data->additional_price = $data['additional_price'][$key];
                    $lims_product_variant_data->qty = 0;
                    $lims_product_variant_data->save();
                }
            }

            if (isset($data['courtesy']) && $data['courtesy'] == "TRUE") {
                if (!empty($data['product_id_courtesy'])) {
                    foreach ($data['product_id_courtesy'] as $item) {
                        ProductAssociated::create([
                            'product_courtesy_id' => $lims_product_data->id,
                            'product_associated_id' => $item,
                        ]);
                    }
                }
            }

            \Session::flash('create_message', 'Producto creado con éxito');
        } catch (Exception $e) {
            \Session::flash('not_permitted', 'Error al Crear Producto, Error: ' . $e->getMessage());
            return array('not_permitted' => 'Error al Crear Producto, Error: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $role = Role::firstOrCreate(['id' => Auth::user()->role_id]);
        if ($role->hasPermissionTo('products-edit')) {
            $lims_product_list_std = Product::where([['is_active', true], ['type', 'standard']])->get();
            $lims_product_list_dig = Product::where([['is_active', true], ['type', 'digital']])->get();
            ///$lims_product_list = array_merge($lims_product_list_std, $lims_product_list_dig);
            foreach ($lims_product_list_std as $key => $value) {
                $lims_product_list[] = $value;
            }
            foreach ($lims_product_list_dig as $key => $value) {
                $lims_product_list[] = $value;
            }
            $lims_product_list_ins = Product::where([['is_active', true], ['type', 'insumo']])->get();
            $lims_brand_list = Brand::where('is_active', true)->get();
            $lims_category_list = Category::where('is_active', true)->get();
            $lims_unit_list = Unit::where('is_active', true)->get();
            $lims_tax_list = Tax::where('is_active', true)->get();
            $lims_product_data = Product::where('id', $id)->first();
            $lims_product_variant_data = $lims_product_data->variant()->orderBy('position')->get();
            $lims_product_asocciated = DB::table('product_associated')
                ->select('products.id', 'products.code', 'products.name', 'products.price')
                ->where('product_courtesy_id', $id)
                ->join('products', 'product_associated.product_associated_id', '=', 'products.id')
                ->get();
            //return dd($lims_product_variant_data);
            $lims_product_list_all = Product::where('is_active', true)->get();
            $actividades = SiatActividadEconomica::get()->sortBy('descripcion');
            $lims_account_list = Account::select('id', 'name', 'account_no')->where('is_active', true)->where('type', 2)->get();
            return view('product.edit', compact('lims_product_list', 'lims_brand_list', 'lims_category_list', 'lims_unit_list', 'lims_tax_list', 'lims_product_data', 'lims_product_variant_data', 'lims_product_list_all', 'lims_product_asocciated', 'lims_product_list_ins', 'actividades', 'lims_account_list'));
        } else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function updateProduct(Request $request)
    {
        $companyId = Auth::check() ? Auth::user()->company_id : null;
        $this->validate($request, [
            'name' => [
                'max:255',
                Rule::unique('products')->ignore($request->input('id'))->where(function ($query) use ($companyId) {
                    $query->where('is_active', 1);
                    if ($companyId) {
                        $query->where('company_id', $companyId);
                    }
                    return $query;
                }),
            ],
            'code' => [
                'max:255',
                Rule::unique('products')->ignore($request->input('id'))->where(function ($query) use ($companyId) {
                    $query->where('is_active', 1);
                    if ($companyId) {
                        $query->where('company_id', $companyId);
                    }
                    return $query;
                }),
            ],
            'file' => 'nullable|image|mimes:jpeg,jpg,png,gif'
        ]);
        try {
            DB::beginTransaction();
            $data = $request->except('image', 'file');
            $lims_product_data = Product::findOrFail($request->input('id'));
            $data = $request->except('image', 'file');

            if ($data['type'] == 'combo') {
                $data['product_list'] = implode(",", $data['product_id']);
                $data['qty_list'] = implode(",", $data['product_qty']);
                $data['price_list'] = implode(",", $data['unit_price']);
                $data['cost'] = 0;
                $data['unit_id'] = $data['purchase_unit_id'] = $data['sale_unit_id'] = null;
                if (!isset($data['saveprice'])) {
                    $data['is_pricelist'] = false;
                    $price = 0;
                    foreach ($data['product_id'] as $key => $item) {
                        $temp_product_data = Product::select('id', 'price')->find($item);
                        $price = $price + ($temp_product_data->price * $data['qty_list'][$key]);
                    }
                    $data['price'] = $price;
                } else
                    $data['is_pricelist'] = true;
            } elseif ($data['type'] == 'producto_terminado') {
                $data['product_list'] = implode(",", $data['product_id']);
                $data['qty_list'] = implode(",", $data['product_qty']);
                $data['price_list'] = implode(",", $data['unit_price']);
                //$data['cost'] = $data['unit_id'] = $data['purchase_unit_id'] = $data['sale_unit_id'] = 0;
            }

            if (!isset($data['featured']))
                $data['featured'] = 0;
            if (!isset($data['is_basicservice']))
                $data['is_basicservice'] = 0;
            $data['product_details'] = str_replace('"', '@', $data['product_details']);
            $data['product_details'] = $data['product_details'];
            if ($data['starting_date'])
                $data['starting_date'] = date('Y-m-d', strtotime($data['starting_date']));
            if ($data['last_date'])
                $data['last_date'] = date('Y-m-d', strtotime($data['last_date']));
            if ($data['permanent'] == 'FALSE') {
                if ($data['starting_date_courtesy'])
                    $data['starting_date_courtesy'] = date('Y-m-d', strtotime($data['starting_date_courtesy']));
                if ($data['ending_date_courtesy'])
                    $data['ending_date_courtesy'] = date('Y-m-d', strtotime($data['ending_date_courtesy']));
            } else {
                $data['starting_date_courtesy'] = null;
                $data['ending_date_courtesy'] = null;
            }
            if (!isset($data['courtesy'])) {
                $data['courtesy'] = 'FALSE';
                $data['permanent'] = 'TRUE';
                $data['starting_date_courtesy'] = null;
                $data['ending_date_courtesy'] = null;
                $data['courtesy_clearance_price'] = 0;
                ProductAssociated::where('product_courtesy_id', $lims_product_data->id)->delete();
            }
            $images = $request->image;
            $image_names = [];
            if ($images) {
                foreach ($images as $key => $image) {
                    $imageName = $image->getClientOriginalName();
                    $image->move('public/images/product', $imageName);
                    $image_names[] = $imageName;
                }
                if ($lims_product_data->image != 'zummXD2dvAtI.png') {
                    $data['image'] = $lims_product_data->image . ',' . implode(",", $image_names);
                } else {
                    $data['image'] = implode(",", $image_names);
                }
            } else {
                $data['image'] = $lims_product_data->image;
            }

            $file = $request->file;
            if ($file) {
                $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
                $fileName = strtotime(date('Y-m-d H:i:s'));
                $fileName = $fileName . '.' . $ext;
                $file->move('public/product/files', $fileName);
                $data['file'] = $fileName;
            }

            $lims_product_data->update($data);
            $lims_product_variant_data = ProductVariant::where('product_id', $request->input('id'))->select('id', 'variant_id')->get();
            foreach ($lims_product_variant_data as $key => $value) {
                if (!in_array($value->variant_id, $data['variant_id'])) {
                    ProductVariant::find($value->id)->delete();
                }
            }
            //dealing with product variant
            if (isset($data['is_variant'])) {
                foreach ($data['variant_name'] as $key => $variant_name) {
                    if ($data['product_variant_id'][$key] == 0) {
                        $lims_variant_data = Variant::firstOrCreate(['name' => $data['variant_name'][$key]]);
                        $lims_product_variant_data = new ProductVariant();

                        $lims_product_variant_data->product_id = $lims_product_data->id;
                        $lims_product_variant_data->variant_id = $lims_variant_data->id;

                        $lims_product_variant_data->position = $key + 1;
                        $lims_product_variant_data->item_code = $data['item_code'][$key];
                        $lims_product_variant_data->additional_price = $data['additional_price'][$key];
                        $lims_product_variant_data->qty = 0;
                        $lims_product_variant_data->save();
                    } else {
                        Variant::find($data['variant_id'][$key])->update(['name' => $variant_name]);
                        ProductVariant::find($data['product_variant_id'][$key])->update([
                            'position' => $key + 1,
                            'item_code' => $data['item_code'][$key],
                            'additional_price' => $data['additional_price'][$key]
                        ]);
                    }
                }
            }
            if (isset($data['courtesy']) && $data['courtesy'] == 'TRUE') {
                ProductAssociated::where('product_courtesy_id', $lims_product_data->id)->delete(); //solucion 1
                foreach ($data['product_id_courtesy'] as $item) {
                    ProductAssociated::create([
                        'product_courtesy_id' => $lims_product_data->id,
                        'product_associated_id' => $item,
                    ]);
                }
            }
            DB::commit();
            \Session::flash('edit_message', 'Producto actualizado con éxito');
        } catch (Exception $e) {
            DB::rollBack();
            log::error("error updating product: " . $e->getMessage());
            \Session::flash('not_permitted', 'Error al Editar Producto, Error: ' . $e->getMessage());
            return array('not_permitted' => 'Error al Editar Producto, Error: ' . $e->getMessage());
        }
    }

    public function generateCode()
    {
        $id = Keygen::numeric(8)->generate();
        return $id;
    }

    public function search(Request $request)
    {
        $product_code = explode(" ", $request['data']);
        $lims_product_data = Product::select('id', 'code', 'name', 'qty', 'price', 'cost', 'sale_unit_id')->where('code', $product_code[0])->first();
        $unit = Unit::find($lims_product_data->sale_unit_id);
        $product[] = $lims_product_data->name;
        $product[] = $lims_product_data->code;
        $product[] = $lims_product_data->qty;
        $product[] = $lims_product_data->price;
        $product[] = $lims_product_data->id;
        if ($unit)
            $product[] = $unit->unit_code;
        else
            $product[] = "S/N";

        $product[] = $lims_product_data->cost;
        return $product;
    }

    public function saleUnit($id)
    {
        $unit = Unit::where("base_unit", $id)->orWhere('id', $id)->pluck('unit_name', 'id');
        return json_encode($unit);
    }

    public function getData($id)
    {
        $data = Product::select('name', 'code')->where('id', $id)->get();
        return $data[0];
    }

    public function getProductByFilter($parameter)
    {
        $data = null;
        if ($parameter == "combo") {
            $lims_product_list_std = Product::where([['is_active', true], ['type', 'standard']])->get();
            $lims_product_list_dig = Product::where([['is_active', true], ['type', 'digital']])->get();
            foreach ($lims_product_list_std as $key => $value) {
                $lims_product_list[] = $value->code . ' [' . $value->name . '] ';
            }
            foreach ($lims_product_list_dig as $key => $value) {
                $lims_product_list[] = $value->code . ' [' . $value->name . '] ';
            }
            $data = $lims_product_list;
        } else if ($parameter == "pro_terminado") {
            $lims_product_list_ins = Product::where([['is_active', true], ['type', 'insumo']])->get();
            foreach ($lims_product_list_ins as $key => $value) {
                $lims_product_list[] = $value->code . ' [' . $value->name . '] ';
            }
            $data = $lims_product_list;
        } else {
            $lims_product_list_all = Product::where('is_active', true)->get();
            foreach ($lims_product_list_all as $key => $value) {
                $lims_product_list[] = $value->code . ' [' . $value->name . '] ';
            }
            $data = $lims_product_list;
        }
        return $data;
    }

    // API: lista pública de productos (servicios) con filtro por category_id y paginación
    public function apiIndex(Request $request)
    {
        $perPage = intval($request->query('per_page', 10));
        if ($perPage <= 0)
            $perPage = 10;

        $query = Product::query();
        // Sólo activos
        $query->where('is_active', true);

        // Filtro por categoría si se provee
        $categoryId = $request->query('category_id');
        if ($categoryId !== null && is_numeric($categoryId)) {
            $query->where('category_id', intval($categoryId));
        }

        // Búsqueda por término (q) en name, code, category.name o brand.title
        $term = $request->query('q');
        if ($term !== null && $term !== '') {
            $term = trim($term);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('code', 'LIKE', "%{$term}%")
                    ->orWhereHas('category', function ($cq) use ($term) {
                        $cq->where('name', 'LIKE', "%{$term}%");
                    })
                    ->orWhereHas('brand', function ($bq) use ($term) {
                        $bq->where('title', 'LIKE', "%{$term}%");
                    });
            });
        }

        $products = $query->with('category', 'brand')->paginate($perPage);

        return response()->json($products);
    }

    // API: lista pública de categorías
    public function apiCategories(Request $request)
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    public function productWarehouseData($id)
    {
        $warehouse = [];
        $qty = [];
        $blocked_qty = [];
        $warehouse_name = [];
        $variant_name = [];
        $variant_qty = [];
        $variant_blocked_qty = [];
        $product_warehouse = [];
        $product_variant_warehouse = [];
        $lims_product_data = Product::select('id', 'is_variant')->find($id);
        if ($lims_product_data->is_variant) {
            $lims_product_variant_warehouse_data = Product_Warehouse::where('product_id', $lims_product_data->id)->orderBy('warehouse_id')->get();
            $lims_product_warehouse_data = Product_Warehouse::select('warehouse_id', DB::raw('sum(qty) as qty'), DB::raw('sum(blocked_qty) as blocked_qty'))->where('product_id', $id)->groupBy('warehouse_id')->get();
            foreach ($lims_product_variant_warehouse_data as $key => $product_variant_warehouse_data) {
                $lims_warehouse_data = Warehouse::find($product_variant_warehouse_data->warehouse_id);
                $lims_variant_data = Variant::find($product_variant_warehouse_data->variant_id);
                $warehouse_name[] = $lims_warehouse_data->name;
                $variant_name[] = $lims_variant_data->name;
                $variant_qty[] = $product_variant_warehouse_data->qty;
                $variant_blocked_qty[] = $product_variant_warehouse_data->blocked_qty;
            }
        } else {
            $lims_product_warehouse_data = Product_Warehouse::where('product_id', $id)->get();
        }
        foreach ($lims_product_warehouse_data as $key => $product_warehouse_data) {
            $lims_warehouse_data = Warehouse::find($product_warehouse_data->warehouse_id);
            $warehouse[] = $lims_warehouse_data->name;
            $qty[] = $product_warehouse_data->qty;
            $blocked_qty[] = $product_warehouse_data->blocked_qty;
        }

        $product_warehouse = [$warehouse, $qty, $blocked_qty]; // agregado qty blocked
        $product_variant_warehouse = [$warehouse_name, $variant_name, $variant_qty, $variant_blocked_qty]; // agregado qty blocked

        return ['product_warehouse' => $product_warehouse, 'product_variant_warehouse' => $product_variant_warehouse];
    }

    public function printBarcode()
    {
        $lims_product_list = Product::where('is_active', true)->get();
        return view('product.print_barcode', compact('lims_product_list'));
    }

    public function limsProductSearch(Request $request)
    {
        $todayDate = date('Y-m-d');
        $product_code = explode(" ", $request['data']);

        $lims_product_data = Product::where('code', $product_code[0])->first();
        $product[] = $lims_product_data->name;
        $product[] = $lims_product_data->code;
        $product[] = $lims_product_data->price;
        $product[] = DNS1D::getBarcodePNG($lims_product_data->code, $lims_product_data->barcode_symbology);
        $product[] = $lims_product_data->promotion_price;
        $product[] = config('currency');
        $product[] = config('currency_position');
        return $product;
    }

    /*public function getBarcode()
    {
    return DNS1D::getBarcodePNG('72782608', 'C128');
    }*/

    public function importProduct(Request $request)
    {
        $upload = $request->file('file');
        $ext = pathinfo($upload->getClientOriginalName(), PATHINFO_EXTENSION);

        if ('csv' == $ext) {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
        } else {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        }

        $filePath = $upload->getRealPath();
        $spreadsheet = $reader->load($filePath);
        $sheet_data = $spreadsheet->getActiveSheet()->toArray();
        $escapedHeader = $sheet_data[0];
        $header_template = [
            0 => 'nombre',
            1 => 'codigo',
            2 => 'tipo',
            3 => 'marca',
            4 => 'categoria',
            5 => 'unidad',
            6 => 'costo',
            7 => 'precio',
            8 => 'precioA',
            9 => 'precioB',
            10 => 'precioC',
            11 => 'descripcion',
            12 => 'codigoActividadSIN',
            13 => 'codigoProductoSIN'
        ];
        $resultCompare = array_diff($escapedHeader, $header_template);
        $error_message = "";
        if (sizeof($resultCompare) > 0) {
            $error_message .= "Error en Validar Estructura de la Cabecera del Archivo, Revise el Documento en Fila Nro: 0 | \n";
            foreach ($resultCompare as $error) {
                $error_message .= "Error en Validar la columna: $error no valida,  Revise el Documento y Corrija la estructura en Fila Nro: 0 | \n";
            }
            return redirect('products')->with('not_permitted', "Error al Importar Productos:  $error_message");
        }
        try {
            DB::beginTransaction();
            Log::info("start product imported...");

            foreach ($sheet_data as $key => $val) {
                if ($key != 0 && $key > 0 && $val[0] != null) {
                    $data = array_combine($escapedHeader, $val);

                    $code = trim($data['codigo']);
                    $name = trim($data['nombre']);

                    // Buscar producto por código o nombre (filtrado por empresa actual)
                    $product = Product::where('is_active', true)
                        ->where(function ($q) use ($code, $name) {
                            $q->where('code', $code)
                                ->orWhere('name', $name);
                        })
                        ->first();
                    // Asignar company_id del usuario autenticado
                    $currentCompanyId = Auth::check() ? Auth::user()->company_id : null;

                    if ($data['marca'] != 'N/A' && $data['marca'] != '') {
                        $lims_brand_data = Brand::firstOrCreate(['title' => $data['marca'], 'is_active' => true]);
                        $brand_id = $lims_brand_data->id;
                    } else {
                        $brand_id = null;
                    }

                    $lims_category_data = Category::firstOrCreate(['name' => $data['categoria'], 'is_active' => true]);
                    $lims_unit_data = Unit::where('unit_code', $data['unidad'])->first();

                    if ($data['codigo'] == null || $data['codigo'] == '') {
                        $data['codigo'] = $this->generateCode();
                        $code = trim($data['codigo']);
                    }

                    if ($product) {
                        // Actualizar producto existente
                        $product->name = $data['nombre'];
                        $product->code = $data['codigo'];
                        $product->type = strtolower($data['tipo']);
                        $product->barcode_symbology = 'C128';
                        $product->brand_id = $brand_id;
                        $product->category_id = $lims_category_data->id;
                        if ($lims_unit_data) {
                            $product->unit_id = $lims_unit_data->id;
                            $product->purchase_unit_id = $lims_unit_data->id;
                            $product->sale_unit_id = $lims_unit_data->id;
                        } else {
                            $product->unit_id = null;
                            $product->purchase_unit_id = null;
                            $product->sale_unit_id = null;
                        }
                        $product->cost = $data['costo'] ?? 0;
                        $product->price = $data['precio'];
                        $product->price_a = $data['precioA'] ?? 0;
                        $product->price_b = $data['precioB'] ?? 0;
                        $product->price_c = $data['precioC'] ?? 0;
                        if ($data['codigoActividadSIN'] != null) {
                            $product->codigo_actividad = $data['codigoActividadSIN'];
                        }
                        if ($data['codigoProductoSIN'] != null) {
                            $product->codigo_producto_servicio = $data['codigoProductoSIN'];
                        }
                        $product->tax_method = 1;
                        $product->qty = 0;
                        $product->product_details = $data['descripcion'];
                        $product->is_active = true;
                        $product->is_basicservice = false;
                        if (!$product->image) {
                            $product->image = 'zummXD2dvAtI.png';
                        }
                        if ($currentCompanyId) {
                            $product->company_id = $currentCompanyId;
                        }
                        $product->save();
                    } else {
                        // Crear nuevo producto
                        $product = new Product();
                        $product->name = $data['nombre'];
                        $product->code = $data['codigo'];
                        $product->type = strtolower($data['tipo']);
                        $product->barcode_symbology = 'C128';
                        $product->brand_id = $brand_id;
                        $product->category_id = $lims_category_data->id;
                        if ($lims_unit_data) {
                            $product->unit_id = $lims_unit_data->id;
                            $product->purchase_unit_id = $lims_unit_data->id;
                            $product->sale_unit_id = $lims_unit_data->id;
                        } else {
                            $product->unit_id = null;
                            $product->purchase_unit_id = null;
                            $product->sale_unit_id = null;
                        }
                        $product->cost = $data['costo'] ?? 0;
                        $product->price = $data['precio'];
                        $product->price_a = $data['precioA'] ?? 0;
                        $product->price_b = $data['precioB'] ?? 0;
                        $product->price_c = $data['precioC'] ?? 0;
                        if ($data['codigoActividadSIN'] != null) {
                            $product->codigo_actividad = $data['codigoActividadSIN'];
                        }
                        if ($data['codigoProductoSIN'] != null) {
                            $product->codigo_producto_servicio = $data['codigoProductoSIN'];
                        }
                        $product->tax_method = 1;
                        $product->qty = 0;
                        $product->product_details = $data['descripcion'];
                        $product->is_active = true;
                        $product->is_basicservice = false;
                        $product->image = 'zummXD2dvAtI.png';
                        if ($currentCompanyId) {
                            $product->company_id = $currentCompanyId;
                        }
                        $product->save();
                    }
                }
            }
            Log::info("end product imported successfully");
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("Error Importacion Masiva de Productos => " . $th);
            $error_message = "Error: " . $th->getMessage();
            return redirect('products')->with('not_permitted', "Error al Importar Productos:  $error_message");
        }
        return redirect('products')->with('import_message', 'Productos importados/actualizados con éxito');
    }

    public function deleteBySelection(Request $request)
    {
        $product_id = $request['productIdArray'];
        foreach ($product_id as $id) {
            $lims_product_data = Product::findOrFail($id);
            $lims_product_data->is_active = false;
            $lims_product_data->save();
        }
        return 'Productos eliminados con éxito!';
    }

    public function destroy($id)
    {
        $lims_product_data = Product::findOrFail($id);
        $lims_product_data->is_active = false;
        if ($lims_product_data->image != 'zummXD2dvAtI.png') {
            $images = explode(",", $lims_product_data->image);
            foreach ($images as $key => $image) {
                try {
                    unlink('public/images/product/' . $image);
                } catch (\Throwable $th) {
                    Log::error("Error Image of Product Id: " . $id . ", not found => " . $th);
                }
            }
        }
        $lims_product_data->save();
        return redirect('products')->with('message', 'Producto eliminado con éxito');
    }

    public function lotesforProduct($id)
    {
        $lotes_list = ProductLote::select('name', 'expiration', 'qty', 'stock', 'status')->where([['idproduct', $id], ['status', '!=', 0]])->get();
        $size_list = sizeof($lotes_list);
        return array(
            'lotes' => $lotes_list,
            'size' => $size_list,
            'product_id' => $id
        );
    }

    public function listGallery(Request $request)
    {
        $list = array();
        $product_id = $request['id'];
        $product = Product::select('id', 'image')->find($product_id);
        $totalData = 0;
        $totalFiltered = 0;
        if ($product) {
            $listImages = explode(',', $product->image);
            $totalData = sizeof($listImages);
            $totalFiltered = $totalData;
            foreach ($listImages as $key => $image) {
                $product_image = htmlspecialchars($image);
                if ($image != 'zummXD2dvAtI.png' && $image != '')
                    $list[] = array(
                        "id" => $key,
                        "image" => '<img src="' . url('public/images/product', $product_image) . '" height="80" width="80">',
                        "name" => $image,
                        "options" => "<button type='button' class='btn btn-danger' data-toggle='tooltip' title='Eliminar Imagen' onclick='deleteImage(" . $key . ")'><i class='fa fa-trash'></i></button>"
                    );
            }
        }
        $json_data = array(
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $list
        );
        echo json_encode($json_data);
    }

    public function getPrice(int $id, string $type)
    {
        $price = 0;
        $listPrice = array();
        $product = Product::select('id', 'price', 'price_a', 'price_b', 'price_c')->find($id);
        if ($product) {
            if ($product->price > 0) {
                $listPrice[] = $product->price;
            }
            if ($product->price_a > 0) {
                $listPrice[] = $product->price_a;
            }
            if ($product->price_b > 0) {
                $listPrice[] = $product->price_b;
            }
            if ($product->price_c > 0) {
                $listPrice[] = $product->price_c;
            }
            if ($type == 'max')
                $price = max($listPrice);
            else
                $price = min($listPrice);

            return array('estado' => true, "mensaje" => "Producto encontrado!", "price" => $price, "price_default" => $product->price);
        } else {
            return array('estado' => false, "mensaje" => "Producto no encontrado!", "price" => $price, "price_default" => $price);
        }
    }

    public function updateImage(Request $request)
    {
        $this->validate($request, [
            'image' => 'required',
        ]);
        $product_id = $request->id;
        $product = Product::select('id', 'image')->find($product_id);
        $images = $request->image;
        $image_names = [];
        if ($product) {
            if ($images) {
                foreach ($images as $key => $image) {
                    $imageName = $image->getClientOriginalName();
                    $fileName = "product_" . $product_id . "_" . $key . "_" . time() . '.' . $image->getClientOriginalExtension();
                    $image->move('public/images/product', $fileName);
                    $image_names[] = $fileName;
                }
                if ($product->image != 'zummXD2dvAtI.png') {
                    $product->image = $product->image . ',' . implode(",", $image_names);
                } else {
                    $product->image = implode(",", $image_names);
                }
            } else {
                $product->image = 'zummXD2dvAtI.png';
            }
            $product->save();
            return array('estado' => true);
        } else {
            return array('estado' => false, "mensaje" => "Producto no encontrado!");
        }
    }

    public function deleteImage($product_id, $position)
    {
        $product = Product::select('id', 'image')->find($product_id);
        if ($product) {
            $listImages = explode(',', $product->image);
            try {
                $listImages[$position];
                try {
                    unlink('public/images/product/' . $listImages[$position]);
                } catch (\Throwable $th) {
                    Log::error("Error Image of Product Id: " . $product_id . ", not found => " . $th);
                }
                unset($listImages[$position]);
                $image = implode(',', $listImages);
                if ($listImages == null) {
                    $product->image = "zummXD2dvAtI.png";
                } else {
                    $product->image = $image;
                }
                $product->save();
                return array('estado' => true);
            } catch (Exception $e) {
                Log::error($e->getMessage());
                return array('estado' => false, "mensaje" => "Eliminar Imagen: " . $e->getMessage());
            }
        } else {
            return array('estado' => false, "mensaje" => "Producto no encontrado!");
        }
    }

    public function downloadExcel($category_id = 0)
    {
        if ($category_id != 0) {
            $products = Product::with('account')->where('is_active', true)->where('category_id', $category_id)->get();
        } else {
            $products = Product::with('account')->where('is_active', true)->get();
        }
        if (count($products) == 0) {
            return redirect('products')->with('not_permitted', 'No hay productos para exportar!');
        }
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header row
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Nombre');
        $sheet->setCellValue('C1', 'Código');
        $sheet->setCellValue('D1', 'Costo');
        $sheet->setCellValue('E1', 'Precio');
        $sheet->setCellValue('F1', 'Precio A');
        $sheet->setCellValue('G1', 'Precio B');
        $sheet->setCellValue('H1', 'Precio C');
        $sheet->setCellValue('I1', 'Nro Cuenta');
        $sheet->setCellValue('J1', 'Cuenta Contable');
        $sheet->setCellValue('K1', 'Ultima Modificación');

        // Populate data rows
        $row = 2;
        foreach ($products as $product) {
            $sheet->setCellValue('A' . $row, $product->id);
            $sheet->setCellValue('B' . $row, $product->name);
            $sheet->setCellValue('C' . $row, $product->code);
            $sheet->setCellValue('D' . $row, $product->cost ? $product->cost : 0);
            $sheet->setCellValue('E' . $row, $product->price ? $product->price : 0);
            $sheet->setCellValue('F' . $row, $product->price_a ? $product->price_a : 0);
            $sheet->setCellValue('G' . $row, $product->price_b ? $product->price_b : 0);
            $sheet->setCellValue('H' . $row, $product->price_c ? $product->price_c : 0);
            $sheet->setCellValue('I' . $row, $product->account ? $product->account->account_no : '');
            $sheet->setCellValue('J' . $row, $product->account ? $product->account->name : '');
            $sheet->setCellValue('K' . $row, $product->updated_at ? $product->updated_at->format('d/m/Y H:i:s') : '');
            $row++;
        }

        // Set headers for download
        if ($category_id != 0) {
            $fileName = 'productos_precios_categoria_' . $category_id . '_' . date('Ymd_His') . '.xlsx';
        } else {
            $fileName = 'productos_precios_todos_' . date('Ymd_His') . '.xlsx';
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function importProductUpdate(Request $request)
    {
        $upload = $request->file('file');
        $ext = pathinfo($upload->getClientOriginalName(), PATHINFO_EXTENSION);

        if ('csv' == $ext) {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
        } else if ('xlsx' == $ext) {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        } else {
            $result = array(
                'message' => "Error en el formato del archivo, solo se permiten archivos .csv o .xlsx",
                'totalreq' => 0,
                'totalprocess' => 1,
                'status' => false
            );
            return json_encode($result);
        }

        $filePath = $upload->getRealPath();
        $spreadsheet = $reader->load($filePath);
        $sheet_data = $spreadsheet->getActiveSheet()->toArray();
        $escapedHeader = $sheet_data[0];
        $header_template = array(
            0 => 'ID',
            1 => 'Nombre',
            2 => 'Código',
            3 => 'Costo',
            4 => 'Precio',
            5 => 'Precio A',
            6 => 'Precio B',
            7 => 'Precio C',
            8 => 'Nro Cuenta',
            9 => 'Cuenta Contable',
            10 => 'Ultima Modificación'
        );

        $resultCompare = array_diff($escapedHeader, $header_template);
        $error_message = "";
        if (sizeof($resultCompare) > 0) {
            $error_message .= "Error en Validar Estructura de la Cabecera del Archivo, Revise el Documento en Fila Nro: " . 0 . " | " . "\n";
            foreach ($resultCompare as $error) {
                $error_message .= "Error en Validar la columna: " . $error . " no valida,  Revise el Documento y Corrija la estructura en Fila Nro: " . 0 . " | " . "\n";
            }
            Log::error("Error Importacion Masiva de Actualización de Productos => " . $error_message);
            $result = array(
                'message' => $error_message,
                'totalreq' => 0,
                'totalprocess' => 1,
                'status' => false
            );
            return json_encode($result);
        }

        try {
            DB::beginTransaction();
            Log::info("start product update import...");
            $subtotal = null;
            $contador_error = 0;
            $total_rows = sizeof($sheet_data);
            foreach ($sheet_data as $key => $val) {
                if ($key != 0 && $key > 0 && $val[0] != null) {
                    $data = array_combine($escapedHeader, $val);

                    $product = Product::with('account')->find($data['ID']);
                    if ($product) {
                        $product->name = $data['Nombre'];
                        $product->code = $data['Código'];
                        $product->cost = $data['Costo'] == null ? 0 : $data['Costo'];
                        $product->price = $data['Precio'];
                        $product->price_a = $data['Precio A'] ?? 0;
                        $product->price_b = $data['Precio B'] ?? 0;
                        $product->price_c = $data['Precio C'] ?? 0;
                        if (
                            ($data['Cuenta Contable'] != null && $data['Nro Cuenta'] != null)
                            || ($data['Cuenta Contable'] != '' && $data['Nro Cuenta'] != '')
                        ) {
                            $account = Account::where('account_no', $data['Nro Cuenta'])->first();
                            if ($account) {
                                $account->account_no = $data['Nro Cuenta'];
                                $account->name = $data['Cuenta Contable'];
                                $account->save();
                                $product->account_id = $account->id;
                            } else {
                                $account = new Account();
                                $account->account_no = $data['Nro Cuenta'];
                                $account->name = $data['Cuenta Contable'];
                                $account->initial_balance = 0;
                                $account->is_default = false;
                                $account->is_active = true;
                                $account->type = 2;
                                $account->save();
                                $product->account_id = $account->id;
                            }
                        } else {
                            $product->account_id = null;
                        }
                        $product->save();
                    } else {
                        Log::warning("Producto con ID " . $data['id'] . " no encontrado.");
                        $contador_error++;
                        /*$result = array(
                            'message' => "Error: Producto con ID " . $data['ID'] . " no encontrado.",
                            'totalreq' => $contador_error,
                            'totalprocess' => $total_rows,
                            'status' => false
                        );
                        return json_encode($result);*/
                    }
                }
            }
            Log::info("end product update import successfully");
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("Error Importacion Masiva de Actualización de Productos => " . $th);
            $error_message = "Error: " . $th->getMessage();
            $result = array(
                'message' => "Error al Actualizar Productos:  " . $error_message,
                'totalreq' => $contador_error,
                'totalprocess' => $total_rows,
                'status' => true
            );
            return json_encode($result);
        }
        $result = array(
            'message' => "Productos actualizados con éxito",
            'totalreq' => $contador_error,
            'totalprocess' => $total_rows,
            'status' => true
        );
        return json_encode($result);
    }
}