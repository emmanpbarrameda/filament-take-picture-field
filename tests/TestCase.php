<?php

namespace emmanpbarrameda\FilamentTakePictureField\Tests;

use Filament\Forms\FormsServiceProvider;
use Filament\Support\SupportServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use emmanpbarrameda\FilamentTakePictureField\FilamentTakePictureFieldServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'emmanpbarrameda\\FilamentTakePictureField\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            SupportServiceProvider::class,
            FormsServiceProvider::class,
            LivewireServiceProvider::class,
            FilamentTakePictureFieldServiceProvider::class,
        ];
    }
}
