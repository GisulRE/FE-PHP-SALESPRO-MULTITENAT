<?php

namespace App\Http\Controllers;

use Log;
use App\Sale;
use App\Biller;
use App\Account;
use App\Payment;
use App\Product;
use App\Customer;
use App\SiatCufd;
use Carbon\Carbon;
use App\PosSetting;
use App\CustomerSale;
use App\Product_Sale;
use App\SiatSucursal;
use App\MethodPayment;
use App\SiatPuntoVenta;

use App\ControlContingencia;
use Illuminate\Http\Request;
use App\SiatParametricaVario;
use App\Http\Traits\CufdTrait;
use App\Http\Traits\SiatTrait;
use App\ControlContingenciaPaquetes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Role;

class ControlContingenciaController extends Controller
{
    use SiatTrait;
    use CufdTrait;

    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('contingencia_siat')) {
            $fecha_actual = new Carbon();
            $c_contingencias = ControlContingencia::orderBy('fecha_inicio_evento', 'DESC')->get();

            $sucursales = SiatSucursal::where('estado', true)->get();
            $parametricas = SiatParametricaVario::where('tipo_clasificador', 'eventosSignificativos')->orderBy('codigo_clasificador', 'ASC')->get();

            return view('control-contingencia.index', [
                'sucursales' => $sucursales,
                'parametricas' => $parametricas,
                'c_contingencias' => $c_contingencias,
                'fecha_actual' => $fecha_actual,
            ]);
        } else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function show($id = null)
    {

    }

    public function store(Request $request)
    {
        try {
            $user = Auth::user()->id;
            $data = $request->all();

            $update_p_venta = SiatPuntoVenta::where('codigo_punto_venta', $data['codigo_punto_venta'])->where('sucursal', $data['sucursal'])->first();

            $data_p_venta_siat = SiatCufd::where('sucursal', $data['sucursal'])->where('codigo_punto_venta', $data['codigo_punto_venta'])->where('estado', true)->first();

            $tipo_factura_lookup = [
                'compra-venta' => 1,
                'alquiler' => 2,
                'servicio-basico' => 13,
            ];
            $tipo_factura = $tipo_factura_lookup[$data['tipo_factura']] ?? 1;

            $data['codigo_documento_sector'] = $tipo_factura;
            $data['usuario_modificacion'] = $user;
            $data['cufd_evento'] = $data_p_venta_siat->codigo_cufd;
            $data['estado'] = 'EN_PROCESO';
            $data['cuis'] = $update_p_venta->codigo_cuis;
            ControlContingencia::create($data);

            $mensaje = 'Evento registrado correctamente!';

            $update_p_venta->modo_contingencia = true;
            $update_p_venta->save();
        } catch (\Throwable $th) {
            $mensaje = 'Error, no se logró registrar el evento.';
        }
        Session::flash('info', $mensaje);
        return redirect('contingencia');
    }

    public function getPuntoVenta($sucursal)
    {
        $puntos_ventas = SiatPuntoVenta::where('sucursal', $sucursal)->where('modo_contingencia', false)->get();
        return $puntos_ventas;
    }

    public function registrarEvento($id)
    {
        $data_c_contingencia = ControlContingencia::where('id', $id)->first();
        $data_p_venta = SiatPuntoVenta::where('sucursal', $data_c_contingencia->sucursal)->where('codigo_punto_venta', $data_c_contingencia->codigo_punto_venta)->first();

        // Se busca exactamente al regitro cufd registrado en control contingencia
        $registro = SiatCufd::where('codigo_cufd', $data_c_contingencia->cufd_evento)->first();
        $cufd_update = SiatCufd::find($registro->id);
        $cufd_update->estado = false;
        $cufd_update->save();

        $this->renovarCUFD($data_p_venta); // CufdTrait
        $this->registrarEventoSignificativo($data_c_contingencia->id); // SiatTrait
        $this->generarPaquetesAlControlContingenciaPaquete($id); // ControlContingencia

        return redirect('contingencia');
    }

    public function registrarEventoAuto($id_biller)
    {
        $estado = false;
        $data_biller = Biller::find($id_biller);
        $data_c_contingencia = ControlContingencia::where([['codigo_punto_venta', $data_biller->punto_venta_siat], ['estado', 'EN_PROCESO']])->first();
        $data_p_venta = SiatPuntoVenta::where('sucursal', $data_c_contingencia->sucursal)->where('codigo_punto_venta', $data_c_contingencia->codigo_punto_venta)->first();

        // Se busca exactamente al regitro cufd registrado en control contingencia
        $registro = SiatCufd::where('codigo_cufd', $data_c_contingencia->cufd_evento)->first();
        $cufd_update = SiatCufd::find($registro->id);
        $cufd_update->estado = false;
        $cufd_update->save();
        $contpaquete = 0;
        $this->renovarCUFD($data_p_venta); // CufdTrait
        $this->registrarEventoSignificativo($data_c_contingencia->id); // SiatTrait
        $controlpaquetes = ControlContingenciaPaquetes::where('control_contingencia_id', $data_c_contingencia->id)->get();
        /** elimina paquetes anteriores si es que se ha generado en un proceso anterior */
        foreach ($controlpaquetes as $paquete) {
            $paquete->delete();
        }
        /** genera nuevos paquetes */
        $this->generarPaquetesAlControlContingenciaPaquete($data_c_contingencia->id); // ControlContingencia
        $paquetes = $this->obtenerVentasxPaquetes($data_c_contingencia->id);
        foreach ($paquetes as $paquete) {
            log::info("Paquete id: " . $paquete->id . " - paso 1");
            $resultVP = $this->validarDatosPaquete($paquete->id);
            //log::info("resultEP: ".$resultVP);
            if ($resultVP == null) {
                log::info("Paquete id: " . $paquete->id . " - paso 2");
                $resultEP = $this->enviarPaquetes($paquete->id);
                //log::info("resultEP: ".$resultEP);
                if ($resultEP == 'PENDIENTE') {
                    log::info("Paquete id: " . $paquete->id . " - paso 3");
                    $resultEPE = $this->verificarEstadoPaqueteEnviado($paquete->id);
                    //log::info("resultEPE: ".$resultEPE);
                    if ($resultEPE == 'VALIDADA') {
                        log::info("Paquete id: " . $paquete->id . " - paso 4");
                        $resultOAVP = $this->obtenerArregloVentasxPaquete($paquete->id);
                        //log::info("resultOAVP: ".$resultOAVP);
                        if ($resultOAVP) {
                            $contpaquete++;
                        } else {
                            log::info("Paquete id: " . $paquete->id . " - paso 4");
                            $resultOAVP = $this->obtenerArregloVentasxPaquete($paquete->id);
                            //log::info("resultOAVP: ".$resultOAVP);
                            if ($resultOAVP) {
                                $contpaquete++;
                            } else {
                                $estado = false;
                                $error = 'Paquete no pudo actualizar ventas, revise logs en contingencia';
                                log::warning($error);
                                break;
                            }
                        }
                    } else {
                        $estado = false;
                        $error = 'Paquete observado al verificar paquete enviado, revise logs en contingencia';
                        log::warning($error);
                        break;
                    }
                } else {
                    $estado = false;
                    $error = 'Paquete observado al enviar paquete, revise logs en contingencia';
                    log::warning($error);
                    break;
                }
            } else {
                $list_sales_exception_nit = [];
                foreach ($resultVP as $key => $sale) {
                    $list_sales_exception_nit[$key] = $sale['sale_id'];
                }
                $requestETNIT = new Request();
                $requestETNIT->setMethod('POST');
                $requestETNIT->request->add(['arreglo_ventas' => $list_sales_exception_nit]);
                try {
                    $resultETNIT = $this->confirmarExcepcionTodosNit($requestETNIT);
                } catch (\Throwable $th) {
                    $estado = false;
                    $error = 'Error no se logró realizar la operación! genera paquetes contingencia => ' . $paquete->id;
                    log::warning($error);
                    log::warning($th);
                    break;
                }
            }
        }
        if ($contpaquete == sizeof($paquetes)) {
            $user = Auth::user()->id;
            $data_p_venta->usuario_alta = $user;
            $data_p_venta->modo_contingencia = false;
            $data_p_venta->save();

            $data_c_contingencia->usuario_modificacion = $user;
            $data_c_contingencia->estado = 'CERRADO';
            $data_c_contingencia->save();
            $estado = true;
            $msj = 'Contingencia cerrada! Todos los paquetes fueron enviados exitosamente, Modo Online! ';
            log::info($msj);
        } else {
            log::warning($error);
            $estado = false;
            $msj = 'No se pudo cerrar contingencia, porque nose pudo enviar todos los paquete, intente enviarlos desde Ventas->Contigencia! Error:' . $error;
        }
        return array("estado" => $estado, "mensaje" => $msj);
    }

    // selecciona todos los paquetes perteneciente al determinado control-contingencia para mostrarla en el modal. 
    public function obtenerVentasxPaquetes($id)
    {
        $paquetes = ControlContingenciaPaquetes::where('control_contingencia_id', $id)->get();
        return $paquetes;
    }

    // Botón Paso CERO, verificar las ventas con NIT
    public function validarDatosPaquete($control_contingencia_paquete_id)
    {
        $data_c_contingencia = ControlContingenciaPaquetes::where('id', $control_contingencia_paquete_id)->first();
        $data_control = ControlContingencia::where('id', $data_c_contingencia->control_contingencia_id)->first();

        $listas = json_decode($data_c_contingencia->arreglo_ventas, true);
        $respuesta = array();
        $cadena_nros_facturas = "";
        foreach ($listas as $key => $value) {
            foreach ($value as $venta) {
                $todo_cadena[] = $venta;
                $data_cliente = CustomerSale::where('sale_id', $venta)->first();
                $data_venta = Sale::where('id', $venta)->first();
                if ($data_cliente->tipo_documento == 5) {
                    $response = $this->getResponseNIT($data_cliente->valor_documento);
                    $mensajes = $response['MENSAJES'];
                    foreach ($mensajes as $key => $value) {
                        if ($value['codigo'] == 994 && $data_cliente->codigo_excepcion != 1) {
                            $respuesta[] = array('sale_id' => $venta, 'tipo_documento' => $data_cliente->tipo_documento, 'valor_documento' => $data_cliente->valor_documento, 'reference_no' => $data_venta->reference_no, 'grand_total' => $data_venta->grand_total, 'estado' => $data_cliente->codigo_excepcion);
                            // se arma una cadena para tener los números de facturas con excepcion
                            $cadena_nros_facturas .= $data_cliente->cuf . '|';
                        }
                    }
                }
            }
        }
        Log::info("La cadena de facturas a reparar son => " . $cadena_nros_facturas);
        if (strlen($cadena_nros_facturas) > 0) {
            // se procede a enviar peticion al backend que actualice los códigos de excepción
            Log::info("entro a enviar la peticion");
            $this->solititudActualizacionCodigoExcepcion($data_control, $cadena_nros_facturas); // SiatTrait
        }

        return $respuesta;
    }

    // Botón Paso UNO, crear el archivo CSV, y lo envía 
    public function enviarPaquetes($control_contingencia_paquete_id)
    {
        $data_c_contingencia = ControlContingenciaPaquetes::where('id', $control_contingencia_paquete_id)->first();
        $data_contingencia = ControlContingencia::where('id', $data_c_contingencia->control_contingencia_id)->first();

        $listas = json_decode($data_c_contingencia->arreglo_ventas, true);

        Log::info("El arreglo de listas es => " . json_encode($listas['CONTINGENCIA']));
        $array_lista = $listas['CONTINGENCIA'];

        if ($data_contingencia->codigo_evento > 4) {
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

        Log::info("El nro inicial es => " . $nro_factura_inicial . " El final es => " . $nro_factura_final);
        $estado_paquete = $this->envioPaqueteRecepcion($control_contingencia_paquete_id, $nro_factura_inicial, $nro_factura_final); // SiatTrait


        return $estado_paquete;
    }

    public function generarPaquetesAlControlContingenciaPaquete($id)
    {
        try {
            $pos_setting = PosSetting::latest()->first();
            $cant_max_paquete = $pos_setting->cant_max_contingencia;
            $data_c_contingencia = ControlContingencia::where('id', $id)->first();

            $ventas = $count_ventas = CustomerSale::where('sucursal', $data_c_contingencia->sucursal)->where('codigo_punto_venta', $data_c_contingencia->codigo_punto_venta)->where('estado_factura', 'CONTINGENCIA')->where('codigo_documento_sector', $data_c_contingencia->codigo_documento_sector)->get();

            $cant_paquetes = ceil(($count_ventas->count() / $cant_max_paquete));

            $data_c_contingencia->cantidad_paquetes = $cant_paquetes;
            $data_c_contingencia->save();

            // si la cantidad de ventas excede el máximo, se procede en otro paquete
            for ($i = 1; $i <= $cant_paquetes; $i++) {

                // toma las primeras ventas 
                $paquete = $ventas->take($cant_max_paquete);

                // guardando control_contingencia_detalle por paquete 
                $obj_detalle_paquete = new ControlContingenciaPaquetes();
                $obj_detalle_paquete->control_contingencia_id = $id;
                $obj_detalle_paquete->cantidad_ventas = $paquete->count();
                if ($data_c_contingencia->codigo_evento > 4) {
                    $obj_detalle_paquete->glosa_nro_factura_inicio_a_fin = 'Paquete ' . $i . ' | Del ' . $paquete->min('nro_factura_manual') . ' - Al: ' . $paquete->max('nro_factura_manual');
                    if ($paquete->count() == 1) {
                        $obj_detalle_paquete->glosa_nro_factura_inicio_a_fin = 'Paquete ' . $i . ' | Del ' . $paquete->min('nro_factura_manual');
                    }
                } else {
                    $obj_detalle_paquete->glosa_nro_factura_inicio_a_fin = 'Paquete ' . $i . ' | Del ' . $paquete->min('nro_factura') . ' - Al: ' . $paquete->max('nro_factura');
                    if ($paquete->count() == 1) {
                        $obj_detalle_paquete->glosa_nro_factura_inicio_a_fin = 'Paquete ' . $i . ' | Del ' . $paquete->min('nro_factura');
                    }
                }

                $obj_detalle_paquete->arreglo_ventas = $paquete->mapToGroups(function ($item, $key) {
                    return [$item['estado_factura'] => $item['sale_id']];
                });

                $obj_detalle_paquete->estado = 'PENDIENTE';
                $obj_detalle_paquete->save();

                // corta las lista de ventas
                $ventas = $ventas->splice($cant_max_paquete);
            }
        } catch (\Throwable $th) {
            $mensaje = 'Error no se logró realizar la operación! genera paquetes contingencia => ' . $id;
            log::warning($mensaje);
            log::warning($th);
            Session::flash('not_permitted', $mensaje);
        }

    }



    public function cerrarModoContingencia($id)
    {
        $data_c_contingencia = ControlContingencia::where('id', $id)->first();
        $data_p_venta = SiatPuntoVenta::where('sucursal', $data_c_contingencia->sucursal)->where('codigo_punto_venta', $data_c_contingencia->codigo_punto_venta)->first();

        $user = Auth::user()->id;
        $data_p_venta->usuario_alta = $user;
        $data_p_venta->modo_contingencia = false;
        $data_p_venta->save();

        $data_c_contingencia->usuario_modificacion = $user;
        $data_c_contingencia->estado = 'CERRADO';
        $data_c_contingencia->save();

        $msj = 'Contingencia cerrada! Todos los paquetes fueron enviados exitosamente! ';
        Session::flash('success', $msj);
        return redirect('contingencia');
    }

    public function verificarEstadoPaqueteEnviado($control_contingencia_paquete_id)
    {
        $response = $this->verificarEstadoPaquete($control_contingencia_paquete_id);

        if ($response->status() == 200) {
            $data_estado = $response->json();
        }
        if ($response->status() == 400) {
            $data = $response->json();
            return $data['title'];
        }

        // en caso esté validada se procede a cambiar el estado de las ventas facturadas de contingencia => facturadas; y el paquete contingencia se actualiza
        if ($data_estado['codigo_descripcion'] == 'VALIDADA') {
            $data_c_contingencia = ControlContingenciaPaquetes::where('id', $control_contingencia_paquete_id)->first();
            $data_c_contingencia->estado = 'VALIDADA';
            $data_c_contingencia->respuesta_servicio = '908';
            $data_c_contingencia->save();
        }

        if ($data_estado['codigo_descripcion'] == 'OBSERVADA') {
            $data_c_contingencia = ControlContingenciaPaquetes::where('id', $control_contingencia_paquete_id)->first();
            $data_c_contingencia->estado = 'OBSERVADA';
            $data_c_contingencia->respuesta_servicio = '904';
            $data_c_contingencia->log_errores = $data_estado['mensajes_recepcion'];
            $mensajes_error = $data_estado['mensajes_recepcion'];
            log::warning("El paquete ha sido observada => " . json_encode($mensajes_error));
            $data_c_contingencia->save();
        }

        return $data_estado['codigo_descripcion'];
    }

    public function obtenerArregloVentasxPaquete($control_contingencia_paquete_id)
    {
        $respuesta = $this->obtenerVentasEnviadasEnPaquetesModoContingencia($control_contingencia_paquete_id); // SiatTrait
        return $respuesta;
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



    // Carga arhivo Excel o CSV para determinado punto de venta en modo contingencia
    public function cargarArchivo(Request $request)
    {
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
        // Obteniendo cabezera 
        $escapedHeader = $sheet_data[0];
        // PROCEDIMIENTO POR REALIZAR
        // DEBE GUARDARSE COMO VENTA + DETALLE + DATOS DEL CLIENTE
        $nroDocument = 0;
        $cant_paquetes = 0;
        // $data_warehouse = Biller::where('is_active', true)->first();
        //$customer_data = Customer::where(['name' => 'Clientes Varios', 'is_active' => true])->first();
        $dataControlContingencia = ControlContingencia::find($dataRequest['idcontrolcontingencia']);
        $data_biller = Biller::where('punto_venta_siat', $dataControlContingencia->codigo_punto_venta)->where('is_active', true)->first();
        $bandera_llenado_datos = false;
        $data_head = [];
        $data_detail = [];
        $list_details = [];
        foreach ($sheet_data as $key => $val) {
            $product = null;
            if ($key != 0 && $key > 0 && $val[0] != null) {
                $data_row = array_combine($escapedHeader, $val);
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
                    $this->create_product($data_row['codigoProducto'], $data);
                    $product = Product::where(['code' => $data_row['codigoProducto'], 'is_active' => true])->first();
                }
                $customer_data = Customer::where(['valor_documento' => $data_row['numeroDocumento'], 'is_active' => true])->first();
                if ($data_row['montoDescuentoLey1886'] < 0) {
                    $data_row['montoDescuentoLey1886'] = 0 - $data_row['montoDescuentoLey1886'];
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
                    $lims_customer_data['address'] = $data_row['domicilio_cliente'];
                    $lims_customer_data['country'] = 'Bolivia';
                    $lims_customer_data['price_type'] = 0;
                    $lims_customer_data['is_credit'] = false;
                    $lims_customer_data['credit'] = 0;
                    $lims_customer_data['is_active'] = true;
                    $customer_data = Customer::create($lims_customer_data);
                }
                if ($val[0] == $nroDocument) {
                    $data_head['item'] += 1;
                    $data_head['total_qty'] += number_format((float) $data_row['cantidad'], 2);
                    /** Detalle Venta */
                    $data_detail['product_id'] = $product->id;
                    $data_detail['qty'] = number_format((float) $data_row['cantidad'], 2);
                    $data_detail['sale_unit_id'] = 0;
                    $data_detail['net_unit_price'] = number_format((float) $data_row['precioUnitario'], 2);
                    $data_detail['discount'] = number_format((float) $data_row['montoDescuento'], 2);
                    $data_detail['tax_rate'] = 0;
                    $data_detail['tax'] = number_format((float) 0, 2);
                    $data_detail['total'] = number_format((float) $data_row['precioUnitario'], 2);
                    $list_details[] = $data_detail;

                    if (isset($sheet_data[$key + 1]) && $sheet_data[$key + 1][0] != $val[0]) {
                        /** Cabecera Venta */
                        if ($data_row['montoDescuentoLey1886'] > 0) {
                            $data_head['sale_note'] = "- Descuento Ley 1886 : " . number_format((float) $data_row['montoDescuentoLey1886'], 2);
                        }
                        $data_head['user_id'] = Auth::user()->id;
                        $data_head['customer_id'] = $customer_data->id;
                        $data_head['warehouse_id'] = $data_biller->warehouse_id;
                        $data_head['biller_id'] = $data_biller->id;
                        $data_head['order_discount'] = number_format((float) $data_row['montoDescuentoLey1886'], 2);
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

                        if ($dataControlContingencia->tipo_factura == 'servicio-basico') {
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
                            $data_siat['lectura_medidor_anterior'] = $data_row['lecturaMedidorAnterior'];
                            $data_siat['lectura_medidor_actual'] = $data_row['lecturaMedidorActual'];

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
                        $this->create_customersale($lims_sale_data->id, $data_siat, $dataControlContingencia);
                        $bandera_llenado_datos = true;
                        $cant_paquetes++;
                        $data_head = [];
                        $data_detail = [];
                        $list_details = [];
                    }
                } else {
                    $data_head['item'] = 1;
                    $data_head['total_qty'] = number_format((float) $data_row['cantidad'], 2);

                    /** Detalle Venta */
                    $data_detail['sale_id'] = null;
                    $data_detail['product_id'] = $product->id;
                    $data_detail['qty'] = number_format((float) $data_row['cantidad'], 2);
                    $data_detail['sale_unit_id'] = 0;
                    $data_detail['net_unit_price'] = number_format((float) $data_row['precioUnitario'], 2);
                    $data_detail['discount'] = number_format((float) $data_row['montoDescuento'], 2);
                    $data_detail['tax_rate'] = 0;
                    $data_detail['tax'] = number_format((float) 0, 2);
                    $data_detail['total'] = number_format((float) $data_row['precioUnitario'], 2);
                    $list_details[] = $data_detail;
                    if (isset($sheet_data[$key + 1]) && $sheet_data[$key + 1][0] != $val[0]) {
                        /** Cabecera Venta */
                        if ($data_row['montoDescuentoLey1886'] > 0) {
                            $data_head['sale_note'] = "- Descuento Ley 1886 : " . number_format((float) $data_row['montoDescuentoLey1886'], 2);
                        }
                        $data_head['user_id'] = Auth::user()->id;
                        $data_head['customer_id'] = $customer_data->id;
                        $data_head['warehouse_id'] = $data_biller->warehouse_id;
                        $data_head['biller_id'] = $data_biller->id;
                        $data_head['order_discount'] = number_format((float) $data_row['montoDescuentoLey1886'], 2);
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

                        if ($dataControlContingencia->tipo_factura == 'servicio-basico') {
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
                            $data_siat['lectura_medidor_anterior'] = $data_row['lecturaMedidorAnterior'];
                            $data_siat['lectura_medidor_actual'] = $data_row['lecturaMedidorActual'];

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
                        $this->create_customersale($lims_sale_data->id, $data_siat, $dataControlContingencia);
                        $bandera_llenado_datos = true;
                        $cant_paquetes++;
                        $data_head = [];
                        $data_detail = [];
                        $list_details = [];
                    }
                }
                $nroDocument = $val[0];
            }
        }

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        unset($reader);

        // creacion del detalle paquete
        if ($bandera_llenado_datos == true) {
            $data_p_venta = SiatPuntoVenta::where('codigo_punto_venta', $data_biller->punto_venta_siat)->first();

            $dataControlContingencia->estado = "EN_PROCESO";
            $dataControlContingencia->cantidad_paquetes = $cant_paquetes;
            $dataControlContingencia->save();

            // $this->generarPaquete($dataControlContingencia->id);
        }
        $mensaje = 'Archivo cargado correctamente, Se cargaron ' . $cant_paquetes . ' ventas';

        return redirect('contingencia')->with('message', $mensaje);
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
        foreach ($dataDetails as $key => $value) {
            $value['sale_id'] = $lims_sale_data->id;
            Product_Sale::create($value);
        }
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

        $product->codigo_actividad = $lims_category_data->codigo_actividad;
        $product->codigo_producto_servicio = $lims_category_data->codigo_producto_servicio;

        $product->save();
        return $product;
    }

    private function create_customersale(int $idSale, array $data, $dataControlContingencia)
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
        $obj_cliente->codigo_documento_sector = $dataControlContingencia->codigo_documento_sector;
        $obj_cliente->usuario = $data['usuario'];

        if ($dataControlContingencia->tipo_factura == 'servicio-basico') {
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
        $obj_cliente->estado_factura = "CONTINGENCIA";
        $obj_cliente->sucursal = $dataControlContingencia->sucursal;
        $obj_cliente->codigo_punto_venta = $dataControlContingencia->codigo_punto_venta;
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


    // Función prevent (vista index de modoContingencia)
    public function verificarArchivoExcel(Request $request)
    {
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

        $dataControlContingencia = ControlContingencia::find($dataRequest['idcontrolcontingencia']);
        // Obteniendo Datos de Punto de Venta
        $data_biller = Biller::where('punto_venta_siat', $dataControlContingencia->codigo_punto_venta)->first();
        $data_p_venta = SiatPuntoVenta::where('codigo_punto_venta', $data_biller->punto_venta_siat)->first();

        $nroDocument = null;
        $subtotal = null;
        $contador_error = 0;
        $total_rows = sizeof($sheet_data);
        foreach ($sheet_data as $key => $val) {
            if ($key != 0 && $key > 0 && $val[0] != null) {
                $data_row = array_combine($escapedHeader, $val);
                if ($data_row['nombreRazonSocial'] == null || $data_row['nombreRazonSocial'] == '') {
                    $key++;
                    $contador_error++;
                    $error_message .= "Error en Validar razon social en factura, Revise el Documento en Fila Nro: " . $key . " | " . "\n";
                }
                if ($data_row['numeroDocumento'] == null || $data_row['numeroDocumento'] == '') {
                    $key++;
                    $contador_error++;
                    $error_message .= "Error en Validar numero de documento en factura, Revise el Documento en Fila Nro: " . $key . " | " . "\n";
                }
                $data_row['codigoSucursal'] = '' . $data_row['codigoSucursal'];
                if ($data_row['codigoSucursal'] == null || $data_row['codigoSucursal'] == '') {
                    $key++;
                    $contador_error++;
                    $error_message .= "Error en Validar numero de sucursal nulo o vacio, Revise el Documento en Fila Nro: " . $key . " | " . "\n";
                } else if ($data_p_venta->sucursal != $data_row['codigoSucursal']) {
                    $key++;
                    $contador_error++;
                    $error_message .= "Error en Validar numero de sucursal no coincide con el sistema, Revise el Documento en Fila Nro: " . $key . " | " . "\n";
                }
                $data_row['montoTotal'] = $this->formatNumber($data_row['montoTotal']);
                $data_row['subTotal'] = $this->formatNumber($data_row['subTotal']);
                $data_row['montoDescuentoLey1886'] = $this->formatNumber($data_row['montoDescuentoLey1886']);
                if ($data_row['montoDescuentoLey1886'] < 0) {
                    $data_row['montoDescuentoLey1886'] = 0 - $data_row['montoDescuentoLey1886'];
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
                    }
                } else {
                    $subtotal = 0;
                    $subtotal = bcadd((float) $subtotal, (float) $data_row['subTotal'], 2);
                    $subtotal = $subtotal - $data_row['montoDescuentoLey1886'];
                    if (isset($sheet_data[$key + 1]) && $sheet_data[$key + 1][0] != $val[0]) {
                        if ((float) $data_row['montoTotal'] != (float) $subtotal) {
                            $key++;
                            $contador_error++;
                            $error_message .= "Error en Validar un monto total de Una factura, Revise el Documento en Fila Nro: " . $key . " | " . "\n";
                        }
                    }
                }
                $nroDocument = $val[0];
            }
        }
        $status = false;
        if ($contador_error > 0) {
            $message = $error_message;
            $status = false;
        } else {
            $message = "Verificado correctamente";
            $status = true;
        }
        unset($reader);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $result = array(
            'message' => $message,
            'totalreq' => $contador_error,
            'totalprocess' => $total_rows,
            'status' => $status
        );
        return json_encode($result);
    }


    public function obtenerLogsErrores($id)
    {
        $data_c_contingencia = ControlContingenciaPaquetes::where('id', $id)->first();

        return $data_c_contingencia;
    }
}