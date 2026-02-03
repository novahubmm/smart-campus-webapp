<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg">
                <i class="fas fa-bell"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('staff.Staff Portal') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('staff.Notification Details') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-6">
        @php
            $data = $notification->data;
            $priority = $data['priority'] ?? 'medium';
            $priorityStyles = [
                'urgent' => 'border-l-red-500 bg-red-50 dark:bg-red-900/20',
                'high' => 'border-l-orange-500 bg-orange-50 dark:bg-orange-900/20',
                'medium' => 'border-l-amber-500 bg-amber-50 dark:bg-amber-900/20',
                'low' => 'border-l-green-500 bg-green-50 dark:bg-green-900/20',
            ];
            $priorityBadgeStyles = [
                'urgent' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                'high' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
                'medium' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
                'low' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
            ];
        @endphp

        <!-- Back Navigation -->
        <div class="flex items-center gap-3">
            @if(request()->has('from') && request('from') === 'announcement')
                <a href="{{ route('announcements.show', $data['announcement_id'] ?? '') }}" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <i class="fas fa-arrow-left"></i>
                    {{ __('staff.Back to Announcement') }}
                </a>
            @else
                <a href="{{ route('staff.notifications.index') }}" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <i class="fas fa-arrow-left"></i>
                    {{ __('staff.Back to My Notifications') }}
                </a>
            @endif
        </div>

        <!-- Notification Detail Card -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm border-l-4 {{ $priorityStyles[$priority] ?? $priorityStyles['medium'] }}">
            <div class="p-6">
                <!-- Header -->
                <div class="flex items-start justify-between gap-4 mb-6">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white">
                                <i class="fas fa-bullhorn"></i>
                            </div>
                        </div>
                        <div>
                            <h1 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                {{ $data['title'] ?? 'Notification' }}
                            </h1>
                            <div class="flex items-center gap-3 text-sm text-gray-500 dark:text-gray-400">
                                <span>{{ $notification->created_at->format('M d, Y \a\t g:i A') }}</span>
                                <span>•</span>
                                <span>{{ $notification->created_at->diffForHumans() }}</span>
                                <span>•</span>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $priorityBadgeStyles[$priority] ?? $priorityBadgeStyles['medium'] }}">
                                    {{ ucfirst($priority) }} Priority
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2">
                        @if(isset($data['announcement_id']))
                            <a href="{{ route('announcements.show', ['announcement' => $data['announcement_id'], 'from' => 'notification']) }}" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30">
                                <i class="fas fa-eye"></i>
                                {{ __('staff.View Announcement') }}
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Content -->
                <div class="prose prose-gray dark:prose-invert max-w-none">
                    <div class="text-gray-700 dark:text-gray-300 leading-relaxed">
                        {!! nl2br(e($data['message'] ?? 'No message content')) !!}
                    </div>
                </div>

                <!-- Additional Information -->
                @if(isset($data['additional_info']) && !empty($data['additional_info']))
                    <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">{{ __('staff.Additional Information') }}</h3>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            {!! nl2br(e($data['additional_info'])) !!}
                        </div>
                    </div>
                @endif

                <!-- Metadata -->
                <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-gray-900 dark:text-white">{{ __('staff.Notification ID') }}:</span>
                            <span class="text-gray-600 dark:text-gray-400 ml-2">{{ $notification->id }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-900 dark:text-white">{{ __('staff.Status') }}:</span>
                            <span class="text-gray-600 dark:text-gray-400 ml-2">
                                @if($notification->read_at)
                                    {{ __('staff.Read') }} ({{ $notification->read_at->format('M d, Y g:i A') }})
                                @else
                                    {{ __('staff.Unread') }}
                                @endif
                            </span>
                        </div>
                        @if(isset($data['type']))
                            <div>
                                <span class="font-medium text-gray-900 dark:text-white">{{ __('staff.Type') }}:</span>
                                <span class="text-gray-600 dark:text-gray-400 ml-2">{{ ucfirst($data['type']) }}</span>
                            </div>
                        @endif
                        @if(isset($data['category']))
                            <div>
                                <span class="font-medium text-gray-900 dark:text-white">{{ __('staff.Category') }}:</span>
                                <span class="text-gray-600 dark:text-gray-400 ml-2">{{ ucfirst($data['category']) }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>