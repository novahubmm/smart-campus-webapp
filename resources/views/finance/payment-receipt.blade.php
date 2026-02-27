<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 text-white shadow-lg">
                <i class="fas fa-receipt"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('finance.Payment') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('finance.Payment Receipt') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10 overflow-x-hidden">
        <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-4">
            <!-- Back Button -->
            <a href="{{ route('student-fees.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-700 dark:bg-gray-600 hover:bg-gray-800 dark:hover:bg-gray-700 text-white rounded-lg shadow transition-colors print:hidden">
                <i class="fas fa-arrow-left"></i>
                <span>{{ __('finance.Back to Fees') }}</span>
            </a>

            <!-- Receipt Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                <!-- Header -->
                <div class="bg-gradient-to-r from-green-500 to-emerald-600 p-8 text-white">
                    <div class="text-center">
                        <h1 class="text-3xl font-bold mb-2">{{ __('finance.Payment Receipt') }}</h1>
                    </div>
                </div>

                <!-- Receipt Body -->
                <div class="p-8 space-y-6">
                    <!-- Payment Status -->
                    <div class="flex items-center justify-center gap-2 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <i class="fas fa-check-circle text-green-600 dark:text-green-400 text-2xl"></i>
                        <span class="text-lg font-semibold text-green-800 dark:text-green-200">{{ __('finance.Payment Successful') }}</span>
                    </div>

                    <!-- Student Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase mb-3">{{ __('finance.Student Information') }}</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">{{ __('finance.Student Name') }}:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $payment->student->user->name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">{{ __('finance.Student ID') }}:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $payment->student->student_identifier }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">{{ __('finance.Class') }}:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $className }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">{{ __('finance.Guardian') }}:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $guardianName }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">{{ __('finance.Guardian Phone No') }}:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $payment->student->guardians->first()->user->phone ?? '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase mb-3">{{ __('finance.Payment Details') }}</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">{{ __('finance.Payment Date') }}:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $payment->payment_date->format('d M Y') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">{{ __('finance.Invoice Number') }}:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $payment->invoice->invoice_number ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">{{ __('finance.Payment Number') }}:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $payment->payment_number }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">{{ __('finance.Payment Method') }}:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $payment->paymentMethod->name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">{{ __('finance.Receptionist') }}:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ auth()->user()->name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Items -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase mb-3">{{ __('finance.Payment Items') }}</h3>
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900/50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">{{ __('finance.Fee Type') }}</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">{{ __('finance.Unit Price') }}</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">{{ __('finance.Month') }}</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">{{ __('finance.Amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                    @foreach($payment->feeDetails as $feeDetail)
                                    <tr>
                                        <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $feeDetail->fee_name }}</td>
                                        <td class="px-4 py-3 text-right text-gray-900 dark:text-white">{{ number_format($feeDetail->full_amount) }} MMK</td>
                                        <td class="px-4 py-3 text-center text-gray-900 dark:text-white">{{ $feeDetail->payment_months }}</td>
                                        <td class="px-4 py-3 text-right text-gray-900 dark:text-white">{{ number_format($feeDetail->paid_amount) }} MMK</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Total Amount -->
                    <div class="border-t-2 border-gray-300 dark:border-gray-600 pt-4 space-y-2">
                        <div class="flex justify-between items-center text-gray-700 dark:text-gray-300">
                            <span class="text-lg font-medium">{{ __('finance.Subtotal') }}:</span>
                            <span class="text-xl font-semibold">{{ number_format($subtotal) }} MMK</span>
                        </div>
                        
                        @if(!$isPartialPayment && $discountAmount > 0)
                        <div class="flex justify-between items-center text-green-600 dark:text-green-400">
                            <span class="text-lg font-medium">{{ __('finance.Discount') }}:</span>
                            <span class="text-xl font-semibold">- {{ number_format($discountAmount) }} MMK</span>
                        </div>
                        @endif
                        
                        <div class="flex justify-between items-center pt-2 border-t border-gray-200 dark:border-gray-700">
                            <span class="text-xl font-bold text-gray-900 dark:text-white">{{ __('finance.Total Amount Paid') }}:</span>
                            <span class="text-3xl font-bold text-green-600 dark:text-green-400">{{ number_format($payment->payment_amount) }} MMK</span>
                        </div>
                        
                        @if($isPartialPayment && $remainingAmount > 0)
                        <!-- Partial Payment Info -->
                        <div class="mt-4 p-4 bg-amber-50 dark:bg-amber-900/20 border-l-4 border-amber-500 rounded-r-lg">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 mt-0.5">
                                    <i class="fas fa-info-circle text-amber-600 dark:text-amber-400 text-xl"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-amber-900 dark:text-amber-200 mb-2">
                                        {{ __('finance.Partial Payment') }}
                                    </p>
                                    <p class="text-sm text-amber-800 dark:text-amber-300 mb-1">
                                        {{ __('finance.This is a partial payment. A new invoice has been created for the remaining balance.') }}
                                    </p>
                                    <div class="mt-3 space-y-1">
                                        <div class="flex justify-between items-center text-sm">
                                            <span class="text-amber-700 dark:text-amber-400">{{ __('finance.Remaining Amount') }}:</span>
                                            <span class="font-bold text-amber-900 dark:text-amber-200">{{ number_format($remainingAmount) }} MMK</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Notes -->
                    @if($payment->notes)
                    <div class="bg-gray-50 dark:bg-gray-900/30 rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">{{ __('finance.Notes') }}</h3>
                        <p class="text-gray-700 dark:text-gray-300">{{ $payment->notes }}</p>
                    </div>
                    @endif

                    <!-- Footer -->
                    <div class="text-center text-sm text-gray-500 dark:text-gray-400 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <p>{{ __('finance.Thank you for your payment') }}</p>
                        <p class="mt-1">{{ __('finance.This is a computer-generated receipt') }}</p>
                    </div>
                </div>
            </div>

            <!-- Print Button Below Receipt -->
            <div class="flex justify-center print:hidden">
                <button onclick="window.print()" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow-lg transition-colors text-lg font-semibold">
                    <i class="fas fa-print"></i>
                    <span>{{ __('finance.Print Receipt') }}</span>
                </button>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        @media print {
            /* Hide everything except the receipt */
            body * {
                visibility: hidden;
            }
            
            .print\:hidden {
                display: none !important;
            }
            
            /* Show only the receipt card */
            .bg-white.dark\:bg-gray-800.rounded-xl,
            .bg-white.dark\:bg-gray-800.rounded-xl * {
                visibility: visible;
            }
            
            /* A5 page setup - minimal margins to maximize space */
            @page {
                size: A5 portrait;
                margin: 5mm;
            }
            
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                line-height: 1.1 !important;
                height: auto !important;
                overflow: hidden !important;
            }
            
            /* Hide outer wrappers completely */
            .py-6.sm\:py-10,
            .py-6.px-4,
            .space-y-4 {
                padding: 0 !important;
                margin: 0 !important;
                height: auto !important;
            }
            
            /* Position receipt for printing */
            .bg-white.dark\:bg-gray-800.rounded-xl {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: 1px solid #000 !important;
                border-radius: 0 !important;
                background: white !important;
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
            }
            
            /* Header styling - black and white */
            .bg-gradient-to-r {
                background: white !important;
                border-bottom: 2px solid #000 !important;
                padding: 3mm 4mm !important;
                color: #000 !important;
            }
            
            .bg-gradient-to-r h1 {
                font-size: 12pt !important;
                margin-bottom: 0.5mm !important;
                color: #000 !important;
                font-weight: bold !important;
                line-height: 1.1 !important;
            }
            
            .bg-gradient-to-r p {
                font-size: 9pt !important;
                color: #000 !important;
                line-height: 1.1 !important;
            }
            
            /* Success badge - black and white */
            .bg-green-50 {
                background: white !important;
                border: 1px solid #000 !important;
                padding: 1.5mm 2mm !important;
                margin: 1.5mm 0 !important;
            }
            
            .bg-green-50 i {
                color: #000 !important;
                font-size: 10pt !important;
            }
            
            .bg-green-50 span {
                font-size: 10pt !important;
                color: #000 !important;
                font-weight: bold !important;
                line-height: 1.1 !important;
            }
            
            /* Content area - reduced spacing */
            .p-8 {
                padding: 3mm 4mm 2mm 4mm !important;
            }
            
            /* Section headings - reduced spacing */
            h3 {
                font-size: 8pt !important;
                margin-bottom: 1mm !important;
                margin-top: 1.5mm !important;
                text-transform: uppercase;
                font-weight: 600 !important;
                color: #000 !important;
                line-height: 1.1 !important;
            }
            
            /* Grid layout for info sections - reduced gap */
            .grid {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 2.5mm !important;
            }
            
            /* Student and Payment info - reduced spacing */
            .space-y-2 {
                margin-top: 0 !important;
            }
            
            .space-y-2 > div {
                font-size: 9pt !important;
                margin-bottom: 0.8mm !important;
                line-height: 1.1 !important;
                color: #000 !important;
            }
            
            .space-y-2 span {
                font-size: 9pt !important;
                color: #000 !important;
            }
            
            /* Table styling - compact */
            .border.border-gray-200 {
                border: 1px solid #000 !important;
                margin-top: 1mm !important;
            }
            
            table {
                font-size: 9pt !important;
                width: 100% !important;
                border-collapse: collapse !important;
            }
            
            table thead {
                background: white !important;
                border-bottom: 1px solid #000 !important;
            }
            
            table thead th {
                font-size: 8pt !important;
                font-weight: 600 !important;
                padding: 1mm !important;
                text-transform: uppercase;
                color: #000 !important;
                border-bottom: 1px solid #000 !important;
                line-height: 1.1 !important;
            }
            
            table tbody {
                background: white !important;
            }
            
            table tbody tr {
                border-bottom: 1px solid #ccc !important;
            }
            
            table tbody td {
                font-size: 9pt !important;
                padding: 1mm !important;
                color: #000 !important;
                line-height: 1.1 !important;
            }
            
            /* Total amount - reduced spacing */
            .border-t-2 {
                padding-top: 1.5mm !important;
                margin-top: 1.5mm !important;
                border-top: 2px solid #000 !important;
            }
            
            .border-t-2 span:first-child {
                font-size: 10pt !important;
                font-weight: bold !important;
                color: #000 !important;
                line-height: 1.1 !important;
            }
            
            .border-t-2 span:last-child {
                font-size: 11pt !important;
                font-weight: bold !important;
                color: #000 !important;
                line-height: 1.1 !important;
            }
            
            /* Notes section - black and white */
            .bg-gray-50 {
                background: white !important;
                border: 1px solid #000 !important;
                padding: 1.5mm !important;
                font-size: 9pt !important;
                margin-top: 1.5mm !important;
                color: #000 !important;
            }
            
            .bg-gray-50 h3 {
                color: #000 !important;
                margin-bottom: 0.5mm !important;
            }
            
            .bg-gray-50 p {
                color: #000 !important;
                line-height: 1.1 !important;
            }
            
            /* Footer text - reduced spacing */
            .text-center.text-sm {
                font-size: 7pt !important;
                padding-top: 1.5mm !important;
                padding-bottom: 0 !important;
                margin-top: 1.5mm !important;
                margin-bottom: 0 !important;
                border-top: 1px solid #000 !important;
                color: #000 !important;
            }
            
            .text-center.text-sm p {
                margin: 0.3mm 0 0 0 !important;
                color: #000 !important;
                line-height: 1.1 !important;
            }
            
            .text-center.text-sm p:last-child {
                margin-bottom: 0 !important;
                padding-bottom: 0 !important;
            }
            
            /* Remove extra spacing between sections */
            .space-y-6 > * + * {
                margin-top: 1.5mm !important;
            }
            
            /* Prevent page breaks */
            * {
                page-break-inside: avoid !important;
                page-break-after: avoid !important;
            }
            
            .bg-white.dark\:bg-gray-800.rounded-xl {
                page-break-after: avoid !important;
            }
            
            /* Force all text to black */
            * {
                color: #000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            /* Remove all background colors */
            .bg-green-600,
            .bg-emerald-600,
            .bg-green-800,
            .bg-green-200,
            .text-green-100,
            .text-green-600,
            .text-green-400,
            .text-green-800 {
                background: white !important;
                color: #000 !important;
            }
            
            /* Ensure icons are black */
            i {
                color: #000 !important;
            }
        }
    </style>
    @endpush
</x-app-layout>
