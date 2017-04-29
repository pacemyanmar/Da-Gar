<?php

namespace App\Policies;

use App\Models\SmsLog;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SmsPolicy
{
    use HandlesAuthorization;

    public function index(User $auth)
    {
        return $auth->role->level == 9;
    }

    /**
     * Determine whether the user can view the smsLog.
     *
     * @param  \App\User  $user
     * @param  \App\SmsLog  $smsLog
     * @return mixed
     */
    public function view(User $user, SmsLog $smsLog)
    {
        //
    }

    /**
     * Determine whether the user can create smsLogs.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the smsLog.
     *
     * @param  \App\User  $user
     * @param  \App\SmsLog  $smsLog
     * @return mixed
     */
    public function update(User $user, SmsLog $smsLog)
    {
        //
    }

    /**
     * Determine whether the user can delete the smsLog.
     *
     * @param  \App\User  $user
     * @param  \App\SmsLog  $smsLog
     * @return mixed
     */
    public function delete(User $user, SmsLog $smsLog)
    {
        //
    }
}
