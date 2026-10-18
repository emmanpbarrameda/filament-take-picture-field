{{-- Camera dropdown (desktop only). --}}
<div x-show="! isMobile && ! showingPreview && webcamActive" x-cloak class="mb-4">
    <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
        {{ __('filament-take-picture-field::take-picture-field.select_camera') }}
    </label>

    <select x-model="selectedCameraId" x-on:change="changeCamera($event.target.value)"
        x-bind:disabled="availableCameras.length <= 1"
        class="block w-full rounded-lg border-none bg-white text-gray-950 py-2 pe-10 ps-3 text-sm shadow-sm ring-1 ring-inset ring-gray-950/10 focus:ring-2 focus:ring-inset focus:ring-primary-600/40 disabled:opacity-60 disabled:cursor-not-allowed dark:bg-white/5 dark:text-white dark:ring-white/10 dark:focus:ring-primary-400/40">
        <template x-for="(camera, index) in availableCameras" x-bind:key="camera.deviceId">
            <option x-bind:value="camera.deviceId" class="bg-white text-gray-950 dark:bg-gray-900 dark:text-white"
                x-text="camera.label ? camera.label : `Camera ${index + 1}`"></option>
        </template>
    </select>

    <p x-show="availableCameras.length <= 1" class="mt-2 text-xs text-gray-500 dark:text-gray-300">
        {{ __('filament-take-picture-field::take-picture-field.only_one_camera_detected') }}
    </p>
</div>
