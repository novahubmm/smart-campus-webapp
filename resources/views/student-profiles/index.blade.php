<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg">
                <i class="fas fa-user-graduate"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('student_profiles.Profiles') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('student_profiles.Student Profiles') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10 overflow-x-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Quick Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-stat-card 
                    icon="fas fa-user-graduate"
                    :title="__('student_profiles.Total Active Students')"
                    :number="number_format($totals['active'])"
                    :subtitle="__('student_profiles.All enrolled')"
                />
                <x-stat-card 
                    icon="fas fa-user-plus"
                    :title="__('student_profiles.Total Students')"
                    :number="number_format($totals['all'])"
                    :subtitle="__('student_profiles.All profiles')"
                />
            </div>

            @php
                $tableFilters = [
                    [
                        'id' => 'grade',
                        'name' => 'grade',
                        'label' => __('student_profiles.Grade'),
                        'type' => 'select',
                        'placeholder' => __('student_profiles.All Grades'),
                        'options' => $grades->pluck('name', 'id')->toArray(),
                    ],
                    [
                        'id' => 'class',
                        'name' => 'class',
                        'label' => __('student_profiles.Class'),
                        'type' => 'select',
                        'placeholder' => __('student_profiles.All Classes'),
                        'options' => $classes->pluck('name', 'id')->toArray(),
                    ],
                    [
                        'id' => 'status',
                        'name' => 'status',
                        'label' => __('student_profiles.Status'),
                        'type' => 'select',
                        'placeholder' => __('student_profiles.All Status'),
                        'options' => [
                            'active' => __('student_profiles.Active'),
                            'inactive' => __('student_profiles.Inactive'),
                        ],
                    ],
                    [
                        'id' => 'search',
                        'name' => 'search',
                        'label' => __('student_profiles.Search'),
                        'type' => 'text',
                        'placeholder' => __('student_profiles.Search by name, email, phone, student ID...'),
                    ],
                ];

                $tableColumns = [
                    [
                        'label' => __('student_profiles.Student ID'),
                        'render' => fn($student) => '<p class="text-sm font-mono font-semibold text-gray-900 dark:text-white">' . ($student->student_identifier ?? '—') . '</p>',
                    ],
                    [
                        'label' => __('student_profiles.Full Name'),
                        'render' => function($student) {
                            return '<p class="text-sm font-semibold text-gray-900 dark:text-white">' . e($student->user?->name ?? '—') . '</p>' .
                                   '<p class="text-xs text-gray-500 dark:text-gray-400">' . e($student->formatted_class_name) . '</p>';
                        },
                    ],
                    [
                        'label' => __('student_profiles.Age'),
                        'render' => function($student) {
                            $age = $student->dob ? now()->diff($student->dob) : null;
                            $ageDisplay = $age ? $age->y . ' year ' . $age->m . ' Month' : '—';
                            return '<span class="text-sm text-gray-700 dark:text-gray-300">' . $ageDisplay . '</span>';
                        },
                    ],
                    [
                        'label' => __('student_profiles.Parent Name'),
                        'render' => function($student) {
                            // Try direct fields first, then guardian relationship
                            $parentName = $student->father_name ?? $student->mother_name;
                            if (!$parentName && $student->guardians->isNotEmpty()) {
                                $parentName = $student->guardians->first()->user->name;
                            }
                            return '<span class="text-sm text-gray-700 dark:text-gray-300">' . e($parentName ?? '—') . '</span>';
                        },
                    ],
                    [
                        'label' => __('student_profiles.Phone'),
                        'render' => function($student) {
                            // Try direct fields first, then guardian relationship
                            $phone = $student->father_phone_no ?? $student->mother_phone_no ?? $student->emergency_contact_phone_no;
                            if (!$phone && $student->guardians->isNotEmpty()) {
                                $phone = $student->guardians->first()->user->phone;
                            }
                            return '<span class="text-sm text-gray-700 dark:text-gray-300">' . e($phone ?? '—') . '</span>';
                        },
                    ],
                    [
                        'label' => __('student_profiles.Enrollment Date'),
                        'render' => fn($student) => '<span class="text-sm text-gray-700 dark:text-gray-300">' . ($student->date_of_joining?->format('Y-m-d') ?? '—') . '</span>',
                    ],
                    [
                        'label' => __('student_profiles.Status'),
                        'render' => function($student) {
                            $statusClass = $student->status === 'active' 
                                ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-100'
                                : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-100';
                            return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold ' . $statusClass . '">' . ucfirst($student->status ?? 'active') . '</span>';
                        },
                    ],
                ];

                $tableActions = [];
                
                if (auth()->user()->can('manage student profiles')) {
                    $tableActions[] = [
                        'type' => 'link',
                        'url' => fn($student) => route('student-profiles.show', $student),
                        'icon' => 'fas fa-eye',
                        'title' => __('student_profiles.View Details'),
                    ];
                    
                    $tableActions[] = [
                        'type' => 'link',
                        'url' => fn($student) => route('student-profiles.edit', $student),
                        'icon' => 'fas fa-pen',
                        'title' => __('student_profiles.Edit'),
                    ];
                }
            @endphp

            <x-data-table
                :title="__('student_profiles.All Students')"
                :addButtonText="auth()->user()->can('manage student profiles') ? __('student_profiles.Add New Student') : null"
                :addButtonAction="auth()->user()->can('manage student profiles') ? 'window.location.href=\'' . route('student-profiles.create') . '\'' : null"
                :filters="$tableFilters"
                :columns="$tableColumns"
                :data="$students"
                :actions="$tableActions"
                :emptyMessage="__('student_profiles.No student profiles found')"
                emptyIcon="fas fa-user-graduate"
            />
        </div>
    </div>
</x-app-layout>
