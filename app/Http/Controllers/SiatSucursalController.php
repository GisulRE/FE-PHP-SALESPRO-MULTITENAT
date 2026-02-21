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
            $sucursales = SiatSucursal::where('empresa_id', $empresa_id)->paginate();
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

        // Mapear campos del formulario a las columnas reales de la tabla
        $data = [
            'codigo' => $request->input('sucursal'),
            'nombre' => $request->input('nombre'),
            'direccion' => $request->input('domicilio_tributario') ?? $request->input('descripcion_sucursal'),
            'departamento' => $request->input('departamento'),
            'email' => $request->input('email'),
            'telefono' => $request->input('telefono'),
            'ciudad' => $request->input('ciudad_municipio'),
            'estado' => $request->has('estado') ? (int)$request->input('estado') : 1,
            'empresa_id' => $user->company_id ?? null,
        ];

        SiatSucursal::create($data);
        return redirect('sucursal')->with('message', 'Sucursal creada correctamente');
    }

    public function edit(SiatSucursal $sucursal)
    {
        return view('siat-sucursal.edit', ['sucursal' => $sucursal]);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();

        $data = [
            'codigo' => $request->input('sucursal'),
            'nombre' => $request->input('nombre'),
            'direccion' => $request->input('domicilio_tributario') ?? $request->input('descripcion_sucursal'),
            'departamento' => $request->input('departamento'),
            'email' => $request->input('email'),
            'telefono' => $request->input('telefono'),
            'ciudad' => $request->input('ciudad_municipio'),
            'estado' => $request->has('estado') ? (int)$request->input('estado') : 1,
            'empresa_id' => $user->company_id ?? null,
        ];

        $update_data = SiatSucursal::find($id);
        $update_data->update($data);
        return redirect('sucursal')->with('message', 'Sucursal actualizada correctamente');
    }

    public function destroy($id)
    {
        $msj = '';
        $item_sucursal = SiatSucursal::find($id);
        // Usar la columna `estado`
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
