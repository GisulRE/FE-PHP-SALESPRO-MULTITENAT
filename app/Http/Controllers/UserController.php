<?php

namespace App\Http\Controllers;

use App\Category;
use App\UserCategory;
use Illuminate\Http\Request;
use App\User;
use App\Roles;
use App\Biller;
use App\Warehouse;
use App\Company;
use Auth;
use Hash;
use Keygen;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Mail\UserNotification;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{

    public function index()
    {
        $user = Auth::user();
        $permissions = is_array(session('permissions')) ? session('permissions') : [];
        $hasPermission = ($user && $user->role_id <= 2) || in_array('users-index', $permissions);

        if ($hasPermission) {
            $all_permission = !empty($permissions) ? $permissions : ['users-index', 'users-add', 'users-edit', 'users-delete'];
            $lims_user_list = User::where('is_deleted', false)->get();
            $categories = Category::select('id', 'name')->where('is_active', true)->get();
            return view('user.index', compact('lims_user_list', 'all_permission', 'categories'));
        } else {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }
    }

    public function create()
    {
        $user = Auth::user();
        $permissions = is_array(session('permissions')) ? session('permissions') : [];
        $hasPermission = ($user && $user->role_id <= 2) || in_array('users-add', $permissions);

        if ($hasPermission) {
            $lims_role_list = Roles::where('is_active', true)->get();
            $lims_biller_list = Biller::select('id', 'name', 'company_name')->where('is_active', true)->get();
            $lims_warehouse_list = Warehouse::select('id', 'name')->where('is_active', true)->get();
            $lims_company_list = Company::all();
            return view('user.create', compact('lims_role_list', 'lims_biller_list', 'lims_warehouse_list', 'lims_company_list'));
        } else {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }
    }

    public function generatePassword()
    {
        $id = Keygen::numeric(6)->generate();
        return $id;
    }

    public function store(Request $request)
    {
        $companyId = $request->input('company_id') ?: (Auth::user() ? Auth::user()->company_id : null);

        $this->validate($request, [
            'name' => [
                'required',
                'max:255',
                Rule::unique('users')->where(function ($query) use ($companyId) {
                    $q = $query->where('is_deleted', false);
                    if ($companyId) {
                        $q->where('company_id', $companyId);
                    }
                    return $q;
                }),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->where(function ($query) use ($companyId) {
                    $q = $query->where('is_deleted', false);
                    if ($companyId) {
                        $q->where('company_id', $companyId);
                    }
                    return $q;
                }),
            ],
        ]);

        $data = $request->all();
        $message = 'User created successfully';
        try {
            Mail::send('mail.user_details', $data, function ($message) use ($data) {
                $message->to($data['email'])->subject('User Account Details');
            });
        } catch (\Exception $e) {
            $message = 'User created successfully.';
        }
        if (!isset($data['is_active']))
            $data['is_active'] = false;
        $data['is_deleted'] = false;
        if (!empty($data['password']))
            $data['password'] = bcrypt($data['password']);
        
        if (isset($data['biller_id']) && $data['biller_id'] === '') $data['biller_id'] = null;
        if (isset($data['company_id']) && $data['company_id'] === '') $data['company_id'] = null;
        if (isset($data['warehouse_id']) && $data['warehouse_id'] === '') $data['warehouse_id'] = null;

        $user = User::create($data);
        if ($user && !empty($data['role_id'])) {
            $role = Role::find($data['role_id']);
            if ($role) {
                $user->syncRoles([$role->name]);
            }
        }
        return redirect('user')->with('message1', $message);
    }

    public function edit($id)
    {
        $user = Auth::user();
        $permissions = is_array(session('permissions')) ? session('permissions') : [];
        $hasPermission = ($user && $user->role_id <= 2) || in_array('users-edit', $permissions);

        if ($hasPermission) {
            $lims_user_data = User::find($id);
            if (!$lims_user_data) {
                return redirect('user')->with('not_permitted', 'Usuario no encontrado');
            }
            $lims_role_list = Roles::where('is_active', true)->get();
            $lims_biller_list = Biller::select('id', 'name', 'company_name')->where('is_active', true)->get();
            $lims_warehouse_list = Warehouse::select('id', 'name')->where('is_active', true)->get();
            $lims_company_list = Company::all();
            return view('user.edit', compact('lims_user_data', 'lims_role_list', 'lims_biller_list', 'lims_warehouse_list', 'lims_company_list'));
        } else {
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
        }
    }

    public function update(Request $request, $id)
    {
        $currentUser = User::find($id);
        $companyId = $request->input('company_id') ?: ($currentUser ? $currentUser->company_id : (Auth::user() ? Auth::user()->company_id : null));

        $this->validate($request, [
            'name' => [
                'required',
                'max:255',
                Rule::unique('users')->ignore($id)->where(function ($query) use ($companyId) {
                    $q = $query->where('is_deleted', false);
                    if ($companyId) {
                        $q->where('company_id', $companyId);
                    }
                    return $q;
                }),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($id)->where(function ($query) use ($companyId) {
                    $q = $query->where('is_deleted', false);
                    if ($companyId) {
                        $q->where('company_id', $companyId);
                    }
                    return $q;
                }),
            ],
        ]);

        try {
            $input = $request->except(['password', '_token', '_method']);
            if (!isset($input['is_active']))
                $input['is_active'] = false;
            if (!empty($request['password']))
                $input['password'] = bcrypt($request['password']);

            if (isset($input['biller_id']) && $input['biller_id'] === '') $input['biller_id'] = null;
            if (isset($input['company_id']) && $input['company_id'] === '') $input['company_id'] = null;
            if (isset($input['warehouse_id']) && $input['warehouse_id'] === '') $input['warehouse_id'] = null;

            $lims_user_data = User::find($id);
            if (!$lims_user_data) {
                return redirect('user')->with('not_permitted', 'Usuario no encontrado');
            }

            $lims_user_data->update($input);

            if (!empty($input['role_id'])) {
                $role = Role::find($input['role_id']);
                if ($role) {
                    $lims_user_data->syncRoles([$role->name]);
                }
            }

            return redirect('user')->with('message2', 'Usuario actualizado correctamente');
        } catch (\Throwable $e) {
            \Log::error('Error actualizando usuario ' . $id . ': ' . $e->getMessage());
            return redirect()->back()->withInput()->with('not_permitted', 'Error al actualizar usuario: ' . $e->getMessage());
        }
    }

    public function profile($id)
    {
        $lims_user_data = User::find($id);
        return view('user.profile', compact('lims_user_data'));
    }

    public function profileUpdate(Request $request, $id)
    {
        $input = $request->all();
        $lims_user_data = User::find($id);
        $lims_user_data->update($input);
        return redirect()->back()->with('message3', 'Data updated successfullly');
    }

    public function changePassword(Request $request, $id)
    {
        $input = $request->all();
        $lims_user_data = User::find($id);
        if ($input['new_pass'] != $input['confirm_pass'])
            return redirect("user/" . "profile/" . $id)->with('message2', "Please Confirm your new password");

        if (Hash::check($input['current_pass'], $lims_user_data->password)) {
            $lims_user_data->password = bcrypt($input['new_pass']);
            $lims_user_data->save();
        } else {
            return redirect("user/" . "profile/" . $id)->with('message1', "Current Password doesn't match");
        }
        auth()->logout();
        return redirect('/');
    }

    public function deleteBySelection(Request $request)
    {
        $user_id = $request['userIdArray'];
        foreach ($user_id as $id) {
            $lims_user_data = User::find($id);
            $lims_user_data->is_deleted = true;
            $lims_user_data->is_active = false;
            $lims_user_data->save();
        }
        return 'User deleted successfully!';
    }

    public function destroy($id)
    {
        $lims_user_data = User::find($id);
        $lims_user_data->is_deleted = true;
        $lims_user_data->is_active = false;
        $lims_user_data->save();
        if (Auth::id() == $id) {
            auth()->logout();
            return redirect('/login');
        } else
            return redirect('user')->with('message3', 'Data deleted successfullly');
    }

    public function permissionCategory($id)
    {
        $list = UserCategory::where('user_id', $id)->get();
        return response()->json(['categories' => $list, 'estado' => 200], 200);
    }

    public function permission(Request $request)
    {
        $data = $request->all();
        if (array_key_exists('categories', $data)) {
            $list_categories = $data['categories'];
            UserCategory::where("user_id", $data['user_id'])->delete();
            foreach ($list_categories as $category) {
                $data_m['user_id'] = $data['user_id'];
                $data_m['category_id'] = $category;
                UserCategory::create($data_m);
            }
        } else {
            UserCategory::where("user_id", $data['user_id'])->delete();
        }
        return redirect('user')->with('message2', 'Permisos Categoria Actualizados');
    }
}