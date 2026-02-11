<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-home"></i>
            </div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('dashboard.Dashboard') }}</h1>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- School Info Header Section -->
        @if($setting)
        <div class="flex flex-col md:flex-row rounded-2xl overflow-hidden shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex-[0.8] p-8 bg-emerald-600 dark:bg-emerald-700 school-info">
                <h2 class="text-3xl font-extrabold text-white tracking-wide uppercase mb-6">{{ $setting->school_name ?? 'SMART CAMPUS' }}</h2>
                <div class="space-y-3 text-white/90">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-map-marker-alt w-5 text-center"></i>
                        <span>{{ $setting->school_address ?? 'Location' }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <i class="fas fa-envelope w-5 text-center"></i>
                        <span>{{ $setting->school_email ?? 'email@school.com' }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <i class="fas fa-phone w-5 text-center"></i>
                        <span>{{ $setting->school_phone ?? '+959 000000000' }}</span>
                    </div>
                </div>
            </div>
            <div class="flex-1 p-6 bg-emerald-600 dark:bg-emerald-700 flex items-center justify-center school-logo">
                @php
                    $schoolLogo = $setting?->school_logo_path;
                    $logoUrl = $schoolLogo ? asset('storage/'.$schoolLogo) : asset('school-logo.jpg');
                @endphp
                <img src="{{ $logoUrl }}" class="school-banner-logo drop-shadow-lg" alt="School Logo">
            </div>
        </div>
        @endif

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('student-profiles.index') }}" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow flex items-start gap-4 cursor-pointer">
                <div class="w-14 h-14 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300 flex items-center justify-center text-xl">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('dashboard.Total Students') }}</h4>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($counts['students'] ?? 0) }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('dashboard.Today') }}: {{ $todayAttendance['students'] ?? '0%' }}</div>
                </div>
            </a>
            <a href="{{ route('staff-profiles.index') }}" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow flex items-start gap-4 cursor-pointer">
                <div class="w-14 h-14 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300 flex items-center justify-center text-xl">
                    <i class="fas fa-users-cog"></i>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('dashboard.Total Staff') }}</h4>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($counts['staff'] ?? 0) }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('dashboard.Today') }}: {{ $todayAttendance['staff'] ?? '0%' }}</div>
                </div>
            </a>
            <a href="{{ route('teacher-profiles.index') }}" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow flex items-start gap-4 cursor-pointer">
                <div class="w-14 h-14 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300 flex items-center justify-center text-xl">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('dashboard.Total Teachers') }}</h4>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($counts['teachers'] ?? 0) }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('dashboard.Today') }}: {{ $todayAttendance['teachers'] ?? '0%' }}</div>
                </div>
            </a>
            <a href="{{ route('student-fees.index') }}" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow flex items-start gap-4 cursor-pointer">
                <div class="w-14 h-14 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300 flex items-center justify-center text-xl">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('dashboard.Fee Collection') }}</h4>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $feeCollectionPercent ?? 0 }}%</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('dashboard.This Month') }}</div>
                </div>
            </a>
        </div>

        <!-- Setup Status Section (only show if not all setup completed) -->
        @php
            $setupSteps = [
                ['key' => 'school_info', 'label' => __('dashboard.School Info'), 'icon' => 'fas fa-school', 'route' => route('settings.school-info')],
                ['key' => 'academic', 'label' => __('dashboard.Academic'), 'icon' => 'fas fa-magic', 'route' => route('academic-setup.index')],
                ['key' => 'events', 'label' => __('dashboard.Events & Announcements'), 'icon' => 'fas fa-bullhorn', 'route' => route('event-announcement-setup.index')],
                ['key' => 'attendance', 'label' => __('dashboard.Time-table & Attendance'), 'icon' => 'fas fa-user-check', 'route' => route('time-table-attendance-setup.index')],
                ['key' => 'finance', 'label' => __('dashboard.Finance'), 'icon' => 'fas fa-dollar-sign', 'route' => route('finance-setup.index')],
            ];
        @endphp

        @if($all_setup_completed == false)
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('dashboard.Setup Status') }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('dashboard.Finish these steps to unlock all dashboards.') }}</p>
                </div>
                <a href="{{ route('setup.overview') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow transition-colors">
                    <i class="fas fa-play"></i>
                    <span>{{ __('dashboard.Open Setup Wizard') }}</span>
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4">
                @foreach($setupSteps as $step)
                    @php
                        $setupKey = 'setup_completed_' . $step['key'];
                        $done = $setting->$setupKey ?? false;
                    @endphp
                    <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4 bg-gray-50 dark:bg-gray-900/40">
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="w-10 h-10 rounded-full flex items-center justify-center {{ $done ? 'bg-green-100 text-green-600 dark:bg-green-900/40 dark:text-green-300' : 'bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-300' }}">
                                <i class="{{ $step['icon'] }}"></i>
                            </span>
                            <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full {{ $done ? 'bg-green-100 text-green-700 dark:bg-green-900/60 dark:text-green-200' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/60 dark:text-amber-200' }}">
                                {{ $done ? __('dashboard.Done') : __('dashboard.Pending') }}
                            </span>
                        </div>
                        <div class="text-sm font-semibold text-gray-900 dark:text-white mb-2">{{ $step['label'] }}</div>
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                            <span>{{ $done ? __('dashboard.Completed') : __('dashboard.Action needed') }}</span>
                            @if(!$done)
                                <a href="{{ $step['route'] }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('dashboard.Review') }}</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Upcoming Events & Exams -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Upcoming Events -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm">
                <div class="flex items-center justify-between p-5 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-bell text-blue-600 dark:text-blue-400"></i>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('dashboard.Upcoming Events') }}</h3>
                    </div>
                    <a href="{{ route('events.index') }}" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition-colors">{{ __('dashboard.View All') }}</a>
                </div>
                <div class="p-5 space-y-3">
                    @forelse($upcomingEvents as $event)
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $event->title }}</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($event->start_date)->format('M d, Y') }} • {{ ucfirst($event->type ?? 'Event') }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">{{ __('dashboard.No upcoming events') }}</p>
                    @endforelse
                </div>
            </div>

            <!-- Upcoming Exams -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm">
                <div class="flex items-center justify-between p-5 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-clipboard-list text-blue-600 dark:text-red-400"></i>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('dashboard.Upcoming Exams') }}</h3>
                    </div>
                    <a href="{{ route('exams.index') }}" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition-colors">{{ __('dashboard.View All') }}</a>
                </div>
                <div class="p-5 space-y-3">
                    @forelse($upcomingExams as $exam)
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-full bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-300 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $exam->name }}</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $exam->start_date?->format('M d, Y') }} • {{ $exam->grade?->name ?? __('dashboard.All Grades') }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">{{ __('dashboard.No upcoming exams') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
