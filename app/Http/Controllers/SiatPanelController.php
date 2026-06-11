<?php

namespace App\Http\Controllers;

use App\Biller;
use App\Warehouse;
use App\PosSetting;
use App\SiatSucursal;
use App\SiatPuntoVenta;
use App\SiatLeyendaFactura;
use App\SiatDocumentoSector;
use Illuminate\Http\Request;
use App\SiatParametricaVario;
use App\SiatProductoServicio;
use App\Http\Traits\CufdTrait;
use App\Http\Traits\SiatTrait;
use App\SiatActividadEconomica;
use App\SiatRegistrosSincronizacion;
use Illuminate\Support\Facades\Session;
use Log;
use Auth;


class SiatPanelController extends Controller
{
    use SiatTrait, CufdTrait;

    public function index()
    {
        //obteniendo Billers
        $billers = Biller::where('is_active', true)->get();

        $actividades = SiatActividadEconomica::all();

        return view('siat-panel.index', ['billers' => $billers, 'actividades' => $actividades]);
    }

    public function getActividadById(Biller $id)
    {
        //obteniendo Billers
        $billers = Biller::where('is_active', true)->get();
        $punto_venta = $id->punto_venta_siat;
        $sucursal = $id->almacen->sucursal->sucursal;
        $actividades = SiatActividadEconomica::where('codigo_punto_venta', $punto_venta)
            ->where('sucursal', $sucursal)->get();

        Log::info("SIAT PANEL BUSQUEDA", [
            'funcion' => 'getActividadById',
            'params' => [
                'biller_id' => $id->id,
                'punto_venta' => $punto_venta,
                'sucursal' => $sucursal,
            ],
            'total_resultados' => $actividades->count(),
        ]);

        return view('siat-panel.index', ['billers' => $billers, 'actividades' => $actividades]);
    }

    public function documentosector()
    {
        //obteniendo Billers
        $billers = Biller::where('is_active', true)->get();
        $documentos = SiatDocumentoSector::orderBy('id')->get();

        return view('siat-panel.documento_sector', ['billers' => $billers, 'documentos' => $documentos]);
    }

    public function getDocumentoSectorById(Biller $id)
    {
        //obteniendo Billers
        $billers = Biller::where('is_active', true)->get();
        $punto_venta = $id->punto_venta_siat;
        $sucursal = $id->almacen->sucursal->sucursal;
        $documentos = SiatDocumentoSector::where('codigo_punto_venta', $punto_venta)
            ->where('sucursal', $sucursal)->get();

        Log::info("SIAT PANEL BUSQUEDA", [
            'funcion' => 'getDocumentoSectorById',
            'params' => [
                'biller_id' => $id->id,
                'punto_venta' => $punto_venta,
                'sucursal' => $sucursal,
            ],
            'total_resultados' => $documentos->count(),
        ]);

        return view('siat-panel.documento_sector', ['billers' => $billers, 'documentos' => $documentos]);
    }

    public function parametros()
    {
        //obteniendo Billers
        $billers = Biller::where('is_active', true)->get();

        $parametricas = SiatParametricaVario::orderBy('id')->get();

        return view('siat-panel.parametrica', ['billers' => $billers, 'parametricas' => $parametricas]);
    }

    public function getParametroById(Biller $id)
    {
        //obteniendo Billers
        $billers = Biller::where('is_active', true)->get();
        $punto_venta = $id->punto_venta_siat;
        $sucursal = $id->almacen->sucursal->sucursal;
        $parametricas = SiatParametricaVario::where('codigo_punto_venta', $punto_venta)
            ->where('sucursal', $sucursal)->get();

        Log::info("SIAT PANEL BUSQUEDA", [
            'funcion' => 'getParametroById',
            'params' => [
                'biller_id' => $id->id,
                'punto_venta' => $punto_venta,
                'sucursal' => $sucursal,
            ],
            'total_resultados' => $parametricas->count(),
        ]);

        return view('siat-panel.parametrica', ['billers' => $billers, 'parametricas' => $parametricas]);
    }




    public function productoservicio()
    {
        //obteniendo Billers
        $billers = Biller::where('is_active', true)->get();
        $productos = SiatProductoServicio::orderBy('descripcion_producto')->get();

        return view('siat-panel.productoservicio', ['billers' => $billers, 'productos' => $productos]);
    }
    public function getProductoServicioById(Biller $id)
    {
        //obteniendo Billers
        $billers = Biller::where('is_active', true)->get();
        $punto_venta = $id->punto_venta_siat;
        $sucursal = $id->almacen->sucursal->sucursal;
        $productos = SiatProductoServicio::where('codigo_punto_venta', $punto_venta)
            ->where('sucursal', $sucursal)->get();

        Log::info("SIAT PANEL BUSQUEDA", [
            'funcion' => 'getProductoServicioById',
            'params' => [
                'biller_id' => $id->id,
                'punto_venta' => $punto_venta,
                'sucursal' => $sucursal,
            ],
            'total_resultados' => $productos->count(),
        ]);

        return view('siat-panel.productoservicio', ['billers' => $billers, 'productos' => $productos]);
    }

    public function leyenda()
    {
        //obteniendo Billers
        $billers = Biller::where('is_active', true)->get();
        $leyendas = SiatLeyendaFactura::orderBy('id')->get();

        return view('siat-panel.leyenda', ['billers' => $billers, 'leyendas' => $leyendas]);
    }

    public function getLeyendaById(Biller $id)
    {
        //obteniendo Billers
        $billers = Biller::where('is_active', true)->get();
        $punto_venta = $id->punto_venta_siat;
        $sucursal = $id->almacen->sucursal->sucursal;
        $leyendas = SiatLeyendaFactura::where('codigo_punto_venta', $punto_venta)
            ->where('sucursal', $sucursal)->get();

        Log::info("SIAT PANEL BUSQUEDA", [
            'funcion' => 'getLeyendaById',
            'params' => [
                'biller_id' => $id->id,
                'punto_venta' => $punto_venta,
                'sucursal' => $sucursal,
            ],
            'total_resultados' => $leyendas->count(),
        ]);

        return view('siat-panel.leyenda', ['billers' => $billers, 'leyendas' => $leyendas]);
    }

    public function logSiat()
    {
        $nit = PosSetting::latest()->first()->pluck('nit_emisor');
        //obteniendo Sucursales
        $sucursales = SiatSucursal::where('estado', true)->get();
        $datos = array();
        return view('siat-panel.log_siat', ['sucursales' => $sucursales, 'datos' => $datos, 'nit' => $nit]);
    }

    public function listaOperaciones()
    {
        $listaOperaciones = array();
        $listaOperaciones[0]['id'] = 1;
        $listaOperaciones[0]['descripcion'] = "Actividades Económicas";
        $listaOperaciones[0]['operacion'] = "actividades";
        $listaOperaciones[1]['id'] = 2;
        $listaOperaciones[1]['descripcion'] = "Documento Sector";
        $listaOperaciones[1]['operacion'] = "actividadesDocumentoSector";
        $listaOperaciones[2]['id'] = 3;
        $listaOperaciones[2]['descripcion'] = "Productos Servicios";
        $listaOperaciones[2]['operacion'] = "productosServicios";
        $listaOperaciones[3]['id'] = 4;
        $listaOperaciones[3]['descripcion'] = "Leyendas Facturas";
        $listaOperaciones[3]['operacion'] = "leyendasFactura";
        $listaOperaciones[4]['id'] = 5;
        $listaOperaciones[4]['descripcion'] = "Motivo Anulación";
        $listaOperaciones[4]['operacion'] = "motivoAnulacion";
        $listaOperaciones[5]['id'] = 6;
        $listaOperaciones[5]['descripcion'] = "Mensajes Servicios";
        $listaOperaciones[5]['operacion'] = "mensajesServicios";
        $listaOperaciones[6]['id'] = 7;
        $listaOperaciones[6]['descripcion'] = "Eventos Significativos";
        $listaOperaciones[6]['operacion'] = "eventosSignificativos";
        $listaOperaciones[7]['id'] = 8;
        $listaOperaciones[7]['descripcion'] = "País Origen";
        $listaOperaciones[7]['operacion'] = "paisOrigen";
        $listaOperaciones[8]['id'] = 9;
        $listaOperaciones[8]['descripcion'] = "Tipo de Documento Identidad";
        $listaOperaciones[8]['operacion'] = "tipoDocumentoIdentidad";
        $listaOperaciones[9]['id'] = 10;
        $listaOperaciones[9]['descripcion'] = "Tipo de Documento Sector";
        $listaOperaciones[9]['operacion'] = "tipoDocumentoSector";
        $listaOperaciones[10]['id'] = 11;
        $listaOperaciones[10]['descripcion'] = "Tipo de Emisión";
        $listaOperaciones[10]['operacion'] = "tipoEmision";
        $listaOperaciones[11]['id'] = 12;
        $listaOperaciones[11]['descripcion'] = "Tipo de Habitación";
        $listaOperaciones[11]['operacion'] = "tipoHabitacion";
        $listaOperaciones[12]['id'] = 13;
        $listaOperaciones[12]['descripcion'] = "Tipo Método de Pago";
        $listaOperaciones[12]['operacion'] = "tipoMetodoPago";
        $listaOperaciones[13]['id'] = 14;
        $listaOperaciones[13]['descripcion'] = "Tipo de Moneda";
        $listaOperaciones[13]['operacion'] = "tipoMoneda";
        $listaOperaciones[14]['id'] = 15;
        $listaOperaciones[14]['descripcion'] = "Tipo Punto Venta";
        $listaOperaciones[14]['operacion'] = "puntoVenta";
        $listaOperaciones[15]['id'] = 16;
        $listaOperaciones[15]['descripcion'] = "Tipo Facturas";
        $listaOperaciones[15]['operacion'] = "tipoFactura";
        $listaOperaciones[16]['id'] = 17;
        $listaOperaciones[16]['descripcion'] = "Unidades de Medidas";
        $listaOperaciones[16]['operacion'] = "unidadMedida";

        return $listaOperaciones;
    }

    public function getPuntoVenta($id)
    {
        $unit = SiatPuntoVenta::where("sucursal", $id)->orderBy('codigo_punto_venta', 'asc')->first();
        return $unit;
    }
    public function getCuis($sucursal, $codigo_punto_venta)
    {
        $cuis = SiatPuntoVenta::where([
            'sucursal' => $sucursal,
            'codigo_punto_venta' => $codigo_punto_venta
        ])->pluck('codigo_cuis', 'id');
        // $unit= $unit->push($nit[0]);
        return json_encode($cuis);
    }

    //Funcion para consultar en la tabla RegistrosSincronizacionSiat
    public function consultaRegistros($sucursal, $punto_venta)
    {
        $sucursales = SiatSucursal::where('estado', true)->get();
        $nitCollection = PosSetting::latest()->first()->pluck('nit_emisor');
        $nitValue = $nitCollection->first();
        $datos_sucursal = SiatSucursal::where('sucursal', $sucursal)->first();

        Log::info("SIAT PANEL CONSULTA", [
            'funcion' => 'consultaRegistros',
            'sucursal' => $sucursal,
            'punto_venta' => $punto_venta,
            'nit' => $nitValue,
        ]);

        $registros = [];

        if (Session::get('token_siat') != null) {
            Log::info("SIAT PANEL CONSULTA - SINCRONIZANDO DESDE API", [
                'funcion' => 'consultaRegistros',
                'nit' => $nitValue,
                'sucursal' => $sucursal,
                'punto_venta' => $punto_venta,
            ]);
            try {
                $response = $this->getResponse($nitValue, $sucursal, $punto_venta);
                if ($response && isset($response['ENTITIES'])) {
                    $registros = $response['ENTITIES'];

                    $siatIdEmpresa = $registros[0]['empresa']['idEmpresa'] ?? null;
                    if ($siatIdEmpresa) {
                        Session::put('siat_id_empresa', $siatIdEmpresa);
                    }

                    Log::info("SIAT PANEL CONSULTA - SINCRONIZACION COMPLETADA", [
                        'funcion' => 'consultaRegistros',
                        'total_entidades' => count($registros),
                        'sucursal' => $sucursal,
                        'punto_venta' => $punto_venta,
                    ]);
                } else {
                    Log::warning("SIAT PANEL CONSULTA - RESPUESTA SIN ENTITIES", [
                        'funcion' => 'consultaRegistros',
                        'response_keys' => $response ? array_keys($response) : [],
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("SIAT PANEL CONSULTA ERROR", [
                    'funcion' => 'consultaRegistros',
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            Log::warning("SIAT PANEL CONSULTA - SIN TOKEN", [
                'funcion' => 'consultaRegistros',
                'sucursal' => $sucursal,
                'punto_venta' => $punto_venta,
            ]);
        }

        return view('siat-panel.log_siat_consulta', [
            'sucursales' => $sucursales,
            'registros' => $registros,
            'nit' => $nitCollection,
            'datos_sucursal' => $datos_sucursal,
            'sucursal' => $sucursal,
        ]);
    }

    public function listarSincronizados()
    {
        $sucursales = SiatSucursal::where('estado', true)->get();
        $nitCollection = PosSetting::latest()->first()->pluck('nit_emisor');
        $operaciones = $this->listaOperaciones();

        return view('siat-panel.datos_sincronizados', [
            'sucursales' => $sucursales,
            'operaciones' => $operaciones,
            'nit' => $nitCollection,
        ]);
    }

    public function getListarNitData(Request $request)
    {
        $nitCollection = PosSetting::latest()->first()->pluck('nit_emisor');
        $nitValue = $nitCollection->first();
        $entidades = [];

        if (Session::get('token_siat') != null) {
            $body = [
                'nit' => $nitValue,
                'parametro' => $request->input('parametro', ''),
                'sucursal' => (int)$request->input('sucursal', 0),
                'codigoPuntoVenta' => (int)$request->input('codigo_punto_venta', 0),
            ];

            try {
                $response = $this->postListarNit($body);
                if ($response && isset($response['ENTITIES'])) {
                    $entidades = $response['ENTITIES'];
                }
            } catch (\Exception $e) {
                Log::error("SIAT GET LISTAR NIT DATA ERROR", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json(['entidades' => $entidades]);
    }

    public function getRegistrosSiat($sucursal, $punto_venta)
    {
        $tablas = $this->listaOperaciones();

        foreach ($tablas as $tabla) {

            $rss = SiatRegistrosSincronizacion::where('sucursal', $sucursal)
                ->where('codigo_punto_venta', $punto_venta)
                ->where('operacion', $tabla['operacion'])->get();
            if ($rss->isNotEmpty()) {
                //No está vacía, no hacer nada    
            } else {
                //Está vacío, llenar de null
                $this->rellenarDatosVacios($tabla['descripcion'], $tabla['operacion'], $sucursal, $punto_venta, $tabla['id']);
            }
        }

        $sucursales = SiatSucursal::where('estado', true)->get();
        $nit = PosSetting::latest()->first()->pluck('nit_emisor');
        $registros = SiatRegistrosSincronizacion::where('sucursal', $sucursal)->where('codigo_punto_venta', $punto_venta)->orderBy('orden', 'asc')->get();
        $datos_sucursal = SiatSucursal::where('sucursal', $sucursal)->first();
        $datos_p_venta = SiatPuntoVenta::where("codigo_punto_venta", $punto_venta)->first();

        Log::info("SIAT PANEL BUSQUEDA", [
            'funcion' => 'getRegistrosSiat',
            'params' => [
                'sucursal' => $sucursal,
                'punto_venta' => $punto_venta,
            ],
            'total_registros' => $registros->count(),
        ]);

        $respuesta = array(
            "sucursal" => $sucursal,
            "punto_venta" => $punto_venta,
            "registros" => $registros,
            "nit" => $nit
        );
        return $respuesta;
    }


    //Mostrar vista 
    public function mostrarDatosdeSucursalPuntoVenta($sucursal, $punto_venta)
    {

        $sucursales = SiatSucursal::where('estado', true)->get();
        $nit = PosSetting::latest()->first()->pluck('nit_emisor');
        $registros = SiatRegistrosSincronizacion::where('sucursal', $sucursal)->where('codigo_punto_venta', $punto_venta)->orderBy('orden', 'asc')->get();
        $datos_sucursal = SiatSucursal::where('sucursal', $sucursal)->first();
        $datos_p_venta = SiatPuntoVenta::where("codigo_punto_venta", $punto_venta)->first();

        Log::info("SIAT PANEL BUSQUEDA", [
            'funcion' => 'mostrarDatosdeSucursalPuntoVenta',
            'params' => [
                'sucursal' => $sucursal,
                'punto_venta' => $punto_venta,
            ],
            'total_registros' => $registros->count(),
        ]);

        return view('siat-panel.log_siat_consulta', ['sucursales' => $sucursales, 'registros' => $registros, 'nit' => $nit, 'datos_sucursal' => $datos_sucursal, 'datos_p_venta' => $datos_p_venta]);
    }

    public function rellenarDatosVacios($descripcion, $operacion, $sucursal, $punto_venta, $orden)
    {
        //Guardar tupla según la operacion
        $obj = new SiatRegistrosSincronizacion();
        $obj->descripcion = $descripcion;
        $obj->operacion = $operacion;
        $obj->orden = $orden;
        $obj->sucursal = $sucursal;
        $obj->codigo_punto_venta = $punto_venta;
        $obj->updated_at = null;
        $obj->save();
        return;
    }


    //Desde la funcion UPDATE, se llamará al model SiatTrait para operaciones genéricas
    public function update(Request $request, $id)
    {
        if (Session::get('token_siat') != null) {
            $registro = $request->registro_id;
            $descripcion = $request->descripcion;
            $operacion = $request->operacion;
            $orden = $request->orden;
            $sucursal = $request->sucursal;
            $p_venta = $request->codigo_punto_venta;
            $idEmpresa = $request->id_empresa;
            $nit = $request->nit;
            $is_auth = false;
            if (isset($request->auth))
                $is_auth = true;

            $body = [
                'id' => (int)$registro,
                'descripcion' => $descripcion,
                'operacion' => $operacion,
                'estado' => 'S',
                'orden' => (int)$orden,
                'sucursal' => (int)$sucursal,
                'codigo_punto_venta' => (int)$p_venta,
                'empresa' => [
                    'idEmpresa' => (int)$idEmpresa,
                    'nit' => $nit,
                ],
            ];

            Log::info("SIAT PANEL SINCRONIZACION", [
                'funcion' => 'update',
                'body' => $body,
            ]);

            try {
                $response = $this->postSincronizar($body);
                if (!$response) {
                    return redirect()->back()->with('warning', 'Error al sincronizar con SIAT');
                }
            } catch (\Exception $e) {
                Log::error("SIAT PANEL SINCRONIZACION ERROR", [
                    'funcion' => 'update',
                    'error' => $e->getMessage(),
                ]);
                return redirect()->back()->with('warning', 'Credenciales SIAT no válidas');
            }

            $msj = $descripcion . '\n Sincronización Siat completada con éxito.';

            Log::info("SIAT PANEL SINCRONIZACION COMPLETADA", [
                'funcion' => 'update',
                'operacion' => $operacion,
                'sucursal' => $sucursal,
                'punto_venta' => $p_venta,
                'estado' => 'completada',
            ]);

            if ($is_auth) {
                return array('mensaje' => $msj, 'status' => true);
            } else {
                Session::flash('success', $msj);
                return redirect()->back();
            }
        } else {
            Log::warning("SIAT PANEL SINCRONIZACION", [
                'funcion' => 'update',
                'error' => 'sin conexion al servicio Gisul',
            ]);
            return array('mensaje' => "sin conexion al servicio Gisul", 'status' => false);
        }
    }

    public function renovarCUFD()
    {
        $this->tareaRenovarCufd();
        $msj = "CUFD renovados, tarea correctamente";
        return $msj;
    }
}