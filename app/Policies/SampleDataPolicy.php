<?php

namespace App\Policies;

use App\Models\SampleData;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SampleDataPolicy
{
    use HandlesAuthorization;

    public function index(User $auth)
    {
        return $auth->role->level > 8;
    }
    /**
     * Determine whether the auth can view the sampleData.
     *
     * @param  \App\User  $auth
     * @param  \App\SampleData  $sampleData
     * @return mixed
     */
    public function view(User $auth, SampleData $sampleData)
    {
        return $auth->role->level > 8;
    }

    /**
     * Determine whether the auth can create sampleDatas.
     *
     * @param  \App\User  $auth
     * @return mixed
     */
    public function create(User $auth)
    {
        return $auth->role->level > 8;
    }

    /**
     * Determine whether the auth can update the sampleData.
     *
     * @param  \App\User  $auth
     * @param  \App\SampleData  $sampleData
     * @return mixed
     */
    public function update(User $auth, SampleData $sampleData)
    {
        return $auth->role->level > 8;
    }

    /**
     * Determine whether the auth can delete the sampleData.
     *
     * @param  \App\User  $auth
     * @param  \App\SampleData  $sampleData
     * @return mixed
     */
    public function delete(User $auth, SampleData $sampleData)
    {
        return $auth->role->level > 8;
    }
}
