<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <i class="fas fa-th-large text-blue-500"></i>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('dashboard.Dashboard') }}</h1>
        </div>
    </x-slot>

    <div class="py-4 px-4 sm:px-6 lg:px-8 space-y-4">

        {{-- ===== App Icon Sections (Dock-driven) ===== --}}

        {{-- HOME section: shows original dashboard --}}
        <div x-show="activeSection === 'home'" x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">



            <!-- School Info Header Section -->
            @if($setting)
                <div
                    class="flex flex-col md:flex-row rounded-2xl overflow-hidden shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="flex-[0.8] p-8 bg-emerald-600 dark:bg-emerald-700 school-info">
                        <h2 class="text-3xl font-extrabold text-white tracking-wide uppercase mb-6">
                            {{ $setting->school_name ?? 'SMART CAMPUS' }}
                        </h2>
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
                            $logoUrl = $schoolLogo ? asset('storage/' . $schoolLogo) : asset('school-banner-logo.svg');
                        @endphp
                        <img src="{{ $logoUrl }}" class="school-banner-logo drop-shadow-lg" alt="School Logo">
                    </div>
                </div>
            @endif

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('student-profiles.index') }}"
                    class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow flex items-start gap-4 cursor-pointer">
                    <div
                        class="w-14 h-14 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300 flex items-center justify-center text-xl">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('dashboard.Total Students') }}
                        </h4>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($counts['students'] ?? 0) }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('dashboard.Today') }}:
                            {{ $todayAttendance['students'] ?? '0%' }}
                        </div>
                    </div>
                </a>
                <a href="{{ route('staff-profiles.index') }}"
                    class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow flex items-start gap-4 cursor-pointer">
                    <div
                        class="w-14 h-14 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300 flex items-center justify-center text-xl">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('dashboard.Total Staff') }}
                        </h4>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($counts['staff'] ?? 0) }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('dashboard.Today') }}:
                            {{ $todayAttendance['staff'] ?? '0%' }}
                        </div>
                    </div>
                </a>
                <a href="{{ route('teacher-profiles.index') }}"
                    class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow flex items-start gap-4 cursor-pointer">
                    <div
                        class="w-14 h-14 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300 flex items-center justify-center text-xl">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('dashboard.Total Teachers') }}
                        </h4>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($counts['teachers'] ?? 0) }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('dashboard.Today') }}:
                            {{ $todayAttendance['teachers'] ?? '0%' }}
                        </div>
                    </div>
                </a>
                <a href="{{ route('student-fees.index') }}"
                    class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow flex items-start gap-4 cursor-pointer">
                    <div
                        class="w-14 h-14 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300 flex items-center justify-center text-xl">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ __('dashboard.Fee Collection') }}
                        </h4>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $feeCollectionPercent ?? 0 }}%
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('dashboard.This Month') }}
                        </div>
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
                <div
                    class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-6 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ __('dashboard.Setup Status') }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('dashboard.Finish these steps to unlock all dashboards.') }}
                            </p>
                        </div>
                        <a href="{{ route('setup.overview') }}"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow transition-colors">
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
                            <div
                                class="border border-gray-200 dark:border-gray-700 rounded-xl p-4 bg-gray-50 dark:bg-gray-900/40">
                                <div class="flex items-center justify-between gap-2 mb-3">
                                    <span
                                        class="w-10 h-10 rounded-full flex items-center justify-center {{ $done ? 'bg-green-100 text-green-600 dark:bg-green-900/40 dark:text-green-300' : 'bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-300' }}">
                                        <i class="{{ $step['icon'] }}"></i>
                                    </span>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full {{ $done ? 'bg-green-100 text-green-700 dark:bg-green-900/60 dark:text-green-200' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/60 dark:text-amber-200' }}">
                                        {{ $done ? __('dashboard.Done') : __('dashboard.Pending') }}
                                    </span>
                                </div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-white mb-2">{{ $step['label'] }}</div>
                                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                    <span>{{ $done ? __('dashboard.Completed') : __('dashboard.Action needed') }}</span>
                                    @if(!$done)
                                        <a href="{{ $step['route'] }}"
                                            class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('dashboard.Review') }}</a>
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
                <div
                    class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm">
                    <div class="flex items-center justify-between p-5 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-bell text-blue-600 dark:text-blue-400"></i>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                                {{ __('dashboard.Upcoming Events') }}
                            </h3>
                        </div>
                        <a href="{{ route('events.index') }}"
                            class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition-colors">{{ __('dashboard.View All') }}</a>
                    </div>
                    <div class="p-5 space-y-3">
                        @forelse($upcomingEvents as $event)
                            <div class="flex items-start gap-3">
                                <div
                                    class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-calendar"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                        {{ $event->title }}
                                    </h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($event->start_date)->format('M d, Y') }} •
                                        {{ ucfirst($event->type ?? 'Event') }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                                {{ __('dashboard.No upcoming events') }}
                            </p>
                        @endforelse
                    </div>
                </div>

                <!-- Upcoming Exams -->
                <div
                    class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm">
                    <div class="flex items-center justify-between p-5 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-clipboard-list text-blue-600 dark:text-red-400"></i>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                                {{ __('dashboard.Upcoming Exams') }}
                            </h3>
                        </div>
                        <a href="{{ route('exams.index') }}"
                            class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition-colors">{{ __('dashboard.View All') }}</a>
                    </div>
                    <div class="p-5 space-y-3">
                        @forelse($upcomingExams as $exam)
                            <div class="flex items-start gap-3">
                                <div
                                    class="w-10 h-10 rounded-full bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-300 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                        {{ $exam->name }}
                                    </h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $exam->start_date?->format('M d, Y') }} •
                                        {{ $exam->grade?->name ?? __('dashboard.All Grades') }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                                {{ __('dashboard.No upcoming exams') }}
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div> {{-- END HOME section --}}

        {{-- ===== ACADEMIC section ===== --}}
        @can('view academic management')
            <div x-show="activeSection === 'academic'" x-cloak x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                <h2 class="app-section-header">{{ __('navigation.Academic Management') }}</h2>
                <div class="app-icon-grid">
                    @can('manage academic setup')
                        <a href="{{ route('academic-setup.index') }}" class="app-icon">
                            <div class="app-icon-badge" style="background: linear-gradient(135deg, #10b981, #059669)"><i
                                    class="fas fa-magic"></i></div>
                            <span class="app-icon-label">Setup</span>
                        </a>
                    @endcan
                    @can('manage academic management')
                        <a href="{{ route('academic-management.index') }}" class="app-icon">
                            <div class="app-icon-badge" style="background: linear-gradient(135deg, #10b981, #047857)"><i
                                    class="fas fa-graduation-cap"></i></div>
                            <span class="app-icon-label">Management</span>
                        </a>
                    @endcan
                    @can('manage exam database')
                        <a href="{{ route('exams.index') }}" class="app-icon">
                            <div class="app-icon-badge" style="background: linear-gradient(135deg, #ef4444, #dc2626)"><i
                                    class="fas fa-clipboard-list"></i></div>
                            <span class="app-icon-label">Exams</span>
                        </a>
                    @endcan
                    @can('manage academic management')
                        <a href="{{ route('ongoing-class.index') }}" class="app-icon">
                            <div class="app-icon-badge" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed)"><i
                                    class="fas fa-chalkboard"></i></div>
                            <span class="app-icon-label">Ongoing Class</span>
                        </a>
                        <a href="{{ route('homework.index') }}" class="app-icon">
                            <div class="app-icon-badge" style="background: linear-gradient(135deg, #f59e0b, #d97706)"><i
                                    class="fas fa-tasks"></i></div>
                            <span class="app-icon-label">Homework</span>
                        </a>
                    @endcan
                </div>
            </div>
        @endcan

        {{-- ===== SCHEDULE section ===== --}}
        @can('view time-table and attendance')
            <div x-show="activeSection === 'schedule'" x-cloak x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                <h2 class="app-section-header">Timetable & Attendance</h2>
                <div class="app-icon-grid">
                    @can('manage time-table planner')
                        <a href="{{ route('time-table.create') }}" class="app-icon">
                            <div class="app-icon-badge" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed)"><i
                                    class="fas fa-calendar-plus"></i></div>
                            <span class="app-icon-label">Planner</span>
                        </a>
                        <a href="{{ route('time-table.index') }}" class="app-icon">
                            <div class="app-icon-badge" style="background: linear-gradient(135deg, #8b5cf6, #6d28d9)"><i
                                    class="fas fa-list"></i></div>
                            <span class="app-icon-label">Timetables</span>
                        </a>
                    @endcan
                    @can('collect student attendance')
                        <a href="{{ route('student-attendance.create') }}" class="app-icon">
                            <div class="app-icon-badge" style="background: linear-gradient(135deg, #14b8a6, #0d9488)"><i
                                    class="fas fa-clipboard-check"></i></div>
                            <span class="app-icon-label">Collect Attd</span>
                        </a>
                    @endcan
                    @can('manage student attendance')
                        <a href="{{ route('student-attendance.index') }}" class="app-icon">
                            <div class="app-icon-badge" style="background: linear-gradient(135deg, #3b82f6, #2563eb)"><i
                                    class="fas fa-user-graduate"></i></div>
                            <span class="app-icon-label">Student Attd</span>
                        </a>
                    @endcan
                    @can('manage teacher attendance')
                        <a href="{{ route('teacher-attendance.index') }}" class="app-icon">
                            <div class="app-icon-badge" style="background: linear-gradient(135deg, #10b981, #059669)"><i
                                    class="fas fa-chalkboard-teacher"></i></div>
                            <span class="app-icon-label">Teacher Attd</span>
                        </a>
                    @endcan
                    @can('manage staff attendance')
                        <a href="{{ route('staff-attendance.index') }}" class="app-icon">
                            <div class="app-icon-badge" style="background: linear-gradient(135deg, #6366f1, #4f46e5)"><i
                                    class="fas fa-users-cog"></i></div>
                            <span class="app-icon-label">Staff Attd</span>
                        </a>
                    @endcan
                    @can('manage leave requests')
                        <a href="{{ route('leave-requests.index') }}" class="app-icon">
                            <div class="app-icon-badge" style="background: linear-gradient(135deg, #f59e0b, #d97706)"><i
                                    class="fas fa-calendar-times"></i></div>
                            <span class="app-icon-label">Leave Req</span>
                        </a>
                    @endcan
                    @can('apply leave requests')
                        <a href="{{ route('leave-requests.apply') }}" class="app-icon">
                            <div class="app-icon-badge" style="background: linear-gradient(135deg, #ec4899, #db2777)"><i
                                    class="fas fa-calendar-alt"></i></div>
                            <span class="app-icon-label">Apply Leave</span>
                        </a>
                    @endcan
                </div>
            </div>
        @endcan

        {{-- ===== PEOPLE section ===== --}}
        @can('view departments and profiles')
            <div x-show="activeSection === 'people'" x-cloak x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                <h2 class="app-section-header">Departments & Profiles</h2>
                <div class="app-icon-grid">
                    @can('manage departments')
                        <a href="{{ route('departments.index') }}" class="app-icon">
                            <div class="app-icon-badge" style="background: linear-gradient(135deg, #6366f1, #4f46e5)"><i
                                    class="fas fa-building"></i></div>
                            <span class="app-icon-label">Departments</span>
                        </a>
                    @endcan
                    @can('manage teacher profiles')
                        <a href="{{ route('teacher-profiles.index') }}" class="app-icon">
                            <div class="app-icon-badge" style="background: linear-gradient(135deg, #10b981, #059669)"><i
                                    class="fas fa-chalkboard-teacher"></i></div>
                            <span class="app-icon-label">Teachers</span>
                        </a>
                    @endcan
                    @can('manage student profiles')
                        <a href="{{ route('student-profiles.index') }}" class="app-icon">
                            <div class="app-icon-badge" style="background: linear-gradient(135deg, #3b82f6, #2563eb)"><i
                                    class="fas fa-user-graduate"></i></div>
                            <span class="app-icon-label">Students</span>
                        </a>
                    @endcan
                    @can('manage staff profiles')
                        <a href="{{ route('staff-profiles.index') }}" class="app-icon">
                            <div class="app-icon-badge" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed)"><i
                                    class="fas fa-users-cog"></i></div>
                            <span class="app-icon-label">Staff</span>
                        </a>
                    @endcan
                </div>
            </div>
        @endcan

        {{-- ===== FINANCE section ===== --}}
        @can('view finance management')
            <div x-show="activeSection === 'finance'" x-cloak x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                <h2 class="app-section-header">Finance</h2>
                <div class="app-icon-grid">
                    @can('manage finance setup')
                        <a href="{{ route('finance-setup.index') }}" class="app-icon">
                            <div class="app-icon-badge" style="background: linear-gradient(135deg, #22c55e, #16a34a)"><i
                                    class="fas fa-cog"></i></div>
                            <span class="app-icon-label">Setup</span>
                        </a>
                    @endcan
                    @can('manage student fees')
                        <a href="{{ route('student-fees.index') }}" class="app-icon">
                            <div class="app-icon-badge" style="background: linear-gradient(135deg, #22c55e, #15803d)"><i
                                    class="fas fa-file-invoice-dollar"></i></div>
                            <span class="app-icon-label">Student Fees</span>
                        </a>
                    @endcan
                    @can('manage salary and payroll')
                        <a href="{{ route('salary-payroll.index') }}" class="app-icon">
                            <div class="app-icon-badge" style="background: linear-gradient(135deg, #3b82f6, #2563eb)"><i
                                    class="fas fa-money-check-alt"></i></div>
                            <span class="app-icon-label">Payroll</span>
                        </a>
                    @endcan
                    @can('manage finance transactions')
                        <a href="{{ route('finance.index') }}" class="app-icon">
                            <div class="app-icon-badge" style="background: linear-gradient(135deg, #10b981, #059669)"><i
                                    class="fas fa-dollar-sign"></i></div>
                            <span class="app-icon-label">Transactions</span>
                        </a>
                    @endcan
                </div>
            </div>
        @endcan

        {{-- ===== NEWS section ===== --}}
        @can('view events and announcements')
            <div x-show="activeSection === 'news'" x-cloak x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                <h2 class="app-section-header">Events & Announcements</h2>
                <div class="app-icon-grid">
                    @can('manage events and announcement setup')
                        <a href="{{ route('event-announcement-setup.index') }}" class="app-icon">
                            <div class="app-icon-badge" style="background: linear-gradient(135deg, #ec4899, #db2777)"><i
                                    class="fas fa-cog"></i></div>
                            <span class="app-icon-label">Setup</span>
                        </a>
                    @endcan
                    @can('manage event planner')
                        <a href="{{ route('events.index') }}" class="app-icon">
                            <div class="app-icon-badge" style="background: linear-gradient(135deg, #ec4899, #be185d)"><i
                                    class="fas fa-calendar"></i></div>
                            <span class="app-icon-label">Events</span>
                        </a>
                    @endcan
                    @can('manage announcements')
                        <a href="{{ route('announcements.index') }}" class="app-icon">
                            <div class="app-icon-badge" style="background: linear-gradient(135deg, #f97316, #ea580c)"><i
                                    class="fas fa-bell"></i></div>
                            <span class="app-icon-label">Announcements</span>
                        </a>
                    @endcan
                </div>
            </div>
        @endcan

        {{-- ===== MORE section ===== --}}
        <div x-show="activeSection === 'more'" x-cloak x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">

            @can('view system settings')
                <h2 class="app-section-header">Settings</h2>
                <div class="app-icon-grid">
                    @can('manage school settings')
                        <a href="{{ route('settings.school-info') }}" class="app-icon">
                            <div class="app-icon-badge" style="background: linear-gradient(135deg, #64748b, #475569)"><i
                                    class="fas fa-school"></i></div>
                            <span class="app-icon-label">School Info</span>
                        </a>
                    @endcan
                    @can('manage users')
                        <a href="{{ route('users.index') }}" class="app-icon">
                            <div class="app-icon-badge" style="background: linear-gradient(135deg, #64748b, #334155)"><i
                                    class="fas fa-users"></i></div>
                            <span class="app-icon-label">Users</span>
                        </a>
                    @endcan
                    @can('manage school settings')
                        <a href="{{ route('rules.index') }}" class="app-icon">
                            <div class="app-icon-badge" style="background: linear-gradient(135deg, #64748b, #475569)"><i
                                    class="fas fa-gavel"></i></div>
                            <span class="app-icon-label">Rules</span>
                        </a>
                    @endcan
                    @can('manage user activity logs')
                        <a href="{{ route('user-activity-logs.index') }}" class="app-icon">
                            <div class="app-icon-badge" style="background: linear-gradient(135deg, #64748b, #334155)"><i
                                    class="fas fa-chart-line"></i></div>
                            <span class="app-icon-label">Activity Logs</span>
                        </a>
                    @endcan
                </div>
            @endcan

            @can('view reports')
                <h2 class="app-section-header mt-4">Reports</h2>
                <div class="app-icon-grid">
                    <a href="{{ route('reports.index') }}" class="app-icon">
                        <div class="app-icon-badge" style="background: linear-gradient(135deg, #06b6d4, #0891b2)"><i
                                class="fas fa-file-alt"></i></div>
                        <span class="app-icon-label">Report Centre</span>
                    </a>
                </div>
            @endcan

            @can('view communication and support')
                <h2 class="app-section-header mt-4">Communication</h2>
                <div class="app-icon-grid">
                    @can('manage contacts')
                        <a href="{{ route('contacts.index') }}" class="app-icon">
                            <div class="app-icon-badge" style="background: linear-gradient(135deg, #f97316, #ea580c)"><i
                                    class="fas fa-address-book"></i></div>
                            <span class="app-icon-label">Contacts</span>
                        </a>
                    @endcan
                    <a href="{{ route('feedback.index') }}" class="app-icon">
                        <div class="app-icon-badge" style="background: linear-gradient(135deg, #f97316, #c2410c)"><i
                                class="fas fa-comment-dots"></i></div>
                        <span class="app-icon-label">Feedback</span>
                    </a>
                </div>
            @endcan

            @can('view system management')
                <h2 class="app-section-header mt-4">System</h2>
                <div class="app-icon-grid">
                    @can('manage roles')
                        <a href="{{ route('roles.index') }}" class="app-icon">
                            <div class="app-icon-badge" style="background: linear-gradient(135deg, #6b7280, #4b5563)"><i
                                    class="fas fa-user-shield"></i></div>
                            <span class="app-icon-label">Roles</span>
                        </a>
                    @endcan
                    @can('manage permissions')
                        <a href="{{ route('permissions.index') }}" class="app-icon">
                            <div class="app-icon-badge" style="background: linear-gradient(135deg, #6b7280, #374151)"><i
                                    class="fas fa-lock"></i></div>
                            <span class="app-icon-label">Permissions</span>
                        </a>
                    @endcan
                </div>
            @endcan

            <div class="mt-4">
                <a href="{{ route('setup.overview') }}" class="app-icon">
                    <div class="app-icon-badge" style="background: linear-gradient(135deg, #f59e0b, #d97706)"><i
                            class="fas fa-wand-magic-sparkles"></i></div>
                    <span class="app-icon-label">Setup Wizard</span>
                </a>
            </div>
        </div>

    </div>
</x-app-layout>