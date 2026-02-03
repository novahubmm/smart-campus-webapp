<x-guest-layout>
    <div class="mb-6 text-center space-y-2">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('auth.Verify with NRC') }}</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            {{ __('auth.Confirm your NRC to send a one-time code to your email or phone.') }}
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.recovery.nrc.store') }}" class="space-y-6">
        @csrf
        <div>
            <x-input-label for="nrc" :value="__('NRC number')" />
            <x-text-input id="nrc" class="block mt-1 w-full" type="text" name="nrc" :value="old('nrc')" required autofocus autocomplete="off" />
            <x-input-error :messages="$errors->get('nrc')" class="mt-2" />
        </div>

        <div class="space-y-3">
            <x-primary-button class="w-full justify-center">
                {{ __('auth.Send OTP') }}
            </x-primary-button>
            <div class="text-center text-sm">
                <a href="{{ route('password.recovery.identifier') }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                    {{ __('auth.Start over') }}
                </a>
            </div>
        </div>
    </form>
</x-guest-layout>
