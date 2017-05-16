<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
{
    use HandlesAuthorization;

    public function index(User $auth)
    {
        return $auth->role->level > 3;
    }
    /**
     * Determine whether the auth can view the project.
     *
     * @param  \App\User  $auth
     * @param  \App\Models\Project  $project
     * @return mixed
     */
    public function view(User $auth, Project $project)
    {
        return $auth->role->level > 5;
    }

    /**
     * Determine whether the auth can create projects.
     *
     * @param  \App\User  $auth
     * @return mixed
     */
    public function create(User $auth)
    {
        return $auth->role->level > 7;
    }

    /**
     * Determine whether the auth can update the project.
     *
     * @param  \App\User  $auth
     * @param  \App\Models\Project  $project
     * @return mixed
     */
    public function update(User $auth, Project $project)
    {
        return $auth->role->level > 7;
    }

    /**
     * Determine whether the auth can delete the project.
     *
     * @param  \App\User  $auth
     * @param  \App\Models\Project  $project
     * @return mixed
     */
    public function delete(User $auth, Project $project)
    {
        return $auth->role->level > 7;
    }
}
