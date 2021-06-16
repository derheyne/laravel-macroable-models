# Macroable Models for Eloquent

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dheyne/laravel-macroable-models.svg?style=flat-square)](https://packagist.org/packages/derheyne/laravel-macroable-models)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/derheyne/laravel-macroable-models/run-tests?label=tests)](https://github.com/derheyne/laravel-macroable-models/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/derheyne/laravel-macroable-models/Check%20&%20fix%20styling?label=code%20style)](https://github.com/derheyne/laravel-macroable-models/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/dheyne/laravel-macroable-models.svg?style=flat-square)](https://packagist.org/packages/derheyne/laravel-macroable-models)

This package allows you to dynamically add methods, accessors, mutators, and local scopes via macros to any Eloquent
model.

I don't know when you would use that - but here it is.

## Installation

Install the package via composer in your Laravel application:

```bash
composer require dheyne/laravel-macroable-models
```

## Usage

To make any of your Eloquent models macroable, just extend them with the `Dheyne\LaravelMacroableModels\MacroableModel`
and you're all set. You can then define accessors, mutators, and local scopes from anywhere like you would in the model
itself.

```php
use Dheyne\LaravelMacroableModels\MacroableModel;

class YourModel extends MacroableModel
{
    // ...
}
```

It's recommended that you add new macros to your models via a service provider.

```php
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Add a simple macro:
        YourModel::macro('getCars', function () {
            return $this->cars;
        });
        
        // Add a local scope:
        YourModel::macro('scopeActive', function ($query) {
            return $query->where('is_active', true);
        });
        
        // Add a computed accessor:
        YourModel::macro('getFullNameAttribute', function () {
            return "{$this->first_name} {$this->last_name}";
        });
        
        // Add an accessor:
        YourModel::macro('getFirstNameAttribute', function ($value) {
            return ucfirst($value);
        });
        
        // Add a mutator:
        YourModel::macro('getFirstNameAttribute', function ($value) {
            return $this->attributes['first_name'] = strtolower($value);
        });
    }
}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Daniel Heyne](https://github.com/derheyne)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
