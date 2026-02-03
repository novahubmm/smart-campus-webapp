@php
    $categoryColors = [
        'academic' => '#4285f4',
        'sports' => '#34a853',
        'cultural' => '#fbbc04',
        'meeting' => '#ea4335',
        'holiday' => '#9e9e9e',
        'exam' => '#9c27b0',
        'other' => '#607d8b',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-lg">
                    <i class="fas fa-calendar-check"></i>
                </span>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('events.Events') }}</p>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('events.Event Management') }}</h2>
                </div>
            </div>
           
        </div>
    </x-slot>

    <div class="py-6 sm:py-10 overflow-x-hidden" x-data="eventManager()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <x-stat-card 
                    icon="fas fa-calendar-alt"
                    :title="__('events.Total Events')"
                    :number="$stats['total'] ?? 0"
                    :subtitle="__('events.All events')"
                />
                <x-stat-card 
                    icon="fas fa-clock"
                    :title="__('events.Upcoming')"
                    :number="$stats['upcoming'] ?? 0"
                    :subtitle="__('events.Scheduled')"
                />
                <x-stat-card 
                    icon="fas fa-bolt"
                    :title="__('events.Active')"
                    :number="$stats['active'] ?? 0"
                    :subtitle="__('events.In progress')"
                />
                <x-stat-card 
                    icon="fas fa-check-circle"
                    :title="__('events.Completed')"
                    :number="$stats['completed'] ?? 0"
                    :subtitle="__('events.Finished')"
                />
            </div>

            <!-- Events List Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('events.All Events') }}</h3>
                     <button type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg text-white bg-indigo-600 hover:bg-indigo-700" onclick="openCreateEventModal()">
                <i class="fas fa-plus"></i>{{ __('events.Add Event') }}
            </button>
            
                </div>

                <!-- Filters -->
                <form method="GET" action="{{ route('events.index') }}" class="p-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ __('events.Month') }}</label>
                            <input type="month" name="month" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ $filter->month ?? now()->format('Y-m') }}">
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ __('events.Category') }}</label>
                            <select name="category_id" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">{{ __('events.All Categories') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" @selected($filter->category_id === $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ __('events.Status') }}</label>
                            <select name="status" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="all">{{ __('events.All Status') }}</option>
                                <option value="active" @selected($filter->status === 'active')>{{ __('events.Active') }}</option>
                                <option value="inactive" @selected($filter->status === 'inactive')>{{ __('events.Inactive') }}</option>
                                <option value="upcoming" @selected($filter->status === 'upcoming')>{{ __('events.Upcoming') }}</option>
                                <option value="completed" @selected($filter->status === 'completed')>{{ __('events.Completed') }}</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ __('events.Period') }}</label>
                            <select name="period" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="all">{{ __('events.All Time') }}</option>
                                <option value="today" @selected($filter->period === 'today')>{{ __('events.Today') }}</option>
                                <option value="this_week" @selected($filter->period === 'this_week')>{{ __('events.This Week') }}</option>
                                <option value="this_month" @selected($filter->period === 'this_month')>{{ __('events.This Month') }}</option>
                                <option value="next_month" @selected($filter->period === 'next_month')>{{ __('events.Next Month') }}</option>
                                <option value="this_year" @selected($filter->period === 'this_year')>{{ __('events.This Year') }}</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="flex-1 px-3 py-2 text-sm font-semibold rounded-lg text-white bg-gray-800 dark:bg-gray-700 hover:bg-gray-900 dark:hover:bg-gray-600">{{ __('events.Apply') }}</button>
                            <a href="{{ route('events.index') }}" class="px-3 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">{{ __('events.Reset') }}</a>
                        </div>
                    </div>
                </form>

                <!-- Events Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('events.Event Title') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('events.Category') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('events.Date & Time') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('events.Location') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('events.Status') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">{{ __('events.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($events as $event)
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $event->title }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ ucfirst($event->type) }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        @php $catColor = $event->category?->color ?? ($categoryColors[$event->type] ?? '#6b7280'); @endphp
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold" style="background: {{ $catColor }}22; color: {{ $catColor }};">
                                            @if($event->category?->icon)<i class="{{ $event->category->icon }}"></i>@endif
                                            {{ $event->category?->name ?? ucfirst($event->type) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="text-sm text-gray-900 dark:text-white">{{ $event->start_date?->format('M j, Y') }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">@if($event->start_time){{ $event->start_time }}@endif @if($event->end_time) - {{ $event->end_time }}@endif</p>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $event->venue ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        @php $isUpcoming = $event->start_date && $event->start_date->isFuture(); $isCompleted = $event->end_date && $event->end_date->isPast(); @endphp
                                        @if(!$event->status)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">{{ __('events.Inactive') }}</span>
                                        @elseif($isCompleted)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-100">{{ __('events.Completed') }}</span>
                                        @elseif($isUpcoming)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-100">{{ __('events.Upcoming') }}</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-100">{{ __('events.Active') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <button type="button" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-blue-500 flex items-center justify-center hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30" title="{{ __('events.Edit') }}" @click="openEditModal(@js($event))">
                                                <i class="fas fa-pen text-xs"></i>
                                            </button>
                                            <button type="button" class="w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 text-red-500 flex items-center justify-center hover:border-red-400 hover:bg-red-50 dark:hover:bg-red-900/30" title="{{ __('events.Delete') }}" @click="submitDelete('{{ $event->id }}')">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('events.No events found') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Event Categories Filter Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-4">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('events.Event Categories') }}</h3>
                    <a href="{{ route('event-categories.index') }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">
                        {{ __('events.View All') }} <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <div class="flex flex-wrap gap-2">
                    @forelse($categories as $category)
                        <button type="button" 
                            class="category-filter-tag inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-medium border-2 transition-all"
                            :class="activeCategories.includes('{{ $category->id }}') ? 'bg-white dark:bg-gray-700 border-current' : 'bg-gray-100 dark:bg-gray-800 border-gray-200 dark:border-gray-600 text-gray-500 dark:text-gray-400'"
                            style="--cat-color: {{ $category->color ?? '#6b7280' }};"
                            @click="toggleCategory('{{ $category->id }}')"
                        >
                            <span class="w-3 h-3 rounded-full flex-shrink-0" style="background: {{ $category->color ?? '#6b7280' }};"></span>
                            <span :style="activeCategories.includes('{{ $category->id }}') ? 'color: {{ $category->color ?? '#6b7280' }}' : ''">{{ $category->name }}</span>
                            <i class="fas fa-check text-xs text-green-500" x-show="activeCategories.includes('{{ $category->id }}')"></i>
                        </button>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('events.No categories found.') }} <a href="{{ route('event-categories.index') }}" class="text-indigo-600 hover:underline">{{ __('events.Add one') }}</a></p>
                    @endforelse
                </div>
            </div>

            <!-- Calendar Section -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-2">
                        <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700" @click="prevPeriod()"><i class="fas fa-chevron-left"></i></button>
                        <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700" @click="nextPeriod()"><i class="fas fa-chevron-right"></i></button>
                        <span class="text-base font-semibold text-gray-900 dark:text-white ml-2" x-text="periodLabel"></span>
                    </div>
                    <div class="flex items-center gap-1 bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                        <button type="button" class="px-3 py-1.5 text-sm font-medium rounded-md transition-all" :class="calendarView === 'day' ? 'bg-white dark:bg-gray-600 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-600 dark:text-gray-300'" @click="calendarView = 'day'">{{ __('events.Day') }}</button>
                        <button type="button" class="px-3 py-1.5 text-sm font-medium rounded-md transition-all" :class="calendarView === 'week' ? 'bg-white dark:bg-gray-600 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-600 dark:text-gray-300'" @click="calendarView = 'week'">{{ __('events.Week') }}</button>
                        <button type="button" class="px-3 py-1.5 text-sm font-medium rounded-md transition-all" :class="calendarView === 'month' ? 'bg-white dark:bg-gray-600 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-600 dark:text-gray-300'" @click="calendarView = 'month'">{{ __('events.Month') }}</button>
                    </div>
                </div>

                <!-- Month View -->
                <div class="p-4" x-show="calendarView === 'month'">
                    <div class="grid grid-cols-7 gap-px bg-gray-200 dark:bg-gray-700 border border-gray-200 dark:border-gray-700 mb-px">
                        @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                            <div class="bg-gray-50 dark:bg-gray-800 px-2 py-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-400">{{ __($day) }}</div>
                        @endforeach
                    </div>
                    <div class="grid grid-cols-7 gap-px bg-gray-200 dark:bg-gray-700 border border-gray-200 dark:border-gray-700" style="min-height: 400px;">
                        <template x-for="day in calendarDays" :key="day.date">
                            <div class="bg-white dark:bg-gray-800 min-h-[80px] p-1 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-750" :class="{ 'bg-indigo-50 dark:bg-indigo-900/20': day.isToday, 'opacity-50': day.isOtherMonth }">
                                <div class="text-xs font-medium mb-1" :class="day.isToday ? 'w-6 h-6 rounded-full bg-indigo-600 text-white flex items-center justify-center' : 'text-gray-700 dark:text-gray-300'" x-text="day.dayNum"></div>
                                <div class="space-y-0.5">
                                    <template x-for="event in day.events.slice(0, 3)" :key="event.id">
                                        <div class="text-xs px-1 py-0.5 rounded truncate text-white cursor-pointer" :style="'background:' + (event.color || '#6b7280')" :title="event.title" x-text="event.title"></div>
                                    </template>
                                    <template x-if="day.events.length > 3">
                                        <div class="text-xs text-gray-500 dark:text-gray-400 px-1" x-text="'+' + (day.events.length - 3) + ' more'"></div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Week View -->
                <div class="p-4" x-show="calendarView === 'week'">
                    <!-- Multi-day events bar -->
                    <div class="mb-2 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden" x-show="getMultiDayEventsForWeek().length > 0">
                        <div class="grid grid-cols-7 gap-px bg-gray-200 dark:bg-gray-700">
                            <template x-for="day in weekDays" :key="'md-' + day.date">
                                <div class="bg-white dark:bg-gray-800 p-1 min-h-[28px]">
                                    <template x-for="event in getMultiDayEventsForDay(day.date)" :key="'md-' + event.id + '-' + day.date">
                                        <div class="text-xs px-1.5 py-0.5 mb-0.5 text-white truncate cursor-pointer"
                                            :class="getMultiDayEventClasses(event, day.date)"
                                            :style="'background:' + (event.color || '#6b7280')"
                                            :title="event.title + ' (' + event.start_date + ' - ' + event.end_date + ')'"
                                            x-text="isEventStart(event, day.date) ? event.title : ''"></div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div class="flex border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        <div class="w-16 flex-shrink-0 bg-gray-50 dark:bg-gray-900/50 border-r border-gray-200 dark:border-gray-700">
                            <div class="h-12 border-b border-gray-200 dark:border-gray-700"></div>
                            <template x-for="hour in hours" :key="hour">
                                <div class="h-12 border-b border-gray-100 dark:border-gray-700 px-2 py-1 text-xs text-gray-500 dark:text-gray-400" x-text="hour + ':00'"></div>
                            </template>
                        </div>
                        <div class="flex-1 grid grid-cols-7">
                            <template x-for="day in weekDays" :key="day.date">
                                <div class="border-r border-gray-200 dark:border-gray-700 last:border-r-0">
                                    <div class="h-12 border-b border-gray-200 dark:border-gray-700 p-2 text-center" :class="{ 'bg-indigo-50 dark:bg-indigo-900/20': day.isToday }">
                                        <div class="text-xs text-gray-500 dark:text-gray-400" x-text="day.dayName"></div>
                                        <div class="text-sm font-semibold" :class="day.isToday ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-900 dark:text-white'" x-text="day.dayNum"></div>
                                    </div>
                                    <div class="relative" style="height: 288px;">
                                        <template x-for="hour in hours" :key="hour">
                                            <div class="h-12 border-b border-gray-100 dark:border-gray-700"></div>
                                        </template>
                                        <template x-for="event in getSingleDayEventsForDay(day.date)" :key="event.id">
                                            <div class="absolute left-1 right-1 px-1 py-0.5 rounded text-xs text-white truncate cursor-pointer" :style="'background:' + (event.color || '#6b7280') + '; top: ' + getEventTop(event) + 'px; height: 24px;'" :title="event.title" x-text="event.title"></div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Day View -->
                <div class="p-4" x-show="calendarView === 'day'">
                    <div class="text-center mb-4">
                        <div class="text-lg font-semibold text-gray-900 dark:text-white" x-text="currentDayLabel"></div>
                    </div>
                    <!-- Multi-day events for this day -->
                    <div class="mb-2 space-y-1" x-show="getMultiDayEventsForDay(formatDate(currentDate)).length > 0">
                        <template x-for="event in getMultiDayEventsForDay(formatDate(currentDate))" :key="'day-md-' + event.id">
                            <div class="px-3 py-2 rounded-lg text-sm text-white cursor-pointer" :style="'background:' + (event.color || '#6b7280')" :title="event.title">
                                <div class="font-medium" x-text="event.title"></div>
                                <div class="text-xs opacity-75" x-text="event.start_date + ' → ' + event.end_date"></div>
                            </div>
                        </template>
                    </div>
                    <div class="flex border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        <div class="w-16 flex-shrink-0 bg-gray-50 dark:bg-gray-900/50 border-r border-gray-200 dark:border-gray-700">
                            <template x-for="hour in hours" :key="hour">
                                <div class="h-12 border-b border-gray-100 dark:border-gray-700 px-2 py-1 text-xs text-gray-500 dark:text-gray-400" x-text="hour + ':00'"></div>
                            </template>
                        </div>
                        <div class="flex-1 relative" style="height: 288px;">
                            <template x-for="hour in hours" :key="hour">
                                <div class="h-12 border-b border-gray-100 dark:border-gray-700"></div>
                            </template>
                            <template x-for="event in getSingleDayEventsForDay(formatDate(currentDate))" :key="event.id">
                                <div class="absolute left-2 right-2 px-2 py-1 rounded text-sm text-white cursor-pointer" :style="'background:' + (event.color || '#6b7280') + '; top: ' + getEventTop(event) + 'px; min-height: 24px;'" :title="event.title">
                                    <div class="font-medium truncate" x-text="event.title"></div>
                                    <div class="text-xs opacity-75" x-text="event.start_time || 'All day'"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Create/Edit Event Modal -->
        <div x-show="modals.event" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeEventModal()">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white dark:bg-gray-800 rounded-xl w-full max-w-2xl shadow-2xl" @click.stop>
                    <form :action="eventAction" method="POST" x-ref="eventForm">
                        @csrf
                        <template x-if="eventMethod === 'PUT'"><input type="hidden" name="_method" value="PUT"></template>
                        <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-t-xl">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-lg"><i class="fas fa-calendar-plus"></i></span>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="eventMethod === 'PUT' ? '{{ __('events.Edit Event') }}' : '{{ __('events.Create New Event') }}'"></h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('events.Fill in the event details') }}</p>
                                </div>
                            </div>
                            <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700" @click="closeEventModal()"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="p-5 space-y-4 max-h-[60vh] overflow-y-auto">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('events.Event Title') }} <span class="text-red-500">*</span></label>
                                    <input type="text" name="title" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500" x-model="eventForm.title" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('events.Category') }} <span class="text-red-500">*</span></label>
                                    <select name="event_category_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500" x-model="eventForm.event_category_id" required>
                                        <option value="">{{ __('events.Select') }}</option>
                                        @foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('events.Start Date & Time') }} <span class="text-red-500">*</span></label>
                                    <div class="flex gap-2">
                                        <input type="date" name="start_date" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500" x-model="eventForm.start_date" required>
                                        <input type="time" name="start_time" class="w-28 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500" x-model="eventForm.start_time">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('events.End Date & Time') }}</label>
                                    <div class="flex gap-2">
                                        <input type="date" name="end_date" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500" x-model="eventForm.end_date">
                                        <input type="time" name="end_time" class="w-28 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500" x-model="eventForm.end_time">
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('events.Location') }}</label>
                                <input type="text" name="venue" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500" x-model="eventForm.venue" placeholder="{{ __('events.e.g., Main Hall, Room 101') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('events.Description') }}</label>
                                <textarea name="description" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500" x-model="eventForm.description"></textarea>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="hidden" name="status" value="0">
                                <input type="checkbox" id="eventStatus" name="status" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" x-model="eventForm.status">
                                <label for="eventStatus" class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('events.Active') }}</label>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 p-5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl">
                            <button type="button" class="px-4 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" @click="closeEventModal()">{{ __('events.Cancel') }}</button>
                            <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-indigo-600 hover:bg-indigo-700"><i class="fas fa-check mr-2"></i><span x-text="eventMethod === 'PUT' ? '{{ __('events.Update') }}' : '{{ __('events.Save') }}'"></span></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <script>
        function openCreateEventModal() { Alpine.store('eventManager')?.openEventModal(); }

        function eventManager() {
            return {
                modals: { event: false },
                eventMethod: 'POST', eventAction: '{{ route('events.store') }}',
                eventForm: { title: '', event_category_id: '', start_date: '', start_time: '', end_date: '', end_time: '', venue: '', description: '', status: true },
                calendarView: 'month',
                currentDate: new Date({{ $monthDate->year }}, {{ $monthDate->month - 1 }}, {{ now()->day }}),
                hours: [8, 9, 10, 11, 12, 13, 14, 15, 16, 17],
                activeCategories: @js($categories->pluck('id')->all()),
                allEvents: @js($events->map(fn($e) => ['id' => $e->id, 'title' => $e->title, 'start_date' => $e->start_date?->toDateString(), 'end_date' => $e->end_date?->toDateString(), 'start_time' => $e->start_time, 'color' => $e->category?->color ?? '#6b7280', 'type' => $e->type, 'category_id' => $e->event_category_id])->values()->all()),
                get events() { return this.allEvents.filter(e => this.activeCategories.includes(e.category_id)); },
                toggleCategory(id) { if (this.activeCategories.includes(id)) { this.activeCategories = this.activeCategories.filter(c => c !== id); } else { this.activeCategories.push(id); } },
                get periodLabel() {
                    if (this.calendarView === 'day') return this.currentDate.toLocaleDateString('default', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
                    if (this.calendarView === 'week') { const start = this.getWeekStart(this.currentDate); const end = new Date(start); end.setDate(end.getDate() + 6); return start.toLocaleDateString('default', { month: 'short', day: 'numeric' }) + ' - ' + end.toLocaleDateString('default', { month: 'short', day: 'numeric', year: 'numeric' }); }
                    return this.currentDate.toLocaleString('default', { month: 'long', year: 'numeric' });
                },
                get currentDayLabel() { return this.currentDate.toLocaleDateString('default', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' }); },
                get calendarDays() {
                    const year = this.currentDate.getFullYear(), month = this.currentDate.getMonth();
                    const firstDay = new Date(year, month, 1), lastDay = new Date(year, month + 1, 0);
                    const days = [], today = new Date(); today.setHours(0, 0, 0, 0);
                    for (let i = firstDay.getDay() - 1; i >= 0; i--) days.push(this.makeDayObj(new Date(year, month, -i), true, today));
                    for (let i = 1; i <= lastDay.getDate(); i++) days.push(this.makeDayObj(new Date(year, month, i), false, today));
                    const remaining = 42 - days.length;
                    for (let i = 1; i <= remaining; i++) days.push(this.makeDayObj(new Date(year, month + 1, i), true, today));
                    return days;
                },
                get weekDays() {
                    const start = this.getWeekStart(this.currentDate), days = [], today = new Date(); today.setHours(0, 0, 0, 0);
                    const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                    for (let i = 0; i < 7; i++) { const d = new Date(start); d.setDate(d.getDate() + i); const dateStr = this.formatDate(d); days.push({ date: dateStr, dayNum: d.getDate(), dayName: dayNames[d.getDay()], isToday: d.getTime() === today.getTime(), events: this.getEventsForDate(dateStr) }); }
                    return days;
                },
                get dayEvents() { const dateStr = this.formatDate(this.currentDate); return this.getEventsForDate(dateStr); },
                getWeekStart(date) { const d = new Date(date); d.setDate(d.getDate() - d.getDay()); return d; },
                formatDate(d) { return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0'); },
                getEventsForDate(dateStr) { return this.events.filter(e => { const start = e.start_date; const end = e.end_date || e.start_date; return dateStr >= start && dateStr <= end; }); },
                isMultiDayEvent(event) { return event.end_date && event.end_date !== event.start_date; },
                getMultiDayEventsForDay(dateStr) { return this.events.filter(e => this.isMultiDayEvent(e) && dateStr >= e.start_date && dateStr <= e.end_date); },
                getSingleDayEventsForDay(dateStr) { return this.events.filter(e => !this.isMultiDayEvent(e) && e.start_date === dateStr); },
                getMultiDayEventsForWeek() { const start = this.weekDays[0]?.date; const end = this.weekDays[6]?.date; return this.events.filter(e => this.isMultiDayEvent(e) && e.start_date <= end && e.end_date >= start); },
                isEventStart(event, dateStr) { return event.start_date === dateStr; },
                isEventEnd(event, dateStr) { return event.end_date === dateStr; },
                getMultiDayEventClasses(event, dateStr) { const isStart = this.isEventStart(event, dateStr); const isEnd = this.isEventEnd(event, dateStr); if (isStart && isEnd) return 'rounded'; if (isStart) return 'rounded-l'; if (isEnd) return 'rounded-r'; return ''; },
                makeDayObj(date, isOtherMonth, today) { const dateStr = this.formatDate(date); return { date: dateStr, dayNum: date.getDate(), isToday: date.getTime() === today.getTime(), isOtherMonth, events: this.getEventsForDate(dateStr) }; },
                getEventTop(event) { if (!event.start_time) return 0; const [h, m] = event.start_time.split(':').map(Number); return ((h - 8) * 48) + (m / 60 * 48); },
                prevPeriod() { if (this.calendarView === 'day') this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), this.currentDate.getDate() - 1); else if (this.calendarView === 'week') this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), this.currentDate.getDate() - 7); else this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() - 1, 1); },
                nextPeriod() { if (this.calendarView === 'day') this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), this.currentDate.getDate() + 1); else if (this.calendarView === 'week') this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), this.currentDate.getDate() + 7); else this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 1); },
                openEventModal() { this.modals.event = true; this.eventMethod = 'POST'; this.eventAction = '{{ route('events.store') }}'; this.eventForm = { title: '', event_category_id: '', start_date: '', start_time: '', end_date: '', end_time: '', venue: '', description: '', status: true }; },
                openEditModal(event) { this.modals.event = true; this.eventMethod = 'PUT'; this.eventAction = '{{ url('events') }}/' + event.id; this.eventForm = { title: event.title || '', event_category_id: event.event_category_id || '', start_date: event.start_date ? event.start_date.split('T')[0] : '', start_time: event.start_time || '', end_date: event.end_date ? event.end_date.split('T')[0] : '', end_time: event.end_time || '', venue: event.venue || '', description: event.description || '', status: event.status ? true : false }; },
                closeEventModal() { this.modals.event = false; },
                submitDelete(id) { 
                    window.dispatchEvent(new CustomEvent('confirm-show', {
                        detail: {
                            title: '{{ __('events.Delete Event') }}',
                            message: '{{ __('events.Are you sure you want to delete this event? This action cannot be undone.') }}',
                            confirmText: '{{ __('events.Delete') }}',
                            cancelText: '{{ __('events.Cancel') }}',
                            onConfirm: () => {
                                const form = document.createElement('form'); 
                                form.method = 'POST'; 
                                form.action = '{{ url('events') }}/' + id; 
                                form.innerHTML = `@csrf <input type="hidden" name="_method" value="DELETE">`; 
                                document.body.appendChild(form); 
                                form.submit();
                            }
                        }
                    }));
                },
                init() { Alpine.store('eventManager', this); }
            };
        }
    </script>
</x-app-layout>
