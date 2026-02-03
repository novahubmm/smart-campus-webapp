<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('reports.index') }}" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                <i class="fas fa-arrow-left"></i>
            </a>
            <x-page-header icon="fas fa-inbox" iconBg="bg-indigo-50 dark:bg-indigo-900/30" iconColor="text-indigo-700 dark:text-indigo-200" :subtitle="__('From Teachers & Assistants')" :title="__('Incoming Reports')" />
        </div>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                            <i class="fas fa-file-alt text-gray-600 dark:text-gray-400"></i>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Total') }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center">
                            <i class="fas fa-calendar-day text-blue-600 dark:text-blue-400"></i>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['today'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Today') }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center">
                            <i class="fas fa-calendar-week text-purple-600 dark:text-purple-400"></i>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $stats['this_week'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('This Week') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                <form method="GET" action="{{ route('reports.incoming') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Report Type') }}</label>
                        <select name="report_type" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                            <option value="">{{ __('All Types') }}</option>
                            <option value="daily" {{ request('report_type') === 'daily' ? 'selected' : '' }}>{{ __('Daily') }}</option>
                            <option value="weekly" {{ request('report_type') === 'weekly' ? 'selected' : '' }}>{{ __('Weekly') }}</option>
                            <option value="incident" {{ request('report_type') === 'incident' ? 'selected' : '' }}>{{ __('Incident') }}</option>
                            <option value="progress" {{ request('report_type') === 'progress' ? 'selected' : '' }}>{{ __('Progress') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Class') }}</label>
                        <select name="class_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                            <option value="">{{ __('All Classes') }}</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') === $class->id ? 'selected' : '' }}>
                                    {{ $class->grade?->level ? 'G'.$class->grade->level.' - ' : '' }}{{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('From Date') }}</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('To Date') }}</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <i class="fas fa-search mr-1"></i>{{ __('Filter') }}
                        </button>
                        <a href="{{ route('reports.incoming') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Reports List -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                @if($reports->count())
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($reports as $report)
                            <a href="{{ route('reports.incoming.show', $report->id) }}" class="block p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <div class="flex flex-col md:flex-row md:items-center gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="font-semibold text-gray-900 dark:text-white">
                                                {{ $report->class?->name ?? 'Unknown Class' }}
                                            </span>
                                            @if($report->class?->grade)
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    Grade {{ $report->class->grade->level }}
                                                </span>
                                            @endif
                                            @php
                                                $typeBadges = [
                                                    'daily' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                                                    'weekly' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
                                                    'incident' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                                                    'progress' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
                                                ];
                                            @endphp
                                            <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $typeBadges[$report->report_type] ?? 'bg-gray-100 text-gray-700' }}">
                                                {{ ucfirst($report->report_type) }}
                                            </span>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-3 text-sm text-gray-600 dark:text-gray-400">
                                            <span><i class="fas fa-user mr-1"></i>{{ $report->teacher?->user?->name ?? 'Unknown' }}</span>
                                            <span><i class="fas fa-calendar mr-1"></i>{{ $report->report_date->format('M d, Y') }}</span>
                                        </div>
                                        @if($report->summary)
                                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">{{ $report->summary }}</p>
                                        @endif
                                    </div>
                                    <div class="flex items-center">
                                        <span class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-gray-500 dark:text-gray-400">
                                            {{ __('View') }} <i class="fas fa-chevron-right text-xs"></i>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                        {{ $reports->withQueryString()->links() }}
                    </div>
                @else
                    <div class="p-12 text-center">
                        <i class="fas fa-inbox text-5xl text-gray-300 dark:text-gray-600 mb-4"></i>
                        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('No reports found') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Daily reports from teachers will appear here.') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
