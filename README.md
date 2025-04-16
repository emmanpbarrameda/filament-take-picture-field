# Filament Take Picture Field v1

A custom Filament 3 form component that allows users to capture photos directly from their device camera and upload them to your application's storage.

## Features

- Take photos directly from the user's device camera
- Seamless integration with Filament 3 forms
- Configurable storage options (disk, directory, visibility)
- Camera selector for devices with multiple cameras
- Adjustable aspect ratio and image quality
- Modal support for better user experience

## Installation

```bash
composer require emmanpbarrameda/filament-take-picture-field
```

## Requirements

- PHP: ^8.1
- Filament: ^3.0
- A device with camera access (desktop or mobile)

## Usage

Add the component to your Filament form:

```php
use emmanpbarrameda\FilamentTakePictureField\Forms\Components\TakePicture;

// ...

TakePicture::make('camera_test')
    ->label('Camera Test')
    ->disk('public')
    ->directory('uploads/services/payment_receipts_proof')
    ->visibility('public')
    ->useModal(true)
    ->showCameraSelector(true)
    ->aspect('16:9')
    ->imageQuality(80)
    ->shouldDeleteOnEdit(false)
```

## Configuration Options

| Method | Description |
|--------|-------------|
| `disk(string $disk)` | Set the storage disk for saving photos (default: 'public') |
| `directory(string $directory)` | Set the directory path within the disk where photos will be stored |
| `visibility(string $visibility)` | Set the file visibility (e.g., 'public', 'private') |
| `useModal(bool $useModal)` | Enable or disable modal view for the camera (default: 'true') |
| `showCameraSelector(bool $showSelector)` | Enable or disable camera selection option for devices with multiple cameras (default: 'true') |
| `aspect(string $aspect)` | Set the aspect ratio for the captured image (e.g., '16:9', '4:3', '1:1') |
| `imageQuality(int $quality)` | Set the JPEG quality of the captured image (0-100) |
| `shouldDeleteOnEdit(bool $shouldDelete)` | Whether to delete the previous file when editing (default: 'true') |

## Screenshots

![image](https://github.com/user-attachments/assets/12813349-b4f0-4ef2-91b7-430104b57742)
![image](https://github.com/user-attachments/assets/2643f1af-b8bb-4a1b-b745-337b4290d74b)
![image](https://github.com/user-attachments/assets/e7a9c5eb-e32c-418c-80b7-d3e425f0edae)

## Contributing

This is version 1.0 of the filament-take-picture-field component plugin. Contributions and pull requests for improvements are welcome!

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
  <img alt='' src='https://img.shields.io/badge/MAKE_IT_WITH_LOVE_FROM_PH-❤️-100000?style=for-the-badge&labelColor=EF4041&color=C1282D'/>
  
</p>
