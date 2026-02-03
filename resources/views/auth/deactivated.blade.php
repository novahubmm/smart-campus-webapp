<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('auth.Account Deactivated') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        if (localStorage.getItem('darkMode') === 'true' || (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
</head>
<body class="min-h-full bg-gray-50 dark:bg-gray-900 flex items-center justify-center px-4 py-8">
    <div class="w-full max-w-xl">
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl overflow-hidden">
            <div class="px-6 py-6 sm:px-8">
                <div class="flex items-center gap-3">
                    <div class="h-12 w-12 rounded-full bg-gradient-to-r from-red-500 to-pink-500 text-white flex items-center justify-center">
                        <i class="fas fa-user-slash text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('auth.Your account is deactivate, connect to admin.') }}</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ __('auth.Please contact an administrator if you believe this is a mistake.') }}</p>
                    </div>
                </div>

                @if (session('error'))
                    <div class="mt-4 text-sm text-red-700 bg-red-50 dark:bg-red-900/40 dark:text-red-200 rounded-lg px-4 py-3">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <a href="{{ route('welcome') }}" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-sm font-semibold">
                        <i class="fas fa-arrow-left mr-2"></i>
                        {{ __('auth.Back to home') }}
                    </a>
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors text-sm font-semibold">
                        <i class="fas fa-right-to-bracket mr-2"></i>
                        {{ __('auth.Return to login') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
