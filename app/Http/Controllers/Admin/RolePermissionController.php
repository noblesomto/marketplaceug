<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Admin;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    public function index()
    {
        $title = "Admin Roles - " . config('global.site_name');
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();
        $admins = Admin::with('roles')->get();

        return view('backend.roles.roles', compact('roles','permissions','admins','title'));
    }

    public function storeRole(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name'
        ]);

        Role::create(['name' => $request->name]);

        return back()->with('success', 'Role created successfully!');
    }

    public function storePermission(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name'
        ]);

        Permission::create(['name' => $request->name]);

        return back()->with('success', 'Permission created successfully!');
    }

    public function assignPermission(Request $request, $roleId)
    {
        $role = Role::findOrFail($roleId);
        $role->permissions()->sync($request->permissions ?? []);
        return back()->with('success', 'Permissions updated for role: '.$role->name);
    }

    public function assignRoleToAdmin(Request $request, $adminId)
    {
        $admin = Admin::findOrFail($adminId);
        $admin->roles()->sync($request->roles ?? []);
        return back()->with('success', 'Roles updated for admin: '.$admin->username);
    }
}
