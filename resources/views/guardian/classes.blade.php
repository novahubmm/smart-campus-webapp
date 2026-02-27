<x-app-layout>
    <div class="p-6 space-y-6">
        <!-- Back Button & Header -->
        <div class="flex items-center justify-between">
            <a href="{{ route('guardian.students') }}" class="flex items-center text-gray-600 dark:text-gray-300">
                <i class="fas fa-chevron-left mr-2"></i>
                <span class="text-sm font-medium">{{ __('Back') }}</span>
            </a>
            <h1 class="text-lg font-bold text-gray-800 dark:text-white">{{ __('My Classes') }}</h1>
            <div class="w-8"></div> <!-- Spacer -->
        </div>

        @if($student && $classInfo)
            <!-- Class Info Card -->
            <div
                class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-3xl p-6 text-white shadow-lg overflow-hidden relative">
                <div class="relative z-10 flex justify-between items-start">
                    <div>
                        <span
                            class="text-blue-100 text-xs font-semibold uppercase tracking-wider">{{ __('Class Information') }}</span>
                        <h2 class="text-3xl font-extrabold mt-1">{{ $classInfo['class_name'] }}-{{ $classInfo['section'] }}
                        </h2>
                        <div class="mt-4 flex items-center space-x-4">
                            <div class="flex items-center">
                                <i class="fas fa-door-open mr-2 text-blue-200"></i>
                                <span class="text-sm">{{ __('Room') }}: {{ $classInfo['room'] }}</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-users mr-2 text-blue-200"></i>
                                <span class="text-sm">{{ $classInfo['total_students'] }} {{ __('Students') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white/20 p-3 rounded-2xl backdrop-blur-md">
                        <i class="fas fa-chalkboard-teacher text-2xl"></i>
                    </div>
                </div>
                <!-- Decorative circle -->
                <div class="absolute -bottom-12 -right-12 w-48 h-48 bg-white/10 rounded-full blur-3xl"></div>
            </div>

            <!-- Tabs Container -->
            <div x-data="{ activeTab: 'subjects', activeDay: '{{ now()->format('l') }}' }" class="space-y-6">
                <!-- Tab Navigation -->
                <div class="flex p-1 space-x-1 bg-gray-100 dark:bg-gray-800 rounded-xl">
                    <button @click="activeTab = 'subjects'"
                        :class="activeTab === 'subjects' ? 'bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-400 shadow' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                        class="flex-1 py-2.5 text-sm font-bold rounded-lg transition-all">
                        {{ __('Subjects (Teacher)') }}
                    </button>
                    <button @click="activeTab = 'timetable'"
                        :class="activeTab === 'timetable' ? 'bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-400 shadow' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                        class="flex-1 py-2.5 text-sm font-bold rounded-lg transition-all">
                        {{ __('Class Timetable') }}
                    </button>
                </div>

                <!-- Subjects Tab -->
                <div x-show="activeTab === 'subjects'" x-cloak class="space-y-4 animate-fadeIn">
                    @if(count($subjects) > 0)
                        <div class="grid grid-cols-1 gap-4">
                            @foreach($subjects as $subject)
                                <div
                                    class="bg-white dark:bg-gray-800 rounded-3xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center">
                                            <div
                                                class="w-12 h-12 rounded-2xl bg-teal-50 dark:bg-teal-900/20 text-teal-500 flex items-center justify-center mr-4 border border-teal-100/50 dark:border-teal-800/50">
                                                <i class="fas fa-{{ $subject['icon'] ?? 'book' }} text-xl"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-gray-800 dark:text-white text-base">{{ $subject['name'] }}
                                                </h4>
                                                <p class="text-[11px] text-gray-500 dark:text-gray-400 flex items-center mt-0.5">
                                                    <i class="fas fa-user-tie mr-1 text-teal-400"></i>
                                                    {{ $subject['teacher'] }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-white dark:bg-gray-800 rounded-3xl p-12 shadow-sm text-center">
                            <div
                                class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                                <i class="fas fa-shapes text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800 dark:text-white">{{ __('No Subjects Found') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                                {{ __('No subjects have been assigned to this class yet.') }}
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Timetable Tab -->
                <div x-show="activeTab === 'timetable'" x-cloak class="space-y-4 animate-fadeIn">
                    @if(count($weeklyTimetable) > 0)
                        <!-- Day Selector -->
                        <div class="flex space-x-2 overflow-x-auto pb-2 scrollbar-hide -mx-1 px-1">
                            @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $day)
                                <button @click="activeDay = '{{ $day }}'"
                                    :class="activeDay === '{{ $day }}' ? 'bg-green-500 text-white shadow-md' : 'bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 border border-gray-100 dark:border-gray-700'"
                                    class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-all whitespace-nowrap">
                                    {{ __($day) }}
                                </button>
                            @endforeach
                        </div>

                        <!-- Timetable Content -->
                        @foreach($weeklyTimetable as $day => $periods)
                            <div x-show="activeDay === '{{ $day }}'" class="space-y-4 animate-fadeIn">
                                @if(count($periods) > 0)
                                    @foreach($periods as $period)
                                        <div
                                            class="bg-white dark:bg-gray-800 rounded-3xl p-4 shadow-sm border border-gray-50 dark:border-gray-700 relative overflow-hidden">
                                            <div class="flex items-start">
                                                <!-- Time Column -->
                                                <div class="mr-4 text-center min-w-[60px]">
                                                    <span
                                                        class="text-xs font-black text-gray-800 dark:text-white block">{{ Carbon\Carbon::parse($period['start_time'])->format('h:i') }}</span>
                                                    <span
                                                        class="text-[9px] font-bold text-gray-400 uppercase">{{ Carbon\Carbon::parse($period['start_time'])->format('A') }}</span>
                                                    <div class="w-px h-6 bg-gray-100 dark:bg-gray-700 mx-auto my-1"></div>
                                                    <span
                                                        class="text-xs font-bold text-gray-400 block">{{ Carbon\Carbon::parse($period['end_time'])->format('h:i') }}</span>
                                                </div>

                                                <!-- Info Column -->
                                                <div class="flex-1">
                                                    <div class="flex items-center mb-1">
                                                        <div
                                                            class="w-8 h-8 rounded-xl bg-green-50 dark:bg-green-900/20 text-green-500 flex items-center justify-center mr-3 border border-green-100/30 dark:border-green-800/30">
                                                            <i class="fas fa-{{ $period['subject_icon'] ?? 'book' }} text-sm"></i>
                                                        </div>
                                                        <h4 class="font-bold text-gray-800 dark:text-white text-base">{{ $period['subject'] }}</h4>
                                                    </div>

                                                    <div class="flex items-center space-x-4 mt-3">
                                                        <div class="flex items-center text-[10px] text-gray-500 dark:text-gray-400 font-medium">
                                                            <i class="fas fa-user-tie mr-1.5 text-green-400"></i>
                                                            {{ $period['teacher'] }}
                                                        </div>
                                                        <div class="flex items-center text-[10px] text-gray-500 dark:text-gray-400 font-medium">
                                                            <i class="fas fa-door-open mr-1.5 text-green-400"></i>
                                                            {{ __('Room') }}: {{ $period['room'] }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div
                                        class="bg-gray-50 dark:bg-gray-800/50 rounded-3xl p-12 text-center border-2 border-dashed border-gray-100 dark:border-gray-700">
                                        <i class="fas fa-mug-hot text-3xl text-gray-300 dark:text-gray-600 mb-3"></i>
                                        <h3 class="font-bold text-gray-800 dark:text-white">{{ __('No Classes') }}</h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('No periods scheduled for this day.') }}</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="bg-white dark:bg-gray-800 rounded-3xl p-12 shadow-sm text-center">
                            <div
                                class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                                <i class="fas fa-calendar-alt text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800 dark:text-white">{{ __('No Timetable Found') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                                {{ __('Timetable is not available for this class.') }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Styles for animations/scroll -->
            <style>
                .animate-fadeIn { animation: fadeIn 0.3s ease-out; }
                @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
                .scrollbar-hide::-webkit-scrollbar { display: none; }
                .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
            </style>
        @else
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-12 shadow-sm text-center">
                <div
                    class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-info-circle text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">{{ __('No Class Info Found') }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    {{ __('The student is not currently assigned to any class.') }}</p>
            </div>
        @endif
    </div>
</x-app-layout>