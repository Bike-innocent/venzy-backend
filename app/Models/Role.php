<?php

namespace App\Models;

use Spatie\Permission\Models\Role as RoleBase;


class Role extends RoleBase // extend from Spatie's Role
{
    public function users()
    {
        return $this->morphedByMany(\App\Models\User::class, 'model', 'model_has_roles', 'role_id', 'model_id');
    }
}