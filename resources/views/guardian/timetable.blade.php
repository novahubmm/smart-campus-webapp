<x-app-layout>
    <div class="p-6 space-y-6" x-data="{ activeDay: '{{ now()->format('l') }}' }">
        <!-- Back Button & Header -->
        <div class="flex items-center justify-between">
            <a href="{{ route('guardian.students') }}" class="flex items-center text-gray-600 dark:text-gray-300">
                <i class="fas fa-chevron-left mr-2"></i>
                <span class="text-sm font-medium">{{ __('Back') }}</span>
            </a>
            <h1 class="text-lg font-bold text-gray-800 dark:text-white">{{ __('time_table.Timetable') }}</h1>
            <div class="w-8"></div> <!-- Spacer -->
        </div>

        @if($student && count($weeklyTimetable) > 0)
            <!-- Day Selector Tabs -->
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
                                @if($period['is_live'])
                                    <div class="absolute top-0 right-0">
                                        <span
                                            class="bg-red-500 text-white text-[8px] font-black px-3 py-1 rounded-bl-2xl uppercase tracking-widest animate-pulse">
                                            {{ __('Live Now') }}
                                        </span>
                                    </div>
                                @endif

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

                                @if($period['meeting_link'])
                                    <a href="{{ $period['meeting_link'] }}" target="_blank"
                                        class="mt-4 flex items-center justify-center w-full py-2 bg-green-50 hover:bg-green-100 dark:bg-green-900/10 dark:hover:bg-green-900/20 text-green-600 dark:text-green-400 rounded-xl text-[10px] font-black uppercase tracking-wider transition-colors">
                                        <i class="fas fa-video mr-2"></i> Join Class
                                    </a>
                                @endif
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
                    {{ __('Timetable is not available for this student.') }}
                </p>
            </div>
        @endif
    </div>

    <style>
        .animate-fadeIn {
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</x-app-layout>