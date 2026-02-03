<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-lg">
                <i class="fas fa-bullhorn"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('announcements.Communications') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('announcements.Announcement Details') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="space-y-6">
            <!-- Back Button -->
            <div class="flex items-center gap-3">
                @if(request()->has('from') && request('from') === 'notification')
                    <a href="{{ route('staff.notifications.index') }}" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <i class="fas fa-arrow-left"></i>
                        {{ __('staff.Back to My Notifications') }}
                    </a>
                @elsecan('manage announcements')
                    <a href="{{ route('announcements.index') }}" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <i class="fas fa-arrow-left"></i>
                        {{ __('announcements.Back to Announcements') }}
                    </a>
                @else
                    <a href="{{ route('staff.notifications.index') }}" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <i class="fas fa-arrow-left"></i>
                        {{ __('staff.Back to My Notifications') }}
                    </a>
                @endcan
            </div>

            <!-- Main Content -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
                <!-- Header -->
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-3">
                                @if($announcement->announcementType)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-semibold" style="background-color: {{ $announcement->announcementType->color }}20; color: {{ $announcement->announcementType->color }}">
                                        <span class="w-5 h-5 flex-shrink-0">{!! $announcement->announcementType->icon !!}</span>
                                        {{ $announcement->announcementType->name }}
                                    </span>
                                @endif
                                
                                @php
                                    $priorityStyles = [
                                        'urgent' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                        'high' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
                                        'medium' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
                                        'low' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-sm font-semibold {{ $priorityStyles[$announcement->priority] ?? $priorityStyles['low'] }}">
                                    {{ ucfirst($announcement->priority) }} {{ __('announcements.Priority') }}
                                </span>

                                @if($announcement->is_published)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        {{ __('announcements.Published') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                        <i class="fas fa-file-alt mr-1"></i>
                                        {{ __('announcements.Draft') }}
                                    </span>
                                @endif
                            </div>
                            
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ $announcement->title }}</h1>
                            
                            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-user"></i>
                                    <span>{{ __('announcements.Created by') }} {{ $announcement->creator->name ?? __('announcements.Unknown') }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-calendar"></i>
                                    <span>{{ $announcement->created_at->format('M d, Y \a\t g:i A') }}</span>
                                </div>
                                @if($announcement->location)
                                    <div class="flex items-center gap-1">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>{{ $announcement->location }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Content -->
                <div class="p-6">
                    <div class="prose prose-gray dark:prose-invert max-w-none text-gray-900 dark:text-gray-100">
                        @if($announcement->content)
                            <div class="text-gray-900 dark:text-gray-100 leading-relaxed">
                                {!! nl2br(e($announcement->content)) !!}
                            </div>
                        @else
                            <p class="text-gray-500 dark:text-gray-400 italic">{{ __('announcements.No content available') }}</p>
                        @endif
                    </div>
                </div>

                <!-- Meta Information -->
                <div class="p-6 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Target Audience -->
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">{{ __('announcements.Target Audience') }}</h4>
                            <div class="flex flex-wrap gap-1">
                                @forelse($announcement->target_roles ?? [] as $role)
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                        {{ ucfirst($role) }}
                                    </span>
                                @empty
                                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('announcements.No specific audience') }}</span>
                                @endforelse
                            </div>
                        </div>

                        <!-- Target Grades -->
                        @php
                            $targetGrades = $announcement->target_grades ?? ['all'];
                            $targetDepts = $announcement->target_departments ?? ['all'];
                            $hasTeacherOrGuardian = collect($announcement->target_roles ?? [])->intersect(['teacher', 'guardian'])->isNotEmpty();
                            $hasStaff = in_array('staff', $announcement->target_roles ?? []);
                        @endphp
                        @if($hasTeacherOrGuardian)
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">
                                    <i class="fas fa-graduation-cap text-amber-500 mr-1"></i>{{ __('announcements.Target Grades') }}
                                </h4>
                                <div class="flex flex-wrap gap-1">
                                    @if(in_array('all', $targetGrades))
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                            {{ __('announcements.All Grades') }}
                                        </span>
                                    @else
                                        @foreach($grades ?? [] as $grade)
                                            @if(in_array($grade->id, $targetGrades))
                                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                                    {{ $grade->name }}
                                                </span>
                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Target Departments -->
                        @if($hasStaff)
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">
                                    <i class="fas fa-building text-amber-500 mr-1"></i>{{ __('announcements.Target Departments') }}
                                </h4>
                                <div class="flex flex-wrap gap-1">
                                    @if(in_array('all', $targetDepts))
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">
                                            {{ __('announcements.All Departments') }}
                                        </span>
                                    @else
                                        @foreach($departments ?? [] as $dept)
                                            @if(in_array($dept->id, $targetDepts))
                                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">
                                                    {{ $dept->name }}
                                                </span>
                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Publishing Info -->
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">{{ __('announcements.Publishing') }}</h4>
                            <div class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                @if($announcement->publish_date)
                                    <div>{{ __('announcements.Publish Date & Time') }}: {{ $announcement->publish_date->format('M d, Y H:i') }}</div>
                                @endif
                                @if(!$announcement->publish_date)
                                    <div>{{ __('announcements.No scheduling set') }}</div>
                                @endif
                            </div>
                        </div>

                        <!-- Event Link -->
                        @if($announcement->event)
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">{{ __('announcements.Linked Event') }}</h4>
                                <div class="text-sm">
                                    <a href="{{ route('events.show', $announcement->event) }}" class="text-amber-600 dark:text-amber-400 hover:text-amber-700 dark:hover:text-amber-300 font-medium">
                                        {{ $announcement->event->title }}
                                    </a>
                                    <div class="text-gray-500 dark:text-gray-400 text-xs mt-1">
                                        {{ $announcement->event->start_date->format('M d, Y') }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Last Updated -->
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">{{ __('announcements.Last Updated') }}</h4>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $announcement->updated_at->format('M d, Y \a\t g:i A') }}
                                @if($announcement->updated_at != $announcement->created_at)
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        ({{ $announcement->updated_at->diffForHumans() }})
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</x-app-layout>