<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('teacher-attendance.index') }}" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                <i class="fas fa-arrow-left"></i>
            </a>
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-lg">
                <i class="fas fa-chalkboard-teacher"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('attendance.Attendance') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('attendance.Teacher Attendance Details') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8 overflow-x-hidden" x-data="teacherDetailPage(@js([
        'teacherId' => $teacher->id,
        'routes' => [
            'detail' => route('teacher-attendance.detail', $teacher->id),
        ],
        'start' => $start,
        'end' => $end,
        'month' => $month,
    ]))" x-init="loadDetail()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="space-y-1">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $teacher->user?->name ?? '—' }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $teacher->employee_id }} • {{ $teacher->department?->name ?? '—' }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('attendance.Month') }}</label>
                        <input type="month" x-model="month" @change="onMonthChange" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500" />
                        <button type="button" @click="resetToCurrentMonth" class="px-4 py-2 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-sm font-semibold">
                            <i class="fas fa-calendar-day mr-1"></i>{{ __('attendance.Today') }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-3 shadow-sm">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-green-600 text-white flex items-center justify-center shadow-lg">
                        <i class="fas fa-percentage text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('attendance.Attendance %') }}</p>
                        <p class="text-xl font-bold" x-text="summary.attendance_pct + '%'" :class="summary.attendance_pct >= 95 ? 'text-green-600' : summary.attendance_pct >= 85 ? 'text-amber-600' : 'text-red-600'"></p>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-3 shadow-sm">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-500 to-emerald-600 text-white flex items-center justify-center shadow-lg">
                        <i class="fas fa-check-circle text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('attendance.Present') }}</p>
                        <p class="text-xl font-bold text-green-600" x-text="summary.counts.present"></p>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-3 shadow-sm">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-amber-500 to-orange-600 text-white flex items-center justify-center shadow-lg">
                        <i class="fas fa-clock text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('attendance.Late') }}</p>
                        <p class="text-xl font-bold text-amber-600" x-text="summary.counts.late"></p>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-3 shadow-sm">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-red-500 to-rose-600 text-white flex items-center justify-center shadow-lg">
                        <i class="fas fa-times-circle text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('attendance.Absent') }}</p>
                        <p class="text-xl font-bold text-red-600" x-text="summary.counts.absent"></p>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-3 shadow-sm">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center shadow-lg">
                        <i class="fas fa-calendar-times text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('attendance.Leave') }}</p>
                        <p class="text-xl font-bold text-blue-600" x-text="summary.counts.excused"></p>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-3 shadow-sm">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-violet-600 text-white flex items-center justify-center shadow-lg">
                        <i class="fas fa-umbrella-beach text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('attendance.Off / Holiday') }}</p>
                        <p class="text-xl font-bold text-purple-600" x-text="(summary.counts.off || 0) + (summary.counts.holiday || 0)"></p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('attendance.Daily Attendance') }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400" x-text="rangeLabel"></p>
                    </div>
                </div>
                <div x-show="daily.length" class="divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="day in daily" :key="day.date">
                        <div class="p-4 space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-semibold text-gray-900 dark:text-white" x-text="formatDate(day.date)"></div>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold" :class="statusClass(day.status)" x-text="day.status"></span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm text-gray-700 dark:text-gray-300">
                                <div>{{ __('attendance.Time In') }}: <span class="font-semibold" x-text="day.start_time || '—'"></span></div>
                                <div>{{ __('attendance.Time Out') }}: <span class="font-semibold" x-text="day.end_time || '—'"></span></div>
                                <div>{{ __('attendance.Notes') }}: <span class="font-semibold" x-text="day.remark || '—'"></span></div>
                            </div>
                        </div>
                    </template>
                </div>
                <div x-show="!daily.length" class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">
                    <i class="fas fa-inbox text-2xl mb-2"></i>
                    <p>{{ __('attendance.No attendance records in this range.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function teacherDetailPage(config) {
            return {
                routes: config.routes,
                start: config.start,
                end: config.end,
                month: config.month,
                summary: { attendance_pct: 0, counts: { present: 0, absent: 0, late: 0, excused: 0, off: 0, holiday: 0 } },
                daily: [],
                rangeLabel: '',

                loadDetail() {
                    const params = new URLSearchParams({ start: this.start, end: this.end });
                    fetch(this.routes.detail + '?' + params.toString(), { headers: { 'Accept': 'application/json' } })
                        .then(r => r.json())
                        .then(data => {
                            this.summary = data.summary || this.summary;
                            this.daily = data.daily || [];
                            this.rangeLabel = `${data.range?.start || this.start} → ${data.range?.end || this.end}`;
                        })
                        .catch(() => {
                            this.daily = [];
                        });
                },
                onMonthChange() {
                    if (!this.month) return;
                    const m = this.month + '-01';
                    const start = new Date(m);
                    const end = new Date(start.getFullYear(), start.getMonth() + 1, 0);
                    this.start = start.toISOString().split('T')[0];
                    this.end = end.toISOString().split('T')[0];
                    this.loadDetail();
                },
                resetToCurrentMonth() {
                    const now = new Date();
                    this.month = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;
                    this.onMonthChange();
                },
                formatDate(date) {
                    const d = new Date(date);
                    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                },
                statusClass(status) {
                    const map = {
                        'present': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-100',
                        'absent': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-100',
                        'late': 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-100',
                        'excused': 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-100',
                        'off': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                        'holiday': 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-100',
                    };
                    return map[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                }
            };
        }
    </script>
</x-app-layout>
