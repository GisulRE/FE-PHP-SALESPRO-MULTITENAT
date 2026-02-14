<?php

namespace App\Http\Controllers;

use App\Biller;
use App\Cashier;
use Auth;
use App\SiatSucursal;
use App\SiatPuntoVenta;
use Illuminate\Http\Request;
use App\Http\Traits\CufdTrait;
use Illuminate\Validation\Rule;
use Log;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Http;

class SiatPuntoVentaController extends Controller
{
    use CufdTrait;

    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('puntoventa_siat')) {
            $items = SiatPuntoVenta::orderBy('id')->get();
            $sucursales = SiatSucursal::where('estado', true)->get();
            return view('punto-venta.index', ['items' => $items, 'sucursales' => $sucursales]);
        } else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');

    }

    public function store(Request $request)
    {
        $user = Auth::user()->id;
        $data = $request->all();
        if ($data['modoSIN'] == "1") {
            if ($data['modoComisionista'] == "1") {
                $result = $this->registrarPuntoVentaComisionista($data);
            } else {
                $result = $this->registrarPuntoVenta($data);
            }
            if ($result['status'] == true) {
                $data_cuis = $this->obtenerCuis($result['data']['CODIGO_PUNTO_VENTA'], $data['sucursal']);
                if ($data_cuis['status'] == true) {
                    $data['usuario_alta'] = $user;
                    $data['correlativo_factura'] = 1;
                    $data['correlativo_alquiler'] = 1;
                    $data['correlativo_servicios_basicos'] = 1;
                    $data['correlativo_nota_debcred'] = 1;
                    $data['codigo_punto_venta'] = $result['data']['CODIGO_PUNTO_VENTA'];
                    $data['codigo_cuis'] = $data_cuis['data']['CUIS'];
                    $data['fecha_vigencia_cuis'] = $data_cuis['data']['FECHA_VIGENCIA_CUIS'];
                    $data['is_siat'] = true;
                    $puntoVenta = SiatPuntoVenta::create($data);
                    $message = "";
                    try {
                        $this->renovarVigenciaxPuntoVenta($puntoVenta);
                        $message = "CUDF Obtenido.";
                    } catch (\Throwable $th) {
                        Log::error("Error: " . $th->getMessage());
                        $message = "Error al obtener CUDF diario, por favor intente renovar en Ajustes POS.";
                    }
                    return redirect('punto_venta')->with('message', 'Punto de Venta creada correctamente! ' . $message);
                } else {
                    return redirect('punto_venta')->with('not_permitted', $result['mensaje']);
                }
            } else {
                return redirect('punto_venta')->with('not_permitted', $result['mensaje']);
            }
        } else {
            $data['codigo_cuis'] = "000000";
            $data['fecha_vigencia_cuis'] = date('Y-m-d', strtotime('+1 year'));
            $data['is_siat'] = false;
            SiatPuntoVenta::create($data);
            return redirect('punto_venta')->with('message', 'Punto de Venta creada correctamente, Sin Conexion a Impuestos Nacionales');
        }
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $update_data = SiatPuntoVenta::find($data['punto_venta_id']);
        if ($data['modoSINEdit'] == "1") {
            $data['is_siat'] = true;
        } else {
            $data['is_siat'] = false;
        }
        $update_data->update($data);
        return redirect('punto_venta')->with('message', 'Punto de Venta actualizada correctamente');
    }

    public function show(int $id)
    {
        $registro = SiatPuntoVenta::find($id);
        $this->verificarCufd();
        return redirect()->back();

    }

    public function renovarCuis($id, $idPuntoVenta, $idSucursal)
    {
        $update_data = SiatPuntoVenta::find($id);
        $data_cuis = $this->obtenerCuis($idPuntoVenta, $idSucursal);
        if ($update_data && $data_cuis['status'] == true) {
            $update_data->codigo_cuis = $data_cuis['data']['CUIS'];
            $update_data->fecha_vigencia_cuis = $data_cuis['data']['FECHA_VIGENCIA_CUIS'];
            $update_data->update();
            if (isset($data_cuis['data']['MENSAJES'])) {
                $descripcion = "Mensajes: ";
                foreach ($data_cuis['data']['MENSAJES'] as $mensaje) {
                    $descripcion .= " Código: " . $mensaje['codigo'] . " - Descripción: " . $mensaje['descripcion'];
                }
                $respuesta = array("status" => true, "mensaje" => "" . $descripcion);
            } else
                $respuesta = array("status" => true, "mensaje" => "Se Renovo y Actualizo el CUIS y Fecha Vigencia con éxito");
        } else {
            if ($update_data == null)
                $respuesta = array("status" => false, "mensaje" => "No se encuentra Registrado el punto de venta en el POS");
            else
                $respuesta = array("status" => false, "mensaje" => $data_cuis['data']['mensaje']);
        }
        return $respuesta;
    }

    public function renovacionMasivaCuis()
    {
        $status = false;
        $list_puntosVentas = SiatPuntoVenta::where([['is_siat', true], ['is_active', true]])->get();
        $mensaje = "";
        foreach ($list_puntosVentas as $punto_venta) {
            $data_cuis = $this->obtenerCuis($punto_venta->codigo_punto_venta, $punto_venta->sucursal);
            if ($punto_venta && $data_cuis['status'] == true) {
                $punto_venta->codigo_cuis = $data_cuis['data']['CUIS'];
                $punto_venta->fecha_vigencia_cuis = $data_cuis['data']['FECHA_VIGENCIA_CUIS'];
                $punto_venta->update();
                if (isset($data_cuis['data']['MENSAJES'])) {
                    $descripcion = "Mensajes: ";
                    foreach ($data_cuis['data']['MENSAJES'] as $code) {
                        $descripcion .= " Código: " . $code['codigo'] . " - Descripción: " . $code['descripcion'];
                    }
                    $status = true;
                    $mensaje .= "" . $descripcion;
                } else {
                    $status = true;
                    $mensaje .= "Se Renovo y Actualizo todos los CUIS y Fecha Vigencia con éxito";
                }
            } else {
                if ($punto_venta == null) {
                    $status = false;
                    $mensaje .= "No se encuentra Registrado el punto de venta en el POS";
                } else {
                    $status = false;
                    $mensaje .= $data_cuis['mensaje'];
                }
            }
        }
        return array("status" => $status, "mensaje" => $mensaje);
    }

    public function destroy(int $id)
    {
        $puntoVenta = SiatPuntoVenta::find($id);
        if ($puntoVenta) {
            if ($puntoVenta->is_siat) {
                $result = $this->bajaPuntoVenta($puntoVenta);
                if ($result['status'] == true) {
                    if ($result['data']['ESTADO'] == "OK") {
                        $puntoVenta->is_active = false;
                        $puntoVenta->save();
                        return redirect('punto_venta')->with('message', 'El Punto de Venta fue dado de baja en Impuestos');
                    }
                } else {
                    return redirect('punto_venta')->with('not_permitted', $result['mensaje']);
                }
            } else {
                $puntoVenta->is_active = false;
                $puntoVenta->save();
                return redirect('punto_venta')->with('message', 'El Punto de Venta fue dado de baja');
            }
        } else {
            return redirect('punto_venta')->with('not_permitted', "Punto de Venta no encontrado.");
        }

    }

    public function estadoPuntoVenta(int $id_biller)
    {
        $biller_data = Biller::find($id_biller);
        $lims_cashier_data = Cashier::select('id', 'end_date')->where([['account_id', $biller_data->account_id], ['is_active', true]])->first();
        $puntoVenta = SiatPuntoVenta::where([['codigo_punto_venta', $biller_data->punto_venta_siat], ['sucursal', $biller_data->sucursal]])->first();
        if ($lims_cashier_data) {
            if ($puntoVenta) {
                if ($puntoVenta->is_active && $puntoVenta->is_siat) {
                    return array('status' => true, 'cashier' => true, 'puntoVenta' => $puntoVenta->nombre_punto_venta, 'message' => "Punto de Venta Operativo");
                } elseif ($puntoVenta->is_active && $puntoVenta->is_siat == false) {
                    return array('status' => true, 'cashier' => true, 'puntoVenta' => $puntoVenta->nombre_punto_venta, 'message' => "Punto de Venta Operativo sin Impuestos Nacionales");
                } else {
                    return array('status' => false, 'cashier' => true, 'puntoVenta' => $puntoVenta->nombre_punto_venta, 'message' => "Punto de Venta con Baja en Impuestos Nacionales");
                }
            } else {
                return array('status' => false, 'cashier' => true, 'puntoVenta' => 'No Definido', 'message' => "Punto de Venta no existe en los registros, contacte con soporte");
            }
        } else {
            return array('status' => false, 'cashier' => false, 'puntoVenta' => 'No Definido', 'message' => "Sin Apertura de Caja, Inicie Sesion Con el Facturador asociado para aperturar la caja");
        }
    }
}