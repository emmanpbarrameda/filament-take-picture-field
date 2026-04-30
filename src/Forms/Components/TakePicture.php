<?php

namespace emmanpbarrameda\FilamentTakePictureField\Forms\Components;

use Filament\Forms\Components\Field;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Closure;

class TakePicture extends Field
{
    protected string $view = 'filament-take-picture-field::forms.components.take-picture';

    protected string $disk = 'user_photo';
    protected ?string $directory = null;
    protected string $visibility = 'public';
    protected ?string $targetField = null;
    protected bool $shouldDeleteTemporaryFile = true;
    protected bool $showCameraSelector = true;
    protected int $imageQuality = 90;
    protected string $aspect = '16:9';
    protected bool $useModal = true;
    protected bool $shouldDeleteOnEdit = true;
    protected ?int $captureMaxWidth = null;
    protected ?int $captureMaxHeight = null;
    protected bool $autoStart = false;
    protected bool $multipleMode = false;
    protected array $shotSequence = [];
    protected bool $requireAllShots = true;
    protected bool $enableTTS = true;
    protected string $ttsLang = 'en-US';


    public function disk(string $disk): static
    {
        $this->disk = $disk;
        return $this;
    }

    public function directory(?string $directory): static
    {
        $this->directory = $directory;
        return $this;
    }

    public function visibility(string $visibility): static
    {
        $this->visibility = $visibility;
        return $this;
    }

    public function targetField(string $fieldName): static
    {
        $this->targetField = $fieldName;
        return $this;
    }

    public function shouldDeleteTemporaryFile(bool $condition = true): static
    {
        $this->shouldDeleteTemporaryFile = $condition;
        return $this;
    }

    public function showCameraSelector(bool $show = true): static
    {
        $this->showCameraSelector = $show;
        return $this;
    }

    public function imageQuality(int $quality): static
    {
        $this->imageQuality = max(1, min(100, $quality));
        return $this;
    }

    public function aspect(string $ratio): static
    {
        $this->aspect = $ratio;
        return $this;
    }

    public function useModal(bool $useModal = true): static
    {
        $this->useModal = $useModal;
        return $this;
    }

    public function captureMaxWidth(?int $width): static
    {
        $this->captureMaxWidth = $width;
        return $this;
    }

    public function captureMaxHeight(?int $height): static
    {
        $this->captureMaxHeight = $height;
        return $this;
    }

    public function captureMaxDimensions(?int $width, ?int $height): static
    {
        $this->captureMaxWidth = $width;
        $this->captureMaxHeight = $height;
        return $this;
    }

    public function autoStart(bool $autoStart = true): static
    {
        $this->autoStart = $autoStart;
        return $this;
    }

    public function getDisk(): string
    {
        return $this->disk;
    }

    public function getDirectory(): ?string
    {
        return $this->directory;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function getTargetField(): ?string
    {
        return $this->targetField ?? $this->getName();
    }

    public function getShouldDeleteTemporaryFile(): bool
    {
        return $this->shouldDeleteTemporaryFile;
    }

    public function getShowCameraSelector(): bool
    {
        return $this->showCameraSelector;
    }

    public function getImageQuality(): int
    {
        return $this->imageQuality;
    }

    public function getAspect(): string
    {
        return $this->aspect;
    }

    public function getUseModal(): bool
    {
        return $this->useModal;
    }

    public function getCaptureMaxWidth(): ?int
    {
        return $this->captureMaxWidth;
    }

    public function getCaptureMaxHeight(): ?int
    {
        return $this->captureMaxHeight;
    }

    public function getAutoStart(): bool
    {
        return $this->autoStart;
    }

    public function shouldDeleteOnEdit(bool $condition = true): static
    {
        $this->shouldDeleteOnEdit = $condition;
        return $this;
    }

    public function getShouldDeleteOnEdit(): bool
    {
        return $this->shouldDeleteOnEdit;
    }

    public function multiple(array $shots = []): static
    {
        $this->multipleMode = true;
        $this->shotSequence = $shots;
        return $this;
    }

    public function shots(array $sequence): static
    {
        $this->shotSequence = $sequence;
        $this->multipleMode = !empty($sequence);
        return $this;
    }

    public function requireAllShots(bool $required = true): static
    {
        $this->requireAllShots = $required;
        return $this;
    }

    public function getMultipleMode(): bool
    {
        return $this->multipleMode;
    }

    public function getShotSequence(): array
    {
        return $this->shotSequence;
    }

    public function getRequireAllShots(): bool
    {
        return $this->requireAllShots;
    }

    public function enableTextToSpeech(bool $enable = true, string $lang = 'en-US'): static
    {
        $this->enableTTS = $enable;
        $this->ttsLang = $lang;
        return $this;
    }

    public function getEnableTTS(): bool
    {
        return $this->enableTTS;
    }

    public function getTTSLang(): string
    {
        return $this->ttsLang;
    }

    public function saveBase64Image(string $base64Data): ?string
    {
        if (empty($base64Data)) {
            return null;
        }
        $base64Data = preg_replace('#^data:image/\w+;base64,#i', '', $base64Data);
        $filename = Str::uuid() . '.jpg';
        $path = $this->directory ? $this->directory . '/' . $filename : $filename;
        $imageData = base64_decode($base64Data);

        if (!$imageData) {
            return null;
        }
        Storage::disk($this->disk)->put($path, $imageData, $this->visibility);
        return $path;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->rule(function (self $component): Closure {
            return function (string $attribute, $value, Closure $fail) use ($component): void {

                if (!$component->getMultipleMode()) {
                    return;
                }

                if (!$component->getRequireAllShots()) {
                    return;
                }

                $sequence = $component->getShotSequence() ?? [];
                if (!is_array($value)) {
                    $fail('Please capture all required photos.');
                    return;
                }

                $missing = [];
                foreach ($sequence as $shot) {
                    $key = $shot['key'] ?? null;
                    if (!$key) {
                        continue;
                    }

                    $v = $value[$key] ?? null;
                    if ($v === null || $v === '') {
                        $missing[] = $shot['label'] ?? $key;
                    }
                }

                if (!empty($missing)) {
                    $fail('Missing required photos: ' . implode(', ', $missing) . '.');
                }
            };
        });

        $this->afterStateHydrated(function ($get, $set, $state): void {
            // displaying existing record
        });

        $component = $this;
        $this->dehydrateStateUsing(function ($state, $get, $set) use ($component): mixed {
            try {
                $multipleMode = $component->getMultipleMode();
                $disk = $component->getDisk();
                $directory = $component->getDirectory();
                $visibility = $component->getVisibility();
                $targetField = $component->getTargetField();
                $name = $component->getName();

                $saveBase64 = function (string $base64Data) use ($disk, $directory, $visibility): ?string {
                    if (empty($base64Data)) {
                        return null;
                    }
                    if (!str_starts_with($base64Data, 'data:image/')) {
                        return $base64Data;
                    }

                    $base64Data = preg_replace('#^data:image/\w+;base64,#i', '', $base64Data);
                    $filename = \Illuminate\Support\Str::uuid() . '.jpg';
                    $path = $directory ? $directory . '/' . $filename : $filename;
                    $imageData = base64_decode($base64Data);
                    if (!$imageData) {
                        return null;
                    }
                    \Illuminate\Support\Facades\Storage::disk($disk)->put($path, $imageData, $visibility);

                    return $path;
                };

                // multiple mode
                if ($multipleMode) {
                    // \Illuminate\Support\Facades\Log::debug('FilamentTakePicture: Processing MULTIPLE mode');

                    if (!is_array($state)) {
                        return $state;
                    }

                    $processed = [];
                    foreach ($state as $key => $value) {
                        if ($value && is_string($value) && str_starts_with($value, 'data:image/')) {
                            $processed[$key] = $saveBase64($value);
                        } else {
                            $processed[$key] = $value;
                        }
                    }

                    return $processed;
                }

                // Single mode
                if (!$state || !is_string($state) || !str_starts_with($state, 'data:image/')) {
                    return $state;
                }

                $path = $saveBase64($state);
                if ($targetField && $targetField !== $name) {
                    $set($targetField, $path);
                    return null;
                }
                return $path;

            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('FilamentTakePicture dehydrateStateUsing ERROR', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return $state;
            }
        });

        $this->afterStateUpdated(function ($get, $old, $state) use ($component): void {
            try {
                $multipleMode = $component->getMultipleMode();
                $disk = $component->getDisk();
                $shouldDeleteTemp = $component->getShouldDeleteTemporaryFile();
                $shouldDeleteOnEdit = $component->getShouldDeleteOnEdit();

                if ($multipleMode) {
                    return;
                }

                if ($shouldDeleteTemp &&
                    $shouldDeleteOnEdit &&
                    $old &&
                    is_string($old) &&
                    !str_starts_with($old, 'data:image/') &&
                    $old !== $state &&
                    \Illuminate\Support\Facades\Storage::disk($disk)->exists($old)
                ) {
                    \Illuminate\Support\Facades\Storage::disk($disk)->delete($old);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('FilamentTakePicture afterStateUpdated ERROR', [
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }

}