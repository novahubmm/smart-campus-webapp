<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('errors.Maintenance Mode') }} - Smart Campus</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-blue-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 px-4">
        <!-- Logo -->
        <div class="mb-8 text-center">
            <svg class="h-16 w-16 mx-auto text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
            </svg>
            <h1 class="mt-4 text-3xl font-bold text-gray-900 dark:text-white">Smart Campus</h1>
        </div>

        <!-- Maintenance Card -->
        <div class="max-w-md w-full bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden">
            <div class="p-8 text-center">
                <!-- Maintenance Icon -->
                <div class="flex justify-center mb-6">
                    <div class="relative">
                        <div class="h-24 w-24 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center">
                            <svg class="h-12 w-12 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div class="absolute -top-2 -right-2 h-8 w-8 rounded-full bg-yellow-500 flex items-center justify-center">
                            <span class="text-white text-xs font-bold">503</span>
                        </div>
                    </div>
                </div>

                <!-- Maintenance Message -->
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">
                    {{ __('errors.We\'ll be right back!') }}
                </h2>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    {{ __('errors.Sorry for the inconvenience. We\'re performing some maintenance at the moment. We\'ll be back online shortly!') }}
                </p>

                <!-- Progress Indicator -->
                <div class="mb-8">
                    <div class="flex justify-center gap-2">
                        <div class="h-3 w-3 rounded-full bg-yellow-500 animate-bounce" style="animation-delay: 0ms"></div>
                        <div class="h-3 w-3 rounded-full bg-yellow-500 animate-bounce" style="animation-delay: 150ms"></div>
                        <div class="h-3 w-3 rounded-full bg-yellow-500 animate-bounce" style="animation-delay: 300ms"></div>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20 mb-4">
                    <p class="text-sm text-blue-800 dark:text-blue-200">
                        {{ __('errors.If you need immediate assistance, please contact our support team.') }}
                    </p>
                </div>

                <!-- Admin Login Link -->
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/50">
                    <div class="flex items-center justify-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <svg class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <span>{{ __('errors.Administrator?') }}</span>
                        <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                            {{ __('errors.Login here') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-sm text-gray-500 dark:text-gray-400">
            <p>&copy; {{ date('Y') }} Smart Campus. {{ __('errors.All rights reserved.') }}</p>
        </div>
    </div>
</body>
</html>
