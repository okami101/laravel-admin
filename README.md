# Vtec Laravel Crud

[![Latest Version on Packagist](https://img.shields.io/packagist/v/vtec/laravel-crud.svg?style=flat-square)](https://packagist.org/packages/vtec/laravel-crud)
[![License](https://img.shields.io/packagist/l/vtec/laravel-crud.svg?style=flat-square)](https://packagist.org/packages/vtec/laravel-crud)

Crud API resource builder for Laravel 7. Fully compatible with [Vtec Admin](https://github.com/okami101/vtec-admin), a 100% SPA separated admin panel builder based on Vue CLI.

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
* Pre configured docker files included with ready to use MySQL, phpMyAdmin, Nginx and Redis !

## Installation

```bash
composer require vtec/laravel-crud
php artisan vtec:install
```

See [dedicated guide](https://vtec.okami101.io/guide/laravel.html) for further detail.

## Full documentation

Full documentation can be found on the [Vtec docs website](https://vtec.okami101.io).

## License

This project is open-sourced software licensed under the [MIT license](https://adr1enbe4udou1n.mit-license.org).
