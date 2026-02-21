<?php

namespace App\Http\Controllers;

use App\Biller_Warehouses;
use App\PosSetting;
use Auth;
use App\Biller;
use App\Account;
use App\Customer;
use App\Warehouse;
use App\SiatSucursal;
use App\SiatPuntoVenta;
use Illuminate\Http\Request;
use App\Mail\UserNotification;
use Illuminate\Validation\Rule;
use Log;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Mail;
use Intervention\Image\Facades\Image;
use Spatie\Permission\Models\Permission;

class BillerController extends Controller
{
    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('billers-index')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if (empty($all_permission))
                $all_permission[] = 'dummy text';
            $lims_biller_all = biller::where('is_active', true)->get();
            return view('biller.index', compact('lims_biller_all', 'all_permission'));
        } else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function create()
    {
        $role = Role::find(Auth::user()->role_id);
        $lims_account_list = Account::where('is_active', true)->get();
        $lims_customer_list = Customer::where('is_active', true)->get();
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $sucursales = SiatSucursal::where('estado', true)->get();
        if ($role->hasPermissionTo('billers-add'))
            return view('biller.create', compact('lims_account_list', 'lims_customer_list', 'lims_warehouse_list', 'sucursales'));
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            /*'company_name' => [
                'max:255',
                Rule::unique('billers')->where(function ($query) {
                    return $query->where('is_active', 1);
                }),
            ],*/
            /*'email' => [
                'email',
                'max:255',
                Rule::unique('billers')->where(function ($query) {
                    return $query->where('is_active', 1);
                }),
            ],*/
            'image' => 'image|mimes:jpg,jpeg,png,gif|max:10000',
        ]);

        try {
            $lims_biller_data = $request->except('image');
            $lims_biller_data['is_active'] = true;
            $image = $request->image;
            if ($image) {
                $ext = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
                $imageName = preg_replace('/[^a-zA-Z0-9]/', '', $request['company_name']);
                /*Image::make($image)
                ->resize(250, null, function ($constraints) {
                $constraints->aspectRatio();
                })->save('public/images/biller/' . $imageName.'-resize.'.$ext);*/
                $imageName = $imageName . '.' . $ext;
                $image->move('public/images/biller', $imageName);

                $lims_biller_data['image'] = $imageName;
            }
            $biller = Biller::create($lims_biller_data);
            if (array_key_exists('warehouses', $lims_biller_data)) {
                $list_warehouses = $lims_biller_data['warehouses'];
                Biller_Warehouses::where("biller_id", $biller->id)->delete();
                foreach ($list_warehouses as $warehouse) {
                    $data_m['biller_id'] = $biller->id;
                    $data_m['warehouse_id'] = $warehouse;
                    Biller_Warehouses::create($data_m);
                }
            }
            $message = 'Dato Ingresado con éxito.';
        } catch (\Exception $e) {
            Log::error("Error: " . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Error: ' . $e->getMessage());
        }
        try {
            Mail::send('mail.biller_create', $lims_biller_data, function ($message) use ($lims_biller_data) {
                $message->to($lims_biller_data['email'])->subject('New Biller');
            });
        } catch (\Exception $e) {
            $message = 'Dato Ingresado con éxito. Por favor ir a Ajustes <a href="setting/mail_setting">configuracion email</a> a enviar email.';
        }
        return redirect('biller')->with('message', $message);
    }

    public function edit($id)
    {
        $role = Role::find(Auth::user()->role_id);
        $lims_account_list = Account::where('is_active', true)->get();
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_warehouse_selects = Biller_Warehouses::select('warehouse_id')->where("biller_id", $id)->get();
        $lims_biller_data = Biller::where('id', $id)->first();
        $sucursales = SiatSucursal::where('estado', true)->get();
        $p_ventas = SiatPuntoVenta::where('sucursal', $lims_biller_data->sucursal)->get();
        $pos_setting = PosSetting::latest()->first() ?? new PosSetting();
        if ($lims_biller_data->sucursal && $pos_setting->customer_sucursal) {
            $lims_customer_list = Customer::where([['is_active', true], ['sucursal_id', $lims_biller_data->sucursal]])->get();
        }else{
            $lims_customer_list = Customer::where('is_active', true)->get();
        }

        if (sizeof($lims_customer_list) == 0) {
            $lims_customer_list = Customer::where('is_active', true)->get();
        }
        if ($role->hasPermissionTo('billers-edit')) {
            return view('biller.edit', compact('lims_biller_data', 'lims_account_list', 'lims_customer_list', 'lims_warehouse_list', 'sucursales', 'p_ventas', 'lims_warehouse_selects'));
        } else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            /*'company_name' => [
                'max:255',
                Rule::unique('billers')->ignore($id)->where(function ($query) {
                    return $query->where('is_active', 1);
                }),
            ],
            'email' => [
                'email',
                'max:255',
                Rule::unique('billers')->ignore($id)->where(function ($query) {
                    return $query->where('is_active', 1);
                }),
            ],*/

            'image' => 'image|mimes:jpg,jpeg,png,gif|max:100000',
        ]);
        try {
            $input = $request->except('image');
            $image = $request->image;
            if ($image) {
                $ext = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
                $imageName = preg_replace('/[^a-zA-Z0-9]/', '', $request['company_name']);
                $imageName = $imageName . '.' . $ext;
                $image->move('public/images/biller', $imageName);
                $input['image'] = $imageName;
            }

            $lims_biller_data = Biller::findOrFail($id);
            $lims_biller_data->update($input);
            if (array_key_exists('warehouses', $input)) {
                $list_warehouses = $input['warehouses'];
                Biller_Warehouses::where("biller_id", $lims_biller_data->id)->delete();
                foreach ($list_warehouses as $warehouse) {
                    $data_m['biller_id'] = $lims_biller_data->id;
                    $data_m['warehouse_id'] = $warehouse;
                    Biller_Warehouses::create($data_m);
                }
            }else{
                Biller_Warehouses::where("biller_id", $lims_biller_data->id)->delete();
            }
        } catch (\Exception $e) {
            Log::error("Error: " . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Error: ' . $e->getMessage());
        }
        return redirect('biller')->with('message', 'Dato Actualizado con éxito.');
    }

    public function importBiller(Request $request)
    {
        $upload = $request->file('file');
        $ext = pathinfo($upload->getClientOriginalName(), PATHINFO_EXTENSION);
        if ($ext != 'csv')
            return redirect()->back()->with('not_permitted', 'Please upload a CSV file');
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

            $biller = Biller::firstOrNew(['company_name' => $data['companyname']]);
            $biller->name = $data['name'];
            $biller->image = $data['image'];
            $biller->vat_number = $data['vatnumber'];
            $biller->email = $data['email'];
            $biller->phone_number = $data['phonenumber'];
            $biller->address = $data['address'];
            $biller->city = $data['city'];
            $biller->state = $data['state'];
            $biller->postal_code = $data['postalcode'];
            $biller->country = $data['country'];
            $biller->is_active = true;
            $biller->save();
            $message = 'Facturadores imporados con éxito, completar los datos de cada uno';
            if ($data['email']) {
                try {
                    Mail::send('mail.biller_create', $data, function ($message) use ($data) {
                        $message->to($data['email'])->subject('New Biller');
                    });
                } catch (\Exception $e) {
                    $message = 'Facturadores imporados con éxito, completar los datos de cada uno. Por favor configurar <a href="setting/mail_setting">ajuste Email</a> para enviar email.';
                }
            }
        }
        return redirect('biller')->with('message', $message);

    }

    public function deleteBySelection(Request $request)
    {
        $biller_id = $request['billerIdArray'];
        foreach ($biller_id as $id) {
            $lims_biller_data = Biller::find($id);
            $lims_biller_data->is_active = false;
            $lims_biller_data->save();
        }
        return 'Facturador eliminado con éxito!';
    }

    public function destroy($id)
    {
        $lims_biller_data = Biller::find($id);
        $lims_biller_data->is_active = false;
        $lims_biller_data->save();
        return redirect('biller')->with('not_permitted', 'Data deleted successfully');
    }
 
    /** test with static method before update */
    public static function warehouseAuthorizate(int $id)
    {
        $lims_warehouse_selects = array();
        if ($id) {
            $lims_biller_data = Biller::select('id', 'warehouse_id')->find($id);
            if ($lims_biller_data) {
                $warehouse_current_biller = Warehouse::find($lims_biller_data->warehouse_id);
                $lims_warehouse_filter = Biller_Warehouses::where('biller_id', $lims_biller_data->id)->get();
                $lims_warehouse_selects[] = $warehouse_current_biller;
                foreach ($lims_warehouse_filter as $warehouse_select) {
                    if ($warehouse_current_biller->id != $warehouse_select->warehouse_id)
                        $lims_warehouse_selects[] = $warehouse_select->warehouse;
                }
            }
        }
        return $lims_warehouse_selects;
    }
}