<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-lg">
                <i class="fas fa-chalkboard-teacher"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('teacher_profiles.Profiles') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('teacher_profiles.Teacher Profiles') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10 overflow-x-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Quick Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-stat-card 
                    icon="fas fa-user-check"
                    :title="__('teacher_profiles.Total Active Teachers')"
                    :number="number_format($totals['active'])"
                    :subtitle="($totals['all'] > 0 ? round(($totals['active'] / $totals['all']) * 100, 1) : 0) . '% ' . __('teacher_profiles.active')"
                />
                <x-stat-card 
                    icon="fas fa-users"
                    :title="__('teacher_profiles.Total Teachers')"
                    :number="number_format($totals['all'])"
                    :subtitle="__('teacher_profiles.All profiles')"
                />
            </div>

            @php
                $tableFilters = [
                    [
                        'id' => 'department_id',
                        'name' => 'department_id',
                        'label' => __('teacher_profiles.Department'),
                        'type' => 'select',
                        'placeholder' => __('teacher_profiles.All Departments'),
                        'options' => $departments->pluck('name', 'id')->toArray(),
                    ],
                    [
                        'id' => 'status',
                        'name' => 'status',
                        'label' => __('teacher_profiles.Status'),
                        'type' => 'select',
                        'placeholder' => __('teacher_profiles.All Status'),
                        'options' => [
                            'active' => __('teacher_profiles.Active'),
                            'inactive' => __('teacher_profiles.Inactive'),
                        ],
                    ],
                    [
                        'id' => 'search',
                        'name' => 'search',
                        'label' => __('teacher_profiles.Search'),
                        'type' => 'text',
                        'placeholder' => __('teacher_profiles.Search by name, email, phone, employee ID...'),
                    ],
                ];

                $tableColumns = [
                    [
                        'label' => __('teacher_profiles.Teacher ID'),
                        'render' => fn($profile) => '<p class="text-sm font-mono font-semibold text-gray-900 dark:text-white">' . ($profile->employee_id ?? '—') . '</p>',
                    ],
                    [
                        'label' => __('teacher_profiles.Full Name'),
                        'render' => function($profile) {
                            return '<p class="text-sm font-semibold text-gray-900 dark:text-white">' . e($profile->user->name) . '</p>' .
                                   '<p class="text-xs text-gray-500 dark:text-gray-400">' . e($profile->department?->name ?? '—') . '</p>';
                        },
                    ],
                    [
                        'label' => __('teacher_profiles.Subject'),
                        'render' => fn($profile) => '<span class="text-sm text-gray-700 dark:text-gray-300">' . e($profile->subjects->pluck('name')->join(', ') ?: '—') . '</span>',
                    ],
                    [
                        'label' => __('teacher_profiles.Phone'),
                        'render' => fn($profile) => '<span class="text-sm text-gray-700 dark:text-gray-300">' . e($profile->phone_no ?? $profile->user->phone ?? '—') . '</span>',
                    ],
                    [
                        'label' => __('teacher_profiles.Join Date'),
                        'render' => fn($profile) => '<span class="text-sm text-gray-700 dark:text-gray-300">' . ($profile->hire_date?->format('Y-m-d') ?? '—') . '</span>',
                    ],
                    [
                        'label' => __('teacher_profiles.Status'),
                        'render' => function($profile) {
                            $statusClass = $profile->status === 'active' 
                                ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-100'
                                : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-100';
                            return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold ' . $statusClass . '">' . ucfirst($profile->status ?? 'active') . '</span>';
                        },
                    ],
                ];

                $tableActions = [];
                
                if (auth()->user()->can(App\Enums\PermissionEnum::VIEW_DEPARTMENTS_PROFILES->value)) {
                    $tableActions[] = [
                        'type' => 'link',
                        'url' => fn($profile) => route('teacher-profiles.show', $profile),
                        'icon' => 'fas fa-eye',
                        'title' => __('teacher_profiles.View Details'),
                    ];
                    $tableActions[] = [
                        'type' => 'link',
                        'url' => fn($profile) => route('teacher-profiles.activities', $profile),
                        'icon' => 'fas fa-clipboard-list',
                        'title' => __('teacher_profiles.View Activities'),
                        'class' => 'text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300',
                    ];
                }
                
                if (auth()->user()->can(App\Enums\PermissionEnum::MANAGE_TEACHER_PROFILES->value)) {
                    $tableActions[] = [
                        'type' => 'link',
                        'url' => fn($profile) => route('teacher-profiles.edit', $profile),
                        'icon' => 'fas fa-pen',
                        'title' => 'Edit',
                    ];
                }
            @endphp

            <x-data-table
                :title="__('teacher_profiles.All Teachers')"
                :addButtonText="auth()->user()->can(App\Enums\PermissionEnum::MANAGE_TEACHER_PROFILES->value) ? __('teacher_profiles.Add New Teacher') : null"
                :addButtonAction="auth()->user()->can(App\Enums\PermissionEnum::MANAGE_TEACHER_PROFILES->value) ? 'window.location.href=\'' . route('teacher-profiles.create') . '\'' : null"
                :filters="$tableFilters"
                :columns="$tableColumns"
                :data="$profiles"
                :actions="$tableActions"
                :emptyMessage="__('teacher_profiles.No teacher profiles found')"
                emptyIcon="fas fa-chalkboard-teacher"
            />
        </div>
    </div>

    <script>
        function confirmAction(url, title, message, confirmText) {
            if (typeof Alpine !== 'undefined') {
                window.dispatchEvent(new CustomEvent('confirm-show', {
                    detail: {
                        title: title,
                        message: message,
                        confirmText: confirmText,
                        cancelText: '{{ __('teacher_profiles.Cancel') }}',
                        onConfirm: () => {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = url;
                            form.innerHTML = '@csrf';
                            document.body.appendChild(form);
                            form.submit();
                        }
                    }
                }));
            } else {
                if (confirm(message)) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;
                    form.innerHTML = '@csrf';
                    document.body.appendChild(form);
                    form.submit();
                }
            }
        }
    </script>
</x-app-layout>
