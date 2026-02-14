<?php

namespace App\Http\Controllers;

use App\UrlWs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class UrlWsController extends Controller
{
    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('urlws_siat')) {
            $items = UrlWs::paginate();
            return view('url-ws.index', ['items' => $items]);
        } else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');

    }

    public function create()
    {
        return view('url-ws.create', ['item' => new UrlWs()]);
    }

    public function store(Request $request)
    {
        $user = Auth::user()->id;
        $data = $request->all();
        $data['usuario_alta'] = $user;
        UrlWs::create($data);
        return redirect('url-ws')->with('message', 'URL-WS creada correctamente');
    }

    public function edit($id)
    {
        $item = UrlWs::find($id);
        return view('url-ws.edit', ['item' => $item]);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user()->id;
        $data = $request->all();
        $data['usuario_alta'] = $user;

        $update_data = UrlWs::find($id);
        $update_data->update($data);
        return redirect('url-ws')->with('message', 'URL-WS actualizada correctamente');
    }
}
