<x-app-layout>
    <div class="p-6 space-y-6">
        <!-- Back Button & Header -->
        <div class="flex items-center justify-between">
            <a href="{{ route('guardian.dashboard') }}" class="flex items-center text-gray-600 dark:text-gray-300">
                <i class="fas fa-chevron-left mr-2"></i>
                <span class="text-sm font-medium">{{ __('Back') }}</span>
            </a>
            <h1 class="text-lg font-bold text-gray-800 dark:text-white">{{ __('Utilities') }}</h1>
            <div class="w-8"></div> <!-- Spacer -->
        </div>

        <div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white border-l-4 border-blue-500 pl-3 mb-4">
                {{ __('Main') }}
            </h3>

            <div class="grid grid-cols-3 gap-4">
                <!-- School Info -->
                <a href="{{ route('guardian.school-info') }}"
                    class="group block bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 flex flex-col items-center justify-center text-center h-32 hover:shadow-md transition-shadow">
                    <div
                        class="w-12 h-12 rounded-full border-2 border-purple-500 flex items-center justify-center mb-3 group-hover:bg-purple-50 dark:group-hover:bg-purple-900/20 transition-colors">
                        <i class="fas fa-graduation-cap text-purple-600 dark:text-purple-400 text-lg"></i>
                    </div>
                    <span class="text-xs font-bold text-gray-900 dark:text-white leading-tight">
                        {{ __('School Info') }}
                    </span>
                </a>

                <!-- Rules & Regulations -->
                <a href="{{ route('guardian.rules') }}"
                    class="group block bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 flex flex-col items-center justify-center text-center h-32 hover:shadow-md transition-shadow">
                    <div
                        class="w-12 h-12 rounded-full border-2 border-gray-500 flex items-center justify-center mb-3 group-hover:bg-gray-50 dark:group-hover:bg-gray-700 transition-colors">
                        <i class="fas fa-tasks text-gray-600 dark:text-gray-400 text-lg"></i>
                    </div>
                    <span class="text-xs font-bold text-gray-900 dark:text-white leading-tight">
                        {{ __('Rules & Regulations') }}
                    </span>
                </a>

                <!-- School Fees -->
                <a href="{{ route('guardian.fees') }}"
                    class="group block bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 flex flex-col items-center justify-center text-center h-32 hover:shadow-md transition-shadow">
                    <div
                        class="w-12 h-12 rounded-full border-2 border-orange-500 flex items-center justify-center mb-3 group-hover:bg-orange-50 dark:group-hover:bg-orange-900/20 transition-colors">
                        <i class="fas fa-credit-card text-orange-600 dark:text-orange-400 text-lg"></i>
                    </div>
                    <span class="text-xs font-bold text-gray-900 dark:text-white leading-tight">
                        {{ __('School Fees') }}
                    </span>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>