<?php

namespace App\Http\Controllers;

use App\MethodPayment;
use Illuminate\Http\Request;
use App\SiatParametricaVario;

class MethodPaymentController extends Controller
{
    public function index()
    {
        $lista_metodo_pago = MethodPayment::get();
        $lista_parametros_siat = SiatParametricaVario::where('tipo_clasificador','tipoMetodoPago')->get();
        return view('method-payment.index', compact('lista_metodo_pago','lista_parametros_siat'));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        MethodPayment::create($data);
        return redirect('method_payment')->with('message', 'Método de Pago creado correctamente');
    }

    public function edit($id)
    {
        $method_edit = MethodPayment::findOrFail($id);
        return $method_edit;
    }

    public function update(Request $request, $id)
    {        
        $data = $request->all();
        $update_data = MethodPayment::find($request->method_payment_id);
        $update_data->update($data);
        return redirect('method_payment')->with('message', 'Método de Pago actualizado correctamente');
    }

    public function destroy($id)
    {
        $msj = '';
        $item_autorizacion = MethodPayment::find($id);
        if ($item_autorizacion->estado == true) {
            $item_autorizacion->estado = false;
            $msj = 'baja';
        }else {
            $item_autorizacion->estado = true;
            $msj = 'alta';
        }
        $item_autorizacion->save();
        return redirect('autorizacion')->with('message', 'Método de Pago dado de '.$msj);
    }
}
