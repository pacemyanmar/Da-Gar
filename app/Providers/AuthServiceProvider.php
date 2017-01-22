<?php

namespace App\Providers;

use App\Models\Project;
use App\Models\Role;
use App\Models\SampleData;
use App\Models\Setting;
use App\Models\User;
use App\Policies\ProjectPolicy;
use App\Policies\RolePolicy;
use App\Policies\SampleDataPolicy;
use App\Policies\SettingPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Role::class => RolePolicy::class,
        Setting::class => SettingPolicy::class,
        SampleData::class => SampleDataPolicy::class,
        Project::class => ProjectPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
