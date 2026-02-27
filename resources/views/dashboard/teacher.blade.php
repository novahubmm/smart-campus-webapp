<x-app-layout>
    <div class="py-6 sm:py-10 bg-gray-50 dark:bg-gray-900 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(isset($teacherProfile))
                <!-- My Ongoing Class Widget -->
                <div class="space-y-4">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        <i class="fas fa-play-circle text-blue-500"></i>
                        {{ __('My Ongoing Class') }}
                    </h2>

                    @if(isset($ongoingClass) && $ongoingClass)
                        <a href="{{ route('teacher.my-classes') }}"
                            class="block bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden active:scale-[0.98] transition-transform">
                            <!-- Background Pulse -->
                            <div
                                class="absolute top-0 right-0 w-32 h-32 bg-green-500/5 rounded-full -mr-16 -mt-16 animate-pulse">
                            </div>

                            <div class="flex items-center justify-between relative z-10">
                                <div class="flex items-center space-x-4">
                                    <div
                                        class="w-14 h-14 rounded-2xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-green-600 dark:text-green-400 text-xl font-bold">
                                        {{ $ongoingClass['period'] }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                                            <span
                                                class="text-[10px] font-bold text-green-600 uppercase tracking-wider">{{ __('Teaching Now') }}</span>
                                        </div>
                                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                            {{ $ongoingClass['subject'] }}</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-300">
                                            <i class="fas fa-users mr-1 text-xs"></i> {{ $ongoingClass['grade'] }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-bold text-gray-900 dark:text-white">
                                        {{ $ongoingClass['time'] }}
                                    </div>
                                    <div class="text-[10px] text-gray-500 uppercase mt-1">
                                        {{ $ongoingClass['room'] }}
                                    </div>
                                </div>
                            </div>

                            <!-- Progress Bar Hint -->
                            <div class="mt-4 h-1.5 w-full bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full bg-green-500 animate-timer" style="width: 45%;"></div>
                            </div>
                        </a>
                    @else
                        <div
                            class="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-sm border border-gray-100 dark:border-gray-700 text-center">
                            <div
                                class="w-16 h-16 bg-gray-50 dark:bg-gray-700/50 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-coffee text-gray-300 dark:text-gray-600 text-2xl"></i>
                            </div>
                            <h3 class="font-bold text-gray-800 dark:text-white">{{ __('No Ongoing Class') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-300 mt-1">
                                {{ __('You don\'t have any class to teach at the moment.') }}</p>
                        </div>
                    @endif
                </div>

                <!-- Today's Teaching Schedule -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                            <i class="fas fa-calendar-day text-blue-500"></i>
                            {{ __('My Teaching Schedule') }}
                        </h2>
                        <a href="{{ route('teacher.my-attendance') }}"
                            class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">
                            {{ __('View Full') }}
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse($todaySchedule ?? [] as $period)
                            <div
                                class="bg-white dark:bg-gray-800 rounded-2xl p-4 flex items-center justify-between shadow-sm border {{ $period['status'] == 'ongoing' ? 'border-green-200 ring-1 ring-green-100' : 'border-gray-100 dark:border-gray-700' }}">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-bold {{ $period['status'] == 'ongoing' ? 'bg-green-100 text-green-600' : ($period['status'] == 'completed' ? 'bg-gray-100 text-gray-400' : 'bg-blue-50 text-blue-500') }}">
                                        {{ $period['period'] }}
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-900 dark:text-white text-sm">
                                            {{ $period['subject'] }}
                                        </h4>
                                        <div
                                            class="flex items-center gap-2 text-[10px] text-gray-500 dark:text-gray-300 mt-0.5">
                                            <span>{{ $period['time'] }}</span>
                                            <span>•</span>
                                            <span>{{ $period['grade'] }}</span>
                                            <span>•</span>
                                            <span>{{ $period['room'] }}</span>
                                        </div>
                                    </div>
                                </div>

                                @if($period['status'] == 'ongoing')
                                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                                @elseif($period['status'] == 'completed')
                                    <i class="fas fa-check-circle text-green-500 text-xs"></i>
                                @else
                                    <i class="fas fa-clock text-gray-300 text-xs"></i>
                                @endif
                            </div>
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
                        <i class="fas fa-chalkboard-teacher text-blue-500 text-4xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ __('Welcome to Teacher Portal') }}</h2>
                    <p class="text-gray-500 mt-2 max-w-xs mx-auto text-sm">
                        {{ __('Please ensure your teacher profile is correctly set up to view your teaching schedule.') }}
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