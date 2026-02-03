<x-app-layout>
    <x-slot name="header">
        <x-page-header 
            icon="fas fa-file-alt"
            iconBg="bg-blue-50 dark:bg-blue-900/30"
            iconColor="text-blue-700 dark:text-blue-200"
            :subtitle="__('homework.Homework')"
            :title="$homework->title"
        />
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            {{-- Back Link & Header --}}
            <div class="flex items-center gap-4">
                <a href="{{ route('homework.index') }}" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                    <i class="fas fa-arrow-left text-gray-600 dark:text-gray-400"></i>
                </a>
                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $homework->title }}</h1>
                    <p class="text-gray-500 dark:text-gray-400">{{ $homework->subject->name ?? '-' }} • @className($homework->schoolClass->name ?? '-', $homework->schoolClass?->grade?->level)</p>
                </div>
                <span class="px-3 py-1 text-sm rounded-full {{ $homework->status === 'active' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : ($homework->status === 'completed' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400') }}">
                    {{ __('homework.' . ucfirst($homework->status)) }}
                </span>
            </div>

            {{-- Homework Info --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">{{ __('homework.Teacher') }}</p>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $homework->teacher->user->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">{{ __('homework.Assigned Date') }}</p>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $homework->assigned_date->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">{{ __('homework.Due Date') }}</p>
                        <p class="font-semibold {{ $homework->isOverdue() ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">
                            {{ $homework->due_date->format('M d, Y') }}
                            @if($homework->isOverdue())
                                <span class="text-xs">({{ __('homework.Overdue') }})</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">{{ __('homework.Priority') }}</p>
                        @php
                            $priorityColors = [
                                'low' => 'text-green-600 dark:text-green-400',
                                'medium' => 'text-amber-600 dark:text-amber-400',
                                'high' => 'text-red-600 dark:text-red-400',
                            ];
                        @endphp
                        <p class="font-semibold {{ $priorityColors[$homework->priority] ?? '' }}">{{ __('homework.' . ucfirst($homework->priority)) }}</p>
                    </div>
                </div>

                @if($homework->description)
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">{{ __('homework.Description') }}</p>
                        <p class="text-gray-700 dark:text-gray-300">{{ $homework->description }}</p>
                    </div>
                @endif
            </div>

            {{-- Student Submissions --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('homework.Student Submissions') }}</h2>
                    <div class="flex items-center gap-4 text-sm">
                        <span class="text-gray-500 dark:text-gray-400">
                            {{ $submissionMap->whereIn('status', ['submitted', 'graded'])->count() }}/{{ $students->total() }} {{ __('homework.submitted') }}
                        </span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('homework.Student') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('homework.Submitted At') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('homework.Status') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('homework.Grade') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($students as $student)
                                @php
                                    $submission = $submissionMap->get($student->id);
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                                                <span class="text-sm font-medium text-primary-600 dark:text-primary-400">
                                                    {{ strtoupper(substr($student->user->name ?? 'S', 0, 1)) }}
                                                </span>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-white">{{ $student->user->name ?? '-' }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $student->student_identifier ?? '-' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($submission && $submission->submitted_at)
                                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                                {{ $submission->submitted_at->format('M d, Y H:i') }}
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($submission)
                                            @php
                                                $statusColors = [
                                                    'pending' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
                                                    'submitted' => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
                                                    'late' => 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300',
                                                    'graded' => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
                                                ];
                                            @endphp
                                            <span class="px-2 py-1 text-xs rounded-full {{ $statusColors[$submission->status] ?? '' }}">
                                                {{ __('homework.' . ucfirst($submission->status)) }}
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300">
                                                {{ __('homework.Pending') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($submission && $submission->grade !== null)
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $submission->grade }}%</span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($students->hasPages())
                    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $students->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
