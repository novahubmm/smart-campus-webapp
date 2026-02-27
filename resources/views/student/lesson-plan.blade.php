<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-book-open"></i>
            </div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('Lesson Plan') }}</h1>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Subject Filter -->
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
            <form action="{{ route('student.lesson-plan') }}" method="GET" class="flex flex-wrap items-center gap-4">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Select Subject:</label>
                <select name="subject_id" onchange="this.form.submit()"
                    class="rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 text-sm focus:ring-purple-500 min-w-[200px]">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ $subjectId == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="space-y-4">
            @forelse($chapters as $chapter)
                <div x-data="{ open: true }"
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <button @click="open = !open"
                        class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 dark:text-purple-400 font-bold">
                                {{ $loop->iteration }}
                            </div>
                            <h3 class="font-bold text-gray-900 dark:text-white">{{ $chapter->title }}</h3>
                        </div>
                        <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200"
                            :class="{'rotate-180': open}"></i>
                    </button>
                    <div x-show="open" x-collapse>
                        <div
                            class="px-6 py-4 space-y-3 bg-gray-50/50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700">
                            @forelse($chapter->topics as $topic)
                                <div
                                    class="flex items-center gap-4 p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                                    <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                    <span class="text-sm text-gray-700 dark:text-gray-300 flex-grow">{{ $topic->title }}</span>
                                    <button
                                        class="text-xs font-bold text-purple-600 dark:text-purple-400 uppercase tracking-wider hover:underline">Resources</button>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500 italic">No topics added to this chapter yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @empty
                <div
                    class="p-12 text-center bg-white dark:bg-gray-800 rounded-2xl border border-dashed border-gray-300 dark:border-gray-700">
                    <p class="text-gray-500 dark:text-gray-400">No curriculum data available for the selected subject.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>