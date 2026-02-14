<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SiatActividadEconomica;
use Auth;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

// Uso de HTtp client
use Illuminate\Support\Facades\Http;


class SiatActividadEconomicaController extends Controller
{
    public function index()
    {
        $actividades = SiatActividadEconomica::orderBy('id')->get();
        return view('siat-actividad-economica.index',['actividades' => $actividades]);

    }
    public function store(Request $request)
    {
        $user = Auth::user()->id;
        $data = $request->all();
        $data['usuario_alta'] = $user;
        $data['usuario_modificacion'] = $user;
        SiatActividadEconomica::create($data);
        return redirect('activities')->with('message', 'Actividad Económica creada correctamente');
    }
    
    public function update(Request $request, $id)
    {        
        $user = Auth::user()->id;
        $data = $request->all();
        $data['usuario_modificacion'] = $user;

        $update_data = SiatActividadEconomica::find($data['actividad_id']);
        $update_data->update($data);
        return redirect('activities')->with('message', 'Actividad Económica actualizada correctamente');
    }

    public function siat()
    {
        $bearer = 'Bearer eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJTcmlvNDZiMU9GQmJYIiwicmVmZXJlbmNlcyI6IjAiLCJpc3MiOiJhZG1pbiIsImV4cCI6MTY1NzAzMTYzNywiaWF0IjoxNjU2OTQ1MjM3LCJqdGkiOiJHU0YifQ.kZKeFtJW69IzHlYg3h-b6j57AinVjxryDn_ThAQbrT0';
        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->post('http://181.188.132.73:5002/sincronizacion/sincronizacion?codigoPuntoVenta=1&codigoSucursal=0&cuis=911B454F&nit=388615026&operacion=actividades');
        $status = $response; 
        
        if($status['ACTIVIDADES']) {  
            $items = $status['ACTIVIDADES']; 
            //Variables 
            $user = Auth::user()->id;
            $tableIsEmpty = SiatActividadEconomica::all();
    
            if($tableIsEmpty->isEmpty()) 
            {
                foreach($items as $item) 
                {
                    $obj = new SiatActividadEconomica();
                
                    $obj->codigo_caeb       = $item['codigoCaeb'];
                    $obj->descripcion       = $item['descripcion'];
                    $obj->tipo_actividad    = $item['tipoActividad']; 
                    $obj->usuario_alta      = $user;
                    $obj->usuario_modificacion= $user;
                    $obj->save();
    
                }
                return redirect('activities')->with('success', 'Actividad Económica Sincronización - SIAT');
            }
            else
            {
                foreach($items as $item) 
                {
                    // $data = SiatActividadEconomica::where( 'codigo_caeb' , $item['codigoCaeb'])->first();
                    // $data->tipo_actividad       = $item['tipoActividad'];
                    // $data->descripcion          = $item['descripcion'];
                    // $data->usuario_modificacion = $user;
                    $obj = new SiatActividadEconomica();
                
                    $obj->codigo_caeb       = $item['codigoCaeb'];
                    $obj->descripcion       = $item['descripcion'];
                    $obj->tipo_actividad    = $item['tipoActividad']; 
                    $obj->sucursal          = 1; 
                    $obj->codigo_punto_venta= 2;
                    $obj->usuario_alta      = $user;
                    $obj->usuario_modificacion= $user;
                    
                    $obj->save();
    
                }
    
                return redirect('activities')->with('success', 'Actividad Económica actualizada y sincronizada del SIAT');
            }
            
        }elseif($status['RESPUESTA']) {
                $msj = $status['RESPUESTA'];
                return redirect('activities')->with('warning', $msj);
        } 
    }
}
