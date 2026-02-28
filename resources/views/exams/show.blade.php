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
                    @if($exam->grade && $exam->grade->isActive())
                        @if(in_array($exam->status, ['results', 'completed']))
                            <button type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700" @click="openEditModal()">
                                <i class="fas fa-edit"></i>{{ __('Edit Status') }}
                            </button>
                        @else
                            <button type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700" @click="openEditModal()">
                                <i class="fas fa-edit"></i>{{ __('Edit Exam') }}
                            </button>
                        @endif
                    @else
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Cannot edit exam with inactive grade') }}</span>
                    @endif
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
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('exam.Examiner') }}</th>
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
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $schedule->teacher?->user?->name ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('exam.No schedule found') }}</td>
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
                    <div class="flex items-center gap-2">
                        @if($exam->status === 'completed')
                            <button type="button" @click="showPublishConfirm = true" class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-semibold rounded-lg text-white bg-purple-600 hover:bg-purple-700">
                                <i class="fas fa-paper-plane"></i>{{ __('exam.Send Results') }}
                            </button>
                            <button type="button" class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-semibold rounded-lg text-green-600 bg-green-50 dark:bg-green-900/30 dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-900/50" @click="openMarkModal()">
                                <i class="fas fa-plus"></i>{{ __('exam.Add Mark') }}
                            </button>
                        @elseif($exam->status === 'results')
                            <span class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-semibold rounded-lg text-green-600 bg-green-50 dark:bg-green-900/30 dark:text-green-400">
                                <i class="fas fa-check-circle"></i>{{ __('exam.Results Published') }}
                            </span>
                        @else
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Marks can only be added when exam is completed') }}</span>
                        @endif
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30">
                    <div class="flex flex-col sm:flex-row gap-3">
                        <!-- Subject Filter -->
                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">{{ __('exam.Filter by Subject') }}</label>
                            <select x-model="filterSubject" @change="currentPage = 1" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500">
                                <option value="">{{ __('exam.All Subjects') }}</option>
                                @foreach($exam->schedules as $schedule)
                                    @if($schedule->subject)
                                    <option value="{{ $schedule->subject_id }}">{{ $schedule->subject->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Student Search -->
                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">{{ __('exam.Search Student') }}</label>
                            <input type="text" 
                                   x-model="filterStudent" 
                                   @input="currentPage = 1"
                                   placeholder="{{ __('exam.Enter student name') }}"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('exam.Student') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('exam.Subject') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('exam.Marks') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('exam.Grade') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('exam.Status') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('exam.Remark') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('exam.Graded By') }}</th>
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
                                        <div class="flex items-center gap-2">
                                            <template x-if="mark.remark && mark.remark.trim().length > 0">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm text-gray-700 dark:text-gray-300 block overflow-hidden text-ellipsis whitespace-nowrap" style="max-width: 150px;" x-text="mark.remark" :title="mark.remark"></span>
                                                    <button type="button" 
                                                            @click="openViewRemarkModal(mark)"
                                                            class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 flex items-center justify-center hover:border-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 flex-shrink-0"
                                                            title="{{ __('View') }}">
                                                        <i class="fas fa-eye text-xs"></i>
                                                    </button>
                                                </div>
                                            </template>
                                            <template x-if="!mark.remark || mark.remark.trim().length === 0">
                                                <span class="text-gray-400">—</span>
                                            </template>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                        <span x-show="mark.graded_by_name" x-text="mark.graded_by_name"></span>
                                        <span x-show="!mark.graded_by_name" class="text-gray-400">—</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            @if($exam->status === 'completed')
                                            <button type="button" @click="openEditMarkModal(mark)" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-blue-500 flex items-center justify-center hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30" title="{{ __('Edit') }}">
                                                <i class="fas fa-edit text-xs"></i>
                                            </button>
                                            <button type="button" @click="deleteMark(mark.id)" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-red-500 flex items-center justify-center hover:border-red-400 hover:bg-red-50 dark:hover:bg-red-900/30" title="{{ __('Delete') }}">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                            @else
                                            <span class="text-xs text-gray-400 dark:text-gray-500">{{ __('exam.Only editable when completed') }}</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    
                    <!-- Empty state -->
                    <div x-show="filteredMarks.length === 0" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        <template x-if="!filterSubject && !filterStudent">
                            <span>{{ __('exam.No results available yet') }}</span>
                        </template>
                        <template x-if="filterSubject || filterStudent">
                            <span>{{ __('exam.No results match your filters') }}</span>
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
                                    @if(in_array($exam->status, ['results', 'completed']))
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Edit Exam Status') }}</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Only status can be changed for completed/results exams') }}</p>
                                    @else
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Edit Exam') }}</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Update exam details and schedule') }}</p>
                                    @endif
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
                                @php
                                    $isLocked = in_array($exam->status, ['results', 'completed']);
                                @endphp
                                @if($isLocked)
                                    <div class="mb-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                        <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                            <i class="fas fa-info-circle mr-2"></i>{{ __('This exam is locked. Only the status can be changed.') }}
                                        </p>
                                    </div>
                                @endif
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('Exam Name') }} <span class="text-red-500">*</span></label>
                                        <input type="text" name="name" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500 {{ $isLocked ? 'opacity-50 cursor-not-allowed' : '' }}" value="{{ $exam->name }}" {{ $isLocked ? 'readonly' : '' }} required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('Exam Type') }} <span class="text-red-500">*</span></label>
                                        <select name="exam_type_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500 {{ $isLocked ? 'opacity-50 cursor-not-allowed' : '' }}" {{ $isLocked ? 'disabled' : '' }} required>
                                            <option value="">{{ __('Select type') }}</option>
                                            @foreach($examTypes as $type)
                                                <option value="{{ $type->id }}" @selected($exam->exam_type_id === $type->id)>{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                        @if($isLocked)
                                            <input type="hidden" name="exam_type_id" value="{{ $exam->exam_type_id }}">
                                        @endif
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('exam.Grade') }} <span class="text-red-500">*</span></label>
                                        <select name="grade_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500 {{ $isLocked ? 'opacity-50 cursor-not-allowed' : '' }}" x-model="editGradeId" @change="onEditGradeChange($event)" {{ $isLocked ? 'disabled' : '' }} required>
                                            <option value="">{{ __('Select grade') }}</option>
                                            @foreach($grades as $grade)
                                                <option value="{{ $grade->id }}">@gradeName($grade->level)</option>
                                            @endforeach
                                        </select>
                                        @if($isLocked)
                                            <input type="hidden" name="grade_id" value="{{ $exam->grade_id }}">
                                        @endif
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('exam.Class') }} <span class="text-red-500">*</span></label>
                                        <select name="class_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500 {{ $isLocked ? 'opacity-50 cursor-not-allowed' : '' }}" x-model="editClassId" :disabled="!editGradeId || {{ $isLocked ? 'true' : 'false' }}" required>
                                            <option value="">{{ __('exam.Select class') }}</option>
                                            <template x-for="cls in filteredClasses" :key="cls.id">
                                                <option :value="cls.id" x-text="cls.name" :selected="cls.id === editClassId"></option>
                                            </template>
                                        </select>
                                        @if($isLocked)
                                            <input type="hidden" name="class_id" value="{{ $exam->class_id }}">
                                        @endif
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-show="!editGradeId && !{{ $isLocked ? 'true' : 'false' }}">{{ __('exam.Select a grade first') }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('Status') }}</label>
                                        <select name="status" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500">
                                            <option value="upcoming" @selected($exam->status === 'upcoming' || (!in_array($exam->status, ['ongoing', 'completed', 'results'])))>{{ __('Upcoming') }}</option>
                                            <option value="ongoing" @selected($exam->status === 'ongoing')>{{ __('Ongoing') }}</option>
                                            <option value="completed" @selected($exam->status === 'completed')>{{ __('Completed') }}</option>
                                            <option value="results" @selected($exam->status === 'results' || $exam->status === 'results_published')>{{ __('Results') }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('Start Date') }} <span class="text-red-500">*</span></label>
                                        <input type="date" name="start_date" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500 {{ $isLocked ? 'opacity-50 cursor-not-allowed' : '' }}" value="{{ $exam->start_date?->format('Y-m-d') }}" {{ $isLocked ? 'readonly' : '' }} required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('End Date') }} <span class="text-red-500">*</span></label>
                                        <input type="date" name="end_date" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500 {{ $isLocked ? 'opacity-50 cursor-not-allowed' : '' }}" value="{{ $exam->end_date?->format('Y-m-d') }}" {{ $isLocked ? 'readonly' : '' }} required>
                                    </div>
                                </div>
                            </div>

                            <!-- Exam Schedule Section -->
                            @if(!$isLocked)
                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                            @else
                            <!-- Hidden schedule data for locked exams -->
                            @foreach($exam->schedules as $idx => $schedule)
                                <input type="hidden" name="schedules[{{ $idx }}][id]" value="{{ $schedule->id }}">
                                <input type="hidden" name="schedules[{{ $idx }}][subject_id]" value="{{ $schedule->subject_id }}">
                                <input type="hidden" name="schedules[{{ $idx }}][exam_date]" value="{{ $schedule->exam_date?->format('Y-m-d') }}">
                                <input type="hidden" name="schedules[{{ $idx }}][start_time]" value="{{ $schedule->start_time }}">
                                <input type="hidden" name="schedules[{{ $idx }}][end_time]" value="{{ $schedule->end_time }}">
                                <input type="hidden" name="schedules[{{ $idx }}][room_id]" value="{{ $schedule->room_id }}">
                                <input type="hidden" name="schedules[{{ $idx }}][teacher_id]" value="{{ $schedule->teacher_id }}">
                                <input type="hidden" name="schedules[{{ $idx }}][total_marks]" value="{{ $schedule->total_marks }}">
                            @endforeach
                            @endif
                            @if(!$isLocked)
                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <h4 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                            <i class="fas fa-calendar-alt text-blue-500"></i>{{ __('Exam Schedule') }}
                                        </h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-show="!editGradeId">{{ __('Select a grade above to add subjects') }}</p>
                                    </div>
                                    <button type="button" class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-semibold rounded-lg text-blue-600 bg-blue-50 dark:bg-blue-900/30 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/50" :class="{'opacity-50 cursor-not-allowed': !editGradeId}" @click="addAllSubjects()" :disabled="!editGradeId">
                                        <i class="fas fa-plus"></i>{{ __('Add Subjects') }}
                                    </button>
                                </div>
                                
                                <!-- Empty State -->
                                <template x-if="!schedules.length">
                                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                        <i class="fas fa-calendar-times text-4xl mb-3 opacity-50"></i>
                                        <p class="text-sm" x-show="!editGradeId">{{ __('Please select a grade above first, then add subjects to the schedule.') }}</p>
                                        <p class="text-sm" x-show="editGradeId">{{ __('No subjects added yet. Click "Add Subjects" to create exam schedule.') }}</p>
                                    </div>
                                </template>
                                
                                <!-- Schedule Cards - Two Row Layout -->
                                <template x-if="schedules.length">
                                    <div class="space-y-4">
                                        <template x-for="(schedule, idx) in schedules" :key="idx">
                                            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 space-y-3">
                                                <!-- Hidden inputs -->
                                                <input type="hidden" :name="`schedules[${idx}][id]`" :value="schedule.id">
                                                <input type="hidden" :name="`schedules[${idx}][subject_id]`" :value="schedule.subject_id">
                                                
                                                <!-- Row 1: Subject, Date, Time -->
                                                <div class="flex flex-wrap items-end gap-3">
                                                    <div class="flex-1 min-w-[200px]">
                                                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">{{ __('Subject') }} <span class="text-red-500">*</span></label>
                                                        <div class="text-base font-semibold text-gray-900 dark:text-white" x-text="getSubjectName(schedule.subject_id)"></div>
                                                    </div>
                                                    <div class="w-40">
                                                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">{{ __('Date') }} <span class="text-red-500">*</span></label>
                                                        <input type="date" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" :name="`schedules[${idx}][exam_date]`" x-model="schedule.exam_date" required>
                                                    </div>
                                                    <div class="w-64">
                                                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">{{ __('Time') }} <span class="text-red-500">*</span></label>
                                                        <div class="flex items-center gap-2">
                                                            <input type="time" class="w-28 text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" :name="`schedules[${idx}][start_time]`" x-model="schedule.start_time" required>
                                                            <span class="text-gray-400 text-xs">-</span>
                                                            <input type="time" class="w-28 text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" :name="`schedules[${idx}][end_time]`" x-model="schedule.end_time" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Row 2: Room, Examiner, Marks, Delete -->
                                                <div class="flex flex-wrap items-end gap-3">
                                                    <div class="flex-1 min-w-[150px]">
                                                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">{{ __('Room') }}</label>
                                                        <select class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" :name="`schedules[${idx}][room_id]`" x-model="schedule.room_id">
                                                            <option value="">{{ __('Select') }}</option>
                                                            @foreach($rooms as $room)
                                                                <option value="{{ $room->id }}">{{ $room->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="flex-1 min-w-[150px]">
                                                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">{{ __('Examiner') }}</label>
                                                        <select class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" :name="`schedules[${idx}][teacher_id]`" x-model="schedule.teacher_id">
                                                            <option value="">{{ __('Select') }}</option>
                                                            @foreach($teachers as $teacher)
                                                                <option value="{{ $teacher->id }}">{{ $teacher->display_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="w-24">
                                                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">{{ __('Marks') }}</label>
                                                        <input type="number" step="1" min="1" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" :name="`schedules[${idx}][total_marks]`" x-model="schedule.total_marks" placeholder="100">
                                                    </div>
                                                    <div>
                                                        <button type="button" class="px-4 py-2 text-sm font-semibold rounded-lg text-red-600 bg-red-50 dark:bg-red-900/30 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/50 flex items-center gap-2 whitespace-nowrap" @click="removeScheduleRow(idx)">
                                                            <i class="fas fa-trash text-xs"></i>{{ __('Remove') }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                            @endif
                        </div>
                        
                        <!-- Modal Footer -->
                        <div class="flex items-center justify-end gap-3 p-5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
                            <button type="button" class="px-4 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" @click="closeEditModal()">{{ __('Cancel') }}</button>
                            <button type="button" @click="validateAndSubmitEdit()" class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700">
                                <i class="fas fa-save mr-2"></i>{{ __('Save Changes') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Add Mark Modal - All Students List -->
        <div x-show="modals.mark" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeMarkModal()">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-xl w-full max-w-7xl my-8 flex flex-col shadow-2xl max-h-[calc(100vh-4rem)]" @click.stop>
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-t-xl">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 text-white shadow-lg">
                                <i class="fas fa-clipboard-list"></i>
                            </span>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Record Exam Marks') }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Enter marks for all students - auto-saves on blur') }}</p>
                            </div>
                        </div>
                        <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700" @click="closeMarkModal()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <!-- Subject Selection -->
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">{{ __('Select Subject') }} <span class="text-red-500">*</span></label>
                        <select x-model="selectedMarkSubject" @change="loadStudentsForMarking()" class="w-full max-w-md rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500">
                            <option value="">{{ __('Select subject') }}</option>
                            @php
                                $examSubjects = $exam->schedules->pluck('subject')->filter()->unique('id');
                            @endphp
                            @foreach($examSubjects as $subject)
                                <option value="{{ $subject->id }}" data-total-marks="{{ $exam->schedules->where('subject_id', $subject->id)->first()->total_marks ?? 100 }}">{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Modal Body - Students List -->
                    <div class="flex-1 overflow-y-auto p-5">
                        <div x-show="!selectedMarkSubject" class="text-center py-12 text-gray-500 dark:text-gray-400">
                            <i class="fas fa-arrow-up text-4xl mb-3 opacity-50"></i>
                            <p>{{ __('Please select a subject to start entering marks') }}</p>
                        </div>
                        
                        <div x-show="selectedMarkSubject" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('exam.Student') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('exam.Total Marks') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('exam.Marks Obtained') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('exam.Grade') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('exam.Absent') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('exam.Remark') }}</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('exam.Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                    <template x-for="(student, idx) in studentsForMarking" :key="student.id">
                                        <tr>
                                            <td class="px-4 py-3">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="student.name"></p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400" x-text="student.identifier"></p>
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="number" 
                                                       x-model="student.total_marks" 
                                                       @blur="autoSaveMark(student)"
                                                       class="w-20 text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" 
                                                       step="0.01" min="1"
                                                       readonly
                                                       title="Total marks cannot be edited">
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="number" 
                                                       x-model="student.marks_obtained" 
                                                       :disabled="student.is_absent"
                                                       :max="student.total_marks"
                                                       @blur="autoSaveMark(student)"
                                                       @input="validateAndCalculateGrade(student)"
                                                       class="w-20 text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50" 
                                                       step="0.01" min="0"
                                                       :title="'Maximum: ' + student.total_marks">
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-100" x-text="student.grade || '—'"></span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="checkbox" 
                                                       x-model="student.is_absent"
                                                       @change="handleAbsentChange(student)"
                                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="text" 
                                                       x-model="student.remark" 
                                                       @blur="autoSaveMark(student)"
                                                       class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" 
                                                       placeholder="{{ __('Optional') }}"
                                                       maxlength="255">
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <span x-show="student.saving" class="text-blue-500">
                                                    <i class="fas fa-spinner fa-spin"></i>
                                                </span>
                                                <span x-show="student.saved && !student.saving" class="text-green-500">
                                                    <i class="fas fa-check"></i>
                                                </span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end gap-3 p-5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
                        <button type="button" class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700" @click="closeMarkModal()">
                            <i class="fas fa-check mr-2"></i>{{ __('Done') }}
                        </button>
                    </div>
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
                                    <input type="number" name="total_marks" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-600 focus:border-blue-500 focus:ring-blue-500" step="0.01" min="1" x-model="editMarkForm.total_marks" @input="calculateGrade('edit')" readonly title="Total marks cannot be edited">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('Marks Obtained') }} <span class="text-red-500">*</span></label>
                                    <input type="number" name="marks_obtained" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" step="0.01" min="0" :max="editMarkForm.total_marks" x-model="editMarkForm.marks_obtained" :disabled="editMarkForm.is_absent" @input="validateAndCalculateGradeEdit()" :title="'Maximum: ' + editMarkForm.total_marks">
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
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">{{ __('Remark') }}</label>
                                        <span class="text-xs text-gray-500 dark:text-gray-400" x-text="(editMarkForm.remark || '').length + '/255'"></span>
                                    </div>
                                    <textarea name="remark" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" rows="2" x-model="editMarkForm.remark" placeholder="{{ __('Optional remark') }}" maxlength="255"></textarea>
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

        <!-- View Remark Modal -->
        <div x-show="modals.viewRemark" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeViewRemarkModal()">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-2xl w-full" @click.stop>
                    <!-- Header -->
                    <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30">
                                <i class="fas fa-comment-alt text-blue-600 dark:text-blue-400"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('View Remark') }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Full remark details') }}</p>
                            </div>
                        </div>
                        <button type="button" @click="closeViewRemarkModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <!-- Modal Body -->
                    <div class="p-5">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('exam.Student') }}</label>
                                <input type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-600" x-model="viewRemarkData.student_name" disabled>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('exam.Subject') }}</label>
                                <input type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-600" x-model="viewRemarkData.subject_name" disabled>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('Remark') }}</label>
                                <textarea class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-600 focus:border-blue-500 focus:ring-blue-500" rows="4" x-model="viewRemarkData.remark" readonly></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end gap-3 p-5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
                        <button type="button" class="px-4 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" @click="closeViewRemarkModal()">{{ __('Close') }}</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Publish Results Confirmation Modal -->
        <div x-show="showPublishConfirm" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="showPublishConfirm = false">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-md w-full" @click.stop>
                    <!-- Header -->
                    <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30">
                                <i class="fas fa-paper-plane text-purple-600 dark:text-purple-400"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('exam.Publish Results') }}</h3>
                        </div>
                        <button type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" @click="showPublishConfirm = false">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-6 space-y-4">
                        <p class="text-gray-700 dark:text-gray-300">{{ __('exam.Are you sure you want to publish the results? Guardians will be notified.') }}</p>
                        <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-700">
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('exam.Exam') }}:</p>
                            <p class="font-semibold text-gray-900 dark:text-white mt-1">{{ $exam->name }}</p>
                        </div>
                        <div class="flex items-start gap-2 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                            <i class="fas fa-info-circle text-blue-600 dark:text-blue-400 mt-0.5"></i>
                            <p class="text-sm text-blue-700 dark:text-blue-300">{{ __('exam.Guardians will receive a notification about the published results.') }}</p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-end gap-3 p-5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
                        <button type="button" class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" @click="showPublishConfirm = false">
                            <i class="fas fa-times mr-2"></i>{{ __('Cancel') }}
                        </button>
                        <form action="{{ route('exams.publish-results', $exam) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-purple-600 hover:bg-purple-700">
                                <i class="fas fa-paper-plane mr-2"></i>{{ __('exam.Publish Results') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('js/select2.min.js') }}"></script>
    <script>
        function examDetail() {
            return {
                modals: { edit: false, mark: false, editMark: false, viewRemark: false },
                showPublishConfirm: false,
                selectedSubject: null,
                selectedMarkSubject: '',
                studentsForMarking: [],
                filterSubject: '',
                filterStudent: '',
                viewRemarkData: {
                    student_name: '',
                    subject_name: '',
                    remark: ''
                },
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
                    'graded_by_name' => $m->enteredBy?->name ?? null,
                ])->values()->all()),
                schedules: @js($exam->schedules->map(fn($s) => [
                    'id' => $s->id,
                    'subject_id' => $s->subject_id,
                    'room_id' => $s->room_id,
                    'teacher_id' => $s->teacher_id,
                    'exam_date' => optional($s->exam_date)->format('Y-m-d'),
                    'start_time' => $s->start_time,
                    'end_time' => $s->end_time,
                    'total_marks' => $s->total_marks ?? 100,
                    'passing_marks' => $s->passing_marks ?? 40,
                ])->values()->all()),
                classes: @js($classes->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'grade_id' => $c->grade_id])),
                subjects: @js($subjects->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'grade_ids' => $s->grades->pluck('id')->toArray()])),
                allStudents: @js(\App\Models\StudentProfile::with('user', 'classModel')
                    ->when($exam->class_id, fn($q) => $q->where('class_id', $exam->class_id))
                    ->orderBy('student_identifier')
                    ->get()
                    ->map(fn($s) => [
                        'id' => $s->id,
                        'name' => $s->user?->name ?? $s->student_identifier ?? 'Student',
                        'identifier' => $s->student_identifier ?? '',
                    ])->values()->all()),
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
                    let marks = this.allMarks;
                    
                    // Filter by subject
                    if (this.filterSubject) {
                        marks = marks.filter(m => m.subject_id === this.filterSubject);
                    }
                    
                    // Filter by student name
                    if (this.filterStudent) {
                        const search = this.filterStudent.toLowerCase();
                        marks = marks.filter(m => 
                            m.student_name.toLowerCase().includes(search) ||
                            m.student_identifier.toLowerCase().includes(search)
                        );
                    }
                    
                    return marks;
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
                    this.selectedMarkSubject = '';
                    this.studentsForMarking = [];
                },
                closeMarkModal() {
                    this.modals.mark = false;
                    this.selectedMarkSubject = '';
                    this.studentsForMarking = [];
                    // Reload page to refresh marks
                    window.location.reload();
                },
                loadStudentsForMarking() {
                    if (!this.selectedMarkSubject) {
                        this.studentsForMarking = [];
                        return;
                    }
                    
                    // Get total marks for this subject from schedule
                    const schedule = this.schedules.find(s => s.subject_id === this.selectedMarkSubject);
                    const defaultTotalMarks = schedule?.total_marks || 100;
                    
                    // Load all students and check if they have existing marks
                    this.studentsForMarking = this.allStudents.map(student => {
                        const existingMark = this.allMarks.find(m => 
                            m.student_id === student.id && m.subject_id === this.selectedMarkSubject
                        );
                        
                        return {
                            id: student.id,
                            name: student.name,
                            identifier: student.identifier,
                            marks_obtained: existingMark?.marks_obtained || '',
                            total_marks: existingMark?.total_marks || defaultTotalMarks,
                            grade: existingMark?.grade || '',
                            remark: existingMark?.remark || '',
                            is_absent: existingMark?.is_absent || false,
                            mark_id: existingMark?.id || null,
                            saving: false,
                            saved: !!existingMark,
                        };
                    });
                },
                calculateStudentGrade(student) {
                    if (student.is_absent) {
                        student.grade = '';
                        return;
                    }
                    
                    const marks = parseFloat(student.marks_obtained) || 0;
                    const totalMarks = parseFloat(student.total_marks) || 100;
                    
                    if (totalMarks <= 0) return;
                    
                    const percentage = (marks / totalMarks) * 100;
                    let grade;
                    
                    if (percentage >= 90) grade = 'A+';
                    else if (percentage >= 80) grade = 'A';
                    else if (percentage >= 70) grade = 'B+';
                    else if (percentage >= 60) grade = 'B';
                    else if (percentage >= 50) grade = 'C+';
                    else if (percentage >= 40) grade = 'C';
                    else grade = 'F';
                    
                    student.grade = grade;
                },
                validateAndCalculateGrade(student) {
                    // Validate marks obtained cannot exceed total marks
                    const marks = parseFloat(student.marks_obtained) || 0;
                    const totalMarks = parseFloat(student.total_marks) || 100;
                    
                    if (marks > totalMarks) {
                        student.marks_obtained = totalMarks;
                        this.showNotification('error', `Marks obtained cannot exceed total marks (${totalMarks})`);
                    }
                    
                    this.calculateStudentGrade(student);
                },
                validateAndCalculateGradeEdit() {
                    // Validate marks obtained cannot exceed total marks
                    const marks = parseFloat(this.editMarkForm.marks_obtained) || 0;
                    const totalMarks = parseFloat(this.editMarkForm.total_marks) || 100;
                    
                    if (marks > totalMarks) {
                        this.editMarkForm.marks_obtained = totalMarks;
                        this.showNotification('error', `Marks obtained cannot exceed total marks (${totalMarks})`);
                    }
                    
                    this.calculateGrade('edit');
                },
                handleAbsentChange(student) {
                    if (student.is_absent) {
                        student.marks_obtained = '';
                        student.grade = '';
                    } else {
                        this.calculateStudentGrade(student);
                    }
                    this.autoSaveMark(student);
                },
                async autoSaveMark(student) {
                    // Don't save if no marks entered and not absent
                    if (!student.is_absent && !student.marks_obtained && student.marks_obtained !== 0) {
                        return;
                    }
                    
                    student.saving = true;
                    
                    try {
                        const formData = new FormData();
                        formData.append('_token', '{{ csrf_token() }}');
                        formData.append('exam_id', '{{ $exam->id }}');
                        formData.append('student_id', student.id);
                        formData.append('subject_id', this.selectedMarkSubject);
                        formData.append('total_marks', student.total_marks || 100);
                        formData.append('marks_obtained', student.is_absent ? 0 : (student.marks_obtained || 0));
                        formData.append('grade', student.grade || '');
                        formData.append('remark', student.remark || '');
                        formData.append('is_absent', student.is_absent ? '1' : '0');
                        
                        let response;
                        if (student.mark_id) {
                            // Update existing mark
                            formData.append('_method', 'PUT');
                            response = await fetch(`/exams/marks/${student.mark_id}`, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                }
                            });
                        } else {
                            // Create new mark
                            response = await fetch('{{ route('exams.marks.store') }}', {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                }
                            });
                        }
                        
                        if (response.ok) {
                            const data = await response.json();
                            if (data.mark_id) {
                                student.mark_id = data.mark_id;
                            }
                            student.saved = true;
                            setTimeout(() => {
                                student.saving = false;
                            }, 500);
                        } else {
                            student.saving = false;
                            alert('Failed to save mark. Please try again.');
                        }
                    } catch (error) {
                        student.saving = false;
                        console.error('Error saving mark:', error);
                        alert('Error saving mark. Please try again.');
                    }
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
                    this.calculateGrade('edit');
                },
                closeEditMarkModal() {
                    this.modals.editMark = false;
                },
                openViewRemarkModal(mark) {
                    this.viewRemarkData = {
                        student_name: mark.student_name,
                        subject_name: mark.subject_name,
                        remark: mark.remark || ''
                    };
                    this.modals.viewRemark = true;
                },
                closeViewRemarkModal() {
                    this.modals.viewRemark = false;
                },
                deleteMark(markId) {
                    if (!confirm('{{ __('Are you sure you want to delete this mark?') }}')) {
                        return;
                    }
                    const form = document.getElementById('deleteMarkForm');
                    form.action = '{{ url('exams/marks') }}/' + markId;
                    form.submit();
                },
                validateAndSubmitEdit() {
                    // Validate schedules
                    if (this.schedules.length === 0) {
                        showToast('{{ __('Please add at least one subject to the exam schedule') }}', 'error');
                        return false;
                    }
                    
                    // Validate each schedule
                    for (let i = 0; i < this.schedules.length; i++) {
                        const schedule = this.schedules[i];
                        const subjectName = this.getSubjectName(schedule.subject_id);
                        
                        // Check if date is filled
                        if (!schedule.exam_date) {
                            showToast(`{{ __('Please select a date for') }} ${subjectName}`, 'error');
                            return false;
                        }
                        
                        // Check if start time is filled
                        if (!schedule.start_time) {
                            showToast(`{{ __('Please select a start time for') }} ${subjectName}`, 'error');
                            return false;
                        }
                        
                        // Check if end time is filled
                        if (!schedule.end_time) {
                            showToast(`{{ __('Please select an end time for') }} ${subjectName}`, 'error');
                            return false;
                        }
                        
                        // Check if end time is after start time
                        if (schedule.start_time >= schedule.end_time) {
                            showToast(`{{ __('End time must be after start time for') }} ${subjectName}`, 'error');
                            return false;
                        }
                        
                        // Check if room is selected
                        if (!schedule.room_id) {
                            showToast(`{{ __('Please select a room for') }} ${subjectName}`, 'error');
                            return false;
                        }
                        
                        // Check if examiner is selected
                        if (!schedule.teacher_id) {
                            showToast(`{{ __('Please select an examiner for') }} ${subjectName}`, 'error');
                            return false;
                        }
                        
                        // Check for conflicts with other schedules
                        for (let j = i + 1; j < this.schedules.length; j++) {
                            const otherSchedule = this.schedules[j];
                            const otherSubjectName = this.getSubjectName(otherSchedule.subject_id);
                            
                            // Only check if same date
                            if (schedule.exam_date === otherSchedule.exam_date) {
                                // Check if times overlap
                                const start1 = schedule.start_time;
                                const end1 = schedule.end_time;
                                const start2 = otherSchedule.start_time;
                                const end2 = otherSchedule.end_time;
                                
                                // Times overlap if: start1 < end2 AND start2 < end1
                                const timesOverlap = start1 < end2 && start2 < end1;
                                
                                if (timesOverlap) {
                                    // Check if same room
                                    if (schedule.room_id === otherSchedule.room_id) {
                                        showToast(`{{ __('Room conflict') }}: ${subjectName} {{ __('and') }} ${otherSubjectName} {{ __('have overlapping times in the same room on') }} ${schedule.exam_date}`, 'error');
                                        return false;
                                    }
                                    
                                    // Check if same examiner
                                    if (schedule.teacher_id === otherSchedule.teacher_id) {
                                        showToast(`{{ __('Examiner conflict') }}: ${subjectName} {{ __('and') }} ${otherSubjectName} {{ __('have overlapping times with the same examiner on') }} ${schedule.exam_date}`, 'error');
                                        return false;
                                    }
                                }
                            }
                        }
                    }
                    
                    // All validations passed, submit the form
                    const form = document.querySelector('form[action="{{ route('exams.update', $exam) }}"]');
                    if (form) {
                        form.submit();
                    }
                },
                addScheduleRow() {
                    this.schedules.push({
                        id: '',
                        subject_id: '',
                        room_id: '',
                        teacher_id: '',
                        exam_date: '',
                        start_time: '',
                        end_time: '',
                        total_marks: 100,
                        passing_marks: 40,
                    });
                },
                addAllSubjects() {
                    if (!this.editGradeId) {
                        alert('{{ __('Please select a grade first') }}');
                        return;
                    }
                    
                    // Get subjects for the selected grade
                    const gradeSubjects = this.subjects.filter(s => s.grade_ids.includes(this.editGradeId));
                    
                    if (gradeSubjects.length === 0) {
                        alert('{{ __('No subjects found for this grade') }}');
                        return;
                    }
                    
                    // Get existing subject IDs
                    const existingSubjectIds = this.schedules.map(s => s.subject_id);
                    
                    // Get the last schedule for defaults
                    const lastSchedule = this.schedules.length > 0 ? this.schedules[this.schedules.length - 1] : null;
                    
                    // Add subjects that aren't already in the schedule
                    gradeSubjects.forEach(subject => {
                        if (!existingSubjectIds.includes(subject.id)) {
                            const newSchedule = {
                                id: '',
                                subject_id: subject.id,
                                room_id: '',
                                teacher_id: '',
                                exam_date: '',
                                start_time: '',
                                end_time: '',
                                total_marks: 100,
                                passing_marks: 40,
                            };
                            
                            // Copy defaults from last schedule
                            if (lastSchedule) {
                                // Copy time from previous
                                newSchedule.start_time = lastSchedule.start_time || '';
                                newSchedule.end_time = lastSchedule.end_time || '';
                                
                                // Set date to next day of previous
                                if (lastSchedule.exam_date) {
                                    const prevDate = new Date(lastSchedule.exam_date);
                                    prevDate.setDate(prevDate.getDate() + 1);
                                    newSchedule.exam_date = prevDate.toISOString().split('T')[0];
                                }
                            }
                            
                            this.schedules.push(newSchedule);
                        }
                    });
                },
                getSubjectName(subjectId) {
                    const subject = this.subjects.find(s => s.id === subjectId);
                    return subject ? subject.name : '—';
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
    </script>
</x-app-layout>
