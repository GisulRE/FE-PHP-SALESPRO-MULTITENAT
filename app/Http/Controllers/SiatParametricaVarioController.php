<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\SiatParametricaVario;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Http;

class SiatParametricaVarioController extends Controller
{
    public function index()
    {
        $parametricas = SiatParametricaVario::all();
        return view('siat-parametrica-vario/index',['parametricas' => $parametricas]);
    }

    public function store(Request $request)
    {
        $user = Auth::user()->id;
        
        $data = $request->all();
        $data['usuario_alta'] = $user;
        $data['usuario_modificacion'] = $user;
        SiatParametricaVario::create($data);
        return redirect('parametric')->with('message', 'Paramétricas creada correctamente');
    }
    
    public function update(Request $request, $id)
    {        
        $user = Auth::user()->id;
        $data = $request->all();
        $data['usuario_modificacion'] = $user;

        $update_data = SiatParametricaVario::find($data['parametrica_id']);
        $update_data->update($data);
        return redirect('parametric')->with('message', 'Paramétricas actualizada correctamente');
    }

    function textOperacion(String $ope)
    {
        if($ope === "tipoDocumentoIdentidad"){
            return "DOCUMENT0_IDENTIDAD";
        }
        if($ope === "mensajesServicios"){
            return "MENSAJES_SERVICIOS";
        }
        if($ope === "eventosSignificativos"){
            return "EVENTOS_SIGNIFICATIVOS";
        }
        if($ope === "paisOrigen"){
            return "PAIS_ORIGEN";
        }
        if($ope === "tipoDocumentoSector"){
            return "DOCUMENTO_SECTOR";
        }
        if($ope === "tipoEmision"){
            return "TIPO_EMISION";
        }
        if($ope === "tipoHabitacion"){
            return "TIPO_HABITACION";
        }
        if($ope === "tipoMetodoPago"){
            return "TIPO_HABITACION";
        }
        if($ope === "tipoMoneda"){
            return "TIPO_HABITACION";
        }
        if($ope === "puntoVenta"){
            return "TIPO_HABITACION";
        }
        if($ope === "tipoFactura"){
            return "TIPO_HABITACION";
        }
        if($ope === "unidadMedida"){
            return "TIPO_HABITACION";
        }

    }
    
    function siatall(String $request)
    {
        $bearer = 'Bearer eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJTcmlvNDZiMU9GQmJYIiwicmVmZXJlbmNlcyI6IjAiLCJpc3MiOiJhZG1pbiIsImV4cCI6MTY1NzAzMTYzNywiaWF0IjoxNjU2OTQ1MjM3LCJqdGkiOiJHU0YifQ.kZKeFtJW69IzHlYg3h-b6j57AinVjxryDn_ThAQbrT0';
        $host   = 'http://181.188.132.73:5002/';
        $path   = 'sincronizacion/sincronizacion?';

        $query  = 'codigoPuntoVenta=1&codigoSucursal=0&cuis=911B454F&nit=388615026&operacion=';
        $ope    = $request;
        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->post($host.$path.$query.$ope);
        $status = $response->json(); 
        $textOperacion = $this->textOperacion($ope);

        if($status[$textOperacion]) {  
            $items = $response[$textOperacion];
            //Variables 
            $user = Auth::user()->id;
            $tableIsEmpty = SiatParametricaVario::all();
    
            if($tableIsEmpty->isEmpty()) 
            {
                foreach($items as $item) 
                {
                    $obj = new SiatParametricaVario();
                
                    $obj->codigo_clasificador   = $item['codigoClasificador'];
                    $obj->descripcion           = $item['descripcion'];
                    $obj->tipo_clasificador     = $textOperacion;
                    $obj->usuario_alta      = $user;
                    $obj->usuario_modificacion= $user;
                    $obj->sucursal= 0;
                    $obj->codigo_punto_venta= 1;
                    $obj->save();
    
                }
            }
            else
            {
                // SiatParametricaVario::get()->each->delete();
                foreach($items as $item) 
                {
                    $obj = new SiatParametricaVario();
                
                    $obj->codigo_clasificador   = $item['codigoClasificador'];
                    $obj->descripcion           = $item['descripcion'];
                    $obj->tipo_clasificador     = $textOperacion;
                    $obj->usuario_alta          = $user;                    
                    $obj->usuario_modificacion  = $user;
                    $obj->sucursal              = 0;
                    $obj->codigo_punto_venta= 2;
                    $obj->save();                    
                }
    
            }
            
        }elseif($status['RESPUESTA']) {
            $msj = $status['RESPUESTA'];
            return redirect('documentsector')->with('warning', $msj);
        }
        
    }
    public function siat()
    {
        $items = [
            "tipoDocumentoIdentidad",
            "mensajesServicios",
            "eventosSignificativos",
            "paisOrigen",
            "tipoDocumentoSector",
            "tipoEmision",
            "tipoHabitacion",
            "tipoMetodoPago",
            "tipoMoneda",
            "puntoVenta",
            "tipoFactura",
            "unidadMedida"
        ];

        foreach($items as $item) {
            $this->siatall($item);
        }
        return redirect('parametric')->with('success', 'Paramétricas Sincronización - SIAT');
    }
}
