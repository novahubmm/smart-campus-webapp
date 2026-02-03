<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }"
      x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))"
      :class="{ 'dark': darkMode }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Smart Campus') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('smart-campus-browser-tab.svg') }}">
        <link rel="alternate icon" href="{{ asset('smart-campus-browser-tab.svg') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <script>
            if (localStorage.getItem('darkMode') === 'true' ||
                (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        </script>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-blue-50 to-purple-50 dark:from-gray-900 dark:to-gray-800 transition-colors duration-200">
            <!-- Language & Dark Mode Switcher -->
            <div class="absolute top-4 right-4 flex items-center gap-3">
                <!-- Dark Mode Toggle -->
                <button @click="darkMode = !darkMode; $dispatch('toast', { type: 'info', text: darkMode ? '{{ __('layouts.Dark mode enabled') }}' : '{{ __('layouts.Light mode enabled') }}', timeout: 4200 });" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg x-show="!darkMode" class="h-5 w-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    <svg x-show="darkMode" class="h-5 w-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </button>

                <!-- Language Switcher -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center space-x-1 px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <svg class="h-5 w-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                        </svg>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ app()->getLocale() === 'mm' ? 'MM' : (app()->getLocale() === 'zh' ? 'ZH' : 'EN') }}</span>
                    </button>
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-32 bg-white dark:bg-gray-700 rounded-md shadow-lg py-1 z-10">
                        <a href="{{ route('language.switch', 'en') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                            🇬🇧 English
                        </a>
                        <a href="{{ route('language.switch', 'mm') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                            🇲🇲 မြန်မာ
                        </a>
                    </div>
                </div>
            </div>

            <!-- Logo -->
            <div class="mb-8 text-center">
                @php
                    $schoolLogo = optional(\App\Models\Setting::first())->school_logo_path;
                    $logoUrl = $schoolLogo ? asset('storage/'.$schoolLogo) : asset('smart-campus-logo.svg');
                @endphp
                <img src="{{ $logoUrl }}" alt="Smart Campus" class="h-16 w-auto mx-auto">
            </div>

            <!-- Card -->
            <div class="w-full sm:max-w-md bg-white dark:bg-gray-800 shadow-xl overflow-hidden sm:rounded-2xl transition-colors duration-200">
                <div class="px-6 py-8">
                    {{ $slot }}
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
                © {{ date('Y') }} Smart Campus. {{ __('layouts.All rights reserved.') }}
            </div>
        </div>

        <x-toast />
        <x-confirm-dialog />
    </body>
</html>
