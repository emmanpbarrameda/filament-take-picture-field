<?php

namespace emmanpbarrameda\FilamentTakePictureField;

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
            ->hasViews(static::$viewNamespace);
    }
}
