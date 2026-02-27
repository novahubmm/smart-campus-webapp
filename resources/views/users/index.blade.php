<x-app-layout>
    <x-slot name="header">
        <x-page-header 
            icon="fas fa-users-cog"
            iconBg="bg-gradient-to-br from-blue-500 to-indigo-600"
            iconColor="text-white shadow-lg"
            subtitle="{{ __('users.Access Control') }}"
            title="{{ __('users.User Management') }}"
        />
    </x-slot>

    <div class="py-6 sm:py-10 overflow-x-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Quick Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-stat-card 
                    icon="fas fa-users"
                    :title="__('Total Users')"
                    :number="number_format($totals['all'])"
                    :subtitle="__('Portal accounts')"
                />
                <x-stat-card 
                    icon="fas fa-user-check"
                    :title="__('Active Users')"
                    :number="number_format($totals['active'])"
                    :subtitle="__('Online access')"
                />
            </div>

            @php
                $tableFilters = [
                    [
                        'id' => 'role',
                        'name' => 'role',
                        'label' => __('Role'),
                        'type' => 'select',
                        'placeholder' => __('All Roles'),
                        'options' => $roles->pluck('name', 'name')->map(fn($name) => ucfirst($name))->toArray(),
                    ],
                    [
                        'id' => 'status',
                        'name' => 'status',
                        'label' => __('Status'),
                        'type' => 'select',
                        'placeholder' => __('All Status'),
                        'options' => [
                            'active' => __('Active'),
                            'inactive' => __('Inactive'),
                        ],
                    ],
                    [
                        'id' => 'search',
                        'name' => 'search',
                        'label' => __('Search'),
                        'type' => 'text',
                        'placeholder' => __('Search by name, ID or email...'),
                    ],
                ];

                $tableColumns = [
                    [
                        'label' => __('User ID'),
                        'render' => function($user) {
                            $roles = $user->roles->pluck('name');
                            $displayId = $user->id;

                            if ($roles->contains('staff') && $user->staffProfile?->employee_id) {
                                $displayId = $user->staffProfile->employee_id;
                            } elseif ($roles->contains('teacher') && $user->teacherProfile?->employee_id) {
                                $displayId = $user->teacherProfile->employee_id;
                            } elseif ($roles->contains('student')) {
                                $displayId = $user->studentProfile?->student_id
                                    ?? $user->studentProfile?->student_identifier
                                    ?? $displayId;
                            } elseif ($roles->contains('guardian') && $user->guardianProfile) {
                                $guardianKey = $user->guardianProfile->id ?? $displayId;
                                $displayId = 'GUA-' . strtoupper(substr((string) $guardianKey, 0, 6));
                            }

                            return '<p class="text-sm font-mono font-semibold text-gray-900 dark:text-white">' . e($displayId) . '</p>';
                        },
                    ],
                    [
                        'label' => __('Full Name'),
                        'render' => function($user) {
                            return '<p class="text-sm font-semibold text-gray-900 dark:text-white">' . e($user->name) . '</p>' .
                                   '<p class="text-xs text-gray-500 dark:text-gray-400">' . e($user->email ?? '—') . '</p>';
                        },
                    ],
                    [
                        'label' => __('Role'),
                        'render' => function($user) {
                            return $user->roles->map(function($role) {
                                return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-100">' . ucfirst($role->name) . '</span>';
                            })->join(' ');
                        },
                    ],
                    [
                        'label' => __('Status'),
                        'render' => function($user) {
                            $statusClass = $user->is_active 
                                ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-100'
                                : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-100';
                            $statusLabel = $user->is_active ? __('Active') : __('Inactive');
                            return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold ' . $statusClass . '">' . $statusLabel . '</span>';
                        },
                    ],
                    [
                        'label' => __('Last Updated'),
                        'render' => fn($user) => '<span class="text-sm text-gray-700 dark:text-gray-300">' . ($user->updated_at?->format('M d, Y H:i') ?? '—') . '</span>',
                    ],
                ];

                $tableActions = [];
                
                if (auth()->user()->can('update users')) {
                    $tableActions[] = [
                        'type' => 'link',
                        'url' => fn($user) => route('users.edit', $user),
                        'icon' => 'fas fa-edit',
                        'title' => __('Edit'),
                    ];
                    
                    $tableActions[] = [
                        'type' => 'button',
                        'icon' => 'fas fa-key',
                        'color' => 'text-purple-500',
                        'title' => __('Reset Password'),
                        'onclick' => function($user) {
                            $route = route('users.reset-password', $user);
                            return "openResetPasswordModal('{$route}', '" . e($user->name) . "')";
                        },
                    ];
                    
                    $tableActions[] = [
                        'type' => 'button',
                        'onclick' => fn($user) => "toggleUserStatus('{$user->id}', '" . ($user->is_active ? 'inactive' : 'active') . "')",
                        'icon' => fn($user) => $user->is_active ? 'fas fa-toggle-on' : 'fas fa-toggle-off',
                        'title' => fn($user) => $user->is_active ? __('Deactivate') : __('Activate'),
                        'color' => fn($user) => $user->is_active ? 'text-green-600 hover:text-green-700' : 'text-gray-400 hover:text-gray-500',
                    ];
                }
            @endphp

            <x-data-table
                :title="__('Portal User Accounts')"
                :addButtonText="auth()->user()->can('create users') ? __('Add New User') : null"
                addButtonAction="openAddUserModal()"
                :filters="$tableFilters"
                :columns="$tableColumns"
                :data="$users"
                :actions="$tableActions"
                :emptyMessage="__('No user accounts found')"
                emptyIcon="fas fa-users"
            />
        </div>
    </div>

    <!-- Add New User Modal - Role Selection -->
    <div id="addUserModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 transition-opacity" onclick="closeAddUserModal()"></div>
            <div class="relative inline-block w-full max-w-3xl p-6 my-8 text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-2xl">
                <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-user-plus text-blue-600"></i>
                        {{ __('users.Add New User - Select Role') }}
                    </h3>
                    <button type="button" onclick="closeAddUserModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="py-6">
                    <p class="text-center text-gray-600 dark:text-gray-400 mb-8">{{ __('users.Choose the type of account you want to create') }}</p>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <!-- Student Role -->
                        <a href="{{ route('student-profiles.create') }}" class="role-card group block border-2 border-gray-200 dark:border-gray-700 rounded-xl p-6 text-center transition-all hover:border-blue-500 hover:shadow-lg hover:-translate-y-1 bg-white dark:bg-gray-800">
                            <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                                <i class="fas fa-user-graduate text-3xl text-white"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ __('users.Student') }}</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('users.Create student profile with full registration') }}</p>
                        </a>
                        
                        <!-- Teacher Role -->
                        <a href="{{ route('teacher-profiles.create') }}" class="role-card group block border-2 border-gray-200 dark:border-gray-700 rounded-xl p-6 text-center transition-all hover:border-emerald-500 hover:shadow-lg hover:-translate-y-1 bg-white dark:bg-gray-800">
                            <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-emerald-500 to-green-600 rounded-full flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                                <i class="fas fa-chalkboard-teacher text-3xl text-white"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ __('users.Teacher') }}</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('users.Create teacher profile with full registration') }}</p>
                        </a>
                        
                        <!-- Staff Role -->
                        <a href="{{ route('staff-profiles.create') }}" class="role-card group block border-2 border-gray-200 dark:border-gray-700 rounded-xl p-6 text-center transition-all hover:border-amber-500 hover:shadow-lg hover:-translate-y-1 bg-white dark:bg-gray-800">
                            <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-amber-500 to-orange-600 rounded-full flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                                <i class="fas fa-user-tie text-3xl text-white"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ __('users.Staff') }}</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('users.Create staff profile with full registration') }}</p>
                        </a>
                        
                        <!-- Admin Role -->
                        <a href="{{ route('users.create') }}" class="role-card group block border-2 border-gray-200 dark:border-gray-700 rounded-xl p-6 text-center transition-all hover:border-purple-500 hover:shadow-lg hover:-translate-y-1 bg-white dark:bg-gray-800">
                            <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-purple-500 to-violet-600 rounded-full flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                                <i class="fas fa-user-shield text-3xl text-white"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ __('users.Admin') }}</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('users.Create admin account (no registration required)') }}</p>
                        </a>
                    </div>
                </div>
                <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" onclick="closeAddUserModal()" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        <i class="fas fa-times mr-2"></i>{{ __('users.Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div id="resetPasswordModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 transition-opacity" onclick="closeResetPasswordModal()"></div>
            <div class="relative inline-block w-full max-w-md p-6 my-8 text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-2xl">
                <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-key text-purple-600"></i>
                        {{ __('users.Reset Password') }}
                    </h3>
                    <button type="button" onclick="closeResetPasswordModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <form id="resetPasswordForm" method="POST" class="py-4 space-y-4">
                    @csrf
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('users.Reset password for') }}: <strong id="resetPasswordUserName" class="text-gray-900 dark:text-white"></strong>
                    </p>
                    <div>
                        <label for="new_password" class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('users.New Password') }}</label>
                        <input type="password" name="password" id="new_password" required minlength="8" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-purple-500 focus:ring-purple-500" placeholder="{{ __('users.Enter new password') }}">
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('users.Confirm Password') }}</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-purple-500 focus:ring-purple-500" placeholder="{{ __('users.Confirm new password') }}">
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" onclick="closeResetPasswordModal()" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            {{ __('users.Cancel') }}
                        </button>
                        <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700 transition-colors">
                            <i class="fas fa-key mr-2"></i>{{ __('users.Reset Password') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openAddUserModal() {
            document.getElementById('addUserModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        function closeAddUserModal() {
            document.getElementById('addUserModal').classList.add('hidden');
            document.body.style.overflow = '';
        }
        
        function openResetPasswordModal(url, userName) {
            document.getElementById('resetPasswordForm').action = url;
            document.getElementById('resetPasswordUserName').textContent = userName;
            document.getElementById('new_password').value = '';
            document.getElementById('password_confirmation').value = '';
            document.getElementById('resetPasswordModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        function closeResetPasswordModal() {
            document.getElementById('resetPasswordModal').classList.add('hidden');
            document.body.style.overflow = '';
        }
        
        function toggleUserStatus(userId, newStatus) {
            const action = newStatus === 'active' ? 'activate' : 'deactivate';
            const title = newStatus === 'active' ? '{{ __('Activate user') }}' : '{{ __('Deactivate user') }}';
            const message = newStatus === 'active' 
                ? '{{ __('Re-enable login for this user.') }}'
                : '{{ __('This will block login for this user.') }}';
            const confirmText = newStatus === 'active' ? '{{ __('Activate') }}' : '{{ __('Deactivate') }}';
            
            confirmAction(
                `/users/${userId}/${action}`,
                title,
                message,
                confirmText
            );
        }
        
        function confirmAction(url, title, message, confirmText) {
            if (typeof Alpine !== 'undefined') {
                window.dispatchEvent(new CustomEvent('confirm-show', {
                    detail: {
                        title: title,
                        message: message,
                        confirmText: confirmText,
                        cancelText: '{{ __('users.Cancel') }}',
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
        
        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAddUserModal();
                closeResetPasswordModal();
            }
        });
    </script>
</x-app-layout>
