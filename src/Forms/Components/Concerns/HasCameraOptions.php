<?php

namespace emmanpbarrameda\FilamentTakePictureField\Forms\Components\Concerns;

trait HasCameraOptions
{
    protected bool $showCameraSelector = true;

    protected int $imageQuality = 90;

    protected string $aspect = '16:9';

    protected bool $useModal = true;

    protected ?int $captureMaxWidth = null;

    protected ?int $captureMaxHeight = null;

    protected bool $autoStart = false;

    /**
     * null  = auto (mirror the front camera, don't mirror the back camera)
     * true  = always mirror
     * false = never mirror
     */
    protected ?bool $mirroredView = null;

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

    public function mirroredView(?bool $mirrored = true): static
    {
        $this->mirroredView = $mirrored;

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

    public function getMirroredView(): ?bool
    {
        return $this->mirroredView;
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
}
