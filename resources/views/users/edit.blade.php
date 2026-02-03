<x-app-layout>
    <x-slot name="header">
        <x-page-header
            icon="fas fa-edit"
            iconBg="bg-gradient-to-br from-blue-500 to-indigo-600"
            iconColor="text-white"
            :subtitle="__('users.Access Control')"
            :title="__('users.Edit User Account')"
        />
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-back-link 
                :href="route('users.index')"
                :text="__('users.Back to Users')"
            />

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <form method="POST" action="{{ route('users.update', $user) }}" class="p-6 sm:p-8 space-y-6">
                    @csrf
                    @method('PUT')

                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('users.Edit User Details') }}</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('users.Name') }}</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500">
                            @error('name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('users.Email') }} <span class="text-xs text-gray-500">{{ __('users.(optional)') }}</span></label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500">
                            @error('email')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('users.Phone') }} <span class="text-xs text-gray-500">{{ __('users.(optional)') }}</span></label>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500">
                            @error('phone')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('users.NRC') }} <span class="text-xs text-gray-500">{{ __('users.(optional)') }}</span></label>
                            <input type="text" name="nrc" value="{{ old('nrc', $user->nrc) }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500">
                            @error('nrc')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <label class="inline-flex items-center text-sm font-semibold text-gray-700 dark:text-gray-300">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-700 text-blue-600 shadow-sm focus:ring-blue-500 dark:bg-gray-900 dark:text-gray-300">
                            <span class="ml-2">{{ __('users.Active (can login)') }}</span>
                        </label>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('users.Deactivate to block login immediately.') }}</p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 sm:justify-end pt-4">
                        <a href="{{ route('users.index') }}" class="w-full sm:w-auto px-6 py-2 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-lg font-semibold text-center hover:bg-gray-200 dark:hover:bg-gray-600">{{ __('users.Cancel') }}</a>
                        <button type="submit" class="w-full sm:w-auto px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm">{{ __('users.Save Changes') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
