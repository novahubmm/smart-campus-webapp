{{-- Class Quick View Modal Content --}}
<div>
    {{-- Header --}}
    <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between sticky top-0 bg-white dark:bg-gray-800">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                <span class="text-lg font-bold text-primary-600 dark:text-primary-400">{{ $class->grade->level ?? '-' }}</span>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $class->name }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $class->teacher->user->name ?? '-' }} • {{ $class->enrolledStudents()->count() }} students
                </p>
            </div>
        </div>
        <button onclick="closeClassModal()" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>

    {{-- Current Period --}}
    @if($currentPeriod)
        <div class="p-4 bg-green-50 dark:bg-green-900/20 border-b border-green-100 dark:border-green-800">
            <div class="flex items-center gap-3">
                <span class="px-2 py-1 text-xs font-medium bg-green-500 text-white rounded flex items-center gap-1">
                    <span class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span>
                    LIVE
                </span>
                @if($currentPeriod->is_break)
                    <span class="font-medium text-green-700 dark:text-green-300">
                        <i class="fas fa-coffee mr-1"></i>{{ __('Break Time') }}
                    </span>
                @else
                    <span class="font-medium text-green-700 dark:text-green-300">{{ $currentPeriod->subject->name ?? '-' }}</span>
                    <span class="text-sm text-green-600 dark:text-green-400">• {{ $currentPeriod->teacher->user->name ?? '-' }}</span>
                @endif
            </div>
        </div>
    @endif

    {{-- Today's Schedule --}}
    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
            <i class="fas fa-calendar-day mr-2"></i>{{ __("Today's Schedule") }}
        </h4>
        @if(count($periods) > 0)
            <div class="flex flex-wrap gap-2">
                @foreach($periods as $period)
                    @php
                        $startTime = \Carbon\Carbon::parse($period->starts_at)->format('H:i');
                        $endTime = \Carbon\Carbon::parse($period->ends_at)->format('H:i');
                        $isActive = $currentTime >= $startTime && $currentTime < $endTime;
                        $isPast = $currentTime >= $endTime;
                    @endphp
                    <div class="px-3 py-2 rounded-lg text-sm {{ $isActive ? 'bg-green-100 dark:bg-green-900/30 border border-green-300' : ($isPast ? 'bg-gray-100 dark:bg-gray-700' : 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600') }}">
                        <div class="font-medium {{ $isActive ? 'text-green-700 dark:text-green-300' : ($isPast ? 'text-gray-400' : 'text-gray-700 dark:text-gray-300') }}">
                            {{ $period->is_break ? 'Break' : ($period->subject->name ?? '-') }}
                        </div>
                        <div class="text-xs {{ $isPast ? 'text-gray-400' : 'text-gray-500' }}">{{ $startTime }}</div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-400 italic">{{ __('No schedule for today') }}</p>
        @endif
    </div>

    {{-- Curriculum Progress --}}
    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
            <i class="fas fa-book-open mr-2"></i>{{ __('Curriculum Progress') }}
        </h4>
        @if(count($curriculumData) > 0)
            <div class="space-y-3">
                @foreach($curriculumData as $data)
                    <div>
                        <div class="flex items-center justify-between text-sm mb-1">
                            <span class="text-gray-700 dark:text-gray-300">{{ $data['subject']->name }}</span>
                            <span class="text-gray-500">{{ $data['completed'] }}/{{ $data['total'] }}</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-primary-500 h-2 rounded-full transition-all" style="width: {{ $data['percent'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-400 italic">{{ __('No curriculum data') }}</p>
        @endif
    </div>

    {{-- Recent Homework --}}
    <div class="p-4">
        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
            <i class="fas fa-file-alt mr-2"></i>{{ __('homework.Recent Homework') }}
        </h4>
        @if($homework->count() > 0)
            <div class="space-y-2">
                @foreach($homework as $hw)
                    <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $hw->title }}</p>
                            <p class="text-xs text-gray-500">{{ $hw->subject->name ?? '-' }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs {{ $hw->isOverdue() ? 'text-red-500' : 'text-gray-400' }}">
                                {{ $hw->due_date->format('M d') }}
                            </span>
                            <span class="px-2 py-0.5 text-xs rounded-full {{ $hw->status === 'active' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-600 dark:text-gray-400' }}">
                                {{ __('homework.' . ucfirst($hw->status)) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-400 italic">{{ __('homework.No homework assigned') }}</p>
        @endif
    </div>

    {{-- Actions --}}
    <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
        <a href="{{ route('ongoing-class.class-detail', $class->id) }}" 
           class="block w-full text-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 text-sm">
            <i class="fas fa-external-link-alt mr-2"></i>{{ __('View Full Details') }}
        </a>
    </div>
</div>
