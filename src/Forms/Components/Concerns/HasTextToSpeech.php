<?php

namespace emmanpbarrameda\FilamentTakePictureField\Forms\Components\Concerns;

trait HasTextToSpeech
{
    protected bool $enableTTS = true;

    protected string $ttsLang = 'en-US';

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
}
