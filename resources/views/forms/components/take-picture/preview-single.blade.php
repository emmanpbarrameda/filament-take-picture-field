{{--
The captured or saved photo (single mode).
--}}
<template x-if="photoData">
    <div class="rounded-lg bg-white p-2 shadow-sm ring-1 ring-gray-950/10 dark:bg-white/5 dark:ring-white/20"
        x-bind:class="{ 'bg-gray-50 dark:bg-transparent dark:ring-white/10': isDisabled }">
        <div class="group relative overflow-hidden rounded-md bg-gray-900">
            <img x-bind:src="getImageUrl(photoData)"
                alt="{{ __('filament-take-picture-field::take-picture-field.captured_photo') }}"
                class="mx-auto block max-h-72 w-full cursor-zoom-in object-contain transition duration-300 ease-out group-hover:scale-[1.03] group-hover:brightness-75"
                x-on:click.stop="handleSinglePreviewClick()">

            {{-- Hover hint --}}
            <div
                class="pointer-events-none absolute inset-0 flex items-center justify-center opacity-0 transition duration-200 group-hover:opacity-100">
                <span
                    class="flex items-center gap-1.5 rounded-full bg-black/60 px-3 py-1.5 text-xs font-medium text-white">
                    <x-filament::icon icon="heroicon-m-magnifying-glass-plus" class="size-4" />
                    {{ __('filament-take-picture-field::take-picture-field.view_full_size') }}
                </span>
            </div>

            <div
                class="pointer-events-none absolute inset-x-0 top-0 flex items-start gap-2 bg-linear-to-b from-black/70 to-transparent p-2.5 pb-10 text-white">
                <button x-show="! isDisabled" type="button" x-on:click.stop="clearPhoto()"
                    class="pointer-events-auto grid size-6 shrink-0 place-items-center rounded-full bg-black/50 transition hover:bg-black/70"
                    title="{{ __('filament-take-picture-field::take-picture-field.remove_photo') }}">
                    <x-filament::icon icon="heroicon-m-x-mark" class="size-4" />
                </button>

                <div class="min-w-0 flex-1 leading-tight">
                    <p class="truncate text-sm font-medium">
                        {{ __('filament-take-picture-field::take-picture-field.captured_photo') }}
                    </p>
                    <p class="truncate text-xs text-white/70"
                        x-text="isBase64Image() ? i18n.new_capture : i18n.saved_photo"></p>
                </div>

                <div class="flex shrink-0 gap-1.5">
                    <button type="button" x-on:click.stop="handleSinglePreviewClick()"
                        class="pointer-events-auto grid size-6 place-items-center rounded-full bg-black/50 transition hover:bg-black/70"
                        title="{{ __('filament-take-picture-field::take-picture-field.view_full_size') }}">
                        <x-filament::icon icon="heroicon-m-arrow-top-right-on-square" class="size-3.5" />
                    </button>

                    <button x-show="! isDisabled" type="button" x-on:click.stop="startCamera()"
                        class="pointer-events-auto grid size-6 place-items-center rounded-full bg-black/50 transition hover:bg-black/70"
                        title="{{ __('filament-take-picture-field::take-picture-field.retake_photo') }}">
                        <x-filament::icon icon="heroicon-m-camera" class="size-3.5" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
