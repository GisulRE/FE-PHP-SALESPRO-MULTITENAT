<?php

namespace App\Http\Controllers;

use Log;
use Carbon\Carbon;
use App\SiatSucursal;
use App\CredencialCafc;
use App\ControlContingencia;
use Illuminate\Http\Request;
use App\SiatParametricaVario;
use Illuminate\Support\Facades\Session;


class CredencialCafcController extends Controller
{
    public function index()
    {
        $fecha_actual = new Carbon();
        $sucursales = SiatSucursal::where('estado', true)->get();
        $lista_cafc = CredencialCafc::get();
        $parametricas = SiatParametricaVario::where( 'tipo_clasificador' ,'eventosSignificativos')->get();

        return view('credencial_cafc.index', [ 
            'sucursales' => $sucursales,
            'parametricas' => $parametricas,
            
            'lista_cafc' => $lista_cafc,
            'fecha_actual' => $fecha_actual,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $tipo_factura_lookup = [
                'compra-venta' => 1,
                'alquiler' => 2,
                'servicio-basico' => 13,
            ];
            $codigo_sector = $tipo_factura_lookup[ $data['tipo_factura'] ] ?? 1;
            
            $data['codigo_documento_sector'] = $codigo_sector;
            $data['is_active'] = true;
            $data['correlativo_factura'] = $data['nro_min'];

            CredencialCafc::create($data);

            $mensaje = 'CAFC registrado correctamente!';
        } catch (\Throwable $th) {
            $mensaje = 'Error, no se logró registrar el cafc.';
        }
        Session::flash('info', $mensaje);
        return redirect('credencial-cafc');
    }

    // Cambia el estado de la credencial, más no la elimina
    public function destroy($id)
    {
        $msj = '';
        $item_cafc = CredencialCafc::find($id);
        if ($item_cafc->is_active == true) {
            $item_cafc->is_active = false;
            $msj = 'baja';
        }else {
            $item_cafc->is_active = true;
            $msj = 'alta';
        }
        $item_cafc->save();
        return redirect('credencial-cafc')->with('message', 'Credencial Cafc dado de '. $msj);
    }
}
