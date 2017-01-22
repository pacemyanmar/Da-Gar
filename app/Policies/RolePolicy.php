<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the auth can view the role.
     *
     * @param  \App\User  $auth
     * @param  \App\Role  $role
     * @return mixed
     */
    public function index(User $auth)
    {
        return $auth->role->level == 9;
    }

    public function view(User $auth)
    {
        return $auth->role->level == 9;
    }

    /**
     * Determine whether the auth can create roles.
     *
     * @param  \App\User  $auth
     * @return mixed
     */
    public function create(User $auth)
    {
        return $auth->role->level == 9;
    }

    /**
     * Determine whether the auth can update the role.
     *
     * @param  \App\User  $auth
     * @param  \App\Role  $role
     * @return mixed
     */
    public function update(User $auth)
    {
        return false;
        //return $auth->role->level == 9;
    }

    /**
     * Determine whether the auth can delete the role.
     *
     * @param  \App\User  $auth
     * @param  \App\Role  $role
     * @return mixed
     */
    public function delete(User $auth)
    {
        return false;
        //return $auth->role->level == 9;
    }
}
