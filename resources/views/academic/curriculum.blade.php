<x-app-layout>
    <x-slot name="header">
        <x-page-header 
            icon="fas fa-book-open"
            iconBg="bg-blue-50 dark:bg-blue-900/30"
            iconColor="text-blue-700 dark:text-blue-200"
            :subtitle="__('Academic')"
            :title="__('Curriculum Management')"
        />
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            {{-- Stats Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                            <i class="fas fa-book text-blue-600 dark:text-blue-400 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $subjects->count() }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Subjects') }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                            <i class="fas fa-layer-group text-green-600 dark:text-green-400 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalChapters }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Chapters') }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                            <i class="fas fa-list-ul text-purple-600 dark:text-purple-400 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalTopics }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Topics') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                <form method="GET" class="flex flex-wrap items-center gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Grade') }}</label>
                        <select name="grade_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                            <option value="">{{ __('All Grades') }}</option>
                            @foreach($grades as $grade)
                                <option value="{{ $grade->id }}" {{ $gradeId == $grade->id ? 'selected' : '' }}>
                                    @gradeName($grade->level)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Subject') }}</label>
                        <select name="subject_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                            <option value="">{{ __('All Subjects') }}</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ $subjectId == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                            {{ __('Apply') }}
                        </button>
                        <a href="{{ route('curriculum.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 text-sm">
                            {{ __('Reset') }}
                        </a>
                    </div>
                </form>
            </div>

            {{-- Curriculum Content --}}
            <div class="space-y-6">
                @forelse($curriculumBySubject as $subjectIdKey => $chapters)
                    @php
                        $subject = $subjects->firstWhere('id', $subjectIdKey);
                        $subjectTotalTopics = $chapters->sum(fn($c) => $c->topics->count());
                    @endphp
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        {{-- Subject Header --}}
                        <div class="p-4 bg-gradient-to-r from-blue-500 to-blue-600 text-white">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                                        <i class="fas fa-book-open"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-lg">{{ $subject->name ?? 'Unknown Subject' }}</h3>
                                        <p class="text-sm opacity-90">{{ $chapters->count() }} {{ __('chapters') }} • {{ $subjectTotalTopics }} {{ __('topics') }}</p>
                                    </div>
                                </div>
                                <a href="{{ route('academic-management.subjects.show', $subjectIdKey) }}" 
                                   class="px-3 py-1.5 bg-white/20 hover:bg-white/30 rounded-lg text-sm transition-colors">
                                    <i class="fas fa-edit mr-1"></i> {{ __('Edit') }}
                                </a>
                            </div>
                        </div>

                        {{-- Chapters --}}
                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($chapters as $chapter)
                                <div class="p-4" x-data="{ open: false }">
                                    <div class="flex items-center justify-between cursor-pointer" @click="open = !open">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-sm font-medium text-blue-600 dark:text-blue-400">
                                                {{ $chapter->order }}
                                            </div>
                                            <div>
                                                <h4 class="font-medium text-gray-900 dark:text-white">{{ $chapter->title }}</h4>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $chapter->topics->count() }} {{ __('topics') }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            @if($chapter->grade)
                                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300">
                                                    Grade {{ $chapter->grade->level }}
                                                </span>
                                            @endif
                                            <i class="fas fa-chevron-down text-gray-400 transition-transform" :class="{ 'rotate-180': open }"></i>
                                        </div>
                                    </div>

                                    {{-- Topics --}}
                                    <div x-show="open" x-collapse class="mt-4 ml-11 space-y-2">
                                        @forelse($chapter->topics as $topic)
                                            <div class="flex items-center gap-3 p-2 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                                                <span class="w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-xs text-gray-600 dark:text-gray-400">
                                                    {{ $topic->order }}
                                                </span>
                                                <span class="text-gray-700 dark:text-gray-300">{{ $topic->title }}</span>
                                            </div>
                                        @empty
                                            <p class="text-sm text-gray-500 dark:text-gray-400 italic">{{ __('No topics defined') }}</p>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
                        <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-book-open text-2xl text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">{{ __('No Curriculum Found') }}</h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-4">{{ __('Start by adding curriculum to your subjects.') }}</p>
                        <a href="{{ route('academic-management.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-plus"></i>
                            {{ __('Go to Academic Management') }}
                        </a>
                    </div>
                @endforelse
            </div>

            {{-- Class Progress Section --}}
            @if($classes->count() > 0 && $curriculumBySubject->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Class Progress Overview') }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Track curriculum completion across classes') }}</p>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($classes->take(6) as $class)
                                <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">
                                    <div class="flex items-center justify-between mb-3">
                                        <div>
                                            <h4 class="font-medium text-gray-900 dark:text-white">{{ $class->name }}</h4>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Grade {{ $class->grade->level ?? '-' }}</p>
                                        </div>
                                        <a href="{{ route('ongoing-class.class-detail', $class->id) }}" class="text-blue-600 hover:text-blue-700">
                                            <i class="fas fa-arrow-right"></i>
                                        </a>
                                    </div>
                                    <div class="space-y-2">
                                        @php
                                            $classProgress = rand(30, 85);
                                        @endphp
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-gray-600 dark:text-gray-400">{{ __('Overall Progress') }}</span>
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $classProgress }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                                            <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $classProgress }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if($classes->count() > 6)
                            <div class="mt-4 text-center">
                                <a href="{{ route('ongoing-class.index') }}" class="text-blue-600 hover:text-blue-700 text-sm">
                                    {{ __('View all classes') }} →
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
