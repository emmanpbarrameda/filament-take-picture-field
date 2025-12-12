<?php

namespace emmanpbarrameda\FilamentTakePictureField\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Livewire\LivewireServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use emmanpbarrameda\FilamentTakePictureField\FilamentTakePictureFieldServiceProvider;

class TestCase extends Orchestra
{

    protected static $latestResponse = null;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) =>
                'emmanpbarrameda\\FilamentTakePictureField\\Database\\Factories\\'
                . class_basename($modelName) . 'Factory'
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            SupportServiceProvider::class,
            FormsServiceProvider::class,
            FilamentTakePictureFieldServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
    }
}
