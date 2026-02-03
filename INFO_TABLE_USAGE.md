# Info Table Component Usage Guide

## Overview
The `<x-info-table>` component is a reusable key-value table for displaying information in detail pages with:
- ✅ Dark theme support
- ✅ Responsive design
- ✅ Clean two-column layout (label | value)
- ✅ Optional title header
- ✅ Hover effects
- ✅ Two usage methods: Array-based or Slot-based

## Method 1: Array-Based Usage (Recommended)

### Basic Example

```blade
<x-info-table 
    :title="__('academic_management.Academic Year')"
    :rows="[
        [
            'label' => __('academic_management.Start Date'),
            'value' => optional($batch->start_date)->format('F d, Y') ?? '—'
        ],
        [
            'label' => __('academic_management.End Date'),
            'value' => optional($batch->end_date)->format('F d, Y') ?? '—'
        ],
        [
            'label' => __('academic_management.Duration'),
            'value' => $batch->start_date && $batch->end_date 
                ? $batch->start_date->diffInMonths($batch->end_date) . ' months'
                : '—'
        ],
    ]"
/>
```

### Without Title

```blade
<x-info-table 
    :rows="[
        ['label' => 'Name', 'value' => $user->name],
        ['label' => 'Email', 'value' => $user->email],
        ['label' => 'Role', 'value' => $user->role],
    ]"
/>
```

### With HTML in Values

```blade
<x-info-table 
    :title="__('Contact Information')"
    :rows="[
        [
            'label' => 'Email',
            'value' => '<a href=\'mailto:' . $user->email . '\' class=\'text-blue-600 hover:underline\'>' . $user->email . '</a>'
        ],
        [
            'label' => 'Phone',
            'value' => '<a href=\'tel:' . $user->phone . '\' class=\'text-blue-600 hover:underline\'>' . $user->phone . '</a>'
        ],
        [
            'label' => 'Status',
            'value' => '<span class=\'inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800\'>' . __('Active') . '</span>'
        ],
    ]"
/>
```

## Method 2: Slot-Based Usage (More Flexible)

### Basic Example with Slots

```blade
<x-info-table :title="__('User Information')">
    <x-info-row :label="__('Name')">
        {{ $user->name }}
    </x-info-row>
    
    <x-info-row :label="__('Email')">
        <a href="mailto:{{ $user->email }}" class="text-blue-600 hover:underline">
            {{ $user->email }}
        </a>
    </x-info-row>
    
    <x-info-row :label="__('Status')">
        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-100">
            {{ __('Active') }}
        </span>
    </x-info-row>
</x-info-table>
```

### With Complex Content

```blade
<x-info-table :title="__('Batch Details')">
    <x-info-row :label="__('Start Date')">
        {{ optional($batch->start_date)->format('F d, Y') ?? '—' }}
    </x-info-row>
    
    <x-info-row :label="__('End Date')">
        {{ optional($batch->end_date)->format('F d, Y') ?? '—' }}
    </x-info-row>
    
    <x-info-row :label="__('Duration')">
        @if($batch->start_date && $batch->end_date)
            <div class="flex items-center gap-2">
                <i class="fas fa-calendar text-gray-400"></i>
                <span>{{ $batch->start_date->diffInMonths($batch->end_date) }} months</span>
            </div>
        @else
            —
        @endif
    </x-info-row>
    
    <x-info-row :label="__('Students')">
        <div class="flex items-center gap-2">
            <span class="text-lg font-bold text-blue-600">{{ $batch->students_count }}</span>
            <span class="text-sm text-gray-500">enrolled</span>
        </div>
    </x-info-row>
</x-info-table>
```

## Component Props

### `<x-info-table>`

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `title` | string | '' | Optional table title (shown in header) |
| `rows` | array | [] | Array of rows with 'label' and 'value' keys |

### `<x-info-row>`

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `label` | string | '' | Row label (left column) |
| `slot` | mixed | - | Row value content (right column) |

## Complete Examples

### 1. Batch Detail (Academic Year)

```blade
<x-info-table 
    :title="__('academic_management.Academic Year')"
    :rows="[
        [
            'label' => __('academic_management.Start Date'),
            'value' => optional($batch->start_date)->format('F d, Y') ?? '—'
        ],
        [
            'label' => __('academic_management.End Date'),
            'value' => optional($batch->end_date)->format('F d, Y') ?? '—'
        ],
        [
            'label' => __('academic_management.Duration'),
            'value' => $batch->start_date && $batch->end_date 
                ? $batch->start_date->diffInMonths($batch->end_date) . ' ' . __('academic_management.months')
                : '—'
        ],
    ]"
/>
```

### 2. Statistics Table

```blade
<x-info-table 
    :title="__('academic_management.Statistics')"
    :rows="[
        [
            'label' => __('academic_management.Total Grades'),
            'value' => $batch->grades->count() ?? 0
        ],
        [
            'label' => __('academic_management.Total Classes'),
            'value' => $batch->grades->sum(function($grade) { return $grade->classes->count(); }) ?? 0
        ],
        [
            'label' => __('academic_management.Total Students'),
            'value' => $batch->students_count ?? 0
        ],
    ]"
/>
```

### 3. User Profile Information

```blade
<x-info-table :title="__('Profile Information')">
    <x-info-row :label="__('Full Name')">
        {{ $user->name }}
    </x-info-row>
    
    <x-info-row :label="__('Email')">
        <a href="mailto:{{ $user->email }}" class="text-blue-600 hover:text-blue-700 dark:text-blue-400">
            {{ $user->email }}
        </a>
    </x-info-row>
    
    <x-info-row :label="__('Phone')">
        {{ $user->phone ?? '—' }}
    </x-info-row>
    
    <x-info-row :label="__('Role')">
        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-100">
            {{ $user->role }}
        </span>
    </x-info-row>
    
    <x-info-row :label="__('Member Since')">
        {{ $user->created_at->format('F d, Y') }}
    </x-info-row>
</x-info-table>
```

### 4. Grade Information

```blade
<x-info-table :title="__('Grade Details')">
    <x-info-row :label="__('Grade Level')">
        <span class="text-lg font-bold">{{ __('Grade') }} {{ $grade->level }}</span>
    </x-info-row>
    
    <x-info-row :label="__('Batch')">
        @if($grade->batch)
            <a href="{{ route('academic-management.batches.show', $grade->batch->id) }}" class="text-blue-600 hover:underline">
                {{ $grade->batch->name }}
            </a>
        @else
            —
        @endif
    </x-info-row>
    
    <x-info-row :label="__('Category')">
        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-100">
            {{ __('Primary') }}
        </span>
    </x-info-row>
    
    <x-info-row :label="__('Total Classes')">
        <div class="flex items-center gap-2">
            <i class="fas fa-chalkboard text-gray-400"></i>
            <span>{{ $grade->classes->count() }}</span>
        </div>
    </x-info-row>
    
    <x-info-row :label="__('Total Students')">
        <div class="flex items-center gap-2">
            <i class="fas fa-users text-gray-400"></i>
            <span>{{ $grade->students->count() }}</span>
        </div>
    </x-info-row>
</x-info-table>
```

### 5. Room Information

```blade
<x-info-table 
    :title="__('Room Details')"
    :rows="[
        [
            'label' => __('Room Number'),
            'value' => $room->name
        ],
        [
            'label' => __('Building'),
            'value' => $room->building ?? 'Building A'
        ],
        [
            'label' => __('Floor'),
            'value' => $room->floor ?? '1st Floor'
        ],
        [
            'label' => __('Capacity'),
            'value' => $room->capacity ?? '—'
        ],
        [
            'label' => __('Status'),
            'value' => '<span class=\'inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-100\'>' . ucfirst($room->status ?? 'Available') . '</span>'
        ],
    ]"
/>
```

### 6. Subject Information with Links

```blade
<x-info-table :title="__('Subject Details')">
    <x-info-row :label="__('Subject Code')">
        <span class="font-mono font-bold">{{ $subject->code }}</span>
    </x-info-row>
    
    <x-info-row :label="__('Subject Name')">
        {{ $subject->name }}
    </x-info-row>
    
    <x-info-row :label="__('Category')">
        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-100">
            {{ ucfirst($subject->category ?? 'Core') }}
        </span>
    </x-info-row>
    
    <x-info-row :label="__('Grade')">
        @if($subject->grade)
            <a href="{{ route('academic-management.grades.show', $subject->grade->id) }}" class="text-blue-600 hover:underline">
                {{ __('Grade') }} {{ $subject->grade->level }}
            </a>
        @else
            —
        @endif
    </x-info-row>
    
    <x-info-row :label="__('Teachers')">
        {{ $subject->teachers_count ?? 0 }}
    </x-info-row>
</x-info-table>
```

### 7. Multiple Tables in Sequence

```blade
<!-- Personal Information -->
<x-info-table :title="__('Personal Information')">
    <x-info-row :label="__('Name')">{{ $student->name }}</x-info-row>
    <x-info-row :label="__('Date of Birth')">{{ $student->dob->format('F d, Y') }}</x-info-row>
    <x-info-row :label="__('Gender')">{{ $student->gender }}</x-info-row>
</x-info-table>

<!-- Academic Information -->
<x-info-table :title="__('Academic Information')">
    <x-info-row :label="__('Student ID')">{{ $student->student_id }}</x-info-row>
    <x-info-row :label="__('Grade')">{{ __('Grade') }} {{ $student->grade->level }}</x-info-row>
    <x-info-row :label="__('Class')">{{ $student->class->name }}</x-info-row>
</x-info-table>

<!-- Contact Information -->
<x-info-table :title="__('Contact Information')">
    <x-info-row :label="__('Email')">
        <a href="mailto:{{ $student->email }}" class="text-blue-600 hover:underline">
            {{ $student->email }}
        </a>
    </x-info-row>
    <x-info-row :label="__('Phone')">{{ $student->phone }}</x-info-row>
    <x-info-row :label="__('Address')">{{ $student->address }}</x-info-row>
</x-info-table>
```

## Features

### ✅ Dark Mode Support
All colors automatically adapt to dark mode using Tailwind's `dark:` variants.

### ✅ Responsive Design
- Table scrolls horizontally on small screens
- Label column has fixed width (200px)
- Value column expands to fill remaining space

### ✅ Hover Effects
Rows have subtle hover effects for better interactivity.

### ✅ HTML Support
Values can contain HTML for links, badges, icons, and formatted content.

### ✅ Empty State
Shows "No data available" message when no rows are provided.

### ✅ Flexible Usage
Choose between array-based (quick) or slot-based (flexible) usage.

## When to Use Which Method

### Use Array-Based When:
- Simple key-value pairs
- Data is already in array format
- Quick implementation needed
- Values are mostly text

### Use Slot-Based When:
- Complex HTML in values
- Conditional rendering needed
- Multiple components per row
- Need full Blade syntax

## Styling

The component uses Tailwind CSS classes and is fully responsive. All colors support dark mode out of the box.

### Container
- White background with dark mode support
- Border and shadow
- Rounded corners

### Header (Optional)
- Gray background
- Bold title text
- Bottom border

### Table
- Full width
- Divided rows
- Hover effects

### Label Column
- Fixed 200px width
- Semi-bold text
- Gray color
- No wrap

### Value Column
- Flexible width
- Regular text
- Dark text color
- Supports HTML

## Tips

1. **Use `—` for empty values** instead of empty strings for better UX
2. **Add icons** to values for visual interest
3. **Use badges** for status indicators
4. **Add links** to related resources
5. **Format dates** consistently across your app
6. **Group related information** in separate tables
