{{--
    The dark area of the capture panel: live camera, review grid / captured
    preview, errors, and the buttons laid over the camera.
--}}
<div class="relative rounded-lg overflow-hidden bg-gray-950 mb-4"
    style="isolation: isolate; -webkit-transform: translateZ(0);">

    {{-- Live camera --}}
    <div x-show="webcamActive && ! webcamError" class="aspect-video flex items-center justify-center">
        <video x-ref="video" autoplay playsinline webkit-playsinline muted
            x-bind:style="mirroredView ? 'transform: scaleX(-1);' : ''"
            class="max-w-full max-h-[60vh] object-contain"></video>
    </div>

    @if ($isMultiple)
        @include("{$partials}.panel.review-grid")
    @else
        {{-- Captured photo, before "Use photo" --}}
        <template x-if="showingPreview && photoData && ! webcamActive">
            <div class="aspect-video flex items-center justify-center bg-gray-900">
                <img x-bind:src="getImageUrl(photoData)" class="max-w-full max-h-[60vh] object-contain"
                    alt="Captured preview">
            </div>
        </template>
    @endif

    {{-- Error --}}
    <div x-show="webcamError"
        class="aspect-video bg-gray-900 flex flex-col items-center justify-center text-center p-6">
        <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-12 w-12 text-danger-500 mb-4" />
        <span class="text-white text-lg font-medium" x-text="webcamError"></span>

        <x-filament::button color="primary" size="sm" class="mt-4"
            x-on:click="webcamError = null; startCamera()">
            {{ __('filament-take-picture-field::take-picture-field.try_again') }}
        </x-filament::button>
    </div>

    {{-- Capture button --}}
    <div x-show="webcamActive && ! webcamError && ! showingPreview"
        class="absolute bottom-4 left-0 right-0 flex justify-center">
        <button type="button" x-on:click="capturePhoto()"
            class="w-16 h-16 rounded-full bg-primary-600 hover:bg-primary-500 border-4 border-white flex items-center justify-center shadow-lg transition-colors"
            title="{{ __('filament-take-picture-field::take-picture-field.take_photo') }}">
            <x-filament::icon icon="heroicon-s-camera" class="h-8 w-8 text-white" />
        </button>
    </div>

    {{-- Mirror toggle --}}
    <div x-show="webcamActive && ! webcamError && ! showingPreview" class="absolute top-4 right-4">
        <button type="button" x-on:click="toggleMirror()"
            class="w-10 h-10 rounded-full bg-gray-900/70 text-white flex items-center justify-center hover:bg-gray-900/90 transition-colors"
            x-bind:class="{ 'ring-2 ring-primary-500': mirroredView }"
            x-bind:title="mirroredView ? i18n.disable_mirror : i18n.enable_mirror">
            <x-filament::icon icon="heroicon-o-arrows-right-left" class="h-5 w-5" />
        </button>
    </div>

    {{-- Switch camera (mobile) --}}
    <div x-show="webcamActive && ! webcamError && isMobile && availableCameras.length > 1 && ! showingPreview"
        class="absolute top-4 left-4">
        <button type="button" x-on:click="flipCamera()"
            class="w-10 h-10 rounded-full bg-gray-900/70 text-white flex items-center justify-center hover:bg-gray-900/90 transition-colors"
            title="{{ __('filament-take-picture-field::take-picture-field.switch_camera') }}">
            <x-filament::icon icon="heroicon-o-arrow-path" class="h-5 w-5" />
        </button>
    </div>

    {{-- Front / back camera indicator --}}
    <div x-show="webcamActive && ! webcamError && ! showingPreview" class="absolute bottom-4 left-4">
        <span class="px-2 py-1 rounded-full bg-gray-900/70 text-white text-xs">
            <span x-text="isBackCamera ? i18n.back_camera : i18n.front_camera"></span>
        </span>
    </div>
</div>
