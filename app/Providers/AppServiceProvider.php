<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Role-based authorization Gates (UTS Slide 5 & Slide 14)
        Gate::define('manage-master-data', fn (User $user) => $user->isAdmin());
        Gate::define('manage-users', fn (User $user) => $user->isAdmin());
        Gate::define('cancel-transaction', fn (User $user) => $user->isAdmin());
        Gate::define('manage-purchases', fn (User $user) => in_array($user->role, ['admin', 'staff', 'customer'], true));
        Gate::define('manage-sales', fn (User $user) => in_array($user->role, ['admin', 'staff', 'customer'], true));
    }
}
