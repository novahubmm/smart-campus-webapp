<x-guest-layout>
    <div class="mb-6 text-center space-y-2">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('auth.Set a new password') }}</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            {{ __('auth.Choose a strong password and confirm it to finish resetting.') }}
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.recovery.reset.store') }}" class="space-y-6">
        @csrf
        <div>
            <x-input-label for="password" :value="__('New password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autofocus autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="space-y-3">
            <x-primary-button class="w-full justify-center">
                {{ __('auth.Update password') }}
            </x-primary-button>
            <div class="text-center text-sm">
                <a href="{{ route('password.recovery.identifier') }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                    {{ __('auth.Restart recovery') }}
                </a>
            </div>
        </div>
    </form>
</x-guest-layout>
