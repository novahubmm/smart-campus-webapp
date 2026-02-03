@props([
    'href' => '#',
    'text' => 'Back',
    'icon' => 'fas fa-arrow-left',
])

<div class="mb-6">
    <a href="{{ $href }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
        <i class="{{ $icon }}"></i>
        <span>{{ $text }}</span>
    </a>
</div>
