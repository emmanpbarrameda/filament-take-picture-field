{{-- Shown when no photo has been captured yet. --}}
<template x-if="! photoData">
    <button type="button" x-on:click="! isDisabled && startCamera()" x-bind:disabled="isDisabled"
        class="relative w-full rounded-lg border-2 border-dashed bg-white shadow-sm border-gray-300 hover:border-primary-500 hover:bg-gray-50 dark:bg-white/5 dark:border-white/10 dark:hover:border-primary-400 dark:hover:bg-white/10 ring-1 ring-transparent dark:ring-white/10 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-600/30 dark:focus:ring-primary-400/30"
        x-bind:class="{
            'cursor-not-allowed opacity-50': isDisabled,
            'cursor-pointer': !isDisabled,
        }">
        <div class="flex flex-col items-center justify-center py-8 px-4">
            <div class="mb-3 rounded-full p-3 bg-gray-100 dark:bg-white/10">
                <x-filament::icon icon="heroicon-o-camera" class="h-6 w-6 text-gray-500 dark:text-gray-200" />
            </div>

            <p class="text-sm font-medium text-gray-900 dark:text-white">
                @if ($isMultiple)
                    {{ __('filament-take-picture-field::take-picture-field.capture_device_photos') }}
                @else
                    {{ __('filament-take-picture-field::take-picture-field.click_to_capture') }}
                @endif
            </p>

            <p class="mt-1 text-xs text-gray-500 dark:text-gray-300">
                @if ($isMultiple)
                    {{ __('filament-take-picture-field::take-picture-field.photos_required', ['count' => count($field->getShotSequence())]) }}
                @else
                    {{ __('filament-take-picture-field::take-picture-field.opens_camera_hint') }}
                @endif
            </p>
        </div>
    </button>
</template>
