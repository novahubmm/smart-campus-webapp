<x-app-layout>
    <div class="p-6 space-y-6">
        <!-- Back Button & Header -->
        <div class="flex items-center justify-between">
            <a href="{{ route('guardian.students') }}" class="flex items-center text-gray-600 dark:text-gray-400">
                <i class="fas fa-chevron-left mr-2"></i>
                <span class="text-sm font-medium">{{ __('Back') }}</span>
            </a>
            <h1 class="text-lg font-bold text-gray-800 dark:text-white">{{ __('exams.Exams') }}</h1>
            <div class="w-8"></div> <!-- Spacer -->
        </div>

        @if($student && count($exams) > 0)
            <!-- Tabs Container -->
            <div x-data="{ activeTab: 'upcoming' }" class="space-y-6">
                <!-- Tab Navigation -->
                <div class="flex p-1 space-x-1 bg-gray-100 dark:bg-gray-800 rounded-xl">
                    <button @click="activeTab = 'upcoming'"
                        :class="activeTab === 'upcoming' ? 'bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-400 shadow' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                        class="flex-1 py-2.5 text-sm font-bold rounded-lg transition-all">
                        {{ __('Upcoming Exams') }}
                    </button>
                    <button @click="activeTab = 'completed'"
                        :class="activeTab === 'completed' ? 'bg-white dark:bg-gray-700 text-green-600 dark:text-green-400 shadow' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                        class="flex-1 py-2.5 text-sm font-bold rounded-lg transition-all">
                        {{ __('Completed Exams') }}
                    </button>
                </div>

                <!-- Upcoming Tab -->
                <div x-show="activeTab === 'upcoming'" x-cloak class="space-y-3 animate-fadeIn">
                    @php
                        $upcomingExams = array_filter($exams, fn($e) => in_array($e['status'], ['scheduled', 'ongoing']));
                    @endphp

                    @if(count($upcomingExams) > 0)
                        @foreach($upcomingExams as $exam)
                            <div class="bg-white dark:bg-gray-800 rounded-3xl p-4 shadow-sm border border-gray-50 dark:border-gray-700">
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-2xl bg-blue-100 dark:bg-blue-900/30 text-blue-500 flex items-center justify-center mr-3">
                                            <i class="fas fa-file-invoice text-lg"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-gray-800 dark:text-white text-sm line-clamp-1">
                                                {{ $exam['name'] }}</h4>
                                            <p class="text-[10px] text-gray-500 dark:text-gray-400 font-medium uppercase">
                                                {{ $exam['subject'] }}</p>
                                        </div>
                                    </div>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase {{ 
                                        $exam['status'] == 'ongoing' ? 'bg-yellow-100 text-yellow-600 animate-pulse' : 'bg-blue-100 text-blue-600' 
                                    }}">
                                        {{ __($exam['status']) }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 gap-y-2 border-t border-gray-50 dark:border-gray-700/50 pt-3">
                                    <div class="flex items-center text-[11px] text-gray-600 dark:text-gray-400">
                                        <i class="fas fa-calendar-day w-4 mr-1 text-gray-400"></i>
                                        {{ Carbon\Carbon::parse($exam['date'])->format('d M, Y') }}
                                    </div>
                                    <div class="flex items-center text-[11px] text-gray-600 dark:text-gray-400">
                                        <i class="fas fa-clock w-4 mr-1 text-gray-400"></i>
                                        {{ $exam['start_time'] ? Carbon\Carbon::parse($exam['start_time'])->format('h:i A') : 'TBA' }}
                                    </div>
                                    <div class="flex items-center text-[11px] text-gray-600 dark:text-gray-400">
                                        <i class="fas fa-map-marker-alt w-4 mr-1 text-gray-400"></i>
                                        {{ $exam['room'] }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-8">
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No upcoming exams scheduled.') }}</p>
                        </div>
                    @endif
                </div>

                <!-- Completed Tab -->
                <div x-show="activeTab === 'completed'" x-cloak class="space-y-3 animate-fadeIn">
                    @php
                        $completedExams = array_filter($exams, fn($e) => $e['status'] == 'completed');
                    @endphp

                    @if(count($completedExams) > 0)
                        @foreach($completedExams as $exam)
                            <div class="bg-white dark:bg-gray-800 rounded-3xl p-4 shadow-sm border border-gray-50 dark:border-gray-700 opacity-90 hover:opacity-100 transition-opacity">
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-2xl bg-gray-100 dark:bg-gray-700 text-gray-500 flex items-center justify-center mr-3">
                                            <i class="fas fa-check-circle text-lg"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-gray-800 dark:text-white text-sm line-clamp-1">
                                                {{ $exam['name'] }}</h4>
                                            <p class="text-[10px] text-gray-500 dark:text-gray-400 font-medium uppercase">
                                                {{ $exam['subject'] }}</p>
                                        </div>
                                    </div>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase bg-gray-100 text-gray-600">
                                        {{ __($exam['status']) }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 gap-y-2 border-t border-gray-50 dark:border-gray-700/50 pt-3">
                                    <div class="flex items-center text-[11px] text-gray-600 dark:text-gray-400">
                                        <i class="fas fa-calendar-day w-4 mr-1 text-gray-400"></i>
                                        {{ Carbon\Carbon::parse($exam['date'])->format('d M, Y') }}
                                    </div>
                                    <div class="flex items-center text-[11px] text-gray-600 dark:text-gray-400">
                                        <i class="fas fa-star w-4 mr-1 text-gray-400"></i>
                                        {{ __('Result') }}:
                                        {!! $exam['has_result'] ? '<span class="text-green-600 font-bold">' . __('Published') . '</span>' : '<span class="text-gray-400">' . __('Pending') . '</span>' !!}
                                    </div>
                                </div>
                                @if($exam['has_result'])
                                    <a href="#"
                                        class="mt-3 block w-full text-center py-2 bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 rounded-xl text-xs font-bold hover:bg-green-100 transition-colors">
                                        {{ __('View Report Card') }}
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-8">
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No completed exams found.') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <style>
                .animate-fadeIn { animation: fadeIn 0.3s ease-out; }
                @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
            </style>
        @else
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-12 shadow-sm text-center">
                <div
                    class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                    <i class="fas fa-clipboard-question text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">{{ __('No Exams Found') }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    {{ __('There are no exams scheduled for this student at the moment.') }}</p>
            </div>
        @endif
    </div>
</x-app-layout>