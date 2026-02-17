<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-lg">
                <i class="fas fa-chalkboard-teacher"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('attendance.Attendance') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('attendance.Teacher Attendance') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10 overflow-x-hidden" x-data="teacherAttendancePage(@js([
        'routes' => [
            'daily' => route('teacher-attendance.daily'),
            'monthly' => route('teacher-attendance.monthly'),
            'summer' => route('teacher-attendance.summer'),
            'annual' => route('teacher-attendance.annual'),
            'detail' => route('teacher-attendance.detail', ['teacher' => '___ID___']),
            'store' => route('teacher-attendance.store'),
        ],
        'today' => $today,
        'currentMonth' => $currentMonth,
        'currentYear' => $currentYear,
        'initialTab' => $initialTab,
    ]))" x-init="initPage()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- View Toggle Tabs -->
            <x-academic-tabs :tabs="[
                'daily' => __('attendance.Daily Attd Register'),
                'monthly' => __('attendance.Monthly Attendance'),
                'summer' => __('attendance.Summer Attendance'),
                'annual' => __('attendance.Annual Attendance'),
            ]" activeTab="tab" />

            <!-- Daily Register -->
            <div x-show="tab === 'daily'" x-cloak class="space-y-4">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-3 shadow-sm">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-green-500 to-emerald-600 text-white flex items-center justify-center shadow-lg">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('attendance.Present') }}</p>
                            <p class="text-2xl font-bold text-green-600" x-text="dailyStats.present"></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('attendance.Today') }}</p>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-3 shadow-sm">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-red-500 to-rose-600 text-white flex items-center justify-center shadow-lg">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('attendance.Absent') }}</p>
                            <p class="text-2xl font-bold text-red-600" x-text="dailyStats.absent"></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('attendance.Today') }}</p>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-3 shadow-sm">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center shadow-lg">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('attendance.Leave') }}</p>
                            <p class="text-2xl font-bold text-blue-600" x-text="dailyStats.leave"></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('attendance.Today') }}</p>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-3 shadow-sm">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-emerald-500 to-green-600 text-white flex items-center justify-center shadow-lg">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('attendance.Total Teachers') }}</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="dailyStats.total"></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('attendance.All Teachers') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Daily Attendance Table -->
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('attendance.Daily Teacher Attendance') }}</h3>
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="relative">
                                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                                <input type="text" x-model="dailySearch" @input.debounce.300ms="filterDailyList" placeholder="{{ __('attendance.Search by name, ID, department...') }}" class="pl-10 pr-4 py-2 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 text-sm focus:border-emerald-500 focus:ring-emerald-500 w-64" />
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="date" x-model="dailyDate" @change="loadDaily" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                                <button type="button" @click="setTodayDaily" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold rounded-lg border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <i class="fas fa-calendar-day"></i>{{ __('attendance.Today') }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Teacher ID') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Name') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Department') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Status') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Start Time') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.End Time') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Total Hours') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700" x-show="paginatedDailyList.length">
                                <template x-for="row in paginatedDailyList" :key="row.id">
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white" x-text="row.employee_id || '—'"></td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white" x-text="row.name"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="row.department"></td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold" :class="statusClass(getRowStatus(row.id))" x-text="getRowStatus(row.id) || '—'"></span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="time" :value="getRowStartTime(row.id)" @change="updateStartTime(row.id, $event.target.value)" class="w-24 text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500" />
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="time" :value="getRowEndTime(row.id)" @change="updateEndTime(row.id, $event.target.value)" class="w-24 text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-emerald-500 focus:ring-emerald-500" />
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="calculateTotalHours(getRowStartTime(row.id), getRowEndTime(row.id))"></td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-end gap-1">
                                                <button type="button" @click="markStatus(row.id, 'present')" class="w-8 h-8 rounded-md border flex items-center justify-center transition-all" :class="getRowStatus(row.id) === 'present' ? 'border-green-500 bg-green-50 dark:bg-green-900/30 text-green-600' : 'border-gray-300 dark:border-gray-600 text-gray-400 hover:border-green-400 hover:text-green-500'" title="{{ __('attendance.Present') }}">
                                                    <i class="fas fa-check text-xs"></i>
                                                </button>
                                                <button type="button" @click="markStatus(row.id, 'absent')" class="w-8 h-8 rounded-md border flex items-center justify-center transition-all" :class="getRowStatus(row.id) === 'absent' ? 'border-red-500 bg-red-50 dark:bg-red-900/30 text-red-600' : 'border-gray-300 dark:border-gray-600 text-gray-400 hover:border-red-400 hover:text-red-500'" title="{{ __('attendance.Absent') }}">
                                                    <i class="fas fa-times text-xs"></i>
                                                </button>
                                                <button type="button" @click="markStatus(row.id, 'excused')" class="w-8 h-8 rounded-md border flex items-center justify-center transition-all" :class="getRowStatus(row.id) === 'excused' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30 text-blue-600' : 'border-gray-300 dark:border-gray-600 text-gray-400 hover:border-blue-400 hover:text-blue-500'" title="{{ __('attendance.Leave') }}">
                                                    <i class="fas fa-calendar-times text-xs"></i>
                                                </button>
                                                <button x-show="getRowStatus(row.id) === 'present' && getRowStartTime(row.id) && !getRowEndTime(row.id)" type="button" @click="checkout(row.id)" class="w-8 h-8 rounded-md border border-amber-500 bg-amber-50 dark:bg-amber-900/30 text-amber-600 flex items-center justify-center transition-all hover:bg-amber-100" title="{{ __('attendance.Checkout') }}">
                                                    <i class="fas fa-sign-out-alt text-xs"></i>
                                                </button>
                                                <a :href="detailUrl(row.id, { start: dailyDate, end: dailyDate })" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 flex items-center justify-center hover:border-emerald-400 hover:text-emerald-500" title="{{ __('attendance.View') }}">
                                                    <i class="fas fa-eye text-xs"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                            <tbody x-show="!filteredDailyList.length">
                                <tr>
                                    <td colspan="20" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('attendance.No records for this date.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <!-- Daily Pagination -->
                        <div x-show="filteredDailyList.length > perPage" class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex flex-wrap items-center justify-between gap-3">
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('pagination.Showing') }} <span x-text="Math.min((dailyCurrentPage - 1) * perPage + 1, filteredDailyList.length)"></span> {{ __('pagination.to') }} <span x-text="Math.min(dailyCurrentPage * perPage, filteredDailyList.length)"></span> {{ __('pagination.of') }} <span x-text="filteredDailyList.length"></span> {{ __('pagination.results') }}
                            </div>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="dailyCurrentPage = 1" :disabled="dailyCurrentPage === 1" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fas fa-angle-double-left"></i>
                                </button>
                                <button type="button" @click="dailyCurrentPage--" :disabled="dailyCurrentPage === 1" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fas fa-angle-left"></i>
                                </button>
                                <template x-for="page in dailyVisiblePages" :key="page">
                                    <button type="button" @click="dailyCurrentPage = page" :class="page === dailyCurrentPage ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-3 py-1.5 text-sm font-medium rounded-lg border" x-text="page"></button>
                                </template>
                                <button type="button" @click="dailyCurrentPage++" :disabled="dailyCurrentPage === dailyTotalPages" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fas fa-angle-right"></i>
                                </button>
                                <button type="button" @click="dailyCurrentPage = dailyTotalPages" :disabled="dailyCurrentPage === dailyTotalPages" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fas fa-angle-double-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Monthly Attendance -->
            <div x-show="tab === 'monthly'" x-cloak class="space-y-4">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('attendance.Employee Monthly Attendance') }}</h3>
                            <div class="flex flex-wrap items-center gap-4 mt-2 text-sm text-gray-600 dark:text-gray-400">
                                <span><strong>{{ __('attendance.Academic Year:') }}</strong> <span x-text="academicYear"></span></span>
                                <span><strong>{{ __('attendance.Total Days:') }}</strong> <span x-text="monthlyTotalDays + ' days'"></span></span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-semibold text-gray-600 dark:text-gray-400">{{ __('attendance.Month:') }}</label>
                            <input type="month" x-model="monthFilter" @change="loadMonthly" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.No.') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Name') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Employee ID') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Position') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Working Day') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Leave Days') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Annual Leave') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Days Absent') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Days Present') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Days Late') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Percentage') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Total Hours') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Total Overtime') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700" x-show="paginatedMonthlyList.length">
                                <template x-for="(row, index) in paginatedMonthlyList" :key="row.id">
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white" x-text="(monthlyCurrentPage - 1) * perPage + index + 1"></td>
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white" x-text="row.name"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="row.employee_id || '—'"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ __('attendance.Teacher') }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="row.working_days || monthlyTotalDays"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="row.counts.excused || 0"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="row.counts.annual_leave || 0"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="row.counts.absent || 0"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="row.counts.present || 0"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="row.counts.late || 0"></td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold" :class="row.attendance_pct >= 95 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-100' : row.attendance_pct >= 85 ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-100' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-100'" x-text="row.attendance_pct + '%'"></span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="row.total_hours || '—'"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="row.overtime || '00:00:00'"></td>
                                    </tr>
                                </template>
                            </tbody>
                            <tbody x-show="!monthlyList.length">
                                <tr>
                                    <td colspan="20" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('attendance.No monthly data.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <!-- Monthly Pagination -->
                        <div x-show="monthlyList.length > perPage" class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex flex-wrap items-center justify-between gap-3">
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('pagination.Showing') }} <span x-text="Math.min((monthlyCurrentPage - 1) * perPage + 1, monthlyList.length)"></span> {{ __('pagination.to') }} <span x-text="Math.min(monthlyCurrentPage * perPage, monthlyList.length)"></span> {{ __('pagination.of') }} <span x-text="monthlyList.length"></span> {{ __('pagination.results') }}
                            </div>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="monthlyCurrentPage = 1" :disabled="monthlyCurrentPage === 1" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fas fa-angle-double-left"></i>
                                </button>
                                <button type="button" @click="monthlyCurrentPage--" :disabled="monthlyCurrentPage === 1" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fas fa-angle-left"></i>
                                </button>
                                <template x-for="page in monthlyVisiblePages" :key="page">
                                    <button type="button" @click="monthlyCurrentPage = page" :class="page === monthlyCurrentPage ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-3 py-1.5 text-sm font-medium rounded-lg border" x-text="page"></button>
                                </template>
                                <button type="button" @click="monthlyCurrentPage++" :disabled="monthlyCurrentPage === monthlyTotalPages" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fas fa-angle-right"></i>
                                </button>
                                <button type="button" @click="monthlyCurrentPage = monthlyTotalPages" :disabled="monthlyCurrentPage === monthlyTotalPages" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fas fa-angle-double-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summer Attendance -->
            <div x-show="tab === 'summer'" x-cloak class="space-y-4">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('attendance.Summer Employee Attendance') }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1"><strong>{{ __('attendance.Academic Year:') }}</strong> <span x-text="summerYear + ' - ' + (parseInt(summerYear) + 1)"></span></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-semibold text-gray-600 dark:text-gray-400">{{ __('attendance.Year:') }}</label>
                            <input type="number" x-model="summerYear" @change="loadSummer" class="w-24 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th rowspan="2" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 border-r border-gray-200 dark:border-gray-600">{{ __('attendance.No.') }}</th>
                                    <th rowspan="2" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 border-r border-gray-200 dark:border-gray-600">{{ __('attendance.Name') }}</th>
                                    <th rowspan="2" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 border-r border-gray-200 dark:border-gray-600">{{ __('attendance.Employee ID') }}</th>
                                    <th rowspan="2" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 border-r border-gray-200 dark:border-gray-600">{{ __('attendance.Position') }}</th>
                                    <th colspan="3" class="px-4 py-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-600">{{ __('attendance.March') }}</th>
                                    <th colspan="3" class="px-4 py-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-600">{{ __('attendance.April') }}</th>
                                    <th colspan="3" class="px-4 py-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 border-b border-gray-200 dark:border-gray-600">{{ __('attendance.May') }}</th>
                                </tr>
                                <tr>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Days') }}</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Present') }}</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 border-r border-gray-200 dark:border-gray-600">%</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Days') }}</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Present') }}</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 border-r border-gray-200 dark:border-gray-600">%</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Days') }}</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Present') }}</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-300">%</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700" x-show="paginatedSummerList.length">
                                <template x-for="(row, index) in paginatedSummerList" :key="row.id">
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white border-r border-gray-200 dark:border-gray-700" x-text="(summerCurrentPage - 1) * perPage + index + 1"></td>
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white border-r border-gray-200 dark:border-gray-700" x-text="row.name"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-700" x-text="row.employee_id || '—'"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-700">{{ __('attendance.Teacher') }}</td>
                                        <td class="px-3 py-3 text-sm text-center text-gray-700 dark:text-gray-300" x-text="row.months?.march?.days || '-'"></td>
                                        <td class="px-3 py-3 text-sm text-center text-gray-700 dark:text-gray-300" x-text="row.months?.march?.present || '-'"></td>
                                        <td class="px-3 py-3 text-sm text-center border-r border-gray-200 dark:border-gray-700">
                                            <span x-show="row.months?.march?.pct !== undefined" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold" :class="row.months?.march?.pct >= 95 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-100' : row.months?.march?.pct >= 85 ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-100' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-100'" x-text="row.months?.march?.pct + '%'"></span>
                                            <span x-show="row.months?.march?.pct === undefined" class="text-gray-400">-</span>
                                        </td>
                                        <td class="px-3 py-3 text-sm text-center text-gray-700 dark:text-gray-300" x-text="row.months?.april?.days || '-'"></td>
                                        <td class="px-3 py-3 text-sm text-center text-gray-700 dark:text-gray-300" x-text="row.months?.april?.present || '-'"></td>
                                        <td class="px-3 py-3 text-sm text-center border-r border-gray-200 dark:border-gray-700">
                                            <span x-show="row.months?.april?.pct !== undefined" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold" :class="row.months?.april?.pct >= 95 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-100' : row.months?.april?.pct >= 85 ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-100' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-100'" x-text="row.months?.april?.pct + '%'"></span>
                                            <span x-show="row.months?.april?.pct === undefined" class="text-gray-400">-</span>
                                        </td>
                                        <td class="px-3 py-3 text-sm text-center text-gray-700 dark:text-gray-300" x-text="row.months?.may?.days || '-'"></td>
                                        <td class="px-3 py-3 text-sm text-center text-gray-700 dark:text-gray-300" x-text="row.months?.may?.present || '-'"></td>
                                        <td class="px-3 py-3 text-sm text-center">
                                            <span x-show="row.months?.may?.pct !== undefined" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold" :class="row.months?.may?.pct >= 95 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-100' : row.months?.may?.pct >= 85 ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-100' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-100'" x-text="row.months?.may?.pct + '%'"></span>
                                            <span x-show="row.months?.may?.pct === undefined" class="text-gray-400">-</span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                            <tbody x-show="!summerList.length">
                                <tr>
                                    <td colspan="20" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('attendance.No records for this summer period.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <!-- Summer Pagination -->
                        <div x-show="summerList.length > perPage" class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex flex-wrap items-center justify-between gap-3">
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('pagination.Showing') }} <span x-text="Math.min((summerCurrentPage - 1) * perPage + 1, summerList.length)"></span> {{ __('pagination.to') }} <span x-text="Math.min(summerCurrentPage * perPage, summerList.length)"></span> {{ __('pagination.of') }} <span x-text="summerList.length"></span> {{ __('pagination.results') }}
                            </div>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="summerCurrentPage = 1" :disabled="summerCurrentPage === 1" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fas fa-angle-double-left"></i>
                                </button>
                                <button type="button" @click="summerCurrentPage--" :disabled="summerCurrentPage === 1" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fas fa-angle-left"></i>
                                </button>
                                <template x-for="page in summerVisiblePages" :key="page">
                                    <button type="button" @click="summerCurrentPage = page" :class="page === summerCurrentPage ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-3 py-1.5 text-sm font-medium rounded-lg border" x-text="page"></button>
                                </template>
                                <button type="button" @click="summerCurrentPage++" :disabled="summerCurrentPage === summerTotalPages" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fas fa-angle-right"></i>
                                </button>
                                <button type="button" @click="summerCurrentPage = summerTotalPages" :disabled="summerCurrentPage === summerTotalPages" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fas fa-angle-double-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Annual Attendance -->
            <div x-show="tab === 'annual'" x-cloak class="space-y-4">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('attendance.Employee Attendance by Education Year') }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1"><strong>{{ __('attendance.Academic Year:') }}</strong> <span x-text="annualYear + ' - ' + (parseInt(annualYear) + 1)"></span></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-semibold text-gray-600 dark:text-gray-400">{{ __('attendance.Year:') }}</label>
                            <input type="number" x-model="annualYear" @change="loadAnnual" class="w-24 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th rowspan="2" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 border-r border-gray-200 dark:border-gray-600">{{ __('attendance.No.') }}</th>
                                    <th rowspan="2" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 border-r border-gray-200 dark:border-gray-600">{{ __('attendance.Name') }}</th>
                                    <th rowspan="2" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 border-r border-gray-200 dark:border-gray-600">{{ __('attendance.Employee ID') }}</th>
                                    <th rowspan="2" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 border-r border-gray-200 dark:border-gray-600">{{ __('attendance.Position') }}</th>
                                    <template x-for="month in annualMonths" :key="month.key">
                                        <th colspan="3" class="px-4 py-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 border-b border-r border-gray-200 dark:border-gray-600" x-text="month.label"></th>
                                    </template>
                                </tr>
                                <tr>
                                    <template x-for="month in annualMonths" :key="month.key + '-sub'">
                                        <template x-fragment>
                                            <th class="px-3 py-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Days') }}</th>
                                            <th class="px-3 py-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('attendance.Present') }}</th>
                                            <th class="px-3 py-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 border-r border-gray-200 dark:border-gray-600">%</th>
                                        </template>
                                    </template>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700" x-show="paginatedAnnualList.length">
                                <template x-for="(row, index) in paginatedAnnualList" :key="row.id">
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white border-r border-gray-200 dark:border-gray-700" x-text="(annualCurrentPage - 1) * perPage + index + 1"></td>
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white border-r border-gray-200 dark:border-gray-700" x-text="row.name"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-700" x-text="row.employee_id || '—'"></td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-700">{{ __('attendance.Teacher') }}</td>
                                        <template x-for="month in annualMonths" :key="row.id + '-' + month.key">
                                            <template x-fragment>
                                                <td class="px-3 py-3 text-sm text-center text-gray-700 dark:text-gray-300" x-text="row.months?.[month.key]?.days || '-'"></td>
                                                <td class="px-3 py-3 text-sm text-center text-gray-700 dark:text-gray-300" x-text="row.months?.[month.key]?.present || '-'"></td>
                                                <td class="px-3 py-3 text-sm text-center border-r border-gray-200 dark:border-gray-700">
                                                    <span x-show="row.months?.[month.key]?.pct !== undefined" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold" :class="row.months?.[month.key]?.pct >= 95 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-100' : row.months?.[month.key]?.pct >= 85 ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-100' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-100'" x-text="row.months?.[month.key]?.pct + '%'"></span>
                                                    <span x-show="row.months?.[month.key]?.pct === undefined" class="text-gray-400">-</span>
                                                </td>
                                            </template>
                                        </template>
                                    </tr>
                                </template>
                            </tbody>
                            <tbody x-show="!annualList.length">
                                <tr>
                                    <td colspan="40" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('attendance.No records for this annual period.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <!-- Annual Pagination -->
                        <div x-show="annualList.length > perPage" class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex flex-wrap items-center justify-between gap-3">
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('pagination.Showing') }} <span x-text="Math.min((annualCurrentPage - 1) * perPage + 1, annualList.length)"></span> {{ __('pagination.to') }} <span x-text="Math.min(annualCurrentPage * perPage, annualList.length)"></span> {{ __('pagination.of') }} <span x-text="annualList.length"></span> {{ __('pagination.results') }}
                            </div>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="annualCurrentPage = 1" :disabled="annualCurrentPage === 1" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fas fa-angle-double-left"></i>
                                </button>
                                <button type="button" @click="annualCurrentPage--" :disabled="annualCurrentPage === 1" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fas fa-angle-left"></i>
                                </button>
                                <template x-for="page in annualVisiblePages" :key="page">
                                    <button type="button" @click="annualCurrentPage = page" :class="page === annualCurrentPage ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-3 py-1.5 text-sm font-medium rounded-lg border" x-text="page"></button>
                                </template>
                                <button type="button" @click="annualCurrentPage++" :disabled="annualCurrentPage === annualTotalPages" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fas fa-angle-right"></i>
                                </button>
                                <button type="button" @click="annualCurrentPage = annualTotalPages" :disabled="annualCurrentPage === annualTotalPages" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fas fa-angle-double-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function teacherAttendancePage(config) {
            return {
                tab: config.initialTab || 'monthly',
                routes: config.routes,
                today: config.today,
                dailyDate: config.today,
                dailyList: [],
                filteredDailyList: [],
                dailySearch: '',
                dailyStats: { present: 0, absent: 0, leave: 0, total: 0 },
                monthFilter: config.currentMonth,
                monthlyList: [],
                monthlyTotalDays: 0,
                academicYear: '',
                summerYear: config.currentYear,
                summerList: [],
                summerStart: '',
                summerEnd: '',
                annualYear: config.currentYear,
                annualList: [],
                annualStart: '',
                annualEnd: '',
                annualMonths: [
                    { key: 'june', label: 'June' },
                    { key: 'july', label: 'July' },
                    { key: 'august', label: 'August' },
                    { key: 'september', label: 'September' },
                    { key: 'october', label: 'October' },
                    { key: 'november', label: 'November' },
                    { key: 'december', label: 'December' },
                    { key: 'january', label: 'January' },
                    { key: 'february', label: 'February' },
                ],
                
                // Pagination
                perPage: 10,
                dailyCurrentPage: 1,
                monthlyCurrentPage: 1,
                summerCurrentPage: 1,
                annualCurrentPage: 1,

                initPage() {
                    this.updateAcademicYear();
                    this.loadDaily();
                    this.loadMonthly();
                    this.loadSummer();
                    this.loadAnnual();
                },
                init() {
                    // Watch for tab changes and update URL
                    this.$watch('tab', (value) => {
                        const url = new URL(window.location);
                        url.searchParams.set('tab', value);
                        window.history.pushState({}, '', url);
                    });
                },

                updateAcademicYear() {
                    const year = parseInt(this.monthFilter.split('-')[0]);
                    const month = parseInt(this.monthFilter.split('-')[1]);
                    if (month >= 6) {
                        this.academicYear = `${year} - ${year + 1}`;
                    } else {
                        this.academicYear = `${year - 1} - ${year}`;
                    }
                },

                setTodayDaily() {
                    this.dailyDate = this.today;
                    this.loadDaily();
                },

                loadDaily() {
                    if (!this.dailyDate) return;
                    fetch(this.routes.daily + '?' + new URLSearchParams({ date: this.dailyDate }), { headers: { 'Accept': 'application/json' } })
                        .then(r => r.json())
                        .then(({ data }) => {
                            this.dailyList = data || [];
                            this.filterDailyList();
                            this.computeDailyStats();
                            this.dailyCurrentPage = 1;
                        })
                        .catch(() => {
                            this.dailyList = [];
                            this.filteredDailyList = [];
                            this.dailyStats = { present: 0, absent: 0, leave: 0, total: 0 };
                        });
                },

                filterDailyList(resetPage = true) {
                    const previousPage = this.dailyCurrentPage;
                    const q = this.dailySearch.toLowerCase().trim();
                    if (!q) {
                        this.filteredDailyList = this.dailyList;
                    } else {
                        this.filteredDailyList = this.dailyList.filter(row => {
                            return (row.name && row.name.toLowerCase().includes(q)) ||
                                   (row.employee_id && row.employee_id.toLowerCase().includes(q)) ||
                                   (row.department && row.department.toLowerCase().includes(q));
                        });
                    }
                    const totalPages = Math.ceil(this.filteredDailyList.length / this.perPage) || 1;
                    this.dailyCurrentPage = resetPage ? 1 : Math.min(Math.max(previousPage, 1), totalPages);
                },

                computeDailyStats() {
                    let present = 0, absent = 0, leave = 0;
                    this.dailyList.forEach(row => {
                        if (row.status === 'present' || row.status === 'late') present++;
                        else if (row.status === 'absent') absent++;
                        else if (row.status === 'excused') leave++;
                    });
                    this.dailyStats = { present, absent, leave, total: this.dailyList.length };
                },

                loadMonthly() {
                    if (!this.monthFilter) return;
                    this.updateAcademicYear();
                    const monthDate = this.monthFilter + '-01';
                    const d = new Date(monthDate);
                    this.monthlyTotalDays = new Date(d.getFullYear(), d.getMonth() + 1, 0).getDate();
                    fetch(this.routes.monthly + '?' + new URLSearchParams({ month: monthDate }), { headers: { 'Accept': 'application/json' } })
                        .then(r => r.json())
                        .then(({ data }) => { 
                            this.monthlyList = data || []; 
                            this.monthlyCurrentPage = 1;
                        })
                        .catch(() => { this.monthlyList = []; });
                },

                loadSummer() {
                    const year = this.summerYear || new Date().getFullYear();
                    this.summerStart = `${year}-03-01`;
                    this.summerEnd = `${year}-05-31`;
                    fetch(this.routes.summer + '?' + new URLSearchParams({ year }), { headers: { 'Accept': 'application/json' } })
                        .then(r => r.json())
                        .then(({ data }) => { 
                            this.summerList = data || []; 
                            this.summerCurrentPage = 1;
                        })
                        .catch(() => { this.summerList = []; });
                },

                loadAnnual() {
                    const year = this.annualYear || new Date().getFullYear();
                    this.annualStart = `${year}-06-01`;
                    this.annualEnd = `${year + 1}-02-28`;
                    fetch(this.routes.annual + '?' + new URLSearchParams({ year }), { headers: { 'Accept': 'application/json' } })
                        .then(r => r.json())
                        .then(({ data }) => { 
                            this.annualList = data || []; 
                            this.annualCurrentPage = 1;
                        })
                        .catch(() => { this.annualList = []; });
                },

                // Pagination computed properties
                get dailyTotalPages() { return Math.ceil(this.filteredDailyList.length / this.perPage) || 1; },
                get paginatedDailyList() {
                    const start = (this.dailyCurrentPage - 1) * this.perPage;
                    return this.filteredDailyList.slice(start, start + this.perPage);
                },
                get dailyVisiblePages() { return this.getVisiblePages(this.dailyCurrentPage, this.dailyTotalPages); },

                get monthlyTotalPages() { return Math.ceil(this.monthlyList.length / this.perPage) || 1; },
                get paginatedMonthlyList() {
                    const start = (this.monthlyCurrentPage - 1) * this.perPage;
                    return this.monthlyList.slice(start, start + this.perPage);
                },
                get monthlyVisiblePages() { return this.getVisiblePages(this.monthlyCurrentPage, this.monthlyTotalPages); },

                get summerTotalPages() { return Math.ceil(this.summerList.length / this.perPage) || 1; },
                get paginatedSummerList() {
                    const start = (this.summerCurrentPage - 1) * this.perPage;
                    return this.summerList.slice(start, start + this.perPage);
                },
                get summerVisiblePages() { return this.getVisiblePages(this.summerCurrentPage, this.summerTotalPages); },

                get annualTotalPages() { return Math.ceil(this.annualList.length / this.perPage) || 1; },
                get paginatedAnnualList() {
                    const start = (this.annualCurrentPage - 1) * this.perPage;
                    return this.annualList.slice(start, start + this.perPage);
                },
                get annualVisiblePages() { return this.getVisiblePages(this.annualCurrentPage, this.annualTotalPages); },

                getVisiblePages(current, total) {
                    const pages = [];
                    let start = Math.max(1, current - 2);
                    let end = Math.min(total, start + 4);
                    if (end - start < 4) start = Math.max(1, end - 4);
                    for (let i = start; i <= end; i++) pages.push(i);
                    return pages;
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
                },

                detailUrl(id, query = {}) {
                    const base = this.routes.detail.replace('___ID___', id);
                    const params = new URLSearchParams(query);
                    const qs = params.toString();
                    return qs ? `${base}?${qs}` : base;
                },

                // Get row data helpers
                getRowStatus(id) {
                    const row = this.dailyList.find(r => r.id === id);
                    return row?.status || null;
                },
                getRowStartTime(id) {
                    const row = this.dailyList.find(r => r.id === id);
                    return row?.start_time || '';
                },
                getRowEndTime(id) {
                    const row = this.dailyList.find(r => r.id === id);
                    return row?.end_time || '';
                },

                // Calculate total hours
                calculateTotalHours(startTime, endTime) {
                    if (!startTime || !endTime) return '—';
                    const [sh, sm] = startTime.split(':').map(Number);
                    const [eh, em] = endTime.split(':').map(Number);
                    let startMins = sh * 60 + sm;
                    let endMins = eh * 60 + em;
                    if (endMins < startMins) endMins += 24 * 60;
                    const diff = endMins - startMins;
                    const hours = Math.floor(diff / 60);
                    const mins = diff % 60;
                    return `${hours}h ${mins}m`;
                },

                // Mark attendance status
                markStatus(id, status) {
                    const row = this.dailyList.find(r => r.id === id);
                    if (!row) return;
                    
                    // Auto-set start time if marking present
                    let startTime = row.start_time;
                    if (status === 'present' && !startTime) {
                        const now = new Date();
                        startTime = `${String(now.getHours()).padStart(2,'0')}:${String(now.getMinutes()).padStart(2,'0')}`;
                    }
                    
                    this.saveAttendance(id, { status, start_time: startTime, end_time: row.end_time });
                },

                // Update start time
                updateStartTime(id, time) {
                    const row = this.dailyList.find(r => r.id === id);
                    if (!row) return;
                    
                    // Auto-set status to present if start time is set
                    let status = row.status;
                    if (time && !status) status = 'present';
                    
                    this.saveAttendance(id, { status, start_time: time, end_time: row.end_time });
                },

                // Update end time
                updateEndTime(id, time) {
                    const row = this.dailyList.find(r => r.id === id);
                    if (!row) return;
                    this.saveAttendance(id, { status: row.status, start_time: row.start_time, end_time: time });
                },

                // Checkout (set end time to now)
                checkout(id) {
                    const row = this.dailyList.find(r => r.id === id);
                    if (!row) return;
                    const now = new Date();
                    const endTime = `${String(now.getHours()).padStart(2,'0')}:${String(now.getMinutes()).padStart(2,'0')}`;
                    this.saveAttendance(id, { status: row.status, start_time: row.start_time, end_time: endTime });
                },

                // Save attendance to server
                saveAttendance(teacherId, data) {
                    fetch(this.routes.store, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({
                            teacher_id: teacherId,
                            date: this.dailyDate,
                            ...data
                        }),
                    })
                    .then(async r => {
                        const payload = await r.json().catch(() => ({}));
                        if (!r.ok || payload.success === false) {
                            throw new Error(payload.message || 'Failed to save attendance');
                        }
                        return payload;
                    })
                    .then((payload) => {
                        const saved = payload?.data || {};
                        // Update local data
                        const row = this.dailyList.find(r => r.id === teacherId);
                        if (row) {
                            row.status = saved.status ?? data.status;
                            row.start_time = saved.start_time ?? data.start_time;
                            row.end_time = saved.end_time ?? data.end_time;
                            row.remark = saved.remark ?? data.remark ?? row.remark;
                        }
                        this.filterDailyList(false);
                        this.computeDailyStats();
                    })
                    .catch(err => console.error('Failed to save attendance:', err));
                }
            };
        }
    </script>
</x-app-layout>
