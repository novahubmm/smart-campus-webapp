<x-app-layout>
    <x-slot name="header">
        <x-page-header icon="fas fa-user-graduate" iconBg="bg-blue-50 dark:bg-blue-900/30" iconColor="text-blue-700 dark:text-blue-200" :subtitle="__('Report Centre')" :title="__('Student Reports')" />
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-back-link :href="route('reports.index')" :text="__('Back to Report Centre')" />

            <!-- Report Generator Card -->
            <div class="mt-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Generate Student Report') }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('Select options below to generate report') }}</p>
                </div>

                <form id="reportForm" action="{{ route('reports.students.generate') }}" method="POST" target="_blank">
                    @csrf
                    <div class="p-6 space-y-6">
                        
                        <!-- Step 1: Report Type -->
                        <div class="space-y-3">
                            <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                                <span class="w-6 h-6 rounded-full bg-blue-600 text-white text-xs flex items-center justify-center">1</span>
                                {{ __('Select Report Type') }}
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <label class="report-type-option relative cursor-pointer">
                                    <input type="radio" name="report_type" value="report_card" class="peer sr-only" required>
                                    <div class="p-4 border-2 border-gray-200 dark:border-gray-700 rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/20 hover:border-gray-300 transition-all">
                                        <div class="flex items-center gap-3">
                                            <i class="fas fa-id-card text-xl text-blue-600"></i>
                                            <div>
                                                <p class="font-semibold text-gray-900 dark:text-white">Report Card</p>
                                                <p class="text-xs text-gray-500">{{ __('Term/Annual grades') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                                <label class="report-type-option relative cursor-pointer">
                                    <input type="radio" name="report_type" value="qcpr" class="peer sr-only">
                                    <div class="p-4 border-2 border-gray-200 dark:border-gray-700 rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/20 hover:border-gray-300 transition-all">
                                        <div class="flex items-center gap-3">
                                            <i class="fas fa-chart-bar text-xl text-green-600"></i>
                                            <div>
                                                <p class="font-semibold text-gray-900 dark:text-white">QCPR</p>
                                                <p class="text-xs text-gray-500">{{ __('Quarterly report') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                                <label class="report-type-option relative cursor-pointer">
                                    <input type="radio" name="report_type" value="ccpr" class="peer sr-only">
                                    <div class="p-4 border-2 border-gray-200 dark:border-gray-700 rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/20 hover:border-gray-300 transition-all">
                                        <div class="flex items-center gap-3">
                                            <i class="fas fa-chart-line text-xl text-purple-600"></i>
                                            <div>
                                                <p class="font-semibold text-gray-900 dark:text-white">CCPR</p>
                                                <p class="text-xs text-gray-500">{{ __('Cumulative report') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Step 2: Academic Selection -->
                        <div class="space-y-3">
                            <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                                <span class="w-6 h-6 rounded-full bg-blue-600 text-white text-xs flex items-center justify-center">2</span>
                                {{ __('Select Academic Year & Grade') }}
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('Academic Year/Batch') }}</label>
                                    <select name="batch_id" id="batch_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm" required>
                                        <option value="">{{ __('Select Batch') }}</option>
                                        @foreach($batches as $batch)
                                            <option value="{{ $batch->id }}">{{ $batch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('Grade') }}</label>
                                    <select name="grade_id" id="grade_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm" required>
                                        <option value="">{{ __('Select Grade') }}</option>
                                        @foreach($grades as $grade)
                                            <option value="{{ $grade->id }}">{{ $grade->level }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Class & Student Selection -->
                        <div class="space-y-3">
                            <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                                <span class="w-6 h-6 rounded-full bg-blue-600 text-white text-xs flex items-center justify-center">3</span>
                                {{ __('Select Class & Student (Optional)') }}
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('Class') }} <span class="text-gray-400">({{ __('Optional') }})</span></label>
                                    <select name="class_id" id="class_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm">
                                        <option value="">{{ __('All Classes') }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('Student') }} <span class="text-gray-400">({{ __('Optional') }})</span></label>
                                    <select name="student_id" id="student_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm">
                                        <option value="">{{ __('All Students') }}</option>
                                    </select>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                <i class="fas fa-info-circle mr-1"></i>{{ __('Leave empty to generate for all students in selected grade/class') }}
                            </p>
                        </div>

                        <!-- Step 4: Term Selection (for Report Card) -->
                        <div id="termSection" class="space-y-3 hidden">
                            <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                                <span class="w-6 h-6 rounded-full bg-blue-600 text-white text-xs flex items-center justify-center">4</span>
                                {{ __('Select Term') }}
                            </label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <label class="cursor-pointer">
                                    <input type="radio" name="term" value="term1" class="peer sr-only">
                                    <div class="p-3 text-center border-2 border-gray-200 dark:border-gray-700 rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/20 hover:border-gray-300 transition-all">
                                        <p class="font-medium text-gray-900 dark:text-white">Term 1</p>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="term" value="term2" class="peer sr-only">
                                    <div class="p-3 text-center border-2 border-gray-200 dark:border-gray-700 rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/20 hover:border-gray-300 transition-all">
                                        <p class="font-medium text-gray-900 dark:text-white">Term 2</p>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="term" value="term3" class="peer sr-only">
                                    <div class="p-3 text-center border-2 border-gray-200 dark:border-gray-700 rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/20 hover:border-gray-300 transition-all">
                                        <p class="font-medium text-gray-900 dark:text-white">Term 3</p>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="term" value="annual" class="peer sr-only">
                                    <div class="p-3 text-center border-2 border-gray-200 dark:border-gray-700 rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/20 hover:border-gray-300 transition-all">
                                        <p class="font-medium text-gray-900 dark:text-white">Annual</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            <i class="fas fa-print mr-1"></i>{{ __('Report will open in new tab for printing') }}
                        </p>
                        <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                            <i class="fas fa-file-alt"></i>{{ __('Generate Report') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Show/hide term section based on report type
        document.querySelectorAll('input[name="report_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.getElementById('termSection').classList.toggle('hidden', this.value !== 'report_card');
            });
        });

        // Load classes when grade changes
        document.getElementById('grade_id').addEventListener('change', async function() {
            const classSelect = document.getElementById('class_id');
            const studentSelect = document.getElementById('student_id');
            classSelect.innerHTML = '<option value="">{{ __("All Classes") }}</option>';
            studentSelect.innerHTML = '<option value="">{{ __("All Students") }}</option>';
            
            if (this.value) {
                const res = await fetch(`/reports/api/classes/${this.value}`);
                const classes = await res.json();
                classes.forEach(c => {
                    classSelect.innerHTML += `<option value="${c.id}">${c.name}</option>`;
                });
            }
        });

        // Load students when class changes
        document.getElementById('class_id').addEventListener('change', async function() {
            const studentSelect = document.getElementById('student_id');
            studentSelect.innerHTML = '<option value="">{{ __("All Students") }}</option>';
            
            if (this.value) {
                const res = await fetch(`/reports/api/students/${this.value}`);
                const students = await res.json();
                students.forEach(s => {
                    studentSelect.innerHTML += `<option value="${s.id}">${s.name}</option>`;
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
