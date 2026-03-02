<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Warehouse;
use App\Product;
use App\Product_Warehouse;
use App\Tax;
use App\Unit;
use App\Transfer;
use App\ProductTransfer;
use App\ProductVariant;
use App\TransferRequestLog;
use Auth;
use App\PosSetting;
use DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Log;
// use App\Services\WhatsAppService;

class TransferController extends Controller
{
    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('transfers-index')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if (empty($all_permission))
                $all_permission[] = 'dummy text';

            if (Auth::user()->role_id > 2 && config('staff_access') == 'own')
                $lims_transfer_all = Transfer::with('fromWarehouse', 'toWarehouse', 'user')->orderBy('id', 'desc')->where('user_id', Auth::id())->get();
            else
                $lims_transfer_all = Transfer::with('fromWarehouse', 'toWarehouse', 'user')->orderBy('id', 'desc')->get();
            return view('transfer.index', compact('lims_transfer_all', 'all_permission'));
        } else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function create()
    {
        $user = Auth::user();
        $role = Role::find($user->role_id);

        if (!$role->hasPermissionTo('transfers-add')) {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }

        $all_warehouse_list = Warehouse::where('is_active', true)->get();

        if ($user->role_id <= 2) {
            $user_warehouse_list = $all_warehouse_list;
        } else {
            $user_warehouse_id = optional($user->biller)->warehouse_id;
            $user_warehouse_list = collect();
            if ($user_warehouse_id) {
                $user_warehouse_list = $all_warehouse_list->where('id', $user_warehouse_id);
            }
        }

        return view('transfer.create', compact('all_warehouse_list', 'user_warehouse_list'));
    }


    public function getProduct($id)
    {
        $companyId = Auth::user()->company_id;

        // JOIN directo con products filtrando por compañía — resuelve el N+1 y
        // evita que Product::find() devuelva null por el global scope de compañía.
        $baseQuery = Product_Warehouse::join('products', 'product_warehouse.product_id', '=', 'products.id')
            ->where('product_warehouse.warehouse_id', $id)
            ->where('product_warehouse.qty', '>', 0)
            ->where('products.is_active', true)
            ->where('products.company_id', $companyId)
            ->select(
                'product_warehouse.product_id',
                'product_warehouse.variant_id',
                'product_warehouse.qty',
                'products.name',
                'products.code'
            );

        $lims_product_warehouse_data          = (clone $baseQuery)->whereNull('product_warehouse.variant_id')->get();
        $lims_product_with_variant_warehouse_data = (clone $baseQuery)->whereNotNull('product_warehouse.variant_id')->get();

        $product_code = [];
        $product_name = [];
        $product_qty  = [];

        // Productos sin variante
        foreach ($lims_product_warehouse_data as $product_warehouse) {
            $product_qty[]  = $product_warehouse->qty;
            $product_code[] = $product_warehouse->code;
            $product_name[] = $product_warehouse->name;
        }

        // Productos con variante
        foreach ($lims_product_with_variant_warehouse_data as $product_warehouse) {
            $lims_product_variant_data = ProductVariant::select('item_code')
                ->FindExactProduct($product_warehouse->product_id, $product_warehouse->variant_id)
                ->first();
            if (!$lims_product_variant_data) continue;
            $product_qty[]  = $product_warehouse->qty;
            $product_code[] = $lims_product_variant_data->item_code;
            $product_name[] = $product_warehouse->name;
        }

        return [$product_code, $product_name, $product_qty];
    }

    public function limsProductSearch(Request $request)
    {
        $product_code = explode(" ", $request['data']);
        $product_variant_id = null;
        $lims_product_data = Product::where('code', $product_code[0])->where('is_active', true)->first();
        if (!$lims_product_data) {
            $lims_product_data = Product::join('product_variants', 'products.id', 'product_variants.product_id')
                ->select('products.*', 'product_variants.id as product_variant_id', 'product_variants.item_code')
                ->where('product_variants.item_code', $product_code)
                ->first();
            $product_variant_id = $lims_product_data->product_variant_id;
            $lims_product_data->code = $lims_product_data->item_code;
        }
        $product[] = $lims_product_data->name;
        $product[] = $lims_product_data->code;
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
        $product[] = $product_variant_id;
        return $product;
    }

    // public function store(Request $request, WhatsAppService $whatsAppService)
    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            $role = Role::find($user->role_id);
            $all_permission = $role->permissions->pluck('name')->toArray();

            if (!in_array('transfers-add', $all_permission)) {
                return redirect()->back()->with('not_permitted', 'No tienes permiso para crear transferencias.');
            }

            if ($user->role_id > 2) {
                $userWarehouseId = optional($user->biller)->warehouse_id;
                if ($request->from_warehouse_id != $userWarehouseId) {
                    return redirect()->back()->with('not_permitted', 'No puedes crear transferencias desde otros almacenes.');
                }
            }
            DB::beginTransaction();

            $data = $request->except('document');
            $data['user_id'] = Auth::id();
            $data['reference_no'] = 'tr-' . date("Ymd") . '-' . date("his");

            $posSetting = PosSetting::first();

            if ($posSetting && $posSetting->require_transfer_authorization == 0) {
                $data['status'] = 1;
            } else {
                $data['status'] = 2;
            }

            if ($document = $request->document) {
                $v = Validator::make(
                    ['extension' => strtolower($document->getClientOriginalExtension())],
                    ['extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt']
                );
                if ($v->fails())
                    return redirect()->back()->withErrors($v->errors());

                $documentName = $document->getClientOriginalName();
                $document->move('public/documents/transfer', $documentName);
                $data['document'] = $documentName;
            }

            $lims_transfer_data = Transfer::create($data);

            TransferRequestLog::create([
                'transfer_id' => $lims_transfer_data->id,
                'user_id' => Auth::id(),
                'action' => 'creada',
                'note' => 'Transferencia registrada por el usuario'
            ]);

            $product_id = $data['product_id'];
            $product_code = $data['product_code'];
            $qty = $data['qty'];
            $purchase_unit = $data['purchase_unit'];
            $net_unit_cost = $data['net_unit_cost'];
            $tax_rate = $data['tax_rate'];
            $tax = $data['tax'];
            $total = $data['subtotal'];

            foreach ($product_id as $i => $id) {
                $lims_purchase_unit_data = Unit::where('unit_name', $purchase_unit[$i])->first();

                $quantity = ($lims_purchase_unit_data->operator == '*'
                    ? $qty[$i] * $lims_purchase_unit_data->operation_value
                    : $qty[$i] / $lims_purchase_unit_data->operation_value);

                $lims_product_data = Product::select('is_variant')->find($id);

                if ($lims_product_data->is_variant) {
                    $lims_product_variant_data = ProductVariant::select('variant_id')
                        ->FindExactProductWithCode($id, $product_code[$i])
                        ->first();

                    $lims_product_warehouse_data = Product_Warehouse::FindProductWithVariant(
                        $id,
                        $lims_product_variant_data->variant_id,
                        $data['from_warehouse_id']
                    )->first();

                    $variant_id = $lims_product_variant_data->variant_id;
                } else {
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $id],
                        ['warehouse_id', $data['from_warehouse_id']],
                    ])->first();

                    $variant_id = null;
                }

                if ($lims_product_warehouse_data->qty < $quantity) {
                    throw new \Exception("Stock insuficiente para el producto ID $id en el almacén origen");
                }

                // Actualiza stock segun el estado
                if ($data['status'] == 1) {
                    $lims_product_warehouse_data->qty -= $quantity;
                    $lims_product_data = Product::select('id', 'name', 'code', 'is_variant')->find($id);

                    $productName = $lims_product_data->name;
                    $productCode = $product_code[$i];
                    TransferRequestLog::create([
                        'transfer_id' => $lims_transfer_data->id,
                        'user_id' => Auth::id(),
                        'action' => 'enviado',
                        'note' => "Se descontó $quantity del producto \"$productName\" ($productCode) del almacén origen"
                    ]);
                } elseif ($data['status'] == 2) { // pendiente / bloqueado
                    $lims_product_warehouse_data->qty -= $quantity;
                    $lims_product_warehouse_data->blocked_qty += $quantity;

                    $lims_product_data = Product::select('id', 'name', 'code', 'is_variant')->find($id);
                    $productName = $lims_product_data->name;
                    $productCode = $product_code[$i];

                    TransferRequestLog::create([
                        'transfer_id' => $lims_transfer_data->id,
                        'user_id' => Auth::id(),
                        'action' => 'bloqueado',
                        'note' => "Se bloqueó $quantity del producto \"$productName\" ($productCode) en el almacén origen"
                    ]);
                }

                $lims_product_warehouse_data->save();

                if ($data['status'] == 1) {
                    $lims_product_dest = Product_Warehouse::firstOrNew([
                        'product_id' => $id,
                        'warehouse_id' => $data['to_warehouse_id'],
                        'variant_id' => $variant_id
                    ]);

                    $lims_product_dest->qty += $quantity;
                    $lims_product_dest->save();

                    $lims_product_data = Product::select('id', 'name', 'code', 'is_variant')->find($id);
                    $productName = $lims_product_data->name;
                    $productCode = $product_code[$i];

                    TransferRequestLog::create([
                        'transfer_id' => $lims_transfer_data->id,
                        'user_id' => Auth::id(),
                        'action' => 'recibido',
                        'note' => "Se agregó $quantity del producto \"$productName\" ($productCode) al almacén destino"
                    ]);
                }


                ProductTransfer::create([
                    'transfer_id' => $lims_transfer_data->id,
                    'product_id' => $id,
                    'qty' => $qty[$i],
                    'variant_id' => $variant_id,
                    'purchase_unit_id' => $lims_purchase_unit_data->id,
                    'net_unit_cost' => $net_unit_cost[$i],
                    'tax_rate' => $tax_rate[$i],
                    'tax' => $tax[$i],
                    'total' => $total[$i]
                ]);
            }

            DB::commit();

            $originWarehouse = $lims_transfer_data->fromWarehouse;
            $destinationWarehouse = $lims_transfer_data->toWarehouse;
            $user = $lims_transfer_data->user;
            $createdAt = $lims_transfer_data->created_at->format('d/m/Y H:i');

            // if ($destinationWarehouse && $destinationWarehouse->phone) {
            //     $transferUrl = url("transfers/{$lims_transfer_data->id}/details");
            //     $message = "*🔔 AUTORIZACIÓN DE TRANSFERENCIA 🔔*\n\n" .
            //         "Estimado(a), se ha generado una solicitud de transferencia pendiente hacia su almacén.\n\n" .
            //         "📦 *Almacén de origen:* {$originWarehouse->name}\n" .
            //         "📦 *Almacén de destino:* {$destinationWarehouse->name}\n" .
            //         "👤 *Solicitado por:* {$user->name}\n" .
            //         "🗓️ *Fecha y hora:* {$createdAt}\n\n" .
            //         "Por favor, haga click en el siguiente enlace para revisarla y tomar acción:\n" .
            //         "{$transferUrl}\n\n" .
            //         "Gracias por su atención.";

            //     $whatsAppService->sendMessage($destinationWarehouse->phone, $message);
            // }


            return redirect('transfers')->with('message', 'Transferencia creada con éxito');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("error save transfer: " . $th->getMessage());
            return redirect('transfers')->with('not_permitted', 'Fallo al crear Transferencia.');
        }
    }


    public function productTransferData($id)
    {
        $lims_product_transfer_data = ProductTransfer::where('transfer_id', $id)->get();
        foreach ($lims_product_transfer_data as $key => $product_transfer_data) {
            $product = Product::find($product_transfer_data->product_id);
            $unit = Unit::find($product_transfer_data->purchase_unit_id);
            if ($product_transfer_data->variant_id) {
                $lims_product_variant_data = ProductVariant::select('item_code')->FindExactProduct($product_transfer_data->product_id, $product_transfer_data->variant_id)->first();
                $product->code = $lims_product_variant_data->item_code;
            }
            $product_transfer[0][$key] = $product->name . ' [' . $product->code . ']';
            $product_transfer[1][$key] = $product_transfer_data->qty;
            $product_transfer[2][$key] = $unit->unit_code;
            $product_transfer[3][$key] = $product_transfer_data->tax;
            $product_transfer[4][$key] = $product_transfer_data->tax_rate;
            $product_transfer[5][$key] = $product_transfer_data->total;
        }
        return $product_transfer;
    }
    public function transferRequest()
    {
        $role = Role::find(Auth::user()->role_id);

        if ($role->hasPermissionTo('transfers-index')) {

            $permissions = Role::findByName($role->name)->permissions;
            $all_permission = [];
            foreach ($permissions as $permission) {
                $all_permission[] = $permission->name;
            }
            if (empty($all_permission)) {
                $all_permission[] = 'dummy text';
            }

            if (Auth::user()->role_id > 2 && config('staff_access') == 'own') {
                $userWarehouseId = optional(Auth::user()->biller)->warehouse_id;

                $lims_transfer_all = Transfer::with('fromWarehouse', 'toWarehouse', 'user')
                    ->where('status', 2)
                    ->where(function ($query) use ($userWarehouseId) {
                        $query->where('user_id', Auth::id())
                            ->orWhere('to_warehouse_id', $userWarehouseId);
                    })
                    ->orderBy('id', 'desc')
                    ->get();
            } else {
                $lims_transfer_all = Transfer::with('fromWarehouse', 'toWarehouse', 'user')
                    ->where('status', 2)
                    ->orderBy('id', 'desc')
                    ->get();
            }

            return view('transfer.request', compact('lims_transfer_all', 'all_permission'));
        } else {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }
    }


    public function showTransferDetails($id)
    {
        $transfer = Transfer::with('fromWarehouse', 'toWarehouse', 'user', 'items.product')->findOrFail($id);
        $role = Role::find(Auth::user()->role_id);
        $all_permission = [];

        if ($role->hasPermissionTo('transfers-index')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if (empty($all_permission))
                $all_permission[] = 'dummy text';

            return view('transfer.details', compact('transfer', 'all_permission'));
        }

        return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }
    public function approve($id)
    {
        try {
            $transfer = Transfer::with('items')->findOrFail($id);
            $user = auth()->user();
            $role = Role::find($user->role_id);
            $all_permission = $role->permissions->pluck('name')->toArray();

            if (!in_array('accept-transfers', $all_permission)) {
                return redirect()->back()->with('not_permitted', 'No tienes permiso para aprobar transferencias.');
            }

            if ($transfer->status != 2) {
                return redirect()->back()->with('not_permitted', 'Solo se pueden aprobar transferencias pendientes.');
            }

            // Validación de warehouse según biller
            if ($user->role_id > 2) {
                $userWarehouseId = optional($user->biller)->warehouse_id;
                if ($transfer->to_warehouse_id != $userWarehouseId) {
                    return redirect()->back()->with('not_permitted', 'No puedes aprobar transferencias de otros almacenes.');
                }
            }

            DB::beginTransaction();

            $transfer->status = 1;
            $transfer->save();

            TransferRequestLog::create([
                'transfer_id' => $transfer->id,
                'user_id' => $user->id,
                'action' => 'aprobada',
                'note' => "Transferencia aprobada por el usuario {$user->name}."
            ]);

            foreach ($transfer->items as $productTransfer) {
                $pw = Product_Warehouse::where([
                    'product_id' => $productTransfer->product_id,
                    'warehouse_id' => $transfer->from_warehouse_id,
                    'variant_id' => $productTransfer->variant_id
                ])->first();

                $product = Product::select('id', 'name', 'code')->find($productTransfer->product_id);
                $productName = $product->name;
                $productCode = $product->code;

                if ($pw) {
                    $pw->blocked_qty -= $productTransfer->qty;
                    $pw->save();

                    TransferRequestLog::create([
                        'transfer_id' => $transfer->id,
                        'user_id' => $user->id,
                        'action' => 'liberado',
                        'note' => "Se liberó {$productTransfer->qty} bloquedos del producto \"$productName\" ($productCode) en almacén origen."
                    ]);
                }

                $pw_dest = Product_Warehouse::firstOrNew([
                    'product_id' => $productTransfer->product_id,
                    'warehouse_id' => $transfer->to_warehouse_id,
                    'variant_id' => $productTransfer->variant_id
                ]);

                $pw_dest->qty += $productTransfer->qty;
                $pw_dest->save();

                TransferRequestLog::create([
                    'transfer_id' => $transfer->id,
                    'user_id' => $user->id,
                    'action' => 'recibido',
                    'note' => "Se agregó {$productTransfer->qty} del producto \"$productName\" ($productCode) al almacén destino."
                ]);
            }

            DB::commit();
            return redirect()->back()->with('message', "Transferencia aprobada correctamente por {$user->name}.");

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("Error aprobar transferencia: " . $th->getMessage());
            return redirect()->back()->with('not_permitted', 'Error al aprobar la transferencia.');
        }
    }

    public function reject($id)
    {
        try {
            $transfer = Transfer::with('items')->findOrFail($id);
            $user = auth()->user();
            $role = Role::find($user->role_id);
            $all_permission = $role->permissions->pluck('name')->toArray();

            if (!in_array('accept-transfers', $all_permission)) {
                return redirect()->back()->with('not_permitted', 'No tienes permiso para rechazar transferencias.');
            }

            if ($transfer->status != 2) {
                return redirect()->back()->with('not_permitted', 'Solo se pueden rechazar transferencias pendientes.');
            }

            if ($user->role_id > 2) {
                $userWarehouseId = optional($user->biller)->warehouse_id;
                if ($transfer->to_warehouse_id != $userWarehouseId) {
                    return redirect()->back()->with('not_permitted', 'No puedes rechazar transferencias de otros almacenes.');
                }
            }

            DB::beginTransaction();

            $transfer->status = 4;
            $transfer->save();

            TransferRequestLog::create([
                'transfer_id' => $transfer->id,
                'user_id' => $user->id,
                'action' => 'rechazada',
                'note' => "Transferencia rechazada por el usuario {$user->name}."
            ]);

            foreach ($transfer->items as $productTransfer) {
                $pw = Product_Warehouse::where([
                    'product_id' => $productTransfer->product_id,
                    'warehouse_id' => $transfer->from_warehouse_id,
                    'variant_id' => $productTransfer->variant_id
                ])->first();

                $product = Product::select('id', 'name', 'code')->find($productTransfer->product_id);
                $productName = $product->name;
                $productCode = $product->code;

                if ($pw) {
                    $pw->qty += $productTransfer->qty;
                    $pw->blocked_qty -= $productTransfer->qty;
                    $pw->save();

                    TransferRequestLog::create([
                        'transfer_id' => $transfer->id,
                        'user_id' => $user->id,
                        'action' => 'desbloqueado',
                        'note' => "Se devolvió {$productTransfer->qty} del producto \"$productName\" ($productCode) al stock del almacén origen."
                    ]);
                }
            }

            DB::commit();
            return redirect()->back()->with('message', "Transferencia rechazada correctamente por {$user->name}.");

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("Error rechazar transferencia: " . $th->getMessage());
            return redirect()->back()->with('not_permitted', 'Error al rechazar la transferencia.');
        }
    }


    public function transferByCsv()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('transfers-add')) {
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            return view('transfer.import', compact('lims_warehouse_list'));
        } else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function importTransfer(Request $request)
    {
        $upload = $request->file('file');
        $ext = pathinfo($upload->getClientOriginalName(), PATHINFO_EXTENSION);
        if ($ext != 'csv')
            return redirect()->back()->with('message', 'Por favor sube un archivo CSV.');

        $filePath = $upload->getRealPath();
        $file_handle = fopen($filePath, 'r');

        // Columnas esperadas: product_code, quantity, product_unit, product_cost, tax_name
        $i = 0;
        $product_data = [];
        $unit         = [];
        $tax          = [];
        $qty          = [];
        $cost         = [];

        while (!feof($file_handle)) {
            $current_line = fgetcsv($file_handle);
            if (!$current_line) { $i++; continue; } // línea vacía al final
            if ($i === 0) { $i++; continue; }        // saltar cabecera

            // product_code → columna 0
            $product = Product::where('code', trim($current_line[0]))->first();
            if (!$product)
                return redirect()->back()->with('message', "Producto no encontrado: {$current_line[0]}");

            // quantity → columna 1
            $qty[] = (float) $current_line[1];

            // product_unit → columna 2
            $unitRow = Unit::where('unit_code', trim($current_line[2]))->first();
            if (!$unitRow)
                return redirect()->back()->with('message', "Unidad no encontrada: {$current_line[2]}");

            // product_cost → columna 3
            $cost[] = (float) $current_line[3];

            // tax_name → columna 4
            if (strtolower(trim($current_line[4])) !== 'no tax') {
                $taxRow = Tax::where('name', trim($current_line[4]))->first();
                if (!$taxRow)
                    return redirect()->back()->with('message', "Impuesto no encontrado: {$current_line[4]}");
                $tax[] = ['rate' => $taxRow->rate, 'name' => $taxRow->name];
            } else {
                $tax[] = ['rate' => 0, 'name' => 'No Tax'];
            }

            $product_data[] = $product;
            $unit[]         = $unitRow;
            $i++;
        }
        fclose($file_handle);

        if (empty($product_data))
            return redirect()->back()->with('message', 'El archivo CSV no contiene productos válidos.');

        $data = $request->except('file');
        $data['reference_no']  = 'tr-' . date("Ymd") . '-' . date("his");
        $data['shipping_cost'] = (float) ($data['shipping_cost'] ?? 0);
        $data['user_id']       = Auth::id();
        $data['company_id']    = Auth::user()->company_id;
        $data['total_qty']     = 0;
        $data['total_tax']     = 0;
        $data['total_cost']    = 0;
        $data['item']          = 0;
        $data['grand_total']   = 0;

        $document = $request->document;
        if ($document) {
            $v = Validator::make(
                ['extension' => strtolower($document->getClientOriginalExtension())],
                ['extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt']
            );
            if ($v->fails())
                return redirect()->back()->withErrors($v->errors());

            $docExt      = pathinfo($document->getClientOriginalName(), PATHINFO_EXTENSION);
            $documentName = $data['reference_no'] . '.' . $docExt;
            $document->move('public/documents/transfer', $documentName);
            $data['document'] = $documentName;
        }

        try {
            DB::beginTransaction();

            $lims_transfer_data = Transfer::create($data);

            foreach ($product_data as $key => $product) {
                $taxRate = $tax[$key]['rate'];
                $unitRow = $unit[$key];
                $unitQty = $qty[$key];
                $unitCost = $cost[$key];

                // Calcular coste neto y tax según tax_method del producto
                if ($product->tax_method == 2) {
                    // inclusive
                    $net_unit_cost = (100 / (100 + $taxRate)) * $unitCost;
                    $product_tax   = ($unitCost - $net_unit_cost) * $unitQty;
                    $total         = $unitCost * $unitQty;
                } else {
                    // exclusive (default) o sin tax
                    $net_unit_cost = $unitCost;
                    $product_tax   = $net_unit_cost * ($taxRate / 100) * $unitQty;
                    $total         = ($net_unit_cost * $unitQty) + $product_tax;
                }

                // Cantidad real según operador de unidad
                $quantity = ($unitRow->operator == '/')
                    ? $unitQty / $unitRow->operation_value
                    : $unitQty * $unitRow->operation_value;

                // Mover stock sólo si status Completado (1) o Enviado (3)
                if (in_array($data['status'], [1, 3])) {
                    $pw_from = Product_Warehouse::where('product_id', $product->id)
                        ->where('warehouse_id', $data['from_warehouse_id'])
                        ->first();
                    if (!$pw_from)
                        throw new \Exception("Sin stock en almacén origen para: {$product->name}");
                    $pw_from->qty -= $quantity;
                    $pw_from->save();
                }

                if ($data['status'] == 1) {
                    $pw_to = Product_Warehouse::where('product_id', $product->id)
                        ->where('warehouse_id', $data['to_warehouse_id'])
                        ->first();
                    if ($pw_to) {
                        $pw_to->qty += $quantity;
                        $pw_to->save();
                    } else {
                        $pw_to = new Product_Warehouse();
                        $pw_to->product_id   = $product->id;
                        $pw_to->warehouse_id = $data['to_warehouse_id'];
                        $pw_to->qty          = $quantity;
                        $pw_to->save();
                    }
                }

                $product_transfer = new ProductTransfer();
                $product_transfer->transfer_id      = $lims_transfer_data->id;
                $product_transfer->product_id       = $product->id;
                $product_transfer->qty              = $unitQty;
                $product_transfer->purchase_unit_id = $unitRow->id;
                $product_transfer->net_unit_cost    = round($net_unit_cost, 2);
                $product_transfer->tax_rate         = $taxRate;
                $product_transfer->tax              = round($product_tax, 2);
                $product_transfer->total            = round($total, 2);
                $product_transfer->save();

                $lims_transfer_data->total_qty  += $unitQty;
                $lims_transfer_data->total_tax  += round($product_tax, 2);
                $lims_transfer_data->total_cost += round($total, 2);
            }

            $lims_transfer_data->item        = count($product_data);
            $lims_transfer_data->grand_total = $lims_transfer_data->total_cost + $lims_transfer_data->shipping_cost;
            $lims_transfer_data->save();

            DB::commit();
            return redirect('transfers')->with('message', 'Transferencia importada con éxito');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("Error importar transferencia CSV: " . $th->getMessage());
            return redirect()->back()->with('not_permitted', 'Error al importar: ' . $th->getMessage());
        }
    }

    public function edit($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('transfers-edit')) {
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_transfer_data = Transfer::find($id);
            $lims_product_transfer_data = ProductTransfer::where('transfer_id', $id)->get();
            return view('transfer.edit', compact('lims_warehouse_list', 'lims_transfer_data', 'lims_product_transfer_data'));
        } else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
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
                $document->move('public/documents/transfer', $documentName);
                $data['document'] = $documentName;
            }

            $lims_transfer_data = Transfer::find($id);
            $lims_product_transfer_data = ProductTransfer::where('transfer_id', $id)->get();
            $product_id = $data['product_id'];
            $product_variant_id = $data['product_variant_id'];
            $qty = $data['qty'];
            $purchase_unit = $data['purchase_unit'];
            $net_unit_cost = $data['net_unit_cost'];
            $tax_rate = $data['tax_rate'];
            $tax = $data['tax'];
            $total = $data['subtotal'];
            $product_transfer = [];
            foreach ($lims_product_transfer_data as $key => $product_transfer_data) {
                $old_product_id[] = $product_transfer_data->product_id;
                $old_product_variant_id[] = null;
                $lims_transfer_unit_data = Unit::find($product_transfer_data->purchase_unit_id);
                if ($lims_transfer_unit_data->operator == '*') {
                    $quantity = $product_transfer_data->qty * $lims_transfer_unit_data->operation_value;
                } else {
                    $quantity = $product_transfer_data->qty / $lims_transfer_unit_data->operation_value;
                }

                if ($lims_transfer_data->status == 1) {
                    if ($product_transfer_data->variant_id) {
                        $lims_product_variant_data = ProductVariant::select('id')->FindExactProduct($product_transfer_data->product_id, $product_transfer_data->variant_id)->first();
                        $lims_product_from_warehouse_data = Product_Warehouse::FindProductWithVariant($product_transfer_data->product_id, $product_transfer_data->variant_id, $lims_transfer_data->from_warehouse_id)->first();
                        $lims_product_to_warehouse_data = Product_Warehouse::FindProductWithVariant($product_transfer_data->product_id, $product_transfer_data->variant_id, $lims_transfer_data->to_warehouse_id)->first();
                        $old_product_variant_id[$key] = $lims_product_variant_data->id;
                    } else {
                        $lims_product_from_warehouse_data = Product_Warehouse::FindProductWithoutVariant($product_transfer_data->product_id, $lims_transfer_data->from_warehouse_id)->first();
                        $lims_product_to_warehouse_data = Product_Warehouse::FindProductWithoutVariant($product_transfer_data->product_id, $lims_transfer_data->to_warehouse_id)->first();
                    }

                    $lims_product_from_warehouse_data->qty += $quantity;
                    $lims_product_from_warehouse_data->save();

                    $lims_product_to_warehouse_data->qty -= $quantity;
                    $lims_product_to_warehouse_data->save();
                    Log::debug("Antes Product_Warehouse: " . json_encode($lims_product_from_warehouse_data));
                } elseif ($lims_transfer_data->status == 3) {
                    if ($product_transfer_data->variant_id) {
                        $lims_product_variant_data = ProductVariant::select('id')->FindExactProduct($product_transfer_data->product_id, $product_transfer_data->variant_id)->first();
                        $lims_product_from_warehouse_data = Product_Warehouse::FindProductWithVariant($product_transfer_data->product_id, $product_transfer_data->variant_id, $lims_transfer_data->from_warehouse_id)->first();
                        $old_product_variant_id[$key] = $lims_product_variant_data->id;
                    } else {
                        $lims_product_from_warehouse_data = Product_Warehouse::FindProductWithoutVariant($product_transfer_data->product_id, $lims_transfer_data->from_warehouse_id)->first();
                    }
                    $lims_product_from_warehouse_data->qty += $quantity;
                    $lims_product_from_warehouse_data->save();
                    Log::debug("Antes Product_Warehouse: " . json_encode($lims_product_from_warehouse_data));
                }

                if ($product_transfer_data->variant_id && !(in_array($old_product_variant_id[$key], $product_variant_id))) {
                    $product_transfer_data->delete();
                } elseif (!(in_array($old_product_id[$key], $product_id))) {
                    $product_transfer_data->delete();
                }
            }

            foreach ($product_id as $key => $pro_id) {
                $lims_product_data = Product::select('is_variant')->find($pro_id);
                $lims_transfer_unit_data = Unit::where('unit_name', $purchase_unit[$key])->first();
                $variant_id = null;
                //unit conversion
                if ($lims_transfer_unit_data->operator == '*') {
                    $quantity = $qty[$key] * $lims_transfer_unit_data->operation_value;
                } else {
                    $quantity = $qty[$key] / $lims_transfer_unit_data->operation_value;
                }

                if ($data['status'] == 1) {
                    if ($lims_product_data->is_variant) {
                        $lims_product_variant_data = ProductVariant::select('variant_id')->find($product_variant_id[$key]);
                        $lims_product_from_warehouse_data = Product_Warehouse::FindProductWithVariant($pro_id, $lims_product_variant_data->variant_id, $data['from_warehouse_id'])->first();
                        $lims_product_to_warehouse_data = Product_Warehouse::FindProductWithVariant($pro_id, $lims_product_variant_data->variant_id, $data['to_warehouse_id'])->first();
                        $variant_id = $lims_product_variant_data->variant_id;
                    } else {
                        $lims_product_from_warehouse_data = Product_Warehouse::FindProductWithoutVariant($pro_id, $data['from_warehouse_id'])->first();
                        $lims_product_to_warehouse_data = Product_Warehouse::FindProductWithoutVariant($pro_id, $data['to_warehouse_id'])->first();
                    }

                    $lims_product_from_warehouse_data->qty -= $quantity;
                    $lims_product_from_warehouse_data->save();

                    if ($lims_product_to_warehouse_data) {
                        $lims_product_to_warehouse_data->qty += $quantity;
                        $lims_product_to_warehouse_data->save();
                    } else {
                        $lims_product_warehouse_data = new Product_Warehouse();
                        $lims_product_warehouse_data->product_id = $pro_id;
                        $lims_product_warehouse_data->variant_id = $variant_id;
                        $lims_product_warehouse_data->warehouse_id = $data['to_warehouse_id'];
                        $lims_product_warehouse_data->qty = $quantity;
                        $lims_product_warehouse_data->save();
                    }
                    Log::debug("Despues Product_Warehouse: " . json_encode($lims_product_from_warehouse_data));
                } elseif ($data['status'] == 3) {
                    if ($lims_product_data->is_variant) {
                        $lims_product_variant_data = ProductVariant::select('variant_id')->find($product_variant_id[$key]);
                        $lims_product_from_warehouse_data = Product_Warehouse::FindProductWithVariant($pro_id, $lims_product_variant_data->variant_id, $data['from_warehouse_id'])->first();
                        $variant_id = $lims_product_variant_data->variant_id;
                    } else {
                        $lims_product_from_warehouse_data = Product_Warehouse::FindProductWithoutVariant($pro_id, $data['from_warehouse_id'])->first();
                    }

                    $lims_product_from_warehouse_data->qty -= $quantity;
                    $lims_product_from_warehouse_data->save();
                    Log::debug("Despues Product_Warehouse: " . json_encode($lims_product_from_warehouse_data));
                }

                $product_transfer['product_id'] = $pro_id;
                $product_transfer['variant_id'] = $variant_id;
                $product_transfer['transfer_id'] = $id;
                $product_transfer['qty'] = $qty[$key];
                $product_transfer['purchase_unit_id'] = $lims_transfer_unit_data->id;
                $product_transfer['net_unit_cost'] = $net_unit_cost[$key];
                $product_transfer['tax_rate'] = $tax_rate[$key];
                $product_transfer['tax'] = $tax[$key];
                $product_transfer['total'] = $total[$key];

                if ($lims_product_data->is_variant && in_array($product_variant_id[$key], $old_product_variant_id)) {
                    ProductTransfer::where([
                        ['transfer_id', $id],
                        ['product_id', $pro_id],
                        ['variant_id', $variant_id]
                    ])->update($product_transfer);
                } elseif ($variant_id == null && in_array($pro_id, $old_product_id)) {
                    ProductTransfer::where([
                        ['transfer_id', $id],
                        ['product_id', $pro_id]
                    ])->update($product_transfer);
                } else
                    ProductTransfer::create($product_transfer);
            }
            $lims_transfer_data->update($data);
            DB::commit();
            Log::info("Transfer updated successfully");

            return redirect('transfers')->with('message', 'Transferencia Actualizado con éxito');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("error update transfer: " . $th->getMessage());
            Log::error("error transfer: " . $th);
            return redirect('transfers')->with('not_permitted', 'Fallo al actualizar Transferencia, Intente de nuevo!');
        }
    }

    public function deleteBySelection(Request $request)
    {
        try {
            DB::beginTransaction();
            $transfer_id = $request['transferIdArray'];
            foreach ($transfer_id as $id) {
                $lims_transfer_data = Transfer::find($id);
                $lims_product_transfer_data = ProductTransfer::where('transfer_id', $id)->get();
                foreach ($lims_product_transfer_data as $product_transfer_data) {
                    $lims_transfer_unit_data = Unit::find($product_transfer_data->purchase_unit_id);
                    if ($lims_transfer_unit_data->operator == '*') {
                        $quantity = $product_transfer_data->qty * $lims_transfer_unit_data->operation_value;
                    } else {
                        $quantity = $product_transfer_data / $lims_transfer_unit_data->operation_value;
                    }

                    if ($lims_transfer_data->status == 1) {
                        //add quantity for from warehouse
                        if ($product_transfer_data->variant_id)
                            $lims_product_warehouse_data = Product_Warehouse::FindProductWithVariant($product_transfer_data->product_id, $product_transfer_data->variant_id, $lims_transfer_data->from_warehouse_id)->first();
                        else
                            $lims_product_warehouse_data = Product_Warehouse::FindProductWithoutVariant($product_transfer_data->product_id, $lims_transfer_data->from_warehouse_id)->first();
                        $lims_product_warehouse_data->qty += $quantity;
                        $lims_product_warehouse_data->save();
                        //deduct quantity for to warehouse
                        if ($product_transfer_data->variant_id)
                            $lims_product_warehouse_data = Product_Warehouse::FindProductWithVariant($product_transfer_data->product_id, $product_transfer_data->variant_id, $lims_transfer_data->to_warehouse_id)->first();
                        else
                            $lims_product_warehouse_data = Product_Warehouse::FindProductWithoutVariant($product_transfer_data->product_id, $lims_transfer_data->to_warehouse_id)->first();

                        $lims_product_warehouse_data->qty -= $quantity;
                        $lims_product_warehouse_data->save();
                    } elseif ($lims_transfer_data->status == 3) {
                        //add quantity for from warehouse
                        if ($product_transfer_data->variant_id)
                            $lims_product_warehouse_data = Product_Warehouse::FindProductWithVariant($product_transfer_data->product_id, $product_transfer_data->variant_id, $lims_transfer_data->from_warehouse_id)->first();
                        else
                            $lims_product_warehouse_data = Product_Warehouse::FindProductWithoutVariant($product_transfer_data->product_id, $lims_transfer_data->from_warehouse_id)->first();

                        $lims_product_warehouse_data->qty += $quantity;
                        $lims_product_warehouse_data->save();
                    }
                    $product_transfer_data->delete();
                }
                $lims_transfer_data->delete();
            }
            DB::commit();
            Log::info("Transfers deleted successfully");
            return 'Transferencias eliminados con éxito!';
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("error delete transfer: " . $th->getMessage());
            return 'Fallo al eliminar Transferencias, Intente de nuevo!';
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $lims_transfer_data = Transfer::find($id);
            $lims_product_transfer_data = ProductTransfer::where('transfer_id', $id)->get();
            foreach ($lims_product_transfer_data as $product_transfer_data) {
                $lims_transfer_unit_data = Unit::find($product_transfer_data->purchase_unit_id);
                if ($lims_transfer_unit_data->operator == '*') {
                    $quantity = $product_transfer_data->qty * $lims_transfer_unit_data->operation_value;
                } else {
                    $quantity = $product_transfer_data / $lims_transfer_unit_data->operation_value;
                }

                if ($lims_transfer_data->status == 1) {
                    //add quantity for from warehouse
                    if ($product_transfer_data->variant_id)
                        $lims_product_warehouse_data = Product_Warehouse::FindProductWithVariant($product_transfer_data->product_id, $product_transfer_data->variant_id, $lims_transfer_data->from_warehouse_id)->first();
                    else
                        $lims_product_warehouse_data = Product_Warehouse::FindProductWithoutVariant($product_transfer_data->product_id, $lims_transfer_data->from_warehouse_id)->first();
                    $lims_product_warehouse_data->qty += $quantity;
                    $lims_product_warehouse_data->save();
                    //deduct quantity for to warehouse
                    if ($product_transfer_data->variant_id)
                        $lims_product_warehouse_data = Product_Warehouse::FindProductWithVariant($product_transfer_data->product_id, $product_transfer_data->variant_id, $lims_transfer_data->to_warehouse_id)->first();
                    else
                        $lims_product_warehouse_data = Product_Warehouse::FindProductWithoutVariant($product_transfer_data->product_id, $lims_transfer_data->to_warehouse_id)->first();

                    $lims_product_warehouse_data->qty -= $quantity;
                    $lims_product_warehouse_data->save();
                } elseif ($lims_transfer_data->status == 3) {
                    //add quantity for from warehouse
                    if ($product_transfer_data->variant_id)
                        $lims_product_warehouse_data = Product_Warehouse::FindProductWithVariant($product_transfer_data->product_id, $product_transfer_data->variant_id, $lims_transfer_data->from_warehouse_id)->first();
                    else
                        $lims_product_warehouse_data = Product_Warehouse::FindProductWithoutVariant($product_transfer_data->product_id, $lims_transfer_data->from_warehouse_id)->first();

                    $lims_product_warehouse_data->qty += $quantity;
                    $lims_product_warehouse_data->save();
                }
                $product_transfer_data->delete();
            }
            $lims_transfer_data->delete();
            DB::commit();
            Log::info("Transfer deleted successfully");
            return redirect('transfers')->with('not_permitted', 'Transferencia eliminado con éxito');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("error delete transfer: " . $th->getMessage());
            return redirect('transfers')->with('not_permitted', 'Fallo al eliminar Transferencia, Intente de nuevo!');
        }
    }
}
