<?php

namespace emmanpbarrameda\FilamentTakePictureField;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentTakePictureFieldServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-take-picture-field';

    public static string $viewNamespace = 'filament-take-picture-field';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasViews(static::$viewNamespace)
            ->hasTranslations();
    }

    public function packageBooted(): void
    {
        FilamentAsset::register(
            assets: [
                AlpineComponent::make('take-picture', __DIR__ . '/../resources/dist/components/take-picture.js'),
            ],
            package: 'emmanpbarrameda/filament-take-picture-field',
        );
    }
}
