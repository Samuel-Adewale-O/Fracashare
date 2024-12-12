<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        //
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Define gates for admin access
        Gate::define('manage-assets', function ($user) {
            return $user->hasRole(['admin', 'asset_manager']);
        });

        Gate::define('manage-users', function ($user) {
            return $user->hasRole('admin');
        });
    }
}