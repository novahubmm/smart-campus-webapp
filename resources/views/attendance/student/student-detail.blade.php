<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg">
                <i class="fas fa-user-graduate"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('attendance.Attendance') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('attendance.Student Attendance Details') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8 overflow-x-hidden" x-data="studentDetailPage(@js([
        'studentId' => $student->id,
        'routes' => [
            'detail' => route('student-attendance.student-detail', $student->id),
        ],
        'start' => $start,
        'end' => $end,
        'month' => $month,
    ]))" x-init="loadDetail()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-back-link 
                :href="route('student-attendance.index')"
                :text="__('attendance.Back to Attendance')"
            />
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="space-y-1">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="student.name"></h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400" x-text="student.student_id + ' • ' + student.grade + ' • ' + student.class"></p>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('attendance.Month') }}</label>
                        <input type="month" x-model="month" @change="onMonthChange" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" />
                        <button type="button" @click="resetToCurrentMonth" class="px-4 py-2 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-sm font-semibold">
                            <i class="fas fa-calendar-day mr-1"></i>{{ __('attendance.Today') }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-3 shadow-sm">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center shadow-lg">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('attendance.Attendance %') }}</p>
                        <p class="text-2xl font-bold" x-text="summary.attendance_pct + '%'" :class="summary.attendance_pct >= 95 ? 'text-green-600' : summary.attendance_pct >= 85 ? 'text-amber-600' : 'text-red-600'"></p>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-3 shadow-sm">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-green-500 to-emerald-600 text-white flex items-center justify-center shadow-lg">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('attendance.Present') }}</p>
                        <p class="text-2xl font-bold text-green-600" x-text="summary.present"></p>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-3 shadow-sm">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-red-500 to-rose-600 text-white flex items-center justify-center shadow-lg">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('attendance.Absent') }}</p>
                        <p class="text-2xl font-bold text-red-600" x-text="summary.absent"></p>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-3 shadow-sm">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-amber-500 to-orange-600 text-white flex items-center justify-center shadow-lg">
                        <i class="fas fa-calendar-times"></i>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('attendance.Leave') }}</p>
                        <p class="text-2xl font-bold text-amber-600" x-text="summary.excused"></p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('attendance.Daily Attendance (by period)') }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400" x-text="rangeLabel"></p>
                    </div>
                </div>
                <div x-show="daily.length" class="divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="day in daily" :key="day.date">
                        <div class="p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-semibold text-gray-900 dark:text-white" x-text="formatDate(day.date) + ' • ' + day.day"></div>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="period in day.periods" :key="period.period_number">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold" :class="statusClass(period.status)">
                                            <span x-text="'P'+(period.period_number || '-')"></span>
                                            <span class="ml-2" x-text="period.subject_name || '—'"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>
                            <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Period') }}</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Subject') }}</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Status') }}</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Notes') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        <template x-for="period in day.periods" :key="period.period_number + '-' + period.subject_name">
                                            <tr>
                                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white" x-text="period.period_number ? 'P'+period.period_number : '—'"></td>
                                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="period.subject_name || '—'"></td>
                                                <td class="px-4 py-3 text-sm">
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold" :class="statusClass(period.status)" x-text="period.status || '—'"></span>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="period.remark || '—'"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
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
        function studentDetailPage(config) {
            return {
                studentId: config.studentId,
                routes: config.routes,
                start: config.start,
                end: config.end,
                month: config.month,
                student: @js([
                    'name' => $student->user?->name ?? '—',
                    'student_id' => $student->student_id,
                    'identifier' => $student->student_identifier,
                    'grade' => $student->grade?->level !== null ? \App\Helpers\GradeHelper::getLocalizedName($student->grade->level) : '—',
                    'class' => $student->classModel ? \App\Helpers\SectionHelper::formatFullClassName($student->classModel->name, $student->grade?->level) : '—',
                ]),
                summary: { attendance_pct: 0, present: 0, absent: 0, excused: 0, late: 0, total: 0, total_days: 0 },
                daily: [],
                rangeLabel: '',

                loadDetail() {
                    const params = new URLSearchParams({ start: this.start, end: this.end });
                    fetch(this.routes.detail + '?' + params.toString(), { headers: { 'Accept': 'application/json' } })
                        .then(r => r.json())
                        .then(data => {
                            this.student = data.student || this.student;
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
                    };
                    return map[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                }
            };
        }
    </script>
</x-app-layout>
