<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-lg">
                <i class="fas fa-dollar-sign"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('finance.Finance') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('finance.Finance Management') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10 overflow-x-hidden" x-data="financeManager()">
        <!-- Toast Notification -->
        <div x-show="showToast" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             class="fixed top-4 right-4 z-50">
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg"
                 :class="toastType === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'">
                <i :class="toastType === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'"></i>
                <span x-text="toastMessage"></span>
                <button @click="showToast = false" class="ml-2 hover:opacity-75"><i class="fas fa-times"></i></button>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if(session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800 dark:border-green-900/50 dark:bg-green-900/30 dark:text-green-100">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 text-white flex items-center justify-center text-xl shadow-lg">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('finance.Total Income') }}</p>
                        <p class="text-xl font-bold text-green-600 dark:text-green-400">{{ number_format($summary['total_income'] ?? 0, 0) }} MMK</p>
                        <p class="text-xs text-green-600 dark:text-green-400">{{ $filter->periodLabel() }}</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-500 to-rose-600 text-white flex items-center justify-center text-xl shadow-lg">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('finance.Total Expenses') }}</p>
                        <p class="text-xl font-bold text-red-600 dark:text-red-400">{{ number_format($summary['total_expenses'] ?? 0, 0) }} MMK</p>
                        <p class="text-xs text-red-600 dark:text-red-400">{{ $filter->periodLabel() }}</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center text-xl shadow-lg">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('finance.Net Balance') }}</p>
                        <p class="text-xl font-bold {{ ($summary['net'] ?? 0) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ number_format($summary['net'] ?? 0, 0) }} MMK</p>
                        <p class="text-xs text-blue-600 dark:text-blue-400">{{ $filter->periodLabel() }}</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white flex items-center justify-center text-xl shadow-lg">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('finance.Selected Month') }}</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $filter->periodLabel() }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('finance.Current view') }}</p>
                    </div>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <x-academic-tabs :tabs="[
                'income' => __('finance.Income'),
                'expenses' => __('finance.Expenses'),
                'profit-loss' => __('finance.Profit & Loss'),
            ]" />

            <!-- Income Tab -->
            <div x-show="activeTab === 'income'" x-cloak>
                <!-- Student Fee Payments Section -->
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm mb-6">
                    <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('finance.Student Fee Payments') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('finance.Auto-synced from student fee payments') }}</p>
                        </div>
                        <form method="GET" action="{{ route('finance.index') }}" class="flex items-center gap-2">
                            <input type="month" name="period" value="{{ request('period', now()->format('Y-m')) }}" class="form-select-sm">
                            <button type="submit" class="btn-filter">{{ __('finance.Apply') }}</button>
                        </form>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full finance-table">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="th-cell">{{ __('finance.Date') }}</th>
                                    <th class="th-cell">{{ __('finance.Payment #') }}</th>
                                    <th class="th-cell">{{ __('finance.Student') }}</th>
                                    <th class="th-cell">{{ __('finance.Grade/Class') }}</th>
                                    <th class="th-cell">{{ __('finance.Amount') }}</th>
                                    <th class="th-cell">{{ __('finance.Payment Type') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($feePayments as $payment)
                                <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="td-cell">{{ $payment->payment_date?->format('M j, Y') }}</td>
                                    <td class="td-cell font-semibold text-green-600 dark:text-green-400">{{ $payment->payment_number }}</td>
                                    <td class="td-cell">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $payment->student?->user?->name ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ $payment->student?->student_identifier ?? '' }}</div>
                                    </td>
                                    <td class="td-cell">{{ $payment->student?->grade?->name ?? '-' }} / {{ $payment->student?->classModel?->name ?? '-' }}</td>
                                    <td class="td-cell font-bold text-green-600 dark:text-green-400">{{ number_format($payment->amount, 0) }} MMK</td>
                                    <td class="td-cell"><span class="payment-method-badge">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="td-empty">
                                        <div class="flex flex-col items-center py-8">
                                            <i class="fas fa-inbox text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
                                            <p class="text-gray-500 dark:text-gray-400">{{ __('finance.No student fee payments for this period.') }}</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($feePayments->hasPages())
                    <div class="p-4 border-t border-gray-200 dark:border-gray-700">{{ $feePayments->withQueryString()->links() }}</div>
                    @endif
                </div>

                <!-- Other Income Section -->
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('finance.Other Income') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('finance.Manual income entries (donations, grants, etc.)') }}</p>
                        </div>
                        <button type="button" class="btn-add-income" @click="showIncomeModal = true">
                            <i class="fas fa-plus"></i> {{ __('finance.Add Income') }}
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full finance-table">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="th-cell">{{ __('finance.Date') }}</th>
                                    <th class="th-cell">{{ __('finance.Income #') }}</th>
                                    <th class="th-cell">{{ __('finance.Category') }}</th>
                                    <th class="th-cell">{{ __('finance.Description') }}</th>
                                    <th class="th-cell">{{ __('finance.Amount') }}</th>
                                    <th class="th-cell">{{ __('finance.Payment Type') }}</th>
                                    <th class="th-cell">{{ __('finance.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($incomes as $income)
                                <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="td-cell">{{ $income->income_date?->format('M j, Y') }}</td>
                                    <td class="td-cell font-semibold text-green-600 dark:text-green-400">{{ $income->income_number }}</td>
                                    <td class="td-cell">{{ $income->category ?? __('finance.Other') }}</td>
                                    <td class="td-cell">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $income->title }}</div>
                                        @if($income->description)
                                        <div class="text-xs text-gray-500">{{ Str::limit($income->description, 50) }}</div>
                                        @endif
                                    </td>
                                    <td class="td-cell font-bold text-green-600 dark:text-green-400">{{ number_format($income->amount, 0) }} MMK</td>
                                    <td class="td-cell"><span class="payment-method-badge">{{ ucfirst(str_replace('_', ' ', $income->payment_method)) }}</span></td>
                                    <td class="td-cell">
                                        <form method="POST" action="{{ route('finance.income.destroy', $income) }}" onsubmit="return confirm('{{ __('finance.Delete this income?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-btn delete" title="{{ __('finance.Delete') }}"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="td-empty">
                                        <div class="flex flex-col items-center py-8">
                                            <i class="fas fa-inbox text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
                                            <p class="text-gray-500 dark:text-gray-400">{{ __('finance.No other income entries yet.') }}</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($incomes->hasPages())
                    <div class="p-4 border-t border-gray-200 dark:border-gray-700">{{ $incomes->withQueryString()->links() }}</div>
                    @endif
                </div>
            </div>

            <!-- Expenses Tab -->
            <div x-show="activeTab === 'expenses'" x-cloak>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('finance.Daily Expenses') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('finance.Track all school expenses') }}</p>
                        </div>
                        <button type="button" class="btn-add-expense" @click="showExpenseModal = true">
                            <i class="fas fa-plus"></i> {{ __('finance.Add Expense') }}
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full finance-table">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="th-cell">{{ __('finance.Date') }}</th>
                                    <th class="th-cell">{{ __('finance.Expense #') }}</th>
                                    <th class="th-cell">{{ __('finance.Category') }}</th>
                                    <th class="th-cell">{{ __('finance.Description') }}</th>
                                    <th class="th-cell">{{ __('finance.Amount') }}</th>
                                    <th class="th-cell">{{ __('finance.Payment Method') }}</th>
                                    <th class="th-cell">{{ __('finance.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($expenses as $expense)
                                <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="td-cell">{{ $expense->expense_date?->format('M j, Y') }}</td>
                                    <td class="td-cell font-semibold text-red-600 dark:text-red-400">{{ $expense->expense_number }}</td>
                                    <td class="td-cell">
                                        <span class="expense-category-badge">{{ $expense->category?->name ?? __('finance.Other') }}</span>
                                    </td>
                                    <td class="td-cell">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $expense->title }}</div>
                                        @if($expense->description)
                                        <div class="text-xs text-gray-500">{{ Str::limit($expense->description, 50) }}</div>
                                        @endif
                                    </td>
                                    <td class="td-cell font-bold text-red-600 dark:text-red-400">{{ number_format($expense->amount, 0) }} MMK</td>
                                    <td class="td-cell"><span class="payment-method-badge">{{ ucfirst(str_replace('_', ' ', $expense->payment_method)) }}</span></td>
                                    <td class="td-cell">
                                        <form method="POST" action="{{ route('finance.expense.destroy', $expense) }}" onsubmit="return confirm('{{ __('finance.Delete this expense?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-btn delete" title="{{ __('finance.Delete') }}"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="td-empty">
                                        <div class="flex flex-col items-center py-8">
                                            <i class="fas fa-inbox text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
                                            <p class="text-gray-500 dark:text-gray-400">{{ __('finance.No expenses recorded. Click "Add Expense" to add one.') }}</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($expenses->hasPages())
                    <div class="p-4 border-t border-gray-200 dark:border-gray-700">{{ $expenses->withQueryString()->links() }}</div>
                    @endif
                </div>
            </div>


            <!-- Profit & Loss Tab -->
            <div x-show="activeTab === 'profit-loss'" x-cloak>
                <!-- P&L Summary Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-5">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-green-700 dark:text-green-300 font-semibold text-sm">{{ __('finance.Total Income') }}</span>
                            <i class="fas fa-arrow-up text-green-500"></i>
                        </div>
                        <div class="text-2xl font-bold text-green-700 dark:text-green-300">{{ number_format($summary['total_income'] ?? 0, 0) }} MMK</div>
                    </div>
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-5">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-red-700 dark:text-red-300 font-semibold text-sm">{{ __('finance.Total Expenses') }}</span>
                            <i class="fas fa-arrow-down text-red-500"></i>
                        </div>
                        <div class="text-2xl font-bold text-red-700 dark:text-red-300">{{ number_format($summary['total_expenses'] ?? 0, 0) }} MMK</div>
                    </div>
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-5">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-blue-700 dark:text-blue-300 font-semibold text-sm">{{ __('finance.Net Profit/Loss') }}</span>
                            <i class="fas fa-chart-line text-blue-500"></i>
                        </div>
                        <div class="text-2xl font-bold {{ ($summary['net'] ?? 0) >= 0 ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">{{ number_format($summary['net'] ?? 0, 0) }} MMK</div>
                    </div>
                </div>

                <!-- Daily P&L Table -->
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm mb-6">
                    <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('finance.Daily Profit & Loss') }}</h3>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $filter->periodLabel() }}</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full finance-table">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="th-cell">{{ __('finance.Date') }}</th>
                                    <th class="th-cell text-right">{{ __('finance.Income') }}</th>
                                    <th class="th-cell text-right">{{ __('finance.Expenses') }}</th>
                                    <th class="th-cell text-right">{{ __('finance.Net') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($dailyBreakdown as $day)
                                <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="td-cell font-medium text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($day['date'])->format('M j, Y (D)') }}</td>
                                    <td class="td-cell text-right text-green-600 dark:text-green-400">{{ number_format($day['income'] ?? 0, 0) }} MMK</td>
                                    <td class="td-cell text-right text-red-600 dark:text-red-400">{{ number_format($day['expenses'] ?? 0, 0) }} MMK</td>
                                    <td class="td-cell text-right font-semibold {{ ($day['net'] ?? 0) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ number_format($day['net'] ?? 0, 0) }} MMK</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="td-empty">
                                        <div class="flex flex-col items-center py-8">
                                            <i class="fas fa-inbox text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
                                            <p class="text-gray-500 dark:text-gray-400">{{ __('finance.No daily data available for selected month') }}</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                                @if(count($dailyBreakdown) > 0)
                                <tr class="bg-gray-100 dark:bg-gray-800 font-bold">
                                    <td class="td-cell text-gray-900 dark:text-white">{{ __('finance.Total') }}</td>
                                    <td class="td-cell text-right text-green-600 dark:text-green-400">{{ number_format($summary['total_income'] ?? 0, 0) }} MMK</td>
                                    <td class="td-cell text-right text-red-600 dark:text-red-400">{{ number_format($summary['total_expenses'] ?? 0, 0) }} MMK</td>
                                    <td class="td-cell text-right {{ ($summary['net'] ?? 0) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ number_format($summary['net'] ?? 0, 0) }} MMK</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Monthly P&L Table -->
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm mb-6">
                    <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('finance.Monthly Profit & Loss') }}</h3>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $filter->periodLabel() }}</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full finance-table">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="th-cell">{{ __('finance.Category') }}</th>
                                    <th class="th-cell text-right">{{ __('finance.Income') }}</th>
                                    <th class="th-cell text-right">{{ __('finance.Expenses') }}</th>
                                    <th class="th-cell text-right">{{ __('finance.Net') }}</th>
                                    <th class="th-cell text-right">{{ __('finance.Percentage') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($monthlyBreakdown as $category => $data)
                                <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="td-cell font-medium text-gray-900 dark:text-white">{{ $category }}</td>
                                    <td class="td-cell text-right text-green-600 dark:text-green-400">{{ number_format($data['income'] ?? 0, 0) }} MMK</td>
                                    <td class="td-cell text-right text-red-600 dark:text-red-400">{{ number_format($data['expenses'] ?? 0, 0) }} MMK</td>
                                    <td class="td-cell text-right font-semibold {{ ($data['net'] ?? 0) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ number_format($data['net'] ?? 0, 0) }} MMK</td>
                                    <td class="td-cell text-right">
                                        @php $pct = ($summary['total_income'] ?? 0) > 0 ? (($data['income'] ?? 0) / $summary['total_income']) * 100 : 0; @endphp
                                        <span class="text-gray-600 dark:text-gray-400">{{ number_format($pct, 1) }}%</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="td-empty">
                                        <div class="flex flex-col items-center py-8">
                                            <i class="fas fa-inbox text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
                                            <p class="text-gray-500 dark:text-gray-400">{{ __('finance.No data available for selected month') }}</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                                @if(count($monthlyBreakdown) > 0)
                                <tr class="bg-gray-100 dark:bg-gray-800 font-bold">
                                    <td class="td-cell text-gray-900 dark:text-white">{{ __('finance.Total') }}</td>
                                    <td class="td-cell text-right text-green-600 dark:text-green-400">{{ number_format($summary['total_income'] ?? 0, 0) }} MMK</td>
                                    <td class="td-cell text-right text-red-600 dark:text-red-400">{{ number_format($summary['total_expenses'] ?? 0, 0) }} MMK</td>
                                    <td class="td-cell text-right {{ ($summary['net'] ?? 0) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ number_format($summary['net'] ?? 0, 0) }} MMK</td>
                                    <td class="td-cell text-right text-gray-600 dark:text-gray-400">100%</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Annual P&L Table -->
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('finance.Annual Profit & Loss') }}</h3>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $filter->year ?? now()->year }}</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full finance-table">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="th-cell">{{ __('finance.Category') }}</th>
                                    <th class="th-cell text-right">{{ __('finance.Income') }}</th>
                                    <th class="th-cell text-right">{{ __('finance.Expenses') }}</th>
                                    <th class="th-cell text-right">{{ __('finance.Net') }}</th>
                                    <th class="th-cell text-right">{{ __('finance.Percentage') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @php
                                    $annualTotalIncome = collect($annualBreakdown)->sum('income');
                                    $annualTotalExpenses = collect($annualBreakdown)->sum('expenses');
                                    $annualNet = $annualTotalIncome - $annualTotalExpenses;
                                @endphp
                                @forelse($annualBreakdown as $category => $data)
                                <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="td-cell font-medium text-gray-900 dark:text-white">{{ $category }}</td>
                                    <td class="td-cell text-right text-green-600 dark:text-green-400">{{ number_format($data['income'] ?? 0, 0) }} MMK</td>
                                    <td class="td-cell text-right text-red-600 dark:text-red-400">{{ number_format($data['expenses'] ?? 0, 0) }} MMK</td>
                                    <td class="td-cell text-right font-semibold {{ ($data['net'] ?? 0) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ number_format($data['net'] ?? 0, 0) }} MMK</td>
                                    <td class="td-cell text-right">
                                        @php $pct = $annualTotalIncome > 0 ? (($data['income'] ?? 0) / $annualTotalIncome) * 100 : 0; @endphp
                                        <span class="text-gray-600 dark:text-gray-400">{{ number_format($pct, 1) }}%</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="td-empty">
                                        <div class="flex flex-col items-center py-8">
                                            <i class="fas fa-inbox text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
                                            <p class="text-gray-500 dark:text-gray-400">{{ __('finance.No annual data available') }}</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                                @if(count($annualBreakdown) > 0)
                                <tr class="bg-gray-100 dark:bg-gray-800 font-bold">
                                    <td class="td-cell text-gray-900 dark:text-white">{{ __('finance.Total') }}</td>
                                    <td class="td-cell text-right text-green-600 dark:text-green-400">{{ number_format($annualTotalIncome, 0) }} MMK</td>
                                    <td class="td-cell text-right text-red-600 dark:text-red-400">{{ number_format($annualTotalExpenses, 0) }} MMK</td>
                                    <td class="td-cell text-right {{ $annualNet >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ number_format($annualNet, 0) }} MMK</td>
                                    <td class="td-cell text-right text-gray-600 dark:text-gray-400">100%</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


            <!-- Income Modal -->
            <div x-show="showIncomeModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="showIncomeModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75" @click="showIncomeModal = false"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                    <div x-show="showIncomeModal" class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-xl transform sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <form method="POST" action="{{ route('finance.income.store') }}">
                            @csrf
                            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white"><i class="fas fa-plus-circle text-green-500 mr-2"></i>{{ __('finance.Add Income') }}</h3>
                                    <button type="button" @click="showIncomeModal = false" class="text-gray-400 hover:text-gray-500"><i class="fas fa-times"></i></button>
                                </div>
                                <div class="space-y-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('finance.Date') }} <span class="text-red-500">*</span></label>
                                            <input type="date" name="income_date" value="{{ now()->format('Y-m-d') }}" required class="form-input-modal">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('finance.Category') }} <span class="text-red-500">*</span></label>
                                            <select name="category" required class="form-input-modal">
                                                <option value="">{{ __('finance.Select Category') }}</option>
                                                <option value="Donations">{{ __('finance.Donations') }}</option>
                                                <option value="Grants">{{ __('finance.Grants') }}</option>
                                                <option value="Additional Fees">{{ __('finance.Additional Fees') }}</option>
                                                <option value="Other">{{ __('finance.Other') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('finance.Title') }} <span class="text-red-500">*</span></label>
                                        <input type="text" name="title" required placeholder="{{ __('finance.Enter income title') }}" class="form-input-modal">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('finance.Description') }}</label>
                                        <textarea name="description" rows="2" placeholder="{{ __('finance.Enter description (optional)') }}" class="form-input-modal"></textarea>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('finance.Amount (MMK)') }} <span class="text-red-500">*</span></label>
                                            <input type="number" name="amount" required min="0" step="1" placeholder="0" class="form-input-modal">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('finance.Payment Method') }}</label>
                                            <select name="payment_method" class="form-input-modal">
                                                <option value="cash">{{ __('finance.Cash') }}</option>
                                                <option value="bank_transfer">{{ __('finance.Bank Transfer') }}</option>
                                                <option value="kbz_pay">{{ __('finance.KBZ Pay') }}</option>
                                                <option value="wave_pay">{{ __('finance.Wave Pay') }}</option>
                                                <option value="check">{{ __('finance.Check') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                                <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 sm:ml-3 sm:w-auto sm:text-sm">
                                    <i class="fas fa-check mr-2"></i>{{ __('finance.Save Income') }}
                                </button>
                                <button type="button" @click="showIncomeModal = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 sm:mt-0 sm:w-auto sm:text-sm">
                                    <i class="fas fa-times mr-2"></i>{{ __('finance.Cancel') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Expense Modal -->
            <div x-show="showExpenseModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="showExpenseModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75" @click="showExpenseModal = false"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                    <div x-show="showExpenseModal" class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-xl transform sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <form method="POST" action="{{ route('finance.expense.store') }}">
                            @csrf
                            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white"><i class="fas fa-plus-circle text-red-500 mr-2"></i>{{ __('finance.Add Expense') }}</h3>
                                    <button type="button" @click="showExpenseModal = false" class="text-gray-400 hover:text-gray-500"><i class="fas fa-times"></i></button>
                                </div>
                                <div class="space-y-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('finance.Date') }} <span class="text-red-500">*</span></label>
                                            <input type="date" name="expense_date" value="{{ now()->format('Y-m-d') }}" required class="form-input-modal">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('finance.Category') }} <span class="text-red-500">*</span></label>
                                            <select name="expense_category_id" required class="form-input-modal">
                                                <option value="">{{ __('finance.Select Category') }}</option>
                                                @foreach($expenseCategories as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('finance.Title') }} <span class="text-red-500">*</span></label>
                                        <input type="text" name="title" required placeholder="{{ __('finance.Enter expense title') }}" class="form-input-modal">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('finance.Description') }}</label>
                                        <textarea name="description" rows="2" placeholder="{{ __('finance.Enter description (optional)') }}" class="form-input-modal"></textarea>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('finance.Amount (MMK)') }} <span class="text-red-500">*</span></label>
                                            <input type="number" name="amount" required min="0" step="1" placeholder="0" class="form-input-modal">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('finance.Payment Method') }}</label>
                                            <select name="payment_method" class="form-input-modal">
                                                <option value="cash">{{ __('finance.Cash') }}</option>
                                                <option value="bank_transfer">{{ __('finance.Bank Transfer') }}</option>
                                                <option value="kbz_pay">{{ __('finance.KBZ Pay') }}</option>
                                                <option value="wave_pay">{{ __('finance.Wave Pay') }}</option>
                                                <option value="check">{{ __('finance.Check') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                                <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm">
                                    <i class="fas fa-check mr-2"></i>{{ __('finance.Save Expense') }}
                                </button>
                                <button type="button" @click="showExpenseModal = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 sm:mt-0 sm:w-auto sm:text-sm">
                                    <i class="fas fa-times mr-2"></i>{{ __('finance.Cancel') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
        .finance-tab { padding: 12px 20px; font-size: 14px; font-weight: 500; color: #6b7280; border-bottom: 3px solid transparent; transition: all 0.2s; display: flex; align-items: center; gap: 8px; }
        .finance-tab:hover { color: #374151; }
        .finance-tab.active { color: #059669; border-bottom-color: #059669; }
        .dark .finance-tab { color: #9ca3af; }
        .dark .finance-tab:hover { color: #e5e7eb; }
        .dark .finance-tab.active { color: #34d399; border-bottom-color: #34d399; }
        .finance-table { width: 100%; border-collapse: collapse; }
        .th-cell { padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; white-space: nowrap; }
        .dark .th-cell { color: #9ca3af; }
        .td-cell { padding: 12px 16px; font-size: 14px; color: #374151; white-space: nowrap; }
        .dark .td-cell { color: #e5e7eb; }
        .td-empty { padding: 40px 16px; text-align: center; color: #9ca3af; }
        .payment-method-badge { display: inline-flex; padding: 4px 10px; font-size: 12px; font-weight: 500; border-radius: 6px; background: #f1f5f9; color: #475569; }
        .dark .payment-method-badge { background: #374151; color: #d1d5db; }
        .expense-category-badge { display: inline-flex; padding: 4px 10px; font-size: 12px; font-weight: 500; border-radius: 6px; background: #fef2f2; color: #991b1b; }
        .dark .expense-category-badge { background: #450a0a; color: #fca5a5; }
        .btn-filter { padding: 8px 16px; font-size: 13px; font-weight: 500; color: #fff; background: linear-gradient(135deg, #3b82f6, #6366f1); border-radius: 8px; transition: all 0.2s; }
        .btn-filter:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-add-income { padding: 8px 16px; font-size: 13px; font-weight: 500; color: #fff; background: linear-gradient(135deg, #10b981, #059669); border-radius: 8px; display: flex; align-items: center; gap: 6px; transition: all 0.2s; }
        .btn-add-income:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-add-expense { padding: 8px 16px; font-size: 13px; font-weight: 500; color: #fff; background: linear-gradient(135deg, #ef4444, #dc2626); border-radius: 8px; display: flex; align-items: center; gap: 6px; transition: all 0.2s; }
        .btn-add-expense:hover { opacity: 0.9; transform: translateY(-1px); }
        .action-btn { padding: 6px 10px; font-size: 12px; border-radius: 6px; transition: all 0.2s; }
        .action-btn.delete { color: #ef4444; background: #fef2f2; }
        .action-btn.delete:hover { background: #fee2e2; }
        .dark .action-btn.delete { background: #450a0a; color: #fca5a5; }
        .form-select-sm { padding: 8px 12px; font-size: 13px; border: 1px solid #d1d5db; border-radius: 8px; background: #fff; color: #374151; }
        .dark .form-select-sm { background: #374151; border-color: #4b5563; color: #e5e7eb; }
        .form-input-modal { width: 100%; padding: 10px 12px; font-size: 14px; border: 1px solid #d1d5db; border-radius: 8px; background: #fff; color: #374151; }
        .dark .form-input-modal { background: #374151; border-color: #4b5563; color: #e5e7eb; }
        .form-input-modal:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    </style>

    <script>
        function financeManager() {
            return {
                activeTab: 'income',
                showToast: false,
                toastMessage: '',
                toastType: 'success',
                showIncomeModal: false,
                showExpenseModal: false,
                init() {
                    @if(session('status'))
                    this.showNotification('{{ session('status') }}', 'success');
                    @endif
                },
                showNotification(message, type = 'success') {
                    this.toastMessage = message;
                    this.toastType = type;
                    this.showToast = true;
                    setTimeout(() => { this.showToast = false; }, 4000);
                }
            };
        }
    </script>
</x-app-layout>
