<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Company;
use Auth;
use Spatie\Permission\Models\Role;

class CompanyController extends Controller
{
    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('users-index')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if (empty($all_permission))
                $all_permission[] = 'dummy';

            $companies = Company::orderBy('id', 'desc')->get();
            return view('company.index', compact('companies', 'all_permission'));
        } else {
            return redirect()->back()->with('not_permitted', '¡Lo sentimos! No tienes permiso para acceder a este módulo');
        }
    }

    public function create()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('users-add')) {
            return view('company.create');
        } else {
            return redirect()->back()->with('not_permitted', '¡Lo sentimos! No tienes permiso para acceder a este módulo');
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:150|unique:companies,name',
        ]);

        $data = $request->all();
        Company::create($data);

        return redirect('companies')->with('create_message', 'Empresa creada exitosamente');
    }

    public function edit($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('users-edit')) {
            $company = Company::findOrFail($id);
            return view('company.edit', compact('company'));
        } else {
            return redirect()->back()->with('not_permitted', '¡Lo sentimos! No tienes permiso para acceder a este módulo');
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|max:150|unique:companies,name,' . $id,
        ]);

        $company = Company::findOrFail($id);
        $company->update($request->all());

        return redirect('companies')->with('edit_message', 'Empresa actualizada exitosamente');
    }

    public function destroy($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('users-delete')) {
            $company = Company::findOrFail($id);
            $company->delete();
            return redirect('companies')->with('delete_message', 'Empresa eliminada exitosamente');
        } else {
            return redirect()->back()->with('not_permitted', '¡Lo sentimos! No tienes permiso para acceder a este módulo');
        }
    }
}
