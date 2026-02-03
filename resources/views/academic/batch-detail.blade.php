<x-app-layout>
    <x-slot name="styles">
        <link rel="stylesheet" href="{{ asset('css/academic-management.css') }}">
    </x-slot>
    <x-slot name="header">
        <x-page-header
            icon="fas fa-calendar-alt"
            iconBg="bg-blue-50 dark:bg-blue-900/30"
            iconColor="text-blue-700 dark:text-blue-200"
            :subtitle="__('academic_management.Academic Management')"
            :title="__('academic_management.Batch Details')"
        />
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-back-link 
                :href="route('academic-management.index', ['tab' => 'batches'])"
                :text="__('academic_management.Back to Academic Management')"
            />

            <x-detail-header
                icon="fas fa-calendar-alt"
                iconBg="bg-blue-50 dark:bg-blue-900/30"
                iconColor="text-blue-600 dark:text-blue-400"
                :title="$batch->name"
                :subtitle="__('academic_management.Academic Year') . ' ' . $batch->name"
                :badge="__('academic_management.Active')"
                badgeColor="active"
                :editRoute="null"
                :deleteRoute="route('academic-management.batches.destroy', $batch->id)"
                :deleteText="__('academic_management.Delete Batch')"
                :deleteTitle="__('academic_management.Delete Batch')"
                :deleteMessage="__('academic_management.Are you sure you want to delete this batch? This action cannot be undone.')"
            >
                <x-slot name="actions">
                    <button type="button" class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors" onclick="openModal('editBatchModal')">
                        <i class="fas fa-edit"></i>
                        <span>{{ __('academic_management.Edit Batch') }}</span>
                    </button>
                </x-slot>
            </x-detail-header>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <x-stat-card 
                    icon="fas fa-calendar-alt"
                    :title="__('academic_management.Batch Name')"
                    :number="$batch->name"
                    subtitle=""
                />
                
                <x-stat-card 
                    icon="fas fa-check-circle"
                    :title="__('academic_management.Status')"
                    :number="__('academic_management.Active')"
                    subtitle=""
                />
                
                <x-stat-card 
                    icon="fas fa-users"
                    :title="__('academic_management.Total Students')"
                    :number="$batch->students_count ?? 0"
                    :subtitle="__('academic_management.Enrolled')"
                />
            </div>

            <x-info-table 
                :title="__('academic_management.Academic Year')"
                :rows="[
                    [
                        'label' => __('academic_management.Start Date'),
                        'value' => optional($batch->start_date)->format('F d, Y') ?? '—'
                    ],
                    [
                        'label' => __('academic_management.End Date'),
                        'value' => optional($batch->end_date)->format('F d, Y') ?? '—'
                    ],
                    [
                        'label' => __('academic_management.Duration'),
                        'value' => $batch->start_date && $batch->end_date 
                            ? $batch->start_date->diffInMonths($batch->end_date) . ' ' . __('academic_management.months')
                            : '—'
                    ],
                ]"
            />

            <x-info-table 
                :title="__('academic_management.Statistics')"
                :rows="[
                    [
                        'label' => __('academic_management.Total Grades'),
                        'value' => $batch->grades->count() ?? 0
                    ],
                    [
                        'label' => __('academic_management.Total Classes'),
                        'value' => $batch->grades->sum(function($grade) { return $grade->classes->count(); }) ?? 0
                    ],
                    [
                        'label' => __('academic_management.Total Students'),
                        'value' => $batch->students_count ?? 0
                    ],
                ]"
            />

            @if($batch->grades && $batch->grades->count() > 0)
            @php
                $gradeColumns = [
                    [
                        'label' => __('academic_management.Grade Name'),
                        'render' => fn($grade) => '<a href="' . route('academic-management.grades.show', $grade->id) . '" style="font-weight: 600; color: #007AFF;">' . \App\Helpers\GradeHelper::getLocalizedName($grade->level) . '</a>',
                    ],
                    [
                        'label' => __('academic_management.Category'),
                        'render' => function($grade) {
                            $categoryName = $grade->gradeCategory?->name ?? __('academic_management.Uncategorized');
                            $categoryColor = $grade->gradeCategory?->color ?? 'gray';
                            return '<span class="category-badge" style="background-color: ' . e($categoryColor) . '20; color: ' . e($categoryColor) . ';">' . e($categoryName) . '</span>';
                        },
                    ],
                    [
                        'label' => __('academic_management.Classes'),
                        'render' => fn($grade) => e($grade->classes->count() ?? 0) . ' ' . __('academic_management.classes'),
                    ],
                    [
                        'label' => __('academic_management.Students'),
                        'render' => function($grade) {
                            $count = \App\Models\StudentProfile::where('grade_id', $grade->id)->count();
                            return e($count) . ' ' . __('academic_management.students');
                        },
                    ],
                ];
            @endphp

            <x-data-table
                :title="__('academic_management.Grades in') . ' ' . $batch->name"
                :columns="$gradeColumns"
                :data="$batch->grades"
                :actions="[]"
                :show-filters="false"
                table-class="basic-table"
            />
            @endif

            <x-form-modal 
                id="editBatchModal" 
                title="{{ __('academic_management.Edit Batch') }}" 
                icon="fas fa-calendar-alt"
                action="{{ route('academic-management.batches.update', $batch->id) }}"
                method="PUT"
                :submitText="__('academic_management.Update Batch')"
                :cancelText="__('academic_management.Cancel')">
                @include('academic.partials.batch-form-fields', ['batch' => $batch])
            </x-form-modal>
        </div>
    </div>
</x-app-layout>
