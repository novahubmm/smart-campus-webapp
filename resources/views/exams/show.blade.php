@php
    use Illuminate\Support\Str;
@endphp

<!-- Select2 CSS -->
<link href="/css/select2.min.css" rel="stylesheet" />

<x-app-layout>
    <x-slot name="header">
        <x-page-header 
            icon="fas fa-clipboard-list"
            iconBg="bg-gradient-to-br from-blue-500 to-indigo-600"
            iconColor="text-white shadow-lg"
            subtitle="{{ __('Academics') }}"
            title="{{ __('Exam Details') }}"
        />
    </x-slot>

    <div class="py-6 sm:py-10 overflow-x-hidden" x-data="examDetail()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Back Navigation -->
            <x-back-link 
                href="{{ route('exams.index') }}"
                text="{{ __('Back to Exam Database') }}"
            />

            <!-- Exam Header Card -->
            <x-detail-header 
                icon="fas fa-clipboard-list"
                iconBg="bg-blue-50 dark:bg-blue-900/30"
                iconColor="text-blue-600 dark:text-blue-400"
                title="{{ $exam->name }}"
                subtitle="{{ $exam->exam_id ?? Str::upper(Str::limit($exam->id, 8, '')) }}"
                badge="{{ $statusState['label'] }}"
                badgeColor="{{ $statusState['class'] }}"
            >
                <x-slot name="meta">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-100">{{ $exam->examType?->name ?? '—' }}</span>
                    @if($exam->schoolClass)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-100">{{ $exam->schoolClass->name }}</span>
                    @endif
                    @if($exam->grade)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-100">@gradeName($exam->grade->level)</span>
                    @endif
                    @if($exam->batch)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">{{ $exam->batch->name }}</span>
                    @endif
                </x-slot>
                <x-slot name="actions">
                    <button type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700" @click="openEditModal()">
                        <i class="fas fa-edit"></i>{{ __('Edit Exam') }}
                    </button>
                </x-slot>
            </x-detail-header>

            <!-- Exam Summary -->
            @php
                $summaryRows = [
                    ['label' => __('exam.Exam Name'), 'value' => $exam->name],
                    ['label' => __('exam.Exam ID'), 'value' => '<span class=\'font-mono\'>' . ($exam->exam_id ?? Str::upper(Str::limit($exam->id, 8, ''))) . '</span>'],
                    ['label' => __('exam.Type'), 'value' => $exam->examType?->name ?? '—'],
                    ['label' => __('exam.Grade'), 'value' => $exam->grade ? \App\Helpers\GradeHelper::getLocalizedName($exam->grade->level) : '—'],
                    ['label' => __('exam.Class'), 'value' => $exam->schoolClass ? \App\Helpers\SectionHelper::formatFullClassName($exam->schoolClass->name, $exam->grade?->level) : '—'],
                    ['label' => __('exam.Batch'), 'value' => $exam->batch?->name ?? '—'],
                    ['label' => __('exam.Status'), 'value' => $statusState['label']],
                    ['label' => __('exam.Start Date'), 'value' => $exam->start_date?->format('M d, Y') ?? '—'],
                    ['label' => __('exam.End Date'), 'value' => $exam->end_date?->format('M d, Y') ?? '—'],
                ];
            @endphp
            <x-info-table title="{{ __('exam.Exam Summary') }}" :rows="$summaryRows" />

            <!-- Exam Schedule -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex items-center gap-2 p-4 border-b border-gray-200 dark:border-gray-700">
                    <i class="fas fa-calendar-alt text-blue-500"></i>
                    <h4 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('exam.Exam Schedule') }}</h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('exam.Subject') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('exam.Marks') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('exam.Date') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('exam.Time') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('exam.Room') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($exam->schedules as $schedule)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $schedule->subject?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $schedule->total_marks ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $schedule->exam_date?->format('M d, Y') ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                        @if($schedule->start_time)
                                            {{ $schedule->start_time }}@if($schedule->end_time) - {{ $schedule->end_time }}@endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $schedule->room?->name ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('exam.No schedule found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Exam Results -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-chart-bar text-blue-500"></i>
                        <h4 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('exam.Exam Results') }}</h4>
                    </div>
                    <button type="button" class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-semibold rounded-lg text-green-600 bg-green-50 dark:bg-green-900/30 dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-900/50" @click="openMarkModal()">
                        <i class="fas fa-plus"></i>{{ __('exam.Add Mark') }}
                    </button>
                </div>

                <!-- Subject Filter Tabs -->
                @if($exam->schedules->count() > 0)
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex flex-wrap gap-2">
                        <button type="button" 
                                @click="selectSubject(null)"
                                :class="selectedSubject === null 
                                    ? 'bg-blue-600 text-white border-blue-600' 
                                    : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600 hover:border-blue-400'"
                                class="px-4 py-2 rounded-lg border-2 transition-all font-semibold text-sm">
                            {{ __('exam.All Subjects') }}
                            <span class="ml-1 text-xs opacity-75" x-text="'(' + allMarks.length + ')'"></span>
                        </button>
                        @foreach($exam->schedules as $schedule)
                            @if($schedule->subject)
                            <button type="button" 
                                    @click="selectSubject('{{ $schedule->subject_id }}')"
                                    :class="selectedSubject === '{{ $schedule->subject_id }}' 
                                        ? 'bg-blue-600 text-white border-blue-600' 
                                        : (subjectHasMarks('{{ $schedule->subject_id }}')
                                            ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 border-green-300 dark:border-green-700' 
                                            : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-600 hover:border-blue-400')"
                                    class="relative px-4 py-2 rounded-lg border-2 transition-all">
                                <div class="font-semibold text-sm">{{ $schedule->subject->name }}</div>
                                <div class="text-xs opacity-75">{{ $schedule->total_marks ?? 100 }} {{ __('exam.marks') }}</div>
                                <template x-if="subjectHasMarks('{{ $schedule->subject_id }}')">
                                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-green-500 rounded-full flex items-center justify-center">
                                        <i class="fas fa-check text-white text-[8px]"></i>
                                    </span>
                                </template>
                            </button>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('exam.Student') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('exam.Subject') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('exam.Marks') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('exam.Grade') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('exam.Status') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('exam.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <template x-for="mark in paginatedMarks" :key="mark.id">
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="mark.student_name"></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400" x-text="mark.student_identifier"></p>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300" x-text="mark.subject_name"></td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                        <span x-show="mark.is_absent" class="text-red-500">{{ __('exam.Absent') }}</span>
                                        <span x-show="!mark.is_absent" x-text="mark.marks_obtained + ' / ' + mark.total_marks"></span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span x-show="mark.grade" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-100" x-text="mark.grade"></span>
                                        <span x-show="!mark.grade" class="text-gray-400">—</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span x-show="mark.is_absent" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-100">{{ __('Absent') }}</span>
                                        <span x-show="!mark.is_absent && mark.is_pass" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-100">{{ __('Pass') }}</span>
                                        <span x-show="!mark.is_absent && !mark.is_pass" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-100">{{ __('Fail') }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <button type="button" @click="openEditMarkModal(mark)" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-blue-500 flex items-center justify-center hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30" title="{{ __('Edit') }}">
                                                <i class="fas fa-edit text-xs"></i>
                                            </button>
                                            <button type="button" @click="deleteMark(mark.id)" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-red-500 flex items-center justify-center hover:border-red-400 hover:bg-red-50 dark:hover:bg-red-900/30" title="{{ __('Delete') }}">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    
                    <!-- Empty state -->
                    <div x-show="filteredMarks.length === 0" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        <template x-if="selectedSubject === null">
                            <span>{{ __('No results available yet') }}</span>
                        </template>
                        <template x-if="selectedSubject !== null">
                            <span>{{ __('No results for this subject yet') }}</span>
                        </template>
                    </div>
                </div>

                <!-- Pagination -->
                <div x-show="totalPages > 1" class="flex items-center justify-between px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Showing') }} <span x-text="((currentPage - 1) * perPage) + 1"></span> {{ __('to') }} <span x-text="Math.min(currentPage * perPage, filteredMarks.length)"></span> {{ __('of') }} <span x-text="filteredMarks.length"></span> {{ __('results') }}
                    </div>
                    <div class="flex items-center gap-1">
                        <button type="button" 
                                @click="currentPage = 1" 
                                :disabled="currentPage === 1"
                                class="px-2 py-1 text-sm rounded border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-angle-double-left"></i>
                        </button>
                        <button type="button" 
                                @click="currentPage--" 
                                :disabled="currentPage === 1"
                                class="px-2 py-1 text-sm rounded border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-angle-left"></i>
                        </button>
                        <template x-for="page in visiblePages" :key="page">
                            <button type="button" 
                                    @click="currentPage = page"
                                    :class="currentPage === page ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'"
                                    class="px-3 py-1 text-sm rounded border"
                                    x-text="page">
                            </button>
                        </template>
                        <button type="button" 
                                @click="currentPage++" 
                                :disabled="currentPage === totalPages"
                                class="px-2 py-1 text-sm rounded border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-angle-right"></i>
                        </button>
                        <button type="button" 
                                @click="currentPage = totalPages" 
                                :disabled="currentPage === totalPages"
                                class="px-2 py-1 text-sm rounded border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-angle-double-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>


        <!-- Edit Exam Modal -->
        <div x-show="modals.edit" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeEditModal()">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-xl w-full max-w-4xl my-8 flex flex-col shadow-2xl max-h-[calc(100vh-4rem)]" @click.stop>
                    <form action="{{ route('exams.update', $exam) }}" method="POST" class="flex flex-col min-h-0">
                        @csrf
                        @method('PUT')
                        
                        <!-- Modal Header -->
                        <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-t-xl">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg">
                                    <i class="fas fa-edit"></i>
                                </span>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Edit Exam') }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Update exam details and schedule') }}</p>
                                </div>
                            </div>
                            <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700" @click="closeEditModal()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        
                        <!-- Modal Body -->
                        <div class="flex-1 overflow-y-auto p-5 space-y-6">
                            <!-- Exam Details Section -->
                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                                <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4">{{ __('Exam Details') }}</h4>
                                <input type="hidden" name="exam_id" value="{{ $exam->exam_id }}">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('Exam Name') }} <span class="text-red-500">*</span></label>
                                        <input type="text" name="name" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" value="{{ $exam->name }}" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('Exam Type') }} <span class="text-red-500">*</span></label>
                                        <select name="exam_type_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" required>
                                            <option value="">{{ __('Select type') }}</option>
                                            @foreach($examTypes as $type)
                                                <option value="{{ $type->id }}" @selected($exam->exam_type_id === $type->id)>{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('exam.Grade') }} <span class="text-red-500">*</span></label>
                                        <select name="grade_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" x-model="editGradeId" @change="onEditGradeChange($event)" required>
                                            <option value="">{{ __('Select grade') }}</option>
                                            @foreach($grades as $grade)
                                                <option value="{{ $grade->id }}">@gradeName($grade->level)</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('exam.Class') }} <span class="text-red-500">*</span></label>
                                        <select name="class_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" x-model="editClassId" :disabled="!editGradeId" required>
                                            <option value="">{{ __('exam.Select class') }}</option>
                                            <template x-for="cls in filteredClasses" :key="cls.id">
                                                <option :value="cls.id" x-text="cls.name" :selected="cls.id === editClassId"></option>
                                            </template>
                                        </select>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-show="!editGradeId">{{ __('exam.Select a grade first') }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('Status') }}</label>
                                        <select name="status" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500">
                                            <option value="upcoming" @selected($exam->status === 'upcoming' || (!in_array($exam->status, ['completed', 'results_published', 'results'])))>{{ __('Upcoming') }}</option>
                                            <option value="completed" @selected($exam->status === 'completed')>{{ __('Completed') }}</option>
                                            <option value="results_published" @selected($exam->status === 'results_published' || $exam->status === 'results')>{{ __('Results') }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('Start Date') }} <span class="text-red-500">*</span></label>
                                        <input type="date" name="start_date" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" value="{{ $exam->start_date?->format('Y-m-d') }}" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('End Date') }} <span class="text-red-500">*</span></label>
                                        <input type="date" name="end_date" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" value="{{ $exam->end_date?->format('Y-m-d') }}" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Exam Schedule Section -->
                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                        <i class="fas fa-calendar-alt text-blue-500"></i>{{ __('Exam Schedule') }}
                                    </h4>
                                    <button type="button" class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-semibold rounded-lg text-blue-600 bg-blue-50 dark:bg-blue-900/30 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/50" @click="addScheduleRow()">
                                        <i class="fas fa-plus"></i>{{ __('Add Subject') }}
                                    </button>
                                </div>
                                
                                <!-- Schedule Table -->
                                <template x-if="schedules.length">
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                            <thead class="bg-white dark:bg-gray-800">
                                                <tr>
                                                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('Subject') }}</th>
                                                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('Marks') }}</th>
                                                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('Date') }}</th>
                                                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('Time') }}</th>
                                                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('Room') }}</th>
                                                    <th class="px-3 py-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 w-12"></th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                                <template x-for="(schedule, idx) in schedules" :key="idx">
                                                    <tr>
                                                        <td class="px-2 py-2">
                                                            <input type="hidden" :name="`schedules[${idx}][id]`" x-model="schedule.id">
                                                            <select class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" :name="`schedules[${idx}][subject_id]`" x-model="schedule.subject_id" required>
                                                                <option value="">{{ __('Select') }}</option>
                                                                @foreach($subjects as $subject)
                                                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td class="px-2 py-2">
                                                            <input type="number" step="1" min="1" class="w-20 text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" :name="`schedules[${idx}][total_marks]`" x-model="schedule.total_marks" placeholder="100">
                                                        </td>
                                                        <td class="px-2 py-2">
                                                            <input type="date" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" :name="`schedules[${idx}][exam_date]`" x-model="schedule.exam_date" required>
                                                        </td>
                                                        <td class="px-2 py-2">
                                                            <div class="flex items-center gap-1">
                                                                <input type="time" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" :name="`schedules[${idx}][start_time]`" x-model="schedule.start_time" required>
                                                                <span class="text-gray-400">-</span>
                                                                <input type="time" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" :name="`schedules[${idx}][end_time]`" x-model="schedule.end_time" required>
                                                            </div>
                                                        </td>
                                                        <td class="px-2 py-2">
                                                            <select class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" :name="`schedules[${idx}][room_id]`" x-model="schedule.room_id">
                                                                <option value="">{{ __('Select') }}</option>
                                                                @foreach($rooms as $room)
                                                                    <option value="{{ $room->id }}">{{ $room->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td class="px-2 py-2 text-center">
                                                            <button type="button" class="w-8 h-8 rounded-md text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 flex items-center justify-center" @click="removeScheduleRow(idx)" title="{{ __('Remove') }}">
                                                                <i class="fas fa-trash text-xs"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>
                                </template>
                                
                                <!-- Empty State -->
                                <template x-if="!schedules.length">
                                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                        <i class="fas fa-calendar-times text-4xl mb-3 opacity-50"></i>
                                        <p class="text-sm">{{ __('No subjects added yet. Click "Add Subject" to create exam schedule.') }}</p>
                                    </div>
                                </template>
                            </div>
                        </div>
                        
                        <!-- Modal Footer -->
                        <div class="flex items-center justify-end gap-3 p-5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
                            <button type="button" class="px-4 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" @click="closeEditModal()">{{ __('Cancel') }}</button>
                            <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700">
                                <i class="fas fa-save mr-2"></i>{{ __('Save Changes') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Add Mark Modal -->
        <div x-show="modals.mark" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeMarkModal()">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-xl w-full max-w-2xl shadow-2xl" @click.stop>
                    <form action="{{ route('exams.marks.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="exam_id" value="{{ $exam->id }}">
                        
                        <!-- Modal Header -->
                        <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-t-xl">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 text-white shadow-lg">
                                    <i class="fas fa-clipboard-list"></i>
                                </span>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Record Exam Mark') }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Enter student marks for the exam') }}</p>
                                </div>
                            </div>
                            <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700" @click="closeMarkModal()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        
                        <!-- Modal Body -->
                        <div class="p-5 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('Student') }} <span class="text-red-500">*</span></label>
                                    <select id="markStudentSelect" name="student_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" x-model="markForm.student_id" required>
                                        <option value="">{{ __('Select student') }}</option>
                                        @php
                                            // Get students from the exam's class only
                                            $students = \App\Models\StudentProfile::with('user', 'classModel')
                                                ->when($exam->class_id, fn($q) => $q->where('class_id', $exam->class_id))
                                                ->orderBy('student_identifier')
                                                ->get();
                                        @endphp
                                        @foreach($students as $student)
                                            <option value="{{ $student->id }}">
                                                {{ $student->user?->name ?? $student->student_identifier ?? __('Student') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('Subject') }} <span class="text-red-500">*</span></label>
                                    <select name="subject_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" x-model="markForm.subject_id" required>
                                        <option value="">{{ __('Select subject') }}</option>
                                        @php
                                            // Get subjects from exam schedules only
                                            $examSubjects = $exam->schedules->pluck('subject')->filter()->unique('id');
                                        @endphp
                                        @foreach($examSubjects as $subject)
                                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('Total Marks') }} <span class="text-red-500">*</span></label>
                                    <input type="number" name="total_marks" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" step="0.01" min="1" value="100" x-model="markForm.total_marks" @input="calculateGrade('mark')" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('Marks Obtained') }} <span class="text-red-500">*</span></label>
                                    <input type="number" name="marks_obtained" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" step="0.01" min="0" x-model="markForm.marks_obtained" :disabled="markForm.is_absent" @input="calculateGrade('mark')">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('exam.Grade') }}</label>
                                    <input type="text" name="grade" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-600" x-model="markForm.grade" readonly placeholder="{{ __('Auto-calculated') }}">
                                </div>
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" id="markAbsent" name="is_absent" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" x-model="markForm.is_absent" @change="calculateGrade('mark')">
                                    <label for="markAbsent" class="text-sm text-gray-700 dark:text-gray-200">{{ __('Mark student as absent') }}</label>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('Remark') }}</label>
                                    <textarea name="remark" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" rows="2" placeholder="{{ __('Optional remark') }}"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Modal Footer -->
                        <div class="flex items-center justify-end gap-3 p-5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
                            <button type="button" class="px-4 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" @click="closeMarkModal()">{{ __('Cancel') }}</button>
                            <button type="button" @click="submitMarkForm()" class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-green-600 hover:bg-green-700">
                                <i class="fas fa-save mr-2"></i>{{ __('Save Mark') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Mark Modal -->
        <div x-show="modals.editMark" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeEditMarkModal()">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-xl w-full max-w-2xl shadow-2xl" @click.stop>
                    <form :action="'{{ url('exams/marks') }}/' + editMarkForm.id" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <!-- Modal Header -->
                        <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-t-xl">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg">
                                    <i class="fas fa-edit"></i>
                                </span>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Edit Exam Mark') }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Update student marks for the exam') }}</p>
                                </div>
                            </div>
                            <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700" @click="closeEditMarkModal()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        
                        <!-- Modal Body -->
                        <div class="p-5 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('Student') }}</label>
                                    <input type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-600" :value="editMarkForm.student_name" disabled>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('Subject') }}</label>
                                    <input type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-600" :value="editMarkForm.subject_name" disabled>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('Total Marks') }} <span class="text-red-500">*</span></label>
                                    <input type="number" name="total_marks" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" step="0.01" min="1" x-model="editMarkForm.total_marks" @input="calculateGrade('edit')" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('Marks Obtained') }} <span class="text-red-500">*</span></label>
                                    <input type="number" name="marks_obtained" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" step="0.01" min="0" x-model="editMarkForm.marks_obtained" :disabled="editMarkForm.is_absent" @input="calculateGrade('edit')">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('exam.Grade') }}</label>
                                    <input type="text" name="grade" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-600" x-model="editMarkForm.grade" readonly placeholder="{{ __('Auto-calculated') }}">
                                </div>
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" id="editMarkAbsent" name="is_absent" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" x-model="editMarkForm.is_absent" @change="calculateGrade('edit')">
                                    <label for="editMarkAbsent" class="text-sm text-gray-700 dark:text-gray-200">{{ __('Mark student as absent') }}</label>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('Remark') }}</label>
                                    <textarea name="remark" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" rows="2" x-model="editMarkForm.remark" placeholder="{{ __('Optional remark') }}"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Modal Footer -->
                        <div class="flex items-center justify-end gap-3 p-5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
                            <button type="button" class="px-4 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" @click="closeEditMarkModal()">{{ __('Cancel') }}</button>
                            <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700">
                                <i class="fas fa-save mr-2"></i>{{ __('Update Mark') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Mark Form (hidden) -->
        <form id="deleteMarkForm" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    </div>

    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('js/select2.min.js') }}"></script>
    <script>
        function examDetail() {
            return {
                modals: { edit: false, mark: false, editMark: false },
                selectedSubject: null,
                currentPage: 1,
                perPage: 10,
                allMarks: @js($exam->marks->map(fn($m) => [
                    'id' => $m->id, 
                    'subject_id' => $m->subject_id,
                    'student_id' => $m->student_id,
                    'student_name' => $m->student?->user?->name ?? '—',
                    'student_identifier' => $m->student?->student_identifier ?? '',
                    'subject_name' => $m->subject?->name ?? '—',
                    'marks_obtained' => $m->marks_obtained ?? 0,
                    'total_marks' => $m->total_marks ?? 100,
                    'grade' => $m->grade,
                    'remark' => $m->remark ?? '',
                    'is_absent' => (bool)$m->is_absent,
                    'is_pass' => !$m->is_absent && ($m->marks_obtained >= ($m->total_marks * 0.4)),
                ])->values()->all()),
                schedules: @js($exam->schedules->map(fn($s) => [
                    'id' => $s->id,
                    'subject_id' => $s->subject_id,
                    'room_id' => $s->room_id,
                    'exam_date' => optional($s->exam_date)->format('Y-m-d'),
                    'start_time' => $s->start_time,
                    'end_time' => $s->end_time,
                    'total_marks' => $s->total_marks ?? 100,
                    'passing_marks' => $s->passing_marks ?? 40,
                ])->values()->all()),
                classes: @js($classes->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'grade_id' => $c->grade_id])),
                editGradeId: '{{ $exam->grade_id }}',
                editClassId: '{{ $exam->class_id }}',
                markForm: {
                    student_id: '',
                    subject_id: '',
                    marks_obtained: '',
                    total_marks: 100,
                    grade: '',
                    is_absent: false,
                },
                editMarkForm: {
                    id: '',
                    student_name: '',
                    subject_name: '',
                    marks_obtained: '',
                    total_marks: 100,
                    grade: '',
                    remark: '',
                    is_absent: false,
                },
                get filteredMarks() {
                    if (this.selectedSubject === null) return this.allMarks;
                    return this.allMarks.filter(m => m.subject_id === this.selectedSubject);
                },
                get totalPages() {
                    return Math.ceil(this.filteredMarks.length / this.perPage) || 1;
                },
                get paginatedMarks() {
                    const start = (this.currentPage - 1) * this.perPage;
                    return this.filteredMarks.slice(start, start + this.perPage);
                },
                get visiblePages() {
                    const pages = [];
                    const total = this.totalPages;
                    const current = this.currentPage;
                    
                    let start = Math.max(1, current - 2);
                    let end = Math.min(total, current + 2);
                    
                    if (end - start < 4) {
                        if (start === 1) {
                            end = Math.min(total, start + 4);
                        } else {
                            start = Math.max(1, end - 4);
                        }
                    }
                    
                    for (let i = start; i <= end; i++) {
                        pages.push(i);
                    }
                    return pages;
                },
                subjectHasMarks(subjectId) {
                    return this.allMarks.some(m => m.subject_id === subjectId);
                },
                selectSubject(subjectId) {
                    this.selectedSubject = subjectId;
                    this.currentPage = 1; // Reset to first page when filter changes
                },
                get filteredClasses() {
                    if (!this.editGradeId) return [];
                    return this.classes.filter(c => c.grade_id === this.editGradeId);
                },
                onEditGradeChange(event) {
                    this.editGradeId = event.target.value;
                    this.editClassId = '';
                },
                openEditModal() {
                    this.modals.edit = true;
                    this.editGradeId = '{{ $exam->grade_id }}';
                    this.editClassId = '{{ $exam->class_id }}';
                },
                closeEditModal() {
                    this.modals.edit = false;
                },
                openMarkModal() {
                    this.modals.mark = true;
                    this.markForm = { 
                        student_id: '',
                        subject_id: '',
                        marks_obtained: '', 
                        total_marks: 100,
                        grade: '',
                        is_absent: false 
                    };
                    
                    // Initialize Select2 for student dropdown after modal is visible
                    setTimeout(function() {
                        initMarkStudentSelect2();
                    }, 200);
                },
                validateDuplicateMark(studentId, subjectId) {
                    // Check if this student already has marks for this subject in this exam
                    return this.allMarks.some(mark => 
                        mark.student_id === studentId && mark.subject_id === subjectId
                    );
                },
                closeMarkModal() {
                    this.modals.mark = false;
                    // Destroy Select2 when modal closes
                    destroyMarkStudentSelect2();
                },
                openEditMarkModal(mark) {
                    this.editMarkForm = {
                        id: mark.id,
                        student_name: mark.student_name,
                        subject_name: mark.subject_name,
                        marks_obtained: mark.marks_obtained,
                        total_marks: mark.total_marks,
                        grade: mark.grade || '',
                        remark: mark.remark || '',
                        is_absent: mark.is_absent,
                    };
                    this.modals.editMark = true;
                    // Calculate grade when opening edit modal
                    this.calculateGrade('edit');
                },
                closeEditMarkModal() {
                    this.modals.editMark = false;
                },
                deleteMark(markId) {
                    if (!confirm('{{ __('Are you sure you want to delete this mark?') }}')) {
                        return;
                    }
                    const form = document.getElementById('deleteMarkForm');
                    form.action = '{{ url('exams/marks') }}/' + markId;
                    form.submit();
                },
                addScheduleRow() {
                    this.schedules.push({
                        id: '',
                        subject_id: '',
                        room_id: '',
                        exam_date: '',
                        start_time: '',
                        end_time: '',
                        total_marks: 100,
                        passing_marks: 40,
                    });
                },
                removeScheduleRow(index) {
                    this.schedules.splice(index, 1);
                },
                calculateGrade(formType) {
                    let marks, totalMarks;
                    
                    if (formType === 'mark') {
                        marks = parseFloat(this.markForm.marks_obtained) || 0;
                        totalMarks = parseFloat(this.markForm.total_marks) || 100;
                        
                        if (this.markForm.is_absent) {
                            this.markForm.grade = '';
                            return;
                        }
                    } else if (formType === 'edit') {
                        marks = parseFloat(this.editMarkForm.marks_obtained) || 0;
                        totalMarks = parseFloat(this.editMarkForm.total_marks) || 100;
                        
                        if (this.editMarkForm.is_absent) {
                            this.editMarkForm.grade = '';
                            return;
                        }
                    }
                    
                    if (totalMarks <= 0) return;
                    
                    const percentage = (marks / totalMarks) * 100;
                    let grade;
                    
                    if (percentage >= 90) grade = 'A+';      // 90% - 100%
                    else if (percentage >= 80) grade = 'A';  // 80% - 89%
                    else if (percentage >= 70) grade = 'B+'; // 70% - 79%
                    else if (percentage >= 60) grade = 'B';  // 60% - 69%
                    else if (percentage >= 50) grade = 'C+'; // 50% - 59%
                    else if (percentage >= 40) grade = 'C';  // 40% - 49%
                    else grade = 'F';                        // 0% - 39%
                    
                    if (formType === 'mark') {
                        this.markForm.grade = grade;
                    } else if (formType === 'edit') {
                        this.editMarkForm.grade = grade;
                    }
                },
                submitMarkForm() {
                    // Validate required fields
                    if (!this.markForm.student_id) {
                        alert('Please select a student.');
                        return;
                    }
                    if (!this.markForm.subject_id) {
                        alert('Please select a subject.');
                        return;
                    }
                    
                    // Check for duplicates
                    if (this.validateDuplicateMark(this.markForm.student_id, this.markForm.subject_id)) {
                        alert('This student already has marks recorded for this subject in this exam.');
                        return;
                    }
                    
                    // Submit the form
                    const form = document.querySelector('form[action="{{ route('exams.marks.store') }}"]');
                    if (form) {
                        form.submit();
                    }
                },
            };
        }
        
        // Select2 initialization functions
        function initMarkStudentSelect2() {
            if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 === 'undefined') {
                console.error('jQuery or Select2 not loaded');
                return;
            }
            
            var $select = jQuery('#markStudentSelect');
            if ($select.length === 0) {
                console.error('Student select element not found');
                return;
            }
            
            // Destroy existing Select2 if any
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
            
            // Find the modal container
            var $modal = $select.closest('[x-show="modals.mark"]');
            
            // Initialize Select2
            $select.select2({
                placeholder: '{{ __("Select student") }}',
                allowClear: true,
                width: '100%',
                dropdownParent: $modal.length ? $modal : jQuery('body')
            });
            
            
        }
        
        function destroyMarkStudentSelect2() {
            if (typeof jQuery === 'undefined') return;
            
            var $select = jQuery('#markStudentSelect');
            if ($select.length && $select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
        }
    </script>
</x-app-layout>
