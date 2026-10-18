{{--
The captured or saved photos (multiple mode).
--}}
<template x-if="photoData && typeof photoData === 'object'">
    <div class="rounded-lg bg-white p-2 shadow-sm ring-1 ring-gray-950/10 dark:bg-white/5 dark:ring-white/20"
        x-bind:class="{ 'bg-gray-50 dark:bg-transparent dark:ring-white/10': isDisabled }">
        <div class="grid grid-cols-2 gap-2 lg:grid-cols-3">
            <template x-for="shot in shotSequence" x-bind:key="shot.key">
                <div>
                    {{-- Captured shot --}}
                    <template x-if="photoData?.[shot.key]">
                        <div class="group relative aspect-video overflow-hidden rounded-md bg-gray-900">
                            <img x-bind:src="getImageUrl(photoData[shot.key])" x-bind:alt="shot.label || shot.key"
                                class="size-full cursor-zoom-in object-cover transition duration-300 ease-out group-hover:scale-105 group-hover:brightness-75"
                                x-on:click.stop="openImage(photoData[shot.key])" />

                            {{-- Hover hint --}}
                            <div
                                class="pointer-events-none absolute inset-0 flex items-center justify-center opacity-0 transition duration-200 group-hover:opacity-100">
                                <span class="grid size-9 place-items-center rounded-full bg-black/60 text-white">
                                    <x-filament::icon icon="heroicon-m-magnifying-glass-plus" class="size-5" />
                                </span>
                            </div>

                            <div
                                class="pointer-events-none absolute inset-x-0 top-0 flex items-start gap-1.5 bg-linear-to-b from-black/70 to-transparent p-2 pb-8 text-white">
                                <button x-show="! isDisabled" type="button" x-on:click.stop="removeShot(shot.key)"
                                    class="pointer-events-auto grid size-6 shrink-0 place-items-center rounded-full bg-black/50 transition hover:bg-black/70"
                                    title="{{ __('filament-take-picture-field::take-picture-field.delete') }}">
                                    <x-filament::icon icon="heroicon-m-x-mark" class="size-4" />
                                </button>

                                <p class="min-w-0 flex-1 truncate pt-0.5 text-xs font-medium"
                                    x-text="shot.label || shot.key"></p>

                                <button x-show="! isDisabled" type="button"
                                    x-on:click.stop="captureShotFromGallery(shot.key)"
                                    class="pointer-events-auto grid size-6 shrink-0 place-items-center rounded-full bg-black/50 transition hover:bg-black/70"
                                    x-bind:title="i18n.retake">
                                    <x-filament::icon icon="heroicon-m-camera" class="size-3.5" />
                                </button>
                            </div>
                        </div>
                    </template>

                    {{-- Missing shot --}}
                    <template x-if="! photoData?.[shot.key]">
                        <button type="button" x-on:click="! isDisabled && captureShotFromGallery(shot.key)"
                            x-bind:disabled="isDisabled"
                            class="flex aspect-video w-full flex-col items-center justify-center gap-1 rounded-md border border-dashed border-gray-300 text-gray-500 transition hover:border-primary-500 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-white/15 dark:text-gray-400 dark:hover:border-primary-400 dark:hover:bg-white/5">
                            <x-filament::icon icon="heroicon-o-camera" class="size-5" />
                            <span class="max-w-full truncate px-2 text-xs" x-text="shot.label || shot.key"></span>
                        </button>
                    </template>
                </div>
            </template>
        </div>

        {{-- Footer --}}
        <div class="mt-2 flex items-center justify-between gap-2 px-1">
            <p class="text-sm text-gray-500 dark:text-gray-400"
                x-text="`${completedShotsCount}/${shotSequence.length} captured`"></p>

            <div x-show="! isDisabled">
                <x-filament::link tag="button" color="gray" size="sm" icon="heroicon-m-trash"
                    x-on:click="clearAllPhotos()">
                    {{ __('filament-take-picture-field::take-picture-field.clear_all') }}
                </x-filament::link>
            </div>
        </div>
    </div>
</template>
