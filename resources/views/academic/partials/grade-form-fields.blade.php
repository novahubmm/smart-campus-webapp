@props([
    'grade' => null,
    'batches' => collect(),
    'gradeCategories' => collect(),
])

<div class="space-y-1">
    <label for="gradeBatch" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
        {{ __('academic_management.Batch') }} <span class="text-red-500">*</span>
    </label>
    <select 
        id="gradeBatch" 
        name="batch_id" 
        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500"
        required>
        @foreach($batches as $batch)
            <option value="{{ $batch->id }}" {{ old('batch_id', $grade->batch_id ?? '') == $batch->id ? 'selected' : '' }}>
                {{ $batch->name }}
            </option>
        @endforeach
    </select>
    @error('batch_id')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div class="space-y-1">
    <label for="gradeLevel" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
        {{ __('academic_management.Grade Level') }} <span class="text-red-500">*</span>
    </label>
    <select 
        id="gradeLevel" 
        name="level" 
        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500"
        required>
        @foreach(range(0, 12) as $level)
            <option value="{{ $level }}" {{ (string) old('level', $grade->level ?? '') === (string) $level ? 'selected' : '' }}>
                {{ $level }}
            </option>
        @endforeach
    </select>
    @error('level')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div class="space-y-1">
    <label for="gradeCategory" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
        {{ __('academic_management.Grade Category') }} <span class="text-red-500">*</span>
    </label>
    <select 
        id="gradeCategory" 
        name="grade_category_id" 
        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500"
        required>
        @foreach($gradeCategories as $category)
            <option value="{{ $category->id }}" {{ old('grade_category_id', $grade->grade_category_id ?? '') == $category->id ? 'selected' : '' }}>
                {{ $category->name }}
            </option>
        @endforeach
    </select>
    @error('grade_category_id')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div class="grid grid-cols-2 gap-4">
    <div class="space-y-1">
        <label for="gradeStartDate" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
            {{ __('academic_management.Start Date') }}
        </label>
        <input 
            type="date" 
            id="gradeStartDate" 
            name="start_date" 
            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500"
            value="{{ old('start_date', $grade && $grade->start_date ? $grade->start_date->format('Y-m-d') : '') }}">
        @error('start_date')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-1">
        <label for="gradeEndDate" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
            {{ __('academic_management.End Date') }}
        </label>
        <input 
            type="date" 
            id="gradeEndDate" 
            name="end_date" 
            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500"
            value="{{ old('end_date', $grade && $grade->end_date ? $grade->end_date->format('Y-m-d') : '') }}">
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            <i class="fas fa-info-circle"></i> {{ __('academic_management.Leave empty for ongoing grade') }}
        </p>
        @error('end_date')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
</div>
