<x-app-layout>
    <x-slot name="header">
        <x-page-header 
            icon="fas fa-clock"
            iconBg="bg-purple-50 dark:bg-purple-900/30"
            iconColor="text-purple-700 dark:text-purple-200"
            :subtitle="$class->name"
            :title="$period->subject->name ?? __('Break')"
        />
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            {{-- Back Link & Actions --}}
            <div class="flex items-center justify-between">
                <a href="{{ route('ongoing-class.class-detail', $class->id) }}" class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                    <i class="fas fa-arrow-left"></i>
                    <span>{{ __('Back to Class') }}</span>
                </a>
                <a href="{{ route('homework.index', ['class_id' => $class->id, 'subject_id' => $period->subject_id]) }}" 
                   class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                    <i class="fas fa-plus"></i>
                    {{ __('homework.Add Homework') }}
                </a>
            </div>

            {{-- Period Info --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">{{ __('Time') }}</p>
                        <p class="font-semibold text-gray-900 dark:text-white">
                            {{ \Carbon\Carbon::parse($period->starts_at)->format('H:i') }} - {{ \Carbon\Carbon::parse($period->ends_at)->format('H:i') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">{{ __('Teacher') }}</p>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $period->teacher->user->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">{{ __('Room') }}</p>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $period->room->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">{{ __('Day') }}</p>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ ucfirst($period->day_of_week) }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Curriculum Progress --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Curriculum Progress') }}</h2>
                    </div>
                    <div class="p-4 space-y-4 max-h-[600px] overflow-y-auto">
                        @forelse($chapters as $chapter)
                            @php
                                $totalTopics = $chapter->topics->count();
                                $completedTopics = $chapter->topics->filter(fn($t) => $t->progress->where('status', 'completed')->count() > 0)->count();
                                $progressPercent = $totalTopics > 0 ? round(($completedTopics / $totalTopics) * 100) : 0;
                            @endphp
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden" x-data="{ open: false }">
                                <div class="p-3 bg-gray-50 dark:bg-gray-700/50 flex items-center justify-between cursor-pointer" @click="open = !open">
                                    <div class="flex items-center gap-2">
                                        @if($progressPercent === 100)
                                            <i class="fas fa-check-circle text-green-500"></i>
                                        @elseif($progressPercent > 0)
                                            <i class="fas fa-spinner text-amber-500"></i>
                                        @else
                                            <i class="fas fa-clock text-gray-400"></i>
                                        @endif
                                        <span class="font-medium text-gray-900 dark:text-white">{{ $chapter->title }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $completedTopics }}/{{ $totalTopics }}</span>
                                        <i class="fas fa-chevron-down text-gray-400 transition-transform" :class="{ 'rotate-180': open }"></i>
                                    </div>
                                </div>
                                <div class="p-3 space-y-2" x-show="open" x-collapse>
                                    @foreach($chapter->topics as $topic)
                                        @php
                                            $isCompleted = $topic->progress->where('status', 'completed')->count() > 0;
                                            $isInProgress = $topic->progress->where('status', 'in_progress')->count() > 0;
                                        @endphp
                                        <div class="flex items-center gap-2 text-sm">
                                            @if($isCompleted)
                                                <i class="fas fa-check text-green-500 w-4"></i>
                                                <span class="text-gray-600 dark:text-gray-400 line-through">{{ $topic->title }}</span>
                                            @elseif($isInProgress)
                                                <i class="fas fa-arrow-right text-amber-500 w-4"></i>
                                                <span class="text-amber-600 dark:text-amber-400 font-medium">{{ $topic->title }}</span>
                                            @else
                                                <span class="w-4 h-4 border border-gray-300 dark:border-gray-600 rounded"></span>
                                                <span class="text-gray-700 dark:text-gray-300">{{ $topic->title }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <i class="fas fa-book-open text-4xl text-gray-400 mb-4"></i>
                                <p class="text-gray-500 dark:text-gray-400">{{ __('No curriculum defined for this subject') }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Homework History --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('homework.Homework History') }}</h2>
                    </div>
                    <div class="divide-y divide-gray-200 dark:divide-gray-700 max-h-[600px] overflow-y-auto">
                        @forelse($homework as $hw)
                            <a href="{{ route('homework.show', $hw->id) }}" class="block p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 mt-1">
                                        <div class="w-8 h-8 rounded-lg {{ $hw->isOverdue() ? 'bg-red-100 dark:bg-red-900/30' : ($hw->status === 'completed' ? 'bg-green-100 dark:bg-green-900/30' : 'bg-blue-100 dark:bg-blue-900/30') }} flex items-center justify-center">
                                            <i class="fas fa-file-alt text-sm {{ $hw->isOverdue() ? 'text-red-600' : ($hw->status === 'completed' ? 'text-green-600' : 'text-blue-600') }}"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $hw->title }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                            {{ __('homework.Assigned') }}: {{ $hw->assigned_date->format('M d, Y') }}
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ __('homework.Due') }}: {{ $hw->due_date->format('M d, Y') }}
                                        </p>
                                        <div class="flex items-center gap-2 mt-2">
                                            <span class="px-2 py-0.5 text-xs rounded-full {{ $hw->status === 'active' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' : ($hw->status === 'completed' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400') }}">
                                                {{ __('homework.' . ucfirst($hw->status)) }}
                                            </span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $hw->submission_count }} {{ __('homework.submissions') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="p-8 text-center">
                                <i class="fas fa-clipboard-list text-4xl text-gray-400 mb-4"></i>
                                <p class="text-gray-500 dark:text-gray-400">{{ __('homework.No homework assigned for this subject') }}</p>
                            </div>
                        @endforelse
                    </div>
                    @if($homework->hasPages())
                        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                            {{ $homework->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
