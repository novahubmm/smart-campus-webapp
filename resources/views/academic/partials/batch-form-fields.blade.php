@props(['batch' => null])

<div class="space-y-1">
    <label for="batchName" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
        {{ __('academic_management.Batch Name') }} <span class="text-red-500">*</span>
    </label>
    <input 
        type="text" 
        id="batchName" 
        name="name" 
        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" 
        placeholder="{{ __('academic_management.e.g., 2025-2026') }}"
        value="{{ old('name', $batch->name ?? '') }}"
        required>
    @error('name')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div class="space-y-1">
    <label for="batchStart" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
        {{ __('academic_management.Start Date') }} <span class="text-red-500">*</span>
    </label>
    <input 
        type="date" 
        id="batchStart" 
        name="start_date" 
        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500"
        value="{{ old('start_date', optional($batch->start_date)->format('Y-m-d')) }}"
        required>
    @error('start_date')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div class="space-y-1">
    <label for="batchEnd" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
        {{ __('academic_management.End Date') }} <span class="text-red-500">*</span>
    </label>
    <input 
        type="date" 
        id="batchEnd" 
        name="end_date" 
        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500"
        value="{{ old('end_date', optional($batch->end_date)->format('Y-m-d')) }}"
        required>
    @error('end_date')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
