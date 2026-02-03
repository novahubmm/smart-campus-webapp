<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-cog"></i>
            </div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('attendance.Time-table & Attendance Setup') }}</h1>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 overflow-x-hidden">
            <!-- Welcome Header -->
            <div class="mb-8 rounded-2xl p-8 shadow-lg bg-gradient-to-br from-emerald-500 to-green-600 dark:from-emerald-600 dark:to-green-700">
                <div class="flex flex-col sm:flex-row items-center gap-6">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center text-3xl text-white flex-shrink-0">
                        <i class="fas fa-cog"></i>
                    </div>
                    <div class="flex-1 text-center sm:text-left">
                        <h2 class="text-2xl sm:text-3xl font-bold mb-2 text-white">{{ __('attendance.Welcome to Time-table & Attendance Setup') }}</h2>
                        <p class="text-base sm:text-lg text-white/90">{{ __('attendance.Configure your time-table format and time settings step by step.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Wizard Container -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm" x-data="timeTableSetupWizard()">
                <!-- Wizard Progress Bar -->
                <div class="border-b border-gray-200 dark:border-gray-700 p-5">
                    <div class="flex justify-between items-center relative max-w-md mx-auto">
                        <!-- Progress Line -->
                        <div class="absolute top-5 left-0 right-0 h-0.5 bg-gray-200 dark:bg-gray-700"></div>
                        <div class="absolute top-5 left-0 h-0.5 bg-emerald-500 transition-all duration-300" :style="'width: ' + ((currentStep - 1) / 1 * 100) + '%'"></div>
                        
                        <!-- Step 1 -->
                        <div class="wizard-step-item flex flex-col items-center cursor-pointer relative z-10" @click="goToStep(1)">
                            <div class="step-indicator rounded-full w-10 h-10 flex items-center justify-center mb-2 transition-all duration-300"
                                :class="currentStep >= 1 ? 'bg-emerald-500 text-white shadow-md' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400'">
                                <i class="fas fa-clock"></i>
                            </div>
                            <span class="step-label text-sm transition-colors duration-300"
                                :class="currentStep >= 1 ? 'text-emerald-600 dark:text-emerald-400 font-semibold' : 'text-gray-600 dark:text-gray-400'">
                                {{ __('attendance.Time-table Format') }}
                            </span>
                        </div>

                        <!-- Step 2 -->
                        <div class="wizard-step-item flex flex-col items-center cursor-pointer relative z-10" @click="goToStep(2)">
                            <div class="step-indicator rounded-full w-10 h-10 flex items-center justify-center mb-2 transition-all duration-300"
                                :class="currentStep >= 2 ? 'bg-emerald-500 text-white shadow-md' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400'">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <span class="step-label text-sm transition-colors duration-300"
                                :class="currentStep >= 2 ? 'text-emerald-600 dark:text-emerald-400 font-semibold' : 'text-gray-600 dark:text-gray-400'">
                                {{ __('attendance.Time Settings') }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row">
                    <!-- Main Content -->
                    <div class="flex-1 p-6 lg:p-8">
                        <form method="POST" action="{{ route('time-table-attendance-setup.store') }}" id="timeTableSetupForm">
                            @csrf
                            <!-- Step 1: Time-table Format -->
                            <div class="wizard-step" x-show="currentStep === 1" x-transition>
                                <div class="flex items-center gap-2 px-3 py-2 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg mb-6 w-fit">
                                    <i class="fas fa-clock text-emerald-600 dark:text-emerald-400 text-lg"></i>
                                    <h4 class="text-lg font-semibold text-emerald-800 dark:text-emerald-200">{{ __('attendance.Time-table Format Configuration') }}</h4>
                                </div>

                                <div class="space-y-6">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label for="number_of_periods_per_day" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                {{ __('attendance.Number of Periods per Day') }} <span class="text-red-500">*</span>
                                            </label>
                                            <select name="number_of_periods_per_day" id="number_of_periods_per_day" x-model="formData.periods"
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500">
                                                @for($i = 5; $i <= 10; $i++)
                                                    <option value="{{ $i }}" {{ old('number_of_periods_per_day', $defaults['number_of_periods_per_day']) == $i ? 'selected' : '' }}>{{ $i }} {{ __('attendance.Periods') }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div>
                                            <label for="minute_per_period" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                {{ __('attendance.Period Duration (minutes)') }} <span class="text-red-500">*</span>
                                            </label>
                                            <input type="number" name="minute_per_period" id="minute_per_period" x-model="formData.periodDuration"
                                                min="30" max="90" placeholder="e.g., 45"
                                                value="{{ old('minute_per_period', $defaults['minute_per_period']) }}"
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500" required>
                                        </div>
                                    </div>

                                    <div>
                                        <label for="break_duration" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            {{ __('attendance.Break Duration (minutes)') }}
                                        </label>
                                        <input type="number" name="break_duration" id="break_duration" x-model="formData.breakDuration"
                                            min="0" max="120" placeholder="e.g., 30"
                                            value="{{ old('break_duration', $defaults['break_duration']) }}"
                                            class="w-full sm:w-1/2 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500">
                                    </div>

                                    <div class="bg-emerald-50 dark:bg-emerald-900/20 border-l-4 border-emerald-500 p-4 rounded-r-lg">
                                        <p class="text-emerald-800 dark:text-emerald-200 text-sm">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            {{ __('attendance.Configure how many periods your school has per day and the duration of each period.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 2: Time Settings -->
                            <div class="wizard-step" x-show="currentStep === 2" x-transition>
                                <div class="flex items-center gap-2 px-3 py-2 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg mb-6 w-fit">
                                    <i class="fas fa-calendar-alt text-emerald-600 dark:text-emerald-400 text-lg"></i>
                                    <h4 class="text-lg font-semibold text-emerald-800 dark:text-emerald-200">{{ __('attendance.Time Settings') }}</h4>
                                </div>

                                <div class="space-y-6">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label for="school_start_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                {{ __('attendance.School Start Time') }} <span class="text-red-500">*</span>
                                            </label>
                                            <input type="time" name="school_start_time" id="school_start_time" x-model="formData.startTime"
                                                value="{{ old('school_start_time', $defaults['school_start_time']) }}"
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500" required>
                                        </div>
                                        <div>
                                            <label for="school_end_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                {{ __('attendance.School End Time') }} <span class="text-red-500">*</span>
                                            </label>
                                            <input type="time" name="school_end_time" id="school_end_time" x-model="formData.endTime"
                                                value="{{ old('school_end_time', $defaults['school_end_time']) }}"
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500" required>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('attendance.Working Days') }}</label>
                                        <div class="grid grid-cols-4 sm:grid-cols-7 gap-2">
                                            @php
                                                $days = [
                                                    'monday' => 'Mon',
                                                    'tuesday' => 'Tue',
                                                    'wednesday' => 'Wed',
                                                    'thursday' => 'Thu',
                                                    'friday' => 'Fri',
                                                    'saturday' => 'Sat',
                                                    'sunday' => 'Sun',
                                                ];
                                                $selectedDays = old('week_days', $defaults['week_days']);
                                            @endphp
                                            @foreach($days as $value => $label)
                                                <label class="flex flex-col items-center gap-1 cursor-pointer p-2 sm:p-3 border-2 border-gray-200 dark:border-gray-700 rounded-lg transition-all hover:border-emerald-400 dark:hover:border-emerald-500 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 dark:has-[:checked]:bg-emerald-900/20">
                                                    <input type="checkbox" name="week_days[]" value="{{ $value }}" 
                                                        class="working-day-checkbox rounded text-emerald-600 border-gray-300 focus:ring-emerald-500"
                                                        {{ in_array($value, $selectedDays) ? 'checked' : '' }}>
                                                    <span class="text-xs sm:text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="bg-emerald-50 dark:bg-emerald-900/20 border-l-4 border-emerald-500 p-4 rounded-r-lg">
                                        <p class="text-emerald-800 dark:text-emerald-200 text-sm">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            {{ __('attendance.Configure school timings and working days for accurate time-table and attendance tracking.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Sidebar with Tips -->
                    <div class="hidden lg:block w-72 flex-shrink-0 bg-gray-50 dark:bg-gray-900 border-l border-gray-200 dark:border-gray-700 p-6">
                        <div class="sticky top-6">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">{{ __('attendance.Setup Guide') }}</h3>
                            <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-bold">•</span>
                                    <span>{{ __('attendance.You can modify these settings later from the settings page') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-bold">•</span>
                                    <span>{{ __('attendance.All configurations can be edited after setup') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 font-bold">•</span>
                                    <span>{{ __('attendance.Return to this page anytime to reconfigure') }}</span>
                                </li>
                            </ul>
                            <div class="mt-5 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg p-3 flex items-center gap-2 text-sm text-emerald-700 dark:text-emerald-300">
                                    <i class="fas fa-info-circle text-emerald-500"></i>
                                    <span>{{ __('attendance.Need help? Contact the admin support team') }}</span>
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
                        {{ __('attendance.Previous') }}
                    </button>
                    <button type="button" x-show="currentStep < 2" @click="nextStep()"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-semibold rounded-lg shadow-md transition-all hover:shadow-lg">
                        {{ __('attendance.Next') }}
                        <i class="fas fa-arrow-right"></i>
                    </button>
                    <button type="submit" form="timeTableSetupForm" x-show="currentStep === 2"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-semibold rounded-lg shadow-md transition-all hover:shadow-lg">
                        <i class="fas fa-check"></i>
                        {{ __('attendance.Complete Setup') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function timeTableSetupWizard() {
            return {
                currentStep: 1,
                formData: {
                    periods: '{{ old('number_of_periods_per_day', $defaults['number_of_periods_per_day']) }}',
                    periodDuration: '{{ old('minute_per_period', $defaults['minute_per_period']) }}',
                    breakDuration: '{{ old('break_duration', $defaults['break_duration']) }}',
                    startTime: '{{ old('school_start_time', $defaults['school_start_time']) }}',
                    endTime: '{{ old('school_end_time', $defaults['school_end_time']) }}'
                },
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
                        const periods = document.getElementById('number_of_periods_per_day').value;
                        const duration = document.getElementById('minute_per_period').value;
                        if (!periods || !duration) {
                            alert('{{ __('attendance.Please fill all required fields') }}');
                            return false;
                        }
                    } else if (this.currentStep === 2) {
                        const startTime = document.getElementById('school_start_time').value;
                        const endTime = document.getElementById('school_end_time').value;
                        const workingDays = document.querySelectorAll('.working-day-checkbox:checked');
                        if (!startTime || !endTime || workingDays.length === 0) {
                            alert('{{ __('attendance.Please fill all required fields and select at least one working day') }}');
                            return false;
                        }
                    }
                    return true;
                }
            }
        }
    </script>
</x-app-layout>
