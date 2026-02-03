<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-lg">
                <i class="fas fa-users-cog"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('staff_profiles.Profiles') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('staff_profiles.Staff Profiles') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10 overflow-x-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Quick Stats -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <x-stat-card 
                    icon="fas fa-users-cog"
                    :title="__('staff_profiles.Total Staff')"
                    :number="number_format($totals['all'])"
                    :subtitle="__('staff_profiles.All employees')"
                />
                <x-stat-card 
                    icon="fas fa-user-check"
                    :title="__('staff_profiles.Active Staff')"
                    :number="number_format($totals['active'])"
                    :subtitle="($totals['all'] > 0 ? round(($totals['active'] / $totals['all']) * 100, 1) : 0) . '% ' . __('staff_profiles.present')"
                />
                <x-stat-card 
                    icon="fas fa-calendar-times"
                    :title="__('staff_profiles.On Leave')"
                    :number="number_format($totals['on_leave'] ?? 0)"
                    :subtitle="__('staff_profiles.Currently')"
                />
                <x-stat-card 
                    icon="fas fa-building"
                    :title="__('staff_profiles.Departments')"
                    :number="$departments->count()"
                    :subtitle="__('staff_profiles.Active')"
                />
            </div>

            @php
                $tableFilters = [
                    [
                        'id' => 'department_id',
                        'name' => 'department_id',
                        'label' => __('staff_profiles.Department'),
                        'type' => 'select',
                        'placeholder' => __('staff_profiles.All Departments'),
                        'options' => $departments->pluck('name', 'id')->toArray(),
                    ],
                    [
                        'id' => 'status',
                        'name' => 'status',
                        'label' => __('staff_profiles.Status'),
                        'type' => 'select',
                        'placeholder' => __('staff_profiles.All Status'),
                        'options' => [
                            'active' => __('staff_profiles.Active'),
                            'inactive' => __('staff_profiles.Inactive'),
                        ],
                    ],
                    [
                        'id' => 'search',
                        'name' => 'search',
                        'label' => __('staff_profiles.Search'),
                        'type' => 'text',
                        'placeholder' => __('staff_profiles.Search by name, email, phone, employee ID...'),
                    ],
                ];

                $tableColumns = [
                    [
                        'label' => __('staff_profiles.Employee ID'),
                        'render' => fn($profile) => '<p class="text-sm font-mono font-semibold text-gray-900 dark:text-white">' . ($profile->employee_id ?? '—') . '</p>',
                    ],
                    [
                        'label' => __('staff_profiles.Full Name'),
                        'render' => function($profile) {
                            return '<p class="text-sm font-semibold text-gray-900 dark:text-white">' . e($profile->user->name) . '</p>' .
                                   '<p class="text-xs text-gray-500 dark:text-gray-400">' . e($profile->department?->name ?? '—') . '</p>';
                        },
                    ],
                    [
                        'label' => __('staff_profiles.Position'),
                        'render' => fn($profile) => '<span class="text-sm text-gray-700 dark:text-gray-300">' . e($profile->position ?? '—') . '</span>',
                    ],
                    [
                        'label' => __('staff_profiles.Phone'),
                        'render' => fn($profile) => '<span class="text-sm text-gray-700 dark:text-gray-300">' . e($profile->phone_no ?? $profile->user->phone ?? '—') . '</span>',
                    ],
                    [
                        'label' => __('staff_profiles.Email'),
                        'render' => fn($profile) => '<span class="text-sm text-gray-700 dark:text-gray-300">' . e($profile->user->email) . '</span>',
                    ],
                    [
                        'label' => __('staff_profiles.Join Date'),
                        'render' => fn($profile) => '<span class="text-sm text-gray-700 dark:text-gray-300">' . ($profile->hire_date?->format('Y-m-d') ?? '—') . '</span>',
                    ],
                    [
                        'label' => __('staff_profiles.Salary'),
                        'render' => fn($profile) => '<span class="text-sm text-gray-700 dark:text-gray-300">' . ($profile->basic_salary ? number_format($profile->basic_salary, 0) . ' MMK' : '—') . '</span>',
                    ],
                    [
                        'label' => __('staff_profiles.Status'),
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
                        'url' => fn($profile) => route('staff-profiles.show', $profile),
                        'icon' => 'fas fa-eye',
                        'title' => __('staff_profiles.View Details'),
                    ];
                }
                
                if (auth()->user()->can(App\Enums\PermissionEnum::MANAGE_STAFF_PROFILES->value)) {
                    $tableActions[] = [
                        'type' => 'link',
                        'url' => fn($profile) => route('staff-profiles.edit', $profile),
                        'icon' => 'fas fa-pen',
                        'title' => __('staff_profiles.Edit'),
                    ];
                }
            @endphp

            <x-data-table
                :title="__('staff_profiles.All Staff')"
                :addButtonText="auth()->user()->can(App\Enums\PermissionEnum::MANAGE_STAFF_PROFILES->value) ? __('staff_profiles.Add New Staff') : null"
                :addButtonAction="auth()->user()->can(App\Enums\PermissionEnum::MANAGE_STAFF_PROFILES->value) ? 'window.location.href=\'' . route('staff-profiles.create') . '\'' : null"
                :filters="$tableFilters"
                :columns="$tableColumns"
                :data="$profiles"
                :actions="$tableActions"
                :emptyMessage="__('staff_profiles.No staff profiles found')"
                emptyIcon="fas fa-users-cog"
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
                        cancelText: '{{ __('staff_profiles.Cancel') }}',
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
