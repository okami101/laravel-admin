# Vtec Crud

[![Latest Version on Packagist](https://img.shields.io/packagist/v/adr1enbe4udou1n/vtec-crud.svg?style=flat-square)](https://packagist.org/packages/adr1enbe4udou1n/vtec-crud)
[![Total Downloads](https://img.shields.io/packagist/dt/adr1enbe4udou1n/vtec-crud.svg?style=flat-square)](https://packagist.org/packages/adr1enbe4udou1n/vtec-crud)

This is where your description should go. Try and limit it to a paragraph or two. Consider adding a small example.

## Support us

We invest a lot of resources into creating [best in class open source packages](https://adr1enbe4udou1n.be/open-source). You can support us by [buying one of our paid products](https://adr1enbe4udou1n.be/open-source/support-us). 

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://adr1enbe4udou1n.be/about-us). We publish all received postcards on [our virtual postcard wall](https://adr1enbe4udou1n.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require vtec/crud

php artisan crud:install
```

> Env variables : ADMIN_URL

## Deps used

[spatie/laravel-medialibrary](https://github.com/spatie/laravel-medialibrary)
[spatie/laravel-query-builder](https://github.com/spatie/laravel-query-builder)
[spatie/laravel-translatable](https://github.com/dimsav/laravel-translatable)
[symfony/yaml](https://github.com/symfony/Yaml/)

## Usage

Use `docker-compose exec laravel` before each command if using docker.

```bash
php artisan storage:link
php artisan migrate:fresh --seed
```

## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email freek@adr1enbe4udou1n.be instead of using the issue tracker.

## Credits

- [Adrien Beaudouin](https://github.com/adr1enbe4udou1n)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
