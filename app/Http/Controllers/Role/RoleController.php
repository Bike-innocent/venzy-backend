<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use Illuminate\Support\Collection;

use Illuminate\Http\Request;

class RoleController extends Controller
{




    public function index()
    {
        $roles = Role::all()->map(function ($role) {
            $count = \DB::table('model_has_roles')
                ->where('role_id', $role->id)
                ->where('model_type', \App\Models\User::class)
                ->count();

            return [
                'id' => $role->id,
                'name' => $role->name,
                'users_count' => $count,
            ];
        });

        return response()->json($roles);
    }





    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        // Force the guard
        $role = \Spatie\Permission\Models\Role::create([
            'name' => $request->name,
            'guard_name' => 'web',
        ]);

        if ($request->permissions) {
            // Explicitly resolve each permission using the web guard
            $permissions = collect($request->permissions)->map(function ($permName) {
                return Permission::findByName($permName, 'web');
            });

            $role->syncPermissions($permissions);
        }

        return response()->json(['message' => 'Role created']);
    }



    public function show($id)
    {
        $role = Role::with('permissions')->findOrFail($id);

        return response()->json([
            'id' => $role->id,
            'name' => $role->name,
            'permissions' => $role->permissions->pluck('name'),
        ]);
    }

  


    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        if ($role->name === 'admin') {
            return response()->json([
                'message' => 'The admin role cannot be edited.'
            ], 403);
        }

        $request->validate([
            'name' => 'required|unique:roles,name,' . $id,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->update([
            'name' => $request->name,
            'guard_name' => 'web',
        ]);

        if ($request->permissions) {
            $permissions = collect($request->permissions)->map(function ($permName) {
                return Permission::findByName($permName, 'web');
            });

            $role->syncPermissions($permissions);
        }

        return response()->json(['message' => 'Role updated']);
    }







    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        if ($role->name === 'admin') {
            return response()->json([
                'message' => 'The admin role cannot be deleted.'
            ], 403);
        }

        $role->delete();

        return response()->json(['message' => 'Role deleted']);
    }
}