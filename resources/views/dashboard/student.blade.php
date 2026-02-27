<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-home"></i>
            </div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('Student Dashboard') }}</h1>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- School Info Header Section -->
        @if($setting)
            <div
                class="bg-gradient-to-r from-emerald-600 to-teal-600 rounded-3xl p-8 text-white shadow-xl relative overflow-hidden">
                <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="space-y-4">
                        <div
                            class="inline-flex items-center px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-sm font-medium">
                            <i class="fas fa-graduation-cap mr-2"></i>
                            {{ __('Student Portal') }}
                        </div>
                        <h2 class="text-4xl font-extrabold tracking-tight">
                            {{ $setting->school_name ?? 'SMART CAMPUS' }}
                        </h2>
                        <div class="flex flex-wrap gap-4 text-emerald-50">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-user-circle opacity-75"></i>
                                <span>{{ $studentProfile->user->name }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-id-card opacity-75"></i>
                                <span>{{ $studentProfile->student_identifier }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-chalkboard opacity-75"></i>
                                <span>{{ $studentProfile->grade->level ?? 'N/A' }} -
                                    {{ $studentProfile->classModel->name ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="hidden lg:block">
                        @php
                            $schoolLogo = $setting?->school_logo_path;
                            $logoUrl = $schoolLogo ? asset('storage/' . $schoolLogo) : asset('school-banner-logo.svg');
                        @endphp
                        <img src="{{ $logoUrl }}" class="h-32 w-auto drop-shadow-2xl" alt="School Logo">
                    </div>
                </div>
                <!-- Abstract Background Shapes -->
                <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-48 h-48 bg-emerald-400/20 rounded-full blur-2xl"></div>
            </div>
        @endif

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center gap-5">
                <div
                    class="w-14 h-14 rounded-2xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600 dark:text-blue-400">
                    <i class="fas fa-clipboard-list text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">{{ __('Upcoming Exams') }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $upcomingExams->count() }}</p>
                </div>
            </div>

            <div
                class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center gap-5">
                <div
                    class="w-14 h-14 rounded-2xl bg-orange-50 dark:bg-orange-900/20 flex items-center justify-center text-orange-600 dark:text-orange-400">
                    <i class="fas fa-tasks text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">{{ __('Active Homework') }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $activeHomework->count() }}</p>
                </div>
            </div>

            <div
                class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center gap-5">
                <div
                    class="w-14 h-14 rounded-2xl bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center text-purple-600 dark:text-purple-400">
                    <i class="fas fa-bullhorn text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">{{ __('Latest Announcements') }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $announcements->count() }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Upcoming Exams -->
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-calendar-alt text-blue-500"></i>
                        {{ __('Upcoming Exams') }}
                    </h3>
                    <a href="{{ route('student.exams') }}" class="text-sm text-blue-600 hover:underline">View All</a>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($upcomingExams as $exam)
                        <div
                            class="p-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 rounded-xl bg-gray-50 dark:bg-gray-700 flex flex-col items-center justify-center border border-gray-100 dark:border-gray-600">
                                    <span
                                        class="text-[10px] uppercase font-bold text-gray-500 dark:text-gray-400">{{ $exam->start_date->format('M') }}</span>
                                    <span
                                        class="text-lg font-bold text-gray-900 dark:text-white leading-none">{{ $exam->start_date->format('d') }}</span>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 dark:text-white">{{ $exam->name }}</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $exam->examType->name ?? 'General' }}</p>
                                </div>
                            </div>
                            <span
                                class="px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                {{ $exam->start_date->diffForHumans() }}
                            </span>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                            <i class="fas fa-check-circle text-3xl mb-2 text-emerald-500"></i>
                            <p>No upcoming exams scheduled</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Active Homework -->
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-tasks text-orange-500"></i>
                        {{ __('Active Homework') }}
                    </h3>
                    <a href="{{ route('student.homework') }}" class="text-sm text-blue-600 hover:underline">View All</a>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($activeHomework as $hw)
                        <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <div class="flex justify-between items-start mb-1">
                                <h4 class="font-semibold text-gray-900 dark:text-white">{{ $hw->title }}</h4>
                                <span
                                    class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase {{ $hw->priority === 'high' ? 'bg-red-100 text-red-800' : ($hw->priority === 'medium' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800') }}">
                                    {{ $hw->priority }}
                                </span>
                            </div>
                            <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-book-open w-4"></i>
                                    {{ $hw->subject->name }}
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-clock w-4"></i>
                                    Due: {{ $hw->due_date->format('M d, Y') }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                            <i class="fas fa-smile text-3xl mb-2 text-yellow-500"></i>
                            <p>All homework completed! Great job!</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>