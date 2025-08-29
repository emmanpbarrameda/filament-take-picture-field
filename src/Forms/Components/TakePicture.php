<?php

namespace emmanpbarrameda\FilamentTakePictureField\Forms\Components;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\Concerns\HasFileAttachments;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/********************************************
 * TakePicture.php
 * author: emmanpbarrameda (emmanuelbarrameda1@gmail.com)
 */
class TakePicture extends Field {

    use HasFileAttachments;

    /********************************************
     * Connected to:
     * take-picture.blade.php
     */
    protected string $view = 'filament-take-picture-field::forms.components.take-picture';

    /********************************************
     * Important Values
     */
    protected string $disk = 'user_photo';
    protected ?string $directory = null;
    protected string $visibility = 'public';
    protected ?string $targetField = null;
    protected bool $shouldDeleteTemporaryFile = true;
    protected bool $showCameraSelector = false;
    protected int $imageQuality = 90;
    protected string $aspect = '16:9';
    protected bool $useModal = true;
    protected bool $shouldDeleteOnEdit = true;

    /********************************************
     * Functions
     */
    public function disk(string $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    public function getImageUrlPrefix(): string
    {
        return $this->disk === 'public' ? Storage::disk($this->disk)->url('') : '';
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

    public function shouldDeleteOnEdit(bool $condition = true): static
    {
        $this->shouldDeleteOnEdit = $condition;

        return $this;
    }

    public function getShouldDeleteOnEdit(): bool
    {
        return $this->shouldDeleteOnEdit;
    }

    public function saveBase64Image(string $base64Data): ?string
    {
        if (empty($base64Data)) {
            return null;
        }
        $base64Data = preg_replace('#^data:image/\w+;base64,#i', '', $base64Data);

        //unique filename
        $filename = Str::uuid() . '.png';
        $path = $this->directory ? $this->directory . '/' . $filename : $filename;

        //store image
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

        $this->afterStateHydrated(function (Get $get, Set $set, ?string $state): void {
            //displaying an existing record
        });

        $this->dehydrateStateUsing(function (?string $state, Get $get, Set $set): mixed {
            //no base64 data, return the original state or null
            if (!$state || !Str::startsWith($state, 'data:image/')) {
                return $state;
            }

            //process the base64 image
            $path = $this->saveBase64Image($state);

            if ($this->targetField && $this->targetField !== $this->getName()) {
                $set($this->targetField, $path);
                return null;
            }

            return $path;
        });

        //clean temp files
        $this->afterStateUpdated(function (Get $get, ?string $old, ?string $state) {
            if (
                $this->shouldDeleteTemporaryFile && $this->shouldDeleteOnEdit &&
                $old && $old !== $state && Storage::disk($this->disk)->exists($old)
            ) {
                Storage::disk($this->disk)->delete($old);
            }
        });

    }
}
