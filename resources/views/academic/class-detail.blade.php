<x-app-layout>
    <x-slot name="styles">
        <link rel="stylesheet" href="{{ asset('css/academic-management.css') }}?v={{ time() }}">
    </x-slot>
    <x-slot name="header">
        <x-page-header
            icon="fas fa-chalkboard"
            iconBg="bg-green-50 dark:bg-green-900/30"
            iconColor="text-green-700 dark:text-green-200"
            :subtitle="__('ongoing_class.Academic') . ' / ' . __('ongoing_class.Virtual Campus')"
        >
            {{ __('academic_management.Class Details') }}: @className($class->name, $class->grade?->level)
        </x-page-header>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-back-link 
                :href="isset($activityData) ? route('ongoing-class.index') : route('academic-management.index', ['tab' => 'classes'])"
                :text="isset($activityData) ? __('navigation.Ongoing Class') : __('academic_management.Back to Academic Management')"
            />

            @php
                $classTeacher = $class->teacher?->user?->name ?? '—';
            @endphp

            @if(!isset($activityData))
            <x-detail-header
                icon="fas fa-chalkboard"
                iconBg="bg-green-50 dark:bg-green-900/30"
                iconColor="text-green-600 dark:text-green-400"
                :title="\App\Helpers\SectionHelper::formatFullClassName($class->name, $class->grade?->level)"
                :subtitle="__('academic_management.Grade') . ': ' . ($class->grade?->name ?? '—')"
                :badge="$class->room?->name ?? __('academic_management.No Room')"
                badgeColor="active"
                :editRoute="null"
                :deleteRoute="route('academic-management.classes.destroy', $class->id)"
                :deleteText="__('academic_management.Delete Class')"
                :deleteTitle="__('academic_management.Delete Class')"
                :deleteMessage="__('academic_management.confirm_delete')"
            >
                <x-slot name="actions">
                    <button type="button" class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors" onclick="openModal('editClassModal')">
                        <i class="fas fa-edit"></i>
                        <span>{{ __('academic_management.Edit Class') }}</span>
                    </button>
                </x-slot>
            </x-detail-header>
            @endif


            {{-- Activity Summary Section --}}
            @if(isset($activityData))
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden"
                 x-data="activitySummary(@js([
                     'timetablePeriods' => $activityData['timetable_periods']->toArray(),
                     'classRemarks' => $activityData['class_remarks']->map(fn($r) => [
                         'id' => $r->id,
                         'period_id' => $r->period_id,
                         'remark' => $r->remark,
                         'type' => $r->type,
                         'teacher_name' => $r->teacher?->user?->name ?? '—',
                         'subject_name' => $r->subject?->name ?? null,
                         'time' => $r->created_at->format('H:i'),
                     ])->values()->toArray(),
                     'studentRemarks' => $activityData['student_remarks']->map(fn($r) => [
                         'id' => $r->id,
                         'period_id' => $r->period_id,
                         'remark' => $r->remark,
                         'type' => $r->type,
                         'student_name' => $r->student?->user?->name ?? '—',
                         'student_initial' => strtoupper(substr($r->student?->user?->name ?? '?', 0, 1)),
                         'time' => $r->created_at->format('H:i'),
                     ])->values()->toArray(),
                     'initialPeriodId' => $periodId ?? null,
                     'currentPeriodId' => $activityData['current_period_id'] ?? null,
                     'isToday' => $activityData['is_today'] ?? false,
                 ]))">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-green-50 dark:bg-green-900/30 flex items-center justify-center">
                            <i class="fas fa-chart-line text-green-600 dark:text-green-400"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ __('ongoing_class.Activity Summary') }}
                                <span x-show="selectedPeriod !== null" class="text-blue-600 dark:text-blue-400" x-text="' - P' + selectedPeriodNumber + ' - ' + selectedPeriodSubject"></span>
                                <span x-show="selectedPeriod === null" class="text-gray-500 dark:text-gray-400 text-base font-normal">- {{ __('ongoing_class.All Periods') }} ({{ $activityData['timetable_periods']->count() }})</span>
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $selectedDate->format('F d, Y') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="date" 
                               value="{{ $selectedDate->format('Y-m-d') }}" 
                               onchange="window.location.href='{{ route('ongoing-class.class-detail', $class->id) }}?date=' + this.value"
                               class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                </div>

                {{-- Period Filter Tabs - Like Exam Results Subject Filter --}}
                @if($activityData['timetable_periods']->count() > 0)
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex flex-wrap gap-2">
                        <button type="button" 
                                @click="selectPeriod(null)"
                                :class="selectedPeriod === null 
                                    ? 'bg-blue-600 text-white border-blue-600' 
                                    : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600 hover:border-blue-400'"
                                class="px-4 py-2 rounded-lg border-2 transition-all font-semibold text-sm">
                            {{ __('ongoing_class.All Periods') }}
                            <span class="ml-1 text-xs opacity-75">({{ $activityData['timetable_periods']->count() }})</span>
                        </button>
                        @foreach($activityData['timetable_periods'] as $tp)
                            @php
                                $hasAttendance = $tp['has_attendance'] ?? false;
                            @endphp
                            <button type="button" 
                                    @click="selectPeriod('{{ $tp['id'] }}')"
                                    :class="selectedPeriod === '{{ $tp['id'] }}' 
                                        ? 'bg-blue-600 text-white border-blue-600' 
                                        : '{{ $hasAttendance ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 border-green-300 dark:border-green-700' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600 hover:border-blue-400' }}'"
                                    class="relative px-4 py-2 rounded-lg border-2 transition-all">
                                <div class="font-semibold text-sm">P{{ $tp['period_number'] }} - {{ $tp['subject_name'] }}</div>
                                <div class="text-xs opacity-75">{{ $tp['starts_at'] }} - {{ $tp['ends_at'] }}</div>
                                @if($hasAttendance)
                                    <span class="absolute -top-1 -right-1 w-5 h-5 bg-green-500 rounded-full flex items-center justify-center">
                                        <i class="fas fa-check text-white text-[10px]"></i>
                                    </span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="p-6 space-y-6">
                    {{-- Attendance Summary --}}
                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-5">
                        <div class="flex items-center gap-2 mb-4">
                            <i class="fas fa-user-check text-green-600 dark:text-green-400"></i>
                            <h4 class="font-semibold text-gray-900 dark:text-white">{{ __('ongoing_class.Attendance') }}</h4>
                            <span class="ml-auto text-sm text-gray-500 dark:text-gray-400" x-text="attendanceSummaryText"></span>
                        </div>
                        
                        {{-- Period-specific attendance numbers --}}
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-3 text-center border border-green-200 dark:border-green-800">
                                <div class="text-2xl font-bold text-green-600 dark:text-green-400" x-text="periodAttendance.present"></div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('ongoing_class.Present') }}</div>
                            </div>
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-3 text-center border border-red-200 dark:border-red-800">
                                <div class="text-2xl font-bold text-red-600 dark:text-red-400" x-text="periodAttendance.absent"></div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('ongoing_class.Absent') }}</div>
                            </div>
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-3 text-center border border-blue-200 dark:border-blue-800">
                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400" x-text="periodAttendance.leave"></div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('ongoing_class.On Leave') }}</div>
                            </div>
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-3 text-center border border-yellow-200 dark:border-yellow-800">
                                <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400" x-text="periodAttendance.late"></div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('ongoing_class.Late') }}</div>
                            </div>
                        </div>

                        {{-- Link to detailed attendance page --}}
                        <div class="text-center">
                            <a :href="`{{ url('attendance/students/class') }}/{{ $class->id }}?date={{ $selectedDate->format('Y-m-d') }}`"
                               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors rounded-lg border border-blue-200 dark:border-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/20">
                                <i class="fas fa-external-link-alt"></i>
                                {{ __('ongoing_class.View Detailed Attendance') }}
                            </a>
                        </div>
                    </div>

                    {{-- Two Column Layout for Remarks --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Class Remarks --}}
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-5">
                            <div class="flex items-center gap-2 mb-4">
                                <i class="fas fa-comment-dots text-blue-600 dark:text-blue-400"></i>
                                <h4 class="font-semibold text-gray-900 dark:text-white">{{ __('ongoing_class.Class Remarks') }}</h4>
                                <span class="bg-blue-100 dark:bg-blue-800 text-blue-700 dark:text-blue-300 text-xs font-medium px-2 py-0.5 rounded-full"
                                      x-text="filteredClassRemarks.length">
                                </span>
                                <button type="button" 
                                        @click="openAddClassRemarkModal()"
                                        class="ml-auto inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-plus"></i>
                                    {{ __('ongoing_class.Add Remark') }}
                                </button>
                            </div>
                            <template x-if="filteredClassRemarks.length > 0">
                                <div class="space-y-3 max-h-64 overflow-y-auto">
                                    <template x-for="remark in filteredClassRemarks" :key="remark.id">
                                        <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-blue-200 dark:border-blue-800">
                                            <div class="flex items-start gap-2">
                                                <span x-show="remark.type === 'positive'" class="text-green-500 mt-0.5">👍</span>
                                                <span x-show="remark.type === 'concern'" class="text-yellow-500 mt-0.5">⚠️</span>
                                                <span x-show="remark.type !== 'positive' && remark.type !== 'concern'" class="text-blue-500 mt-0.5">📝</span>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm text-gray-700 dark:text-gray-300" x-text="remark.remark"></p>
                                                    <div class="flex items-center gap-2 mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                        <span x-text="remark.teacher_name"></span>
                                                        <template x-if="remark.subject_name">
                                                            <span>•</span>
                                                        </template>
                                                        <span x-show="remark.subject_name" x-text="remark.subject_name"></span>
                                                        <span>•</span>
                                                        <span x-text="remark.time"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <template x-if="filteredClassRemarks.length === 0">
                                <div class="text-center py-4 text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-comment-slash text-2xl mb-2 opacity-50"></i>
                                    <p class="text-sm">{{ __('ongoing_class.No class remarks for this period') }}</p>
                                </div>
                            </template>
                        </div>

                        {{-- Student Remarks --}}
                        <div class="bg-purple-50 dark:bg-purple-900/20 rounded-xl p-5">
                            <div class="flex items-center gap-2 mb-4">
                                <i class="fas fa-user-edit text-purple-600 dark:text-purple-400"></i>
                                <h4 class="font-semibold text-gray-900 dark:text-white">{{ __('ongoing_class.Student Remarks') }}</h4>
                                <span class="bg-purple-100 dark:bg-purple-800 text-purple-700 dark:text-purple-300 text-xs font-medium px-2 py-0.5 rounded-full"
                                      x-text="filteredStudentRemarks.length">
                                </span>
                                <button type="button" 
                                        @click="openAddStudentRemarkModal()"
                                        class="ml-auto inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg text-white bg-purple-600 hover:bg-purple-700 transition-colors">
                                    <i class="fas fa-plus"></i>
                                    {{ __('ongoing_class.Add Remark') }}
                                </button>
                            </div>
                            <template x-if="filteredStudentRemarks.length > 0">
                                <div class="space-y-3 max-h-64 overflow-y-auto">
                                    <template x-for="remark in filteredStudentRemarks" :key="remark.id">
                                        <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border"
                                             :class="remark.type === 'positive' ? 'border-green-200 dark:border-green-800' : (remark.type === 'concern' ? 'border-yellow-200 dark:border-yellow-800' : 'border-blue-200 dark:border-blue-800')">
                                            <div class="flex items-start gap-3">
                                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold flex-shrink-0"
                                                     :class="remark.type === 'positive' ? 'bg-green-100 dark:bg-green-800 text-green-700 dark:text-green-300' : (remark.type === 'concern' ? 'bg-yellow-100 dark:bg-yellow-800 text-yellow-700 dark:text-yellow-300' : 'bg-blue-100 dark:bg-blue-800 text-blue-700 dark:text-blue-300')"
                                                     x-text="remark.student_initial">
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="remark.student_name"></p>
                                                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-1" x-text="remark.remark"></p>
                                                    <div class="flex items-center gap-2 mt-2">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                                              :class="remark.type === 'positive' ? 'bg-green-100 dark:bg-green-800 text-green-700 dark:text-green-300' : (remark.type === 'concern' ? 'bg-yellow-100 dark:bg-yellow-800 text-yellow-700 dark:text-yellow-300' : 'bg-blue-100 dark:bg-blue-800 text-blue-700 dark:text-blue-300')"
                                                              x-text="remark.type.charAt(0).toUpperCase() + remark.type.slice(1)">
                                                        </span>
                                                        <span class="text-xs text-gray-500 dark:text-gray-400" x-text="remark.time"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <template x-if="filteredStudentRemarks.length === 0">
                                <div class="text-center py-4 text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-user-slash text-2xl mb-2 opacity-50"></i>
                                    <p class="text-sm">{{ __('ongoing_class.No student remarks for this period') }}</p>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Curriculum Updates & Homework --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Curriculum Updates --}}
                        <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-xl p-5">
                            <div class="flex items-center gap-2 mb-4">
                                <i class="fas fa-book-open text-indigo-600 dark:text-indigo-400"></i>
                                <h4 class="font-semibold text-gray-900 dark:text-white">{{ __('ongoing_class.Curriculum Progress') }}</h4>
                                <span class="ml-auto bg-indigo-100 dark:bg-indigo-800 text-indigo-700 dark:text-indigo-300 text-xs font-medium px-2 py-0.5 rounded-full"
                                      x-text="filteredCurriculumUpdates.length">
                                </span>
                            </div>
                            <template x-if="filteredCurriculumUpdates.length > 0">
                                <div class="space-y-2 max-h-48 overflow-y-auto">
                                    <template x-for="progress in filteredCurriculumUpdates" :key="progress.id">
                                        <div class="flex items-center gap-3 bg-white dark:bg-gray-800 rounded-lg p-3 border border-indigo-200 dark:border-indigo-800">
                                            <div class="w-6 h-6 rounded-full bg-green-100 dark:bg-green-800 flex items-center justify-center flex-shrink-0">
                                                <i class="fas fa-check text-green-600 dark:text-green-400 text-xs"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate" x-text="progress.topic_title"></p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400" x-text="progress.subject_name"></p>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <template x-if="filteredCurriculumUpdates.length === 0">
                                <div class="text-center py-4 text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-book text-2xl mb-2 opacity-50"></i>
                                    <p class="text-sm">{{ __('ongoing_class.No curriculum updates for this period') }}</p>
                                </div>
                            </template>
                        </div>

                        {{-- Homework Assigned --}}
                        <div class="bg-amber-50 dark:bg-amber-900/20 rounded-xl p-5">
                            <div class="flex items-center gap-2 mb-4">
                                <i class="fas fa-tasks text-amber-600 dark:text-amber-400"></i>
                                <h4 class="font-semibold text-gray-900 dark:text-white">{{ __('ongoing_class.Homework Assigned') }}</h4>
                                <span class="ml-auto bg-amber-100 dark:bg-amber-800 text-amber-700 dark:text-amber-300 text-xs font-medium px-2 py-0.5 rounded-full"
                                      x-text="filteredHomeworkAssigned.length">
                                </span>
                            </div>
                            <template x-if="filteredHomeworkAssigned.length > 0">
                                <div class="space-y-2 max-h-48 overflow-y-auto">
                                    <template x-for="hw in filteredHomeworkAssigned" :key="hw.id">
                                        <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-amber-200 dark:border-amber-800">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="hw.title"></p>
                                            <div class="flex items-center gap-2 mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                <span x-text="hw.subject_name"></span>
                                                <span>•</span>
                                                <span>{{ __('ongoing_class.Due') }}: <span x-text="hw.due_date"></span></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <template x-if="filteredHomeworkAssigned.length === 0">
                                <div class="text-center py-4 text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-clipboard text-2xl mb-2 opacity-50"></i>
                                    <p class="text-sm">{{ __('ongoing_class.No homework assigned for this period') }}</p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Add Class Remark Modal --}}
                <div x-show="showAddClassRemarkModal" 
                     x-cloak
                     class="fixed inset-0 z-50 overflow-y-auto"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0">
                    <div class="flex items-center justify-center min-h-screen p-4">
                        <div class="fixed inset-0 bg-black/60" @click="closeAddClassRemarkModal()"></div>
                        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-lg"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95">
                            
                            {{-- Modal Header --}}
                            <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                                        <i class="fas fa-comment-dots text-blue-600 dark:text-blue-400"></i>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('ongoing_class.Add Class Remark') }}</h3>
                                </div>
                                <button type="button" @click="closeAddClassRemarkModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>

                            {{-- Modal Body --}}
                            <div class="p-5 space-y-4">
                                {{-- Period Selection --}}
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('ongoing_class.Period') }}</label>
                                    <select x-model="classRemarkForm.period_id" 
                                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">{{ __('ongoing_class.Select Period (Optional)') }}</option>
                                        @foreach($activityData['timetable_periods'] as $tp)
                                            <option value="{{ $tp['id'] }}">P{{ $tp['period_number'] }} - {{ $tp['subject_name'] }} ({{ $tp['starts_at'] }} - {{ $tp['ends_at'] }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Remark Type --}}
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">{{ __('ongoing_class.Remark Type') }}</label>
                                    <div class="flex flex-wrap gap-2">
                                        <label class="flex items-center gap-2 px-4 py-2 rounded-lg border-2 cursor-pointer transition-all"
                                               :class="classRemarkForm.type === 'note' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30' : 'border-gray-300 dark:border-gray-600 hover:border-blue-300'">
                                            <input type="radio" x-model="classRemarkForm.type" value="note" class="sr-only">
                                            <span class="text-blue-500">📝</span>
                                            <span class="text-sm font-medium" :class="classRemarkForm.type === 'note' ? 'text-blue-700 dark:text-blue-300' : 'text-gray-700 dark:text-gray-300'">{{ __('ongoing_class.Note') }}</span>
                                        </label>
                                        <label class="flex items-center gap-2 px-4 py-2 rounded-lg border-2 cursor-pointer transition-all"
                                               :class="classRemarkForm.type === 'positive' ? 'border-green-500 bg-green-50 dark:bg-green-900/30' : 'border-gray-300 dark:border-gray-600 hover:border-green-300'">
                                            <input type="radio" x-model="classRemarkForm.type" value="positive" class="sr-only">
                                            <span class="text-green-500">👍</span>
                                            <span class="text-sm font-medium" :class="classRemarkForm.type === 'positive' ? 'text-green-700 dark:text-green-300' : 'text-gray-700 dark:text-gray-300'">{{ __('ongoing_class.Positive') }}</span>
                                        </label>
                                        <label class="flex items-center gap-2 px-4 py-2 rounded-lg border-2 cursor-pointer transition-all"
                                               :class="classRemarkForm.type === 'concern' ? 'border-yellow-500 bg-yellow-50 dark:bg-yellow-900/30' : 'border-gray-300 dark:border-gray-600 hover:border-yellow-300'">
                                            <input type="radio" x-model="classRemarkForm.type" value="concern" class="sr-only">
                                            <span class="text-yellow-500">⚠️</span>
                                            <span class="text-sm font-medium" :class="classRemarkForm.type === 'concern' ? 'text-yellow-700 dark:text-yellow-300' : 'text-gray-700 dark:text-gray-300'">{{ __('ongoing_class.Concern') }}</span>
                                        </label>
                                    </div>
                                </div>

                                {{-- Remark Text --}}
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('ongoing_class.Remark') }} <span class="text-red-500">*</span></label>
                                    <textarea x-model="classRemarkForm.remark" 
                                              rows="4" 
                                              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500"
                                              placeholder="{{ __('ongoing_class.Enter your remark about the class...') }}"></textarea>
                                </div>
                            </div>

                            {{-- Modal Footer --}}
                            <div class="flex items-center justify-end gap-3 p-5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
                                <button type="button" 
                                        @click="closeAddClassRemarkModal()" 
                                        class="px-4 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    {{ __('components.Cancel') }}
                                </button>
                                <button type="button" 
                                        @click="submitClassRemark()"
                                        :disabled="isSubmittingClassRemark || !classRemarkForm.remark.trim()"
                                        class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fas fa-save mr-2" x-show="!isSubmittingClassRemark"></i>
                                    <i class="fas fa-spinner fa-spin mr-2" x-show="isSubmittingClassRemark"></i>
                                    {{ __('ongoing_class.Save Remark') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Add Student Remark Modal --}}
                <div x-show="showAddStudentRemarkModal" 
                     x-cloak
                     class="fixed inset-0 z-50 overflow-y-auto"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0">
                    <div class="flex items-center justify-center min-h-screen p-4">
                        <div class="fixed inset-0 bg-black/60" @click="closeAddStudentRemarkModal()"></div>
                        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-lg"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95">
                            
                            {{-- Modal Header --}}
                            <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center">
                                        <i class="fas fa-user-edit text-purple-600 dark:text-purple-400"></i>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('ongoing_class.Add Student Remark') }}</h3>
                                </div>
                                <button type="button" @click="closeAddStudentRemarkModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>

                            {{-- Modal Body --}}
                            <div class="p-5 space-y-4">
                                {{-- Student Search --}}
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('ongoing_class.Student') }} <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <input type="text" 
                                               x-model="studentSearchQuery"
                                               @input.debounce.300ms="searchStudentsForRemark()"
                                               @focus="showStudentDropdown = true"
                                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-purple-500 focus:ring-purple-500"
                                               :placeholder="studentRemarkForm.student_id ? studentRemarkForm.student_name : '{{ __('ongoing_class.Search student by name or ID...') }}'">
                                        
                                        {{-- Selected Student Badge --}}
                                        <div x-show="studentRemarkForm.student_id" class="absolute right-2 top-1/2 -translate-y-1/2">
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-800 text-purple-700 dark:text-purple-300">
                                                <span x-text="studentRemarkForm.student_name"></span>
                                                <button type="button" @click="clearSelectedStudentForRemark()" class="hover:text-purple-900 dark:hover:text-purple-100">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </span>
                                        </div>
                                        
                                        {{-- Student Dropdown --}}
                                        <div x-show="showStudentDropdown && studentSearchResults.length > 0" 
                                             x-cloak
                                             @click.away="showStudentDropdown = false"
                                             class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                            <template x-for="student in studentSearchResults" :key="student.id">
                                                <div @click="selectStudentForRemark(student)" 
                                                     class="px-4 py-2 hover:bg-purple-50 dark:hover:bg-purple-900/30 cursor-pointer border-b border-gray-100 dark:border-gray-600 last:border-b-0">
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-800 flex items-center justify-center text-purple-700 dark:text-purple-300 text-sm font-semibold"
                                                             x-text="student.name.charAt(0).toUpperCase()"></div>
                                                        <div>
                                                            <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="student.name"></p>
                                                            <p class="text-xs text-gray-500 dark:text-gray-400" x-text="student.student_identifier || '—'"></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                {{-- Period Selection - Only show if no active period --}}
                                <div x-show="!hasActivePeriod">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('ongoing_class.Period') }}</label>
                                    <select x-model="studentRemarkForm.period_id" 
                                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-purple-500 focus:ring-purple-500">
                                        <option value="">{{ __('ongoing_class.Select Period (Optional)') }}</option>
                                        @foreach($activityData['timetable_periods'] as $tp)
                                            <option value="{{ $tp['id'] }}">P{{ $tp['period_number'] }} - {{ $tp['subject_name'] }} ({{ $tp['starts_at'] }} - {{ $tp['ends_at'] }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Current Period Badge - Show when there's an active period --}}
                                <div x-show="hasActivePeriod" class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 border border-green-200 dark:border-green-800">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                                        <span class="text-sm font-medium text-green-700 dark:text-green-300">{{ __('ongoing_class.Current Period') }}:</span>
                                        <span class="text-sm text-green-600 dark:text-green-400" x-text="currentPeriodLabel"></span>
                                    </div>
                                </div>

                                {{-- Remark Type --}}
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">{{ __('ongoing_class.Remark Type') }}</label>
                                    <div class="flex flex-wrap gap-2">
                                        <label class="flex items-center gap-2 px-4 py-2 rounded-lg border-2 cursor-pointer transition-all"
                                               :class="studentRemarkForm.type === 'note' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30' : 'border-gray-300 dark:border-gray-600 hover:border-blue-300'">
                                            <input type="radio" x-model="studentRemarkForm.type" value="note" class="sr-only">
                                            <span class="text-blue-500">📝</span>
                                            <span class="text-sm font-medium" :class="studentRemarkForm.type === 'note' ? 'text-blue-700 dark:text-blue-300' : 'text-gray-700 dark:text-gray-300'">{{ __('ongoing_class.Note') }}</span>
                                        </label>
                                        <label class="flex items-center gap-2 px-4 py-2 rounded-lg border-2 cursor-pointer transition-all"
                                               :class="studentRemarkForm.type === 'positive' ? 'border-green-500 bg-green-50 dark:bg-green-900/30' : 'border-gray-300 dark:border-gray-600 hover:border-green-300'">
                                            <input type="radio" x-model="studentRemarkForm.type" value="positive" class="sr-only">
                                            <span class="text-green-500">👍</span>
                                            <span class="text-sm font-medium" :class="studentRemarkForm.type === 'positive' ? 'text-green-700 dark:text-green-300' : 'text-gray-700 dark:text-gray-300'">{{ __('ongoing_class.Positive') }}</span>
                                        </label>
                                        <label class="flex items-center gap-2 px-4 py-2 rounded-lg border-2 cursor-pointer transition-all"
                                               :class="studentRemarkForm.type === 'concern' ? 'border-yellow-500 bg-yellow-50 dark:bg-yellow-900/30' : 'border-gray-300 dark:border-gray-600 hover:border-yellow-300'">
                                            <input type="radio" x-model="studentRemarkForm.type" value="concern" class="sr-only">
                                            <span class="text-yellow-500">⚠️</span>
                                            <span class="text-sm font-medium" :class="studentRemarkForm.type === 'concern' ? 'text-yellow-700 dark:text-yellow-300' : 'text-gray-700 dark:text-gray-300'">{{ __('ongoing_class.Concern') }}</span>
                                        </label>
                                    </div>
                                </div>

                                {{-- Remark Text --}}
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('ongoing_class.Remark') }} <span class="text-red-500">*</span></label>
                                    <textarea x-model="studentRemarkForm.remark" 
                                              rows="4" 
                                              class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-purple-500 focus:ring-purple-500"
                                              placeholder="{{ __('ongoing_class.Enter your remark about the student...') }}"></textarea>
                                </div>
                            </div>

                            {{-- Modal Footer --}}
                            <div class="flex items-center justify-end gap-3 p-5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
                                <button type="button" 
                                        @click="closeAddStudentRemarkModal()" 
                                        class="px-4 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    {{ __('components.Cancel') }}
                                </button>
                                <button type="button" 
                                        @click="submitStudentRemark()"
                                        :disabled="isSubmittingStudentRemark || !studentRemarkForm.remark.trim() || !studentRemarkForm.student_id"
                                        class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-purple-600 hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fas fa-save mr-2" x-show="!isSubmittingStudentRemark"></i>
                                    <i class="fas fa-spinner fa-spin mr-2" x-show="isSubmittingStudentRemark"></i>
                                    {{ __('ongoing_class.Save Remark') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Activity Summary Alpine.js Component --}}
            <script>
                function activitySummary(config) {
                    return {
                        timetablePeriods: config.timetablePeriods || [],
                        classRemarks: config.classRemarks || [],
                        studentRemarks: config.studentRemarks || [],
                        attendanceData: @json($activityData['attendance']),
                        curriculumUpdates: @json($activityData['curriculum_updates_js']),
                        homeworkAssigned: @json($activityData['homework_assigned_js']),
                        selectedPeriod: config.initialPeriodId || null,

                        // Initialize with URL period_id
                        init() {
                            if (config.initialPeriodId) {
                                this.selectedPeriod = config.initialPeriodId;
                            }
                        },

                        // Select a period and update URL
                        selectPeriod(periodId) {
                            // Update URL and reload page to fetch period-specific data
                            const url = new URL(window.location);
                            if (periodId) {
                                url.searchParams.set('period_id', periodId);
                            } else {
                                url.searchParams.delete('period_id');
                            }
                            window.location.href = url.toString();
                        },

                        // Get selected period number
                        get selectedPeriodNumber() {
                            if (!this.selectedPeriod) return '';
                            const period = this.timetablePeriods.find(p => p.id === this.selectedPeriod);
                            return period ? period.period_number : '';
                        },

                        // Get selected period subject name
                        get selectedPeriodSubject() {
                            if (!this.selectedPeriod) return '';
                            const period = this.timetablePeriods.find(p => p.id === this.selectedPeriod);
                            return period ? period.subject_name : '';
                        },

                        // Check if a period has any remarks (for Blade template)
                        periodHasRemarks(periodId) {
                            return this.classRemarks.some(r => r.period_id === periodId) ||
                                   this.studentRemarks.some(r => r.period_id === periodId);
                        },

                        // All remarks count
                        get allRemarks() {
                            return [...this.classRemarks, ...this.studentRemarks];
                        },

                        // Filtered class remarks based on selected period
                        get filteredClassRemarks() {
                            if (this.selectedPeriod === null) {
                                return this.classRemarks;
                            }
                            return this.classRemarks.filter(r => r.period_id === this.selectedPeriod);
                        },

                        // Filtered student remarks based on selected period
                        get filteredStudentRemarks() {
                            if (this.selectedPeriod === null) {
                                return this.studentRemarks;
                            }
                            return this.studentRemarks.filter(r => r.period_id === this.selectedPeriod);
                        },

                        // Period-specific attendance calculation
                        get periodAttendance() {
                            if (this.selectedPeriod === null) {
                                // Show daily totals when no period selected
                                return {
                                    present: this.attendanceData.present,
                                    absent: this.attendanceData.absent,
                                    leave: this.attendanceData.leave,
                                    late: this.attendanceData.late
                                };
                            }

                            // Calculate period-specific attendance
                            let present = 0, absent = 0, leave = 0, late = 0;
                            
                            this.attendanceData.detailed.forEach(student => {
                                const periodRecord = student.attendance_records.find(r => r.period_id === this.selectedPeriod);
                                if (periodRecord) {
                                    switch (periodRecord.status) {
                                        case 'present': present++; break;
                                        case 'absent': absent++; break;
                                        case 'leave': leave++; break;
                                        case 'late': late++; break;
                                    }
                                }
                            });

                            return { present, absent, leave, late };
                        },

                        // Attendance summary text
                        get attendanceSummaryText() {
                            const counts = this.periodAttendance;
                            const total = counts.present + counts.absent + counts.leave + counts.late;
                            const totalStudents = this.attendanceData.total;
                            
                            if (total === 0) {
                                return `0/${totalStudents}`;
                            }
                            
                            return `${total}/${totalStudents}`;
                        },

                        // Filtered curriculum updates based on selected period
                        get filteredCurriculumUpdates() {
                            if (this.selectedPeriod === null) {
                                return this.curriculumUpdates;
                            }
                            
                            // Find selected period's subject
                            const selectedPeriodData = this.timetablePeriods.find(p => p.id === this.selectedPeriod);
                            if (!selectedPeriodData || !selectedPeriodData.subject_id) {
                                return [];
                            }
                            
                            return this.curriculumUpdates.filter(update => 
                                update.subject_id === selectedPeriodData.subject_id
                            );
                        },

                        // Filtered homework based on selected period
                        get filteredHomeworkAssigned() {
                            if (this.selectedPeriod === null) {
                                return this.homeworkAssigned;
                            }
                            
                            // Find selected period's subject
                            const selectedPeriodData = this.timetablePeriods.find(p => p.id === this.selectedPeriod);
                            if (!selectedPeriodData || !selectedPeriodData.subject_id) {
                                return [];
                            }
                            
                            return this.homeworkAssigned.filter(hw => 
                                hw.subject_id === selectedPeriodData.subject_id
                            );
                        },

                        // Add Class Remark Modal
                        showAddClassRemarkModal: false,
                        classRemarkForm: {
                            remark: '',
                            type: 'note',
                            subject_id: '',
                            period_id: ''
                        },
                        isSubmittingClassRemark: false,

                        openAddClassRemarkModal() {
                            this.classRemarkForm = {
                                remark: '',
                                type: 'note',
                                subject_id: '',
                                period_id: this.selectedPeriod || ''
                            };
                            this.showAddClassRemarkModal = true;
                        },

                        closeAddClassRemarkModal() {
                            this.showAddClassRemarkModal = false;
                            this.classRemarkForm = {
                                remark: '',
                                type: 'note',
                                subject_id: '',
                                period_id: ''
                            };
                        },

                        async submitClassRemark() {
                            if (!this.classRemarkForm.remark.trim()) {
                                alert('{{ __("ongoing_class.Please enter a remark") }}');
                                return;
                            }

                            this.isSubmittingClassRemark = true;

                            try {
                                const response = await fetch('{{ route("class-remarks.store") }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        class_id: '{{ $class->id }}',
                                        date: '{{ $selectedDate->format("Y-m-d") }}',
                                        remark: this.classRemarkForm.remark,
                                        type: this.classRemarkForm.type,
                                        subject_id: this.classRemarkForm.subject_id || null,
                                        period_id: this.classRemarkForm.period_id || null
                                    })
                                });

                                const data = await response.json();

                                if (data.success) {
                                    // Add the new remark to the list
                                    const selectedPeriodData = this.timetablePeriods.find(p => p.id === this.classRemarkForm.period_id);
                                    this.classRemarks.unshift({
                                        id: data.data.id,
                                        period_id: this.classRemarkForm.period_id || null,
                                        remark: this.classRemarkForm.remark,
                                        type: this.classRemarkForm.type,
                                        teacher_name: '{{ auth()->user()->name }}',
                                        subject_name: selectedPeriodData?.subject_name || null,
                                        time: new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false })
                                    });
                                    this.closeAddClassRemarkModal();
                                } else {
                                    alert(data.message || '{{ __("ongoing_class.Failed to add remark") }}');
                                }
                            } catch (error) {
                                console.error('Error:', error);
                                alert('{{ __("ongoing_class.An error occurred") }}');
                            } finally {
                                this.isSubmittingClassRemark = false;
                            }
                        },

                        // Add Student Remark Modal
                        showAddStudentRemarkModal: false,
                        studentRemarkForm: {
                            student_id: '',
                            student_name: '',
                            remark: '',
                            type: 'note',
                            period_id: ''
                        },
                        isSubmittingStudentRemark: false,
                        studentSearchQuery: '',
                        studentSearchResults: [],
                        showStudentDropdown: false,
                        classStudents: @json($activityData['students'] ?? []),
                        currentPeriodId: config.currentPeriodId || null,
                        isToday: config.isToday || false,

                        // Check if there's an active period
                        get hasActivePeriod() {
                            return this.isToday && this.currentPeriodId !== null;
                        },

                        // Get current period label
                        get currentPeriodLabel() {
                            if (!this.currentPeriodId) return '';
                            const period = this.timetablePeriods.find(p => p.id === this.currentPeriodId);
                            return period ? `P${period.period_number} - ${period.subject_name}` : '';
                        },

                        openAddStudentRemarkModal() {
                            // Auto-select current period if active
                            const periodId = this.hasActivePeriod ? this.currentPeriodId : (this.selectedPeriod || '');
                            this.studentRemarkForm = {
                                student_id: '',
                                student_name: '',
                                remark: '',
                                type: 'note',
                                period_id: periodId
                            };
                            this.studentSearchQuery = '';
                            this.studentSearchResults = [];
                            this.showAddStudentRemarkModal = true;
                        },

                        closeAddStudentRemarkModal() {
                            this.showAddStudentRemarkModal = false;
                            this.studentRemarkForm = {
                                student_id: '',
                                student_name: '',
                                remark: '',
                                type: 'note',
                                period_id: ''
                            };
                            this.studentSearchQuery = '';
                            this.studentSearchResults = [];
                        },

                        searchStudentsForRemark() {
                            const query = this.studentSearchQuery.toLowerCase().trim();
                            if (!query) {
                                this.studentSearchResults = this.classStudents.slice(0, 10);
                                return;
                            }
                            this.studentSearchResults = this.classStudents.filter(s => 
                                s.name.toLowerCase().includes(query) || 
                                (s.student_identifier && s.student_identifier.toLowerCase().includes(query))
                            ).slice(0, 10);
                            this.showStudentDropdown = true;
                        },

                        selectStudentForRemark(student) {
                            this.studentRemarkForm.student_id = student.id;
                            this.studentRemarkForm.student_name = student.name;
                            this.studentSearchQuery = '';
                            this.showStudentDropdown = false;
                        },

                        clearSelectedStudentForRemark() {
                            this.studentRemarkForm.student_id = '';
                            this.studentRemarkForm.student_name = '';
                        },

                        async submitStudentRemark() {
                            if (!this.studentRemarkForm.remark.trim()) {
                                alert('{{ __("ongoing_class.Please enter a remark") }}');
                                return;
                            }
                            if (!this.studentRemarkForm.student_id) {
                                alert('{{ __("ongoing_class.Please select a student") }}');
                                return;
                            }

                            this.isSubmittingStudentRemark = true;

                            try {
                                const response = await fetch('{{ route("student-remarks.store") }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        class_id: '{{ $class->id }}',
                                        student_id: this.studentRemarkForm.student_id,
                                        date: '{{ $selectedDate->format("Y-m-d") }}',
                                        remark: this.studentRemarkForm.remark,
                                        type: this.studentRemarkForm.type,
                                        period_id: this.studentRemarkForm.period_id || null
                                    })
                                });

                                const data = await response.json();

                                if (data.success) {
                                    // Add the new remark to the list
                                    this.studentRemarks.unshift({
                                        id: data.data.id,
                                        period_id: this.studentRemarkForm.period_id || null,
                                        remark: this.studentRemarkForm.remark,
                                        type: this.studentRemarkForm.type,
                                        student_name: this.studentRemarkForm.student_name,
                                        student_initial: this.studentRemarkForm.student_name.charAt(0).toUpperCase(),
                                        time: new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false })
                                    });
                                    this.closeAddStudentRemarkModal();
                                } else {
                                    alert(data.message || '{{ __("ongoing_class.Failed to add remark") }}');
                                }
                            } catch (error) {
                                console.error('Error:', error);
                                alert('{{ __("ongoing_class.An error occurred") }}');
                            } finally {
                                this.isSubmittingStudentRemark = false;
                            }
                        }
                    };
                }
            </script>
            @endif

            @if(!isset($activityData))
            <x-info-table 
                :title="__('academic_management.Class Information')"
                :rows="[
                    [
                        'label' => __('academic_management.Class Name'),
                        'value' => \App\Helpers\SectionHelper::formatFullClassName($class->name, $class->grade?->level)
                    ],
                    [
                        'label' => __('academic_management.Grade'),
                        'value' => $class->grade ? $class->grade->name : '—'
                    ],
                    [
                        'label' => __('academic_management.Room'),
                        'value' => e($class->room->name ?? '—')
                    ],
                    [
                        'label' => __('academic_management.Class Teacher'),
                        'value' => e($classTeacher)
                    ],
                    [
                        'label' => __('academic_management.Total Students'),
                        'value' => $totalStudents
                    ],
                    [
                        'label' => __('academic_management.Created At'),
                        'value' => $class->created_at?->format('F d, Y') ?? '—'
                    ],
                ]"
            />

            <x-timetable
                :title="__('academic_management.Class Timetable')"
                :week-days="$timetableWeekDays"
                :periods="$timetablePeriods"
                :entries="$timetableEntries"
                :period-labels="$timetablePeriodLabels"
            />

            <x-data-table
                :title="__('academic_management.Teachers in Class')"
                :columns="[
                    [
                        'label' => __('academic_management.Teacher ID'),
                        'render' => fn($row) => e($row['teacher_id'] ?? '—')
                    ],
                    [
                        'label' => __('academic_management.Teacher Name'),
                        'render' => fn($row) => e($row['name'] ?? '—')
                    ],
                    [
                        'label' => __('academic_management.Subject'),
                        'render' => fn($row) => e($row['subject'] ?? '—')
                    ],
                ]"
                :data="$teachersPaginated"
                :actions="[]"
                :show-filters="false"
                table-class="basic-table"
            />

            <x-data-table
                :title="__('academic_management.Students in Class')"
                :addButtonText="__('academic_management.Add Student')"
                addButtonAction="openAddStudentModal()"
                :columns="[
                    [
                        'label' => __('academic_management.Student ID'),
                        'render' => fn($student) => e($student->student_identifier ?? '—')
                    ],
                    [
                        'label' => __('academic_management.Student Name'),
                        'render' => fn($student) => e($student->user->name ?? '—')
                    ],
                    [
                        'label' => __('academic_management.Phone'),
                        'render' => fn($student) => e($student->user->phone ?? '—')
                    ],
                ]"
                :data="$studentsPaginated"
                :actions="[]"
                :show-filters="false"
                table-class="basic-table"
            />

            <div id="addStudentModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="fixed inset-0 bg-black/60" onclick="closeAddStudentModal()"></div>
                    <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-2xl">
                        <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('academic_management.Add Student') }}</h3>
                            <button type="button" onclick="closeAddStudentModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div class="p-5 space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('academic_management.Search Students') }}</label>
                                <div class="relative">
                                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                                    <input type="text" id="studentSearchInput" class="w-full pl-10 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" placeholder="{{ __('academic_management.Search by name, ID...') }}" oninput="searchStudents(this.value)">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('academic_management.Select Student') }}</label>
                                <div id="studentSearchResults" class="max-h-60 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-900/50">
                                    <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                                        <i class="fas fa-search text-2xl mb-2 block opacity-50"></i>
                                        <p class="text-sm">{{ __('academic_management.Start typing to search for students') }}</p>
                                    </div>
                                </div>
                            </div>

                            <div id="selectedStudentInfo" class="hidden p-4 bg-blue-50 dark:bg-blue-900/30 rounded-lg border border-blue-200 dark:border-blue-800">
                                <div class="flex items-center gap-3">
                                    <div id="selectedStudentAvatar" class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center font-bold text-lg"></div>
                                    <div class="flex-1">
                                        <p id="selectedStudentName" class="font-semibold text-gray-900 dark:text-white"></p>
                                        <p id="selectedStudentDetails" class="text-sm text-gray-500 dark:text-gray-400"></p>
                                    </div>
                                    <button type="button" onclick="clearSelectedStudent()" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-gray-500 flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 p-5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
                            <button type="button" onclick="closeAddStudentModal()" class="px-4 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">{{ __('components.Cancel') }}</button>
                            <button type="button" onclick="addSelectedStudent()" id="addStudentBtn" disabled class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-user-plus mr-2"></i>{{ __('academic_management.Add Student') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <x-form-modal 
                id="editClassModal" 
                title="{{ __('academic_management.Edit Class') }}" 
                icon="fas fa-chalkboard"
                action="{{ route('academic-management.classes.update', $class->id) }}"
                method="PUT"
                :submitText="__('academic_management.Update Class')"
                :cancelText="__('academic_management.Cancel')">
                @include('academic.partials.class-form-fields', [
                    'grades' => $grades,
                    'rooms' => $rooms,
                    'teachers' => $teachers,
                    'class' => $class,
                ])
            </x-form-modal>
            @endif
        </div>
    </div>

    @if(!isset($activityData))
    <script>
        let selectedStudent = null;
        let studentSearchTimeout;
        const classId = '{{ $class->id }}';

        function fetchJson(url, options = {}) {
            return fetch(url, options).then(async response => {
                const contentType = response.headers.get('content-type') || '';
                const bodyText = await response.text();
                const isJson = contentType.includes('application/json');
                let parsed = null;

                if (isJson && bodyText) {
                    try {
                        parsed = JSON.parse(bodyText);
                    } catch (error) {
                        parsed = null;
                    }
                }

                if (!response.ok) {
                    const message = parsed?.message
                        || (bodyText && !bodyText.trim().startsWith('<') ? bodyText.trim() : `Request failed (${response.status})`);
                    throw new Error(message);
                }

                if (!isJson) {
                    const message = bodyText && !bodyText.trim().startsWith('<')
                        ? bodyText.trim()
                        : 'Unexpected response format.';
                    throw new Error(message);
                }

                return parsed ?? [];
            });
        }

        function openAddStudentModal() {
            document.getElementById('addStudentModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            document.getElementById('studentSearchInput').value = '';
            clearSelectedStudent();
            resetStudentSearchResults();
        }

        function closeAddStudentModal() {
            document.getElementById('addStudentModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        function resetStudentSearchResults() {
            const resultsContainer = document.getElementById('studentSearchResults');
            resultsContainer.innerHTML = `
                <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                    <i class="fas fa-search text-2xl mb-2 block opacity-50"></i>
                    <p class="text-sm">{{ __('academic_management.Start typing to search for students') }}</p>
                </div>
            `;
        }

        function searchStudents(query) {
            const resultsContainer = document.getElementById('studentSearchResults');

            clearTimeout(studentSearchTimeout);

            if (!query.trim() || query.length < 2) {
                resetStudentSearchResults();
                return;
            }

            resultsContainer.innerHTML = `
                <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                    <i class="fas fa-spinner fa-spin text-2xl mb-2 block"></i>
                    <p class="text-sm">{{ __('academic_management.Searching...') }}</p>
                </div>
            `;

            studentSearchTimeout = setTimeout(() => {
                fetchJson(`{{ route('academic-management.classes.search-students') }}?search=${encodeURIComponent(query)}&class_id=${classId}`)
                    .then(data => {
                        displayStudentResults(data);
                    })
                    .catch(error => {
                        console.error('Search error:', error);
                        resultsContainer.innerHTML = `
                            <div class="p-6 text-center text-red-500 dark:text-red-400">
                                <i class="fas fa-exclamation-triangle text-2xl mb-2 block"></i>
                                <p class="text-sm">{{ __('academic_management.Error searching for students') }}</p>
                            </div>
                        `;
                    });
            }, 300);
        }

        function displayStudentResults(results) {
            const resultsContainer = document.getElementById('studentSearchResults');

            if (results.length === 0) {
                resultsContainer.innerHTML = `
                    <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                        <i class="fas fa-info-circle text-2xl mb-2 block opacity-50"></i>
                        <p class="text-sm">{{ __('academic_management.No matching students found.') }}</p>
                    </div>
                `;
                return;
            }

            resultsContainer.innerHTML = results.map(student => `
                <div class="p-3 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-200 dark:border-gray-600 last:border-b-0"
                     onclick="selectStudent(${JSON.stringify(student).replace(/"/g, '&quot;')})">
                    <div class="flex items-center space-x-3">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400">
                            <i class="fas fa-user-graduate text-xs"></i>
                        </span>
                        <div class="flex-1">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">${student.name}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                ${student.student_identifier ?? '—'} • ${student.email ?? '—'} • ${student.phone ?? '—'}
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function selectStudent(student) {
            selectedStudent = student;

            const infoDiv = document.getElementById('selectedStudentInfo');
            const avatarDiv = document.getElementById('selectedStudentAvatar');
            const nameDiv = document.getElementById('selectedStudentName');
            const detailsDiv = document.getElementById('selectedStudentDetails');
            const addBtn = document.getElementById('addStudentBtn');

            const initials = student.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();

            avatarDiv.textContent = initials;
            nameDiv.textContent = student.name;
            detailsDiv.textContent = `${student.student_identifier ?? '—'} • ${student.email ?? '—'} • ${student.phone ?? '—'}`;

            infoDiv.classList.remove('hidden');
            addBtn.disabled = false;
        }

        function clearSelectedStudent() {
            selectedStudent = null;
            document.getElementById('selectedStudentInfo').classList.add('hidden');
            document.getElementById('addStudentBtn').disabled = true;
        }

        function addSelectedStudent() {
            if (!selectedStudent) return;

            fetchJson(`{{ route('academic-management.classes.add-student', $class) }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    student_id: selectedStudent.id
                })
            })
            .then(data => {
                if (data.success) {
                    closeAddStudentModal();
                    location.reload();
                    showNotification(data.message, 'success');
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Add student error:', error);
                showNotification(error.message || '{{ __("academic_management.An error occurred while adding the student.") }}', 'error');
            });
        }

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white`;
            notification.textContent = message;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAddStudentModal();
            }
        });
    </script>
    @endif
</x-app-layout>
