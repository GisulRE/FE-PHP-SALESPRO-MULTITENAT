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
use Illuminate\Support\Facades\Session;

class SiatPuntoVentaController extends Controller
{
    use CufdTrait;

    private function resolveSucursalCode($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        $textValue = trim((string) $value);
        if ($textValue === '') {
            return null;
        }

        if (strpos($textValue, '|') !== false) {
            $textValue = trim(explode('|', $textValue)[0]);
        }

        $upperValue = strtoupper($textValue);

        $sucursal = SiatSucursal::whereRaw('UPPER(nombre) = ?', [$upperValue])
            ->orWhereRaw('UPPER(descripcion_sucursal) = ?', [$upperValue])
            ->orWhereRaw('UPPER(domicilio_tributario) = ?', [$upperValue])
            ->first();

        return $sucursal ? (int) $sucursal->sucursal : null;
    }

    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if (\App\Helpers\RolePermission::check('puntoventa_siat')) {
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
        $data['sucursal'] = $this->resolveSucursalCode($data['sucursal'] ?? null);

        if ($data['sucursal'] === null) {
            return redirect('punto_venta')->with('not_permitted', 'Sucursal invalida. Seleccione una sucursal valida.');
        }

        $pos_setting = \App\PosSetting::latest()->first();
        if ($data['modoSIN'] == "1" && (!$pos_setting || !$pos_setting->nit_emisor)) {
            return redirect('punto_venta')->with('not_permitted', 'El NIT del emisor no está configurado en los Ajustes POS. Configurelo antes de registrar.');
        }

        $data['codigo_punto_venta'] = (int) ($data['codigo_punto_venta'] ?? 0);
        $data['tipo_punto_venta'] = (int) ($data['tipo_punto_venta'] ?? 0);

        if ($data['tipo_punto_venta'] <= 0) {
            return redirect('punto_venta')->with('not_permitted', 'Tipo Punto Venta invalido.');
        }

        $data['usuario_alta'] = $user;
        $data['correlativo_factura'] = $data['correlativo_factura'] ?? 1;
        $data['correlativo_alquiler'] = $data['correlativo_alquiler'] ?? 1;
        $data['correlativo_servicios_basicos'] = $data['correlativo_servicios_basicos'] ?? 1;
        $data['correlativo_nota_debcred'] = $data['correlativo_nota_debcred'] ?? 1;
        $data['is_active'] = $data['is_active'] ?? true;

        if ($data['modoSIN'] == "1") {
            Log::info('[store PuntoVenta] Registrando en SIAT', [
                'sucursal'           => $data['sucursal'],
                'tipo_punto_venta'   => $data['tipo_punto_venta'],
                'modoComisionista'   => $data['modoComisionista'] ?? '0',
                'cuis_manual'        => !empty($data['codigo_cuis']) ? 'sí' : 'no',
            ]);

            if ($data['modoComisionista'] == "1") {
                $result = $this->registrarPuntoVentaComisionista($data);
            } else {
                $result = $this->registrarPuntoVenta($data);
            }

            Log::info('[store PuntoVenta] Resultado registro SIAT', [
                'status'  => $result['status'],
                'mensaje' => $result['mensaje'] ?? ($result['data'] ?? null),
            ]);

            if ($result['status'] == true) {
                $data['codigo_punto_venta'] = $result['data']['CODIGO_PUNTO_VENTA'];
                $data['is_siat'] = true;

                // Si el usuario ingresó manualmente un CUIS, lo usamos
                if (!empty($data['codigo_cuis'])) {
                    Log::info('[store PuntoVenta] CUIS ingresado manualmente', ['cuis' => $data['codigo_cuis']]);
                    $data['fecha_vigencia_cuis'] = $data['fecha_vigencia_cuis'] ?? date('Y-m-d H:i:s', strtotime('+1 year'));
                    $puntoVenta = SiatPuntoVenta::create($data);
                    $message = "CUIS ingresado manualmente.";
                    try {
                        $resultadoCufd = $this->renovarVigenciaxPuntoVenta($puntoVenta);
                        if ($resultadoCufd['status'] == true) {
                            $message .= " CUFD Obtenido.";
                        } else {
                            Log::error('[store PuntoVenta] Error al obtener CUFD con CUIS manual: ' . ($resultadoCufd['mensaje'] ?? 'sin detalle'));
                            $message .= " Error al obtener CUFD diario: " . ($resultadoCufd['mensaje'] ?? 'sin detalle') . " Intente renovar en Ajustes POS.";
                        }
                    } catch (\Throwable $th) {
                        Log::error('[store PuntoVenta] Error al obtener CUFD con CUIS manual: ' . $th->getMessage());
                        $message .= " Error al obtener CUFD diario, intente renovar en Ajustes POS.";
                    }
                    return redirect('punto_venta')->with('message', 'Punto de Venta creado correctamente! ' . $message);
                } else {
                    // Intenta obtener automáticamente
                    Log::info('[store PuntoVenta] Obteniendo CUIS automáticamente', [
                        'codigo_punto_venta' => $result['data']['CODIGO_PUNTO_VENTA'],
                        'sucursal'           => $data['sucursal'],
                    ]);
                    $data_cuis = $this->obtenerCuis($result['data']['CODIGO_PUNTO_VENTA'], $data['sucursal']);
                    if ($data_cuis['status'] == true) {
                        Log::info('[store PuntoVenta] CUIS automático obtenido OK', [
                            'cuis'           => $data_cuis['data']['CUIS'] ?? '?',
                            'fecha_vigencia' => $data_cuis['data']['FECHA_VIGENCIA_CUIS'] ?? '?',
                        ]);
                        $data['codigo_cuis'] = $data_cuis['data']['CUIS'];
                        $data['fecha_vigencia_cuis'] = $data_cuis['data']['FECHA_VIGENCIA_CUIS'];
                        $puntoVenta = SiatPuntoVenta::create($data);
                        $message = "";
                        try {
                            $resultadoCufd = $this->renovarVigenciaxPuntoVenta($puntoVenta);
                            if ($resultadoCufd['status'] == true) {
                                $message = "CUFD Obtenido.";
                            } else {
                                Log::error('[store PuntoVenta] Error al obtener CUFD: ' . ($resultadoCufd['mensaje'] ?? 'sin detalle'));
                                $message = "Error al obtener CUFD diario: " . ($resultadoCufd['mensaje'] ?? 'sin detalle') . " Por favor intente renovar en Ajustes POS.";
                            }
                        } catch (\Throwable $th) {
                            Log::error('[store PuntoVenta] Error al obtener CUFD: ' . $th->getMessage());
                            $message = "Error al obtener CUFD diario, por favor intente renovar en Ajustes POS.";
                        }
                        return redirect('punto_venta')->with('message', 'Punto de Venta creado correctamente! ' . $message);
                    } else {
                        Log::error('[store PuntoVenta] Falló la generación automática de CUIS', [
                            'codigo_punto_venta' => $result['data']['CODIGO_PUNTO_VENTA'],
                            'sucursal'           => $data['sucursal'],
                            'error'              => $data_cuis['mensaje'] ?? 'sin detalle',
                        ]);
                        // Fallback: guardar con CUIS pendiente para no bloquear la creación
                        $data['codigo_cuis'] = 'CUIS-PENDIENTE';
                        $data['fecha_vigencia_cuis'] = date('Y-m-d H:i:s');
                        SiatPuntoVenta::create($data);
                        return redirect('punto_venta')->with('message', 'Punto de Venta registrado en SIAT, pero falló la generación automática de CUIS. Se guardó con CUIS pendiente. Intente renovarlo o ingresarlo manualmente. Error SIAT: ' . ($data_cuis['mensaje'] ?? 'desconocido'));
                    }
                }
            } else {
                Log::error('[store PuntoVenta] Falló el registro en SIAT', ['mensaje' => $result['mensaje'] ?? 'sin detalle']);
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
        $data['sucursal'] = $this->resolveSucursalCode($data['sucursal'] ?? null);
        if ($data['sucursal'] === null) {
            return redirect('punto_venta')->with('not_permitted', 'Sucursal invalida. Seleccione una sucursal valida.');
        }

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
        Log::info('[renovarCuis] Inicio', [
            'id'           => $id,
            'idPuntoVenta' => $idPuntoVenta,
            'idSucursal'   => $idSucursal,
        ]);

        $update_data = SiatPuntoVenta::find($id);
        if (!$update_data) {
            Log::warning('[renovarCuis] Punto de venta no encontrado en BD', ['id' => $id]);
            return array("status" => false, "mensaje" => "No se encuentra registrado el punto de venta en el POS");
        }

        $data_cuis = $this->obtenerCuis($idPuntoVenta, $idSucursal);

        if ($data_cuis['status'] == true) {
            Log::info('[renovarCuis] CUIS obtenido, guardando en BD', [
                'cuis'           => $data_cuis['data']['CUIS'] ?? '?',
                'fecha_vigencia' => $data_cuis['data']['FECHA_VIGENCIA_CUIS'] ?? '?',
            ]);
            $update_data->codigo_cuis = $data_cuis['data']['CUIS'];
            $update_data->fecha_vigencia_cuis = $data_cuis['data']['FECHA_VIGENCIA_CUIS'];
            $update_data->update();
            Log::info('[renovarCuis] BD actualizada correctamente', ['id' => $id]);

            if (isset($data_cuis['data']['MENSAJES'])) {
                $descripcion = "Mensajes: ";
                foreach ($data_cuis['data']['MENSAJES'] as $mensaje) {
                    $descripcion .= " Código: " . $mensaje['codigo'] . " - Descripción: " . $mensaje['descripcion'];
                }
                $respuesta = array("status" => true, "mensaje" => $descripcion);
            } else {
                $respuesta = array("status" => true, "mensaje" => "Se renovó y actualizó el CUIS y Fecha Vigencia con éxito");
            }
        } else {
            Log::error('[renovarCuis] Falló la obtención de CUIS', [
                'id'          => $id,
                'puntoVenta'  => $idPuntoVenta,
                'sucursal'    => $idSucursal,
                'mensaje_api' => $data_cuis['mensaje'] ?? 'sin detalle',
            ]);
            // Corrección: $data_cuis['mensaje'] directo, no $data_cuis['data']['mensaje']
            $respuesta = array("status" => false, "mensaje" => $data_cuis['mensaje'] ?? 'Error desconocido al obtener CUIS');
        }

        return $respuesta;
    }

    public function renovacionMasivaCuis()
    {
        $list_puntosVentas = SiatPuntoVenta::where([['is_siat', true], ['is_active', true]])->get();
        Log::info('[renovacionMasivaCuis] Inicio', ['total_puntos_venta' => $list_puntosVentas->count()]);

        $status = false;
        $mensaje = "";
        $exitosos = 0;
        $fallidos = 0;

        foreach ($list_puntosVentas as $punto_venta) {
            Log::info('[renovacionMasivaCuis] Procesando punto de venta', [
                'id'                  => $punto_venta->id,
                'codigo_punto_venta'  => $punto_venta->codigo_punto_venta,
                'sucursal'            => $punto_venta->sucursal,
                'cuis_actual'         => $punto_venta->codigo_cuis ?? 'sin CUIS',
            ]);

            $data_cuis = $this->obtenerCuis($punto_venta->codigo_punto_venta, $punto_venta->sucursal);

            if ($data_cuis['status'] == true) {
                Log::info('[renovacionMasivaCuis] CUIS obtenido OK, actualizando BD', [
                    'id'             => $punto_venta->id,
                    'nuevo_cuis'     => $data_cuis['data']['CUIS'] ?? '?',
                    'fecha_vigencia' => $data_cuis['data']['FECHA_VIGENCIA_CUIS'] ?? '?',
                ]);
                $punto_venta->codigo_cuis = $data_cuis['data']['CUIS'];
                $punto_venta->fecha_vigencia_cuis = $data_cuis['data']['FECHA_VIGENCIA_CUIS'];
                $punto_venta->update();
                $exitosos++;
                if (isset($data_cuis['data']['MENSAJES'])) {
                    $descripcion = "Mensajes: ";
                    foreach ($data_cuis['data']['MENSAJES'] as $code) {
                        $descripcion .= " Código: " . $code['codigo'] . " - Descripción: " . $code['descripcion'];
                    }
                    $status = true;
                    $mensaje .= $descripcion . " | ";
                } else {
                    $status = true;
                    $mensaje .= "PV {$punto_venta->codigo_punto_venta} actualizado. | ";
                }
            } else {
                $fallidos++;
                $error_msg = $data_cuis['mensaje'] ?? 'Error desconocido';
                Log::error('[renovacionMasivaCuis] Falló obtención de CUIS', [
                    'id'                 => $punto_venta->id,
                    'codigo_punto_venta' => $punto_venta->codigo_punto_venta,
                    'sucursal'           => $punto_venta->sucursal,
                    'error'              => $error_msg,
                ]);
                $status = false;
                $mensaje .= "Error PV {$punto_venta->codigo_punto_venta}: {$error_msg} | ";
            }
        }

        Log::info('[renovacionMasivaCuis] Fin', [
            'exitosos' => $exitosos,
            'fallidos' => $fallidos,
            'status'   => $status,
        ]);

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

    public function sincronizarDesdeSiat()
    {
        $pos_setting = \App\PosSetting::latest()->first();
        if (!$pos_setting || !$pos_setting->nit_emisor) {
            return redirect('punto_venta')->with('not_permitted', 'El NIT del emisor no está configurado en los Ajustes POS.');
        }

        $bearer = 'Bearer ' . Session::get('token_siat');
        if (!Session::get('token_siat')) {
            $this->getToken();
            $bearer = 'Bearer ' . Session::get('token_siat');
        }

        $host = $pos_setting->url_operaciones;
        $path = '/operaciones/lista.punto.venta';

        $sucursales = SiatSucursal::where('estado', true)->get();
        $total_importados = 0;
        $total_actualizados = 0;

        foreach ($sucursales as $sucursal) {
            // Obtenemos el CUIS directamente de la API para el punto de venta 0
            $data_cuis = $this->obtenerCuis(0, $sucursal->sucursal);
            if ($data_cuis['status'] == true) {
                $cuis = $data_cuis['data']['CUIS'];
                $punto_venta_cero = SiatPuntoVenta::where('sucursal', $sucursal->sucursal)
                    ->where('codigo_punto_venta', 0)
                    ->first();
                if (!$punto_venta_cero) {
                    SiatPuntoVenta::create([
                        'codigo_punto_venta' => 0,
                        'nombre_punto_venta' => 'Punto de Venta Principal',
                        'descripcion' => 'Punto de Venta Principal Casa Matriz',
                        'tipo_punto_venta' => 1,
                        'codigo_cuis' => $cuis,
                        'fecha_vigencia_cuis' => $data_cuis['data']['FECHA_VIGENCIA_CUIS'],
                        'sucursal' => $sucursal->sucursal,
                        'is_siat' => true,
                        'is_active' => true,
                        'usuario_alta' => Auth::user()->id,
                    ]);
                } else {
                    $punto_venta_cero->codigo_cuis = $cuis;
                    $punto_venta_cero->fecha_vigencia_cuis = $data_cuis['data']['FECHA_VIGENCIA_CUIS'];
                    $punto_venta_cero->save();
                }
            } else {
                Log::warning("No se pudo obtener CUIS desde la API para la sucursal {$sucursal->sucursal} al sincronizar.");
                continue;
            }

            $query = '?nit=' . $pos_setting->nit_emisor . '&sucursal=' . $sucursal->sucursal . '&cuis=' . $cuis;
            $url = $host . $path . $query;

            try {
                $response = Http::withHeaders([
                    'Authorization' => $bearer,
                ])->get($url);

                if ($response->successful()) {
                    $res_data = $response->json();
                    $puntos_venta = [];
                    if (isset($res_data['DATOS'])) {
                        $puntos_venta = $res_data['DATOS'];
                    } elseif (isset($res_data['listaPuntosVentas'])) {
                        $puntos_venta = $res_data['listaPuntosVentas'];
                    } elseif (is_array($res_data)) {
                        $puntos_venta = $res_data;
                    }

                    if (is_array($puntos_venta)) {
                        $codigos_siat = [];
                        foreach ($puntos_venta as $pv) {
                            $codigo = $pv['codigoPuntoVenta'] ?? $pv['codigo_punto_venta'] ?? null;
                            if ($codigo === null) continue;
                            $codigos_siat[] = (int) $codigo;

                            $nombre = $pv['nombrePuntoVenta'] ?? $pv['nombre_punto_venta'] ?? 'Punto de Venta ' . $codigo;
                            $tipo = $pv['codigoTipoPuntoVenta'] ?? $pv['tipo_punto_venta'] ?? 1;
                            $descripcion = $pv['descripcionPuntoVenta'] ?? $pv['descripcion'] ?? '';

                            $local_pv = SiatPuntoVenta::where('sucursal', $sucursal->sucursal)
                                ->where('codigo_punto_venta', $codigo)
                                ->first();

                            if ($local_pv) {
                                $local_pv->nombre_punto_venta = $nombre;
                                $local_pv->tipo_punto_venta = $tipo;
                                $local_pv->descripcion = $descripcion;
                                $local_pv->is_active = true;
                                $local_pv->is_siat = true;
                                $local_pv->save();
                                $total_actualizados++;
                            } else {
                                SiatPuntoVenta::create([
                                    'codigo_punto_venta' => $codigo,
                                    'nombre_punto_venta' => $nombre,
                                    'descripcion' => $descripcion,
                                    'tipo_punto_venta' => $tipo,
                                    'sucursal' => $sucursal->sucursal,
                                    'codigo_cuis' => 'CUIS-PENDIENTE',
                                    'fecha_vigencia_cuis' => date('Y-m-d H:i:s'),
                                    'is_siat' => true,
                                    'is_active' => true,
                                    'usuario_alta' => Auth::user()->id,
                                ]);
                                $total_importados++;
                            }
                        }

                        // Desactivar localmente los puntos de venta con is_siat = true que ya no estén registrados en SIAT
                        $locales_a_desactivar = SiatPuntoVenta::where('sucursal', $sucursal->sucursal)
                            ->where('is_siat', true)
                            ->whereNotIn('codigo_punto_venta', $codigos_siat)
                            ->get();

                        foreach ($locales_a_desactivar as $pv_des) {
                            $pv_des->is_active = false;
                            $pv_des->save();
                        }
                    }
                } else {
                    Log::error("Error al obtener puntos de venta de SIAT sucursal {$sucursal->sucursal}. Status: " . $response->status() . " Body: " . $response->body());
                }
            } catch (\Throwable $e) {
                Log::error("Excepción al sincronizar puntos de venta de SIAT sucursal {$sucursal->sucursal}: " . $e->getMessage());
            }
        }

        return redirect('punto_venta')->with('message', "Sincronización de puntos de venta SIAT completada con éxito. Importados: {$total_importados}, Actualizados: {$total_actualizados}.");
    }
}