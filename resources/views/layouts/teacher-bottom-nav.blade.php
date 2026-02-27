<div
    class="fixed bottom-0 left-0 z-50 w-full h-16 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 md:hidden">
    <div class="flex h-full max-w-lg mx-auto font-medium justify-between">
        <a href="{{ route('teacher.academic') }}"
            class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 dark:hover:bg-gray-700 group {{ request()->routeIs('teacher.academic') ? 'text-blue-600 dark:text-blue-500' : 'text-gray-500 dark:text-gray-400' }}">
            <i class="fas fa-graduation-cap w-5 h-5 mb-1 group-hover:text-blue-600 dark:group-hover:text-blue-500"></i>
            <span class="text-xs group-hover:text-blue-600 dark:group-hover:text-blue-500">{{ __('Academic') }}</span>
        </a>

        <a href="{{ route('announcements.index') }}"
            class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 dark:hover:bg-gray-700 group {{ request()->routeIs('announcements.index') ? 'text-blue-600 dark:text-blue-500' : 'text-gray-500 dark:text-gray-400' }}">
            <i class="fas fa-bullhorn w-5 h-5 mb-1 group-hover:text-blue-600 dark:group-hover:text-blue-500"></i>
            <span class="text-xs group-hover:text-blue-600 dark:group-hover:text-blue-500">{{ __('Announce') }}</span>
        </a>

        <a href="{{ route('teacher.dashboard') }}"
            class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 dark:hover:bg-gray-700 group {{ request()->routeIs('teacher.dashboard') ? 'text-blue-600 dark:text-blue-500' : 'text-gray-500 dark:text-gray-400' }}">
            <div
                class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center shadow-lg -mt-6 border-4 border-white dark:border-gray-800">
                <i class="fas fa-home w-5 h-5 text-white"></i>
            </div>
            <span class="text-xs mt-1 group-hover:text-blue-600 dark:group-hover:text-blue-500">{{ __('Home') }}</span>
        </a>

        <a href="{{ route('teacher.utilities') }}"
            class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 dark:hover:bg-gray-700 group {{ request()->routeIs('teacher.utilities') ? 'text-blue-600 dark:text-blue-500' : 'text-gray-500 dark:text-gray-400' }}">
            <i class="fas fa-th-large w-5 h-5 mb-1 group-hover:text-blue-600 dark:group-hover:text-blue-500"></i>
            <span class="text-xs group-hover:text-blue-600 dark:group-hover:text-blue-500">{{ __('Utilities') }}</span>
        </a>

        <a href="{{ route('profile.edit') }}"
            class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 dark:hover:bg-gray-700 group {{ request()->routeIs('profile.edit') ? 'text-blue-600 dark:text-blue-500' : 'text-gray-500 dark:text-gray-400' }}">
            <i class="fas fa-user w-5 h-5 mb-1 group-hover:text-blue-600 dark:group-hover:text-blue-500"></i>
            <span
                class="text-xs group-hover:text-blue-600 dark:group-hover:text-blue-500">{{ __('layouts.Profile') }}</span>
        </a>
    </div>
</div>