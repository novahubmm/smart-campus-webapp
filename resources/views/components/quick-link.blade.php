@props([
    'href' => '#',
    'label' => '',
    'icon' => 'fas fa-arrow-right',
])

<a href="{{ $href }}" class="flex items-center justify-between gap-3 px-4 py-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl hover:-translate-y-0.5 hover:shadow-sm transition-all duration-150 group">
    <div class="flex items-center gap-3">
        <span class="w-10 h-10 rounded-lg bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-300">
            <i class="{{ $icon }} text-sm"></i>
        </span>
        <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $label }}</span>
    </div>
    <i class="fas fa-chevron-right text-xs text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-200"></i>
</a>
