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
            $empresa_id = Auth::user()->company_id;
            $sucursales = SiatSucursal::where('id_empresa', $empresa_id)->paginate();
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
        $user = Auth::user();

        $data = $request->only([
            'sucursal', 'nombre', 'descripcion_sucursal', 'domicilio_tributario',
            'ciudad_municipio', 'telefono', 'email', 'departamento', 'estado',
        ]);
        $data['id_empresa']    = $user->company_id ?? null;
        $data['usuario_alta']  = $user->id;

        SiatSucursal::create($data);
        return redirect('sucursal')->with('message', 'Sucursal creada correctamente');
    }

    public function edit(SiatSucursal $sucursal)
    {
        return view('siat-sucursal.edit', ['sucursal' => $sucursal]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->only([
            'sucursal', 'nombre', 'descripcion_sucursal', 'domicilio_tributario',
            'ciudad_municipio', 'telefono', 'email', 'departamento', 'estado',
        ]);

        $sucursal = SiatSucursal::findOrFail($id);
        $sucursal->update($data);
        return redirect('sucursal')->with('message', 'Sucursal actualizada correctamente');
    }

    public function destroy($id)
    {
        $item_sucursal = SiatSucursal::findOrFail($id);
        if ($item_sucursal->estado == 1) {
            $item_sucursal->estado = 0;
            $msj = 'baja';
        } else {
            $item_sucursal->estado = 1;
            $msj = 'alta';
        }
        $item_sucursal->save();
        return redirect('sucursal')->with('message', 'Sucursal dado de ' . $msj);
    }
}
