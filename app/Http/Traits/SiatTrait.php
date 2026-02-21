<?php

namespace App\Http\Traits;

use App\Biller;
use App\ControlContingencia;
use App\ControlContingenciaPaquetes;
use App\CredencialCafc;
use App\CustomerSale;
use App\FacturaMasiva;
use App\FacturaMasivaPaquetes;
use App\Payment;
use App\PosSetting;
use App\Product;
use App\Sale;
// PhpSpreadsheet para adjuntar archivos CSV
use App\SiatActividadEconomica;
use App\SiatCufd;
use App\SiatDocumentoSector;
use App\SiatLeyendaFactura;
use App\SiatParametricaVario;
use App\SiatProductoServicio;
use App\SiatPuntoVenta;
use App\SiatSucursal;
use App\Tax;
use App\Unit;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

trait SiatTrait
{

    public function getToken()
    {
        $pos_setting = PosSetting::latest()->first();
        $user_siat = $pos_setting->user_siat;
        $pass_siat = $pos_setting->pass_siat;
        $url_siat = $pos_setting->url_siat;

        if ($user_siat && $pass_siat && $url_siat) {
            try {
                $response = Http::timeout(3)->post($url_siat . '/TokenRest/v1/token', [
                    'dataPassword' => $pass_siat,
                    'dataUser' => $user_siat,
                ]);
            } catch (\Throwable $th) {
                // Log del error pero NO bloquea el login
                Log::warning('SIAT: No se pudo conectar durante login - ' . $th->getMessage());
                Session::put('auth_siat', false);
                return;
            }

            Session::put('auth_siat', true);
            //entre 200 y 299
            if ($response->successful()) {
                $token_siat = $response->json();
                Session::put('token_siat', $token_siat['token']);
                return;
            }
            //error >500
            if ($response->serverError()) {
                // Log del error pero NO bloquea el login
                Log::warning('SIAT: Error del servidor durante login');
                Session::put('auth_siat', false);
                return;
            }
            //error >400
            if ($response->clientError()) {
                // Log del error pero NO bloquea el login
                Log::warning('SIAT: Credenciales inválidas durante login');
                Session::put('auth_siat', false);
                return;
            }
        } else {
            Session::put('auth_siat', false);
        }
        return;
    }

    //retorna el response
    public function getResponse(string $operacion, $sucursal_id, $p_venta, $cuis, $nit)
    {
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_operaciones;
        $path = '/sincronizacion/sincronizacion';
        $punto_venta = '?codigoPuntoVenta=' . $p_venta;
        $sucursal = '&codigoSucursal=' . $sucursal_id;
        $codigo_cuis = '&cuis=' . $cuis;
        $nit_emisor = '&nit=' . $nit;
        $ope = '&operacion=' . $operacion;
        $query = $punto_venta . $sucursal . $codigo_cuis . $nit_emisor . $ope;
        
        Log::info("SINCRONIZACION SIAT - " . strtoupper($operacion));
        Log::info("URL => " . $host . $path . $query);
        
        try {
            $response = Http::withHeaders([
                'Authorization' => $bearer,
            ])->post($host . $path . $query);
            
            $http_status = $response->status();
            
            //entre 200 y 299
            if ($response->successful()) {
                $status = $response->json();
                Log::info("Response => " . json_encode($status, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                return $status;
            }
            //error >500
            if ($response->serverError()) {
                Log::error("HTTP Status: " . $http_status . " - ERROR DEL SERVIDOR (5xx)");
                Log::error("Response => " . $response->body());
                $msj = 'Problemas de conexión Siat';
                Session::flash('warning', $msj);
                return;
            }
            //error >400
            if ($response->clientError()) {
                Log::error("HTTP Status: " . $http_status . " - ERROR DEL CLIENTE (4xx)");
                Log::error("Response => " . $response->body());
                $msj = 'Error | Credenciales inválidas';
                Session::flash('warning', $msj);
                return;
            }
        } catch (Exception $e) {
            Log::error("ERROR EN SINCRONIZACIÓN: " . $e->getMessage());
            Session::flash('warning', $e->getMessage());
            return;
        }
    }

    //Consulta la operación, y busca la tabla determinada
    public function llenarTablaxOperacion($operacion, $response, $sucursal, $p_venta)
    {
        try {
            $textOpe = $this->textOperacion($operacion);
            if ($textOpe == null) {
                return redirect()->route('siat_panel.log_siat')->with('warning', 'Operación no encontrada');
            }

            if ($textOpe === "ACTIVIDADES") {
                $this->setActividadEconomica($textOpe, $response, $sucursal, $p_venta);
                return;
            }
            if ($textOpe === "ACTIVIDADES_DOCUMENTO_SECTOR") {
                $this->setDocumentoSector($textOpe, $response, $sucursal, $p_venta);
                return;
            }
            if ($textOpe === "PRODUCTOS") {
                $this->setProductosServicios($textOpe, $response, $sucursal, $p_venta);
                return;
            }
            if ($textOpe === "LEYENDAS_FACTURA") {
                $this->setLeyendasFactura($textOpe, $response, $sucursal, $p_venta);
                return;
            }
            if ($textOpe) {
                $this->setParametricaVarios($operacion, $response, $sucursal, $p_venta);
                return;
            }
        } catch (\Excepcion $e) {
            return redirect()->route('siat_panel.log_siat')->with('warning', 'Operación no encontrada');
        }
    }
    //insertar todo lo relacionado a Actividad Económica
    public function setActividadEconomica($textOpe, $response, $sucursal, $p_venta)
    {

        try {
            //Obtener solo los datos de la operacion "ACTIVIDADES"
            $items = $response[$textOpe];
            //Variables 
            $user = Auth::user()->id;

            //Consultar si hay tuplas con Sucursal-PuntoVenta, y proceder a eliminarlas
            $arreglos = SiatActividadEconomica::where('sucursal', $sucursal)->where('codigo_punto_venta', $p_venta)->get()->each->delete();

            //Insertar los datos en función de Sucursal - PuntoVenta
            foreach ($items as $item) {
                $obj = new SiatActividadEconomica();
                $obj->codigo_caeb = $item['codigoCaeb'];
                $obj->descripcion = $item['descripcion'];
                $obj->tipo_actividad = $item['tipoActividad'];
                $obj->usuario_alta = $user;
                $obj->sucursal = $sucursal;
                $obj->codigo_punto_venta = $p_venta;
                $obj->save();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    //insertar todo lo relacionado a Documento Sector
    public function setDocumentoSector($textOpe, $response, $sucursal, $p_venta)
    {
        try {
            //Obtener solo los datos de la operacion "ACTIVIDADES_DOCUMENTO_SECTOR"
            $items = $response[$textOpe];
            //Variables 
            $user = Auth::user()->id;

            //Consultar si hay tuplas con Sucursal-PuntoVenta, y proceder a eliminarlas
            $arreglos = SiatDocumentoSector::where('sucursal', $sucursal)->where('codigo_punto_venta', $p_venta)->get()->each->delete();

            //Insertar los datos en función de Sucursal - PuntoVenta
            foreach ($items as $item) {
                $obj = new SiatDocumentoSector();

                $obj->codigo_actividad = $item['codigoActividad'];
                $obj->codigo_documento_sector = $item['codigoDocumentoSector'];
                $obj->tipo_documento_sector = $item['tipoDocumentoSector'];
                $obj->usuario_alta = $user;
                $obj->sucursal = $sucursal;
                $obj->codigo_punto_venta = $p_venta;
                $obj->save();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
    //insertar todo lo relacionado a Productos/Servicios
    public function setProductosServicios($textOpe, $response, $sucursal, $p_venta)
    {
        try {
            //Obtener solo los datos de la operacion "PRODUCTOS"
            $items = $response[$textOpe];
            //Variables 
            $user = Auth::user()->id;

            //Consultar si hay tuplas con Sucursal-PuntoVenta, y proceder a eliminarlas
            $arreglos = SiatProductoServicio::where('sucursal', $sucursal)->where('codigo_punto_venta', $p_venta)->get()->each->delete();
            //Insertar los datos en función de Sucursal - PuntoVenta
            foreach ($items as $item) {
                $obj = new SiatProductoServicio();

                $obj->codigo_actividad = $item['codigoActividad'];
                $obj->codigo_producto = $item['codigoProducto'];
                $obj->descripcion_producto = $item['descripcionProducto'];
                $obj->usuario_alta = $user;
                $obj->sucursal = $sucursal;
                $obj->codigo_punto_venta = $p_venta;
                $obj->save();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
    //insertar todo lo relacionado a Leyendas de Factura
    public function setLeyendasFactura($textOpe, $response, $sucursal, $p_venta)
    {
        try {
            //Obtener solo los datos de la operacion "LEYENDAS_FACTURA"
            $items = $response[$textOpe];
            //Variables 
            $user = Auth::user()->id;

            //Consultar si hay tuplas con Sucursal-PuntoVenta, y proceder a eliminarlas
            $arreglos = SiatLeyendaFactura::where('sucursal', $sucursal)->where('codigo_punto_venta', $p_venta)->get()->each->delete();
            //Insertar los datos en función de Sucursal - PuntoVenta
            foreach ($items as $item) {
                $obj = new SiatLeyendaFactura();

                $obj->codigo_actividad = $item['codigoActividad'];
                $obj->descripcion_leyenda = $item['descripcionLeyenda'];
                $obj->usuario_alta = $user;
                $obj->sucursal = $sucursal;
                $obj->codigo_punto_venta = $p_venta;
                $obj->save();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    //insertar todo lo relacionado a Paramétricas Varios 
    public function setParametricaVarios($operacion, $response, $sucursal, $p_venta)
    {
        try {
            //Obtener solo los datos de la operacion. 
            $textOpe = $this->textOperacion($operacion);
            $items = $response[$textOpe];
            //Variables 
            $user = Auth::user()->id;

            //Consultar si hay tuplas con Sucursal-PuntoVenta, y proceder a eliminarlas
            $arreglos = SiatParametricaVario::where('sucursal', $sucursal)->where('codigo_punto_venta', $p_venta)->where('tipo_clasificador', $operacion)->get()->each->delete();
            //Insertar los datos en función de Sucursal - PuntoVenta
            foreach ($items as $item) {
                $obj = new SiatParametricaVario();

                $obj->codigo_clasificador = $item['codigoClasificador'];
                $obj->descripcion = $item['descripcion'];
                $obj->tipo_clasificador = $operacion;
                $obj->usuario_alta = $user;
                $obj->usuario_modificacion = $user;
                $obj->sucursal = $sucursal;
                $obj->codigo_punto_venta = $p_venta;
                $obj->save();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    //permite obtener el response a Impuestos.  
    public function getResponseNIT($consultarNIT)
    {
        //{{Url}}/obtencion.codigos/verifica.nit?cuis={{cuis}}&nit=388615026&nitParaVerificacion=469255901&sucursal={{sucursal}}
        $pos_setting = PosSetting::latest()->first();
        $p_venta = SiatPuntoVenta::oldest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_operaciones;
        $path = '/obtencion.codigos/verifica.nit';
        $codigo_cuis = '?cuis=' . $p_venta->codigo_cuis;
        $nit_emisor = '&nit=' . $pos_setting->nit_emisor;
        $nit_consulta = '&nitParaVerificacion=' . $consultarNIT;
        $sucursal = '&sucursal=' . $p_venta->sucursal;
        $query = $codigo_cuis . $nit_emisor . $nit_consulta . $sucursal;

        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->get($host . $path . $query);
        //entre 200 y 299
        if ($response->successful()) {
            $status = $response->json();
            return $status;
        }
    }


    function textOperacion(string $ope)
    {
        if ($ope === "actividades") {
            return "ACTIVIDADES";
        }
        if ($ope === "actividadesDocumentoSector") {
            return "ACTIVIDADES_DOCUMENTO_SECTOR";
        }
        if ($ope === "productosServicios") {
            return "PRODUCTOS";
        }
        if ($ope === "leyendasFactura") {
            return "LEYENDAS_FACTURA";
        }
        // Operaciones paramétricas
        if ($ope === "tipoDocumentoIdentidad") {
            return "DOCUMENTO_IDENTIDAD";
        }
        if ($ope === "mensajesServicios") {
            return "MENSAJES_SERVICIOS";
        }
        if ($ope === "eventosSignificativos") {
            return "EVENTOS_SIGNIFICATIVOS";
        }
        if ($ope === "motivoAnulacion") {
            return "MOTIVO_ANULACION";
        }
        if ($ope === "paisOrigen") {
            return "PAIS_ORIGEN";
        }
        if ($ope === "tipoDocumentoSector") {
            return "DOCUMENTO_SECTOR";
        }
        if ($ope === "tipoEmision") {
            return "TIPO_EMISION";
        }
        if ($ope === "tipoHabitacion") {
            return "TIPO_HABITACION";
        }
        if ($ope === "tipoMetodoPago") {
            return "TIPO_METODO_PAGO";
        }
        if ($ope === "tipoMoneda") {
            return "TIPO_MONEDA";
        }
        if ($ope === "puntoVenta") {
            return "PUNTO_VENTA";
        }
        if ($ope === "tipoFactura") {
            return "TIPO_FACTURA";
        }
        if ($ope === "unidadMedida") {
            return "UNIDAD_MEDIDA";
        }
        return null;
    }


    public function generarFacturaIndividual($id)
    {
        // $venta_id = 314;
        $venta_id = $id;
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_operaciones;
        $path = '/factura.venta/factura.individual';

        Log::info("FACTURACION INDIVIDUAL ESTANDAR - Venta ID: " . $id);

        $data_venta = Sale::where('id', $venta_id)->first();
        $data_biller = Biller::where('id', $data_venta->biller_id)->first();
        $data_p_venta = SiatPuntoVenta::where([
            'sucursal' => $data_biller->sucursal,
            'codigo_punto_venta' => $data_biller->punto_venta_siat
        ])->first();
        $data_siat_cufd = SiatCufd::where('sucursal', $data_p_venta->sucursal)->where('codigo_punto_venta', $data_p_venta->codigo_punto_venta)->where('estado', true)->orderBy('fecha_registro', 'desc')->first();
        
        $data_sucursal = SiatSucursal::where('sucursal', $data_p_venta->sucursal)->first();
        $data_cliente = CustomerSale::where('sale_id', $venta_id)->first();
        $leyendas = SiatLeyendaFactura::all();
        $data_leyenda = $leyendas->random();
        if (!$data_siat_cufd) {
            return array('mensaje' => 'Error Codigo Control no existe, renovar CUFD. ', 'status' => false);
        }
        $tipo_impresion = 'rollo';
        $nro_impresion = $pos_setting->type_print;
        $tipo_impresion = $this->formatoImpresion($nro_impresion);
        $totalVenta = 0;
        $data_gift_card = new Payment();
        // obteniendo los productos vendidos
        // y guardalos en un array
        $array_product_sales = DB::table('product_sales')->where('sale_id', '=', $venta_id)->get();
        foreach ($array_product_sales as $nro_prod_sales => $product_sales) {
            $info_product = Product::where('id', $product_sales->product_id)->first();
            $info_par_unit = null;

            if ($info_product && $info_product->unit_id > 0) {
                $info_unit = Unit::where('id', $info_product->unit_id)->first();
                if ($info_unit) {
                    $info_par_unit = SiatParametricaVario::where('codigo_clasificador', $info_unit->codigo_clasificador_siat)->where('tipo_clasificador', 'unidadMedida')->first();
                }
            } else {
                $unitDefault = Unit::where('is_active', true)
                    ->where(function ($q) {
                        $q->where('codigo_clasificador_siat', 57)
                            ->orWhere('codigo_clasificador_siat', 58);
                    })
                    ->first();
                if ($unitDefault) {
                    $info_par_unit = SiatParametricaVario::where('codigo_clasificador', $unitDefault->codigo_clasificador_siat)->where('tipo_clasificador', 'unidadMedida')->first();
                }
            }

            if (!$info_par_unit) {
                $info_par_unit = [
                    'codigo_clasificador' => "58",
                    'descripcion' => "UNIDAD (SERVICIOS)",
                ];
            }

            $descripcion_adicional = "";
            if ($product_sales->description != null) {
                $descripcion_adicional = ' - ' . $product_sales->description;
            }
            // Redondear valores leídos de BD por si tienen decimales largos
            $qty_clean = number_format($product_sales->qty, 2, '.', '');
            $price_clean = number_format($product_sales->net_unit_price, 2, '.', '');
            $subtotal = $qty_clean * $price_clean;
            $subtotal = number_format($subtotal, 2, '.', ''); // Redondear a 2 decimales
            $totalVenta = $totalVenta + $subtotal;
            log::info("Subtotal: " . number_format($subtotal, 2, '.', ''));
            $data_product_sales[$nro_prod_sales] = array(
                "actividadEconomica" => $info_product->codigo_actividad ? (string) $info_product->codigo_actividad : "620100",
                "cantidad" => $qty_clean,
                "codigoProducto" => (string) $info_product->code,
                "codigoProductoSin" => $info_product->codigo_producto_servicio ? (int) $info_product->codigo_producto_servicio : 83141,
                "descripcion" => $info_product->name . $descripcion_adicional,
                "montoDescuento" => number_format($product_sales->discount, 2, '.', ''),
                "numeroImei" => "",
                "numeroSerie" => "",
                "precioUnitario" => number_format($price_clean, 2, '.', ''),
                "subTotal" => number_format($subtotal, 2, '.', ''),
                "unidadMedida" => (int) $info_par_unit['codigo_clasificador'],
                "nombreUnidadMedida" => $info_par_unit['descripcion'],
            );
        }
        log::info("Total Venta: " . number_format($totalVenta, 2, '.', ''));
        $array_payment_sales = DB::table('payments')->where('sale_id', '=', $venta_id)->get();
        $nro_de_pagos = $array_payment_sales->count();
        foreach ($array_payment_sales as $item_pago) {
            if ($item_pago->paying_method == "Tarjeta_Regalo") {
                $data_gift_card = $item_pago;
            }
        }

        // preguntar casos de NULL, setear valor 0;
        if ($data_gift_card->amount == null) {
            $data_gift_card->amount = 0;
        }
        if ($data_venta->order_discount == null) {
            $data_venta->order_discount = 0;
        }
        $nro_tarjeta = null;
        if ($data_cliente->numero_tarjeta_credito_debito != null) {
            $nro_tarjeta = $data_cliente->numero_tarjeta_credito_debito;
        }
        $complemento = "";
        if ($data_cliente->complemento_documento != null) {
            $complemento = $data_cliente->complemento_documento;
        }
        $listAdicionales = null;
        if ($data_venta->sale_note != null || $data_venta->sale_note != '') {
            $listAdicionales[] = array("etiqueta" => "paciente", "valor" => $data_venta->sale_note);
        }
        
        // Construir data_body base
        $data_body = [
            'codigoControl' => $data_siat_cufd->codigo_control,
            'codigoDocumento' => (int) $data_cliente->codigo_documento_sector,
            'codigoPuntoVenta' => (int) $data_siat_cufd->codigo_punto_venta,
            'cuis' => $data_p_venta->codigo_cuis,
            'nit' => (int) $pos_setting->nit_emisor,
            'sucursal' => (int) $data_siat_cufd->sucursal,
            'formatoFactura' => $tipo_impresion,
            'adicionales' => $listAdicionales,
            'factura' => [
                'nitEmisor' => (int) $pos_setting->nit_emisor,
                'razonSocialEmisor' => $pos_setting->razon_social_emisor,
                'direccion' => $data_siat_cufd->direccion,
                'fechaEmision' => "",
                'leyenda' => $data_leyenda->descripcion_leyenda,
                'montoGiftCard' => number_format($data_gift_card->amount, 2, '.', ''),
                'montoTotal' => number_format($totalVenta, 2, '.', ''),
                'montoTotalMoneda' => number_format($totalVenta, 2, '.', ''),
                'montoTotalSujetoIva' => number_format(($totalVenta - $data_gift_card->amount), 2, '.', ''),
                "codigoMetodoPago" => max(1, (int) ($data_cliente->tipo_metodo_pago ?? 1)),
                "municipio" => $data_sucursal->ciudad_municipio,

                "nombreRazonSocial" => $data_cliente->razon_social,
                "numeroDocumento" => $data_cliente->valor_documento,
                "numeroFactura" => (int) $data_cliente->nro_factura,
                "numeroTarjeta" => $nro_tarjeta ? (int) $nro_tarjeta : 0,
                "telefono" => $data_sucursal->telefono,
                "tipoCambio" => 1,
                "usuario" => $data_cliente->usuario,
                "cafc" => "",
                "codigoCliente" => $data_cliente->codigofijo,
                "email" => $data_cliente->email,

                "periodoFacturado" => $data_cliente->glosa_periodo_facturado,
                "codigoDocumentoSector" => (int) $data_cliente->codigo_documento_sector,
                "codigoExcepcion" => (int) $data_cliente->codigo_excepcion,
                "codigoMoneda" => 1,
                "codigoPuntoVenta" => (int) $data_siat_cufd->codigo_punto_venta,
                "codigoSucursal" => (int) $data_siat_cufd->sucursal,
                "codigoTipoDocumentoIdentidad" => (int) $data_cliente->tipo_documento,
                "complemento" => $complemento,
                "cuf" => "",
                "cufd" => "",
                "descuentoAdicional" => number_format($data_venta->order_discount, 2, '.', ''),
                "detalle" => $data_product_sales,
            ]
        ];
        
        // Solo enviar CUFD cuando NO esté en modo centralizado
        if (($pos_setting->cufd_centralizado ?? 0) == 0) {
            $data_body['cufd'] = $data_siat_cufd->codigo_cufd;
        }
        
        Log::info("URL => " . $host . $path);
        Log::info(json_encode($data_body, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        
        try {
            $response = Http::withHeaders([
                'Authorization' => $bearer,
            ])->post($host . $path, $data_body);
            
            $http_status = $response->status();
        } catch (\Exception $e) {
            Log::error("ERROR DE CONEXIÓN CON SERVIDOR SIAT: " . $e->getMessage());
            return array('mensaje' => 'Error de conexión con servidor SIAT: ' . $e->getMessage(), 'status' => false);
        }

        $respuesta = array();

        if ($response->successful()) {
            $data_json = $response->json();
            
            // Log de respuesta exitosa
            $data_json_log = $data_json;
            if (isset($data_json_log['xmlfactura'])) {
                $data_json_log['xmlfactura'] = '[XML_OMITIDO]';
            }
            
            Log::info("Response => " . json_encode($data_json_log, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            $update_customer_sale = CustomerSale::where('sale_id', $data_cliente->sale_id)->first();
            $update_customer_sale->codigo_recepcion = $data_json['codigo_recepcion'];
            $update_customer_sale->cuf = $data_json['cuf'];
            $update_customer_sale->codigo_cufd = $data_siat_cufd->codigo_cufd;
            $update_customer_sale->xml = $data_json['xmlfactura'];
            $update_customer_sale->estado_factura = "VIGENTE";
            $update_customer_sale->save();
            
            $msj = "Venta Facturada correctamente";
            $respuesta = array('mensaje' => $msj, 'status' => true);
        } else{
            $http_status = $response->status();
            $response_body = $response->body();
            
            try {
                $error = $response->json();
            } catch (\Exception $e) {
                $error = ['status' => $http_status, 'raw_response' => $response_body];
            }
            
            $titulo_error = $error['status'] ?? $http_status;
            $error_info = "";
            
            if ($titulo_error == 500) {
                $error_info = "Tipo Error: ERROR INTERNO DEL SERVIDOR (500)";
                $respuesta = array('mensaje' => 'Error interno del servidor. ', 'status' => false);
            } elseif ($titulo_error == 400) {
                $mensajes_error = $error['mensajesRecepcion'] ?? [];
                $descripcion = "";
                $detalle_errores = "";
                foreach ($mensajes_error as $mensaje) {
                    $descripcion .= " Código: " . $mensaje['codigo'] . " - Descripción: " . $mensaje['descripcion'];
                    $detalle_errores .= "\n  - " . $mensaje['codigo'] . ": " . $mensaje['descripcion'];
                }
                $error_info = "Tipo Error: BAD REQUEST (400)" . $detalle_errores;
                $msj = 'Problemas de conexión Siat, la venta no ha sido facturada. Error: ' . $titulo_error . $descripcion;
                $respuesta = array('mensaje' => $msj, 'status' => false);
            } elseif ($titulo_error == 404) {
                $error_info = "Tipo Error: SERVICIO NO ENCONTRADO (404)";
                $respuesta = array('mensaje' => 'Servicio no encontrado, contacte con soporte. ', 'status' => false);
            } else {
                $error_info = "Tipo Error: DESCONOCIDO (" . $titulo_error . ")";
                $respuesta = array('mensaje' => 'Error en respuesta de servicio, contacte con soporte. ', 'status' => false);
            }
            
            Log::error("HTTP Status: " . $http_status);
            Log::error("Response => " . json_encode($error, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }
        return $respuesta;
    }

    public function generarFacturaIndividualComisionista($id)
    {
        $venta_id = $id;
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_operaciones;
        $path = '/factura.venta/factura.individual.comisionista';

        Log::info("FACTURACION COMISIONISTA - Venta ID: " . $venta_id);

        $data_venta = Sale::where('id', $venta_id)->first();
        $data_biller = Biller::where('id', $data_venta->biller_id)->first();
        $data_p_venta = SiatPuntoVenta::where([
            'sucursal' => $data_biller->sucursal,
            'codigo_punto_venta' => $data_biller->punto_venta_siat
        ])->first();
        
        // MODO COMISIONISTA: En modo centralizado NO se busca CUFD en BD local
        // El CUFD es gestionado por el servidor central
        
        $data_sucursal = SiatSucursal::where('sucursal', $data_p_venta->sucursal)->first();
        $data_cliente = CustomerSale::where('sale_id', $venta_id)->first();

        $leyendas = SiatLeyendaFactura::all();
        $data_leyenda = $leyendas->random();

        $nro_impresion = $pos_setting->type_print;
        $tipo_impresion = $this->formatoImpresion($nro_impresion);

        $totalVenta = 0;
        $data_gift_card = new Payment();
        $data_product_sales = [];

        // obteniendo los productos vendidos
        $array_product_sales = DB::table('product_sales')->where('sale_id', '=', $venta_id)->get();
        foreach ($array_product_sales as $nro_prod_sales => $product_sales) {
            $info_product = Product::where('id', $product_sales->product_id)->first();
            $info_par_unit = null;

            if ($info_product && $info_product->unit_id > 0) {
                $info_unit = Unit::where('id', $info_product->unit_id)->first();
                if ($info_unit) {
                    $info_par_unit = SiatParametricaVario::where('codigo_clasificador', $info_unit->codigo_clasificador_siat)
                        ->where('tipo_clasificador', 'unidadMedida')
                        ->first();
                }
            } else {
                $unitDefault = Unit::where('is_active', true)
                    ->where(function ($q) {
                        $q->where('codigo_clasificador_siat', 57)
                            ->orWhere('codigo_clasificador_siat', 58);
                    })
                    ->first();

                if ($unitDefault) {
                    $info_par_unit = SiatParametricaVario::where('codigo_clasificador', $unitDefault->codigo_clasificador_siat)
                        ->where('tipo_clasificador', 'unidadMedida')
                        ->first();
                }
            }

            if (!$info_par_unit) {
                $info_par_unit = [
                    'codigo_clasificador' => "58",
                    'descripcion' => "UNIDAD (SERVICIOS)",
                ];
            }

            $descripcion_adicional = "";
            if ($product_sales->description != null) {
                $descripcion_adicional = ' - ' . $product_sales->description;
            }

            // Redondear valores leídos de BD por si tienen decimales largos
            $qty_clean = number_format($product_sales->qty, 2, '.', '');
            $price_clean = number_format($product_sales->net_unit_price, 2, '.', '');
            $subtotal = $qty_clean * $price_clean;
            $subtotal = number_format($subtotal, 2, '.', ''); // Redondear a 2 decimales
            $totalVenta = $totalVenta + $subtotal;

            // Construir detalle según formato SIAT
            $data_product_sales[$nro_prod_sales] = array(
                "actividadEconomica" => $info_product && $info_product->codigo_actividad ? (string) $info_product->codigo_actividad : "620100",
                "cantidad" => $qty_clean,
                "codigoProducto" => $info_product ? (string) $info_product->code : "",
                "codigoProductoSin" => $info_product && $info_product->codigo_producto_servicio ? (string) $info_product->codigo_producto_servicio : "83141",
                "descripcion" => ($info_product ? $info_product->name : "") . $descripcion_adicional,
                "montoDescuento" => number_format($product_sales->discount, 2, '.', ''),
                "numeroImei" => "",
                "numeroSerie" => "",
                "precioUnitario" => number_format($price_clean, 2, '.', ''),
                "subTotal" => number_format($subtotal, 2, '.', ''),
                "unidadMedida" => (string) $info_par_unit['codigo_clasificador'],
                "nombreUnidadMedida" => $info_par_unit['descripcion'],
            );
        }

        $array_payment_sales = DB::table('payments')->where('sale_id', '=', $venta_id)->get();
        foreach ($array_payment_sales as $item_pago) {
            if ($item_pago->paying_method == "Tarjeta_Regalo") {
                $data_gift_card = $item_pago;
            }
        }

        // preguntar casos de NULL, setear valor 0;
        if ($data_gift_card->amount == null) {
            $data_gift_card->amount = 0;
        }
        if ($data_venta->order_discount == null) {
            $data_venta->order_discount = 0;
        }

        $nro_tarjeta = null;
        if ($data_cliente->numero_tarjeta_credito_debito != null) {
            $nro_tarjeta = $data_cliente->numero_tarjeta_credito_debito;
        }

        $complemento = "";
        if ($data_cliente->complemento_documento != null) {
            $complemento = $data_cliente->complemento_documento;
        }

        $listAdicionales = [];
        if ($data_venta->sale_note != null && $data_venta->sale_note != '') {
            $listAdicionales[] = array("etiqueta" => "paciente", "valor" => $data_venta->sale_note);
        }

        $online = ($pos_setting->codigo_emision ?? 1) == 1;

        // Validar codigoMetodoPago (SIAT requiere mínimo 1)
        $codigoMetodoPago = (int) ($data_cliente->tipo_metodo_pago ?? 1);
        if ($codigoMetodoPago < 1) {
            $codigoMetodoPago = 1; // Por defecto 1 = Efectivo
        }

        // Construir JSON limpio según especificación SIAT
        $data_body = [
            'nit' => (int) $pos_setting->nit_emisor,
            'sucursal' => (int) $data_p_venta->sucursal,
            'adicionales' => $listAdicionales,
            'codigoDocumento' => (int) $data_cliente->codigo_documento_sector,
            'codigoPuntoVenta' => (int) $data_p_venta->codigo_punto_venta,
            'factura' => [
                'nitEmisor' => (int) $pos_setting->nit_emisor,
                'codigoCliente' => (string) $data_cliente->codigofijo,
                'codigoDocumentoSector' => (int) $data_cliente->codigo_documento_sector,
                'codigoExcepcion' => (int) $data_cliente->codigo_excepcion,
                'codigoMetodoPago' => $codigoMetodoPago,
                'codigoMoneda' => 1,
                'codigoPuntoVenta' => (int) $data_p_venta->codigo_punto_venta,
                'codigoSucursal' => (int) $data_p_venta->sucursal,
                'codigoTipoDocumentoIdentidad' => (int) $data_cliente->tipo_documento,
                'complemento' => $complemento,
                'descuentoAdicional' => number_format($data_venta->order_discount, 2, '.', ''),
                'domicilioCliente' => "",
                'email' => $data_cliente->email,
                'montoTotal' => number_format($totalVenta, 2, '.', ''),
                'montoTotalMoneda' => number_format($totalVenta, 2, '.', ''),
                'montoTotalOriginal' => number_format($totalVenta, 2, '.', ''),
                'montoTotalSujetoIva' => number_format(($totalVenta - $data_gift_card->amount), 2, '.', ''),
                'nombreRazonSocial' => $data_cliente->razon_social,
                'numeroDocumento' => $data_cliente->valor_documento,
                'telefono' => $data_sucursal->telefono,
                'tipoCambio' => 1,
                'usuario' => $data_cliente->usuario,
                'detalle' => $data_product_sales,
            ]
        ];

        // Preparar datos para el log (el data_body ya está limpio)
        $data_body_log = $data_body;

        $url_completa = $host . $path;
        Log::info("URL => " . $url_completa);
        Log::info(json_encode($data_body, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        try {
            $response = Http::timeout(30)->withHeaders([
                'Authorization' => $bearer,
            ])->post($url_completa, $data_body);

            $http_status = $response->status();
            
        } catch (\Exception $e) {
            Log::error("ERROR DE CONEXIÓN CON SERVIDOR SIAT: " . $e->getMessage());
            return array(
                'mensaje' => 'Error de conexión con servidor SIAT: ' . $e->getMessage(),
                'status' => false
            );
        }

        $respuesta = array();

        if ($response->successful()) {
            $data_json = $response->json();
            
            // Log de respuesta exitosa sin XML
            $data_json_log = $data_json;
            if (isset($data_json_log['xmlfactura'])) {
                $data_json_log['xmlfactura'] = '[XML_OMITIDO]';
            }
            if (isset($data_json_log['xml_factura'])) {
                $data_json_log['xml_factura'] = '[XML_OMITIDO]';
            }
            
            Log::info("Response => " . json_encode($data_json_log, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            
            $update_customer_sale = CustomerSale::where('sale_id', $data_cliente->sale_id)->first();

            if (isset($data_json['codigo_recepcion'])) {
                $update_customer_sale->codigo_recepcion = $data_json['codigo_recepcion'];
            }
            if (isset($data_json['cuf'])) {
                $update_customer_sale->cuf = $data_json['cuf'];
            }

            // En modo comisionista, NO guardamos codigo_cufd local porque es gestionado centralmente
            // El servidor central maneja el CUFD
            if (isset($data_json['xmlfactura'])) {
                $update_customer_sale->xml = $data_json['xmlfactura'];
            }
            $update_customer_sale->estado_factura = "VIGENTE";
            $update_customer_sale->save();

            $msj = "Venta Facturada correctamente";
            $respuesta = array('mensaje' => $msj, 'status' => true);
        } else {
            $http_status = $response->status();
            $response_body = $response->body();
            
            try {
                $error = $response->json();
            } catch (\Exception $e) {
                $error = ['raw_response' => $response_body];
            }
            
            Log::error("HTTP Status: " . $http_status);
            Log::error("Response => " . json_encode($error, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            
            $titulo_error = $error['status'] ?? $http_status;
            
            if ($titulo_error == 500) {
                $respuesta = array('mensaje' => 'Error interno del servidor SIAT. ', 'status' => false);
            } elseif ($titulo_error == 400) {
                $mensajes_error = $error['mensajesRecepcion'] ?? [];
                $descripcion = "";
                
                foreach ($mensajes_error as $index => $mensaje) {
                    $codigo_msg = $mensaje['codigo'] ?? 'N/A';
                    $desc_msg = $mensaje['descripcion'] ?? 'N/A';
                    $descripcion .= " [" . $codigo_msg . ": " . $desc_msg . "]";
                }
                
                $msj = 'Problemas con la facturación SIAT. Error ' . $titulo_error . ':' . $descripcion;
                $respuesta = array('mensaje' => $msj, 'status' => false);
            } elseif ($titulo_error == 404) {
                $respuesta = array('mensaje' => 'Servicio no encontrado en SIAT, contacte con soporte. ', 'status' => false);
            } else {
                $respuesta = array('mensaje' => 'Error en respuesta de servicio SIAT (código ' . $titulo_error . '), contacte con soporte. ', 'status' => false);
            }
        }

        return $respuesta;
    }

    public function generarFacturaIndividualOffline($id, $codigoEvento)
    {
        $venta_id = $id;
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_operaciones;
        $path = '/factura.venta/factura.individual.offline';

        $data_venta = Sale::where('id', $venta_id)->first();
        $data_biller = Biller::where('id', $data_venta->biller_id)->first();
        $data_p_venta = SiatPuntoVenta::where([
            'sucursal' => $data_biller->sucursal,
            'codigo_punto_venta' => $data_biller->punto_venta_siat
        ])->first();
        $data_siat_cufd = SiatCufd::where('sucursal', $data_p_venta->sucursal)->where('codigo_punto_venta', $data_p_venta->codigo_punto_venta)->where('estado', true)->orderBy('fecha_registro', 'desc')->first();
        $data_sucursal = SiatSucursal::where('sucursal', $data_p_venta->sucursal)->first();
        $data_cliente = CustomerSale::where('sale_id', $venta_id)->first();

        $leyendas = SiatLeyendaFactura::all();
        $data_leyenda = $leyendas->random();

        $tipo_impresion = 'rollo';
        $nro_impresion = $pos_setting->type_print;
        $tipo_impresion = $this->formatoImpresion($nro_impresion);

        $data_gift_card = new Payment();
        // obteniendo los productos vendidos
        // y guardalos en un array
        $array_product_sales = DB::table('product_sales')->where('sale_id', '=', $venta_id)->get();
        foreach ($array_product_sales as $nro_prod_sales => $product_sales) {
            $info_product = Product::where('id', $product_sales->product_id)->first();
            $info_par_unit = null;

            if ($info_product && $info_product->unit_id > 0) {
                $info_unit = Unit::where('id', $info_product->unit_id)->first();
                if ($info_unit) {
                    $info_par_unit = SiatParametricaVario::where('codigo_clasificador', $info_unit->codigo_clasificador_siat)->where('tipo_clasificador', 'unidadMedida')->first();
                }
            } else {
                $info_par_unit = [
                    'codigo_clasificador' => "58",
                    'descripcion' => "UNIDAD (SERVICIOS)",
                ];
            }

            if (!$info_par_unit) {
                $info_par_unit = [
                    'codigo_clasificador' => "58",
                    'descripcion' => "UNIDAD (SERVICIOS)",
                ];
            }

            $descripcion_adicional = "";
            if ($product_sales->description != null) {
                $descripcion_adicional = ' - ' . $product_sales->description;
            }

            $data_product_sales[$nro_prod_sales] = array(
                "actividadEconomica" => $info_product->codigo_actividad,
                "cantidad" => $product_sales->qty,
                "codigoProducto" => $info_product->code,
                "codigoProductoSin" => $info_product->codigo_producto_servicio,
                "descripcion" => $info_product->name . $descripcion_adicional,
                "montoDescuento" => number_format($product_sales->discount, 2, '.', ''),
                "numeroImei" => "",
                "numeroSerie" => "",
                "precioUnitario" => number_format($product_sales->net_unit_price, 2, '.', ''),
                "subTotal" => number_format($product_sales->total, 2, '.', ''),
                "unidadMedida" => $info_par_unit['codigo_clasificador'],
                "nombreUnidadMedida" => $info_par_unit['descripcion'],
            );
        }
        $array_payment_sales = DB::table('payments')->where('sale_id', '=', $venta_id)->get();
        $nro_de_pagos = $array_payment_sales->count();
        foreach ($array_payment_sales as $item_pago) {
            if ($item_pago->paying_method == "Tarjeta_Regalo") {
                $data_gift_card = $item_pago;
            }
        }

        // preguntar casos de NULL, setear valor 0;
        if ($data_gift_card->amount == null) {
            $data_gift_card->amount = 0;
        }
        if ($data_venta->order_discount == null) {
            $data_venta->order_discount = 0;
        }
        $nro_tarjeta = null;
        if ($data_cliente->numero_tarjeta_credito_debito != null) {
            $nro_tarjeta = $data_cliente->numero_tarjeta_credito_debito;
        }
        $complemento = "";
        if ($data_cliente->complemento_documento != null) {
            $complemento = $data_cliente->complemento_documento;
        }
        $codigo_cafc = "";
        if ($codigoEvento > 4) {
            $data_credencial_cafc = CredencialCafc::where('sucursal', $data_cliente->sucursal)
                ->where('codigo_punto_venta', $data_cliente->codigo_punto_venta)
                ->where('codigo_documento_sector', $data_cliente->codigo_documento_sector)
                ->where('is_active', true)
                ->first();

            $codigo_cafc = $data_credencial_cafc->codigo_cafc;
            $nro_literal_factura = $data_cliente->nro_factura_manual;
            $fecha_emision = $data_cliente->fecha_manual;
        } else {
            $nro_literal_factura = $data_cliente->nro_factura;
            $fecha_emision = "";
        }
        $listAdicionales = null;
        if ($data_venta->sale_note != null || $data_venta->sale_note != '') {
            $listAdicionales[] = array("etiqueta" => "paciente", "valor" => $data_venta->sale_note);
        }

        // Construir data_body base - Individual Offline
        $data_body = [
            'codigoControl' => $data_siat_cufd->codigo_control,
            'codigoDocumento' => $data_cliente->codigo_documento_sector,
            'codigoPuntoVenta' => $data_siat_cufd->codigo_punto_venta,
            'cuis' => $data_p_venta->codigo_cuis,
            'nit' => $pos_setting->nit_emisor,
            'sucursal' => $data_siat_cufd->sucursal,
            'formatoFactura' => $tipo_impresion,
            'adicionales' => $listAdicionales,
            'factura' => [
                'nitEmisor' => $pos_setting->nit_emisor,
                'razonSocialEmisor' => $pos_setting->razon_social_emisor,
                'direccion' => $data_siat_cufd->direccion,
                'fechaEmision' => $fecha_emision,
                'leyenda' => $data_leyenda->descripcion_leyenda,
                'montoGiftCard' => number_format($data_gift_card->amount, 2, '.', ''),
                'montoTotal' => number_format($data_venta->grand_total, 2, '.', ''),
                'montoTotalMoneda' => number_format($data_venta->grand_total, 2, '.', ''),
                'montoTotalSujetoIva' => number_format(($data_venta->grand_total - $data_gift_card->amount), 2, '.', ''),
                "codigoMetodoPago" => $data_cliente->tipo_metodo_pago,
                "municipio" => $data_sucursal->ciudad_municipio,

                "nombreRazonSocial" => $data_cliente->razon_social,
                "numeroDocumento" => $data_cliente->valor_documento,
                "numeroFactura" => $nro_literal_factura,
                "numeroTarjeta" => $nro_tarjeta,
                "telefono" => $data_sucursal->telefono,
                "tipoCambio" => 1,
                "usuario" => $data_cliente->usuario,
                "cafc" => $codigo_cafc,
                "codigoCliente" => $data_cliente->codigofijo,
                "email" => $data_cliente->email,

                "periodoFacturado" => $data_cliente->glosa_periodo_facturado,
                "codigoDocumentoSector" => $data_cliente->codigo_documento_sector,
                "codigoExcepcion" => $data_cliente->codigo_excepcion,
                "codigoMoneda" => 1,
                "codigoPuntoVenta" => $data_siat_cufd->codigo_punto_venta,
                "codigoSucursal" => $data_siat_cufd->sucursal,
                "codigoTipoDocumentoIdentidad" => $data_cliente->tipo_documento,
                "complemento" => $complemento,
                "cuf" => "",
                "cufd" => "",
                "descuentoAdicional" => number_format($data_venta->order_discount, 2, '.', ''),
                "detalle" => $data_product_sales,
            ]
        ];
        
        // Solo enviar CUFD cuando NO esté en modo centralizado - Individual Offline
        if (($pos_setting->cufd_centralizado ?? 0) == 0) {
            $data_body['cufd'] = $data_siat_cufd->codigo_cufd;
            log::info('CUFD incluido en request (modo estándar) - Individual Offline');
        } else {
            log::info('CUFD NO incluido en request (modo centralizado - flag activa) - Individual Offline');
        }

        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->post($host . $path, $data_body);
        log::info("URL => " . $host . $path);
        log::info("Body => " . json_encode($data_body));
        log::info("El response es => " . $response);

        $respuesta = array();

        if ($response->successful()) {

            $update_customer_sale = CustomerSale::where('sale_id', $data_cliente->sale_id)->first();
            $data_json = $response->json();
            //$update_customer_sale->codigo_recepcion = $data_json['codigo_recepcion'];
            $update_customer_sale->cuf = $data_json['cuf'];
            $update_customer_sale->codigo_cufd = $data_siat_cufd->codigo_cufd;
            $update_customer_sale->xml = $data_json['xmlfactura'];
            if ($pos_setting->codigo_emision == 1) {
                // emisión en línea
                $update_customer_sale->estado_factura = "CONTINGENCIA";
                $msj = "Venta Facturada correctamente en modo Contingencia";
            } else {
                // emisión masiva
                $update_customer_sale->estado_factura = "MASIVO";
                $msj = "Venta Facturada correctamente en modo Masivo";
            }
            $update_customer_sale->save();

            $respuesta = array('mensaje' => $msj, 'status' => true);
        } else {
            $error = $response->json();
            $titulo_error = $error['status'];
            if ($titulo_error == 500) {
                $respuesta = array('mensaje' => 'Error interno del servidor. ', 'status' => false);
            } elseif ($titulo_error == 400) {
                $mensajes_error = $error['mensajesRecepcion'];
                log::warning("mensajes de Error => " . json_encode($mensajes_error));
                $descripcion = "";
                foreach ($mensajes_error as $mensaje) {
                    $descripcion .= " Código: " . $mensaje['codigo'] . " - Descripción: " . $mensaje['descripcion'];
                    log::info($descripcion);
                }
                $msj = 'Problemas de conexión Siat, la venta no ha sido facturada. Error: ' . $titulo_error . $descripcion;
                $respuesta = array('mensaje' => $msj, 'status' => false);
            }
            log::warning('Error! archivo SiatTrait, operación_generarFacturaIndividualOffline => ');
            log::warning($error);
        }
        return $respuesta;
    }

    public function generarFacturaServicioBasicoOffline($id, $codigoEvento)
    {
        $venta_id = $id;
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_operaciones;
        $path = '/factura.venta/factura.individual.offline';

        $data_venta = Sale::where('id', $venta_id)->first();
        $data_biller = Biller::where('id', $data_venta->biller_id)->first();
        $data_p_venta = SiatPuntoVenta::where([
            'sucursal' => $data_biller->sucursal,
            'codigo_punto_venta' => $data_biller->punto_venta_siat
        ])->first();
        $data_siat_cufd = SiatCufd::where('sucursal', $data_p_venta->sucursal)->where('codigo_punto_venta', $data_p_venta->codigo_punto_venta)->where('estado', true)->orderBy('fecha_registro', 'desc')->first();
        $data_sucursal = SiatSucursal::where('sucursal', $data_p_venta->sucursal)->first();
        $data_cliente = CustomerSale::where('sale_id', $venta_id)->first();

        $leyendas = SiatLeyendaFactura::all();
        $data_leyenda = $leyendas->random();

        $tipo_impresion = 'rollo';
        $nro_impresion = $pos_setting->type_print;
        $tipo_impresion = $this->formatoImpresion($nro_impresion);

        $data_gift_card = new Payment();
        // obteniendo los productos vendidos
        // y guardalos en un array
        $array_product_sales = DB::table('product_sales')->where('sale_id', '=', $venta_id)->get();
        foreach ($array_product_sales as $nro_prod_sales => $product_sales) {
            $info_product = Product::where('id', $product_sales->product_id)->first();
            $info_par_unit = null;

            if ($info_product && $info_product->unit_id > 0) {
                $info_unit = Unit::where('id', $info_product->unit_id)->first();
                if ($info_unit) {
                    $info_par_unit = SiatParametricaVario::where('codigo_clasificador', $info_unit->codigo_clasificador_siat)->where('tipo_clasificador', 'unidadMedida')->first();
                }
            } else {
                $info_par_unit = [
                    'codigo_clasificador' => "58",
                    'descripcion' => "UNIDAD (SERVICIOS)",
                ];
            }

            if (!$info_par_unit) {
                $info_par_unit = [
                    'codigo_clasificador' => "58",
                    'descripcion' => "UNIDAD (SERVICIOS)",
                ];
            }

            if ($data_cliente->codigo_documento_sector == 13) {
                $info_product->price = $info_product->price - $data_cliente->monto_descuento_ley_1886 - $data_cliente->monto_descuento_tarifa_dignidad;
            }

            $descripcion_adicional = "";
            if ($product_sales->description != null) {
                $descripcion_adicional = ' - ' . $product_sales->description;
            }

            $data_product_sales[$nro_prod_sales] = array(
                "actividadEconomica" => $info_product->codigo_actividad,
                "cantidad" => $product_sales->qty,
                "codigoProducto" => $info_product->code,
                "codigoProductoSin" => $info_product->codigo_producto_servicio,
                "descripcion" => $info_product->name . $descripcion_adicional,
                "montoDescuento" => number_format($product_sales->discount, 2, '.', ''),
                "numeroImei" => "",
                "numeroSerie" => "",
                "precioUnitario" => number_format($product_sales->net_unit_price, 2, '.', ''),
                "subTotal" => number_format($product_sales->total, 2, '.', ''),
                "unidadMedida" => $info_par_unit['codigo_clasificador'],
                "nombreUnidadMedida" => $info_par_unit['descripcion'],
            );
        }
        $array_payment_sales = DB::table('payments')->where('sale_id', '=', $venta_id)->get();
        $nro_de_pagos = $array_payment_sales->count();
        foreach ($array_payment_sales as $item_pago) {
            if ($item_pago->paying_method == "Tarjeta_Regalo") {
                $data_gift_card = $item_pago;
            }
        }

        // preguntar casos de NULL, setear valor 0;
        if ($data_gift_card->amount == null) {
            $data_gift_card->amount = 0;
        }
        if ($data_venta->order_discount == null) {
            $data_venta->order_discount = 0;
        }
        $nro_tarjeta = null;
        if ($data_cliente->numero_tarjeta_credito_debito != null) {
            $nro_tarjeta = $data_cliente->numero_tarjeta_credito_debito;
        }
        $complemento = "";
        if ($data_cliente->complemento_documento != null) {
            $complemento = $data_cliente->complemento_documento;
        }
        if ($data_cliente->beneficiario_ley_1886 == null) {
            $data_cliente->beneficiario_ley_1886 = 0;
        }
        $codigo_cafc = "";
        if ($codigoEvento > 4) {
            $data_credencial_cafc = CredencialCafc::where('sucursal', $data_cliente->sucursal)
                ->where('codigo_punto_venta', $data_cliente->codigo_punto_venta)
                ->where('codigo_documento_sector', $data_cliente->codigo_documento_sector)
                ->where('is_active', true)
                ->first();

            $codigo_cafc = $data_credencial_cafc->codigo_cafc;
            $nro_literal_factura = $data_cliente->nro_factura_manual;
            $fecha_emision = $data_cliente->fecha_manual;
        } else {
            $nro_literal_factura = $data_cliente->nro_factura;
            $fecha_emision = "";
        }
        $listAdicionales = null;
        if ($data_venta->sale_note != null || $data_venta->sale_note != '') {
            $listAdicionales[] = array("etiqueta" => "paciente", "valor" => $data_venta->sale_note);
        }

        // Construir data_body base
        $data_body = [
            'codigoControl' => $data_siat_cufd->codigo_control,
            'codigoDocumento' => $data_cliente->codigo_documento_sector,
            'codigoPuntoVenta' => $data_siat_cufd->codigo_punto_venta,
            'cuis' => $data_p_venta->codigo_cuis,
            'nit' => $pos_setting->nit_emisor,
            'sucursal' => $data_siat_cufd->sucursal,
            'formatoFactura' => $tipo_impresion,
            'adicionales' => $listAdicionales,
            'factura' => [
                'nitEmisor' => $pos_setting->nit_emisor,
                'razonSocialEmisor' => $pos_setting->razon_social_emisor,
                'direccion' => $data_siat_cufd->direccion,
                'fechaEmision' => $fecha_emision,
                'leyenda' => $data_leyenda->descripcion_leyenda,
                'montoGiftCard' => number_format($data_gift_card->amount, 2, '.', ''),
                'montoTotal' => number_format(($data_venta->grand_total + $data_cliente->tasa_aseo + $data_cliente->tasa_alumbrado + $data_cliente->otras_tasas + $data_cliente->ajuste_sujeto_iva + $data_cliente->otros_pagos_no_sujeto_iva), 2, '.', ''),
                'montoTotalMoneda' => number_format(($data_venta->grand_total + $data_cliente->tasa_aseo + $data_cliente->tasa_alumbrado + $data_cliente->otras_tasas + $data_cliente->ajuste_sujeto_iva + $data_cliente->otros_pagos_no_sujeto_iva), 2, '.', ''),
                'montoTotalSujetoIva' => number_format(($data_venta->grand_total - $data_gift_card->amount + $data_cliente->ajuste_sujeto_iva), 2, '.', ''),
                "codigoMetodoPago" => $data_cliente->tipo_metodo_pago,
                "municipio" => $data_sucursal->ciudad_municipio,
                "nombreRazonSocial" => $data_cliente->razon_social,
                "numeroDocumento" => $data_cliente->valor_documento,
                "numeroFactura" => $nro_literal_factura,
                "numeroTarjeta" => $nro_tarjeta,
                "telefono" => $data_sucursal->telefono,
                "tipoCambio" => 1,
                "usuario" => $data_cliente->usuario,
                "cafc" => $codigo_cafc,
                "codigoCliente" => $data_cliente->codigofijo,
                "email" => $data_cliente->email,
                "periodoFacturado" => $data_cliente->glosa_periodo_facturado,
                "codigoDocumentoSector" => $data_cliente->codigo_documento_sector,
                "codigoExcepcion" => $data_cliente->codigo_excepcion,
                "codigoMoneda" => 1,
                "codigoPuntoVenta" => $data_siat_cufd->codigo_punto_venta,
                "codigoSucursal" => $data_siat_cufd->sucursal,
                "codigoTipoDocumentoIdentidad" => $data_cliente->tipo_documento,
                "complemento" => $complemento,
                "cuf" => "",
                "cufd" => "",
                "descuentoAdicional" => number_format($data_venta->order_discount, 2, '.', ''),

                "mes" => $data_cliente->mes,
                "gestion" => $data_cliente->gestion,
                "ciudad" => $data_cliente->ciudad,
                "numeroMedidor" => $data_cliente->numero_medidor,
                "domicilioCliente" => $data_cliente->domicilio_cliente,
                "consumoPeriodo" => $data_cliente->consumo_periodo,
                "beneficiarioLey1886" => $data_cliente->beneficiario_ley_1886,
                "montoDescuentoLey1886" => 0 + $data_cliente->monto_descuento_ley_1886,
                "montoDescuentoTarifaDignidad" => 0 + $data_cliente->monto_descuento_tarifa_dignidad,
                "tasaAseo" => $data_cliente->tasa_aseo,
                "tasaAlumbrado" => $data_cliente->tasa_alumbrado,
                "otrasTasas" => $data_cliente->otras_tasas,
                "ajusteNoSujetoIva" => $data_cliente->ajuste_no_sujeto_iva,
                "detalleAjusteNoSujetoIva" => $data_cliente->detalle_ajuste_no_sujeto_iva,
                "ajusteSujetoIva" => $data_cliente->ajuste_sujeto_iva,
                "detalleAjusteSujetoIva" => $data_cliente->detalle_ajuste_sujeto_iva,
                "otrosPagosNoSujetoIva" => 0 + $data_cliente->otros_pagos_no_sujeto_iva,
                "detalleOtrosPagosNoSujetoIva" => $data_cliente->detalle_otros_pagos_no_sujeto_iva,

                "detalle" => $data_product_sales,
            ]
        ];
        
        // Solo enviar CUFD cuando NO esté en modo centralizado - Servicio Básico Offline
        if (($pos_setting->cufd_centralizado ?? 0) == 0) {
            $data_body['cufd'] = $data_siat_cufd->codigo_cufd;
            log::info('CUFD incluido en request (modo estándar) - Servicio Básico Offline');
        } else {
            log::info('CUFD NO incluido en request (modo centralizado - flag activa) - Servicio Básico Offline');
        }
        
        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->post($host . $path, $data_body);
        log::info("URL => " . $host . $path);
        log::info("Body => " . json_encode($data_body));

        $respuesta = array();
        if ($response->successful()) {

            $update_customer_sale = CustomerSale::where('sale_id', $data_cliente->sale_id)->first();
            $data_json = $response->json();
            // $update_customer_sale->codigo_recepcion = $data_json['codigo_recepcion'];
            $update_customer_sale->cuf = $data_json['cuf'];
            $update_customer_sale->codigo_cufd = $data_siat_cufd->codigo_cufd;
            $update_customer_sale->xml = $data_json['xmlfactura'];
            if ($pos_setting->codigo_emision == 1) {
                // emisión en línea
                $update_customer_sale->estado_factura = "CONTINGENCIA";
                $msj = "Venta Facturada correctamente en modo Contingencia";
            } else {
                // emisión masiva
                $update_customer_sale->estado_factura = "MASIVO";
                $msj = "Venta Facturada correctamente en modo Masivo";
            }
            $update_customer_sale->save();
            $totalGrand = ($data_venta->grand_total + $data_cliente->tasa_aseo + $data_cliente->tasa_alumbrado + $data_cliente->otras_tasas + $data_cliente->ajuste_sujeto_iva + $data_cliente->otros_pagos_no_sujeto_iva);
            $data_venta->total_price = $totalGrand;
            $data_venta->grand_total = $totalGrand;
            $data_venta->paid_amount = $totalGrand;
            $data_venta->save();

            $respuesta = array('mensaje' => $msj, 'status' => true);
        } else {
            $error = $response->json();
            $titulo_error = $error['status'];
            if ($titulo_error == 500) {
                $respuesta = array('mensaje' => 'Error interno del servidor. ', 'status' => false);
            } elseif ($titulo_error == 400) {
                $mensajes_error = $error['mensajesRecepcion'];
                log::warning("mensajes de Error => " . json_encode($mensajes_error));
                $descripcion = "";
                foreach ($mensajes_error as $mensaje) {
                    $descripcion .= " Código: " . $mensaje['codigo'] . " - Descripción: " . $mensaje['descripcion'];
                    log::info($descripcion);
                }
                $msj = 'Problemas de conexión Siat, la venta no ha sido facturada. Error: ' . $titulo_error . $descripcion;
                $respuesta = array('mensaje' => $msj, 'status' => false);
            }
            log::warning('Error! archivo SiatTrait, operacion_generarFacturaServicioBasicoOffline => ');
            log::warning($error);
        }
        return $respuesta;
    }

    public function generarFacturaAlquilerOffline($id, $codigoEvento)
    {
        $venta_id = $id;
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_operaciones;
        $path = '/factura.venta/factura.individual.offline';

        $data_venta = Sale::where('id', $venta_id)->first();
        $data_biller = Biller::where('id', $data_venta->biller_id)->first();
        $data_p_venta = SiatPuntoVenta::where([
            'sucursal' => $data_biller->sucursal,
            'codigo_punto_venta' => $data_biller->punto_venta_siat
        ])->first();
        $data_siat_cufd = SiatCufd::where('sucursal', $data_p_venta->sucursal)->where('codigo_punto_venta', $data_p_venta->codigo_punto_venta)->where('estado', true)->orderBy('fecha_registro', 'desc')->first();
        $data_sucursal = SiatSucursal::where('sucursal', $data_p_venta->sucursal)->first();
        $data_cliente = CustomerSale::where('sale_id', $venta_id)->first();

        $leyendas = SiatLeyendaFactura::all();
        $data_leyenda = $leyendas->random();

        $tipo_impresion = 'rollo';
        $nro_impresion = $pos_setting->type_print;
        $tipo_impresion = $this->formatoImpresion($nro_impresion);

        $data_gift_card = new Payment();
        // obteniendo los productos vendidos
        // y guardalos en un array
        $array_product_sales = DB::table('product_sales')->where('sale_id', '=', $venta_id)->get();
        foreach ($array_product_sales as $nro_prod_sales => $product_sales) {
            $info_product = Product::where('id', $product_sales->product_id)->first();
            $info_par_unit = null;

            if ($info_product && $info_product->unit_id > 0) {
                $info_unit = Unit::where('id', $info_product->unit_id)->first();
                if ($info_unit) {
                    $info_par_unit = SiatParametricaVario::where('codigo_clasificador', $info_unit->codigo_clasificador_siat)->where('tipo_clasificador', 'unidadMedida')->first();
                }
            } else {
                $info_par_unit = [
                    'codigo_clasificador' => "58",
                    'descripcion' => "UNIDAD (SERVICIOS)",
                ];
            }

            if (!$info_par_unit) {
                $info_par_unit = [
                    'codigo_clasificador' => "58",
                    'descripcion' => "UNIDAD (SERVICIOS)",
                ];
            }

            $data_product_sales[$nro_prod_sales] = array(
                "actividadEconomica" => $info_product->codigo_actividad,
                "cantidad" => $product_sales->qty,
                "codigoProducto" => $info_product->code,
                "codigoProductoSin" => $info_product->codigo_producto_servicio,
                "descripcion" => $info_product->name,
                "montoDescuento" => number_format($product_sales->discount, 2, '.', ''),
                "numeroImei" => "",
                "numeroSerie" => "",
                "precioUnitario" => number_format($info_product->price, 2, '.', ''),
                "subTotal" => number_format($product_sales->total, 2, '.', ''),
                "unidadMedida" => $info_par_unit['codigo_clasificador'],
                "nombreUnidadMedida" => $info_par_unit['descripcion'],
            );
        }
        $array_payment_sales = DB::table('payments')->where('sale_id', '=', $venta_id)->get();
        $nro_de_pagos = $array_payment_sales->count();
        foreach ($array_payment_sales as $item_pago) {
            if ($item_pago->paying_method == "Tarjeta_Regalo") {
                $data_gift_card = $item_pago;
            }
        }

        // preguntar casos de NULL, setear valor 0;
        if ($data_gift_card->amount == null) {
            $data_gift_card->amount = 0;
        }
        if ($data_venta->order_discount == null) {
            $data_venta->order_discount = 0;
        }
        $nro_tarjeta = null;
        if ($data_cliente->numero_tarjeta_credito_debito != null) {
            $nro_tarjeta = $data_cliente->numero_tarjeta_credito_debito;
        }
        $complemento = "";
        if ($data_cliente->complemento_documento != null) {
            $complemento = $data_cliente->complemento_documento;
        }
        $codigo_cafc = "";
        if ($codigoEvento > 4) {
            $data_credencial_cafc = CredencialCafc::where('sucursal', $data_cliente->sucursal)
                ->where('codigo_punto_venta', $data_cliente->codigo_punto_venta)
                ->where('codigo_documento_sector', $data_cliente->codigo_documento_sector)
                ->where('is_active', true)
                ->first();

            $codigo_cafc = $data_credencial_cafc->codigo_cafc;
            $nro_literal_factura = $data_cliente->nro_factura_manual;
            $fecha_emision = $data_cliente->fecha_manual;
        } else {
            $nro_literal_factura = $data_cliente->nro_factura;
            $fecha_emision = "";
        }
        $listAdicionales = null;
        if ($data_venta->sale_note != null || $data_venta->sale_note != '') {
            $listAdicionales[] = array("etiqueta" => "paciente", "valor" => $data_venta->sale_note);
        }
        // Construir data_body base - Alquiler Offline
        $data_body = [
            'codigoControl' => $data_siat_cufd->codigo_control,
            'codigoDocumento' => $data_cliente->codigo_documento_sector,
            'codigoPuntoVenta' => $data_siat_cufd->codigo_punto_venta,
            'cuis' => $data_p_venta->codigo_cuis,
            'nit' => $pos_setting->nit_emisor,
            'sucursal' => $data_siat_cufd->sucursal,
            'formatoFactura' => $tipo_impresion,
            'adicionales' => $listAdicionales,
            'factura' => [
                'nitEmisor' => $pos_setting->nit_emisor,
                'razonSocialEmisor' => $pos_setting->razon_social_emisor,
                'direccion' => $data_siat_cufd->direccion,
                'fechaEmision' => $fecha_emision,
                'leyenda' => $data_leyenda->descripcion_leyenda,
                'montoGiftCard' => number_format($data_gift_card->amount, 2, '.', ''),
                'montoTotal' => number_format($data_venta->grand_total, 2, '.', ''),
                'montoTotalMoneda' => number_format($data_venta->grand_total, 2, '.', ''),
                'montoTotalSujetoIva' => number_format(($data_venta->grand_total - $data_gift_card->amount), 2, '.', ''),
                "codigoMetodoPago" => $data_cliente->tipo_metodo_pago,
                "municipio" => $data_sucursal->ciudad_municipio,

                "nombreRazonSocial" => $data_cliente->razon_social,
                "numeroDocumento" => $data_cliente->valor_documento,
                "numeroFactura" => $nro_literal_factura,
                "numeroTarjeta" => $nro_tarjeta,
                "telefono" => $data_sucursal->telefono,
                "tipoCambio" => 1,
                "usuario" => $data_cliente->usuario,
                "cafc" => $codigo_cafc,
                "codigoCliente" => $data_cliente->customer_id,
                "email" => $data_cliente->email,

                "periodoFacturado" => $data_cliente->glosa_periodo_facturado,
                "codigoDocumentoSector" => $data_cliente->codigo_documento_sector,
                "codigoExcepcion" => $data_cliente->codigo_excepcion,
                "codigoMoneda" => 1,
                "codigoPuntoVenta" => $data_siat_cufd->codigo_punto_venta,
                "codigoSucursal" => $data_siat_cufd->sucursal,
                "codigoTipoDocumentoIdentidad" => $data_cliente->tipo_documento,
                "complemento" => $complemento,
                "cuf" => "",
                "cufd" => "",
                "descuentoAdicional" => number_format($data_venta->order_discount, 2, '.', ''),
                "detalle" => $data_product_sales,
            ]
        ];
        
        // Solo enviar CUFD cuando NO esté en modo centralizado - Alquiler Offline
        if (($pos_setting->cufd_centralizado ?? 0) == 0) {
            $data_body['cufd'] = $data_siat_cufd->codigo_cufd;
            log::info('CUFD incluido en request (modo estándar) - Alquiler Offline');
        } else {
            log::info('CUFD NO incluido en request (modo centralizado - flag activa) - Alquiler Offline');
        }

        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->post($host . $path, $data_body);
        log::info("URL => " . $host . $path, $data_body);
        log::info("Body => " . json_encode($data_body));

        $respuesta = array();

        if ($response->successful()) {

            $update_customer_sale = CustomerSale::where('sale_id', $data_cliente->sale_id)->first();
            $data_json = $response->json();
            // $update_customer_sale->codigo_recepcion = $data_json['codigo_recepcion'];
            $update_customer_sale->cuf = $data_json['cuf'];
            $update_customer_sale->codigo_cufd = $data_siat_cufd->codigo_cufd;
            $update_customer_sale->xml = $data_json['xmlfactura'];
            if ($pos_setting->codigo_emision == 1) {
                // emisión en línea
                $update_customer_sale->estado_factura = "CONTINGENCIA";
                $msj = "Venta Facturada correctamente en modo Contingencia";
            } else {
                // emisión masiva
                $update_customer_sale->estado_factura = "MASIVO";
                $msj = "Venta Facturada correctamente en modo Masivo";
            }
            $update_customer_sale->save();

            $respuesta = array('mensaje' => $msj, 'status' => true);
        } else {
            $error = $response->json();
            $titulo_error = $error['status'];
            if ($titulo_error == 500) {
                $respuesta = array('mensaje' => 'Error interno del servidor. ', 'status' => false);
            } elseif ($titulo_error == 400) {
                $mensajes_error = $error['mensajesRecepcion'];
                log::warning("mensajes de Error => " . json_encode($mensajes_error));
                $descripcion = "";
                foreach ($mensajes_error as $mensaje) {
                    $descripcion .= " Código: " . $mensaje['codigo'] . " - Descripción: " . $mensaje['descripcion'];
                    log::info($descripcion);
                }
                $msj = 'Problemas de conexión Siat, la venta no ha sido facturada. Error: ' . $titulo_error . $descripcion;
                $respuesta = array('mensaje' => $msj, 'status' => false);
            }
            log::warning('Error! archivo SiatTrait, operacion_generarFacturaAlquilerOffline => ');
            log::warning($error);
        }
        return $respuesta;
    }

    // funcion para anular solo una venta facturada
    public function anularFactura($id, $motivo_anulacion_id, $tipo_id, $codigo_punto_venta, $sucursal)
    {
        // $venta_id = 319;
        $venta_id = $id;
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_operaciones;
        $path = ($pos_setting->cufd_centralizado == 0) ? '/factura.venta/anula.factura' : '/factura.venta/anula.factura.gestionado';
        
        Log::info("ANULACION DE FACTURA - Venta ID: " . $id);
        if ($pos_setting->cufd_centralizado == 0) {
            if ($tipo_id) {
                $factura = $this->getFacturaData($id);
                $venta_facturada = CustomerSale::where('cuf', $id)->first();
                
                // Optimización: Consultar punto de venta y CUFD en una sola consulta
                $data_p_venta = SiatPuntoVenta::where([
                    'sucursal' => $factura['codigoSucursal'],
                    'codigo_punto_venta' => $factura['codigoPuntoVenta']
                ])->first(['codigo_cuis', 'sucursal', 'codigo_punto_venta']);
                
                $data_siat_cufd = SiatCufd::where('sucursal', $factura['codigoSucursal'])
                    ->where('codigo_punto_venta', $factura['codigoPuntoVenta'])
                    ->where('estado', true)
                    ->orderBy('fecha_registro', 'desc')
                    ->first(['codigo_control', 'codigo_punto_venta', 'sucursal', 'codigo_cufd']);
                    
                $body = [
                    'codigoControl' => $data_siat_cufd->codigo_control,
                    'codigoDocumento' => $factura['codigoDocumentoSector'],
                    'codigoMotivoAnulacion' => $motivo_anulacion_id,
                    'codigoPuntoVenta' => $data_siat_cufd->codigo_punto_venta,
                    'sucursal' => $data_siat_cufd->sucursal,
                    'cuis' => $data_p_venta->codigo_cuis,
                    'cufd' => $factura['cufd'],
                    'cuf' => $factura['cuf'],
                    'nit' => $pos_setting->nit_emisor,
                ];
                try {
                    $venta_id = $venta_facturada->sale_id;
                } catch (Exception $e) {
                    log::error("venta no encontrada");
                    $venta_id = 0;
                }
            } else {
                // Optimización: Usar eager loading para reducir consultas
                $data_venta = Sale::with('biller')->where('id', $venta_id)->first(['id', 'biller_id']);
                $data_biller = $data_venta->biller;
                
                $data_p_venta = SiatPuntoVenta::where([
                    'sucursal' => $data_biller->sucursal,
                    'codigo_punto_venta' => $data_biller->punto_venta_siat
                ])->first(['codigo_cuis', 'sucursal', 'codigo_punto_venta']);
                
                $data_siat_cufd = SiatCufd::where('sucursal', $data_p_venta->sucursal)
                    ->where('codigo_punto_venta', $data_p_venta->codigo_punto_venta)
                    ->where('estado', true)
                    ->orderBy('fecha_registro', 'desc')
                    ->first(['codigo_control', 'codigo_punto_venta', 'sucursal', 'codigo_cufd']);

                $venta_facturada = CustomerSale::where('sale_id', $data_venta->id)
                    ->first(['sale_id', 'codigo_documento_sector', 'cuf', 'nro_factura', 'estado_factura']);
                    
                $body = [
                    'codigoControl' => $data_siat_cufd->codigo_control,
                    'codigoDocumento' => $venta_facturada->codigo_documento_sector,
                    'codigoMotivoAnulacion' => $motivo_anulacion_id,
                    'codigoPuntoVenta' => $data_siat_cufd->codigo_punto_venta,
                    'sucursal' => $data_siat_cufd->sucursal,
                    'cuis' => $data_p_venta->codigo_cuis,
                    'cufd' => $data_siat_cufd->codigo_cufd,
                    'cuf' => $venta_facturada->cuf,
                    'nit' => $pos_setting->nit_emisor,
                ];
                $venta_id = $data_venta->id;
            }
        } else {
            // Modo centralizado
            if ($tipo_id) {
                // Si es CUF, buscar el venta_facturada para obtener sale_id
                $venta_facturada = CustomerSale::where('cuf', $id)->first(['sale_id', 'nro_factura', 'estado_factura']);
                if ($venta_facturada) {
                    $venta_id = $venta_facturada->sale_id;
                }
            }
            
            $body = [
                'codigoMotivoAnulacion' => $motivo_anulacion_id,
                'codigoPuntoVenta' => $codigo_punto_venta,
                'sucursal' => $sucursal,
                'cuf' => $id,
                'nit' => $pos_setting->nit_emisor,
            ];
        }
        
        Log::info("URL => " . $host . $path);
        Log::info(json_encode($body, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        
        try {
            $response = Http::withHeaders([
                'Authorization' => $bearer,
            ])->post($host . $path, $body);
            
            $http_status = $response->status();
            
        } catch (\Exception $e) {
            Log::error("ERROR DE CONEXIÓN CON SERVIDOR SIAT: " . $e->getMessage());
            return array('mensaje' => 'Error de conexión: ' . $e->getMessage(), 'estado' => false);
        }
        
        // procedemos a cambiar el estado de la factura de VIGENTE -> ANULADO
        // Obtenemos los datos que necesitamos para el mensaje ANTES de hacer el UPDATE
        // (los $venta_facturada previos pueden tener select limitado sin PK, así que
        //  usamos UPDATE directo por WHERE-clause en lugar de fetch+save para evitar
        //  que Eloquent falle silenciosamente por PK nula)
        if (!isset($factura)) {
            $factura = $this->getFacturaData($id);
        }

        // Datos para el mensaje de respuesta
        $nro_factura_msj = null;
        if (!$tipo_id && isset($venta_facturada)) {
            $nro_factura_msj = $venta_facturada->nro_factura;
        } elseif ($tipo_id && isset($factura['numeroFactura'])) {
            $nro_factura_msj = $factura['numeroFactura'];
        }

        if ($response->successful()) {
            $data_response = $response->json();

            Log::info("Response => " . json_encode($data_response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            // UPDATE directo: no depende de PK cargada en el modelo
            $campos_anulacion = [
                'estado_factura' => 'ANULADO',
                'cuf'            => null,
                'codigo_cufd'    => null,
            ];

            if (!$tipo_id && $venta_id) {
                // Anulación por sale_id
                $rows = CustomerSale::where('sale_id', $venta_id)->update($campos_anulacion);
                Log::info("ANULADO en customer_sales por sale_id={$venta_id} | rows={$rows}");
            } else {
                // Anulación por CUF (libro de ventas)
                $cuf_original = $tipo_id ? $id : ($venta_facturada->cuf ?? null);
                if (!$nro_factura_msj && isset($venta_facturada)) {
                    $nro_factura_msj = $venta_facturada->nro_factura ?? null;
                }
                if ($cuf_original) {
                    $rows = CustomerSale::where('cuf', $cuf_original)->update($campos_anulacion);
                    Log::info("ANULADO en customer_sales por cuf={$cuf_original} | rows={$rows}");
                } elseif ($venta_id) {
                    $rows = CustomerSale::where('sale_id', $venta_id)->update($campos_anulacion);
                    Log::info("ANULADO en customer_sales fallback por sale_id={$venta_id} | rows={$rows}");
                }
            }

            if ($tipo_id) {
                $msj = 'Factura Nro. ' . ($nro_factura_msj ?? 'N/A') . '\n Estado: ' . $data_response['codigo_estado'] . ' - ' . $data_response['codigo_descripcion'];
            } else {
                $msj = 'Factura Nro. ' . ($nro_factura_msj ?? 'N/A') . '\n Estado: ' . $data_response['codigo_estado'] . ' - ' . $data_response['codigo_descripcion'];
            }

            $respuesta = array('mensaje' => $msj, 'estado' => true);
        } else {
            $http_status = $response->status();
            $error = $response->json();
            
            $titulo_error = $error['status'] ?? $http_status;
            $error_info = "";
            
            if ($titulo_error == 500) {
                $error_info = "Tipo Error: INTERNO DEL SERVIDOR (500)";
                $msj = 'Error interno del servidor.';
                $respuesta = array('mensaje' => $msj, 'estado' => false);
            } elseif ($titulo_error == 400) {
                $data_response = $error['mensajesRecepcion'][0] ?? ['codigo' => 'N/A', 'descripcion' => 'Error desconocido'];
                $error_info = "Tipo Error: BAD REQUEST (400)
Código: " . $data_response['codigo'] . "
Descripción: " . $data_response['descripcion'];
                
                if ($tipo_id) {
                    $msj = 'Factura Nro. ' . $factura['numeroFactura'] . '\n Estado: ' . $data_response['codigo'] . ' - ' . $data_response['descripcion'];
                } else {
                    $msj = 'Factura Nro. ' . $venta_facturada->nro_factura . '\n Estado: ' . $data_response['codigo'] . ' - ' . $data_response['descripcion'];
                }
                $respuesta = array('mensaje' => $msj, 'estado' => false);
            } else {
                $error_info = "Tipo Error: DESCONOCIDO (" . $titulo_error . ")";
                $msj = 'Error en anulación de factura';
                $respuesta = array('mensaje' => $msj, 'estado' => false);
            }
            
            Log::error("HTTP Status: " . $http_status);
            Log::error("Response => " . json_encode($error, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            
            Session::flash('info', $msj);
        }
        return $respuesta;
    }
    // funcion para reenviar solo una venta facturada
    public function reenviarFactura($cuf, $correo, $telefono)
    {
        $pos_setting = PosSetting::latest()->first();
        $nit = $pos_setting->nit_emisor;
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_operaciones;
        $path = '/factura.venta/notification/whatsapp/generico';
        log::info("CUF: " . $cuf);
        log::info("NIT: " . $nit);
        $body = [
            'correo' => $correo,
            'cuf' => $cuf,
            'nit' => $nit,
            'telefono' => $telefono
        ];
        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->post(
            $host . $path,
            $body
        );
        log::info("URL => " . $host . $path);
        log::info("Body => " . json_encode($body));
        // procedemos a cambiar el estado de la factura de VIGENTE -> ANULADO
        log::info("Response => " . json_encode($response->json()));
        if ($response->successful()) {
            $msj = 'Respuesta Exitosa.';
            $data_response = $response->json();
            //$respuesta = array('mensaje' => $data_response['MENSAJE'], 'estado' => true);
            $respuesta = array('mensaje' => "Factura enviada correctamente!.", 'estado' => true);
        } else {
            $error = $response->json();
            log::warning(json_encode($error));
            $titulo_error = $error['status'];
            if ($titulo_error == 500) {
                $msj = 'Error interno del servidor.';
                $respuesta = array('mensaje' => $error['message'], 'estado' => false);
            } elseif ($titulo_error == 400) {
                //$data_response = $error['mensajesRecepcion'][0];
                $respuesta = array('mensaje' => $error['title'], 'estado' => false);
            }
            log::warning('Error! archivo SiatTrait, reenviarFactura => ');
            log::warning($error);
            //Session::flash('info', $msj);
        }
        return $respuesta;
    }


    // verificar si los servicios están caídos 
    public function verificarServiciosSiat()
    {
        $pos_setting = PosSetting::select('user_siat', 'pass_siat', 'url_siat', 'url_operaciones', 'nit_emisor')->latest()->first();
        $user_siat = $pos_setting->user_siat;
        $pass_siat = $pos_setting->pass_siat;
        $url_siat = $pos_setting->url_siat;

        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_operaciones;
        $path = '/factura.venta/comunicacion/?';
        $nit_emisor = 'nit=' . $pos_setting->nit_emisor;
        $query = $nit_emisor;
        log::info($host . $path . $query);
        if ($user_siat && $pass_siat && $url_siat) {
            try {
                $response = Http::timeout(3)->withHeaders([
                    'Authorization' => $bearer,
                ])->post($host . $path . $query);
            } catch (\Throwable $th) {
                log::warning(json_encode($th));
                $msj = 'Problemas de conexión Siat | Servidor';
                Session::flash('warning', $msj);
                return;
            }
            log::info(json_encode($response));
            //entre 200 y 299
            if ($response->successful()) {
                log::info($response->json());
                $estado_sin = $response->json();
                return $estado_sin['RESPUESTA'];
            }
        }
    }


    // funcion para resgistrar un evento significativo para un punto de venta
    public function registrarEventoSignificativo($id)
    {
        $control_contingencia_id = $id;
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_operaciones;
        $path = '/operaciones/registro.evento.significativo';

        $data_control = ControlContingencia::where('id', $control_contingencia_id)->first();
        $registro_cufd = SiatCufd::where('sucursal', $data_control->sucursal)->where('codigo_punto_venta', $data_control->codigo_punto_venta)->where('estado', true)->orderBy('fecha_registro', 'desc')->first();
        $fecha_actual = date('Y-m-d H:i:s');

        // body
        $body = [
            'codigoMotivoEvento' => $data_control->codigo_evento,
            'descripcion' => $data_control->descripcion,
            'cufdEvento' => $data_control->cufd_evento,
            'codigoPuntoVenta' => $data_control->codigo_punto_venta,
            'sucursal' => $data_control->sucursal,
            'cuis' => $data_control->cuis,
            'cufd' => $registro_cufd->codigo_cufd,
            'nit' => $pos_setting->nit_emisor,
            'fechaInicio' => $data_control->fecha_inicio_evento,
            'fechaFin' => $fecha_actual
        ];

        log::info($host . $path);
        log::info(json_encode($body));
        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->post($host . $path, $body);

        // procedemos a guardar los datos
        $data_response = $response->json();
        $msj = '';
        if ($response->successful()) {

            // actualizamos Control Contingencia
            $data_control->fecha_fin_evento = $fecha_actual;
            $data_control->cufd_valido = $registro_cufd->codigo_cufd;
            $data_control->estado = 'EVENTO_REGISTRADO';
            $data_control->codigo_registro_evento = $data_response['CODIGO_RECEPCION'];
            $data_control->save();

            $msj = 'Evento Significativo fue registrado exitosamente. Recepción: ' . $data_response['CODIGO_RECEPCION'];
            Session::flash('info', $msj);
        } else {
            log::warning('Error! archivo SiatTrait, operacion_registrarEventoSignificativo => ');
            log::warning(json_encode($response->json()));
            $mensajes = $data_response['mensajes'];
            foreach ($mensajes as $key => $mensaje) {
                $msj .= ' Error: ' . $mensaje['codigo'] . ' - ' . $mensaje['descripcion'] . '.';
            }
            Session::flash('info', $msj);
        }
    }


    public function envioPaqueteRecepcion($control_contingencia_paquete_id, $nro_factura_inicial, $nro_factura_final)
    {
        // http://66.94.100.10:5014/factura.venta/factura.paquete.recepcion?cafc=0&codigoControl=4D8ACC9DD5E6D74&codigoControlEvento=862438&codigoDocumento=1&codigoEventoSignificativo=1&codigoPuntoVenta=0&cufd=codigo-cufd&cufdEvento=cufd-evento&cuis=FD520714&nit=388615026&sucursal=0&tipoFacturaDocumento=1
        
        Log::info("ENVIO PAQUETE CONTINGENCIA - Paquete ID: " . $control_contingencia_paquete_id);
        
        $data_c_contingencia_paquete = ControlContingenciaPaquetes::where('id', $control_contingencia_paquete_id)->first();
        $control_contingencia_id = $data_c_contingencia_paquete->control_contingencia_id;

        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_operaciones;
        $path = '/factura.venta/factura.paquete.recepcion?';
        
        $data_control = ControlContingencia::where('id', $control_contingencia_id)->first();
        $registro_cufd_anterior = SiatCufd::where('codigo_cufd', $data_control->cufd_evento)->first();
        $registro_cufd_valido = SiatCufd::where('codigo_cufd', $data_control->cufd_valido)->first();
        
        $codigo_cafc = "";
        if ($data_control->codigo_evento > 4) {
            $data_credencial_cafc = CredencialCafc::where('sucursal', $data_control->sucursal)
                ->where('codigo_punto_venta', $data_control->codigo_punto_venta)
                ->where('codigo_documento_sector', $data_control->codigo_documento_sector)
                ->where('is_active', true)
                ->first();

            $codigo_cafc = $data_credencial_cafc->codigo_cafc;
        }
        
        Log::info("Endpoint: " . $host . $path);

        // query
        $query = 'cafc=' . $codigo_cafc;
        $query .= '&cuis=' . $data_control->cuis;
        $query .= '&sucursal=' . $data_control->sucursal;
        $query .= '&codigoPuntoVenta=' . $data_control->codigo_punto_venta;
        $query .= '&nit=' . $pos_setting->nit_emisor;
        $query .= '&codigoDocumento=' . $data_control->codigo_documento_sector;

        $query .= '&codigoControl=' . $registro_cufd_valido->codigo_control;
        $query .= '&cufd=' . $registro_cufd_valido->codigo_cufd;

        $query .= '&tipoFacturaDocumento=' . 1;
        $query .= '&codigoEventoSignificativo=' . $data_control->codigo_registro_evento;

        $query .= '&codigoControlEvento=' . $registro_cufd_anterior->codigo_control;
        $query .= '&cufdEvento=' . $registro_cufd_anterior->codigo_cufd;

        $query .= '&factura_inicial=' . $nro_factura_inicial;
        $query .= '&factura_final=' . $nro_factura_final;

        Log::info("URL => " . $host . $path . $query);

        try {
            $response = Http::withHeaders([
                'Authorization' => $bearer,
            ])->post($host . $path . $query);
            
            $http_status = $response->status();
        } catch (\Exception $e) {
            Log::error("ERROR DE CONEXIÓN CON SERVIDOR SIAT: " . $e->getMessage());
            return 'Error: Problemas de conexión con SIAT';
        }

        $data_response = $response->json();
        
        $msj = '';
        if ($response->successful()) {
            Log::info("Response => " . json_encode($data_response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            
            // se procede a guardar y/o actualizar datos de la respuesta (response) del envío del paquete
            $data_c_contingencia_paquete->fecha_de_envio = Carbon::now();
            $data_c_contingencia_paquete->codigo_recepcion = $data_response['codigo_recepcion'];
            $data_c_contingencia_paquete->respuesta_servicio = $data_response['codigo_estado'];
            $data_c_contingencia_paquete->estado = $data_response['codigo_descripcion'];
            $data_c_contingencia_paquete->log_errores = $data_response['mensajes_recepcion'];
            $data_c_contingencia_paquete->save();

            return $data_response['codigo_descripcion'];
        } else {
            Log::error("HTTP Status: " . $response->status());
            Log::error("Response => " . json_encode($response->json(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            return 'Error: Servicios caídos';
        }
    }

    // Función para recorrer una lista de ventas pertenecientes a un paquete, y crear archivo CSV
    public function crearArchivoPaquete($listas, $data_contingencia)
    {
        if ($data_contingencia->codigo_evento > 4) {
            // si evento es mayor a 4, se precisa el cafc, y el nro_factura es manual
            foreach ($listas as $key => $value) {
                foreach ($value as $venta) {
                    $todo_cadena[] = $this->obtenerArregloVentaCafc($venta);
                }
            }
        } else {
            foreach ($listas as $key => $value) {
                foreach ($value as $venta) {
                    $todo_cadena[] = $this->obtenerArregloVenta($venta);
                }
            }
        }

        // instancia 
        log::info('Iniciando creacion de archivo CSV de facturas...');
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        if ($data_contingencia->tipo_factura == 'compra-venta') {
            $textoEncabezado = $this->textoEncabezadoCSV_CompraVenta();
        }
        if ($data_contingencia->tipo_factura == 'alquiler') {
            $textoEncabezado = $this->textoEncabezadoCSV_Alquiler();
        }
        if ($data_contingencia->tipo_factura == 'servicio-basico') {
            $textoEncabezado = $this->textoEncabezadoCSV_Servicios();
        }
        $sheet->fromArray($textoEncabezado);
        $data_row[] = "";
        $contador_fila = 2;
        foreach ($todo_cadena as $contenido) {
            foreach ($contenido as $row) {
                $sheet->fromArray($row, null, 'A' . $contador_fila);
                // para ambito de pruebas
                //$data_row[$contador_fila] = array_combine($textoEncabezado, $row);
                $contador_fila++;
            }
        }

        //log::info('El archivo CSV tiene las siguiente información: ');
        //log::info($data_row);
        // como se desea guardar
        $writer = new Csv($spreadsheet);
        $writer->setUseBOM(true);
        $writer->setDelimiter('|');
        $writer->setEnclosureRequired(false);

        $writer->save("export.csv");
        log::info('Se Genero el Archivo CSV de facturas');


        return $todo_cadena;
    }

    // Se crear un arreglo de todas las especificaciones de la respectiva venta
    public function obtenerArregloVenta($id)
    {
        $venta_id = $id;
        $pos_setting = PosSetting::latest()->first();

        $data_venta = Sale::where('id', $venta_id)->first();
        $data_biller = Biller::where('id', $data_venta->biller_id)->first();
        $data_p_venta = SiatPuntoVenta::where([
            'sucursal' => $data_biller->sucursal,
            'codigo_punto_venta' => $data_biller->punto_venta_siat
        ])->first();
        $data_siat_cufd = SiatCufd::where('sucursal', $data_p_venta->sucursal)->where('codigo_punto_venta', $data_p_venta->codigo_punto_venta)->where('estado', true)->orderBy('fecha_registro', 'desc')->first();
        $data_sucursal = SiatSucursal::where('sucursal', $data_p_venta->sucursal)->first();
        $data_cliente = CustomerSale::where('sale_id', $venta_id)->first();

        $leyendas = SiatLeyendaFactura::all();
        $data_leyenda = $leyendas->random();

        $data_gift_card = new Payment();
        // obteniendo los productos vendidos
        // y guardalos en un array
        $array_product_sales = DB::table('product_sales')->where('sale_id', '=', $venta_id)->get();

        $array_payment_sales = DB::table('payments')->where('sale_id', '=', $venta_id)->get();
        $nro_de_pagos = $array_payment_sales->count();
        foreach ($array_payment_sales as $item_pago) {
            if ($item_pago->paying_method == "Tarjeta_Regalo") {
                $data_gift_card = $item_pago;
            }
        }

        // preguntar casos de NULL, setear valor 0;
        if ($data_gift_card->amount == null) {
            $data_gift_card->amount = 0;
        }
        if ($data_venta->order_discount == null) {
            $data_venta->order_discount = 0;
        }
        $nro_tarjeta = null;
        if ($data_cliente->numero_tarjeta_credito_debito != null) {
            $nro_tarjeta = $data_cliente->numero_tarjeta_credito_debito;
        }
        $complemento = "";
        if ($data_cliente->complemento_documento != null) {
            $complemento = $data_cliente->complemento_documento;
        }



        foreach ($array_product_sales as $nro_prod_sales => $product_sales) {
            $info_product = Product::where('id', $product_sales->product_id)->first();
            $info_unit = Unit::where('id', $info_product->unit_id)->first();
            if ($info_product->unit_id > 0) {
                $info_par_unit = SiatParametricaVario::where('codigo_clasificador', $info_unit->codigo_clasificador_siat)->where('tipo_clasificador', 'unidadMedida')->first();
            } else {
                $info_par_unit['codigo_clasificador'] = "58";
                $info_par_unit['descripcion'] = "UNIDAD (SERVICIOS)";
            }
            if ($data_cliente->codigo_documento_sector == 2) {
                // Alquiler
                $data_o[$nro_prod_sales] = [
                    ($pos_setting->nit_emisor),
                    $pos_setting->razon_social_emisor,
                    $data_sucursal->ciudad_municipio,
                    $data_sucursal->telefono,

                    $data_cliente->nro_factura,
                    // cuf
                    '',
                    // cufd
                    '',
                    $data_siat_cufd->sucursal,
                    $data_siat_cufd->direccion,
                    $data_siat_cufd->codigo_punto_venta,
                    $data_cliente->created_at,

                    $data_cliente->razon_social,
                    $data_cliente->tipo_documento,
                    $data_cliente->valor_documento,
                    $complemento,
                    $data_cliente->customer_id,
                    $data_cliente->email,

                    $data_cliente->tipo_metodo_pago,
                    $nro_tarjeta,
                    number_format($data_venta->grand_total, 2),
                    number_format(($data_venta->grand_total - $data_gift_card->amount), 2),
                    1,
                    1,
                    number_format($data_venta->grand_total, 2),
                    number_format($data_gift_card->amount, 2),
                    number_format($data_venta->order_discount, 2),
                    $data_cliente->codigo_excepcion,
                    '',
                    $data_leyenda->descripcion_leyenda,
                    $user = $data_cliente->usuario,
                    $data_cliente->codigo_documento_sector,
                    $data_cliente->glosa_periodo_facturado,

                    // Detalle producto
                    $info_product->codigo_actividad,
                    $info_product->codigo_producto_servicio,
                    $info_product->code,
                    $info_product->name,
                    $product_sales->qty,
                    $info_par_unit['codigo_clasificador'],
                    number_format($info_product->price, 2),
                    number_format($product_sales->discount, 2),
                    number_format($product_sales->total, 2),
                    '',
                    '',
                    $info_par_unit['descripcion'],
                ];
            }
            if ($data_cliente->codigo_documento_sector == 1) {
                // CompraVenta
                $data_o[$nro_prod_sales] = [
                    ($pos_setting->nit_emisor),
                    $pos_setting->razon_social_emisor,
                    $data_sucursal->ciudad_municipio,
                    $data_sucursal->telefono,

                    $data_cliente->nro_factura,
                    // cuf
                    '',
                    // cufd
                    '',
                    $data_siat_cufd->sucursal,
                    $data_siat_cufd->direccion,
                    $data_siat_cufd->codigo_punto_venta,
                    $data_cliente->created_at,

                    $data_cliente->razon_social,
                    $data_cliente->tipo_documento,
                    $data_cliente->valor_documento,
                    $complemento,
                    $data_cliente->customer_id,
                    $data_cliente->email,

                    $data_cliente->tipo_metodo_pago,
                    $nro_tarjeta,
                    number_format($data_venta->grand_total, 2),
                    number_format(($data_venta->grand_total - $data_gift_card->amount), 2),
                    1,
                    1,
                    number_format($data_venta->grand_total, 2),
                    number_format($data_gift_card->amount, 2),
                    number_format($data_venta->order_discount, 2),
                    $data_cliente->codigo_excepcion,
                    '',
                    $data_leyenda->descripcion_leyenda,
                    $user = $data_cliente->usuario,
                    1,


                    //Detalle producto
                    $info_product->codigo_actividad,
                    $info_product->codigo_producto_servicio,
                    $info_product->code,
                    $info_product->name,
                    $product_sales->qty,
                    $info_par_unit['codigo_clasificador'],
                    number_format($info_product->price, 2),
                    number_format($product_sales->discount, 2),
                    number_format($product_sales->total, 2),
                    '',
                    '',
                    $info_par_unit['descripcion'],
                ];
            }
            if ($data_cliente->codigo_documento_sector == 13) {
                // FACTURA SERVICIOS
                $data_o[$nro_prod_sales] = [
                    '' . ($pos_setting->nit_emisor),
                    '' . $pos_setting->razon_social_emisor,
                    '' . $data_sucursal->ciudad_municipio,
                    '' . $data_sucursal->telefono,

                    '' . $data_cliente->nro_factura,
                    // cuf
                    '',
                    // cufd
                    '',
                    '' . $data_cliente->sucursal,
                    '' . $data_siat_cufd->direccion,
                    '' . $data_cliente->codigo_punto_venta,
                    '' . $data_cliente->created_at,

                    '' . $data_cliente->razon_social,
                    '' . $data_cliente->tipo_documento,
                    '' . $data_cliente->valor_documento,
                    '' . $complemento,
                    '' . $data_cliente->codigofijo,
                    '' . $data_cliente->email,

                    '' . $data_cliente->tipo_metodo_pago,
                    '' . $nro_tarjeta,
                    '' . number_format(($data_venta->grand_total + $data_cliente->tasa_aseo + $data_cliente->tasa_alumbrado + $data_cliente->otras_tasas + $data_cliente->ajuste_sujeto_iva), 2),
                    '' . number_format(($data_venta->grand_total - $data_gift_card->amount + $data_cliente->ajuste_sujeto_iva), 2),
                    1,
                    1,
                    '' . number_format(($data_venta->grand_total + $data_cliente->tasa_aseo + $data_cliente->tasa_alumbrado + $data_cliente->otras_tasas + $data_cliente->ajuste_sujeto_iva), 2),

                    '' . number_format($data_venta->order_discount, 2),
                    '' . $data_cliente->codigo_excepcion,
                    // cafc
                    '',
                    '' . $data_leyenda->descripcion_leyenda,
                    '' . $user = $data_cliente->usuario,
                    '' . $data_cliente->codigo_documento_sector,

                    '' . $data_cliente->mes,
                    '' . $data_cliente->gestion,
                    '' . $data_cliente->ciudad,
                    '' . $data_cliente->zona,
                    '' . $data_cliente->categoria,
                    '' . $data_cliente->numero_medidor,
                    '' . $data_cliente->lectura_medidor_anterior,
                    '' . $data_cliente->lectura_medidor_actual,
                    '' . $data_cliente->domicilio_cliente,
                    '' . $data_cliente->consumo_periodo,

                    '' . $data_cliente->beneficiario_ley_1886,
                    '' . $data_cliente->monto_descuento_ley_1886,
                    '' . $data_cliente->monto_descuento_tarifa_dignidad,

                    '' . $data_cliente->tasa_aseo,
                    '' . $data_cliente->tasa_alumbrado,
                    '' . $data_cliente->otras_tasas,

                    '' . $data_cliente->ajuste_no_sujeto_iva,
                    '' . $data_cliente->detalle_ajuste_no_sujeto_iva,

                    '' . $data_cliente->ajuste_sujeto_iva,
                    '' . $data_cliente->detalle_ajuste_sujeto_iva,

                    '' . $data_cliente->otros_pagos_no_sujeto_iva,
                    '' . $data_cliente->detalle_otros_pagos_no_sujeto_iva,

                    // Detalle producto
                    '' . $info_product->codigo_actividad,
                    '' . $info_product->codigo_producto_servicio,
                    '' . $info_product->code,
                    '' . $info_product->name,
                    '' . $product_sales->qty,
                    '' . $info_par_unit['codigo_clasificador'],
                    '' . number_format($product_sales->net_unit_price, 2),
                    '' . number_format($product_sales->discount, 2),
                    '' . number_format($product_sales->total, 2),
                    '' . $info_par_unit['descripcion'],
                ];
            }
        }

        return $data_o;
    }

    // Arreglo de ventas con evento significativos con cafc
    public function obtenerArregloVentaCafc($id)
    {
        $venta_id = $id;
        $pos_setting = PosSetting::latest()->first();

        $data_venta = Sale::where('id', $venta_id)->first();
        $data_biller = Biller::where('id', $data_venta->biller_id)->first();
        $data_p_venta = SiatPuntoVenta::where([
            'sucursal' => $data_biller->sucursal,
            'codigo_punto_venta' => $data_biller->punto_venta_siat
        ])->first();
        $data_siat_cufd = SiatCufd::where('sucursal', $data_p_venta->sucursal)->where('codigo_punto_venta', $data_p_venta->codigo_punto_venta)->where('estado', true)->orderBy('fecha_registro', 'desc')->first();
        $data_sucursal = SiatSucursal::where('sucursal', $data_p_venta->sucursal)->first();
        $data_cliente = CustomerSale::where('sale_id', $venta_id)->first();

        $leyendas = SiatLeyendaFactura::all();
        $data_leyenda = $leyendas->random();

        $data_gift_card = new Payment();
        // obteniendo los productos vendidos
        // y guardalos en un array
        $array_product_sales = DB::table('product_sales')->where('sale_id', '=', $venta_id)->get();

        $array_payment_sales = DB::table('payments')->where('sale_id', '=', $venta_id)->get();
        $nro_de_pagos = $array_payment_sales->count();
        foreach ($array_payment_sales as $item_pago) {
            if ($item_pago->paying_method == "Tarjeta_Regalo") {
                $data_gift_card = $item_pago;
            }
        }

        // preguntar casos de NULL, setear valor 0;
        if ($data_gift_card->amount == null) {
            $data_gift_card->amount = 0;
        }
        if ($data_venta->order_discount == null) {
            $data_venta->order_discount = 0;
        }
        $nro_tarjeta = null;
        if ($data_cliente->numero_tarjeta_credito_debito != null) {
            $nro_tarjeta = $data_cliente->numero_tarjeta_credito_debito;
        }
        $complemento = "";
        if ($data_cliente->complemento_documento != null) {
            $complemento = $data_cliente->complemento_documento;
        }



        foreach ($array_product_sales as $nro_prod_sales => $product_sales) {
            $info_product = Product::where('id', $product_sales->product_id)->first();
            $info_unit = Unit::where('id', $info_product->unit_id)->first();
            if ($info_product->unit_id > 0) {
                $info_par_unit = SiatParametricaVario::where('codigo_clasificador', $info_unit->codigo_clasificador_siat)->where('tipo_clasificador', 'unidadMedida')->first();
            } else {
                $info_par_unit['codigo_clasificador'] = "58";
                $info_par_unit['descripcion'] = "UNIDAD (SERVICIOS)";
            }
            if ($data_cliente->codigo_documento_sector == 2) {
                // Alquiler
                $data_o[$nro_prod_sales] = [
                    ($pos_setting->nit_emisor),
                    $pos_setting->razon_social_emisor,
                    $data_sucursal->ciudad_municipio,
                    $data_sucursal->telefono,

                    $data_cliente->nro_factura_manual,
                    // cuf
                    '',
                    // cufd
                    '',
                    $data_siat_cufd->sucursal,
                    $data_siat_cufd->direccion,
                    $data_siat_cufd->codigo_punto_venta,
                    $data_cliente->fecha_manual,

                    $data_cliente->razon_social,
                    $data_cliente->tipo_documento,
                    $data_cliente->valor_documento,
                    $complemento,
                    $data_cliente->customer_id,
                    $data_cliente->email,

                    $data_cliente->tipo_metodo_pago,
                    $nro_tarjeta,
                    number_format($data_venta->grand_total, 2),
                    number_format(($data_venta->grand_total - $data_gift_card->amount), 2),
                    1,
                    1,
                    number_format($data_venta->grand_total, 2),
                    number_format($data_gift_card->amount, 2),
                    number_format($data_venta->order_discount, 2),
                    $data_cliente->codigo_excepcion,
                    '',
                    $data_leyenda->descripcion_leyenda,
                    $user = $data_cliente->usuario,
                    $data_cliente->codigo_documento_sector,
                    $data_cliente->glosa_periodo_facturado,

                    // Detalle producto
                    $info_product->codigo_actividad,
                    $info_product->codigo_producto_servicio,
                    $info_product->code,
                    $info_product->name,
                    $product_sales->qty,
                    $info_par_unit['codigo_clasificador'],
                    number_format($info_product->price, 2),
                    number_format($product_sales->discount, 2),
                    number_format($product_sales->total, 2),
                    '',
                    '',
                    $info_par_unit['descripcion'],
                ];
            }
            if ($data_cliente->codigo_documento_sector == 1) {
                // CompraVenta
                $data_o[$nro_prod_sales] = [
                    ($pos_setting->nit_emisor),
                    $pos_setting->razon_social_emisor,
                    $data_sucursal->ciudad_municipio,
                    $data_sucursal->telefono,

                    $data_cliente->nro_factura_manual,
                    // cuf
                    '',
                    // cufd
                    '',
                    $data_siat_cufd->sucursal,
                    $data_siat_cufd->direccion,
                    $data_siat_cufd->codigo_punto_venta,
                    $data_cliente->fecha_manual,

                    $data_cliente->razon_social,
                    $data_cliente->tipo_documento,
                    $data_cliente->valor_documento,
                    $complemento,
                    $data_cliente->customer_id,
                    $data_cliente->email,

                    $data_cliente->tipo_metodo_pago,
                    $nro_tarjeta,
                    number_format($data_venta->grand_total, 2),
                    number_format(($data_venta->grand_total - $data_gift_card->amount), 2),
                    1,
                    1,
                    number_format($data_venta->grand_total, 2),
                    number_format($data_gift_card->amount, 2),
                    number_format($data_venta->order_discount, 2),
                    $data_cliente->codigo_excepcion,
                    '',
                    $data_leyenda->descripcion_leyenda,
                    $user = $data_cliente->usuario,
                    1,


                    //Detalle producto
                    $info_product->codigo_actividad,
                    $info_product->codigo_producto_servicio,
                    $info_product->code,
                    $info_product->name,
                    $product_sales->qty,
                    $info_par_unit['codigo_clasificador'],
                    number_format($info_product->price, 2),
                    number_format($product_sales->discount, 2),
                    number_format($product_sales->total, 2),
                    '',
                    '',
                    $info_par_unit['descripcion'],
                ];
            }
            if ($data_cliente->codigo_documento_sector == 13) {
                // FACTURA SERVICIOS
                $data_o[$nro_prod_sales] = [
                    '' . ($pos_setting->nit_emisor),
                    '' . $pos_setting->razon_social_emisor,
                    '' . $data_sucursal->ciudad_municipio,
                    '' . $data_sucursal->telefono,

                    '' . $data_cliente->nro_factura_manual,
                    // cuf
                    '',
                    // cufd
                    '',
                    '' . $data_cliente->sucursal,
                    '' . $data_siat_cufd->direccion,
                    '' . $data_cliente->codigo_punto_venta,
                    '' . $data_cliente->fecha_manual,

                    '' . $data_cliente->razon_social,
                    '' . $data_cliente->tipo_documento,
                    '' . $data_cliente->valor_documento,
                    '' . $complemento,
                    '' . $data_cliente->customer_id,
                    '' . $data_cliente->email,

                    '' . $data_cliente->tipo_metodo_pago,
                    '' . $nro_tarjeta,
                    '' . number_format($data_venta->grand_total, 2),
                    '' . number_format(($data_venta->grand_total - $data_gift_card->amount), 2),
                    1,
                    1,
                    '' . number_format($data_venta->grand_total, 2),

                    '' . number_format($data_venta->order_discount, 2),
                    '' . $data_cliente->codigo_excepcion,
                    // cafc
                    '',
                    '' . $data_leyenda->descripcion_leyenda,
                    '' . $user = $data_cliente->usuario,
                    '' . $data_cliente->codigo_documento_sector,

                    '' . $data_cliente->mes,
                    '' . $data_cliente->gestion,
                    '' . $data_cliente->ciudad,
                    '' . $data_cliente->zona,
                    '' . $data_cliente->categoria,
                    '' . $data_cliente->numero_medidor,
                    '' . $data_cliente->lectura_medidor_anterior,
                    '' . $data_cliente->lectura_medidor_actual,
                    '' . $data_cliente->domicilio_cliente,
                    '' . $data_cliente->consumo_periodo,

                    '' . $data_cliente->beneficiario_ley_1886,
                    '' . $data_cliente->monto_descuento_ley_1886,
                    '' . $data_cliente->monto_descuento_tarifa_dignidad,

                    '' . $data_cliente->tasa_aseo,
                    '' . $data_cliente->tasa_alumbrado,
                    '' . $data_cliente->otras_tasas,

                    '' . $data_cliente->ajuste_no_sujeto_iva,
                    '' . $data_cliente->detalle_ajuste_no_sujeto_iva,

                    '' . $data_cliente->ajuste_sujeto_iva,
                    '' . $data_cliente->detalle_ajuste_sujeto_iva,

                    '' . $data_cliente->otros_pagos_no_sujeto_iva,
                    '' . $data_cliente->detalle_otros_pagos_no_sujeto_iva,

                    // Detalle producto
                    '' . $info_product->codigo_actividad,
                    '' . $info_product->codigo_producto_servicio,
                    '' . $info_product->code,
                    '' . $info_product->name,
                    '' . $product_sales->qty,
                    '' . $info_par_unit['codigo_clasificador'],
                    '' . number_format($product_sales->net_unit_price, 2),
                    '' . number_format($product_sales->discount, 2),
                    '' . number_format($product_sales->total, 2),
                    '' . $info_par_unit['descripcion'],
                ];
            }
        }

        return $data_o;
    }

    // texto cabecera de archivo csv
    public function textoEncabezadoCSV_CompraVenta()
    {
        return [
            "nitEmisor",
            "razonSocialEmisor",
            "municipio",
            "telefono",
            "numeroFactura",
            "cuf",
            "cufd",
            "codigoSucursal",
            "direccion",
            "codigoPuntoVenta",
            "fechaEmision",
            "nombreRazonSocial",
            "codigoTipoDocumentoIdentidad",
            "numeroDocumento",
            "complemento",
            "codigoCliente",
            "email",
            "codigoMetodoPago",
            "numeroTarjeta",
            "montoTotal",
            "montoTotalSujetoIva",
            "codigoMoneda",
            "tipoCambio",
            "montoTotalMoneda",
            "montoGiftCard",
            "descuentoAdicional",
            "codigoExcepcion",
            "cafc",
            "leyenda",
            "usuario",
            "codigoDocumentoSector",
            "actividadEconomica",
            "codigoProductoSin",
            "codigoProducto",
            "descripcion",
            "cantidad",
            "unidadMedida",
            "precioUnitario",
            "montoDescuento",
            "subTotal",
            "numeroSerie",
            "numeroImei",
            "nombreUnidadMedida"
        ];
    }

    // texto cabecera de archivo csv
    public function textoEncabezadoCSV_Alquiler()
    {
        return [
            "nitEmisor",
            "razonSocialEmisor",
            "municipio",
            "telefono",
            "numeroFactura",
            "cuf",
            "cufd",
            "codigoSucursal",
            "direccion",
            "codigoPuntoVenta",
            "fechaEmision",
            "nombreRazonSocial",
            "codigoTipoDocumentoIdentidad",
            "numeroDocumento",
            "complemento",
            "codigoCliente",
            "email",
            "codigoMetodoPago",
            "numeroTarjeta",
            "montoTotal",
            "montoTotalSujetoIva",
            "codigoMoneda",
            "tipoCambio",
            "montoTotalMoneda",
            "montoGiftCard",
            "descuentoAdicional",
            "codigoExcepcion",
            "cafc",
            "leyenda",
            "usuario",
            "codigoDocumentoSector",
            "periodoFacturado",
            "actividadEconomica",
            "codigoProductoSin",
            "codigoProducto",
            "descripcion",
            "cantidad",
            "unidadMedida",
            "precioUnitario",
            "montoDescuento",
            "subTotal",
            "numeroSerie",
            "numeroImei",
            "nombreUnidadMedida"
        ];
    }

    private function textoEncabezadoCSV_Servicios()
    {
        return [
            "nitEmisor",
            "razonSocialEmisor",
            "municipio",
            "telefono",
            "numeroFactura",
            "cuf",
            "cufd",
            "codigoSucursal",
            "direccion",
            "codigoPuntoVenta",
            "fechaEmision",
            "nombreRazonSocial",
            "codigoTipoDocumentoIdentidad",
            "numeroDocumento",
            "complemento",
            "codigoCliente",
            "email",
            "codigoMetodoPago",
            "numeroTarjeta",
            "montoTotal",
            "montoTotalSujetoIva",
            "codigoMoneda",
            "tipoCambio",
            "montoTotalMoneda",
            // "montoGiftCard",
            "descuentoAdicional",
            "codigoExcepcion",
            "cafc",
            "leyenda",
            "usuario",
            "codigoDocumentoSector",
            // "glosa_periodo_facturado",
            "mes",
            "gestion",
            "ciudad",
            "zona",
            "categoria",
            "numeroMedidor",
            "lecturaMedidorAnterior",
            "lecturaMedidorActual",
            "domicilioCliente",
            "consumoPeriodo",
            "beneficiarioLey1886",
            "montoDescuentoLey1886",
            "montoDescuentoTarifaDignidad",
            "tasaAseo",
            "tasaAlumbrado",
            "otrasTasas",
            "ajusteNoSujetoIva",
            "DetalleAjusteNoSujetoIva",
            "AjusteSujetoIva",
            "DetalleAjusteSujetoIva",
            "OtrosPagosNoSujetoIva",
            "DetalleOtrosPagosNoSujetoIva",
            "actividadEconomica",
            "codigoProductoSin",
            "codigoProducto",
            "descripcion",
            "cantidad",
            "unidadMedida",
            "precioUnitario",
            "montoDescuento",
            "subTotal",
            "nombreUnidad"
        ];
    }

    // funcion para verificar el estado del envío de un paquete específico
    public function verificarEstadoPaquete($control_contingencia_paquete_id)
    {
        $data_c_contingencia_paquete = ControlContingenciaPaquetes::where('id', $control_contingencia_paquete_id)->first();
        $control_contingencia_id = $data_c_contingencia_paquete->control_contingencia_id;

        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_operaciones;
        $path = '/factura.venta/factura.paquete.validacion';

        $data_control = ControlContingencia::where('id', $control_contingencia_id)->first();
        $registro_cufd_valido = SiatCufd::where('estado', true)->where('codigo_punto_venta', $data_control->codigo_punto_venta)->where('sucursal', $data_control->sucursal)->orderBy('fecha_registro', 'desc')->first();

        // query o body
        $query = [
            'codigoDocumento' => $data_control->codigo_documento_sector,
            'tipoFacturaDocumento' => "1",

            'nit' => $pos_setting->nit_emisor,
            'codigoRecepcion' => $data_c_contingencia_paquete->codigo_recepcion,
            'codigoControl' => $registro_cufd_valido->codigo_control,
            'cufd' => $registro_cufd_valido->codigo_cufd,

            'cuis' => $data_control->cuis,
            'sucursal' => $data_control->sucursal,
            'codigoPuntoVenta' => $data_control->codigo_punto_venta,
        ];

        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->post($host . $path, $query);

        log::info($host . $path, $query);
        return $response;
    }

    public function getImprimirFactura($venta_id, $id = null)
    {
        if ($venta_id == null) {
            $data_cliente = CustomerSale::find($id);
        } else {
            $data_cliente = CustomerSale::where('sale_id', $venta_id)->first();
        }
        $tipo_impresion = "rollo";

        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_operaciones;
        $path = '/factura.venta/imprimir?';

        $nro_impresion = $pos_setting->type_print;
        $tipo_impresion = $this->formatoImpresion($nro_impresion);

        $cuf = 'cuf=' . $data_cliente->cuf;
        $formato = '&formato=' . $tipo_impresion;
        $query = $cuf . $formato;

        log::info($host . $path . $query);
        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->get($host . $path . $query);

        $respuesta = $response->json();
        if ($response->successful()) {
            log::info("Impresión de la factura con CUF=" . $data_cliente->cuf . ", RESPUESTA: " . $respuesta['ESTADO']);
        }
        if ($response->serverError()) {
            log::info("Error en impresión de la factura, response => " . json_encode($respuesta));
        }
        return $response->json();
    }

    public function getImprimirFacturaCuf($cuf)
    {
        $tipo_impresion = "rollo";
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_operaciones;
        $path = '/factura.venta/imprimir?';

        $nro_impresion = $pos_setting->type_print;
        $tipo_impresion = $this->formatoImpresion($nro_impresion);


        $cuf = 'cuf=' . $cuf;
        $formato = '&formato=' . $tipo_impresion;
        $query = $cuf . $formato;
        log::info($host . $path . $query);

        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->get($host . $path . $query);

        $respuesta = $response->json();
        $result = [];
        if ($response->successful()) {
            log::info("Impresión de la factura con CUF=" . $cuf . ", RESPUESTA: " . $respuesta['ESTADO']);
            $result = $response->json();
        }
        if ($response->serverError()) {
            log::info("Error en impresión de la factura, response => " . json_encode($respuesta));
            $result = array("error" => $respuesta, "ESTADO" => "NOK");
        }
        return $result;
    }


    public function generarFacturaIndividualAlquiler($id)
    {

        $venta_id = $id;
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_operaciones;
        $path = '/factura.venta/factura.individual';

        $data_venta = Sale::where('id', $venta_id)->first();
        $data_biller = Biller::where('id', $data_venta->biller_id)->first();
        $data_p_venta = SiatPuntoVenta::where([
            'sucursal' => $data_biller->sucursal,
            'codigo_punto_venta' => $data_biller->punto_venta_siat
        ])->first();
        $data_siat_cufd = SiatCufd::where('sucursal', $data_p_venta->sucursal)->where('codigo_punto_venta', $data_p_venta->codigo_punto_venta)->where('estado', true)->orderBy('fecha_registro', 'desc')->first();
        $data_sucursal = SiatSucursal::where('sucursal', $data_p_venta->sucursal)->first();
        $data_cliente = CustomerSale::where('sale_id', $venta_id)->first();

        $leyendas = SiatLeyendaFactura::all();
        $data_leyenda = $leyendas->random();

        $tipo_impresion = 'rollo';
        $nro_impresion = $pos_setting->type_print;
        $tipo_impresion = $this->formatoImpresion($nro_impresion);

        $data_gift_card = new Payment();
        // obteniendo los productos vendidos
        // y guardalos en un array
        $array_product_sales = DB::table('product_sales')->where('sale_id', '=', $venta_id)->get();
        foreach ($array_product_sales as $nro_prod_sales => $product_sales) {
            $info_product = Product::where('id', $product_sales->product_id)->first();
            $info_par_unit = null;

            if ($info_product && $info_product->unit_id > 0) {
                $info_unit = Unit::where('id', $info_product->unit_id)->first();
                if ($info_unit) {
                    $info_par_unit = SiatParametricaVario::where('codigo_clasificador', $info_unit->codigo_clasificador_siat)->where('tipo_clasificador', 'unidadMedida')->first();
                }
            } else {
                $info_par_unit = [
                    'codigo_clasificador' => "58",
                    'descripcion' => "UNIDAD (SERVICIOS)",
                ];
            }

            if (!$info_par_unit) {
                $info_par_unit = [
                    'codigo_clasificador' => "58",
                    'descripcion' => "UNIDAD (SERVICIOS)",
                ];
            }

            $descripcion_adicional = "";
            if ($product_sales->description != null) {
                $descripcion_adicional = ' - ' . $product_sales->description;
            }

            $data_product_sales[$nro_prod_sales] = array(
                "actividadEconomica" => $info_product->codigo_actividad,
                "cantidad" => $product_sales->qty,
                "codigoProducto" => $info_product->code,
                "codigoProductoSin" => $info_product->codigo_producto_servicio,
                "descripcion" => $info_product->name . $descripcion_adicional,
                "montoDescuento" => number_format($product_sales->discount, 2, '.', ''),
                "numeroImei" => "",
                "numeroSerie" => "",
                "precioUnitario" => number_format($product_sales->net_unit_price, 2, '.', ''),
                "subTotal" => number_format($product_sales->total, 2, '.', ''),
                "unidadMedida" => $info_par_unit['codigo_clasificador'],
                "nombreUnidadMedida" => $info_par_unit['descripcion'],
            );
        }
        $array_payment_sales = DB::table('payments')->where('sale_id', '=', $venta_id)->get();
        $nro_de_pagos = $array_payment_sales->count();
        foreach ($array_payment_sales as $item_pago) {
            if ($item_pago->paying_method == "Tarjeta_Regalo") {
                $data_gift_card = $item_pago;
            }
        }

        // preguntar casos de NULL, setear valor 0;
        if ($data_gift_card->amount == null) {
            $data_gift_card->amount = 0;
        }
        if ($data_venta->order_discount == null) {
            $data_venta->order_discount = 0;
        }
        $nro_tarjeta = null;
        if ($data_cliente->numero_tarjeta_credito_debito != null) {
            $nro_tarjeta = $data_cliente->numero_tarjeta_credito_debito;
        }
        $complemento = "";
        if ($data_cliente->complemento_documento != null) {
            $complemento = $data_cliente->complemento_documento;
        }
        $listAdicionales = null;
        if ($data_venta->sale_note != null || $data_venta->sale_note != '') {
            $listAdicionales[] = array("etiqueta" => "paciente", "valor" => $data_venta->sale_note);
        }
        // Construir data_body base - Alquiler
        $data_body = [
            'codigoControl' => $data_siat_cufd->codigo_control,
            'codigoDocumento' => $data_cliente->codigo_documento_sector,
            'codigoPuntoVenta' => $data_siat_cufd->codigo_punto_venta,
            'cuis' => $data_p_venta->codigo_cuis,
            'cuis' => $data_p_venta->codigo_cuis,
            'nit' => $pos_setting->nit_emisor,
            'sucursal' => $data_siat_cufd->sucursal,
            'formatoFactura' => $tipo_impresion,
            'adicionales' => $listAdicionales,
            'factura' => [
                'nitEmisor' => $pos_setting->nit_emisor,
                'razonSocialEmisor' => $pos_setting->razon_social_emisor,
                'direccion' => $data_siat_cufd->direccion,
                'fechaEmision' => "",
                'leyenda' => $data_leyenda->descripcion_leyenda,
                'montoGiftCard' => number_format($data_gift_card->amount, 2, '.', ''),
                'montoTotal' => number_format($data_venta->grand_total, 2, '.', ''),
                'montoTotalMoneda' => number_format($data_venta->grand_total, 2, '.', ''),
                'montoTotalSujetoIva' => number_format(($data_venta->grand_total - $data_gift_card->amount), 2, '.', ''),
                "codigoMetodoPago" => $data_cliente->tipo_metodo_pago,
                "municipio" => $data_sucursal->ciudad_municipio,

                "nombreRazonSocial" => $data_cliente->razon_social,
                "numeroDocumento" => $data_cliente->valor_documento,
                "numeroFactura" => $data_cliente->nro_factura,
                "numeroTarjeta" => $nro_tarjeta,
                "telefono" => $data_sucursal->telefono,
                "tipoCambio" => 1,
                "usuario" => $data_cliente->usuario,
                "cafc" => "",
                "codigoCliente" => $data_cliente->customer_id,
                "email" => $data_cliente->email,

                "periodoFacturado" => $data_cliente->glosa_periodo_facturado,
                "codigoDocumentoSector" => $data_cliente->codigo_documento_sector,
                "codigoExcepcion" => $data_cliente->codigo_excepcion,
                "codigoMoneda" => 1,
                "codigoPuntoVenta" => $data_siat_cufd->codigo_punto_venta,
                "codigoSucursal" => $data_siat_cufd->sucursal,
                "codigoTipoDocumentoIdentidad" => $data_cliente->tipo_documento,
                "complemento" => $complemento,
                "cuf" => "",
                "cufd" => "",
                "descuentoAdicional" => number_format($data_venta->order_discount, 2, '.', ''),
                "detalle" => $data_product_sales,
            ]
        ];
        
        // Solo enviar CUFD cuando NO esté en modo centralizado - Alquiler
        if (($pos_setting->cufd_centralizado ?? 0) == 0) {
            $data_body['cufd'] = $data_siat_cufd->codigo_cufd;
            log::info('CUFD incluido en request (modo estándar) - Alquiler');
        } else {
            log::info('CUFD NO incluido en request (modo centralizado - flag activa) - Alquiler');
        }

        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->post($host . $path, $data_body);
        log::info("URL => " . $host . $path, $data_body);
        log::info("Body => " . json_encode($data_body));

        $respuesta = array();

        if ($response->successful()) {

            $update_customer_sale = CustomerSale::where('sale_id', $data_cliente->sale_id)->first();
            $data_json = $response->json();
            $update_customer_sale->codigo_recepcion = $data_json['codigo_recepcion'];
            $update_customer_sale->cuf = $data_json['cuf'];
            $update_customer_sale->codigo_cufd = $data_siat_cufd->codigo_cufd;
            $update_customer_sale->xml = $data_json['xmlfactura'];
            $update_customer_sale->estado_factura = "VIGENTE";
            $update_customer_sale->save();

            $msj = "Venta Facturada correctamente";
            $respuesta = array('mensaje' => $msj, 'status' => true);
        } else {
            $error = $response->json();
            $titulo_error = $error['status'];
            if ($titulo_error == 500) {
                $respuesta = array('mensaje' => 'Error interno del servidor. ', 'status' => false);
            } elseif ($titulo_error == 400) {
                $mensajes_error = $error['mensajesRecepcion'];
                log::warning("mensajes de Error => " . json_encode($mensajes_error));
                $descripcion = "";
                foreach ($mensajes_error as $mensaje) {
                    $descripcion .= " Código: " . $mensaje['codigo'] . " - Descripción: " . $mensaje['descripcion'];
                    log::info($descripcion);
                }
                $msj = 'Problemas de conexión Siat, la venta no ha sido facturada. Error: ' . $titulo_error . $descripcion;
                $respuesta = array('mensaje' => $msj, 'status' => false);
            }
            log::warning('Error! archivo SiatTrait, generarFacturaIndividualAlquiler => ');
            log::warning($error);
        }
        return $respuesta;
    }


    public function generarFacturaServicioBasico($id)
    {
        $venta_id = $id;
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_operaciones;
        $path = '/factura.venta/factura.individual';

        $data_venta = Sale::where('id', $venta_id)->first();
        $data_biller = Biller::where('id', $data_venta->biller_id)->first();
        $data_p_venta = SiatPuntoVenta::where([
            'sucursal' => $data_biller->sucursal,
            'codigo_punto_venta' => $data_biller->punto_venta_siat
        ])->first();
        $data_siat_cufd = SiatCufd::where('sucursal', $data_p_venta->sucursal)->where('codigo_punto_venta', $data_p_venta->codigo_punto_venta)->where('estado', true)->orderBy('fecha_registro', 'desc')->first();
        $data_sucursal = SiatSucursal::where('sucursal', $data_p_venta->sucursal)->first();
        $data_cliente = CustomerSale::where('sale_id', $venta_id)->first();

        $leyendas = SiatLeyendaFactura::all();
        $data_leyenda = $leyendas->random();

        $tipo_impresion = 'rollo';
        $nro_impresion = $pos_setting->type_print;
        $tipo_impresion = $this->formatoImpresion($nro_impresion);

        $data_gift_card = new Payment();
        // obteniendo los productos vendidos
        // y guardalos en un array
        $array_product_sales = DB::table('product_sales')->where('sale_id', '=', $venta_id)->get();
        foreach ($array_product_sales as $nro_prod_sales => $product_sales) {
            $info_product = Product::where('id', $product_sales->product_id)->first();
            $info_par_unit = null;

            if ($info_product && $info_product->unit_id > 0) {
                $info_unit = Unit::where('id', $info_product->unit_id)->first();
                if ($info_unit) {
                    $info_par_unit = SiatParametricaVario::where('codigo_clasificador', $info_unit->codigo_clasificador_siat)->where('tipo_clasificador', 'unidadMedida')->first();
                }
            } else {
                $info_par_unit = [
                    'codigo_clasificador' => "58",
                    'descripcion' => "UNIDAD (SERVICIOS)",
                ];
            }

            if (!$info_par_unit) {
                $info_par_unit = [
                    'codigo_clasificador' => "58",
                    'descripcion' => "UNIDAD (SERVICIOS)",
                ];
            }

            if ($data_cliente->codigo_documento_sector == 13) {
                $info_product->price = $info_product->price - $data_cliente->monto_descuento_ley_1886 - $data_cliente->monto_descuento_tarifa_dignidad;
            }

            $descripcion_adicional = "";
            if ($product_sales->description != null) {
                $descripcion_adicional = ' - ' . $product_sales->description;
            }

            $data_product_sales[$nro_prod_sales] = array(
                "actividadEconomica" => $info_product->codigo_actividad,
                "cantidad" => $product_sales->qty,
                "codigoProducto" => $info_product->code,
                "codigoProductoSin" => $info_product->codigo_producto_servicio,
                "descripcion" => $info_product->name . $descripcion_adicional,
                "montoDescuento" => number_format($product_sales->discount, 2, '.', ''),
                "numeroImei" => "",
                "numeroSerie" => "",
                "precioUnitario" => number_format($product_sales->net_unit_price, 2, '.', ''),
                "subTotal" => number_format($product_sales->total, 2, '.', ''),
                "unidadMedida" => $info_par_unit['codigo_clasificador'],
                "nombreUnidadMedida" => $info_par_unit['descripcion'],
            );
        }
        $array_payment_sales = DB::table('payments')->where('sale_id', '=', $venta_id)->get();
        $nro_de_pagos = $array_payment_sales->count();
        foreach ($array_payment_sales as $item_pago) {
            if ($item_pago->paying_method == "Tarjeta_Regalo") {
                $data_gift_card = $item_pago;
            }
        }

        // preguntar casos de NULL, setear valor 0;
        if ($data_gift_card->amount == null) {
            $data_gift_card->amount = 0;
        }
        if ($data_venta->order_discount == null) {
            $data_venta->order_discount = 0;
        }
        $nro_tarjeta = null;
        if ($data_cliente->numero_tarjeta_credito_debito != null) {
            $nro_tarjeta = $data_cliente->numero_tarjeta_credito_debito;
        }
        $complemento = "";
        if ($data_cliente->complemento_documento != null) {
            $complemento = $data_cliente->complemento_documento;
        }
        if ($data_cliente->beneficiario_ley_1886 == null) {
            $data_cliente->beneficiario_ley_1886 = 0;
        }
        $listAdicionales = null;
        if ($data_venta->sale_note != null || $data_venta->sale_note != '') {
            $listAdicionales[] = array("etiqueta" => "paciente", "valor" => $data_venta->sale_note);
        }
        // Construir data_body base
        $data_body = [
            'codigoControl' => $data_siat_cufd->codigo_control,
            'codigoDocumento' => $data_cliente->codigo_documento_sector,
            'codigoPuntoVenta' => $data_siat_cufd->codigo_punto_venta,
            'cuis' => $data_p_venta->codigo_cuis,
            'nit' => $pos_setting->nit_emisor,
            'sucursal' => $data_siat_cufd->sucursal,
            'formatoFactura' => $tipo_impresion,
            'adicionales' => $listAdicionales,
            'factura' => [
                'nitEmisor' => $pos_setting->nit_emisor,
                'razonSocialEmisor' => $pos_setting->razon_social_emisor,
                'direccion' => $data_siat_cufd->direccion,
                'fechaEmision' => "",
                'leyenda' => $data_leyenda->descripcion_leyenda,
                'montoGiftCard' => number_format($data_gift_card->amount, 2, '.', ''),
                'montoTotal' => number_format(($data_venta->grand_total + $data_cliente->tasa_aseo + $data_cliente->tasa_alumbrado + $data_cliente->otras_tasas + $data_cliente->ajuste_sujeto_iva + $data_cliente->otros_pagos_no_sujeto_iva), 2, '.', ''),
                'montoTotalMoneda' => number_format(($data_venta->grand_total + $data_cliente->tasa_aseo + $data_cliente->tasa_alumbrado + $data_cliente->otras_tasas + $data_cliente->ajuste_sujeto_iva + $data_cliente->otros_pagos_no_sujeto_iva), 2, '.', ''),
                'montoTotalSujetoIva' => number_format(($data_venta->grand_total - $data_gift_card->amount + $data_cliente->ajuste_sujeto_iva), 2, '.', ''),
                "codigoMetodoPago" => $data_cliente->tipo_metodo_pago,
                "municipio" => $data_sucursal->ciudad_municipio,
                "nombreRazonSocial" => $data_cliente->razon_social,
                "numeroDocumento" => $data_cliente->valor_documento,
                "numeroFactura" => $data_cliente->nro_factura,
                "numeroTarjeta" => $nro_tarjeta,
                "telefono" => $data_sucursal->telefono,
                "tipoCambio" => 1,
                "usuario" => $data_cliente->usuario,
                "cafc" => "",
                "codigoCliente" => $data_cliente->codigofijo,
                "email" => $data_cliente->email,
                "periodoFacturado" => $data_cliente->glosa_periodo_facturado,
                "codigoDocumentoSector" => $data_cliente->codigo_documento_sector,
                "codigoExcepcion" => $data_cliente->codigo_excepcion,
                "codigoMoneda" => 1,
                "codigoPuntoVenta" => $data_siat_cufd->codigo_punto_venta,
                "codigoSucursal" => $data_siat_cufd->sucursal,
                "codigoTipoDocumentoIdentidad" => $data_cliente->tipo_documento,
                "complemento" => $complemento,
                "cuf" => "",
                "cufd" => "",
                "descuentoAdicional" => number_format($data_venta->order_discount, 2, '.', ''),

                "mes" => $data_cliente->mes,
                "gestion" => $data_cliente->gestion,
                "ciudad" => $data_cliente->ciudad,
                "numeroMedidor" => $data_cliente->numero_medidor,
                "domicilioCliente" => $data_cliente->domicilio_cliente,
                "consumoPeriodo" => $data_cliente->consumo_periodo,
                "beneficiarioLey1886" => $data_cliente->beneficiario_ley_1886,
                "montoDescuentoLey1886" => 0 + $data_cliente->monto_descuento_ley_1886,
                "montoDescuentoTarifaDignidad" => 0 + $data_cliente->monto_descuento_tarifa_dignidad,
                "tasaAseo" => $data_cliente->tasa_aseo,
                "tasaAlumbrado" => $data_cliente->tasa_alumbrado,
                "otrasTasas" => $data_cliente->otras_tasas,
                "ajusteNoSujetoIva" => $data_cliente->ajuste_no_sujeto_iva,
                "detalleAjusteNoSujetoIva" => $data_cliente->detalle_ajuste_no_sujeto_iva,
                "ajusteSujetoIva" => $data_cliente->ajuste_sujeto_iva,
                "detalleAjusteSujetoIva" => $data_cliente->detalle_ajuste_sujeto_iva,
                "otrosPagosNoSujetoIva" => 0 + $data_cliente->otros_pagos_no_sujeto_iva,
                "detalleOtrosPagosNoSujetoIva" => $data_cliente->detalle_otros_pagos_no_sujeto_iva,

                "detalle" => $data_product_sales,
            ]
        ];
        
        // Solo enviar CUFD cuando NO esté en modo centralizado - Servicio Básico
        if (($pos_setting->cufd_centralizado ?? 0) == 0) {
            $data_body['cufd'] = $data_siat_cufd->codigo_cufd;
            log::info('CUFD incluido en request (modo estándar) - Servicio Básico');
        } else {
            log::info('CUFD NO incluido en request (modo centralizado - flag activa) - Servicio Básico');
        }
        
        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->post($host . $path, $data_body);
        log::info("URL => " . $host . $path);
        log::info("Body => " . json_encode($data_body));

        $respuesta = array();
        if ($response->successful()) {

            $update_customer_sale = CustomerSale::where('sale_id', $data_cliente->sale_id)->first();
            $data_json = $response->json();
            $update_customer_sale->codigo_recepcion = $data_json['codigo_recepcion'];
            $update_customer_sale->cuf = $data_json['cuf'];
            $update_customer_sale->codigo_cufd = $data_siat_cufd->codigo_cufd;
            $update_customer_sale->xml = $data_json['xmlfactura'];
            $update_customer_sale->estado_factura = "VIGENTE";
            $update_customer_sale->save();
            $totalGrand = ($data_venta->grand_total + $data_cliente->tasa_aseo + $data_cliente->tasa_alumbrado + $data_cliente->otras_tasas + $data_cliente->ajuste_sujeto_iva + $data_cliente->otros_pagos_no_sujeto_iva);
            $data_venta->total_price = $totalGrand;
            $data_venta->grand_total = $totalGrand;
            $data_venta->paid_amount = $totalGrand;
            $data_venta->save();
            $msj = "Venta Facturada correctamente";
            $respuesta = array('mensaje' => $msj, 'status' => true);
        } else {
            $error = $response->json();
            log::warning($error);
            $titulo_error = $error['status'];
            if ($titulo_error == 500) {
                $respuesta = array('mensaje' => 'Error interno del servidor. ', 'status' => false);
            } elseif ($titulo_error == 400) {
                $mensajes_error = $error['mensajesRecepcion'];
                $descripcion = "";
                foreach ($mensajes_error as $mensaje) {
                    $descripcion .= " Código: " . $mensaje['codigo'] . " - Descripción: " . $mensaje['descripcion'];
                    log::info($descripcion);
                }
                $msj = 'Problemas de conexión Siat, la venta no ha sido facturada. Error: ' . $titulo_error . $descripcion;
                $respuesta = array('mensaje' => $msj, 'status' => false);
            }
            log::warning('Error! archivo SiatTrait, generarFacturaIndividualServicioBasico => ');
            log::warning($error);
        }
        return $respuesta;
    }

    // Crea archivo CSV para Factura Masiva
    public function crearArchivoCsvFacturaMasiva($listas, $data_factura_masiva)
    {
        log::info('Se esta creando data para el csv...');
        // optimizar tiempo de ejecucion en este proceso
        foreach ($listas as $key => $value) {
            foreach ($value as $venta) {
                $todo_cadena[] = $this->obtenerArregloFacturaMasiva($venta);
            }
        }
        // instancia 
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        if ($data_factura_masiva->codigo_documento_sector == '13') {
            $textoEncabezado = $this->textoEncabezadoFacturaMasivaCSV_servicios();
        }
        if ($data_factura_masiva->codigo_documento_sector == '1') {
            $textoEncabezado = $this->textoEncabezadoFacturaMasivaCSV_compraVenta();
        }
        if ($data_factura_masiva->codigo_documento_sector == '2') {
            $textoEncabezado = $this->textoEncabezadoFacturaMasivaCSV_alquiler();
        }

        $sheet->fromArray($textoEncabezado);

        $data_row[] = "";
        $contador_fila = 2;
        foreach ($todo_cadena as $contenido) {
            foreach ($contenido as $row) {
                $sheet->fromArray($row, null, 'A' . $contador_fila);
                // Para ámbito de pruebas
                //$data_row[$contador_fila] = array_combine($textoEncabezado, $row);
                $contador_fila++;
            }
        }
        log::info('Se esta creando el archivo csv...');
        //log::info('El archivo CSV tiene las siguiente información: ');
        //log::info($data_row);
        // como se desea guardar
        $writer = new Csv($spreadsheet);
        $writer->setUseBOM(true);
        $writer->setDelimiter('|');
        $writer->setEnclosureRequired(false);
        $namefile = "export_factura_masiva_" . date('dmY') . "_" . date('His') . ".csv";
        $writer->save("export_factura_masiva.csv");
        //$writer->save($namefile);
        //log::info('Se genero el archivo CSV '.$namefile);
        log::info('Se genero el archivo CSV');

        return $todo_cadena;
    }

    // Endpoint para Factura Masiva
    private function facturaMasivaRecepcion($factura_masiva_paquete_id)
    {
        // {{urlSiat}}/factura.venta/factura.masiva.recepcion?nit=388615026&codigoDocumentoSector=1&sucursal=0&codigoPuntoVenta=0&codigoControl={{codigo_control}}&tipoFacturaDocumento=1&cuis={{cuis}}&cufd={{cufd}}
        $data_f_masiva_paquete = FacturaMasivaPaquetes::where('id', $factura_masiva_paquete_id)->first();
        $factura_masiva_id = $data_f_masiva_paquete->factura_masiva_id;

        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_operaciones;
        $path = '/factura.venta/factura.masiva.recepcion?';

        $data_control = FacturaMasiva::where('id', $factura_masiva_id)->first();
        $registro_cufd = SiatCufd::where('sucursal', $data_control->sucursal)->where('codigo_punto_venta', $data_control->codigo_punto_venta)->where('estado', true)->orderBy('fecha_registro', 'desc')->first();

        // query
        $query = 'nit=' . $pos_setting->nit_emisor;
        $query .= '&codigoDocumentoSector=' . $data_control->codigo_documento_sector;
        $query .= '&sucursal=' . $data_control->sucursal;
        $query .= '&codigoPuntoVenta=' . $data_control->codigo_punto_venta;
        $query .= '&codigoControl=' . $registro_cufd->codigo_control;
        $query .= '&tipoFacturaDocumento=' . 1;
        $query .= '&cuis=' . $data_control->cuis;
        $query .= '&cufd=' . $registro_cufd->codigo_cufd;

        log::info($host . $path . $query);
        $response = Http::attach(
            'file',
            file_get_contents('export_factura_masiva.csv'),
            'export_factura_masiva.csv'
        )->withHeaders([
            'Authorization' => $bearer,
        ])->post($host . $path . $query);

        $data_response = $response->json();
        if ($response->successful()) {
            log::info("Respuesta FacturaMasivaR: " . json_encode($data_response));
            // se procede a guardar y/o actualizar datos de la respuesta (response) del envío del paquete
            $data_f_masiva_paquete->fecha_de_envio = Carbon::now();
            $data_f_masiva_paquete->codigo_recepcion = $data_response['codigo_recepcion'];
            $data_f_masiva_paquete->respuesta_servicio = $data_response['codigo_estado'];
            $data_f_masiva_paquete->estado = $data_response['codigo_descripcion'];
            $data_f_masiva_paquete->log_errores = $data_response['mensajes_recepcion'];
            $data_f_masiva_paquete->save();

            return $data_response['codigo_descripcion'];
        } else {
            log::warning('Error! archivo SiatTrait, facturaMasivaRecepcion => ');
            log::warning($response->json());
            return 'Error: Servicios caídos';
        }
    }

    private function obtenerArregloFacturaMasiva($id)
    {
        $venta_id = $id;
        $pos_setting = PosSetting::latest()->first();

        $data_venta = Sale::where('id', $venta_id)->first();
        $data_biller = Biller::where('id', $data_venta->biller_id)->first();
        $data_p_venta = SiatPuntoVenta::where([
            'sucursal' => $data_biller->sucursal,
            'codigo_punto_venta' => $data_biller->punto_venta_siat
        ])->first();
        $data_siat_cufd = SiatCufd::where('sucursal', $data_p_venta->sucursal)->where('codigo_punto_venta', $data_p_venta->codigo_punto_venta)->where('estado', true)->orderBy('fecha_registro', 'desc')->first();
        $data_sucursal = SiatSucursal::where('sucursal', $data_p_venta->sucursal)->first();
        $data_cliente = CustomerSale::where('sale_id', $venta_id)->first();

        $leyendas = SiatLeyendaFactura::all();
        $data_leyenda = $leyendas->random();

        $data_gift_card = new Payment();
        // obteniendo los productos vendidos
        // y guardalos en un array
        $array_product_sales = DB::table('product_sales')->where('sale_id', '=', $venta_id)->get();

        $array_payment_sales = DB::table('payments')->where('sale_id', '=', $venta_id)->get();
        $nro_de_pagos = $array_payment_sales->count();
        foreach ($array_payment_sales as $item_pago) {
            if ($item_pago->paying_method == "Tarjeta_Regalo") {
                $data_gift_card = $item_pago;
            }
        }

        // preguntar casos de NULL, setear valor 0;
        if ($data_gift_card->amount == null) {
            $data_gift_card->amount = 0;
        }
        if ($data_venta->order_discount == null) {
            $data_venta->order_discount = 0;
        }
        $nro_tarjeta = null;
        if ($data_cliente->numero_tarjeta_credito_debito != null) {
            $nro_tarjeta = $data_cliente->numero_tarjeta_credito_debito;
        }
        $complemento = "";
        if ($data_cliente->complemento_documento != null) {
            $complemento = $data_cliente->complemento_documento;
        }

        foreach ($array_product_sales as $nro_prod_sales => $product_sales) {
            $info_product = Product::where('id', $product_sales->product_id)->first();
            $info_unit = Unit::where('id', $info_product->unit_id)->first();
            if ($info_product->unit_id > 0) {
                $info_par_unit = SiatParametricaVario::where('codigo_clasificador', $info_unit->codigo_clasificador_siat)->where('tipo_clasificador', 'unidadMedida')->first();
            } else {
                $info_par_unit['codigo_clasificador'] = "58";
                $info_par_unit['descripcion'] = "UNIDAD (SERVICIOS)";
            }
            if ($data_cliente->codigo_documento_sector == 13) {
                // FACTURA SERVICIOS
                $data_o[$nro_prod_sales] = [
                    '' . ($pos_setting->nit_emisor),
                    '' . $pos_setting->razon_social_emisor,
                    '' . $data_sucursal->ciudad_municipio,
                    '' . $data_sucursal->telefono,

                    '' . $data_cliente->nro_factura,
                    // cuf
                    '',
                    // cufd
                    '',
                    '' . $data_siat_cufd->sucursal,
                    '' . $data_siat_cufd->direccion,
                    '' . $data_siat_cufd->codigo_punto_venta,
                    '' . $data_cliente->created_at,

                    '' . $data_cliente->razon_social,
                    '' . $data_cliente->tipo_documento,
                    '' . $data_cliente->valor_documento,
                    '' . $complemento,
                    '' . $data_cliente->codigofijo,
                    '' . $data_cliente->email,

                    '' . $data_cliente->tipo_metodo_pago,
                    '' . $nro_tarjeta,
                    '' . number_format($data_venta->grand_total, 2, '.', ''),
                    '' . number_format(($data_venta->grand_total - $data_gift_card->amount), 2, '.', ''),
                    '' . 1,
                    '' . 1,
                    '' . number_format($data_venta->grand_total, 2, '.', ''),

                    '' . number_format($data_venta->order_discount, 2, '.', ''),
                    '' . $data_cliente->codigo_excepcion,
                    // cafc
                    '',
                    '' . $data_leyenda->descripcion_leyenda,
                    '' . $user = $data_cliente->usuario,
                    '' . $data_cliente->codigo_documento_sector,

                    '' . $data_cliente->mes,
                    '' . $data_cliente->gestion,
                    '' . $data_cliente->ciudad,
                    '' . $data_cliente->zona,
                    '' . $data_cliente->categoria,
                    '' . $data_cliente->numero_medidor,
                    '' . $data_cliente->lectura_medidor_anterior,
                    '' . $data_cliente->lectura_medidor_actual,
                    '' . $data_cliente->domicilio_cliente,
                    '' . $data_cliente->consumo_periodo,

                    '' . $data_cliente->beneficiario_ley_1886,
                    '' . $data_cliente->monto_descuento_ley_1886,
                    '' . $data_cliente->monto_descuento_tarifa_dignidad,

                    '' . $data_cliente->tasa_aseo,
                    '' . $data_cliente->tasa_alumbrado,
                    '' . $data_cliente->otras_tasas,

                    '' . $data_cliente->ajuste_no_sujeto_iva,
                    '' . $data_cliente->detalle_ajuste_no_sujeto_iva,

                    '' . $data_cliente->ajuste_sujeto_iva,
                    '' . $data_cliente->detalle_ajuste_sujeto_iva,

                    '' . $data_cliente->otros_pagos_no_sujeto_iva,
                    '' . $data_cliente->detalle_otros_pagos_no_sujeto_iva,

                    // Detalle producto
                    '' . $info_product->codigo_actividad,
                    '' . $info_product->codigo_producto_servicio,
                    '' . $info_product->code,
                    '' . $info_product->name,
                    '' . $product_sales->qty,
                    '' . $info_par_unit['codigo_clasificador'],
                    '' . number_format($product_sales->net_unit_price, 2, '.', ''),
                    '' . number_format($product_sales->discount, 2, '.', ''),
                    '' . number_format($product_sales->total, 2, '.', ''),
                    '' . $info_par_unit['descripcion'],
                ];
            }
            if ($data_cliente->codigo_documento_sector == 1) {
                // factura compra-venta
                $data_o[$nro_prod_sales] = [
                    ($pos_setting->nit_emisor),
                    $pos_setting->razon_social_emisor,
                    $data_sucursal->ciudad_municipio,
                    $data_sucursal->telefono,

                    $data_cliente->nro_factura,
                    // cuf
                    '',
                    // cufd
                    '',
                    $data_siat_cufd->sucursal,
                    $data_siat_cufd->direccion,
                    $data_siat_cufd->codigo_punto_venta,
                    $data_cliente->created_at,

                    $data_cliente->razon_social,
                    $data_cliente->tipo_documento,
                    $data_cliente->valor_documento,
                    $complemento,
                    $data_cliente->codigofijo,
                    $data_cliente->email,

                    $data_cliente->tipo_metodo_pago,
                    $nro_tarjeta,
                    number_format($data_venta->grand_total, 2, '.', ''),
                    number_format(($data_venta->grand_total - $data_gift_card->amount), 2, '.', ''),
                    1,
                    1,
                    number_format($data_venta->grand_total, 2, '.', ''),
                    number_format($data_gift_card->amount, 2, '.', ''),
                    number_format($data_venta->order_discount, 2, '.', ''),
                    $data_cliente->codigo_excepcion,
                    '',
                    $data_leyenda->descripcion_leyenda,
                    $user = $data_cliente->usuario,
                    $data_cliente->codigo_documento_sector,

                    //Detalle producto
                    $info_product->codigo_actividad,
                    $info_product->codigo_producto_servicio,
                    $info_product->code,
                    $info_product->name,
                    $product_sales->qty,
                    $info_par_unit['codigo_clasificador'],
                    number_format($info_product->price, 2, '.', ''),
                    number_format($product_sales->discount, 2, '.', ''),
                    number_format($product_sales->total, 2, '.', ''),
                    '',
                    '',
                    $info_par_unit['descripcion'],
                ];
            }
            if ($data_cliente->codigo_documento_sector == 2) {
                // Alquiler
                $data_o[$nro_prod_sales] = [
                    ($pos_setting->nit_emisor),
                    $pos_setting->razon_social_emisor,
                    $data_sucursal->ciudad_municipio,
                    $data_sucursal->telefono,

                    $data_cliente->nro_factura,
                    // cuf
                    '',
                    // cufd
                    '',
                    $data_siat_cufd->sucursal,
                    $data_siat_cufd->direccion,
                    $data_siat_cufd->codigo_punto_venta,
                    $data_cliente->created_at,

                    $data_cliente->razon_social,
                    $data_cliente->tipo_documento,
                    $data_cliente->valor_documento,
                    $complemento,
                    $data_cliente->customer_id,
                    $data_cliente->email,

                    $data_cliente->tipo_metodo_pago,
                    $nro_tarjeta,
                    number_format($data_venta->grand_total, 2, '.', ''),
                    number_format(($data_venta->grand_total - $data_gift_card->amount), 2, '.', ''),
                    1,
                    1,
                    number_format($data_venta->grand_total, 2, '.', ''),
                    number_format($data_gift_card->amount, 2, '.', ''),
                    number_format($data_venta->order_discount, 2, '.', ''),
                    $data_cliente->codigo_excepcion,
                    '',
                    $data_leyenda->descripcion_leyenda,
                    $user = $data_cliente->usuario,
                    $data_cliente->codigo_documento_sector,
                    $data_cliente->glosa_periodo_facturado,

                    // Detalle producto
                    $info_product->codigo_actividad,
                    $info_product->codigo_producto_servicio,
                    $info_product->code,
                    $info_product->name,
                    $product_sales->qty,
                    $info_par_unit['codigo_clasificador'],
                    number_format($info_product->price, 2, '.', ''),
                    number_format($product_sales->discount, 2, '.', ''),
                    number_format($product_sales->total, 2, '.', ''),
                    '',
                    '',
                    $info_par_unit['descripcion'],
                ];
            }
        }
        return $data_o;
    }

    private function textoEncabezadoFacturaMasivaCSV_servicios()
    {
        return [
            "nitEmisor",
            "razonSocialEmisor",
            "municipio",
            "telefono",
            "numeroFactura",
            "cuf",
            "cufd",
            "codigoSucursal",
            "direccion",
            "codigoPuntoVenta",
            "fechaEmision",
            "nombreRazonSocial",
            "codigoTipoDocumentoIdentidad",
            "numeroDocumento",
            "complemento",
            "codigoCliente",
            "email",
            "codigoMetodoPago",
            "numeroTarjeta",
            "montoTotal",
            "montoTotalSujetoIva",
            "codigoMoneda",
            "tipoCambio",
            "montoTotalMoneda",
            // "montoGiftCard",
            "descuentoAdicional",
            "codigoExcepcion",
            "cafc",
            "leyenda",
            "usuario",
            "codigoDocumentoSector",
            // "glosa_periodo_facturado",
            "mes",
            "gestion",
            "ciudad",
            "zona",
            "categoria",
            "numeroMedidor",
            "lecturaMedidorAnterior",
            "lecturaMedidorActual",
            "domicilioCliente",
            "consumoPeriodo",
            "beneficiarioLey1886",
            "montoDescuentoLey1886",
            "montoDescuentoTarifaDignidad",
            "tasaAseo",
            "tasaAlumbrado",
            "otrasTasas",
            "ajusteNoSujetoIva",
            "DetalleAjusteNoSujetoIva",
            "AjusteSujetoIva",
            "DetalleAjusteSujetoIva",
            "OtrosPagosNoSujetoIva",
            "DetalleOtrosPagosNoSujetoIva",
            "actividadEconomica",
            "codigoProductoSin",
            "codigoProducto",
            "descripcion",
            "cantidad",
            "unidadMedida",
            "precioUnitario",
            "montoDescuento",
            "subTotal",
            "nombreUnidad"
        ];
    }

    public function textoEncabezadoFacturaMasivaCSV_compraVenta()
    {
        return [
            "nitEmisor",
            "razonSocialEmisor",
            "municipio",
            "telefono",
            "numeroFactura",
            "cuf",
            "cufd",
            "codigoSucursal",
            "direccion",
            "codigoPuntoVenta",
            "fechaEmision",
            "nombreRazonSocial",
            "codigoTipoDocumentoIdentidad",
            "numeroDocumento",
            "complemento",
            "codigoCliente",
            "email",
            "codigoMetodoPago",
            "numeroTarjeta",
            "montoTotal",
            "montoTotalSujetoIva",
            "codigoMoneda",
            "tipoCambio",
            "montoTotalMoneda",
            "montoGiftCard",
            "descuentoAdicional",
            "codigoExcepcion",
            "cafc",
            "leyenda",
            "usuario",
            "codigoDocumentoSector",
            "actividadEconomica",
            "codigoProductoSin",
            "codigoProducto",
            "descripcion",
            "cantidad",
            "unidadMedida",
            "precioUnitario",
            "montoDescuento",
            "subTotal",
            "numeroSerie",
            "numeroImei",
            "nombreUnidadMedida"
        ];
    }

    public function textoEncabezadoFacturaMasivaCSV_alquiler()
    {
        return [
            "nitEmisor",
            "razonSocialEmisor",
            "municipio",
            "telefono",
            "numeroFactura",
            "cuf",
            "cufd",
            "codigoSucursal",
            "direccion",
            "codigoPuntoVenta",
            "fechaEmision",
            "nombreRazonSocial",
            "codigoTipoDocumentoIdentidad",
            "numeroDocumento",
            "complemento",
            "codigoCliente",
            "email",
            "codigoMetodoPago",
            "numeroTarjeta",
            "montoTotal",
            "montoTotalSujetoIva",
            "codigoMoneda",
            "tipoCambio",
            "montoTotalMoneda",
            "montoGiftCard",
            "descuentoAdicional",
            "codigoExcepcion",
            "cafc",
            "leyenda",
            "usuario",
            "codigoDocumentoSector",
            "periodoFacturado",
            "actividadEconomica",
            "codigoProductoSin",
            "codigoProducto",
            "descripcion",
            "cantidad",
            "unidadMedida",
            "precioUnitario",
            "montoDescuento",
            "subTotal",
            "numeroSerie",
            "numeroImei",
            "nombreUnidad"
        ];
    }

    // funcion para verificar el estado del envío de un paquete específico MASIVA
    public function verificarEstadoPaqueteMasiva($factura_masiva_paquete_id)
    {
        $data_f_paquete = FacturaMasivaPaquetes::where('id', $factura_masiva_paquete_id)->first();
        $factura_masiva_id = $data_f_paquete->factura_masiva_id;

        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_operaciones;
        $path = '/factura.venta/factura.masiva.validacion';

        $data_control = FacturaMasiva::where('id', $factura_masiva_id)->first();
        $registro_cufd_valido = SiatCufd::where('estado', true)->where('codigo_punto_venta', $data_control->codigo_punto_venta)->where('sucursal', $data_control->sucursal)->orderBy('fecha_registro', 'desc')->first();

        log::info($data_control);

        // query o body
        $query = [
            'codigoDocumento' => $data_control->codigo_documento_sector,
            'tipoFacturaDocumento' => 1,

            'nit' => $pos_setting->nit_emisor,
            'codigoRecepcion' => $data_f_paquete->codigo_recepcion,
            'codigoControl' => $registro_cufd_valido->codigo_control,
            'cufd' => $registro_cufd_valido->codigo_cufd,

            'cuis' => $data_control->cuis,
            'sucursal' => $data_control->sucursal,
            'codigoPuntoVenta' => $data_control->codigo_punto_venta,
        ];

        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->post($host . $path, $query);

        log::info($host . $path, $query);
        return $response;
    }

    // obtenerArreglo de ventas enviadas en paquetesRecepción
    public function obtenerVentasEnviadasEnPaquetesModoContingencia($control_contingencia_paquete_id)
    {
        $data_c_contingencia_paquete = ControlContingenciaPaquetes::where('id', $control_contingencia_paquete_id)->first();
        $control_contingencia_id = $data_c_contingencia_paquete->control_contingencia_id;

        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_operaciones;
        $path = '/factura.venta/consultafacturasrecepcion';

        $data_control = ControlContingencia::where('id', $control_contingencia_id)->first();
        $registro_cufd_valido = SiatCufd::where('estado', true)->where('codigo_punto_venta', $data_control->codigo_punto_venta)->where('sucursal', $data_control->sucursal)->orderBy('fecha_registro', 'desc')->first();

        $listas = json_decode($data_c_contingencia_paquete->arreglo_ventas, true);
        Log::info("El arreglo de listas para VALIDAR_PAQUETE es => " . json_encode($listas['CONTINGENCIA']));
        $array_lista = $listas['CONTINGENCIA'];

        if ($data_control->codigo_evento > 4) {
            // si evento es mayor a 4, se precisa el cafc, y el nro_factura es manual
            $data_inicial = CustomerSale::where('sale_id', $array_lista[0])->first();
            $data_final = CustomerSale::where('sale_id', end($array_lista))->first();
            $nro_factura_inicial = $data_inicial->nro_factura_manual;
            $nro_factura_final = $data_final->nro_factura_manual;
        } else {
            $data_inicial = CustomerSale::where('sale_id', $array_lista[0])->first();
            $data_final = CustomerSale::where('sale_id', end($array_lista))->first();
            $nro_factura_inicial = $data_inicial->nro_factura;
            $nro_factura_final = $data_final->nro_factura;
        }

        // query o body
        $query = [
            'documentoSector' => $data_control->codigo_documento_sector,

            'nit' => $pos_setting->nit_emisor,

            'sucursal' => $data_control->sucursal,
            'puntoVenta' => $data_control->codigo_punto_venta,

            'cufdEvento' => $data_control->cufd_evento,
            'factura_inicial' => $nro_factura_inicial,
            'factura_final' => $nro_factura_final
        ];

        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->post($host . $path, $query);

        log::info('Endpoint para obtenerArregloDeVentas tipoContingencia => ' . $host . $path, $query);

        $item = $response->json();

        $respuesta = $this->actualizarEstadoDeLasVentasPaquete($item, $data_c_contingencia_paquete, $data_control);
        return $respuesta;
    }

    public function actualizarEstadoDeLasVentasPaquete($response, $data_paquete, $data_control)
    {

        $ventas = $response['ENTITIES'];
        $bandera = false;
        foreach ($ventas as $value) {
            if ($data_control->codigo_evento > 4) {
                $data_customer_sale = CustomerSale::where('sucursal', $value['codigoSucursal'])
                    ->where('codigo_punto_venta', $value['codigoPuntoVenta'])
                    ->where('nro_factura_manual', $value['numeroFactura'])
                    ->where('codigo_documento_sector', $data_control->codigo_documento_sector)
                    ->first();
            } else {
                $data_customer_sale = CustomerSale::where('sucursal', $value['codigoSucursal'])
                    ->where('codigo_punto_venta', $value['codigoPuntoVenta'])
                    ->where('nro_factura', $value['numeroFactura'])
                    ->where('codigo_documento_sector', $data_control->codigo_documento_sector)
                    ->first();
            }
            $data_customer_sale->codigo_recepcion = $value['codigo_recepcion'];
            // $data_customer_sale->xml = $value['xml_factura'];
            $data_customer_sale->cuf = $value['cuf'];
            $data_customer_sale->codigo_cufd = $value['cufd'];
            $data_customer_sale->estado_factura = 'FACTURADA';
            $data_customer_sale->save();

            $bandera = true;
        }
        return $bandera;
    }

    public function actualizarEstadoDeLasVentasPaqueteMasivo($response, $data_paquete, $data_control)
    {

        $ventas = $response['ENTITIES'];
        $bandera = false;
        foreach ($ventas as $value) {
            $data_customer_sale = CustomerSale::where('sucursal', $value['codigoSucursal'])
                ->where('codigo_punto_venta', $value['codigoPuntoVenta'])
                ->where('nro_factura', $value['numeroFactura'])
                ->where('codigo_documento_sector', $data_control->codigo_documento_sector)
                ->first();
            $data_customer_sale->codigo_recepcion = $value['codigo_recepcion'];
            //$data_customer_sale->xml = $value['xml_factura'];
            $data_customer_sale->cuf = $value['cuf'];
            $data_customer_sale->codigo_cufd = $value['cufd'];
            $data_customer_sale->estado_factura = 'FACTURADA';
            $data_customer_sale->save();

            $bandera = true;
        }
        return $bandera;
    }


    // Tipo Masivo
    public function obtenerVentasEnviadasEnPaquetesModoMasivo($factura_masiva_paquete_id)
    {
        $data_factura_masiva_paquete = FacturaMasivaPaquetes::where('id', $factura_masiva_paquete_id)->first();
        $factura_masiva_id = $data_factura_masiva_paquete->factura_masiva_id;

        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_operaciones;
        $path = '/factura.venta/consultafacturasrecepcion_masivo';


        $data_control = FacturaMasiva::where('id', $factura_masiva_id)->first();

        // query o body
        $query = [
            'documentoSector' => $data_control->codigo_documento_sector,

            'nit' => $pos_setting->nit_emisor,
            'codigoRecepcion' => $data_factura_masiva_paquete->codigo_recepcion,

            'sucursal' => $data_control->sucursal,
            'puntoVenta' => $data_control->codigo_punto_venta,
        ];

        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->post($host . $path, $query);

        log::info('Endpoint para obtenerArregloDeVentas tipoMasivo => ' . $host . $path, $query);

        $item = $response->json();

        $respuesta = $this->actualizarEstadoDeLasVentasPaqueteMasivo($item, $data_factura_masiva_paquete, $data_control);
        //$respuesta = $this->anularMasivamente($item);
        return $respuesta;
    }


    public function anularMasivamente($response)
    {
        $ventas = $response['ENTITIES'];
        $bandera = false;
        foreach ($ventas as $value) {
            $res = $this->anularFactura($value['cuf'], 1, true);
            $bandera = true;
        }
        return $bandera;
    }

    public function buscarFacturas($filtro)
    {
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_optimo;
        $path = '/facturacion/v1/facturasporsucursal';
        // query o body
        $query = [
            'cuf' => $filtro['cuf'],
            'documentoSector' => $filtro['documentoSector'],
            'fechaInicial' => $filtro['fechaInc'],
            'fechaFinal' => $filtro['fechaFin'],
            'nit' => $pos_setting->nit_emisor,
            'numeroFactura' => $filtro['numeroFactura'],
            'opcionBusqueda' => $filtro['opcion'],
            'puntoVenta' => $filtro['puntoVenta'],
            'sucursal' => $filtro['sucursal'],
            'valorBusqueda' => $filtro['valor'],
        ];
        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->post($host . $path, $query);
        log::info("URL => " . $host . $path);
        log::info("Body => " . json_encode($query));
        log::info("Response => " . json_encode($response->successful()));
        if ($response->successful()) {
            $data = $response->json();
            $respuesta = array('facturas' => $data['ENTITIES'], 'status' => true);
        } else {
            $error = $response->json();
            $titulo_error = $error['status'];
            if ($titulo_error == 500) {
                $respuesta = array('mensaje' => 'Error interno del servidor. ', 'status' => false);
            } elseif ($titulo_error == 400) {
                $mensajes_error = $error['title'];
                $respuesta = array('mensaje' => $mensajes_error, 'status' => false);
            }
            log::warning('Error! archivo SiatTrait, buscarFacturas => ');
            log::warning($error);
        }

        return $respuesta;
    }

    public function buscarFacturasxLibro($filtro)
    {
        $pos_setting = PosSetting::latest()->first();
        
        // Validar que exista configuración
        if (!$pos_setting) {
            Log::error('No se encontró configuración de POS');
            return array(
                'mensaje' => 'Configuración de POS no encontrada',
                'status' => false,
                'facturas' => array(),
                'total' => 0
            );
        }
        
        // Validar que exista token
        $token = Session::get('token_siat');
        if (!$token) {
            Log::warning('Token SIAT no disponible, intentando obtener nuevo token');
            $this->getToken();
            $token = Session::get('token_siat');
            
            if (!$token) {
                Log::error('No se pudo obtener token SIAT');
                return array(
                    'mensaje' => 'No hay autenticación SIAT. Por favor, inicie sesión nuevamente.',
                    'status' => false,
                    'facturas' => array(),
                    'total' => 0
                );
            }
        }
        
        $bearer = 'Bearer ' . $token;
        $host = $pos_setting->url_optimo;
        $path = '/facturacion/v1/reportedeventas';
        
        // Configuración de filas
        if (isset($filtro['fila']) && $filtro['fila'] == -1) {
            $filtro['fila'] = Session::get('total_fila', 10);
        }
        
        // Construcción del JSON mejorado según el ejemplo
        $query = [
            'cuf' => isset($filtro['cuf']) ? $filtro['cuf'] : '',
            'documentoSector' => isset($filtro['documentoSector']) ? $filtro['documentoSector'] : '1',
            'fechaInicial' => isset($filtro['fechaInc']) ? $filtro['fechaInc'] : '',
            'fechaFinal' => isset($filtro['fechaFin']) ? $filtro['fechaFin'] : '',
            'nit' => $pos_setting->nit_emisor ?? '',
            'numeroFactura' => isset($filtro['numeroFactura']) ? (int)$filtro['numeroFactura'] : 0,
            'opcionBusqueda' => isset($filtro['opcion']) ? $filtro['opcion'] : 'razonSocial',
            'puntoVenta' => isset($filtro['puntoVenta']) ? (string)$filtro['puntoVenta'] : '0',
            'sucursal' => isset($filtro['sucursal']) ? (string)$filtro['sucursal'] : '0',
            'valorBusqueda' => isset($filtro['valor']) ? $filtro['valor'] : '',
            'estado' => isset($filtro['estadoFactura']) ? $filtro['estadoFactura'] : 'T',
            'pagina' => isset($filtro['pagina']) ? (string)$filtro['pagina'] : '0',
            'filas' => isset($filtro['fila']) ? (string)$filtro['fila'] : '10',
        ];

        try {
            Log::info("===== buscarFacturasxLibro REQUEST =====");
            Log::info("URL: " . $host . $path);
            Log::info("Query params:", $query);
            Log::info("Token disponible: " . (!empty($token) ? 'SI' : 'NO'));
            
            $response = Http::timeout(30)->withHeaders([
                'Authorization' => $bearer,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($host . $path, $query);

            Log::info("Response status: " . $response->status());
            Log::info("Response headers:", $response->headers());
            
            $respuesta = [];
            
            if ($response->successful()) {
                $data = $response->json();
                Log::info("Response data recibida:", $data);
                
                // Verificar que la respuesta tenga la estructura esperada
                if (isset($data['ENTITIES']) && isset($data['TOTAL_FILAS'])) {
                    Log::info("Estructura válida - Total filas: " . $data['TOTAL_FILAS']);
                    $respuesta = array(
                        'facturas' => is_array($data['ENTITIES']) ? $data['ENTITIES'] : [],
                        'status' => true,
                        'total' => intval($data['TOTAL_FILAS'])
                    );
                } else {
                    Log::warning('Respuesta exitosa pero estructura inesperada');
                    Log::warning('Keys disponibles: ' . implode(', ', array_keys($data)));
                    Log::warning('Data completa:', $data);
                    $respuesta = array(
                        'mensaje' => 'Estructura de respuesta inválida',
                        'status' => false,
                        'facturas' => array(),
                        'total' => 0
                    );
                }
            } else {
                $statusCode = $response->status();
                $errorBody = $response->body();
                
                Log::error("Error HTTP {$statusCode} en buscarFacturasxLibro");
                Log::error("Response body: " . $errorBody);
                
                // Intentar parsear el error como JSON
                try {
                    $error = $response->json();
                    $mensaje_error = isset($error['title']) ? $error['title'] : 
                                   (isset($error['message']) ? $error['message'] : 'Error desconocido');
                } catch (\Exception $e) {
                    $mensaje_error = "Error del servidor (HTTP {$statusCode})";
                }
                
                $respuesta = array(
                    'mensaje' => $mensaje_error,
                    'status' => false,
                    'facturas' => array(),
                    'total' => 0
                );
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Error de conexión en buscarFacturasxLibro: ' . $e->getMessage());
            $respuesta = array(
                'mensaje' => 'No se pudo conectar con el servidor de facturación',
                'status' => false,
                'facturas' => array(),
                'total' => 0
            );
        } catch (\Exception $e) {
            Log::error('Excepción en buscarFacturasxLibro: ' . $e->getMessage());
            $respuesta = array(
                'mensaje' => 'Error inesperado: ' . $e->getMessage(),
                'status' => false,
                'facturas' => array(),
                'total' => 0
            );
        }

        return $respuesta;
    }

    public function pagarFactura($dataPago)
    {
        $factura = $this->getFacturaData($dataPago['cuf']);
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_cobranza;
        $path = '/cobranzas/v1/pagar/facturaindividual';

        $sucursal = SiatSucursal::where([['sucursal', $factura['codigoSucursal'], ['estado', true]]])->first();
        // query o body
        $query = [
            'cuf' => $dataPago['cuf'],
            'nit' => $pos_setting->nit_emisor,
            'sucursal' => $factura['codigoSucursal'],
            'dpto' => $sucursal->departamento,
            'ciudad' => $sucursal->ciudad_municipio,
            'entidadFinanciera' => $sucursal->nombre,
            'entidadCobranza' => "EPSA MANCHACO",
            'agencia' => $sucursal->domicilio_tributario,
            'operador' => Auth::user()->name,
            'fechaTransaccion' => $dataPago['fecha'],
            'horaTransaccion' => $dataPago['hora'],
        ];

        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->post($host . $path, $query);

        Log::info("pagar factura => " . $host . $path, $query);
        $respuesta = [];
        if ($response->successful()) {
            $data = $response->json();
            $respuesta = array('factura' => $data, 'status' => true);
        } else {
            $error = $response->json();
            $titulo_error = $error['status'];
            if ($titulo_error == 500) {
                $respuesta = array('mensaje' => 'Error interno del servidor. ', 'status' => false, 'factura' => array(),);
            } elseif ($titulo_error == 400) {
                $mensajes_error = $error['title'];
                $respuesta = array('mensaje' => $mensajes_error, 'status' => false, 'factura' => array(),);
            } elseif ($titulo_error == 404) {
                $respuesta = array('mensaje' => "Servicio no encontrado, contacte con soporte!", 'status' => false, 'factura' => array(),);
            }
            log::warning('Error! archivo SiatTrait, pagarFactura => ');
            log::warning($error);
        }

        return $respuesta;
    }

    public function revertirPagoFactura($dataPago)
    {
        $factura = $this->getFacturaData($dataPago['cuf']);
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_cobranza;
        $path = '/cobranzas/v1/pagar/reviertepagoindividual';
        // query o body
        $query = [
            'cuf' => $dataPago['cuf'],
            'nit' => $pos_setting->nit_emisor,
            'sucursal' => $factura['codigoSucursal'],
            'operador' => Auth::user()->name,
            'observaciones' => $dataPago['observaciones']
        ];

        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->post($host . $path, $query);

        Log::info("revertirPagoFactura => " . $host . $path, $query);
        $respuesta = [];
        if ($response->successful()) {
            $data = $response->json();
            $respuesta = array('factura' => $data, 'status' => true);
        } else {
            $error = $response->json();
            $titulo_error = $error['status'];
            if ($titulo_error == 500) {
                $respuesta = array('mensaje' => 'Error interno del servidor. ', 'status' => false, 'factura' => array(),);
            } elseif ($titulo_error == 400) {
                $mensajes_error = $error['title'];
                $respuesta = array('mensaje' => $mensajes_error, 'status' => false, 'factura' => array(),);
            } elseif ($titulo_error == 404) {
                $respuesta = array('mensaje' => "Servicio no encontrado, contacte con soporte!", 'status' => false, 'factura' => array(),);
            }
            log::warning('Error! archivo SiatTrait, revertirPagoFactura => ');
            log::warning($error);
        }

        return $respuesta;
    }

    public function getFacturaData(string $cuf)
    {
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_optimo;
        $path = '/facturacion/v1?cuf=' . $cuf;
        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->get($host . $path);
        $respuesta = [];
        if ($response->successful()) {
            $respuesta = $response->json();
        } else {
            $error = $response->json();
            $titulo_error = $error['status'];
            if ($titulo_error == 500) {
                $respuesta = array('mensaje' => 'Error interno del servidor. ', 'status' => false);
            } elseif ($titulo_error == 400) {
                $mensajes_error = $error['title'];
                $respuesta = array('mensaje' => $mensajes_error, 'status' => false);
            } elseif ($titulo_error == 404) {
                $respuesta = array('mensaje' => "Servicio no encontrado, contacte con soporte!", 'status' => false, 'factura' => array(),);
            }
            $respuesta = null;
            log::warning('Error! archivo SiatTrait, getFactura => ');
            log::warning($error);
        }

        return $respuesta;
    }

    /**
     * Intento integral para obtener datos de factura por CUF.
     * Primero consulta SIAT (getFacturaData). Si no hay respuesta, busca en la BD local (customer_sales).
     * Retorna null si no encuentra nada.
     */
    public function datosFacturaByCuf(string $cuf)
    {
        // Intentar consulta directa a SIAT
        $factura = $this->getFacturaData($cuf);
        if ($factura) {
            return ['ESTADO' => 'OK', 'ENTITY' => $factura, 'MENSAJE' => ''];
        }

        // Fallback local: buscar en customer_sales
        $customer_sale = \App\CustomerSale::where('cuf', $cuf)->first();
        if (!$customer_sale) return null;

        $sale = $customer_sale->sale;

        $entity = [
            'codigo_recepcion' => $customer_sale->codigo_recepcion ?? null,
            'nro_factura' => $customer_sale->nro_factura ?? null,
            'fecha_factura' => $sale ? date('Y-m-d H:i:s', strtotime($sale->date_sell)) : null,
            'idfactura' => $sale ? $sale->id : null,
            'cufd' => $customer_sale->codigo_cufd ?? null,
            'cuf' => $customer_sale->cuf ?? $cuf,
            'xml' => $customer_sale->xml ?? null,
        ];

        return ['ESTADO' => 'OK', 'ENTITY' => $entity, 'MENSAJE' => ''];
    }

    public function reporteFacturaCobrada($dataFilter)
    {
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_optimo;
        $path = '/facturacion/v1/facturascobradas';

        $sucursal = SiatSucursal::where([['sucursal', $dataFilter['sucursal'], ['estado', true]]])->first();
        // query o body
        $query = [
            'nit' => $pos_setting->nit_emisor,
            'sucursal' => $dataFilter['sucursal'],
            'puntoVenta' => $dataFilter['puntoVenta'],
            'entidad_financiera' => $sucursal->nombre,
            'operador' => $dataFilter['usuario'],
            'fecha' => $dataFilter['fecha'],
        ];

        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->post($host . $path, $query);

        Log::info("reporteFacturaCobrada => " . $host . $path, $query);
        $respuesta = [];
        if ($response->successful()) {
            $data = $response->json();
            $respuesta = array('pdf' => $data, 'status' => true);
        } else {
            $error = $response->json();
            $titulo_error = $error['status'];
            if ($titulo_error == 500) {
                $mensajes_error = $error['title'];
                $respuesta = array('mensaje' => 'Error interno del servidor. ' . $mensajes_error, 'status' => false, 'factura' => array(),);
            } elseif ($titulo_error == 400) {
                $mensajes_error = $error['title'];
                $respuesta = array('mensaje' => $mensajes_error, 'status' => false, 'factura' => array(),);
            } elseif ($titulo_error == 404) {
                $respuesta = array('mensaje' => "Servicio no encontrado, contacte con soporte!", 'status' => false, 'factura' => array(),);
            }
            log::warning('Error! archivo SiatTrait, reporteFacturaCobrada => ');
            log::warning($error);
        }

        return $respuesta;
    }

    public function reporteFacturaRevertida($dataFilter)
    {
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_cobranza;
        $path = '/reportes/v1/exportafacturasrevertidas';

        // query o body
        $query = [
            'nit' => $pos_setting->nit_emisor,
            'sucursal' => $dataFilter['sucursal'],
            'operador' => $dataFilter['usuario'],
            'fecha' => $dataFilter['fecha'],
        ];

        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->post($host . $path, $query);

        Log::info("reporteFacturaRevertida => " . $host . $path, $query);
        $respuesta = [];
        if ($response->successful()) {
            $data = $response->json();
            $respuesta = array('pdf' => $data, 'status' => true);
        } else {
            $error = $response->json();
            $titulo_error = $error['status'];
            if ($titulo_error == 500) {
                $mensajes_error = $error['title'];
                $respuesta = array('mensaje' => 'Error interno del servidor. ' . $mensajes_error, 'status' => false, 'factura' => array(),);
            } elseif ($titulo_error == 400) {
                $mensajes_error = $error['title'];
                $respuesta = array('mensaje' => $mensajes_error, 'status' => false, 'factura' => array(),);
            } elseif ($titulo_error == 404) {
                $respuesta = array('mensaje' => "Servicio no encontrado, contacte con soporte!", 'status' => false, 'factura' => array(),);
            }
            log::warning('Error! archivo SiatTrait, reporteFacturaRevertida => ');
            log::warning($error);
        }

        return $respuesta;
    }
    public function reporteArqueoGeneral($dataFilter)
    {
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_optimo;
        $path = '/facturacion/v1/arqueogeneral';
        $user = Auth::user();
        // query o body
        $query = [
            'nit' => $pos_setting->nit_emisor,
            'sucursal' => $dataFilter['sucursal'],
            'operador' => $user->name,
            'fecha_inicial' => $dataFilter['fechaInicial'],
            'fecha_final' => $dataFilter['fechaFin'],
        ];

        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->post($host . $path, $query);

        Log::info("reporteArqueoCaja => " . $host . $path, $query);
        $respuesta = [];
        if ($response->successful()) {
            $data = $response->json();
            $respuesta = array('pdf' => $data, 'status' => true);
        } else {
            $error = $response->json();
            $titulo_error = $error['status'];
            if ($titulo_error == 500) {
                $mensajes_error = $error['title'];
                $respuesta = array('mensaje' => 'Error interno del servidor. ' . $mensajes_error, 'status' => false, 'factura' => array(),);
            } elseif ($titulo_error == 400) {
                $mensajes_error = $error['title'];
                $respuesta = array('mensaje' => $mensajes_error, 'status' => false, 'factura' => array(),);
            } elseif ($titulo_error == 404) {
                $respuesta = array('mensaje' => "Servicio no encontrado, contacte con soporte!", 'status' => false, 'factura' => array(),);
            }
            log::warning('Error! archivo SiatTrait, reporteArqueoCaja => ');
            log::warning($error);
        }

        return $respuesta;
    }

    public function reporteArqueoCategoria($dataFilter)
    {
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_optimo;
        $path = '/facturacion/v1/arqueogeneralcategoria';
        $user = Auth::user();
        // query o body
        $query = [
            'nit' => $pos_setting->nit_emisor,
            'sucursal' => $dataFilter['sucursal'],
            'operador' => $user->name,
            'fecha_inicial' => $dataFilter['fechaInicial'],
            'fecha_final' => $dataFilter['fechaFin'],
        ];

        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->post($host . $path, $query);

        Log::info("reporteArqueoCaja => " . $host . $path, $query);
        $respuesta = [];
        if ($response->successful()) {
            $data = $response->json();
            $respuesta = array('pdf' => $data, 'status' => true);
        } else {
            $error = $response->json();
            $titulo_error = $error['status'];
            if ($titulo_error == 500) {
                $mensajes_error = $error['title'];
                $respuesta = array('mensaje' => 'Error interno del servidor. ' . $mensajes_error, 'status' => false, 'factura' => array(),);
            } elseif ($titulo_error == 400) {
                $mensajes_error = $error['title'];
                $respuesta = array('mensaje' => $mensajes_error, 'status' => false, 'factura' => array(),);
            } elseif ($titulo_error == 404) {
                $respuesta = array('mensaje' => "Servicio no encontrado, contacte con soporte!", 'status' => false, 'factura' => array(),);
            }
            log::warning('Error! archivo SiatTrait, reporteArqueoCaja => ');
            log::warning($error);
        }

        return $respuesta;
    }

    public function generaNotaFiscal($id, $factura, $return_data, $list_return, $lims_customer_data)
    {
        $venta_id = $id;
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_operaciones;
        $path = '/documento.ajuste/nota.ajuste';

        $data_venta = Sale::where('id', $venta_id)->first();
        $data_biller = Biller::where([['id', $data_venta->biller_id], ['is_active', true]])->first();
        $data_p_venta = SiatPuntoVenta::where([
            'sucursal' => $data_biller->sucursal,
            'codigo_punto_venta' => $data_biller->punto_venta_siat
        ])->first();
        $data_siat_cufd = SiatCufd::where('sucursal', $data_p_venta->sucursal)->where('codigo_punto_venta', $data_p_venta->codigo_punto_venta)->where('estado', true)->orderBy('fecha_registro', 'desc')->first();
        $data_sucursal = SiatSucursal::where('sucursal', $data_p_venta->sucursal)->first();
        $data_cliente = CustomerSale::where('sale_id', $venta_id)->first();
        $tax = Tax::where([['name', 'IVA'], ['is_active', true]])->first();
        if (!$data_siat_cufd) {
            $respuesta = array('mensaje' => "Datos Cufd del Dia nulo, debe renovar CUFD", 'status' => false);
        }
        $correlativo_notafiscal = 0;
        if ($data_p_venta->correlativo_nota_debcred != null && $data_p_venta->correlativo_nota_debcred > 0) {
            $correlativo_notafiscal = $data_p_venta->correlativo_nota_debcred;
        } else {
            $correlativo_notafiscal = 1;
        }
        $array_payment_sales = DB::table('payments')->where('sale_id', '=', $venta_id)->get();
        $nro_de_pagos = $array_payment_sales->count();
        /*foreach ($array_payment_sales as $item_pago) {
        if ($item_pago->paying_method == "Tarjeta_Regalo") {
        $data_gift_card = $item_pago;
        $return_data->total_price = $return_data->total_price - $item_pago->amount;
        }
        }*/
        $leyendas = SiatLeyendaFactura::all();
        $data_leyenda = $leyendas->random();
        $fechaemision = str_replace('-', '/', $factura['fechaEmision']);
        $tipo_impresion = 'rollo';
        $nro_impresion = $pos_setting->type_print;
        switch ($nro_impresion) {
            case '1':
                $tipo_impresion = "rollo";
                break;
            case '2':
                $tipo_impresion = "rollo";
                break;
            case '3':
                $tipo_impresion = "media_carta";
                break;
            case '4':
                $tipo_impresion = "media_carta";
                break;
            default:
                $tipo_impresion = "rollo";
                break;
        }
        $list_items = [];
        foreach ($factura["detalle"] as $item) {
            $info_par_unit = SiatParametricaVario::where('codigo_clasificador', $item["unidadMedida"])->where('tipo_clasificador', 'unidadMedida')->first();
            $item_return = array(
                "actividadEconomica" => $item["actividadEconomica"],
                "cantidad" => $item["cantidad"],
                "codigoDetalleTransaccion" => 1,
                "codigoProducto" => $item["codigoProducto"],
                "codigoProductoSin" => $item["codigoProductoSin"],
                "descripcion" => $item["descripcion"],
                "montoDescuento" => number_format((float) $item["montoDescuento"], 2, '.', ''),
                "precioUnitario" => number_format((float) $item["precioUnitario"], 2, '.', ''),
                "subTotal" => number_format((float) $item["subTotal"], 2, '.', ''),
                "unidadMedida" => $info_par_unit['codigo_clasificador'],
                "nombreUnidadMedida" => $info_par_unit['descripcion'],
            );
            $list_items[] = $item_return;
        }

        foreach ($list_return as $item_return) {
            $list_items[] = $item_return;
        }
        $montoIVA = ($return_data->total_price * $tax->rate) / 100;
        
        // Construir data_body base - Nota Fiscal
        $data_body = [
            'codigoControl' => $data_siat_cufd->codigo_control,
            'codigoDocumento' => 24,
            'codigoPuntoVenta' => $data_siat_cufd->codigo_punto_venta,
            'cuis' => $data_p_venta->codigo_cuis,
            'nit' => $pos_setting->nit_emisor,
            'sucursal' => $data_siat_cufd->sucursal,
            'formato' => $tipo_impresion,
            'notaFiscal' => [
                "codigoCliente" => $data_cliente->codigofijo,
                "codigoDocumentoSector" => 24,
                "codigoExcepcion" => $data_cliente->codigo_excepcion,
                "codigoPuntoVenta" => $data_siat_cufd->codigo_punto_venta,
                "codigoSucursal" => $data_siat_cufd->sucursal,
                "codigoTipoDocumentoIdentidad" => $lims_customer_data->tipo_documento,
                "complemento" => $lims_customer_data->complemento_documento,
                "cuf" => $factura['cuf'],
                "cufd" => $data_siat_cufd->codigo_cufd,
                "direccion" => $data_siat_cufd->direccion,
                "fechaEmision" => date("Y-m-d H:i:s"),
                "fechaEmisionFactura" => $fechaemision,
                "leyenda" => $data_leyenda->descripcion_leyenda,
                "montoDescuentoCreditoDebito" => 0,
                "montoEfectivoCreditoDebito" => number_format((float) $montoIVA, 2, '.', ''),
                "montoTotalDevuelto" => number_format($return_data->total_price, 2, '.', ''),
                "montoTotalOriginal" => number_format($factura['montoTotalMoneda'] + $factura['descuentoAdicional'], 2, '.', ''),
                "municipio" => $data_sucursal->ciudad_municipio,
                "nitEmisor" => $pos_setting->nit_emisor,
                "nombreRazonSocial" => $lims_customer_data->razon_social,
                "numeroAutorizacionCuf" => $factura['cuf'],
                "numeroDocumento" => $lims_customer_data->valor_documento,
                "numeroFactura" => $data_cliente->nro_factura,
                "numeroNotaCreditoDebito" => $correlativo_notafiscal,
                'razonSocialEmisor' => $pos_setting->razon_social_emisor,
                "telefono" => $data_sucursal->telefono,
                "usuario" => $data_cliente->usuario,
                "email" => $lims_customer_data->email,
                "detalle" => $list_items,
            ],
        ];
        
        // Solo enviar CUFD cuando NO esté en modo centralizado - Nota Fiscal
        if (($pos_setting->cufd_centralizado ?? 0) == 0) {
            $data_body['cufd'] = $data_siat_cufd->codigo_cufd;
            log::info('CUFD incluido en request (modo estándar) - Nota Fiscal');
        } else {
            log::info('CUFD NO incluido en request (modo centralizado - flag activa) - Nota Fiscal');
        }

        log::info("URL => " . $host . $path);
        log::info("Body => " . json_encode($data_body));
        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->post($host . $path, $data_body);
        // entre 200 y 299
        // procedemos a incrementar el correlativo factura
        // y actualizamos los datos Cuf y Codigo Recepción de la Factura Exitosamente
        $respuesta = array();
        if ($response->successful()) {
            $data = $response->json();
            log::info(json_encode($data));
            $user = Auth::user();
            $obj_cliente = new CustomerSale();
            $obj_cliente->sale_id = 0;
            $obj_cliente->customer_id = $lims_customer_data->id;
            $obj_cliente->razon_social = $lims_customer_data->razon_social;
            $obj_cliente->email = $lims_customer_data->email;
            $obj_cliente->tipo_documento = $lims_customer_data->tipo_documento;
            $obj_cliente->valor_documento = $lims_customer_data->valor_documento;
            $obj_cliente->complemento_documento = $lims_customer_data->complemento_documento;
            $obj_cliente->codigo_excepcion = $data_cliente->codigo_excepcion;
            $obj_cliente->codigo_documento_sector = 24;
            $obj_cliente->nro_factura = $correlativo_notafiscal;
            $obj_cliente->tipo_caso_especial = false;
            $obj_cliente->estado_factura = "VIGENTE";
            $obj_cliente->sucursal = $data_p_venta->sucursal;
            $obj_cliente->codigo_punto_venta = $data_p_venta->codigo_punto_venta;
            $obj_cliente->codigo_cufd = $data_siat_cufd->codigo_cufd;
            $obj_cliente->cuf = $data['cuf'];
            $obj_cliente->codigo_recepcion = $data['codigo_recepcion'];
            $obj_cliente->xml = $data['xmlfactura'];
            $obj_cliente->usuario = $user->name;
            $obj_cliente->save();
            $data_p_venta->correlativo_nota_debcred += 1;
            $data_p_venta->save();
            $respuesta = array('data' => $data, 'customer_sale' => $obj_cliente, 'status' => true);
        } else {
            $error = $response->json();
            $titulo_error = $error['status'];
            if ($titulo_error == 500) {
                log::error($response->json());
                $respuesta = array('mensaje' => 'Error interno del servidor. ', 'status' => false);
            } elseif ($titulo_error == 400) {
                $mensajes_error = $error['mensajesRecepcion'];
                log::warning("mensajes de Error => " . json_encode($mensajes_error));
                $descripcion = "";
                foreach ($mensajes_error as $mensaje) {
                    $descripcion .= " Código: " . $mensaje['codigo'] . " - Descripción: " . $mensaje['descripcion'];
                    log::info($descripcion);
                }
                $data_p_venta->correlativo_nota_debcred -= 1;
                $data_p_venta->save();
                $msj = 'Problemas de conexión Siat, la venta no ha sido facturada. Error: ' . $titulo_error . $descripcion;
                $respuesta = array('mensaje' => $msj, 'status' => false);
            }
        }

        return $respuesta;
    }

    public function anularNotaDebitoCredito($factura, $motivo, $customerSale)
    {
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_operaciones;
        $path = '/documento.ajuste/anula.nota.ajuste';
        $data_p_venta = SiatPuntoVenta::where([
            'sucursal' => $customerSale->sucursal,
            'codigo_punto_venta' => $customerSale->codigo_punto_venta
        ])->first();
        $data_siat_cufd = SiatCufd::where('sucursal', $data_p_venta->sucursal)->where('codigo_punto_venta', $data_p_venta->codigo_punto_venta)->where('estado', true)->orderBy('fecha_registro', 'desc')->first();

        $data_body = [
            "codigoDocumento" => 24,
            "codigoPuntoVenta" => $customerSale->codigo_punto_venta,
            "cuf" => $customerSale->cuf,
            "cufd" => $data_siat_cufd->codigo_cufd,
            "cuis" => $data_p_venta->codigo_cuis,
            "motivo" => $motivo,
            "nit" => $pos_setting->nit_emisor,
            "sucursal" => $customerSale->sucursal
        ];
        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->post($host . $path, $data_body);
        log::info("URL => " . $host . $path, $data_body);
        log::info("Body => " . json_encode($data_body));
        $respuesta = array();
        if ($response->successful()) {
            $data = $response->json();
            $respuesta = array('data' => $data, 'status' => true);
            log::info("respuesta => " . json_encode($data));
        } else {
            $error = $response->json();
            $titulo_error = $error['status'];
            if ($titulo_error == 500) {
                $respuesta = array('mensaje' => 'Error interno del servidor. ', 'status' => false);
            } elseif ($titulo_error == 400) {
                if (isset($error['mensajesRecepcion'])) {
                    $mensajes_error = $error['mensajesRecepcion'];
                    log::warning("mensajes de Error => " . json_encode($mensajes_error));
                    $descripcion = "";
                    foreach ($mensajes_error as $mensaje) {
                        $descripcion .= " Código: " . $mensaje['codigo'] . " - Descripción: " . $mensaje['descripcion'];
                        log::info($descripcion);
                    }
                    $msj = $descripcion;
                } else {
                    $mensajes_error = $error['title'];
                    log::warning("mensajes de Error => " . json_encode($mensajes_error));
                    $msj = 'Problemas de conexión Siat, la nota de crédito/débito no ha sido anulada. Error: ' . $titulo_error . " - " . $mensajes_error;
                }
                $respuesta = array('mensaje' => $msj, 'status' => false);
            }
        }
        return $respuesta;
    }

    public function solititudActualizacionCodigoExcepcion($data_control, $cadena_nros_facturas)
    {
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_operaciones;
        $path = '/factura.venta/actualizaexcepcionnit';

        // query o body
        $query = [
            'documentoSector' => $data_control->codigo_documento_sector,
            'nit' => $pos_setting->nit_emisor,

            'sucursal' => $data_control->sucursal,
            'puntoVenta' => $data_control->codigo_punto_venta,

            'cufs' => $cadena_nros_facturas
        ];

        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->post($host . $path, $query);

        Log::info("Función solititudActualizacionCodigoExcepcion, el endpoint es => " . $host . $path, $query);
    }



    public function busquedaDocumentoSector($modalidad, $ambiente)
    {
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_optimo;
        $path = '/documentosector/v1/certificadospormodalidad?';
        // query
        $query = 'modalidad=' . $modalidad;
        $query .= '&ambiente=' . $ambiente;


        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->get($host . $path . $query);

        Log::info("busquedaDocumentoSector => " . $host . $path . $query);
        $respuesta = [];
        if ($response->successful()) {
            $data = $response->json();
            $respuesta = array('data' => $data['ENTITY'], 'status' => true);
        } else {
            $error = $response->json();
            $titulo_error = $error['status'];
            if ($titulo_error == 500) {
                $respuesta = array('mensaje' => 'Error interno del servidor. ', 'status' => false);
            } elseif ($titulo_error == 400) {
                $mensajes_error = $error['title'];
                $respuesta = array('mensaje' => $mensajes_error, 'status' => false);
            }
            log::warning('Error! archivo SiatTrait, busquedaDocumentoSector => ');
            log::warning($error);
        }

        return $respuesta;
    }



    public function saveAutorizacionFacturacion($data)
    {
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_optimo;
        $path = '/autorizacion/v1';
        $fecha_actual = Carbon::now();
        $user = Auth::user()->name;

        foreach ($data['documentosSector'] as $key => $value) {
            $data_documentos[$key] = array(
                "ambiente" => $data['tipoAmbiente'],
                "modalidad" => $data['tipoModalidad'],
                "codigo_sin" => $value,
            );
        }


        // query o body
        $query = [
            'tipo_modalidad' => $data['tipoModalidad'],
            'ambiente' => $data['tipoAmbiente'],

            'nitEmpresa' => $data['nitEmpresa'],
            'fechaSolicitud' => $data['fechaSolicitud'],
            'tipo_sistema' => $data['tipoSistema'],
            'codigo_sistema' => $data['codigoSistema'],
            'token' => $data['token'],
            'fecha_vencimiento_token' => $data['fechaVencimientoToken'],
            'documentos' => $data_documentos,
            'fechaAlta' => $fecha_actual->format('Y-m-d H:i:s'),
            'usuarioAlta' => $user,
            'estado' => $data['estado'],
        ];


        Log::info("saveAutorizacionFacturacion => " . $host . $path, $query);
        // return;
        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->post($host . $path, $query);

        $respuesta = [];
        if ($response->successful()) {
            $data = $response->json();
            $respuesta = array('status' => true);
        } else {
            $error = $response->json();
            $titulo_error = $error['status'];
            if ($titulo_error == 500) {
                $respuesta = array('mensaje' => 'Error interno del servidor. ', 'status' => false);
            } elseif ($titulo_error == 400) {
                $mensajes_error = $error['title'];
                $respuesta = array('mensaje' => $mensajes_error, 'status' => false);
            }
            log::warning('Error! archivo SiatTrait, saveAutorizacionFacturacion => ');
            log::warning($error);
        }

        return $respuesta;
    }

    public function listaAutorizacionFacturacion()
    {
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_optimo;
        $path = '/autorizacion/v1/autorizaciones/nit/?';
        // query
        $query = 'nit=' . $pos_setting->nit_emisor;


        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->get($host . $path . $query);

        Log::info("listaAutorizacionFacturacion => " . $host . $path . $query);
        $respuesta = [];
        if ($response->successful()) {
            $data = $response->json();
            $respuesta = array('data' => $data['ENTITIES'], 'status' => true);
        } else {
            $error = $response->json();
            $titulo_error = $error['status'];
            if ($titulo_error == 500) {
                $respuesta = array('mensaje' => 'Error interno del servidor. ', 'status' => false);
            } elseif ($titulo_error == 400) {
                $mensajes_error = $error['title'];
                $respuesta = array('mensaje' => $mensajes_error, 'status' => false);
            }
            log::warning('Error! archivo SiatTrait, listaAutorizacionFacturacion => ');
            log::warning($error);
        }

        return $respuesta;
    }

    public function obtenerAutorizacionFacturacionxID($id)
    {
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_optimo;
        $path = '/autorizacion/v1?';
        // query
        $query = 'id=' . $id;


        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->get($host . $path . $query);

        Log::info("obtenerAutorizacionFacturacionxID => " . $host . $path . $query);
        $respuesta = [];
        if ($response->successful()) {
            $data = $response->json();
            $respuesta = array('data' => $data['ENTITY'], 'status' => true);
        } else {
            $error = $response->json();
            $titulo_error = $error['status'];
            if ($titulo_error == 500) {
                $respuesta = array('mensaje' => 'Error interno del servidor. ', 'status' => false);
            } elseif ($titulo_error == 400) {
                $mensajes_error = $error['title'];
                $respuesta = array('mensaje' => $mensajes_error, 'status' => false);
            }
            log::warning('Error! archivo SiatTrait, obtenerAutorizacionFacturacionxID => ');
            log::warning($error);
        }

        return $respuesta;
    }

    public function updateAutorizacionFacturacion($data, $id)
    {
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_optimo;
        $path = '/autorizacion/v1?id=' . $id;

        $fecha_actual = Carbon::now();
        $user = Auth::user()->name;

        foreach ($data['documentosSector'] as $key => $value) {
            $data_documentos[$key] = array(
                "ambiente" => $data['tipoAmbiente'],
                "modalidad" => $data['tipoModalidad'],
                "codigo_sin" => $value,
            );
        }


        // query o body
        $query = [
            'tipo_modalidad' => $data['tipoModalidad'],
            'ambiente' => $data['tipoAmbiente'],

            'nitEmpresa' => $data['nitEmpresa'],
            'fechaSolicitud' => $data['fechaSolicitud'],
            'tipo_sistema' => $data['tipoSistema'],
            'codigo_sistema' => $data['codigoSistema'],
            'token' => $data['token'],
            'fecha_vencimiento_token' => $data['fechaVencimientoToken'],
            'documentos' => $data_documentos,
            'fechaModificacion' => $fecha_actual->format('Y-m-d H:i:s'),
            'usuarioModificacion' => $user,
            'estado' => $data['estado'],
        ];


        Log::info("updateAutorizacionFacturacion => " . $host . $path, $query);
        // return;
        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->put($host . $path, $query);

        $respuesta = [];
        if ($response->successful()) {
            $data = $response->json();
            $respuesta = array('status' => true);
        } else {
            $error = $response->json();
            $titulo_error = $error['status'];
            if ($titulo_error == 500) {
                $respuesta = array('mensaje' => 'Error interno del servidor. ', 'status' => false);
            } elseif ($titulo_error == 400) {
                $mensajes_error = $error['title'];
                $respuesta = array('mensaje' => $mensajes_error, 'status' => false);
            }
            log::warning('Error! archivo SiatTrait, updateAutorizacionFacturacion => ');
            log::warning($error);
        }

        return $respuesta;
    }

    public function activarAutorizacionFacturacion($id)
    {
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_optimo;
        $path = '/autorizacion/v1/activar?';
        // query
        $query = 'nit=' . $pos_setting->nit_emisor;
        $query .= '&idautorizacion=' . $id;



        Log::info("activarAutorizacionFacturacion => " . $host . $path . $query);
        // return;
        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->put($host . $path . $query);

        $respuesta = [];
        if ($response->successful()) {
            $data = $response->json();
            $respuesta = array('status' => true);
        } else {
            $error = $response->json();
            $titulo_error = $error['status'];
            if ($titulo_error == 500) {
                $respuesta = array('mensaje' => 'Error interno del servidor. ', 'status' => false);
            } elseif ($titulo_error == 400) {
                $mensajes_error = $error['title'];
                $respuesta = array('mensaje' => $mensajes_error, 'status' => false);
            }
            log::warning('Error! archivo SiatTrait, activarAutorizacionFacturacion => ');
            log::warning($error);
        }

        return $respuesta;
    }

    public function reporteFacturasPDF($dataFilter)
    {
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_optimo;
        $path = '/facturacion/v1/libroventas/pdf';
        // query o body
        $query = [
            'nit' => $pos_setting->nit_emisor,
            'sucursal' => $dataFilter['sucursal'],
            'documento' => $dataFilter['documento'],
            'gestion' => $dataFilter['gestion'],
            'mes' => $dataFilter['mes'],
            'consolidado' => true
        ];
        Log::info("reporteFacturasPDF => " . $host . $path, $query);
        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->post($host . $path, $query);

        $respuesta = [];
        if ($response->successful()) {
            $data = $response->json();
            $respuesta = array('pdf' => $data, 'status' => true);
        } else {
            $error = $response->json();
            $titulo_error = $error['status'];
            if ($titulo_error == 500) {
                $respuesta = array('mensaje' => 'Error interno del servidor. ', 'status' => false, 'pdf' => null,);
            } elseif ($titulo_error == 400) {
                $mensajes_error = $error['title'];
                $respuesta = array('mensaje' => $mensajes_error, 'status' => false, 'pdf' => null,);
            } elseif ($titulo_error == 404) {
                $respuesta = array('mensaje' => "Servicio no encontrado, contacte con soporte!", 'status' => false, 'factura' => array(),);
            } else {
                $respuesta = array('mensaje' => "Error de servicio no encontrado o servidor", 'status' => false, 'pdf' => null,);
            }
            log::warning('Error! archivo SiatTrait, reporteFacturasPDF => ');
            log::warning($error);
        }

        return $respuesta;
    }

    public function reporteFacturasExcel($dataFilter)
    {
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_optimo;
        $path = '/facturacion/v1/libroventas/excel';
        $tempName = tempnam(sys_get_temp_dir(), 'response') . '.xlsx';

        // query o body
        $query = [
            'nit' => $pos_setting->nit_emisor,
            'sucursal' => $dataFilter['sucursalFiltroR'],
            'documento' => $dataFilter['documentoSectorFiltroR'],
            'gestion' => $dataFilter['anioFiltroR'],
            'mes' => $dataFilter['mesFiltroR'],
            'consolidado' => true
        ];
        Log::info("reporteFacturasExcel => " . $host . $path, $query);
        $response = Http::sink($tempName)->withHeaders([
            'Authorization' => $bearer,
        ])->post($host . $path, $query);

        $respuesta = [];
        if ($response->successful()) {
            $headers = $response->headers();
            $nameFile = $headers['Content-Disposition'][0];
            log::info("header excel: " . json_encode($headers));
            return array('file' => $tempName, 'name' => $nameFile, 'status' => true);
        } else {
            $error = $response->json();
            $titulo_error = $error['status'];
            if ($titulo_error == 500) {
                $respuesta = array('mensaje' => 'Error interno del servidor. ', 'status' => false, 'file' => null,);
            } elseif ($titulo_error == 400) {
                $mensajes_error = $error['title'];
                $respuesta = array('mensaje' => $mensajes_error, 'status' => false, 'file' => null,);
            } elseif ($titulo_error == 404) {
                $respuesta = array('mensaje' => "Servicio no encontrado, contacte con soporte!", 'status' => false, 'factura' => array(),);
            } else {
                $respuesta = array('mensaje' => "Error de servicio no encontrado o servidor", 'status' => false, 'file' => null,);
            }
            log::warning('Error! archivo SiatTrait, reporteFacturasExcel => ');
            log::warning($error);
            Session::flash('not_permitted', $respuesta['mensaje']);
            return $respuesta;
        }
    }


    function formatoImpresion(int $nro_impresion)
    {
        switch ($nro_impresion) {
            case '1':
                $tipo_impresion = "rollo";
                break;
            case '2':
                $tipo_impresion = "rollo";
                break;
            case '3':
                $tipo_impresion = "media_carta";
                break;
            case '4':
                $tipo_impresion = "media_carta";
                break;
            case '5':
                $tipo_impresion = "media_carta";
                break;
            case '6':
                $tipo_impresion = "EPSAS_01";
                break;
            case '7':
                $tipo_impresion = "media_carta";
                break;
            case '8':
                $tipo_impresion = "MOLE";
                break;
            default:
                $tipo_impresion = "media_carta";
                break;
        }
        return $tipo_impresion;
    }

    /**
     * Funcion para resetear contadores de punto venta
     * aplica reseteo si el año de updated_at es diferente al actual
     * @return void
     */
    public function verificaGestionPV()
    {
        DB::select('CALL check_year_resetcount_pv()');
    }
}
