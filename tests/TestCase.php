<?php

namespace emmanpbarrameda\FilamentTakePictureField\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /** @var mixed */
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
            \Livewire\LivewireServiceProvider::class,
            \Filament\Support\SupportServiceProvider::class,
            \Filament\Forms\FormsServiceProvider::class,
            \emmanpbarrameda\FilamentTakePictureField\FilamentTakePictureFieldServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
    }
}
