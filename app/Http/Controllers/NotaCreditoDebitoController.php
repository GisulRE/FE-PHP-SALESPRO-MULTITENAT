<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\SiatTrait;
use App\Http\Traits\CufdTrait;
use App\Sale;
use App\CustomerSale;
use App\Product_Sale;
use App\Returns;
use App\ProductReturn;
use App\Product;
use App\Product_Warehouse;
use App\ProductVariant;
use App\Variant;
use App\Unit;
use App\Customer;
use App\Biller;
use App\Warehouse;
use App\Tax;
use App\Account;
use App\Cashier;
use App\Deposit;
use App\GiftCard;
use App\Payment;
use App\PaymentWithGiftCard;
use App\PosSetting;
use App\GeneralSetting;
use App\SiatPuntoVenta;
use App\SiatCufd;
use App\SiatSucursal;
use App\SiatParametricaVario;
use App\SiatLeyendaFactura;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use NumberToWords\NumberToWords;
use Barryvdh\DomPDF\Facade\Pdf;

class NotaCreditoDebitoController extends Controller
{
    use SiatTrait, CufdTrait;

    /**
     * Verifica la elegibilidad de una venta para emisión de Nota de Crédito-Débito (Sector 24).
     * Valida estado VIGENTE, antigüedad de 18 meses, calcula ítems disponibles y pagos con Gift Card.
     */
    public function checkSale($sale_id)
    {
        try {
            $customer_sale = null;
            if (!is_numeric($sale_id) || strlen((string)$sale_id) > 15) {
                // Se pasó el CUF de la factura (ej: desde Libro de Ventas)
                $customer_sale = CustomerSale::where('cuf', $sale_id)->first();
                if ($customer_sale && $customer_sale->sale_id) {
                    $sale_id = $customer_sale->sale_id;
                }
            }

            $sale = Sale::with(['customer', 'biller', 'warehouse', 'productSales.product.unit', 'payments'])->find($sale_id);
            if (!$sale) {
                return response()->json([
                    'status' => false,
                    'message' => 'Venta asociada a la factura no encontrada.'
                ], 404);
            }

            // Buscar la factura asociada (soporta codigo_documento_sector null o != 24) si no se obtuvo por CUF
            if (!$customer_sale) {
                $customer_sale = CustomerSale::where('sale_id', $sale_id)
                    ->where(function($q) {
                        $q->whereNull('codigo_documento_sector')
                          ->orWhere('codigo_documento_sector', '!=', 24);
                    })
                    ->orderBy('id', 'desc')
                    ->first();
            }

            if (!$customer_sale || empty($customer_sale->cuf)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Esta venta no cuenta con una factura fiscal electrónica SIAT asociada.'
                ]);
            }

            // Validar estado de la factura
            $estado_factura = strtoupper(trim($customer_sale->estado_factura ?? ''));
            if ($estado_factura === 'ANULADO') {
                return response()->json([
                    'status' => false,
                    'message' => 'La factura N° ' . $customer_sale->nro_factura . ' se encuentra ANULADA en el SIAT. No es posible emitir una Nota de Crédito-Débito sobre una factura anulada.'
                ]);
            }

            // Validar límite normativo de 18 meses (RND 102100000011 / Normativa SIAT)
            $fecha_emision = $customer_sale->created_at 
                ? Carbon::parse($customer_sale->created_at) 
                : ($sale->date_sell ? Carbon::parse($sale->date_sell) : Carbon::now());
            
            $now = Carbon::now();
            $dias_transcurridos = $fecha_emision->diffInDays($now);
            $meses_transcurridos = round($dias_transcurridos / 30.4375, 1);
            $is_within_18_months = $meses_transcurridos <= 18.0;

            if (!$is_within_18_months) {
                return response()->json([
                    'status' => false,
                    'is_expired' => true,
                    'message' => 'Plazo legal vencido: La factura fue emitida hace ' . round($meses_transcurridos, 1) . ' meses (' . $dias_transcurridos . ' días). La normativa tributaria del SIAT establece un plazo máximo de 18 meses para emitir Notas de Crédito-Débito.'
                ]);
            }

            // Obtener datos detallados de la factura original desde SIAT o local
            $factura_siat = $this->getFacturaData($customer_sale->cuf);
            $factura_detalle_map = [];
            if ($factura_siat && isset($factura_siat['detalle']) && is_array($factura_siat['detalle'])) {
                foreach ($factura_siat['detalle'] as $det) {
                    $factura_detalle_map[trim((string)$det['codigoProducto'])] = $det;
                }
            }

            // Obtener devoluciones previas activas para esta factura
            $prev_returns = Returns::where(function($q) use ($customer_sale) {
                    $q->where('cuf', $customer_sale->cuf)
                      ->orWhere('customer_sale_id', $customer_sale->id);
                })
                ->where('is_active', true)
                ->pluck('id');

            $returned_qtys = [];
            if ($prev_returns->count() > 0) {
                $returned_qtys = ProductReturn::whereIn('return_id', $prev_returns)
                    ->groupBy('product_id')
                    ->selectRaw('product_id, SUM(qty) as total_returned')
                    ->pluck('total_returned', 'product_id')
                    ->toArray();
            }

            // Verificar si fue pagada con Gift Card
            $monto_gift_card_total = 0;
            if ($factura_siat && isset($factura_siat['montoGiftCard'])) {
                $monto_gift_card_total = (float) $factura_siat['montoGiftCard'];
            } else {
                foreach ($sale->payments as $payment) {
                    if (in_array($payment->paying_method, ['Tarjeta_Regalo', 'Gift Card'])) {
                        $monto_gift_card_total += (float) $payment->amount;
                    }
                }
            }

            $monto_total_factura = (float) ($factura_siat['montoTotal'] ?? $sale->grand_total);
            $monto_sujeto_iva = (float) ($factura_siat['montoTotalSujetoIva'] ?? max(0, $monto_total_factura - $monto_gift_card_total));
            $is_gift_card_only = ($monto_gift_card_total > 0 && $monto_sujeto_iva <= 0.01);

            // Mapear ítems de la venta con cantidades disponibles
            $items = [];
            $total_available_items = 0;

            foreach ($sale->productSales as $ps) {
                $product = $ps->product;
                $pro_id = $ps->product_id;
                $original_qty = (float) $ps->qty;
                $already_returned = (float) ($returned_qtys[$pro_id] ?? 0);
                $available_qty = max(0, $original_qty - $already_returned);

                if ($available_qty > 0) {
                    $total_available_items++;
                }

                $code = $product ? $product->code : 'PROD-' . $pro_id;
                $orig_det = $factura_detalle_map[trim($code)] ?? null;

                $unit_price = (float) $ps->net_unit_price;
                $discount_total = (float) $ps->discount;
                $discount_per_unit = $original_qty > 0 ? ($discount_total / $original_qty) : 0;

                $unit_name = 'UNIDAD';
                if ($ps->sale_unit_id) {
                    $unit = Unit::find($ps->sale_unit_id);
                    if ($unit) {
                        $unit_name = $unit->unit_name;
                    }
                } elseif ($product && $product->unit) {
                    $unit_name = $product->unit->unit_name;
                }

                $items[] = [
                    'product_id' => $pro_id,
                    'product_code' => $code,
                    'product_name' => $product ? $product->name : ($orig_det['descripcion'] ?? 'Producto ' . $pro_id),
                    'unit_name' => $unit_name,
                    'original_qty' => $original_qty,
                    'already_returned_qty' => $already_returned,
                    'available_qty' => $available_qty,
                    'net_unit_price' => $unit_price,
                    'original_discount' => $discount_total,
                    'discount_per_unit' => round($discount_per_unit, 4),
                    'subtotal' => round($available_qty * $unit_price, 2),
                    'actividad_economica' => $orig_det['actividadEconomica'] ?? ($product->codigo_actividad ?? '477300'),
                    'codigo_producto_sin' => $orig_det['codigoProductoSin'] ?? ($product->codigo_producto_servicio ?? 99100),
                ];
            }

            // Datos de la caja activa / cuenta
            $active_cashier = null;
            if ($sale->biller && $sale->biller->account_id) {
                $active_cashier = Cashier::where([
                    ['account_id', $sale->biller->account_id],
                    ['is_active', true],
                    ['end_date', null]
                ])->first();
            }

            return response()->json([
                'status' => true,
                'sale' => [
                    'id' => $sale->id,
                    'reference_no' => $sale->reference_no,
                    'date' => $fecha_emision->format('d/m/Y H:i'),
                    'grand_total' => (float) $sale->grand_total,
                    'customer_id' => $sale->customer_id,
                    'customer_name' => $sale->customer ? $sale->customer->name : 'N/A',
                    'customer_phone' => $sale->customer ? $sale->customer->phone_number : '',
                    'warehouse_id' => $sale->warehouse_id,
                    'biller_id' => $sale->biller_id,
                ],
                'invoice' => [
                    'nro_factura' => $customer_sale->nro_factura,
                    'cuf' => $customer_sale->cuf,
                    'fecha_emision' => $fecha_emision->format('d/m/Y H:i:s'),
                    'estado_factura' => $estado_factura ?: 'VIGENTE',
                    'dias_transcurridos' => $dias_transcurridos,
                    'meses_transcurridos' => $meses_transcurridos,
                    'is_within_18_months' => $is_within_18_months,
                    'razon_social' => $customer_sale->razon_social,
                    'nit_ci' => $customer_sale->valor_documento,
                    'complemento' => $customer_sale->complemento_documento,
                    'email' => $customer_sale->email,
                    'tipo_documento' => $customer_sale->tipo_documento,
                    'monto_total' => $monto_total_factura,
                    'monto_sujeto_iva' => $monto_sujeto_iva,
                    'monto_gift_card' => $monto_gift_card_total,
                ],
                'is_gift_card_only' => $is_gift_card_only,
                'has_gift_card' => ($monto_gift_card_total > 0),
                'gift_card_amount' => $monto_gift_card_total,
                'items' => $items,
                'total_available_items' => $total_available_items,
                'has_active_cashier' => !empty($active_cashier)
            ]);

        } catch (\Exception $e) {
            Log::error('NotaCreditoDebitoController::checkSale - Error: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
            return response()->json([
                'status' => false,
                'message' => 'Error al verificar la venta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Emite la Nota de Crédito-Débito SIAT (Documento Sector 24) bajo transacción atómica.
     * Si SIAT falla, revierte completamente la base de datos sin dejar registros huérfanos.
     */
    public function emitir(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|numeric',
            'items' => 'required|array|min:1',
            'refund_method' => 'required|in:cash,customer_balance,gift_card',
            'adjust_inventory' => 'nullable|boolean',
            'staff_note' => 'nullable|string',
        ]);

        $sale_id = $request->input('sale_id');
        $items_input = $request->input('items');
        $refund_method = $request->input('refund_method');
        $adjust_inventory = (bool) $request->input('adjust_inventory', true);
        $staff_note = $request->input('staff_note', '');

        DB::beginTransaction();
        try {
            $sale = Sale::with(['customer', 'biller', 'warehouse', 'productSales'])->find($sale_id);
            if (!$sale) {
                DB::rollBack();
                return response()->json(['status' => false, 'message' => 'Venta no encontrada.'], 404);
            }

            $customer_sale = CustomerSale::where('sale_id', $sale_id)
                ->where(function($q) {
                    $q->whereNull('codigo_documento_sector')
                      ->orWhere('codigo_documento_sector', '!=', 24);
                })
                ->orderBy('id', 'desc')
                ->first();

            if (!$customer_sale || empty($customer_sale->cuf)) {
                DB::rollBack();
                return response()->json(['status' => false, 'message' => 'No se encontró la factura fiscal asociada a esta venta.'], 400);
            }

            if (strtoupper(trim($customer_sale->estado_factura ?? '')) === 'ANULADO') {
                DB::rollBack();
                return response()->json(['status' => false, 'message' => 'La factura original está ANULADA. No procede Nota de Crédito-Débito.'], 400);
            }

            // Validar 18 meses
            $fecha_factura = $customer_sale->created_at ? Carbon::parse($customer_sale->created_at) : Carbon::parse($sale->date_sell);
            if ($fecha_factura->diffInDays(Carbon::now()) > 548) { // 18 meses
                DB::rollBack();
                return response()->json(['status' => false, 'message' => 'La factura supera el límite normativo de 18 meses para notas de crédito/débito.'], 400);
            }

            // Validar cantidades disponibles contra devoluciones previas
            $prev_returns = Returns::where(function($q) use ($customer_sale) {
                    $q->where('cuf', $customer_sale->cuf)
                      ->orWhere('customer_sale_id', $customer_sale->id);
                })
                ->where('is_active', true)
                ->pluck('id');

            $returned_qtys = [];
            if ($prev_returns->count() > 0) {
                $returned_qtys = ProductReturn::whereIn('return_id', $prev_returns)
                    ->groupBy('product_id')
                    ->selectRaw('product_id, SUM(qty) as total_returned')
                    ->pluck('total_returned', 'product_id')
                    ->toArray();
            }

            $product_sales_map = [];
            foreach ($sale->productSales as $ps) {
                $product_sales_map[$ps->product_id] = $ps;
            }

            $list_return = [];
            $montoTotalDevuelto = 0;
            $montoDescuentoCreditoDebito = 0;

            foreach ($items_input as $item_req) {
                $pro_id = (int) ($item_req['product_id'] ?? 0);
                $qty_to_return = (float) ($item_req['qty'] ?? 0);

                if ($qty_to_return <= 0) {
                    continue; // Saltar ítems en 0
                }

                $ps = $product_sales_map[$pro_id] ?? null;
                if (!$ps) {
                    DB::rollBack();
                    return response()->json(['status' => false, 'message' => 'El producto ID ' . $pro_id . ' no pertenece a esta venta.'], 400);
                }

                $original_qty = (float) $ps->qty;
                $already_returned = (float) ($returned_qtys[$pro_id] ?? 0);
                $available_qty = max(0, $original_qty - $already_returned);

                if ($qty_to_return > ($available_qty + 0.0001)) {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => 'La cantidad a devolver (' . $qty_to_return . ') supera la cantidad disponible (' . $available_qty . ') para el ítem.'
                    ], 400);
                }

                $product = Product::find($pro_id);
                $unit_price = (float) $ps->net_unit_price;

                // Descuento proporcional
                $original_discount = (float) $ps->discount;
                $discount_per_unit = $original_qty > 0 ? ($original_discount / $original_qty) : 0;
                $proportional_discount = round($discount_per_unit * $qty_to_return, 2);

                $subtotal_bruto = round($qty_to_return * $unit_price, 2);
                $subtotal_neto = round($subtotal_bruto - $proportional_discount, 2);

                $montoTotalDevuelto += $subtotal_bruto;
                $montoDescuentoCreditoDebito += $proportional_discount;

                $unit_id_siat = 58;
                $unit_name_siat = 'UNIDAD';
                if ($ps->sale_unit_id) {
                    $u = Unit::find($ps->sale_unit_id);
                    if ($u) {
                        $unit_name_siat = $u->unit_name;
                        $unit_id_siat = $u->codigo_clasificador_siat ?? 58;
                    }
                }

                $list_return[] = [
                    'product_id' => $pro_id,
                    'product_code' => $product ? $product->code : 'PROD-' . $pro_id,
                    'product_name' => $product ? $product->name : 'Producto',
                    'cantidad' => $qty_to_return,
                    'qty' => $qty_to_return,
                    'precioUnitario' => $unit_price,
                    'net_unit_price' => $unit_price,
                    'montoDescuento' => $proportional_discount,
                    'discount' => $proportional_discount,
                    'subTotal' => $subtotal_neto,
                    'unidadMedida' => $unit_id_siat,
                    'nombreUnidadMedida' => $unit_name_siat,
                    'sale_unit_id' => $ps->sale_unit_id ?? 0,
                    'tax_rate' => $ps->tax_rate ?? 0,
                    'tax' => round(($subtotal_neto * ($ps->tax_rate ?? 0)) / 100, 2),
                    'total' => $subtotal_neto,
                    'variant_id' => $ps->variant_id ?? null,
                ];
            }

            if (empty($list_return)) {
                DB::rollBack();
                return response()->json(['status' => false, 'message' => 'Debe seleccionar al menos un producto con cantidad mayor a 0 para devolver.'], 400);
            }

            $montoEfectivoCreditoDebito = round($montoTotalDevuelto - $montoDescuentoCreditoDebito, 2);
            $montoIVA = round($montoEfectivoCreditoDebito * 0.13, 2);

            // Obtener cuenta/caja activa para asignar salida de dinero
            $account_id = null;
            if ($sale->biller && $sale->biller->account_id) {
                $account_id = $sale->biller->account_id;
            } else {
                $default_acc = Account::where('is_default', true)->first();
                $account_id = $default_acc ? $default_acc->id : 1;
            }

            // Preparar datos del cliente SIN sobreescribir la tabla customers maestra
            $customer_info = (object) [
                'id' => $sale->customer_id,
                'name' => $customer_sale->razon_social ?: ($sale->customer ? $sale->customer->name : 'N/A'),
                'tipo_documento' => $customer_sale->tipo_documento ?: ($sale->customer ? $sale->customer->tipo_documento : 1),
                'valor_documento' => $customer_sale->valor_documento ?: ($sale->customer ? $sale->customer->valor_documento : ''),
                'complemento_documento' => $customer_sale->complemento_documento ?: '',
                'razon_social' => $customer_sale->razon_social ?: ($sale->customer ? $sale->customer->name : 'SIN NOMBRE'),
                'email' => $customer_sale->email ?: ($sale->customer ? $sale->customer->email : ''),
            ];

            // Caso especial: Factura pagada 100% con Gift Card (montoTotalSujetoIva <= 0.01)
            $factura_original = $this->getFacturaData($customer_sale->cuf) ?? [];
            $monto_gift_card_total = (float) ($factura_original['montoGiftCard'] ?? 0);
            if ($monto_gift_card_total <= 0) {
                foreach ($sale->payments as $payment) {
                    if (in_array($payment->paying_method, ['Tarjeta_Regalo', 'Gift Card'])) {
                        $monto_gift_card_total += (float) $payment->amount;
                    }
                }
            }
            $monto_total_factura = (float) ($factura_original['montoTotal'] ?? $sale->grand_total);
            $monto_sujeto_iva = (float) ($factura_original['montoTotalSujetoIva'] ?? max(0, $monto_total_factura - $monto_gift_card_total));
            $is_gift_card_only = ($monto_gift_card_total > 0 && $monto_sujeto_iva <= 0.01);

            if ($is_gift_card_only) {
                // 100% Gift Card -> No existe IVA que ajustar en SIAT. Se procesa devolución física y se restituye saldo.
                $lims_return_data = Returns::create([
                    'reference_no' => 'DEV-GC-' . date('Ymd') . '-' . date('His'),
                    'user_id' => Auth::id() ?? 1,
                    'customer_id' => $sale->customer_id,
                    'customer_sale_id' => null,
                    'cuf' => $customer_sale->cuf,
                    'warehouse_id' => $sale->warehouse_id,
                    'biller_id' => $sale->biller_id,
                    'account_id' => ($refund_method === 'cash') ? $account_id : null,
                    'item' => count($list_return),
                    'total_qty' => array_sum(array_column($list_return, 'cantidad')),
                    'total_discount' => $montoDescuentoCreditoDebito,
                    'total_tax' => 0,
                    'total_price' => $montoTotalDevuelto,
                    'order_tax_rate' => 0,
                    'order_tax' => 0,
                    'grand_total' => $montoEfectivoCreditoDebito,
                    'return_note' => 'Devolución de venta 100% Gift Card (Sin ajuste fiscal SIAT)',
                    'staff_note' => $staff_note,
                    'is_active' => true,
                ]);

                foreach ($list_return as $ret_item) {
                    ProductReturn::create([
                        'return_id' => $lims_return_data->id,
                        'product_id' => $ret_item['product_id'],
                        'variant_id' => $ret_item['variant_id'],
                        'qty' => $ret_item['cantidad'],
                        'sale_unit_id' => $ret_item['sale_unit_id'],
                        'net_unit_price' => $ret_item['precioUnitario'],
                        'discount' => $ret_item['montoDescuento'],
                        'tax_rate' => 0,
                        'tax' => 0,
                        'total' => $ret_item['subTotal'],
                    ]);

                    if ($adjust_inventory) {
                        $this->reingresarInventario($ret_item, $sale->warehouse_id);
                    }
                }

                // Restituir saldo a la Gift Card
                $this->restituirSaldoGiftCard($sale->id, $montoEfectivoCreditoDebito);

                DB::commit();

                return response()->json([
                    'status' => true,
                    'is_gift_card_only' => true,
                    'message' => 'Devolución de venta con Gift Card completada con éxito. Se restituyó el saldo a la tarjeta. Al ser 100% Gift Card sin crédito fiscal, no requirió emisión de Nota Sector 24 en SIAT.',
                    'return_id' => $lims_return_data->id,
                    'grand_total' => $montoEfectivoCreditoDebito
                ]);
            }

            // Caso Normal / Fiscal: Enviar a SIAT Documento Sector 24
            $prep = $this->prepararNotaCreditoDebito($sale_id, $factura_original, $list_return, $customer_info);
            if (!$prep['status']) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Error al preparar la Nota de Crédito-Débito: ' . ($prep['mensaje'] ?? 'Configuración incompleta.')
                ], 400);
            }

            // Llamada al backend Spring Boot SIAT
            $siat_res = $this->enviarNotaCreditoDebito($prep['data_body']);
            if (!$siat_res['status']) {
                DB::rollBack(); // ATÓMICO: Falló SIAT, se cancela todo en la BD local sin registros huérfanos
                return response()->json([
                    'status' => false,
                    'message' => $siat_res['mensaje'] ?? 'El servidor del SIAT rechazó la Nota de Crédito-Débito.',
                    'siat_error' => $siat_res['error'] ?? null
                ], 400);
            }

            // SIAT Aprobó la emisión -> Registrar CustomerSale Sector 24 y persistir
            $siat_data = $siat_res['data'];
            $meta = $siat_res['meta'] ?? [];
            $nro_nota = $siat_res['correlativo'];

            $obj_nota_fiscal = new CustomerSale();
            $obj_nota_fiscal->sale_id = $sale->id; // ID real de la venta
            $obj_nota_fiscal->customer_id = $sale->customer_id;
            $obj_nota_fiscal->razon_social = $customer_info->razon_social;
            $obj_nota_fiscal->email = $customer_info->email;
            $obj_nota_fiscal->tipo_documento = $customer_info->tipo_documento;
            $obj_nota_fiscal->valor_documento = $customer_info->valor_documento;
            $obj_nota_fiscal->complemento_documento = $customer_info->complemento_documento;
            $obj_nota_fiscal->codigo_excepcion = $customer_sale->codigo_excepcion ?? 0;
            $obj_nota_fiscal->codigo_documento_sector = 24;
            $obj_nota_fiscal->nro_factura = $nro_nota;
            $obj_nota_fiscal->tipo_caso_especial = false;
            $obj_nota_fiscal->estado_factura = "VIGENTE";
            $obj_nota_fiscal->sucursal = $meta['data_p_venta']->sucursal ?? $customer_sale->sucursal;
            $obj_nota_fiscal->codigo_punto_venta = $meta['data_p_venta']->codigo_punto_venta ?? $customer_sale->codigo_punto_venta;
            $obj_nota_fiscal->codigo_cufd = $siat_data['cufd'] ?? ($meta['data_siat_cufd']->codigo_cufd ?? $customer_sale->codigo_cufd);
            $obj_nota_fiscal->cuf = $siat_data['cuf'];
            $obj_nota_fiscal->codigo_recepcion = $siat_data['codigo_recepcion'];
            $obj_nota_fiscal->xml = $siat_data['xmlfactura'] ?? '';
            $obj_nota_fiscal->usuario = Auth::user() ? Auth::user()->name : 'ADMIN';
            $obj_nota_fiscal->save();

            // Crear registro maestro de Devolución
            $lims_return_data = Returns::create([
                'reference_no' => 'NCD-' . date('Ymd') . '-' . date('His'),
                'user_id' => Auth::id() ?? 1,
                'customer_id' => $sale->customer_id,
                'customer_sale_id' => $obj_nota_fiscal->id,
                'cuf' => $customer_sale->cuf, // Referencia al CUF original
                'warehouse_id' => $sale->warehouse_id,
                'biller_id' => $sale->biller_id,
                'account_id' => ($refund_method === 'cash') ? $account_id : null,
                'item' => count($list_return),
                'total_qty' => array_sum(array_column($list_return, 'cantidad')),
                'total_discount' => $montoDescuentoCreditoDebito,
                'total_tax' => $montoIVA,
                'total_price' => $montoTotalDevuelto,
                'order_tax_rate' => 13,
                'order_tax' => $montoIVA,
                'grand_total' => $montoEfectivoCreditoDebito,
                'return_note' => 'Nota de Crédito/Débito SIAT N° ' . $nro_nota . ' (CUF: ' . $siat_data['cuf'] . ')',
                'staff_note' => $staff_note,
                'is_active' => true,
            ]);

            // Crear detalle de productos devueltos
            foreach ($list_return as $ret_item) {
                ProductReturn::create([
                    'return_id' => $lims_return_data->id,
                    'product_id' => $ret_item['product_id'],
                    'variant_id' => $ret_item['variant_id'],
                    'qty' => $ret_item['cantidad'],
                    'sale_unit_id' => $ret_item['sale_unit_id'],
                    'net_unit_price' => $ret_item['precioUnitario'],
                    'discount' => $ret_item['montoDescuento'],
                    'tax_rate' => $ret_item['tax_rate'],
                    'tax' => $ret_item['tax'],
                    'total' => $ret_item['subTotal'],
                ]);

                // Ajuste de inventario si corresponde
                if ($adjust_inventory) {
                    $this->reingresarInventario($ret_item, $sale->warehouse_id);
                }
            }

            // Aplicar método de reembolso
            if ($refund_method === 'customer_balance') {
                $customer = Customer::find($sale->customer_id);
                if ($customer) {
                    $customer->deposit += $montoEfectivoCreditoDebito;
                    $customer->save();

                    Deposit::create([
                        'customer_id' => $customer->id,
                        'user_id' => Auth::id() ?? 1,
                        'amount' => $montoEfectivoCreditoDebito,
                        'note' => 'Abono por Nota de Crédito/Débito N° ' . $nro_nota . ' (Ref: ' . $sale->reference_no . ')'
                    ]);
                }
            } elseif ($refund_method === 'gift_card') {
                $this->restituirSaldoGiftCard($sale->id, $montoEfectivoCreditoDebito);
            }
            // Si es 'cash', ya queda registrado el egreso en Returns asociado a account_id de la caja activa.

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Nota de Crédito-Débito N° ' . $nro_nota . ' emitida exitosamente ante el SIAT.',
                'return_id' => $lims_return_data->id,
                'customer_sale_id' => $obj_nota_fiscal->id,
                'nro_factura' => $nro_nota,
                'cuf' => $obj_nota_fiscal->cuf,
                'monto_total_devuelto' => $montoTotalDevuelto,
                'monto_descuento' => $montoDescuentoCreditoDebito,
                'monto_efectivo' => $montoEfectivoCreditoDebito,
                'monto_iva' => $montoIVA,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('NotaCreditoDebitoController::emitir - Exception: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
            return response()->json([
                'status' => false,
                'message' => 'Error durante la emisión: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reingresa la mercadería devuelta al stock de almacén y producto (maneja variantes y combos).
     */
    protected function reingresarInventario($ret_item, $warehouse_id)
    {
        $product = Product::find($ret_item['product_id']);
        if (!$product) return;

        $qty = (float) $ret_item['cantidad'];
        $sale_unit_id = $ret_item['sale_unit_id'];

        if ($sale_unit_id && $sale_unit_id > 0) {
            $unit = Unit::find($sale_unit_id);
            if ($unit) {
                if ($unit->operator === '*') {
                    $qty = $qty * $unit->operation_value;
                } elseif ($unit->operator === '/') {
                    $qty = $qty / $unit->operation_value;
                }
            }
        }

        if ($product->type == 'combo' && !empty($product->product_list)) {
            $product_list = explode(",", $product->product_list);
            $qty_list = explode(",", $product->qty_list ?? '');
            foreach ($product_list as $index => $child_id) {
                $child_data = Product::find($child_id);
                $ratio = (float) ($qty_list[$index] ?? 1);
                $child_qty = $qty * $ratio;
                if ($child_data && $child_data->unit_id != 0 && $child_data->type != 'digital') {
                    $child_pw = Product_Warehouse::FindProductWithoutVariant($child_id, $warehouse_id)->first();
                    if ($child_pw) {
                        $child_pw->qty += $child_qty;
                        $child_pw->save();
                    }
                    $child_data->qty += $child_qty;
                    $child_data->save();
                }
            }
        } elseif ($product->is_variant && !empty($ret_item['variant_id'])) {
            $product_variant = ProductVariant::where('product_id', $product->id)
                ->where('variant_id', $ret_item['variant_id'])
                ->first();
            if ($product_variant) {
                $product_variant->qty += $qty;
                $product_variant->save();
            }
            $pw = Product_Warehouse::FindProductWithVariant($product->id, $ret_item['variant_id'], $warehouse_id)->first();
            if ($pw) {
                $pw->qty += $qty;
                $pw->save();
            }
        } else {
            $pw = Product_Warehouse::FindProductWithoutVariant($product->id, $warehouse_id)->first();
            if ($pw) {
                $pw->qty += $qty;
                $pw->save();
            }
        }

        $product->qty += $qty;
        $product->save();
    }

    /**
     * Restituye saldo a la tarjeta de regalo si se utilizó en la venta.
     */
    protected function restituirSaldoGiftCard($sale_id, $amount_to_restore)
    {
        $payments = Payment::where('sale_id', $sale_id)
            ->whereIn('paying_method', ['Tarjeta_Regalo', 'Gift Card'])
            ->get();

        $remaining = (float) $amount_to_restore;
        foreach ($payments as $payment) {
            if ($remaining <= 0) break;

            $pwgc = PaymentWithGiftCard::where('payment_id', $payment->id)->first();
            if ($pwgc && $pwgc->gift_card_id) {
                $gift_card = GiftCard::find($pwgc->gift_card_id);
                if ($gift_card) {
                    $restore = min($remaining, (float)$gift_card->expense);
                    $gift_card->expense -= $restore;
                    $gift_card->save();
                    $remaining -= $restore;
                }
            }
        }
    }

    /**
     * Vista de impresión en formato rollo térmico 80mm para la Nota de Crédito-Débito (Sector 24).
     */
    public function print80mm($id)
    {
        $customer_sale = CustomerSale::where(function($q) use ($id) {
            $q->where('id', $id)
              ->orWhere('sale_id', $id);
        })->where('codigo_documento_sector', 24)->orderBy('id', 'desc')->first();

        $return = null;
        if ($customer_sale) {
            $return = Returns::where('customer_sale_id', $customer_sale->id)->first();
        } else {
            $return = Returns::where('id', $id)->orWhere('customer_sale_id', $id)->first();
            if ($return && $return->customer_sale_id) {
                $customer_sale = CustomerSale::find($return->customer_sale_id);
            }
        }

        if (!$customer_sale && !$return) {
            abort(404, 'Nota de Crédito-Débito o Devolución no encontrada.');
        }

        $sale = null;
        if ($customer_sale) {
            $sale = Sale::find($customer_sale->sale_id);
        } elseif ($return && $return->cuf) {
            $orig_cs = CustomerSale::where('cuf', $return->cuf)->first();
            if ($orig_cs) {
                $sale = Sale::find($orig_cs->sale_id);
            }
        }

        $customer = ($customer_sale ? Customer::find($customer_sale->customer_id) : null) 
            ?? ($sale ? $sale->customer : ($return ? Customer::find($return->customer_id) : null));
        $biller = ($sale ? Biller::find($sale->biller_id) : null) 
            ?? ($return ? Biller::find($return->biller_id) : Biller::first());
        $warehouse = ($sale ? Warehouse::find($sale->warehouse_id) : null) 
            ?? ($return ? Warehouse::find($return->warehouse_id) : Warehouse::first());
        $pos_setting = PosSetting::latest()->first() ?? PosSetting::withoutGlobalScope('company')->latest()->first();
        $general_setting = GeneralSetting::latest()->first();

        // Buscar factura original asociada
        $original_customer_sale = null;
        if ($sale) {
            $original_customer_sale = CustomerSale::where('sale_id', $sale->id)
                ->where(function($q) {
                    $q->whereNull('codigo_documento_sector')
                      ->orWhere('codigo_documento_sector', '!=', 24);
                })
                ->first();
        } elseif ($return && $return->cuf) {
            $original_customer_sale = CustomerSale::where('cuf', $return->cuf)->first();
        }

        // Ítems devueltos
        $product_returns = [];
        if ($return) {
            $product_returns = ProductReturn::with('product.unit')
                ->where('return_id', $return->id)
                ->get();
        }

        // Construir URL del código QR SIAT o identificador
        $qr_url = '';
        if ($customer_sale && !empty($customer_sale->cuf)) {
            $is_pilot = strpos($pos_setting->url_operaciones ?? '', 'piloto') !== false;
            $sin_domain = $is_pilot ? 'https://pilotosiat.impuestos.gob.bo' : 'https://siat.impuestos.gob.bo';
            $qr_url = $sin_domain . '/consulta/QR?nit=' . ($pos_setting->nit_emisor ?? '') .
                      '&cuf=' . $customer_sale->cuf .
                      '&numero=' . $customer_sale->nro_factura .
                      '&t=2';
        } elseif ($return) {
            $qr_url = ($pos_setting->nit_emisor ?? '') . '|' . ($return->reference_no ?? '') . '|' . ($return->grand_total ?? '');
        }

        // Literal del monto
        $total_amount = $return ? (float)$return->grand_total : 0;
        $numberToWords = new NumberToWords();
        $numberTransformer = $numberToWords->getNumberTransformer('es');
        $numberInWords = $numberTransformer->toWords(floor($total_amount));
        $cents = round(($total_amount - floor($total_amount)) * 100);
        $cadenaCentavos = str_pad($cents, 2, '0', STR_PAD_LEFT) . '/100 BOLIVIANOS';

        // Leyenda
        $leyenda = 'Ley N° 453: Los servicios deben suministrarse en condiciones de inocuidad, calidad y seguridad.';
        $leyendas_all = SiatLeyendaFactura::all();
        if ($leyendas_all->count() > 0) {
            $leyenda = $this->pickRandomLeyenda($leyendas_all)->descripcion_leyenda ?? $leyenda;
        }

        $is_pdf = false;

        return view('siat.nota_credito_debito_80mm', compact(
            'customer_sale',
            'original_customer_sale',
            'return',
            'sale',
            'customer',
            'biller',
            'warehouse',
            'pos_setting',
            'general_setting',
            'product_returns',
            'qr_url',
            'numberInWords',
            'cadenaCentavos',
            'leyenda',
            'is_pdf'
        ));
    }

    /**
     * Descarga el PDF térmico 80mm de la Nota de Crédito-Débito.
     */
    public function downloadPdf($id)
    {
        $customer_sale = CustomerSale::where(function($q) use ($id) {
            $q->where('id', $id)
              ->orWhere('sale_id', $id);
        })->where('codigo_documento_sector', 24)->orderBy('id', 'desc')->first();

        $return = null;
        if ($customer_sale) {
            $return = Returns::where('customer_sale_id', $customer_sale->id)->first();
        } else {
            $return = Returns::where('id', $id)->orWhere('customer_sale_id', $id)->first();
            if ($return && $return->customer_sale_id) {
                $customer_sale = CustomerSale::find($return->customer_sale_id);
            }
        }

        if (!$customer_sale && !$return) {
            abort(404, 'Nota de Crédito-Débito o Devolución no encontrada.');
        }

        $sale = null;
        if ($customer_sale) {
            $sale = Sale::find($customer_sale->sale_id);
        } elseif ($return && $return->cuf) {
            $orig_cs = CustomerSale::where('cuf', $return->cuf)->first();
            if ($orig_cs) {
                $sale = Sale::find($orig_cs->sale_id);
            }
        }

        $customer = ($customer_sale ? Customer::find($customer_sale->customer_id) : null) 
            ?? ($sale ? $sale->customer : ($return ? Customer::find($return->customer_id) : null));
        $biller = ($sale ? Biller::find($sale->biller_id) : null) 
            ?? ($return ? Biller::find($return->biller_id) : Biller::first());
        $warehouse = ($sale ? Warehouse::find($sale->warehouse_id) : null) 
            ?? ($return ? Warehouse::find($return->warehouse_id) : Warehouse::first());
        $pos_setting = PosSetting::latest()->first() ?? PosSetting::withoutGlobalScope('company')->latest()->first();
        $general_setting = GeneralSetting::latest()->first();

        $original_customer_sale = null;
        if ($sale) {
            $original_customer_sale = CustomerSale::where('sale_id', $sale->id)
                ->where(function($q) {
                    $q->whereNull('codigo_documento_sector')
                      ->orWhere('codigo_documento_sector', '!=', 24);
                })
                ->first();
        } elseif ($return && $return->cuf) {
            $original_customer_sale = CustomerSale::where('cuf', $return->cuf)->first();
        }

        $product_returns = $return ? ProductReturn::with('product.unit')->where('return_id', $return->id)->get() : [];

        $qr_url = '';
        if ($customer_sale && !empty($customer_sale->cuf)) {
            $is_pilot = strpos($pos_setting->url_operaciones ?? '', 'piloto') !== false;
            $sin_domain = $is_pilot ? 'https://pilotosiat.impuestos.gob.bo' : 'https://siat.impuestos.gob.bo';
            $qr_url = $sin_domain . '/consulta/QR?nit=' . ($pos_setting->nit_emisor ?? '') .
                      '&cuf=' . $customer_sale->cuf .
                      '&numero=' . $customer_sale->nro_factura .
                      '&t=2';
        } elseif ($return) {
            $qr_url = ($pos_setting->nit_emisor ?? '') . '|' . ($return->reference_no ?? '') . '|' . ($return->grand_total ?? '');
        }

        $total_amount = $return ? (float)$return->grand_total : 0;
        $numberToWords = new NumberToWords();
        $numberTransformer = $numberToWords->getNumberTransformer('es');
        $numberInWords = $numberTransformer->toWords(floor($total_amount));
        $cents = round(($total_amount - floor($total_amount)) * 100);
        $cadenaCentavos = str_pad($cents, 2, '0', STR_PAD_LEFT) . '/100 BOLIVIANOS';

        $leyenda = 'Ley N° 453: Los servicios deben suministrarse en condiciones de inocuidad, calidad y seguridad.';
        $leyendas_all = SiatLeyendaFactura::all();
        if ($leyendas_all->count() > 0) {
            $leyenda = $this->pickRandomLeyenda($leyendas_all)->descripcion_leyenda ?? $leyenda;
        }

        $is_pdf = true;

        $pdf = Pdf::loadView('siat.nota_credito_debito_80mm', compact(
            'customer_sale',
            'original_customer_sale',
            'return',
            'sale',
            'customer',
            'biller',
            'warehouse',
            'pos_setting',
            'general_setting',
            'product_returns',
            'qr_url',
            'numberInWords',
            'cadenaCentavos',
            'leyenda',
            'is_pdf'
        ))->setPaper([0, 0, 226.77, 650], 'portrait');

        $doc_num = $customer_sale ? $customer_sale->nro_factura : ($return->reference_no ?? $id);
        return $pdf->download('Nota_Credito_Debito_' . $doc_num . '.pdf');
    }
}
