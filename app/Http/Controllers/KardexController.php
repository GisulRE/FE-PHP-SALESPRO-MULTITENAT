<?php

namespace App\Http\Controllers;

use App\Coupon;
use App\CustomerSale;
use App\GeneralSetting;
use App\kardex;
use App\Product;
use App\Product_Warehouse;
use App\Purchase;
use App\PurchaseProductReturn;
use App\ReturnPurchase;
use App\Returns;
use App\Sale;
use App\Supplier;
use App\Transfer;
use App\User;
use App\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use NumberToWords\Legacy\Numbers\Words\Locale\Id;
use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Sum;

use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\isNull;

class KardexController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $lims_warehouse_list = Warehouse::select('id', 'name')->get();

        return view('report.kardex', compact(
            'lims_warehouse_list',

        ));
    }

    public function search(Request $request)
    {
        //  return $this->searchService($request, null);
        return view('report.kardex', $this->searchService($request, null));
    }

    public function controlPoint(Request $request)
    {

        try {
            $product_id = $this->getproductIdByCode($request->lims_productcode);
            $warehouse_id = $request->warehouse_id;
            $product_warehouse_qty = Product_Warehouse::select('qty')
                ->where('product_id', $product_id)
                ->where('warehouse_id', $warehouse_id)
                ->first();



            if (!empty($product_warehouse_qty)) {
                DB::table('record')->insert([
                    'transaction_id' => 0,
                    'warehouse_id' => $warehouse_id,
                    'product_id' => $product_id,
                    'reference_no' => 0,
                    'transaction_type' => 0,
                    'product_qty_before' => 0,
                    'product_qty_after' => 0,
                    'warehouse_qty_before' => 0,
                    'warehouse_qty_after' => $product_warehouse_qty->qty,
                ]);
                $message = ["success" => "Punto de control creado con exito"];
            } else {
                $message = ["alert" => "El producto " . $request->product_code_name . " no existe en este almacen"];
            }
        } catch (\Throwable $th) {
            $message = ["alert" => 'Error al crear el punto de control: ' . $th->getMessage()];
        }

        return view('report.kardex', $this->searchService($request, $message));
    }

    public function warehouseControlPoint(Request $request)
    {
        $products_id_list = Product::where('is_active', true)->pluck('id');
        $lims_warehouse_list = Warehouse::select('id', 'name')->get();
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $warehouse_id = $request->warehouse_id;

        try {
            DB::beginTransaction();

            $products_warehouse_qties = Product_Warehouse::whereIn('product_id', $products_id_list)
                ->where('warehouse_id', $warehouse_id)
                ->pluck('product_id', 'qty');

            foreach ($products_id_list as $product_id) {
                $warehouse_qty = $products_warehouse_qties[$product_id] ?? 0;
                $product_warehouse_qty = Product_Warehouse::select('qty')
                    ->where('product_id', $product_id)
                    ->where('warehouse_id', $warehouse_id)
                    ->first();

                DB::table('record')->insert([
                    'transaction_id' => 0,
                    'warehouse_id' => $warehouse_id,
                    'product_id' => $product_id,
                    'reference_no' => 0,
                    'transaction_type' => 0,
                    'product_qty_before' => 0,
                    'product_qty_after' => 0,
                    'warehouse_qty_before' => 0,
                    'warehouse_qty_after' => $product_warehouse_qty != null ? $product_warehouse_qty->qty : 0
                ]);
            }

            DB::commit();

            $date = date('Y-m-d');
            $report_data_list = Kardex::whereDate('date', $date)->where('warehouse_id', $warehouse_id)->get();
            $message = ["success" => "Punto de control creado con exito"];
        } catch (\Throwable $th) {
            DB::rollback();
            $message = ["alert" => 'Error al crear el punto de control: ' . $th->getMessage()];
        }

        return view('report.kardex', compact(
            'report_data_list',
            'lims_warehouse_list',
            'start_date',
            'end_date',
            'warehouse_id',
            'message'
        ));
    }

    private function searchService($request, $message)
    {

        // Validar la solicitud y obtener los datos
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'warehouse_id' => 'nullable|integer',
            'lims_productcode' => 'nullable|string',
        ]);

        // Obtener los datos de la solicitud
        $lims_warehouse_list = Warehouse::select('id', 'name')->get();
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $warehouse_id = $request->warehouse_id;
        $lims_productcode = $request->lims_productcode;
        $product_id = null;
        $product_code_name = $request->product_code_name;

        // Crear la consulta base
        $query = Kardex::query();

        // Aplicar filtro de almacén si está presente
        if ($warehouse_id) {
            $query->where('warehouse_id', $warehouse_id);
        }

        // Aplicar filtro de código de producto si está presente
        if ($lims_productcode) {
            $product_id = $this->getproductIdByCode($lims_productcode);
            $query->where('product_id', $product_id);
        }

        $query_prev_balance = $query;

        // Aplicar filtros de fecha si están presentes
        if ($start_date && $end_date) {
            // $query->whereBetween('date', [$start_date, $end_date]);

            $query->whereDate('date', '>=', $start_date);
            $query->whereDate('date', '<=', $end_date);
        }

        // Obtener los datos filtrados
        //$query->where('transaction_id', '!=', 0);
        $report_data_list = $query->orderBy('date', 'asc')->get();

        if ($start_date) {
            // $query->whereBetween('date', [$start_date]);
            $query_prev_balance->whereDate('date', '<', $start_date);
        }

        // Obtener saldo anterior
        #$query_prev_balance->select(DB::raw('SUM(entrada) as total_entrada'), DB::raw('SUM(salida) as total_salida'));
        $query_prev_balance->select(DB::raw('SUM(entrada) as total_entrada'), DB::raw('SUM(salida) as total_salida'), 'product', 'warehouse', 'cost', DB::raw('SUM(entrada) - SUM(salida) as saldo'));

        $prev_balance = $query_prev_balance->first();

        // Validad saldo anterior 
        if ($prev_balance->product == null && $lims_productcode) {

            // Si   antes de la fecha inicial no hay una transaccion, el kardex tomara el valor el valor de inicializacion del producto en el kardex. 

            $prev_balance = Kardex::select('entrada', 'salida', 'warehouse_qty_after as saldo', 'product', 'warehouse', 'cost')->where('product_id', $product_id)->where('warehouse_id', $warehouse_id)->where('transaction_id', 0)->first();
            // se cambio logica de $prev_balance siempre entre en la condicion, esperar informe si afecta reporte saldo anterior
            if ($prev_balance) {
                $product = Product::find($product_id);
                $product_name = $product->name;
                $product_cost = $product->cost;
                $warehouse_name = Warehouse::find($warehouse_id)->name;
                $last_stock = DB::select('Select warehouse_qty_after from record where product_id = ? and warehouse_id = ? and action_taken_at < ? order by action_taken_at desc limit 1', [$product_id, $warehouse_id, $start_date]);
                $prev_balance = (object) [
                    'entrada' => 0,
                    'salida' => 0,
                    'saldo' => $last_stock != null ? $last_stock[0]->warehouse_qty_after : 0,
                    'product' => $product_name,
                    'warehouse' => $warehouse_name,
                    'cost' => $product_cost
                ];
            }
        }

        $totalBalance = 0;

        $calcTotalBalance = function ($prev_balance, $transaction, $lastRs, $key) {
            if ($key == 0) {
                $lastBalance = ($prev_balance->cost * $prev_balance->saldo);
                if ($transaction->entrada > 0) {
                    $operation = $transaction->entrada * $transaction->cost;
                    $totalBalance = $lastBalance + $operation;
                } else {
                    $operation = $transaction->salida * $transaction->cost;
                    $totalBalance = $lastBalance - $operation;
                }
            } else {
                if ($transaction->entrada > 0) {
                    $operation = $transaction->entrada * $transaction->cost;
                    $totalBalance = $lastRs + $operation;
                } else {
                    $operation = $transaction->salida * $transaction->cost;
                    $totalBalance = $lastRs - $operation;
                }
            }

            return $totalBalance;
        };

        // Precompute transfer-related display values to avoid N+1 queries in the view
        $transferIds = $report_data_list->where('transaction_type', 'TRANSFER')->pluck('transaction_id')->filter()->unique()->values()->all();
        $transfers = [];
        if (!empty($transferIds)) {
            $transfers = Transfer::whereIn('id', $transferIds)->get()->keyBy('id');
        }

        foreach ($report_data_list as $tx) {
            // default display value
            $tx->display_warehouse_qty_after = $tx->warehouse_qty_after;
            $tx->transfer_status_label = null;
            $tx->transfer_status_class = null;

            if ($tx->transaction_type == 'TRANSFER' && $tx->transaction_id) {
                $t = $transfers[$tx->transaction_id] ?? null;
                if ($t) {
                    // Map statuses and adjust display qty when needed
                    if ($t->status == 1) {
                        // Completed: add entrada
                        $tx->display_warehouse_qty_after = $tx->warehouse_qty_after + $tx->entrada;
                        $tx->transfer_status_label = trans('file.Completed');
                        $tx->transfer_status_class = 'badge-success';
                    } elseif ($t->status == 2) {
                        $tx->transfer_status_label = trans('file.Pending');
                        $tx->transfer_status_class = 'badge-warning';
                    } elseif ($t->status == 3) {
                        $tx->transfer_status_label = trans('file.Sent');
                        $tx->transfer_status_class = 'badge-info';
                    } elseif ($t->status == 4) {
                        // Rejected (Cancelado): add salida
                        $tx->display_warehouse_qty_after = $tx->warehouse_qty_after + $tx->salida;
                        $tx->transfer_status_label = 'Cancelado';
                        $tx->transfer_status_class = 'badge-danger';
                    } else {
                        $tx->transfer_status_label = trans('file.Unknown');
                        $tx->transfer_status_class = 'badge-secondary';
                    }
                }
            }
        }

        return compact(
            'prev_balance',
            'report_data_list',
            'lims_warehouse_list',
            'start_date',
            'end_date',
            'warehouse_id',
            'lims_productcode',
            'product_code_name',
            'message',
            'totalBalance',
            'calcTotalBalance'
        );
    }

    public function getproductIdByCode($code)
    {
        $product = Product::where('code', '=', $code)->where('is_active', true)->get();
        return $product[0]->id;
    }

    public function transactionDetails(Request $request)
    {

        switch ($request->type) {
            case 'VENTA':
                return $this->saleDetail($request->id);
                break;

            case 'COMPRA':
                return $this->purchaseDetail($request->id);
                break;

            case 'RETURN':
                return $this->returnDetail($request->id);
                break;

            case 'TRANSFER':
                return $this->transferDetail($request->id);
                break;

            case 'COMPRA_RETURN':
                return $this->purchaceReturnDetail($request->id);
                break;
        }
    }

    public function saleDetail($id)
    {
        $sales = Sale::select('sales.*')
            ->with('biller', 'customer', 'warehouse', 'user')
            ->join('customers', 'sales.customer_id', '=', 'customers.id')
            ->join('billers', 'sales.biller_id', '=', 'billers.id')
            ->where('sales.id', '=', $id)->get();

        if (!empty($sales)) {
            foreach ($sales as $key => $sale) {

                if ($sale->sale_status == 1) {
                    $sale_status = trans('file.Completed');
                } elseif ($sale->sale_status == 2) {
                    $sale_status = trans('file.Pending');
                } elseif ($sale->sale_status == 4) {
                    $sale_status = trans('file.Receivable');
                } else {
                    $sale_status = trans('file.Draft');
                }

                // data for sale details by one click
                $coupon = Coupon::find($sale->coupon_id);
                if ($coupon) {
                    $coupon_code = $coupon->code;
                } else {
                    $coupon_code = null;
                }

                $data = array(
                    date(config('date_format'), strtotime($sale->date_sell)),
                    $sale->reference_no,
                    $sale_status,
                    $sale->biller->name,
                    $sale->biller->company_name,
                    $sale->biller->email,
                    $sale->biller->phone_number,
                    $sale->biller->address,
                    $sale->biller->city,
                    $sale->customer->name,
                    $sale->customer->phone_number,
                    $sale->customer->address,
                    $sale->customer->city,
                    $sale->id,
                    $sale->total_tax,
                    $sale->total_discount,
                    $sale->total_price,
                    $sale->order_tax,
                    $sale->order_tax_rate,
                    $sale->order_discount,
                    $sale->shipping_cost,
                    $sale->grand_total,
                    $sale->paid_amount,
                    $sale->sale_note,
                    $sale->staff_note,
                    $sale->user->name,
                    $sale->user->email,
                    $sale->warehouse->name,
                    $coupon_code,
                    $sale->coupon_discount,
                    $sale->total_tips,
                );
            }
        }

        return $data;
    }

    public function purchaseDetail($id)
    {

        $purchases = Purchase::with('supplier', 'warehouse')
            ->where('purchases.id', $id)->get();

        if (!empty($purchases)) {
            foreach ($purchases as $key => $purchase) {


                if ($purchase->supplier_id) {
                    $supplier = $purchase->supplier;
                } else {
                    $supplier = new Supplier();
                }

                if ($purchase->status == 1) {

                    $purchase_status = trans('file.Recieved');
                } elseif ($purchase->status == 2) {

                    $purchase_status = trans('file.Partial');
                } elseif ($purchase->status == 3) {

                    $purchase_status = trans('file.Pending');
                } else {

                    $purchase_status = trans('file.Ordered');
                }

                // data for purchase details by one click
                $user = User::find($purchase->user_id);
                $data = array(
                    date(config('date_format'), strtotime($purchase->created_at->toDateString())),
                    $purchase->reference_no,
                    $purchase_status,
                    $purchase->id,
                    $purchase->warehouse->name,
                    $purchase->warehouse->phone,
                    $purchase->warehouse->address,
                    $supplier->name,
                    $supplier->company_name,
                    $supplier->email,
                    $supplier->phone_number,
                    $supplier->address,
                    $supplier->city,
                    $purchase->total_tax,
                    $purchase->total_discount,
                    $purchase->total_cost,
                    $purchase->order_tax,
                    $purchase->order_tax_rate,
                    $purchase->order_discount,
                    $purchase->shipping_cost,
                    $purchase->grand_total,
                    $purchase->paid_amount,
                    $purchase->purchase_note,
                    $user->name,
                    $user->email,
                );
            }
        }
        return $data;
    }

    public function returnDetail($id)
    {

        $return = Returns::with('biller', 'customer', 'warehouse', 'user')->where('returns.id', '=', $id)->get();

        $data =
            [

                date(config('date_format'), strtotime($return[0]->created_at->toDateString())),
                $return[0]->reference_no,
                $return[0]->warehouse->name,
                $return[0]->biller->name,
                $return[0]->biller->company_name,
                $return[0]->biller->email,
                $return[0]->biller->phone_number,
                $return[0]->biller->address,
                $return[0]->biller->city,
                $return[0]->customer->name,
                $return[0]->customer->phone_number,
                $return[0]->customer->address,
                $return[0]->customer->city,
                $return[0]->id,
                $return[0]->total_tax,
                $return[0]->total_discount,
                $return[0]->total_price,
                $return[0]->order_tax,
                $return[0]->order_tax_rate,
                $return[0]->grand_total,
                $return[0]->return_note,
                $return[0]->staff_note,
                $return[0]->user->name,
                $return[0]->user->email
            ];


        return $data;
    }

    public function purchaceReturnDetail($id)
    {
        $return = ReturnPurchase::with('supplier', 'warehouse', 'user')
            ->orderBy('id', 'desc')
            ->where('return_purchases.id', '=', $id)
            ->get();
        $data =
            [

                date(config('date_format'), strtotime($return[0]->created_at->toDateString())),
                $return[0]->reference_no,
                $return[0]->warehouse->name,
                $return[0]->warehouse->phone .
                $return[0]->warehouse->address,
                $return[0]->supplier->name,
                $return[0]->supplier->company_name,
                $return[0]->supplier->email,
                $return[0]->supplier->phone_number,
                $return[0]->supplier->address,
                $return[0]->supplier->city,
                $return[0]->id,
                $return[0]->total_tax,
                $return[0]->total_discount,
                $return[0]->total_cost,
                $return[0]->order_tax,
                $return[0]->order_tax_rate,
                $return[0]->grand_total,
                $return[0]->return_note,
                $return[0]->staff_note,
                $return[0]->user->name,
                $return[0]->user->email,

            ];


        return $data;
    }

    public function transferDetail($id)
    {

        $transfer = Transfer::with('fromWarehouse', 'toWarehouse', 'user')->where('transfers.id', '=', $id)->get();
        if ($transfer[0]->status == 1) {
            $status = trans('file.Completed');
        } elseif ($transfer[0]->status == 2) {
            $status = trans('file.Pending');
        } elseif ($transfer[0]->status == 3) {
            $status = trans('file.Sent');
        }
        $general_setting = new GeneralSetting;

        $data =
            [
                date(config('date_format'), strtotime($transfer[0]->created_at->toDateString())),
                $transfer[0]->reference_no,
                $status,
                $transfer[0]->id,
                $transfer[0]->fromWarehouse->name,
                $transfer[0]->fromWarehouse->phone,
                $transfer[0]->fromWarehouse->address,
                $transfer[0]->toWarehouse->name,
                $transfer[0]->toWarehouse->phone,
                $transfer[0]->toWarehouse->address,
                $transfer[0]->total_tax,
                $transfer[0]->total_cost,
                $transfer[0]->shipping_cost,
                $transfer[0]->grand_total,
                $transfer[0]->note,
                $transfer[0]->user->name,
                $transfer[0]->user->email
            ];


        return $data;
    }

    public function getEstadoVentaFacturada($sale_id)
    {
        $tipo_factura_lookup = [
            1 => 'COM-VEN',
            2 => 'ALQ',
            13 => 'SERV',
        ];

        $estado_factura = " ";
        $venta_facturada = CustomerSale::where('sale_id', $sale_id)->first();
        if (!empty($venta_facturada)) {
            if ($venta_facturada->estado_factura != null) {
                $tipo_factura = $tipo_factura_lookup[$venta_facturada->codigo_documento_sector];
                if ($venta_facturada->nro_factura != null) {
                    $texto_factura = '[FACT-' . $tipo_factura . '#' . $venta_facturada->nro_factura . '|' . $venta_facturada->estado_factura . ']';
                    $estado_factura .= $texto_factura;
                } else {
                    $texto_factura = '[FACT-' . '<div class="badge badge-info">Manual</div>' . '-' . $tipo_factura . '#' . $venta_facturada->nro_factura_manual . ' |' . $venta_facturada->estado_factura . ']';
                    $estado_factura .= $texto_factura;
                }
            }
        }
        return $estado_factura;
    }
}
