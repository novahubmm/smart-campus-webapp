@props([
    'totalVar' => 'filteredItems.length',
    'currentPageVar' => 'currentPage',
    'perPageVar' => 'perPage',
    'totalPagesVar' => 'totalPages',
    'visiblePagesVar' => 'visiblePages',
    'goToPageFn' => 'goToPage',
])

<div x-show="{{ $totalVar }} > 0" class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex flex-wrap items-center justify-between gap-3">
    <div class="text-sm text-gray-600 dark:text-gray-400">
        {{ __('pagination.Showing') }} <span x-text="Math.min(({{ $currentPageVar }} - 1) * {{ $perPageVar }} + 1, {{ $totalVar }})"></span> {{ __('pagination.to') }} <span x-text="Math.min({{ $currentPageVar }} * {{ $perPageVar }}, {{ $totalVar }})"></span> {{ __('pagination.of') }} <span x-text="{{ $totalVar }}"></span> {{ __('pagination.results') }}
    </div>
    <div class="flex items-center gap-1">
        <button type="button" @click="{{ $goToPageFn }}(1)" :disabled="{{ $currentPageVar }} === 1" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
            <i class="fas fa-angle-double-left"></i>
        </button>
        <button type="button" @click="{{ $goToPageFn }}({{ $currentPageVar }} - 1)" :disabled="{{ $currentPageVar }} === 1" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
            <i class="fas fa-angle-left"></i>
        </button>
        <template x-for="page in {{ $visiblePagesVar }}" :key="page">
            <button type="button" @click="{{ $goToPageFn }}(page)" :class="page === {{ $currentPageVar }} ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-3 py-1.5 text-sm font-medium rounded-lg border" x-text="page"></button>
        </template>
        <button type="button" @click="{{ $goToPageFn }}({{ $currentPageVar }} + 1)" :disabled="{{ $currentPageVar }} === {{ $totalPagesVar }}" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
            <i class="fas fa-angle-right"></i>
        </button>
        <button type="button" @click="{{ $goToPageFn }}({{ $totalPagesVar }})" :disabled="{{ $currentPageVar }} === {{ $totalPagesVar }}" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
            <i class="fas fa-angle-double-right"></i>
        </button>
    </div>
</div>
