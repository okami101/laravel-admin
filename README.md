# Vtec Laravel Crud

[![Latest Version on Packagist](https://img.shields.io/packagist/v/vtec/laravel-crud.svg?style=flat-square)](https://packagist.org/packages/vtec/laravel-crud)
[![License](https://img.shields.io/packagist/l/vtec/laravel-crud.svg?style=flat-square)](https://packagist.org/packages/vtec/laravel-crud)

Crud API resource builder for Laravel 7. Fully compatible with [Vtec Admin](https://github.com/okami101/vtec-admin), a 100% SPA separated admin panel builder based on Vue CLI.

> See [dedicated guide](https://vtec.okami101.io/guide/laravel.html)

## Features

* On-asking installer for quick start by optional packages selection, including associated Vue CLI admin project !
* Many optional dev packages proposed by the installer as [IDE Helper](https://github.com/barryvdh/laravel-ide-helper), PHP CS Fixer with Laravel preset, [Clockwork](https://github.com/itsgoingd/clockwork), [Laracasts Generators](https://github.com/laracasts/Laravel-5-Generators-Extended).
* [Laravel Sanctum](https://github.com/laravel/sanctum) for admin SPA auth.
* [Laravel elFinder](https://github.com/barryvdh/laravel-elfinder) for direct disk file management with Wysiwyg bridges.
* Removable image upload controller compatible with TinyMCE 5.
* Media support thanks to [spatie/laravel-medialibrary](https://github.com/spatie/laravel-medialibrary).
* Translatable model support thanks to [spatie/laravel-translatable](https://github.com/dimsav/laravel-translatable).
* Simple account controller for profile editing and password change.
* User impersonation with dedicated middleware.
* Quick resource api generator commands including direct YAML descriptor file !
* Preconfigured docker files included with ready to use MySQL, phpMyadmin, Nginx and Redis !

## Installation

Simply init your project by this simple 2 steps :

```bash
composer require vtec/laravel-crud
php artisan vtec:install
```

Simply follow wizard. The installer will preconfigure all asked packages for you and generate all minimal boilerplate code for quick start, including auth, users controller with impersonation.

Laravel Sanctum is ready to use for [Vtec Admin](https://github.com/okami101/vtec-admin).

At the end of installation, a full ready Vue CLI Admin project will be installed inside `admin` subfolder (default) with all required dependencies by using [this preset](preset.json).

### Run backend

After the installation, if you have selected docker, simply launch `docker-compose up`. Don't forget to adapt your environments variables with those outputted by installer when finished. By default API run at [http://localhost:8000](http://localhost:8000).

> If you use docker, use `docker-compose exec laravel` before each next artisan commands.

Then you just have to setup laravel database installation :

```bash
php artisan storage:link
php artisan migrate:fresh --seed
```

Finally create your first user by `php artisan vtec:user admin@example.com`. You will be prompted for the user name and password.

> By default admin URL is configured at [http://localhost:8080](http://localhost:8080) which is default Vue CLI dev serve URL.  
> Don't forget to change it on production by adapting `ADMIN_URL` environment variable.

### Run admin UI

Finish UI installation by doing a first commit and installing dedicated [Vtec Admin Vue CLI plugin](https://npm.okami101.io/-/web/detail/vue-cli-plugin-vtec-admin) :

```bash
cd admin
vue add vtec-admin # Will generated all minimal admin boilerplate as well as UI crud commands
yarn serve # Run Vue CLI project
```

## Scaffold API CRUD resources and UI pages

You'll got 2 new npm scripts :

* `php artisan crud:make MyNewResource [options]` : Main API crud command maker.
* `php artisan crud:yaml my-new-resource.yml` : Superset of previous command which use a YAML file descriptor.

> Launch `php artisan crud:make --help` for all options documentation.  
> See [dedicated guide](https://vtec.okami101.io/guide/laravel.html) for further details.

## Used dependencies

* [spatie/laravel-query-builder](https://github.com/spatie/laravel-query-builder) for quick api list builder with pagination, filtering and sorting support
* [spatie/laravel-medialibrary](https://github.com/spatie/laravel-medialibrary) for easy database file management support
* [spatie/laravel-translatable](https://github.com/dimsav/laravel-translatable) for json based translatable fields database support
* [symfony/yaml](https://github.com/symfony/Yaml/) for generator based on YAML file descriptor

## Full documentation

Full documentation can be found on the [Vtec docs website](https://vtec.okami101.io).

## License

This project is open-sourced software licensed under the [MIT license](https://adr1enbe4udou1n.mit-license.org).
