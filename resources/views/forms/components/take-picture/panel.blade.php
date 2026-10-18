{{-- The capture panel: used by both the modal and the inline layout. --}}

{{-- Header --}}
<div class="border-b border-gray-200 dark:border-white/10 px-6 py-4">
    <div class="flex items-center justify-between">
        <h3 class="text-base font-semibold text-gray-950 dark:text-white">
            @if ($isMultiple)
                <span x-show="! showingPreview" x-text="currentShot?.label || i18n.take_photo"></span>
                <span
                    x-show="showingPreview">{{ __('filament-take-picture-field::take-picture-field.review_photos') }}</span>
            @else
                <span>{{ __('filament-take-picture-field::take-picture-field.take_photo') }}</span>
            @endif
        </h3>
    </div>

    @if ($isMultiple)
        {{-- Progress --}}
        <div x-show="! showingPreview" x-cloak class="mt-3">
            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-2">
                <span x-text="`Shot ${currentShotIndex + 1} of ${shotSequence.length}`"></span>
                <span x-text="`${completedShotsCount} captured`"></span>
            </div>
            <div class="flex gap-1">
                <template x-for="(shot, idx) in shotSequence" x-bind:key="shot.key">
                    <div class="h-1.5 flex-1 rounded-full transition-colors duration-200"
                        x-bind:class="capturedShots?.[shot.key] ? 'bg-primary-600 dark:bg-primary-400' : (idx === currentShotIndex ?
                            'bg-primary-500 dark:bg-primary-500' : 'bg-gray-300 dark:bg-white/10')">
                    </div>
                </template>
            </div>
            <p x-show="currentShot?.instruction" class="mt-2 text-xs text-gray-500 dark:text-gray-400"
                x-text="currentShot?.instruction"></p>
        </div>
    @endif
</div>

{{-- Body --}}
<div class="p-6">
    @include("{$partials}.panel.viewport")

    @if ($field->getShowCameraSelector())
        @include("{$partials}.panel.camera-selector")
    @endif

    @include("{$partials}.panel.actions")
</div>
