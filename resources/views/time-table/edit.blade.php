@php
    $timetableLabel = $timetable->schoolClass?->name
        ? \App\Helpers\SectionHelper::formatFullClassName($timetable->schoolClass->name, $timetable->grade?->level)
        : __('the selected class');
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('time-table.index') }}" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                <i class="fas fa-arrow-left"></i>
            </a>
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg">
                <i class="fas fa-edit"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('time_table.Scheduling') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('time_table.Edit Time-table') }}</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $timetableLabel }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10 px-4 sm:px-6 lg:px-8 space-y-6 overflow-x-hidden" x-data="timetableForm()" x-init="initPage()">
        @if (session('status'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800 dark:border-green-900/50 dark:bg-green-900/30 dark:text-green-100">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800 dark:border-red-900/50 dark:bg-red-900/30 dark:text-red-100 space-y-1">
                <div class="font-semibold">{{ __('time_table.Please fix the following:') }}</div>
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-5 space-y-5">
            <div class="flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <div class="text-sm uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('time_table.Create timetable') }}</div>
                    <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('time_table.Build multiple grade/class timetables side by side') }}</div>
                    <p class="text-sm text-gray-600 dark:text-gray-300">{{ __('time_table.Pick grade + class to spawn a grid. Save or publish each grid independently; others stay on screen.') }}</p>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 items-end">
                <div class="space-y-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('time_table.Class') }}</label>
                    <select class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white" x-model="selection.class_id">
                        <option value="">{{ __('time_table.Select class') }}</option>
                        <template x-for="c in classes" :key="c.id">
                            <option :value="c.id" x-text="c.label"></option>
                        </template>
                    </select>
                    <p class="text-xs text-gray-500 dark:text-gray-400" x-show="selection.class_id">{{ __('time_table.Subjects auto-filter by this class\'s grade') }}</p>
                </div>
                <div class="flex items-center gap-2 md:justify-end">
                    <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-purple-600 px-4 py-2 text-white font-semibold shadow hover:bg-purple-700 w-full md:w-auto" @click="addTimetable">
                        <i class="fas fa-plus"></i><span>{{ __('time_table.Add timetable grid') }}</span>
                    </button>
                </div>
            </div>

            <template x-if="!timetables.length">
                <div class="border border-dashed border-gray-300 dark:border-gray-700 rounded-lg p-4 text-sm text-gray-600 dark:text-gray-300">
                    {{ __('time_table.Pick a grade and class to start building timetables.') }}
                </div>
            </template>

            <div class="space-y-6">
                <template x-for="(tt, idx) in timetables" :key="tt.local_id">
                    <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4 bg-gray-50 dark:bg-gray-900/40 space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-white" x-text="tt.display_label"></div>
                                <div class="text-xs text-gray-600 dark:text-gray-400">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full" :class="statusBadgeClass(tt.status)">
                                        <span class="w-2 h-2 rounded-full" :class="statusDotClass(tt.status)"></span>
                                        <span x-text="statusLabel(tt.status)"></span>
                                    </span>
                                    <span class="ml-2" x-text="periodSummary(tt)"></span>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-800 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-800" @click="removeTimetable(idx)">
                                    <i class="fas fa-trash"></i><span>{{ __('time_table.Remove') }}</span>
                                </button>
                                <button type="button" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-800 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-800" @click="toggleSettings(idx)">
                                    <i class="fas fa-sliders-h"></i><span>{{ __('time_table.Settings') }}</span>
                                </button>
                                <button type="button" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700" @click="submitTimetable(idx)">
                                    <i class="fas fa-save"></i><span>{{ __('time_table.Save') }}</span>
                                </button>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2" x-show="tt.showSettings">
                            <div class="space-y-1">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('time_table.Minutes per period') }}</label>
                                <input type="number" min="1" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white" x-model="tt.minutes_per_period">
                            </div>
                            <div class="space-y-1">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('time_table.Break duration (minutes)') }}</label>
                                <input type="number" min="0" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white" x-model="tt.break_duration">
                            </div>
                            <div class="space-y-1">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('time_table.School start time') }}</label>
                                <input type="time" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white" x-model="tt.school_start_time">
                            </div>
                            <div class="space-y-1">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('time_table.School end time') }}</label>
                                <input type="time" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white" x-model="tt.school_end_time">
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white" x-text="tt.display_label"></div>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ __('time_table.Click a cell to set subject/teacher or mark break. Days & periods use Settings.') }}</p>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('time_table.Periods per day:') }} <span class="font-semibold" x-text="periodCount"></span></div>
                            </div>

                            <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-800">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('time_table.Period') }}</th>
                                            <template x-for="day in tt.week_days" :key="`head-${tt.local_id}-${day}`">
                                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-300" x-text="dayLabel(day)"></th>
                                            </template>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        <template x-for="pNum in periodCountArray" :key="`row-${tt.local_id}-${pNum}`">
                                            <tr>
                                                <td class="px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                    <div>#<span x-text="pNum"></span></div>
                                                    <div class="text-xs font-normal text-gray-500 dark:text-gray-400" x-text="periodTimeLabel(tt, pNum)"></div>
                                                </td>
                                                <template x-for="day in tt.week_days" :key="`cell-${tt.local_id}-${day}-${pNum}`">
                                                    <td class="px-3 py-2">
                                                        <button type="button" class="w-full rounded-lg border border-dashed border-gray-300 dark:border-gray-700 px-3 py-2 text-left text-sm bg-white dark:bg-gray-900 hover:border-blue-400" @click="openCell(idx, day, pNum)">
                                                            <div class="flex items-center justify-between gap-2">
                                                                <div>
                                                                    <div class="font-medium text-gray-900 dark:text-gray-100" x-text="cellTitle(tt, day, pNum)"></div>
                                                                    <div class="text-xs text-gray-500 dark:text-gray-400" x-text="cellTime(tt, day, pNum)"></div>
                                                                </div>
                                                                <span class="text-xs text-blue-600 dark:text-blue-300">{{ __('time_table.Edit') }}</span>
                                                            </div>
                                                        </button>
                                                    </td>
                                                </template>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <template x-if="modalOpen">
            <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4" @click.self="modalOpen=false">
                <div class="w-full max-w-xl rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-xl p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-white" x-text="dayLabel(modalData.day)"></div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('time_table.Period') }} <span x-text="modalData.period_number"></span></div>
                        </div>
                        <button type="button" class="text-gray-500 hover:text-gray-800 dark:text-gray-400" @click="modalOpen=false">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="grid gap-3 md:grid-cols-2">
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('time_table.Starts at') }}</label>
                            <input type="time" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white" x-model="modalData.starts_at">
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('time_table.Ends at') }}</label>
                            <input type="time" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white" x-model="modalData.ends_at">
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('time_table.Subject') }}</label>
                            <select class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white" x-model="modalData.subject_id" :disabled="modalData.is_break || !activeTimetable" @change="syncTeacher(modalData.subject_id)">
                                <option value="">{{ __('time_table.None') }}</option>
                                <template x-for="s in (activeTimetable?.subjects || [])" :key="s.id">
                                    <option :value="s.id" x-text="subjectLabel(activeTimetable, s.id)"></option>
                                </template>
                            </select>
                        </div>
                        <div class="flex items-center gap-2">
                            <input id="modal_is_break" type="checkbox" class="rounded border-gray-300 text-blue-600" x-model="modalData.is_break" @change="handleBreakToggle(modalData.is_break)">
                            <label for="modal_is_break" class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('time_table.Is break') }}</label>
                        </div>
                    </div>

                    <div class="flex justify-between items-center gap-3">
                        <button type="button" class="text-sm text-red-600 dark:text-red-300 hover:underline" @click="clearCell()">{{ __('time_table.Clear slot') }}</button>
                        <div class="flex gap-2">
                            <button type="button" class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-100" @click="modalOpen=false">{{ __('time_table.Cancel') }}</button>
                            <button type="button" class="px-4 py-2 rounded-lg bg-blue-600 text-white" @click="saveCell()">{{ __('time_table.Save slot') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    </div>

    @include('time-table._form-script', [
        'storeUrl' => route('time-table.store'),
        'updateUrl' => route('time-table.update', $timetable),
        'initialClassId' => $timetable->class_id,
    ])
</x-app-layout>
