<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-briefcase"></i>
            </div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('Resource Hub') }}</h1>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-6">
        <div
            class="bg-white dark:bg-gray-800 rounded-3xl p-12 text-center shadow-sm border border-gray-100 dark:border-gray-700">
            <div
                class="w-24 h-24 bg-cyan-50 dark:bg-cyan-900/20 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-tools text-4xl text-cyan-600 dark:text-cyan-400"></i>
            </div>
            <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-4">Under Construction</h2>
            <p class="text-gray-500 dark:text-gray-400 max-w-lg mx-auto mb-8">
                The Resource Hub is currently being built to provide you with all the study materials, e-books, and
                digital artifacts you need. Stay tuned!
            </p>
            <div class="flex justify-center gap-4">
                <a href="{{ route('student.dashboard') }}"
                    class="px-6 py-3 bg-cyan-600 text-white rounded-xl font-bold hover:bg-cyan-700 transition-colors shadow-lg shadow-cyan-600/20">
                    Back to Dashboard
                </a>
            </div>
        </div>

        <div
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 opacity-50 grayscale pointer-events-none select-none">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700">
                <i class="fas fa-book text-2xl text-cyan-500 mb-4"></i>
                <h4 class="font-bold mb-2">E-Books</h4>
                <p class="text-xs text-gray-500">Digital textbooks for all subjects.</p>
            </div>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700">
                <i class="fas fa-file-pdf text-2xl text-cyan-500 mb-4"></i>
                <h4 class="font-bold mb-2">Past Papers</h4>
                <p class="text-xs text-gray-500">Previous year exam questions.</p>
            </div>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700">
                <i class="fas fa-video text-2xl text-cyan-500 mb-4"></i>
                <h4 class="font-bold mb-2">Video Lessons</h4>
                <p class="text-xs text-gray-500">Recorded sessions from teachers.</p>
            </div>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700">
                <i class="fas fa-puzzle-piece text-2xl text-cyan-500 mb-4"></i>
                <h4 class="font-bold mb-2">Worksheets</h4>
                <p class="text-xs text-gray-500">Practice materials and quizzes.</p>
            </div>
        </div>
    </div>
</x-app-layout>