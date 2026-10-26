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
use Illuminate\Support\Facades\Http;
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

    public function parametros(Request $request)
    {
        //obteniendo Billers
        $billers = Biller::where('is_active', true)->get();
        $selected_biller_id = $request->get('biller_id', optional($billers->first())->id);
        $selected_tipo = $request->get('tipo', '');

        $query = SiatParametricaVario::query();
        if (!empty($selected_tipo)) {
            $query->where('tipo_clasificador', $selected_tipo);
        }

        $parametricas = $query->selectRaw('DISTINCT ON (tipo_clasificador, codigo_clasificador) *')
            ->orderBy('tipo_clasificador')
            ->orderBy('codigo_clasificador')
            ->orderByDesc('id')
            ->get();

        $tipos = SiatParametricaVario::distinct('tipo_clasificador')
            ->orderBy('tipo_clasificador')
            ->pluck('tipo_clasificador');

        return view('siat-panel.parametrica', [
            'billers' => $billers,
            'parametricas' => $parametricas,
            'selected_biller_id' => $selected_biller_id,
            'selected_tipo' => $selected_tipo,
            'tipos' => $tipos,
        ]);
    }

    public function getParametroById(Biller $id, Request $request)
    {
        //obteniendo Billers
        $billers = Biller::where('is_active', true)->get();
        $punto_venta = $id->punto_venta_siat;
        $sucursal = optional(optional($id->almacen)->sucursal)->sucursal ?? 0;
        $selected_tipo = $request->get('tipo', '');

        $query = SiatParametricaVario::query();
        if ($punto_venta !== null && $sucursal !== null) {
            $query->where('codigo_punto_venta', $punto_venta)
                ->where('sucursal', $sucursal);
        }
        if (!empty($selected_tipo)) {
            $query->where('tipo_clasificador', $selected_tipo);
        }

        $parametricas = $query->selectRaw('DISTINCT ON (tipo_clasificador, codigo_clasificador) *')
            ->orderBy('tipo_clasificador')
            ->orderBy('codigo_clasificador')
            ->orderByDesc('id')
            ->get();

        if ($parametricas->isEmpty() && ($punto_venta != 0 || $sucursal != 0)) {
            $queryFallback = SiatParametricaVario::query();
            if (!empty($selected_tipo)) {
                $queryFallback->where('tipo_clasificador', $selected_tipo);
            }
            $parametricas = $queryFallback->selectRaw('DISTINCT ON (tipo_clasificador, codigo_clasificador) *')
                ->orderBy('tipo_clasificador')
                ->orderBy('codigo_clasificador')
                ->orderByDesc('id')
                ->get();
        }

        $tipos = SiatParametricaVario::distinct('tipo_clasificador')
            ->orderBy('tipo_clasificador')
            ->pluck('tipo_clasificador');

        Log::info("SIAT PANEL BUSQUEDA", [
            'funcion' => 'getParametroById',
            'params' => [
                'biller_id' => $id->id,
                'punto_venta' => $punto_venta,
                'sucursal' => $sucursal,
                'tipo' => $selected_tipo,
            ],
            'total_resultados' => $parametricas->count(),
        ]);

        return view('siat-panel.parametrica', [
            'billers' => $billers,
            'parametricas' => $parametricas,
            'selected_biller_id' => $id->id,
            'selected_tipo' => $selected_tipo,
            'tipos' => $tipos,
        ]);
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
        if (!$unit) {
            $unit = SiatPuntoVenta::where(function($q) {
                $q->where('codigo_punto_venta', 0)->orWhere('codigo_punto_venta', '0');
            })->first();
        }
        if (!$unit) {
            $unit = (object)[
                'codigo_punto_venta' => 0,
                'nombre_punto_venta' => 'Punto de Venta Principal (0)'
            ];
        }
        return response()->json($unit);
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
            'punto_venta' => $punto_venta,
        ]);
    }

    public function listarSincronizados()
    {
        $sucursales = SiatSucursal::where('estado', true)->get();
        $posSetting = PosSetting::first();
        $nitValue = $posSetting ? $posSetting->nit_emisor : '';
        $operaciones = $this->listaOperaciones();

        $sucursal_actual = $sucursales->first()->sucursal ?? 0;
        $puntos_venta = SiatPuntoVenta::where('sucursal', $sucursal_actual)->orderBy('codigo_punto_venta', 'asc')->get();
        if ($puntos_venta->isEmpty()) {
            $puntos_venta = collect([(object)[
                'codigo_punto_venta' => 0,
                'nombre_punto_venta' => 'Punto de Venta Principal (0)'
            ]]);
        }

        return view('siat-panel.datos_sincronizados', [
            'sucursales' => $sucursales,
            'puntos_venta' => $puntos_venta,
            'operaciones' => $operaciones,
            'nit' => [$nitValue],
        ]);
    }

    public function getListarNitData(Request $request)
    {
        $posSetting = PosSetting::first();
        $nitValue = $posSetting ? $posSetting->nit_emisor : '';
        $companyName = $posSetting ? $posSetting->razon_social_emisor : '';

        $parametro = (string) $request->input('parametro', '');
        $sucursal = $request->has('sucursal') && $request->input('sucursal') !== '' ? (int)$request->input('sucursal') : null;
        $codigoPuntoVenta = $request->has('codigo_punto_venta') && $request->input('codigo_punto_venta') !== '' ? (int)$request->input('codigo_punto_venta') : null;

        $entidades = [];

        // 1. Intentar consultar desde el microservicio remoto
        try {
            $token = Session::get('token_siat');
            if (!$token) {
                $this->getToken();
                $token = Session::get('token_siat');
            }

            if ($token && !empty($posSetting->url_optimo)) {
                $body = [
                    'nit' => $nitValue,
                    'parametro' => $parametro,
                    'sucursal' => $sucursal ?? 0,
                    'codigoPuntoVenta' => $codigoPuntoVenta ?? 0,
                ];

                $response = $this->postListarNit($body);
                if ($response && isset($response['ENTITIES']) && is_array($response['ENTITIES']) && count($response['ENTITIES']) > 0) {
                    $entidades = $response['ENTITIES'];
                }
            }
        } catch (\Exception $e) {
            Log::warning("SIAT GET LISTAR NIT DATA API ERROR: " . $e->getMessage());
        }

        // 2. Fallback resiliente: consultar tablas locales sincronizadas en la base de datos
        if (empty($entidades)) {
            $entidades = $this->obtenerEntidadesLocales($parametro, $sucursal, $codigoPuntoVenta, $nitValue, $companyName);
        }

        return response()->json(['entidades' => $entidades]);
    }

    protected function obtenerEntidadesLocales($parametro, $sucursal, $codigoPuntoVenta, $nitValue, $companyName)
    {
        $entidades = [];

        switch ($parametro) {
            case 'actividades':
                $items = SiatActividadEconomica::query()
                    ->when($sucursal !== null, function ($q) use ($sucursal) {
                        $q->where('sucursal', $sucursal);
                    })
                    ->when($codigoPuntoVenta !== null, function ($q) use ($codigoPuntoVenta) {
                        $q->where('codigo_punto_venta', $codigoPuntoVenta);
                    })
                    ->orderBy('id', 'asc')
                    ->get();

                if ($items->isEmpty() && ($sucursal !== null || $codigoPuntoVenta !== null)) {
                    $items = SiatActividadEconomica::orderBy('id', 'asc')->get();
                }

                foreach ($items as $item) {
                    $entidades[] = [
                        'id' => $item->id,
                        'descripcion' => $item->descripcion,
                        'clave' => $item->codigo_caeb,
                        'valor' => $item->tipo_actividad,
                        'empresa' => ['nombre' => $companyName, 'nit' => $nitValue],
                        'estado' => 'A',
                        'fecha_sincronizacion' => $item->updated_at ? $item->updated_at->format('Y-m-d H:i:s') : '',
                        'fecha_alta' => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '',
                    ];
                }
                break;

            case 'actividadesDocumentoSector':
                $items = SiatDocumentoSector::query()
                    ->when($sucursal !== null, function ($q) use ($sucursal) {
                        $q->where('sucursal', $sucursal);
                    })
                    ->when($codigoPuntoVenta !== null, function ($q) use ($codigoPuntoVenta) {
                        $q->where('codigo_punto_venta', $codigoPuntoVenta);
                    })
                    ->orderBy('id', 'asc')
                    ->get();

                if ($items->isEmpty() && ($sucursal !== null || $codigoPuntoVenta !== null)) {
                    $items = SiatDocumentoSector::orderBy('id', 'asc')->get();
                }

                foreach ($items as $item) {
                    $entidades[] = [
                        'id' => $item->id,
                        'descripcion' => $item->tipo_documento_sector,
                        'clave' => $item->codigo_actividad,
                        'valor' => $item->codigo_documento_sector,
                        'empresa' => ['nombre' => $companyName, 'nit' => $nitValue],
                        'estado' => 'A',
                        'fecha_sincronizacion' => $item->updated_at ? $item->updated_at->format('Y-m-d H:i:s') : '',
                        'fecha_alta' => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '',
                    ];
                }
                break;

            case 'productosServicios':
                $items = SiatProductoServicio::query()
                    ->when($sucursal !== null, function ($q) use ($sucursal) {
                        $q->where('sucursal', $sucursal);
                    })
                    ->when($codigoPuntoVenta !== null, function ($q) use ($codigoPuntoVenta) {
                        $q->where('codigo_punto_venta', $codigoPuntoVenta);
                    })
                    ->orderBy('descripcion_producto', 'asc')
                    ->get();

                if ($items->isEmpty() && ($sucursal !== null || $codigoPuntoVenta !== null)) {
                    $items = SiatProductoServicio::orderBy('descripcion_producto', 'asc')->get();
                }

                foreach ($items as $item) {
                    $entidades[] = [
                        'id' => $item->id,
                        'descripcion' => $item->descripcion_producto,
                        'clave' => $item->codigo_actividad,
                        'valor' => $item->codigo_producto,
                        'empresa' => ['nombre' => $companyName, 'nit' => $nitValue],
                        'estado' => 'A',
                        'fecha_sincronizacion' => $item->updated_at ? $item->updated_at->format('Y-m-d H:i:s') : '',
                        'fecha_alta' => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '',
                    ];
                }
                break;

            case 'leyendasFactura':
                $items = SiatLeyendaFactura::query()
                    ->when($sucursal !== null, function ($q) use ($sucursal) {
                        $q->where('sucursal', $sucursal);
                    })
                    ->when($codigoPuntoVenta !== null, function ($q) use ($codigoPuntoVenta) {
                        $q->where('codigo_punto_venta', $codigoPuntoVenta);
                    })
                    ->orderBy('id', 'asc')
                    ->get();

                if ($items->isEmpty() && ($sucursal !== null || $codigoPuntoVenta !== null)) {
                    $items = SiatLeyendaFactura::orderBy('id', 'asc')->get();
                }

                foreach ($items as $item) {
                    $entidades[] = [
                        'id' => $item->id,
                        'descripcion' => $item->descripcion_leyenda,
                        'clave' => $item->codigo_actividad,
                        'valor' => $item->codigo_actividad,
                        'empresa' => ['nombre' => $companyName, 'nit' => $nitValue],
                        'estado' => 'A',
                        'fecha_sincronizacion' => $item->updated_at ? $item->updated_at->format('Y-m-d H:i:s') : '',
                        'fecha_alta' => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '',
                    ];
                }
                break;

            default:
                if (!empty($parametro)) {
                    $items = SiatParametricaVario::where('tipo_clasificador', $parametro)
                        ->when($sucursal !== null, function ($q) use ($sucursal) {
                            $q->where('sucursal', $sucursal);
                        })
                        ->when($codigoPuntoVenta !== null, function ($q) use ($codigoPuntoVenta) {
                            $q->where('codigo_punto_venta', $codigoPuntoVenta);
                        })
                        ->orderBy('id', 'asc')
                        ->get();

                    if ($items->isEmpty() && ($sucursal !== null || $codigoPuntoVenta !== null)) {
                        $items = SiatParametricaVario::where('tipo_clasificador', $parametro)
                            ->orderBy('id', 'asc')
                            ->get();
                    }

                    $items = $items->unique('codigo_clasificador');

                    foreach ($items as $item) {
                        $entidades[] = [
                            'id' => $item->id,
                            'descripcion' => $item->descripcion,
                            'clave' => $item->tipo_clasificador,
                            'valor' => $item->codigo_clasificador,
                            'empresa' => ['nombre' => $companyName, 'nit' => $nitValue],
                            'estado' => $item->estado ? 'A' : 'I',
                            'fecha_sincronizacion' => $item->updated_at ? $item->updated_at->format('Y-m-d H:i:s') : '',
                            'fecha_alta' => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '',
                        ];
                    }
                }
                break;
        }

        return $entidades;
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
        if (!Session::get('token_siat')) {
            $this->getToken();
        }

        if (Session::get('token_siat') != null) {
            $registro = $request->registro_id;
            $descripcion = $request->descripcion;
            $operacion = $request->operacion;
            $orden = $request->orden;
            $sucursal = $request->sucursal;
            $p_venta = $request->codigo_punto_venta;
            $idEmpresa = $request->id_empresa;
            $nit = $request->nit;
            $is_auth = isset($request->auth);

            $company_id = Auth::check() ? Auth::user()->company_id : (Session::get('company_id') ?? 1);
            $pos_setting = PosSetting::where('company_id', $company_id)->first() ?? PosSetting::first();

            if (empty($idEmpresa)) {
                $idEmpresa = Session::get('siat_id_empresa') ?? ($pos_setting->id_empresa_siat ?? 0);
            }
            if (empty($nit)) {
                $nit = $pos_setting->nit_emisor ?? '';
            }

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

            // Descargar y sincronizar datos reales del SIAT a las tablas locales por Empresa
            $sincronizadoExitoso = false;
            $pv = SiatPuntoVenta::where('sucursal', $sucursal)
                ->where('codigo_punto_venta', $p_venta)
                ->when($company_id, function ($q) use ($company_id) {
                    $q->where('company_id', $company_id);
                })
                ->first();
            $cuis = ($pv && $pv->codigo_cuis && $pv->codigo_cuis !== 'CUIS-PENDIENTE') ? $pv->codigo_cuis : null;
            if (!$cuis) {
                $pvFallback = SiatPuntoVenta::where('sucursal', $sucursal)
                    ->where('codigo_punto_venta', $p_venta)
                    ->whereNotNull('codigo_cuis')
                    ->where('codigo_cuis', '!=', 'CUIS-PENDIENTE')
                    ->first();
                $cuis = $pvFallback ? $pvFallback->codigo_cuis : null;
            }

            if ($cuis && !empty($pos_setting->url_operaciones)) {
                $queryParams = [
                    'nit' => (int)$nit,
                    'codigoSucursal' => (int)$sucursal,
                    'codigoPuntoVenta' => (int)$p_venta,
                    'operacion' => $operacion,
                    'cuis' => $cuis
                ];
                $urlSinc = rtrim($pos_setting->url_operaciones, '/') . '/sincronizacion/sincronizacion?' . http_build_query($queryParams);
                try {
                    $respSiat = Http::timeout(35)->withHeaders([
                        'Authorization' => 'Bearer ' . Session::get('token_siat'),
                    ])->post($urlSinc);

                    if ($respSiat->successful()) {
                        $siatData = $respSiat->json();
                        if (isset($siatData['ESTADO']) && $siatData['ESTADO'] === 'OK') {
                            $this->llenarTablaxOperacion($operacion, $siatData, $sucursal, $p_venta);
                            $sincronizadoExitoso = true;
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning("SIAT PANEL ERROR DESCARGA 7014: " . $e->getMessage());
                }
            }

            // Fallback si 7014 no respondió o no hubo CUIS: consultar datos sincronizados desde backend Optimo
            if (!$sincronizadoExitoso && !empty($pos_setting->url_optimo)) {
                $parametroMap = [
                    'tipoMetodoPago' => 'tipo_metodo_pago',
                    'tipoDocumentoIdentidad' => 'tipo_documento_identidad',
                    'tipoMoneda' => 'tipo_moneda',
                    'unidadMedida' => 'unidades_medida',
                    'tipoDocumentoSector' => 'tipo_documento_sector',
                    'motivoAnulacion' => 'motivo_anulacion',
                    'mensajesServicios' => 'mensajes_servicios',
                    'eventosSignificativos' => 'evento_significativos',
                    'paisOrigen' => 'pais_origen',
                    'tipoEmision' => 'tipo_emision',
                    'tipoHabitacion' => 'tipo_habitacion',
                    'puntoVenta' => 'tipo_punto_venta',
                    'tipoFactura' => 'tipo_factura',
                    'actividades' => 'actividades',
                    'leyendasFactura' => 'leyendas',
                    'productosServicios' => 'productos',
                    'actividadesDocumentoSector' => 'actividad_documento_sector',
                ];
                $paramSnake = $parametroMap[$operacion] ?? $operacion;
                try {
                    $respListar = $this->postListarNit([
                        'nit' => $nit,
                        'parametro' => $paramSnake,
                        'sucursal' => (int)$sucursal,
                        'codigoPuntoVenta' => (int)$p_venta,
                    ]);
                    if ($respListar && !empty($respListar['ENTITIES'])) {
                        $this->llenarTablaxOperacion($operacion, ['ENTITIES' => $respListar['ENTITIES']], $sucursal, $p_venta);
                        $sincronizadoExitoso = true;
                    }
                } catch (\Throwable $e) {
                    Log::warning("SIAT PANEL ERROR LISTAR-NIT 7012: " . $e->getMessage());
                }
            }

            // Actualizar tupla en registros_sincronizacion_siat local
            SiatRegistrosSincronizacion::where('sucursal', $sucursal)
                ->where('codigo_punto_venta', $p_venta)
                ->where('operacion', $operacion)
                ->when($company_id, function ($q) use ($company_id) {
                    $q->where('company_id', $company_id);
                })
                ->update([
                    'estado' => 'S',
                    'usuario_modificacion' => Auth::check() ? Auth::user()->id : null,
                    'updated_at' => now(),
                ]);

            $msj = $descripcion . "\n Sincronización Siat completada con éxito.";

            Log::info("SIAT PANEL SINCRONIZACION COMPLETADA", [
                'funcion' => 'update',
                'operacion' => $operacion,
                'sucursal' => $sucursal,
                'punto_venta' => $p_venta,
                'estado' => 'completada',
                'sincronizado_local' => $sincronizadoExitoso,
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