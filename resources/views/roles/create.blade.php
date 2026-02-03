<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('roles.index') }}" class="mr-4 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('roles.Create New Role') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <form method="POST" action="{{ route('roles.store') }}" class="p-6 sm:p-8">
                    @csrf

                    <!-- Role Name -->
                    <div class="mb-6">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('roles.Role Name') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                               class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600"
                               placeholder="{{ __('roles.Enter role name (e.g., editor, moderator)') }}">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Permissions -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">
                            {{ __('roles.Permissions') }} <span class="text-red-500">*</span>
                        </label>

                        @foreach($permissions as $group => $groupPermissions)
                            <div class="mb-6 last:mb-0">
                                <h3 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ $group }}</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach($groupPermissions as $permission)
                                        <label class="flex items-center p-3 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors">
                                            <input type="checkbox" name="permissions[]" value="{{ $permission->value }}"
                                                   {{ in_array($permission->value, old('permissions', [])) ? 'checked' : '' }}
                                                   class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600">
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
                            {{ __('roles.Create Role') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
