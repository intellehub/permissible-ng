<?php

namespace Shahnewaz\PermissibleNg\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Route as Router;
use Illuminate\Support\Arr;
use Shahnewaz\PermissibleNg\Console\Commands\Setup;
use Shahnewaz\PermissibleNg\Contracts\PermissibleAuthInterface;
use Shahnewaz\PermissibleNg\Facades\PermissibleAuth;
use Shahnewaz\PermissibleNg\Services\PermissibleService;
use Shahnewaz\PermissibleNg\Console\Commands\RolePermissionSeed;

class PermissibleServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function register(): void 
    {
        $this->mergeConfigFrom($this->packagePath('config/permissible.php'), 'permissible');
        
        // Register middleware first
        $this->app['router']->aliasMiddleware(
            'roles', \Shahnewaz\PermissibleNg\Http\Middleware\RoleAccessGuard::class
        );
        $this->app['router']->aliasMiddleware(
            'permissions', \Shahnewaz\PermissibleNg\Http\Middleware\PermissionAccessGuard::class
        );

        // Bind the interface to the concrete implementation
        $this->app->singleton(PermissibleAuthInterface::class, PermissibleService::class);

        // Bind the facade accessor
        $this->app->singleton('permissible.auth', function ($app) {
            return $app->make(PermissibleService::class);
        });

        $loader = AliasLoader::getInstance();
        $loader->alias('PermissibleAuth', PermissibleAuth::class);
    }

    public function boot(): void 
    {
        $this->registerMacroHelpers();

        if ($this->app->runningInConsole()) {
            $this->commands([
                RolePermissionSeed::class,
                Setup::class
            ]);
        }

        $this->load();
        $this->publish();
    }

    protected function registerMacroHelpers(): void
    {
        if (!method_exists(Router::class, 'macro')) { // Check for Lumen
            return;
        }

        Router::macro('roles', function ($roles = []) {
            /** @var Router $this */
            return $this->middleware('roles:' . implode('|', Arr::wrap($roles)));
        });

        Router::macro('permissions', function ($permissions = []) {
            /** @var Router $this */
            return $this->middleware('permissions:' . implode('|', Arr::wrap($permissions)));
        });
    }

    private function packagePath($path): string
    {
        return __DIR__ . "/../../$path";
    }

    private function load(): void
    {
        $this->loadRoutesFrom($this->packagePath('src/routes/api.php'));
        $this->loadMigrationsFrom($this->packagePath('database/migrations'));
        $this->loadTranslationsFrom($this->packagePath('resources/lang'), 'permissible');
    }

    private function publish(): void
    {
        $this->publishes([
            $this->packagePath('resources/lang') => resource_path('lang/vendor/permissible'),
        ], 'permissible-translations');
        
        $this->publishes([
            $this->packagePath('config/permissible.php') => config_path('permissible.php'),
        ], 'permissible-config');

        $this->publishes([
            $this->packagePath('config/jwt.php') => config_path('jwt.php'),
        ], 'permissible-config');

        $this->publishes([
            $this->packagePath('config/auth.php') => config_path('auth.php'),
        ], 'permissible-config');
    }

    public function provides(): array
    {
        return [PermissibleService::class];
    }
}
