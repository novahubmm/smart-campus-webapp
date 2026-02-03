@props([
    'title' => '',
    'columns' => [],
    'rows' => [],
    'emptyMessage' => 'No data available',
    'emptyIcon' => 'fas fa-inbox',
])

<div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
    @if($title)
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
        </div>
    @endif
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            @if(!empty($columns))
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        @foreach($columns as $column)
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">
                                {{ $column }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
            @endif
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @if(!empty($rows) && count($rows) > 0)
                    {{ $slot }}
                @else
                    <tr>
                        <td colspan="{{ count($columns) }}" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                <i class="{{ $emptyIcon }} text-4xl mb-3 opacity-50"></i>
                                <p class="text-sm">{{ $emptyMessage }}</p>
                            </div>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
