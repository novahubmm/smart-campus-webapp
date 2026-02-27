<x-app-layout>
    <!-- Main Content Container -->
    <div class="p-6 space-y-8">

        <!-- Main Section -->
        <div>
            <div class="flex items-center mb-4">
                <div class="w-1 h-6 bg-blue-500 rounded-full mr-3"></div>
                <h2 class="text-lg font-bold text-gray-800 dark:text-white">{{ __('Main') }}</h2>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <!-- My Classes -->
                <a href="{{ route('guardian.classes') }}"
                    class="bg-white dark:bg-gray-800 p-4 rounded-3xl shadow-sm flex flex-col items-center justify-center text-center aspect-square hover:shadow-md transition-shadow">
                    <div
                        class="w-12 h-12 rounded-full border-2 border-blue-500 flex items-center justify-center mb-3 text-blue-500">
                        <i class="fas fa-book-open text-xl"></i>
                    </div>
                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ __('My Classes') }}</span>
                </a>

                <!-- Exam -->
                <a href="{{ route('guardian.exams') }}"
                    class="bg-white dark:bg-gray-800 p-4 rounded-3xl shadow-sm flex flex-col items-center justify-center text-center aspect-square hover:shadow-md transition-shadow">
                    <div
                        class="w-12 h-12 rounded-full border-2 border-yellow-500 flex items-center justify-center mb-3 text-yellow-500">
                        <i class="fas fa-clipboard-question text-xl"></i>
                    </div>
                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ __('exam.Exam') }}</span>
                </a>

                <!-- Subjects -->
                <a href="{{ route('guardian.subjects') }}"
                    class="bg-white dark:bg-gray-800 p-4 rounded-3xl shadow-sm flex flex-col items-center justify-center text-center aspect-square hover:shadow-md transition-shadow">
                    <div
                        class="w-12 h-12 rounded-full border-2 border-blue-400 flex items-center justify-center mb-3 text-blue-400">
                        <i class="fas fa-shapes text-xl"></i>
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
                <a href="{{ route('guardian.homework') }}"
                    class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex items-center">
                        <div
                            class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 text-blue-500 flex items-center justify-center mr-4">
                            <i class="fas fa-book text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-white text-sm">{{ __('View Homework') }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Check homework assignments') }}
                            </p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 text-sm"></i>
                </a>

                <!-- Timetable -->
                <a href="{{ route('guardian.timetable') }}"
                    class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex items-center">
                        <div
                            class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/30 text-green-500 flex items-center justify-center mr-4">
                            <i class="fas fa-calendar-alt text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-white text-sm">{{ __('Timetable') }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('View class schedule') }}</p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 text-sm"></i>
                </a>

                <!-- Attendance -->
                <a href="{{ route('guardian.attendance') }}"
                    class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex items-center">
                        <div
                            class="w-12 h-12 rounded-xl bg-teal-100 dark:bg-teal-900/30 text-teal-500 flex items-center justify-center mr-4">
                            <i class="fas fa-calendar-check text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-white text-sm">
                                {{ __('attendance.Attendance') }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('View attendance records') }}</p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 text-sm"></i>
                </a>

                <!-- Leave Request -->
                <a href="{{ route('guardian.leave-requests') }}"
                    class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex items-center">
                        <div
                            class="w-12 h-12 rounded-xl bg-orange-100 dark:bg-orange-900/30 text-orange-500 flex items-center justify-center mr-4">
                            <i class="fas fa-door-open text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-white text-sm">{{ __('Leave Request') }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ __('Submit absence or leave applications') }}
                            </p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 text-sm"></i>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>