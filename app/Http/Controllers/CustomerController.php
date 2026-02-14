<?php

namespace App\Http\Controllers;

use App\Biller;
use App\Customer;
use App\CustomerCompany;
use App\CustomerGroup;
use App\Deposit;
use App\Http\Traits\SiatTrait;
use App\PosSetting;
use App\SiatParametricaVario;
use App\SiatSucursal;
use App\User;
use App\CustomerNit;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Log;
use Spatie\Permission\Models\Role;
use App\Services\WhatsAppService;

class CustomerController extends Controller
{
    use SiatTrait;

    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('customers-index')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if (empty($all_permission))
                $all_permission[] = 'dummy text';
            return view('customer.index', compact('all_permission'));
        } else
            return redirect()->back()->with('not_permitted', '¡Lo siento! No tienes permiso para acceder a este módulo.');
    }

    public function listData(Request $request)
    {
        $columns = array(
            2 => 'name',
            3 => 'tipo_documento',
            4 => 'valor_documento',
        );

        $totalData = Customer::where('is_active', true)->count();


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
            $customers = Customer::where('is_active', true)->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();

        } else {
            $search = $request->input('search.value');
            $customers = Customer::where('is_active', true)
                ->whereDate('created_at', '=', date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                ->orwhere('valor_documento', 'LIKE', "%{$search}%")
                ->orwhere('name', 'LIKE', "%{$search}%")
                ->orwhere('codigofijo', 'LIKE', "%{$search}%")
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();

            $totalFiltered = Customer::where('is_active', true)
                ->whereDate('created_at', '=', date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                ->orwhere('valor_documento', 'LIKE', "%{$search}%")
                ->orwhere('name', 'LIKE', "%{$search}%")
                ->orwhere('codigofijo', 'LIKE', "%{$search}%")
                ->count();
        }

        $data = array();
        if (!empty($customers)) {
            foreach ($customers as $key => $customer) {
                $nestedData['id'] = $customer->id;
                $nestedData['key'] = $key;
                $nestedData['name'] = $customer->name;
                $customer_group = CustomerGroup::where('id', $customer->customer_group_id)->first();
                $lims_customer_company = CustomerCompany::where([['is_active', true], ['customer_id', $customer->id]])->first();
                if ($customer_group) {
                    $nestedData['customer_group'] = $customer_group->name;
                } else {
                    $nestedData['customer_group'] = "Sin Grupo";
                }
                if ($customer->tipo_documento) {
                    $nestedData['tipo_documento'] = $customer->getDescripcionTipoDocumento();
                } else {
                    $nestedData['tipo_documento'] = "Sin Definir";
                }
                $nestedData['valor_documento'] = $customer->valor_documento;
                $nestedData['email'] = $customer->email;
                $nestedData['phone_number'] = $customer->phone_number;
                $nestedData['address'] = $customer->address . ", " . $customer->city;
                if ($customer->country) {
                    $nestedData['address'] .= " , " . $customer->country;
                }
                $nestedData['balance'] = number_format($customer->deposit - $customer->expense, 2);
                $nestedData['date'] = date(config('date_format'), strtotime($customer->created_at->toDateString()));

                $nestedData['options'] = '<div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . trans("file.action") . '
                              <span class="caret"></span>
                              <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">';
                if (in_array("customers-edit", $request['all_permission'])) {
                    $nestedData['options'] .= '<li>
                        <a href="' . route('customer.edit', $customer->id) . '" class="btn btn-link"><i class="dripicons-document-edit"></i> ' . trans('file.edit') . '</a>
                        </li>';
                }
                if ($lims_customer_company && $lims_customer_company->lat != null && $lims_customer_company->lon != null) {
                    $nestedData['options'] .=
                        '<li>
                        <button type="button" onclick="showMap(' . $lims_customer_company->lat . ', ' . $lims_customer_company->lon . ')" class="btn btn-link" data-id = "' . $customer->id . '" data-toggle="modal"  data-target="#showMapModal"><i class="fa fa-map-marker"></i> Ubicacion</button>
                    </li>';
                }
                $nestedData['options'] .=
                    '<li>
                        <button type="button" onclick="deposit(' . $customer->id . ')" class="deposit btn btn-link" data-id = "' . $customer->id . '" data-toggle="modal"  data-target="#depositModal"><i class="fa fa-plus"></i> ' . trans('file.Add Deposit') . '</button>
                    </li>
                    <li>
                        <button type="button" onclick="getDeposit(' . $customer->id . ')" class="getDeposit btn btn-link" data-id = "' . $customer->id . '"><i class="fa fa-money"></i> ' . trans('file.View Deposit') . '</button>
                    </li>';
                if (in_array("customers-delete", $request['all_permission'])) {
                    $nestedData['options'] .= \Form::open(["route" => ["customer.destroy", $customer->id], "method" => "DELETE"]) . '
                            <li>
                              <button type="submit" class="btn btn-link" onclick="return confirmDelete()"><i class="dripicons-trash"></i> ' . trans("file.delete") . '</button>
                            </li>' . \Form::close() . '
                        </ul>
                    </div>';
                }
                if ($customer->is_active)
                    $data[] = $nestedData;
            }
            $totalFiltered = sizeof($data);
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
        if ($role->hasPermissionTo('customers-add')) {
            $lims_customer_group_all = CustomerGroup::where('is_active', true)->get();
            $lista_documentos = SiatParametricaVario::where('tipo_clasificador', 'tipoDocumentoIdentidad')->get();
            $lims_sucursal_all = SiatSucursal::where('estado', 1)->get();
            return view('customer.create', compact('lims_customer_group_all', 'lista_documentos', 'lims_sucursal_all'));
        } else
            return redirect()->back()->with('not_permitted', '¡Lo siento! No tienes permiso para acceder a este módulo.');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => [
                'max:255',
                Rule::unique('customers')->where(function ($query) {
                    return $query->where('is_active', 1);
                }),
            ],
        ]);
        $lims_customer_data = $request->all();
        try {
            if (!isset($lims_customer_data['is_credit']))
                $lims_customer_data['is_credit'] = false;
            else
                $lims_customer_data['is_credit'] = true;
            if (!isset($lims_customer_data['is_tasadignidad'])) {
                $lims_customer_data['is_tasadignidad'] = false;
                $lims_customer_data['porcentaje_tasadignidad'] = 0;
            } else {
                $lims_customer_data['is_tasadignidad'] = true;
            }
            if (!isset($lims_customer_data['is_ley1886'])) {
                $lims_customer_data['is_ley1886'] = false;
                $lims_customer_data['porcentaje_ley1886'] = 0;
            } else {
                $lims_customer_data['is_ley1886'] = true;
            }
            $lims_customer_data['is_active'] = true;
            $message = 'Cliente creado con éxito';
            if ($lims_customer_data['email']) {
                try {
                    Mail::send('mail.customer_create', $lims_customer_data, function ($message) use ($lims_customer_data) {
                        $message->to($lims_customer_data['email'])->subject('New Customer');
                    });
                } catch (\Exception $e) {
                    $message = 'Cliente creado con éxito. Please setup your <a href="setting/mail_setting">mail setting</a> to send mail.';
                }
            }
            $customer = Customer::create($lims_customer_data);
            if ($customer && !$lims_customer_data['pos'] && isset($input['fullname']) != null) {
                $lims_customer_data['address'] = $lims_customer_data['address_company'];
                $lims_customer_data['customer_id'] = $customer->id;
                CustomerCompany::create($lims_customer_data);
            }
            if ($lims_customer_data['pos'])
                return array('status' => true, 'message' => $message, 'customer' => $customer);
            else
                return redirect('customer')->with('create_message', $message);
        } catch (\Exception $e) {
            return array('status' => false, 'message' => $e->getMessage());
        }

    }

    public function edit($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('customers-edit')) {
            $lims_customer_data = Customer::find($id);
            $lims_customer_company = CustomerCompany::where([['is_active', true], ['customer_id', $id]])->first();
            $lims_customer_group_all = CustomerGroup::where('is_active', true)->get();
            $lista_documentos = SiatParametricaVario::where('tipo_clasificador', 'tipoDocumentoIdentidad')->get();
            $lims_sucursal_all = SiatSucursal::where('estado', 1)->get();
            return view('customer.edit', compact('lims_customer_data', 'lims_customer_group_all', 'lista_documentos', 'lims_sucursal_all', 'lims_customer_company'));
        } else
            return redirect()->back()->with('not_permitted', '¡Lo siento! No tienes permiso para acceder a este módulo.');
    }

    public function update(Request $request, $id)
    {

        $input = $request->all();
        if (!isset($input['is_credit']))
            $input['is_credit'] = false;
        else
            $input['is_credit'] = true;
        if (!isset($input['is_tasadignidad'])) {
            $input['is_tasadignidad'] = false;
            $input['porcentaje_tasadignidad'] = 0;
        } else {
            $input['is_tasadignidad'] = true;
        }
        if (!isset($input['is_ley1886'])) {
            $input['is_ley1886'] = false;
            $input['porcentaje_ley1886'] = 0;
        } else {
            $input['is_ley1886'] = true;
        }
        $lims_customer_data = Customer::find($id);
        $lims_customer_data->update($input);
        if ($lims_customer_data && $input['fullname'] != null) {
            $input['address'] = $input['address_company'];
            $input['customer_id'] = $lims_customer_data->id;
            $lims_customer_company = CustomerCompany::where([['is_active', true], ['customer_id', $id]])->first();
            if ($lims_customer_company) {
                $lims_customer_company->update($input);
            } else {
                CustomerCompany::create($input);
            }
        }
        $nit_data = CustomerNit::where([
            'tipo_documento' => $lims_customer_data->tipo_documento,
            'valor_documento' => $lims_customer_data->valor_documento
        ])->first();
        if ($nit_data != null) {
            $nit_data->email = $lims_customer_data->email;
            $nit_data->save();
        }
        return redirect('customer')->with('edit_message', 'Datos actualizados con éxito');
    }

    public function importCustomer(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('customers-add')) {
            $upload = $request->file('file');
            $ext = pathinfo($upload->getClientOriginalName(), PATHINFO_EXTENSION);
            if ($ext != 'csv')
                return redirect()->back()->with('not_permitted', 'Por favor cargue el archivo CSV');
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
                $lims_customer_group_data = CustomerGroup::where('name', $data['customergroup'])->first();
                $customer = Customer::firstOrNew(['name' => $data['name']]);
                $customer->customer_group_id = $lims_customer_group_data->id;
                $customer->name = $data['name'];
                $customer->company_name = $data['companyname'];
                $customer->email = $data['email'];
                $customer->phone_number = $data['phonenumber'];
                $customer->address = $data['address'];
                $customer->city = $data['city'];
                $customer->state = $data['state'];
                $customer->postal_code = $data['postalcode'];
                $customer->country = $data['country'];
                $customer->is_active = true;
                $customer->save();
                $message = 'Cliente(s) importados con éxito';
                if ($data['email']) {
                    try {
                        Mail::send('mail.customer_create', $data, function ($message) use ($data) {
                            $message->to($data['email'])->subject('New Customer');
                        });
                    } catch (\Exception $e) {
                        $message = 'Cliente(s) importados con éxito. Please setup your <a href="setting/mail_setting">mail setting</a> to send mail.';
                    }
                }
            }
            return redirect('customer')->with('import_message', $message);
        } else
            return redirect()->back()->with('not_permitted', '¡Lo siento! No tienes permiso para acceder a este módulo.');
    }

    public function getDeposit($id)
    {
        $lims_deposit_list = Deposit::where('customer_id', $id)->get();
        $deposit_id = [];
        $deposits = [];
        foreach ($lims_deposit_list as $deposit) {
            $deposit_id[] = $deposit->id;
            $date[] = $deposit->created_at->toDateString() . ' ' . $deposit->created_at->toTimeString();
            $amount[] = $deposit->amount;
            $note[] = $deposit->note;
            $lims_user_data = User::find($deposit->user_id);
            $name[] = $lims_user_data->name;
            $email[] = $lims_user_data->email;
        }
        if (!empty($deposit_id)) {
            $deposits[] = $deposit_id;
            $deposits[] = $date;
            $deposits[] = $amount;
            $deposits[] = $note;
            $deposits[] = $name;
            $deposits[] = $email;
        }
        return $deposits;
    }

    public function addDeposit(Request $request)
    {
        $data = $request->all();
        $data['user_id'] = Auth::id();
        $lims_customer_data = Customer::find($data['customer_id']);
        $lims_customer_data->deposit += $data['amount'];
        $lims_customer_data->save();
        Deposit::create($data);
        $message = 'Datos registrados con éxito';
        if ($lims_customer_data->email) {
            $data['name'] = $lims_customer_data->name;
            $data['email'] = $lims_customer_data->email;
            $data['balance'] = $lims_customer_data->deposit - $lims_customer_data->expense;
            try {
                Mail::send('mail.customer_deposit', $data, function ($message) use ($data) {
                    $message->to($data['email'])->subject('Recharge Info');
                });
            } catch (\Exception $e) {
                $message = 'Datos registrados con éxito. Please setup your <a href="setting/mail_setting">mail setting</a> to send mail.';
            }
        }
        return redirect('customer')->with('create_message', $message);
    }

    public function updateDeposit(Request $request)
    {
        $data = $request->all();
        $lims_deposit_data = Deposit::find($data['deposit_id']);
        $lims_customer_data = Customer::find($lims_deposit_data->customer_id);
        $amount_dif = $data['amount'] - $lims_deposit_data->amount;
        $lims_customer_data->deposit += $amount_dif;
        $lims_customer_data->save();
        $lims_deposit_data->update($data);
        return redirect('customer')->with('create_message', 'Datos actualizados con éxito');
    }

    public function deleteDeposit(Request $request)
    {
        $data = $request->all();
        $lims_deposit_data = Deposit::find($data['id']);
        $lims_customer_data = Customer::find($lims_deposit_data->customer_id);
        $lims_customer_data->deposit -= $lims_deposit_data->amount;
        $lims_customer_data->save();
        $lims_deposit_data->delete();
        return redirect('customer')->with('not_permitted', 'Dato eliminado con éxito');
    }

    public function deleteBySelection(Request $request)
    {
        $customer_id = $request['customerIdArray'];
        foreach ($customer_id as $id) {
            $lims_customer_data = Customer::find($id);
            if ($lims_customer_data) {
                $lims_customer_data->is_active = false;
                $lims_customer_data->save();
                $data_additional = CustomerCompany::where([['is_active', true], ['customer_id', $lims_customer_data->id]])->first();
                if ($data_additional) {
                    $data_additional->is_active = false;
                    $data_additional->save();
                }
            }
        }
        return 'Cliente(s) eliminado con éxito!';
    }

    public function destroy($id)
    {
        $lims_customer_data = Customer::find($id);
        if ($lims_customer_data) {
            $lims_customer_data->is_active = false;
            $lims_customer_data->save();
            $data_additional = CustomerCompany::where([['is_active', true], ['customer_id', $id]])->first();
            if ($data_additional) {
                $data_additional->is_active = false;
                $data_additional->save();
            }
            return redirect('customer')->with('not_permitted', 'Dato eliminado con éxito');
        } else {
            return redirect('customer')->with('not_permitted', 'Error al eliminar, cliente no encontrado');

        }
    }

    public function verificarNIT($nit)
    {
        $response = $this->getResponseNIT($nit);
        //obteniendo la respuesta
        if ($response) {
            $mensajes = $response['MENSAJES'];
            foreach ($mensajes as $key => $value) {
                return $value;
            }
        } else {
            return array('status' => false, 'message' => "Sin respuesta del servicio");
        }
    }

    public function importarClienteDetallado(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('customers-add')) {
            $this->validate($request, [
                'file' => 'required|file|mimes:xls,xlsx,csv'
            ]);
            $upload = $request->file('file');
            $dataRequest = $request->except('file');
            $ext = pathinfo($upload->getClientOriginalName(), PATHINFO_EXTENSION);
            //checking if this is a CSV file
            if ('csv' == $ext) {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
            } else {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            }

            $filePath = $upload->getRealPath();
            $spreadsheet = $reader->load($filePath);
            $sheet_data = $spreadsheet->getActiveSheet()->toArray();
            $escapedHeader = $sheet_data[0];
            try {
                foreach ($sheet_data as $key => $val) {
                    if ($key != 0 && $key > 0 && $val[0] != null) {
                        $data = array_combine($escapedHeader, $val);

                        $lims_customer_group_data = CustomerGroup::where('name', $data['grupocliente'])->first();
                        if ($lims_customer_group_data == null) {
                            $lims_customer_group_data = new CustomerGroup();
                            $lims_customer_group_data->name = $data['grupocliente'];
                            $lims_customer_group_data->percentage = 0;
                            $lims_customer_group_data->is_active = true;
                            $lims_customer_group_data->save();
                        }
                        $customer = Customer::where([['is_active', true], ['valor_documento', $data['valordocumento']], ['tipo_documento', $data['tipodocumento']], ['name', $data['nombrecompleto']]])->first();
                        if ($customer == null) {
                            $customer = new Customer();
                        }
                        if ($data['edad'] != null || $data['edad'] >= 0) {
                            $año = date('Y') - $data['edad'];
                            $customer->date_birh = $año . '-01-01';
                        }
                        if ($data['tipoprecio'] != null) {
                            switch ($data['tipoprecio']) {
                                case 'A':
                                    $customer->price_type = 1;
                                    break;
                                case 'B':
                                    $customer->price_type = 2;
                                    break;
                                case 'C':
                                    $customer->price_type = 3;
                                    break;
                                default:
                                    $customer->price_type = 0;
                                    break;
                            }
                        }
                        $customer->customer_group_id = $lims_customer_group_data->id;
                        $customer->tipo_documento = $data['tipodocumento'];
                        $customer->valor_documento = $data['valordocumento'];
                        $customer->complemento_documento = $data['complemento'];
                        if ($data['razonsocial'] != null) {
                            $customer->razon_social = $data['razonsocial'];
                        } else {
                            $customer->razon_social = $data['nombrecompleto'];
                        }
                        $customer->name = $data['nombrecompleto'];
                        $customer->email = $data['correoelectronico'];
                        $customer->phone_number = $data['numerotelefono'];
                        $customer->address = $data['direccion'];
                        $customer->city = $data['ciudad'];
                        $customer->country = $data['pais'];
                        $customer->is_credit = false;
                        $customer->is_tasadignidad = false;
                        $customer->is_ley1886 = false;
                        $customer->is_active = true;
                        $customer->save();
                        if ($customer->id && $data['empresa'] != null && $data['nombrecontacto'] != null) {
                            $customer_company = CustomerCompany::firstOrNew(['customer_id' => $customer->id], ['is_active', true]);
                            $customer_company->fullname = $data['nombrecontacto'];
                            $customer_company->company_name = $data['empresa'];
                            $customer_company->address = $data['direccionempresa'];
                            $customer_company->phone = $data['latitud'];
                            $customer_company->url_custom = $data['sitioweb'];
                            $customer_company->lat = $data['latitud'];
                            $customer_company->lon = $data['longitud'];
                            $customer_company->save();
                        }
                        $message = 'Cliente(s) importados con éxito';
                    }
                }
            } catch (\Throwable $th) {
                Log::error("Error Importacion Masiva Cliente Detallado => " . $th);
                $error_message = "Error: " . $th->getMessage();
                return redirect('customer')->with('not_permitted', "Error al Importar Clientes:  " . $error_message);
            }
            return redirect('customer')->with('import_message', $message);
        } else
            return redirect()->back()->with('not_permitted', '¡Lo siento! No tienes permiso para acceder a este módulo.');
    }

    public function searchCustomer(Request $request)
    {
        $data = $request->all();
        $pos_setting = PosSetting::firstOrNew(['id' => 1]);
        if (Auth::user()->role_id > 2 && $pos_setting->customer_sucursal) {
            $biller_data = Biller::select('sucursal')->find(Auth::user()->biller_id);
            $list_customers = Customer::select("id", "name", "valor_documento", "codigofijo", "nro_medidor")
                ->where([
                    ['name', 'LIKE', "%{$data['term']}%"],
                    ['sucursal_id', $biller_data->sucursal],
                    ['is_active', true]
                ])->orWhere([
                        ['valor_documento', 'LIKE', "%{$data['term']}%"],
                        ['sucursal_id', $biller_data->sucursal],
                        ['is_active', true]
                    ])->orWhere([
                        ['codigofijo', 'LIKE', "%{$data['term']}%"],
                        ['sucursal_id', $biller_data->sucursal],
                        ['is_active', true]
                    ])->orWhere([
                        ['nro_medidor', 'LIKE', "%{$data['term']}%"],
                        ['sucursal_id', $biller_data->sucursal],
                        ['is_active', true]
                    ])->limit(30)->get();
        } else {
            $list_customers = Customer::select("id", "name", "valor_documento", "codigofijo", "nro_medidor")
                ->where([
                    ['name', 'LIKE', "%{$data['term']}%"],
                    ['is_active', true]
                ])->orWhere([
                        ['valor_documento', 'LIKE', "%{$data['term']}%"],
                        ['is_active', true]
                    ])->orWhere([
                        ['codigofijo', 'LIKE', "%{$data['term']}%"],
                        ['is_active', true]
                    ])->orWhere([
                        ['nro_medidor', 'LIKE', "%{$data['term']}%"],
                        ['is_active', true]
                    ])->limit(30)->get();
        }

        return $list_customers;
    }

    private function findCustomerByPhone($phone)
    {
        if (!$phone)
            return null;
        $clean = preg_replace('/\D/', '', $phone);

        return Customer::where('is_active', true)
            ->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone_number,'+',''),' ',''),'-',''),'(',''),')','') = ?", [$clean])
            ->first();
    }

    // API: Obtener datos de un cliente por su número de teléfono (público)
    public function apiByPhone(Request $request)
    {
        $phone = $request->query('phone');
        if (!$phone) {
            return response()->json(['message' => 'Phone parameter is required'], 400);
        }
        $code = $request->query('code') ?? $request->query('otp');
        if (!$code) {
            return response()->json(['message' => 'code parameter is required to verify OTP'], 400);
        }

        $clean = preg_replace('/\D/', '', $phone);
        $cacheKey = 'otp:' . $clean;
        $cached = Cache::get($cacheKey);

        if (!$cached) {
            return response()->json(['message' => 'OTP expired or not found'], 404);
        }

        if ((string) $cached !== (string) $code) {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        Cache::forget($cacheKey);
        $customer = $this->findCustomerByPhone($phone);
        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        return response()->json($customer, 200);
    }

    // API: Registrar cliente vía GET (público)
    public function apiRegister(Request $request)
    {
        $name = $request->query('name');
        $phone = $request->query('phone') ?? $request->query('phone_number');

        if (!$name || !$phone) {
            return response()->json(['message' => 'name and phone parameters are required'], 400);
        }

        $existing = $this->findCustomerByPhone($phone);
        if ($existing) {
            return response()->json(['message' => 'Customer already exists', 'customer' => $existing], 409);
        }

        $customerData = [
            'name' => $name,
            'phone_number' => $phone,
            'email' => $request->query('email'),
            'address' => $request->query('address'),
            'city' => $request->query('city'),
            'country' => $request->query('country'),
            'is_active' => true
        ];

        // Eliminar valores nulos para evitar asignación de columnas vacías
        $payload = array_filter($customerData, function ($v) {
            return $v !== null && $v !== '';
        });

        try {
            $customer = Customer::create($payload);
            return response()->json(['message' => 'Customer created', 'customer' => $customer], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating customer', 'error' => $e->getMessage()], 500);
        }
    }

    // API: Enviar OTP vía WhatsApp (GET público)
    public function apiSendOtp(Request $request)
    {
        $phone = $request->query('phone') ?? $request->query('phone_number');

        $purpose = $request->query('purpose') ?? $request->query('for') ?? 'login';


        if (!$phone) {
            return response()->json(['message' => 'phone parameter is required'], 400);
        }
        $existing = $this->findCustomerByPhone($phone);

        if ($purpose === 'register') {
            if ($existing) {
                return response()->json(['message' => 'Phone already registered'], 409);
            }
        } elseif ($purpose === 'login') {
            if (!$existing) {
                return response()->json(['message' => 'Customer not found'], 404);
            }
        }

        try {
            try {
                $code = random_int(100000, 999999);
            } catch (\Exception $e) {
                $code = mt_rand(100000, 999999);
            }

            $text = "Tu código OTP es: {$code}. No lo compartas con nadie. Válido 5 minutos.";

            $wa = new WhatsAppService();
            $sent = $wa->sendMessage($phone, $text);

            if ($sent) {
                $cleanCache = preg_replace('/\D/', '', $phone);
                Cache::put('otp:' . $cleanCache, $code, 300);
                return response()->json(['message' => 'OTP sent'], 200);
            }

            return response()->json(['message' => 'Failed to send OTP'], 500);
        } catch (\Exception $e) {
            Log::error('Error sending OTP in apiSendOtp: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to send OTP'], 500);
        }
    }

    // API: Verificar OTP (GET público)
    public function apiVerifyOtp(Request $request)
    {
        $phone = $request->query('phone') ?? $request->query('phone_number');
        $code = $request->query('code') ?? $request->query('otp');

        if (!$phone || !$code) {
            return response()->json(['message' => 'phone and code parameters are required'], 400);
        }

        $clean = preg_replace('/\D/', '', $phone);
        $cacheKey = 'otp:' . $clean;
        $cached = Cache::get($cacheKey);

        if (!$cached) {
            return response()->json(['message' => 'OTP expired or not found'], 404);
        }

        if ((string) $cached === (string) $code) {
            Cache::forget($cacheKey);
            return response()->json(['message' => 'OTP valid'], 200);
        }

        return response()->json(['message' => 'Invalid OTP'], 400);
    }
}