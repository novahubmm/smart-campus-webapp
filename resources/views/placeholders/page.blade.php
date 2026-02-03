<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-200">
                <i class="fas fa-hammer"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('placeholders.Coming soon') }}</p>
                <h2 class="font-semibold text-lg text-gray-800 dark:text-gray-100 leading-tight">
                    {{ $title }}
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm p-8 space-y-4">
                <p class="text-sm text-gray-700 dark:text-gray-200 leading-relaxed">{{ $description }}</p>
                <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-6 text-sm text-gray-600 dark:text-gray-300">
                    {{ __('placeholders.This page will be wired to live data and CRUD soon. Admins can already access it via navigation.') }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
