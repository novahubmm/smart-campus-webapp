@props([
    'title' => '',
    'rows' => [],
])

<div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
    @if($title)
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
        </div>
    @endif
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @if(!empty($rows))
                    @foreach($rows as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-4 text-sm font-semibold text-gray-600 dark:text-gray-400 whitespace-nowrap" style="width: 200px;">
                                {{ $row['label'] }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                {!! $row['value'] !!}
                            </td>
                        </tr>
                    @endforeach
                @elseif(!$slot->isEmpty())
                    {{ $slot }}
                @else
                    <tr>
                        <td colspan="2" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                            {{ __('components.No data available') }}
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
