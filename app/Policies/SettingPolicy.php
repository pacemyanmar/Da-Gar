<?php

namespace App\Policies;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SettingPolicy
{
    use HandlesAuthorization;

    public function index(User $auth)
    {
        return $auth->role->level > 8;
    }

    /**
     * Determine whether the auth can view the setting.
     *
     * @param  \App\User  $auth
     * @param  \App\Setting  $setting
     * @return mixed
     */
    public function view(User $auth, Setting $setting)
    {
        return $auth->role->level > 8;
    }

    /**
     * Determine whether the auth can create settings.
     *
     * @param  \App\User  $auth
     * @return mixed
     */
    public function create(User $auth)
    {
        return $auth->role->level > 8;
    }

    /**
     * Determine whether the auth can update the setting.
     *
     * @param  \App\User  $auth
     * @param  \App\Setting  $setting
     * @return mixed
     */
    public function update(User $auth, Setting $setting)
    {
        return $auth->role->level > 8;
    }

    /**
     * Determine whether the auth can delete the setting.
     *
     * @param  \App\User  $auth
     * @param  \App\Setting  $setting
     * @return mixed
     */
    public function delete(User $auth, Setting $setting)
    {
        return $auth->role->level > 8;
    }
}
