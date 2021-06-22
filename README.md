# Laravel Admin

[![Latest Version on Packagist](https://img.shields.io/packagist/v/okami101/laravel-admin.svg?style=flat-square)](https://packagist.org/packages/okami101/laravel-admin)
[![License](https://img.shields.io/packagist/l/okami101/laravel-admin.svg?style=flat-square)](https://packagist.org/packages/okami101/laravel-admin)

Admin Crud resource builder for Laravel 8, from backend API to UI with help of [Vuetify Admin](https://github.com/okami101/vuetify-admin), a 100% SPA separated Vue CLI admin panel builder based on Vuetify.

> See [full documentation](https://www.okami101.io/vuetify-admin)  
> Check [online demo](https://va-demo.okami101.io) -> go to admin and use pre-filled login (read only)  

[![demo](https://www.okami101.io/vuetify-admin/assets/screenshot.png)](https://va-demo.okami101.io)

## Features

### From this package

* On-asking installer for quick start by optional packages selection, including associated Vue CLI admin project !
* Many optional dev packages proposed by the installer as [IDE Helper](https://github.com/barryvdh/laravel-ide-helper), PHP CS Fixer with Laravel preset, [Clockwork](https://github.com/itsgoingd/clockwork), [Dump Server](https://github.com/beyondcode/laravel-dump-server), [Laracasts Generators](https://github.com/laracasts/Laravel-5-Generators-Extended).
* [Laravel Fortify](https://github.com/laravel/fortify) for frontend agnostic authentication.
* [Laravel Sanctum](https://github.com/laravel/sanctum) for admin SPA auth.
* [Laravel elFinder](https://github.com/barryvdh/laravel-elfinder) for direct disk file management with Wysiwyg bridges.
* Removable image upload controller compatible with TinyMCE 5.
* Media support thanks to [spatie/laravel-medialibrary](https://github.com/spatie/laravel-medialibrary).
* Translatable model support thanks to [spatie/laravel-translatable](https://github.com/dimsav/laravel-translatable).
* Simple account controller for profile editing and password change.
* User impersonation with dedicated middleware.
* Quick resource api generator commands including direct YAML descriptor file !
* Pre-configured docker files included with ready to use MySQL, phpMyAdmin, Nginx and Redis !

### From Vuetify Admin

See [dedicated readme](https://github.com/okami101/vuetify-admin#features) of Vuetify Admin repo for full listing.

## Install

Use `laravel new my-laravel-admin-app` to initialize a new Laravel 8 project then :

```bash
composer require okami101/laravel-admin
php artisan admin:install
```

See [dedicated guide](https://www.okami101.io/vuetify-admin/guide/laravel.html) for full showcase.

## Documentation

Documentation for Vuetify Admin can be found on the [Okami101 website](https://www.okami101.io/vuetify-admin).

## License

This project is open-sourced software licensed under the [MIT license](https://adr1enbe4udou1n.mit-license.org).
