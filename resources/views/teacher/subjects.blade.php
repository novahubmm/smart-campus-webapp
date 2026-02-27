<x-app-layout>
    <!-- Header Section -->
    <div class="px-6 py-4 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
        <div class="flex items-center">
            <a href="{{ route('teacher.academic') }}"
                class="mr-4 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white leading-tight">
                    {{ __('Subjects') }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ __('Your assigned teaching subjects') }}
                </p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="p-6">
        <div
            class="bg-white dark:bg-gray-800 rounded-3xl p-12 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col items-center justify-center text-center">
            <div
                class="w-20 h-20 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center text-blue-500 mb-6">
                <i class="fas fa-book text-3xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-2">
                {{ __('Subjects Portal Coming Soon') }}
            </h3>
            <p class="text-gray-500 dark:text-gray-400 max-w-xs mx-auto mb-8">
                {{ __('We are currently building this section to help you manage your subjects, resources, and syllabus more effectively.') }}
            </p>
            <a href="{{ route('teacher.academic') }}"
                class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-2xl transition-all active:scale-95">
                {{ __('Back to Academic Hub') }}
            </a>
        </div>
    </div>
</x-app-layout>