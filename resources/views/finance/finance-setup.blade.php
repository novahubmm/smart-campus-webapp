<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('finance.Finance Setup') }}</h1>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 overflow-x-hidden">
            <!-- Welcome Header -->
            <div class="mb-8 rounded-2xl p-8 shadow-lg bg-gradient-to-br from-violet-500 to-purple-600 dark:from-violet-600 dark:to-purple-700">
                <div class="flex flex-col sm:flex-row items-center gap-6">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center text-3xl text-white flex-shrink-0">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="flex-1 text-center sm:text-left">
                        <h2 class="text-2xl sm:text-3xl font-bold mb-2 text-white">{{ __('finance.Welcome to Finance Setup') }}</h2>
                        <p class="text-base sm:text-lg text-white/90">{{ __('finance.Configure fee structures and expense categories for your finance management system.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Wizard Container -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm" x-data="financeSetupWizard()">
                <!-- Wizard Progress Bar -->
                <div class="border-b border-gray-200 dark:border-gray-700 p-5">
                    <div class="flex justify-between items-center relative max-w-md mx-auto">
                        <!-- Progress Line -->
                        <div class="absolute top-5 left-0 right-0 h-0.5 bg-gray-200 dark:bg-gray-700"></div>
                        <div class="absolute top-5 left-0 h-0.5 bg-violet-500 transition-all duration-300" :style="'width: ' + ((currentStep - 1) / 1 * 100) + '%'"></div>
                        
                        <!-- Step 1 -->
                        <div class="wizard-step-item flex flex-col items-center cursor-pointer relative z-10" @click="goToStep(1)">
                            <div class="step-indicator rounded-full w-10 h-10 flex items-center justify-center mb-2 transition-all duration-300"
                                :class="currentStep >= 1 ? 'bg-violet-500 text-white shadow-md' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400'">
                                <span class="font-semibold">1</span>
                            </div>
                            <span class="step-label text-sm transition-colors duration-300"
                                :class="currentStep >= 1 ? 'text-violet-600 dark:text-violet-400 font-semibold' : 'text-gray-600 dark:text-gray-400'">
                                {{ __('finance.Fee Structure') }}
                            </span>
                        </div>

                        <!-- Step 2 -->
                        <div class="wizard-step-item flex flex-col items-center cursor-pointer relative z-10" @click="goToStep(2)">
                            <div class="step-indicator rounded-full w-10 h-10 flex items-center justify-center mb-2 transition-all duration-300"
                                :class="currentStep >= 2 ? 'bg-violet-500 text-white shadow-md' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400'">
                                <span class="font-semibold">2</span>
                            </div>
                            <span class="step-label text-sm transition-colors duration-300"
                                :class="currentStep >= 2 ? 'text-violet-600 dark:text-violet-400 font-semibold' : 'text-gray-600 dark:text-gray-400'">
                                {{ __('finance.Expense Categories') }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row">
                    <!-- Main Content -->
                    <div class="flex-1 p-6 lg:p-8">
                        <form method="POST" action="{{ route('finance-setup.store') }}" id="financeSetupForm">
                            @csrf
                            <!-- Step 1: Fee Structure -->
                            <div class="wizard-step" x-show="currentStep === 1" x-transition>
                                <div class="flex items-center gap-2 px-3 py-2 bg-violet-50 dark:bg-violet-900/20 rounded-lg mb-6 w-fit">
                                    <i class="fas fa-graduation-cap text-violet-600 dark:text-violet-400 text-lg"></i>
                                    <h4 class="text-lg font-semibold text-violet-800 dark:text-violet-200">{{ __('finance.Tuition Fee by Grade') }}</h4>
                                </div>

                                <div class="space-y-4">
                                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ __('finance.Configure monthly tuition fees for each grade level') }}</p>
                                        
                                        <div id="tuitionFeeList" class="space-y-3">
                                            @php
                                                $existingFees = collect($setting->tuition_fee_by_grade ?? [])->keyBy('grade_id');
                                            @endphp
                                            @foreach($grades as $grade)
                                                <div class="tuition-fee-item bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
                                                        <div>
                                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('finance.Grade Name') }}</label>
                                                            <input type="text" value="{{ __('finance.Grade') }} {{ $grade->level }}" readonly
                                                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                                            <input type="hidden" name="grade_fee_grade_id[]" value="{{ $grade->id }}">
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('finance.Price/Month') }}</label>
                                                            <input type="number" name="grade_fee_amount[]" step="0.01" min="0" placeholder="e.g., 50000"
                                                                value="{{ $existingFees->get($grade->id)['amount'] ?? '' }}"
                                                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:border-violet-500 focus:ring-violet-500">
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="bg-violet-50 dark:bg-violet-900/20 border-l-4 border-violet-500 p-4 rounded-r-lg">
                                        <p class="text-violet-800 dark:text-violet-200 text-sm">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            {{ __('finance.Set the monthly tuition fee for each grade. These fees will be used when generating invoices.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 2: Expense Categories -->
                            <div class="wizard-step" x-show="currentStep === 2" x-transition>
                                <div class="flex items-center gap-2 px-3 py-2 bg-violet-50 dark:bg-violet-900/20 rounded-lg mb-6 w-fit">
                                    <i class="fas fa-list text-violet-600 dark:text-violet-400 text-lg"></i>
                                    <h4 class="text-lg font-semibold text-violet-800 dark:text-violet-200">{{ __('finance.Expense Categories') }}</h4>
                                </div>

                                <div class="space-y-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('finance.Default Expense Categories') }}</label>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            @php
                                                $defaultCategories = [
                                                    'salaries' => 'Salaries',
                                                    'utilities' => 'Utilities',
                                                    'maintenance' => 'Maintenance',
                                                    'supplies' => 'Supplies',
                                                    'equipment' => 'Equipment',
                                                    'transport' => 'Transport',
                                                    'marketing' => 'Marketing',
                                                    'training' => 'Training',
                                                    'electricity' => 'Electricity',
                                                    'fuel' => 'Fuel',
                                                    'teaching_aids' => 'Teaching Aids',
                                                    'electronic_devices' => 'Electronic Devices',
                                                ];
                                                // Ensure we have a collection and convert to lowercase strings
                                                $existingCategories = collect($expenseCategories)
                                                    ->map(function($c) {
                                                        return is_string($c) ? strtolower($c) : strtolower((string)$c);
                                                    })
                                                    ->filter()
                                                    ->all();
                                            @endphp
                                            @foreach($defaultCategories as $value => $label)
                                                <label class="flex items-center gap-3 cursor-pointer p-3 border-2 border-gray-200 dark:border-gray-700 rounded-lg transition-all hover:border-violet-400 dark:hover:border-violet-500 has-[:checked]:border-violet-500 has-[:checked]:bg-violet-50 dark:has-[:checked]:bg-violet-900/20">
                                                    <input type="checkbox" name="expense_categories[]" value="{{ $value }}" 
                                                        class="expense-category-checkbox rounded text-violet-600 border-gray-300 focus:ring-violet-500"
                                                        {{ in_array($value, $existingCategories) ? 'checked' : '' }}>
                                                    <span class="text-gray-900 dark:text-gray-100">{{ $label }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div>
                                        <label for="customExpenseCategories" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('finance.Custom Categories (comma-separated)') }}</label>
                                        @php
                                            $customCats = $expenseCategories->filter(fn($c) => !in_array(strtolower($c), array_keys($defaultCategories)))->implode(', ');
                                        @endphp
                                        <input type="text" name="custom_expense_categories" id="customExpenseCategories" 
                                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:border-violet-500 focus:ring-violet-500" 
                                            placeholder="e.g., Insurance, Legal, Consulting"
                                            value="{{ old('custom_expense_categories', $customCats) }}">
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('finance.Add additional expense categories if needed') }}</p>
                                    </div>

                                    <div class="bg-violet-50 dark:bg-violet-900/20 border-l-4 border-violet-500 p-4 rounded-r-lg">
                                        <p class="text-violet-800 dark:text-violet-200 text-sm">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            {{ __('finance.Select the expense categories that will be available when recording expenses. You can add custom categories as needed.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Sidebar with Tips -->
                    <div class="hidden lg:block w-72 flex-shrink-0 bg-gray-50 dark:bg-gray-900 border-l border-gray-200 dark:border-gray-700 p-6">
                        <div class="sticky top-6">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">{{ __('finance.Setup Guide') }}</h3>
                            <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                                <li class="flex items-start gap-2">
                                    <span class="text-violet-500 font-bold">•</span>
                                    <span>{{ __('finance.Complete each step to configure your finance management system') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-violet-500 font-bold">•</span>
                                    <span>{{ __('finance.You can modify these settings later from the settings page') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-violet-500 font-bold">•</span>
                                    <span>{{ __('finance.All configurations can be edited after setup') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-violet-500 font-bold">•</span>
                                    <span>{{ __('finance.Return to this page anytime to reconfigure') }}</span>
                                </li>
                            </ul>
                            <div class="mt-5 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <div class="bg-violet-50 dark:bg-violet-900/20 border border-violet-200 dark:border-violet-800 rounded-lg p-3 flex items-center gap-2 text-sm text-violet-700 dark:text-violet-300">
                                    <i class="fas fa-info-circle text-violet-500"></i>
                                    <span>{{ __('finance.Need help? Contact the admin support team') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Wizard Footer -->
                <div class="border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-5 rounded-b-xl flex justify-end gap-3">
                    <button type="button" x-show="currentStep > 1" @click="prevStep()"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-lg shadow-sm transition-all hover:bg-gray-50 dark:hover:bg-gray-700">
                        <i class="fas fa-arrow-left"></i>
                        {{ __('finance.Previous') }}
                    </button>
                    <button type="button" x-show="currentStep < 2" @click="nextStep()"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-violet-500 hover:bg-violet-600 text-white font-semibold rounded-lg shadow-md transition-all hover:shadow-lg">
                        {{ __('finance.Next') }}
                        <i class="fas fa-arrow-right"></i>
                    </button>
                    <button type="submit" form="financeSetupForm" x-show="currentStep === 2"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-violet-500 hover:bg-violet-600 text-white font-semibold rounded-lg shadow-md transition-all hover:shadow-lg">
                        <i class="fas fa-check"></i>
                        {{ __('finance.Complete Setup') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function financeSetupWizard() {
            return {
                currentStep: 1,
                goToStep(step) {
                    if (step <= this.currentStep || this.validateCurrentStep()) {
                        this.currentStep = step;
                    }
                },
                nextStep() {
                    if (this.validateCurrentStep() && this.currentStep < 2) {
                        this.currentStep++;
                    }
                },
                prevStep() {
                    if (this.currentStep > 1) {
                        this.currentStep--;
                    }
                },
                validateCurrentStep() {
                    if (this.currentStep === 1) {
                        // Check if at least one grade has a fee
                        const amounts = document.querySelectorAll('input[name="grade_fee_amount[]"]');
                        let hasAtLeastOne = false;
                        amounts.forEach(input => {
                            if (input.value && parseFloat(input.value) > 0) {
                                hasAtLeastOne = true;
                            }
                        });
                        if (!hasAtLeastOne) {
                            alert('{{ __('finance.Please set at least one grade fee') }}');
                            return false;
                        }
                    }
                    return true;
                }
            }
        }
    </script>
</x-app-layout>
