<?php

namespace App\Http\Controllers;

use App\Account;
use App\Biller;
use App\Category;
use App\Customer;
use App\CustomerSale;
use App\FacturaMasiva;
use App\FacturaMasivaPaquetes;
use App\Http\Controllers\CustomerController;
use App\Http\Traits\CufdTrait;
use App\Http\Traits\SiatTrait;
use App\MethodPayment;
use App\Payment;
use App\PosSetting;
use App\Product;
use App\Product_Sale;
use App\Sale;
use App\SaleImporTemp;
use App\SiatPuntoVenta;
use App\SiatSucursal;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Log;
use DB;
use Spatie\Permission\Models\Role;

class FacturaMasivaController extends Controller
{
    use SiatTrait;
    use CufdTrait;

    const CANT_MAX_PAQUETE = 5;

    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('facturamasiva_siat')) {
            $fecha_actual = new Carbon();
            $list_f_masivas = FacturaMasiva::orderBy('fecha_inicio', 'DESC')->get();
            $sucursales = SiatSucursal::where('estado', true)->get();

            return view('factura-masiva.index', [
                'list_f_masivas' => $list_f_masivas,
                'fecha_actual' => $fecha_actual,
                'sucursales' => $sucursales,
            ]);
        } else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $data_p_venta = SiatPuntoVenta::where('codigo_punto_venta', $data['codigo_punto_venta'])->where('sucursal', $data['sucursal'])->first();
            if ($data['tipo_factura'] == 'servicio-basico') {
                $data['codigo_documento_sector'] = 13;
                $data['estado'] = 'EN_PROCESO';
            }
            if ($data['tipo_factura'] == 'compra-venta') {
                $data['codigo_documento_sector'] = 1;
                $data['estado'] = 'EVENTO_REGISTRADO';
            }
            if ($data['tipo_factura'] == 'alquiler') {
                $data['codigo_documento_sector'] = 2;
                $data['estado'] = 'EVENTO_REGISTRADO';
            }

            $data['cuis'] = $data_p_venta->codigo_cuis;
            $data['created_by'] = Auth::user()->id;
            $data_factura_masiva = FacturaMasiva::create($data);

            if ($data_factura_masiva->tipo_factura == 'compra-venta' || $data_factura_masiva->tipo_factura == 'alquiler') {
                // generar Paquete del determinado punto de venta
                $this->generarPaquete($data_factura_masiva->id);
            }

            $mensaje = 'Factura Masiva registrada correctamente! ';
            log::info($mensaje);
        } catch (\Throwable $th) {
            $mensaje = 'Error, no se logró registrar la Factura Masiva.';
            log::warning($mensaje);
            log::warning($th);
        }
        Session::flash('info', $mensaje);
        return redirect('factura-masiva');
    }

    public function show($id = null)
    {

    }

    // Procedimiento para cargar masivamente las facturas y guardarlas en la BD en orden general de Encabezado, Detalle y Datos del Cliente
    public function cargarArchivo($id)
    {
        // PROCEDIMIENTO POR REALIZAR
        $status = false;
        try {
            $dataFacturaMasiva = FacturaMasiva::find($id);
            Log::info("Iniciando Registro Factura Masiva");
            $sheet_data = SaleImporTemp::where('facturamasiva_id', $dataFacturaMasiva->id)->groupBy('NRO_FACT')->get();
            // DEBE GUARDARSE COMO VENTA + DETALLE + DATOS DEL CLIENTE
            $cant_paquetes = 0;
            $cant_ventas = 0;
            // El biller pertenece según el encabezado de FacturaMasiva
            $data_biller = Biller::where([['punto_venta_siat', $dataFacturaMasiva->codigo_punto_venta], ['sucursal', $dataFacturaMasiva->sucursal]])->first();
            $data_warehouse = $data_biller->warehouse_id;
            $bandera_llenado_datos = false;
            $data_head = [];
            $data_detail = [];
            Log::info("FacturaMasiva => " . $dataFacturaMasiva->id);
            foreach ($sheet_data as $key => $sheet) {
                /** Obtener o Crear Cliente */
                if ($sheet->codigoCliente != null)
                    $customer_data = Customer::where(['codigofijo' => $sheet->codigoCliente, 'sucursal_id' => $dataFacturaMasiva->sucursal, 'is_active' => true])->first();
                else
                    $customer_data = Customer::where(['valor_documento' => $sheet->numeroDocumento, 'sucursal_id' => $dataFacturaMasiva->sucursal, 'is_active' => true])->first();

                if ($customer_data == null) {
                    $lims_customer_data['customer_group_id'] = 1;
                    $lims_customer_data['name'] = $sheet->nombreRazonSocial;
                    $lims_customer_data['email'] = $sheet->email;
                    $lims_customer_data['razon_social'] = $sheet->nombreRazonSocial;
                    $lims_customer_data['tipo_documento'] = $sheet->codigoTipoDocumentoIdentidad;
                    $lims_customer_data['valor_documento'] = $sheet->numeroDocumento;
                    $lims_customer_data['complemento_documento'] = $sheet->complemento;
                    $lims_customer_data['city'] = $sheet->ciudad;
                    if ($sheet->domicilio_cliente != '') {
                        $lims_customer_data['address'] = $sheet->domicilio_cliente;
                    } else {
                        $lims_customer_data['address'] = 'S/N';
                    }
                    $lims_customer_data['country'] = 'Bolivia';
                    $lims_customer_data['price_type'] = 0;
                    $lims_customer_data['is_credit'] = false;
                    $lims_customer_data['credit'] = 0;
                    $lims_customer_data['codigofijo'] = $sheet->codigoCliente;
                    $lims_customer_data['nro_medidor'] = $sheet->numero_medidor;
                    $lims_customer_data['is_active'] = true;
                    $lims_customer_data['sucursal_id'] = $dataFacturaMasiva->sucursal;
                    $customer_data = Customer::create($lims_customer_data);
                    Log::info("Cliente Nuevo Registrado => " . $customer_data->id);

                } else {
                    if (
                        $customer_data->razon_social != $sheet->nombreRazonSocial
                        || $customer_data->valor_documento != $sheet->numeroDocumento
                    ) {
                        $customer_data->name = $sheet->nombreRazonSocial;
                        $customer_data->valor_documento = $sheet->numeroDocumento;
                        $customer_data->razon_social = $sheet->nombreRazonSocial;
                        $customer_data->tipo_documento = $sheet->codigoTipoDocumentoIdentidad;
                        $customer_data->save();
                        Log::info("Cliente Existente Actualizado => " . $customer_data->id);
                    }
                }
                /** Fin Obtener o Crear Cliente */

                if ($sheet->montoDescuentoLey1886 != null || $sheet->montoDescuentoLey1886 != '') {
                    $sheet->montoDescuentoLey1886 = abs($sheet->montoDescuentoLey1886);
                } else {
                    $sheet->montoDescuentoLey1886 = 0;
                    $sheet->beneficiarioLey1886 = 0;
                }
                if ($sheet->montoDescuentoTarifaDignidad != null || $sheet->montoDescuentoTarifaDignidad != '') {
                    $sheet->montoDescuentoTarifaDignidad = $this->formatNumber($sheet->montoDescuentoTarifaDignidad);
                    $sheet->montoDescuentoTarifaDignidad = abs($sheet->montoDescuentoTarifaDignidad);
                } else {
                    $sheet->montoDescuentoTarifaDignidad = 0;
                }
                /** Crear Cabecera Venta */
                $data_head = [];
                $data_head['user_id'] = Auth::user()->id;
                $data_head['invoice_no'] = $sheet->NRO_FACT;
                $data_head['customer_id'] = $customer_data->id;
                $data_head['warehouse_id'] = $data_warehouse;
                $data_head['biller_id'] = $data_biller->id;
                $data_head['order_discount'] = number_format((float) $sheet->montoDescuentoLey1886, 2);
                $data_head['order_discount'] += number_format((float) $sheet->montoDescuentoTarifaDignidad, 2);
                $data_head['total_tax'] = 0;
                $data_head['total_price'] = number_format((float) $sheet->montoTotal, 2, '.', '');
                $data_head['grand_total'] = number_format((float) $sheet->montoTotal, 2, '.', '');
                $data_head['order_tax_rate'] = 0;
                $data_head['order_tax'] = 0;
                $data_head['sale_status'] = 1;
                $data_head['payment_status'] = 4;
                $data_head['paid_amount'] = number_format((float) $sheet->montoTotal, 2, '.', '');
                $data_head['date_sell'] = date('Y-m-d H:i:s');
                $data_head['total_qty'] = 0;
                $data_head['item'] = 0;
                $data_head['sale_note'] = '';
                if ($sheet->montoDescuentoLey1886 > 0) {
                    $data_head['sale_note'] .= "- Descuento Ley 1886 : " . number_format((float) $sheet->montoDescuentoLey1886, 2);
                }
                if ($sheet->montoDescuentoTarifaDignidad > 0) {
                    $data_head['sale_note'] .= "- Descuento Tarifa Dignidad : " . number_format((float) $sheet->montoDescuentoTarifaDignidad, 2);
                }
                $last_ref = Sale::get()->last();
                if ($last_ref != null) {
                    $nros = explode("-", $last_ref['reference_no']);
                    $nro = ltrim($nros[1], "0");
                    $nro++;
                    $nro = str_pad($nro, 8, "0", STR_PAD_LEFT);
                } else {
                    $nro = str_pad(1, 8, "0", STR_PAD_LEFT);
                }
                $data_head['reference_no'] = 'NVR-' . $nro;
                $lims_sale_data = Sale::create($data_head);
                /** Fin Cabecera Venta */
                $metodoPago = MethodPayment::where('codigo_clasificador_siat', $sheet->codigoMetodoPago)->first();
                $paying_method = $metodoPago->name;
                switch ($metodoPago->name) {
                    case 'Tarjeta Credito/Debito':
                        $paying_method = "Tarjeta_Credito_Debito";
                        break;
                    case 'Qr Simple':
                        $paying_method = "Qr_Simple";
                        break;

                }
                $this->payment_sale($lims_sale_data, $paying_method);
                /** Crear CustomerSale */
                $data_siat = [];
                $data_siat['biller_id'] = $lims_sale_data->biller_id;

                $obj_cliente = new CustomerSale();
                $obj_cliente->sale_id = $lims_sale_data->id;
                $obj_cliente->customer_id = $lims_sale_data->customer_id;
                $obj_cliente->codigofijo = $sheet->codigoCliente;
                $obj_cliente->razon_social = $sheet->nombreRazonSocial;

                $obj_cliente->tipo_documento = $sheet->codigoTipoDocumentoIdentidad;
                $obj_cliente->valor_documento = $sheet->numeroDocumento;
                $obj_cliente->complemento_documento = $sheet->complemento;
                $obj_cliente->codigo_documento_sector = $dataFacturaMasiva->codigo_documento_sector;
                $obj_cliente->usuario = $sheet->usuario;
                $obj_cliente->tipo_caso_especial = 1;
                if ($sheet->email != null || $sheet->email == '') {
                    $obj_cliente->email = $sheet->email;
                } else {
                    $obj_cliente->email = $data_biller->email;
                }
                $data_siat['number_card'] = $sheet->numeroTarjeta;

                if ($dataFacturaMasiva->tipo_factura == 'servicio-basico') {

                    $obj_cliente->numero_medidor = $sheet->numero_medidor;
                    $obj_cliente->glosa_periodo_facturado = $sheet->mes . ' / ' . $sheet->gestion;
                    $obj_cliente->gestion = $sheet->gestion;
                    $obj_cliente->mes = $sheet->mes;
                    $obj_cliente->ciudad = $sheet->ciudad;
                    $obj_cliente->zona = $sheet->zona;
                    $obj_cliente->domicilio_cliente = $sheet->domicilio_cliente;
                    $obj_cliente->consumo_periodo = $sheet->consumoPeriodo;
                    $obj_cliente->beneficiario_ley_1886 = $sheet->beneficiarioLey1886;
                    $obj_cliente->monto_descuento_ley_1886 = $sheet->montoDescuentoLey1886;
                    $obj_cliente->monto_descuento_tarifa_dignidad = $sheet->montoDescuentoTarifaDignidad;

                    $obj_cliente->categoria = $sheet->categoria;
                    $obj_cliente->lectura_medidor_anterior = $sheet->lectant . ' ' . $sheet->f_lectAnt;
                    $obj_cliente->lectura_medidor_actual = $sheet->lectact . ' ' . $sheet->f_lectAct;

                    $obj_cliente->tasa_aseo = $sheet->tasaAseo;
                    $obj_cliente->tasa_alumbrado = $sheet->tasaAlumbrado;
                    $obj_cliente->otras_tasas = $sheet->otrasTasas;
                    $obj_cliente->ajuste_no_sujeto_iva = $sheet->ajusteNoSujetoIva;
                    $obj_cliente->detalle_ajuste_no_sujeto_iva = $sheet->detalleAjusteNoSujetoIva;
                    $obj_cliente->ajuste_sujeto_iva = $sheet->ajusteSujetoIva;
                    $obj_cliente->detalle_ajuste_sujeto_iva = $sheet->detalleAjusteSujetoIva;
                    $obj_cliente->otros_pagos_no_sujeto_iva = $sheet->otrosPagosNoSujetoIva;
                    // $data_siat['detalle_otros_pagos_no_sujeto_iva'] = $sheet->detalleOtrosPagosNoSujetoIva;
                }
                $this->create_customersale($lims_sale_data->id, $data_siat, $obj_cliente);

                /** Obtener Detalles Venta */
                $list_venta = SaleImporTemp::where([['facturamasiva_id', $dataFacturaMasiva->id], ['NRO_FACT', $sheet->NRO_FACT]])->get();

                foreach ($list_venta as $key => $data_row) {
                    $product = null;
                    /** Obtener o Crear Producto */
                    $product = Product::where(['code' => $data_row->codigoProducto, 'is_active' => true])->first();
                    if ($product == null) {
                        $data['code'] = $data_row->codigoProducto;
                        $data['name'] = $data_row->descripcion;
                        $data['price'] = $data_row->precioUnitario;
                        $data['category'] = 'ServiciosBasicos';
                        $data['unitcode'] = "C/U";
                        if ($dataFacturaMasiva->tipo_factura == "servicio-basico")
                            $data['is_basicservice'] = true;
                        else
                            $data['is_basicservice'] = false;
                        $this->create_product($data_row->codigoProducto, $data);
                        $product = Product::where(['code' => $data_row->codigoProducto, 'is_active' => true])->first();
                    }
                    /** Fin Obtener o Crear Producto */

                    /** Actualiza Cabecera Venta */
                    $lims_sale_data->item += 1;
                    $lims_sale_data->total_qty += number_format((float) $data_row->cantidad, 2);
                    $lims_sale_data->save();

                    /** Crear Detalle Venta */
                    $data_detail['sale_id'] = $lims_sale_data->id;
                    $data_detail['product_id'] = $product->id;
                    $data_detail['qty'] = number_format((float) $data_row->cantidad, 2);
                    $data_detail['sale_unit_id'] = 0;
                    $data_detail['net_unit_price'] = number_format((float) $data_row->precioUnitario, 2, '.', '');
                    $data_detail['discount'] = number_format((float) $data_row->montoDescuento, 2);
                    $data_detail['tax_rate'] = 0;
                    $data_detail['tax'] = number_format((float) 0, 2);
                    $data_detail['total'] = number_format((float) $data_row->subTotal, 2, '.', '');
                    Product_Sale::create($data_detail);
                    /** Fin Detalle Venta */
                    $cant_paquetes++;
                    $data_head = [];
                    $data_detail = [];
                    $data = [];
                    $lims_customer_data = [];
                }
                SaleImporTemp::where([['facturamasiva_id', $dataFacturaMasiva->id], ['NRO_FACT', $sheet->NRO_FACT]])->delete();
                $bandera_llenado_datos = true;
                $cant_ventas++;
                $data_head = [];
                $data_detail = [];
                $data_siat = [];
                $data = [];
            }

            // creacion del detalle paquete
            if ($bandera_llenado_datos == true) {
                $dataFacturaMasiva->estado = "EVENTO_REGISTRADO";
                $dataFacturaMasiva->cantidad_paquetes = $cant_paquetes;
                $dataFacturaMasiva->save();

                $this->generarPaquete($dataFacturaMasiva->id);
                $status = true;
                $mensaje = 'Archivo cargado correctamente, Se registraron ' . $cant_ventas . ' ventas';
                Log::info($mensaje);
                Log::info("Finalizo Registro Factura Masiva");
            }
            $result = array(
                'message' => $mensaje,
                'totalprocess' => $cant_paquetes,
                'status' => $status
            );
            return json_encode($result);
        } catch (\Throwable $th) {
            Log::error("Error Factura Masiva => " . $th);
            SaleImporTemp::where('facturamasiva_id', $dataFacturaMasiva->id)->delete();
            $mensaje = "Error: " . $th->getMessage();
            $result = array(
                'message' => $mensaje,
                'status' => $status
            );
            return json_encode($result);
        }
    }
    private function create_product(string $code, array $data)
    {
        $lims_category_data = Category::firstOrCreate(['name' => $data['category'], 'is_active' => true]);
        // $lims_unit_data = Unit::firstOrCreate(['unit_code' => $data['unitcode'], 'is_active' => true]);
        $lims_unit_data = 0;

        $product = Product::firstOrNew(['code' => $code, 'is_active' => true]);
        $product->image = 'zummXD2dvAtI.png';
        $product->name = $data['name'];
        $product->code = $data['code'];
        $product->type = strtolower('Digital');
        $product->barcode_symbology = 'C128';
        $product->brand_id = null;
        $product->category_id = $lims_category_data->id;
        $product->unit_id = $lims_unit_data;
        $product->purchase_unit_id = $lims_unit_data;
        $product->sale_unit_id = $lims_unit_data;
        $product->cost = 0;
        $product->price = $data['price'];
        $product->tax_method = 1;
        $product->qty = 0;
        $product->product_details = $data['name'];
        $product->is_active = true;
        $product->is_pricelist = false;
        $product->is_basicservice = $data['is_basicservice'];
        $product->codigo_actividad = $lims_category_data->codigo_actividad;
        $product->codigo_producto_servicio = $lims_category_data->codigo_producto_servicio;

        $product->save();
        return $product;
    }

    private function create_customersale(int $idSale, array $data, $obj_cliente)
    {
        $customer = new CustomerController();
        $codigo_excepcion = 0;
        /*if ($obj_cliente->tipo_documento == 5) {
            $result = $customer->verificarNIT($obj_cliente->valor_documento);
            if (isset($result['status']) && $result['status'] == false) {
                $codigo_excepcion = 0;
            } else {
                if ($result != null && $result['codigo'] == 994) {
                    $codigo_excepcion = 1;
                } else {
                    $codigo_excepcion = 0;
                }
            }

        }*/
        $obj_cliente->codigo_excepcion = $codigo_excepcion;

        // En caso de tarjeta de crédito/débito se procede enmascarar.
        if ($data['number_card'] != null) {
            $nro_tarjeta = Str::of($data['number_card'])->replaceMatches('/[^A-Za-z0-9]++/', '');
            $primeros_cuatro = Str::substr($nro_tarjeta, 0, 4);
            $relleno = "00000000";
            $ultimos_cuatro = Str::substr($nro_tarjeta, 12, 4);
            $nro_completo = $primeros_cuatro . $relleno . $ultimos_cuatro;

            $obj_cliente->numero_tarjeta_credito_debito = $nro_completo;
        }
        // Setear nro. factura y aumentamos el correlativo
        $data_biller = Biller::where('id', $data['biller_id'])->first();
        $data_p_venta = SiatPuntoVenta::where([['codigo_punto_venta', $data_biller->punto_venta_siat], ['sucursal', $data_biller->sucursal]])->first();
        $update_p_venta = SiatPuntoVenta::where([['codigo_punto_venta', $data_biller->punto_venta_siat], ['sucursal', $data_biller->sucursal]])->first();

        if ($obj_cliente->codigo_documento_sector == 1) {
            $obj_cliente->nro_factura = $data_p_venta->correlativo_factura;
            $update_p_venta->correlativo_factura += 1;
        }
        if ($obj_cliente->codigo_documento_sector == 2) {
            $obj_cliente->nro_factura = $data_p_venta->correlativo_alquiler;
            $update_p_venta->correlativo_alquiler += 1;
        }
        if ($obj_cliente->codigo_documento_sector == 13) {
            $obj_cliente->nro_factura = $data_p_venta->correlativo_servicios_basicos;
            $update_p_venta->correlativo_servicios_basicos += 1;
        }

        $update_p_venta->save();
        // fin de correlativo factura
        $obj_cliente->tipo_metodo_pago = 1;
        $obj_cliente->estado_factura = "MASIVO";
        $obj_cliente->sucursal = $data_p_venta->sucursal;
        $obj_cliente->codigo_punto_venta = $data_p_venta->codigo_punto_venta;
        $obj_cliente->save();
    }

    private function payment_sale($sale, $paying_method)
    {
        $lims_account_data = Account::where('is_default', true)->first();
        $lims_payment_data = new Payment();
        $lims_payment_data->user_id = Auth::id();
        $lims_payment_data->account_id = $lims_account_data->id;
        $lims_payment_data->sale_id = $sale->id;
        $data['payment_reference'] = 'spr-' . date("Ymd") . '-' . date("his");
        $lims_payment_data->payment_reference = $data['payment_reference'];
        $lims_payment_data->amount = $sale->paid_amount;
        $lims_payment_data->change = 0;
        $lims_payment_data->paying_method = $paying_method;
        $lims_payment_data->payment_note = "";
        $lims_payment_data->save();
        return $lims_payment_data;
    }

    public function generarPaquete($id)
    {
        $pos_setting = PosSetting::latest()->first();
        $cant_max_paquete = $pos_setting->cant_max_masiva;
        $data_factura_masiva = FacturaMasiva::where('id', $id)->first();

        $ventas = $count_ventas = CustomerSale::where('sucursal', $data_factura_masiva->sucursal)->where('codigo_punto_venta', $data_factura_masiva->codigo_punto_venta)->where('estado_factura', 'MASIVO')->where('codigo_documento_sector', $data_factura_masiva->codigo_documento_sector)->get();

        $cant_paquetes = ceil(($count_ventas->count() / $cant_max_paquete));

        $data_factura_masiva->cantidad_paquetes = $cant_paquetes;
        $data_factura_masiva->save();
        // si la cantidad de ventas excede el máximo, se procede en otro paquete
        for ($i = 1; $i <= $cant_paquetes; $i++) {

            // toma las primeras ventas 
            $paquete = $ventas->take($cant_max_paquete);

            // guardando control_contingencia_detalle por paquete 
            $obj_detalle_paquete = new FacturaMasivaPaquetes();
            $obj_detalle_paquete->factura_masiva_id = $id;
            $obj_detalle_paquete->cantidad_ventas = $paquete->count();
            $obj_detalle_paquete->glosa_nro_factura_inicio_a_fin = 'Paquete ' . $i . ' | Del ' . $paquete->min('nro_factura') . ' - Al: ' . $paquete->max('nro_factura');
            if ($paquete->count() == 1) {
                $obj_detalle_paquete->glosa_nro_factura_inicio_a_fin = 'Paquete ' . $i . ' | Del ' . $paquete->min('nro_factura');
            }

            $obj_detalle_paquete->arreglo_ventas = $paquete->mapToGroups(function ($item, $key) {
                return [$item['estado_factura'] => $item['sale_id']];
            });

            $obj_detalle_paquete->estado = 'PENDIENTE';
            $obj_detalle_paquete->save();

            // corta las lista de ventas
            $ventas = $ventas->splice($cant_max_paquete);
        }

    }

    public function obtenerPaquetes($id)
    {
        $paquetes = FacturaMasivaPaquetes::where('factura_masiva_id', $id)->get();
        return $paquetes;
    }

    public function cerrarFacturaMasiva($id)
    {
        $data_factura_masiva = FacturaMasiva::where('id', $id)->first();
        $data_factura_masiva->fecha_fin = new Carbon();
        $data_factura_masiva->estado = 'CERRADO';
        $data_factura_masiva->save();

        $msj = 'Factura Masiva cerrada! ';
        Session::flash('success', $msj);
        return redirect('factura-masiva');
    }

    public function validarDatosPaquete($factura_masiva_paquete_id)
    {
        $data_factura_masiva = FacturaMasivaPaquetes::where('id', $factura_masiva_paquete_id)->first();

        $listas = json_decode($data_factura_masiva->arreglo_ventas, true);
        $listas = $listas['MASIVO'];
        $respuesta = array();
        $customerSales = CustomerSale::whereIn('sale_id', $listas)->where('tipo_documento', 5)->get();
        Log::info("Factura Masiva ID: " . $data_factura_masiva->factura_masiva_id . " |Paquete ID: " . $data_factura_masiva->id . " |Total NITs a verificar: " . sizeof($customerSales));
        foreach ($customerSales as $key => $c_venta) {
            $data_venta = Sale::where('id', $c_venta->sale_id)->first();
            $response = $this->getResponseNIT($c_venta->valor_documento);
            $mensajes = $response['MENSAJES'];
            foreach ($mensajes as $key => $value) {
                if ($value['codigo'] == 994) {
                    $respuesta[] = array('sale_id' => $c_venta->sale_id, 'tipo_documento' => $c_venta->tipo_documento, 'valor_documento' => $c_venta->valor_documento, 'reference_no' => $data_venta->reference_no, 'grand_total' => $data_venta->grand_total, 'estado' => $c_venta->codigo_excepcion);
                }
            }
        }
        Log::info("Se verifico los NITs.");
        return $respuesta;
    }

    // Botón Paso UNO, crear el archivo CSV, y lo envía 
    public function enviarPaqueteFacturaMasiva($factura_masiva_paquete_id)
    {
        $data_factura_masiva_paquete = FacturaMasivaPaquetes::where('id', $factura_masiva_paquete_id)->first();
        $data_factura_masiva = FacturaMasiva::where('id', $data_factura_masiva_paquete->factura_masiva_id)->first();

        $listas = json_decode($data_factura_masiva_paquete->arreglo_ventas, true);

        $this->crearArchivoCsvFacturaMasiva($listas, $data_factura_masiva); // SiatTrait
        $estado_paquete = $this->facturaMasivaRecepcion($factura_masiva_paquete_id); // SiatTrait
        // return 'Hola mundo';

        return $estado_paquete;
    }

    private function formatNumber($number, $scale = 2)
    {
        //(float) $number = str_replace('.', '', $number);
        (float) $number = str_replace(',', '', $number);
        return $number;
    }

    // Función prevent (vista index de modoMasivo)
    public function verificarArchivoExcel(Request $request)
    {
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
        $error_message = "" . "\n";
        // Obteniendo cabezera 
        $escapedHeader = $sheet_data[0];
        $header_template = array(
            0 => 'NRO_FACT',
            1 => 'codigoSucursal',
            2 => 'nombreRazonSocial',
            3 => 'codigoTipoDocumentoIdentidad',
            4 => 'numeroDocumento',
            5 => 'complemento',
            6 => 'codigoCliente',
            7 => 'mes',
            8 => 'gestion',
            9 => 'ciudad',
            10 => 'zona',
            11 => 'numero_medidor',
            12 => 'domicilio_cliente',
            13 => 'codigoMetodoPago',
            14 => 'numeroTarjeta',
            15 => 'montoTotal',
            16 => 'montoTotalSujetoIva',
            17 => 'consumoPeriodo',
            18 => 'beneficiarioLey1886',
            19 => 'montoDescuentoLey1886',
            20 => 'montoDescuentoTarifaDignidad',
            21 => 'tasaAseo',
            22 => 'tasaAlumbrado',
            23 => 'otrasTasas',
            24 => 'ajusteNoSujetoIva',
            25 => 'detalleAjusteNoSujetoIva',
            26 => 'ajusteSujetoIva',
            27 => 'detalleAjusteSujetoIva',
            28 => 'otrosPagosNoSujetoIva',
            29 => 'descuentoAdicional',
            30 => 'codigoExcepcion',
            31 => 'cafc',
            32 => 'usuario',
            33 => 'codigoProducto',
            34 => 'descripcion',
            35 => 'cantidad',
            36 => 'precioUnitario',
            37 => 'montoDescuento',
            38 => 'subTotal',
            39 => 'categoria',
            40 => 'email',
            41 => 'lectant',
            42 => 'f_lectAnt',
            43 => 'lectact',
            44 => 'f_lectAct'
        );
        $dataFacturaMasiva = FacturaMasiva::find($dataRequest['idfacturamasiva']);
        Log::info("Iniciando Verificacion Factura Masiva");
        Log::info("FacturaMasiva => " . $dataFacturaMasiva->id);
        // Obteniendo Datos de Punto de Venta
        $data_biller = Biller::where([['punto_venta_siat', $dataFacturaMasiva->codigo_punto_venta], ['sucursal', $dataFacturaMasiva->sucursal]])->first();
        $resultCompare = array_diff($escapedHeader, $header_template);
        if (sizeof($resultCompare) > 0) {
            $error_message .= "Error en Validar Estructura de la Cabecera del Archivo, Revise el Documento en Fila Nro: " . 0 . " | " . "\n";
            foreach ($resultCompare as $error) {
                $error_message .= "Error en Validar la columna: " . $error . " no valida,  Revise el Documento y Corrija la estructura en Fila Nro: " . 0 . " | " . "\n";
            }
            $result = array(
                'message' => $error_message,
                'totalreq' => 0,
                'totalprocess' => 1,
                'status' => false
            );
            return json_encode($result);
        }

        $nroDocument = null;
        $subtotal = null;
        $contador_error = 0;
        $total_rows = sizeof($sheet_data);
        $montoDescuentoLey1886 = 0;
        $montoDescuentoTarifaDignidad = 0;
        try {
            DB::beginTransaction();
            foreach ($sheet_data as $key => $val) {
                if ($key != 0 && $key > 0 && $val[0] != null) {
                    $data_row = array_combine($escapedHeader, $val);
                    if ($data_row['nombreRazonSocial'] == null || $data_row['nombreRazonSocial'] == '') {
                        $key++;
                        $contador_error++;
                        $error_message .= "Error en Validar razon social en factura, Revise el Documento en Fila Nro: " . $key . " | " . "\n";
                    }
                    if ($data_row['numeroDocumento'] == null || $data_row['numeroDocumento'] == '' || $data_row['numeroDocumento'] <= 0) {
                        $key++;
                        $contador_error++;
                        $error_message .= "Error en Validar numero de documento en factura, Revise el Documento en Fila Nro: " . $key . " | " . "\n";
                    }
                    $data_row['codigoSucursal'] = '' . $data_row['codigoSucursal'];
                    if ($data_row['codigoSucursal'] == null || $data_row['codigoSucursal'] == '') {
                        $key++;
                        $contador_error++;
                        $error_message .= "Error en Validar numero de sucursal nulo o vacio, Revise el Documento en Fila Nro: " . $key . " | " . "\n";
                    } else if ($data_biller->sucursal != $data_row['codigoSucursal']) {
                        $key++;
                        $contador_error++;
                        $error_message .= "Error en Validar numero de sucursal no coincide con el sistema, Revise el Documento en Fila Nro: " . $key . " | " . "\n";
                    }
                    $data_row['cantidad'] = $this->formatNumber($data_row['cantidad']);
                    $data_row['montoTotalSujetoIva'] = $this->formatNumber($data_row['montoTotalSujetoIva']);
                    $data_row['montoTotal'] = $this->formatNumber($data_row['montoTotal']);
                    $data_row['precioUnitario'] = $this->formatNumber($data_row['precioUnitario']);
                    $data_row['montoDescuento'] = $this->formatNumber($data_row['montoDescuento']);
                    $data_row['subTotal'] = $this->formatNumber($data_row['subTotal']);
                    $data_row['montoDescuentoLey1886'] = $this->formatNumber($data_row['montoDescuentoLey1886']);
                    $data_row['montoDescuentoTarifaDignidad'] = $this->formatNumber($data_row['montoDescuentoTarifaDignidad']);
                    $data_row['tasaAseo'] = $this->formatNumber($data_row['tasaAseo']);
                    $data_row['tasaAlumbrado'] = $this->formatNumber($data_row['tasaAlumbrado']);
                    $data_row['otrasTasas'] = $this->formatNumber($data_row['otrasTasas']);

                    /*** Control montoDescuentoLey1886  */
                    if ($data_row['montoDescuentoLey1886'] != null || $data_row['montoDescuentoLey1886'] != '') {
                        $data_row['montoDescuentoLey1886'] = $this->formatNumber($data_row['montoDescuentoLey1886']);
                        $data_row['montoDescuentoLey1886'] = abs($data_row['montoDescuentoLey1886']);
                    } else {
                        $data_row['montoDescuentoLey1886'] = 0;
                    }
                    /*** Control montoDescuentoTarifaDignidad  */
                    if ($data_row['montoDescuentoTarifaDignidad'] != null || $data_row['montoDescuentoTarifaDignidad'] != '') {
                        $data_row['montoDescuentoTarifaDignidad'] = $this->formatNumber($data_row['montoDescuentoTarifaDignidad']);
                        $data_row['montoDescuentoTarifaDignidad'] = abs($data_row['montoDescuentoTarifaDignidad']);
                    } else {
                        $data_row['montoDescuentoTarifaDignidad'] = 0;
                    }
                    if ($data_row['precioUnitario'] <= 0) {
                        $key++;
                        $contador_error++;
                        $error_message .= "Error en Validar precioUnitario en factura (No se permite monto: 0.00), Revise el Documento en Fila Nro: " . $key . " | " . "\n";
                    }
                    if ($data_row['subTotal'] <= 0) {
                        $key++;
                        $contador_error++;
                        $error_message .= "Error en Validar subTotal en factura (No se permite monto: 0.00), Revise el Documento en Fila Nro: " . $key . " | " . "\n";
                    }
                    if ($data_row['montoTotal'] <= 0) {
                        $key++;
                        $contador_error++;
                        $error_message .= "Error en Validar monto total en factura (No se permite monto: 0.00), Revise el Documento en Fila Nro: " . $key . " | " . "\n";
                    }
                    if ($val[0] == $nroDocument) {
                        $subtotal = bcadd((float) $subtotal, (float) $data_row['subTotal'], 2);
                        if (isset($sheet_data[$key + 1]) && $sheet_data[$key + 1][0] != $val[0]) {
                            if ((float) $data_row['montoTotal'] != (float) $subtotal) {
                                $key++;
                                $contador_error++;
                                $error_message .= "Error en Validar un monto total de Una factura, Revise el Documento en Fila Nro: " . $key . " | " . "\n";
                            }
                            if ($data_row['montoDescuentoLey1886'] != $montoDescuentoLey1886) {
                                $key++;
                                $contador_error++;
                                $error_message .= "Error en Validar un monto total por diferencia de montoDescuentoLey1886 de Una factura, Revise el Documento en Fila Nro: " . $key . " | " . "\n";
                            }
                            if ($data_row['montoDescuentoTarifaDignidad'] != $montoDescuentoTarifaDignidad) {
                                $key++;
                                $contador_error++;
                                $error_message .= "Error en Validar un monto total por diferencia de montoDescuentoTarifaDignidad de Una factura, Revise el Documento en Fila Nro: " . $key . " | " . "\n";
                            }
                        }
                    } else {
                        $subtotal = 0;
                        $subtotal = bcadd((float) $subtotal, (float) $data_row['subTotal'], 2);
                        $subtotal = $subtotal - abs($data_row['montoDescuentoLey1886']);
                        $subtotal = $subtotal - abs($data_row['montoDescuentoTarifaDignidad']);
                        $montoDescuentoLey1886 = $data_row['montoDescuentoLey1886'];
                        $montoDescuentoTarifaDignidad = $data_row['montoDescuentoTarifaDignidad'];
                        if (isset($sheet_data[$key + 1]) && $sheet_data[$key + 1][0] != $val[0]) {
                            if ((float) $data_row['montoTotal'] != (float) $subtotal) {
                                $key++;
                                $contador_error++;
                                $error_message .= "Error en Validar un monto total de Una factura, Revise el Documento en Fila Nro: " . $key . " | " . "\n";
                            }
                        }
                    }
                    if ($data_row['numero_medidor'] == null || $data_row['numero_medidor'] == '') {
                        $data_row['numero_medidor'] = 0;
                    }
                    $data_row['facturamasiva_id'] = $dataFacturaMasiva->id;

                    SaleImporTemp::create($data_row);
                    $nroDocument = $val[0];
                }
            }
            DB::commit();
        } catch (\Throwable $th) {
            Log::error("Error Factura Masiva => " . $th);
            DB::rollBack();
            $error_message = "Error: " . $th->getMessage();
        }

        Log::info("Finalizo Verificacion Factura Masiva");
        $status = false;
        unset($reader);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        if ($contador_error > 0) {
            SaleImporTemp::where('facturamasiva_id', $dataFacturaMasiva->id)->delete();
            $message = $error_message;
            Log::warning("FacturaMasiva => " . $message);
            $status = false;
        } else {
            $sheet_data = [];
            $message = "Verificado correctamente";
            Log::info("FacturaMasiva => " . $message);
            $status = true;
            return $this->cargarArchivo($dataFacturaMasiva->id);
        }

        $result = array(
            'message' => $message,
            'totalreq' => $contador_error,
            'totalprocess' => $total_rows,
            'status' => $status
        );
        return json_encode($result);
    }


    public function verificarEstadoPaqueteEnviado($factura_masiva_paquete_id)
    {
        $response = $this->verificarEstadoPaqueteMasiva($factura_masiva_paquete_id);
        log::info(json_encode($response));
        if ($response->status() == 200) {
            $data_estado = $response->json();
        }
        if ($response->status() == 400) {
            $data = $response->json();
            return $data['title'];
        }

        // en caso esté validada se procede a cambiar el estado de las ventas facturadas de contingencia => facturadas; y el paquete contingencia se actualiza
        if ($data_estado['codigo_descripcion'] == 'VALIDADA') {
            $data_factura_masiva_paquete = FacturaMasivaPaquetes::where('id', $factura_masiva_paquete_id)->first();
            // $this->obtenerVentasEnviadasEnPaquetesModoMasivo($factura_masiva_paquete_id); // SiatTrait
            $data_factura_masiva_paquete->estado = 'VALIDADA';
            $data_factura_masiva_paquete->respuesta_servicio = '908';
            $data_factura_masiva_paquete->save();
        }

        if ($data_estado['codigo_descripcion'] == 'OBSERVADA') {
            $data_factura_masiva_paquete = FacturaMasivaPaquetes::where('id', $factura_masiva_paquete_id)->first();
            $data_factura_masiva_paquete->estado = 'OBSERVADA';
            $data_factura_masiva_paquete->respuesta_servicio = '904';
            $data_factura_masiva_paquete->log_errores = $data_estado['mensajes_recepcion'];
            $mensajes_error = $data_estado['mensajes_recepcion'];
            log::warning("El paquete ha sido observada => " . json_encode($mensajes_error));
            $data_factura_masiva_paquete->save();
        }

        return $data_estado['codigo_descripcion'];
    }

    public function obtenerArregloVentasxPaquete($factura_masiva_paquete_id)
    {
        $respuesta = $this->obtenerVentasEnviadasEnPaquetesModoMasivo($factura_masiva_paquete_id); // SiatTrait
        return $respuesta;
    }

    public function obtenerArregloVentasxPaqueteAnular($factura_masiva_paquete_id)
    {
        $respuesta = $this->obtenerVentasEnviadasEnPaquetesModoMasivo($factura_masiva_paquete_id); // SiatTrait
        return $respuesta;
    }


    public function confirmarExcepcionTodosNit(Request $request)
    {
        $data = $request->all();
        $lista_venta = $data['arreglo_ventas'];

        Log::info("confirmarExcepcionTodosNit(), los números de ventas a confirmar son: " . json_encode($lista_venta));
        foreach ($lista_venta as $venta) {
            $data_cliente = CustomerSale::where('sale_id', $venta)->first();
            $data_cliente->codigo_excepcion = 1;
            $data_cliente->save();
        }
        return 'Se ha confirmado los códigos excepción correctamente';
    }

    public function confirmarExcepcionNit($sale_id)
    {
        try {
            $data_cliente = CustomerSale::where('sale_id', $sale_id)->first();
            $data_cliente->codigo_excepcion = 1;
            $data_cliente->save();
            $mensaje = 'Código Excepción actualizado correctamente! ';
        } catch (\Throwable $th) {
            $mensaje = 'Error no se logró realizar la operación! venta => ' + $sale_id;
            log::warning($mensaje);
            log::warning($th);
        }
        return $mensaje;
    }

    public function obtenerLogsErrores($id)
    {
        $paquete = FacturaMasivaPaquetes::where('id', $id)->first();

        return $paquete;
    }

    public function validarNuevaFacturaMasiva($id_sucursal, $id_puntoVenta)
    {
        $facturasMasivas = FacturaMasiva::where([['sucursal', $id_sucursal], ['codigo_punto_venta', $id_puntoVenta], ['estado', '!=', 'CERRADO']])->first();
        if ($facturasMasivas) {
            return array("estado" => true);
        } else {
            return array("estado" => false);
        }
    }

}