# Vtec Laravel Crud

[![Latest Version on Packagist](https://img.shields.io/packagist/v/vtec/laravel-crud.svg?style=flat-square)](https://packagist.org/packages/vtec/laravel-crud)
[![Total Downloads](https://img.shields.io/packagist/dt/vtec/laravel-crud.svg?style=flat-square)](https://packagist.org/packages/vtec/laravel-crud)
[![License](https://img.shields.io/packagist/l/vtec/laravel-crud.svg?style=flat-square)](https://packagist.org/packages/vtec/laravel-crud)

Quick crud api resource builder for Laravel 7. Fully compatible with [Vtec Admin](https://github.com/okami101/vtec-admin), a 100% SPA separated admin panel builder based on Vue CLI.

## Features

* On-asking installer for quick start by optional packages selection
* Many optional dev packages proposed by the installer as IDE Helper, PHP CS Fixer with Laravel preset, Clockwork, Laracasts Generators
* [Laravel Sanctum](https://github.com/laravel/sanctum) for admin SPA auth
* [Laravel elFinder](https://github.com/barryvdh/laravel-elfinder) for direct disk file management with Wysiwyg bridges
* Media support thanks to [spatie/laravel-medialibrary](https://github.com/spatie/laravel-medialibrary)
* Translatable model support thanks to [spatie/laravel-translatable](https://github.com/dimsav/laravel-translatable)
* Simple account controller for profile editing and password change
* User impersonation with dedicated middleware
* Quick resource api generator commands with direct YAML descriptor support !
* Preconfigured docker files included with ready to use MySQL, phpMyadmin, Nginx and Redis !

## Requirements

* You must have PHP 7.4 (required by spatie media library package)  
* Should be installed on totally fresh laravel 7 installation

## Installation

Simply quick init your project by this simple 2 steps :

```bash
composer require vtec/laravel-crud
php artisan crud:install
```

Simply follow wizard. The installer will preconfigure all of asked package for you and generate all minimal boilerplate code for quick start.  
Laravel Sanctum is ready to use for [Vtec Admin](https://github.com/okami101/vtec-admin).

> By default admin URL will run at [http://localhost:8080](http://localhost:8080) which is default Vue CLI dev serve URL.  
> Dont forget to edit it on production. Just use ADMIN_URL environment variable for that.

After the installation, if you selected docker, simply launch `docker-compose up`. Don't forget to adapt your environments variables with those outputted by installer when finished.

Then you just have to setup laravel installation as normal (after env setup) :

```bash
php artisan storage:link
php artisan migrate:fresh --seed
```

Finally create your first user by `php artisan user:create admin@example.com`. You will be prompted for the user name and password.

> If you use docker, use `docker-compose exec laravel` before each command.

## Generate api based crud resources

Simply use `php artisan crud:make Monsters` with many as option as possible in order to generate your model.  
Use `php artisan crud:make --help` for all many options detail.  
Don't hesitate to use `php artisan ide-helper:models` after in order to have all field Model autocompletion enabled !

In addition, api routes should be registered automatically at `routes/api.php` file in that place :
```php
Route::apiResources([
    /** previous entities */
    'monsters' => 'MonsterController',
]);
```

For even more auto generation power, and because `crud:make` can be exhausting to write with all options, a direct resource yaml file descriptor can be used via `php artisan crud:generate my-new-resource.yml`.  
You can also directly provide a directory which contains all necessary YAML resource descriptor files as needed.

For supported YAML schema, see [full documentation](https://vtec.okami101.io).

> For both commands, you can add `-mfs` options to generate full migration file with all pre-generated fields, in addition to factory and seeder files.  
> In case of model relation, even if foreign keys can be generated in migration file by `foreign` on schema, you must manually add related eloquent relation in you model.  
> For server-side validation, you must manually add rules inside store and update generated request files.

## Full documentation

Full documentation can be found on the [Vtec docs website](https://vtec.okami101.io).

## Used dependencies

* [spatie/laravel-query-builder](https://github.com/spatie/laravel-query-builder) for quick api list builder with pagination, filtering and sorting support
* [spatie/laravel-medialibrary](https://github.com/spatie/laravel-medialibrary) for easy database file management support
* [spatie/laravel-translatable](https://github.com/dimsav/laravel-translatable) for json based translatable fields database support
* [symfony/yaml](https://github.com/symfony/Yaml/) for generator based on YAML file descriptor

## License

This project is open-sourced software licensed under the [MIT license](https://adr1enbe4udou1n.mit-license.org).
