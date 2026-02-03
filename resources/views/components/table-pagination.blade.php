@props(['paginator', 'tabParam' => null])

@php
    // Helper function to append tab parameter to URL
    $appendTab = function($url) use ($tabParam) {
        if (!$tabParam || !$url) return $url;
        $separator = str_contains($url, '?') ? '&' : '?';
        return $url . $separator . 'tab=' . $tabParam;
    };
@endphp

@if($paginator->hasPages())
<div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex flex-wrap items-center justify-between gap-3">
    <div class="text-sm text-gray-600 dark:text-gray-400">
        Showing
        <span class="font-medium">{{ $paginator->firstItem() }}</span>
        to
        <span class="font-medium">{{ $paginator->lastItem() }}</span>
        of
        <span class="font-medium">{{ $paginator->total() }}</span>
        results
    </div>
    <div class="flex items-center gap-1">
        {{-- First Page --}}
        @if ($paginator->onFirstPage())
            <span class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-400 dark:text-gray-500 cursor-not-allowed">
                <i class="fas fa-angle-double-left"></i>
            </span>
        @else
            <a href="{{ $appendTab($paginator->url(1)) }}" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                <i class="fas fa-angle-double-left"></i>
            </a>
        @endif

        {{-- Previous Page --}}
        @if ($paginator->onFirstPage())
            <span class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-400 dark:text-gray-500 cursor-not-allowed">
                <i class="fas fa-angle-left"></i>
            </span>
        @else
            <a href="{{ $appendTab($paginator->previousPageUrl()) }}" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                <i class="fas fa-angle-left"></i>
            </a>
        @endif

        {{-- Page Numbers --}}
        @php
            $start = max(1, $paginator->currentPage() - 2);
            $end = min($paginator->lastPage(), $paginator->currentPage() + 2);
        @endphp
        
        @if($start > 1)
            <a href="{{ $appendTab($paginator->url(1)) }}" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">1</a>
            @if($start > 2)
                <span class="px-2 py-1.5 text-sm text-gray-500 dark:text-gray-400">...</span>
            @endif
        @endif

        @for ($i = $start; $i <= $end; $i++)
            @if ($i == $paginator->currentPage())
                <span class="px-3 py-1.5 text-sm font-medium rounded-lg border bg-blue-600 text-white border-blue-600">{{ $i }}</span>
            @else
                <a href="{{ $appendTab($paginator->url($i)) }}" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">{{ $i }}</a>
            @endif
        @endfor

        @if($end < $paginator->lastPage())
            @if($end < $paginator->lastPage() - 1)
                <span class="px-2 py-1.5 text-sm text-gray-500 dark:text-gray-400">...</span>
            @endif
            <a href="{{ $appendTab($paginator->url($paginator->lastPage())) }}" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">{{ $paginator->lastPage() }}</a>
        @endif

        {{-- Next Page --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $appendTab($paginator->nextPageUrl()) }}" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                <i class="fas fa-angle-right"></i>
            </a>
        @else
            <span class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-400 dark:text-gray-500 cursor-not-allowed">
                <i class="fas fa-angle-right"></i>
            </span>
        @endif

        {{-- Last Page --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $appendTab($paginator->url($paginator->lastPage())) }}" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                <i class="fas fa-angle-double-right"></i>
            </a>
        @else
            <span class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-400 dark:text-gray-500 cursor-not-allowed">
                <i class="fas fa-angle-double-right"></i>
            </span>
        @endif
    </div>
</div>
@endif
