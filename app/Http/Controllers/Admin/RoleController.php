<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $currentUser = auth()->user();
        $isSuperAdmin = $currentUser->role && strtolower($currentUser->role->name) === 'super-admin';

        $roles = Role::with(['permissions.module', 'permissions.action'])
            ->when(!$isSuperAdmin, function ($query) use ($currentUser) {
                $query->where('id', $currentUser->role_id);
            })
            ->orderBy('id', 'DESC')
            ->get();

        // Return structured data for the matrix
        $modules = \App\Models\Module::with(['permissions.module', 'permissions.action'])->get();

        return response()->json([
            'roles' => $roles,
            'modules' => $modules
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:roles,name',
        ]);

        $role = Role::create(['name' => $request->input('name')]);
        $role->syncPermissions($request->input('permission'));

        return response()->json(['success' => 'Role created successfully', 'role' => $role]);
    }

    public function show($id)
    {
        $role = Role::with(['permissions.module', 'permissions.action'])->find($id);

        return response()->json([
            'role' => $role,
            'rolePermissions' => $role->permissions
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'permission' => 'required',
        ]);

        $role = Role::find($id);
        $role->name = $request->input('name');
        $role->save();

        $role->syncPermissions($request->input('permission'));

        return response()->json(['success' => 'Role updated successfully', 'role' => $role]);
    }

    public function destroy($id)
    {
        Role::find($id)->delete();
        return response()->json(['success' => 'Role deleted successfully']);
    }
}
