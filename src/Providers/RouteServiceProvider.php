<?php

namespace Shahnewaz\PermissibleNg\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Route::macro('roles', function ($roles) {
            $roles = is_array($roles) ? $roles : [$roles];
            return $this->middleware('role:' . implode('|', $roles));
        });

        Route::macro('permissions', function ($permissions) {
            $permissions = is_array($permissions) ? $permissions : [$permissions];
            return $this->middleware('permissions:' . implode('|', $permissions));
        });
    }
} 