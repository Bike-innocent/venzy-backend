<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::all();

        $grouped = $permissions->groupBy(function ($permission) {
            return explode('.', $permission->name)[0]; // group by prefix
        })->map(function ($group) {
            return $group->map(function ($perm) {
                return [
                    'id' => $perm->id,
                    'name' => $perm->name,
                    'label' => ucwords(str_replace('.', ' → ', $perm->name)),
                ];
            });
        });

        return response()->json($grouped);
    }
}