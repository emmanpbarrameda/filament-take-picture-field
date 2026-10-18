{{-- Buttons at the bottom of the capture panel. --}}
<div class="flex justify-end gap-3">
    <x-filament::button x-show="! showingPreview" color="gray" x-on:click="cancelModal()">
        {{ __('filament-take-picture-field::take-picture-field.cancel') }}
    </x-filament::button>

    <x-filament::button x-show="showingPreview" color="gray" x-on:click="retakeInModal()">
        @if ($isMultiple)
            {{ __('filament-take-picture-field::take-picture-field.retake_all') }}
        @else
            {{ __('filament-take-picture-field::take-picture-field.retake') }}
        @endif
    </x-filament::button>

    @if ($isMultiple)
        <x-filament::button x-show="showingPreview" color="primary" x-on:click="confirmAllPhotos()"
            x-bind:disabled="!allShotsCompleted">
            {{ __('filament-take-picture-field::take-picture-field.use_all_photos') }}
        </x-filament::button>
    @else
        <x-filament::button x-show="showingPreview" color="primary" x-on:click="confirmPhoto()">
            {{ __('filament-take-picture-field::take-picture-field.use_photo') }}
        </x-filament::button>
    @endif
</div>
