<?php

namespace emmanpbarrameda\FilamentTakePictureField\Forms\Components\Concerns;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasStorage
{
    protected string $disk = 'user_photo';

    protected ?string $directory = null;

    protected string $visibility = 'public';

    protected ?string $targetField = null;

    protected bool $shouldDeleteTemporaryFile = true;

    protected bool $shouldDeleteOnEdit = true;

    /**
     * Image types a captured photo is allowed to be, mapped to the file
     * extension it is stored with.
     *
     * @var array<string, string>
     */
    protected static array $allowedImageMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

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

    public function shouldDeleteOnEdit(bool $condition = true): static
    {
        $this->shouldDeleteOnEdit = $condition;

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

    public function getShouldDeleteOnEdit(): bool
    {
        return $this->shouldDeleteOnEdit;
    }

    /**
     * Whether a state value is a freshly captured photo (a base64 data URL)
     * rather than the path of a photo that is already stored.
     */
    public static function isBase64Image(mixed $value): bool
    {
        return is_string($value) && str_starts_with($value, 'data:image/');
    }

    /**
     * Decode a captured photo, check that it really is an image, and store it.
     * Returns the stored path, or null when the data is not a valid image.
     */
    public function saveBase64Image(string $base64Data): ?string
    {
        if (empty($base64Data)) {
            return null;
        }

        $payload = preg_replace('#^data:image/\w+;base64,#i', '', $base64Data);
        $imageData = base64_decode($payload, true);

        if ($imageData === false || $imageData === '') {
            return null;
        }

        $extension = $this->detectImageExtension($imageData);

        if ($extension === null) {
            return null;
        }

        $filename = Str::uuid() . '.' . $extension;
        $directory = $this->directory ? rtrim($this->directory, '/') : null;
        $path = $directory ? $directory . '/' . $filename : $filename;

        Storage::disk($this->disk)->put($path, $imageData, $this->visibility);

        return $path;
    }

    /**
     * Look at the decoded bytes themselves (not the data URL prefix, which the
     * browser controls) to decide whether this is an allowed image type.
     */
    protected function detectImageExtension(string $imageData): ?string
    {
        $info = @getimagesizefromstring($imageData);

        if ($info === false) {
            return null;
        }

        return static::$allowedImageMimeTypes[$info['mime'] ?? ''] ?? null;
    }

    protected function deletePreviousFile(mixed $old, mixed $new): void
    {
        if (!$this->getShouldDeleteTemporaryFile() || !$this->getShouldDeleteOnEdit()) {
            return;
        }

        if (!is_string($old) || $old === '' || static::isBase64Image($old) || $old === $new) {
            return;
        }

        $storage = Storage::disk($this->getDisk());

        if ($storage->exists($old)) {
            $storage->delete($old);
        }
    }
}
