@php
    $statePath = $getStatePath();
    $isDisabled = $field->isDisabled();
    $isMultiple = $field->getMultipleMode();
    $partials = 'filament-take-picture-field::forms.components.take-picture';
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div wire:key="{{ $statePath }}-{{ $isDisabled ? 'disabled' : 'enabled' }}" x-load
        x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('take-picture', 'emmanpbarrameda/filament-take-picture-field') }}"
        x-data="takePicture({
            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }},
            config: @js($field->getJsConfig()),
            i18n: @js($field->getJsTranslations()),
        })" x-on:keydown.escape.window="onEscape()" class="w-full">
        <div class="w-full">
            @include("{$partials}.empty-state")

            @if ($isMultiple)
                @include("{$partials}.preview-multiple")
            @else
                @include("{$partials}.preview-single")
            @endif
        </div>

        @include("{$partials}.error")

        <input type="hidden" {{ $applyStateBindingModifiers('wire:model') }}="{{ $statePath }}">

        @if ($field->getUseModal())
            @include("{$partials}.modal")
        @else
            @include("{$partials}.inline")
        @endif
    </div>
</x-dynamic-component>
