<?php

namespace App\Http\Controllers;

use Auth;
use App\SiatProductoServicio;
use Illuminate\Http\Request;
use App\SiatActividadEconomica;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Http;

class SiatProductoServicioController extends Controller
{
    public function index()
    {
        $productos = SiatProductoServicio::orderBy('id')->get();
        $actividades = SiatActividadEconomica::get();
        return view('siat-producto-servicio/index',['productos' => $productos, 'actividades'=> $actividades]);
    }

    public function store(Request $request)
    {
        $user = Auth::user()->id;
        
        $data = $request->all();
        $data['usuario_alta'] = $user;
        $data['usuario_modificacion'] = $user;
        SiatProductoServicio::create($data);
        return redirect('productservice')->with('message', 'Productos Servicios creada correctamente');
    }
    
    public function update(Request $request, $id)
    {        
        $user = Auth::user()->id;
        $data = $request->all();
        $data['usuario_modificacion'] = $user;

        $update_data = SiatProductoServicio::find($data['producto_id']);
        $update_data->update($data);
        return redirect('productservice')->with('message', 'Productos Servicios actualizada correctamente');
    }



    public function destroy($id)
    {
        $product = SiatProductoServicio::find($id);
        $product->delete();
        return redirect('productservice')->with('message', 'Productos Servicios eliminada');
    }

    public function siat()
    {
        $bearer = 'Bearer eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJTcmlvNDZiMU9GQmJYIiwicmVmZXJlbmNlcyI6IjAiLCJpc3MiOiJhZG1pbiIsImV4cCI6MTY1NzAzMTYzNywiaWF0IjoxNjU2OTQ1MjM3LCJqdGkiOiJHU0YifQ.kZKeFtJW69IzHlYg3h-b6j57AinVjxryDn_ThAQbrT0';
        $response = Http::withHeaders([
            'Authorization' => $bearer,
        ])->post('http://181.188.132.73:5002/sincronizacion/sincronizacion?codigoPuntoVenta=1&codigoSucursal=0&cuis=911B454F&nit=388615026&operacion=productosServicios');
        $status = $response; 

        
        if($status['PRODUCTOS']) {  
            $items = $response['PRODUCTOS'];
            //Variables 
            $user = Auth::user()->id;
            $tableIsEmpty = SiatProductoServicio::all();
    
            if($tableIsEmpty->isEmpty()) 
            {
                foreach($items as $item) 
                {
                    $data = SiatActividadEconomica::where( 'codigo_caeb' , $item['codigoActividad'])->first();
                    $obj = new SiatProductoServicio();
                
                    $obj->actividad_id          = $data['id'];
                    $obj->codigo_producto       = $item['codigoProducto'];
                    $obj->descripcion_producto  = $item['descripcionProducto'];
                    $obj->usuario_alta          = $user;
                    $obj->usuario_modificacion  = $user;
                    $obj->sucursal= 0;
                    $obj->codigo_punto_venta= 1;
                    $obj->save();
    
                }
                return redirect('productservice')->with('success', 'Productos Servicios Sincronización - SIAT');
            }
            else
            {
                SiatProductoServicio::get()->each->delete();
                foreach($items as $item) 
                {
                    $data = SiatActividadEconomica::where( 'codigo_caeb' , $item['codigoActividad'])->first();
                    $obj = new SiatProductoServicio();
                
                    $obj->actividad_id          = $data['id'];
                    $obj->codigo_producto       = $item['codigoProducto'];
                    $obj->descripcion_producto  = $item['descripcionProducto'];
                    $obj->usuario_alta          = $user;
                    $obj->usuario_modificacion  = $user;
                    $obj->sucursal= 0;
                    $obj->codigo_punto_venta= 1;
                    $obj->save();
    
                }    
                return redirect('productservice')->with('success', 'Productos Servicios actualizada y sincronizada del SIAT');
            }
            
        }else{
            return redirect('productservice')->with('warning', 'SIAT en mantenimiento');
        }
        
    }
}
