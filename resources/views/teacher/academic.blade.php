<x-app-layout>
    <!-- Main Content Container -->
    <div class="p-6 space-y-8 pb-20 md:pb-8">

        <!-- Main Section -->
        <div>
            <div class="flex items-center mb-4">
                <div class="w-1 h-6 bg-blue-500 rounded-full mr-3"></div>
                <h2 class="text-lg font-bold text-gray-800 dark:text-white">{{ __('Main') }}</h2>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <!-- My Classes -->
                <a href="{{ route('teacher.my-classes') }}"
                    class="bg-white dark:bg-gray-800 p-4 rounded-3xl shadow-sm flex flex-col items-center justify-center text-center aspect-square hover:shadow-md transition-shadow">
                    <div
                        class="w-12 h-12 rounded-full border-2 border-teal-500 flex items-center justify-center mb-3 text-teal-500">
                        <i class="fas fa-book-open text-xl"></i>
                    </div>
                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ __('My Classes') }}</span>
                </a>

                <!-- Exam -->
                <a href="{{ route('teacher.exams') }}"
                    class="bg-white dark:bg-gray-800 p-4 rounded-3xl shadow-sm flex flex-col items-center justify-center text-center aspect-square hover:shadow-md transition-shadow">
                    <div
                        class="w-12 h-12 rounded-full border-2 border-orange-500 flex items-center justify-center mb-3 text-orange-500">
                        <i class="fas fa-clipboard-question text-xl"></i>
                    </div>
                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ __('exam.Exam') }}</span>
                </a>

                <!-- Subjects -->
                <a href="{{ route('teacher.subjects') }}"
                    class="bg-white dark:bg-gray-800 p-4 rounded-3xl shadow-sm flex flex-col items-center justify-center text-center aspect-square hover:shadow-md transition-shadow">
                    <div
                        class="w-12 h-12 rounded-full border-2 border-blue-500 flex items-center justify-center mb-3 text-blue-500">
                        <i class="fas fa-book text-xl"></i>
                    </div>
                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ __('Subjects') }}</span>
                </a>
            </div>
        </div>

        <!-- More Section -->
        <div>
            <div class="flex items-center mb-4">
                <div class="w-1 h-6 bg-blue-500 rounded-full mr-3"></div>
                <h2 class="text-lg font-bold text-gray-800 dark:text-white">{{ __('More') }}</h2>
            </div>

            <div class="space-y-4">
                <!-- View Homework -->
                <a href="{{ route('teacher.homework') }}"
                    class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex items-center">
                        <div
                            class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 text-blue-500 flex items-center justify-center mr-4">
                            <i class="fas fa-book text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-white text-sm">{{ __('View Homework') }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Check assigned homework') }}</p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 text-sm"></i>
                </a>

                <!-- Collect Attendance -->
                <a href="{{ route('teacher.attendance') }}"
                    class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex items-center">
                        <div
                            class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/30 text-green-500 flex items-center justify-center mr-4">
                            <i class="fas fa-calendar-check text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-white text-sm">{{ __('Collect Attendance') }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Mark student attendance') }}</p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 text-sm"></i>
                </a>

                <!-- View Attendance History -->
                <a href="{{ route('teacher.my-attendance') }}"
                    class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex items-center">
                        <div
                            class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 text-blue-500 flex items-center justify-center mr-4">
                            <i class="fas fa-history text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-white text-sm">
                                {{ __('View Attendance History') }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ __('Check past attendance records') }}
                            </p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 text-sm"></i>
                </a>

                <!-- View Class Records -->
                <a href="{{ route('teacher.resources') }}"
                    class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex items-center">
                        <div
                            class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/30 text-purple-500 flex items-center justify-center mr-4">
                            <i class="fas fa-folder-open text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-white text-sm">{{ __('View Class Records') }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ __('Access class logs and history') }}
                            </p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 text-sm"></i>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>