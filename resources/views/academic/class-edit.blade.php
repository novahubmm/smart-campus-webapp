<x-app-layout>
    <x-slot name="styles">
        <link rel="stylesheet" href="{{ asset('css/academic-management.css') }}?v={{ time() }}">
    </x-slot>
    <x-slot name="header">
        <x-page-header
            icon="fas fa-edit"
            iconBg="bg-blue-50 dark:bg-blue-900/30"
            iconColor="text-blue-700 dark:text-blue-200"
            :subtitle="__('ongoing_class.Academic') . ' / ' . __('ongoing_class.Virtual Campus')"
        >
            {{ __('academic_management.Edit Class') }}: @className($class->name, $class->grade?->level)
        </x-page-header>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-back-link 
                :href="route('academic-management.classes.show', $class->id)"
                :text="__('academic_management.Back to Class Details')"
            />

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                        {{ __('academic_management.Edit Class Information') }}
                    </h2>
                </div>

                <form action="{{ route('academic-management.classes.update', $class->id) }}" method="POST" class="p-6 space-y-6">
                    @csrf
                    @method('PUT')

                    @include('academic.partials.class-form-fields', [
                        'grades' => $grades,
                        'rooms' => $rooms,
                        'teachers' => $teachers,
                        'class' => $class,
                    ])

                    <div class="flex items-center justify-between gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('academic-management.classes.show', $class->id) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <i class="fas fa-times"></i>
                            <span>{{ __('academic_management.Cancel') }}</span>
                        </a>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-2 text-sm font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                            <i class="fas fa-check"></i>
                            <span>{{ __('academic_management.Update Class') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
