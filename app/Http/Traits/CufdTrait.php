<?php

namespace App\Http\Traits;

use App\PosSetting;
use App\SiatCufd;
use App\SiatPuntoVenta;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

use Log;

trait CufdTrait
{

    protected function writeCufdDebug(string $event, array $context = []): void
    {
        try {
            $line = sprintf(
                "[%s] %s %s%s",
                date('Y-m-d H:i:s'),
                $event,
                json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                PHP_EOL
            );
            file_put_contents(storage_path('logs/pos_siat_debug.log'), $line, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {
            // No bloquear flujo de negocio por fallas de logging.
        }
    }

    public function verificarCufd()
    {
        //listar todos los puntos de venta
        $items = SiatPuntoVenta::get();

        //iterar todos los puntos de ventas.
        foreach ($items as $item) {
            if ($item->codigo_cuis) {
                if ($this->estaVigenteCUFD($item)) { //verifica si está vigente boolean
                    //no hace nada
                } else {
                    $this->renovarCUFD($item);
                }
            }
        }
    }

    public function renovarCUFD($p_venta)
    {
        if ($p_venta == null) {
            return;
        }
        $response = $this->getResponseCufd($p_venta->sucursal, $p_venta->codigo_punto_venta, $p_venta->codigo_cuis);
        if ($response == null) {
            return;
        }
        $item = $response['DATOS'];
        //Variables 
        Log::info('═══════════════════════════════════════════════════════════');
        Log::info('RENOVACIÓN DE CUFD - Respuesta de SIAT:');
        Log::info(json_encode($item));
        Log::info('═══════════════════════════════════════════════════════════');

        $fecha_registro = new Carbon($item['fechaVigencia']);
        $fecha_registro->subDay();

        $fecha_vigencia = new Carbon($fecha_registro);
        $fecha_vigencia->endOfDay();
        
        // PASO 1: DESACTIVAR TODOS LOS CUFDs ANTERIORES de este punto de venta
        Log::info('PASO 1: Desactivando CUFDs antiguos...');
        $cufds_antiguos = SiatCufd::where('sucursal', $p_venta->sucursal)
            ->where('codigo_punto_venta', $p_venta->codigo_punto_venta)
            ->where('estado', true)
            ->get();
        
        $count_desactivados = 0;
        foreach ($cufds_antiguos as $cufd_antiguo) {
            Log::info('  Desactivando CUFD ID=' . $cufd_antiguo->id . ' Control=...' . substr($cufd_antiguo->codigo_control, -15));
            $cufd_antiguo->estado = false;
            $cufd_antiguo->save();
            $count_desactivados++;
        }
        Log::info('  ✓ Total CUFDs desactivados: ' . $count_desactivados);
        
        // PASO 2: GUARDAR EL NUEVO CUFD
        Log::info('PASO 2: Guardando nuevo CUFD...');
        $obj = new SiatCufd();

        $obj->codigo_cufd = $item['codigo'];
        $obj->codigo_control = $item['codigoControl'];
        $obj->direccion = $item['direccion'];
        $obj->fecha_registro = $fecha_registro;
        $obj->fecha_vigencia = $item['fechaVigencia'];

        $obj->sucursal = $p_venta->sucursal;
        $obj->codigo_punto_venta = $p_venta->codigo_punto_venta;
        $obj->estado = true;
        $obj->usuario_alta = 1;
        $obj->save();
        
        Log::info('  ✓ Nuevo CUFD guardado con ID=' . $obj->id);
        Log::info('  CUFD: ' . substr($obj->codigo_cufd, 0, 50) . '...');
        Log::info('  Control: ...' . substr($obj->codigo_control, -15));
        Log::info('  Vigencia: ' . $obj->fecha_vigencia);
        
        // PASO 3: LIMPIAR CACHÉS
        Log::info('PASO 3: Limpiando cachés...');
        try {
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            Log::info('  ✓ Cachés limpiados exitosamente');
        } catch (\Exception $e) {
            Log::warning('  ⚠ No se pudo limpiar caché automáticamente: ' . $e->getMessage());
        }
        
        Log::info('═══════════════════════════════════════════════════════════');
        Log::info('✓ RENOVACIÓN DE CUFD COMPLETADA EXITOSAMENTE');
        Log::info('═══════════════════════════════════════════════════════════');

        return;
    }

    public function estaVigenteCUFD($p_venta)
    {
        $registro = SiatCufd::where('sucursal', $p_venta->sucursal)->where('codigo_punto_venta', $p_venta->codigo_punto_venta)->where('estado', true)->first();
        // en caso 0, no exista, retorna false
        if ($registro == null) {
            return false;
        }

        $fecha_actual = Carbon::now();
        // comparación de la fecha
        if ($registro->fecha_vigencia > $fecha_actual) {
            // todo correcto
            return true;
        } else { // desfase de la vigencia, la fecha actual es superior a la fecha de la vigencia del CUFD.

            // Si el punto de venta no se encuentra en modo contingencia, el estado cambia y se renueva el CUFD.
            if ($p_venta->modo_contingencia == false) {
                //al no estar vigente, el estado pasa a false.
                $cufd_update = SiatCufd::find($registro->id);
                $cufd_update->estado = false;
                $cufd_update->save();
                return false;
            } else {
                // el punto de venta está modo contingencia, por tanto, se mantiene su CUFD sin renovar.
                return true;
            }
        }
    }

    // retorna el response del endpoint
    public function getResponseCufd($sucursal_id, $p_venta, $cuis)
    {
        //http://66.94.100.10:5014/obtencion.codigos/cufd?codigoPuntoVenta=0&cuis=FD520714&nit=388615026&sucursal=0
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_operaciones;
        $path = '/obtencion.codigos/cufd';
        $punto_venta = '?codigoPuntoVenta=' . $p_venta;
        $codigo_cuis = '&cuis=' . $cuis;
        $nit_emisor = '&nit=' . $pos_setting->nit_emisor;
        $sucursal = '&sucursal=' . $sucursal_id;
        $query = $punto_venta . $codigo_cuis . $nit_emisor . $sucursal;

        try {
            $response = Http::withHeaders([
                'Authorization' => $bearer,
            ])->post($host . $path . $query);
        } catch (\Throwable $th) {
            $msj = 'Problemas de conexión Siat o Internet';
            Session::flash('warning', $msj);
            return;
        }
        //entre 200 y 299
        if ($response->successful()) {
            $status = $response->json();
            return $status;
        }
        //error >500
        if ($response->serverError()) {
            $msj = 'Problemas de conexión Siat o Internet';
            Session::flash('warning', $msj);
            return;
        }
        //error >400
        if ($response->clientError()) {
            $msj = 'Error | Credenciales inválidas para Punto Venta: ' . $p_venta;
            Session::flash('warning', $msj);
            return;
        }
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////////
    // función/tarea para renovar los CUFD de los puntos de venta
    public function tareaRenovarCufd()
    {
        $items = SiatPuntoVenta::where("is_siat", true)->where("is_active", true)->get();

        //iterar todos los puntos de ventas.
        foreach ($items as $item) {
            if ($item->codigo_cuis) {
                if ($this->estaVigenteCUFD($item)) { //verifica si está vigente boolean
                    Log::info("CUFD esta vigente para PV: ".$item->codigo_punto_venta);
                } else {
                    $this->taskRenovarCUFD($item);
                }
            }
        }
    }

    public function taskRenovarCUFD($p_venta)
    {
        if ($p_venta == null) {
            $this->writeCufdDebug('CUFD taskRenovarCUFD: punto de venta nulo', []);
            return;
        }

        $this->writeCufdDebug('CUFD taskRenovarCUFD: inicio', [
            'sucursal' => $p_venta->sucursal,
            'codigo_punto_venta' => $p_venta->codigo_punto_venta,
            'codigo_cuis' => $p_venta->codigo_cuis,
        ]);

        $response = $this->getResponseCufdTask($p_venta->sucursal, $p_venta->codigo_punto_venta, $p_venta->codigo_cuis);
        if ($response == null) {
            $this->writeCufdDebug('CUFD taskRenovarCUFD: respuesta nula', [
                'sucursal' => $p_venta->sucursal,
                'codigo_punto_venta' => $p_venta->codigo_punto_venta,
            ]);
            return;
        }
        $item = $response['DATOS'];
        Log::info(json_encode($item));
        $fecha_registro = new Carbon($item['fechaVigencia']);
        $fecha_registro->subDay();

        $fecha_vigencia = new Carbon($item['fechaVigencia']);
        // proceso de guardar registro de cufd por punto de venta.
        $obj = new SiatCufd();

        $obj->codigo_cufd = $item['codigo'];
        $obj->codigo_control = $item['codigoControl'];
        $obj->direccion = $item['direccion'];
        $obj->fecha_registro = $fecha_registro;
        $obj->fecha_vigencia = $fecha_vigencia;

        $obj->sucursal = $p_venta->sucursal;
        $obj->codigo_punto_venta = $p_venta->codigo_punto_venta;
        $obj->estado = true;
        $obj->save();

        $this->writeCufdDebug('CUFD taskRenovarCUFD: renovacion exitosa', [
            'sucursal' => $p_venta->sucursal,
            'codigo_punto_venta' => $p_venta->codigo_punto_venta,
            'siat_cufd_id' => $obj->id,
            'fecha_vigencia' => $obj->fecha_vigencia,
        ]);

        log::info('Renovación CUFD exitosa para el Punto de Venta => ' . $p_venta->codigo_punto_venta . ' de la Sucursal => ' . $p_venta->sucursal);
        return;
    }

    // retorna el response para Tarea Programada
    public function getResponseCufdTask($sucursal_id, $p_venta, $cuis)
    {
        $token_siat = $this->getTokenTask();
        if ($token_siat == null) {
            log::warning('Error: no ha obtenido el token   | archivo CufdTrait, operación_getResponseCufdTask');
            return;
        }
        //http://66.94.100.10:5014/obtencion.codigos/cufd?codigoPuntoVenta=0&cuis=FD520714&nit=388615026&sucursal=0
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . $token_siat;
        $host = $pos_setting->url_operaciones;
        $path = '/obtencion.codigos/cufd';
        $punto_venta = '?codigoPuntoVenta=' . $p_venta;
        $codigo_cuis = '&cuis=' . $cuis;
        $nit_emisor = '&nit=' . $pos_setting->nit_emisor;
        $sucursal = '&sucursal=' . $sucursal_id;
        $query = $punto_venta . $codigo_cuis . $nit_emisor . $sucursal;
        $url = $host . $path . $query;

        $this->writeCufdDebug('CUFD getResponseCufdTask: request SIAT', [
            'endpoint' => $path,
            'url' => $url,
            'sucursal' => $sucursal_id,
            'codigo_punto_venta' => $p_venta,
        ]);
        try {
            $response = Http::withHeaders([
                'Authorization' => $bearer,
            ])->post($url);
        } catch (\Throwable $th) {
            $msj = 'Problemas de conexión Siat o Internet';
            Session::flash('warning', $msj);
            $this->writeCufdDebug('CUFD getResponseCufdTask: excepcion de conexion', [
                'url' => $url,
                'message' => $th->getMessage(),
            ]);
            return;
        }
        // entre 200 y 299
        if ($response->successful()) {
            $status = $response->json();
            $this->writeCufdDebug('CUFD getResponseCufdTask: respuesta exitosa', [
                'url' => $url,
                'status_http' => $response->status(),
                'body' => $status,
            ]);
            return $status;
        }

        // error > 500
        if ($response->serverError()) {
            log::warning('Error: serverError, archivo CufdTrait, operación_getResponseCufdTask => ');
            log::warning(json_encode($response->json()));
            $this->writeCufdDebug('CUFD getResponseCufdTask: serverError', [
                'url' => $url,
                'status_http' => $response->status(),
                'body' => $response->json(),
            ]);
            return;
        }
        // error > 400
        if ($response->clientError()) {
            log::warning('Error: clientError, archivo CufdTrait, operación_getResponseCufdTask => ');
            log::warning(json_encode($response->json()));
            log::warning($url);
            $this->writeCufdDebug('CUFD getResponseCufdTask: clientError', [
                'url' => $url,
                'status_http' => $response->status(),
                'body' => $response->json(),
            ]);
            $respuesta = $response->json();
            if (isset($respuesta['mensajes'])) {
                $msj = 'Error 400: problema en servicios causa: ';
            } else {
                $msj = 'Error 400: problema en servicios causa, contacte con soporte';
            }
            Session::flash('warning', $msj);
            return;
        }
    }

    // obtener token para Tarea Programada
    public function getTokenTask()
    {
        $pos_setting = PosSetting::latest()->first();
        $user_siat = $pos_setting->user_siat;
        $pass_siat = $pos_setting->pass_siat;
        $url_siat = $pos_setting->url_siat;

        if ($user_siat && $pass_siat && $url_siat) {

            $response = null;
            try {
                $response = Http::post($url_siat . '/TokenRest/v1/token', [
                    'dataPassword' => $pass_siat,
                    'dataUser' => $user_siat,
                ]);
            } catch (\Throwable $th) {
                log::warning('Error: sercURL error 28: Failed to connect - Timed out   | archivoCufdTrait, operación_getTokenTask');
            }

            if ($response == null) {
                return;
            }
            Session::put('auth_siat', true);
            //entre 200 y 299
            if ($response->successful()) {
                $token_siat = $response->json();
                return $token_siat['token'];
            }

            // error > 500
            if ($response->serverError()) {
                log::warning('Error: serverError, archivo CufdTrait, operación_getTokenTask => ');
                log::warning(json_encode($response->json()));
                return;
            }

            // error > 400
            if ($response->clientError()) {
                log::warning('Error: clientError, archivo CufdTrait, operación_getTokenTask => ');
                log::warning(json_encode($response->json()));
                return;
            }
        } else {
            Session::put('auth_siat', false);
        }
    }

    ///////////////////////////////////////
    // función/tarea para forzar a cambiar los estados y renovar todos los puntos de venta
    public function forceRenovarCUFD()
    {
        $items = SiatPuntoVenta::where("is_siat", true)->where("is_active", true)->get();
        $bandera = false;

        $this->writeCufdDebug('CUFD forceRenovarCUFD: inicio', [
            'total_puntos' => $items->count(),
        ]);

        try {
            //iterar todos los puntos de ventas.
            foreach ($items as $item) {
                if ($item->codigo_cuis) {
                    $this->writeCufdDebug('CUFD forceRenovarCUFD: procesando punto', [
                        'sucursal' => $item->sucursal,
                        'codigo_punto_venta' => $item->codigo_punto_venta,
                        'codigo_cuis' => $item->codigo_cuis,
                    ]);
                    $this->desactivarRegistroCUFD($item);
                }
            }
            $this->writeCufdDebug('CUFD forceRenovarCUFD: fin exitoso', [
                'total_puntos' => $items->count(),
            ]);
            return $bandera = true;
        } catch (\Throwable $th) {
            $this->writeCufdDebug('CUFD forceRenovarCUFD: excepcion', [
                'message' => $th->getMessage(),
            ]);
            return $bandera;
        }
    }

    public function desactivarRegistroCUFD($p_venta)
    {
        $activos_antes = SiatCufd::where('sucursal', $p_venta->sucursal)
            ->where('codigo_punto_venta', $p_venta->codigo_punto_venta)
            ->where('estado', true)
            ->count();

        // obtener sólo los estado = true, y por each a false
        $registro = SiatCufd::where('sucursal', $p_venta->sucursal)->where('codigo_punto_venta', $p_venta->codigo_punto_venta)->where('estado', true)->get()->each->updateEstado();

        $this->writeCufdDebug('CUFD desactivarRegistroCUFD: estados desactivados', [
            'sucursal' => $p_venta->sucursal,
            'codigo_punto_venta' => $p_venta->codigo_punto_venta,
            'activos_antes' => $activos_antes,
        ]);

        // se procede a renovar el punto de venta
        $this->taskRenovarCUFD($p_venta);
    }

    // función para el caso que la hora actual sea superior a las 23:30, y se renueva el CUFD para el día siguiente.
    // para el caso
    public function renovarVigenciaxPuntoVenta($p_venta)
    {
        if ($p_venta == null) {
            $this->writeCufdDebug('CUFD renovarVigenciaxPuntoVenta: punto de venta nulo', []);
            return;
        }

        $this->writeCufdDebug('CUFD renovarVigenciaxPuntoVenta: inicio', [
            'sucursal' => $p_venta->sucursal,
            'codigo_punto_venta' => $p_venta->codigo_punto_venta,
            'codigo_cuis' => $p_venta->codigo_cuis,
        ]);

        $response = $this->getResponseCufdTask($p_venta->sucursal, $p_venta->codigo_punto_venta, $p_venta->codigo_cuis);
        if ($response == null) {
            $this->writeCufdDebug('CUFD renovarVigenciaxPuntoVenta: respuesta nula', [
                'sucursal' => $p_venta->sucursal,
                'codigo_punto_venta' => $p_venta->codigo_punto_venta,
            ]);
            return;
        }
        $item = $response['DATOS'];
        Log::info(json_encode($item));
        $fecha_registro = new Carbon($item['fechaVigencia']);
        $fecha_registro->subDay();

        $fecha_vigencia = new Carbon($item['fechaVigencia']);
        // proceso de guardar registro de cufd por punto de venta.
        $obj = new SiatCufd();

        $obj->codigo_cufd = $item['codigo'];
        $obj->codigo_control = $item['codigoControl'];
        $obj->direccion = $item['direccion'];
        $obj->fecha_registro = $fecha_registro;
        $obj->fecha_vigencia = $fecha_vigencia;

        $obj->sucursal = $p_venta->sucursal;
        $obj->codigo_punto_venta = $p_venta->codigo_punto_venta;
        $obj->estado = true;
        $obj->save();

        $this->writeCufdDebug('CUFD renovarVigenciaxPuntoVenta: renovacion exitosa', [
            'sucursal' => $p_venta->sucursal,
            'codigo_punto_venta' => $p_venta->codigo_punto_venta,
            'siat_cufd_id' => $obj->id,
            'fecha_vigencia' => $obj->fecha_vigencia,
        ]);

        log::info('operación renovarVigenciaxPuntoVenta, renovación CUFD exitosa para el Punto de Venta => ' . $p_venta->codigo_punto_venta . ' de la Sucursal => ' . $p_venta->sucursal);
        return;
    }

    public function obtenerCuis(int $puntoVenta, int $sucursalId = null)
    {
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_operaciones;
        $path = '/obtencion.codigos/cuis';
        try {
            $codigoPuntoVenta = '?codigoPuntoVenta=' . $puntoVenta;
            $nit = '&nit=' . $pos_setting->nit_emisor;
            $sucursal = '&sucursal=' . $sucursalId;
            $query = $codigoPuntoVenta . $nit . $sucursal;

            $response = Http::withHeaders([
                'Authorization' => $bearer,
            ])->post($host . $path . $query);
            log::info("url => " . $host . $path . $query);
            //entre 200 y 299
            if ($response->successful()) {
                $data = $response->json();
                $respuesta = array('data' => $data, 'status' => true);
                log::info("respuesta => " . json_encode($data));
            } else {
                $error = $response->json();
                log::info("respuesta => " . json_encode($error));
                $titulo_error = $error['status'];
                if ($titulo_error == 500) {
                    $respuesta = array('mensaje' => 'Error interno del servidor. ', 'status' => false);
                } elseif ($titulo_error == 400) {
                    if (isset($error['mensajes'])) {
                        $mensajes_error = $error['mensajes'];
                        log::warning("mensajes de Error => " . json_encode($mensajes_error));
                        $descripcion = "";
                        foreach ($mensajes_error as $mensaje) {
                            $descripcion .= " Código: " . $mensaje['codigo'] . " - Descripción: " . $mensaje['descripcion'];
                            log::info($descripcion);
                        }
                        $msj = 'Nose pudo obtener el CUIS. Error: ' . $descripcion;
                    } else {
                        $mensajes_error = $error['title'];
                        log::warning("mensajes de Error => " . json_encode($mensajes_error));
                        $msj = 'Nose pudo obtener el CUIS. Error: ' . $titulo_error . " - " . $mensajes_error;
                    }
                    $respuesta = array('mensaje' => $msj, 'status' => false);
                }
            }
        } catch (\Throwable $th) {
            $msj = 'Problemas de conexión Siat o Error: ' . $th->getMessage();
            $respuesta = array('mensaje' => $msj, 'status' => false);
            return $respuesta;
        }
        return $respuesta;
    }

    public function registrarPuntoVenta($puntoVenta)
    {
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_operaciones;
        $path = '/operaciones/registro.punto.venta';

        $data_cuis = $this->obtenerCuis(0, $puntoVenta['sucursal']);
        if ($data_cuis['status']) {
            //Body
            $body = [
                'nombrePuntoVenta' => $puntoVenta['nombre_punto_venta'],
                'descripcionPuntoVenta' => $puntoVenta['descripcion'],
                'codigoPuntoVenta' => 0,
                'sucursal' => $puntoVenta['sucursal'],
                'cuis' => $data_cuis['data']['CUIS'],
                'nit' => $pos_setting->nit_emisor,
                'codigoTipoPuntoVenta' => $puntoVenta['tipo_punto_venta']
            ];
            log::info("url => " . $host . $path);
            log::info("body => " . json_encode($body));
            try {
                $response = Http::withHeaders([
                    'Authorization' => $bearer,
                ])->post($host . $path, $body);
            } catch (\Throwable $th) {
                $msj = 'Problemas de conexión Siat o Internet';
                $respuesta = array('mensaje' => $msj, 'status' => false);
                return $respuesta;
            }
            if ($response->successful()) {
                $data = $response->json();
                $respuesta = array('data' => $data, 'status' => true);
                log::info("respuesta => " . json_encode($data));
            } else {
                $error = $response->json();
                $titulo_error = is_array($error) && isset($error['status']) ? $error['status'] : $response->status();
                if ($titulo_error == 500) {
                    $respuesta = array('mensaje' => 'Error interno del servidor. ', 'status' => false);
                } elseif ($titulo_error == 400 || $titulo_error == 404) {
                    if (is_array($error) && isset($error['mensajes'])) {
                        $mensajes_error = $error['mensajes'];
                        log::warning("mensajes de Error => " . json_encode($mensajes_error));
                        $descripcion = "";
                        foreach ($mensajes_error as $mensaje) {
                            $descripcion .= " Código: " . $mensaje['codigo'] . " - Descripción: " . $mensaje['descripcion'];
                            log::info($descripcion);
                        }
                        $msj = 'El Punto de Venta no se pudo registrar. Error: ' . $descripcion;
                    } elseif (is_array($error) && isset($error['title'])) {
                        $mensajes_error = $error['title'];
                        log::warning("mensajes de Error => " . json_encode($mensajes_error));
                        $msj = 'El Punto de Venta no se pudo registrar. Error: ' . $titulo_error . " - " . $mensajes_error;
                    } else {
                        $msj = 'El Punto de Venta no se pudo registrar. Código HTTP: ' . $titulo_error;
                    }
                    $respuesta = array('mensaje' => $msj, 'status' => false);
                } else {
                    $respuesta = array('mensaje' => 'Error al registrar punto de venta en SIAT. Código HTTP: ' . $titulo_error, 'status' => false);
                }
            }
        } else {
            $respuesta = array('mensaje' => "Punto de Venta Creado, No se pudo obtener Cuis, Actualice sus servicios", 'status' => false);
        }
        return $respuesta;
    }

    public function registrarPuntoVentaComisionista($puntoVenta)
    {
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_operaciones;
        $path = '/operaciones/registro.punto.venta.comisionista';

        $data_cuis = $this->obtenerCuis(0, $puntoVenta['sucursal']);
        if ($data_cuis['status']) {
            $body = [
                'nombrePuntoVenta' => $puntoVenta['nombre_punto_venta'],
                'descripcionPuntoVenta' => $puntoVenta['descripcion'],
                'nitComisionista' => $puntoVenta['nit_comisionista'],
                'codigoPuntoVenta' => 0,
                'sucursal' => $puntoVenta['sucursal'],
                'cuis' => $data_cuis['data']['CUIS'],
                'numeroContrato' => $puntoVenta['numero_contrato'],
                'nit' => $pos_setting->nit_emisor,
                'fechaInicio' => $puntoVenta['fecha_inicio'],
                'fechaFin' => $puntoVenta['fecha_fin']
            ];
            log::info("url => " . $host . $path);
            log::info("body => " . json_encode($body));
            try {
                $response = Http::withHeaders([
                    'Authorization' => $bearer,
                ])->post($host . $path, $body);
                //entre 200 y 299
                if ($response->successful()) {
                    $data = $response->json();
                    log::info("respuesta => " . json_encode($data));
                    $respuesta = array('data' => $data, 'status' => true);
                } else {
                    $error = $response->json();
                    log::error($error);
                    $titulo_error = is_array($error) && isset($error['status']) ? $error['status'] : $response->status();
                    if ($titulo_error == 500) {
                        $respuesta = array('mensaje' => 'Error interno del servidor. ', 'status' => false);
                    } elseif ($titulo_error == 400 || $titulo_error == 404) {
                        if (is_array($error) && isset($error['mensajes'])) {
                            $mensajes_error = $error['mensajes'];
                            log::warning("mensajes de Error => " . json_encode($mensajes_error));
                            $descripcion = "";
                            foreach ($mensajes_error as $mensaje) {
                                $descripcion .= " Código: " . $mensaje['codigo'] . " - Descripción: " . $mensaje['descripcion'];
                                log::info($descripcion);
                            }
                            $msj = 'El Punto de Venta no se pudo registrar. Error: ' . $descripcion;
                        } elseif (is_array($error) && isset($error['title'])) {
                            $mensajes_error = $error['title'];
                            log::warning("mensajes de Error => " . json_encode($mensajes_error));
                            $msj = 'El Punto de Venta no se pudo registrar. Error: ' . $titulo_error . " - " . $mensajes_error;
                        } else {
                            $msj = 'El Punto de Venta no se pudo registrar. Código HTTP: ' . $titulo_error;
                        }
                    } else {
                        $msj = 'El Servicio no esta disponible, contacte con soporte. Código HTTP: ' . $titulo_error;
                    }
                    $respuesta = array('mensaje' => $msj, 'status' => false);
                }
            } catch (\Throwable $th) {
                log::error($th);
                $msj = 'Problemas de conexión Siat o Internet, Error: ' . $th->getMessage();
                $respuesta = array('mensaje' => $msj, 'status' => false);
                return $respuesta;
            }
        } else {
            $respuesta = array('mensaje' => "Punto de Venta Creado, No se pudo obtener Cuis, Actualice sus servicios", 'status' => false);
        }
        return $respuesta;
    }

    public function bajaPuntoVenta($puntoVenta)
    {
        $pos_setting = PosSetting::latest()->first();
        $bearer = 'Bearer ' . Session::get('token_siat');
        $host = $pos_setting->url_operaciones;
        $path = '/operaciones/cierre.operaciones.sistema';

        $data_cuis = $this->obtenerCuis($puntoVenta['codigo_punto_venta'], $puntoVenta['sucursal']);
        if ($data_cuis['status']) {
            $codigoPuntoVenta = '?codigoPuntoVenta=' . $puntoVenta['codigo_punto_venta'];
            $cuis = '&cuis=' . $data_cuis['data']['CUIS'];
            $nit = '&nit=' . $pos_setting->nit_emisor;
            $sucursal = '&sucursal=' . $puntoVenta['sucursal'];

            $query = $codigoPuntoVenta . $cuis . $nit . $sucursal;
            log::info("url => " . $host . $path);
            log::info("query => " . $query);
            try {
                $response = Http::withHeaders([
                    'Authorization' => $bearer,
                ])->post($host . $path . $query);
                //entre 200 y 299
                if ($response->successful()) {
                    $data = $response->json();
                    log::info("respuesta => " . json_encode($data));
                    $respuesta = array('data' => $data, 'status' => true);
                } else {
                    $error = $response->json();
                    log::error($error);
                    $titulo_error = $error['status'];
                    if ($titulo_error == 500) {
                        $respuesta = array('mensaje' => 'Error interno del servidor. ', 'status' => false);
                    } elseif ($titulo_error == 400) {
                        if (isset($error['mensajes'])) {
                            $mensajes_error = $error['mensajes'];
                            log::warning("mensajes de Error => " . json_encode($mensajes_error));
                            $descripcion = "";
                            foreach ($mensajes_error as $mensaje) {
                                $descripcion .= " Código: " . $mensaje['codigo'] . " - Descripción: " . $mensaje['descripcion'];
                                log::info($descripcion);
                            }
                            $msj = 'El Punto de Venta no se pudo dar de baja. Error: ' . $descripcion;
                        } else {
                            $mensajes_error = $error['title'];
                            log::warning("mensajes de Error => " . json_encode($mensajes_error));
                            $msj = 'El Punto de Venta no se pudo dar de baja. Error: ' . $titulo_error . " - " . $mensajes_error;
                        }
                    } elseif ($titulo_error == 404) {
                        $msj = 'El Servicio no esta disponible, contacte con soporte';
                    }
                    $respuesta = array('mensaje' => $msj, 'status' => false);
                }
            } catch (\Throwable $th) {
                log::error($th);
                $msj = 'Problemas de conexión Siat o Internet, Error: ' . $th->getMessage();
                $respuesta = array('mensaje' => $msj, 'status' => false);
                return $respuesta;
            }
        } else {
            $respuesta = array('mensaje' => "Baja de Punto de Venta Cancelado, No se pudo obtener Cuis, Actualice sus servicios", 'status' => false);
        }
        return $respuesta;
    }

    public function getPuntosVentaDesdeSiatApi($sucursal)
    {
        $sucursalCode = null;
        if ($sucursal !== null && $sucursal !== '') {
            if (is_numeric($sucursal)) {
                $sucursalCode = (int) $sucursal;
            } else {
                $textValue = trim((string) $sucursal);
                if ($textValue !== '') {
                    if (strpos($textValue, '|') !== false) {
                        $textValue = trim(explode('|', $textValue)[0]);
                    }
                    if (is_numeric($textValue)) {
                        $sucursalCode = (int) $textValue;
                    } else {
                        $upperValue = strtoupper($textValue);
                        $suc_row = \App\SiatSucursal::whereRaw('UPPER(nombre) = ?', [$upperValue])
                            ->orWhereRaw('UPPER(descripcion_sucursal) = ?', [$upperValue])
                            ->orWhereRaw('UPPER(domicilio_tributario) = ?', [$upperValue])
                            ->first();
                        if ($suc_row) {
                            $sucursalCode = (int) $suc_row->sucursal;
                        }
                    }
                }
            }
        }

        if ($sucursalCode === null) {
            Log::warning("getPuntosVentaDesdeSiatApi: Sucursal invalida: " . var_export($sucursal, true));
            return null;
        }

        $pos_setting = PosSetting::latest()->first();
        if (!$pos_setting || !$pos_setting->nit_emisor || !$pos_setting->url_operaciones) {
            Log::warning("getPuntosVentaDesdeSiatApi: POS settings incomplete.");
            return null;
        }

        $bearer = 'Bearer ' . Session::get('token_siat');
        if (!Session::get('token_siat')) {
            $this->getToken();
            $bearer = 'Bearer ' . Session::get('token_siat');
        }

        $data_cuis = $this->obtenerCuis(0, $sucursalCode);
        if (!$data_cuis || $data_cuis['status'] != true) {
            Log::warning("getPuntosVentaDesdeSiatApi: Falló al obtener CUIS para la sucursal {$sucursalCode}.");
            return null;
        }
        $cuis = $data_cuis['data']['CUIS'];

        $host = $pos_setting->url_operaciones;
        $path = '/operaciones/lista.punto.venta';
        $query = '?nit=' . $pos_setting->nit_emisor . '&sucursal=' . $sucursalCode . '&cuis=' . $cuis;
        $url = $host . $path . $query;

        try {
            $response = Http::withHeaders([
                'Authorization' => $bearer,
            ])->timeout(5)->get($url);

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
                    $resultList = [];
                    foreach ($puntos_venta as $pv) {
                        $codigo = $pv['codigoPuntoVenta'] ?? $pv['codigo_punto_venta'] ?? null;
                        if ($codigo === null) continue;
                        $codigo = (int) $codigo;

                        $nombre = $pv['nombrePuntoVenta'] ?? $pv['nombre_punto_venta'] ?? 'Punto de Venta ' . $codigo;
                        $tipo = $pv['codigoTipoPuntoVenta'] ?? $pv['tipo_punto_venta'] ?? 1;
                        $descripcion = $pv['descripcionPuntoVenta'] ?? $pv['descripcion'] ?? '';

                        $local_pv = SiatPuntoVenta::where('sucursal', $sucursalCode)
                            ->where('codigo_punto_venta', $codigo)
                            ->first();

                        if ($local_pv) {
                            $local_pv->nombre_punto_venta = $nombre;
                            $local_pv->tipo_punto_venta = $tipo;
                            $local_pv->descripcion = $descripcion;
                            $local_pv->is_active = true;
                            $local_pv->is_siat = true;
                            $local_pv->save();
                        } else {
                            $local_pv = SiatPuntoVenta::create([
                                'codigo_punto_venta' => $codigo,
                                'nombre_punto_venta' => $nombre,
                                'descripcion' => $descripcion,
                                'tipo_punto_venta' => $tipo,
                                'sucursal' => $sucursalCode,
                                'codigo_cuis' => 'CUIS-PENDIENTE',
                                'fecha_vigencia_cuis' => date('Y-m-d H:i:s'),
                                'is_siat' => true,
                                'is_active' => true,
                                'usuario_alta' => Auth::user() ? Auth::user()->id : 1,
                            ]);
                        }
                        $resultList[] = $local_pv;
                    }
                    return collect($resultList);
                }
            } else {
                Log::error("getPuntosVentaDesdeSiatApi: API call failed with status: " . $response->status());
            }
        } catch (\Throwable $e) {
            Log::error("getPuntosVentaDesdeSiatApi: Exception calling SIAT list endpoint: " . $e->getMessage());
        }

        return null;
    }
}