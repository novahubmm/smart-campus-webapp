<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg">
                <i class="fas fa-users"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('attendance.Attendance') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">@className($class->name, $class->grade?->level)</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('attendance.Attendance History') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10 overflow-x-hidden" x-data="classDetailPage(@js([
        'classId' => $class->id,
        'date' => $date,
        'perPage' => 10,
        'timetablePeriods' => $timetablePeriods ?? [],
        'hasTimetable' => $hasTimetable ?? false,
        'routes' => [
            'detail' => route('student-attendance.class-detail', $class->id),
        ],
        'csrf' => csrf_token(),
    ]))" x-init="loadDetail()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-back-link 
                :href="route('student-attendance.index')"
                :text="__('attendance.Back to Attendance')"
            />
            <!-- Date Filter -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('attendance.Attendance Records') }}</h3>
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('attendance.Select Date:') }}</label>
                        <input type="date" x-model="selectedDate" @change="loadDetail" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" />
                        <button type="button" @click="setToday" class="px-4 py-2 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-sm font-semibold">
                            <i class="fas fa-calendar-day mr-1"></i>{{ __('attendance.Today') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Period Filter Tabs -->
            <div x-show="timetablePeriods && timetablePeriods.length > 0" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-4">
                <div class="flex flex-wrap gap-2">
                    <button type="button" 
                            @click="selectedPeriod = null"
                            :class="selectedPeriod === null 
                                ? 'bg-blue-600 text-white border-blue-600' 
                                : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600 hover:border-blue-400'"
                            class="px-4 py-3 rounded-lg border-2 transition-all">
                        <div class="font-semibold">{{ __('attendance.All Periods') }}</div>
                        <div class="text-xs opacity-75" x-text="timetablePeriods.length + ' {{ __('attendance.periods') }}'"></div>
                    </button>
                    <template x-for="tp in timetablePeriods" :key="tp.id">
                        <button type="button" 
                                @click="selectedPeriod = tp.id"
                                :class="selectedPeriod === tp.id 
                                    ? 'bg-blue-600 text-white border-blue-600' 
                                    : (hasPeriodData(tp.id) 
                                        ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 border-green-300 dark:border-green-700' 
                                        : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600 hover:border-blue-400')"
                                class="relative px-4 py-3 rounded-lg border-2 transition-all">
                            <div class="font-semibold" x-text="'P' + tp.period_number + ' - ' + (tp.subject_name || '—')"></div>
                            <div class="text-xs opacity-75" x-text="tp.starts_at + ' - ' + tp.ends_at"></div>
                            <template x-if="hasPeriodData(tp.id)">
                                <span class="absolute -top-1 -right-1 w-4 h-4 bg-green-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-check text-white text-[8px]"></i>
                                </span>
                            </template>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Attendance Records by Period -->
            <template x-for="(period, index) in filteredPeriods" :key="period.id">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700 space-y-3">
                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-3">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <i :class="getSubjectIcon(period.subject_name)" class="text-blue-600"></i>
                                    <span x-text="'P' + period.period_number + ' - ' + (period.subject_name || '—')"></span>
                                </h3>
                                <div class="flex flex-wrap items-center gap-2 mt-2 text-xs text-gray-600 dark:text-gray-400">
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-gray-50 dark:bg-gray-700">
                                        <i class="fas fa-clock"></i>
                                        <span x-text="period.starts_at + ' - ' + period.ends_at"></span>
                                    </span>
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-gray-50 dark:bg-gray-700">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                        <span x-text="period.teacher_name || '—'"></span>
                                    </span>
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-gray-50 dark:bg-gray-700">
                                        <i class="fas fa-door-open"></i>
                                        <span x-text="period.room_name || '—'"></span>
                                    </span>
                                    <span x-show="period.collect_time" class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-gray-50 dark:bg-gray-700">
                                        <i class="fas fa-stopwatch"></i>
                                        <span>{{ __('attendance.Collect Time:') }}</span>
                                        <span x-text="period.collect_time"></span>
                                    </span>
                                    <span x-show="period.collected_by_name" class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-gray-50 dark:bg-gray-700">
                                        <i class="fas fa-user"></i>
                                        <span>{{ __('attendance.Collected By:') }}</span>
                                        <span x-text="period.collected_by_name"></span>
                                    </span>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-semibold" :class="period.present_pct >= 95 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-100' : period.present_pct >= 85 ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-100' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-100'">
                                    {{ __('attendance.Present:') }} <span x-text="period.present_pct + '%'"></span>
                                </span>
                                <span class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-semibold bg-red-50 dark:bg-red-900/30 text-red-800 dark:text-red-100">
                                    {{ __('attendance.Absent:') }} <span x-text="period.absent_count"></span>
                                </span>
                                <span class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-semibold bg-blue-50 dark:bg-blue-900/30 text-blue-800 dark:text-blue-100">
                                    {{ __('attendance.Leave:') }} <span x-text="period.leave_count"></span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div x-show="period.students && period.students.length > 0" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Student ID') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Name') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Today Present %') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Status') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Notes') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <template x-for="student in getPaginatedStudents(period)" :key="student.id">
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white font-semibold" x-text="student.student_id"></td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white" x-text="student.name"></td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold" :class="student.today_pct >= 95 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-100' : student.today_pct >= 85 ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-100' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-100'" x-text="student.today_pct + '%'"></span>
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold" :class="getStatusClass(student.status)" x-text="student.status"></span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="student.remark || '—'"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        <!-- Pagination for this period -->
                        <div x-show="period.students.length > perPage" class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex flex-wrap items-center justify-between gap-3">
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('pagination.Showing') }} <span x-text="Math.min((getPeriodPage(period.id) - 1) * perPage + 1, period.students.length)"></span> {{ __('pagination.to') }} <span x-text="Math.min(getPeriodPage(period.id) * perPage, period.students.length)"></span> {{ __('pagination.of') }} <span x-text="period.students.length"></span> {{ __('pagination.results') }}
                            </div>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="goToPeriodPage(period.id, 1)" :disabled="getPeriodPage(period.id) === 1" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fas fa-angle-double-left"></i>
                                </button>
                                <button type="button" @click="goToPeriodPage(period.id, getPeriodPage(period.id) - 1)" :disabled="getPeriodPage(period.id) === 1" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fas fa-angle-left"></i>
                                </button>
                                <template x-for="page in getVisiblePages(period)" :key="page">
                                    <button type="button" @click="goToPeriodPage(period.id, page)" :class="page === getPeriodPage(period.id) ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-3 py-1.5 text-sm font-medium rounded-lg border" x-text="page"></button>
                                </template>
                                <button type="button" @click="goToPeriodPage(period.id, getPeriodPage(period.id) + 1)" :disabled="getPeriodPage(period.id) === getTotalPages(period)" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fas fa-angle-right"></i>
                                </button>
                                <button type="button" @click="goToPeriodPage(period.id, getTotalPages(period))" :disabled="getPeriodPage(period.id) === getTotalPages(period)" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fas fa-angle-double-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div x-show="!period.students || period.students.length === 0" class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        <i class="fas fa-inbox text-2xl mb-2"></i>
                        <p>{{ __('attendance.No attendance records found for this period') }}</p>
                    </div>
                </div>
            </template>

            <div x-show="!filteredPeriods || filteredPeriods.length === 0" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-8 text-center text-sm text-gray-500 dark:text-gray-400">
                <i class="fas fa-inbox text-2xl mb-2"></i>
                <p>{{ __('attendance.No attendance records found for this date') }}</p>
            </div>
        </div>
    </div>

    <script>
        function classDetailPage(config) {
            return {
                classId: config.classId,
                selectedDate: config.date,
                routes: config.routes,
                csrf: config.csrf,
                periods: [],
                timetablePeriods: config.timetablePeriods || [],
                hasTimetable: config.hasTimetable || false,
                perPage: config.perPage || 10,
                periodPages: {}, // Track current page for each period
                selectedPeriod: null, // null = all periods, or period.id for specific period

                setToday() {
                    this.selectedDate = new Date().toISOString().split('T')[0];
                    this.loadDetail();
                },

                loadDetail() {
                    if (!this.selectedDate) return;

                    fetch(this.routes.detail + '?' + new URLSearchParams({
                        date: this.selectedDate
                    }), {
                        headers: { 'Accept': 'application/json' }
                    })
                        .then(r => r.json())
                        .then((data) => {
                            this.periods = data.periods || [];
                            this.timetablePeriods = data.timetable_periods || this.timetablePeriods;
                            // Reset pagination for all periods
                            this.periodPages = {};
                            this.periods.forEach(p => {
                                this.periodPages[p.id] = 1;
                            });
                            // Reset period filter
                            this.selectedPeriod = null;
                        })
                        .catch(() => {
                            this.periods = [];
                            this.periodPages = {};
                        });
                },

                // Check if a timetable period has attendance data
                hasPeriodData(periodId) {
                    return this.periods.some(p => p.id === periodId && p.present_count > 0);
                },

                // Filtered periods based on selection
                get filteredPeriods() {
                    if (this.selectedPeriod === null) {
                        return this.periods;
                    }
                    return this.periods.filter(p => p.id === this.selectedPeriod);
                },

                // Pagination methods
                getPeriodPage(periodId) {
                    return this.periodPages[periodId] || 1;
                },

                goToPeriodPage(periodId, page) {
                    const period = this.periods.find(p => p.id === periodId);
                    if (!period) return;
                    const totalPages = this.getTotalPages(period);
                    if (page >= 1 && page <= totalPages) {
                        this.periodPages[periodId] = page;
                    }
                },

                getTotalPages(period) {
                    if (!period.students || period.students.length === 0) return 1;
                    return Math.ceil(period.students.length / this.perPage);
                },

                getPaginatedStudents(period) {
                    if (!period.students || period.students.length === 0) return [];
                    const currentPage = this.getPeriodPage(period.id);
                    const start = (currentPage - 1) * this.perPage;
                    return period.students.slice(start, start + this.perPage);
                },

                getVisiblePages(period) {
                    const totalPages = this.getTotalPages(period);
                    const currentPage = this.getPeriodPage(period.id);
                    const pages = [];
                    
                    let start = Math.max(1, currentPage - 2);
                    let end = Math.min(totalPages, start + 4);
                    
                    if (end - start < 4) {
                        start = Math.max(1, end - 4);
                    }
                    
                    for (let i = start; i <= end; i++) {
                        pages.push(i);
                    }
                    return pages;
                },

                getSubjectIcon(subject) {
                    const icons = {
                        'Mathematics': 'fas fa-calculator',
                        'Advanced Mathematics': 'fas fa-calculator',
                        'Calculus': 'fas fa-calculator',
                        'English': 'fas fa-book',
                        'English Literature': 'fas fa-book',
                        'Advanced English': 'fas fa-book',
                        'Literature': 'fas fa-book',
                        'Science': 'fas fa-flask',
                        'Physics': 'fas fa-atom',
                        'Advanced Physics': 'fas fa-atom',
                        'Chemistry': 'fas fa-flask',
                        'Biology': 'fas fa-dna',
                        'History': 'fas fa-landmark',
                        'Geography': 'fas fa-globe',
                        'Myanmar': 'fas fa-book-open',
                        'Physical Education': 'fas fa-running',
                        'Art': 'fas fa-palette',
                        'Music': 'fas fa-music',
                        'Programming': 'fas fa-code',
                        'Computer Science': 'fas fa-laptop-code',
                        'Data Structures': 'fas fa-sitemap',
                        'Economics': 'fas fa-chart-line'
                    };
                    return icons[subject] || 'fas fa-book-open';
                },

                getStatusClass(status) {
                    const classes = {
                        'present': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-100',
                        'absent': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-100',
                        'late': 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-100',
                        'excused': 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-100'
                    };
                    return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                }
            };
        }
    </script>
</x-app-layout>
