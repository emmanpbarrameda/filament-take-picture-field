<?php

namespace emmanpbarrameda\FilamentTakePictureField\Forms\Components;

use Closure;
use emmanpbarrameda\FilamentTakePictureField\Forms\Components\Concerns\HasCameraOptions;
use emmanpbarrameda\FilamentTakePictureField\Forms\Components\Concerns\HasMultipleShots;
use emmanpbarrameda\FilamentTakePictureField\Forms\Components\Concerns\HasStorage;
use emmanpbarrameda\FilamentTakePictureField\Forms\Components\Concerns\HasTextToSpeech;
use Filament\Forms\Components\Field;
use Illuminate\Support\Facades\Log;
use Throwable;

class TakePicture extends Field
{
    use HasCameraOptions;
    use HasMultipleShots;
    use HasStorage;
    use HasTextToSpeech;

    protected string $view = 'filament-take-picture-field::forms.components.take-picture';

    protected function setUp(): void
    {
        parent::setUp();

        $this->rule(static fn(self $component): Closure => $component->getRequiredShotsRule());

        $this->dehydrateStateUsing(static function (self $component, $state, $set): mixed {
            try {
                return $component->storeCapturedState($state, $set);
            } catch (Throwable $e) {
                Log::error('FilamentTakePicture dehydrateStateUsing ERROR', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return $state;
            }
        });

        $this->afterStateUpdated(static function (self $component, $old, $state): void {
            if ($component->getMultipleMode()) {
                return;
            }

            try {
                $component->deletePreviousFile($old, $state);
            } catch (Throwable $e) {
                Log::error('FilamentTakePicture afterStateUpdated ERROR', [
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }

    /**
     * Turn freshly captured photos in the state into stored file paths.
     * Paths of photos that were already stored are left untouched.
     */
    protected function storeCapturedState(mixed $state, callable $set): mixed
    {
        if ($this->getMultipleMode()) {
            if (!is_array($state)) {
                return $state;
            }

            return array_map(
                fn($value) => static::isBase64Image($value) ? $this->saveBase64Image($value) : $value,
                $state,
            );
        }

        if (!static::isBase64Image($state)) {
            return $state;
        }

        $path = $this->saveBase64Image($state);
        $targetField = $this->getTargetField();

        if ($targetField && $targetField !== $this->getName()) {
            $set($targetField, $path);

            return null;
        }

        return $path;
    }

    /**
     * Everything the Alpine component needs to know about this field.
     */
    public function getJsConfig(): array
    {
        return [
            'aspectRatio' => $this->getAspect(),
            'imageQuality' => $this->getImageQuality(),
            'maxWidth' => $this->getCaptureMaxWidth(),
            'maxHeight' => $this->getCaptureMaxHeight(),
            'autoStart' => $this->getAutoStart(),
            'useModal' => $this->getUseModal(),
            'mirroredView' => $this->getMirroredView(),
            'showCameraSelector' => $this->getShowCameraSelector(),
            'isDisabled' => $this->isDisabled(),
            'isMultiple' => $this->getMultipleMode(),
            'shotSequence' => array_values($this->getShotSequence()),
            'requireAllShots' => $this->getRequireAllShots(),
            'enableTts' => $this->getEnableTTS(),
            'ttsLang' => $this->getTTSLang(),
        ];
    }

    /**
     * Translated strings used from JavaScript. Placeholders are passed through
     * as {name} so the component can fill them in at runtime.
     */
    public function getJsTranslations(): array
    {
        $keys = [
            'error_unable_to_detect_cameras',
            'error_https_required_mobile',
            'error_permission_denied',
            'error_no_camera_found',
            'error_camera_in_use',
            'error_overconstrained',
            'error_security',
            'error_abort',
            'error_unknown',
            'new_capture',
            'saved_photo',
            'retake',
            'capture',
            'take_photo',
            'enable_mirror',
            'disable_mirror',
            'back_camera',
            'front_camera',
        ];

        $translations = [];

        foreach ($keys as $key) {
            $translations[$key] = __("filament-take-picture-field::take-picture-field.{$key}");
        }

        $translations['take_picture_of'] = __(
            'filament-take-picture-field::take-picture-field.take_picture_of',
            ['label' => '{label}'],
        );

        return $translations;
    }
}
