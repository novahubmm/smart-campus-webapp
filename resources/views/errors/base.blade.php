<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }"
      x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val)); if (darkMode || (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) document.documentElement.classList.add('dark');"
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? __('Error') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('smart-campus-browser-tab.svg') }}">
    <link rel="alternate icon" href="{{ asset('smart-campus-browser-tab.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        if (localStorage.getItem('darkMode') === 'true' ||
            (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
</head>
<body class="font-sans antialiased bg-gradient-to-br from-blue-50 to-purple-50 dark:from-gray-900 dark:to-gray-800 transition-colors duration-200">
    <div class="min-h-screen flex flex-col items-center justify-center px-4">
        <div class="absolute top-4 right-4 flex items-center gap-3">
            <button @click="darkMode = !darkMode; $dispatch('toast', { type: 'info', text: darkMode ? '{{ __('errors.Dark mode enabled') }}' : '{{ __('errors.Light mode enabled') }}' });"
                    class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <svg x-show="!darkMode" class="h-5 w-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
                <svg x-show="darkMode" class="h-5 w-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </button>
        </div>

        <div class="text-center space-y-6">
            @php
                $schoolLogo = optional(\App\Models\Setting::first())->school_logo_path;
                $logoUrl = $schoolLogo ? asset('storage/'.$schoolLogo) : asset('smart-campus-logo.svg');
            @endphp
            <img src="{{ $logoUrl }}" alt="Smart Campus" class="h-16 w-auto mx-auto">
            <div class="space-y-2">
                <p class="text-sm uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">{{ __('errors.Smart Campus') }}</p>
                <h1 class="text-4xl font-bold text-gray-900 dark:text-white">{{ $title ?? __('Oops!') }}</h1>
                <p class="text-lg text-gray-600 dark:text-gray-300 max-w-xl mx-auto">{{ $message ?? __('Something went wrong.') }}</p>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-5 py-3 rounded-lg bg-blue-600 text-white font-semibold shadow hover:bg-blue-700 transition-colors">
                        <i class="fas fa-gauge mr-2"></i> {{ __('errors.Go to dashboard') }}
                    </a>
                @endauth
                <a href="{{ route('welcome') }}" class="inline-flex items-center px-5 py-3 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 font-semibold hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                    <i class="fas fa-home mr-2"></i> {{ __('errors.Back to home') }}
                </a>
            </div>
        </div>

        <x-toast />
        <x-alert-dialog />
    </div>
</body>
</html>
