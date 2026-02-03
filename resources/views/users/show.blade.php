<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('users.index') }}" class="text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('users.User Profile') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ $user->name }}
                </h2>
            </div>
        </div>
    </x-slot>

    @php
        $primaryRole = $user->roles->first()?->name;
    @endphp

    <div class="py-6 sm:py-10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-5">
                <div class="flex items-center gap-4 flex-wrap">
                    <div class="h-14 w-14 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 text-white flex items-center justify-center text-2xl font-semibold">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $user->name }}</h3>
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                {{ ucfirst($primaryRole ?? 'user') }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ $user->email }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ $user->phone }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $user->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200' }}">
                            {{ $user->is_active ? __('Active') : __('Inactive') }}
                        </span>
                        <a href="{{ route('users.edit', $user) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm bg-purple-600 text-white hover:bg-purple-700">
                            <i class="fas fa-edit mr-2"></i>{{ __('users.Edit') }}
                        </a>
                    </div>
                </div>
            </div>

            @if($primaryRole === 'student')
                @include('users.partials.student-profile')
            @elseif($primaryRole === 'teacher')
                @include('users.partials.teacher-profile')
            @elseif($primaryRole === 'staff')
                @include('users.partials.staff-profile')
            @elseif($primaryRole === 'admin')
                @include('users.partials.staff-profile', ['showAsAdmin' => true])
            @else
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-6">
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ __('users.No profile data available for this role yet.') }}</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
