<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                <i class="fas fa-comment-dots text-blue-600 dark:text-blue-400"></i>
            </div>
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('feedback.Submit Feedback') }}</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('feedback.Help us improve Smart Campus by sharing your feedback') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Success/Error Messages -->
            @if (session('success'))
                <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-3"></i>
                        <p class="text-green-800 dark:text-green-200">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                        <p class="text-red-800 dark:text-red-200">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('feedback.store') }}" class="space-y-6">
                        @csrf

                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('feedback.Title') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title') }}"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500"
                                   placeholder="{{ __('feedback.Brief summary of your feedback') }}"
                                   required>
                            @error('title')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Category -->
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('feedback.Category') }} <span class="text-red-500">*</span>
                            </label>
                            <select id="category" 
                                    name="category" 
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500"
                                    required>
                                <option value="">{{ __('feedback.Select a category') }}</option>
                                <option value="bug" {{ old('category') === 'bug' ? 'selected' : '' }}>{{ __('feedback.Bug Report') }}</option>
                                <option value="feature" {{ old('category') === 'feature' ? 'selected' : '' }}>{{ __('feedback.Feature Request') }}</option>
                                <option value="improvement" {{ old('category') === 'improvement' ? 'selected' : '' }}>{{ __('feedback.Improvement Suggestion') }}</option>
                                <option value="question" {{ old('category') === 'question' ? 'selected' : '' }}>{{ __('feedback.Question/Help') }}</option>
                                <option value="other" {{ old('category') === 'other' ? 'selected' : '' }}>{{ __('feedback.Other') }}</option>
                            </select>
                            @error('category')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Priority -->
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('feedback.Priority') }} <span class="text-red-500">*</span>
                            </label>
                            <select id="priority" 
                                    name="priority" 
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500"
                                    required>
                                <option value="">{{ __('feedback.Select priority level') }}</option>
                                <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>{{ __('feedback.Low') }}</option>
                                <option value="normal" {{ old('priority') === 'normal' ? 'selected' : '' }}>{{ __('feedback.Normal') }}</option>
                                <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>{{ __('feedback.High') }}</option>
                                <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>{{ __('feedback.Urgent') }}</option>
                            </select>
                            @error('priority')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Message -->
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('feedback.Message') }} <span class="text-red-500">*</span>
                            </label>
                            <textarea id="message" 
                                      name="message" 
                                      rows="6"
                                      class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500"
                                      placeholder="{{ __('feedback.Please provide detailed information about your feedback...') }}"
                                      required>{{ old('message') }}</textarea>
                            @error('message')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Info Box -->
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                            <div class="flex items-start">
                                <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
                                <div class="text-sm text-blue-800 dark:text-blue-200">
                                    <p class="font-medium mb-1">{{ __('feedback.Your feedback will be sent directly to our support team') }}</p>
                                    <p>{{ __('feedback.We review all feedback and will respond if necessary. Thank you for helping us improve Smart Campus!') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('dashboard') }}" 
                               class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                {{ __('feedback.Cancel') }}
                            </a>
                            <button type="submit" 
                                    class="px-6 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-lg">
                                <i class="fas fa-paper-plane mr-2"></i>
                                {{ __('feedback.Submit Feedback') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>