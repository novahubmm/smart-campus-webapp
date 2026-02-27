<x-app-layout>
    <div class="p-6 space-y-6">
        <!-- Back Button & Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <a href="{{ route('guardian.dashboard') }}" class="flex items-center text-gray-600 dark:text-gray-300">
                    <i class="fas fa-chevron-left mr-2"></i>
                    <span class="text-sm font-medium">{{ __('Back') }}</span>
                </a>
            </div>
            <h1 class="text-lg font-bold text-gray-800 dark:text-white">{{ __('ongoing_class.Class Details') }}</h1>
            <div class="text-gray-500 dark:text-gray-300 text-[10px] font-bold uppercase">
                {{ $selectedDate->format('D, M d Y') }}
            </div>
        </div>

        <!-- Period Info Card -->
        <div
            class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-3xl p-6 text-white shadow-lg overflow-hidden relative">
            <div class="relative z-10 flex items-center space-x-4">
                <div
                    class="w-16 h-16 rounded-2xl bg-white/20 flex items-center justify-center text-white text-2xl font-bold">
                    P{{ $period->period_number }}
                </div>
                <div class="flex-1">
                    <h2 class="text-xl font-bold text-white">
                        {{ $period->subject->name ?? ($period->is_break ? 'Break' : 'N/A') }}
                    </h2>
                    <p class="text-blue-100 text-sm">
                        <i class="fas fa-clock mr-1"></i> {{ $period->starts_at->format('H:i') }} -
                        {{ $period->ends_at->format('H:i') }}
                    </p>
                    <p class="text-blue-100 text-sm mt-1">
                        <i class="fas fa-user-tie mr-1"></i> {{ $period->teacher->user->name ?? 'N/A' }} • <i
                            class="fas fa-door-open ml-2 mr-1"></i> {{ $period->room->name ?? 'N/A' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="space-y-6">

            <!-- Attendance Card -->
            <div
                class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-green-100 text-green-600 flex items-center justify-center">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <h3 class="font-bold text-gray-800 dark:text-white">{{ __('ongoing_class.Attendance') }}</h3>
                    </div>
                    @if($attendance)
                        <span
                            class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ $attendance->status === 'present' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                            {{ $attendance->status }}
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-gray-100 text-gray-500">
                            {{ __('ongoing_class.Not Marked') }}
                        </span>
                    @endif
                </div>
                @if($attendance && $attendance->collect_time)
                    <p class="text-xs text-gray-500 dark:text-gray-300">
                        <i class="fas fa-clock mr-1"></i> {{ __('Marked at') }}
                        {{ $attendance->collect_time->format('H:i') }}
                    </p>
                @endif
            </div>

            <!-- Teacher Remarks -->
            <div
                class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center">
                        <i class="fas fa-comment-dots"></i>
                    </div>
                    <h3 class="font-bold text-gray-800 dark:text-white">{{ __('ongoing_class.Teacher Remarks') }}</h3>
                </div>

                @forelse($remarks as $remark)
                    <div
                        class="p-4 rounded-2xl border mb-3 {{ $remark->type === 'positive' ? 'bg-green-50 border-green-100' : 'bg-blue-50 border-blue-100' }}">
                        <div class="flex justify-between items-start mb-2">
                            <span
                                class="text-xs font-bold uppercase {{ $remark->type === 'positive' ? 'text-green-600' : 'text-blue-600' }}">
                                {{ $remark->type }}
                            </span>
                            <span class="text-[10px] text-gray-400">{{ $remark->created_at->format('H:i') }}</span>
                        </div>
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $remark->remark }}</p>
                        <div class="mt-2 text-[10px] font-medium text-gray-500 italic">
                            — {{ $remark->teacher->user->name ?? 'Teacher' }}
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4">
                        <p class="text-sm text-gray-400 dark:text-gray-300">{{ __('No remarks for this class session.') }}</p>
                    </div>
                @endforelse
            </div>

            <!-- Homework Assigned -->
            <div
                class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <h3 class="font-bold text-gray-800 dark:text-white">{{ __('ongoing_class.Homework Assigned') }}</h3>
                </div>

                @forelse($homework as $hw)
                    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-100 mb-3">
                        <h4 class="font-bold text-gray-800 text-sm mb-1">{{ $hw->title }}</h4>
                        <p class="text-xs text-amber-700 mb-2">{{ Str::limit($hw->description, 100) }}</p>
                        <div class="flex items-center justify-between text-[10px] font-medium text-amber-600 uppercase">
                            <span><i class="fas fa-calendar-alt mr-1"></i> {{ __('Due') }}:
                                {{ $hw->due_date->format('M d') }}</span>
                            @if($hw->priority === 'high')
                                <span class="text-red-500"><i
                                        class="fas fa-exclamation-circle mr-1"></i>{{ __('High Priority') }}</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4">
                        <p class="text-sm text-gray-400 dark:text-gray-300">{{ __('No homework assigned in this session.') }}</p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
    @endsection