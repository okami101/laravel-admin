<?php

namespace Okami101\LaravelAdmin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Okami101\LaravelAdmin\Commands\CrudMakeCommand;
use Okami101\LaravelAdmin\Commands\CrudYamlCommand;
use Okami101\LaravelAdmin\Commands\InstallCommand;
use Okami101\LaravelAdmin\Commands\UICommand;
use Okami101\LaravelAdmin\Commands\UserCommand;

class AdminServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/admin.php' => config_path('admin.php'),
                __DIR__.'/../config/cors.php' => config_path('cors.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/crud'),
            ], 'lang');

            $this->publishes([
                __DIR__.'/../files/docker' => base_path('docker'),
                __DIR__.'/../files/docker-compose.yml' => base_path('docker-compose.yml'),
                __DIR__.'/../files/Dockerfile' => base_path('Dockerfile'),
            ], 'docker');

            $this->publishes([
                __DIR__.'/../files/.php_cs.dist' => base_path('.php_cs.dist'),
            ], 'phpcs');

            $this->commands([
                InstallCommand::class,
                CrudMakeCommand::class,
                CrudYamlCommand::class,
                UICommand::class,
                UserCommand::class,
            ]);
        }

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'admin');
        Route::mixin(new AdminRouteMethods);

        Builder::macro('exportOrPaginate', function () {
            if (request()->get('perPage')) {
                return $this
                    ->paginate(request()->get('perPage'))
                    ->appends(request()->query());
            }

            return $this->get();
        });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/admin.php', 'admin');
    }
}
