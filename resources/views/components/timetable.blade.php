@props([
    'title' => '',
    'weekDays' => ['mon', 'tue', 'wed', 'thu', 'fri'],
    'periods' => 6,
    'entries' => [],
    'periodLabels' => [],
])

@php
    $dayLabel = fn(string $day) => ucfirst($day);
    $periodNumbers = range(1, max(1, (int) $periods));
@endphp

<div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900/40">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wide">
                        {{ __('components.Period') }}
                    </th>
                    @foreach($weekDays as $day)
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wide">
                            {{ $dayLabel($day) }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($periodNumbers as $period)
                    <tr>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-gray-100">
                            <div>#{{ $period }}</div>
                            @if(!empty($periodLabels[$period]))
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $periodLabels[$period] }}</div>
                            @endif
                        </td>
                        @foreach($weekDays as $day)
                            @php
                                $cell = $entries[$day][$period] ?? null;
                            @endphp
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 align-top">
                                @if($cell)
                                    <div class="font-semibold">{{ $cell['subject'] ?? __('academic_management.Subject') }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $cell['teacher'] ?? __('academic_management.Teacher') }}</div>
                                    @if(!empty($cell['room']))
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $cell['room'] }}</div>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400">{{ __('components.No data available') }}</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($weekDays) + 1 }}" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                            {{ __('components.No data available') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
