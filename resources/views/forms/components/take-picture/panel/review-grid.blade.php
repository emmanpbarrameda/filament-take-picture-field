{{-- Review grid shown after all shots are taken (multiple mode). --}}
<div x-show="showingPreview" class="space-y-4 p-3">
    <p class="text-center font-medium text-gray-900 dark:text-white">
        {{ __('filament-take-picture-field::take-picture-field.review_your_photos') }}
    </p>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2">
        <template x-for="shot in shotSequence" x-bind:key="shot.key">
            <div class="group relative aspect-video rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800 ring-1 ring-gray-950/5 dark:ring-white/10 cursor-pointer"
                x-on:click="selectShotFromReview(shot?.key)">
                <img x-show="capturedShots?.[shot.key]"
                    x-bind:src="capturedShots?.[shot.key] ? getImageUrl(capturedShots[shot.key]) : ''"
                    class="w-full h-full object-cover" alt="">

                <div x-show="! capturedShots?.[shot.key]" class="flex items-center justify-center h-full">
                    <x-filament::icon icon="heroicon-o-camera" class="h-5 w-5 text-gray-400" />
                </div>

                <div
                    class="absolute inset-0 bg-black/0 group-hover:bg-black/30 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                    <x-filament::icon icon="heroicon-m-camera" class="h-6 w-6 text-white" />
                </div>

                <div class="absolute bottom-0 left-0 right-0 bg-black/50 px-1.5 py-1">
                    <p class="text-[10px] leading-none text-white truncate text-center" x-text="shot.label || shot.key">
                    </p>
                </div>
            </div>
        </template>
    </div>

    <p x-show="requireAllShots && ! allShotsCompleted" class="text-center text-xs text-gray-500 dark:text-gray-400">
        {{ __('filament-take-picture-field::take-picture-field.missing_required_photos') }}
    </p>
</div>
