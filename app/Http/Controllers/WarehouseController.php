<?php

namespace App\Http\Controllers;

use Keygen;
use App\Warehouse;
use App\SiatSucursal;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WarehouseController extends Controller
{

    public function index()
    {
        $lims_warehouse_all = Warehouse::where('is_active', true)->get();
        $sucursales = SiatSucursal::get();
        return view('warehouse.create', compact('lims_warehouse_all', 'sucursales'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => [
                'max:255',
                Rule::unique('warehouses')->where(function ($query) {
                    return $query->where('is_active', 1);
                }),
            ],
        ]);
        $input = $request->all();
        $input['is_active'] = true;
        if (auth()->check()) {
            $input['company_id'] = auth()->user()->company_id;
        }
        Warehouse::create($input);
        return redirect('warehouse')->with('message', 'Data inserted successfully');
    }

    public function edit($id)
    {
        $lims_warehouse_data = Warehouse::findOrFail($id);
        return $lims_warehouse_data;
    }

    // API: devuelve todos los warehouses en JSON (público)
    public function apiAll(Request $request)
    {
        $warehouses = Warehouse::all();
        return response()->json($warehouses);
    }

    // API: devuelve un warehouse por su id en JSON (público)
    public function apiShow($id)
    {
        $warehouse = Warehouse::find($id);
        if (!$warehouse) {
            return response()->json(['message' => 'Warehouse not found'], 404);
        }
        return response()->json($warehouse);
    }
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => [
                'max:255',
                Rule::unique('warehouses')->ignore($request->warehouse_id)->where(function ($query) {
                    return $query->where('is_active', 1);
                }),
            ],
        ]);
        $input = $request->all();
        $lims_warehouse_data = Warehouse::find($input['warehouse_id']);
        $lims_warehouse_data->update($input);
        return redirect('warehouse')->with('message', 'Data updated successfully');
    }

    public function importWarehouse(Request $request)
    {
        //get file
        $upload = $request->file('file');
        $ext = pathinfo($upload->getClientOriginalName(), PATHINFO_EXTENSION);
        if ($ext != 'csv')
            return redirect()->back()->with('not_permitted', 'Please upload a CSV file');
        $filename = $upload->getClientOriginalName();
        $upload = $request->file('file');
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

            $warehouse = Warehouse::firstOrNew(['name' => $data['name'], 'is_active' => true]);
            $warehouse->name = $data['name'];
            $warehouse->phone = $data['phone'];
            $warehouse->email = $data['email'];
            $warehouse->address = $data['address'];
            $warehouse->is_active = true;
            if (auth()->check()) {
                $warehouse->company_id = auth()->user()->company_id;
            }
            $warehouse->save();
        }
        return redirect('warehouse')->with('message', 'Warehouse imported successfully');
    }

    public function deleteBySelection(Request $request)
    {
        $warehouse_id = $request['warehouseIdArray'];
        foreach ($warehouse_id as $id) {
            $lims_warehouse_data = Warehouse::find($id);
            $lims_warehouse_data->is_active = false;
            $lims_warehouse_data->save();
        }
        return 'Warehouse deleted successfully!';
    }

    public function destroy($id)
    {
        $lims_warehouse_data = Warehouse::find($id);
        $lims_warehouse_data->is_active = false;
        $lims_warehouse_data->save();
        return redirect('warehouse')->with('not_permitted', 'Data deleted successfully');
    }
}
