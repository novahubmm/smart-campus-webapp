<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-lg">
                <i class="fas fa-clipboard-list"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('teacher_profiles.Free Period Activities') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ $profile->user->name }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10 overflow-x-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <x-back-link :href="route('teacher-profiles.index')" :text="__('teacher_profiles.Back to Teachers')" />

            <!-- Teacher Info Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center text-white text-2xl font-bold">
                        {{ strtoupper(substr($profile->user->name, 0, 1)) }}
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $profile->user->name }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $profile->employee_id ?? '—' }} • {{ $profile->department?->name ?? '—' }}</p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <form method="GET" class="flex flex-wrap items-end gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Start Date') }}</label>
                        <input type="date" name="start_date" value="{{ $filters['start_date'] }}" 
                               class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.End Date') }}</label>
                        <input type="date" name="end_date" value="{{ $filters['end_date'] }}" 
                               class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('teacher_profiles.Activity Type') }}</label>
                        <select name="activity_type" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 text-sm">
                            <option value="">{{ __('teacher_profiles.All Types') }}</option>
                            @foreach($activityTypes as $type)
                                <option value="{{ $type->code }}" {{ $filters['activity_type'] === $type->code ? 'selected' : '' }}>
                                    {{ $type->localized_label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">
                        <i class="fas fa-filter mr-1"></i> {{ __('teacher_profiles.Filter') }}
                    </button>
                </form>
            </div>

            <!-- Summary Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('teacher_profiles.Total Activities') }}</p>
                    <p class="text-2xl font-bold text-indigo-600">{{ number_format($summary['total_activities']) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('teacher_profiles.Total Time') }}</p>
                    <p class="text-2xl font-bold text-emerald-600">{{ floor($summary['total_minutes'] / 60) }}h {{ $summary['total_minutes'] % 60 }}m</p>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 col-span-2">
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">{{ __('teacher_profiles.By Activity Type') }}</p>
                    <div class="flex flex-wrap gap-2">
                        @forelse($summary['by_type'] as $label => $data)
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium text-white" style="background-color: {{ $data['color'] }}">
                                {{ $label }}: {{ $data['count'] }}
                            </span>
                        @empty
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('teacher_profiles.No activities') }}</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Activities List -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('teacher_profiles.Activity Log') }}</h3>
                </div>

                @if($activities->count())
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($activities as $activity)
                            <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <div class="flex items-start gap-4">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white flex-shrink-0 activity-icon" style="background-color: {{ $activity->activityType?->color ?? '#6B7280' }}">
                                        @if($activity->activityType?->icon_svg)
                                            <div class="w-5 h-5">
                                                {!! $activity->activityType->icon_svg !!}
                                            </div>
                                        @else
                                            <i class="fas fa-clipboard-check text-sm"></i>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-semibold text-gray-900 dark:text-white">{{ $activity->activityType?->localized_label ?? '—' }}</span>
                                            <span class="text-xs px-2 py-0.5 rounded-full text-white" style="background-color: {{ $activity->activityType?->color ?? '#6B7280' }}">
                                                {{ $activity->duration_minutes }} min
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                            <i class="fas fa-calendar mr-1"></i> {{ $activity->date->format('D, M j, Y') }}
                                            <span class="mx-2">•</span>
                                            <i class="fas fa-clock mr-1"></i> {{ \Carbon\Carbon::parse($activity->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($activity->end_time)->format('H:i') }}
                                        </p>
                                        @if($activity->notes)
                                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-2 bg-gray-50 dark:bg-gray-900/50 rounded-lg p-2">
                                                {{ $activity->notes }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                <!-- Pagination -->
                <x-table-pagination :paginator="$activities" />
                @else
                    <div class="p-12 text-center">
                        <i class="fas fa-clipboard-list text-5xl text-gray-300 dark:text-gray-600 mb-4"></i>
                        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('teacher_profiles.No activities found') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('teacher_profiles.No activities recorded for the selected period.') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        /* Style SVG icons in activity log */
        .activity-icon svg {
            width: 20px;
            height: 20px;
            fill: currentColor;
        }
    </style>
</x-app-layout>
