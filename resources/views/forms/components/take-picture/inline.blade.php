{{-- Capture panel shown in place under the field (useModal(false)). --}}
<div x-cloak x-show="modalOpen"
    class="mt-3 w-full rounded-xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10"
    style="display: none;">
    @include("{$partials}.panel")
</div>
