<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-lg">
                <i class="fas fa-user"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('profiles.Account') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('profiles.Profile') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10 overflow-x-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Profile Information Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex items-center gap-2 p-4 border-b border-gray-200 dark:border-gray-700">
                    <i class="fas fa-user-lock text-amber-500"></i>
                    <h4 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('profiles.Profile details are managed by administrators') }}</h4>
                </div>
                <div class="p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ __('profiles.Contact your school administrator to update name, email, phone, NRC, or role assignments.') }}</p>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr>
                                    <th class="w-32 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('profiles.Name') }}</th>
                                    <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $user->name }}</td>
                                </tr>
                                <tr>
                                    <th class="w-32 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('profiles.Email') }}</th>
                                    <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $user->email }}</td>
                                </tr>
                                @if($user->phone)
                                <tr>
                                    <th class="w-32 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('profiles.Phone') }}</th>
                                    <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $user->phone }}</td>
                                </tr>
                                @endif
                                @if($user->nrc)
                                <tr>
                                    <th class="w-32 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('profiles.NRC') }}</th>
                                    <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $user->nrc }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th class="w-32 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">{{ __('profiles.Roles') }}</th>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-2">
                                            @forelse($user->roles as $role)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300">
                                                    {{ ucfirst($role->name) }}
                                                </span>
                                            @empty
                                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('profiles.No role assigned') }}</span>
                                            @endforelse
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Update Password Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex items-center gap-2 p-4 border-b border-gray-200 dark:border-gray-700">
                    <i class="fas fa-lock text-emerald-500"></i>
                    <h4 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('profiles.Update Password') }}</h4>
                </div>
                <div class="p-4">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
