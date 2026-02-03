<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-200">
                <i class="fas fa-tools"></i>
            </span>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('maintence.Sample Page') }}</p>
                <h2 class="font-semibold text-lg text-gray-800 dark:text-gray-100 leading-tight">
                    {{ __('maintence.Maintenance Mode') }}
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm p-8 space-y-6">
                <div class="flex items-start gap-4">
                    <span class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-200">
                        <i class="fas fa-screwdriver-wrench text-xl"></i>
                    </span>
                    <div class="space-y-2">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('maintence.System maintenance placeholder') }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                            {{ __('maintence.Use this sample page to preview new layout styles while core modules are being added. Tie real maintenance messaging or guidance for admins here later.') }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/40 p-4">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-[0.18em]">{{ __('maintence.What to do next') }}</p>
                        <ul class="mt-3 space-y-2 text-sm text-gray-700 dark:text-gray-200 list-disc list-inside">
                            <li>{{ __('maintence.Share planned downtime and ETA with users') }}</li>
                            <li>{{ __('maintence.Run health checks before reenabling features') }}</li>
                            <li>{{ __('maintence.Use confirm dialogs for risky actions') }}</li>
                        </ul>
                    </div>
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/40 p-4">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-[0.18em]">{{ __('maintence.Quick links') }}</p>
                        <div class="mt-3 space-y-2">
                            <x-quick-link :href="route('dashboard')" label="{{ __('maintence.Back to dashboard') }}" icon="fas fa-home" />
                            <x-quick-link :href="route('profile.edit')" label="{{ __('maintence.Update contact preferences') }}" icon="fas fa-id-badge" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
