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

    <div class="py-6 sm:py-10 overflow-x-hidden" x-data="studentFeeManager()">
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
                'pending_reject' => __('finance.Pending & Rejects'),
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
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('finance.Total Received') }}</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format($totalReceived, 0) }} MMK</p>
                            <p class="text-xs text-green-600 dark:text-green-400">{{ $currentMonth }}</p>
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

                {{-- Legacy "Pending Payments from Guardian App" section removed --}}
                {{-- All pending payments now handled via PaymentSystem Payment Proofs section below --}}


                <!-- Fee Management Section -->
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm student-fee-section">
                    <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('finance.Student Fee List') }} - {{ $currentMonth }}</h3>
                    </div>

                    <!-- Filters -->
                    <div class="p-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                        <form method="GET" action="{{ route('student-fees.index') }}" class="flex flex-wrap items-center gap-3" id="fee-filter-form">
                            <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">{{ __('finance.Filters:') }}</span>
                            <select name="month" class="form-select-sm" onchange="this.form.submit()">
                                @foreach($monthOptions as $option)
                                    <option value="{{ $option['value'] }}" {{ $selectedMonth == $option['value'] ? 'selected' : '' }}>
                                        {{ $option['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            <select name="grade" class="form-select-sm" onchange="this.form.submit()">
                                <option value="">{{ __('finance.All Grades') }}</option>
                                @foreach($grades as $grade)
                                    <option value="{{ $grade->id }}" {{ request('grade') == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>
                                @endforeach
                            </select>
                            <select name="fee_type" class="form-select-sm" onchange="this.form.submit()">
                                <option value="">{{ __('finance.All Fee Types') }}</option>
                                @foreach($feeTypes as $feeType)
                                    <option value="{{ $feeType->id }}" {{ request('fee_type') == $feeType->id ? 'selected' : '' }}>{{ $feeType->name }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('finance.Search by name or ID...') }}" class="form-input-sm" id="fee-search-input">
                            <button type="submit" class="hidden">{{ __('finance.Apply') }}</button>
                            <a href="{{ route('student-fees.index') }}" class="btn-filter-reset">{{ __('finance.Reset') }}</a>
                        </form>
                    </div>

                    <script>
                        // Debounce search input
                        const searchInput = document.getElementById('fee-search-input');
                        const filterForm = document.getElementById('fee-filter-form');
                        let timeout = null;

                        searchInput.addEventListener('input', function() {
                            clearTimeout(timeout);
                            timeout = setTimeout(function() {
                                filterForm.submit();
                            }, 500);
                        });
                        
                        // Focus search input if it has value (after reload)
                        if (searchInput.value) {
                             // We can't easily auto-focus without scrolling, so we might skip this 
                             // or handle it with URL params if needed. 
                             // For now, simpler is better.
                        }
                    </script>

                    <!-- Invoices Fee Table -->
                    <!-- Table Header with Scrollbar -->
                    <!-- Invoices Fee Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="th-cell">{{ __('finance.No.') }}</th>
                                    <th class="th-cell">{{ __('finance.Student Name') }}</th>
                                    <th class="th-cell">{{ __('finance.Student ID') }}</th>
                                    <th class="th-cell">{{ __('finance.Class') }}</th>
                                    <th class="th-cell">{{ __('finance.Invoice No') }}</th>
                                    <th class="th-cell">{{ __('finance.Fee Type') }}</th>
                                    <th class="th-cell">{{ __('finance.Month') }}</th>
                                    <th class="th-cell">{{ __('finance.Fee Amount') }}</th>
                                    <th class="th-cell">{{ __('finance.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($unpaidInvoices as $index => $invoice)
                                    @php
                                        $student = $invoice->student;
                                        $hasRejectedProof = isset($rejectedProofsByInvoice[$invoice->id]);
                                        $rejectedProof = $hasRejectedProof ? $rejectedProofsByInvoice[$invoice->id] : null;
                                    @endphp
                                    <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800/50 {{ $hasRejectedProof ? 'border-l-4 border-red-500' : '' }}">
                                        <td class="td-cell text-center">{{ $unpaidInvoices->firstItem() + $index }}</td>
                                        <td class="td-cell font-medium text-gray-900 dark:text-white">
                                            <div>{{ $student->user?->name ?? '-' }}</div>
                                            @if($hasRejectedProof)
                                                <div class="flex items-center gap-1 mt-1">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                                        <i class="fas fa-times-circle mr-1"></i>
                                                        {{ __('finance.Payment Rejected') }}
                                                    </span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="td-cell text-gray-600 dark:text-gray-400">{{ $student->student_identifier }}</td>
                                        <td class="td-cell">{{ $student->formatted_class_name }}</td>
                                        <td class="td-cell font-mono text-xs text-gray-600 dark:text-gray-400">{{ $invoice->invoice_number ?? '-' }}</td>
                                        <td class="td-cell">
                                            @if($invoice->fees && $invoice->fees->isNotEmpty())
                                                @foreach($invoice->fees as $fee)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                                        {{ $fee->fee_name }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-gray-500 dark:text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="td-cell">
                                            <div>{{ $invoice->invoice_date?->translatedFormat('F Y') ?? $currentMonth }}</div>
                                            @if($hasRejectedProof && $rejectedProof->rejection_reason)
                                                <div class="text-xs text-red-600 dark:text-red-400 mt-1">
                                                    <i class="fas fa-info-circle"></i> {{ Str::limit($rejectedProof->rejection_reason, 30) }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="td-cell font-semibold text-green-600 dark:text-green-400">{{ number_format($invoice->total_amount, 0) }} MMK</td>
                                        <!-- Actions -->
                                        <td class="td-cell">
                                            <div class="flex items-center gap-1">
                                                <button type="button" class="action-btn process" @click="openPaymentModal(@js(['student' => $student, 'amount' => $invoice->total_amount, 'invoice' => $invoice]))" title="{{ __('finance.Process Payment') }}">
                                                    <i class="fas fa-credit-card"></i> {{ __('finance.Pay') }}
                                                </button>
                                                <form method="POST" action="{{ route('student-fees.students.reinform', $student) }}" class="inline" id="reminder-form-{{ $student->id }}">
                                                    @csrf
                                                    <button type="button" class="action-btn" title="{{ __('finance.Remind again to parent') }}" 
                                                        onclick="confirmAction('{{ route('student-fees.students.reinform', $student) }}', '{{ __('finance.Send Reminder') }}', '{{ __('finance.Send payment reminder to guardian?') }}', '{{ __('finance.Send Reminder') }}')">
                                                        <i class="fas fa-bell"></i> {{ __('finance.Remind again to parent') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="td-empty">
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

                <!-- Payment History Details Section -->
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm mt-6">
                    <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('finance.Payment History Details') }} - {{ $currentMonth }}</h3>
                    </div>

                    <!-- Payment History Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="th-cell">{{ __('finance.No.') }}</th>
                                    <th class="th-cell">{{ __('finance.Student Name') }}</th>
                                    <th class="th-cell">{{ __('finance.Student ID') }}</th>
                                    <th class="th-cell">{{ __('finance.Class') }}</th>
                                    <th class="th-cell">{{ __('finance.Invoice No') }}</th>
                                    <th class="th-cell">{{ __('finance.Fee Type') }}</th>
                                    <th class="th-cell">{{ __('finance.Month') }}</th>
                                    <th class="th-cell">{{ __('finance.Fee Amount') }}</th>
                                    <th class="th-cell">{{ __('finance.Date') }}</th>
                                    <th class="th-cell">{{ __('finance.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($paidPayments as $index => $invoice)
                                    @php
                                        // Get the most recent verified payment for this invoice
                                        $latestPayment = $invoice->payments->first();
                                        $paymentAmount = $latestPayment ? $latestPayment->payment_amount : $invoice->total_amount;
                                    @endphp
                                    <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                        <td class="td-cell text-center">{{ $paidPayments->firstItem() + $index }}</td>
                                        <td class="td-cell font-medium text-gray-900 dark:text-white">{{ $invoice->student->user->name ?? '-' }}</td>
                                        <td class="td-cell text-gray-600 dark:text-gray-400">{{ $invoice->student->student_identifier ?? '-' }}</td>
                                        <td class="td-cell">{{ $invoice->student->formatted_class_name }}</td>
                                        <td class="td-cell font-mono text-xs">{{ $invoice->invoice_number ?? '-' }}</td>
                                        <td class="td-cell">
                                            @if($invoice->fees && $invoice->fees->isNotEmpty())
                                                @foreach($invoice->fees as $fee)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                                        {{ $fee->fee_name }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-gray-500 dark:text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="td-cell">{{ $invoice->invoice_date?->translatedFormat('F Y') ?? $currentMonth }}</td>
                                        <td class="td-cell font-semibold text-green-600 dark:text-green-400">{{ number_format($paymentAmount, 0) }} MMK</td>
                                        <td class="td-cell">{{ $invoice->updated_at?->translatedFormat('M j, Y') }}</td>
                                        <td class="td-cell">
                                            @if($latestPayment)
                                                <a href="{{ route('student-fees.payment-receipt', ['payment' => $latestPayment->id]) }}" class="action-btn view" title="{{ __('finance.View Receipt') }}">
                                                    <i class="fas fa-receipt"></i> {{ __('finance.View Receipt') }}
                                                </a>
                                            @else
                                                <button type="button" class="action-btn view" title="{{ __('finance.View Invoice') }}" @click="openInvoiceModal(@js($invoice))">
                                                    <i class="fas fa-file-invoice"></i> {{ __('finance.View Invoice') }}
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="td-empty">
                                            <div class="flex flex-col items-center py-8">
                                                <i class="fas fa-history text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
                                                <p class="text-gray-500 dark:text-gray-400">{{ __('finance.No payments history found.') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($paidPayments->total() > 0)
                        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex flex-wrap items-center justify-between gap-3">
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('pagination.Showing') }} {{ $paidPayments->firstItem() ?? 0 }} {{ __('pagination.to') }} {{ $paidPayments->lastItem() ?? 0 }} {{ __('pagination.of') }} {{ $paidPayments->total() }} {{ __('pagination.results') }}
                            </div>
                            @if($paidPayments->hasPages())
                                <div class="flex items-center gap-1">
                                    {{-- First Page --}}
                                    <a href="{{ $paidPayments->url(1) }}" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 {{ $paidPayments->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}">
                                        <i class="fas fa-angle-double-left"></i>
                                    </a>
                                    {{-- Previous Page --}}
                                    <a href="{{ $paidPayments->previousPageUrl() ?? '#' }}" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 {{ $paidPayments->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}">
                                        <i class="fas fa-angle-left"></i>
                                    </a>
                                    {{-- Page Numbers --}}
                                    @php
                                        $histCurrentPage = $paidPayments->currentPage();
                                        $histLastPage = $paidPayments->lastPage();
                                        $histStart = max(1, $histCurrentPage - 2);
                                        $histEnd = min($histLastPage, $histStart + 4);
                                        if ($histEnd - $histStart < 4) $histStart = max(1, $histEnd - 4);
                                    @endphp
                                    @for($page = $histStart; $page <= $histEnd; $page++)
                                        <a href="{{ $paidPayments->url($page) }}" class="px-3 py-1.5 text-sm font-medium rounded-lg border {{ $page === $histCurrentPage ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                            {{ $page }}
                                        </a>
                                    @endfor
                                    {{-- Next Page --}}
                                    <a href="{{ $paidPayments->nextPageUrl() ?? '#' }}" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 {{ !$paidPayments->hasMorePages() ? 'opacity-50 pointer-events-none' : '' }}">
                                        <i class="fas fa-angle-right"></i>
                                    </a>
                                    {{-- Last Page --}}
                                    <a href="{{ $paidPayments->url($paidPayments->lastPage()) }}" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 {{ !$paidPayments->hasMorePages() ? 'opacity-50 pointer-events-none' : '' }}">
                                        <i class="fas fa-angle-double-right"></i>
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Pending & Rejects Tab -->
            <div x-show="activeTab === 'pending_reject'" x-cloak>
                <!-- Status Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-400 to-yellow-600 text-white flex items-center justify-center text-xl shadow-lg">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('finance.Pending Verifications') }}</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $pendingPayments->total() }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('finance.Requires Action') }}</p>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-500 to-rose-600 text-white flex items-center justify-center text-xl shadow-lg">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('finance.Rejected Payments') }}</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $rejectedPayments->total() }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('finance.Needs Review') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Filters for Pending & Rejected -->
                <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl">
                    <form method="GET" action="{{ route('student-fees.index') }}" class="flex flex-wrap items-center gap-3" id="pending-filter-form">
                        <input type="hidden" name="tab" value="pending_reject">
                        <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">{{ __('finance.Filters:') }}</span>
                        <select name="month" class="form-select-sm" onchange="this.form.submit()">
                            @foreach($monthOptions as $option)
                                <option value="{{ $option['value'] }}" {{ $selectedMonth == $option['value'] ? 'selected' : '' }}>
                                    {{ $option['label'] }}
                                </option>
                            @endforeach
                        </select>
                        <select name="grade" class="form-select-sm" onchange="this.form.submit()">
                            <option value="">{{ __('finance.All Grades') }}</option>
                            @foreach($grades as $grade)
                                <option value="{{ $grade->id }}" {{ request('grade') == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('finance.Search by name or ID...') }}" class="form-input-sm" id="pending-search-input">
                        <button type="submit" class="hidden">{{ __('finance.Apply') }}</button>
                        <a href="{{ route('student-fees.index', ['tab' => 'pending_reject']) }}" class="btn-filter-reset">{{ __('finance.Reset') }}</a>
                    </form>
                    <script>
                        // Debounce search input for pending tab
                        const pendingSearchInput = document.getElementById('pending-search-input');
                        const pendingFilterForm = document.getElementById('pending-filter-form');
                        let pendingTimeout = null;

                        pendingSearchInput.addEventListener('input', function() {
                            clearTimeout(pendingTimeout);
                            pendingTimeout = setTimeout(function() {
                                pendingFilterForm.submit();
                            }, 500);
                        });
                    </script>
                </div>

                <!-- Pending Invoices Section -->
                <div class="bg-white dark:bg-gray-800 border border-yellow-200 dark:border-yellow-700 rounded-xl shadow-sm mb-6">
                    <div class="flex items-center justify-between p-4 border-b border-yellow-200 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/20">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-yellow-500 text-white flex items-center justify-center">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('finance.Pending Invoices List') }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="th-cell">{{ __('finance.No.') }}</th>
                                    <th class="th-cell">{{ __('finance.Student Name') }}</th>
                                    <th class="th-cell">{{ __('finance.Student ID') }}</th>
                                    <th class="th-cell">{{ __('finance.Class') }}</th>
                                    <th class="th-cell">{{ __('finance.Invoice No') }}</th>
                                    <th class="th-cell">{{ __('finance.Month') }}</th>
                                    <th class="th-cell">{{ __('finance.Fee Amount') }}</th>
                                    <th class="th-cell">{{ __('finance.Date') }}</th>
                                    <th class="th-cell">{{ __('finance.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($pendingPayments as $index => $payment)
                                    <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                        <td class="td-cell text-center">{{ $pendingPayments->firstItem() + $index }}</td>
                                        <td class="td-cell font-medium text-gray-900 dark:text-white">{{ $payment->student->user->name ?? '-' }}</td>
                                        <td class="td-cell text-gray-600 dark:text-gray-400">{{ $payment->student->student_identifier ?? '-' }}</td>
                                        <td class="td-cell">{{ $payment->student->formatted_class_name ?? '-' }}</td>
                                        <td class="td-cell font-mono text-xs text-gray-600 dark:text-gray-400">{{ $payment->invoice ? $payment->invoice->invoice_number : '-' }}</td>
                                        <td class="td-cell">{{ $payment->invoice ? ($payment->invoice->invoice_date?->translatedFormat('F Y') ?? '-') : '-' }}</td>
                                        <td class="td-cell font-semibold text-gray-900 dark:text-white">{{ number_format($payment->payment_amount, 0) }} MMK</td>
                                        <td class="td-cell text-gray-600 dark:text-gray-400">{{ $payment->created_at->translatedFormat('M j, Y') }}</td>
                                        <td class="td-cell">
                                            <button type="button" class="action-btn process" title="{{ __('finance.Verify') }}" onclick="viewPaymentProof('{{ $payment->id }}')">
                                                <i class="fas fa-check-circle"></i> {{ __('finance.Verify') }}
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="td-empty">
                                            <div class="flex flex-col items-center py-8">
                                                <i class="fas fa-clock text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
                                                <p class="text-gray-500 dark:text-gray-400">{{ __('finance.No pending invoices found.') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                     @if($pendingPayments->hasPages())
                        <div class="p-4 border-t border-gray-200 dark:border-gray-700">{{ $pendingPayments->appends(request()->query())->links() }}</div>
                     @endif
                </div>

                <!-- Rejected Invoices Section -->
                <div class="bg-white dark:bg-gray-800 border border-red-200 dark:border-red-700 rounded-xl shadow-sm">
                    <div class="flex items-center justify-between p-4 border-b border-red-200 dark:border-red-700 bg-red-50 dark:bg-red-900/20">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-red-500 text-white flex items-center justify-center">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('finance.Rejected Invoices List') }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="th-cell">{{ __('finance.No.') }}</th>
                                    <th class="th-cell">{{ __('finance.Student Name') }}</th>
                                    <th class="th-cell">{{ __('finance.Student ID') }}</th>
                                    <th class="th-cell">{{ __('finance.Class') }}</th>
                                    <th class="th-cell">{{ __('finance.Invoice No') }}</th>
                                    <th class="th-cell">{{ __('finance.Month') }}</th>
                                    <th class="th-cell">{{ __('finance.Fee Amount') }}</th>
                                    <th class="th-cell">{{ __('finance.Date') }}</th>
                                    <th class="th-cell">{{ __('finance.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($rejectedPayments as $index => $payment)
                                    <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                        <td class="td-cell text-center">{{ $rejectedPayments->firstItem() + $index }}</td>
                                        <td class="td-cell font-medium text-gray-900 dark:text-white">{{ $payment->student->user->name ?? '-' }}</td>
                                        <td class="td-cell text-gray-600 dark:text-gray-400">{{ $payment->student->student_identifier ?? '-' }}</td>
                                        <td class="td-cell">{{ $payment->student->formatted_class_name ?? '-' }}</td>
                                        <td class="td-cell font-mono text-xs text-gray-600 dark:text-gray-400">{{ $payment->invoice ? $payment->invoice->invoice_number : '-' }}</td>
                                        <td class="td-cell">
                                            <div>{{ $payment->invoice ? ($payment->invoice->invoice_date?->translatedFormat('F Y') ?? '-') : '-' }}</div>
                                            @if($payment->rejection_reason)
                                                <div class="text-xs text-red-600 dark:text-red-400 mt-1">
                                                    <i class="fas fa-info-circle"></i> {{ Str::limit($payment->rejection_reason, 30) }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="td-cell font-semibold text-red-600 dark:text-red-400">{{ number_format($payment->payment_amount, 0) }} MMK</td>
                                        <td class="td-cell text-gray-600 dark:text-gray-400">{{ $payment->updated_at->translatedFormat('M j, Y') }}</td>
                                        <td class="td-cell">
                                            <button type="button" class="action-btn view" title="{{ __('finance.View') }}" onclick="viewPaymentProof('{{ $payment->id }}')">
                                                <i class="fas fa-eye"></i> {{ __('finance.View') }}
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="td-empty">
                                            <div class="flex flex-col items-center py-8">
                                                <i class="fas fa-times-circle text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
                                                <p class="text-gray-500 dark:text-gray-400">{{ __('finance.No rejected invoices found.') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                     @if($rejectedPayments->hasPages())
                        <div class="p-4 border-t border-gray-200 dark:border-gray-700">{{ $rejectedPayments->appends(request()->query())->links() }}</div>
                     @endif
                </div>
            </div>

            <div x-show="activeTab === 'structure'" x-cloak>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                <i class="fas fa-graduation-cap text-amber-600"></i> {{ __('finance.School Fees (Monthly Recurring)') }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('finance.Monthly tuition fees set in Academic Management for each grade level') }}</p>
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
                                    <th class="th-cell">{{ __('finance.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($grades as $grade)
                                    @php
                                        $gradeFee = $grade->price_per_month ?? 0;
                                        $gradeStudents = $studentCountByGrade[$grade->id] ?? 0;
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

                    <!-- Additional Fee Table -->
                    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                            <h4 class="text-md font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                <i class="fas fa-tags text-purple-600"></i> {{ __('finance.Additional Fee Table') }}
                            </h4>
                            <button type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg text-white bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 shadow-md hover:shadow-lg transition-all" @click="openCategoryModal()">
                                <i class="fas fa-plus"></i> {{ __('finance.Add Additional Fee') }}
                            </button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="th-cell">{{ __('finance.No.') }}</th>
                                        <th class="th-cell">{{ __('finance.Name') }}</th>
                                        <th class="th-cell">{{ __('finance.Fee Type') }}</th>
                                        <th class="th-cell">{{ __('finance.Amount') }}</th>
                                        <th class="th-cell">{{ __('finance.Due Date') }}</th>
                                        <th class="th-cell">{{ __('finance.Partial') }}</th>
                                        <th class="th-cell">{{ __('finance.Discount') }}</th>
                                        <th class="th-cell">{{ __('finance.Status') }}</th>
                                        <th class="th-cell">{{ __('finance.Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($feeTypes as $index => $feeType)
                                        <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                            <td class="td-cell text-center">{{ $index + 1 }}</td>
                                            <td class="td-cell">
                                                <div class="font-semibold text-gray-900 dark:text-white">{{ $feeType->name }}</div>
                                                @if($feeType->description)
                                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ Str::limit($feeType->description, 40) }}</div>
                                                @endif
                                            </td>
                                            <td class="td-cell">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-md bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300">
                                                    {{ $feeType->fee_type ?? 'Other' }}
                                                </span>
                                            </td>
                                            <td class="td-cell font-semibold text-amber-600 dark:text-amber-400">{{ number_format($feeType->amount, 0) }} MMK</td>
                                            <td class="td-cell text-sm text-gray-600 dark:text-gray-400">
                                                @if($feeType->due_date)
                                                    <span class="font-medium">{{ $feeType->due_date }}<sup>{{ in_array($feeType->due_date, [1,21]) ? 'st' : (in_array($feeType->due_date, [2,22]) ? 'nd' : (in_array($feeType->due_date, [3,23]) ? 'rd' : 'th')) }}</sup></span>
                                                    <span class="text-xs text-gray-400 dark:text-gray-500 ml-0.5">/ {{ __('finance.month') }}</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="td-cell">
                                                @if($feeType->partial_status)
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-md bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">
                                                        <i class="fas fa-check-circle mr-1"></i>{{ __('finance.Yes') }}
                                                    </span>
                                                @else
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-md bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">
                                                        {{ __('finance.No') }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="td-cell">
                                                @if($feeType->discount_status)
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-md bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">
                                                        <i class="fas fa-check-circle mr-1"></i>{{ __('finance.Yes') }}
                                                    </span>
                                                @else
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-md bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">
                                                        {{ __('finance.No') }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="td-cell">
                                                @if($feeType->status)
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-md bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">
                                                        <i class="fas fa-check-circle mr-1"></i>{{ __('finance.Active') }}
                                                    </span>
                                                @else
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-md bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300">
                                                        {{ __('finance.Inactive') }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="td-cell">
                                                <div class="flex items-center gap-1">
                                                    <a href="{{ route('student-fees.categories.show', $feeType) }}" class="action-btn view" title="{{ __('finance.View Details') }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button type="button" class="action-btn edit" @click="openEditCategoryModal(@js($feeType))" title="{{ __('finance.Edit') }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form method="POST" action="{{ route('student-fees.categories.destroy', $feeType) }}" class="inline" onsubmit="return confirm('{{ __('finance.Delete this fee?') }}')">
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
                                            <td colspan="9" class="td-empty">
                                                <div class="flex flex-col items-center py-8">
                                                    <i class="fas fa-tags text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
                                                    <p class="text-gray-500 dark:text-gray-400">{{ __('finance.No additional fees yet. Click "Add Additional Fee" to create one.') }}</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>




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



        <!-- Process Payment Modal -->
        <div x-show="showPaymentModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="showPaymentModal = false">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-xl w-full max-w-2xl shadow-2xl" @click.stop>
                    <form @submit.prevent="submitPayment" x-ref="paymentForm" enctype="multipart/form-data">
                        <!-- Header -->
                        <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                <i class="fas fa-money-check-alt text-green-600"></i> {{ __('finance.Payment') }}
                            </h4>
                            <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700" @click="showPaymentModal = false">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div class="p-5 space-y-5 max-h-[70vh] overflow-y-auto">
                            <!-- Student Info -->
                            <div class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                <p class="text-sm font-semibold text-blue-800 dark:text-blue-200" x-text="paymentInfo"></p>
                            </div>

                            <!-- Payment Type Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-3">{{ __('finance.Payment Type') }}</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all" :class="paymentData.payment_type === 'full' ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-green-300'">
                                        <input type="radio" name="payment_type" value="full" x-model="paymentData.payment_type" @change="updatePaymentCalculation" class="sr-only">
                                        <div class="flex items-center gap-3">
                                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center" :class="paymentData.payment_type === 'full' ? 'border-green-500' : 'border-gray-400'">
                                                <div x-show="paymentData.payment_type === 'full'" class="w-3 h-3 rounded-full bg-green-500"></div>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-900 dark:text-white">{{ __('finance.Full Payment') }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('finance.Pay all fees') }}</p>
                                            </div>
                                        </div>
                                    </label>
                                    <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all" :class="[paymentData.payment_type === 'partial' ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-amber-300', (paymentData.hasOverdueFees || paymentData.hasNonPartialFee || paymentData.hasReachedPartialLimit) ? 'opacity-50 cursor-not-allowed' : '']">
                                        <input type="radio" name="payment_type" value="partial" x-model="paymentData.payment_type" @change="updatePaymentCalculation" :disabled="paymentData.hasOverdueFees || paymentData.hasNonPartialFee || paymentData.hasReachedPartialLimit" class="sr-only">
                                        <div class="flex items-center gap-3">
                                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center" :class="paymentData.payment_type === 'partial' ? 'border-amber-500' : 'border-gray-400'">
                                                <div x-show="paymentData.payment_type === 'partial'" class="w-3 h-3 rounded-full bg-amber-500"></div>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-900 dark:text-white">{{ __('finance.Partial Payment') }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('finance.Adjust amounts') }}</p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <p x-show="paymentData.hasOverdueFees" class="mt-2 text-xs text-red-600 dark:text-red-400">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>{{ __('finance.Cannot make partial payment - some fees are overdue') }}
                                </p>
                                <p x-show="paymentData.hasNonPartialFee && !paymentData.hasOverdueFees" class="mt-2 text-xs text-red-600 dark:text-red-400">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>{{ __('finance.Cannot make partial payment - some fees do not support partial payment') }}
                                </p>
                                <p x-show="paymentData.hasReachedPartialLimit && !paymentData.hasOverdueFees && !paymentData.hasNonPartialFee" class="mt-2 text-xs text-red-600 dark:text-red-400">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>{{ __('finance.Maximum partial payment limit reached. Please pay the full remaining amount.') }}
                                </p>
                            </div>

                            <!-- Payment Period Selection - Full Payment Only (Hidden for Remaining Invoices) -->
                            <div x-show="paymentData.payment_type === 'full' && !paymentData.isRemainingInvoice">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-3">{{ __('finance.Payment Period') }}</label>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">{{ __('finance.Select payment period for each fee type') }}</p>
                                
                                <!-- Fee-specific payment period selection -->
                                <div class="space-y-2">
                                    <template x-for="(fee, index) in paymentData.fees" :key="fee.id">
                                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 bg-white dark:bg-gray-800">
                                            <!-- Fee Header with Amount -->
                                            <div class="flex items-center justify-between mb-2">
                                                <div class="flex-1">
                                                    <div class="flex items-baseline gap-2">
                                                        <p class="font-medium text-sm text-gray-900 dark:text-white" x-text="fee.fee_name"></p>
                                                        <p class="text-xs text-gray-500" x-text="'{{ __('finance.Due') }}: ' + formatDueDate(fee.due_date)"></p>
                                                    </div>
                                                    <!-- Show calculation dynamically -->
                                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                                        <span x-text="fee.remaining_amount.toLocaleString() + ' MMK'"></span>
                                                        <template x-if="fee.payment_months > 1">
                                                            <span x-text="' × ' + fee.payment_months"></span>
                                                        </template>
                                                        <template x-if="fee.payment_months == 1">
                                                            <span>/{{ __('finance.month') }}</span>
                                                        </template>
                                                        <!-- Add discount notice -->
                                                        <template x-if="(() => {
                                                            const isSchoolFee = fee.fee_name && fee.fee_name.toLowerCase().includes('school fee');
                                                            const discountOption = paymentPeriodOptions.find(opt => opt.months == (fee.payment_months || 1));
                                                            return isSchoolFee && discountOption && discountOption.discount_percent > 0;
                                                        })()">
                                                            <span class="text-green-600 dark:text-green-400 ml-1" x-text="'(' + parseInt(paymentPeriodOptions.find(opt => opt.months == (fee.payment_months || 1)).discount_percent) + '% {{ __('finance.off') }})'"></span>
                                                        </template>
                                                    </p>
                                                </div>
                                                <div class="text-right">
                                                    <span class="text-sm font-bold text-gray-900 dark:text-white" x-text="(() => {
                                                        let amount = fee.remaining_amount * fee.payment_months;
                                                        const isSchoolFee = fee.fee_name && fee.fee_name.toLowerCase().includes('school fee');
                                                        const discountOption = paymentPeriodOptions.find(opt => opt.months == (fee.payment_months || 1));
                                                        if (isSchoolFee && discountOption && discountOption.discount_percent > 0) {
                                                            amount = amount * (1 - discountOption.discount_percent / 100);
                                                        }
                                                        return amount.toLocaleString() + ' MMK';
                                                    })()"></span>
                                                    <template x-if="(() => {
                                                        const isSchoolFee = fee.fee_name && fee.fee_name.toLowerCase().includes('school fee');
                                                        const discountOption = paymentPeriodOptions.find(opt => opt.months == (fee.payment_months || 1));
                                                        return isSchoolFee && discountOption && discountOption.discount_percent > 0;
                                                    })()">
                                                        <div class="text-xs text-gray-500 line-through" x-text="(fee.remaining_amount * fee.payment_months).toLocaleString() + ' MMK'"></div>
                                                    </template>
                                                </div>
                                            </div>
                                            
                                            <!-- Payment period options in one line -->
                                            <div class="flex gap-2">
                                                <template x-for="option in paymentPeriodOptions" :key="option.months">
                                                    <label class="flex-1 relative flex flex-col items-center justify-center p-2 border-2 rounded-lg cursor-pointer transition-all text-center" :class="fee.payment_months == option.months ? 'border-teal-500 bg-teal-50 dark:bg-teal-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-teal-300'">
                                                        <input type="radio" :name="'fee_months_' + fee.id" :value="option.months" x-model.number="fee.payment_months" @change="updatePaymentCalculation" class="sr-only">
                                                        <div class="flex flex-row items-center justify-center gap-1">
                                                            <span class="font-bold text-sm text-gray-900 dark:text-white" x-text="option.months"></span>
                                                            <span class="text-xs text-gray-600 dark:text-gray-400" x-text="option.months === 1 ? '{{ __('finance.month') }}' : '{{ __('finance.months') }}'"></span>
                                                            <template x-if="option.discount_percent > 0 && fee.fee_name && fee.fee_name.toLowerCase().includes('school fee')">
                                                                <span class="text-xs font-medium text-green-600 dark:text-green-400" x-text="'(-' + parseInt(option.discount_percent) + '%)'"></span>
                                                            </template>
                                                        </div>
                                                    </label>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Partial Payment - Fee Breakdown with Input Field -->
                            <div x-show="paymentData.payment_type === 'partial'">
                                <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4 bg-white dark:bg-gray-800">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-1">{{ __('finance.Payment Summary') }}</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">{{ __('finance.Select fees and adjust amounts') }}</p>
                                    
                                    <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">{{ __('finance.Fees Breakdown') }}</p>
                                        
                                        <div class="space-y-4">
                                            <template x-for="(fee, index) in paymentData.fees" :key="fee.id">
                                                <div class="space-y-2">
                                                    <div class="flex items-start justify-between gap-3">
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate" x-text="fee.fee_name"></p>
                                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                                {{ __('finance.Total') }}: <span x-text="fee.remaining_amount.toLocaleString()"></span> MMK
                                                                <span x-show="fee.payment_amount < fee.remaining_amount" class="ml-2">
                                                                    | {{ __('finance.Remaining') }}: <span x-text="(fee.remaining_amount - fee.payment_amount).toLocaleString()"></span> MMK
                                                                </span>
                                                            </p>
                                                        </div>
                                                        
                                                        <div class="flex items-center gap-2 flex-shrink-0">
                                                            <button type="button" 
                                                                @click="adjustFeeAmount(index, -500)"
                                                                class="w-9 h-9 flex items-center justify-center rounded-lg border-2 transition-all"
                                                                :class="fee.payment_amount <= 1000 ? 'border-gray-200 dark:border-gray-700 text-gray-300 dark:text-gray-600 cursor-not-allowed' : 'border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:border-teal-500 hover:text-teal-500'"
                                                                :disabled="fee.payment_amount <= 1000">
                                                                <i class="fas fa-minus text-xs"></i>
                                                            </button>
                                                            
                                                            <input 
                                                                type="number" 
                                                                :value="fee.payment_amount"
                                                                @input="setFeeAmount(index, $event.target.value)"
                                                                @blur="validateFeeAmount(index)"
                                                                min="1000"
                                                                :max="fee.remaining_amount"
                                                                step="500"
                                                                class="w-28 px-2 py-2 text-center text-sm font-bold border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                                                                placeholder="1,000"
                                                            />
                                                            
                                                            <button type="button" 
                                                                @click="adjustFeeAmount(index, 500)"
                                                                class="w-9 h-9 flex items-center justify-center rounded-lg border-2 transition-all"
                                                                :class="fee.payment_amount >= fee.remaining_amount ? 'border-gray-200 dark:border-gray-700 text-gray-300 dark:text-gray-600 cursor-not-allowed' : 'border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:border-teal-500 hover:text-teal-500'"
                                                                :disabled="fee.payment_amount >= fee.remaining_amount">
                                                                <i class="fas fa-plus text-xs"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    
                                                    <p x-show="fee.payment_amount < 1000" class="text-xs text-red-600 dark:text-red-400 text-right">
                                                        <i class="fas fa-exclamation-circle mr-1"></i>{{ __('finance.Minimum amount is 1,000 MMK') }}
                                                    </p>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Amount -->
                            <div class="p-3 bg-gradient-to-br from-teal-500 to-emerald-500 rounded-lg">
                                <div class="flex items-center justify-between text-white">
                                    <span class="text-sm font-medium">{{ __('finance.Total Amount') }}</span>
                                    <span class="font-bold text-xl" x-text="paymentData.total.toLocaleString() + ' MMK'"></span>
                                </div>
                            </div>

                            <!-- Hidden inputs for fee payment months -->
                            <template x-for="(fee, index) in paymentData.fees" :key="fee.id">
                                <input type="hidden" :name="'fee_payment_months[' + fee.id + ']'" :value="fee.payment_months">
                            </template>
                            
                            <!-- Hidden inputs for fee amounts (partial payment) -->
                            <template x-for="(fee, index) in paymentData.fees" :key="'amount-' + fee.id">
                                <input type="hidden" :name="'fee_amounts[' + fee.id + ']'" :value="fee.payment_amount">
                            </template>
                            
                            <!-- Hidden input for overall payment_months (use max value for validation) -->
                            <input type="hidden" name="payment_months" :value="Math.max(...paymentData.fees.map(f => f.payment_months || 1))">
                            
                            <!-- Hidden input for payment type -->
                            <input type="hidden" name="payment_type" :value="paymentData.payment_type">

                            <!-- Payment Method -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">{{ __('finance.Payment Method') }} <span class="text-red-500">*</span></label>
                                <select name="payment_method_id" x-model="paymentData.payment_method_id" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    <option value="">{{ __('finance.Select Payment Method') }}</option>
                                    @foreach($paymentMethods as $method)
                                        <option value="{{ $method->id }}">{{ $method->name }} @if($method->account_number && $method->account_number !== 'N/A') - {{ $method->account_number }} @endif</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Reference Number & Payment Date -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">{{ __('finance.Reference Number') }}</label>
                                    <input type="text" name="reference_number" x-model="paymentData.reference_number" placeholder="{{ __('finance.Transaction ID, Check #, etc.') }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">{{ __('finance.Payment Date') }} <span class="text-red-500">*</span></label>
                                    <input type="date" name="payment_date" x-model="paymentData.payment_date" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                </div>
                            </div>

                            <!-- Receipt Image Upload -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">{{ __('finance.Receipt Image') }} <span class="text-gray-500 text-xs">({{ __('finance.Optional') }})</span></label>
                                <input type="file" name="receipt_image" accept="image/*" @change="handleReceiptUpload" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            </div>

                            <!-- Notes -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">{{ __('finance.Notes') }}</label>
                                <textarea name="notes" x-model="paymentData.notes" rows="2" placeholder="{{ __('finance.Additional payment details...') }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="flex items-center justify-between gap-3 p-5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
                            <button type="button" class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" @click="showPaymentModal = false">
                                <i class="fas fa-times mr-2"></i>{{ __('finance.Cancel') }}
                            </button>
                            <button type="submit" class="px-8 py-3 text-base font-bold rounded-lg text-white bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-600 hover:to-emerald-600 shadow-xl hover:shadow-2xl transform hover:scale-105 transition-all duration-200" :disabled="isSubmitting">
                                <span x-show="!isSubmitting" class="flex items-center gap-2">
                                    <i class="fas fa-check-circle"></i>
                                    <span>{{ __('finance.Pay Now') }}</span>
                                    <span class="px-2 py-0.5 bg-white/20 rounded text-sm" x-text="paymentData.total.toLocaleString() + ' MMK'"></span>
                                </span>
                                <span x-show="isSubmitting"><i class="fas fa-spinner fa-spin mr-2"></i>{{ __('finance.Processing...') }}</span>
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
                                    <input type="number" step="1" min="0" max="100" name="discount_percent" class="form-input-full pr-12" x-model="promotionForm.discount_percent" placeholder="0" required>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <span class="text-gray-500 dark:text-gray-400 font-semibold">%</span>
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('finance.Enter whole number percentage (0-100)') }}</p>
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
                <div class="relative bg-white dark:bg-gray-800 rounded-xl w-full max-w-2xl shadow-2xl" @click.stop>
                    <form method="POST" :action="categoryFormAction" x-ref="categoryForm">
                        @csrf
                        <template x-if="categoryFormMethod === 'PUT'"><input type="hidden" name="_method" value="PUT"></template>
                        <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                <i class="fas fa-tags text-purple-600"></i>
                                <span x-text="categoryFormMethod === 'PUT' ? '{{ __('finance.Edit Additional Fee') }}' : '{{ __('finance.Add Additional Fee') }}'"></span>
                            </h4>
                            <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700" @click="showCategoryModal = false">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="p-5 space-y-4 max-h-[70vh] overflow-y-auto">
                            <!-- Row 1: Name & Fee Type -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Name') }} <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" class="form-input-full" x-model="categoryForm.name" placeholder="{{ __('finance.e.g., Library Fee, Sport Fee') }}" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Fee Type') }} <span class="text-red-500">*</span></label>
                                    <select name="fee_type" class="form-select-full" x-model="categoryForm.fee_type" required>
                                        @foreach(\App\Models\FeeType::FEE_TYPES as $type)
                                            <option value="{{ $type }}">{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Row 2: Amount -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Amount') }} (MMK) <span class="text-red-500">*</span></label>
                                    <input type="number" name="amount" class="form-input-full" x-model="categoryForm.amount" placeholder="0" min="0" step="1" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Status') }} <span class="text-red-500">*</span></label>
                                    <select name="status" class="form-select-full" x-model="categoryForm.status" required>
                                        <option value="active">{{ __('finance.Active') }}</option>
                                        <option value="inactive">{{ __('finance.Inactive') }}</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Row 4: Frequency -->
                            <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Frequency') }} <span class="text-red-500">*</span></label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <select name="frequency" class="form-select-full" x-model="categoryForm.frequency" required>
                                            <option value="0">{{ __('finance.Choose Frequency') }}</option>
                                            <option value="one_time">{{ __('finance.This Month') }}</option>
                                            <option value="monthly">{{ __('finance.Monthly') }}</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Month Selection (shown only for monthly) -->
                                <div x-show="categoryForm.frequency === 'monthly'" class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Start Month') }} <span class="text-red-500">*</span></label>
                                        <select name="start_month" class="form-select-full" x-model="categoryForm.start_month" :required="categoryForm.frequency === 'monthly'">
                                            <option value="1" :disabled="1 < currentMonth">{{ __('finance.January') }}</option>
                                            <option value="2" :disabled="2 < currentMonth">{{ __('finance.February') }}</option>
                                            <option value="3" :disabled="3 < currentMonth">{{ __('finance.March') }}</option>
                                            <option value="4" :disabled="4 < currentMonth">{{ __('finance.April') }}</option>
                                            <option value="5" :disabled="5 < currentMonth">{{ __('finance.May') }}</option>
                                            <option value="6" :disabled="6 < currentMonth">{{ __('finance.June') }}</option>
                                            <option value="7" :disabled="7 < currentMonth">{{ __('finance.July') }}</option>
                                            <option value="8" :disabled="8 < currentMonth">{{ __('finance.August') }}</option>
                                            <option value="9" :disabled="9 < currentMonth">{{ __('finance.September') }}</option>
                                            <option value="10" :disabled="10 < currentMonth">{{ __('finance.October') }}</option>
                                            <option value="11" :disabled="11 < currentMonth">{{ __('finance.November') }}</option>
                                            <option value="12" :disabled="12 < currentMonth">{{ __('finance.December') }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.End Month') }} <span class="text-red-500">*</span></label>
                                        <select name="end_month" class="form-select-full" x-model="categoryForm.end_month" :required="categoryForm.frequency === 'monthly'">
                                            <option value="1" :disabled="1 < currentMonth">{{ __('finance.January') }}</option>
                                            <option value="2" :disabled="2 < currentMonth">{{ __('finance.February') }}</option>
                                            <option value="3" :disabled="3 < currentMonth">{{ __('finance.March') }}</option>
                                            <option value="4" :disabled="4 < currentMonth">{{ __('finance.April') }}</option>
                                            <option value="5" :disabled="5 < currentMonth">{{ __('finance.May') }}</option>
                                            <option value="6" :disabled="6 < currentMonth">{{ __('finance.June') }}</option>
                                            <option value="7" :disabled="7 < currentMonth">{{ __('finance.July') }}</option>
                                            <option value="8" :disabled="8 < currentMonth">{{ __('finance.August') }}</option>
                                            <option value="9" :disabled="9 < currentMonth">{{ __('finance.September') }}</option>
                                            <option value="10" :disabled="10 < currentMonth">{{ __('finance.October') }}</option>
                                            <option value="11" :disabled="11 < currentMonth">{{ __('finance.November') }}</option>
                                            <option value="12" :disabled="12 < currentMonth">{{ __('finance.December') }}</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Validation Error Messages -->
                                <div x-show="categoryForm.frequency === 'monthly' && parseInt(categoryForm.start_month) < currentMonth" class="mt-2 p-2 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800">
                                    <p class="text-sm text-red-600 dark:text-red-400 flex items-center gap-2">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span>{{ __('finance.Start month cannot be earlier than current month') }}</span>
                                    </p>
                                </div>
                                <div x-show="categoryForm.frequency === 'monthly' && parseInt(categoryForm.end_month) < currentMonth" class="mt-2 p-2 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800">
                                    <p class="text-sm text-red-600 dark:text-red-400 flex items-center gap-2">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span>{{ __('finance.End month cannot be earlier than current month') }}</span>
                                    </p>
                                </div>
                                <div x-show="categoryForm.frequency === 'monthly' && parseInt(categoryForm.end_month) < parseInt(categoryForm.start_month)" class="mt-2 p-2 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800">
                                    <p class="text-sm text-red-600 dark:text-red-400 flex items-center gap-2">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span>{{ __('finance.End month cannot be earlier than start month') }}</span>
                                    </p>
                                </div>
                            </div>

                            <!-- Row 5: Due Date -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Due Date') }} <span class="text-red-500">*</span></label>
                                    <select name="due_date" class="form-select-full" x-model="categoryForm.due_date" required>
                                        @for($day = 1; $day <= 28; $day++)
                                            <option value="{{ $day }}" :disabled="categoryForm.frequency === 'one_time' && {{ $day }} < currentDay">{{ $day }}</option>
                                        @endfor
                                    </select>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-show="categoryForm.frequency === 'monthly'">{{ __('finance.Every month on this day') }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-show="categoryForm.frequency === 'one_time'">{{ __('finance.Due date for this month') }}</p>
                                </div>
                            </div>
                            
                            <!-- Due Date Validation Error -->
                            <div x-show="categoryForm.frequency === 'one_time' && parseInt(categoryForm.due_date) < currentDay" class="p-2 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800">
                                <p class="text-sm text-red-600 dark:text-red-400 flex items-center gap-2">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <span>{{ __('finance.Due date cannot be earlier than today') }}</span>
                                </p>
                            </div>

                            <!-- Row 6: Description -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('finance.Description') }}</label>
                                <textarea name="description" rows="2" class="form-input-full" x-model="categoryForm.description" placeholder="{{ __('finance.Brief description of this fee...') }}"></textarea>
                            </div>

                            <!-- Row 5: Toggle Options -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-gray-200 dark:border-gray-700">
                                <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-900/50">
                                    <input type="checkbox" name="partial_status" value="1" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500 w-5 h-5" x-model="categoryForm.partial_status" id="partial_status_toggle">
                                    <label for="partial_status_toggle" class="cursor-pointer">
                                        <div class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('finance.Allow Partial') }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('finance.Partial payment') }}</div>
                                    </label>
                                </div>
                                <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-900/50">
                                    <input type="checkbox" name="discount_status" value="1" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500 w-5 h-5" x-model="categoryForm.discount_status" id="discount_status_toggle">
                                    <label for="discount_status_toggle" class="cursor-pointer">
                                        <div class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('finance.Allow Discount') }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('finance.Discount eligible') }}</div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between gap-3 p-5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
                            <button type="button" class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" @click="showCategoryModal = false">
                                <i class="fas fa-times mr-2"></i>{{ __('finance.Cancel') }}
                            </button>
                            <button type="submit" 
                                class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700"
                                :disabled="(categoryForm.frequency === 'monthly' && (parseInt(categoryForm.start_month) < currentMonth || parseInt(categoryForm.end_month) < currentMonth || parseInt(categoryForm.end_month) < parseInt(categoryForm.start_month))) || (categoryForm.frequency === 'one_time' && parseInt(categoryForm.due_date) < currentDay)"
                                :class="{'opacity-50 cursor-not-allowed': (categoryForm.frequency === 'monthly' && (parseInt(categoryForm.start_month) < currentMonth || parseInt(categoryForm.end_month) < currentMonth || parseInt(categoryForm.end_month) < parseInt(categoryForm.start_month))) || (categoryForm.frequency === 'one_time' && parseInt(categoryForm.due_date) < currentDay)}">
                                <i class="fas fa-save mr-2"></i>{{ __('finance.Save') }}
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
                        <form :action="approveConfirmData ? approveConfirmData.formAction : '#'" method="POST" id="approveConfirmForm" class="inline">
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
            const currentDay = new Date().getDate(); // 1-31
            const currentMonth = new Date().getMonth() + 1; // 1-12
            return {
                initialized: false,
                currentDay: currentDay,
                currentMonth: currentMonth,
                activeTab: new URLSearchParams(window.location.search).get('tab') || 'invoice',
                showPaymentModal: false,
                // showStructureModal removed
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

                categoryForm: {
                    name: '',
                    code: '',
                    description: '',
                    fee_type: 'Other',
                    amount: 0,
                    due_date: 1,
                    partial_status: false,
                    discount_status: false,
                    status: 'active',
                    frequency: 'monthly',
                    start_month: new Date().getMonth() + 1,
                    end_month: new Date().getMonth() + 1
                },
                categoryFormAction: '',
                categoryFormMethod: 'POST',
                gradeFeeForm: {
                    gradeName: '',
                    price_per_month: ''
                },
                gradeFeeFormAction: '',
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
                paymentMethodFormAction: '',
                paymentMethodFormMethod: 'POST',
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
                paymentData: {
                    payment_type: 'full',
                    payment_months: 1,
                    payment_method_id: '',
                    payment_date: '{{ now()->format('Y-m-d') }}',
                    reference_number: '',
                    notes: '',
                    fees: [],
                    hasOverdueFees: false,
                    subtotal: 0,
                    discount: 0,
                    discountPercent: 0,
                    total: 0
                },
                paymentPeriodOptions: @json($paymentPromotions ?? []),
                allPaymentPromotions: @json($paymentPromotions ?? []),
                
                init() {
                    // Mark as initialized to prevent errors during Alpine setup
                    this.$nextTick(() => {
                        this.initialized = true;
                    });
                },
                
                openPaymentModal(data) {
                    const student = data.student;
                    const invoice = data.invoice;
                    
                    this.paymentInfo = `${student.user?.name || 'Student'} (${student.student_identifier}) - ${student.grade?.name || ''} ${student.class_model?.name || ''}`;
                    this.paymentStudentId = student.id;
                    this.paymentInvoiceId = invoice?.id || '';
                    
                    // Safety check for fees
                    if (!invoice || !invoice.fees || !Array.isArray(invoice.fees)) {
                        console.error('Invoice fees not properly loaded:', invoice);
                        alert('{{ __('finance.Error loading invoice fees. Please refresh the page.') }}');
                        return;
                    }
                    
                    // Check if this is a remaining balance invoice (invoice number ends with -1 or -2)
                    const isRemainingInvoice = invoice.invoice_type === 'remaining_balance' || 
                                              (invoice.invoice_number && invoice.invoice_number.match(/-\d+$/));
                    
                    // Calculate remaining months based on batch end date
                    const batchEndDate = student.batch?.end_date || '{{ now()->addMonths(10)->format('Y-m-d') }}';
                    const remainingMonths = this.calculateRemainingMonths(batchEndDate);
                    
                    // Filter payment period options based on remaining months
                    this.paymentPeriodOptions = this.getAvailablePaymentOptions(remainingMonths);
                    
                    // Check for overdue fees
                    const hasOverdue = invoice.fees.some(fee => {
                        const dateToCompare = fee.due_date_raw || fee.due_date;
                        return new Date(dateToCompare) < new Date() && fee.remaining_amount > 0;
                    });
                    
                    // Check if any fee doesn't support partial payment
                    const hasNonPartialFee = invoice.fees.some(fee => {
                        return fee.supports_payment_period === false || fee.supports_payment_period === 0;
                    });
                    
                    // Check partial payment limit (max 2 partial payments)
                    // Count how many remaining balance invoices exist for this invoice's parent
                    const parentInvoiceId = invoice.parent_invoice_id || invoice.id;
                    const partialPaymentCount = invoice.partial_payment_count || 0;
                    const hasReachedPartialLimit = partialPaymentCount >= 2;
                    
                    // Initialize payment data
                    this.paymentData = {
                        payment_type: 'full',
                        payment_method_id: '',
                        payment_date: '{{ now()->format('Y-m-d') }}',
                        reference_number: '',
                        notes: '',
                        fees: invoice.fees.map(fee => ({
                            id: fee.id,
                            fee_name: fee.fee_name,
                            fee_name_mm: fee.fee_name_mm,
                            amount: parseFloat(fee.amount),
                            remaining_amount: parseFloat(fee.remaining_amount),
                            payment_amount: parseFloat(fee.remaining_amount),
                            payment_months: 1,
                            due_date: fee.due_date,
                            supports_payment_period: fee.supports_payment_period
                        })),
                        hasOverdueFees: hasOverdue,
                        hasNonPartialFee: hasNonPartialFee,
                        hasReachedPartialLimit: hasReachedPartialLimit,
                        isRemainingInvoice: isRemainingInvoice,
                        subtotal: 0,
                        discount: 0,
                        discountPercent: 0,
                        total: 0
                    };
                    
                    this.updatePaymentCalculation();
                    this.showPaymentModal = true;
                },
                
                updatePaymentCalculation() {
                    let subtotal = 0;
                    let totalDiscount = 0;
                    let maxDiscountPercent = 0;
                    
                    this.paymentData.fees.forEach(fee => {
                        let feeAmount = 0;
                        
                        if (this.paymentData.payment_type === 'full') {
                            // Each fee uses its own payment_months
                            feeAmount = fee.remaining_amount * (fee.payment_months || 1);
                        } else {
                            feeAmount = fee.payment_amount || 0;
                        }
                        
                        subtotal += feeAmount;
                        
                        // Apply discount ONLY to School Fee based on its payment_months
                        const isSchoolFee = fee.fee_name && fee.fee_name.toLowerCase().includes('school fee');
                        if (isSchoolFee) {
                            const discountOption = this.paymentPeriodOptions.find(opt => opt.months == (fee.payment_months || 1));
                            if (discountOption && discountOption.discount_percent > 0) {
                                const feeDiscount = feeAmount * (discountOption.discount_percent / 100);
                                totalDiscount += feeDiscount;
                                maxDiscountPercent = Math.max(maxDiscountPercent, parseFloat(discountOption.discount_percent));
                            }
                        }
                    });
                    
                    this.paymentData.subtotal = subtotal;
                    this.paymentData.discount = Math.round(totalDiscount);
                    this.paymentData.discountPercent = maxDiscountPercent;
                    this.paymentData.total = subtotal - Math.round(totalDiscount);
                },
                
                adjustFeeAmount(index, amount) {
                    const fee = this.paymentData.fees[index];
                    let newAmount = (fee.payment_amount || 0) + amount;
                    
                    // Ensure amount is within valid range (minimum 1000)
                    if (newAmount < 1000) {
                        newAmount = 1000;
                    } else if (newAmount > fee.remaining_amount) {
                        newAmount = fee.remaining_amount;
                    }
                    
                    // Round to nearest 500
                    newAmount = Math.round(newAmount / 500) * 500;
                    
                    // Final check after rounding
                    if (newAmount > fee.remaining_amount) {
                        newAmount = Math.floor(fee.remaining_amount / 500) * 500;
                    }
                    
                    fee.payment_amount = newAmount;
                    this.updatePaymentCalculation();
                },
                
                setFeeAmount(index, value) {
                    const fee = this.paymentData.fees[index];
                    let newAmount = parseInt(value) || 0;
                    
                    // Allow typing but don't enforce limits yet (will be validated on blur)
                    fee.payment_amount = newAmount;
                    this.updatePaymentCalculation();
                },
                
                validateFeeAmount(index) {
                    const fee = this.paymentData.fees[index];
                    let amount = fee.payment_amount;
                    
                    // Enforce minimum 1000
                    if (amount < 1000) {
                        amount = 1000;
                    }
                    
                    // Enforce maximum (remaining amount)
                    if (amount > fee.remaining_amount) {
                        amount = fee.remaining_amount;
                    }
                    
                    // Round to nearest 500
                    amount = Math.round(amount / 500) * 500;
                    
                    // Final check after rounding
                    if (amount < 1000) {
                        amount = 1000;
                    }
                    if (amount > fee.remaining_amount) {
                        amount = Math.floor(fee.remaining_amount / 500) * 500;
                    }
                    
                    fee.payment_amount = amount;
                    this.updatePaymentCalculation();
                },
                
                roundToNearest500(index) {
                    const fee = this.paymentData.fees[index];
                    let amount = fee.payment_amount || 0;
                    
                    // Round to nearest 500
                    amount = Math.round(amount / 500) * 500;
                    
                    // Ensure within bounds
                    if (amount < 0) {
                        amount = 0;
                    } else if (amount > fee.remaining_amount) {
                        amount = Math.floor(fee.remaining_amount / 500) * 500;
                    }
                    
                    fee.payment_amount = amount;
                    this.updatePaymentCalculation();
                },
                
                formatDueDate(dateString) {
                    if (!dateString) return 'N/A';
                    
                    // If already formatted
                    if (dateString.match(/^[A-Za-z]{3}\s\d{1,2},\s\d{4}$/)) {
                        return dateString;
                    }
                    
                    // Parse ISO date format
                    try {
                        const date = new Date(dateString);
                        const options = { year: 'numeric', month: 'short', day: 'numeric' };
                        return date.toLocaleDateString('en-US', options);
                    } catch (e) {
                        return dateString;
                    }
                },
                
                handleReceiptUpload(event) {
                    const file = event.target.files[0];
                    if (file && file.size > 2048 * 1024) {
                        alert('{{ __('finance.Receipt image must be less than 2MB') }}');
                        event.target.value = '';
                    }
                },
                
                async submitPayment() {
                    if (this.isSubmitting) return;
                    
                    // Validation
                    if (!this.paymentData.payment_method_id) {
                        alert('{{ __('finance.Please select a payment method') }}');
                        return;
                    }
                    
                    // Validate that all fees have payment months selected
                    const missingPaymentMonths = this.paymentData.fees.some(fee => !fee.payment_months || fee.payment_months < 1);
                    if (missingPaymentMonths) {
                        alert('{{ __('finance.Please select payment period for all fees') }}');
                        return;
                    }
                    
                    // Validate minimum amount for partial payments
                    if (this.paymentData.payment_type === 'partial') {
                        const belowMinimum = this.paymentData.fees.some(fee => fee.payment_amount > 0 && fee.payment_amount < 1000);
                        if (belowMinimum) {
                            alert('{{ __('finance.Minimum payment amount is 1,000 MMK per fee') }}');
                            return;
                        }
                    }
                    
                    if (this.paymentData.total <= 0) {
                        alert('{{ __('finance.Payment amount must be greater than 0') }}');
                        return;
                    }
                    
                    this.isSubmitting = true;
                    
                    try {
                        const formData = new FormData(this.$refs.paymentForm);
                        formData.append('_token', '{{ csrf_token() }}');
                        
                        const response = await fetch(`/student-fees/payment-system/invoices/${this.paymentInvoiceId}/process`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        });
                        
                        const result = await response.json();
                        
                        if (response.ok && result.success) {
                            // Redirect to receipt page
                            window.location.href = result.redirect_url;
                        } else {
                            alert(result.message || '{{ __('finance.Payment processing failed') }}');
                            this.isSubmitting = false;
                        }
                    } catch (error) {
                        console.error('Payment submission error:', error);
                        alert('{{ __('finance.Payment failed. Please try again.') }}');
                        this.isSubmitting = false;
                    }
                },
                

                
                openCategoryModal() {
                    this.categoryFormMethod = 'POST';
                    this.categoryFormAction = '{{ route('student-fees.categories.store') }}';
                    this.categoryForm = {
                        name: '',
                        code: '',
                        description: '',
                        fee_type: 'Other',
                        amount: '',
                        due_date: '1',
                        partial_status: false,
                        discount_status: false,
                        status: 'active'
                    };
                    this.showCategoryModal = true;
                },
                
                openEditCategoryModal(category) {
                    const currentMonth = new Date().getMonth() + 1;
                    this.categoryFormMethod = 'PUT';
                    this.categoryFormAction = '{{ url('student-fees/categories') }}/' + category.id;
                    this.categoryForm = {
                        name: category.name || '',
                        code: category.code || '',
                        description: category.description || '',
                        fee_type: category.fee_type || 'Other',
                        amount: category.amount || '',
                        due_date: category.due_date ? String(category.due_date) : '1',
                        partial_status: (category.partial_status === true || category.partial_status === 1 || category.partial_status === '1'),
                        discount_status: (category.discount_status === true || category.discount_status === 1 || category.discount_status === '1'),
                        status: category.status ? 'active' : 'inactive',
                        frequency: category.frequency?.frequency || 'monthly',
                        start_month: category.frequency?.start_month || currentMonth,
                        end_month: category.frequency?.end_month || currentMonth
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
                    // Use pre-computed attributes if available (from payment history)
                    // Otherwise compute from nested relationships (for new payments)
                    const guardianName = payment.guardian_name || payment.student?.guardians?.[0]?.user?.name || 'N/A';
                    
                    let className = payment.class_name || '-';
                    // If not pre-computed, try to compute from relationships
                    if (className === '-' && payment.student?.grade && payment.student?.classModel) {
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
                    const schoolLogo = '{{ asset("images/school-logo.png") }}';
                    
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
                                        <div class="signature-line">အမည် ${guardianName || '-----------------------'}</div>
                                        <div class="signature-line">လက်မှတ် -----------------------</div>
                                    </div>
                                    <div class="signature-box">
                                        <div class="signature-label">(ငွေလက်ခံသူ)</div>
                                        <div class="signature-line">အမည် ${this.receiptData.receptionist_name || '-----------------------'}</div>
                                        <div class="signature-line">လက်မှတ် -----------------------</div>
                                    </div>
                                </div>
                                
                                <div class="line">
                                    မှတ်ချက် ${paymentNotes || '---------------------------------------------------------------------------'}
                                </div>
                                
                                <div class="note-section">
                                   <div class="separator">
                                        _____________________________________________________________________________________________________________
                                    </div>
                                    <div class="note">
                                        မည်သည့်အကြောင်းနှင့်ဖြစ်စေ ပေးသွင်းပြီးသောအခကြေးငွေကို ပြန်လည်ထုတ်ပေးမည်မဟုတ်ပါ။
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
                    
                    // Watch for tab changes and update URL
                    this.$watch('activeTab', (value) => {
                        const url = new URL(window.location);
                        url.searchParams.set('tab', value);
                        window.history.pushState({}, '', url);
                    });
                    
                    // Listen for approve modal event
                    window.addEventListener('open-approve-modal', (event) => {
                        this.$nextTick(() => {
                            this.approveConfirmData = event.detail;
                            this.showApproveConfirmModal = true;
                        });
                    });
                },
                
                submitDelete() {
                    const form = document.getElementById('deleteConfirmForm');
                    form.submit();
                },
                
                /**
                 * Calculate remaining months from now to batch end date
                 */
                calculateRemainingMonths(batchEndDate) {
                    const now = new Date();
                    const endDate = new Date(batchEndDate);
                    
                    // Calculate difference in months
                    let months = (endDate.getFullYear() - now.getFullYear()) * 12;
                    months += endDate.getMonth() - now.getMonth();
                    
                    // Add 1 if we're not at the end of current month
                    if (endDate.getDate() >= now.getDate()) {
                        months += 1;
                    }
                    
                    return Math.max(1, months); // At least 1 month
                },
                
                /**
                 * Get available payment options based on remaining months
                 * Logic: [1, 3, 6, remaining] with smart filtering
                 */
                getAvailablePaymentOptions(remainingMonths) {
                    const allPromotions = this.allPaymentPromotions;
                    let availableMonths = [];
                    
                    // Always include 1 month
                    availableMonths.push(1);
                    
                    if (remainingMonths >= 10) {
                        // 10+ months: [1, 3, 6, remaining]
                        availableMonths = [1, 3, 6, remainingMonths];
                    } else if (remainingMonths >= 7 && remainingMonths <= 9) {
                        // 7-9 months: [1, 3, remaining]
                        availableMonths = [1, 3, remainingMonths];
                    } else if (remainingMonths >= 4 && remainingMonths <= 6) {
                        // 4-6 months: [1, 3, remaining] or [1, 3, 6]
                        if (remainingMonths === 6) {
                            availableMonths = [1, 3, 6];
                        } else {
                            availableMonths = [1, 3, remainingMonths];
                        }
                    } else if (remainingMonths === 3) {
                        // Exactly 3 months: [1, 3]
                        availableMonths = [1, 3];
                    } else if (remainingMonths === 2) {
                        // Exactly 2 months: [1, 2]
                        availableMonths = [1, 2];
                    } else {
                        // 1 month: [1]
                        availableMonths = [1];
                    }
                    
                    // Remove duplicates and sort
                    availableMonths = [...new Set(availableMonths)].sort((a, b) => a - b);
                    
                    // Build options array
                    const options = [];
                    availableMonths.forEach(months => {
                        const existing = allPromotions.find(p => p.months == months);
                        if (existing) {
                            options.push(existing);
                        } else {
                            // Create custom option for non-standard month counts
                            options.push({
                                months: months,
                                discount_percent: this.calculateDiscountForMonths(months),
                                is_active: true
                            });
                        }
                    });
                    
                    return options;
                },
                
                /**
                 * Calculate discount percentage for custom month counts
                 */
                calculateDiscountForMonths(months) {
                    const tiers = {
                        1: 0, 2: 0, 3: 5, 6: 10, 9: 15, 12: 20
                    };
                    
                    if (tiers[months] !== undefined) {
                        return tiers[months];
                    }
                    
                    // Interpolate for values between tiers
                    if (months < 3) return 0;
                    if (months < 6) return 5 + ((months - 3) / 3) * 5;
                    if (months < 9) return 10 + ((months - 6) / 3) * 5;
                    if (months < 12) return 15 + ((months - 9) / 3) * 5;
                    return 20;
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
                const response = await fetch(`/student-fees/payment-system/payments/${proofId}/details`);
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
            form.action = `/student-fees/payment-system/payments/${proofId}/reject`;
            modal.classList.remove('hidden');
        }

        function closeRejectProofModal() {
            const modal = document.getElementById('rejectProofModal');
            modal.classList.add('hidden');
            document.getElementById('rejectProofReason').value = '';
        }

        function openApproveProofModal(proofId, studentName, amount, paymentDate) {
            closePaymentProofModal();
            
            // Dispatch a custom event that Alpine can listen to
            const event = new CustomEvent('open-approve-modal', {
                detail: {
                    proofId: proofId,
                    studentName: studentName,
                    amount: amount,
                    paymentDate: paymentDate,
                    formAction: `/student-fees/payment-system/payments/${proofId}/approve`
                }
            });
            window.dispatchEvent(event);
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
        function closeInvoiceHistoryModal() {
            document.getElementById('invoiceHistoryModal').classList.add('hidden');
        }

        function confirmAction(url, title, message, confirmText) {
            if (typeof Alpine !== 'undefined') {
                window.dispatchEvent(new CustomEvent('confirm-show', {
                    detail: {
                        title: title,
                        message: message,
                        confirmText: confirmText,
                        cancelText: '{{ __('finance.Cancel') }}',
                        onConfirm: () => {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = url;
                            form.innerHTML = '@csrf';
                            document.body.appendChild(form);
                            form.submit();
                        }
                    }
                }));
            } else {
                if (confirm(message)) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;
                    form.innerHTML = '@csrf';
                    document.body.appendChild(form);
                    form.submit();
                }
            }
        }

        function confirmDeleteAction(url, title, message, confirmText) {
            if (typeof Alpine !== 'undefined') {
                window.dispatchEvent(new CustomEvent('confirm-show', {
                    detail: {
                        title: title,
                        message: message,
                        confirmText: confirmText,
                        cancelText: '{{ __('finance.Cancel') }}',
                        onConfirm: () => {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = url;
                            form.innerHTML = '@csrf @method("DELETE")';
                            document.body.appendChild(form);
                            form.submit();
                        }
                    }
                }));
            } else {
                if (confirm(message)) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;
                    form.innerHTML = '@csrf @method("DELETE")';
                    document.body.appendChild(form);
                    form.submit();
                }
            }
        }

        // Sync scrollbars between header and body tables
        document.addEventListener('DOMContentLoaded', function() {
            const headerWrapper = document.getElementById('headerTableWrapper');
            const mainWrapper = document.getElementById('mainTableWrapper');

            if (headerWrapper && mainWrapper) {
                // Sync scroll from header to body
                let isHeaderScrolling = false;
                headerWrapper.addEventListener('scroll', function() {
                    if (!isHeaderScrolling) {
                        isHeaderScrolling = true;
                        mainWrapper.scrollLeft = headerWrapper.scrollLeft;
                        setTimeout(() => { isHeaderScrolling = false; }, 10);
                    }
                });

                // Sync scroll from body to header
                let isMainScrolling = false;
                mainWrapper.addEventListener('scroll', function() {
                    if (!isMainScrolling) {
                        isMainScrolling = true;
                        headerWrapper.scrollLeft = mainWrapper.scrollLeft;
                        setTimeout(() => { isMainScrolling = false; }, 10);
                    }
                });
            }
        });
    </script>
</x-app-layout>
