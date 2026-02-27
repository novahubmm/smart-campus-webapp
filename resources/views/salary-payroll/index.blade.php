<x-app-layout>
    <x-slot name="header">
        <x-page-header
            icon="fas fa-money-check-alt"
            iconBg="bg-gradient-to-br from-blue-500 to-indigo-600"
            iconColor="text-white"
            :subtitle="__('salary_payroll.Finance')"
            :title="__('salary_payroll.Salary & Payroll Management')"
        />
    </x-slot>

    @push('styles')
    <style>
        /* Prevent page horizontal scroll */
        .payroll-section {
            overflow: hidden;
            max-width: 100%;
            width: 100%;
        }
        
        /* Table wrapper with horizontal scroll */
        .payroll-table-wrapper {
            position: relative;
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            overflow-y: visible;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 4px;
        }
        
        /* Force scrollbar to always show on macOS */
        .payroll-table-wrapper::-webkit-scrollbar {
            -webkit-appearance: none;
            height: 12px;
            display: block !important;
        }
        .payroll-table-wrapper::-webkit-scrollbar-track {
            background: #e2e8f0;
            border-radius: 6px;
        }
        .payroll-table-wrapper::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #94a3b8 0%, #64748b 100%);
            border-radius: 6px;
            border: 2px solid #e2e8f0;
            min-width: 40px;
        }
        .payroll-table-wrapper::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #64748b 0%, #475569 100%);
        }
        .dark .payroll-table-wrapper::-webkit-scrollbar-track { background: #1e293b; }
        .dark .payroll-table-wrapper::-webkit-scrollbar-thumb { 
            background: linear-gradient(180deg, #475569 0%, #334155 100%);
            border-color: #1e293b; 
        }
        .dark .payroll-table-wrapper::-webkit-scrollbar-thumb:hover { 
            background: linear-gradient(180deg, #64748b 0%, #475569 100%);
        }
        
        /* Firefox scrollbar - always visible */
        .payroll-table-wrapper {
            scrollbar-width: auto;
            scrollbar-color: #94a3b8 #e2e8f0;
        }
        .dark .payroll-table-wrapper {
            scrollbar-color: #475569 #1e293b;
        }
        
        /* Table styling */
        .payroll-table {
            border-collapse: separate;
            border-spacing: 0;
            min-width: 1800px;
            width: max-content;
        }
        
        /* Sticky columns */
        .sticky-col {
            position: sticky;
            z-index: 10;
            background-color: #ffffff;
        }
        .dark .sticky-col {
            background-color: #1f2937;
        }
        .sticky-col-1 { left: 0; min-width: 50px; max-width: 50px; }
        .sticky-col-2 { left: 50px; min-width: 200px; max-width: 200px; }
        .sticky-col-3 { left: 250px; min-width: 120px; max-width: 120px; box-shadow: 3px 0 6px -3px rgba(0,0,0,0.15); }
        .dark .sticky-col-3 { box-shadow: 3px 0 6px -3px rgba(0,0,0,0.4); }
        
        /* Header sticky columns */
        thead .sticky-col {
            background-color: #f9fafb !important;
        }
        .dark thead .sticky-col {
            background-color: #374151 !important;
        }
        
        /* Body sticky columns */
        tbody .sticky-col {
            background-color: #ffffff;
        }
        .dark tbody .sticky-col {
            background-color: #1f2937;
        }
        
        /* Ensure hover state works on sticky columns */
        .payroll-table tbody tr:hover .sticky-col {
            background-color: #f9fafb !important;
        }
        .dark .payroll-table tbody tr:hover .sticky-col {
            background-color: rgb(55 65 81) !important;
        }
    </style>
    @endpush

    <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-6 overflow-x-hidden" x-data="salaryPayrollManager()">
        <!-- Toast Notification -->
        <x-toast />
        <x-alert-dialog />

        @if(session('success'))
            <x-alert-success :message="session('success')" />
        @endif

        <!-- Tabs Navigation -->
        <x-academic-tabs :tabs="[
            'management' => __('salary_payroll.Payroll Management'),
            'history' => __('salary_payroll.Payroll History'),
        ]" />

        <!-- Payroll Management Tab -->
        <div x-show="activeTab === 'management'" x-cloak>
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 shadow-sm flex items-start gap-4">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center text-xl shadow-lg">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('salary_payroll.Total Payout') }}</h4>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['totalPayout'], 0) }} <span class="text-sm font-normal">MMK</span></div>
                        <div class="text-xs text-blue-600 dark:text-blue-400 mt-1">{{ \Carbon\Carbon::createFromDate($selectedYear, $selectedMonth, 1)->format('F Y') }}</div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 shadow-sm flex items-start gap-4">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 text-white flex items-center justify-center text-xl shadow-lg">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('salary_payroll.Total Employees') }}</h4>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['totalEmployees'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $stats['teacherCount'] }} {{ __('salary_payroll.Teachers') }}, {{ $stats['staffCount'] }} {{ __('salary_payroll.Staff') }}</div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 shadow-sm flex items-start gap-4">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 text-white flex items-center justify-center text-xl shadow-lg">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('salary_payroll.Withdrawn') }}</h4>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['paidCount'] }} / {{ $stats['totalEmployees'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $stats['totalEmployees'] > 0 ? round(($stats['paidCount'] / $stats['totalEmployees']) * 100) : 0 }}% {{ __('salary_payroll.completed') }}</div>
                    </div>
                </div>
            </div>

            <!-- Payroll Table Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm payroll-section">
                <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('salary_payroll.Payroll Management') }}</h3>
                </div>

                <!-- Filters -->
                <div class="p-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">{{ __('salary_payroll.Filters:') }}</span>
                        <select x-model="filters.employeeType" @change="applyFilters()" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="all">{{ __('salary_payroll.All Employees') }}</option>
                            <option value="teacher">{{ __('salary_payroll.Teachers Only') }}</option>
                            <option value="staff">{{ __('salary_payroll.Staff Only') }}</option>
                        </select>
                        <input type="text" x-model="filters.search" @input.debounce.300ms="applyFilters()" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500 min-w-[200px]" placeholder="{{ __('salary_payroll.Search by name or ID...') }}">
                        <button type="button" @click="resetFilters()" class="px-3 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">{{ __('salary_payroll.Reset') }}</button>
                    </div>
                </div>

                <!-- Table with Sticky Columns -->
                <div class="payroll-table-wrapper">
                    <table class="divide-y divide-gray-200 dark:divide-gray-700 payroll-table">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="sticky-col sticky-col-1 px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap bg-gray-50 dark:bg-gray-700">{{ __('salary_payroll.No.') }}</th>
                                <th class="sticky-col sticky-col-2 px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap bg-gray-50 dark:bg-gray-700">{{ __('salary_payroll.Employee') }}</th>
                                <th class="sticky-col sticky-col-3 px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap bg-gray-50 dark:bg-gray-700">{{ __('salary_payroll.Position') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('salary_payroll.Department') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('salary_payroll.Working Days') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('salary_payroll.Days Present') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('salary_payroll.Leave Days') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('salary_payroll.Days Absent') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('salary_payroll.Basic Salary') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('salary_payroll.Attendance Allowance') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('salary_payroll.Loyalty Bonus') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('salary_payroll.Other Bonus') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('salary_payroll.Total Salary') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('salary_payroll.Paid Salary') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('salary_payroll.Remaining Amount') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('salary_payroll.Date of Joining') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('salary_payroll.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <template x-for="(entry, index) in paginatedEntries" :key="entry.employee_id">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="sticky-col sticky-col-1 px-4 py-3 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800" x-text="(currentPage - 1) * perPage + index + 1"></td>
                                    <td class="sticky-col sticky-col-2 px-4 py-3 bg-white dark:bg-gray-800">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center text-xs font-semibold" x-text="entry.name.substring(0, 2).toUpperCase()"></div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="entry.name"></div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400" x-text="entry.display_employee_id || entry.employee_id"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="sticky-col sticky-col-3 px-4 py-3 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800" x-text="entry.position"></td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="entry.department"></td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="entry.working_days"></td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="entry.days_present"></td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="entry.leave_days"></td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="entry.days_absent"></td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="formatNumber(entry.basic_salary)"></td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="formatNumber(entry.attendance_allowance)"></td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="formatNumber(entry.loyalty_bonus)"></td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="formatNumber(entry.other_bonus)"></td>
                                    <td class="px-4 py-3 text-sm font-semibold text-green-600 dark:text-green-400" x-text="formatNumber(entry.total_salary) + ' MMK'"></td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                        <span x-show="entry.paid_amount > 0" x-text="formatNumber(entry.paid_amount) + ' MMK'"></span>
                                        <span x-show="entry.paid_amount === 0" class="text-gray-400">-</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm font-semibold text-orange-600 dark:text-orange-400">
                                        <span x-show="entry.remaining_amount > 0" x-text="formatNumber(entry.remaining_amount) + ' MMK'"></span>
                                        <span x-show="entry.remaining_amount === 0" class="text-gray-400">-</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="entry.hire_date || '-'"></td>
                                    <td class="px-4 py-3 text-right">
                                        <button @click="openPayModal(entry)" type="button" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/40 dark:text-green-300 dark:hover:bg-green-900/60">
                                            <i class="fas fa-hand-holding-usd"></i> {{ __('salary_payroll.Pay') }}
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <template x-if="filteredEntries.length === 0">
                                <tr>
                                    <td colspan="17" class="px-4 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                            <i class="fas fa-inbox text-4xl mb-3 opacity-50"></i>
                                            <p class="text-sm">{{ __('salary_payroll.No payroll entries found.') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <x-alpine-pagination 
                    totalVar="filteredEntries.length"
                    currentPageVar="currentPage"
                    perPageVar="perPage"
                    totalPagesVar="totalPages"
                    visiblePagesVar="visiblePages"
                    goToPageFn="goToPage"
                />
            </div>
        </div>

        <!-- Payroll History Tab -->
        <div x-show="activeTab === 'history'" x-cloak>
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 shadow-sm flex items-start gap-4">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center text-xl shadow-lg">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('salary_payroll.Selected Month') }}</h4>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white" x-text="selectedMonthDisplay"></div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('salary_payroll.Current selection') }}</div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 shadow-sm flex items-start gap-4">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white flex items-center justify-center text-xl shadow-lg">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('salary_payroll.Total Payroll') }}</h4>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $history->total() }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('salary_payroll.Generated') }}</div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 shadow-sm flex items-start gap-4">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 text-white flex items-center justify-center text-xl shadow-lg">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('salary_payroll.Withdrawn') }}</h4>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['paidCount'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('salary_payroll.Completed') }}</div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 shadow-sm flex items-start gap-4">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center text-xl shadow-lg">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('salary_payroll.Total Amount') }}</h4>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['withdrawnAmount'], 0) }} <span class="text-sm font-normal">MMK</span></div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('salary_payroll.Withdrawn amount') }}</div>
                    </div>
                </div>
            </div>

            <!-- History Table Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm payroll-section">
                <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('salary_payroll.Browse Payroll History') }}</h3>
                    <div class="flex items-center gap-2">
                        <select x-model="historyFilters.month" @change="loadHistoryPage()" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}">{{ \Carbon\Carbon::createFromDate(2000, $m, 1)->format('F') }}</option>
                            @endforeach
                        </select>
                        <select x-model="historyFilters.year" @change="loadHistoryPage()" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach(range(now()->year, now()->year - 3) as $y)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Filters -->
                <div class="p-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">{{ __('salary_payroll.Filters:') }}</span>
                        <select x-model="historyFilters.employeeType" @change="applyHistoryFilters()" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="all">{{ __('salary_payroll.All Types') }}</option>
                            <option value="teacher">{{ __('salary_payroll.Teachers') }}</option>
                            <option value="staff">{{ __('salary_payroll.Staff') }}</option>
                        </select>
                        <input type="text" x-model="historyFilters.search" @input.debounce.300ms="applyHistoryFilters()" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500 min-w-[200px]" placeholder="{{ __('salary_payroll.Search by name or ID...') }}">
                        <button type="button" @click="resetHistoryFilters()" class="px-3 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">{{ __('salary_payroll.Reset') }}</button>
                    </div>
                </div>

                <!-- Table with Sticky Columns -->
                <div class="payroll-table-wrapper">
                    <table class="divide-y divide-gray-200 dark:divide-gray-700 payroll-table">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="sticky-col sticky-col-1 px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap bg-gray-50 dark:bg-gray-700">{{ __('salary_payroll.No.') }}</th>
                                <th class="sticky-col sticky-col-2 px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap bg-gray-50 dark:bg-gray-700">{{ __('salary_payroll.Employee') }}</th>
                                <th class="sticky-col sticky-col-3 px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap bg-gray-50 dark:bg-gray-700">{{ __('salary_payroll.Position') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('salary_payroll.Department') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('salary_payroll.Working Days') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('salary_payroll.Days Present') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('salary_payroll.Leave Days') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('salary_payroll.Days Absent') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('salary_payroll.Basic Salary') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('salary_payroll.Attendance Allowance') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('salary_payroll.Loyalty Bonus') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('salary_payroll.Other Bonus') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('salary_payroll.Total Salary') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('salary_payroll.Paid Salary') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('salary_payroll.Payment Type') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('salary_payroll.Withdrawal Date') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <template x-for="(item, index) in paginatedHistoryEntries" :key="item.employee_id + '-' + index">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="sticky-col sticky-col-1 px-4 py-3 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800" x-text="(historyCurrentPage - 1) * historyPerPage + index + 1"></td>
                                    <td class="sticky-col sticky-col-2 px-4 py-3 bg-white dark:bg-gray-800">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center text-xs font-semibold" x-text="item.employee_name.substring(0, 2).toUpperCase()"></div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="item.employee_name"></div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400" x-text="item.employee_id"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="sticky-col sticky-col-3 px-4 py-3 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800" x-text="item.position"></td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="item.department"></td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="item.working_days"></td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="item.days_present"></td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="item.leave_days"></td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="item.days_absent"></td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="formatNumber(item.basic_salary)"></td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="formatNumber(item.attendance_allowance)"></td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="formatNumber(item.loyalty_bonus)"></td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="formatNumber(item.other_bonus)"></td>
                                    <td class="px-4 py-3 text-sm font-semibold text-green-600 dark:text-green-400" x-text="formatNumber(item.total_salary) + ' MMK'"></td>
                                    <td class="px-4 py-3 text-sm text-orange-600 dark:text-orange-400 font-semibold" x-text="formatNumber(item.paid_amount) + ' MMK'"></td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="item.payment_method || '-'"></td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="item.paid_at || '-'"></td>
                                </tr>
                            </template>
                            <template x-if="filteredHistoryEntries.length === 0">
                                <tr>
                                    <td colspan="16" class="px-4 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                            <i class="fas fa-inbox text-4xl mb-3 opacity-50"></i>
                                            <p class="text-sm">{{ __('salary_payroll.No payroll history found.') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <x-alpine-pagination 
                    totalVar="filteredHistoryEntries.length"
                    currentPageVar="historyCurrentPage"
                    perPageVar="historyPerPage"
                    totalPagesVar="historyTotalPages"
                    visiblePagesVar="historyVisiblePages"
                    goToPageFn="goToHistoryPage"
                />
            </div>
        </div>

        <!-- Payment Modal -->
        <div x-show="showPayModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="showPayModal = false">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="showPayModal = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-xl w-full max-w-md shadow-2xl" @click.stop>
                    <form @submit.prevent="submitPayment">
                        <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                <i class="fas fa-check-circle text-green-600"></i> {{ __('salary_payroll.Confirm Payroll Payment') }}
                            </h4>
                            <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700" @click="showPayModal = false">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="p-5 space-y-4">
                            <p class="text-gray-700 dark:text-gray-300">
                                {{ __('salary_payroll.Are you sure you want to process payment for') }} <strong x-text="selectedEntry?.name"></strong>?
                            </p>
                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4 space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('salary_payroll.Total Salary') }}:</span>
                                    <span class="font-semibold text-gray-900 dark:text-gray-100" x-text="formatCurrency(selectedEntry?.total_salary || 0)"></span>
                                </div>
                                <div class="flex justify-between items-center" x-show="(selectedEntry?.paid_amount || 0) > 0">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('salary_payroll.Already Paid') }}:</span>
                                    <span class="font-semibold text-red-600 dark:text-red-400" x-text="'- ' + formatCurrency(selectedEntry?.paid_amount || 0)"></span>
                                </div>
                                <div class="flex justify-between items-center pt-2 border-t border-gray-200 dark:border-gray-700">
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('salary_payroll.Remaining Amount') }}:</span>
                                    <span class="font-bold text-lg text-green-600 dark:text-green-400" x-text="formatCurrency(remainingAmount)"></span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('salary_payroll.Payment Amount') }} <span class="text-red-500">*</span></label>
                                <input 
                                    type="number" 
                                    x-model.number="paymentAmount" 
                                    @input="validatePaymentAmount()"
                                    :max="remainingAmount"
                                    min="1"
                                    step="1"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500" 
                                    placeholder="{{ __('salary_payroll.Enter amount to pay') }}"
                                    required
                                >
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-show="paymentAmount > 0 && paymentAmount < remainingAmount">
                                    {{ __('salary_payroll.Remaining after this payment') }}: <span class="font-semibold" x-text="formatCurrency(remainingAmount - paymentAmount)"></span>
                                </p>
                                <p class="text-xs text-red-500 mt-1" x-show="paymentAmountError" x-text="paymentAmountError"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('salary_payroll.Payment Method') }}</label>
                                <select x-model="paymentMethod" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                                    @foreach($paymentMethods as $method)
                                        <option value="{{ $method->name }}">
                                            @if(strtolower($method->name) === 'cash')
                                                {{ $method->name }}
                                            @else
                                                {{ $method->name }} ({{ $method->account_number }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('salary_payroll.Remark') }} ({{ __('salary_payroll.Optional') }})</label>
                                <textarea x-model="paymentRemark" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="{{ __('salary_payroll.Add any remarks or notes...') }}"></textarea>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 p-5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
                            <button type="button" class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" @click="showPayModal = false">
                                {{ __('salary_payroll.Cancel') }}
                            </button>
                            <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed" :disabled="isSubmitting || !paymentAmount || paymentAmount <= 0 || !!paymentAmountError">
                                <span x-show="!isSubmitting"><i class="fas fa-check-circle mr-2"></i>{{ __('salary_payroll.Confirm Payment') }}</span>
                                <span x-show="isSubmitting"><i class="fas fa-spinner fa-spin mr-2"></i>{{ __('salary_payroll.Processing...') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function salaryPayrollManager() {
            return {
                activeTab: new URLSearchParams(window.location.search).get('tab') || 'management',
                showPayModal: false,
                isSubmitting: false,
                selectedEntry: null,
                paymentRemark: '',
                paymentMethod: '{{ $paymentMethods->first()->name ?? "Cash" }}',
                paymentAmount: 0,
                paymentAmountError: '',
                selectedMonthDisplay: '{{ \Carbon\Carbon::createFromDate($selectedYear, $selectedMonth, 1)->format("F Y") }}',
                
                // Payroll entries from server
                allEntries: @json($payrollEntries),
                filteredEntries: [],
                
                // History entries from server
                allHistoryEntries: @json($historyEntriesJson),
                filteredHistoryEntries: [],
                
                // Pagination for management table
                currentPage: 1,
                perPage: 10,
                
                // Pagination for history table
                historyCurrentPage: 1,
                historyPerPage: 10,
                
                // Filters
                filters: {
                    employeeType: 'all',
                    search: ''
                },
                historyFilters: {
                    month: '{{ $selectedMonth }}',
                    year: '{{ $selectedYear }}',
                    employeeType: 'all',
                    search: ''
                },

                init() {
                    this.filteredEntries = [...this.allEntries];
                    this.filteredHistoryEntries = [...this.allHistoryEntries];
                    
                    // Watch for tab changes and update URL
                    this.$watch('activeTab', (value) => {
                        const url = new URL(window.location);
                        url.searchParams.set('tab', value);
                        window.history.pushState({}, '', url);
                    });
                },

                // Management table pagination
                get paginatedEntries() {
                    const start = (this.currentPage - 1) * this.perPage;
                    const end = start + this.perPage;
                    return this.filteredEntries.slice(start, end);
                },

                get totalPages() {
                    return Math.ceil(this.filteredEntries.length / this.perPage) || 1;
                },

                get visiblePages() {
                    return this.getVisiblePagesFor(this.currentPage, this.totalPages);
                },

                goToPage(page) {
                    if (page >= 1 && page <= this.totalPages) {
                        this.currentPage = page;
                    }
                },

                // History table pagination
                get paginatedHistoryEntries() {
                    const start = (this.historyCurrentPage - 1) * this.historyPerPage;
                    const end = start + this.historyPerPage;
                    return this.filteredHistoryEntries.slice(start, end);
                },

                get historyTotalPages() {
                    return Math.ceil(this.filteredHistoryEntries.length / this.historyPerPage) || 1;
                },

                get historyVisiblePages() {
                    return this.getVisiblePagesFor(this.historyCurrentPage, this.historyTotalPages);
                },

                goToHistoryPage(page) {
                    if (page >= 1 && page <= this.historyTotalPages) {
                        this.historyCurrentPage = page;
                    }
                },

                // Shared pagination helper
                getVisiblePagesFor(current, total) {
                    const pages = [];
                    let start = Math.max(1, current - 2);
                    let end = Math.min(total, current + 2);
                    
                    if (current <= 3) {
                        end = Math.min(5, total);
                    }
                    if (current >= total - 2) {
                        start = Math.max(1, total - 4);
                    }
                    
                    for (let i = start; i <= end; i++) {
                        pages.push(i);
                    }
                    return pages;
                },

                applyFilters() {
                    this.filteredEntries = this.allEntries.filter(entry => {
                        if (this.filters.employeeType !== 'all' && entry.employee_type !== this.filters.employeeType) {
                            return false;
                        }
                        if (this.filters.search) {
                            const search = this.filters.search.toLowerCase();
                            if (!entry.name.toLowerCase().includes(search) && !(entry.display_employee_id || entry.employee_id).toLowerCase().includes(search)) {
                                return false;
                            }
                        }
                        return true;
                    });
                    this.currentPage = 1;
                },

                resetFilters() {
                    this.filters = { employeeType: 'all', search: '' };
                    this.applyFilters();
                },

                applyHistoryFilters() {
                    this.filteredHistoryEntries = this.allHistoryEntries.filter(entry => {
                        if (this.historyFilters.employeeType !== 'all' && entry.employee_type !== this.historyFilters.employeeType) {
                            return false;
                        }
                        if (this.historyFilters.search) {
                            const search = this.historyFilters.search.toLowerCase();
                            if (!entry.employee_name.toLowerCase().includes(search) && !entry.employee_id.toLowerCase().includes(search)) {
                                return false;
                            }
                        }
                        return true;
                    });
                    this.historyCurrentPage = 1;
                },

                resetHistoryFilters() {
                    this.historyFilters.employeeType = 'all';
                    this.historyFilters.search = '';
                    this.applyHistoryFilters();
                },

                loadHistoryPage() {
                    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                    this.selectedMonthDisplay = monthNames[this.historyFilters.month - 1] + ' ' + this.historyFilters.year;
                    
                    // Reload page with new month/year
                    const url = new URL(window.location.href);
                    url.searchParams.set('month', this.historyFilters.month);
                    url.searchParams.set('year', this.historyFilters.year);
                    window.location.href = url.toString();
                },

                formatNumber(num) {
                    return parseInt(num || 0).toLocaleString();
                },

                formatCurrency(amount) {
                    return this.formatNumber(amount) + ' MMK';
                },

                get remainingAmount() {
                    if (!this.selectedEntry) return 0;
                    const totalSalary = this.selectedEntry.total_salary || 0;
                    const paidAmount = this.selectedEntry.paid_amount || 0;
                    return totalSalary - paidAmount;
                },

                validatePaymentAmount() {
                    this.paymentAmountError = '';
                    
                    if (!this.paymentAmount || this.paymentAmount <= 0) {
                        this.paymentAmountError = '{{ __("salary_payroll.Payment amount must be greater than 0") }}';
                        return false;
                    }
                    
                    if (this.paymentAmount > this.remainingAmount) {
                        this.paymentAmountError = '{{ __("salary_payroll.Payment amount cannot exceed remaining amount") }}';
                        return false;
                    }
                    
                    return true;
                },

                openPayModal(entry) {
                    this.selectedEntry = entry;
                    this.paymentRemark = '';
                    this.paymentMethod = entry.payment_method || 'Cash';
                    this.paymentAmount = (entry.total_salary || 0) - (entry.paid_amount || 0);
                    this.paymentAmountError = '';
                    this.showPayModal = true;
                },

                async submitPayment() {
                    if (this.isSubmitting || !this.selectedEntry) return;
                    
                    if (!this.validatePaymentAmount()) {
                        return;
                    }
                    
                    this.isSubmitting = true;

                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('employee_type', this.selectedEntry.employee_type);
                    formData.append('employee_id', this.selectedEntry.employee_id);
                    formData.append('year', {{ $selectedYear }});
                    formData.append('month', {{ $selectedMonth }});
                    formData.append('working_days', this.selectedEntry.working_days);
                    formData.append('days_present', this.selectedEntry.days_present);
                    formData.append('leave_days', this.selectedEntry.leave_days);
                    formData.append('days_absent', this.selectedEntry.days_absent);
                    formData.append('basic_salary', this.selectedEntry.basic_salary);
                    formData.append('attendance_allowance', this.selectedEntry.attendance_allowance || 0);
                    formData.append('loyalty_bonus', this.selectedEntry.loyalty_bonus || 0);
                    formData.append('other_bonus', this.selectedEntry.other_bonus || 0);
                    formData.append('total_amount', this.selectedEntry.total_salary);
                    formData.append('amount', this.paymentAmount);
                    formData.append('payment_method', this.paymentMethod);
                    formData.append('remark', this.paymentRemark || '');

                    try {
                        const response = await fetch('{{ route("salary-payroll.pay") }}', {
                            method: 'POST',
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                            body: formData
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.showPayModal = false;
                            window.toast('{{ __("salary_payroll.Payroll payment processed successfully!") }}', 'success');
                            // Redirect to history tab
                            setTimeout(() => {
                                window.location.href = '{{ route("salary-payroll.index") }}?tab=history';
                            }, 1000);
                        } else {
                            window.toast(data.message || '{{ __("salary_payroll.Payment failed. Please try again.") }}', 'error');
                        }
                    } catch (error) {
                        window.toast('{{ __("salary_payroll.Payment failed. Please try again.") }}', 'error');
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
