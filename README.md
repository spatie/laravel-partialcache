# Laravel Cache Partial Blade Directive

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-partialcache.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-partialcache)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/spatie/laravel-partialcache/master.svg?style=flat-square)](https://travis-ci.org/spatie/laravel-partialcache)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/xxxxxxxxx.svg?style=flat-square)](https://insight.sensiolabs.com/projects/xxxxxxxxx)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/laravel-partialcache.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/laravel-partialcache)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-partialcache.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-partialcache)

Blade directive to cache rendered partials in laravel.

## Install

You can install the package via Composer:

```bash
$ composer require spatie/laravel-partialcache
```

Start by registering the package's the service provider and facade:

```php
// config/app.php

'providers' => [
  ...
  'Spatie\PartialCache\PartialCacheServiceProvider',
],

'aliases' => [
  ...
  'PartialCache' => 'Spatie\PartialCache\PartialCacheFacade',
],
```

*The facade is optional, but the rest of this guide assumes you're using it.*

Optionally publish the config files:

```bash
$ php artisan vendor:publish --provider="Spatie\PartialCache\PartialCacheServiceProvider"
```

## Usage



### Configuration



## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Credits

- [Sebastian De Deyne](https://github.com/sebastiandedeyne)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
