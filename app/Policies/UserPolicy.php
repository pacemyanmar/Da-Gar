<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the user.
     *
     * @param  \App\User  $auth
     * @param  \App\User  $user
     * @return mixed
     */
    public function index(User $auth)
    {
        return $auth->role->level > 8;
    }

    /**
     * Determine whether the user can view the user.
     *
     * @param  \App\User  $auth
     * @param  \App\User  $user
     * @return mixed
     */
    public function view(User $auth, User $user)
    {
        return ($auth->role->level === 9 || $auth->id == $user->id || $auth->role->level > $user->role->level);
    }

    /**
     * Determine whether the user can create users.
     *
     * @param  \App\User  $auth
     * @return mixed
     */
    public function create(User $auth)
    {
        return $auth->role->level > 8;
    }

    /**
     * Determine whether the user can update the user.
     *
     * @param  \App\User  $auth
     * @param  \App\User  $user
     * @return mixed
     */
    public function update(User $auth, User $user)
    {
        return ($auth->role->level === 9 || $auth->id == $user->id || $auth->role->level > $user->role->level);
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  \App\User  $auth
     * @param  \App\User  $user
     * @return mixed
     */
    public function delete(User $auth, User $user)
    {
        if ($auth->role->level === 9 && $auth->id != $user->id) {
            return true;
        }
        return $auth->role->level > $user->role->level;
    }
}
