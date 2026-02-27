<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-gradient-to-br from-pink-500 to-rose-600 flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-bullhorn"></i>
            </div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('Events & Announcements') }}</h1>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-8">
        <!-- Desktop View -->
        <div class="hidden md:block space-y-8">
            <!-- Announcements Section -->
            <section>
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <span class="w-1.5 h-8 bg-pink-500 rounded-full"></span>
                        {{ __('navigation.Announcements') }}
                    </h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @forelse($announcements as $announcement)
                        <div
                            class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-pink-500/5 -mr-16 -mt-16 rounded-full"></div>
                            <div class="flex gap-4 mb-4">
                                <div
                                    class="w-12 h-12 rounded-xl bg-pink-50 dark:bg-pink-900/30 flex items-center justify-center text-pink-600 dark:text-pink-400 flex-shrink-0">
                                    <i class="fas fa-bell"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 dark:text-white">{{ $announcement->title }}</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $announcement->created_at->format('M d, Y • h:i A') }}
                                    </p>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-3 mb-4">
                                {{ $announcement->content }}
                            </p>
                            <a href="{{ route('announcements.show', $announcement) }}"
                                class="text-sm font-bold text-pink-600 dark:text-pink-400 hover:underline">Read more</a>
                        </div>
                    @empty
                        <p class="col-span-full text-center py-10 text-gray-500 italic">No announcements at the moment.</p>
                    @endforelse
                </div>
                <div class="mt-4">
                    {{ $announcements->links() }}
                </div>
            </section>

            <!-- Events Section -->
            <section>
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                        <span class="w-1.5 h-8 bg-blue-500 rounded-full"></span>
                        {{ __('Upcoming Events') }}
                    </h2>
                </div>
                <div class="space-y-4">
                    @forelse($events as $event)
                        <div
                            class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden flex flex-col md:flex-row divide-y md:divide-y-0 md:divide-x divide-gray-100 dark:divide-gray-700">
                            <div
                                class="p-6 md:w-48 bg-gray-50/50 dark:bg-gray-900/50 flex flex-col items-center justify-center">
                                <span
                                    class="text-sm font-bold text-blue-600 dark:text-blue-400 uppercase tracking-widest mb-1">{{ $event->start_date->format('M') }}</span>
                                <span
                                    class="text-4xl font-black text-gray-900 dark:text-white">{{ $event->start_date->format('d') }}</span>
                                <span
                                    class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $event->start_date->format('Y') }}</span>
                            </div>
                            <div class="p-6 flex-grow flex flex-col md:flex-row justify-between items-center gap-6">
                                <div class="space-y-2 text-center md:text-left">
                                    <span
                                        class="px-2 py-0.5 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded text-[10px] font-bold uppercase">{{ $event->category->name ?? 'General' }}</span>
                                    <h4 class="text-xl font-bold text-gray-900 dark:text-white">{{ $event->title }}</h4>
                                    <div
                                        class="flex flex-wrap justify-center md:justify-start gap-4 text-sm text-gray-500 dark:text-gray-400">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-clock text-blue-400"></i>
                                            {{ $event->start_date->format('h:i A') }}
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-map-marker-alt text-blue-400"></i>
                                            {{ $event->location ?? 'School Campus' }}
                                        </div>
                                    </div>
                                </div>
                                <a href="{{ route('events.show', $event) }}"
                                    class="px-6 py-2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-xl font-bold hover:opacity-90 transition-opacity">
                                    View Details
                                </a>
                            </div>
                        </div>
                    @empty
                        <p class="text-center py-10 text-gray-500 italic">No upcoming events scheduled.</p>
                    @endforelse
                </div>
                <div class="mt-4">
                    {{ $events->links() }}
                </div>
            </section>
        </div>

        <!-- Mobile View (New) -->
        <div class="md:hidden" x-data="{ activeTab: 'announcements' }">
            <!-- Tabs -->
            <div class="grid grid-cols-2 gap-1 p-1 mb-4 bg-gray-100 rounded-xl dark:bg-gray-800">
                <button @click="activeTab = 'announcements'"
                    :class="activeTab === 'announcements' ? 'bg-white text-gray-900 shadow dark:bg-gray-700 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                    class="py-2 text-sm font-semibold rounded-lg transition-all duration-200">
                    {{ __('announcements.Announcements') }}
                </button>
                <button @click="activeTab = 'events'"
                    :class="activeTab === 'events' ? 'bg-white text-gray-900 shadow dark:bg-gray-700 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                    class="py-2 text-sm font-semibold rounded-lg transition-all duration-200">
                    {{ __('announcements.Events') }}
                </button>
            </div>

            <!-- Announcements Tab Content -->
            <div x-show="activeTab === 'announcements'" class="space-y-3">
                @forelse($announcements as $announcement)
                    <div
                        class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
                        <a href="{{ route('announcements.show', $announcement) }}" class="block">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-semibold text-gray-900 dark:text-white line-clamp-2">
                                    {{ $announcement->title }}</h4>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-3">
                                {{ $announcement->content }}</p>
                            <div class="flex items-center text-xs text-gray-400">
                                <span>{{ $announcement->created_at->format('M d, Y') }}</span>
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                        <p class="text-sm">{{ __('announcements.No announcements found') }}</p>
                    </div>
                @endforelse
            </div>

            <!-- Events Tab Content -->
            <div x-show="activeTab === 'events'" class="space-y-3" style="display: none;">
                @forelse($events as $event)
                    <div
                        class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
                        <div class="flex gap-4">
                            <!-- Date Box -->
                            <div
                                class="flex-shrink-0 w-12 h-12 bg-gray-50 dark:bg-gray-700 rounded-lg flex flex-col items-center justify-center text-gray-600 dark:text-gray-300">
                                <span class="text-xs font-bold uppercase">{{ $event->start_date->format('M') }}</span>
                                <span class="text-base font-bold">{{ $event->start_date->format('d') }}</span>
                            </div>

                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold text-gray-900 dark:text-white truncate mb-1">{{ $event->title }}
                                </h4>
                                <div class="flex flex-col text-xs text-gray-500 dark:text-gray-400 gap-0.5">
                                    <span>
                                        {{ \Carbon\Carbon::parse($event->start_time)->format('g:i A') }} -
                                        {{ \Carbon\Carbon::parse($event->end_time)->format('g:i A') }}
                                    </span>
                                    <span class="truncate">{{ $event->location ?? 'School Campus' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                        <p class="text-sm">No upcoming events.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>