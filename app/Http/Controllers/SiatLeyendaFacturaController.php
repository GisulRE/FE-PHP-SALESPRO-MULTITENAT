<?php

namespace App\Http\Controllers;

use Auth;
use App\SiatLeyendaFactura;
use Illuminate\Http\Request;
use App\SiatActividadEconomica;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Http;

class SiatLeyendaFacturaController extends Controller
{
    public function index()
    {
        $leyendas = SiatLeyendaFactura::orderBy('id')->get();
        $actividades = SiatActividadEconomica::get();
        return view('siat-leyenda-factura/index',['leyendas' => $leyendas, 'actividades'=> $actividades]);
    }

    public function store(Request $request)
    {
        $user = Auth::user()->id;
                
        $data = $request->all();
        $data['usuario_alta'] = $user;
        $data['usuario_modificacion'] = $user;
        SiatLeyendaFactura::create($data);
        return redirect('legends')->with('message', 'Leyendas de Factura creada correctamente');
    }
    
    public function update(Request $request, $id)
    {        
        $user = Auth::user()->id;
        $data = $request->all();
        $data['usuario_modificacion'] = $user;

        $update_data = SiatLeyendaFactura::find($data['leyenda_id']);
        $update_data->update($data);
        return redirect('legends')->with('message', 'Leyendas de Factura actualizada correctamente');
    }

    public function siat()
    {
        $bearer = 'Bearer eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJTcmlvNDZiMU9GQmJYIiwicmVmZXJlbmNlcyI6IjAiLCJpc3MiOiJhZG1pbiIsImV4cCI6MTY1NzAzMTYzNywiaWF0IjoxNjU2OTQ1MjM3LCJqdGkiOiJHU0YifQ.kZKeFtJW69IzHlYg3h-b6j57AinVjxryDn_ThAQbrT0';
        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->post('http://181.188.132.73:5002/sincronizacion/sincronizacion?codigoPuntoVenta=1&codigoSucursal=0&cuis=911B454F&nit=388615026&operacion=leyendasFactura');
        $status = $response; 

        
        if($status['LEYENDAS_FACTURA']) {  
            $items = $response['LEYENDAS_FACTURA'];
            //Variables 
            $user = Auth::user()->id;
            $tableIsEmpty = SiatLeyendaFactura::all();
    
            if($tableIsEmpty->isEmpty()) 
            {
                foreach($items as $item) 
                {
                    $data = SiatActividadEconomica::where( 'codigo_caeb' , $item['codigoActividad'])->first();
                    $obj = new SiatLeyendaFactura();
                
                    $obj->actividad_id          = $data['id'];
                    $obj->descripcion_leyenda   = $item['descripcionLeyenda'];
                    $obj->usuario_alta      = $user;
                    $obj->usuario_modificacion= $user;
                    $obj->sucursal= 0;
                    $obj->codigo_punto_venta= 1;
                    $obj->save();
    
                }
                return redirect('legends')->with('success', 'Leyendas de Factura Sincronización - SIAT');
            }
            else
            {
                SiatLeyendaFactura::get()->each->delete();
                foreach($items as $item) 
                {
                    $data = SiatActividadEconomica::where( 'codigo_caeb' , $item['codigoActividad'])->first();
                    $obj = new SiatLeyendaFactura();
                
                    $obj->actividad_id              = $data['id'];
                    $obj->descripcion_leyenda       = $item['descripcionLeyenda'];
                    $obj->usuario_alta      = $user;
                    $obj->usuario_modificacion= $user;
                    $obj->sucursal= 0;
                    $obj->codigo_punto_venta= 1;
                    $obj->save();                    
                }
    
                return redirect('legends')->with('success', 'Leyendas de Factura actualizada y sincronizada del SIAT');
            }
            
        }elseif($status['RESPUESTA']) {
            $msj = $status['RESPUESTA'];
            return redirect('legends')->with('warning', $msj);
        }
        
    }

}
