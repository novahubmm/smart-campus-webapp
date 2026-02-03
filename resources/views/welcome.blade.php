<x-guest-layout>
    <div class="mb-8 text-center space-y-3">
        <p class="inline-flex items-center rounded-full bg-blue-50 dark:bg-blue-900/30 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-blue-700 dark:text-blue-200">
            {{ __('School Management Platform') }}
        </p>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            {{ __('Welcome') }}
        </h1>
        <p class="text-gray-600 dark:text-gray-400 max-w-md mx-auto">
            {{ __("Sign in to access your dashboard. Guests can switch language or theme using the controls above.") }}
        </p>
    </div>

    <div class="space-y-4">
        <a href="{{ route('login') }}" class="flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-3 text-white font-semibold shadow-lg shadow-blue-600/30 hover:bg-blue-700 transition-colors">
            <span>{{ __('Go to Login') }}</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </a>

        <div class="rounded-xl border border-dashed border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
            <p class="font-semibold">{{ __('Need help signing in?') }}</p>
            <p class="mt-1">
                <a class="text-blue-600 dark:text-blue-400 hover:underline" href="{{ route('password.recovery.identifier') }}">
                    {{ __('Use account recovery (email/phone + NRC)') }}
                </a>
            </p>
        </div>
    </div>
</x-guest-layout>
