@props(['message' => null])

<div
    x-data="{ show: true }"
    x-show="show"
    x-init="setTimeout(() => show = false, 5000)"
    class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4"
>
    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 px-4 py-3 rounded-lg relative flex items-start gap-3">
        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-100 dark:bg-green-800 text-green-700 dark:text-green-200">
            <i class="fas fa-check"></i>
        </span>
        <span class="block text-sm sm:text-base">{{ $message ?? $slot }}</span>
        <button @click="show = false" class="absolute top-2 right-2 text-green-600 dark:text-green-300 hover:text-green-800 dark:hover:text-green-100">
            <span class="sr-only">{{ __('components.Close') }}</span>
            <i class="fas fa-times text-sm"></i>
        </button>
    </div>
</div>
