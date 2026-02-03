<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg">
                <i class="fas fa-clipboard-check"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('attendance.Collect Attendance') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    @className($class->name, $class->grade?->level)
                </h2>
            </div>
        </div>
    </x-slot>

    <!-- Toast Notification -->
    <div x-data="{ show: false, message: '', type: 'success' }" 
         x-show="show" 
         x-transition:enter="transform ease-out duration-300"
         x-transition:enter-start="translate-y-2 opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transform ease-in duration-200"
         x-transition:leave-start="translate-y-0 opacity-100"
         x-transition:leave-end="translate-y-2 opacity-0"
         @show-toast.window="show = true; message = $event.detail.message; type = $event.detail.type || 'success'; setTimeout(() => show = false, 3000)"
         class="fixed top-4 right-4 z-50"
         x-cloak>
        <div class="flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg"
             :class="type === 'success' ? 'bg-green-500 text-white' : (type === 'error' ? 'bg-red-500 text-white' : 'bg-blue-500 text-white')">
            <i class="fas" :class="type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-times-circle' : 'fa-info-circle')"></i>
            <span x-text="message" class="font-medium"></span>
            <button @click="show = false" class="ml-2 hover:opacity-75">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <div class="py-6 sm:py-10 overflow-x-hidden" x-data="collectClassAttendance(@js([
        'routes' => [
            'students' => route('student-attendance.collect-class-students', $class),
            'store' => route('student-attendance.collect-class-store', $class),
            'periodStatus' => route('student-attendance.collect-class-period-status', $class),
            'back' => route('student-attendance.create'),
        ],
        'today' => $today,
        'selectedDate' => $selectedDate,
        'className' => \App\Helpers\SectionHelper::formatFullClassName($class->name, $class->grade?->level),
        'periods' => $periods,
        'hasTimetable' => $hasTimetable,
        'periodsWithAttendance' => $periodsWithAttendance ?? [],
    ]))" x-init="initPage()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            @if(!$hasTimetable)
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4">
                <div class="flex items-center gap-3">
                    <i class="fas fa-exclamation-triangle text-amber-500 text-xl"></i>
                    <div>
                        <p class="font-medium text-amber-800 dark:text-amber-200">{{ __('attendance.No Active Timetable') }}</p>
                        <p class="text-sm text-amber-600 dark:text-amber-400">{{ __('attendance.Please set an active timetable for this class to collect period-based attendance.') }}</p>
                    </div>
                </div>
            </div>
            @endif

            <x-back-link 
                :href="route('student-attendance.create')"
                :text="__('attendance.Back to Attendance')"
            />

            <!-- Header with Date -->
            <div class="flex flex-wrap items-center justify-end gap-4">
                <div class="flex items-center gap-3">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('attendance.Date:') }}</label>
                    <input type="date" x-model="selectedDate" @change="onDateChange" :max="today"
                           class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500" />
                    <button type="button" @click="setToday" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold rounded-lg border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <i class="fas fa-calendar-day"></i>{{ __('attendance.Today') }}
                    </button>
                </div>
            </div>

            <!-- Period Tabs -->
            @if($hasTimetable && count($periods) > 0)
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-4">
                <div class="flex flex-wrap gap-2">
                    @foreach($periods as $period)
                    <button type="button" 
                            @click="selectPeriod({{ $period['number'] }})"
                            :class="selectedPeriod === {{ $period['number'] }} 
                                ? 'bg-blue-600 text-white border-blue-600' 
                                : (periodStatus[{{ $period['number'] }}] 
                                    ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 border-green-300 dark:border-green-700' 
                                    : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600 hover:border-blue-400')"
                            class="relative px-4 py-3 rounded-lg border-2 transition-all">
                        <div class="font-semibold">{{ $period['label'] }}</div>
                        <div class="text-xs opacity-75">{{ $period['time'] }}</div>
                        <template x-if="periodStatus[{{ $period['number'] }}]">
                            <span class="absolute -top-1 -right-1 w-4 h-4 bg-green-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-white text-[8px]"></i>
                            </span>
                        </template>
                    </button>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-3 shadow-sm">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-green-500 to-emerald-600 text-white flex items-center justify-center shadow-lg">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('attendance.Present') }}</p>
                        <p class="text-2xl font-bold text-green-600" x-text="presentCount"></p>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-3 shadow-sm">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-red-500 to-rose-600 text-white flex items-center justify-center shadow-lg">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('attendance.Absent') }}</p>
                        <p class="text-2xl font-bold text-red-600" x-text="absentCount"></p>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-3 shadow-sm">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-amber-500 to-orange-600 text-white flex items-center justify-center shadow-lg">
                        <i class="fas fa-calendar-minus"></i>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('attendance.Leave') }}</p>
                        <p class="text-2xl font-bold text-amber-600" x-text="leaveCount"></p>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 flex items-center gap-3 shadow-sm">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center shadow-lg">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('attendance.Total') }}</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="students.length"></p>
                    </div>
                </div>
            </div>

            <!-- Attendance Collection -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <!-- Header -->
                <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            <span x-text="className"></span>
                            <span x-show="selectedPeriod" class="text-blue-600 dark:text-blue-400">
                                - {{ __('attendance.Period') }} <span x-text="selectedPeriod"></span>
                            </span>
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            <span x-text="formatDate(selectedDate)"></span>
                            <span x-show="hasExistingAttendance" class="ml-2 text-green-600 dark:text-green-400">
                                <i class="fas fa-check-circle"></i> {{ __('attendance.Attendance recorded') }}
                            </span>
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" @click="markAllPresent" :disabled="!selectedPeriod" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold rounded-lg border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50">
                            <i class="fas fa-check-double text-green-500"></i>{{ __('attendance.All Present') }}
                        </button>
                        <button type="button" @click="markAllAbsent" :disabled="!selectedPeriod" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold rounded-lg border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50">
                            <i class="fas fa-times text-red-500"></i>{{ __('attendance.All Absent') }}
                        </button>
                        <button type="button" @click="saveAttendance" :disabled="saving || students.length === 0 || !selectedPeriod" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-60">
                            <i class="fas" :class="saving ? 'fa-spinner fa-spin' : 'fa-save'"></i>
                            <span x-text="hasExistingAttendance ? '{{ __('attendance.Update') }}' : '{{ __('attendance.Save') }}'"></span>
                        </button>
                    </div>
                </div>

                <!-- No Period Selected -->
                <div x-show="!selectedPeriod && hasTimetable" class="p-8 text-center">
                    <i class="fas fa-hand-pointer text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-500 dark:text-gray-400">{{ __('attendance.Select a period above to collect attendance.') }}</p>
                </div>

                <!-- Loading -->
                <div x-show="loading" class="p-8 text-center">
                    <i class="fas fa-spinner fa-spin text-2xl text-blue-500"></i>
                    <p class="text-gray-500 dark:text-gray-400 mt-2">{{ __('attendance.Loading students...') }}</p>
                </div>

                <!-- Student Grid -->
                <div x-show="!loading && selectedPeriod" class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <template x-for="student in students" :key="student.id">
                            <div class="bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl p-3 flex items-center gap-3 hover:border-blue-400 dark:hover:border-blue-500 transition-all">
                                <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 font-semibold text-sm flex-shrink-0" x-text="getInitials(student.name)"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-900 dark:text-white truncate text-sm" x-text="student.name"></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400" x-text="student.identifier || student.student_id || ''"></p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button type="button" @click="toggleStatus(student.id, 'present')" class="w-9 h-9 rounded-full border-2 flex items-center justify-center transition-all" :class="getStatus(student.id) === 'present' ? 'border-green-500 bg-green-50 dark:bg-green-900/30 text-green-600' : 'border-gray-300 dark:border-gray-600 text-gray-400 hover:border-green-400 hover:text-green-500'" title="{{ __('attendance.Present') }}">
                                        <i class="fas fa-check text-sm"></i>
                                    </button>
                                    <button type="button" @click="toggleStatus(student.id, 'absent')" class="w-9 h-9 rounded-full border-2 flex items-center justify-center transition-all" :class="getStatus(student.id) === 'absent' ? 'border-red-500 bg-red-50 dark:bg-red-900/30 text-red-600' : 'border-gray-300 dark:border-gray-600 text-gray-400 hover:border-red-400 hover:text-red-500'" title="{{ __('attendance.Absent') }}">
                                        <i class="fas fa-times text-sm"></i>
                                    </button>
                                    <button type="button" @click="toggleStatus(student.id, 'leave')" class="w-9 h-9 rounded-full border-2 flex items-center justify-center transition-all" :class="getStatus(student.id) === 'leave' ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/30 text-amber-600' : 'border-gray-300 dark:border-gray-600 text-gray-400 hover:border-amber-400 hover:text-amber-500'" title="{{ __('attendance.Leave') }}">
                                        <i class="fas fa-calendar-minus text-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div x-show="students.length === 0 && !loading" class="text-center py-8">
                        <i class="fas fa-users text-4xl text-gray-400 mb-4"></i>
                        <p class="text-gray-500 dark:text-gray-400">{{ __('attendance.No students found in this class.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function collectClassAttendance(config) {
            return {
                routes: config.routes,
                today: config.today,
                className: config.className,
                periods: config.periods || [],
                hasTimetable: config.hasTimetable,
                selectedDate: config.selectedDate || config.today,
                selectedPeriod: null,
                students: [],
                attendance: {},
                periodStatus: (config.periodsWithAttendance || []).reduce((acc, num) => { acc[num] = true; return acc; }, {}),
                loading: false,
                saving: false,
                hasExistingAttendance: false,

                initPage() {
                    // Auto-select first period if available
                    if (this.periods.length > 0) {
                        this.selectPeriod(this.periods[0].number);
                    }
                },

                get presentCount() {
                    return Object.values(this.attendance).filter(s => s === 'present').length;
                },

                get absentCount() {
                    return Object.values(this.attendance).filter(s => s === 'absent').length;
                },

                get leaveCount() {
                    return Object.values(this.attendance).filter(s => s === 'leave').length;
                },

                setToday() {
                    this.selectedDate = this.today;
                    this.onDateChange();
                },

                onDateChange() {
                    // Fetch period status for the new date
                    this.periodStatus = {};
                    
                    fetch(this.routes.periodStatus + '?date=' + this.selectedDate, {
                        headers: { 'Accept': 'application/json' }
                    })
                        .then(r => r.json())
                        .then(data => {
                            const periodsWithAttendance = data.periods_with_attendance || [];
                            periodsWithAttendance.forEach(periodNum => {
                                this.periodStatus[periodNum] = true;
                            });
                        })
                        .catch(() => {
                            // Ignore errors
                        });
                    
                    if (this.selectedPeriod) {
                        this.loadStudents();
                    }
                },

                selectPeriod(periodNumber) {
                    this.selectedPeriod = periodNumber;
                    this.loadStudents();
                },

                loadStudents() {
                    if (!this.selectedPeriod) return;
                    
                    this.loading = true;

                    fetch(this.routes.students + '?date=' + this.selectedDate + '&period_number=' + this.selectedPeriod, {
                        headers: { 'Accept': 'application/json' }
                    })
                        .then(r => r.json())
                        .then(data => {
                            this.students = data.students || [];
                            this.hasExistingAttendance = data.has_attendance || false;
                            this.attendance = {};
                            
                            // Update period status
                            if (this.hasExistingAttendance) {
                                this.periodStatus[this.selectedPeriod] = true;
                            }
                            
                            // Set attendance status - default to 'present' if no existing status
                            this.students.forEach(s => {
                                this.attendance[s.id] = s.status || 'present';
                            });
                        })
                        .catch(() => {
                            this.students = [];
                            this.attendance = {};
                        })
                        .finally(() => {
                            this.loading = false;
                        });
                },

                getStatus(studentId) {
                    return this.attendance[studentId] || 'present';
                },

                toggleStatus(studentId, status) {
                    this.attendance[studentId] = status;
                },

                markAllPresent() {
                    this.students.forEach(s => {
                        this.attendance[s.id] = 'present';
                    });
                },

                markAllAbsent() {
                    this.students.forEach(s => {
                        this.attendance[s.id] = 'absent';
                    });
                },

                saveAttendance() {
                    if (this.students.length === 0 || !this.selectedPeriod) return;

                    this.saving = true;

                    const records = this.students.map(s => ({
                        student_id: s.id,
                        status: this.attendance[s.id] || 'present',
                        remark: null,
                    }));

                    fetch(this.routes.store, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify({ 
                            date: this.selectedDate,
                            period_number: this.selectedPeriod,
                            records: records 
                        }),
                    })
                        .then(async r => {
                            const data = await r.json();
                            if (!r.ok) throw new Error(data.message || 'Failed to save');
                            
                            this.hasExistingAttendance = true;
                            this.periodStatus[this.selectedPeriod] = true;
                            window.dispatchEvent(new CustomEvent('show-toast', { 
                                detail: { 
                                    message: '{{ __('attendance.Attendance saved successfully!') }}', 
                                    type: 'success' 
                                } 
                            }));
                        })
                        .catch(e => {
                            window.dispatchEvent(new CustomEvent('show-toast', { 
                                detail: { 
                                    message: e.message || '{{ __('attendance.Error saving attendance.') }}', 
                                    type: 'error' 
                                } 
                            }));
                        })
                        .finally(() => {
                            this.saving = false;
                        });
                },

                formatDate(dateString) {
                    if (!dateString) return '—';
                    const d = new Date(dateString);
                    return d.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
                },

                getInitials(name) {
                    if (!name) return '?';
                    return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
                },
            };
        }
    </script>
</x-app-layout>
