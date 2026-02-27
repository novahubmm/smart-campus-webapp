<x-app-layout>
    <div class="p-6 space-y-6">
        <!-- Back Button & Header -->
        <div class="flex items-center justify-between">
            <a href="{{ route('guardian.students') }}" class="flex items-center text-gray-600 dark:text-gray-300">
                <i class="fas fa-chevron-left mr-2"></i>
                <span class="text-sm font-medium">{{ __('Back') }}</span>
            </a>
            <h1 class="text-lg font-bold text-gray-800 dark:text-white">{{ __('Subjects') }}</h1>
            <div class="w-8"></div> <!-- Spacer -->
        </div>

        @if($student && count($subjects) > 0)
            <!-- Featured Subject Card (Top performance etc. - placeholder for now) -->
            <div
                class="bg-gradient-to-br from-teal-500 to-emerald-600 rounded-3xl p-6 text-white shadow-lg overflow-hidden relative">
                <div class="relative z-10">
                    <span
                        class="text-teal-100 text-[10px] font-bold uppercase tracking-wider">{{ __('Academic Excellence') }}</span>
                    <h2 class="text-2xl font-bold mt-1">{{ __('Course Overview') }}</h2>
                    <p class="text-teal-50 mt-2 text-xs opacity-90 leading-relaxed">
                        {{ __('Your child is currently enrolled in :count subjects this semester.', ['count' => count($subjects)]) }}
                    </p>
                    <div class="mt-4 flex items-center space-x-2">
                        <div class="px-3 py-1 bg-white/20 rounded-full text-[10px] font-bold backdrop-blur-sm">
                            <i class="fas fa-chart-line mr-1"></i> {{ __('Progressing Well') }}
                        </div>
                    </div>
                </div>
                <i class="fas fa-graduation-cap absolute -bottom-4 -right-4 text-8xl text-white/10 rotate-12"></i>
            </div>

            <!-- Subjects Grid -->
            <div class="space-y-4">
                <h3 class="text-sm font-bold text-gray-800 dark:text-white flex items-center">
                    <i class="fas fa-book-reader text-teal-500 mr-2"></i>
                    {{ __('All Subjects') }}
                </h3>

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
                                <div class="text-right">
                                    <span
                                        class="text-xs font-bold text-gray-400 dark:text-gray-500 block uppercase tracking-tighter">{{ __('Recent Score') }}</span>
                                    <span
                                        class="text-xl font-black text-teal-600 dark:text-teal-400">{{ $subject['current_marks'] }}<span
                                            class="text-[10px] text-gray-400 font-medium ml-0.5">/{{ $subject['total_marks'] }}</span></span>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div class="space-y-1.5">
                                <div class="flex justify-between items-center text-[10px] font-bold uppercase tracking-tight">
                                    <span class="text-gray-400">{{ __('Overall Performance') }}</span>
                                    <span
                                        class="text-teal-600 dark:text-teal-400">{{ round(($subject['current_marks'] / $subject['total_marks']) * 100) }}%</span>
                                </div>
                                <div class="h-2 w-full bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                    <div class="h-full bg-teal-500 rounded-full"
                                        style="width: {{ ($subject['current_marks'] / $subject['total_marks']) * 100 }}%"></div>
                                </div>
                            </div>

                            <div class="mt-4 flex space-x-2">
                                <button
                                    class="flex-1 py-1.5 bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 rounded-xl text-[10px] font-bold hover:bg-gray-100 transition-colors uppercase tracking-wider">
                                    {{ __('Curriculum') }}
                                </button>
                                <button
                                    class="flex-1 py-1.5 bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 rounded-xl text-[10px] font-bold hover:bg-gray-100 transition-colors uppercase tracking-wider">
                                    {{ __('Assignments') }}
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-12 shadow-sm text-center">
                <div
                    class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                    <i class="fas fa-shapes text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">{{ __('No Subjects Found') }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    {{ __('No subjects have been assigned to this student yet.') }}
                </p>
            </div>
        @endif
    </div>
</x-app-layout>