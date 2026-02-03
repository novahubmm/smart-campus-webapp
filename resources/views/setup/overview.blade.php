<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-wand-magic-sparkles"></i>
            </div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('setup.Setup Wizard') }}</h1>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($steps as $step)
                @php
                    $isMissing = $missing === $step['key'];
                @endphp
                <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm h-full flex flex-col overflow-hidden">
                    <div class="p-5 border-b border-gray-100 dark:border-gray-800">
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <p class="text-[11px] uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">{{ __('setup.Step') }}</p>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold whitespace-nowrap flex-shrink-0
                                {{ $step['complete'] ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-200' : 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-200' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $step['complete'] ? 'bg-green-500' : 'bg-amber-500' }}"></span>
                                {{ $step['complete'] ? __('setup.Complete') : __('setup.Pending') }}
                            </span>
                        </div>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ $step['title'] }}</h2>
                    </div>
                    <div class="p-5 flex-1 flex flex-col">
                        <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ $step['description'] }}</p>
                        @if($isMissing)
                            <div class="mt-3 text-xs font-semibold text-amber-600 dark:text-amber-300 flex items-center gap-2">
                                <i class="fas fa-arrow-right"></i>
                                <span>{{ __('You were redirected here because this step is incomplete.') }}</span>
                            </div>
                        @endif
                        <div class="mt-auto pt-4">
                            <a href="{{ $step['action'] }}" class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 focus:ring-offset-white dark:focus:ring-offset-gray-900 transition-colors text-center w-full">
                                {{ $step['action_label'] }}
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
