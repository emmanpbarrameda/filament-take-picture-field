{{-- Camera error shown under the field while no capture session is open. --}}
<template x-if="webcamError && ! modalOpen">
    <div class="mt-2 rounded-lg bg-danger-50 dark:bg-danger-400/10 p-3 text-sm text-danger-600 dark:text-danger-400">
        <div class="flex items-start gap-2">
            <x-filament::icon icon="heroicon-m-exclamation-circle" class="h-5 w-5 flex-shrink-0" />
            <span x-text="webcamError"></span>
        </div>
    </div>
</template>
