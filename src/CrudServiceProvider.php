<?php

namespace Vtec\Crud;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Vtec\Crud\Commands\CrudMakeCommand;
use Vtec\Crud\Commands\CrudYamlCommand;
use Vtec\Crud\Commands\InstallCommand;
use Vtec\Crud\Commands\UICommand;
use Vtec\Crud\Commands\UserCommand;

class CrudServiceProvider extends ServiceProvider
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

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'crud');
        Route::mixin(new AdminRouteMethods);

        Validator::extend('current_password', function ($attribute, $value, $parameters, $validator) {
            return Hash::check($value, auth()->user()->password);
        }, __('crud::validation.mismatch_password'));

        Validator::extend('strong_password', function ($attribute, $value, $parameters, $validator) {
            if (! config('app.debug')) {
                return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*(_|[^\w])).+$/', (string) $value);
            }

            return true;
        }, __('crud::validation.strong_password'));

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
