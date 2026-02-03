# Form Modal Component Usage Guide

## Overview
The `<x-form-modal>` component is a reusable modal for creating and updating data with:
- ✅ Dark theme support
- ✅ Responsive design (mobile-friendly)
- ✅ Single column form layout
- ✅ Smooth animations
- ✅ Keyboard support (ESC to close)
- ✅ Click outside to close

## Basic Usage

### 1. Simple Modal with One Field

```blade
<x-form-modal 
    id="batchModal" 
    title="Create Batch" 
    icon="fas fa-folder-plus"
    action="{{ route('academic-management.batches.store') }}"
    method="POST"
    submitText="Save Batch"
    cancelText="Cancel">
    
    <div class="space-y-1">
        <label for="batchName" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
            Batch Name <span class="text-red-500">*</span>
        </label>
        <input 
            type="text" 
            id="batchName" 
            name="name" 
            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" 
            placeholder="e.g., 2025-2026"
            required>
    </div>
    
    <div class="space-y-1">
        <label for="batchStart" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
            Start Date <span class="text-red-500">*</span>
        </label>
        <input 
            type="date" 
            id="batchStart" 
            name="start_date" 
            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500"
            required>
    </div>
    
    <div class="space-y-1">
        <label for="batchEnd" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
            End Date <span class="text-red-500">*</span>
        </label>
        <input 
            type="date" 
            id="batchEnd" 
            name="end_date" 
            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500"
            required>
    </div>
</x-form-modal>
```

### 2. Grade Modal

```blade
<x-form-modal 
    id="gradeModal" 
    title="Create Grade" 
    icon="fas fa-layer-group"
    action="{{ route('academic-management.grades.store') }}"
    submitText="Save Grade">
    
    <div class="space-y-1">
        <label for="gradeLevel" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
            Grade Level <span class="text-red-500">*</span>
        </label>
        <input 
            type="number" 
            id="gradeLevel" 
            name="level" 
            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" 
            placeholder="e.g., 1, 2, 3..."
            min="1"
            required>
    </div>
    
    <div class="space-y-1">
        <label for="gradeCategory" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
            Category
        </label>
        <select 
            id="gradeCategory" 
            name="category" 
            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500">
            <option value="primary">Primary</option>
            <option value="secondary">Secondary</option>
            <option value="high">High School</option>
        </select>
    </div>
</x-form-modal>
```

### 3. Update/Edit Modal (with PUT method)

```blade
<x-form-modal 
    id="editBatchModal" 
    title="Edit Batch" 
    icon="fas fa-edit"
    action="{{ route('academic-management.batches.update', $batch->id) }}"
    method="PUT"
    submitText="Update Batch">
    
    <div class="space-y-1">
        <label for="editBatchName" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
            Batch Name <span class="text-red-500">*</span>
        </label>
        <input 
            type="text" 
            id="editBatchName" 
            name="name" 
            value="{{ $batch->name }}"
            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" 
            required>
    </div>
</x-form-modal>
```

## Opening the Modal

### From a Button
```html
<button type="button" onclick="openModal('batchModal')">
    <i class="fas fa-plus"></i> Add Batch
</button>
```

### From JavaScript
```javascript
openModal('batchModal');
```

## Closing the Modal

### Programmatically
```javascript
closeModal('batchModal');
```

### User Actions
- Click the X button
- Click outside the modal
- Press ESC key
- Click Cancel button

## Component Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `id` | string | 'formModal' | Unique modal identifier |
| `title` | string | 'Form' | Modal title text |
| `icon` | string | 'fas fa-plus' | FontAwesome icon class |
| `action` | string | '#' | Form submission URL |
| `method` | string | 'POST' | HTTP method (POST, PUT, PATCH, DELETE) |
| `submitText` | string | 'Save' | Submit button text |
| `cancelText` | string | 'Cancel' | Cancel button text |

## Styling Classes for Form Fields

### Input Field
```html
<input class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500">
```

### Select Field
```html
<select class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500">
```

### Textarea
```html
<textarea class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" rows="3"></textarea>
```

### Label
```html
<label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
    Field Name <span class="text-red-500">*</span>
</label>
```

## Features

### ✅ Dark Mode Support
All elements automatically adapt to dark mode using Tailwind's `dark:` variants.

### ✅ Responsive Design
- Mobile: Buttons stack vertically
- Desktop: Buttons display horizontally
- Modal width adjusts to screen size

### ✅ Accessibility
- Keyboard navigation (ESC to close)
- Focus management
- ARIA-friendly structure

### ✅ Validation Display
Add Laravel validation errors:
```blade
@error('name')
    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
@enderror
```

## Complete Example for Academic Management

```blade
<!-- In your academic-management.blade.php -->

<!-- Batch Modal -->
<x-form-modal 
    id="batchModal" 
    title="{{ __('academic_management.Create Batch') }}" 
    icon="fas fa-folder-plus"
    action="{{ route('academic-management.batches.store') }}"
    :submitText="__('academic_management.Save Batch')"
    :cancelText="__('academic_management.Cancel')">
    
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
            value="{{ old('name') }}"
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
            value="{{ old('start_date') }}"
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
            value="{{ old('end_date') }}"
            required>
        @error('end_date')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
</x-form-modal>

<!-- Grade Modal -->
<x-form-modal 
    id="gradeModal" 
    title="{{ __('academic_management.Create Grade') }}" 
    icon="fas fa-layer-group"
    action="{{ route('academic-management.grades.store') }}"
    :submitText="__('academic_management.Save Grade')">
    
    <div class="space-y-1">
        <label for="gradeLevel" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
            {{ __('academic_management.Grade Level') }} <span class="text-red-500">*</span>
        </label>
        <input 
            type="number" 
            id="gradeLevel" 
            name="level" 
            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500" 
            placeholder="{{ __('academic_management.e.g., 1, 2, 3...') }}"
            min="1"
            value="{{ old('level') }}"
            required>
        @error('level')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
</x-form-modal>
```
