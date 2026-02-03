<x-app-layout>
    <x-slot name="header">
        <x-page-header icon="fas fa-chalkboard-teacher" iconBg="bg-green-50 dark:bg-green-900/30" iconColor="text-green-700 dark:text-green-200" :subtitle="__('Report Centre')" :title="__('Teacher Reports')" />
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-back-link :href="route('reports.index')" :text="__('Back to Report Centre')" />

            <div class="mt-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Generate Teacher Report') }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('Select options below to generate report') }}</p>
                </div>

                <form action="{{ route('reports.teachers.generate') }}" method="POST" target="_blank">
                    @csrf
                    <div class="p-6 space-y-6">
                        
                        <!-- Step 1: Report Type -->
                        <div class="space-y-3">
                            <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                                <span class="w-6 h-6 rounded-full bg-green-600 text-white text-xs flex items-center justify-center">1</span>
                                {{ __('Select Report Type') }}
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <label class="cursor-pointer">
                                    <input type="radio" name="report_type" value="profile" class="peer sr-only" required>
                                    <div class="p-4 border-2 border-gray-200 dark:border-gray-700 rounded-lg peer-checked:border-green-500 peer-checked:bg-green-50 dark:peer-checked:bg-green-900/20 hover:border-gray-300 transition-all">
                                        <div class="flex items-center gap-3">
                                            <i class="fas fa-user text-xl text-green-600"></i>
                                            <div>
                                                <p class="font-semibold text-gray-900 dark:text-white">Profile</p>
                                                <p class="text-xs text-gray-500">{{ __('Teacher details') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="report_type" value="performance" class="peer sr-only">
                                    <div class="p-4 border-2 border-gray-200 dark:border-gray-700 rounded-lg peer-checked:border-green-500 peer-checked:bg-green-50 dark:peer-checked:bg-green-900/20 hover:border-gray-300 transition-all">
                                        <div class="flex items-center gap-3">
                                            <i class="fas fa-chart-line text-xl text-blue-600"></i>
                                            <div>
                                                <p class="font-semibold text-gray-900 dark:text-white">Performance</p>
                                                <p class="text-xs text-gray-500">{{ __('Teaching stats') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="report_type" value="attendance" class="peer sr-only">
                                    <div class="p-4 border-2 border-gray-200 dark:border-gray-700 rounded-lg peer-checked:border-green-500 peer-checked:bg-green-50 dark:peer-checked:bg-green-900/20 hover:border-gray-300 transition-all">
                                        <div class="flex items-center gap-3">
                                            <i class="fas fa-calendar-check text-xl text-purple-600"></i>
                                            <div>
                                                <p class="font-semibold text-gray-900 dark:text-white">Attendance</p>
                                                <p class="text-xs text-gray-500">{{ __('Attendance record') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Step 2: Teacher Selection -->
                        <div class="space-y-3">
                            <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                                <span class="w-6 h-6 rounded-full bg-green-600 text-white text-xs flex items-center justify-center">2</span>
                                {{ __('Select Teacher') }}
                            </label>
                            <select name="teacher_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm">
                                <option value="">{{ __('All Teachers') }}</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}">{{ $teacher->user->name ?? $teacher->name ?? 'Unknown' }} - {{ $teacher->department->name ?? 'No Dept' }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                <i class="fas fa-info-circle mr-1"></i>{{ __('Leave empty to generate for all teachers') }}
                            </p>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            <i class="fas fa-print mr-1"></i>{{ __('Report will open in new tab for printing') }}
                        </p>
                        <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold rounded-lg text-white bg-green-600 hover:bg-green-700 transition-colors">
                            <i class="fas fa-file-alt"></i>{{ __('Generate Report') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
