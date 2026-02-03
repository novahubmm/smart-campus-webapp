<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-lg">
                <i class="fas fa-book-open"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('user_manual.Help & Documentation') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('user_manual.User Manual') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10 overflow-x-hidden">
        <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Quick Start Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex items-center gap-2 p-4 border-b border-gray-200 dark:border-gray-700">
                    <i class="fas fa-rocket text-emerald-500"></i>
                    <h4 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('user_manual.Quick Start') }}</h4>
                </div>
                <div class="p-4">
                    <ol class="list-decimal list-inside text-sm text-gray-700 dark:text-gray-300 space-y-2">
                        <li>{{ __('user_manual.Sign in with your school-issued account') }}</li>
                        <li>{{ __('user_manual.Open the dashboard to review role-based access') }}</li>
                        <li>{{ __('user_manual.Complete the Setup Wizard if you are an administrator') }}</li>
                        <li>{{ __('user_manual.Update your password under Profile') }}</li>
                    </ol>
                </div>
            </div>

            <!-- System Modules Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex items-center gap-2 p-4 border-b border-gray-200 dark:border-gray-700">
                    <i class="fas fa-cubes text-emerald-500"></i>
                    <h4 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('user_manual.System Modules') }}</h4>
                </div>
                <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Academic Management -->
                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fas fa-graduation-cap text-blue-500"></i>
                            <h5 class="font-semibold text-gray-900 dark:text-white text-sm">{{ __('user_manual.Academic Management') }}</h5>
                        </div>
                        <ul class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
                            <li>• {{ __('user_manual.Manage batches, grades, and classes') }}</li>
                            <li>• {{ __('user_manual.Configure subjects and rooms') }}</li>
                            <li>• {{ __('user_manual.Exam database and mark entry') }}</li>
                        </ul>
                    </div>

                    <!-- Events & Announcements -->
                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fas fa-calendar text-purple-500"></i>
                            <h5 class="font-semibold text-gray-900 dark:text-white text-sm">{{ __('user_manual.Events & Announcements') }}</h5>
                        </div>
                        <ul class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
                            <li>• {{ __('user_manual.Create and manage school events') }}</li>
                            <li>• {{ __('user_manual.Publish announcements to users') }}</li>
                            <li>• {{ __('user_manual.Event categories management') }}</li>
                        </ul>
                    </div>

                    <!-- Timetable & Attendance -->
                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fas fa-clock text-amber-500"></i>
                            <h5 class="font-semibold text-gray-900 dark:text-white text-sm">{{ __('user_manual.Timetable & Attendance') }}</h5>
                        </div>
                        <ul class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
                            <li>• {{ __('user_manual.Create and publish timetables') }}</li>
                            <li>• {{ __('user_manual.Student, teacher, and staff attendance') }}</li>
                            <li>• {{ __('user_manual.Leave request management') }}</li>
                        </ul>
                    </div>

                    <!-- Departments & Profiles -->
                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fas fa-users text-cyan-500"></i>
                            <h5 class="font-semibold text-gray-900 dark:text-white text-sm">{{ __('user_manual.Departments & Profiles') }}</h5>
                        </div>
                        <ul class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
                            <li>• {{ __('user_manual.Manage departments') }}</li>
                            <li>• {{ __('user_manual.Teacher, student, and staff profiles') }}</li>
                            <li>• {{ __('user_manual.Photo uploads and detailed information') }}</li>
                        </ul>
                    </div>

                    <!-- Finance -->
                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fas fa-dollar-sign text-green-500"></i>
                            <h5 class="font-semibold text-gray-900 dark:text-white text-sm">{{ __('user_manual.Finance Management') }}</h5>
                        </div>
                        <ul class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
                            <li>• {{ __('user_manual.Student fee management and payments') }}</li>
                            <li>• {{ __('user_manual.Salary and payroll processing') }}</li>
                            <li>• {{ __('user_manual.Income, expenses, and profit/loss tracking') }}</li>
                        </ul>
                    </div>

                    <!-- Settings & System -->
                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fas fa-cog text-gray-500"></i>
                            <h5 class="font-semibold text-gray-900 dark:text-white text-sm">{{ __('user_manual.Settings & System') }}</h5>
                        </div>
                        <ul class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
                            <li>• {{ __('user_manual.School information and key contacts') }}</li>
                            <li>• {{ __('user_manual.User management and activity logs') }}</li>
                            <li>• {{ __('user_manual.Roles and permissions') }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Features Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex items-center gap-2 p-4 border-b border-gray-200 dark:border-gray-700">
                    <i class="fas fa-star text-emerald-500"></i>
                    <h4 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('user_manual.Key Features') }}</h4>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <i class="fas fa-check-circle text-green-500"></i>
                            <span>{{ __('user_manual.Dark Mode') }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <i class="fas fa-check-circle text-green-500"></i>
                            <span>{{ __('user_manual.Multi-language') }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <i class="fas fa-check-circle text-green-500"></i>
                            <span>{{ __('user_manual.Role-based Access') }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <i class="fas fa-check-circle text-green-500"></i>
                            <span>{{ __('user_manual.Responsive Design') }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <i class="fas fa-check-circle text-green-500"></i>
                            <span>{{ __('user_manual.Activity Logging') }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <i class="fas fa-check-circle text-green-500"></i>
                            <span>{{ __('user_manual.MMK Currency') }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <i class="fas fa-check-circle text-green-500"></i>
                            <span>{{ __('user_manual.Receipt Generation') }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <i class="fas fa-check-circle text-green-500"></i>
                            <span>{{ __('user_manual.Setup Wizard') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Support Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex items-center gap-2 p-4 border-b border-gray-200 dark:border-gray-700">
                    <i class="fas fa-headset text-emerald-500"></i>
                    <h4 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('user_manual.Support') }}</h4>
                </div>
                <div class="p-4">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr>
                                    <th class="w-32 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">
                                        <i class="fas fa-phone mr-2 text-emerald-500"></i>{{ __('user_manual.Phone') }}
                                    </th>
                                    <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                                        <a href="tel:+959979587680" class="hover:text-emerald-600 dark:hover:text-emerald-400">+959979587680</a>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="w-32 px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50">
                                        <i class="fas fa-envelope mr-2 text-emerald-500"></i>{{ __('user_manual.Email') }}
                                    </th>
                                    <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                                        <a href="mailto:support@smartcampusedu.com" class="hover:text-emerald-600 dark:hover:text-emerald-400">support@smartcampusedu.com</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Mobile App Download Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex items-center gap-2 p-4 border-b border-gray-200 dark:border-gray-700">
                    <i class="fas fa-mobile-alt text-emerald-500"></i>
                    <h4 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('user_manual.Mobile App') }}</h4>
                </div>
                <div class="p-4">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                        <div class="flex-1">
                            <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('user_manual.Download the Teacher mobile app to access the system on your Android device.') }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ __('user_manual.File') }}: Teacher_0.0.1.apk
                            </p>
                        </div>
                        <a href="{{ asset('Teacher_0.0.1.apk') }}" 
                           download="Teacher_0.0.1.apk"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <i class="fas fa-download"></i>
                            {{ __('user_manual.Download APK') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Tip -->
            <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <i class="fas fa-lightbulb text-emerald-600 dark:text-emerald-400 mt-0.5"></i>
                    <p class="text-sm text-emerald-800 dark:text-emerald-200">
                        {{ __('user_manual.Tip: Use the sidebar navigation to access all modules. Toggle dark mode from the profile dropdown for comfortable viewing.') }}
                    </p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
