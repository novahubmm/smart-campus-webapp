@props(['subject' => null, 'subjectTypes' => collect(), 'grades' => collect()])

<div class="space-y-1">
    <label for="subjectCode" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
        {{ __('academic_management.Subject Code') }} <span class="text-red-500">*</span>
    </label>
    <input 
        type="text" 
        id="subjectCode" 
        name="code" 
        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" 
        value="{{ old('code', $subject->code ?? '') }}"
        required>
    @error('code')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div class="space-y-1">
    <label for="subjectName" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
        {{ __('academic_management.Subject Name') }} <span class="text-red-500">*</span>
    </label>
    <input 
        type="text" 
        id="subjectName" 
        name="name" 
        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" 
        value="{{ old('name', $subject->name ?? '') }}"
        required>
    @error('name')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="space-y-1">
        <label for="subjectIcon" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Icon</label>
        <input
            type="text"
            id="subjectIcon"
            name="icon"
            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500"
            value="{{ old('icon', $subject->icon ?? '') }}"
            placeholder="fas fa-book">
        @error('icon')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
    <div class="space-y-1">
        <label for="subjectIconColor" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Icon Color</label>
        <input
            type="text"
            id="subjectIconColor"
            name="icon_color"
            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500"
            value="{{ old('icon_color', $subject->icon_color ?? '') }}"
            placeholder="#3B82F6">
        @error('icon_color')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
    <div class="space-y-1">
        <label for="subjectProgressColor" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Progress Color</label>
        <input
            type="text"
            id="subjectProgressColor"
            name="progress_color"
            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500"
            value="{{ old('progress_color', $subject->progress_color ?? '') }}"
            placeholder="#22C55E">
        @error('progress_color')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="space-y-1">
    <label for="subjectGrades" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
        {{ __('academic_management.Grades') }} <span class="text-red-500">*</span>
    </label>
    <select 
        id="subjectGrades" 
        name="grade_ids[]" 
        multiple
        class="select2 w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500"
        required>
        @foreach($grades as $grade)
            <option value="{{ $grade->id }}" 
                {{ (old('grade_ids') ? in_array($grade->id, old('grade_ids')) : ($subject && $subject->grades->contains($grade->id))) ? 'selected' : '' }}>
                @gradeName($grade->level)
            </option>
        @endforeach
    </select>
    @error('grade_ids')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div class="space-y-1">
    <label for="subjectType" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
        {{ __('academic_management.Subject Type') }}
    </label>
    <select 
        id="subjectType" 
        name="subject_type_id" 
        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500">
        <option value="">{{ __('academic_management.Select Subject Type') }}</option>
        @foreach($subjectTypes as $type)
            <option value="{{ $type->id }}" {{ old('subject_type_id', $subject->subject_type_id ?? '') == $type->id ? 'selected' : '' }}>
                {{ $type->name }}
            </option>
        @endforeach
    </select>
    @error('subject_type_id')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
