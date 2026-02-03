<x-guest-layout>
    <div class="mb-6 text-center space-y-2">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('auth.Find your account') }}</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            {{ __('auth.Enter your email or phone number to start password recovery.') }}
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.recovery.identifier.store') }}" class="space-y-6">
        @csrf
        <div>
            <x-input-label for="identifier" :value="__('Email or phone number')" />
            <x-text-input id="identifier" class="block mt-1 w-full" type="text" name="identifier" :value="old('identifier')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('identifier')" class="mt-2" />
        </div>

        <div class="space-y-3">
            <x-primary-button class="w-full justify-center">
                {{ __('auth.Continue') }}
            </x-primary-button>
            <div class="text-center text-sm">
                <a href="{{ route('login') }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                    {{ __('auth.Back to login') }}
                </a>
            </div>
        </div>
    </form>
</x-guest-layout>
