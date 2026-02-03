<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-lg">
                    <i class="fas fa-user-shield"></i>
                </span>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('roles.Access Control') }}</p>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('roles.Role Management') }}</h2>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10 overflow-x-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Quick Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-stat-card 
                    icon="fas fa-user-shield"
                    :title="__('roles.Total Roles')"
                    :number="$roles->count()"
                    :subtitle="__('roles.System roles')"
                />
                <x-stat-card 
                    icon="fas fa-users"
                    :title="__('roles.Total Users')"
                    :number="$roles->sum('users_count')"
                    :subtitle="__('roles.Assigned users')"
                />
            </div>

            @php
                $tableFilters = [];

                $tableColumns = [
                    [
                        'label' => __('roles.Role Name'),
                        'render' => function($role) {
                            return '<div class="flex items-center gap-3">' .
                                   '<span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400">' .
                                   '<i class="fas fa-shield-alt"></i>' .
                                   '</span>' .
                                   '<p class="text-sm font-semibold text-gray-900 dark:text-white">' . ucfirst($role->name) . '</p>' .
                                   '</div>';
                        },
                    ],
                    [
                        'label' => __('roles.Users'),
                        'render' => fn($role) => '<span class="inline-flex items-center px-2.5 py-1 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 text-xs font-medium">' .
                                                  '<i class="fas fa-users mr-1.5"></i>' . $role->users_count . ' ' . __('roles.users') .
                                                  '</span>',
                    ],
                    [
                        'label' => __('roles.Permissions'),
                        'render' => fn($role) => '<span class="inline-flex items-center px-2.5 py-1 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 text-xs font-medium">' .
                                                  '<i class="fas fa-key mr-1.5"></i>' . $role->permissions_count . ' ' . __('roles.permissions') .
                                                  '</span>',
                    ],
                ];

                $tableActions = [];
                
                if (auth()->user()->can('manage roles')) {
                    $tableActions[] = [
                        'type' => 'link',
                        'url' => fn($role) => route('roles.edit', $role),
                        'icon' => 'fas fa-edit',
                        'title' => __('ui.Edit'),
                    ];
                    
                    // Only show delete for roles with no users
                    $tableActions[] = [
                        'type' => 'delete',
                        'url' => fn($role) => $role->users_count === 0 ? route('roles.destroy', $role) : null,
                        'icon' => 'fas fa-trash',
                        'title' => __('ui.Delete'),
                        'confirmTitle' => __('roles.Delete role'),
                        'confirmMessage' => __('roles.Are you sure you want to delete this role?'),
                        'confirmText' => __('ui.Delete'),
                        'cancelText' => __('ui.Cancel'),
                    ];
                }
            @endphp

            <x-data-table
                :title="__('roles.All Roles')"
                :addButtonText="auth()->user()->can('manage roles') ? __('roles.Add Role') : null"
                :addButtonAction="auth()->user()->can('manage roles') ? 'window.location.href=\'' . route('roles.create') . '\'' : null"
                :filters="$tableFilters"
                :columns="$tableColumns"
                :data="$roles"
                :actions="$tableActions"
                :emptyMessage="__('roles.No roles found')"
                emptyIcon="fas fa-user-shield"
                :showFilters="false"
            />
        </div>
    </div>
</x-app-layout>
