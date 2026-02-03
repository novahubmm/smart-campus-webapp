{{-- Timetable Editor Modal Component --}}
{{-- Usage: @include('time-table._timetable-editor-modal', ['class' => $class, 'subjects' => $subjects, 'timetables' => $timetables]) --}}

@php
    $editorConfig = [
        'classId' => $class->id,
        'className' => \App\Helpers\SectionHelper::formatFullClassName($class->name, $class->grade?->level),
        'gradeId' => $class->grade_id,
        'batchId' => $class->batch_id,
        'gradeName' => $class->grade ? \App\Helpers\GradeHelper::getLocalizedName($class->grade->level) : '',
    ];
    
    // Format timetables for JavaScript
    $timetablesJson = $timetables->map(function($tt) {
        $formatTime = function ($time) {
            if (!$time) return null;
            if ($time instanceof \DateTimeInterface) return $time->format('H:i');
            $str = (string) $time;
            if (strlen($str) === 5) return $str;
            if (strlen($str) >= 5) return substr($str, 0, 5);
            return $str;
        };
        
        return [
            'id' => $tt->id,
            'display_name' => $tt->display_name,
            'version' => $tt->version,
            'version_name' => $tt->version_name,
            'status' => $tt->status,
            'is_active' => $tt->is_active,
            'week_days' => $tt->week_days ?? ['mon', 'tue', 'wed', 'thu', 'fri'],
            'school_start_time' => $formatTime($tt->school_start_time) ?? '08:00',
            'school_end_time' => $formatTime($tt->school_end_time) ?? '15:00',
            'minutes_per_period' => $tt->minutes_per_period ?? 45,
            'break_duration' => $tt->break_duration ?? 15,
            'periods' => $tt->periods->map(function($p) use ($formatTime) {
                return [
                    'id' => $p->id,
                    'day_of_week' => strtolower(substr($p->day_of_week, 0, 3)),
                    'period_number' => $p->period_number,
                    'subject_id' => $p->subject_id,
                    'subject_name' => $p->subject?->name ?? '',
                    'teacher_profile_id' => $p->teacher_profile_id,
                    'teacher_name' => $p->teacher?->user?->name ?? '',
                    'starts_at' => $formatTime($p->starts_at),
                    'ends_at' => $formatTime($p->ends_at),
                    'is_break' => $p->is_break,
                ];
            })->values()->toArray(),
        ];
    })->keyBy('id')->toArray();

    // Build subject options
    $subjectOptions = [];
    foreach ($subjects as $subject) {
        $subjectTeachers = $subject->teachers ?? collect();
        if ($subjectTeachers->isEmpty()) {
            $subjectOptions[] = [
                'id' => $subject->id,
                'name' => $subject->name,
                'teacher_profile_id' => null,
                'teacher_name' => '',
            ];
        } else {
            foreach ($subjectTeachers as $teacher) {
                $subjectOptions[] = [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'teacher_profile_id' => $teacher->id,
                    'teacher_name' => $teacher->user?->name ?? '',
                ];
            }
        }
    }
    
    // Get defaults from settings
    $setting = \App\Models\Setting::first();
    $defaultWeekDays = $setting?->week_days ?? ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
    $defaultWeekDays = collect($defaultWeekDays)->map(fn($d) => strtolower(substr($d, 0, 3)))->toArray();
    
    $defaults = [
        'number_of_periods_per_day' => $setting?->number_of_periods_per_day ?? 7,
        'minute_per_period' => $setting?->minute_per_period ?? 45,
        'break_duration' => $setting?->break_duration ?? 15,
        'school_start_time' => $setting?->school_start_time ? substr($setting->school_start_time, 0, 5) : '08:00',
        'school_end_time' => $setting?->school_end_time ? substr($setting->school_end_time, 0, 5) : '13:30',
        'week_days' => $defaultWeekDays,
        'time_format' => $timeFormat ?? $setting?->timetable_time_format ?? '24h',
    ];
@endphp

{{-- Editor Modal --}}
<div id="timetable-editor-modal" 
     x-data="timetableEditorModal()" 
     x-show="isOpen" 
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     @keydown.escape.window="closeModal()"
     @open-timetable-editor.window="openForTimetable($event.detail.timetableId)"
     @open-timetable-editor-create.window="openForCreate()">
    
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-black/50 transition-opacity" @click="closeModal()"></div>
    
    {{-- Modal Content --}}
    <div class="flex items-start justify-center min-h-screen px-4 pt-6 pb-4">
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-7xl max-h-[95vh] overflow-hidden"
             @click.stop>
            
            {{-- Modal Header --}}
            <div class="sticky top-0 z-10 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <i class="fas fa-calendar-alt text-blue-500"></i>
                            <span x-text="modalTitle"></span>
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $editorConfig['className'] }} • {{ $editorConfig['gradeName'] }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <template x-if="currentTimetable && !currentTimetable.isNew">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium"
                                  :class="currentTimetable.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'">
                                <span x-text="currentTimetable.is_active ? '{{ __('time_table.Active') }}' : '{{ __('time_table.Inactive') }}'"></span>
                            </span>
                        </template>
                        <button @click="closeModal()" class="p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Modal Body --}}
            <div class="p-6 overflow-y-auto max-h-[calc(95vh-140px)]" x-show="currentTimetable">
                
                {{-- Edit Mode Banner --}}
                <div x-show="isEditMode" class="mb-4 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                    <div class="flex items-center gap-2 text-amber-700 dark:text-amber-300">
                        <i class="fas fa-edit"></i>
                        <span class="font-medium">{{ __('time_table.Edit Mode') }}</span>
                        <span class="text-sm">- {{ __('time_table.Click on periods to edit times, click cells to assign subjects') }}</span>
                    </div>
                </div>

                {{-- Periods Row --}}
                <div class="mb-4 p-4 bg-gray-50 dark:bg-gray-900/30 rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="flex flex-wrap gap-2 items-center">
                        <template x-for="(period, pIdx) in currentTimetable?.periods || []" :key="pIdx">
                            <div class="relative group">
                                <div class="px-3 py-2 rounded-lg border text-sm font-medium transition-colors"
                                     :class="[
                                         period.is_break ? 'bg-amber-50 dark:bg-amber-900/30 border-amber-300 dark:border-amber-700 text-amber-800 dark:text-amber-200' : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white',
                                         isEditMode ? 'pr-8 cursor-pointer hover:border-blue-400' : ''
                                     ]"
                                     @click="isEditMode && editPeriodTime(pIdx)">
                                    <span x-text="period.label + ': ' + formatTimeRange(period.starts_at, period.ends_at)"></span>
                                </div>
                                <template x-if="isEditMode && currentTimetable.periods.length > 1">
                                    <button type="button" 
                                        class="absolute top-1/2 right-1 -translate-y-1/2 w-5 h-5 rounded-full bg-red-500 text-white text-xs flex items-center justify-center opacity-80 hover:opacity-100"
                                        @click.stop="removePeriod(pIdx)">
                                        <i class="fas fa-times text-[10px]"></i>
                                    </button>
                                </template>
                            </div>
                        </template>
                        <template x-if="isEditMode">
                            <button type="button" class="px-3 py-2 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-blue-400 hover:text-blue-600 text-sm" @click="addPeriod('period')">
                                <i class="fas fa-plus mr-1"></i>{{ __('time_table.Period') }}
                            </button>
                        </template>
                        <template x-if="isEditMode">
                            <button type="button" class="px-3 py-2 rounded-lg border-2 border-dashed border-amber-300 dark:border-amber-700 text-amber-600 dark:text-amber-400 hover:border-amber-400 text-sm" @click="addPeriod('break')">
                                <i class="fas fa-coffee mr-1"></i>{{ __('time_table.Break') }}
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Schedule Grid --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase w-28">{{ __('time_table.Period') }}</th>
                                <template x-for="day in currentTimetable?.week_days || []" :key="day">
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase" x-text="dayLabel(day)"></th>
                                </template>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                            <template x-for="(period, pIdx) in currentTimetable?.periods || []" :key="pIdx">
                                <tr>
                                    <td class="px-4 py-3 text-sm font-semibold" :class="period.is_break ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-800 dark:text-amber-200' : 'bg-gray-50 dark:bg-gray-900/30 text-gray-900 dark:text-white'">
                                        <div x-text="period.label"></div>
                                        <div class="text-xs font-normal opacity-70" x-text="formatTimeRange(period.starts_at, period.ends_at)"></div>
                                    </td>
                                    <template x-for="day in currentTimetable?.week_days || []" :key="day + '-' + pIdx">
                                        <td class="px-2 py-2 text-center">
                                            <template x-if="period.is_break">
                                                <div class="text-gray-400 dark:text-gray-500">—</div>
                                            </template>
                                            <template x-if="!period.is_break">
                                                <div class="w-full min-h-[60px] rounded-lg border px-2 py-2 text-left text-sm transition-colors"
                                                     :class="[
                                                         hasEntry(day, pIdx) ? 'border-green-300 dark:border-green-700 bg-green-50 dark:bg-green-900/20' : 'border-gray-300 dark:border-gray-600',
                                                         isEditMode ? 'border-dashed cursor-pointer hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20' : ''
                                                     ]"
                                                     @click="isEditMode && openSubjectModal(day, pIdx)">
                                                    <template x-if="hasEntry(day, pIdx)">
                                                        <div>
                                                            <div class="font-medium text-gray-900 dark:text-white text-xs" x-text="getEntrySubject(day, pIdx)"></div>
                                                            <div class="text-xs text-gray-500 dark:text-gray-400" x-text="getEntryTeacher(day, pIdx)"></div>
                                                        </div>
                                                    </template>
                                                    <template x-if="!hasEntry(day, pIdx)">
                                                        <div class="text-center text-gray-400 dark:text-gray-500" x-text="isEditMode ? '+' : '—'"></div>
                                                    </template>
                                                </div>
                                            </template>
                                        </td>
                                    </template>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="sticky bottom-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <template x-if="!isEditMode && !currentTimetable?.is_active">
                            <button @click="startEdit()" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg">
                                <i class="fas fa-edit"></i>
                                <span>{{ __('time_table.Edit') }}</span>
                            </button>
                        </template>
                        <template x-if="!isEditMode && currentTimetable?.is_active">
                            <div class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded-lg cursor-not-allowed">
                                <i class="fas fa-lock"></i>
                                <span>{{ __('time_table.Active Version (Cannot Edit)') }}</span>
                            </div>
                        </template>
                        <template x-if="isEditMode">
                            <button @click="openSettingsModal()" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg">
                                <i class="fas fa-cog"></i>
                                <span>{{ __('time_table.Settings') }}</span>
                            </button>
                        </template>
                    </div>
                    <div class="flex items-center gap-2">
                        <template x-if="isEditMode">
                            <button @click="cancelEdit()" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg">
                                <i class="fas fa-times"></i>
                                <span>{{ __('time_table.Cancel') }}</span>
                            </button>
                        </template>
                        <template x-if="isEditMode">
                            <button @click="saveTimetable()" :disabled="isSaving" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg disabled:opacity-50">
                                <i class="fas" :class="isSaving ? 'fa-spinner fa-spin' : 'fa-save'"></i>
                                <span x-text="isSaving ? '{{ __('time_table.Saving...') }}' : '{{ __('time_table.Save') }}'"></span>
                            </button>
                        </template>
                        <template x-if="isEditMode && currentTimetable?.isNew">
                            <button @click="saveTimetable(true)" :disabled="isSaving" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-lg disabled:opacity-50">
                                <i class="fas fa-star"></i>
                                <span>{{ __('time_table.Save & Set Active') }}</span>
                            </button>
                        </template>
                        <template x-if="!isEditMode">
                            <button @click="closeModal()" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg">
                                <span>{{ __('time_table.Close') }}</span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Subject Selection Modal --}}
    <div x-show="subjectModalOpen" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 px-4" @click.self="subjectModalOpen = false">
        <div class="w-full max-w-lg bg-white dark:bg-gray-900 rounded-xl shadow-2xl overflow-hidden" @click.stop>
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('time_table.Select Subject & Teacher') }}</h4>
                <button type="button" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800" @click="subjectModalOpen = false">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-5 space-y-4 max-h-[60vh] overflow-y-auto">
                <input type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white" placeholder="{{ __('time_table.Search subjects or teachers...') }}" x-model="subjectSearch">
                <div class="space-y-2">
                    <template x-for="subject in filteredSubjects" :key="subject.id + '-' + (subject.teacher_profile_id || '')">
                        <button type="button" class="w-full flex items-center gap-3 p-3 rounded-lg border-2 border-gray-200 dark:border-gray-700 hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 text-left" @click="assignSubject(subject)">
                            <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="flex-1">
                                <div class="font-medium text-gray-900 dark:text-white" x-text="subject.name"></div>
                                <div class="text-sm text-gray-500 dark:text-gray-400" x-text="subject.teacher_name || '{{ __('time_table.No teacher assigned') }}'"></div>
                            </div>
                        </button>
                    </template>
                </div>
            </div>
            <div class="flex justify-between items-center px-5 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                <button type="button" class="text-sm text-red-600 dark:text-red-400 hover:underline" @click="clearSlot">{{ __('time_table.Clear slot') }}</button>
                <button type="button" class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200" @click="subjectModalOpen = false">{{ __('time_table.Cancel') }}</button>
            </div>
        </div>
    </div>

    {{-- Period Edit Modal --}}
    <div x-show="periodEditModalOpen" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 px-4" @click.self="periodEditModalOpen = false">
        <div class="w-full max-w-md bg-white dark:bg-gray-900 rounded-xl shadow-2xl overflow-hidden" @click.stop>
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('time_table.Edit Period') }}</h4>
                <button type="button" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700" @click="periodEditModalOpen = false">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-5 space-y-4">
                <div class="space-y-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('time_table.Period Name') }}</label>
                    <input type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white" x-model="periodEditData.label">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('time_table.Start Time') }}</label>
                        <input type="time" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white" x-model="periodEditData.starts_at">
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('time_table.End Time') }}</label>
                        <input type="time" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white" x-model="periodEditData.ends_at">
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="modalPeriodIsBreak" class="rounded border-gray-300 text-amber-600" x-model="periodEditData.is_break">
                    <label for="modalPeriodIsBreak" class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('time_table.This is a break') }}</label>
                </div>
            </div>
            <div class="flex justify-end gap-2 px-5 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                <button type="button" class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200" @click="periodEditModalOpen = false">{{ __('time_table.Cancel') }}</button>
                <button type="button" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700" @click="savePeriodEdit">{{ __('time_table.Save') }}</button>
            </div>
        </div>
    </div>

    {{-- Settings Modal --}}
    <div x-show="settingsModalOpen" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 px-4" @click.self="settingsModalOpen = false">
        <div class="w-full max-w-xl bg-white dark:bg-gray-900 rounded-xl shadow-2xl overflow-hidden" @click.stop>
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('time_table.Timetable Settings') }}</h4>
                <button type="button" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700" @click="settingsModalOpen = false">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-5 space-y-6">
                {{-- Info Banner --}}
                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <div class="flex items-start gap-2 text-sm text-blue-700 dark:text-blue-300">
                        <i class="fas fa-info-circle mt-0.5"></i>
                        <span>{{ __('time_table.Changing these settings will regenerate all period times. Subject assignments will be preserved.') }}</span>
                    </div>
                </div>
                
                <div class="space-y-3">
                    <h5 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('time_table.School Timings') }}</h5>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('time_table.Start Time') }}</label>
                            <input type="time" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white" x-model="settingsData.school_start_time">
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('time_table.End Time') }}</label>
                            <input type="time" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white" x-model="settingsData.school_end_time">
                        </div>
                    </div>
                </div>
                <div class="space-y-3">
                    <h5 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('time_table.Period Duration') }}</h5>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('time_table.Minutes per Period') }}</label>
                            <input type="number" min="15" max="120" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white" x-model.number="settingsData.minutes_per_period">
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('time_table.Break Duration (min)') }}</label>
                            <input type="number" min="0" max="60" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white" x-model.number="settingsData.break_duration">
                        </div>
                    </div>
                </div>
                <div class="space-y-3">
                    <h5 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('time_table.Week Days') }}</h5>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="day in allDays" :key="day">
                            <button type="button" class="px-3 py-2 rounded-lg text-sm font-medium transition-colors"
                                    :class="settingsData.week_days.includes(day) ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                                    @click="toggleDay(day)" x-text="dayLabel(day)">
                            </button>
                        </template>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-2 px-5 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                <button type="button" class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200" @click="settingsModalOpen = false">{{ __('time_table.Cancel') }}</button>
                <button type="button" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700" @click="applySettings">{{ __('time_table.Apply & Regenerate') }}</button>
            </div>
        </div>
    </div>
</div>


@push('scripts')
<script>
function timetableEditorModal() {
    const config = @json($editorConfig);
    const timetablesData = @json($timetablesJson);
    const subjectOptions = @json($subjectOptions);
    const defaults = @json($defaults);
    const periodLabel = '{{ __("time_table.Period") }}';
    const breakLabel = '{{ __("time_table.Break") }}';

    return {
        isOpen: false,
        isEditMode: false,
        isSaving: false,
        currentTimetable: null,
        originalTimetable: null,
        
        // Sub-modals
        subjectModalOpen: false,
        periodEditModalOpen: false,
        settingsModalOpen: false,
        
        // Subject modal
        subjectSearch: '',
        currentDay: null,
        currentPeriodIdx: null,
        
        // Period edit
        periodEditData: { label: '', starts_at: '', ends_at: '', is_break: false },
        editingPeriodIdx: null,
        
        // Settings
        settingsData: { school_start_time: '', school_end_time: '', minutes_per_period: 45, break_duration: 15, week_days: [] },
        allDays: ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'],

        get modalTitle() {
            if (!this.currentTimetable) return '';
            if (this.currentTimetable.isNew) return '{{ __("time_table.Create New Version") }}';
            return this.currentTimetable.display_name + ' (v' + this.currentTimetable.version + ')';
        },

        get filteredSubjects() {
            const search = this.subjectSearch.toLowerCase();
            return subjectOptions.filter(s => {
                if (!search) return true;
                return s.name?.toLowerCase().includes(search) || s.teacher_name?.toLowerCase().includes(search);
            });
        },

        // Open modal for viewing/editing existing timetable
        openForTimetable(timetableId) {
            const tt = timetablesData[timetableId];
            if (!tt) return;
            
            this.currentTimetable = this.prepareTimetable(tt);
            this.originalTimetable = JSON.parse(JSON.stringify(this.currentTimetable));
            this.isEditMode = false;
            this.isOpen = true;
        },

        // Open modal for creating new version
        openForCreate() {
            // Find highest version number
            const versions = Object.values(timetablesData).map(t => t.version || 1);
            const nextVersion = versions.length > 0 ? Math.max(...versions) + 1 : 1;
            
            // Create empty timetable with default periods (no subjects assigned)
            const periods = this.generateDefaultPeriods();
            const weekDays = defaults.week_days;
            const entries = {}; // Empty - no subjects assigned
            
            this.currentTimetable = {
                id: null,
                isNew: true,
                display_name: `Version ${nextVersion}`,
                version: nextVersion,
                is_active: false,
                week_days: weekDays,
                school_start_time: defaults.school_start_time,
                school_end_time: defaults.school_end_time,
                minutes_per_period: defaults.minute_per_period,
                break_duration: defaults.break_duration,
                periods: periods,
                entries: entries,
            };
            this.originalTimetable = JSON.parse(JSON.stringify(this.currentTimetable));
            this.isEditMode = true;
            this.isOpen = true;
        },

        prepareTimetable(tt) {
            const periods = this.convertSeedPeriods(tt.periods);
            const entries = this.buildEntriesFromPeriods(tt.periods);
            
            // Convert week_days to short format for UI
            const convertDayToShort = (day) => {
                const dayMap = {
                    'monday': 'mon',
                    'tuesday': 'tue',
                    'wednesday': 'wed',
                    'thursday': 'thu',
                    'friday': 'fri',
                    'saturday': 'sat',
                    'sunday': 'sun'
                };
                const lowerDay = day.toLowerCase();
                return dayMap[lowerDay] || lowerDay.substring(0, 3);
            };
            
            const weekDays = (tt.week_days || []).map(day => convertDayToShort(day));
            
            return {
                ...tt,
                isNew: false,
                periods: periods,
                entries: entries,
                week_days: weekDays,
            };
        },

        convertSeedPeriods(seedPeriods) {
            const periodMap = new Map();
            
            // First, add existing periods from seed data
            (seedPeriods || []).forEach(p => {
                if (!periodMap.has(p.period_number)) {
                    periodMap.set(p.period_number, {
                        label: p.is_break ? breakLabel : `${periodLabel} ${p.period_number}`,
                        starts_at: this.extractTime(p.starts_at),
                        ends_at: this.extractTime(p.ends_at),
                        is_break: !!p.is_break
                    });
                }
            });
            
            // Find the maximum period number from the seed data
            let maxPeriodNumber = 0;
            periodMap.forEach((value, key) => {
                if (key > maxPeriodNumber) {
                    maxPeriodNumber = key;
                }
            });
            
            // For existing timetables, only use periods from database (don't add extra)
            // Only use configured max if there are no periods in database
            const maxPeriods = maxPeriodNumber > 0 ? maxPeriodNumber : (defaults.number_of_periods_per_day || 7);
            
            // If we have fewer periods than the maximum, generate the missing ones
            if (periodMap.size < maxPeriods) {
                let currentMinutes = this.parseTime(defaults.school_start_time) || 480;
                const duration = defaults.minute_per_period || 45;
                const breakDur = defaults.break_duration || 15;
                
                for (let i = 1; i <= maxPeriods; i++) {
                    if (!periodMap.has(i)) {
                        periodMap.set(i, {
                            label: `${periodLabel} ${i}`,
                            starts_at: this.minutesToTime(currentMinutes),
                            ends_at: this.minutesToTime(currentMinutes + duration),
                            is_break: false
                        });
                    }
                    // Move to next period time slot regardless of whether we created it or not
                    currentMinutes += duration + breakDur;
                }
            }
            
            // Return periods in order (1, 2, 3, ...)
            const sortedPeriods = [];
            for (let i = 1; i <= maxPeriods; i++) {
                if (periodMap.has(i)) {
                    sortedPeriods.push(periodMap.get(i));
                }
            }
            
            return sortedPeriods;
        },

        buildEntriesFromPeriods(periods) {
            const entries = {};
            
            // Helper to convert full day to short format
            const convertDayToShort = (day) => {
                const dayMap = {
                    'monday': 'mon',
                    'tuesday': 'tue',
                    'wednesday': 'wed',
                    'thursday': 'thu',
                    'friday': 'fri',
                    'saturday': 'sat',
                    'sunday': 'sun'
                };
                const lowerDay = day.toLowerCase();
                return dayMap[lowerDay] || lowerDay.substring(0, 3);
            };
            
            (periods || []).forEach(p => {
                const dayShort = convertDayToShort(p.day_of_week);
                const key = `${dayShort}|${p.period_number - 1}`;
                entries[key] = {
                    subject_id: p.subject_id || '',
                    teacher_profile_id: p.teacher_profile_id || '',
                    subject_name: p.subject_name || '',
                    teacher_name: p.teacher_name || '',
                };
            });
            
            return entries;
        },

        extractTime(timeStr) {
            if (!timeStr) return '08:00';
            const str = String(timeStr);
            if (/^\d{2}:\d{2}$/.test(str)) return str;
            if (/^\d{2}:\d{2}:\d{2}$/.test(str)) return str.substring(0, 5);
            if (str.includes('T')) return str.split('T')[1]?.substring(0, 5) || '08:00';
            if (str.includes(' ')) return str.split(' ')[1]?.substring(0, 5) || '08:00';
            const match = str.match(/(\d{2}:\d{2})/);
            return match ? match[1] : '08:00';
        },

        generateDefaultPeriods() {
            const periods = [];
            let currentMinutes = this.parseTime(defaults.school_start_time) || 480;
            const count = defaults.number_of_periods_per_day || 7;
            const duration = defaults.minute_per_period || 45;
            const breakDur = defaults.break_duration || 15;
            
            for (let i = 0; i < count; i++) {
                periods.push({
                    label: `${periodLabel} ${i + 1}`,
                    starts_at: this.minutesToTime(currentMinutes),
                    ends_at: this.minutesToTime(currentMinutes + duration),
                    is_break: false
                });
                currentMinutes += duration + breakDur;
            }
            return periods;
        },

        parseTime(timeStr) {
            if (!timeStr || !timeStr.includes(':')) return null;
            const [h, m] = timeStr.split(':').map(Number);
            return isNaN(h) || isNaN(m) ? null : h * 60 + m;
        },

        minutesToTime(totalMinutes) {
            const h = Math.floor((totalMinutes % (24 * 60)) / 60);
            const m = totalMinutes % 60;
            return `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}`;
        },

        dayLabel(day) {
            const map = { mon: 'Mon', tue: 'Tue', wed: 'Wed', thu: 'Thu', fri: 'Fri', sat: 'Sat', sun: 'Sun' };
            return map[day] || day;
        },

        // Format time based on global setting (12h or 24h)
        formatTime(time24) {
            if (!time24) return '';
            if (defaults.time_format === '12h') {
                const [hours, minutes] = time24.split(':').map(Number);
                const period = hours >= 12 ? 'PM' : 'AM';
                const hours12 = hours % 12 || 12;
                return `${hours12}:${minutes.toString().padStart(2, '0')} ${period}`;
            }
            return time24;
        },

        // Format time range (e.g., "08:00-08:45" or "8:00 AM-8:45 AM")
        formatTimeRange(startTime, endTime) {
            return this.formatTime(startTime) + '-' + this.formatTime(endTime);
        },

        cellKey(day, periodIdx) {
            return `${day}|${periodIdx}`;
        },

        hasEntry(day, periodIdx) {
            const key = this.cellKey(day, periodIdx);
            const entry = this.currentTimetable?.entries?.[key];
            
            return entry && entry.subject_id;
        },

        getEntrySubject(day, periodIdx) {
            return this.currentTimetable?.entries?.[this.cellKey(day, periodIdx)]?.subject_name || '';
        },

        getEntryTeacher(day, periodIdx) {
            return this.currentTimetable?.entries?.[this.cellKey(day, periodIdx)]?.teacher_name || '';
        },

        startEdit() {
            if (this.currentTimetable?.is_active) {
                alert('{{ __("time_table.Cannot edit active timetable. Deactivate it first.") }}');
                return;
            }
            this.isEditMode = true;
        },

        cancelEdit() {
            this.currentTimetable = JSON.parse(JSON.stringify(this.originalTimetable));
            this.isEditMode = false;
        },

        closeModal() {
            if (this.isEditMode && JSON.stringify(this.currentTimetable) !== JSON.stringify(this.originalTimetable)) {
                // Use custom confirm dialog
                this.$dispatch('confirm-show', {
                    title: '{{ __("time_table.Unsaved Changes") }}',
                    message: '{{ __("time_table.You have unsaved changes. Are you sure you want to close?") }}',
                    confirmText: '{{ __("time_table.Close Without Saving") }}',
                    cancelText: '{{ __("time_table.Cancel") }}',
                    onConfirm: () => {
                        this.isOpen = false;
                        this.isEditMode = false;
                        this.currentTimetable = null;
                    }
                });
                return;
            }
            this.isOpen = false;
            this.isEditMode = false;
            this.currentTimetable = null;
        },

        // Subject modal
        openSubjectModal(day, periodIdx) {
            this.currentDay = day;
            this.currentPeriodIdx = periodIdx;
            this.subjectSearch = '';
            this.subjectModalOpen = true;
        },

        assignSubject(subject) {
            const key = this.cellKey(this.currentDay, this.currentPeriodIdx);
            if (!this.currentTimetable.entries) this.currentTimetable.entries = {};
            this.currentTimetable.entries[key] = {
                subject_id: subject.id,
                teacher_profile_id: subject.teacher_profile_id || '',
                subject_name: subject.name,
                teacher_name: subject.teacher_name || '',
            };
            this.subjectModalOpen = false;
        },

        clearSlot() {
            const key = this.cellKey(this.currentDay, this.currentPeriodIdx);
            if (this.currentTimetable?.entries) {
                delete this.currentTimetable.entries[key];
            }
            this.subjectModalOpen = false;
        },

        // Period edit
        editPeriodTime(periodIdx) {
            const period = this.currentTimetable.periods[periodIdx];
            this.editingPeriodIdx = periodIdx;
            this.periodEditData = { ...period };
            this.periodEditModalOpen = true;
        },

        savePeriodEdit() {
            if (this.editingPeriodIdx === null) return;
            const period = this.currentTimetable.periods[this.editingPeriodIdx];
            Object.assign(period, this.periodEditData);
            if (period.is_break) {
                this.currentTimetable.week_days.forEach(day => {
                    delete this.currentTimetable.entries?.[this.cellKey(day, this.editingPeriodIdx)];
                });
            }
            this.periodEditModalOpen = false;
        },

        addPeriod(type) {
            const tt = this.currentTimetable;
            const lastPeriod = tt.periods[tt.periods.length - 1];
            const lastEndMinutes = this.parseTime(lastPeriod?.ends_at) || 480;
            const breakDur = tt.break_duration || 15;
            const periodDur = tt.minutes_per_period || 45;
            
            // Start immediately at the end time of the previous period
            const newStart = this.minutesToTime(lastEndMinutes);
            // Use appropriate duration: break duration for breaks, period duration for periods
            const duration = type === 'break' ? breakDur : periodDur;
            const newEnd = this.minutesToTime(lastEndMinutes + duration);
            
            // Find the highest period number from existing periods
            let maxPeriodNumber = 0;
            tt.periods.forEach(p => {
                if (!p.is_break) {
                    // Extract number from label like "Period 10" or "ကာလ 10"
                    const match = p.label.match(/\d+/);
                    if (match) {
                        const num = parseInt(match[0]);
                        if (num > maxPeriodNumber) {
                            maxPeriodNumber = num;
                        }
                    }
                }
            });
            
            const nextPeriodNumber = maxPeriodNumber + 1;
            
            tt.periods.push({
                label: type === 'break' ? breakLabel : `${periodLabel} ${nextPeriodNumber}`,
                starts_at: newStart,
                ends_at: newEnd,
                is_break: type === 'break'
            });
            
            // Initialize empty entries for all days for this new period
            const newPeriodIndex = tt.periods.length - 1;
            if (!tt.entries) {
                tt.entries = {};
            }
            
            // Force empty slots for all days (overwrite any existing data)
            tt.week_days.forEach(day => {
                const key = this.cellKey(day, newPeriodIndex);
                tt.entries[key] = {
                    subject_id: '',
                    teacher_profile_id: '',
                    subject_name: '',
                    teacher_name: ''
                };
            });
        },

        removePeriod(periodIdx) {
            const tt = this.currentTimetable;
            if (tt.periods.length <= 1) return;
            
            tt.week_days.forEach(day => {
                delete tt.entries?.[this.cellKey(day, periodIdx)];
            });
            
            // Shift entries
            const newEntries = {};
            Object.keys(tt.entries || {}).forEach(key => {
                const [day, pIdx] = key.split('|');
                const pIdxNum = parseInt(pIdx);
                if (pIdxNum > periodIdx) {
                    newEntries[this.cellKey(day, pIdxNum - 1)] = tt.entries[key];
                } else if (pIdxNum < periodIdx) {
                    newEntries[key] = tt.entries[key];
                }
            });
            tt.entries = newEntries;
            tt.periods.splice(periodIdx, 1);
            
            // Renumber
            let num = 1;
            tt.periods.forEach(p => {
                if (!p.is_break) p.label = `${periodLabel} ${num++}`;
            });
        },

        // Settings
        openSettingsModal() {
            const tt = this.currentTimetable;
            this.settingsData = {
                school_start_time: tt.school_start_time,
                school_end_time: tt.school_end_time,
                minutes_per_period: tt.minutes_per_period,
                break_duration: tt.break_duration,
                week_days: [...tt.week_days],
            };
            this.settingsModalOpen = true;
        },

        toggleDay(day) {
            const idx = this.settingsData.week_days.indexOf(day);
            if (idx > -1) {
                this.settingsData.week_days.splice(idx, 1);
            } else {
                this.settingsData.week_days.push(day);
                this.settingsData.week_days.sort((a, b) => this.allDays.indexOf(a) - this.allDays.indexOf(b));
            }
        },

        applySettings() {
            const tt = this.currentTimetable;
            const oldWeekDays = [...tt.week_days];
            
            // Update settings
            tt.school_start_time = this.settingsData.school_start_time;
            tt.school_end_time = this.settingsData.school_end_time;
            tt.minutes_per_period = this.settingsData.minutes_per_period;
            tt.break_duration = this.settingsData.break_duration;
            tt.week_days = [...this.settingsData.week_days];
            
            // Regenerate periods with new timing settings
            const newPeriods = this.regeneratePeriodsWithSettings(
                tt.periods.length,
                this.settingsData.school_start_time,
                this.settingsData.minutes_per_period,
                this.settingsData.break_duration
            );
            
            // Preserve existing entries but map to new periods
            // Also handle week day changes - remove entries for removed days
            const newEntries = {};
            Object.keys(tt.entries || {}).forEach(key => {
                const [day, pIdx] = key.split('|');
                // Only keep entries for days that are still in week_days
                if (tt.week_days.includes(day) && parseInt(pIdx) < newPeriods.length) {
                    newEntries[key] = tt.entries[key];
                }
            });
            
            tt.periods = newPeriods;
            tt.entries = newEntries;
            
            this.settingsModalOpen = false;
        },

        // Regenerate periods based on new settings
        regeneratePeriodsWithSettings(periodCount, startTime, minutesPerPeriod, breakDuration) {
            const periods = [];
            let currentMinutes = this.parseTime(startTime) || 480;
            
            for (let i = 0; i < periodCount; i++) {
                periods.push({
                    label: `${periodLabel} ${i + 1}`,
                    starts_at: this.minutesToTime(currentMinutes),
                    ends_at: this.minutesToTime(currentMinutes + minutesPerPeriod),
                    is_break: false
                });
                currentMinutes += minutesPerPeriod + breakDuration;
            }
            return periods;
        },

        // Save timetable
        async saveTimetable(setActive = false) {
            const tt = this.currentTimetable;
            if (!tt) return;
            
            this.isSaving = true;
            
            // Helper function to convert short day format to full format
            const convertDayToFull = (day) => {
                const dayMap = {
                    'mon': 'monday',
                    'tue': 'tuesday', 
                    'wed': 'wednesday',
                    'thu': 'thursday',
                    'fri': 'friday',
                    'sat': 'saturday',
                    'sun': 'sunday'
                };
                const lowerDay = day.toLowerCase();
                return dayMap[lowerDay] || lowerDay;
            };
            
            // Build periods array - IMPORTANT: Create ALL periods for ALL days
            const periods = [];
            tt.periods.forEach((period, pIdx) => {
                tt.week_days.forEach(day => {
                    const entry = tt.entries?.[this.cellKey(day, pIdx)] || {};
                    periods.push({
                        day_of_week: convertDayToFull(day), // Convert to full format
                        period_number: pIdx + 1,
                        starts_at: period.starts_at,
                        ends_at: period.ends_at,
                        is_break: period.is_break ? 1 : 0,
                        subject_id: entry.subject_id || null,
                        teacher_profile_id: entry.teacher_profile_id || null,
                        room_id: null, // Add room_id field
                        notes: null,   // Add notes field
                    });
                });
            });

            const payload = {
                batch_id: config.batchId,
                grade_id: config.gradeId,
                class_id: config.classId,
                week_days: tt.week_days,
                school_start_time: tt.school_start_time,
                school_end_time: tt.school_end_time,
                minutes_per_period: tt.minutes_per_period,
                break_duration: tt.break_duration,
                periods,
            };

            const isUpdate = !tt.isNew && tt.id;
            const endpoint = isUpdate ? `/time-table/${tt.id}` : '/time-table';
            const method = isUpdate ? 'PUT' : 'POST';
            const body = isUpdate ? payload : { timetables: [payload] };

            try {
                const res = await fetch(endpoint, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(body),
                });

                if (!res.ok) {
                    const err = await res.json().catch(() => ({}));
                    throw new Error(err.message || '{{ __("time_table.Failed to save timetable") }}');
                }

                const result = await res.json().catch(() => ({}));
                
                if (setActive && (result.timetable_id || result.id)) {
                    const timetableId = result.timetable_id || result.id;
                    await fetch(`/time-table/${timetableId}/set-active`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        },
                    });
                }

                // Reload page to show updated data
                window.location.reload();
            } catch (e) {
                console.error(e);
                alert(e.message || '{{ __("time_table.Failed to save timetable") }}');
            } finally {
                this.isSaving = false;
            }
        },
    };
}

// Global functions to open modal from outside Alpine
window.openTimetableEditorFor = function(timetableId) {
    window.dispatchEvent(new CustomEvent('open-timetable-editor', { 
        detail: { timetableId: timetableId } 
    }));
};

window.openTimetableEditorCreate = function() {
    window.dispatchEvent(new CustomEvent('open-timetable-editor-create'));
};
</script>
@endpush
