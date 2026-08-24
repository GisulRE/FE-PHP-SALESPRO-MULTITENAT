<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Department;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class DepartmentController extends Controller
{
    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('department')) {
            $lims_department_all = Department::where('is_active', true)->get();
            return view('department.index', compact('lims_department_all'));
        }
        return redirect()->back()->with('not_permitted', '¡Lo sentimos! No tienes permiso para acceder a este módulo');
    }

    public function store(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $this->validate($request, [
            'name' => [
                'required',
                'max:255',
                Rule::unique('departments')->where(function ($query) use ($companyId) {
                    return $query->where('is_active', 1)
                                 ->where('company_id', $companyId);
                }),
            ],
        ]);

        $data = $request->all();
        $data['is_active'] = true;
        $data['company_id'] = $companyId;
        Department::create($data);
        return redirect('departments')->with('message', 'Department created successfully');
    }

    public function update(Request $request, $id)
    {
        $companyId = Auth::user()->company_id;

        $this->validate($request, [
            'name' => [
                'required',
                'max:255',
                Rule::unique('departments')->ignore($request->department_id)->where(function ($query) use ($companyId) {
                    return $query->where('is_active', 1)
                                 ->where('company_id', $companyId);
                }),
            ],
        ]);

        $data = $request->all();
        $lims_department_data = Department::find($data['department_id']);
        $lims_department_data->update($data);
        return redirect('departments')->with('message', 'Department updated successfully');
    }

    public function deleteBySelection(Request $request)
    {
        $department_id = $request['departmentIdArray'];
        foreach ($department_id as $id) {
            $lims_department_data = Department::find($id);
            if ($lims_department_data) {
                $lims_department_data->is_active = false;
                $lims_department_data->save();
            }
        }
        return 'Department deleted successfully!';
    }

    public function destroy($id)
    {
        $lims_department_data = Department::find($id);
        if ($lims_department_data) {
            $lims_department_data->is_active = false;
            $lims_department_data->save();
        }
        return redirect('departments')->with('message', 'Department deleted successfully');
    }
}
