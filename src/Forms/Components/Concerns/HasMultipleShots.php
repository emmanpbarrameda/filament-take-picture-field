<?php

namespace emmanpbarrameda\FilamentTakePictureField\Forms\Components\Concerns;

use Closure;

trait HasMultipleShots
{
    protected bool $multipleMode = false;

    protected array $shotSequence = [];

    protected bool $requireAllShots = true;

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

    /**
     * Validation rule that fails when any shot in the sequence is missing.
     */
    protected function getRequiredShotsRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (!$this->getMultipleMode() || !$this->getRequireAllShots()) {
                return;
            }

            if (!is_array($value)) {
                $fail('Please capture all required photos.');

                return;
            }

            $missing = [];

            foreach ($this->getShotSequence() as $shot) {
                $key = $shot['key'] ?? null;

                if (!$key) {
                    continue;
                }

                $captured = $value[$key] ?? null;

                if ($captured === null || $captured === '') {
                    $missing[] = $shot['label'] ?? $key;
                }
            }

            if (!empty($missing)) {
                $fail('Missing required photos: ' . implode(', ', $missing) . '.');
            }
        };
    }
}
