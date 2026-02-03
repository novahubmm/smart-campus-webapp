<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg">
                <i class="fas fa-building"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('departments.Academic') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('departments.Department Management') }}
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10 overflow-x-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Quick Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-stat-card 
                    icon="fas fa-layer-group"
                    :title="__('departments.Total Departments')"
                    :number="number_format($totals['all'])"
                    :subtitle="__('departments.All departments')"
                />
                <x-stat-card 
                    icon="fas fa-check-circle"
                    :title="__('departments.Active Departments')"
                    :number="number_format($totals['active'])"
                    :subtitle="__('departments.Currently active')"
                />
            </div>

            @php
                $tableFilters = [
                    [
                        'id' => 'status',
                        'name' => 'status',
                        'label' => __('departments.Status'),
                        'type' => 'select',
                        'placeholder' => __('departments.All Status'),
                        'options' => [
                            'active' => __('departments.Active'),
                            'inactive' => __('departments.Inactive'),
                        ],
                    ],
                    [
                        'id' => 'search',
                        'name' => 'search',
                        'label' => __('departments.Search'),
                        'type' => 'text',
                        'placeholder' => __('departments.Search by code or name...'),
                    ],
                ];

                $tableColumns = [
                    [
                        'label' => __('departments.Department Code'),
                        'render' => fn($department) => '<span class="font-mono text-sm font-semibold text-blue-600 dark:text-blue-400">' . e($department->code) . '</span>',
                    ],
                    [
                        'label' => __('departments.Department Name'),
                        'render' => fn($department) => '<p class="text-sm font-semibold text-gray-900 dark:text-white">' . e($department->name) . '</p>',
                    ],
                    [
                        'label' => __('departments.Staff'),
                        'render' => fn($department) => '<span class="text-sm text-gray-700 dark:text-gray-300">' . ($department->members_count ?? 0) . '</span>',
                    ],
                ];

                $tableActions = [
                    [
                        'type' => 'link',
                        'url' => fn($department) => route('departments.show', $department),
                        'icon' => 'fas fa-eye',
                        'title' => __('departments.View'),
                    ],
                ];
                
                if (auth()->user()->can('manage departments')) {
                    $tableActions[] = [
                        'type' => 'link',
                        'url' => fn($department) => route('departments.edit', $department),
                        'icon' => 'fas fa-edit',
                        'title' => __('departments.Edit'),
                    ];
                    
                    $tableActions[] = [
                        'type' => 'delete',
                        'url' => fn($department) => route('departments.destroy', $department),
                        'icon' => 'fas fa-trash',
                        'title' => __('departments.Delete'),
                        'confirmTitle' => __('departments.Delete department'),
                        'confirmMessage' => __('departments.Are you sure you want to delete this department?'),
                        'confirmText' => __('departments.Delete'),
                        'cancelText' => __('departments.Cancel'),
                    ];
                }
            @endphp

            <x-data-table
                :title="__('departments.Departments')"
                :addButtonText="auth()->user()->can('manage departments') ? __('departments.Add Department') : null"
                addButtonAction="openCreateDepartmentModal()"
                :filters="$tableFilters"
                :columns="$tableColumns"
                :data="$departments"
                :actions="$tableActions"
                :emptyMessage="__('departments.No departments found')"
                emptyIcon="fas fa-building"
            />
        </div>
    </div>

    <!-- Create Department Modal -->
    @can('manage departments')
    <div id="createDepartmentModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 transition-opacity" onclick="closeCreateDepartmentModal()"></div>
            <div class="relative inline-block w-full max-w-lg p-6 my-8 text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-2xl">
                <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-building text-blue-600"></i>
                        {{ __('departments.Create New Department') }}
                    </h3>
                    <button type="button" onclick="closeCreateDepartmentModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form method="POST" action="{{ route('departments.store') }}" class="py-6 space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('departments.Department Code') }} <span class="text-red-500">*</span></label>
                            <input type="text" name="code" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" placeholder="{{ __('departments.e.g., LANG') }}" required>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('departments.Short code for the department') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('departments.Department Name') }} <span class="text-red-500">*</span></label>
                            <input type="text" name="name" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" placeholder="{{ __('departments.e.g., Language Teachers') }}" required>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('departments.Full name of the department') }}</p>
                        </div>
                    </div>
                    <label class="inline-flex items-center text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 dark:border-gray-700 text-blue-600 focus:ring-blue-500 dark:bg-gray-900">
                        <span class="ml-2">{{ __('departments.Active') }}</span>
                    </label>
                    
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" onclick="closeCreateDepartmentModal()" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            <i class="fas fa-times mr-2"></i>{{ __('departments.Cancel') }}
                        </button>
                        <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-lg shadow-sm transition-all">
                            <i class="fas fa-check mr-2"></i>{{ __('departments.Save Department') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    <script>
        function openCreateDepartmentModal() {
            document.getElementById('createDepartmentModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        function closeCreateDepartmentModal() {
            document.getElementById('createDepartmentModal').classList.add('hidden');
            document.body.style.overflow = '';
        }
        
        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeCreateDepartmentModal();
            }
        });
    </script>
</x-app-layout>
