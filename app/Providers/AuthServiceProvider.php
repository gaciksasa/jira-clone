<?php

namespace App\Providers;

use App\Models\Project;
use App\Policies\ProjectPolicy;
use App\Models\VacationRequest;
use App\Policies\VacationRequestPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Project::class => ProjectPolicy::class,
        VacationRequest::class => VacationRequestPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define gates for specific permissions
        Gate::define('manage-users', function ($user) {
            return $user->hasPermissionTo('manage users');
        });

        Gate::define('manage-projects', function ($user) {
            return $user->hasPermissionTo('manage projects');
        });
    }
}