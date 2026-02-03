<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-lg">
                <i class="fas fa-key"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('permissions.Access Control') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('permissions.Permissions') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10 overflow-x-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Info Banner -->
            <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 px-4 py-3 rounded-xl mb-6">
                <div class="flex items-start">
                    <i class="fas fa-info-circle mt-0.5 mr-3 flex-shrink-0"></i>
                    <span class="text-sm">{{ __('permissions.Permissions are defined in the system and assigned to roles. To modify permissions, edit the PermissionEnum class.') }}</span>
                </div>
            </div>

            <!-- Permissions Display -->
            <div class="space-y-6">
                @foreach($permissions as $group => $groupPermissions)
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="flex items-center gap-2 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <i class="fas fa-folder text-emerald-500"></i>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $group }}</h3>
                            <span class="ml-auto inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300">
                                {{ count($groupPermissions) }}
                            </span>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($groupPermissions as $permission)
                                    <div class="flex items-center p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-700">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-green-100 dark:bg-green-900/40 text-green-600 dark:text-green-400 mr-3 flex-shrink-0">
                                            <i class="fas fa-check text-sm"></i>
                                        </span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                                {{ __(ucfirst(str_replace('-', ' ', $permission->value))) }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                {{ $permission->value }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
