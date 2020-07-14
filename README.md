# Vtec Laravel Crud

[![Latest Version on Packagist](https://img.shields.io/packagist/v/vtec/laravel-crud.svg?style=flat-square)](https://packagist.org/packages/vtec/laravel-crud)
[![License](https://img.shields.io/packagist/l/vtec/laravel-crud.svg?style=flat-square)](https://packagist.org/packages/vtec/laravel-crud)

Admin Crud resource builder for Laravel 7, from backend API to UI with help of [Vtec Admin](https://github.com/okami101/vtec-admin), a 100% SPA separated Vue CLI admin panel builder based on Vuetify.

> See [full documentation](https://vtec.okami101.io)  
> Check [online demo](https://vtec-bookstore-demo.okami101.io) -> go to admin and use pre-filled login (read only)  

[![demo](https://vtec.okami101.io/assets/screenshot.png)](https://vtec-bookstore-demo.okami101.io)

## Features

### From this package

* On-asking installer for quick start by optional packages selection, including associated Vue CLI admin project !
* Many optional dev packages proposed by the installer as [IDE Helper](https://github.com/barryvdh/laravel-ide-helper), PHP CS Fixer with Laravel preset, [Clockwork](https://github.com/itsgoingd/clockwork), [Dump Server](https://github.com/beyondcode/laravel-dump-server), [Laracasts Generators](https://github.com/laracasts/Laravel-5-Generators-Extended).
* [Laravel Sanctum](https://github.com/laravel/sanctum) for admin SPA auth.
* [Laravel elFinder](https://github.com/barryvdh/laravel-elfinder) for direct disk file management with Wysiwyg bridges.
* Removable image upload controller compatible with TinyMCE 5.
* Media support thanks to [spatie/laravel-medialibrary](https://github.com/spatie/laravel-medialibrary).
* Translatable model support thanks to [spatie/laravel-translatable](https://github.com/dimsav/laravel-translatable).
* Simple account controller for profile editing and password change.
* User impersonation with dedicated middleware.
* Quick resource api generator commands including direct YAML descriptor file !
* Pre-configured docker files included with ready to use MySQL, phpMyAdmin, Nginx and Redis !

### From Vtec Admin

See [dedicated readme](https://github.com/okami101/vtec-admin#features) of Vtec Admin repo for full listing.

## Install

```bash
composer require vtec/laravel-crud
php artisan crud:install
```

See [dedicated guide](https://vtec.okami101.io/guide/laravel.html) for full showcase.

## Documentation

Documentation for Vtec Admin can be found on the [Vtec website](https://vtec.okami101.io).

## License

This project is open-sourced software licensed under the [MIT license](https://adr1enbe4udou1n.mit-license.org).
