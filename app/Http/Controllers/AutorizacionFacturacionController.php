<?php

namespace App\Http\Controllers;

use App\UrlWs;
use Carbon\Carbon;
use App\PosSetting;
use App\GeneralSetting;
use Illuminate\Http\Request;
use App\Http\Traits\SiatTrait;
use App\AutorizacionFacturacion;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AutorizacionFacturacionController extends Controller
{
    use SiatTrait;

    public function index()
    {
        $respuesta = $this->listaAutorizacionFacturacion(); // SiatTrait
        $formato_fecha = GeneralSetting::first()->date_format;

        
        $items = [];
        if ($respuesta['status']) {
            $formato_fecha = GeneralSetting::first()->date_format;
            $items = $respuesta['data'];
            return view('autorizacion-facturacion.index', compact('items', 'formato_fecha'));

        } else {            
            $message = " Error, Intente de Nuevo";
            return redirect()->to('autorizacion')->with('message', $message)->with('message_error', $respuesta['mensaje']);
        }


    }

    public function create()
    {
        $pos_setting = PosSetting::latest()->first();
        $urls = UrlWs::get(); 
        $partials_create = 1;
        return view('autorizacion-facturacion.create', [
            'pos_setting' => $pos_setting,
            'item'=> new AutorizacionFacturacion(), 
            'urls'=> $urls, 
            'partials_create'=> $partials_create
            ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user()->id;
        $data = $request->all();
        $dataJson['tipoModalidad'] = $data['tipo_modalidad'];
        $dataJson['tipoAmbiente'] = $data['tipo_ambiente'];

        $dataJson['nitEmpresa'] = $data['nit_empresa'];
        $dataJson['fechaSolicitud'] = $data['fecha_solicitud'];
        $dataJson['tipoSistema'] = $data['tipo_sistema'];
        $dataJson['codigoSistema'] = $data['codigo_sistema'];
        $dataJson['token'] = $data['token'];
        $dataJson['fechaVencimientoToken'] = $data['fecha_vencimiento_token'];
        $dataJson['documentosSector'] = $data['documento_sector'];

        $dataJson['estado'] = 'B';

        
        $respuesta = $this->saveAutorizacionFacturacion($dataJson); // SiatTrait

        if ($respuesta['status']) {
            return redirect('autorizacion')->with('message', 'Autorización/Facturación creada correctamente');
        } else {            
            $message .= " Error al crear, Intente de Nuevo";
            return redirect()->to('autorizacion')->with('message', $message)->with('message_error', $respuesta['mensaje']);
        }
    }

    public function edit($id)
    {
        $pos_setting = PosSetting::latest()->first();
        $respuesta = $this->obtenerAutorizacionFacturacionxID($id);
        
        if ($respuesta['status']) {       
            return view('autorizacion-facturacion.edit', [
                'item' => $respuesta['data'], 
                'pos_setting' => $pos_setting,
                'documentos' => $respuesta['data']['documentos']
            ]);
        } else {            
            $message = " Error, Intente de Nuevo";
            return redirect()->to('autorizacion')->with('warning', $message)->with('message_error', $respuesta['mensaje']);
        }
    }

    public function update(Request $request, $id)
    {  
        $user = Auth::user()->id;
        $data = $request->all();
        
        $dataJson['tipoModalidad'] = $data['tipo_modalidad'];
        $dataJson['tipoAmbiente'] = $data['tipo_ambiente'];

        $dataJson['nitEmpresa'] = $data['nit_empresa'];
        $dataJson['fechaSolicitud'] = $data['fecha_solicitud'];
        $dataJson['tipoSistema'] = $data['tipo_sistema'];
        $dataJson['codigoSistema'] = $data['codigo_sistema'];
        $dataJson['token'] = $data['token'];
        $dataJson['fechaVencimientoToken'] = $data['fecha_vencimiento_token'];
        $dataJson['documentosSector'] = $data['documento_sector'];

        $dataJson['estado'] = 'B';

        
        $respuesta = $this->updateAutorizacionFacturacion($dataJson, $id); // SiatTrait

        if ($respuesta['status']) {
            return redirect('autorizacion')->with('message', 'Autorización/Facturación actualizada correctamente');
        } else {            
            $message = " Error al actualizar, Intente de Nuevo";
            return redirect()->to('autorizacion')->with('message', $message)->with('message_error', $respuesta['mensaje']);
        }
    }

    public function getUrls($status, $modalidad_id) 
    {
        if ($modalidad_id == 1) {
            $data = UrlWs::where('ambiente',$status)->whereIn('uso_modalidad',[1, 3])->get();
            return $data;
        }
        if ($modalidad_id == 2) {
            $data = UrlWs::where('ambiente',$status)->whereIn('uso_modalidad',[2, 3])->get();
            return $data;
        }
        return "No hay datos";
    }

    public function cambiarEstadoAutorizacion($id)
    {
        $respuesta = $this->activarAutorizacionFacturacion($id); // SiatTrait

        return $respuesta;
    }


    // OPERACIONESP 
    public function getDocumentoSector(Request $request)
    {
        $data = $request->all();
        $modalidad = $data['modalidad'];
        $ambiente = $data['ambiente'];

        $respuesta = $this->busquedaDocumentoSector($modalidad, $ambiente);

        if ($respuesta['status'] == false) {
            $respuesta = [];
        }
        return $respuesta['data'];
    }


}
