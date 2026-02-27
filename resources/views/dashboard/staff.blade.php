<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-home"></i>
            </div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('dashboard.Dashboard') }}</h1>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- School Info Header Section -->
        @if($setting)
            <div
                class="flex flex-col md:flex-row rounded-2xl overflow-hidden shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="flex-[0.8] p-8 bg-emerald-600 dark:bg-emerald-700 school-info">
                    <h2 class="text-3xl font-extrabold text-white tracking-wide uppercase mb-6">
                        {{ $setting->school_name ?? 'SMART CAMPUS' }}</h2>
                    <div class="space-y-3 text-white/90">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-map-marker-alt w-5 text-center"></i>
                            <span>{{ $setting->school_address ?? 'Location' }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <i class="fas fa-envelope w-5 text-center"></i>
                            <span>{{ $setting->school_email ?? 'email@school.com' }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <i class="fas fa-phone w-5 text-center"></i>
                            <span>{{ $setting->school_phone ?? '+959 000000000' }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex-1 p-6 bg-emerald-600 dark:bg-emerald-700 flex items-center justify-center school-logo">
                    @php
                        $schoolLogo = $setting?->school_logo_path;
                        $logoUrl = $schoolLogo ? asset('storage/' . $schoolLogo) : asset('school-banner-logo.svg');
                    @endphp
                    <img src="{{ $logoUrl }}" class="school-banner-logo drop-shadow-lg" alt="School Logo">
                </div>
            </div>
        @endif

        <!-- Staff Dashboard Grid Placeholder -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Placeholder Content -->
            <div
                class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 shadow-sm min-h-[150px] flex items-center justify-center">
                <p class="text-gray-500 dark:text-gray-400 font-medium">Staff Features Coming Soon</p>
            </div>
        </div>
    </div>
</x-app-layout>