<x-app-layout>
    <!-- Header Section -->
    <div
        class="bg-white dark:bg-gray-900 pb-24 pt-6 px-6 rounded-b-[40px] relative overflow-hidden transition-colors duration-200">

        <!-- Top Bar: Title -->
        <div class="flex justify-between items-center mb-6 relative z-10">
            <h1 class="text-xl font-bold text-gray-800 dark:text-white">{{ __('profile.Profile') }}</h1>
        </div>

        <!-- Student Info Card -->
        <div
            class="bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-3xl p-6 relative z-10">
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <div class="w-16 h-16 rounded-full p-1 bg-gradient-to-tr from-yellow-400 to-pink-500">
                        <img src="{{ $selectedStudent && $selectedStudent->photo_path ? asset('storage/' . $selectedStudent->photo_path) : 'https://ui-avatars.com/api/?name=' . ($selectedStudent->user->name ?? 'Student') . '&background=random' }}"
                            alt="Student Photo"
                            class="w-full h-full rounded-full object-cover border-2 border-white dark:border-gray-800">
                    </div>
                    <div
                        class="absolute bottom-0 right-0 bg-green-400 text-white text-[10px] w-5 h-5 flex items-center justify-center rounded-full border-2 border-white dark:border-gray-800">
                        <i class="fas fa-check"></i>
                    </div>
                </div>

                <div class="flex-1">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        {{ $selectedStudent->user->name ?? 'Student Name' }}</h2>
                    <div class="flex items-center text-gray-500 dark:text-gray-400 text-xs mt-1 space-x-2">
                        <span
                            class="bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">{{ $selectedStudent->grade->name ?? 'Grade' }}</span>
                        <span>•</span>
                        <span>{{ $selectedStudent->student_identifier ?? 'ID: —' }}</span>
                    </div>
                    <div class="flex items-center text-yellow-500 text-xs mt-2 font-semibold">
                        <i class="fas fa-star mr-1"></i> {{ __('High Achiever') }}
                    </div>
                </div>

                <button
                    class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    <i class="fas fa-file-alt"></i>
                </button>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 gap-3 mt-6">
                <!-- Attendance -->
                <div
                    class="bg-teal-50 dark:bg-teal-900/10 rounded-xl p-3 flex items-center space-x-3 shadow-sm border border-teal-100 dark:border-teal-900/20">
                    <div
                        class="w-8 h-8 rounded-lg bg-teal-100 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 flex items-center justify-center">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div>
                        <p class="text-gray-800 dark:text-white font-bold text-sm">{{ $attendancePercentage ?? '0' }}%
                        </p>
                        <p class="text-teal-600 dark:text-teal-400 text-[10px]">{{ __('attendance.Attendance') }}</p>
                    </div>
                </div>

                <!-- Rank -->
                <div
                    class="bg-yellow-50 dark:bg-yellow-900/10 rounded-xl p-3 flex items-center space-x-3 shadow-sm border border-yellow-100 dark:border-yellow-900/20">
                    <div
                        class="w-8 h-8 rounded-lg bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400 flex items-center justify-center">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div>
                        <p class="text-gray-800 dark:text-white font-bold text-sm">{{ __('Top 3') }}</p>
                        <p class="text-yellow-600 dark:text-yellow-400 text-[10px]">{{ __('Rank') }}</p>
                    </div>
                </div>

                <!-- Class Leader -->
                <div
                    class="bg-pink-50 dark:bg-pink-900/10 rounded-xl p-3 flex items-center space-x-3 shadow-sm border border-pink-100 dark:border-pink-900/20">
                    <div
                        class="w-8 h-8 rounded-lg bg-pink-100 dark:bg-pink-900/30 text-pink-600 dark:text-pink-400 flex items-center justify-center">
                        <i class="fas fa-star"></i>
                    </div>
                    <div>
                        <p class="text-gray-800 dark:text-white font-bold text-sm">{{ __('Leader') }}</p>
                        <p class="text-pink-600 dark:text-pink-400 text-[10px]">{{ __('Class Role') }}</p>
                    </div>
                </div>

                <!-- Competition -->
                <div
                    class="bg-indigo-50 dark:bg-indigo-900/10 rounded-xl p-3 flex items-center space-x-3 shadow-sm border border-indigo-100 dark:border-indigo-900/20">
                    <div
                        class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                        <i class="fas fa-medal"></i>
                    </div>
                    <div>
                        <p class="text-gray-800 dark:text-white font-bold text-sm">{{ __('Winner') }}</p>
                        <p class="text-indigo-600 dark:text-indigo-400 text-[10px]">{{ __('Math Olympiad') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="px-6 -mt-10 relative z-20 pb-24 space-y-6" x-data="{ activeTab: 'academic' }">

        <!-- Tabs -->
        <div
            class="bg-white dark:bg-gray-800 rounded-full p-1.5 flex shadow-sm border border-gray-100 dark:border-gray-700 overflow-x-auto">
            <button @click="activeTab = 'academic'"
                :class="activeTab === 'academic' ? 'bg-blue-500 text-white shadow-sm' : 'text-gray-500 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white'"
                class="flex-1 rounded-full py-2 text-xs font-bold px-4 whitespace-nowrap transition">
                {{ __('Academic') }}
            </button>
            <button @click="activeTab = 'attendance'"
                :class="activeTab === 'attendance' ? 'bg-blue-500 text-white shadow-sm' : 'text-gray-500 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white'"
                class="flex-1 rounded-full py-2 text-xs font-bold px-4 whitespace-nowrap transition">
                {{ __('attendance.Attendance') }}
            </button>
            <button @click="activeTab = 'rankings'"
                :class="activeTab === 'rankings' ? 'bg-blue-500 text-white shadow-sm' : 'text-gray-500 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white'"
                class="flex-1 rounded-full py-2 text-xs font-bold px-4 whitespace-nowrap transition">
                {{ __('Rankings') }}
            </button>
        </div>

        <!-- Academic Content -->
        <div x-show="activeTab === 'academic'" class="space-y-6 animate-fadeIn">
            <!-- Academic Overview -->
            <div
                class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="grid grid-cols-4 gap-4 text-center divide-x divide-gray-100 dark:divide-gray-700">
                    <div>
                        <div
                            class="w-10 h-10 mx-auto rounded-full bg-green-100 text-green-500 flex items-center justify-center mb-2">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="text-lg font-bold text-gray-800 dark:text-white">3.75</div>
                        <div class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                            {{ __('GPA') }}
                        </div>
                    </div>
                    <div>
                        <div
                            class="w-10 h-10 mx-auto rounded-full bg-blue-100 text-blue-500 flex items-center justify-center mb-2">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="text-lg font-bold text-gray-800 dark:text-white">82.5%</div>
                        <div class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                            {{ __('Avg') }}
                        </div>
                    </div>
                    <div>
                        <div
                            class="w-10 h-10 mx-auto rounded-full bg-orange-100 text-orange-500 flex items-center justify-center mb-2">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="text-lg font-bold text-gray-800 dark:text-white">95</div>
                        <div class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                            {{ __('Highest') }}
                        </div>
                    </div>
                    <div>
                        <div
                            class="w-10 h-10 mx-auto rounded-full bg-purple-100 text-purple-500 flex items-center justify-center mb-2">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="text-lg font-bold text-gray-800 dark:text-white">8/8</div>
                        <div class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                            {{ __('Passed') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress Tracking -->
            <div>
                <h3 class="font-bold text-gray-800 dark:text-white mb-4">{{ __('Progress Tracking') }}</h3>
                <div class="grid grid-cols-2 gap-4">
                    <!-- GPA Card -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-3xl p-5 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden">
                        <div class="flex items-center space-x-3 mb-4">
                            <div
                                class="w-10 h-10 rounded-xl bg-purple-100 text-purple-500 flex items-center justify-center">
                                <i class="fas fa-star"></i>
                            </div>
                            <span class="font-bold text-gray-700 dark:text-gray-300 text-sm">{{ __('GPA') }}</span>
                        </div>
                        <div class="flex items-end justify-between">
                            <span class="text-3xl font-bold text-gray-900 dark:text-white">3.75</span>
                            <div class="flex items-center text-green-500 text-xs font-bold mb-1">
                                <i class="fas fa-arrow-up mr-1"></i> +2.7%
                            </div>
                        </div>
                        <div
                            class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-500 to-pink-500 opacity-20">
                        </div>
                    </div>

                    <!-- Rank Card -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-3xl p-5 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden">
                        <div class="flex items-center space-x-3 mb-4">
                            <div
                                class="w-10 h-10 rounded-xl bg-orange-100 text-orange-500 flex items-center justify-center">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <span class="font-bold text-gray-700 dark:text-gray-300 text-sm">{{ __('Rank') }}</span>
                        </div>
                        <div class="flex items-end justify-between">
                            <span class="text-3xl font-bold text-gray-900 dark:text-white">#2</span>
                            <div class="flex items-center text-green-500 text-xs font-bold mb-1">
                                <i class="fas fa-arrow-up mr-1"></i>
                            </div>
                        </div>
                        <div
                            class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-orange-500 to-yellow-500 opacity-20">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Content -->
        <div x-show="activeTab === 'attendance'" class="space-y-6 animate-fadeIn" style="display: none;">
            <div
                class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-bold text-gray-800 dark:text-white">{{ __('Attendance History') }}</h3>
                    <div class="text-xs text-blue-600 font-bold uppercase">{{ now()->format('F Y') }}</div>
                </div>

                <!-- Attendance Stats -->
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-green-50 dark:bg-green-900/10 rounded-2xl p-4 text-center">
                        <div class="text-lg font-bold text-green-600">18</div>
                        <div class="text-[10px] text-green-600/70 uppercase font-bold">{{ __('Present') }}</div>
                    </div>
                    <div class="bg-red-50 dark:bg-red-900/10 rounded-2xl p-4 text-center">
                        <div class="text-lg font-bold text-red-600">1</div>
                        <div class="text-[10px] text-red-600/70 uppercase font-bold">{{ __('Absent') }}</div>
                    </div>
                    <div class="bg-blue-50 dark:bg-blue-900/10 rounded-2xl p-4 text-center">
                        <div class="text-lg font-bold text-blue-600">2</div>
                        <div class="text-[10px] text-blue-600/70 uppercase font-bold">{{ __('Late') }}</div>
                    </div>
                </div>

                <div class="text-center">
                    <a href="{{ route('guardian.attendance') }}"
                        class="inline-flex items-center text-sm font-bold text-blue-600 dark:text-blue-400 group">
                        {{ __('View Detailed Calendar') }}
                        <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Rankings Content -->
        <div x-show="activeTab === 'rankings'" class="space-y-6 animate-fadeIn" style="display: none;">
            <div
                class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center space-x-4 mb-6">
                    <div
                        class="w-12 h-12 rounded-full bg-yellow-400 flex items-center justify-center text-white text-xl shadow-lg shadow-yellow-400/20">
                        <i class="fas fa-crown"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800 dark:text-white">{{ __('Class Top Ranker') }}</h3>
                        <p class="text-xs text-gray-500">Currently ranked #2 in Class 4-A</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-gray-700/50">
                        <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Class Rank</span>
                        <span class="font-bold text-gray-900 dark:text-white">2 / 32</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-gray-700/50">
                        <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Grade Rank</span>
                        <span class="font-bold text-gray-900 dark:text-white">12 / 145</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-gray-700/50">
                        <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Percentile</span>
                        <span class="font-bold text-green-600">Top 5%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>