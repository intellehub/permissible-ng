<?php

namespace Shahnewaz\PermissibleNg\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use Shahnewaz\PermissibleNg\Console\Commands\Setup;
use Shahnewaz\PermissibleNg\Contracts\PermissibleAuthInterface;
use Shahnewaz\PermissibleNg\Facades\PermissibleAuth;
use Shahnewaz\PermissibleNg\Services\PermissibleService;
use Shahnewaz\PermissibleNg\Console\Commands\RolePermissionSeed;


class PermissibleServiceProvider extends ServiceProvider
{

    protected $defer = false;

    public function boot(): void {

        if ($this->app->runningInConsole()) {
            $this->commands([
                RolePermissionSeed::class
            ]);
        }
        if ($this->app->runningInConsole()) {
            $this->commands([
                Setup::class
            ]);
        }

        $this->load();
        $this->publish();
    }


    public function register (): void {
        $this->mergeConfigFrom($this->packagePath('config/permissible.php'), 'permissible');
        // Add route middlewares
        $this->app['router']->aliasMiddleware(
            'role', \Shahnewaz\PermissibleNg\Http\Middleware\RoleAccessGuard::class
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

    // Root path for package files
    private function packagePath ($path) {
        return __DIR__."/../../$path";
    }

    // Facade provider
    public function provides() {
        return [PermissibleService::class];
    }

    // Class loaders for package
    public function load () {
        // Routes
        $this->loadRoutesFrom($this->packagePath('src/routes/api.php'));
        // Migrations
        $this->loadMigrationsFrom($this->packagePath('database/migrations'));
        // Translations
        $this->loadTranslationsFrom($this->packagePath('resources/lang'), 'permissible');
    }

    // Publish required resouces from package
    private function publish () {
        // Publish Translations
        $this->publishes([
            $this->packagePath('resources/lang') => resource_path('lang/vendor/permissible'),
        ], 'permissible-translations');
        
        // Publish PermissibleAuth Config
        $this->publishes([
            $this->packagePath('config/permissible.php') => config_path('permissible.php'),
        ], 'permissible-config');

        $this->publishes([
            $this->packagePath('config/jwt.php') => config_path('jwt.php'),
        ], 'permissible-jwt-config');

        $this->publishes([
            $this->packagePath('config/auth.php') => config_path('auth.php'),
        ], 'permissible-auth-config');

        $this->publishes([
            $this->packagePath('config/jwt.php') => config_path('jwt.php'),
        ], 'config');

        $this->publishes([
            $this->packagePath('config/auth.php') => config_path('auth.php'),
        ], 'config');
    }
}
