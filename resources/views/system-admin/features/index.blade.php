<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 text-white shadow-lg">
                    <i class="fas fa-toggle-on"></i>
                </span>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">System Administration</p>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Feature Flag Management</h2>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <x-alert-success>{{ session('success') }}</x-alert-success>
            @endif

            <!-- Feature Flags Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">School Features</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Enable or disable features for this school</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('system-admin.features.update') }}">
                        @csrf
                        
                        <div class="space-y-4">
                            @foreach($availableFeatures as $key => $label)
                                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                    <div class="flex items-center gap-3">
                                        <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-white dark:bg-gray-800 shadow-sm">
                                            <i class="fas fa-{{ $this->getFeatureIcon($key) }} text-gray-600 dark:text-gray-400"></i>
                                        </div>
                                        <div>
                                            <label for="feature_{{ $key }}" class="text-sm font-medium text-gray-900 dark:text-white cursor-pointer">
                                                {{ $label }}
                                            </label>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $key }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center">
                                        <input 
                                            type="checkbox" 
                                            id="feature_{{ $key }}" 
                                            name="features[]" 
                                            value="{{ $key }}"
                                            {{ in_array($key, $enabledFeatures) ? 'checked' : '' }}
                                            class="w-5 h-5 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                        >
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6 flex items-center justify-end gap-3">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                <i class="fas fa-save mr-2"></i>
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Info Card -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex gap-3">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-1">About Feature Flags</h4>
                        <p class="text-sm text-blue-800 dark:text-blue-200">
                            Feature flags allow you to enable or disable specific features for this school. 
                            Disabled features will not appear in the navigation menu and will be inaccessible to users.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
    function getFeatureIcon($key) {
        return match($key) {
            'announcements' => 'bullhorn',
            'attendance' => 'clipboard-check',
            'timetable' => 'calendar-alt',
            'exams' => 'graduation-cap',
            'homework' => 'book',
            'fees' => 'dollar-sign',
            'payroll' => 'money-check-alt',
            'reports' => 'chart-bar',
            'events' => 'calendar-day',
            'leave_requests' => 'user-clock',
            'daily_reports' => 'file-alt',
            'curriculum' => 'book-open',
            'rules' => 'gavel',
            default => 'cog'
        };
    }
    @endphp
</x-app-layout>
