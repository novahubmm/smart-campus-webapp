<x-app-layout>
    <div class="p-6 space-y-6">
        <!-- Back Button & Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('guardian.utilities') }}" class="flex items-center text-gray-600 dark:text-gray-300">
                    <i class="fas fa-chevron-left mr-2"></i>
                    <span class="text-sm font-medium">{{ __('Back') }}</span>
                </a>
            </div>
            <h1 class="text-lg font-bold text-gray-800 dark:text-white">{{ __('School Fees') }}</h1>
            @if($student)
                <div
                    class="bg-gray-100 dark:bg-gray-800 px-3 py-1 rounded-full text-[10px] font-bold text-gray-500 uppercase">
                    {{ $student->user->name }}
                </div>
            @endif
        </div>

        <div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Summary Sidebar -->
                <div class="space-y-6">
                    <!-- Outstanding Balance Card -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                        <h3
                            class="text-[10px] font-bold text-gray-400 dark:text-gray-400 uppercase tracking-widest mb-4">
                            {{ __('PENDING PAYMENT') }}
                        </h3>
                        @if($pendingFee)
                            <div class="mb-6">
                                <span
                                    class="text-4xl font-extrabold text-gray-900 dark:text-white">{{ number_format($pendingFee['amount']) }}</span>
                                <span class="text-gray-500 dark:text-gray-400 font-bold ml-1">MMK</span>
                            </div>
                            <div class="space-y-3 mb-6">
                                @foreach($pendingFee['breakdown'] as $item)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">{{ $item['category'] }}</span>
                                        <span
                                            class="text-gray-900 dark:text-white font-semibold">{{ number_format($item['amount']) }}</span>
                                    </div>
                                @endforeach
                                <div
                                    class="pt-2 border-t border-gray-100 dark:border-gray-700 flex justify-between font-bold text-gray-900 dark:text-white">
                                    <span>{{ __('Total due') }}</span>
                                    <span>{{ number_format($pendingFee['amount']) }}</span>
                                </div>
                            </div>
                            <button
                                class="w-full py-4 bg-orange-600 hover:bg-orange-700 text-white rounded-xl font-bold shadow-lg shadow-orange-600/20 transition-all flex items-center justify-center gap-2">
                                <i class="fas fa-credit-card"></i>
                                {{ __('Pay Now') }}
                            </button>
                        @else
                            <div class="text-center py-4">
                                <div
                                    class="w-12 h-12 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center mx-auto mb-3 text-green-600 dark:text-green-400">
                                    <i class="fas fa-check-circle text-2xl"></i>
                                </div>
                                <p class="text-gray-900 dark:text-white font-bold">{{ __('No Pending Fees') }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ __('All your fees are up to date.') }}
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Payment Methods Card -->
                    <div
                        class="bg-gray-50 dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-6">
                        <h3 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-4">
                            {{ __('Accepted Methods') }}
                        </h3>
                        <div class="grid grid-cols-3 gap-2">
                            <div
                                class="bg-white dark:bg-gray-700 p-2 rounded-lg flex flex-col items-center gap-1 border border-gray-100 dark:border-gray-600">
                                <i class="fas fa-mobile-alt text-blue-500 text-sm"></i>
                                <span class="text-[10px] font-bold text-gray-700 dark:text-gray-300">Easy Pay</span>
                            </div>
                            <div
                                class="bg-white dark:bg-gray-700 p-2 rounded-lg flex flex-col items-center gap-1 border border-gray-100 dark:border-gray-600">
                                <i class="fas fa-university text-gray-500 text-sm"></i>
                                <span class="text-[10px] font-bold text-gray-700 dark:text-gray-300">Bank</span>
                            </div>
                            <div
                                class="bg-white dark:bg-gray-700 p-2 rounded-lg flex flex-col items-center gap-1 border border-gray-100 dark:border-gray-600">
                                <i class="fas fa-money-bill-wave text-green-500 text-sm"></i>
                                <span class="text-[10px] font-bold text-gray-700 dark:text-gray-300">Cash</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoices List -->
                <div class="md:col-span-2 space-y-6">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div
                            class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ __('Payment History') }}
                            </h3>
                            <div class="flex gap-2">
                                <button
                                    class="px-3 py-1 text-xs font-bold bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-md">{{ __('All') }}</button>
                                <button
                                    class="px-3 py-1 text-xs font-bold text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md">{{ __('Paid') }}</button>
                                <button
                                    class="px-3 py-1 text-xs font-bold text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md">{{ __('Pending') }}</button>
                            </div>
                        </div>

                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($invoices as $invoice)
                                <div
                                    class="p-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="w-10 h-10 rounded-full flex items-center justify-center {{ $invoice['status'] === 'paid' ? 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400' : 'bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400' }}">
                                            <i
                                                class="fas {{ $invoice['status'] === 'paid' ? 'fa-check' : 'fa-clock' }}"></i>
                                        </div>
                                        <div>
                                            <h4
                                                class="text-sm font-bold text-gray-900 dark:text-white leading-tight capitalize">
                                                {{ str_replace('_', ' ', $invoice['term']) }}
                                            </h4>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                {{ __('Due Date') }}:
                                                {{ \Carbon\Carbon::parse($invoice['due_date'])->format('d M Y') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-bold text-gray-900 dark:text-white">
                                            {{ number_format($invoice['amount']) }} MMK
                                        </p>
                                        <span
                                            class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider mt-1 {{ $invoice['status'] === 'paid' ? 'bg-green-500/10 text-green-600' : 'bg-orange-500/10 text-orange-600' }}">
                                            {{ $invoice['status'] }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="p-12 text-center">
                                    <i
                                        class="fas fa-file-invoice-dollar text-3xl text-gray-300 dark:text-gray-600 mb-3 block"></i>
                                    <p class="text-gray-500 dark:text-gray-400 font-medium">
                                        {{ __('No invoice history found.') }}
                                    </p>
                                </div>
                            @endforelse
                        </div>

                        @if($invoices->hasPages())
                            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                                {{ $invoices->links() }}
                            </div>
                        @endif
                    </div>

                    <!-- Note Card -->
                    <div
                        class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-100 dark:border-blue-900/30 flex gap-4">
                        <i class="fas fa-info-circle text-blue-500 mt-1"></i>
                        <p class="text-xs text-blue-700 dark:text-blue-300 leading-relaxed">
                            {{ __('Note: Online payments may take up to 24 hours to be processed and reflected in your payment history. For urgent issues, please contact the school accountant.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
</x-app-layout>