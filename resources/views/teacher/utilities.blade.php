<x-app-layout>
    <div class="p-6 space-y-8 pb-20 md:pb-8">
        <!-- Back Button & Header -->
        <div class="flex items-center justify-between">
            <a href="{{ route('teacher.dashboard') }}" class="flex items-center text-gray-600 dark:text-gray-300">
                <i class="fas fa-chevron-left mr-2"></i>
                <span class="text-sm font-medium">{{ __('Back') }}</span>
            </a>
            <h1 class="text-lg font-bold text-gray-800 dark:text-white">{{ __('Utilities') }}</h1>
            <div class="w-8"></div> <!-- Spacer -->
        </div>

        <!-- Main Section (Grid) -->
        <div>
            <div class="flex items-center mb-4">
                <div class="w-1 h-6 bg-blue-500 rounded-full mr-3"></div>
                <h2 class="text-lg font-bold text-gray-800 dark:text-white">{{ __('Main') }}</h2>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <!-- Payslips -->
                <a href="{{ route('teacher.payslips') }}"
                    class="group block bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 flex flex-col items-center justify-center text-center h-32 hover:shadow-md transition-shadow">
                    <div
                        class="w-12 h-12 rounded-full border-2 border-green-500 flex items-center justify-center mb-3 group-hover:bg-green-50 dark:group-hover:bg-green-900/20 transition-colors">
                        <i class="fas fa-file-invoice-dollar text-green-600 dark:text-green-400 text-lg"></i>
                    </div>
                    <span class="text-xs font-bold text-gray-900 dark:text-white">
                        {{ __('Payslips') }}
                    </span>
                </a>

                <!-- School Info -->
                <a href="{{ route('teacher.school-info') }}"
                    class="group block bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 flex flex-col items-center justify-center text-center h-32 hover:shadow-md transition-shadow">
                    <div
                        class="w-12 h-12 rounded-full border-2 border-blue-500 flex items-center justify-center mb-3 group-hover:bg-blue-50 dark:group-hover:bg-blue-900/20 transition-colors">
                        <i class="fas fa-school text-blue-600 dark:text-blue-400 text-lg"></i>
                    </div>
                    <span class="text-xs font-bold text-gray-900 dark:text-white">
                        {{ __('School Info') }}
                    </span>
                </a>

                <!-- Rules -->
                <a href="{{ route('teacher.rules') }}"
                    class="group block bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 flex flex-col items-center justify-center text-center h-32 hover:shadow-md transition-shadow">
                    <div
                        class="w-12 h-12 rounded-full border-2 border-gray-500 flex items-center justify-center mb-3 group-hover:bg-gray-50 dark:group-hover:bg-gray-700 transition-colors">
                        <i class="fas fa-list-check text-gray-600 dark:text-gray-400 text-lg"></i>
                    </div>
                    <span class="text-xs font-bold text-gray-900 dark:text-white">
                        {{ __('Rules') }}
                    </span>
                </a>
            </div>
        </div>

        <!-- Approvals Section -->
        <div>
            <div class="flex items-center mb-4">
                <div class="w-1 h-6 bg-orange-500 rounded-full mr-3"></div>
                <h2 class="text-lg font-bold text-gray-800 dark:text-white">{{ __('Approvals') }}</h2>
            </div>

            <div class="space-y-4">
                <!-- Student Leave Requests -->
                <a href="{{ route('teacher.leave-requests.students') }}"
                    class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex items-center">
                        <div
                            class="relative w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex items-center justify-center mr-4">
                            <i class="fas fa-calendar-alt text-xl"></i>
                            <span
                                class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white border-2 border-white dark:border-gray-800">
                                6
                            </span>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-white text-sm">
                                {{ __('Student Leave Requests') }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ __('Review and approve student leave applications') }}
                            </p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-purple-400 text-sm"></i>
                </a>
            </div>
        </div>

        <!-- More Section -->
        <div>
            <div class="flex items-center mb-4">
                <div class="w-1 h-6 bg-blue-400 rounded-full mr-3"></div>
                <h2 class="text-lg font-bold text-gray-800 dark:text-white">{{ __('More') }}</h2>
            </div>

            <div class="space-y-4">
                <!-- My Leave Request -->
                <a href="{{ route('teacher.leave-requests.my') }}"
                    class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex items-center">
                        <div
                            class="w-12 h-12 rounded-xl bg-orange-100 dark:bg-orange-900/30 text-orange-600 flex items-center justify-center mr-4">
                            <i class="fas fa-book text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-white text-sm">
                                {{ __('leave.Leave Request') }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ __('Submit your leave applications') }}
                            </p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-orange-400 text-sm"></i>
                </a>

                <!-- Daily Report -->
                <a href="{{ route('teacher.daily-report') }}"
                    class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex items-center">
                        <div
                            class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 text-blue-600 flex items-center justify-center mr-4">
                            <i class="fas fa-chart-bar text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-white text-sm">{{ __('Daily Report') }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Submit daily report to admin') }}
                            </p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-blue-400 text-sm"></i>
                </a>

                <!-- My Attendance -->
                <a href="{{ route('teacher.my-attendance') }}"
                    class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex items-center">
                        <div
                            class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/30 text-purple-600 flex items-center justify-center mr-4">
                            <i class="fas fa-id-card text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-white text-sm">{{ __('My Attendance') }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ __('View your daily attendance history') }}
                            </p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-purple-400 text-sm"></i>
                </a>

                <!-- Free Period Activities -->
                <a href="{{ route('teacher.activities') }}"
                    class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex items-center">
                        <div
                            class="w-12 h-12 rounded-xl bg-teal-100 dark:bg-teal-900/30 text-teal-600 flex items-center justify-center mr-4">
                            <i class="fas fa-clock text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-white text-sm">
                                {{ __('Free Period Activities') }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ __('View your recorded free period activities') }}
                            </p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-teal-400 text-sm"></i>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>