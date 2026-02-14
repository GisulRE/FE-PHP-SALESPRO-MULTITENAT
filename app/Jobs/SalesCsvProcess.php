<?php

namespace App\Jobs;

use App\Account;
use App\Biller;
use App\Category;
use App\Customer;
use App\CustomerSale;
use App\Http\Controllers\CustomerController;
use App\MethodPayment;
use App\Payment;
use App\Product;
use App\Product_Sale;
use App\Sale;
use App\SiatPuntoVenta;
use Auth;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class SalesCsvProcess implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $data;
    public $header;

    public $facturaMasiva;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $header, $facturaMasiva)
    {
        $this->data = $data;
        $this->header = $header;
        $this->facturaMasiva = $facturaMasiva;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data_biller = Biller::where('punto_venta_siat', $this->facturaMasiva->codigo_punto_venta)->where('is_active', true)->first();
        $data_warehouse = $data_biller->warehouse_id;
        $bandera_llenado_datos = false;
        $data_head = [];
        $data_detail = [];
        $list_details = [];

        foreach ($this->data as $key => $val) {
            $product = null;
            $data_row = null;
            if ($key != 0 && $key > 0 && $val[0] != null) {
                $data_row = array_combine($this->header, $val);
                $data_row['montoTotal'] = $this->formatNumber($data_row['montoTotal']);
                $data_row['montoTotalSujetoIva'] = $this->formatNumber($data_row['montoTotalSujetoIva']);
                $data_row['subTotal'] = $this->formatNumber($data_row['subTotal']);
                $data_row['precioUnitario'] = $this->formatNumber($data_row['precioUnitario']);
                $data_row['montoDescuento'] = $this->formatNumber($data_row['montoDescuento']);
                $data_row['montoDescuentoLey1886'] = $this->formatNumber($data_row['montoDescuentoLey1886']);

                $product = Product::where(['code' => $data_row['codigoProducto'], 'is_active' => true])->first();
                if ($product == null) {
                    $data['code'] = $data_row['codigoProducto'];
                    $data['name'] = $data_row['descripcion'];
                    $data['price'] = $data_row['precioUnitario'];
                    $data['category'] = 'ServiciosBasicos';
                    $data['unitcode'] = "C/U";
                    if ($this->facturaMasiva->tipo_factura == "servicio-basico")
                        $data['is_basicservice'] = true;
                    else
                        $data['is_basicservice'] = false;
                    $this->create_product($data_row['codigoProducto'], $data);
                    $product = Product::where(['code' => $data_row['codigoProducto'], 'is_active' => true])->first();
                }
                $customer_data = Customer::where(['valor_documento' => $data_row['numeroDocumento'], 'is_active' => true])->first();
                if ($data_row['montoDescuentoLey1886'] != null || $data_row['montoDescuentoLey1886'] != '') {
                    $data_row['montoDescuentoLey1886'] = abs($data_row['montoDescuentoLey1886']);
                } else {
                    $data_row['montoDescuentoLey1886'] = 0;
                    $data_row['beneficiarioLey1886'] = 0;
                }
                if ($data_row['montoDescuentoTarifaDignidad'] != null || $data_row['montoDescuentoTarifaDignidad'] != '') {
                    $data_row['montoDescuentoTarifaDignidad'] = $this->formatNumber($data_row['montoDescuentoTarifaDignidad']);
                    $data_row['montoDescuentoTarifaDignidad'] = abs($data_row['montoDescuentoTarifaDignidad']);
                } else {
                    $data_row['montoDescuentoTarifaDignidad'] = 0;
                }
                if ($customer_data == null) {
                    $lims_customer_data['customer_group_id'] = 1;
                    $lims_customer_data['name'] = $data_row['nombreRazonSocial'];
                    $lims_customer_data['email'] = $data_row['email'];
                    $lims_customer_data['razon_social'] = $data_row['nombreRazonSocial'];
                    $lims_customer_data['tipo_documento'] = $data_row['codigoTipoDocumentoIdentidad'];
                    $lims_customer_data['valor_documento'] = $data_row['numeroDocumento'];
                    $lims_customer_data['complemento_documento'] = $data_row['complemento'];
                    $lims_customer_data['city'] = $data_row['ciudad'];
                    if ($data_row['domicilio_cliente'] != '') {
                        $lims_customer_data['address'] = $data_row['domicilio_cliente'];
                    } else {
                        $lims_customer_data['address'] = 'S/N';
                    }
                    $lims_customer_data['country'] = 'Bolivia';
                    $lims_customer_data['price_type'] = 0;
                    $lims_customer_data['is_credit'] = false;
                    $lims_customer_data['credit'] = 0;
                    $lims_customer_data['codigofijo'] = $data_row['codigoCliente'];
                    $lims_customer_data['nro_medidor'] = $data_row['numero_medidor'];
                    $lims_customer_data['is_active'] = true;
                    $lims_customer_data['sucursal_id'] = $this->facturaMasiva->sucursal;
                    $customer_data = Customer::create($lims_customer_data);
                }
                if ($val[0] == $nroDocument) {
                    $data_head['item'] += 1;
                    $data_head['total_qty'] += number_format((float) $data_row['cantidad'], 2);
                    /** Detalle Venta */
                    $data_detail['product_id'] = $product->id;
                    $data_detail['qty'] = number_format((float) $data_row['cantidad'], 2);
                    $data_detail['sale_unit_id'] = 0;
                    $data_detail['net_unit_price'] = $data_row['precioUnitario'];
                    $data_detail['discount'] = $data_row['montoDescuento'];
                    $data_detail['tax_rate'] = 0;
                    $data_detail['tax'] = number_format((float) 0, 2);
                    $data_detail['total'] = $data_row['precioUnitario'];
                    $list_details[] = $data_detail;

                    if (isset($this->data[$key + 1]) && $this->data[$key + 1][0] != $val[0]) {
                        /** Cabecera Venta */
                        if ($data_row['montoDescuentoLey1886'] > 0) {
                            $data_head['sale_note'] = "- Descuento Ley 1886 : " . number_format((float) $data_row['montoDescuentoLey1886'], 2);
                        }
                        if ($data_row['montoDescuentoTarifaDignidad'] > 0) {
                            $data_head['sale_note'] = "- Descuento Tarifa Dignidad : " . number_format((float) $data_row['montoDescuentoTarifaDignidad'], 2);
                        }
                        $data_head['user_id'] = Auth::user()->id;
                        $data_head['invoice_no'] = $val[0];
                        $data_head['customer_id'] = $customer_data->id;
                        $data_head['warehouse_id'] = $data_warehouse;
                        $data_head['biller_id'] = $data_biller->id;
                        $data_head['order_discount'] = number_format((float) $data_row['montoDescuentoLey1886'], 2);
                        $data_head['order_discount'] += number_format((float) $data_row['montoDescuentoTarifaDignidad'], 2);
                        $data_head['total_tax'] = 0;
                        $data_head['total_price'] = $data_row['montoTotal'];
                        $data_head['grand_total'] = $data_row['montoTotal'];
                        $data_head['order_tax_rate'] = 0;
                        $data_head['order_tax'] = 0;
                        $data_head['sale_status'] = 1;
                        $data_head['payment_status'] = 4;
                        $data_head['paid_amount'] = $data_row['montoTotal'];
                        $data_head['date_sell'] = date('Y-m-d H:i:s');

                        $lims_sale_data = $this->create_sale($data_head, $list_details);
                        $metodoPago = MethodPayment::where('codigo_clasificador_siat', $data_row['codigoMetodoPago'])->first();
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
                        $data_siat = [];
                        $data_siat['biller_id'] = $lims_sale_data->biller_id;
                        $data_siat['customer_id'] = $lims_sale_data->customer_id;
                        $data_siat['sales_valor_documento'] = $data_row['numeroDocumento'];
                        $data_siat['sales_tipo_documento'] = $data_row['codigoTipoDocumentoIdentidad'];
                        $data_siat['sales_valor_complemento'] = $data_row['complemento'];
                        $data_siat['sales_razon_social'] = $data_row['nombreRazonSocial'];
                        $data_siat['sales_caso_especial'] = 1;
                        $data_siat['sales_email'] = $data_row['email'];
                        $data_siat['number_card'] = null;
                        $data_siat['usuario'] = $data_row['usuario'];

                        if ($this->facturaMasiva->tipo_factura == 'servicio-basico') {
                            if ($data_row['numero_medidor'] == null || $data_row['numero_medidor'] == '')
                                $data_siat['numero_medidor'] = 0;
                            else
                                $data_siat['numero_medidor'] = $data_row['numero_medidor'];

                            $data_siat['gestion'] = $data_row['gestion'];
                            $data_siat['mes'] = $data_row['mes'];
                            $data_siat['ciudad'] = $data_row['ciudad'];
                            $data_siat['zona'] = $data_row['zona'];
                            if ($data_row['domicilio_cliente'] != '') {
                                $data_siat['domicilio_cliente'] = $data_row['domicilio_cliente'];
                            } else {
                                $data_siat['domicilio_cliente'] = 'S/N';
                            }
                            $data_siat['consumo_periodo'] = $data_row['consumoPeriodo'];
                            $data_siat['beneficiario_ley_1886'] = $data_row['beneficiarioLey1886'];
                            $data_siat['monto_descuento_ley_1886'] = $data_row['montoDescuentoLey1886'];
                            $data_siat['monto_descuento_tarifa_dignidad'] = $data_row['montoDescuentoTarifaDignidad'];

                            $data_siat['categoria'] = $data_row['categoria'];
                            $data_siat['lectura_medidor_anterior'] = $data_row['lectant'] . ' ' . $data_row['f_lectAnt'];
                            $data_siat['lectura_medidor_actual'] = $data_row['lectact'] . ' ' . $data_row['f_lectAct'];

                            $data_siat['tasa_aseo'] = $data_row['tasaAseo'];
                            $data_siat['tasa_alumbrado'] = $data_row['tasaAlumbrado'];
                            $data_siat['otras_tasas'] = $data_row['otrasTasas'];

                            $data_siat['ajuste_no_sujeto_iva'] = $data_row['ajusteNoSujetoIva'];
                            $data_siat['detalle_ajuste_no_sujeto_iva'] = $data_row['detalleAjusteNoSujetoIva'];

                            $data_siat['ajuste_sujeto_iva'] = $data_row['ajusteSujetoIva'];
                            $data_siat['detalle_ajuste_sujeto_iva'] = $data_row['detalleAjusteSujetoIva'];

                            $data_siat['otros_pagos_no_sujeto_iva'] = $data_row['otrosPagosNoSujetoIva'];
                            // $data_siat['detalle_otros_pagos_no_sujeto_iva'] = $data_row['detalleOtrosPagosNoSujetoIva'];
                        }
                        $this->create_customersale($lims_sale_data->id, $data_siat, $this->facturaMasiva);
                        $bandera_llenado_datos = true;
                        $cant_paquetes++;
                        $data_head = [];
                        $data_detail = [];
                        $list_details = [];
                        $data_siat = [];
                        $data = [];
                    }
                } else {
                    $data_head['item'] = 1;
                    $data_head['total_qty'] = number_format((float) $data_row['cantidad'], 2);

                    /** Detalle Venta */
                    $data_detail['sale_id'] = null;
                    $data_detail['product_id'] = $product->id;
                    $data_detail['qty'] = number_format((float) $data_row['cantidad'], 2);
                    $data_detail['sale_unit_id'] = 0;
                    $data_detail['net_unit_price'] = $data_row['precioUnitario'];
                    $data_detail['discount'] = $data_row['montoDescuento'];
                    $data_detail['tax_rate'] = 0;
                    $data_detail['tax'] = number_format((float) 0, 2);
                    $data_detail['total'] = $data_row['precioUnitario'];
                    $list_details[] = $data_detail;
                    if (isset($this->data[$key + 1]) && $this->data[$key + 1][0] != $val[0]) {
                        /** Cabecera Venta */
                        if ($data_row['montoDescuentoLey1886'] > 0) {
                            $data_head['sale_note'] = "- Descuento Ley 1886 : " . number_format((float) $data_row['montoDescuentoLey1886'], 2);
                        }
                        if ($data_row['montoDescuentoTarifaDignidad'] > 0) {
                            $data_head['sale_note'] = "- Descuento Tarifa Dignidad : " . number_format((float) $data_row['montoDescuentoTarifaDignidad'], 2);
                        }
                        $data_head['user_id'] = Auth::user()->id;
                        $data_head['invoice_no'] = $val[0];
                        $data_head['customer_id'] = $customer_data->id;
                        $data_head['warehouse_id'] = $data_warehouse;
                        $data_head['biller_id'] = $data_biller->id;
                        $data_head['order_discount'] = number_format((float) $data_row['montoDescuentoLey1886'], 2);
                        $data_head['order_discount'] += number_format((float) $data_row['montoDescuentoTarifaDignidad'], 2);
                        $data_head['total_tax'] = 0;
                        $data_head['total_price'] = $data_row['montoTotal'];
                        $data_head['grand_total'] = $data_row['montoTotal'];
                        $data_head['order_tax_rate'] = 0;
                        $data_head['order_tax'] = 0;
                        $data_head['sale_status'] = 1;
                        $data_head['payment_status'] = 4;
                        $data_head['paid_amount'] = $data_row['montoTotal'];
                        $data_head['date_sell'] = date('Y-m-d H:i:s');

                        $lims_sale_data = $this->create_sale($data_head, $list_details);
                        $metodoPago = MethodPayment::where('codigo_clasificador_siat', $data_row['codigoMetodoPago'])->first();
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
                        $data_siat = [];
                        $data_siat['biller_id'] = $lims_sale_data->biller_id;
                        $data_siat['customer_id'] = $lims_sale_data->customer_id;
                        $data_siat['sales_valor_documento'] = $data_row['numeroDocumento'];
                        $data_siat['sales_tipo_documento'] = $data_row['codigoTipoDocumentoIdentidad'];
                        $data_siat['sales_valor_complemento'] = $data_row['complemento'];
                        $data_siat['sales_razon_social'] = $data_row['nombreRazonSocial'];
                        $data_siat['sales_caso_especial'] = 1;
                        if ($data_row['email'] != null || $data_row['email'] == '') {
                            $data_siat['sales_email'] = $data_row['email'];
                        } else {
                            $data_siat['sales_email'] = 'soporte@gisul.com.bo';
                        }
                        $data_siat['number_card'] = null;
                        $data_siat['usuario'] = $data_row['usuario'];

                        if ($this->facturaMasiva->tipo_factura == 'servicio-basico') {
                            if ($data_row['numero_medidor'] == null || $data_row['numero_medidor'] == '')
                                $data_siat['numero_medidor'] = 0;
                            else
                                $data_siat['numero_medidor'] = $data_row['numero_medidor'];

                            $data_siat['gestion'] = $data_row['gestion'];
                            $data_siat['mes'] = $data_row['mes'];
                            $data_siat['ciudad'] = $data_row['ciudad'];
                            $data_siat['zona'] = $data_row['zona'];
                            $data_siat['domicilio_cliente'] = $data_row['domicilio_cliente'];
                            $data_siat['consumo_periodo'] = $data_row['consumoPeriodo'];
                            $data_siat['beneficiario_ley_1886'] = $data_row['beneficiarioLey1886'];
                            $data_siat['monto_descuento_ley_1886'] = $data_row['montoDescuentoLey1886'];
                            $data_siat['monto_descuento_tarifa_dignidad'] = $data_row['montoDescuentoTarifaDignidad'];

                            $data_siat['categoria'] = $data_row['categoria'];
                            $data_siat['lectura_medidor_anterior'] = $data_row['lectant'] . ' ' . $data_row['f_lectAnt'];
                            $data_siat['lectura_medidor_actual'] = $data_row['lectact'] . ' ' . $data_row['f_lectAct'];

                            $data_siat['tasa_aseo'] = $data_row['tasaAseo'];
                            $data_siat['tasa_alumbrado'] = $data_row['tasaAlumbrado'];
                            $data_siat['otras_tasas'] = $data_row['otrasTasas'];

                            $data_siat['ajuste_no_sujeto_iva'] = $data_row['ajusteNoSujetoIva'];
                            $data_siat['detalle_ajuste_no_sujeto_iva'] = $data_row['detalleAjusteNoSujetoIva'];

                            $data_siat['ajuste_sujeto_iva'] = $data_row['ajusteSujetoIva'];
                            $data_siat['detalle_ajuste_sujeto_iva'] = $data_row['detalleAjusteSujetoIva'];

                            $data_siat['otros_pagos_no_sujeto_iva'] = $data_row['otrosPagosNoSujetoIva'];
                            // $data_siat['detalle_otros_pagos_no_sujeto_iva'] = $data_row['detalleOtrosPagosNoSujetoIva'];
                        }
                        $this->create_customersale($lims_sale_data->id, $data_siat, $this->facturaMasiva);
                        $bandera_llenado_datos = true;
                        $cant_paquetes++;
                        $data_head = [];
                        $data_detail = [];
                        $list_details = [];
                        $data_siat = [];
                        $data = [];
                    }
                }
                $nroDocument = $val[0];
            }
        }
    }

    private function create_sale(array $dataHead, array $dataDetails)
    {
        $last_ref = Sale::get()->last();
        if ($last_ref != null) {
            $nros = explode("-", $last_ref['reference_no']);
            $nro = ltrim($nros[1], "0");
            $nro++;
            $nro = str_pad($nro, 8, "0", STR_PAD_LEFT);
        } else {
            $nro = str_pad(1, 8, "0", STR_PAD_LEFT);
        }
        $dataHead['reference_no'] = 'NVR-' . $nro;
        $lims_sale_data = Sale::create($dataHead);
        //$total = 0;
        foreach ($dataDetails as $key => $value) {
            $value['sale_id'] = $lims_sale_data->id;
            //$total =  $total + $value['total'];
            Product_Sale::create($value);
        }
        /*$lims_sale_data->grand_total = $total;
        $lims_sale_data->total_price = $total;
        $lims_sale_data->paid_amount = $total;
        $lims_sale_data->save();
        log::info("Venta Masiva Nro: ".$lims_sale_data->reference_no);
        log::info($dataHead);
        log::info($dataDetails);*/
        return $lims_sale_data;
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

    private function create_customersale(int $idSale, array $data, $dataFacturaMasiva)
    {
        $customer = new CustomerController();
        $codigo_excepcion = 0;
        if ($data['sales_tipo_documento'] == 5) {
            $result = $customer->verificarNIT($data['sales_valor_documento']);
            if ($result != null && $result['codigo'] == 994) {
                $codigo_excepcion = 0;
            } else {
                $codigo_excepcion = 1;
            }
        }

        $obj_cliente = new CustomerSale();
        $obj_cliente->sale_id = $idSale;
        $obj_cliente->customer_id = $data['customer_id'];
        $obj_cliente->razon_social = $data['sales_razon_social'];
        $obj_cliente->email = $data['sales_email'];
        $obj_cliente->tipo_documento = $data['sales_tipo_documento'];
        $obj_cliente->valor_documento = $data['sales_valor_documento'];
        $obj_cliente->complemento_documento = $data['sales_valor_complemento'];
        $obj_cliente->codigo_excepcion = $codigo_excepcion;
        $obj_cliente->codigo_documento_sector = $dataFacturaMasiva->codigo_documento_sector;
        $obj_cliente->usuario = $data['usuario'];

        if ($dataFacturaMasiva->tipo_factura == 'servicio-basico') {
            $obj_cliente->glosa_periodo_facturado = $data['mes'] . ' / ' . $data['gestion'];
            $obj_cliente->numero_medidor = $data['numero_medidor'];
            $obj_cliente->gestion = $data['gestion'];
            $obj_cliente->mes = $data['mes'];
            $obj_cliente->ciudad = $data['ciudad'];
            $obj_cliente->zona = $data['zona'];
            $obj_cliente->domicilio_cliente = $data['domicilio_cliente'];
            $obj_cliente->consumo_periodo = $data['consumo_periodo'];
            $obj_cliente->beneficiario_ley_1886 = $data['beneficiario_ley_1886'];
            $obj_cliente->monto_descuento_ley_1886 = $data['monto_descuento_ley_1886'];
            $obj_cliente->monto_descuento_tarifa_dignidad = $data['monto_descuento_tarifa_dignidad'];

            $obj_cliente->categoria = $data['categoria'];
            $obj_cliente->lectura_medidor_anterior = $data['lectura_medidor_anterior'];
            $obj_cliente->lectura_medidor_actual = $data['lectura_medidor_actual'];

            $obj_cliente->tasa_aseo = $data['tasa_aseo'];
            $obj_cliente->tasa_alumbrado = $data['tasa_alumbrado'];
            $obj_cliente->otras_tasas = $data['otras_tasas'];

            $obj_cliente->ajuste_no_sujeto_iva = $data['ajuste_no_sujeto_iva'];
            $obj_cliente->detalle_ajuste_no_sujeto_iva = $data['detalle_ajuste_no_sujeto_iva'];

            $obj_cliente->ajuste_sujeto_iva = $data['ajuste_sujeto_iva'];
            $obj_cliente->detalle_ajuste_sujeto_iva = $data['detalle_ajuste_sujeto_iva'];

            $obj_cliente->otros_pagos_no_sujeto_iva = $data['otros_pagos_no_sujeto_iva'];
            // $obj_cliente->detalle_otros_pagos_no_sujeto_iva = $data['detalle_otros_pagos_no_sujeto_iva'];
        }

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
        $data_p_venta = SiatPuntoVenta::where('codigo_punto_venta', $data_biller->punto_venta_siat)->first();
        $update_p_venta = SiatPuntoVenta::where('codigo_punto_venta', $data_p_venta->codigo_punto_venta)->where('sucursal', $data_p_venta->sucursal)->first();

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
        $obj_cliente->tipo_caso_especial = $data['sales_caso_especial'];
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

    private function formatNumber($number, $scale = 2)
    {
        //(float) $number = str_replace('.', '', $number);
        (float) $number = str_replace(',', '', $number);
        return $number;
    }

}