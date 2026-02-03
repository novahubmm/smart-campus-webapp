<x-app-layout>
    <x-slot name="header">
        <x-page-header icon="fas fa-clipboard-check" iconBg="bg-purple-50 dark:bg-purple-900/30" iconColor="text-purple-700 dark:text-purple-200" :subtitle="__('Report Centre')" :title="__('Attendance Reports')" />
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-back-link :href="route('reports.index')" :text="__('Back to Report Centre')" />

            <div class="mt-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Generate Attendance Report') }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('Select options below to generate attendance register') }}</p>
                </div>

                <form id="attendanceForm" action="{{ route('reports.attendance.generate') }}" method="POST" target="_blank">
                    @csrf
                    <div class="p-6 space-y-6">
                        
                        <!-- Step 1: Report Type -->
                        <div class="space-y-3">
                            <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                                <span class="w-6 h-6 rounded-full bg-purple-600 text-white text-xs flex items-center justify-center">1</span>
                                {{ __('Select Register Type') }}
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <label class="cursor-pointer">
                                    <input type="radio" name="report_type" value="pr" class="peer sr-only" required>
                                    <div class="p-4 border-2 border-gray-200 dark:border-gray-700 rounded-lg peer-checked:border-purple-500 peer-checked:bg-purple-50 dark:peer-checked:bg-purple-900/20 hover:border-gray-300 transition-all">
                                        <div class="text-center">
                                            <i class="fas fa-clock text-2xl text-purple-600 mb-2"></i>
                                            <p class="font-semibold text-gray-900 dark:text-white">PR</p>
                                            <p class="text-xs text-gray-500">{{ __('Period Register') }}</p>
                                        </div>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="report_type" value="dar" class="peer sr-only">
                                    <div class="p-4 border-2 border-gray-200 dark:border-gray-700 rounded-lg peer-checked:border-purple-500 peer-checked:bg-purple-50 dark:peer-checked:bg-purple-900/20 hover:border-gray-300 transition-all">
                                        <div class="text-center">
                                            <i class="fas fa-calendar-day text-2xl text-blue-600 mb-2"></i>
                                            <p class="font-semibold text-gray-900 dark:text-white">DAR</p>
                                            <p class="text-xs text-gray-500">{{ __('Daily Attendance') }}</p>
                                        </div>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="report_type" value="mar" class="peer sr-only">
                                    <div class="p-4 border-2 border-gray-200 dark:border-gray-700 rounded-lg peer-checked:border-purple-500 peer-checked:bg-purple-50 dark:peer-checked:bg-purple-900/20 hover:border-gray-300 transition-all">
                                        <div class="text-center">
                                            <i class="fas fa-calendar-alt text-2xl text-green-600 mb-2"></i>
                                            <p class="font-semibold text-gray-900 dark:text-white">MAR</p>
                                            <p class="text-xs text-gray-500">{{ __('Monthly Register') }}</p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Step 2: Academic Selection -->
                        <div class="space-y-3">
                            <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                                <span class="w-6 h-6 rounded-full bg-purple-600 text-white text-xs flex items-center justify-center">2</span>
                                {{ __('Select Class') }}
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('Batch') }}</label>
                                    <select name="batch_id" id="att_batch_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm" required>
                                        <option value="">{{ __('Select') }}</option>
                                        @foreach($batches as $batch)
                                            <option value="{{ $batch->id }}">{{ $batch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('Grade') }}</label>
                                    <select name="grade_id" id="att_grade_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm" required>
                                        <option value="">{{ __('Select') }}</option>
                                        @foreach($grades as $grade)
                                            <option value="{{ $grade->id }}">{{ $grade->level }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('Class') }}</label>
                                    <select name="class_id" id="att_class_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm" required>
                                        <option value="">{{ __('Select Grade First') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Date Selection (Dynamic based on report type) -->
                        <div class="space-y-3">
                            <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                                <span class="w-6 h-6 rounded-full bg-purple-600 text-white text-xs flex items-center justify-center">3</span>
                                {{ __('Select Date/Period') }}
                            </label>
                            
                            <!-- For PR and DAR: Single Date -->
                            <div id="singleDateSection" class="hidden">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('Date') }}</label>
                                <input type="date" name="date" value="{{ now()->format('Y-m-d') }}" class="w-full sm:w-64 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm">
                            </div>

                            <!-- For MAR: Month/Year -->
                            <div id="monthYearSection" class="hidden">
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('Month') }}</label>
                                        <select name="month" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm">
                                            @foreach(range(1, 12) as $m)
                                                <option value="{{ $m }}" {{ now()->month == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('Year') }}</label>
                                        <select name="year" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm">
                                            @foreach(range(now()->year - 2, now()->year + 1) as $y)
                                                <option value="{{ $y }}" {{ now()->year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            <i class="fas fa-print mr-1"></i>{{ __('Report will open in new tab for printing') }}
                        </p>
                        <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold rounded-lg text-white bg-purple-600 hover:bg-purple-700 transition-colors">
                            <i class="fas fa-file-alt"></i>{{ __('Generate Report') }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Report Type Info -->
            <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-4">
                    <h4 class="font-semibold text-purple-900 dark:text-purple-100 mb-1">PR - Period Register</h4>
                    <p class="text-xs text-purple-700 dark:text-purple-300">{{ __('Attendance by period/subject for a single day') }}</p>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-1">DAR - Daily Register</h4>
                    <p class="text-xs text-blue-700 dark:text-blue-300">{{ __('Daily attendance with morning/afternoon sessions') }}</p>
                </div>
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                    <h4 class="font-semibold text-green-900 dark:text-green-100 mb-1">MAR - Monthly Register</h4>
                    <p class="text-xs text-green-700 dark:text-green-300">{{ __('Monthly summary with present/absent counts') }}</p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Show/hide date sections based on report type
        document.querySelectorAll('input[name="report_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const single = document.getElementById('singleDateSection');
                const monthly = document.getElementById('monthYearSection');
                
                if (this.value === 'mar') {
                    single.classList.add('hidden');
                    monthly.classList.remove('hidden');
                } else {
                    single.classList.remove('hidden');
                    monthly.classList.add('hidden');
                }
            });
        });

        // Load classes when grade changes
        document.getElementById('att_grade_id').addEventListener('change', async function() {
            const classSelect = document.getElementById('att_class_id');
            classSelect.innerHTML = '<option value="">{{ __("Select") }}</option>';
            
            if (this.value) {
                const res = await fetch(`/reports/api/classes/${this.value}`);
                const classes = await res.json();
                classes.forEach(c => {
                    classSelect.innerHTML += `<option value="${c.id}">${c.name}</option>`;
                });
            }
        });

        // Initialize: show single date by default
        document.getElementById('singleDateSection').classList.remove('hidden');
    </script>
    @endpush
</x-app-layout>
