<?php

namespace App\Http\Controllers;

use Auth;
use App\SiatDocumentoSector;
use Illuminate\Http\Request;
use App\SiatActividadEconomica;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Http;

class SiatDocumentoSectorController extends Controller
{
    public function index()
    {
        $documentos = SiatDocumentoSector::orderBy('id')->get();
        $actividades = SiatActividadEconomica::get();
        return view('siat-documento-sector/index',['documentos' => $documentos, 'actividades'=> $actividades]);
    }

    public function store(Request $request)
    {
        $user = Auth::user()->id;        
        
        $data = $request->all();
        $data['usuario_alta'] = $user;
        $data['usuario_modificacion'] = $user;
        SiatDocumentoSector::create($data);
        return redirect('documentsector')->with('message', 'Documento de Sector creada correctamente');
    }
    
    public function update(Request $request, $id)
    {        
        $user = Auth::user()->id;
        $var_codigo_actividad = SiatActividadEconomica::select('descripcion')->where('id', $request['actividad_id'])->get()[0]; 
        $data = $request->all();
        $data['codigo_actividad'] = $var_codigo_actividad['descripcion'];
        $data['usuario_modificacion'] = $user;

        $update_data = SiatDocumentoSector::find($data['documento_id']);
        $update_data->update($data);
        return redirect('documentsector')->with('message', 'Documento de Sector actualizada correctamente');
    }

    public function siat()
    {
        $bearer = 'Bearer eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJTcmlvNDZiMU9GQmJYIiwicmVmZXJlbmNlcyI6IjAiLCJpc3MiOiJhZG1pbiIsImV4cCI6MTY1NzAzMTYzNywiaWF0IjoxNjU2OTQ1MjM3LCJqdGkiOiJHU0YifQ.kZKeFtJW69IzHlYg3h-b6j57AinVjxryDn_ThAQbrT0';
        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->post('http://181.188.132.73:5002/sincronizacion/sincronizacion?codigoPuntoVenta=1&codigoSucursal=0&cuis=911B454F&nit=388615026&operacion=actividadesDocumentoSector');
        $status = $response; 
        
        if($status['ACTIVIDADES_DOCUMENTO_SECTOR']) {  
            $items = $response['ACTIVIDADES_DOCUMENTO_SECTOR'];
            //Variables 
            $user = Auth::user()->id;
            $tableIsEmpty = SiatDocumentoSector::all();
    
            if($tableIsEmpty->isEmpty()) 
            {
                foreach($items as $item) 
                {
                    $data = SiatActividadEconomica::where( 'codigo_caeb' , $item['codigoActividad'])->first();
                    $obj = new SiatDocumentoSector();
                
                    $obj->actividad_id              = $data['id'];
                    $obj->codigo_documento_sector   = $item['codigoDocumentoSector'];
                    $obj->tipo_documento_sector     = $item['tipoDocumentoSector'];
                    $obj->usuario_alta      = $user;
                    $obj->usuario_modificacion= $user;
                    $obj->save();
    
                }
                return redirect('documentsector')->with('success', 'Documento Sector Sincronización - SIAT');
            }
            else
            {
                SiatDocumentoSector::get()->each->delete();
                foreach($items as $item) 
                {
                    $data = SiatActividadEconomica::where( 'codigo_caeb' , $item['codigoActividad'])->first();
                    $obj = new SiatDocumentoSector();
                
                    $obj->actividad_id              = $data['id'];
                    $obj->codigo_documento_sector   = $item['codigoDocumentoSector'];
                    $obj->tipo_documento_sector     = $item['tipoDocumentoSector'];
                    $obj->usuario_alta      = $user;
                    $obj->usuario_modificacion= $user;
                    $obj->save();                    
                }
    
                return redirect('documentsector')->with('success', 'Documento Sector actualizada y sincronizada del SIAT');
            }
            
        }elseif($status['RESPUESTA']) {
            $msj = $status['RESPUESTA'];
            return redirect('documentsector')->with('warning', $msj);
        }
        
    }
}
