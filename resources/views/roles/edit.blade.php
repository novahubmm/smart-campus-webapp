<x-app-layout>
    <x-slot name="header">
        <x-page-header
            icon="fas fa-user-shield"
            iconBg="bg-gradient-to-br from-purple-500 to-indigo-600"
            iconColor="text-white"
            :subtitle="__('roles.Access Control')"
            :title="__('roles.Edit Role') . ': ' . ucfirst($role->name)"
        />
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-back-link 
                :href="route('roles.index')"
                :text="__('roles.Back to Roles')"
            />

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <form method="POST" action="{{ route('roles.update', $role) }}" class="p-6 sm:p-8">
                    @csrf
                    @method('PUT')

                    <!-- Role Info -->
                    <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <div class="flex items-center gap-2 text-blue-800 dark:text-blue-300">
                            <i class="fas fa-info-circle"></i>
                            <span class="text-sm font-medium">
                                {{ __('roles.This role is assigned to') }} <strong>{{ $role->users()->count() }}</strong> {{ __('roles.user(s)') }}
                            </span>
                        </div>
                    </div>

                    <!-- Role Name -->
                    <div class="mb-6">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('roles.Role Name') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name', $role->name) }}" required
                               class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600"
                               placeholder="{{ __('roles.Enter role name (e.g., editor, moderator)') }}">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Permissions -->
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('roles.Permissions') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="flex gap-2">
                                <button type="button" onclick="selectAllPermissions()" class="text-xs px-3 py-1 bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900/60">
                                    {{ __('roles.Select All') }}
                                </button>
                                <button type="button" onclick="deselectAllPermissions()" class="text-xs px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">
                                    {{ __('roles.Deselect All') }}
                                </button>
                            </div>
                        </div>

                        @php
                            $rolePermissions = $role->permissions->pluck('name')->toArray();
                        @endphp

                        @foreach($permissions as $group => $groupPermissions)
                            <div class="mb-6 last:mb-0">
                                <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ $group }}</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach($groupPermissions as $permission)
                                        <label class="flex items-center p-3 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors">
                                            <input type="checkbox" name="permissions[]" value="{{ $permission->value }}"
                                                   {{ in_array($permission->value, old('permissions', $rolePermissions)) ? 'checked' : '' }}
                                                   class="permission-checkbox rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600">
                                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __(ucfirst(str_replace('-', ' ', $permission->value))) }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        @error('permissions')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3 sm:justify-end mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('roles.index') }}"
                           class="w-full sm:w-auto px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md transition-colors text-center">
                            {{ __('roles.Cancel') }}
                        </a>
                        <button type="submit"
                                class="w-full sm:w-auto px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition-colors">
                            {{ __('roles.Update Role') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function selectAllPermissions() {
            document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                checkbox.checked = true;
            });
        }

        function deselectAllPermissions() {
            document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
        }
    </script>
</x-app-layout>
