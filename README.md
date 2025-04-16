# FilamentTakePictureField Component for Filament Forms

[![Latest Version on Packagist](https://img.shields.io/packagist/v/emmanpbarrameda/filament-take-picture-field.svg?style=flat-square)](https://packagist.org/packages/emmanpbarrameda/filament-take-picture-field)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/emmanpbarrameda/filament-take-picture-field/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/emmanpbarrameda/filament-take-picture-field/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/emmanpbarrameda/filament-take-picture-field/fix-php-code-styling.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/emmanpbarrameda/filament-take-picture-field/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/emmanpbarrameda/filament-take-picture-field.svg?style=flat-square)](https://packagist.org/packages/emmanpbarrameda/filament-take-picture-field)



This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require emmanpbarrameda/filament-take-picture-field
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="filament-take-picture-field-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="filament-take-picture-field-config"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="filament-take-picture-field-views"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
$filamentTakePictureField = new emmanpbarrameda\FilamentTakePictureField();
echo $filamentTakePictureField->echoPhrase('Hello, emmanpbarrameda!');
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

- [emmanpbarrameda](https://github.com/emmanpbarrameda)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
