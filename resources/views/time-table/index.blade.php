<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg">
                <i class="fas fa-list"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('time_table.Scheduling') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('time_table.Time-table List') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10 overflow-x-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800 dark:border-green-900/50 dark:bg-green-900/30 dark:text-green-100">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800 dark:border-red-900/50 dark:bg-red-900/30 dark:text-red-100">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Quick Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-3 shadow-sm">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center shadow-lg">
                        <i class="fas fa-layer-group text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('time_table.Total') }}</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format($totals['all']) }}</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-3 shadow-sm">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-500 to-emerald-600 text-white flex items-center justify-center shadow-lg">
                        <i class="fas fa-check-circle text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('time_table.Active') }}</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format($totals['active'] ?? 0) }}</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-3 shadow-sm">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-400 to-gray-500 text-white flex items-center justify-center shadow-lg">
                        <i class="fas fa-circle text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('time_table.Inactive') }}</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format($totals['all'] - ($totals['active'] ?? 0)) }}</p>
                    </div>
                </div>
            </div>

            <!-- Time-tables List by Grade -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('time_table.Class Time-tables') }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('time_table.Click on a class to manage its time-table versions') }}</p>
                    </div>
                    <button type="button" onclick="openTimetableSettingsModal()" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                        <i class="fas fa-cog"></i>
                        <span>{{ __('time_table.Settings') }}</span>
                    </button>
                </div>

                @php
                    $groupedByGrade = $classes->groupBy(fn($c) => $c->grade?->level ?? 0)->sortKeys();
                @endphp

                @if($groupedByGrade->count())
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($groupedByGrade as $gradeLevel => $gradeClasses)
                            <div class="p-4">
                                <div class="flex items-center gap-2 mb-4">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 font-bold text-sm">
                                        {{ $gradeLevel ?: '0' }}
                                    </span>
                                    <h4 class="font-semibold text-gray-900 dark:text-white">
                                        @gradeName($gradeLevel ?: 0)
                                    </h4>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">({{ $gradeClasses->count() }} {{ __('time_table.classes') }})</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                                    @foreach($gradeClasses->sortBy('name') as $class)
                                        @php
                                            $versionCount = $timetableCounts[$class->id] ?? 0;
                                            $activeTimetable = $activeTimetables[$class->id] ?? null;
                                        @endphp
                                        <a href="{{ route('time-table.class-versions', $class) }}" 
                                           class="group bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-blue-400 dark:hover:border-blue-500 hover:shadow-md transition-all cursor-pointer">
                                            <div class="flex items-start justify-between gap-2 mb-3">
                                                <div>
                                                    <h5 class="font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                                        @className($class->name, $class->grade?->level)
                                                    </h5>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $versionCount }} {{ $versionCount === 1 ? __('time_table.version') : __('time_table.versions') }}
                                                    </p>
                                                </div>
                                                @if($activeTimetable)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-100">
                                                        <i class="fas fa-check-circle mr-1"></i>{{ __('time_table.Active') }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                                        <i class="fas fa-minus-circle mr-1"></i>{{ __('time_table.None') }}
                                                    </span>
                                                @endif
                                            </div>

                                            @if($activeTimetable)
                                                <div class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
                                                    <p><i class="fas fa-clock mr-1"></i>{{ $activeTimetable->display_name }}</p>
                                                    <p><i class="fas fa-calendar mr-1"></i>{{ __('time_table.Updated') }} {{ $activeTimetable->updated_at?->diffForHumans() }}</p>
                                                </div>
                                            @else
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('time_table.No active time-table') }}</p>
                                            @endif

                                            <div class="mt-3 flex items-center justify-between">
                                                <span class="text-xs text-blue-600 dark:text-blue-400 font-medium group-hover:underline">
                                                    {{ __('time_table.Manage Versions') }} <i class="fas fa-arrow-right ml-1"></i>
                                                </span>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-12 text-center">
                        <i class="fas fa-school text-5xl text-gray-300 dark:text-gray-600 mb-4"></i>
                        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('time_table.No classes found') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('time_table.Please create classes in Academic Management first.') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Timetable Settings Modal --}}
    <div id="timetableSettingsModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75" onclick="closeTimetableSettingsModal()"></div>
            
            <div class="relative inline-block w-full max-w-md p-6 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 rounded-xl shadow-xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-cog text-blue-500"></i>
                        {{ __('time_table.Timetable Settings') }}
                    </h3>
                    <button type="button" onclick="closeTimetableSettingsModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('time-table.global-settings') }}" id="timetableSettingsForm">
                    @csrf
                    
                    {{-- Info Banner --}}
                    <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <div class="flex items-start gap-2 text-sm text-blue-700 dark:text-blue-300">
                            <i class="fas fa-info-circle mt-0.5"></i>
                            <span>{{ __('time_table.These settings apply globally to all timetables and API responses.') }}</span>
                        </div>
                    </div>

                    {{-- Time Format --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('time_table.Time Display Format') }}</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <input type="radio" name="timetable_time_format" value="24h" class="text-blue-600 focus:ring-blue-500" {{ ($timetableSettings['time_format'] ?? '24h') === '24h' ? 'checked' : '' }}>
                                <div>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ __('time_table.24-hour format') }}</span>
                                    <span class="block text-sm text-gray-500 dark:text-gray-400">{{ __('time_table.Example: 08:00 - 15:30') }}</span>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <input type="radio" name="timetable_time_format" value="12h" class="text-blue-600 focus:ring-blue-500" {{ ($timetableSettings['time_format'] ?? '24h') === '12h' ? 'checked' : '' }}>
                                <div>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ __('time_table.12-hour format') }}</span>
                                    <span class="block text-sm text-gray-500 dark:text-gray-400">{{ __('time_table.Example: 8:00 AM - 3:30 PM') }}</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeTimetableSettingsModal()" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            {{ __('time_table.Cancel') }}
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                            {{ __('time_table.Save Settings') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openTimetableSettingsModal() {
            document.getElementById('timetableSettingsModal').classList.remove('hidden');
        }

        function closeTimetableSettingsModal() {
            document.getElementById('timetableSettingsModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
