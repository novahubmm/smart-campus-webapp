<x-app-layout>
    <x-slot name="header">
        <x-page-header 
            icon="fas fa-chalkboard"
            iconBg="bg-green-50 dark:bg-green-900/30"
            iconColor="text-green-700 dark:text-green-200"
            :subtitle="__('ongoing_class.Academic')"
            :title="__('ongoing_class.Virtual Campus')"
        />
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            {{-- Header with Stats & Filters --}}
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></span>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            <span id="ongoing-count">{{ collect($campusData)->where('period_status', 'ongoing')->count() }}</span> {{ __('Live') }}
                        </span>
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        <i class="fas fa-clock mr-1"></i>{{ $currentTime }} • {{ $selectedDate->format('D, M d') }}
                    </div>
                </div>
                <form method="GET" class="flex flex-wrap items-center gap-2" id="filterForm">
                    <div class="flex items-center gap-2">
                        <input type="date" name="date" value="{{ $selectedDate->format('Y-m-d') }}"
                            onchange="document.getElementById('filterForm').submit()"
                            class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:border-primary-500 focus:ring-primary-500">
                        @if(!$selectedDate->isToday())
                            <a href="{{ route('ongoing-class.index', ['grade_id' => $gradeId]) }}" 
                               class="px-3 py-2 text-sm font-medium text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 rounded-lg hover:bg-primary-100 dark:hover:bg-primary-900/40 transition-colors">
                                <i class="fas fa-calendar-day mr-1"></i>{{ __('Today') }}
                            </a>
                        @endif
                    </div>
                    <select name="grade_id" onchange="document.getElementById('filterForm').submit()"
                        class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="">{{ __('All Grades') }}</option>
                        @foreach($grades as $grade)
                            <option value="{{ $grade->id }}" {{ $gradeId == $grade->id ? 'selected' : '' }}>@gradeName($grade->level)</option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                        <i class="fas fa-search mr-1"></i>{{ __('Filter') }}
                    </button>
                </form>
            </div>

            {{-- Weekend Notice --}}
            @if($selectedDate->isWeekend())
                <div class="bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-800 p-6 text-center">
                    <i class="fas fa-umbrella-beach text-4xl text-amber-500 mb-3"></i>
                    <p class="text-amber-700 dark:text-amber-300 font-medium">{{ __('Weekend - No Classes') }}</p>
                </div>
            @endif

            {{-- Class Cards --}}
            <div class="space-y-4">
                @forelse($campusData as $data)
                    @php
                        $class = $data['class'];
                        $periods = $data['periods'];
                        $currentPeriod = $data['current_period'];
                        $status = $data['period_status'];
                        $studentCount = $data['student_count'];
                        $isToday = $selectedDate->isToday();
                    @endphp
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        {{-- Class Header - Clickable --}}
                        <a href="{{ route('ongoing-class.class-detail', ['class' => $class->id, 'date' => $selectedDate->format('Y-m-d')]) }}" 
                           class="block p-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                                    <span class="text-lg font-bold text-primary-600 dark:text-primary-400">{{ $class->grade->level ?? '-' }}</span>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">@className($class->name, $class->grade?->level)</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        <i class="fas fa-users mr-1"></i>{{ $studentCount }} {{ __('ongoing_class.students') }}
                                        @if($class->teacher)
                                            • <i class="fas fa-user-tie mr-1"></i>{{ $class->teacher->user->name ?? '-' }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <span class="text-gray-400">
                                <i class="fas fa-chevron-right"></i>
                            </span>
                        </a>

                        {{-- Today's Schedule --}}
                        <div class="p-4">
                          
                            @if(count($periods) > 0)
                                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-2">
                                    @foreach($periods as $period)
                                        @php
                                            $startTime = \Carbon\Carbon::parse($period->starts_at)->format('H:i');
                                            $endTime = \Carbon\Carbon::parse($period->ends_at)->format('H:i');
                                            $isPastDate = $selectedDate->lt(\Carbon\Carbon::today());
                                            $isActive = $isToday && $currentTime >= $startTime && $currentTime < $endTime;
                                            $isPast = $isPastDate || ($isToday && $currentTime >= $endTime);
                                            $isUpcoming = !$isPastDate && (!$isToday || $currentTime < $startTime);
                                            $periodDetailUrl = route('ongoing-class.class-detail', ['class' => $class->id, 'date' => $selectedDate->format('Y-m-d'), 'period_id' => $period->id]);
                                        @endphp
                                        <a href="{{ $periodDetailUrl }}" 
                                           class="block p-3 rounded-lg text-center cursor-pointer hover:ring-2 hover:ring-blue-400 transition-all
                                            {{ $isActive ? 'bg-green-100 dark:bg-green-900/30 border-2 border-green-500' : '' }}
                                            {{ $isPast ? 'bg-gray-100 dark:bg-gray-700/50' : '' }}
                                            {{ $isUpcoming ? 'bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-600' : '' }}">
                                            
                                            {{-- Status Badge --}}
                                            <div class="mb-1">
                                                @if($isActive)
                                                    <span class="px-1.5 py-0.5 text-[10px] font-bold bg-green-500 text-white rounded uppercase">{{ __('ongoing_class.Live') }}</span>
                                                @elseif($isPast)
                                                    <span class="px-1.5 py-0.5 text-[10px] font-medium bg-gray-400 text-white rounded uppercase">{{ __('ongoing_class.Done') }}</span>
                                                @else
                                                    <span class="px-1.5 py-0.5 text-[10px] font-medium bg-amber-400 text-white rounded uppercase">{{ __('ongoing_class.Soon') }}</span>
                                                @endif
                                            </div>
                                            
                                            {{-- Period Number --}}
                                            <div class="text-xs text-gray-400 mb-1">P{{ $period->period_number }}</div>
                                            
                                            {{-- Subject --}}
                                            <div class="font-medium text-sm truncate {{ $isActive ? 'text-green-700 dark:text-green-300' : ($isPast ? 'text-gray-400' : 'text-gray-700 dark:text-gray-300') }}">
                                                @if($period->is_break)
                                                    <i class="fas fa-coffee text-amber-500"></i>
                                                @else
                                                    {{ Str::limit($period->subject->name ?? '-', 10) }}
                                                @endif
                                            </div>
                                            
                                            {{-- Time --}}
                                            <div class="text-[10px] text-gray-400 mt-1">{{ $startTime }}</div>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-400 italic text-center py-4">{{ __('No schedule for this day') }}</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                        <i class="fas fa-school text-4xl text-gray-300 dark:text-gray-600 mb-4"></i>
                        <p class="text-gray-500 dark:text-gray-400">{{ __('No classes found') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Auto-refresh every 60 seconds
        setInterval(function() {
            fetch('{{ route("ongoing-class.api.data") }}?grade_id={{ $gradeId }}')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('ongoing-count').textContent = data.total_ongoing;
                });
        }, 60000);
    </script>
    @endpush
</x-app-layout>