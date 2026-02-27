<x-app-layout>
    <div class="py-6" x-data="{ activeTab: 'announcements' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Tabs Navigation -->
            <div class="flex space-x-1 rounded-xl bg-gray-900/5 p-1 dark:bg-gray-700/50 mb-6">
                <button @click="activeTab = 'announcements'"
                    :class="activeTab === 'announcements' 
                        ? 'bg-white shadow text-gray-900 dark:bg-gray-600 dark:text-white' 
                        : 'text-gray-600 hover:bg-white/[0.12] hover:text-gray-800 dark:text-gray-300 dark:hover:text-white'"
                    class="w-full rounded-lg py-2.5 text-sm font-medium leading-5 ring-white ring-opacity-60 ring-offset-2 ring-offset-blue-400 focus:outline-none focus:ring-2 transition-all duration-200">
                    {{ __('announcements.Announcements') }}
                </button>
                <button @click="activeTab = 'events'"
                    :class="activeTab === 'events' 
                        ? 'bg-white shadow text-gray-900 dark:bg-gray-600 dark:text-white' 
                        : 'text-gray-600 hover:bg-white/[0.12] hover:text-gray-800 dark:text-gray-300 dark:hover:text-white'"
                    class="w-full rounded-lg py-2.5 text-sm font-medium leading-5 ring-white ring-opacity-60 ring-offset-2 ring-offset-blue-400 focus:outline-none focus:ring-2 transition-all duration-200">
                    {{ __('announcements.Events') }}
                </button>
            </div>

            <!-- Announcements Content -->
            <div x-show="activeTab === 'announcements'" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                class="space-y-4">

                @forelse ($announcements as $announcement)
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg border border-gray-100 dark:border-gray-700 relative">
                        <!-- Priority Indicator -->
                        @if($announcement->priority === 'urgent')
                            <div class="absolute top-0 right-0 w-3 h-3 bg-red-500 rounded-bl-lg"></div>
                        @elseif($announcement->priority === 'high')
                            <div class="absolute top-0 right-0 w-3 h-3 bg-orange-400 rounded-bl-lg"></div>
                        @endif

                        <div class="p-5">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex items-center gap-2">
                                    @if($announcement->announcementType)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                            style="background-color: {{ $announcement->announcementType->color }}20; color: {{ $announcement->announcementType->color }}">
                                            {{ $announcement->announcementType->name }}
                                        </span>
                                    @endif
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $announcement->publish_date ? $announcement->publish_date->diffForHumans() : __('Just now') }}
                                    </span>
                                </div>
                            </div>

                            <a href="{{ route('announcements.show', $announcement) }}" class="block group">
                                <h3
                                    class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                    {{ $announcement->title }}
                                </h3>
                                <p class="text-gray-600 dark:text-gray-300 text-sm line-clamp-3 mb-4">
                                    {{ Str::limit($announcement->content, 150) }}
                                </p>
                            </a>

                            <div
                                class="flex items-center justify-between mt-4 text-xs text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-gray-700 pt-3">
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-user-circle text-gray-400"></i>
                                    <span>{{ $announcement->creator->name ?? __('School Admin') }}</span>
                                </div>
                                <a href="{{ route('announcements.show', $announcement) }}"
                                    class="text-blue-600 dark:text-blue-400 font-medium hover:underline">
                                    {{ __('Read More') }} <i class="fas fa-arrow-right ml-1 text-[10px]"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div
                        class="flex flex-col items-center justify-center py-12 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-dashed border-gray-300 dark:border-gray-700">
                        <div
                            class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-bullhorn text-2xl text-gray-400 dark:text-gray-500"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('No announcements yet') }}
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1 text-center max-w-sm">
                            {{ __('Stay tuned! Important school updates and news will appear here.') }}
                        </p>
                    </div>
                @endforelse

                <div class="mt-4">
                    {{ $announcements->links() }}
                </div>
            </div>

            <!-- Events Content -->
            <div x-show="activeTab === 'events'" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                style="display: none;" class="space-y-4">

                @forelse ($events as $event)
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg border border-gray-100 dark:border-gray-700 flex">
                        <!-- Date Column -->
                        <div
                            class="w-20 bg-blue-50 dark:bg-blue-900/20 flex flex-col items-center justify-center p-2 text-center border-r border-gray-100 dark:border-gray-700 flex-shrink-0">
                            <span class="text-xs uppercase font-bold text-blue-600 dark:text-blue-400 tracking-wider">
                                {{ $event->start_date->format('M') }}
                            </span>
                            <span class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                {{ $event->start_date->format('d') }}
                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $event->start_date->format('D') }}
                            </span>
                        </div>

                        <!-- Content Column -->
                        <div class="flex-1 p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    @if($event->category)
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium uppercase tracking-wide mb-1"
                                            style="background-color: {{ $event->category->color }}20; color: {{ $event->category->color }}">
                                            {{ $event->category->name }}
                                        </span>
                                    @endif
                                    <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 mb-1">
                                        {{ $event->title }}
                                    </h3>
                                </div>
                            </div>

                            <div class="flex flex-col gap-1 mt-2 text-sm text-gray-600 dark:text-gray-400">
                                <div class="flex items-center gap-2">
                                    <i class="far fa-clock text-gray-400 w-4 text-center"></i>
                                    <span>
                                        {{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }}
                                        @if($event->end_time)
                                            - {{ \Carbon\Carbon::parse($event->end_time)->format('h:i A') }}
                                        @endif
                                    </span>
                                </div>
                                @if($event->venue)
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-map-marker-alt text-gray-400 w-4 text-center"></i>
                                        <span>{{ $event->venue }}</span>
                                    </div>
                                @endif
                                @if($event->end_date && $event->end_date->ne($event->start_date))
                                    <div class="flex items-center gap-2">
                                        <i class="far fa-calendar-alt text-gray-400 w-4 text-center"></i>
                                        <span>Ends: {{ $event->end_date->format('M d, Y') }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                                <a href="{{ route('events.show', $event) }}"
                                    class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300">
                                    {{ __('View Details') }} &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div
                        class="flex flex-col items-center justify-center py-12 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-dashed border-gray-300 dark:border-gray-700">
                        <div
                            class="w-16 h-16 bg-blue-50 dark:bg-blue-900/20 rounded-full flex items-center justify-center mb-4">
                            <i class="far fa-calendar-alt text-2xl text-blue-400 dark:text-blue-500"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('No upcoming events') }}</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1 text-center max-w-sm">
                            {{ __('There are no upcoming events scheduled at the moment.') }}
                        </p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>