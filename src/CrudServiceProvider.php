<?php

namespace Vtec\Crud;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Spatie\QueryBuilder\QueryBuilder;
use Vtec\Crud\Commands\CrudGeneratorCommand;
use Vtec\Crud\Commands\CrudMakeCommand;

class CrudServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/crud.php' => config_path('crud.php'),
            ], 'config');

            $this->loadTranslationsFrom(__DIR__.'/resources/lang', 'crud');

            $this->commands([
                CrudMakeCommand::class,
                CrudGeneratorCommand::class,
            ]);

            /*
            $this->loadViewsFrom(__DIR__.'/../resources/views', 'crud');

            $this->publishes([
                __DIR__.'/../resources/views' => base_path('resources/views/vendor/crud'),
            ], 'views');
            */
        }

        Validator::extend('current_password', function ($attribute, $value, $parameters, $validator) {
            return Hash::check($value, auth()->user()->password);
        }, __('crud.validation.mismatch_password'));

        Validator::extend('strong_password', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*(_|[^\w])).+$/', (string) $value);
        }, __('crud.validation.strong_password'));

        QueryBuilder::macro('exportOrPaginate', function () {
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
        $this->mergeConfigFrom(__DIR__.'/../config/crud.php', 'crud');
    }
}
