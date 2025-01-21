# CakePHP wrapper for OpenAgenda-API

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![codecov](https://codecov.io/gh/Erwane/openagenda-wrapper-cakephp/branch/1.x/graph/badge.svg?token=HOIAXK9V64)](https://codecov.io/gh/Erwane/openagenda-wrapper-cakephp)
[![Build Status](https://github.com/Erwane/openagenda-wrapper-cakephp/actions/workflows/ci.yml/badge.svg?branch=1.x)](https://github.com/Erwane/openagenda-wrapper-cakephp/actions)
[![Packagist Downloads](https://img.shields.io/packagist/dt/Erwane/openagenda-wrapper-cakephp)](https://packagist.org/packages/Erwane/openagenda-wrapper-cakephp)
[![Packagist Version](https://img.shields.io/packagist/v/Erwane/openagenda-wrapper-cakephp)](https://packagist.org/packages/Erwane/openagenda-wrapper-cakephp)

CakePHP wrapper for [erwane/openagenda-api](https://github.com/Erwane/openagenda-api) package.

## Version map

| version | OpenAgenda-API Package | CakePHP | PHP min |
|---------|------------------------|---------|---------|
| 1.3.*   | 3.0.*                  | ^3.10   | PHP 7.2 |
| 1.4.*   | 3.0.*                  | ^4.2    | PHP 7.4 |
| 2.4.*   | ^3.1                   | ^4.2    | PHP 8.0 |
| 2.5.*   | ^3.1                   | ^5.0    | PHP 8.1 |

## Usage

```php
composer require erwane/openagenda-wrapper-cakephp
```

```php
use Cake\Cache\Cache;
use OpenAgenda\OpenAgenda;
use OpenAgenda\Wrapper\CakeWrapper

// PSR-18 Http client.
$wrapper = new CakeWrapper($guzzleOptions);

// PSR-16 Simple cache. Optional
$cache = Cache::pool('default');

// Create the OpenAgenda client. The public key is required for reading data (GET)
// The private key is optional and only needed for writing data (POST, PUT, DELETE)
$oa = new OpenAgenda([
    'public_key' => 'my public key', // Required
    'secret_key' => 'my secret key', // Optional, only for create/update/delete
    'wrapper' => $wrapper, // Required
    'cache' => $cache, // Optional
    'defaultLang' => 'fr', // Optional
]);
```

Check [OpenAgenda API lib](https://github.com/Erwane/openagenda-api/blob/3.x/README.md) for details.
