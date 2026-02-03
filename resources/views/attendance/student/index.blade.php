<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg">
                <i class="fas fa-user-graduate"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('attendance.Attendance') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('attendance.Student Attendance') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10 overflow-x-hidden" x-data="attendancePage(@js([
        'routes' => [
            'summary' => route('student-attendance.summary'),
            'students' => route('student-attendance.students'),
            'register' => route('student-attendance.register'),
            'registerStore' => route('student-attendance.register.store'),
        ],
        'classes' => $classes,
        'grades' => $grades,
        'today' => $today,
        'csrf' => csrf_token(),
    ]))" x-init="initPage()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- View Toggle Tabs -->
            <x-academic-tabs :tabs="[
                'class' => __('attendance.Class Attendance'),
                'individual' => __('attendance.Individual Student Attendance'),
            ]" activeTab="tab" />

            <!-- Class Attendance -->
            <div x-show="tab === 'class'" x-cloak class="space-y-4">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="`{{ __('attendance.Today\'s Student Attendance') }} - ${formatDate(summaryDate)}`">{{ __('attendance.Today\'s Student Attendance') }}</h3>
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="flex items-center gap-2">
                                <label class="text-sm font-semibold text-gray-600 dark:text-gray-400">{{ __('attendance.Select Date:') }}</label>
                                <input type="date" x-model="summaryDate" @change="loadSummary" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                            <button type="button" @click="setTodaySummary()" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold rounded-lg border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <i class="fas fa-calendar-day"></i>{{ __('attendance.Today') }}
                            </button>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="flex flex-wrap items-center gap-3">
                            <label class="text-sm font-semibold text-gray-600 dark:text-gray-400">{{ __('attendance.Filter:') }}</label>
                            <select x-model="summaryFilters.class_id" @change="loadSummary" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">{{ __('attendance.All Classes') }}</option>
                                <template x-for="c in classes" :key="c.id">
                                    <option :value="c.id" x-text="c.label"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-3 shadow-sm">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center shadow-lg">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('attendance.Total Attendance %') }}</p>
                            <p class="text-2xl font-bold" x-text="overallTotals.attendancePct + '%'" :class="overallTotals.attendancePct >= 95 ? 'text-green-600' : overallTotals.attendancePct >= 85 ? 'text-amber-600' : 'text-red-600'"></p>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-3 shadow-sm">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-red-500 to-rose-600 text-white flex items-center justify-center shadow-lg">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('attendance.Total Absent') }}</p>
                            <p class="text-2xl font-bold text-red-600" x-text="overallTotals.absent"></p>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-3 shadow-sm">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-amber-500 to-orange-600 text-white flex items-center justify-center shadow-lg">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('attendance.Total Leave') }}</p>
                            <p class="text-2xl font-bold text-amber-600" x-text="overallTotals.excused"></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Class') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Total Students') }}</th>
                                <template x-for="period in periodHeaders" :key="period">
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300" x-text="'P'+period"></th>
                                </template>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Today %') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700" x-show="paginatedClassSummary.length">
                            <template x-for="item in paginatedClassSummary" :key="item.class_id">
                                <tr>
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white" x-text="item.label"></td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="item.total_students"></td>
                                    <template x-for="period in periodHeaders" :key="period">
                                        <td class="px-4 py-3 text-sm">
                                            <template x-for="p in item.periods" :key="p.period_id">
                                                <span x-show="p.period_number === period" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold" :class="p.present_pct >= 95 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-100' : p.present_pct >= 85 ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-100' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-100'" x-text="p.present_pct + '%'" ></span>
                                            </template>
                                        </td>
                                    </template>
                                    <td class="px-4 py-3 text-sm font-semibold" :class="item.overall_pct >= 95 ? 'text-green-600' : item.overall_pct >= 85 ? 'text-amber-600' : 'text-red-600'" x-text="item.overall_pct + '%'"></td>
                                    <td class="px-4 py-3 text-right">
                                        <a :href="getClassDetailUrl(item.class_id)" class="inline-flex items-center px-3 py-2 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-100 text-xs font-semibold hover:bg-blue-100 dark:hover:bg-blue-900/50">
                                            <i class="fas fa-eye mr-1"></i>{{ __('attendance.View Detail') }}
                                        </a>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tbody x-show="!classSummary.length">
                            <tr>
                                <td colspan="20" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('attendance.No attendance data for this date.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <!-- Class Summary Pagination -->
                    <div x-show="classSummary.length > classPerPage" class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex flex-wrap items-center justify-between gap-3">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            {{ __('pagination.Showing') }} <span x-text="Math.min((classCurrentPage - 1) * classPerPage + 1, classSummary.length)"></span> {{ __('pagination.to') }} <span x-text="Math.min(classCurrentPage * classPerPage, classSummary.length)"></span> {{ __('pagination.of') }} <span x-text="classSummary.length"></span> {{ __('pagination.results') }}
                        </div>
                        <div class="flex items-center gap-1">
                            <button type="button" @click="classCurrentPage = 1" :disabled="classCurrentPage === 1" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-angle-double-left"></i>
                            </button>
                            <button type="button" @click="classCurrentPage--" :disabled="classCurrentPage === 1" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-angle-left"></i>
                            </button>
                            <template x-for="page in classVisiblePages" :key="page">
                                <button type="button" @click="classCurrentPage = page" :class="page === classCurrentPage ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-3 py-1.5 text-sm font-medium rounded-lg border" x-text="page"></button>
                            </template>
                            <button type="button" @click="classCurrentPage++" :disabled="classCurrentPage === classTotalPages" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-angle-right"></i>
                            </button>
                            <button type="button" @click="classCurrentPage = classTotalPages" :disabled="classCurrentPage === classTotalPages" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-angle-double-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Individual Attendance -->
            <div x-show="tab === 'individual'" x-cloak class="space-y-4">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow-sm space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('attendance.Search') }}</label>
                            <input type="text" x-model="studentFilters.search" @input.debounce.400ms="loadStudents" placeholder="{{ __('attendance.Search by name or ID') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('attendance.Grade') }}</label>
                            <select x-model="studentFilters.grade_id" @change="loadStudents" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500">
                                <option value="">{{ __('attendance.All') }}</option>
                                <template x-for="g in grades" :key="g.id">
                                    <option :value="g.id" x-text="g.label"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('attendance.Class') }}</label>
                            <select x-model="studentFilters.class_id" @change="loadStudents" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500">
                                <option value="">{{ __('attendance.All') }}</option>
                                <template x-for="c in classes" :key="c.id">
                                    <option :value="c.id" x-text="c.label"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('attendance.Month') }}</label>
                            <input type="month" x-model="studentFilters.month" @change="loadStudents" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" />
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Student ID') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Name') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Class') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.This Month Attendance %') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700" x-show="paginatedStudentList.length">
                            <template x-for="student in paginatedStudentList" :key="student.id">
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white" x-text="student.identifier"></td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white" x-text="student.name"></td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="student.class"></td>
                                    <td class="px-4 py-3 text-sm font-semibold" :class="student.attendance_percent >= 95 ? 'text-green-600' : student.attendance_percent >= 85 ? 'text-amber-600' : 'text-red-600'" x-text="student.attendance_percent !== null ? student.attendance_percent + '%' : '—'"></td>
                                    <td class="px-4 py-3 text-right">
                                        <a :href="getStudentDetailUrl(student.id)" class="inline-flex items-center px-3 py-2 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-100 text-xs font-semibold hover:bg-blue-100 dark:hover:bg-blue-900/50">
                                            <i class="fas fa-eye mr-1"></i>{{ __('attendance.View') }}
                                        </a>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tbody x-show="!studentList.length">
                            <tr>
                                <td colspan="10" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('attendance.No students found for this filter.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <!-- Student List Pagination -->
                    <div x-show="studentList.length > studentPerPage" class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex flex-wrap items-center justify-between gap-3">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            {{ __('pagination.Showing') }} <span x-text="Math.min((studentCurrentPage - 1) * studentPerPage + 1, studentList.length)"></span> {{ __('pagination.to') }} <span x-text="Math.min(studentCurrentPage * studentPerPage, studentList.length)"></span> {{ __('pagination.of') }} <span x-text="studentList.length"></span> {{ __('pagination.results') }}
                        </div>
                        <div class="flex items-center gap-1">
                            <button type="button" @click="studentCurrentPage = 1" :disabled="studentCurrentPage === 1" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-angle-double-left"></i>
                            </button>
                            <button type="button" @click="studentCurrentPage--" :disabled="studentCurrentPage === 1" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-angle-left"></i>
                            </button>
                            <template x-for="page in studentVisiblePages" :key="page">
                                <button type="button" @click="studentCurrentPage = page" :class="page === studentCurrentPage ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-3 py-1.5 text-sm font-medium rounded-lg border" x-text="page"></button>
                            </template>
                            <button type="button" @click="studentCurrentPage++" :disabled="studentCurrentPage === studentTotalPages" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-angle-right"></i>
                            </button>
                            <button type="button" @click="studentCurrentPage = studentTotalPages" :disabled="studentCurrentPage === studentTotalPages" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-angle-double-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function attendancePage(config) {
            return {
                tab: 'class',
                routes: config.routes,
                classes: config.classes,
                grades: config.grades,
                summaryDate: config.today,
                summaryFilters: { class_id: '' },
                classSummary: [],
                periodHeaders: [],
                overallTotals: { attendancePct: 0, absent: 0, excused: 0 },
                studentFilters: { search: '', grade_id: '', class_id: '', month: config.today.substring(0, 7) },
                studentList: [],
                csrf: config.csrf,
                
                // Pagination for class summary
                classCurrentPage: 1,
                classPerPage: 10,
                
                // Pagination for student list
                studentCurrentPage: 1,
                studentPerPage: 10,
                
                initPage() {
                    this.loadSummary();
                    this.loadStudents();
                },
                setTodaySummary() {
                    this.summaryDate = config.today;
                    this.loadSummary();
                },
                loadSummary() {
                    if (!this.summaryDate) return;
                    fetch(this.routes.summary + '?' + new URLSearchParams({
                        date: this.summaryDate,
                        class_id: this.summaryFilters.class_id || ''
                    }), {
                        headers: { 'Accept': 'application/json' }
                    })
                        .then(r => r.json())
                        .then(({ data }) => {
                            this.classSummary = data || [];
                            this.periodHeaders = this.extractPeriodHeaders();
                            this.overallTotals = this.computeOverallTotals();
                            this.classCurrentPage = 1; // Reset to first page
                        })
                        .catch(() => {
                            this.classSummary = [];
                            this.periodHeaders = [];
                            this.overallTotals = { attendancePct: 0, absent: 0, excused: 0 };
                        });
                },
                loadStudents() {
                    fetch(this.routes.students + '?' + new URLSearchParams({
                        search: this.studentFilters.search || '',
                        class_id: this.studentFilters.class_id || '',
                        grade_id: this.studentFilters.grade_id || '',
                        month: this.studentFilters.month || ''
                    }), {
                        headers: { 'Accept': 'application/json' }
                    })
                        .then(r => r.json())
                        .then(({ data }) => { 
                            this.studentList = data; 
                            this.studentCurrentPage = 1; // Reset to first page
                        })
                        .catch(() => { this.studentList = []; });
                },
                
                // Class summary pagination
                get classTotalPages() {
                    return Math.ceil(this.classSummary.length / this.classPerPage) || 1;
                },
                get paginatedClassSummary() {
                    const start = (this.classCurrentPage - 1) * this.classPerPage;
                    return this.classSummary.slice(start, start + this.classPerPage);
                },
                get classVisiblePages() {
                    const pages = [];
                    const total = this.classTotalPages;
                    const current = this.classCurrentPage;
                    let start = Math.max(1, current - 2);
                    let end = Math.min(total, start + 4);
                    if (end - start < 4) start = Math.max(1, end - 4);
                    for (let i = start; i <= end; i++) pages.push(i);
                    return pages;
                },
                
                // Student list pagination
                get studentTotalPages() {
                    return Math.ceil(this.studentList.length / this.studentPerPage) || 1;
                },
                get paginatedStudentList() {
                    const start = (this.studentCurrentPage - 1) * this.studentPerPage;
                    return this.studentList.slice(start, start + this.studentPerPage);
                },
                get studentVisiblePages() {
                    const pages = [];
                    const total = this.studentTotalPages;
                    const current = this.studentCurrentPage;
                    let start = Math.max(1, current - 2);
                    let end = Math.min(total, start + 4);
                    if (end - start < 4) start = Math.max(1, end - 4);
                    for (let i = start; i <= end; i++) pages.push(i);
                    return pages;
                },
                
                extractPeriodHeaders() {
                    const set = new Set();
                    this.classSummary.forEach(item => {
                        (item.periods || []).forEach(p => set.add(p.period_number));
                    });
                    return Array.from(set).sort((a, b) => a - b);
                },
                computeOverallTotals() {
                    let sumTodayPct = 0;
                    let totalAbsent = 0;
                    let totalLeave = 0;
                    let classCount = 0;

                    this.classSummary.forEach(item => {
                        sumTodayPct += item.overall_pct || 0;
                        classCount++;
                        const t = item.totals || {};
                        totalAbsent += t.absent || 0;
                        totalLeave += (t.leave || 0) + (t.excused || 0);
                    });

                    const attendancePct = classCount > 0 ? Math.round(sumTodayPct / classCount) : 0;
                    return { attendancePct, absent: totalAbsent, excused: totalLeave };
                },
                formatDate(dateStr) {
                    if (!dateStr) return '';
                    const d = new Date(dateStr);
                    return d.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
                },
                getClassDetailUrl(classId) {
                    return `/attendance/students/class/${classId}?date=${this.summaryDate}`;
                },
                getStudentDetailUrl(studentId) {
                    const month = this.studentFilters.month || '';
                    return `/attendance/students/detail/${studentId}?month=${month}`;
                }
            };
        }
    </script>
</x-app-layout>
