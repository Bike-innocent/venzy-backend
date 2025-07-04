<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

class AdminUserRoleController extends Controller
{
    // 1. List users with their roles
    public function index()
    {
        $users = User::with('roles')->get();

        $data = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
            ];
        });

        return response()->json($data);
    }

    // 2. Show roles for a specific user
    public function showRoles($id)
    {
        $user = User::with('roles')->findOrFail($id);
        return response()->json([
            'roles' => $user->roles->pluck('name'),
        ]);
    }

    // 3. Assign roles to user
    // public function assignRoles(Request $request, $id)
    // {
    //     $user = User::findOrFail($id);

    //     $request->validate([
    //         'roles' => 'required|array',
    //         'roles.*' => 'exists:roles,name',
    //     ]);

    //     $user->syncRoles($request->roles); // overwrites existing roles

    //     return response()->json(['message' => 'Roles updated']);
    // }









    public function assignRoles(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);

        $isTryingToAssignAdmin = in_array('admin', $request->roles);

        // If assigning admin, check if someone else is already admin
        if ($isTryingToAssignAdmin && $user->hasRole('admin') === false) {
            $adminExists = User::role('admin')->exists(); // Spatie helper

            if ($adminExists) {
                return response()->json(['message' => 'There can only be one admin.'], 403);
            }
        }

        $user->syncRoles($request->roles); // Overwrites existing roles

        return response()->json(['message' => 'Roles updated']);
    }


    // 4. Revoke a specific role from user
    public function revokeRole($id, $roleName)
{
    $user = User::findOrFail($id);

    // Prevent current user from revoking their own admin role
    if (auth()->id() === $user->id && $roleName === 'admin') {
        return response()->json(['message' => 'You cannot revoke your own admin role.'], 403);
    }

    $user->removeRole($roleName);

    return response()->json(['message' => 'Role revoked']);
}

}