<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-lg">
                <i class="fas fa-file-invoice-dollar"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('finance.Finance') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('finance.Student Fee Management') }}</h2>
            </div>
        </div>
    </x-slot>

    @push('styles')
    <style>
        /* Prevent page horizontal scroll */
        .student-fee-section {
            overflow: hidden;
            max-width: 100%;
            width: 100%;
        }
        
        /* Table wrapper with horizontal scroll */
        .student-fee-table-wrapper {
            position: relative;
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            overflow-y: visible;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 4px;
        }
        
        /* Scrollbar styling */
        .student-fee-table-wrapper::-webkit-scrollbar {
            -webkit-appearance: none;
            height: 12px;
            display: block !important;
        }
        .student-fee-table-wrapper::-webkit-scrollbar-track {
            background: #e2e8f0;
            border-radius: 6px;
        }
        .student-fee-table-wrapper::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #94a3b8 0%, #64748b 100%);
            border-radius: 6px;
            border: 2px solid #e2e8f0;
            min-width: 40px;
        }
        .student-fee-table-wrapper::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #64748b 0%, #475569 100%);
        }
        .dark .student-fee-table-wrapper::-webkit-scrollbar-track { background: #1e293b; }
        .dark .student-fee-table-wrapper::-webkit-scrollbar-thumb { 
            background: linear-gradient(180deg, #475569 0%, #334155 100%);
            border-color: #1e293b; 
        }
        
        /* Firefox scrollbar */
        .student-fee-table-wrapper {
            scrollbar-width: auto;
            scrollbar-color: #94a3b8 #e2e8f0;
        }
        .dark .student-fee-table-wrapper {
            scrollbar-color: #475569 #1e293b;
        }
        
        /* Table styling */
        .student-fee-table {
            border-collapse: separate;
            border-spacing: 0;
            min-width: 1400px;
            width: max-content;
        }
        
        /* Sticky columns */
        .fee-sticky-col {
            position: sticky;
            z-index: 10;
            background-color: #ffffff;
        }
        .dark .fee-sticky-col {
            background-color: #1f2937;
        }
        .fee-sticky-col-1 { left: 0; min-width: 40px; max-width: 40px; }
        .fee-sticky-col-2 { left: 40px; min-width: 150px; max-width: 150px; }
        .fee-sticky-col-3 { left: 190px; min-width: 100px; max-width: 100px; }
        .fee-sticky-col-4 { left: 290px; min-width: 120px; max-width: 120px; box-shadow: 3px 0 6px -3px rgba(0,0,0,0.15); }
        .dark .fee-sticky-col-4 { box-shadow: 3px 0 6px -3px rgba(0,0,0,0.4); }
        
        /* Header sticky columns */
        thead .fee-sticky-col {
            background-color: #f9fafb !important;
        }
        .dark thead .fee-sticky-col {
            background-color: #374151 !important;
        }
        
        /* Body sticky columns */
        tbody .fee-sticky-col {
            background-color: #ffffff;
        }
        .dark tbody .fee-sticky-col {
            background-color: #111827;
        }
        
        /* Hover state for sticky columns */
        .student-fee-table tbody tr:hover .fee-sticky-col {
            background-color: #f9fafb !important;
        }
        .dark .student-fee-table tbody tr:hover .fee-sticky-col {
            background-color: rgb(31 41 55) !important;
        }
    </style>
    @endpush

    <div class="py-6 sm:py-10 overflow-x-hidden" x-data="studentFeeManager()" x-init="init()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if(session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800 dark:border-green-900/50 dark:bg-green-900/30 dark:text-green-100">
                    {{ session('status') }}
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800 dark:border-red-900/50 dark:bg-red-900/30 dark:text-red-100">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Tabs Navigation -->
            <x-academic-tabs :tabs="[
                'invoice' => __('finance.Fee Management'),
                'history' => __('finance.Payment History'),
                'structure' => __('finance.Fee Structure'),
                'payment-methods' => __('finance.Payment Methods'),
            ]" />

            <!-- Fee Management Tab -->
            <div x-show="activeTab === 'invoice'" x-cloak>
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white flex items-center justify-center text-xl shadow-lg">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('finance.Total Receivable') }}</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format($totalReceivable, 0) }} MMK</p>
                            <p class="text-xs text-green-600 dark:text-green-400">{{ $currentMonth }}</p>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center text-xl shadow-lg">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('finance.Total Students') }}</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $totalStudents }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('finance.Active students') }}</p>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 text-white flex items-center justify-center text-xl shadow-lg">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('finance.Payments Received') }}</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $paidInvoices }} / {{ $totalInvoices }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $totalInvoices > 0 ? round(($paidInvoices / $totalInvoices) * 100) : 0 }}% {{ __('finance.collected') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Pending Payments from Guardian App -->
                @if($pendingAppPayments->count() > 0)
                <div class="bg-white dark:bg-gray-800 border border-amber-200 dark:border-amber-700 rounded-xl shadow-sm mb-6">
                    <div class="flex items-center justify-between p-4 border-b border-amber-200 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-amber-500 text-white flex items-center justify-center">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('finance.Pending Payments from Guardian App') }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $pendingAppPayments->count() }} {{ __('finance.payments awaiting confirmation') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('finance.Payment #') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('finance.Student') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('finance.Grade') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('finance.Amount') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('finance.Method') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('finance.Reference') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('finance.Date') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('finance.Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($pendingAppPayments as $payment)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                            <span class="font-mono">{{ $payment->payment_number }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $payment->student?->user?->name ?? 'N/A' }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $payment->student?->student_identifier ?? 'N/A' }}</p>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                            @gradeName($payment->student?->grade?->level ?? 0)
                                        </td>
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            {{ number_format($payment->amount, 0) }} MMK
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                @if($payment->paymentMethod?->type === 'mobile_wallet') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300
                                                @elseif($payment->paymentMethod?->type === 'bank') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                                @endif">
                                                {{ $payment->paymentMethod?->name ?? ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                            <span class="font-mono text-xs">{{ $payment->reference_number }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                            {{ $payment->payment_date?->format('M j, Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <div class="flex items-center gap-2">
                                                <form method="POST" action="{{ route('student-fees.payments.confirm', $payment) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" 
                                                            onclick="return confirm('Confirm this payment?')"
                                                            class="inline-flex items-center px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-lg transition-colors">
                                                        <i class="fas fa-check mr-1"></i>
                                                        {{ __('finance.Confirm') }}
                                                    </button>
                                                </form>
                                                <button type="button"
                                                        onclick="openRejectModal('{{ $payment->id }}')"
                                                        class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded-lg transition-colors">
                                                    <i class="fas fa-times mr-1"></i>
                                                    {{ __('finance.Reject') }}
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Fee Management Section -->
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm student-fee-section">
                    <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('finance.Student Fee List') }} - {{ $currentMonth }}</h3>
                    </div>

                    <!-- Filters -->
                    <div class="p-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                        <form method="GET" action="{{ route('student-fees.index') }}" class="flex flex-wrap items-center gap-3">
                            <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">{{ __('finance.Filters:') }}</span>
                            <select name="month" class="form-select-sm">
                                @foreach($monthOptions as $option)
                                    <option value="{{ $option['value'] }}" {{ $selectedMonth == $option['value'] ? 'selected' : '' }}>
                                        {{ $option['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            <select name="grade" class="form-select-sm">
                                <option value="">{{ __('finance.All Grades') }}</option>
                                @foreach($grades as $grade)
                                    <option value="{{ $grade->id }}" {{ request('grade') == $grade->id ? 'selected' : '' }}>@gradeName($grade->level)</option>
                                @endforeach
                            </select>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('finance.Search by name or ID...') }}" class="form-input-sm">
                            <button type="submit" class="btn-filter">{{ __('finance.Apply') }}</button>
                            <a href="{{ route('student-fees.index') }}" class="btn-filter-reset">{{ __('finance.Reset') }}</a>
                        </form>
                    </div>

                    <!-- Invoices Fee Table -->
                    <div class="student-fee-table-wrapper">
                        <table class="divide-y divide-gray-200 dark:divide-gray-700 student-fee-table">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="fee-sticky-col fee-sticky-col-1 px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap bg-gray-50 dark:bg-gray-700">{{ __('finance.No.') }}</th>
                                    <th class="fee-sticky-col fee-sticky-col-2 px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap bg-gray-50 dark:bg-gray-700">{{ __('finance.Student Name') }}</th>
                                    <th class="fee-sticky-col fee-sticky-col-3 px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap bg-gray-50 dark:bg-gray-700">{{ __('finance.Student ID') }}</th>
                                    <th class="fee-sticky-col fee-sticky-col-4 px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap bg-gray-50 dark:bg-gray-700">{{ __('finance.Grade/Class') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('finance.Invoice No') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('finance.Fee Type') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('finance.Month') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('finance.Fee Amount') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('finance.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($unpaidInvoices as $index => $invoice)
                                    @php
                                        $student = $invoice->student;
                                        $feeType = $invoice->feeStructure?->feeType;
                                        $hasRejectedProof = isset($rejectedProofsByInvoice[$invoice->id]);
                                        $rejectedProof = $hasRejectedProof ? $rejectedProofsByInvoice[$invoice->id] : null;
                                    @endphp
                                    <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800/50 {{ $hasRejectedProof ? 'border-l-4 border-red-500' : '' }}">
                                        <td class="td-cell text-center fee-sticky-col fee-sticky-col-1">{{ $unpaidInvoices->firstItem() + $index }}</td>
                                        <td class="td-cell fee-sticky-col fee-sticky-col-2">
                                            <div class="font-medium text-gray-900 dark:text-white">{{ $student->user?->name ?? '-' }}</div>
                                            @if($hasRejectedProof)
                                                <div class="flex items-center gap-1 mt-1">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                                        <i class="fas fa-times-circle mr-1"></i>
                                                        {{ __('finance.Payment Rejected') }}
                                                    </span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="td-cell text-gray-600 dark:text-gray-400 fee-sticky-col fee-sticky-col-3">{{ $student->student_identifier }}</td>
                                        <td class="td-cell fee-sticky-col fee-sticky-col-4">@gradeName($student->grade?->level ?? 0) / @className($student->classModel?->name ?? '-', $student->grade?->level)</td>
                                        <td class="td-cell">
                                            <span class="font-mono text-xs text-gray-600 dark:text-gray-400">{{ $invoice->invoice_number ?? '-' }}</span>
                                        </td>
                                        <td class="td-cell">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                                                {{ $feeType->name ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="td-cell">
                                            <div>{{ $invoice->invoice_date?->format('F Y') ?? $currentMonth }}</div>
                                            @if($hasRejectedProof && $rejectedProof->rejection_reason)
                                                <div class="text-xs text-red-600 dark:text-red-400 mt-1">
                                                    <i class="fas fa-info-circle"></i> {{ Str::limit($rejectedProof->rejection_reason, 30) }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="td-cell font-semibold">{{ number_format($invoice->total_amount, 0) }} MMK</td>
                                        <!-- Actions -->
                                        <td class="td-cell">
                                            <div class="flex items-center gap-1">
                                                <button type="button" class="action-btn process" @click="openPaymentModal(@js(['student' => $student, 'amount' => $invoice->total_amount, 'invoice' => $invoice]))" title="{{ __('finance.Process Payment') }}">
                                                    <i class="fas fa-credit-card"></i> {{ __('finance.Pay') }}
                                                </button>
                                                <button type="button" 
                                                        onclick="showInvoiceHistory('{{ $invoice->id }}')" 
                                                        class="action-btn" 
                                                        title="{{ __('finance.View Payment History') }}">
                                                    <i class="fas fa-history"></i>
                                                </button>
                                                <form method="POST" action="{{ route('student-fees.students.reinform', $student) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="action-btn" title="{{ __('finance.Send Reminder') }}" onclick="return confirm('{{ __('finance.Send payment reminder to guardian?') }}')">
                                                        <i class="fas fa-bell"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="td-empty">
                                            <div class="flex flex-col items-center py-8">
                                                <i class="fas fa-file-invoice text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
                                                <p class="text-gray-500 dark:text-gray-400">{{ __('finance.No unpaid invoices found.') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($unpaidInvoices->total() > 0)
                        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex flex-wrap items-center justify-between gap-3">
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('pagination.Showing') }} {{ $unpaidInvoices->firstItem() ?? 0 }} {{ __('pagination.to') }} {{ $unpaidInvoices->lastItem() ?? 0 }} {{ __('pagination.of') }} {{ $unpaidInvoices->total() }} {{ __('pagination.results') }}
                            </div>
                            @if($unpaidInvoices->hasPages())
                                <div class="flex items-center gap-1">
                                    {{-- First Page --}}
                                    <a href="{{ $unpaidInvoices->url(1) }}" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 {{ $unpaidInvoices->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}">
                                        <i class="fas fa-angle-double-left"></i>
                                    </a>
                                    {{-- Previous Page --}}
                                    <a href="{{ $unpaidInvoices->previousPageUrl() ?? '#' }}" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 {{ $unpaidInvoices->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}">
                                        <i class="fas fa-angle-left"></i>
                                    </a>
                                    {{-- Page Numbers --}}
                                    @php
                                        $currentPage = $unpaidInvoices->currentPage();
                                        $lastPage = $unpaidInvoices->lastPage();
                                        $start = max(1, $currentPage - 2);
                                        $end = min($lastPage, $start + 4);
                                        if ($end - $start < 4) $start = max(1, $end - 4);
                                    @endphp
                                    @for($page = $start; $page <= $end; $page++)
                                        <a href="{{ $unpaidInvoices->url($page) }}" class="px-3 py-1.5 text-sm font-medium rounded-lg border {{ $page === $currentPage ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                            {{ $page }}
                                        </a>
                                    @endfor
                                    {{-- Next Page --}}
                                    <a href="{{ $unpaidInvoices->nextPageUrl() ?? '#' }}" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 {{ !$unpaidInvoices->hasMorePages() ? 'opacity-50 pointer-events-none' : '' }}">
                                        <i class="fas fa-angle-right"></i>
                                    </a>
                                    {{-- Last Page --}}
                                    <a href="{{ $unpaidInvoices->url($unpaidInvoices->lastPage()) }}" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 {{ !$unpaidInvoices->hasMorePages() ? 'opacity-50 pointer-events-none' : '' }}">
                                        <i class="fas fa-angle-double-right"></i>
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Pending Payment Proofs Section - Always show -->
                <div class="bg-white dark:bg-gray-800 border border-purple-200 dark:border-purple-700 rounded-xl shadow-sm mt-6">
                    <div class="flex items-center justify-between p-4 border-b border-purple-200 dark:border-purple-700 bg-purple-50 dark:bg-purple-900/20">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-purple-500 text-white flex items-center justify-center">
                                <i class="fas fa-receipt"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('finance.Pending Payment Proofs') }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    @if($pendingPaymentProofs->total() > 0)
                                        {{ $pendingPaymentProofs->total() }} {{ __('finance.payment proofs awaiting verification') }}
                                    @else
                                        {{ __('finance.No pending payment proofs') }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Proof Filters -->
                    <div class="p-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                        <form method="GET" action="{{ route('student-fees.index') }}" class="flex flex-wrap items-center gap-3">
                            <!-- Preserve student fee list filters -->
                            <input type="hidden" name="month" value="{{ $selectedMonth }}">
                            @if(request('grade'))
                                <input type="hidden" name="grade" value="{{ request('grade') }}">
                            @endif
                            @if(request('search'))
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif
                            
                            <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">{{ __('finance.Proof Filters:') }}</span>
                            <select name="proof_month" class="form-select-sm">
                                @foreach($monthOptions as $option)
                                    <option value="{{ $option['value'] }}" {{ request('proof_month', $selectedMonth) == $option['value'] ? 'selected' : '' }}>
                                        {{ $option['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            <select name="proof_grade" class="form-select-sm">
                                <option value="">{{ __('finance.All Grades') }}</option>
                                @foreach($grades as $grade)
                                    <option value="{{ $grade->id }}" {{ request('proof_grade') == $grade->id ? 'selected' : '' }}>@gradeName($grade->level)</option>
                                @endforeach
                            </select>
                            <input type="text" name="proof_search" value="{{ request('proof_search') }}" placeholder="{{ __('finance.Search by name or ID...') }}" class="form-input-sm">
                            <button type="submit" class="btn-filter">{{ __('finance.Apply') }}</button>
                            <a href="{{ route('student-fees.index', ['month' => $selectedMonth]) }}" class="btn-filter-reset">{{ __('finance.Reset') }}</a>
                        </form>
                    </div>

                    <div class="p-4">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('finance.Student') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('finance.Grade') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('finance.Amount') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('finance.Months') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('finance.Method') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('finance.Payment Date') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('finance.Submitted') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('finance.Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($pendingPaymentProofs as $proof)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-4 py-3 text-sm">
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $proof->student?->user?->name ?? 'N/A' }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $proof->student?->student_identifier ?? 'N/A' }}</p>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                            @gradeName($proof->student?->grade?->level ?? 0)
                                        </td>
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            {{ number_format($proof->payment_amount, 0) }} MMK
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                            {{ $proof->payment_months }} {{ __('finance.month(s)') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                                {{ $proof->paymentMethod?->name ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                            {{ $proof->payment_date?->format('M j, Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                            {{ $proof->created_at?->diffForHumans() }}
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <button type="button"
                                                    onclick="viewPaymentProof('{{ $proof->id }}')"
                                                    class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition-colors">
                                                <i class="fas fa-eye mr-1"></i>
                                                {{ __('finance.View & Process') }}
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-8 text-center">
                                            <div class="flex flex-col items-center">
                                                <i class="fas fa-inbox text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
                                                <p class="text-gray-500 dark:text-gray-400">{{ __('finance.No pending payment proofs at the moment.') }}</p>
                                                <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">{{ __('finance.Payment proofs submitted by guardians will appear here.') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Proof Pagination -->
                    @if($pendingPaymentProofs->hasPages())
                        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex flex-wrap items-center justify-between gap-3">
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('pagination.Showing') }} {{ $pendingPaymentProofs->firstItem() ?? 0 }} {{ __('pagination.to') }} {{ $pendingPaymentProofs->lastItem() ?? 0 }} {{ __('pagination.of') }} {{ $pendingPaymentProofs->total() }} {{ __('pagination.results') }}
                            </div>
                            <div class="flex items-center gap-1">
                                {{-- Previous Page --}}
                                <a href="{{ $pendingPaymentProofs->appends(request()->except('proof_page'))->previousPageUrl() ?? '#' }}" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 {{ $pendingPaymentProofs->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}">
                                    <i class="fas fa-angle-left"></i>
                                </a>
                                {{-- Page Numbers --}}
                                @php
                                    $currentPage = $pendingPaymentProofs->currentPage();
                                    $lastPage = $pendingPaymentProofs->lastPage();
                                    $start = max(1, $currentPage - 2);
                                    $end = min($lastPage, $start + 4);
                                    if ($end - $start < 4) $start = max(1, $end - 4);
                                @endphp
                                @for($page = $start; $page <= $end; $page++)
                                    <a href="{{ $pendingPaymentProofs->appends(request()->except('proof_page'))->url($page) }}" class="px-3 py-1.5 text-sm font-medium rounded-lg border {{ $page === $currentPage ? 'bg-purple-600 text-white border-purple-600' : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                        {{ $page }}
                                    </a>
                                @endfor
                                {{-- Next Page --}}
                                <a href="{{ $pendingPaymentProofs->appends(request()->except('proof_page'))->nextPageUrl() ?? '#' }}" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 {{ !$pendingPaymentProofs->hasMorePages() ? 'opacity-50 pointer-events-none' : '' }}">
                                    <i class="fas fa-angle-right"></i>
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Fee Structure Tab -->
            <div x-show="activeTab === 'structure'" x-cloak>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                <i class="fas fa-graduation-cap text-amber-600"></i> {{ __('finance.School Fees (Monthly Recurring)') }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('finance.Monthly tuition fees set in Academic Management for each grade level') }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg text-white bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 shadow-md hover:shadow-lg transition-all" @click="openCategoryModal()">
                                <i class="fas fa-tags"></i> {{ __('finance.Add Category') }}
                            </button>
                            <button type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg text-white bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 shadow-md hover:shadow-lg transition-all" @click="openStructureModal()">
                                <i class="fas fa-plus-circle"></i> {{ __('finance.Add Fees') }}
                            </button>
                        </div>
                    </div>

                    <!-- Fee Structure Table - Show all grades with their fees from price_per_month -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="th-cell">{{ __('finance.Grade') }}</th>
                                    <th class="th-cell">{{ __('finance.Monthly Fee') }}</th>
                                    <th class="th-cell">{{ __('finance.Students') }}</th>
                                    <th class="th-cell">{{ __('finance.Collection %') }}</th>
                                    <th class="th-cell">{{ __('finance.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($grades as $grade)
                                    @php
                                        $gradeFee = $grade->price_per_month ?? 0;
                                        $gradeStudents = $studentCountByGrade[$grade->id] ?? 0;
                                        $gradeInvoices = $invoices->filter(fn($i) => $i->student && $i->student->grade_id === $grade->id);
                                        $gradePaid = $gradeInvoices->where('status', 'paid')->count();
                                        $gradeTotal = $gradeInvoices->count();
                                        $collectionPct = $gradeTotal > 0 ? round(($gradePaid / $gradeTotal) * 100) : 0;
                                    @endphp
                                    <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                        <td class="td-cell font-semibold">@gradeName($grade->level)</td>
                                        <td class="td-cell font-semibold text-amber-600 dark:text-amber-400">
                                            @if($gradeFee > 0)
                                                {{ number_format($gradeFee, 0) }} MMK
                                            @else
                                                <span class="text-gray-400">{{ __('finance.Not set') }}</span>
                                            @endif
                                        </td>
                                        <td class="td-cell">{{ $gradeStudents }} {{ __('finance.students') }}</td>
                                        <td class="td-cell">
                                            <span class="collection-badge">{{ $collectionPct }}%</span>
                                        </td>
                                        <td class="td-cell">
                                            <div class="flex items-center gap-1">
                                                <button type="button" class="action-btn edit" @click="openEditGradeFeeModal(@js($grade))" title="{{ __('finance.Edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                @if($gradeFee > 0)
                                                    <button type="button" 
                                                            class="action-btn delete" 
                                                            @click.prevent="$dispatch('confirm-show', {
                                                                title: '{{ __('finance.Clear Fee') }}',
                                                                message: '{{ __('finance.Are you sure you want to clear the fee for this grade?') }}',
                                                                confirmText: '{{ __('finance.Clear') }}',
                                                                cancelText: '{{ __('finance.Cancel') }}',
                                                                onConfirm: () => clearGradeFee('{{ $grade->id }}')
                                                            })"
                                                            title="{{ __('finance.Clear Fee') }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Fee Categories -->
                    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                <i class="fas fa-tags text-purple-600"></i> {{ __('finance.Fee Categories') }}
                            </h4>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="th-cell">{{ __('finance.Category Name') }}</th>
                                        <th class="th-cell">{{ __('finance.Category Code') }}</th>
                                        <th class="th-cell">{{ __('finance.Description') }}</th>
                                        <th class="th-cell">{{ __('finance.Mandatory Fee') }}</th>
                                        <th class="th-cell">{{ __('finance.Status') }}</th>
                                        <th class="th-cell">{{ __('finance.Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($feeTypes as $feeType)
                                        <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                            <td class="td-cell font-semibold">{{ $feeType->name }}</td>
                                            <td class="td-cell">
                                                @if($feeType->code)
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-md bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300">
                                                        {{ $feeType->code }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="td-cell text-sm text-gray-600 dark:text-gray-400">
                                                {{ $feeType->description ? Str::limit($feeType->description, 50) : '-' }}
                                            </td>
                                            <td class="td-cell">
                                                @if($feeType->is_mandatory)
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-md bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300">
                                                        <i class="fas fa-exclamation-circle mr-1"></i>{{ __('finance.Mandatory Fee') }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-400">{{ __('finance.Optional') }}</span>
                                                @endif
                                            </td>
                                            <td class="td-cell">
                                                <span class="collection-badge">{{ $feeType->status ? __('finance.Active') : __('finance.Inactive') }}</span>
                                            </td>
                                            <td class="td-cell">
                                                <div class="flex items-center gap-1">
                                                    <button type="button" class="action-btn edit" @click="openEditCategoryModal(@js($feeType))" title="{{ __('finance.Edit') }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form method="POST" action="{{ route('student-fees.categories.destroy', $feeType) }}" class="inline" onsubmit="return confirm('{{ __('finance.Delete this category?') }}')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="action-btn delete" title="{{ __('finance.Delete') }}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="td-cell text-center text-gray-500 dark:text-gray-400">
                                                {{ __('finance.No fee categories yet. Click "Add Category" to create one.') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Additional Fee Structures (non-monthly) -->
                    @if($structures->count() > 0)
                        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                                <h4 class="text-md font-semibold text-gray-900 dark:text-white">{{ __('finance.Other Fee Structures') }}</h4>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead class="bg-gray-50 dark:bg-gray-800">
                                        <tr>
                                            <th class="th-cell">{{ __('finance.Grade') }}</th>
                                            <th class="th-cell">{{ __('finance.Fee Type') }}</th>
                                            <th class="th-cell">{{ __('finance.Amount') }}</th>
                                            <th class="th-cell">{{ __('finance.Frequency') }}</th>
                                            <th class="th-cell">{{ __('finance.Status') }}</th>
                                            <th class="th-cell">{{ __('finance.Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($structures as $structure)
                                            <tr class="bg-white dark:bg-gray-900">
                                                <td class="td-cell">@gradeName($structure->grade->level ?? 0)</td>
                                                <td class="td-cell">{{ $structure->feeType->name ?? '-' }}</td>
                                                <td class="td-cell font-semibold">{{ number_format($structure->amount, 0) }} MMK</td>
                                                <td class="td-cell">{{ ucfirst($structure->frequency) }}</td>
                                                <td class="td-cell">
                                                    <span class="collection-badge">{{ $structure->status ? __('finance.Active') : __('finance.Inactive') }}</span>
                                                </td>
                                                <td class="td-cell">
                                                    <div class="flex items-center gap-1">
                                                        <button type="button" class="action-btn edit" @click="openEditStructureModal(@js($structure))">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="action-btn delete" @click="confirmDeleteStructure(@js($structure))">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- Payment Promotions -->
                    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                            <div>
                                <h4 class="text-md font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <i class="fas fa-percentage text-green-600"></i> {{ __('finance.Payment Promotions') }}
                                </h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('finance.Discount percentages for multi-month payments') }}</p>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="th-cell">{{ __('finance.Payment Duration') }}</th>
                                        <th class="th-cell">{{ __('finance.Discount Percentage') }}</th>
                                        <th class="th-cell">{{ __('finance.Status') }}</th>
                                        <th class="th-cell">{{ __('finance.Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($paymentPromotions as $promotion)
                                        <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                            <td class="td-cell font-semibold">
                                                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">
                                                    <i class="fas fa-calendar-alt"></i>
                                                    {{ $promotion->months }} {{ $promotion->months == 1 ? __('finance.Month') : __('finance.Months') }}
                                                </span>
                                            </td>
                                            <td class="td-cell">
                                                <span class="text-lg font-bold {{ $promotion->discount_percent > 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-400' }}">
                                                    {{ number_format($promotion->discount_percent, 1) }}%
                                                </span>
                                                @if($promotion->discount_percent > 0)
                                                    <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">{{ __('finance.discount') }}</span>
                                                @endif
                                            </td>
                                            <td class="td-cell">
                                                @if($promotion->is_active)
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-md bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">
                                                        <i class="fas fa-check-circle mr-1"></i>{{ __('finance.Active') }}
                                                    </span>
                                                @else
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                                        {{ __('finance.Inactive') }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="td-cell">
                                                <button type="button" class="action-btn edit" @click="openEditPromotionModal(@js($promotion))" title="{{ __('finance.Edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment History Tab -->
            <div x-show="activeTab === 'history'" x-cloak>
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center text-xl shadow-lg">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('finance.Selected Month') }}</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $currentMonth }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('finance.Current selection') }}</p>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white flex items-center justify-center text-xl shadow-lg">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('finance.Total Invoices') }}</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $totalInvoices }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('finance.Generated') }}</p>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 text-white flex items-center justify-center text-xl shadow-lg">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('finance.Payments Collected') }}</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $payments->count() }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('finance.Completed') }}</p>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-violet-600 text-white flex items-center justify-center text-xl shadow-lg">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('finance.Total Amount') }}</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format($payments->sum('amount'), 0) }} MMK</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('finance.Collected') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Payment History Section -->
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('finance.Payment History Details') }}</h3>
                    </div>

                    <!-- Filters -->
                    <div class="p-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                        <form method="GET" action="{{ route('student-fees.index') }}" class="flex flex-wrap items-center gap-3">
                            <input type="hidden" name="tab" value="history">
                            <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">{{ __('finance.Filter by Month:') }}</span>
                            <select name="month" class="form-select-sm" onchange="this.form.submit()">
                                @foreach($monthOptions as $option)
                                    <option value="{{ $option['value'] }}" {{ $selectedMonth == $option['value'] ? 'selected' : '' }}>
                                        {{ $option['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            <a href="{{ route('student-fees.index') }}?tab=history" class="btn-filter-reset">{{ __('finance.Reset') }}</a>
                        </form>
                    </div>

                    <!-- Payment History Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="th-cell">{{ __('finance.Payment #') }}</th>
                                    <th class="th-cell">{{ __('finance.Student ID') }}</th>
                                    <th class="th-cell">{{ __('finance.Student Name') }}</th>
                                    <th class="th-cell">{{ __('finance.Grade/Class') }}</th>
                                    <th class="th-cell">{{ __('finance.Amount') }}</th>
                                    <th class="th-cell">{{ __('finance.Payment Method') }}</th>
                                    <th class="th-cell">{{ __('finance.Date') }}</th>
                                    <th class="th-cell">{{ __('finance.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($payments as $payment)
                                    <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                        <td class="td-cell font-semibold text-amber-600 dark:text-amber-400">{{ $payment->payment_number }}</td>
                                        <td class="td-cell text-gray-600 dark:text-gray-400">{{ $payment->student->student_identifier ?? '-' }}</td>
                                        <td class="td-cell">
                                            <div class="font-medium text-gray-900 dark:text-white">{{ $payment->student->user->name ?? '-' }}</div>
                                        </td>
                                        <td class="td-cell">@gradeName($payment->student->grade->level ?? 0) / @className($payment->student->classModel->name ?? '-', $payment->student->grade?->level)</td>
                                        <td class="td-cell font-semibold text-green-600 dark:text-green-400">{{ number_format($payment->amount, 0) }} MMK</td>
                                        <td class="td-cell">
                                            <span class="payment-method-badge" data-method="{{ $payment->payment_method }}">
                                                {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                            </span>
                                        </td>
                                        <td class="td-cell">{{ $payment->payment_date?->format('M j, Y') }}</td>
                                        <td class="td-cell">
                                            <button type="button" class="action-btn view" title="{{ __('finance.View Receipt') }}" @click="openReceiptModal(@js($payment))">
                                                <i class="fas fa-receipt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="td-empty">
                                            <div class="flex flex-col items-center py-8">
                                                <i class="fas fa-history text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
                                                <p class="text-gray-500 dark:text-gray-400">{{ __('finance.No payments recorded yet.') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($payments->hasPages())
                        <div class="p-4 border-t border-gray-200 dark:border-gray-700">{{ $payments->withQueryString()->links() }}</div>
                    @endif
                </div>
            </div>

            <!-- Payment Methods Tab -->
            <div x-show="activeTab === 'payment-methods'" x-cloak>
                <!-- Header with Add Button -->
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm mb-6">
                    <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <i class="fas fa-credit-card text-blue-600"></i>
                            {{ __('finance.Payment Methods') }}
                        </h3>
                        <button type="button" @click="openPaymentMethodModal()" class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700 shadow-sm">
                            <i class="fas fa-plus mr-2"></i>{{ __('finance.Add Payment Method') }}
                        </button>
                    </div>

                    <!-- Payment Methods Grid -->
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @forelse($paymentMethods as $method)
                                <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl p-4 hover:shadow-lg transition-all">
                                    <!-- Header -->
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex items-center gap-3">
                                            @if($method->logo_url && file_exists(public_path($method->logo_url)))
                                                <img src="{{ asset($method->logo_url) }}" alt="{{ $method->name }}" class="w-12 h-12 rounded-lg object-cover">
                                            @else
                                                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg">
                                                    {{ substr($method->name, 0, 1) }}
                                                </div>
                                            @endif
                                            <div>
                                                <h4 class="font-semibold text-gray-900 dark:text-white">{{ $method->name }}</h4>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $method->name_mm }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @if($method->is_active)
                                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                                    {{ __('finance.Active') }}
                                                </span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400">
                                                    {{ __('finance.Inactive') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Type Badge -->
                                    <div class="mb-3">
                                        @if($method->type === 'bank')
                                            <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                                <i class="fas fa-university"></i> {{ __('finance.Bank Transfer') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400">
                                                <i class="fas fa-mobile-alt"></i> {{ __('finance.Mobile Wallet') }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Account Details -->
                                    <div class="space-y-2 mb-4">
                                        <div class="flex items-center justify-between p-2 bg-white dark:bg-gray-900 rounded-lg">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('finance.Account Number') }}</span>
                                            <span class="text-sm font-mono font-semibold text-gray-900 dark:text-white">{{ $method->account_number }}</span>
                                        </div>
                                        <div class="flex items-center justify-between p-2 bg-white dark:bg-gray-900 rounded-lg">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('finance.Account Name') }}</span>
                                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $method->account_name }}</span>
                                        </div>
                                    </div>

                                    <!-- Instructions -->
                                    <div class="mb-4">
                                        <p class="text-xs text-gray-600 dark:text-gray-300 line-clamp-2">{{ $method->instructions }}</p>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center gap-2 pt-3 border-t border-gray-200 dark:border-gray-600">
                                        <button type="button" @click="editPaymentMethod({{ json_encode($method) }})" class="flex-1 px-3 py-2 text-xs font-medium rounded-lg text-blue-700 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50">
                                            <i class="fas fa-edit mr-1"></i>{{ __('finance.Edit') }}
                                        </button>
                                        <form method="POST" action="{{ route('payment-methods.destroy', $method) }}" class="flex-1" onsubmit="return confirm('{{ __('finance.Are you sure you want to delete this payment method?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full px-3 py-2 text-xs font-medium rounded-lg text-red-700 bg-red-50 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50">
                                                <i class="fas fa-trash mr-1"></i>{{ __('finance.Delete') }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-full text-center py-12">
                                    <i class="fas fa-credit-card text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
                                    <p class="text-gray-500 dark:text-gray-400">{{ __('finance.No payment methods found') }}</p>
                                    <button type="button" @click="openPaymentMethodModal()" class="mt-4 px-4 py-2 text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700">
                                        <i class="fas fa-plus mr-2"></i>{{ __('finance.Add Your First Payment Method') }}
                                    </button>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Process Payment Modal -->
        <div x-show="showPaymentModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="showPaymentModal = false">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-xl w-full max-w-lg shadow-2xl" @click.stop>
                    <form @submit.prevent="submitPayment" x-ref="paymentForm">
                        <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                <i class="fas fa-money-check-alt text-green-600"></i> {{ __('finance.Process Payment') }}
                            </h4>
                            <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700" @click="showPaymentModal = false">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="p-5 space-y-4">
                            <div class="p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                                <p class="text-sm font-semibold text-amber-800 dark:text-amber-200" x-text="paymentInfo"></p>
                            </div>
                            <input type="hidden" name="student_id" x-model="paymentStudentId">
                            <input type="hidden" name="items[0][invoice_id]" x-model="paymentInvoiceId">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Payment Type') }} <span class="text-red-500">*</span></label>
                                    <select name="payment_method" class="form-select-full" x-model="paymentForm.payment_method" required>
                                        <option value="">{{ __('finance.Select Payment Type') }}</option>
                                        <option value="cash">{{ __('finance.Cash') }}</option>
                                        <option value="card">{{ __('finance.Card Payment') }}</option>
                                        <option value="bank_transfer">{{ __('finance.Bank Transfer') }}</option>
                                        <option value="cheque">{{ __('finance.Check') }}</option>
                                        <option value="online">{{ __('finance.Online Payment') }}</option>
                                        <option value="mobile_payment">{{ __('finance.Mobile Payment') }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Payment Reference') }}</label>
                                    <input type="text" name="reference_number" class="form-input-full" x-model="paymentForm.reference_number" placeholder="{{ __('finance.Transaction ID, Check #, etc.') }}">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Amount (MMK)') }} <span class="text-red-500">*</span></label>
                                    <input type="number" step="1" min="1" name="amount" class="form-input-full" x-model="paymentAmount" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Payment Date') }} <span class="text-red-500">*</span></label>
                                    <input type="date" name="payment_date" class="form-input-full" x-model="paymentForm.payment_date" required>
                                </div>
                            </div>
                            <input type="hidden" name="items[0][amount]" x-model="paymentAmount">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Receptionist ID') }}</label>
                                    <input type="text" name="receptionist_id" class="form-input-full" x-model="paymentForm.receptionist_id" placeholder="{{ __('finance.e.g., R001') }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Receptionist Name') }}</label>
                                    <input type="text" name="receptionist_name" class="form-input-full" x-model="paymentForm.receptionist_name" placeholder="{{ __('finance.Enter name') }}">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Payment Notes') }}</label>
                                <textarea name="notes" rows="2" class="form-input-full" x-model="paymentForm.notes" placeholder="{{ __('finance.Additional payment details...') }}"></textarea>
                            </div>
                        </div>
                        <div class="flex items-center justify-between gap-3 p-5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
                            <button type="button" class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" @click="showPaymentModal = false">
                                <i class="fas fa-times mr-2"></i>{{ __('finance.Cancel') }}
                            </button>
                            <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-green-600 hover:bg-green-700" :disabled="isSubmitting">
                                <span x-show="!isSubmitting"><i class="fas fa-check mr-2"></i>{{ __('finance.Confirm Payment') }}</span>
                                <span x-show="isSubmitting"><i class="fas fa-spinner fa-spin mr-2"></i>{{ __('finance.Processing...') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Add/Edit Fee Structure Modal -->
        <div x-show="showStructureModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="showStructureModal = false">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-xl w-full max-w-lg shadow-2xl" @click.stop>
                    <form method="POST" :action="structureFormAction" x-ref="structureForm">
                        @csrf
                        <template x-if="structureFormMethod === 'PUT'"><input type="hidden" name="_method" value="PUT"></template>
                        <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                <i class="fas fa-dollar-sign text-amber-600"></i>
                                <span x-text="structureFormMethod === 'PUT' ? '{{ __('finance.Edit School Fee') }}' : '{{ __('finance.Add School Fee') }}'"></span>
                            </h4>
                            <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700" @click="showStructureModal = false">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="p-5 space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Grade') }} <span class="text-red-500">*</span></label>
                                    <select name="grade_id" class="form-select-full" x-model="structureForm.grade_id" required>
                                        <option value="">{{ __('finance.Select Grade') }}</option>
                                        @foreach($grades as $grade)
                                            <option value="{{ $grade->id }}">@gradeName($grade->level)</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Batch') }} <span class="text-red-500">*</span></label>
                                    <select name="batch_id" class="form-select-full" x-model="structureForm.batch_id" required>
                                        <option value="">{{ __('finance.Select Batch') }}</option>
                                        @foreach($batches as $batch)
                                            <option value="{{ $batch->id }}">{{ $batch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Fee Type') }} <span class="text-red-500">*</span></label>
                                    <select name="fee_type_id" class="form-select-full" x-model="structureForm.fee_type_id" required>
                                        <option value="">{{ __('finance.Select Fee Type') }}</option>
                                        @foreach($feeTypes as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Monthly Fee (MMK)') }} <span class="text-red-500">*</span></label>
                                    <input type="number" step="1" min="0" name="amount" class="form-input-full" x-model="structureForm.amount" placeholder="15000" required>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Frequency') }} <span class="text-red-500">*</span></label>
                                    <select name="frequency" class="form-select-full" x-model="structureForm.frequency" required>
                                        <option value="monthly">{{ __('finance.Monthly') }}</option>
                                        <option value="quarterly">{{ __('finance.Quarterly') }}</option>
                                        <option value="half-yearly">{{ __('finance.Half-Yearly') }}</option>
                                        <option value="yearly">{{ __('finance.Yearly') }}</option>
                                        <option value="one-time">{{ __('finance.One-Time') }}</option>
                                    </select>
                                </div>
                                <div class="flex items-center pt-6">
                                    <input type="hidden" name="status" value="0">
                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="status" value="1" class="rounded border-gray-300 text-amber-600 focus:ring-amber-500" x-model="structureForm.status">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('finance.Active') }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between gap-3 p-5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
                            <button type="button" class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" @click="showStructureModal = false">
                                <i class="fas fa-times mr-2"></i>{{ __('finance.Cancel') }}
                            </button>
                            <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-amber-600 hover:bg-amber-700">
                                <i class="fas fa-save mr-2"></i>{{ __('finance.Save Fee') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Grade Fee Modal -->
        <div x-show="showGradeFeeModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="showGradeFeeModal = false">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-xl w-full max-w-md shadow-2xl" @click.stop>
                    <form method="POST" :action="gradeFeeFormAction" x-ref="gradeFeeForm">
                        @csrf
                        @method('PUT')
                        <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                <i class="fas fa-edit text-amber-600"></i> {{ __('finance.Edit School Fee') }}
                            </h4>
                            <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700" @click="showGradeFeeModal = false">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="p-5 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Grade') }}</label>
                                <input type="text" class="form-input-full bg-gray-100 dark:bg-gray-700" x-model="gradeFeeForm.gradeName" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Monthly Fee (MMK)') }} <span class="text-red-500">*</span></label>
                                <input type="number" step="1" min="0" name="price_per_month" class="form-input-full" x-model="gradeFeeForm.price_per_month" placeholder="15000" required>
                            </div>
                        </div>
                        <div class="flex items-center justify-between gap-3 p-5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
                            <button type="button" class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" @click="showGradeFeeModal = false">
                                <i class="fas fa-times mr-2"></i>{{ __('finance.Cancel') }}
                            </button>
                            <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-amber-600 hover:bg-amber-700">
                                <i class="fas fa-save mr-2"></i>{{ __('finance.Save Fee') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Promotion Modal -->
        <div x-show="showPromotionModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="showPromotionModal = false">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-xl w-full max-w-md shadow-2xl" @click.stop>
                    <form method="POST" :action="promotionFormAction" x-ref="promotionForm">
                        @csrf
                        @method('PUT')
                        <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                <i class="fas fa-percentage text-green-600"></i> {{ __('finance.Edit Promotion') }}
                            </h4>
                            <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700" @click="showPromotionModal = false">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="p-5 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Payment Duration') }}</label>
                                <input type="text" class="form-input-full bg-gray-100 dark:bg-gray-700" x-model="promotionForm.durationLabel" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Discount Percentage') }} <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input type="number" step="0.1" min="0" max="100" name="discount_percent" class="form-input-full pr-12" x-model="promotionForm.discount_percent" placeholder="0" required>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <span class="text-gray-500 dark:text-gray-400 font-semibold">%</span>
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('finance.Enter discount percentage (0-100)') }}</p>
                            </div>
                            <div class="flex items-center">
                                <input type="hidden" name="is_active" value="0">
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-green-600 focus:ring-green-500" x-model="promotionForm.is_active">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('finance.Active') }}</span>
                                </label>
                            </div>
                        </div>
                        <div class="flex items-center justify-between gap-3 p-5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
                            <button type="button" class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" @click="showPromotionModal = false">
                                <i class="fas fa-times mr-2"></i>{{ __('finance.Cancel') }}
                            </button>
                            <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-green-600 hover:bg-green-700">
                                <i class="fas fa-save mr-2"></i>{{ __('finance.Save Promotion') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Payment Receipt Modal -->
        <div x-show="showReceiptModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="showReceiptModal = false">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-xl w-full max-w-md shadow-2xl" @click.stop>
                    <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <i class="fas fa-receipt text-green-600"></i> {{ __('finance.Payment Receipt') }}
                        </h4>
                        <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700" @click="showReceiptModal = false">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="p-5 space-y-3">
                        <div class="text-center mb-4">
                            <div class="w-16 h-16 mx-auto rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center mb-3">
                                <i class="fas fa-check-circle text-3xl text-green-600 dark:text-green-400"></i>
                            </div>
                            <p class="text-lg font-semibold text-green-600 dark:text-green-400">{{ __('finance.Payment Successful') }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4 space-y-3">
                            <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('finance.Receipt Number') }}</span>
                                <span class="font-semibold text-gray-900 dark:text-white" x-text="receiptData.payment_number"></span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('finance.Student Name') }}</span>
                                <span class="font-semibold text-gray-900 dark:text-white" x-text="receiptData.student_name"></span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('finance.Student ID') }}</span>
                                <span class="font-semibold text-gray-900 dark:text-white" x-text="receiptData.student_id"></span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('finance.Amount') }}</span>
                                <span class="font-bold text-lg text-green-600 dark:text-green-400" x-text="receiptData.amount + ' MMK'"></span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('finance.Payment Method') }}</span>
                                <span class="font-semibold text-gray-900 dark:text-white" x-text="receiptData.payment_method"></span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('finance.Payment Date') }}</span>
                                <span class="font-semibold text-gray-900 dark:text-white" x-text="receiptData.payment_date"></span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700" x-show="receiptData.receptionist_id">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('finance.Receptionist ID') }}</span>
                                <span class="font-semibold text-gray-900 dark:text-white" x-text="receiptData.receptionist_id"></span>
                            </div>
                            <div class="flex justify-between items-center py-2" x-show="receiptData.receptionist_name">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('finance.Receptionist Name') }}</span>
                                <span class="font-semibold text-gray-900 dark:text-white" x-text="receiptData.receptionist_name"></span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between gap-3 p-5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
                        <button type="button" class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" @click="closeReceiptAndReload()">
                            <i class="fas fa-times mr-2"></i>{{ __('finance.Close') }}
                        </button>
                        <button type="button" class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-green-600 hover:bg-green-700" @click="printReceipt()">
                            <i class="fas fa-print mr-2"></i>{{ __('finance.Print Receipt') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Category Modal -->
        <div x-show="showCategoryModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="showCategoryModal = false">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-xl w-full max-w-lg shadow-2xl" @click.stop>
                    <form method="POST" :action="categoryFormAction" x-ref="categoryForm">
                        @csrf
                        <template x-if="categoryFormMethod === 'PUT'"><input type="hidden" name="_method" value="PUT"></template>
                        <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                <i class="fas fa-tags text-purple-600"></i>
                                <span x-text="categoryFormMethod === 'PUT' ? '{{ __('finance.Edit Fee Category') }}' : '{{ __('finance.Add Fee Category') }}'"></span>
                            </h4>
                            <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700" @click="showCategoryModal = false">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="p-5 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Category Name') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="name" class="form-input-full" x-model="categoryForm.name" placeholder="{{ __('finance.e.g., Library Fee, Sport Fee, Lab Fee') }}" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Category Code') }}</label>
                                <input type="text" name="code" class="form-input-full" x-model="categoryForm.code" placeholder="{{ __('finance.e.g., LIB, SPORT, LAB') }}">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('finance.Short code for internal reference (optional)') }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Description') }}</label>
                                <textarea name="description" rows="3" class="form-input-full" x-model="categoryForm.description" placeholder="{{ __('finance.Brief description of this fee category...') }}"></textarea>
                            </div>
                            <div class="flex items-center gap-4">
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="hidden" name="is_mandatory" value="0">
                                    <input type="checkbox" name="is_mandatory" value="1" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500" x-model="categoryForm.is_mandatory">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('finance.Mandatory Fee') }}</span>
                                </label>
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="hidden" name="status" value="0">
                                    <input type="checkbox" name="status" value="1" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500" x-model="categoryForm.status">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('finance.Active') }}</span>
                                </label>
                            </div>
                        </div>
                        <div class="flex items-center justify-between gap-3 p-5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
                            <button type="button" class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" @click="showCategoryModal = false">
                                <i class="fas fa-times mr-2"></i>{{ __('finance.Cancel') }}
                            </button>
                            <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700">
                                <i class="fas fa-save mr-2"></i>{{ __('finance.Save Category') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Payment Method Modal -->
        <div x-show="showPaymentMethodModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="showPaymentMethodModal = false">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-xl w-full max-w-2xl shadow-2xl" @click.stop>
                    <form method="POST" :action="paymentMethodFormAction">
                        @csrf
                        <template x-if="paymentMethodFormMethod === 'PUT'"><input type="hidden" name="_method" value="PUT"></template>
                        
                        <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                <i class="fas fa-credit-card text-blue-600"></i>
                                <span x-text="paymentMethodFormMethod === 'PUT' ? '{{ __('finance.Edit Payment Method') }}' : '{{ __('finance.Add Payment Method') }}'"></span>
                            </h4>
                            <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700" @click="showPaymentMethodModal = false">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        
                        <div class="p-5 space-y-4 max-h-[70vh] overflow-y-auto">
                            <!-- Basic Information -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Name (English)') }} <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" x-model="paymentMethodForm.name" class="form-input-full" required placeholder="e.g., KBZ Bank">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Name (Myanmar)') }}</label>
                                    <input type="text" name="name_mm" x-model="paymentMethodForm.name_mm" class="form-input-full" placeholder="e.g., KBZ ဘဏ်">
                                </div>
                            </div>

                            <!-- Type -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Type') }} <span class="text-red-500">*</span></label>
                                <select name="type" x-model="paymentMethodForm.type" class="form-select-full" required>
                                    <option value="bank">{{ __('finance.Bank Transfer') }}</option>
                                    <option value="mobile_wallet">{{ __('finance.Mobile Wallet') }}</option>
                                </select>
                            </div>

                            <!-- Account Information -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Account Number') }} <span class="text-red-500">*</span></label>
                                    <input type="text" name="account_number" x-model="paymentMethodForm.account_number" class="form-input-full" required placeholder="e.g., 01234567890123456">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Account Name (English)') }} <span class="text-red-500">*</span></label>
                                    <input type="text" name="account_name" x-model="paymentMethodForm.account_name" class="form-input-full" required placeholder="e.g., SmartCampus School">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Account Name (Myanmar)') }}</label>
                                <input type="text" name="account_name_mm" x-model="paymentMethodForm.account_name_mm" class="form-input-full" placeholder="e.g., SmartCampus ကျောင်း">
                            </div>

                            <!-- Instructions -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Instructions (English)') }}</label>
                                <textarea name="instructions" x-model="paymentMethodForm.instructions" rows="2" class="form-input-full" placeholder="e.g., Transfer to this account and upload receipt"></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Instructions (Myanmar)') }}</label>
                                <textarea name="instructions_mm" x-model="paymentMethodForm.instructions_mm" rows="2" class="form-input-full" placeholder="e.g., ဒီ account ကို လွှဲပြီး ပြေစာ upload လုပ်ပါ"></textarea>
                            </div>

                            <!-- Logo URL and Sort Order -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Logo URL') }}</label>
                                    <input type="text" name="logo_url" x-model="paymentMethodForm.logo_url" class="form-input-full" placeholder="/images/payment-methods/kbz.png">
                                    <p class="text-xs text-gray-500 mt-1">{{ __('finance.Relative path to logo image') }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Sort Order') }}</label>
                                    <input type="number" name="sort_order" x-model="paymentMethodForm.sort_order" class="form-input-full" min="0" placeholder="0">
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="flex items-center gap-2">
                                <input type="checkbox" name="is_active" id="is_active" x-model="paymentMethodForm.is_active" value="1" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('finance.Active') }}</label>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between gap-3 p-5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
                            <button type="button" class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" @click="showPaymentMethodModal = false">
                                <i class="fas fa-times mr-2"></i>{{ __('finance.Cancel') }}
                            </button>
                            <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700">
                                <i class="fas fa-save mr-2"></i>{{ __('finance.Save Payment Method') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div x-show="showDeleteConfirmModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeDeleteConfirm()">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-md w-full" @click.stop>
                    <!-- Header -->
                    <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-red-100 dark:bg-red-900/30">
                                <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="deleteConfirmData.title"></h3>
                        </div>
                        <button type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" @click="closeDeleteConfirm()">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-6 space-y-4">
                        <p class="text-gray-700 dark:text-gray-300" x-text="deleteConfirmData.message"></p>
                        <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-700">
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('finance.Item to delete') }}:</p>
                            <p class="font-semibold text-gray-900 dark:text-white mt-1" x-text="deleteConfirmData.itemName"></p>
                        </div>
                        <div class="flex items-start gap-2 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                            <i class="fas fa-exclamation-circle text-red-600 dark:text-red-400 mt-0.5"></i>
                            <p class="text-sm text-red-700 dark:text-red-300">{{ __('finance.This action cannot be undone.') }}</p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-end gap-3 p-5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
                        <button type="button" class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" @click="closeDeleteConfirm()">
                            <i class="fas fa-times mr-2"></i>{{ __('finance.Cancel') }}
                        </button>
                        <form :action="deleteConfirmData.formAction" method="POST" id="deleteConfirmForm" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-red-600 hover:bg-red-700">
                                <i class="fas fa-trash mr-2"></i>{{ __('finance.Delete') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approve Payment Proof Confirmation Modal -->
        <div x-show="showApproveConfirmModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeApproveConfirm()">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-md w-full" @click.stop>
                    <!-- Header -->
                    <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30">
                                <i class="fas fa-check-circle text-green-600 dark:text-green-400"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('finance.Approve Payment Proof') }}</h3>
                        </div>
                        <button type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" @click="closeApproveConfirm()">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-6 space-y-4">
                        <p class="text-gray-700 dark:text-gray-300">{{ __('finance.Are you sure you want to approve this payment proof?') }}</p>
                        <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-700 space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('finance.Student') }}:</span>
                                <span class="font-semibold text-gray-900 dark:text-white" x-text="approveConfirmData.studentName"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('finance.Amount') }}:</span>
                                <span class="font-semibold text-gray-900 dark:text-white" x-text="approveConfirmData.amount"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('finance.Payment Date') }}:</span>
                                <span class="font-semibold text-gray-900 dark:text-white" x-text="approveConfirmData.paymentDate"></span>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                            <i class="fas fa-info-circle text-green-600 dark:text-green-400 mt-0.5"></i>
                            <div class="text-sm text-green-700 dark:text-green-300">
                                <p class="font-semibold mb-1">{{ __('finance.This action will:') }}</p>
                                <ul class="list-disc list-inside space-y-1">
                                    <li>{{ __('finance.Mark the invoices as paid') }}</li>
                                    <li>{{ __('finance.Create a payment record') }}</li>
                                    <li>{{ __('finance.Notify the guardian via app') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-end gap-3 p-5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
                        <button type="button" class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" @click="closeApproveConfirm()">
                            <i class="fas fa-times mr-2"></i>{{ __('finance.Cancel') }}
                        </button>
                        <form :action="approveConfirmData.formAction" method="POST" id="approveConfirmForm" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-green-600 hover:bg-green-700">
                                <i class="fas fa-check mr-2"></i>{{ __('finance.Approve') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
        
        /* Tabs - Underline Style */
        .fee-tab {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            font-size: 14px;
            font-weight: 600;
            color: #6b7280;
            border-bottom: 3px solid transparent;
            transition: all 0.2s ease;
            background: none;
            border-top: none;
            border-left: none;
            border-right: none;
        }
        .fee-tab:hover { color: #f59e0b; background: #fef3c7; border-radius: 8px 8px 0 0; }
        .fee-tab.active { color: #f59e0b; border-bottom-color: #f59e0b; }
        .dark .fee-tab { color: #9ca3af; }
        .dark .fee-tab:hover { color: #fbbf24; background: rgba(245, 158, 11, 0.1); }
        .dark .fee-tab.active { color: #fbbf24; border-bottom-color: #fbbf24; }
        
        /* Form Controls */
        .form-select-sm, .form-input-sm {
            padding: 8px 12px;
            font-size: 14px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            background: white;
            color: #111827;
            min-width: 140px;
        }
        .dark .form-select-sm, .dark .form-input-sm {
            background: #1f2937;
            border-color: #374151;
            color: #f3f4f6;
        }
        .form-select-full, .form-input-full {
            width: 100%;
            padding: 10px 14px;
            font-size: 14px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            background: white;
            color: #111827;
        }
        .dark .form-select-full, .dark .form-input-full {
            background: #1f2937;
            border-color: #374151;
            color: #f3f4f6;
        }
        .form-select-full:focus, .form-input-full:focus,
        .form-select-sm:focus, .form-input-sm:focus {
            outline: none;
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
        }
        
        /* Filter Buttons */
        .btn-filter {
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            background: #111827;
            color: white;
        }
        .dark .btn-filter { background: #f3f4f6; color: #111827; }
        .btn-filter-reset {
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            background: #f3f4f6;
            color: #374151;
        }
        .dark .btn-filter-reset { background: #374151; color: #e5e7eb; }
        
        /* Table Styles */
        .th-cell {
            padding: 12px 16px;
            text-align: left;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
            white-space: nowrap;
        }
        .dark .th-cell { color: #9ca3af; }
        
        /* Grouped Table Header Styles */
        .fee-grouped-table { border-collapse: collapse; }
        .th-group-header {
            padding: 10px 16px;
            text-align: center;
            font-size: 13px;
            font-weight: 700;
            color: #374151;
            background: #f3f4f6;
            border-bottom: 2px solid #e5e7eb;
            border-left: 1px solid #e5e7eb;
        }
        .th-group-header:first-of-type { border-left: none; }
        .dark .th-group-header { color: #e5e7eb; background: #1f2937; border-bottom-color: #374151; border-left-color: #374151; }
        .th-group-header.group-toggle:hover { background: #e5e7eb; }
        .dark .th-group-header.group-toggle:hover { background: #374151; }
        .th-cell-group {
            padding: 10px 16px;
            text-align: center;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
            background: #f3f4f6;
            vertical-align: middle;
            border-left: 1px solid #e5e7eb;
        }
        .th-cell-group:first-child { border-left: none; }
        .dark .th-cell-group { color: #9ca3af; background: #1f2937; border-left-color: #374151; }
        .th-cell-sub {
            padding: 10px 14px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #6b7280;
            white-space: nowrap;
            border-left: 1px solid #e5e7eb;
        }
        .th-cell-sub:first-child { border-left: none; }
        .dark .th-cell-sub { color: #9ca3af; border-left-color: #374151; }
        .group-header-row th { border-top: 1px solid #e5e7eb; }
        .dark .group-header-row th { border-top-color: #374151; }
        .column-header-row th { border-bottom: 1px solid #e5e7eb; }
        .dark .column-header-row th { border-bottom-color: #374151; }
        .rotate-180 { transform: rotate(180deg); }
        
        .td-cell {
            padding: 12px 16px;
            font-size: 14px;
            color: #111827;
            vertical-align: middle;
        }
        .dark .td-cell { color: #e5e7eb; }
        .td-empty {
            padding: 24px 16px;
            text-align: center;
            color: #6b7280;
        }
        .dark .td-empty { color: #9ca3af; }
        
        /* Status Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .status-badge.paid { background: #dcfce7; color: #166534; }
        .dark .status-badge.paid { background: rgba(22, 101, 52, 0.35); color: #bbf7d0; }
        .status-badge.partial, .status-badge.draft, .status-badge.sent { background: #fef3c7; color: #92400e; }
        .dark .status-badge.partial, .dark .status-badge.draft, .dark .status-badge.sent { background: rgba(217, 119, 6, 0.3); color: #fde68a; }
        .status-badge.overdue { background: #fee2e2; color: #991b1b; }
        .dark .status-badge.overdue { background: rgba(153, 27, 27, 0.35); color: #fecdd3; }
        
        .collection-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            background: #dcfce7;
            color: #166534;
        }
        .dark .collection-badge { background: rgba(22, 101, 52, 0.35); color: #bbf7d0; }
        
        /* Payment Method Badge */
        .payment-method-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: capitalize;
            background: #e3f2fd;
            color: #1976d2;
        }
        .payment-method-badge[data-method="cash"] { background: #e8f5e9; color: #2e7d32; }
        .payment-method-badge[data-method="card"] { background: #fff3e0; color: #ef6c00; }
        .payment-method-badge[data-method="bank_transfer"] { background: #e3f2fd; color: #1976d2; }
        .payment-method-badge[data-method="cheque"] { background: #f3e5f5; color: #7b1fa2; }
        .payment-method-badge[data-method="online"] { background: #e0f2f1; color: #00695c; }
        .payment-method-badge[data-method="mobile_payment"] { background: #fff8e1; color: #f57f17; }
        
        /* Action Buttons */
        .action-btn {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .dark .action-btn { background: #374151; border-color: #4b5563; color: #9ca3af; }
        .action-btn:hover { background: #f8fafc; border-color: #cbd5e1; }
        .dark .action-btn:hover { background: #4b5563; }
        .action-btn.view:hover { background: #eff6ff; border-color: #3b82f6; color: #1d4ed8; }
        .action-btn.process:hover { background: #dcfce7; border-color: #22c55e; color: #16a34a; }
        .action-btn.process { background: #dcfce7; border-color: #86efac; color: #16a34a; }
        .action-btn.edit:hover { background: #eff6ff; border-color: #3b82f6; color: #1d4ed8; }
        .action-btn.delete:hover { background: #fef2f2; border-color: #ef4444; color: #dc2626; }
    </style>

    <script>
        function studentFeeManager() {
            return {
                activeTab: new URLSearchParams(window.location.search).get('tab') || 'invoice',
                showPaymentModal: false,
                showStructureModal: false,
                showCategoryModal: false,
                showGradeFeeModal: false,
                showPromotionModal: false,
                showReceiptModal: false,
                showPaymentMethodModal: false,
                showDeleteConfirmModal: false,
                showApproveConfirmModal: false,
                deleteConfirmData: {
                    title: '',
                    message: '',
                    formAction: '',
                    itemName: ''
                },
                approveConfirmData: {
                    proofId: '',
                    studentName: '',
                    amount: '',
                    paymentDate: '',
                    formAction: ''
                },
                receiptData: {
                    payment_number: '',
                    student_name: '',
                    student_id: '',
                    amount: '',
                    payment_method: '',
                    payment_date: '',
                    receptionist_id: '',
                    receptionist_name: ''
                },
                structureFormMethod: 'POST',
                structureFormAction: '{{ route('student-fees.structures.store') }}',
                categoryFormMethod: 'POST',
                categoryFormAction: '{{ route('student-fees.categories.store') }}',
                gradeFeeFormAction: '',
                paymentMethodFormMethod: 'POST',
                paymentMethodFormAction: '{{ route('payment-methods.store') }}',
                structureForm: {
                    grade_id: '',
                    batch_id: '',
                    fee_type_id: '',
                    amount: '',
                    frequency: 'monthly',
                    status: true
                },
                categoryForm: {
                    name: '',
                    code: '',
                    description: '',
                    is_mandatory: false,
                    status: true
                },
                gradeFeeForm: {
                    gradeName: '',
                    price_per_month: ''
                },
                promotionFormAction: '',
                promotionForm: {
                    durationLabel: '',
                    discount_percent: '',
                    is_active: true
                },
                paymentMethodForm: {
                    id: '',
                    name: '',
                    name_mm: '',
                    type: 'bank',
                    account_number: '',
                    account_name: '',
                    account_name_mm: '',
                    logo_url: '',
                    is_active: true,
                    instructions: '',
                    instructions_mm: '',
                    sort_order: 0
                },
                paymentInfo: '',
                paymentStudentId: '',
                paymentInvoiceId: '',
                paymentAmount: '',
                isSubmitting: false,
                paymentForm: {
                    payment_method: '',
                    reference_number: '',
                    payment_date: '{{ now()->format('Y-m-d') }}',
                    receptionist_id: '',
                    receptionist_name: '{{ auth()->user()->name }}',
                    notes: ''
                },
                
                openPaymentModal(data) {
                    const student = data.student;
                    const amount = data.amount;
                    const invoice = data.invoice;
                    
                    this.paymentInfo = `${student.user?.name || 'Student'} (${student.student_identifier}) - Fee: ${parseInt(amount).toLocaleString()} MMK`;
                    this.paymentStudentId = student.id;
                    this.paymentInvoiceId = invoice?.id || '';
                    this.paymentAmount = amount;
                    // Reset form
                    this.paymentForm = {
                        payment_method: '',
                        reference_number: '',
                        payment_date: '{{ now()->format('Y-m-d') }}',
                        receptionist_id: '',
                        receptionist_name: '{{ auth()->user()->name }}',
                        notes: ''
                    };
                    this.showPaymentModal = true;
                },
                
                async submitPayment() {
                    if (this.isSubmitting) return;
                    if (!this.paymentForm.payment_method) {
                        alert('{{ __('finance.Please select a payment method') }}');
                        return;
                    }
                    
                    this.isSubmitting = true;
                    
                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('student_id', this.paymentStudentId);
                    formData.append('amount', this.paymentAmount);
                    formData.append('payment_method', this.paymentForm.payment_method);
                    formData.append('payment_date', this.paymentForm.payment_date);
                    formData.append('reference_number', this.paymentForm.reference_number || '');
                    formData.append('receptionist_id', this.paymentForm.receptionist_id || '');
                    formData.append('receptionist_name', this.paymentForm.receptionist_name || '');
                    formData.append('notes', this.paymentForm.notes || '');
                    formData.append('items[0][invoice_id]', this.paymentInvoiceId || '');
                    formData.append('items[0][amount]', this.paymentAmount);
                    
                    try {
                        const response = await fetch('{{ route('student-fees.payments.store') }}', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            this.showPaymentModal = false;
                            this.receiptData = {
                                payment_number: data.payment.payment_number || '',
                                student_name: data.payment.student_name || '-',
                                student_id: data.payment.student_id || '-',
                                class_name: data.payment.class_name || '-',
                                guardian_name: data.payment.guardian_name || 'N/A',
                                amount: parseInt(data.payment.amount || 0).toLocaleString(),
                                payment_method: (data.payment.payment_method || '').replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()),
                                payment_date: data.payment.payment_date || '-',
                                receptionist_id: data.payment.receptionist_id || '',
                                receptionist_name: data.payment.receptionist_name || '',
                                ferry_fee: data.payment.ferry_fee || '0',
                                notes: data.payment.notes || ''
                            };
                            this.showReceiptModal = true;
                        } else {
                            alert(data.message || '{{ __('finance.Payment failed. Please try again.') }}');
                        }
                    } catch (error) {
                        console.error('Payment error:', error);
                        alert('{{ __('finance.Payment failed. Please try again.') }}');
                    } finally {
                        this.isSubmitting = false;
                    }
                },
                
                openStructureModal() {
                    this.structureFormMethod = 'POST';
                    this.structureFormAction = '{{ route('student-fees.structures.store') }}';
                    this.structureForm = {
                        grade_id: '',
                        batch_id: '',
                        fee_type_id: '',
                        amount: '',
                        frequency: 'monthly',
                        status: true
                    };
                    this.showStructureModal = true;
                },
                
                openStructureModalForGrade(gradeId) {
                    this.structureFormMethod = 'POST';
                    this.structureFormAction = '{{ route('student-fees.structures.store') }}';
                    this.structureForm = {
                        grade_id: gradeId,
                        batch_id: '',
                        fee_type_id: '',
                        amount: '',
                        frequency: 'monthly',
                        status: true
                    };
                    this.showStructureModal = true;
                },
                
                openEditStructureModal(structure) {
                    this.structureFormMethod = 'PUT';
                    this.structureFormAction = '{{ url('student-fees/structures') }}/' + structure.id;
                    this.structureForm = {
                        grade_id: structure.grade_id || '',
                        batch_id: structure.batch_id || '',
                        fee_type_id: structure.fee_type_id || '',
                        amount: structure.amount || '',
                        frequency: structure.frequency || 'monthly',
                        status: structure.status ? true : false
                    };
                    this.showStructureModal = true;
                },
                
                openCategoryModal() {
                    this.categoryFormMethod = 'POST';
                    this.categoryFormAction = '{{ route('student-fees.categories.store') }}';
                    this.categoryForm = {
                        name: '',
                        code: '',
                        description: '',
                        is_mandatory: false,
                        status: true
                    };
                    this.showCategoryModal = true;
                },
                
                openEditCategoryModal(category) {
                    this.categoryFormMethod = 'PUT';
                    this.categoryFormAction = '{{ url('student-fees/categories') }}/' + category.id;
                    this.categoryForm = {
                        name: category.name || '',
                        code: category.code || '',
                        description: category.description || '',
                        is_mandatory: category.is_mandatory ? true : false,
                        status: category.status ? true : false
                    };
                    this.showCategoryModal = true;
                },
                
                openEditGradeFeeModal(grade) {
                    this.gradeFeeFormAction = '{{ url('student-fees/grades') }}/' + grade.id;
                    this.gradeFeeForm = {
                        gradeName: grade.name || '',
                        price_per_month: grade.price_per_month || ''
                    };
                    this.showGradeFeeModal = true;
                },
                
                openEditPromotionModal(promotion) {
                    this.promotionFormAction = '{{ url('student-fees/promotions') }}/' + promotion.id;
                    const monthLabel = promotion.months == 1 ? '1 {{ __('finance.Month') }}' : promotion.months + ' {{ __('finance.Months') }}';
                    this.promotionForm = {
                        durationLabel: monthLabel,
                        discount_percent: promotion.discount_percent || 0,
                        is_active: promotion.is_active ? true : false
                    };
                    this.showPromotionModal = true;
                },
                
                clearGradeFee(gradeId) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ url('student-fees/grades') }}/' + gradeId;
                    
                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = '{{ csrf_token() }}';
                    form.appendChild(csrf);
                    
                    const method = document.createElement('input');
                    method.type = 'hidden';
                    method.name = '_method';
                    method.value = 'PUT';
                    form.appendChild(method);
                    
                    const price = document.createElement('input');
                    price.type = 'hidden';
                    price.name = 'price_per_month';
                    price.value = '0';
                    form.appendChild(price);
                    
                    document.body.appendChild(form);
                    form.submit();
                },
                
                openReceiptModal(payment) {
                    // Get guardian name (first guardian)
                    const guardianName = payment.student?.guardians?.[0]?.user?.name || 'N/A';
                    
                    // Get class name
                    let className = '-';
                    if (payment.student?.grade && payment.student?.classModel) {
                        const gradeLevel = payment.student.grade.level;
                        const classNameRaw = payment.student.classModel.name;
                        // Format class name using the helper
                        className = window.formatClassName ? window.formatClassName(classNameRaw, gradeLevel) : classNameRaw;
                    }
                    
                    this.receiptData = {
                        payment_number: payment.payment_number || '',
                        student_name: payment.student?.user?.name || '-',
                        student_id: payment.student?.student_identifier || '-',
                        class_name: className,
                        guardian_name: guardianName,
                        amount: parseInt(payment.amount || 0).toLocaleString(),
                        payment_method: (payment.payment_method || '').replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()),
                        payment_date: payment.payment_date ? new Date(payment.payment_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '-',
                        receptionist_id: payment.receptionist_id || '',
                        receptionist_name: payment.receptionist_name || '',
                        ferry_fee: payment.ferry_fee || '0',
                        notes: payment.notes || ''
                    };
                    this.showReceiptModal = true;
                },
                
                closeReceiptAndReload() {
                    this.showReceiptModal = false;
                    window.location.reload();
                },
                
                printReceipt() {
                    const printWindow = window.open('', '_blank');
                    
                    // Get school info from settings
                    const schoolLogo = '{{ asset("images/school-logo.jpg") }}';
                    
                    // Helper function to convert numbers to Myanmar words
                    function numberToMyanmarWords(num) {
                        const ones = ['', 'တစ်', 'နှစ်', 'သုံး', 'လေး', 'ငါး', 'ခြောက်', 'ခုနစ်', 'ရှစ်', 'ကိုး'];
                        const tens = ['', 'ဆယ်', 'နှစ်ဆယ်', 'သုံးဆယ်', 'လေးဆယ်', 'ငါးဆယ်', 'ခြောက်ဆယ်', 'ခုနစ်ဆယ်', 'ရှစ်ဆယ်', 'ကိုးဆယ်'];
                        
                        if (num === 0) return 'သုည';
                        
                        let result = '';
                        
                        // Lakhs (သိန်း)
                        if (num >= 100000) {
                            const lakhs = Math.floor(num / 100000);
                            result += ones[lakhs] + 'သိန်း';
                            num %= 100000;
                        }
                        
                        // Ten thousands (သောင်း)
                        if (num >= 10000) {
                            const tenThousands = Math.floor(num / 10000);
                            result += ones[tenThousands] + 'သောင်း';
                            num %= 10000;
                        }
                        
                        // Thousands (ထောင်)
                        if (num >= 1000) {
                            const thousands = Math.floor(num / 1000);
                            result += ones[thousands] + 'ထောင်';
                            num %= 1000;
                        }
                        
                        // Hundreds (ရာ)
                        if (num >= 100) {
                            const hundreds = Math.floor(num / 100);
                            result += ones[hundreds] + 'ရာ';
                            num %= 100;
                        }
                        
                        // Tens
                        if (num >= 10) {
                            const tensDigit = Math.floor(num / 10);
                            result += tens[tensDigit];
                            num %= 10;
                        }
                        
                        // Ones
                        if (num > 0) {
                            result += ones[num];
                        }
                        
                        return result;
                    }
                    
                    // Convert amount to Myanmar words
                    const amountNum = parseInt(this.receiptData.amount.replace(/,/g, ''));
                    const amountInWords = numberToMyanmarWords(amountNum);
                    
                    // Get month names in Myanmar
                    const monthNamesMM = ['ဇန်နဝါရီ', 'ဖေဖော်ဝါရီ', 'မတ်', 'ဧပြီ', 'မေ', 'ဇွန်', 'ဇူလိုင်', 'သြဂုတ်', 'စက်တင်ဘာ', 'အောက်တိုဘာ', 'နိုဝင်ဘာ', 'ဒီဇင်ဘာ'];
                    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    
                    // Format date as 11/Feb/2026
                    const paymentDate = this.receiptData.payment_date || '';
                    let formattedDate = '';
                    let invoiceMonthMM = '';
                    if (paymentDate) {
                        const dateObj = new Date(paymentDate);
                        const day = dateObj.getDate();
                        const monthIndex = dateObj.getMonth();
                        const month = monthNames[monthIndex];
                        const year = dateObj.getFullYear();
                        formattedDate = `${day}/${month}/${year}`;
                        invoiceMonthMM = monthNamesMM[monthIndex];
                    }
                    
                    const studentClass = this.receiptData.class_name || '-';
                    const guardianName = this.receiptData.guardian_name || 'N/A';
                    const paymentNotes = this.receiptData.notes || '';
                    
                    printWindow.document.write(`
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <title>Receipt</title>
                            <meta charset="UTF-8">
                            <style>
                                @page { 
                                    size: A5 landscape;
                                    margin: 0.3in;
                                }
                                * {
                                    margin: 0;
                                    padding: 0;
                                    box-sizing: border-box;
                                }
                                html, body { 
                                    width: 210mm;
                                    height: 148mm;
                                    margin: 0;
                                    padding: 0;
                                }
                                body { 
                                    font-family: 'Myanmar3', 'Pyidaungsu', Arial, sans-serif; 
                                    background: #90EE90;
                                    padding: 0.3in;
                                    font-size: 9.5pt;
                                    line-height: 1.4;
                                }
                                .header {
                                    display: flex;
                                    align-items: flex-start;
                                    margin-bottom: 2mm;
                                }
                                .logo {
                                    width: 20mm;
                                    flex-shrink: 0;
                                    margin-right: 3mm;
                                }
                                .logo img {
                                    width: 20mm;
                                    height: 20mm;
                                    display: block;
                                }
                                .header-text {
                                    flex: 1;
                                    text-align: center;
                                }
                                .school-name {
                                    font-size: 20pt;
                                    font-weight: bold;
                                    margin-bottom: 1mm;
                                }
                                .invoice-no {
                                    font-size: 15pt;
                                    margin-bottom: 2mm;
                                }
                                .content {
                                    font-size: 12pt;
                                    line-height: 1.5;
                                }
                                .line {
                                    margin: 1.5mm 0;
                                    text-align: justify;
                                    text-justify: inter-word;
                                }
                                .signature-section {
                                    display: flex;
                                    justify-content: space-between;
                                    margin-top: 2mm;
                                }
                                .signature-box {
                                    text-align: center;
                                    flex: 1;
                                    font-size: 12pt;
                                }
                                .signature-label {
                                    margin-bottom: 25mm;
                                }
                                .signature-line {
                                    margin: 1.5mm 0;
                                    font-size: 12pt;
                                }
                                .note-section {
                                    margin-top: 2mm;
                                    padding-top: 1mm;
                                    text-align: justify;
                                }
                                .note {
                                    text-align: center;
                                    font-size: 10pt;
                                    margin-bottom: 1mm;
                                }
                                .separator {
                                    text-align: center;
                                    margin: 1mm 0;
                                    font-size: 9pt;
                                }
                                .contact {
                                    text-align: center;
                                    font-size: 10pt;
                                }
                                @media print {
                                    html, body {
                                        width: 210mm;
                                        height: 148mm;
                                    }
                                    body { 
                                        background: #90EE90;
                                        -webkit-print-color-adjust: exact;
                                        print-color-adjust: exact;
                                    }
                                    @page {
                                        size: A5 landscape;
                                        margin: 0.3in;
                                    }
                                }
                            </style>
                        </head>
                        <body>
                            <div class="header">
                                <div class="logo">
                                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ccircle cx='50' cy='50' r='45' fill='%234CAF50'/%3E%3Ctext x='50' y='60' font-size='40' text-anchor='middle' fill='white' font-family='Arial'%3EYKST%3C/text%3E%3C/svg%3E" alt="Logo">
                                </div>
                                <div class="header-text">
                                    <div class="school-name">
                                        ယာခင်းရှင်သာ ကိုယ်ပိုင်အထက်တန်းကျောင်း
                                    </div>
                                    <div class="invoice-no">
                                        No. ${this.receiptData.payment_number}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="content">
                                <div class="line">
                                    ကျောင်းသား/သူအမည် ${this.receiptData.student_name} &nbsp;&nbsp;&nbsp; တန်းခွဲ ${studentClass}
                                    ကျောင်းလခပေးသွင်းသည့် ${invoiceMonthMM} လအတွက် ကျောင်းလခ ${this.receiptData.amount} ကျပ်
                                    (စာဖြင့်) ${amountInWords} ကျပ်တိတိနှင့် ${formattedDate} နေ့တွင် လက်ခံရပါသည်။
                                </div>
                                
                                <div class="line">
                                    (ကျောင်းဖယ်ရီ ${this.receiptData.ferry_fee || '0'} ကျပ် (စာဖြင့်) ${this.receiptData.ferry_fee ? numberToMyanmarWords(parseInt(this.receiptData.ferry_fee)) : 'သုည'} ကျပ်တိတိ)
                                </div>
                                
                                <div class="signature-section">
                                    <div class="signature-box">
                                        <div class="signature-label">(ပေးသွင်းသူ)</div>
                                        <div class="signature-line">အမည် ${guardianName}</div>
                                        <div class="signature-line">လက်မှတ် _____________</div>
                                    </div>
                                    <div class="signature-box">
                                        <div class="signature-label">(ငွေလက်ခံသူ)</div>
                                        <div class="signature-line">အမည် ${this.receiptData.receptionist_name || '_____________'}</div>
                                        <div class="signature-line">လက်မှတ် _____________</div>
                                    </div>
                                </div>
                                
                                <div class="line">
                                    မှတ်ချက် ${paymentNotes || '_____________________________________________'}
                                </div>
                                
                                <div class="note-section">
                                    <div class="note">
                                        မည်သည့်အကြောင်းနှင့်ဖြစ်စေ ပေးသွင်းပြီးသောအခကြေးငွေကို ပြန်လည်ထုတ်ပေးမည်မဟုတ်ပါ။
                                    </div>
                                    <div class="separator">
                                        ------------------------------------------------------------------------------------------------
                                    </div>
                                    <div class="contact">
                                        ဖုန်း - ၀၉ - ၄၄၃၀၈၉၆၅၆၊ ၀၉ - ၇၉၇၃၅၃၃၄၆၊၀၉-၆၈၈၉၈၉၆၅၆။ Hot Line : ၄၀၉၃၀၈၃၆၀၈
                                    </div>
                                </div>
                            </div>
                        </body>
                        </html>
                    `);
                    printWindow.document.close();
                    
                    // Wait for content to load, then print with no headers/footers
                    setTimeout(() => {
                        printWindow.focus();
                        printWindow.print();
                    }, 500);
                },
                
                openPaymentMethodModal() {
                    this.paymentMethodFormMethod = 'POST';
                    this.paymentMethodFormAction = '{{ route('payment-methods.store') }}';
                    this.paymentMethodForm = {
                        id: '',
                        name: '',
                        name_mm: '',
                        type: 'bank',
                        account_number: '',
                        account_name: '',
                        account_name_mm: '',
                        logo_url: '',
                        is_active: true,
                        instructions: '',
                        instructions_mm: '',
                        sort_order: 0
                    };
                    this.showPaymentMethodModal = true;
                },
                
                editPaymentMethod(method) {
                    this.paymentMethodFormMethod = 'PUT';
                    this.paymentMethodFormAction = `/payment-methods/${method.id}`;
                    this.paymentMethodForm = {
                        id: method.id,
                        name: method.name,
                        name_mm: method.name_mm || '',
                        type: method.type,
                        account_number: method.account_number,
                        account_name: method.account_name,
                        account_name_mm: method.account_name_mm || '',
                        logo_url: method.logo_url || '',
                        is_active: method.is_active,
                        instructions: method.instructions || '',
                        instructions_mm: method.instructions_mm || '',
                        sort_order: method.sort_order || 0
                    };
                    this.showPaymentMethodModal = true;
                },
                
                confirmDeleteStructure(structure) {
                    this.deleteConfirmData = {
                        title: '{{ __('finance.Delete Fee Structure') }}',
                        message: '{{ __('finance.Are you sure you want to delete this fee structure? All associated invoices will also be deleted.') }}',
                        formAction: `/student-fees/structures/${structure.id}`,
                        itemName: `${structure.fee_type?.name || 'Fee'} - ${parseInt(structure.amount).toLocaleString()} MMK`
                    };
                    this.showDeleteConfirmModal = true;
                },
                
                closeDeleteConfirm() {
                    this.showDeleteConfirmModal = false;
                },
                
                closeApproveConfirm() {
                    this.showApproveConfirmModal = false;
                },
                
                init() {
                    console.log('studentFeeManager init called');
                    
                    // Watch for tab changes and update URL
                    this.$watch('activeTab', (value) => {
                        const url = new URL(window.location);
                        url.searchParams.set('tab', value);
                        window.history.pushState({}, '', url);
                    });
                    
                    // Listen for approve modal event
                    window.addEventListener('open-approve-modal', (event) => {
                        console.log('open-approve-modal event received', event.detail);
                        this.$nextTick(() => {
                            this.approveConfirmData = event.detail;
                            this.showApproveConfirmModal = true;
                            console.log('showApproveConfirmModal set to:', this.showApproveConfirmModal);
                            console.log('approveConfirmData:', this.approveConfirmData);
                        });
                    });
                },
                
                submitDelete() {
                    const form = document.getElementById('deleteConfirmForm');
                    form.submit();
                }
            };
        }

        // Reject Payment Modal
        function openRejectModal(paymentId) {
            const modal = document.getElementById('rejectModal');
            const form = document.getElementById('rejectForm');
            form.action = `/student-fees/payments/${paymentId}/reject`;
            modal.classList.remove('hidden');
        }

        function closeRejectModal() {
            const modal = document.getElementById('rejectModal');
            modal.classList.add('hidden');
            document.getElementById('rejectReason').value = '';
        }

        // Payment Proof Modal Functions
        async function viewPaymentProof(proofId) {
            const modal = document.getElementById('paymentProofModal');
            const content = document.getElementById('paymentProofContent');
            
            // Show modal with loading state
            modal.classList.remove('hidden');
            content.innerHTML = '<div class="flex items-center justify-center py-8"><i class="fas fa-spinner fa-spin fa-2x text-gray-400"></i></div>';
            
            try {
                const response = await fetch(`/student-fees/payment-proofs/${proofId}/details`);
                const result = await response.json();
                
                if (result.success) {
                    const data = result.data;
                    content.innerHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Student Information -->
                            <div class="space-y-3">
                                <h4 class="font-semibold text-gray-900 dark:text-white border-b pb-2">{{ __('finance.Student Information') }}</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">{{ __('finance.Name') }}:</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">${data.student.name}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">{{ __('finance.Student ID') }}:</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">${data.student.identifier}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">{{ __('finance.Grade') }}:</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">${data.student.grade}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">{{ __('finance.Class') }}:</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">${data.student.class}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Payment Information -->
                            <div class="space-y-3">
                                <h4 class="font-semibold text-gray-900 dark:text-white border-b pb-2">{{ __('finance.Payment Information') }}</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">{{ __('finance.Amount') }}:</span>
                                        <span class="font-semibold text-lg text-gray-900 dark:text-gray-100">${Number(data.payment_amount).toLocaleString()} MMK</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">{{ __('finance.Months') }}:</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">${data.payment_months} {{ __('finance.month(s)') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">{{ __('finance.Payment Date') }}:</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">${data.payment_date}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">{{ __('finance.Method') }}:</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">${data.payment_method}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">{{ __('finance.Submitted') }}:</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">${data.submitted_at}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Notes -->
                        ${data.notes ? `
                        <div class="mt-4">
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-2">{{ __('finance.Notes') }}</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">${data.notes}</p>
                        </div>
                        ` : ''}
                        
                        <!-- Receipt Image -->
                        ${data.receipt_image ? `
                        <div class="mt-4">
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-2">{{ __('finance.Receipt Image') }}</h4>
                            <div class="border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden">
                                <img src="${data.receipt_image}" alt="Receipt" class="w-full h-auto cursor-pointer" onclick="window.open('${data.receipt_image}', '_blank')">
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ __('finance.Click image to view full size') }}</p>
                        </div>
                        ` : ''}
                        
                        <!-- Action Buttons -->
                        <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <button type="button" 
                                    onclick="closePaymentProofModal()"
                                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-lg transition-colors">
                                {{ __('finance.Cancel') }}
                            </button>
                            <button type="button" 
                                    onclick="openRejectProofModal('${proofId}')"
                                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                                <i class="fas fa-times mr-1"></i>
                                {{ __('finance.Reject') }}
                            </button>
                            <button type="button"
                                    onclick="openApproveProofModal('${proofId}', '${data.student.name}', '${parseInt(data.payment_amount).toLocaleString()} MMK', '${data.payment_date}')"
                                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                                <i class="fas fa-check mr-1"></i>
                                {{ __('finance.Approve') }}
                            </button>
                        </div>
                    `;
                } else {
                    content.innerHTML = '<div class="text-center py-8 text-red-600">{{ __('finance.Failed to load payment proof details') }}</div>';
                }
            } catch (error) {
                console.error('Error loading payment proof:', error);
                content.innerHTML = '<div class="text-center py-8 text-red-600">{{ __('finance.Error loading payment proof details') }}</div>';
            }
        }

        function closePaymentProofModal() {
            const modal = document.getElementById('paymentProofModal');
            modal.classList.add('hidden');
        }

        function openRejectProofModal(proofId) {
            closePaymentProofModal();
            const modal = document.getElementById('rejectProofModal');
            const form = document.getElementById('rejectProofForm');
            form.action = `/student-fees/payment-proofs/${proofId}/reject`;
            modal.classList.remove('hidden');
        }

        function closeRejectProofModal() {
            const modal = document.getElementById('rejectProofModal');
            modal.classList.add('hidden');
            document.getElementById('rejectProofReason').value = '';
        }

        function openApproveProofModal(proofId, studentName, amount, paymentDate) {
            console.log('openApproveProofModal called', {proofId, studentName, amount, paymentDate});
            closePaymentProofModal();
            
            console.log('Dispatching open-approve-modal event');
            // Dispatch a custom event that Alpine can listen to
            const event = new CustomEvent('open-approve-modal', {
                detail: {
                    proofId: proofId,
                    studentName: studentName,
                    amount: amount,
                    paymentDate: paymentDate,
                    formAction: `/student-fees/payment-proofs/${proofId}/approve`
                }
            });
            window.dispatchEvent(event);
            console.log('Event dispatched', event);
        }
    </script>

    <!-- Reject Payment Modal -->
    <div id="rejectModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-xl bg-white dark:bg-gray-800">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('finance.Reject Payment') }}</h3>
                <button onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="rejectReason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('finance.Reason for rejection') }}
                    </label>
                    <textarea id="rejectReason" 
                              name="reason" 
                              rows="4" 
                              required
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                              placeholder="{{ __('finance.Enter reason for rejecting this payment...') }}"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" 
                            onclick="closeRejectModal()"
                            class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-lg transition-colors">
                        {{ __('finance.Cancel') }}
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                        {{ __('finance.Reject Payment') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Payment Proof Modal -->
    <div id="paymentProofModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-xl bg-white dark:bg-gray-800">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('finance.Payment Proof Details') }}</h3>
                <button onclick="closePaymentProofModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div id="paymentProofContent" class="space-y-4">
                <!-- Content will be loaded dynamically -->
                <div class="flex items-center justify-center py-8">
                    <i class="fas fa-spinner fa-spin fa-2x text-gray-400"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Payment Proof Modal -->
    <div id="rejectProofModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-xl bg-white dark:bg-gray-800">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('finance.Reject Payment Proof') }}</h3>
                <button onclick="closeRejectProofModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="rejectProofForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="rejectProofReason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('finance.Reason for rejection') }} <span class="text-red-500">*</span>
                    </label>
                    <textarea id="rejectProofReason" 
                              name="rejection_reason" 
                              rows="4" 
                              required
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                              placeholder="{{ __('finance.Enter reason for rejecting this payment proof...') }}"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" 
                            onclick="closeRejectProofModal()"
                            class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-lg transition-colors">
                        {{ __('finance.Cancel') }}
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                        {{ __('finance.Reject') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Invoice Payment History Modal -->
    <div id="invoiceHistoryModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-xl bg-white dark:bg-gray-800">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('finance.Invoice Payment History') }}</h3>
                <button onclick="closeInvoiceHistoryModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div id="invoiceHistoryContent" class="space-y-4">
                <div class="flex items-center justify-center py-8">
                    <i class="fas fa-spinner fa-spin fa-2x text-gray-400"></i>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function showInvoiceHistory(invoiceId) {
            const modal = document.getElementById('invoiceHistoryModal');
            const content = document.getElementById('invoiceHistoryContent');
            
            modal.classList.remove('hidden');
            content.innerHTML = '<div class="flex items-center justify-center py-8"><i class="fas fa-spinner fa-spin fa-2x text-gray-400"></i></div>';
            
            try {
                const response = await fetch(`/student-fees/invoices/${invoiceId}/history`);
                const result = await response.json();
                
                if (result.success) {
                    const data = result.data;
                    let html = `
                        <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg mb-4">
                            <h4 class="font-semibold text-gray-900 dark:text-white mb-2">{{ __('finance.Invoice Info') }}</h4>
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div><span class="text-gray-600 dark:text-gray-400">{{ __('finance.Invoice Number') }}:</span> <span class="font-medium text-gray-900 dark:text-gray-100">${data.invoice.invoice_number}</span></div>
                                <div><span class="text-gray-600 dark:text-gray-400">{{ __('finance.Amount') }}:</span> <span class="font-medium text-gray-900 dark:text-gray-100">${Number(data.invoice.total_amount).toLocaleString()} MMK</span></div>
                                <div><span class="text-gray-600 dark:text-gray-400">{{ __('finance.Student') }}:</span> <span class="font-medium text-gray-900 dark:text-gray-100">${data.invoice.student_name}</span></div>
                                <div><span class="text-gray-600 dark:text-gray-400">{{ __('finance.Month') }}:</span> <span class="font-medium text-gray-900 dark:text-gray-100">${data.invoice.month}</span></div>
                            </div>
                        </div>
                    `;
                    
                    if (data.payment_proofs && data.payment_proofs.length > 0) {
                        html += '<h4 class="font-semibold text-gray-900 dark:text-white mb-3">{{ __('finance.Payment Proof History') }}</h4><div class="space-y-3">';
                        
                        data.payment_proofs.forEach((proof, index) => {
                            const statusColors = {
                                'pending_verification': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                'verified': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                'rejected': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'
                            };
                            
                            html += `
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">#${index + 1}</span>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${statusColors[proof.status]}">${proof.status_label}</span>
                                        </div>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">${proof.submitted_at}</span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 text-sm">
                                        <div><span class="text-gray-600 dark:text-gray-400">{{ __('finance.Amount') }}:</span> <span class="font-medium">${Number(proof.payment_amount).toLocaleString()} MMK</span></div>
                                        <div><span class="text-gray-600 dark:text-gray-400">{{ __('finance.Method') }}:</span> <span class="font-medium">${proof.payment_method}</span></div>
                                        <div><span class="text-gray-600 dark:text-gray-400">{{ __('finance.Payment Date') }}:</span> <span class="font-medium">${proof.payment_date}</span></div>
                                        ${proof.verified_at ? `<div><span class="text-gray-600 dark:text-gray-400">{{ __('finance.Processed At') }}:</span> <span class="font-medium">${proof.verified_at}</span></div>` : ''}
                                    </div>
                                    ${proof.rejection_reason ? `<div class="mt-3 p-2 bg-red-50 dark:bg-red-900/20 rounded border border-red-200 dark:border-red-800"><span class="text-xs font-medium text-red-800 dark:text-red-300">{{ __('finance.Rejection Reason') }}:</span><p class="text-sm text-red-700 dark:text-red-400 mt-1">${proof.rejection_reason}</p></div>` : ''}
                                    ${proof.notes ? `<div class="mt-2 text-sm text-gray-600 dark:text-gray-400"><span class="font-medium">{{ __('finance.Notes') }}:</span> ${proof.notes}</div>` : ''}
                                </div>
                            `;
                        });
                        
                        html += '</div>';
                    } else {
                        html += '<div class="text-center py-8"><i class="fas fa-inbox text-4xl text-gray-300 dark:text-gray-600 mb-3"></i><p class="text-gray-500 dark:text-gray-400">{{ __('finance.No payment history for this invoice') }}</p></div>';
                    }
                    
                    content.innerHTML = html;
                } else {
                    content.innerHTML = '<div class="text-center py-8 text-red-600">{{ __('finance.Failed to load payment history') }}</div>';
                }
            } catch (error) {
                console.error('Error:', error);
                content.innerHTML = '<div class="text-center py-8 text-red-600">{{ __('finance.Error loading payment history') }}</div>';
            }
        }

        function closeInvoiceHistoryModal() {
            document.getElementById('invoiceHistoryModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
