<x-guest-layout>
    <div class="mb-6 text-center space-y-2">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('auth.Enter OTP') }}</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            {{ __('auth.Check your email or phone for the 6-digit code we sent. It expires in 10 minutes.') }}
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.recovery.otp.store') }}" class="space-y-6">
        @csrf
        <div>
            <x-input-label for="otp" :value="__('OTP code')" />
            <x-text-input id="otp" class="block mt-1 w-full" type="text" name="otp" inputmode="numeric" maxlength="6" :value="old('otp')" required autofocus />
            <x-input-error :messages="$errors->get('otp')" class="mt-2" />
        </div>

        <div class="space-y-3">
            <x-primary-button class="w-full justify-center">
                {{ __('auth.Verify code') }}
            </x-primary-button>
            <div class="text-center text-sm">
                <a href="{{ route('password.recovery.identifier') }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                    {{ __('auth.Start over') }}
                </a>
            </div>
        </div>
    </form>
</x-guest-layout>
