<?php

namespace App\Http\Controllers;

use App\SiatSucursal;
use Illuminate\Http\Request;
use App\AutorizacionFacturacion;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class SiatSucursalController extends Controller
{
    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('sucursal_siat')) {
            $sucursales = SiatSucursal::paginate();
            return view('siat-sucursal.index', ['sucursales' => $sucursales]);
        } else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function create()
    {
        return view('siat-sucursal.create', ['sucursal' => new SiatSucursal()]);
    }

    public function store(Request $request)
    {
        $user = Auth::user()->id;
        $data = $request->all();
        $data['usuario_alta'] = $user;
        SiatSucursal::create($data);
        return redirect('sucursal')->with('message', 'Sucursal creada correctamente');
    }

    public function edit(SiatSucursal $sucursal)
    {
        return view('siat-sucursal.edit', ['sucursal' => $sucursal]);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user()->id;
        $data = $request->all();
        $data['usuario_modificacion'] = $user;

        $update_data = SiatSucursal::find($id);
        $update_data->update($data);
        return redirect('sucursal')->with('message', 'Sucursal actualizada correctamente');
    }

    public function destroy($id)
    {
        $msj = '';
        $item_sucursal = SiatSucursal::find($id);
        if ($item_sucursal->estado == true) {
            $item_sucursal->estado = false;
            $msj = 'baja';
        } else {
            $item_sucursal->estado = true;
            $msj = 'alta';
        }

        $item_sucursal->save();
        return redirect('sucursal')->with('message', 'Sucursal dado de ' . $msj);
    }
}
