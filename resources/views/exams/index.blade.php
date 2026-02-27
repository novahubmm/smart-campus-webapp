@php
    use Illuminate\Support\Str;
    $initialFilter = [
        'exam_type_id' => $filter->exam_type_id,
        'batch_id' => $filter->batch_id,
        'grade_id' => $filter->grade_id,
        'class_id' => $filter->class_id,
        'status' => $filter->status,
        'month' => $filter->month,
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg">
                <i class="fas fa-database"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('exams.Academics') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('exams.Exam Database') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10" x-data="examManager({
        exams: @js($examsForFront),
        endpoints: {
            base: '{{ url('exams') }}',
            store: '{{ route('exams.store') }}',
        },
        classes: @js($classes->map(fn($c) => ['id' => $c->id, 'name' => \App\Helpers\SectionHelper::formatFullClassName($c->name, $c->grade?->level), 'grade_id' => $c->grade_id])),
        subjects: @js($subjects->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'grade_ids' => $s->grades->pluck('id')->toArray()])),
    })">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <x-stat-card
                    icon="fas fa-bolt"
                    :title="__('exams.Active Exams')"
                    :number="$stats['active'] ?? 0"
                    :subtitle="__('exams.Currently running')"
                />

                <x-stat-card
                    icon="fas fa-calendar-alt"
                    :title="__('exams.Upcoming Exams')"
                    :number="$stats['upcoming'] ?? 0"
                    :subtitle="__('exams.Scheduled')"
                />

                <x-stat-card
                    icon="fas fa-check-circle"
                    :title="__('exams.Completed Exams')"
                    :number="$stats['completed'] ?? 0"
                    :subtitle="__('exams.Finished')"
                />
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('exams.All Exams') }}</h3>
                    <button type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700" @click="openExamModal()">
                        <i class="fas fa-plus"></i>{{ __('exams.Create Exam') }}
                    </button>
                </div>

                <form method="GET" action="{{ route('exams.index') }}" class="p-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700" x-data="{ filterGradeId: '{{ $filter->grade_id ?? '' }}' }">
                    <div class="grid grid-cols-4 sm:grid-cols-4 lg:grid-cols-7 gap-3">
                        <div class="flex flex-col gap-1">
                            <label for="filterExamType" class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ __('exams.Exam Type') }}</label>
                            <select id="filterExamType" name="exam_type_id" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">{{ __('exams.All Types') }}</option>
                                @foreach($examTypes as $type)
                                    <option value="{{ $type->id }}" @selected($filter->exam_type_id === $type->id)>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label for="filterGrade" class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ __('exams.Grade') }}</label>
                            <select id="filterGrade" name="grade_id" x-model="filterGradeId" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">{{ __('exams.All Grades') }}</option>
                                @foreach($grades as $grade)
                                    <option value="{{ $grade->id }}" @selected($filter->grade_id === $grade->id)>@gradeName($grade->level)</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label for="filterClass" class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ __('exams.Class') }}</label>
                            <select id="filterClass" name="class_id" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">{{ __('exams.All Classes') }}</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" @selected($filter->class_id === $class->id) x-show="!filterGradeId || filterGradeId === '{{ $class->grade_id }}'">@className($class->name, $class->grade?->level)</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label for="filterStatus" class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ __('exams.Status') }}</label>
                            <select id="filterStatus" name="status" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="all">{{ __('exams.All Statuses') }}</option>
                                <option value="upcoming" @selected($filter->status === 'upcoming')>{{ __('exams.Upcoming') }}</option>
                                <option value="completed" @selected($filter->status === 'completed')>{{ __('exams.Completed') }}</option>
                                <option value="results" @selected($filter->status === 'results')>{{ __('exams.Results') }}</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label for="filterMonth" class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ __('exams.Month') }}</label>
                            <input type="month" id="filterMonth" name="month" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500" value="{{ $filter->month }}">
                        </div>
                        <div class="flex items-end gap-2 col-span-2">
                            <button type="submit" class="flex-1 px-3 py-2 text-sm font-semibold rounded-lg text-white bg-gray-800 dark:bg-gray-700 hover:bg-gray-900 dark:hover:bg-gray-600">{{ __('exams.Apply') }}</button>
                            <a href="{{ route('exams.index') }}" class="px-3 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">{{ __('exams.Reset') }}</a>
                        </div>
                    </div>
                </form>

                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <div class="inline-block min-w-full align-middle">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('exams.Exam ID') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('exams.Exam Name') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('exams.Type') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('exams.Subject(s)') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('exams.Status') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('exams.Actions') }}</th>
                                </tr>
                            </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($examsForFront as $exam)
                                <tr x-data="{ exam: @js($exam) }">
                                    <td class="px-4 py-3">
                                        <p class="text-sm font-mono font-semibold text-gray-900 dark:text-white">{{ $exam['exam_id'] ?? '—' }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $exam['name'] }}</p>
                                        @php
                                            $gradeLabel = isset($exam['grade']) ? \App\Helpers\GradeHelper::getLocalizedName($exam['grade']) : '';
                                            $classLabel = $exam['class_name'] ?? '';
                                        @endphp
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-100">{{ $exam['exam_type'] ?? '—' }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ implode(', ', $exam['subject_list']) ?: '—' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold
                                            @if($exam['status_class'] === 'upcoming') bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-100
                                            @elseif($exam['status_class'] === 'completed') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-100
                                            @elseif($exam['status_class'] === 'results') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-100
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                            @endif">{{ $exam['status_label'] }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('exams.show', $exam['id']) }}" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-blue-500 flex items-center justify-center hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30" title="{{ __('exams.View Details') }}">
                                                <i class="fas fa-eye text-xs"></i>
                                            </a>
                                            <button type="button" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-red-500 flex items-center justify-center hover:border-red-400 hover:bg-red-50 dark:hover:bg-red-900/30" title="{{ __('exams.Delete') }}" @click.prevent="submitDelete(exam.id)">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                            <i class="fas fa-clipboard-list text-4xl mb-3 opacity-50"></i>
                                            <p class="text-sm">{{ __('exams.No exams found') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>

                <!-- Pagination -->
                <x-table-pagination :paginator="$exams" />
            </div>
        </div>

        <!-- Exam Modal -->
        <div x-show="modals.exam" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeExamModal()">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-xl w-full max-w-4xl my-8 flex flex-col shadow-2xl max-h-[calc(100vh-4rem)]" @click.stop>
                    <form :action="examAction" method="POST" x-ref="examForm" class="flex flex-col min-h-0">
                        @csrf
                        <template x-if="examMethod === 'PUT'">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <!-- Modal Header -->
                        <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-t-xl">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg">
                                    <i class="fas fa-calendar-plus"></i>
                                </span>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="examMethod === 'PUT' ? '{{ __('exams.Edit Exam') }}' : '{{ __('exams.Create New Exam') }}'"></h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('exams.Fill in the exam details and schedule') }}</p>
                                </div>
                            </div>
                            <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700" @click="closeExamModal()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <!-- Modal Body -->
                        <div class="flex-1 overflow-y-auto p-5 space-y-6">
                            <!-- Exam Details Section -->
                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                                <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4">{{ __('exams.Exam Details') }}</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('exams.Exam Name') }} <span class="text-red-500">*</span></label>
                                        <input type="text" name="name" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" x-model="examForm.name" placeholder="{{ __('exams.Enter exam name') }}" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('exams.Grade') }} <span class="text-red-500">*</span></label>
                                        <select name="grade_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" x-model="examForm.grade_id" @change="onGradeChange()" required>
                                            <option value="">{{ __('exams.Select grade') }}</option>
                                            @foreach($grades as $grade)
                                                <option value="{{ $grade->id }}">@gradeName($grade->level)</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('exams.Class') }} <span class="text-red-500">*</span></label>
                                        <select name="class_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" x-model="examForm.class_id" :disabled="!examForm.grade_id" required>
                                            <option value="">{{ __('exams.Select class') }}</option>
                                            <template x-for="cls in filteredClasses" :key="cls.id">
                                                <option :value="cls.id" x-text="cls.name"></option>
                                            </template>
                                        </select>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-show="!examForm.grade_id">{{ __('exams.Select a grade first') }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('exams.Exam Type') }} <span class="text-red-500">*</span></label>
                                        <select name="exam_type_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" x-model="examForm.exam_type_id" required>
                                            <option value="">{{ __('exams.Select type') }}</option>
                                            @foreach($examTypes as $type)
                                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('exams.Start Date') }} <span class="text-red-500">*</span></label>
                                        <input type="date" name="start_date" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" x-model="examForm.start_date" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('exams.End Date') }} <span class="text-red-500">*</span></label>
                                        <input type="date" name="end_date" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" x-model="examForm.end_date" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Exam Schedule Section -->
                            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <h4 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                            <i class="fas fa-calendar-alt text-blue-500"></i>{{ __('exams.Exam Schedule') }}
                                        </h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-show="!examForm.grade_id">{{ __('exams.Select a grade above to add subjects') }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-show="examForm.grade_id && !examForm.start_date">{{ __('exams.Select a start date to add subjects') }}</p>
                                    </div>
                                    <button type="button" class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-semibold rounded-lg text-blue-600 bg-blue-50 dark:bg-blue-900/30 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/50" :class="{'opacity-50 cursor-not-allowed': !examForm.grade_id || !examForm.start_date}" @click="addAllSubjects()" :disabled="!examForm.grade_id || !examForm.start_date">
                                        <i class="fas fa-plus"></i>{{ __('exams.Add Subjects') }}
                                    </button>
                                </div>

                                <!-- Empty State -->
                                <template x-if="!examForm.schedules.length">
                                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                        <i class="fas fa-calendar-times text-4xl mb-3 opacity-50"></i>
                                        <p class="text-sm" x-show="!examForm.grade_id">{{ __('exams.Please select a grade above first, then add subjects to the schedule.') }}</p>
                                        <p class="text-sm" x-show="examForm.grade_id">{{ __('exams.No subjects added yet. Click "Add Subjects" to create exam schedule.') }}</p>
                                    </div>
                                </template>

                                <!-- Schedule Cards - Two Row Layout -->
                                <template x-if="examForm.schedules.length">
                                    <div class="space-y-4">
                                        <template x-for="(schedule, idx) in examForm.schedules" :key="idx">
                                            <div class="bg-gray-800 dark:bg-gray-900 border border-gray-700 dark:border-gray-600 rounded-lg p-4 space-y-3">
                                                <!-- Hidden inputs -->
                                                <input type="hidden" :name="`schedules[${idx}][subject_id]`" :value="schedule.subject_id">
                                                
                                                <!-- Row 1: Subject, Date, Time -->
                                                <div class="flex flex-wrap items-end gap-3">
                                                    <div class="flex-1 min-w-[200px]">
                                                        <label class="block text-xs font-semibold text-gray-400 mb-1">{{ __('exams.Subject') }} <span class="text-red-500">*</span></label>
                                                        <div class="text-base font-semibold text-white" x-text="getSubjectName(schedule.subject_id)"></div>
                                                    </div>
                                                    <div class="w-40">
                                                        <label class="block text-xs font-semibold text-gray-400 mb-1">{{ __('exams.Date') }} <span class="text-red-500">*</span></label>
                                                        <input type="date" class="w-full text-sm rounded-lg border-gray-600 bg-gray-700 text-gray-200 focus:border-blue-500 focus:ring-blue-500" :name="`schedules[${idx}][exam_date]`" x-model="schedule.exam_date" @change="autoReorderSchedules()" required>
                                                    </div>
                                                    <div class="w-64">
                                                        <label class="block text-xs font-semibold text-gray-400 mb-1">{{ __('exams.Time') }} <span class="text-red-500">*</span></label>
                                                        <div class="flex items-center gap-2">
                                                            <input type="time" class="w-28 text-sm rounded-lg border-gray-600 bg-gray-700 text-gray-200 focus:border-blue-500 focus:ring-blue-500" :name="`schedules[${idx}][start_time]`" x-model="schedule.start_time" required>
                                                            <span class="text-gray-400 text-xs">-</span>
                                                            <input type="time" class="w-28 text-sm rounded-lg border-gray-600 bg-gray-700 text-gray-200 focus:border-blue-500 focus:ring-blue-500" :name="`schedules[${idx}][end_time]`" x-model="schedule.end_time" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Row 2: Room, Examiner, Delete -->
                                                <div class="flex flex-wrap items-end gap-3">
                                                    <div class="flex-1 min-w-[200px]">
                                                        <label class="block text-xs font-semibold text-gray-400 mb-1">{{ __('exams.Room') }}</label>
                                                        <select class="w-full text-sm rounded-lg border-gray-600 bg-gray-700 text-gray-200 focus:border-blue-500 focus:ring-blue-500" :name="`schedules[${idx}][room_id]`" x-model="schedule.room_id">
                                                            <option value="">{{ __('exams.Select') }}</option>
                                                            @foreach($rooms as $room)
                                                                <option value="{{ $room->id }}">{{ $room->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="flex-1 min-w-[200px]">
                                                        <label class="block text-xs font-semibold text-gray-400 mb-1">{{ __('exams.Examiner') }}</label>
                                                        <select class="w-full text-sm rounded-lg border-gray-600 bg-gray-700 text-gray-200 focus:border-blue-500 focus:ring-blue-500" :name="`schedules[${idx}][teacher_id]`" x-model="schedule.teacher_id">
                                                            <option value="">{{ __('exams.Select') }}</option>
                                                            @foreach($teachers as $teacher)
                                                                <option value="{{ $teacher->id }}">{{ $teacher->user?->name ?? __('exams.Teacher') }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <button type="button" class="px-4 py-2 text-sm font-semibold rounded-lg text-red-400 bg-red-900/30 hover:bg-red-900/50 flex items-center gap-2 whitespace-nowrap" @click="removeScheduleRow(idx)">
                                                            <i class="fas fa-trash text-xs"></i>{{ __('exams.Remove') }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="flex items-center justify-end gap-3 p-5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
                            <button type="button" class="px-4 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" @click="closeExamModal()">{{ __('exams.Cancel') }}</button>
                            <button type="button" class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700" @click="submitExam()">
                                <i class="fas fa-save mr-2"></i>{{ __('exams.Save Exam') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <script src="{{ asset('js/exam-schedule.js') }}"></script>
    <script>
        function examManager(options) {
            return {
                exams: options.exams || [],
                modals: { exam: false },
                endpoints: options.endpoints,
                examMethod: 'POST',
                examAction: options.endpoints.store,
                classes: options.classes || [],
                subjects: options.subjects || [],
                selectedGradeId: null,
                scheduleManager: null,
                examForm: {
                    name: '',
                    grade_id: '',
                    class_id: '',
                    exam_type_id: '',
                    start_date: '',
                    end_date: '',
                    status: 'upcoming',
                    schedules: []
                },
                init() {
                    // Initialize schedule manager
                    this.scheduleManager = new ExamScheduleManager({
                        gradeId: this.examForm.grade_id,
                        allSubjects: this.subjects,
                        schedules: this.examForm.schedules,
                        onSchedulesChange: (schedules) => {
                            this.examForm.schedules = schedules;
                        }
                    });
                },
                get filteredClasses() {
                    if (!this.examForm.grade_id) return [];
                    return this.classes.filter(c => c.grade_id === this.examForm.grade_id);
                },
                get filteredSubjects() {
                    // Use grade from form if set, otherwise use selectedGradeId from class
                    const gradeId = this.examForm.grade_id || this.selectedGradeId;
                    if (!gradeId) return [];
                    return this.subjects.filter(s => s.grade_ids.includes(gradeId));
                },
                onGradeChange() {
                    // Update selectedGradeId when grade is manually changed
                    this.selectedGradeId = this.examForm.grade_id;
                    // Reset class and schedules when grade changes
                    this.examForm.class_id = '';
                    this.examForm.schedules = [];
                    
                    // Update schedule manager
                    if (this.scheduleManager) {
                        this.scheduleManager.setGrade(this.examForm.grade_id);
                    }
                },
                openExamModal() {
                    this.modals.exam = true;
                    this.examMethod = 'POST';
                    this.examAction = this.endpoints.store;
                    this.examForm = this.defaultExamForm();
                    this.selectedGradeId = null;
                    
                    // Reinitialize schedule manager
                    this.scheduleManager = new ExamScheduleManager({
                        gradeId: this.examForm.grade_id,
                        allSubjects: this.subjects,
                        schedules: this.examForm.schedules,
                        onSchedulesChange: (schedules) => {
                            this.examForm.schedules = schedules;
                        }
                    });
                },
                closeExamModal() {
                    this.modals.exam = false;
                    this.examForm = this.defaultExamForm();
                    this.selectedGradeId = null;
                },
                submitExam() {
                    // Validate exam basic info
                    if (!this.examForm.name || this.examForm.name.trim() === '') {
                        showToast('{{ __('exams.Please enter exam name') }}', 'error');
                        return false;
                    }
                    
                    if (!this.examForm.exam_type_id) {
                        showToast('{{ __('exams.Please select exam type') }}', 'error');
                        return false;
                    }
                    
                    if (!this.examForm.grade_id) {
                        showToast('{{ __('exams.Please select a grade first') }}', 'error');
                        return false;
                    }
                    
                    if (!this.examForm.class_id) {
                        showToast('{{ __('exams.Please select a class') }}', 'error');
                        return false;
                    }
                    
                    if (!this.examForm.start_date || this.examForm.start_date.trim() === '') {
                        showToast('{{ __('exams.Please select start date') }}', 'error');
                        return false;
                    }
                    
                    if (!this.examForm.end_date || this.examForm.end_date.trim() === '') {
                        showToast('{{ __('exams.Please select end date') }}', 'error');
                        return false;
                    }
                    
                    // Check if end date is after or equal to start date
                    if (this.examForm.start_date && this.examForm.end_date) {
                        const startDate = new Date(this.examForm.start_date);
                        const endDate = new Date(this.examForm.end_date);
                        if (endDate < startDate) {
                            showToast('{{ __('exams.End date must be after or equal to start date') }}', 'error');
                            return false;
                        }
                    }
                    
                    if (!this.examForm.schedules.length) {
                        showToast('{{ __('exams.Please add at least one subject to the schedule') }}', 'error');
                        return false;
                    }
                    
                    // Validate each schedule
                    for (let i = 0; i < this.examForm.schedules.length; i++) {
                        const schedule = this.examForm.schedules[i];
                        const subjectName = this.getSubjectName(schedule.subject_id);
                        
                        // Check if exam date is provided
                        if (!schedule.exam_date) {
                            showToast(`{{ __('exams.Please select exam date for') }} ${subjectName}`, 'error');
                            return false;
                        }
                        
                        // Check if start time is provided
                        if (!schedule.start_time || schedule.start_time.trim() === '') {
                            showToast(`{{ __('exams.Please select a start time for') }} ${subjectName}`, 'error');
                            return false;
                        }
                        
                        // Check if end time is provided
                        if (!schedule.end_time || schedule.end_time.trim() === '') {
                            showToast(`{{ __('exams.Please select an end time for') }} ${subjectName}`, 'error');
                            return false;
                        }
                        
                        // Check if end time is greater than start time
                        if (schedule.end_time <= schedule.start_time) {
                            showToast(`{{ __('exams.End time must be greater than start time for') }} ${subjectName}`, 'error');
                            return false;
                        }
                        
                        // Check if room is selected
                        if (!schedule.room_id) {
                            showToast(`{{ __('exams.Please select a room for') }} ${subjectName}`, 'error');
                            return false;
                        }
                        
                        // Check if examiner is selected
                        if (!schedule.teacher_id) {
                            showToast(`{{ __('exams.Please select an examiner for') }} ${subjectName}`, 'error');
                            return false;
                        }
                        
                        // Check for conflicts with other schedules
                        for (let j = i + 1; j < this.examForm.schedules.length; j++) {
                            const otherSchedule = this.examForm.schedules[j];
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
                                        showToast(`{{ __('exams.Room conflict') }}: ${subjectName} {{ __('exams.and') }} ${otherSubjectName} {{ __('exams.have overlapping times in the same room on') }} ${schedule.exam_date}`, 'error');
                                        return false;
                                    }
                                    
                                    // Check if same examiner
                                    if (schedule.teacher_id === otherSchedule.teacher_id) {
                                        showToast(`{{ __('exams.Examiner conflict') }}: ${subjectName} {{ __('exams.and') }} ${otherSubjectName} {{ __('exams.have overlapping times with the same examiner on') }} ${schedule.exam_date}`, 'error');
                                        return false;
                                    }
                                }
                            }
                        }
                    }
                    
                    // All validations passed, submit the form
                    this.$refs.examForm?.submit();
                },
                addAllSubjects() {
                    if (!this.examForm.grade_id) {
                        showToast('{{ __('exams.Please select a grade first') }}', 'error');
                        return;
                    }
                    
                    if (!this.examForm.start_date) {
                        showToast('{{ __('exams.Please select a start date first') }}', 'error');
                        return;
                    }
                    
                    const result = this.scheduleManager.addAllSubjects(this.examForm.start_date);
                    
                    if (!result.success) {
                        showToast(result.message, 'warning');
                    } else {
                        showToast(result.message, 'success');
                    }
                },
                removeScheduleRow(index) {
                    this.scheduleManager.removeSchedule(index);
                    this.autoReorderSchedules();
                },
                autoReorderSchedules() {
                    // Sort schedules by exam_date, then by start_time
                    this.examForm.schedules.sort((a, b) => {
                        if (a.exam_date !== b.exam_date) {
                            return a.exam_date.localeCompare(b.exam_date);
                        }
                        return a.start_time.localeCompare(b.start_time);
                    });
                },
                getSubjectName(subjectId) {
                    return this.scheduleManager.getSubjectName(subjectId);
                },
                defaultExamForm() {
                    return {
                        name: '',
                        grade_id: '',
                        class_id: '',
                        exam_type_id: '',
                        start_date: '',
                        end_date: '',
                        status: 'upcoming',
                        schedules: [],
                    };
                },
                submitDelete(id) {
                    this.$dispatch('confirm-show', {
                        title: '{{ __('exams.Delete Exam') }}',
                        message: '{{ __('exams.confirm_delete') }}',
                        confirmText: '{{ __('exams.Delete') }}',
                        cancelText: '{{ __('exams.Cancel') }}',
                        onConfirm: () => {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = `${this.endpoints.base}/${id}`;
                            form.innerHTML = `@csrf <input type="hidden" name="_method" value="DELETE">`;
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                },
            };
        }
    </script>
</x-app-layout>
