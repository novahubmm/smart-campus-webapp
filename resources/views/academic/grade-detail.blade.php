<x-app-layout>
    <x-slot name="styles">
        <link rel="stylesheet" href="{{ asset('css/academic-management.css') }}?v={{ time() }}">
    </x-slot>
    <x-slot name="header">
        <x-page-header
            icon="fas fa-layer-group"
            iconBg="bg-purple-50 dark:bg-purple-900/30"
            iconColor="text-purple-700 dark:text-purple-200"
            :subtitle="__('academic_management.Academic Management')"
            :title="__('academic_management.Grade Details') . ': ' . \App\Helpers\GradeHelper::getLocalizedName($grade->level)"
        />
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-back-link 
                :href="route('academic-management.index', ['tab' => 'grades'])"
                :text="__('academic_management.Back to Academic Management')"
            />

            <x-detail-header
                icon="fas fa-layer-group"
                iconBg="bg-purple-50 dark:bg-purple-900/30"
                iconColor="text-purple-600 dark:text-purple-400"
                :title="\App\Helpers\GradeHelper::getLocalizedName($grade->level)"
                :subtitle="__('academic_management.Batch') . ': ' . ($grade->batch->name ?? '—')"
                :badge="__('academic_management.Primary')"
                badgeColor="active"
                :editRoute="null"
                :deleteRoute="route('academic-management.grades.destroy', $grade->id)"
                :deleteText="__('academic_management.Delete Grade')"
                :deleteTitle="__('academic_management.Delete Grade')"
                :deleteMessage="__('academic_management.Are you sure you want to delete this grade? This action cannot be undone.')"
            >
                <x-slot name="actions">
                    <button type="button" class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors" onclick="openModal('editGradeModal')">
                        <i class="fas fa-edit"></i>
                        <span>{{ __('academic_management.Edit Grade') }}</span>
                    </button>
                </x-slot>
            </x-detail-header>

            @php
                $totalClasses = $grade->classes->count();
                $totalStudents = $grade->classes->sum(fn($class) => $class->enrolledStudents->count());
                $subjectsCount = $grade->subjects->count();
            @endphp

            <div class="detail-stats-grid">
                <x-stat-card
                    icon="fas fa-layer-group"
                    :title="__('academic_management.Total Classes')"
                    :number="$totalClasses"
                    :subtitle="__('academic_management.Classes')"
                />
                <x-stat-card
                    icon="fas fa-user-graduate"
                    :title="__('academic_management.Total Students')"
                    :number="$totalStudents"
                    :subtitle="__('academic_management.Students')"
                />
                <x-stat-card
                    icon="fas fa-book"
                    :title="__('academic_management.Subjects')"
                    :number="$subjectsCount"
                    :subtitle="__('academic_management.Subjects')"
                />
            </div>

            <x-info-table 
                :title="__('academic_management.Academic Statistics')"
                :rows="[
                    [
                        'label' => __('academic_management.Grade Level'),
                        'value' => \App\Helpers\GradeHelper::getLocalizedName($grade->level)
                    ],
                    [
                        'label' => __('academic_management.Batch'),
                        'value' => e($grade->batch->name ?? '—')
                    ],
                    [
                        'label' => __('academic_management.Category'),
                        'value' => e($grade->gradeCategory->name ?? __('academic_management.Primary'))
                    ],
                    [
                        'label' => __('academic_management.Total Classes'),
                        'value' => $totalClasses
                    ],
                    [
                        'label' => __('academic_management.Total Students'),
                        'value' => $totalStudents
                    ],
                    [
                        'label' => __('academic_management.Created At'),
                        'value' => $grade->created_at?->format('F d, Y') ?? '—'
                    ],
                ]"
            />

            @php
                $classColumns = [
                    [
                        'label' => __('academic_management.Class Name'),
                        'render' => fn($class) => '<a href="' . route('academic-management.classes.show', $class->id) . '" style="font-weight: 600; color: #007AFF;">' . e($class->name) . '</a>',
                    ],
                    [
                        'label' => __('academic_management.Room'),
                        'render' => fn($class) => e($class->room->name ?? '—'),
                    ],
                    [
                        'label' => __('academic_management.Teacher'),
                        'render' => fn($class) => e($class->teacher?->user?->name ?? '—'),
                    ],
                    [
                        'label' => __('academic_management.Students'),
                        'render' => fn($class) => e($class->enrolledStudents->count() ?? 0),
                    ],
                ];
            @endphp

            <x-data-table
                :title="__('academic_management.Classes in') . ' ' . \App\Helpers\GradeHelper::getLocalizedName($grade->level)"
                :columns="$classColumns"
                :data="$grade->classes"
                :actions="[]"
                :show-filters="false"
                table-class="basic-table"
                :empty-message="__('No data available')"
            />

            <x-form-modal 
                id="editGradeModal" 
                title="{{ __('academic_management.Edit Grade') }}" 
                icon="fas fa-layer-group"
                action="{{ route('academic-management.grades.update', $grade->id) }}"
                method="PUT"
                :submitText="__('academic_management.Update Grade')"
                :cancelText="__('academic_management.Cancel')">
                @include('academic.partials.grade-form-fields', [
                    'grade' => $grade,
                    'batches' => $batches ?? collect(),
                    'gradeCategories' => $gradeCategories ?? collect(),
                ])
            </x-form-modal>
        </div>
    </div>
</x-app-layout>
