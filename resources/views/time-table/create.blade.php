<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg">
                <i class="fas fa-calendar-plus"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('time_table.Scheduling') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('time_table.Time-table Planner') }}</h2>
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

        <!-- Create New Timetable Section -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('time_table.Create New Time-table') }}</h3>
            </div>
            <div class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px] space-y-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('time_table.Choose Class') }}:</label>
                    <select class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white" x-model="selection.class_id">
                        <option value="">{{ __('time_table.Select a class...') }}</option>
                        <template x-for="c in classes" :key="c.id">
                            <option :value="c.id" x-text="c.label"></option>
                        </template>
                    </select>
                </div>
                <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-white font-semibold shadow hover:bg-blue-700 transition-colors" @click="createTimetable">
                    <i class="fas fa-plus"></i><span>{{ __('time_table.Create Time-table') }}</span>
                </button>
            </div>
        </div>

        <!-- New Timetables (In Progress) -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h4 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('time_table.Time-tables') }}</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('time_table.Existing versions and new timetables for selected class') }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">{{ __('time_table.Class') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">{{ __('time_table.Version') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">{{ __('time_table.Status') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">{{ __('time_table.Changes') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">{{ __('time_table.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <template x-if="timetables.length === 0">
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">{{ __('time_table.Select a class and click Create to start') }}</td>
                            </tr>
                        </template>
                        <template x-for="(tt, idx) in timetables" :key="tt.local_id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30" :class="{ 'bg-blue-50/50 dark:bg-blue-900/10': tt.isNew, 'bg-green-50/30 dark:bg-green-900/10': tt.is_active }">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white" x-text="tt.display_label"></td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" :class="tt.isNew ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'" x-text="'v' + tt.version + (tt.isNew ? ' ({{ __('time_table.New') }})' : '')"></span>
                                    <span x-show="tt.version_name" class="text-xs text-gray-500 dark:text-gray-400 ml-1" x-text="tt.version_name"></span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold" :class="statusBadgeClass(tt)" x-text="statusLabel(tt)"></span>
                                </td>
                                <td class="px-4 py-3">
                                    <template x-if="tt.isNew">
                                        <span x-show="tt.isDirty" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-200">{{ __('time_table.Unsaved changes') }}</span>
                                    </template>
                                    <template x-if="tt.isExisting">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('time_table.Saved') }}</span>
                                    </template>
                                    <span x-show="tt.isNew && !tt.isDirty" class="text-gray-400">—</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <button type="button" class="p-2 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700" @click="viewTimetable(idx)" :title="tt.isExisting ? '{{ __('time_table.View') }}' : '{{ __('time_table.Edit') }}'">
                                            <i class="fas" :class="tt.isExisting ? 'fa-eye' : 'fa-edit'"></i>
                                        </button>
                                        <template x-if="tt.isNew">
                                            <button type="button" class="p-2 rounded-lg text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30" @click="removeTimetable(idx)" title="{{ __('time_table.Remove') }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </template>
                                        <template x-if="tt.isExisting && !tt.is_active">
                                            <button type="button" class="p-2 rounded-lg text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30" @click="deleteExistingTimetable(idx)" title="{{ __('time_table.Delete') }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Timetable Editors Container -->
        <div id="schedulesContainer" class="space-y-6">
            <template x-for="(tt, idx) in timetables" :key="tt.local_id">
                <div x-show="tt.isOpen" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
                    <!-- Editor Header -->
                    <div class="flex items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="tt.display_label + ' (v' + tt.version + ')'"></h3>
                            <template x-if="tt.isExisting">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ __('time_table.Read Only') }}</span>
                            </template>
                            <template x-if="tt.isNew">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300">{{ __('time_table.New') }}</span>
                            </template>
                            <template x-if="tt.is_active">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-600 dark:bg-purple-900/40 dark:text-purple-300">
                                    <i class="fas fa-star mr-1"></i>{{ __('time_table.Active') }}
                                </span>
                            </template>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <template x-if="tt.isNew">
                                <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm" @click="openSettingsModal(idx)">
                                    <i class="fas fa-cog"></i><span>{{ __('time_table.Settings') }}</span>
                                </button>
                            </template>
                            <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm" @click="closeTimetableEditor(idx)">
                                <i class="fas fa-times"></i><span>{{ __('time_table.Close') }}</span>
                            </button>
                            <template x-if="tt.isNew">
                                <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600 text-white hover:bg-blue-700 text-sm font-medium" @click="submitTimetable(idx)">
                                    <i class="fas fa-save"></i><span>{{ __('time_table.Save') }}</span>
                                </button>
                            </template>
                            <template x-if="tt.isNew">
                                <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-green-600 text-white hover:bg-green-700 text-sm font-medium" @click="submitAndSetActive(idx)">
                                    <i class="fas fa-star"></i><span>{{ __('time_table.Save & Set Active') }}</span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Periods List (Inline) -->
                    <div class="p-4 bg-gray-50 dark:bg-gray-900/30 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex flex-wrap gap-2 items-center">
                            <template x-for="(period, pIdx) in tt.periods" :key="`period-${tt.local_id}-${pIdx}`">
                                <div class="relative group">
                                    <div 
                                        class="px-3 py-2 rounded-lg border text-sm font-medium transition-colors"
                                        :class="[
                                            period.is_break ? 'bg-amber-50 dark:bg-amber-900/30 border-amber-300 dark:border-amber-700 text-amber-800 dark:text-amber-200' : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white',
                                            tt.isNew ? 'pr-8 cursor-pointer hover:border-blue-400 dark:hover:border-blue-500' : ''
                                        ]"
                                        @click="tt.isNew && editPeriodTime(idx, pIdx)">
                                        <span x-text="period.label + ': ' + period.starts_at + '-' + period.ends_at"></span>
                                    </div>
                                    <template x-if="tt.isNew">
                                        <button type="button" 
                                            class="absolute top-1/2 right-1 -translate-y-1/2 w-5 h-5 rounded-full bg-red-500 text-white text-xs flex items-center justify-center opacity-80 hover:opacity-100 hover:scale-110 transition-all"
                                            @click.stop="removePeriod(idx, pIdx)" title="{{ __('time_table.Remove Period') }}">
                                            <i class="fas fa-times text-[10px]"></i>
                                        </button>
                                    </template>
                                </div>
                            </template>
                            <template x-if="tt.isNew">
                                <button type="button" class="px-3 py-2 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-blue-400 hover:text-blue-600 dark:hover:border-blue-500 dark:hover:text-blue-400 text-sm transition-colors" @click="addPeriod(idx, 'period')">
                                    <i class="fas fa-plus mr-1"></i>{{ __('time_table.Period') }}
                                </button>
                            </template>
                            <template x-if="tt.isNew">
                                <button type="button" class="px-3 py-2 rounded-lg border-2 border-dashed border-amber-300 dark:border-amber-700 text-amber-600 dark:text-amber-400 hover:border-amber-400 hover:text-amber-700 dark:hover:border-amber-500 dark:hover:text-amber-300 text-sm transition-colors" @click="addPeriod(idx, 'break')">
                                    <i class="fas fa-coffee mr-1"></i>{{ __('time_table.Break') }}
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Schedule Grid -->
                    <div class="p-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase w-28">{{ __('time_table.Period') }}</th>
                                    <template x-for="day in tt.week_days" :key="`head-${tt.local_id}-${day}`">
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase" x-text="dayLabel(day)"></th>
                                    </template>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                <template x-for="(period, pIdx) in tt.periods" :key="`row-${tt.local_id}-${pIdx}`">
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-semibold" :class="period.is_break ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-800 dark:text-amber-200' : 'bg-gray-50 dark:bg-gray-900/30 text-gray-900 dark:text-white'">
                                            <div x-text="period.label"></div>
                                            <div class="text-xs font-normal opacity-70" x-text="period.starts_at + '-' + period.ends_at"></div>
                                        </td>
                                        <template x-for="day in tt.week_days" :key="`cell-${tt.local_id}-${day}-${pIdx}`">
                                            <td class="px-2 py-2 text-center">
                                                <template x-if="period.is_break">
                                                    <div class="text-gray-400 dark:text-gray-500">—</div>
                                                </template>
                                                <template x-if="!period.is_break">
                                                    <div 
                                                        class="w-full min-h-[60px] rounded-lg border px-2 py-2 text-left text-sm transition-colors"
                                                        :class="[
                                                            hasEntry(tt, day, pIdx) ? 'border-green-300 dark:border-green-700 bg-green-50 dark:bg-green-900/20' : 'border-gray-300 dark:border-gray-600',
                                                            tt.isNew ? 'border-dashed cursor-pointer hover:border-blue-400 dark:hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20' : 'cursor-default'
                                                        ]"
                                                        @click="tt.isNew && openSubjectModal(idx, day, pIdx)">
                                                        <template x-if="hasEntry(tt, day, pIdx)">
                                                            <div>
                                                                <div class="font-medium text-gray-900 dark:text-white text-xs" x-text="getEntrySubject(tt, day, pIdx)"></div>
                                                                <div class="text-xs text-gray-500 dark:text-gray-400" x-text="getEntryTeacher(tt, day, pIdx)"></div>
                                                            </div>
                                                        </template>
                                                        <template x-if="!hasEntry(tt, day, pIdx)">
                                                            <div class="text-center text-gray-400 dark:text-gray-500" x-text="tt.isNew ? '+' : '—'"></div>
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
            </template>
        </div>

        <!-- Subject Selection Modal -->
        <template x-if="subjectModalOpen">
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4" @click.self="subjectModalOpen = false">
                <div class="w-full max-w-lg bg-white dark:bg-gray-900 rounded-xl shadow-2xl overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('time_table.Select Subject & Teacher') }}</h4>
                        <button type="button" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800" @click="subjectModalOpen = false">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="p-5 space-y-4 max-h-[60vh] overflow-y-auto">
                        <input type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white" placeholder="{{ __('time_table.Search subjects or teachers...') }}" x-model="subjectSearch">
                        <div class="space-y-2">
                            <template x-for="subject in filteredSubjects" :key="subject.id">
                                <button type="button" class="w-full flex items-center gap-3 p-3 rounded-lg border-2 border-gray-200 dark:border-gray-700 hover:border-blue-400 dark:hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors text-left" @click="assignSubject(subject)">
                                    <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                                        <i class="fas fa-book"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900 dark:text-white" x-text="subject.name"></div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400" x-text="subject.teacher_name || '{{ __('time_table.No teacher assigned') }}'"></div>
                                    </div>
                                </button>
                            </template>
                            <template x-if="filteredSubjects.length === 0">
                                <div class="text-center py-8 text-gray-500 dark:text-gray-400">{{ __('time_table.No subjects found') }}</div>
                            </template>
                        </div>
                    </div>
                    <div class="flex justify-between items-center px-5 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                        <button type="button" class="text-sm text-red-600 dark:text-red-400 hover:underline" @click="clearSlot">{{ __('time_table.Clear slot') }}</button>
                        <button type="button" class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600" @click="subjectModalOpen = false">{{ __('time_table.Cancel') }}</button>
                    </div>
                </div>
            </div>
        </template>

        <!-- Period Time Edit Modal -->
        <template x-if="periodEditModalOpen">
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4" @click.self="periodEditModalOpen = false">
                <div class="w-full max-w-md bg-white dark:bg-gray-900 rounded-xl shadow-2xl overflow-hidden">
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
                            <input type="checkbox" id="periodIsBreak" class="rounded border-gray-300 text-amber-600" x-model="periodEditData.is_break">
                            <label for="periodIsBreak" class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('time_table.This is a break (not counted for attendance)') }}</label>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 px-5 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                        <button type="button" class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600" @click="periodEditModalOpen = false">{{ __('time_table.Cancel') }}</button>
                        <button type="button" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700" @click="savePeriodEdit">{{ __('time_table.Save') }}</button>
                    </div>
                </div>
            </div>
        </template>

        <!-- Settings Modal -->
        <template x-if="settingsModalOpen">
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4" @click.self="settingsModalOpen = false">
                <div class="w-full max-w-xl bg-white dark:bg-gray-900 rounded-xl shadow-2xl overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('time_table.Time Period Settings') }}</h4>
                        <button type="button" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700" @click="settingsModalOpen = false">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="p-5 space-y-6 max-h-[70vh] overflow-y-auto">
                        <!-- School Timings -->
                        <div class="space-y-3">
                            <h5 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('time_table.School Timings') }}</h5>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('time_table.School Start Time') }}</label>
                                    <input type="time" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white" x-model="settingsData.school_start_time">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('time_table.School End Time') }}</label>
                                    <input type="time" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white" x-model="settingsData.school_end_time">
                                </div>
                            </div>
                        </div>

                        <!-- Period Duration -->
                        <div class="space-y-3">
                            <h5 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('time_table.Period Duration') }}</h5>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('time_table.Minutes per Period') }}</label>
                                    <input type="number" min="15" max="120" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white" x-model="settingsData.minutes_per_period">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('time_table.Break Duration (minutes)') }}</label>
                                    <input type="number" min="0" max="60" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white" x-model="settingsData.break_duration">
                                </div>
                            </div>
                        </div>

                        <!-- Active Days -->
                        <div class="space-y-3">
                            <h5 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('time_table.Active Days') }}</h5>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="day in allDays" :key="day">
                                    <button type="button" 
                                        class="px-4 py-2 rounded-lg border-2 font-medium text-sm transition-colors"
                                        :class="settingsData.week_days.includes(day) ? 'bg-blue-100 dark:bg-blue-900/30 border-blue-500 text-blue-700 dark:text-blue-300' : 'bg-gray-100 dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400'"
                                        @click="toggleDay(day)" x-text="dayLabel(day)">
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 px-5 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                        <button type="button" class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600" @click="settingsModalOpen = false">{{ __('time_table.Close') }}</button>
                        <button type="button" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700" @click="applySettings">
                            <i class="fas fa-check mr-1"></i>{{ __('time_table.Apply Settings') }}
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    @include('time-table._form-script', [
        'storeUrl' => route('time-table.store'),
        'initialClassId' => $selectedClassId ?? null,
    ])
</x-app-layout>
