<?php

namespace App\Http\Controllers;

use App\PosSetting;
use App\UserCategory;
use DB;
use Auth;
use App\Product;
use App\Category;
use Exception;
use Illuminate\Http\Request;
use App\SiatProductoServicio;
use App\SiatActividadEconomica;
use Illuminate\Validation\Rule;
use Log;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CategoryController extends Controller
{
    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('category')) {
            $lims_pos_setting_data = PosSetting::latest()->first();
            $lims_categories = Category::where('is_active', true)->pluck('name', 'id');
            if ($lims_pos_setting_data && $lims_pos_setting_data->user_category) {
                $lims_category_all = Category::select('categories.id', 'categories.name', 'categories.image', 'categories.parent_id', 'categories.is_active', 'categories.codigo_actividad', 'categories.codigo_producto_servicio')
                    ->join('user_category', 'categories.id', '=', 'user_category.category_id')->where('user_category.user_id', '=', Auth::user()->id)->where('categories.is_active', '=', true)->get();
            } else
                $lims_category_all = Category::where('is_active', true)->get();

            $actividades = SiatActividadEconomica::get()->sortBy('descripcion');
            $actividades = $actividades->unique('codigo_caeb');
            return view('category.create', compact('lims_categories', 'lims_category_all', 'actividades'));
        } else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function categoryData(Request $request)
    {
        $columns = array(
            0 => 'id',
            2 => 'name',
            3 => 'parent_id',
            4 => 'is_active',
        );
        $lims_pos_setting_data = PosSetting::latest()->first();
        if ($lims_pos_setting_data && $lims_pos_setting_data->user_category)
            $totalData = UserCategory::where('user_id', Auth::user()->id)->count();
        else
            $totalData = Category::where('is_active', true)->count();

        $totalFiltered = $totalData;

        if ($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        if (empty($request->input('search.value'))) {
            if ($lims_pos_setting_data && $lims_pos_setting_data->user_category) {
                $categories = Category::select('categories.id', 'categories.name', 'categories.image', 'categories.parent_id', 'categories.is_active', 'categories.codigo_actividad', 'categories.codigo_producto_servicio')
                ->offset($start)
                    ->join('user_category', 'categories.id', '=', 'user_category.category_id')
                    ->where('categories.is_active', true)
                    ->where('user_category.user_id', '=', Auth::user()->id)
                    ->limit($limit)
                    ->orderBy($order, $dir)
                    ->get();
            } else {
                $categories = Category::offset($start)
                    ->where('is_active', true)
                    ->limit($limit)
                    ->orderBy($order, $dir)
                    ->get();
            }
        } else {
            $search = $request->input('search.value');
            if ($lims_pos_setting_data && $lims_pos_setting_data->user_category) {
                $categories = Category::select('categories.id', 'categories.name', 'categories.image', 'categories.parent_id', 'categories.is_active', 'categories.codigo_actividad', 'categories.codigo_producto_servicio')
                ->join('user_category', 'categories.id', '=', 'user_category.category_id')
                ->where([
                    ['categories.name', 'LIKE', "%{$search}%"],
                    ['categories.is_active', true]
                ])
                ->where('user_category.user_id', '=', Auth::user()->id)
                ->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)->get();

                $totalFiltered = Category::select('categories.id', 'categories.name', 'categories.image', 'categories.parent_id', 'categories.is_active', 'categories.codigo_actividad', 'categories.codigo_producto_servicio')
                ->join('user_category', 'categories.id', '=', 'user_category.category_id')
                ->where([
                    ['categories.name', 'LIKE', "%{$search}%"],
                    ['categories.is_active', true]
                ])->where('user_category.user_id', '=', Auth::user()->id)->count();
            } else {
                $categories = Category::where([
                    ['name', 'LIKE', "%{$search}%"],
                    ['is_active', true]
                ])->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)->get();

                $totalFiltered = Category::where([
                    ['name', 'LIKE', "%{$search}%"],
                    ['is_active', true]
                ])->count();
            }
        }
        $data = array();
        if (!empty($categories)) {
            foreach ($categories as $key => $category) {
                $nestedData['id'] = $category->id;
                $nestedData['key'] = $key;

                if ($category->image)
                    $nestedData['image'] = '<img src="' . url('public/images/category', $category->image) . '" height="70" width="70">';
                else
                    $nestedData['image'] = '<img src="' . url('public/images/product/zummXD2dvAtI.png') . '" height="80" width="80">';

                $nestedData['name'] = $category->name;

                if ($category->parent_id)
                    $nestedData['parent_id'] = Category::find($category->parent_id)->name;
                else
                    $nestedData['parent_id'] = "N/A";

                $nestedData['number_of_product'] = $category->product()->where('is_active', true)->count();
                $nestedData['stock_qty'] = $category->product()->where('is_active', true)->sum('qty');
                $total_price = $category->product()->where('is_active', true)->sum(DB::raw('price * qty'));
                $total_cost = $category->product()->where('is_active', true)->sum(DB::raw('cost * qty'));

                if (config('currency_position') == 'prefix')
                    $nestedData['stock_worth'] = config('currency') . ' ' . $total_price . ' / ' . config('currency') . ' ' . $total_cost;
                else
                    $nestedData['stock_worth'] = $total_price . ' ' . config('currency') . ' / ' . $total_cost . ' ' . config('currency');

                $nestedData['options'] = '<div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . trans("file.action") . '
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                                <li>
                                    <button type="button" data-id="' . $category->id . '" class="open-EditCategoryDialog btn btn-link" data-toggle="modal" data-target="#editModal" ><i class="dripicons-document-edit"></i> ' . trans("file.edit") . '</button>
                                </li>
                                <li class="divider"></li>' .
                    \Form::open(["route" => ["category.destroy", $category->id], "method" => "DELETE"]) . '
                                <li>
                                    <button type="submit" class="btn btn-link" onclick="return confirmDelete()"><i class="dripicons-trash"></i> ' . trans("file.delete") . '</button> 
                                </li>' . \Form::close() . '
                            </ul>
                        </div>';
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

    public function store(Request $request)
    {
        $request->name = preg_replace('/\s+/', ' ', $request->name);
        $this->validate($request, [
            'name' => [
                'max:255',
                Rule::unique('categories')->where(function ($query) {
                    return $query->where('is_active', 1);
                }),
            ],
            'image' => 'image|mimes:jpg,jpeg,png,gif',
        ]);
        $image = $request->image;
        if ($image) {
            $ext = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
            $imageName = date("Ymdhis");
            $imageName = $imageName . '.' . $ext;
            $image->move('public/images/category', $imageName);

            $lims_category_data['image'] = $imageName;
        }
        $lims_category_data['name'] = $request->name;
        $lims_category_data['parent_id'] = $request->parent_id;
        $lims_category_data['is_active'] = true;
        $lims_category_data['codigo_actividad'] = $request->actividad_id;
        $lims_category_data['codigo_producto_servicio'] = $request->codigo_pro_ser;
        Category::create($lims_category_data);
        return redirect('category')->with('message', 'Categoría creado correctamente');
    }

    public function edit($id)
    {
        $lims_category_data = Category::findOrFail($id);
        $lims_parent_data = Category::where('id', $lims_category_data['parent_id'])->first();
        if ($lims_parent_data)
            $lims_category_data['parent'] = $lims_parent_data['name'];
        return $lims_category_data;
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'name' => [
                'max:255',
                Rule::unique('categories')->ignore($request->category_id)->where(function ($query) {
                    return $query->where('is_active', 1);
                }),
            ],
            'image' => 'image|mimes:jpg,jpeg,png,gif',
        ]);

        $input = $request->except('image');
        $image = $request->image;
        if ($image) {
            $ext = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
            $imageName = date("Ymdhis");
            $imageName = $imageName . '.' . $ext;
            $image->move('public/images/category', $imageName);
            $input['image'] = $imageName;
        }
        $lims_category_data = Category::findOrFail($request->category_id);
        $lims_category_data['codigo_actividad'] = $request->actividad_id;
        $lims_category_data['codigo_producto_servicio'] = $request->codigo_pro_ser;
        $lims_category_data->update($input);
        return redirect('category')->with('message', 'Categoría actualizada con éxito');
    }

    public function import(Request $request)
    {
        //get file
        $upload = $request->file('file');
        $ext = pathinfo($upload->getClientOriginalName(), PATHINFO_EXTENSION);
        if ($ext != 'csv')
            return redirect()->back()->with('not_permitted', 'Por favor usar archivo CSV');
        $filename = $upload->getClientOriginalName();
        $filePath = $upload->getRealPath();
        //open and read
        $file = fopen($filePath, 'r');
        $header = fgetcsv($file);
        $escapedHeader = [];
        //validate
        foreach ($header as $key => $value) {
            $lheader = strtolower($value);
            $escapedItem = preg_replace('/[^a-z]/', '', $lheader);
            array_push($escapedHeader, $escapedItem);
        }
        //looping through othe columns
        while ($columns = fgetcsv($file)) {
            if ($columns[0] == "")
                continue;
            foreach ($columns as $key => $value) {
                $value = preg_replace('/\D/', '', $value);
            }
            $data = array_combine($escapedHeader, $columns);
            $category = Category::firstOrNew(['name' => $data['name'], 'is_active' => true]);
            if ($data['parentcategory']) {
                $parent_category = Category::firstOrNew(['name' => $data['parentcategory'], 'is_active' => true]);
                $parent_id = $parent_category->id;
            } else
                $parent_id = null;

            $category->parent_id = $parent_id;
            $category->is_active = true;
            $category->save();
        }
        return redirect('category')->with('message', 'Categoria Importado correctamente');
    }

    public function deleteBySelection(Request $request)
    {
        $category_id = $request['categoryIdArray'];
        foreach ($category_id as $id) {
            $lims_product_data = Product::where([['category_id', $id], ['is_active', true]])->get();
            foreach ($lims_product_data as $product_data) {
                $product_data->is_active = false;
                $product_data->save();
            }
            $lims_category_data = Category::findOrFail($id);
            if ($lims_category_data->image)
                try {
                    unlink('public/images/category/' . $lims_category_data->image);
                } catch (Exception $e) {
                    Log::error("error eliminando archivo: " . $e->getMessage());
                }
            $lims_category_data->is_active = false;
            $lims_category_data->save();
        }
        return 'Categorias Eliminadas, Productos relacionados fueron inactivados!';
    }

    public function destroy($id)
    {
        $lims_category_data = Category::findOrFail($id);
        $lims_product_data = Product::where([['category_id', $id], ['is_active', true]])->get();
        foreach ($lims_product_data as $product_data) {
            $product_data->is_active = false;
            $product_data->save();
        }
        if ($lims_category_data->image)
            try {
                unlink('public/images/category/' . $lims_category_data->image);
            } catch (Exception $e) {
                Log::error("error eliminando archivo: " . $e->getMessage());
            }
        $lims_category_data->is_active = false;
        $lims_category_data->save();
        return redirect('category')->with('not_permitted', 'Categoria Eliminada, Productos relacionados fueron inactivados');
    }

    public function getProductosServicios($estatus)
    {
        return $data = SiatProductoServicio::where('codigo_actividad', $estatus)->orderBy('descripcion_producto')->get();
    }
}