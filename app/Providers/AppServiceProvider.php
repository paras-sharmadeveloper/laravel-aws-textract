<?php

namespace App\Providers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

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
        //
         Schema::defaultStringLength(191);

        foreach (array_keys(Role::PERMISSIONS) as $permission) {
            Gate::define($permission, fn(User $user) => $user->hasPermission($permission));
        }
    }
}
