<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-500 to-red-600 flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-tasks"></i>
            </div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('Homework Portal') }}</h1>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($homework as $hw)
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden flex flex-col h-full">
                    <div class="p-6 flex-grow">
                        <div class="flex justify-between items-start mb-4">
                            <span
                                class="px-2.5 py-1 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-lg text-xs font-bold uppercase transition-colors">
                                {{ $hw->subject->name }}
                            </span>
                            <span
                                class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold uppercase {{ $hw->priority === 'high' ? 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300' : ($hw->priority === 'medium' ? 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300' : 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300') }}">
                                <span
                                    class="w-1.5 h-1.5 rounded-full {{ $hw->priority === 'high' ? 'bg-red-500' : ($hw->priority === 'medium' ? 'bg-orange-500' : 'bg-blue-500') }}"></span>
                                {{ $hw->priority }}
                            </span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2 line-clamp-1">{{ $hw->title }}</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm line-clamp-3 mb-4">
                            {{ $hw->description ?? 'No detailed description provided.' }}</p>

                        <div class="space-y-3">
                            <div class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-300">
                                <i class="fas fa-user-edit w-5 text-gray-400"></i>
                                <span>{{ $hw->teacher->user->name }}</span>
                            </div>
                            <div class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-300">
                                <i class="fas fa-calendar-alt w-5 text-gray-400"></i>
                                <span>Due: {{ $hw->due_date->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="p-6 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 mt-auto">
                        <button
                            class="w-full py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl text-sm font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-sm">
                            View Details
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-20 text-center">
                    <div
                        class="w-20 h-20 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-check-double text-3xl text-emerald-500"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-1">All Caught Up!</h3>
                    <p class="text-gray-500 dark:text-gray-400">No active homework assignments found for your class.</p>
                </div>
            @endforelse
        </div>
        @if($homework->hasPages())
            <div class="mt-6">
                {{ $homework->links() }}
            </div>
        @endif
    </div>
</x-app-layout>