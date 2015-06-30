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

The package registers a blade directive, `@cache`. The cache directive accepts the same arguments as `@include`, plus optional parameters for the amount of minutes a view should be cached for, a key unique to the rendered view, and a cache tag for the rendered view. If no minutes are provided, the view will be remembered until you manually remove it from the cache.

Note that this caches the rendered html, not the rendered php like blade's default view caching.

```
{{-- Simple example --}}
@cache('footer.section.partial')

{{-- With extra view data --}}
@cache('products.card', ['product' => $category->products->first()])

{{-- For a certain time --}}
{{-- (cache will invalidate in 60 minutes in this example, set null to remember forever) --}}
@cache('homepage.news', null, 60)

{{-- With an added key (cache entry will be partialcache.user.profile.{$user->id}) --}}
@cache('user.profile', null, null, $user->id)

{{-- With an added tag (only supported by memcached and others) }}
@cache('user.profile', null, null, $user->id, 'userprofiles')
```

### Clearing The PartialCache

You can forget a partialcache entry with `PartialCache::forget($view, $key)`. 

```php
PartialCache::forget('user.profile', $user->id);
```

If you want to flush all entries, you'll need to either call `PartialCache::flush()` (note: this is only supported by drivers that support tags), or clear your entire cache.

### Configuration

Configuration isn't necessary, but there are two options specified in the config file:

- `partialcache.directive`: The name of the blade directive to register. Defaults to `cache`.
- `partialcache.key`: The base key that used for cache entries. Defaults to `partialcache`.

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
