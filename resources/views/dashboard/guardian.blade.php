<x-app-layout>
    <div class="py-6 sm:py-10 bg-gray-50 dark:bg-gray-900 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(isset($student))
                <!-- Ongoing Class Widget -->
                <div class="space-y-4">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        <i class="fas fa-play-circle text-blue-500"></i>
                        {{ __('ongoing_class.ongoing_class') }}
                    </h2>

                    @if(isset($ongoingClass) && $ongoingClass)
                        <a href="{{ route('guardian.ongoing-class-detail', $ongoingClass['id']) }}"
                            class="block bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden active:scale-[0.98] transition-transform">
                            <!-- Background Pulse -->
                            <div
                                class="absolute top-0 right-0 w-32 h-32 bg-green-500/5 rounded-full -mr-16 -mt-16 animate-pulse">
                            </div>

                            <div class="flex items-center justify-between relative z-10">
                                <div class="flex items-center space-x-4">
                                    <div
                                        class="w-14 h-14 rounded-2xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-green-600 dark:text-green-400 text-xl font-bold">
                                        P{{ $ongoingClass['period_number'] }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                                            <span
                                                class="text-[10px] font-bold text-green-600 uppercase tracking-wider">{{ __('Live Now') }}</span>
                                        </div>
                                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                            {{ $ongoingClass['subject'] }}</h3>
                                        @if(isset($ongoingClass['teacher']))
                                            <p class="text-sm text-gray-500 dark:text-gray-300">
                                                <i class="fas fa-user-tie mr-1 text-xs"></i> {{ $ongoingClass['teacher'] }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-bold text-gray-900 dark:text-white">
                                        {{ $ongoingClass['start_time'] }} - {{ $ongoingClass['end_time'] }}
                                    </div>
                                    <div class="text-[10px] text-gray-500 uppercase mt-1">
                                        {{ $ongoingClass['room'] }}
                                    </div>
                                </div>
                            </div>

                            <!-- Progress Bar Hint -->
                            <div class="mt-4 h-1.5 w-full bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full bg-green-500 animate-timer" style="width: 65%;"></div>
                            </div>
                        </a>
                    @else
                        <div
                            class="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-sm border border-gray-100 dark:border-gray-700 text-center">
                            <div
                                class="w-16 h-16 bg-gray-50 dark:bg-gray-700/50 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-bed text-gray-300 dark:text-gray-600 text-2xl"></i>
                            </div>
                            <h3 class="font-bold text-gray-800 dark:text-white">{{ __('No Ongoing Class') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-300 mt-1">
                                {{ __('Either no class is scheduled right now or it\'s a break.') }}</p>
                        </div>
                    @endif
                </div>

                <!-- Today's Schedule -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                            <i class="fas fa-calendar-day text-blue-500"></i>
                            {{ __('ongoing_class.today_schedule') }}
                        </h2>
                        <a href="{{ route('guardian.timetable') }}"
                            class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">
                            {{ __('View Full') }}
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse($todaySchedule ?? [] as $period)
                            <a href="{{ route('guardian.ongoing-class-detail', $period['id']) }}"
                                class="bg-white dark:bg-gray-800 rounded-2xl p-4 flex items-center justify-between shadow-sm border {{ $period['is_live'] ? 'border-green-200 ring-1 ring-green-100' : 'border-gray-100 dark:border-gray-700' }} active:scale-[0.99] transition-transform">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-bold {{ $period['is_live'] ? 'bg-green-100 text-green-600' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400' }}">
                                        P{{ $period['period_number'] }}
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-900 dark:text-white text-sm">
                                            @if($period['is_break'])
                                                <i class="fas fa-mug-hot text-amber-500 mr-1"></i>
                                            @endif
                                            {{ $period['subject'] }}
                                        </h4>
                                        <div
                                            class="flex items-center gap-2 text-[10px] text-gray-500 dark:text-gray-300 mt-0.5">
                                            <span>{{ $period['start_time'] }} - {{ $period['end_time'] }}</span>
                                            <span>•</span>
                                            <span>{{ $period['teacher'] }}</span>
                                        </div>
                                    </div>
                                </div>

                                @if($period['is_live'])
                                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                                @else
                                    <i class="fas fa-chevron-right text-gray-300 text-xs text-right"></i>
                                @endif
                            </a>
                        @empty
                            <div class="text-center py-10">
                                <i class="fas fa-calendar-times text-gray-200 text-4xl mb-3"></i>
                                <p class="text-gray-500 dark:text-gray-400 italic text-sm">{{ __('No classes scheduled for today.') }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @else
                <div class="text-center py-20">
                    <div
                        class="w-24 h-24 bg-blue-50 dark:bg-blue-900/20 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-user-graduate text-blue-500 text-4xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ __('Welcome to Guardian Portal') }}</h2>
                    <p class="text-gray-500 mt-2 max-w-xs mx-auto text-sm">
                        {{ __('Please select a student from the switcher above to view their academic progress and schedule.') }}
                    </p>
                </div>
            @endif

        </div>
    </div>

    <style>
        @keyframes timer {
            from {
                width: 0%;
            }

            to {
                width: 100%;
            }
        }

        .animate-timer {
            animation: timer 45s linear infinite;
        }
    </style>
</x-app-layout>