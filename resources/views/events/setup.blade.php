<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-calendar-check"></i>
            </div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('events.Events Setup') }}</h1>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 overflow-x-hidden">
            <!-- Welcome Header -->
            <div class="mb-8 rounded-2xl p-8 shadow-lg bg-gradient-to-br from-amber-500 to-orange-600 dark:from-amber-600 dark:to-orange-700">
                <div class="flex flex-col sm:flex-row items-center gap-6">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center text-3xl text-white flex-shrink-0">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="flex-1 text-center sm:text-left">
                        <h2 class="text-2xl sm:text-3xl font-bold mb-2 text-white">{{ __('events.Welcome to Events Setup') }}</h2>
                        <p class="text-base sm:text-lg text-white/90">{{ __('events.Configure event categories, default settings, notifications, and approval workflows for your event management system.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Wizard Container -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                <!-- Wizard Progress Bar -->
                <div class="border-b border-gray-200 dark:border-gray-700 p-5">
                    <div class="flex justify-center">
                        <div class="wizard-step-item flex flex-col items-center" data-step="1">
                            <div class="step-indicator rounded-full w-10 h-10 flex items-center justify-center mb-2 bg-amber-500 text-white shadow-md">
                                <i class="fas fa-tags"></i>
                            </div>
                            <span class="step-label text-sm text-amber-600 dark:text-amber-400 font-semibold">{{ __('events.Event Categories') }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row">
                    <!-- Main Content -->
                    <div class="flex-1 p-6 lg:p-8">
                        <form method="POST" action="{{ route('event-announcement-setup.store') }}" id="eventsSetupForm">
                            @csrf
                            <!-- Step 1: Event Categories -->
                            <div class="wizard-step" id="setup-step-1">
                                <div class="flex items-center gap-2 px-3 py-2 bg-amber-50 dark:bg-amber-900/20 rounded-lg mb-6 w-fit">
                                    <i class="fas fa-tags text-amber-600 dark:text-amber-400 text-lg"></i>
                                    <h4 class="text-lg font-semibold text-amber-800 dark:text-amber-200">{{ __('events.Event Categories Configuration') }}</h4>
                                </div>

                                <div class="space-y-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('events.Default Event Categories') }}</label>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            @php
                                                $defaultCategories = [
                                                    'academic' => 'Academic',
                                                    'sports' => 'Sports',
                                                    'cultural' => 'Cultural',
                                                    'meeting' => 'Meeting',
                                                    'holiday' => 'Holiday',
                                                    'ceremony' => 'Ceremony',
                                                    'others' => 'Others',
                                                ];
                                                $existingCategories = old('event_categories', $eventCategories ?? []);
                                            @endphp
                                            @foreach($defaultCategories as $value => $label)
                                                <label class="flex items-center gap-3 cursor-pointer p-3 border-2 border-gray-200 dark:border-gray-700 rounded-lg transition-all hover:border-amber-400 dark:hover:border-amber-500 has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50 dark:has-[:checked]:bg-amber-900/20">
                                                    <input type="checkbox" name="event_categories[]" value="{{ $value }}" class="event-category-checkbox rounded text-amber-600 border-gray-300 focus:ring-amber-500"
                                                        {{ in_array($value, $existingCategories) ? 'checked' : '' }}>
                                                    <span class="text-gray-900 dark:text-gray-100">{{ $label }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div>
                                        <label for="customCategories" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('events.Custom Categories (comma-separated)') }}</label>
                                        <input type="text" name="custom_categories" id="customCategories"
                                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:border-amber-500 focus:ring-amber-500"
                                            placeholder="e.g., Workshop, Seminar, Competition"
                                            value="{{ old('custom_categories', $customCategories ?? '') }}">
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('events.Add additional event categories if needed') }}</p>
                                    </div>

                                    <div class="bg-amber-50 dark:bg-amber-900/20 border-l-4 border-amber-500 p-4 rounded-r-lg">
                                        <p class="text-amber-800 dark:text-amber-200 text-sm">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            {{ __('events.Select the default event categories that will be available when creating events. You can add custom categories as needed.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Sidebar with Tips -->
                    <div class="hidden lg:block w-72 flex-shrink-0 bg-gray-50 dark:bg-gray-900 border-l border-gray-200 dark:border-gray-700 p-6">
                        <div class="sticky top-6">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">{{ __('events.Setup Guide') }}</h3>
                            <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-500 font-bold">•</span>
                                    <span>{{ __('events.Complete each step to configure your event management system') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-500 font-bold">•</span>
                                    <span>{{ __('events.You can modify these settings later from the settings page') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-500 font-bold">•</span>
                                    <span>{{ __('events.All configurations can be edited after setup') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-amber-500 font-bold">•</span>
                                    <span>{{ __('events.Return to this page anytime to reconfigure') }}</span>
                                </li>
                            </ul>
                            <div class="mt-5 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-3 flex items-center gap-2 text-sm text-amber-700 dark:text-amber-300">
                                    <i class="fas fa-info-circle text-amber-500"></i>
                                    <span>{{ __('events.Need help? Contact the admin support team') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Wizard Footer -->
                <div class="border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-5 rounded-b-xl flex justify-end">
                    <button type="submit" form="eventsSetupForm" class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-lg shadow-md transition-all hover:shadow-lg">
                        <i class="fas fa-check"></i>
                        {{ __('events.Complete Setup') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
