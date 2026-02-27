<x-app-layout>
    <div class="p-6 space-y-6">
        <!-- Back Button & Header -->
        <div class="flex items-center justify-between">
            <a href="{{ route('guardian.utilities') }}" class="flex items-center text-gray-600 dark:text-gray-300">
                <i class="fas fa-chevron-left mr-2"></i>
                <span class="text-sm font-medium">{{ __('Back') }}</span>
            </a>
            <h1 class="text-lg font-bold text-gray-800 dark:text-white">{{ __('Rules & Regulations') }}</h1>
            <div class="w-8"></div> <!-- Spacer -->
        </div>

        <div>
        <div class="space-y-4">
            @forelse($categories as $category)
                @if($category->rules->count() > 0)
                    <div x-data="{ open: false }"
                        class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <button @click="open = !open"
                            class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50/50 dark:hover:bg-gray-700/50 transition-colors">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 flex-shrink-0">
                                    <i class="fas fa-book-open"></i>
                                </div>
                                <div class="text-left">
                                    <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ $category->title }}</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $category->rules_count }}
                                        {{ __('Rules') }}</p>
                                </div>
                            </div>
                            <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200"
                                :class="{ 'rotate-180': open }"></i>
                        </button>

                        <div x-show="open" x-collapse x-cloak>
                            <div class="px-6 pb-6 pt-2 space-y-4">
                                @if($category->description)
                                    <div class="text-sm text-gray-600 dark:text-gray-400 italic mb-4">
                                        {{ $category->description }}
                                    </div>
                                @endif

                                <div class="space-y-3">
                                    @foreach($category->rules as $index => $rule)
                                        <div
                                            class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-100 dark:border-gray-700">
                                            <div class="flex gap-3">
                                                <span
                                                    class="flex-shrink-0 w-6 h-6 rounded-full bg-white dark:bg-gray-800 flex items-center justify-center text-[10px] font-bold text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
                                                    {{ $index + 1 }}
                                                </span>
                                                <div>
                                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white leading-tight mb-1">
                                                        {{ $rule->title }}</h4>
                                                    <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                                        {{ $rule->content }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @empty
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-12 text-center">
                    <div
                        class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-900/50 flex items-center justify-center mx-auto mb-4 text-gray-400">
                        <i class="fas fa-clipboard-list text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('No rules found') }}</h3>
                    <p class="text-gray-500 dark:text-gray-400 mt-2">
                        {{ __('School rules and regulations have not been published yet.') }}</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>