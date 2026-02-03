<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg">
                <i class="fas fa-clipboard-check"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('attendance.Attendance') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('attendance.Collect Attendance') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10 overflow-x-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <!-- Date Picker -->
            <div class="flex items-center justify-end gap-3">
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('attendance.Date') }}:</span>
                <input type="date" 
                       id="attendance-date" 
                       value="{{ $selectedDate }}" 
                       max="{{ $today }}"
                       class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-200 rounded-lg px-4 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                <button type="button" 
                        id="today-btn"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-200 rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <i class="fas fa-calendar-day"></i>
                    {{ __('attendance.Today') }}
                </button>
            </div>

            <!-- Today's Stats Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-3 shadow-sm">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-green-500 to-emerald-600 text-white flex items-center justify-center shadow-lg">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('attendance.Present') }}</p>
                        <p class="text-2xl font-bold text-green-600">{{ number_format($stats['present']) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-3 shadow-sm">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-red-500 to-rose-600 text-white flex items-center justify-center shadow-lg">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('attendance.Absent') }}</p>
                        <p class="text-2xl font-bold text-red-600">{{ number_format($stats['absent']) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-3 shadow-sm">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-amber-500 to-orange-600 text-white flex items-center justify-center shadow-lg">
                        <i class="fas fa-calendar-minus"></i>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('attendance.Leave') }}</p>
                        <p class="text-2xl font-bold text-amber-600">{{ number_format($stats['leave']) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-3 shadow-sm">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center shadow-lg">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('attendance.Total Students') }}</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total']) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('attendance.All Classes') }}</p>
                    </div>
                </div>
            </div>

            <!-- Classes List by Grade -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('attendance.Select Class') }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('attendance.Click on a class to collect attendance') }}</p>
                </div>

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
                                    <span class="text-sm text-gray-500 dark:text-gray-400">({{ $gradeClasses->count() }} {{ __('attendance.classes') }})</span>
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-3">
                                    @foreach($gradeClasses->sortBy('name') as $class)
                                        @php
                                            $classStats = $classAttendanceCounts[$class->id] ?? null;
                                            $hasAttendance = $classStats !== null;
                                        @endphp
                                        <a href="{{ route('student-attendance.collect-class', $class) }}?date={{ $selectedDate }}" 
                                           class="group bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-blue-400 dark:hover:border-blue-500 hover:shadow-md transition-all text-center">
                                            <div class="w-12 h-12 mx-auto mb-2 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center text-blue-600 dark:text-blue-400">
                                                <i class="fas fa-users"></i>
                                            </div>
                                            <h5 class="font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                                @className($class->name, $class->grade?->level)
                                            </h5>
                                            @if($hasAttendance)
                                                <p class="text-xs mt-1">
                                                    <span class="text-green-600">{{ $classStats['present'] }}</span>
                                                    <span class="text-gray-400">/</span>
                                                    <span class="text-red-600">{{ $classStats['absent'] }}</span>
                                                    <span class="text-gray-400">/</span>
                                                    <span class="text-amber-600">{{ $classStats['leave'] }}</span>
                                                    <span class="text-gray-500 dark:text-gray-400 ml-1">{{ __('attendance.students') }}</span>
                                                </p>
                                            @else
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                    {{ $class->students->count() }} {{ __('attendance.students') }}
                                                </p>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-12 text-center">
                        <i class="fas fa-school text-5xl text-gray-300 dark:text-gray-600 mb-4"></i>
                        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('attendance.No classes found') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('attendance.Please create classes in Academic Management first.') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.getElementById('attendance-date');
            const todayBtn = document.getElementById('today-btn');
            const today = '{{ $today }}';

            // Handle date change
            dateInput.addEventListener('change', function() {
                const selectedDate = this.value;
                if (selectedDate) {
                    window.location.href = '{{ route("student-attendance.create") }}?date=' + selectedDate;
                }
            });

            // Handle Today button click
            todayBtn.addEventListener('click', function() {
                window.location.href = '{{ route("student-attendance.create") }}?date=' + today;
            });
        });
    </script>
    @endpush
</x-app-layout>
