@props(['message' => null])

<div
    x-data="{ show: true }"
    x-show="show"
    x-init="setTimeout(() => show = false, 7000)"
    class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4"
>
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 px-4 py-3 rounded-lg relative flex items-start gap-3">
        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-100 dark:bg-red-800 text-red-700 dark:text-red-200">
            <i class="fas fa-exclamation-circle"></i>
        </span>
        <span class="block text-sm sm:text-base">{{ $message ?? $slot }}</span>
        <button @click="show = false" class="absolute top-2 right-2 text-red-600 dark:text-red-300 hover:text-red-800 dark:hover:text-red-100">
            <span class="sr-only">{{ __('components.Close') }}</span>
            <i class="fas fa-times text-sm"></i>
        </button>
    </div>
</div>
