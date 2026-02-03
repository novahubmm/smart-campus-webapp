@props([
    'title',
    'addButtonText' => null,
    'addButtonAction' => null,
    'filters' => [],
    'columns' => [],
    'data' => [],
    'actions' => [],
    'emptyMessage' => 'No data found',
    'emptyIcon' => 'fas fa-inbox',
    'showFilters' => true,
])

<div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
    <!-- Section Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
        @if($addButtonText && $addButtonAction)
            <button type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700" onclick="{{ $addButtonAction }}">
                <i class="fas fa-plus"></i>{{ $addButtonText }}
            </button>
        @endif
    </div>

    <!-- Filters (if provided) -->
    @if($showFilters && count($filters) > 0)
        <form method="GET" class="p-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-{{ min(count($filters) + 1, 7) }} gap-3">
                @foreach($filters as $filter)
                    <div class="flex flex-col gap-1">
                        <label for="{{ $filter['id'] }}" class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ $filter['label'] }}</label>
                        
                        @if($filter['type'] === 'select')
                            <select 
                                id="{{ $filter['id'] }}" 
                                name="{{ $filter['name'] }}" 
                                class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">{{ $filter['placeholder'] ?? 'All' }}</option>
                                @foreach($filter['options'] as $value => $label)
                                    <option value="{{ $value }}" {{ request($filter['name']) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        @elseif($filter['type'] === 'text')
                            <input 
                                type="text" 
                                id="{{ $filter['id'] }}" 
                                name="{{ $filter['name'] }}" 
                                class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="{{ $filter['placeholder'] ?? '' }}"
                                value="{{ request($filter['name']) }}"
                            >
                        @elseif($filter['type'] === 'date')
                            <input 
                                type="date" 
                                id="{{ $filter['id'] }}" 
                                name="{{ $filter['name'] }}" 
                                class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500"
                                value="{{ request($filter['name']) }}"
                            >
                        @elseif($filter['type'] === 'month')
                            <input 
                                type="month" 
                                id="{{ $filter['id'] }}" 
                                name="{{ $filter['name'] }}" 
                                class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500"
                                value="{{ request($filter['name']) }}"
                            >
                        @endif
                    </div>
                @endforeach
                
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 px-3 py-2 text-sm font-semibold rounded-lg text-white bg-gray-800 dark:bg-gray-700 hover:bg-gray-900 dark:hover:bg-gray-600">{{ __('components.Apply') }}</button>
                    <a href="{{ url()->current() }}" class="px-3 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">{{ __('components.Reset') }}</a>
                </div>
            </div>
        </form>
    @endif

    <!-- Table with Horizontal Scroll -->
    <div class="overflow-x-auto -mx-4 sm:mx-0">
        <div class="inline-block min-w-full align-middle">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        @foreach($columns as $column)
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                {{ $column['label'] }}
                            </th>
                        @endforeach
                        @if(count($actions) > 0)
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('components.Actions') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($data as $row)
                        <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            @foreach($columns as $column)
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 {{ $column['class'] ?? '' }}">
                                    @if(isset($column['render']))
                                        {!! $column['render']($row) !!}
                                    @else
                                        {{ data_get($row, $column['field']) ?? '—' }}
                                    @endif
                                </td>
                            @endforeach
                            
                            @if(count($actions) > 0)
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        @foreach($actions as $action)
                                            @if($action['type'] === 'link')
                                                <a 
                                                    href="{{ $action['url']($row) }}" 
                                                    class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-blue-500 flex items-center justify-center hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30" 
                                                    title="{{ $action['title'] ?? '' }}"
                                                >
                                                    <i class="{{ $action['icon'] }} text-xs"></i>
                                                </a>
                                            @elseif($action['type'] === 'delete')
                                                @php $deleteUrl = $action['url']($row); @endphp
                                                @if($deleteUrl)
                                                    <form 
                                                        method="POST" 
                                                        action="{{ $deleteUrl }}" 
                                                        style="display: inline;"
                                                        @submit.prevent="$dispatch('confirm-show', {
                                                            title: '{{ $action['confirmTitle'] ?? __('Delete') }}',
                                                            message: '{{ $action['confirmMessage'] ?? __('Are you sure you want to delete this item? This action cannot be undone.') }}',
                                                            confirmText: '{{ $action['confirmText'] ?? __('Delete') }}',
                                                            cancelText: '{{ $action['cancelText'] ?? __('Cancel') }}',
                                                            onConfirm: () => $el.submit()
                                                        })"
                                                    >
                                                        @csrf
                                                        @method('DELETE')
                                                        <button 
                                                            type="submit" 
                                                            class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-red-500 flex items-center justify-center hover:border-red-400 hover:bg-red-50 dark:hover:bg-red-900/30" 
                                                            title="{{ $action['title'] ?? __('Delete') }}"
                                                        >
                                                            <i class="{{ $action['icon'] ?? 'fas fa-trash' }} text-xs"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <button 
                                                        type="button" 
                                                        disabled
                                                        class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-gray-400 dark:text-gray-600 flex items-center justify-center opacity-50 cursor-not-allowed" 
                                                        title="{{ __('components.Cannot delete') }}"
                                                    >
                                                        <i class="{{ $action['icon'] ?? 'fas fa-trash' }} text-xs"></i>
                                                    </button>
                                                @endif
                                            @elseif($action['type'] === 'button')
                                                <button 
                                                    type="button"
                                                    class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 {{ $action['color'] ?? 'text-gray-500' }} flex items-center justify-center hover:border-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700" 
                                                    title="{{ $action['title'] ?? '' }}"
                                                    onclick="{{ $action['onclick']($row) }}"
                                                >
                                                    <i class="{{ $action['icon'] }} text-xs"></i>
                                                </button>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($columns) + (count($actions) > 0 ? 1 : 0) }}" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                    <i class="{{ $emptyIcon }} text-4xl mb-3 opacity-50"></i>
                                    <p class="text-sm">{{ $emptyMessage }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if(method_exists($data, 'links') && $data->total() > 0)
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex flex-wrap items-center justify-between gap-3">
            <div class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('pagination.Showing') }} {{ $data->firstItem() ?? 0 }} {{ __('pagination.to') }} {{ $data->lastItem() ?? 0 }} {{ __('pagination.of') }} {{ $data->total() }} {{ __('pagination.results') }}
            </div>
            @if($data->hasPages())
                <div class="flex items-center gap-1">
                    {{-- First Page --}}
                    <a href="{{ $data->url(1) }}" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 {{ $data->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    {{-- Previous Page --}}
                    <a href="{{ $data->previousPageUrl() ?? '#' }}" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 {{ $data->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}">
                        <i class="fas fa-angle-left"></i>
                    </a>
                    {{-- Page Numbers --}}
                    @php
                        $currentPage = $data->currentPage();
                        $lastPage = $data->lastPage();
                        $start = max(1, $currentPage - 2);
                        $end = min($lastPage, $start + 4);
                        if ($end - $start < 4) $start = max(1, $end - 4);
                    @endphp
                    @for($page = $start; $page <= $end; $page++)
                        <a href="{{ $data->url($page) }}" class="px-3 py-1.5 text-sm font-medium rounded-lg border {{ $page === $currentPage ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            {{ $page }}
                        </a>
                    @endfor
                    {{-- Next Page --}}
                    <a href="{{ $data->nextPageUrl() ?? '#' }}" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 {{ !$data->hasMorePages() ? 'opacity-50 pointer-events-none' : '' }}">
                        <i class="fas fa-angle-right"></i>
                    </a>
                    {{-- Last Page --}}
                    <a href="{{ $data->url($data->lastPage()) }}" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 {{ !$data->hasMorePages() ? 'opacity-50 pointer-events-none' : '' }}">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                </div>
            @endif
        </div>
    @endif
</div>
