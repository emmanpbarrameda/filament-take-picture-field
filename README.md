# Filament Take Picture Field v1.4
[![Latest Version on Packagist](https://img.shields.io/packagist/v/emmanpbarrameda/filament-take-picture-field.svg?style=flat-square)](https://packagist.org/packages/emmanpbarrameda/filament-take-picture-field)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/emmanpbarrameda/filament-take-picture-field/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/emmanpbarrameda/filament-take-picture-field/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/emmanpbarrameda/filament-take-picture-field.svg?style=flat-square)](https://packagist.org/packages/emmanpbarrameda/filament-take-picture-field)
[![License](https://img.shields.io/packagist/l/emmanpbarrameda/filament-take-picture-field.svg?style=flat-square)](https://packagist.org/packages/emmanpbarrameda/filament-take-picture-field)

### A custom Filament form component to capture photos from your device camera.

<img width="1250" height="727" alt="Screenshot 2026-10-19 003257" src="https://github.com/user-attachments/assets/ec5e39fb-f591-40bb-8c43-fddd8765157e" />


## Features

- Capture photos directly from the user's device camera
- Seamless integration with Filament forms
- Configurable storage options: disk, directory, and visibility
- Camera selector support for devices with multiple cameras
- Adjustable aspect ratio, image quality, and maximum capture dimensions
- Multiple-shot mode with custom shot keys and labels
- Option to require all configured shots before saving
- Text-to-speech instructions for multiple-shot capture
- Support for previewing, retaking, and removing captured photos
- Camera in a modal or inline, with configurable mirroring

## Requirements

- Laravel: ^11.28, ^12, or ^13
- PHP: ^8.2
- Filament: ^4.0 or ^5.0
- A device with camera access (desktop or mobile)

## Installation

```bash
composer require emmanpbarrameda/filament-take-picture-field:^1.4
```

Then publish the plugin's assets (most apps already run this automatically through `php artisan filament:upgrade`):

```bash
php artisan filament:assets
```

### Filament theme setup

The field's styles are compiled by your Filament theme, so a custom theme is required. If you don't have one yet:

```bash
php artisan make:filament-theme
```

Add the plugin's views to your theme CSS file (e.g. `resources/css/filament/admin/theme.css`):

```css
@source '../../../../vendor/emmanpbarrameda/filament-take-picture-field/resources/views/**/*.blade.php';
```

Then rebuild:

```bash
npm run build
```

## Quick start

```php
use emmanpbarrameda\FilamentTakePictureField\Forms\Components\TakePicture;

TakePicture::make('photo')
    ->disk('public')
    ->directory('uploads/photos')
    ->columnSpanFull();
```

## Upgrading from v1.3

Run `php artisan filament:assets` after updating, and make sure your theme's `@source` line ends with `resources/views/**/*.blade.php` (the views are now split into subfolders). No code changes are needed.

## Screenshots (Single Mode)
<p align="center">
  <img width="100%" alt="Single mode screenshot 1" src="https://github.com/user-attachments/assets/edc45324-8d45-49de-91b0-22b7668dedd3" />
  <img width="100%" alt="Single mode screenshot 2" src="https://github.com/user-attachments/assets/7746c51e-c72f-4680-b968-f4fda67ba869" />
  <img width="49%" alt="Single mode screenshot 3" src="https://github.com/user-attachments/assets/3d6cf609-f170-4569-9ae4-310116edcab7" />
  <img width="49%" alt="Single mode screenshot 4" src="https://github.com/user-attachments/assets/ca4b5eb7-2956-48d9-bf03-9f3790cb9804" />
</p>

## Screenshots (Multiple Mode)
<p align="center">
  <img width="100%" alt="Multiple mode screenshot 1" src="https://github.com/user-attachments/assets/755072b8-7053-41c1-aeff-fb526312c549" />
  <img width="49%" alt="Multiple mode screenshot 2" src="https://github.com/user-attachments/assets/8fe30301-8589-4df8-bcdf-07e62b69ce3e" />
  <img width="49%" alt="Multiple mode screenshot 3" src="https://github.com/user-attachments/assets/765fcb1a-ecdf-45d0-852f-c5c9dbedd350" />
  <img width="100%" alt="Multiple mode screenshot 4" src="https://github.com/user-attachments/assets/ba4248d5-1da3-4b43-8ab4-6f705c7ea39a" />
</p>

## Full Documentation
Read the full installation and configuration guide here:
[Filament Take Picture Field Documentation](https://emmanpbarrameda.github.io/dev-notes/filament-take-picture-field/#a-custom-filament-form-component-to-capture-photos-from-your-device-camera)

## Contributing

Contributions and pull requests for improvements are welcome!

## License
MIT

## <img src="https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/Travel%20and%20places/Glowing%20Star.png" alt="Glowing Star" width="40" height="40" /> Get in touch

<p align="center">
  <a href="https://emmanpbarrameda.github.io" target="_blank"><img src="https://img.shields.io/badge/My Portfolio-%20-blue?style=for-the-badge&logo=web"></a>
  &nbsp;&nbsp;
  <a href="mailto:emmanuelbarrameda1@gmail.com" target="_blank"><img src="https://img.shields.io/badge/Email-%20-red?style=for-the-badge&logo=gmail"></a>
  &nbsp;&nbsp;
  <a href="https://facebook.com/emmanpbarrameda/" target="_blank"><img src="https://img.shields.io/badge/Facebook-%20-blue?style=for-the-badge&logo=facebook"></a>
  &nbsp;&nbsp;
  <a href="https://t.me/emmanpbarrameda/" target="_blank"><img src="https://img.shields.io/badge/Telegram-%20-blue?style=for-the-badge&logo=telegram"></a>
  &nbsp;&nbsp;
  <a href="https://linkedin.com/in/emmanpbarrameda/" target="_blank"><img src="https://img.shields.io/badge/LinkedIn-%20-blue?style=for-the-badge&logo=linkedin"></a>
  &nbsp;&nbsp;
  <a href="https://github.com/emmanpbarrameda/" target="_blank"><img src="https://img.shields.io/badge/GitHub-%20-black?style=for-the-badge&logo=github"></a>
</p>
<br>

<p align="center">

  <!-- my name https://kapasia-dev-ed.my.site.com/Badges4Me/s/ -->
  <img alt='/e/' src='https://img.shields.io/badge/MADE_BY - EMMAN_P_BARRAMEDA-100000?style=for-the-badge&logo=/e/&logoColor=1877F2&labelColor=FFFFFF&color=1877F2'/>
  
  <!-- made with love -->
  <img alt='' src='https://img.shields.io/badge/MAKE_WITH_LOVE_FROM_PH-❤️-100000?style=for-the-badge&labelColor=EF4041&color=C1282D'/>
  
</p>
