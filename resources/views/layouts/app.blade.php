<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }"
      x-init="
        $watch('darkMode', val => localStorage.setItem('darkMode', val));
        if (darkMode || (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
      "
      :class="{ 'dark': darkMode }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="page-loaded-at" content="{{ now()->timestamp }}">
        @auth
            <meta name="user-role" content="{{ auth()->user()->hasRole('staff') ? 'staff' : 'other' }}">
        @endauth

        <!-- Favicon - Load immediately -->
        @php
            $faviconPath = optional(\App\Models\Setting::first())->school_short_logo_path;
            $faviconUrl = $faviconPath ? asset('storage/'.$faviconPath) : asset('logo_short.png');
        @endphp
        <link rel="preload" href="{{ $faviconUrl }}" as="image">
        <link rel="icon" href="{{ $faviconUrl }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ $faviconUrl }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ $faviconUrl }}">
        <link rel="icon" type="image/png" sizes="48x48" href="{{ $faviconUrl }}">
        <link rel="icon" type="image/png" sizes="64x64" href="{{ $faviconUrl }}">
        <link rel="alternate icon" href="{{ $faviconUrl }}">

        @auth
            <meta name="user-role" content="{{ Auth::user()->getRoleNames()->first() }}">
            <meta name="user-permissions" content="{{ Auth::user()->getAllPermissions()->pluck('name')->implode(',') }}">
        @endauth

        <title>{{ config('app.name', 'Smart Campus') }}</title>

        <!-- Fonts - with Google Fonts fallback -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
        
        <!-- Fallback font styles -->
        <style>
            body {
                font-family: 'Figtree', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            }
        </style>

        <!-- Page-specific styles -->
        @stack('styles')

        <!-- Scripts -->
        @vite(['resources/css/app.css','public/css/academic-management.css', 'resources/js/app.js','public/js/academic-management.js'])
        
        <!-- Custom CSS -->
        <link rel="stylesheet" href="{{ asset('css/own-rules.css') }}">
        
        <!-- Custom JavaScript -->
        <script src="{{ asset('js/own-script.js') }}" defer></script>

        <script>
            // Initialize dark mode before page load to prevent flash
            if (localStorage.getItem('darkMode') === 'true' ||
                (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        </script>
    </head>
    <body class="font-sans antialiased" x-data="{ sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true' }" x-init="$watch('sidebarCollapsed', val => localStorage.setItem('sidebarCollapsed', val))">
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
            @include('layouts.navigation')

            <!-- Floating Toggle Button (when sidebar is collapsed) -->
            <button @click="sidebarCollapsed = !sidebarCollapsed" 
                    x-show="sidebarCollapsed"
                    x-transition:enter="transition ease-out duration-300 delay-300"
                    x-transition:enter-start="opacity-0 scale-75"
                    x-transition:enter-end="opacity-100 scale-100"
                    class="floating-toggle-btn delayed-show fixed left-4 z-50 w-10 h-10 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>

            <div class="min-h-screen flex flex-col" :class="sidebarCollapsed ? 'main-content-collapsed' : 'main-content-expanded'">

                <div class="sticky-header bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 h-[80px] flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        @isset($header)
                            <div class="flex items-center gap-2">
                                {{ $header }}
                            </div>
                        @endisset
                    </div>

                    <div class="flex items-center gap-3 sticky-header">
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-200">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 text-white flex items-center justify-center font-semibold">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                                <div class="hidden sm:block text-left">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</p>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ Auth::user()->name }}</p>
                                </div>
                                <svg class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div x-show="open" x-cloak @click.away="open = false" x-transition class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ Auth::user()->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</p>
                                </div>
                                <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-700 space-y-2">
                                    <button @click="darkMode = !darkMode; $dispatch('toast', { type: 'info', text: darkMode ? '{{ __('layouts.Dark mode enabled') }}' : '{{ __('layouts.Light mode enabled') }}', timeout: 4200 });" class="w-full inline-flex items-center justify-between px-3 py-2 text-sm rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                                        <span>{{ __('layouts.Theme') }}</span>
                                        <svg x-show="!darkMode" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                                        </svg>
                                        <svg x-show="darkMode" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                    </button>
                                    <div class="grid grid-cols-3 gap-2">
                                        <a href="{{ route('language.switch', 'en') }}" class="px-2 py-1 text-center text-xs rounded-md {{ app()->getLocale() === 'en' ? 'bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300' }}">EN</a>
                                        <a href="{{ route('language.switch', 'mm') }}" class="px-2 py-1 text-center text-xs rounded-md {{ app()->getLocale() === 'mm' ? 'bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300' }}">MM</a>
                                    </div>
                                </div>
                                <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m-2-2a2 2 0 10-4 0 2 2 0 004 0zm-6 8a6 6 0 1112 0H9z"></path>
                                    </svg>
                                    {{ __('layouts.Profile') }}
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="button"
                                            class="w-full flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
                                            @click.prevent="$dispatch('confirm-show', {
                                                title: '{{ __('layouts.Confirm logout') }}',
                                                message: '{{ __('layouts.Are you sure you want to log out?') }}',
                                                confirmText: '{{ __('layouts.Log Out') }}',
                                                cancelText: '{{ __('layouts.Cancel') }}',
                                                onConfirm: () => $el.closest('form').submit()
                                            })">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h5a2 2 0 012 2v1"></path>
                                        </svg>
                                        {{ __('layouts.Log Out') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Page Content -->
                <main class="flex-grow overflow-x-hidden">
                    {{ $slot }}
                </main>

                <!-- Footer -->
                <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 transition-colors duration-200">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                            <div class="text-gray-600 dark:text-gray-400 text-sm text-center md:text-left">
                                © {{ date('Y') }} Smart Campus. {{ __('layouts.All rights reserved.') }}
                            </div>
                            <div class="flex space-x-6">
                                <a href="{{ route('manual') }}" class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                    <i class="fas fa-book-open text-lg"></i>
                                    <span class="text-sm font-semibold">{{ __('layouts.User Manual') }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>

        <x-toast />

        @auth
            <x-confirm-dialog />
            <x-alert-dialog />
        @endauth

        @stack('scripts')
    </body>
</html>
