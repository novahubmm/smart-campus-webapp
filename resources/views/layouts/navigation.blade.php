<aside class="fixed left-0 top-0 h-screen bg-white dark:bg-gray-950 border-r border-gray-200 dark:border-gray-800 shadow-lg transition-all duration-300 z-50" x-show="!sidebarCollapsed" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="width: 256px;">
    @php
        $setting = optional(\App\Models\Setting::first());
        $schoolLogo = $setting->school_short_logo_path ?: $setting->school_logo_path;
        $logoUrl = $schoolLogo ? asset('storage/'.$schoolLogo) : asset('logo_short.png');
        $schoolName = $setting->school_name ?? 'Smart Campus';
        
        // Generate short name from school name (take first letter of each word, max 4 letters)
        $shortName = '';
        if ($setting->school_code) {
            $shortName = $setting->school_code;
        } else {
            $words = preg_split('/\s+/', $schoolName);
            $shortName = strtoupper(implode('', array_map(fn($word) => mb_substr($word, 0, 1), array_slice($words, 0, 4))));
        }
    @endphp
    <div class="flex items-center justify-between px-4 h-[80px] border-b border-gray-200 dark:border-gray-800">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <img src="{{ $logoUrl }}" alt="Smart Campus" class="h-[50px] w-auto">
            <span class="text-xl font-bold text-gray-800 dark:text-white tracking-tight">{{ $shortName }}</span>
        </a>

        <!-- Collapse Button -->
        <button @click="sidebarCollapsed = !sidebarCollapsed"
                class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700 transition-all duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
        </button>
    </div>

    <nav class="h-[calc(100vh-80px)] overflow-y-auto px-3 py-4 space-y-5 sidebar-scroll">
            @php
                $setting = optional(\App\Models\Setting::first());
                $needsSetup = !($setting->setup_completed_school_info && $setting->setup_completed_academic && $setting->setup_completed_event_and_announcements && $setting->setup_completed_time_table_and_attendance && $setting->setup_completed_finance);
                $featureService = app(\App\Services\FeatureService::class);
                $isSystemAdmin = auth()->user() && auth()->user()->hasRole('system_admin');
            @endphp
            @if($needsSetup)
                <div class="space-y-2">
                    <x-nav-link :href="route('setup.overview')" label="{{ __('navigation.Setup Wizard') }}" icon="fas fa-wand-magic-sparkles" :active="(bool) request()->routeIs('setup.overview')" />
                    <div class="flex items-center gap-2 text-[11px] text-amber-600 dark:text-amber-300 px-3">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span>{{ __('navigation.Pending steps') }}</span>
                    </div>
                </div>
            @endif
            <div class="space-y-2">
                <x-nav-link :href="route('dashboard')" label="{{ __('navigation.Dashboard') }}" icon="fas fa-home" :active="(bool) request()->routeIs('dashboard')" />
            </div>
            @can('view academic management')
            <div class="space-y-2">
                <p class="text-[10px] font-semibold tracking-[0.18em] text-gray-500 dark:text-gray-400 uppercase px-3">{{ __('navigation.Academic Management') }}</p>
                @can('manage academic setup')
                    @php
                        $academicSetupCompleted = optional(\App\Models\Setting::first())->setup_completed_academic ?? false;
                    @endphp
                    @if(!$academicSetupCompleted)
                        <x-nav-link :href="route('academic-setup.index')" label="{{ __('navigation.Setup') }}" icon="fas fa-magic" :active="(bool) request()->routeIs('academic-setup.*')" />
                    @endif
                @endcan
                @can('manage academic management')
                    <x-nav-link :href="route('academic-management.index')" label="{{ __('navigation.Academic Management') }}" icon="fas fa-graduation-cap" :active="(bool) request()->routeIs('academic-management.*')" />
                @endcan
                @can('manage exam database')
                    <x-nav-link :href="route('exams.index')" label="{{ __('navigation.Exam Database') }}" icon="fas fa-clipboard-list" :active="(bool) request()->routeIs('exams.*')" />
                @endcan
                @can('manage academic management')
                    <x-nav-link :href="route('ongoing-class.index')" label="{{ __('navigation.Ongoing Class') }}" icon="fas fa-chalkboard" :active="(bool) request()->routeIs('ongoing-class.*')" />
                @endcan
                <!-- @can('manage academic management')
                    <x-nav-link :href="route('homework.index')" label="{{ __('navigation.Homework') }}" icon="fas fa-tasks" :active="(bool) request()->routeIs('homework.*')" />
                @endcan -->
            </div>
            @endcan

            @can('view events and announcements')
            <div class="space-y-2">
                <p class="text-[10px] font-semibold tracking-[0.18em] text-gray-500 dark:text-gray-400 uppercase px-3">{{ __('navigation.Events & Announcements') }}</p>
                @can('manage events and announcement setup')
                    @php
                        $eventsSetupCompleted = optional(\App\Models\Setting::first())->setup_completed_event_and_announcements ?? false;
                    @endphp
                    @if(!$eventsSetupCompleted)
                        <x-nav-link :href="route('event-announcement-setup.index')" label="{{ __('navigation.Setup') }}" icon="fas fa-cog" :active="(bool) request()->routeIs('event-announcement-setup.*')" />
                    @endif
                @endcan
                @can('manage event planner')
                    <x-nav-link :href="route('events.index')" label="{{ __('navigation.Event Planner') }}" icon="fas fa-calendar" :active="(bool) request()->routeIs('events.*')" />
                @endcan
                @can('manage announcements')
                    <x-nav-link :href="route('announcements.index')" label="{{ __('navigation.Announcements') }}" icon="fas fa-bell" :active="(bool) request()->routeIs('announcements.*')" />
                @endcan
            </div>
            @endcan
            @can('view time-table and attendance')
            <div class="space-y-2">
                <p class="text-[10px] font-semibold tracking-[0.18em] text-gray-500 dark:text-gray-400 uppercase px-3">{{ __('navigation.Time-table & Attendance') }}</p>
                @can('manage time-table and attendance setup')
                    @php
                        $attendanceSetupCompleted = optional(\App\Models\Setting::first())->setup_completed_time_table_and_attendance ?? false;
                    @endphp
                    @if(!$attendanceSetupCompleted)
                        <x-nav-link :href="route('time-table-attendance-setup.index')" label="{{ __('navigation.Setup') }}" icon="fas fa-cog" :active="(bool) request()->routeIs('time-table-attendance-setup.index')" />
                    @endif
                @endcan

                @can('manage time-table planner')
                    <x-nav-link :href="route('time-table.create')" label="{{ __('navigation.Time-table Planner') }}" icon="fas fa-calendar-plus" :active="(bool) request()->routeIs('time-table.create')" />
                @endcan
                @can('manage time-table planner')
                    <x-nav-link :href="route('time-table.index')" label="{{ __('navigation.Time-table List') }}" icon="fas fa-list" :active="(bool) (request()->routeIs('time-table.index', 'time-table.edit') || request()->is('time-table/class/*/versions'))" />
                @endcan
                @can('collect student attendance')
                    <x-nav-link :href="route('student-attendance.create')" label="{{ __('navigation.Collect Attendance') }}" icon="fas fa-clipboard-check" :active="(bool) (request()->routeIs('student-attendance.create') || request()->is('attendance/collect-attendance/*'))" />
                @endcan
                @can('manage student attendance')
                    <x-nav-link :href="route('student-attendance.index')" label="{{ __('navigation.Student Attendance') }}" icon="fas fa-user-graduate" :active="(bool) (request()->routeIs('student-attendance.index') || request()->is('attendance/students/class/*'))" />
                @endcan
                @can('manage teacher attendance')
                    <x-nav-link :href="route('teacher-attendance.index')" label="{{ __('navigation.Teacher Attendance') }}" icon="fas fa-chalkboard-teacher" :active="(bool) request()->routeIs('teacher-attendance.*')" />
                @endcan
                @can('manage staff attendance')
                    <x-nav-link :href="route('staff-attendance.index')" label="{{ __('navigation.Staff Attendance') }}" icon="fas fa-users-cog" :active="(bool) request()->routeIs('staff-attendance.*')" />
                @endcan
                @can('manage leave requests')
                    <x-nav-link :href="route('leave-requests.index')" label="{{ __('navigation.Leave Requests') }}" icon="fas fa-calendar-times" :active="(bool) request()->routeIs('leave-requests.index')" />
                    <x-nav-link :href="route('leave-requests.apply-for-other')" label="{{ __('navigation.Leave Requests For Other') }}" icon="fas fa-calendar-alt" :active="(bool) request()->routeIs('leave-requests.apply-for-other')" />
                @endcan
                @can('apply leave requests')
                    <x-nav-link :href="route('leave-requests.apply')" label="{{ __('navigation.Apply Leave Requests') }}" icon="fas fa-calendar-alt" :active="(bool) request()->routeIs('leave-requests.apply')" />
                @endcan
            </div>
            @endcan
            @can('view departments and profiles')
            <div class="space-y-2">
                <p class="text-[10px] font-semibold tracking-[0.18em] text-gray-500 dark:text-gray-400 uppercase px-3">{{ __('navigation.Departments & Profiles') }}</p>
                @can('manage departments')
                    <x-safe-nav-link route-name="departments.index" route-pattern="departments.*" label="{{ __('navigation.Departments') }}" icon="fas fa-building" />
                @endcan
                @can('manage teacher profiles')
                    <x-safe-nav-link route-name="teacher-profiles.index" route-pattern="teacher-profiles.*" label="{{ __('navigation.Teacher Profiles') }}" icon="fas fa-chalkboard-teacher" />
                @endcan
                @can('manage student profiles')
                    <x-safe-nav-link route-name="student-profiles.index" route-pattern="student-profiles.*" label="{{ __('navigation.Student Profiles') }}" icon="fas fa-user-graduate" />
                @endcan
                @can('manage staff profiles')
                    <x-safe-nav-link route-name="staff-profiles.index" route-pattern="staff-profiles.*" label="{{ __('navigation.Staff Profiles') }}" icon="fas fa-users-cog" />
                @endcan
            </div>
            @endcan

            @can('view finance management')
            <div class="space-y-2">
                <p class="text-[10px] font-semibold tracking-[0.18em] text-gray-500 dark:text-gray-400 uppercase px-3">{{ __('navigation.Finance') }}</p>
                @can('manage finance setup')
                    @php $financeSetupCompleted = optional(\App\Models\Setting::first())->setup_completed_finance ?? false; @endphp
                    @if(!$financeSetupCompleted)
                        <x-nav-link :href="route('finance-setup.index')" label="{{ __('navigation.Setup') }}" icon="fas fa-cog" :active="(bool) request()->routeIs('finance-setup.*')" />
                    @endif
                @endcan
                @can('manage student fees')
                    <x-nav-link :href="route('student-fees.index')" label="{{ __('navigation.Student Fee') }}" icon="fas fa-file-invoice-dollar" :active="(bool) request()->routeIs('student-fees.*')" />
                @endcan
                @can('manage salary and payroll')
                    <x-nav-link :href="route('salary-payroll.index')" label="{{ __('navigation.Salary and Payroll') }}" icon="fas fa-money-check-alt" :active="(bool) request()->routeIs('salary-payroll.*')" />
                @endcan
                @can('manage finance transactions')
                    <x-nav-link :href="route('finance.index')" label="{{ __('navigation.Finance') }}" icon="fas fa-dollar-sign" :active="(bool) request()->routeIs('finance.*')" />
                @endcan
            </div>
            @endcan

            @can('view system settings')
            <div class="space-y-2">
                <p class="text-[10px] font-semibold tracking-[0.18em] text-gray-500 dark:text-gray-400 uppercase px-3">{{ __('navigation.Settings') }}</p>
                @can('manage school settings')
                    <x-nav-link :href="route('settings.school-info')" label="{{ __('navigation.School Info') }}" icon="fas fa-school" :active="(bool) request()->routeIs('settings.school-info')" />
                @endcan
                @can('manage users')
                    <x-nav-link :href="route('users.index')" label="{{ __('navigation.User Management') }}" icon="fas fa-users" :active="(bool) request()->routeIs('users.*')" />
                @endcan
                @can('manage school settings')
                    <x-nav-link :href="route('rules.index')" label="{{ __('navigation.Rules') }}" icon="fas fa-calendar-alt" :active="(bool) (request()->routeIs('rules.*') || request()->is('rules/*'))" />
                @endcan
                @can('manage user activity logs')
                    <x-nav-link :href="route('user-activity-logs.index')" label="{{ __('navigation.User Activity Logs') }}" icon="fas fa-chart-line" :active="(bool) request()->routeIs('user-activity-logs.*')" />
                @endcan
            </div>
            @endcan
            @can('view reports')
            <div class="space-y-2">
                <p class="text-[10px] font-semibold tracking-[0.18em] text-gray-500 dark:text-gray-400 uppercase px-3">{{ __('navigation.Reports') }}</p>
                @can('generate reports')
                    <x-nav-link :href="route('reports.index')" label="{{ __('navigation.Report Centre') }}" icon="fas fa-file-alt" :active="(bool) (request()->routeIs('reports.*') || request()->is('reports') || request()->is('reports/incoming/*'))" />
                @endcan
            </div>
            @endcan
            @can('view communication and support')
            <div class="space-y-2">
                <p class="text-[10px] font-semibold tracking-[0.18em] text-gray-500 dark:text-gray-400 uppercase px-3">{{ __('navigation.Communication & Support') }}</p>
                @can('manage contacts')
                    <x-nav-link :href="route('contacts.index')" label="{{ __('navigation.Contacts') }}" icon="fas fa-address-book" :active="(bool) request()->routeIs('contacts.*')" />
                @endcan
                @role('staff')
                    @php
                        $unreadNotificationCount = auth()->user() ? 
                            \App\Models\Notification::where('notifiable_type', get_class(auth()->user()))
                                ->where('notifiable_id', auth()->user()->id)
                                ->whereNull('read_at')
                                ->count() : 0;
                        $isNotificationActive = request()->routeIs('staff.notifications.*');
                        $notificationStateClasses = $isNotificationActive
                            ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-200 ring-1 ring-inset ring-blue-500/30'
                            : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800';
                    @endphp
                    <a href="{{ route('staff.notifications.index') }}" class="relative group flex items-center px-3 py-2 gap-3 text-sm font-semibold rounded-xl transition-all duration-150 {{ $notificationStateClasses }}">
                        <span class="flex items-center justify-center w-9 h-9 flex-shrink-0 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-300 group-hover:bg-white/70 dark:group-hover:bg-gray-700 transition-colors relative">
                            <i class="fas fa-bell text-base"></i>
                            <span id="notification-badge" class="absolute -top-1 -right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-500 rounded-full min-w-[18px] h-[18px] {{ $unreadNotificationCount > 0 ? '' : 'hidden' }}">
                                <span id="notification-count">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>
                            </span>
                        </span>
                        <span class="truncate">{{ __('navigation.My Notifications') }}</span>
                    </a>
                @endrole
                <x-nav-link :href="route('feedback.create')" label="{{ __('navigation.Submit Feedback') }}" icon="fas fa-comment-dots" :active="(bool) request()->routeIs('feedback.*')" />
            </div>
            @endcan

            @role('system_admin')
            <div class="space-y-2">
                <p class="text-[10px] font-semibold tracking-[0.18em] text-gray-500 dark:text-gray-400 uppercase px-3">{{ __('navigation.System Administration') }}</p>
                <x-nav-link :href="route('system-admin.features.index')" label="{{ __('navigation.Feature Flags') }}" icon="fas fa-toggle-on" :active="(bool) request()->routeIs('system-admin.features.*')" />
                <x-nav-link :href="route('system-admin.feedback.index')" label="{{ __('navigation.Feedback Management') }}" icon="fas fa-comments" :active="(bool) request()->routeIs('system-admin.feedback.*')" />
                @can('manage roles')
                    <x-nav-link :href="route('roles.index')" label="{{ __('navigation.Roles') }}" icon="fas fa-user-shield" :active="(bool) request()->routeIs('roles.*')" />
                @endcan
                @can('manage permissions')
                    <x-nav-link :href="route('permissions.index')" label="{{ __('navigation.Permissions') }}" icon="fas fa-lock" :active="(bool) request()->routeIs('permissions.*')" />
                @endcan
            </div>
            @endrole
    </nav>
</aside>

@role('staff')
<script>
// Define updateNotificationCount immediately (not inside DOMContentLoaded)
// so it's available when FCM fires
window.updateNotificationCount = function() {
    const badge = document.getElementById('notification-badge');
    const countElement = document.getElementById('notification-count');
    
    fetch('{{ route("staff.notifications.unread-count") }}', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        const count = data.count || 0;
        
        if (badge && countElement) {
            if (count > 0) {
                badge.classList.remove('hidden');
                countElement.textContent = count > 99 ? '99+' : count;
            } else {
                badge.classList.add('hidden');
            }
            
            // Add visual pulse effect to the bell
            const bellIcon = badge.parentElement.querySelector('i');
            if (bellIcon) {
                bellIcon.classList.add('animate-pulse', 'text-red-500');
                setTimeout(() => {
                    bellIcon.classList.remove('animate-pulse', 'text-red-500');
                }, 3000);
            }
        }
        
        // Dispatch event for other components to listen
        window.dispatchEvent(new CustomEvent('notification-count-updated', { detail: { count } }));
    })
    .catch(error => {
        // Silent fail
    });
};

// Update count once on page load
document.addEventListener('DOMContentLoaded', function() {
    window.updateNotificationCount();
});
</script>
@endrole
