{{-- Capture panel shown in a modal (useModal(true), the default). --}}
<template x-teleport="body">
    <div x-cloak x-show="modalOpen" x-on:click.self="onBackdropClick()"
        class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-950/50 p-4"
        style="display: none;">
        <div x-on:click.stop
            class="w-full max-w-xl rounded-xl bg-white dark:bg-gray-900 shadow-xl ring-1 ring-gray-950/5 dark:ring-white/10">
            @include("{$partials}.panel")
        </div>
    </div>
</template>
