# Detail Header Component Usage Guide

## Overview
The `<x-detail-header>` component is a reusable large stat card for detail pages with:
- ✅ Dark theme support
- ✅ Responsive design (mobile-friendly)
- ✅ Icon with customizable colors
- ✅ Title and subtitle
- ✅ Status badges with color variants
- ✅ Edit and Delete action buttons
- ✅ Confirmation dialog integration
- ✅ Flexible layout

## Basic Usage

### Simple Header with Edit and Delete

```blade
<x-detail-header
    icon="fas fa-calendar-alt"
    iconBg="bg-blue-50 dark:bg-blue-900/30"
    iconColor="text-blue-600 dark:text-blue-400"
    :title="$batch->name"
    :subtitle="__('academic_management.Academic Year') . ' ' . $batch->name"
    :badge="__('academic_management.Active')"
    badgeColor="active"
    :editRoute="route('academic-management.batches.edit', $batch->id)"
    :editText="__('academic_management.Edit Batch')"
    :deleteRoute="route('academic-management.batches.destroy', $batch->id)"
    :deleteText="__('academic_management.Delete Batch')"
    :deleteTitle="__('academic_management.Delete Batch')"
    :deleteMessage="__('academic_management.Are you sure you want to delete this batch? This action cannot be undone.')"
/>
```

## Component Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `icon` | string | 'fas fa-circle' | FontAwesome icon class |
| `iconBg` | string | 'bg-blue-50 dark:bg-blue-900/30' | Icon background color classes |
| `iconColor` | string | 'text-blue-600 dark:text-blue-400' | Icon text color classes |
| `title` | string | '' | Main title text |
| `subtitle` | string | '' | Subtitle text below title |
| `badge` | string\|null | null | Badge text (optional) |
| `badgeColor` | string | 'active' | Badge color variant |
| `editRoute` | string\|null | null | Edit button URL (optional) |
| `editText` | string | 'Edit' | Edit button text |
| `deleteRoute` | string\|null | null | Delete form action URL (optional) |
| `deleteText` | string | 'Delete' | Delete button text |
| `deleteTitle` | string | 'Confirm Delete' | Delete confirmation title |
| `deleteMessage` | string | 'Are you sure...' | Delete confirmation message |

## Badge Color Variants

The `badgeColor` prop supports these variants:

- **active**: Green badge (for active/enabled status)
- **inactive**: Gray badge (for inactive/disabled status)
- **pending**: Amber badge (for pending/warning status)
- **completed**: Blue badge (for completed status)
- Any other value: Gray badge (default)

## Icon Color Combinations

### Blue (Default)
```blade
iconBg="bg-blue-50 dark:bg-blue-900/30"
iconColor="text-blue-600 dark:text-blue-400"
```

### Purple
```blade
iconBg="bg-purple-50 dark:bg-purple-900/30"
iconColor="text-purple-600 dark:text-purple-400"
```

### Green
```blade
iconBg="bg-green-50 dark:bg-green-900/30"
iconColor="text-green-600 dark:text-green-400"
```

### Amber
```blade
iconBg="bg-amber-50 dark:bg-amber-900/30"
iconColor="text-amber-600 dark:text-amber-400"
```

### Indigo
```blade
iconBg="bg-indigo-50 dark:bg-indigo-900/30"
iconColor="text-indigo-600 dark:text-indigo-400"
```

### Red
```blade
iconBg="bg-red-50 dark:bg-red-900/30"
iconColor="text-red-600 dark:text-red-400"
```

## Examples

### 1. Batch Detail Header

```blade
<x-detail-header
    icon="fas fa-calendar-alt"
    iconBg="bg-blue-50 dark:bg-blue-900/30"
    iconColor="text-blue-600 dark:text-blue-400"
    :title="$batch->name"
    :subtitle="__('academic_management.Academic Year') . ' ' . $batch->name"
    :badge="__('academic_management.Active')"
    badgeColor="active"
    :editRoute="route('academic-management.batches.edit', $batch->id)"
    :editText="__('academic_management.Edit Batch')"
    :deleteRoute="route('academic-management.batches.destroy', $batch->id)"
    :deleteText="__('academic_management.Delete Batch')"
    :deleteTitle="__('academic_management.Delete Batch')"
    :deleteMessage="__('academic_management.Are you sure you want to delete this batch? This action cannot be undone.')"
/>
```

### 2. Grade Detail Header

```blade
<x-detail-header
    icon="fas fa-layer-group"
    iconBg="bg-purple-50 dark:bg-purple-900/30"
    iconColor="text-purple-600 dark:text-purple-400"
    :title="__('academic_management.Grade') . ' ' . $grade->level"
    :subtitle="__('academic_management.Batch') . ': ' . ($grade->batch->name ?? '—')"
    :badge="__('academic_management.Primary')"
    badgeColor="active"
    :editRoute="route('academic-management.grades.edit', $grade->id)"
    :editText="__('academic_management.Edit Grade')"
    :deleteRoute="route('academic-management.grades.destroy', $grade->id)"
    :deleteText="__('academic_management.Delete Grade')"
    :deleteTitle="__('academic_management.Delete Grade')"
    :deleteMessage="__('academic_management.Are you sure you want to delete this grade? This action cannot be undone.')"
/>
```

### 3. Class Detail Header

```blade
<x-detail-header
    icon="fas fa-chalkboard"
    iconBg="bg-green-50 dark:bg-green-900/30"
    iconColor="text-green-600 dark:text-green-400"
    :title="$class->name"
    :subtitle="__('academic_management.Grade') . ' ' . ($class->grade->level ?? '—') . ' • ' . ($class->room->name ?? __('academic_management.No Room'))"
    :badge="__('academic_management.Active')"
    badgeColor="active"
    :editRoute="route('academic-management.classes.edit', $class->id)"
    :editText="__('academic_management.Edit Class')"
    :deleteRoute="route('academic-management.classes.destroy', $class->id)"
    :deleteText="__('academic_management.Delete Class')"
    :deleteTitle="__('academic_management.Delete Class')"
    :deleteMessage="__('academic_management.Are you sure you want to delete this class? This action cannot be undone.')"
/>
```

### 4. Room Detail Header

```blade
<x-detail-header
    icon="fas fa-door-open"
    iconBg="bg-amber-50 dark:bg-amber-900/30"
    iconColor="text-amber-600 dark:text-amber-400"
    :title="$room->name"
    :subtitle="($room->building ?? 'Building A') . ' • ' . ($room->floor ?? '1st Floor')"
    :badge="ucfirst($room->status ?? 'Available')"
    badgeColor="active"
    :editRoute="route('academic-management.rooms.edit', $room->id)"
    :editText="__('academic_management.Edit Room')"
    :deleteRoute="route('academic-management.rooms.destroy', $room->id)"
    :deleteText="__('academic_management.Delete Room')"
    :deleteTitle="__('academic_management.Delete Room')"
    :deleteMessage="__('academic_management.Are you sure you want to delete this room? This action cannot be undone.')"
/>
```

### 5. Subject Detail Header

```blade
<x-detail-header
    icon="fas fa-book"
    iconBg="bg-indigo-50 dark:bg-indigo-900/30"
    iconColor="text-indigo-600 dark:text-indigo-400"
    :title="$subject->name"
    :subtitle="__('academic_management.Code') . ': ' . $subject->code"
    :badge="ucfirst($subject->category ?? 'Core')"
    badgeColor="pending"
    :editRoute="route('academic-management.subjects.edit', $subject->id)"
    :editText="__('academic_management.Edit Subject')"
    :deleteRoute="route('academic-management.subjects.destroy', $subject->id)"
    :deleteText="__('academic_management.Delete Subject')"
    :deleteTitle="__('academic_management.Delete Subject')"
    :deleteMessage="__('academic_management.Are you sure you want to delete this subject? This action cannot be undone.')"
/>
```

### 6. Header with Only Edit Button (No Delete)

```blade
<x-detail-header
    icon="fas fa-user"
    iconBg="bg-blue-50 dark:bg-blue-900/30"
    iconColor="text-blue-600 dark:text-blue-400"
    :title="$user->name"
    :subtitle="$user->email"
    :badge="__('Active')"
    badgeColor="active"
    :editRoute="route('users.edit', $user->id)"
    :editText="__('Edit Profile')"
/>
```

### 7. Header with Custom Meta Slot

```blade
<x-detail-header
    icon="fas fa-calendar-alt"
    iconBg="bg-blue-50 dark:bg-blue-900/30"
    iconColor="text-blue-600 dark:text-blue-400"
    :title="$event->name"
    :subtitle="$event->date->format('F d, Y')"
    :badge="__('Upcoming')"
    badgeColor="pending"
    :editRoute="route('events.edit', $event->id)"
    :deleteRoute="route('events.destroy', $event->id)">
    
    <x-slot name="meta">
        <span class="text-sm text-gray-600 dark:text-gray-400">
            <i class="fas fa-users"></i> {{ $event->attendees_count }} attendees
        </span>
        <span class="text-sm text-gray-600 dark:text-gray-400">
            <i class="fas fa-map-marker-alt"></i> {{ $event->location }}
        </span>
    </x-slot>
</x-detail-header>
```

### 8. Header with Custom Actions Slot

```blade
<x-detail-header
    icon="fas fa-file"
    iconBg="bg-blue-50 dark:bg-blue-900/30"
    iconColor="text-blue-600 dark:text-blue-400"
    :title="$document->name"
    :subtitle="$document->type"
    :badge="__('Published')"
    badgeColor="completed">
    
    <x-slot name="actions">
        <a href="{{ route('documents.download', $document->id) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            <i class="fas fa-download"></i>
            <span>{{ __('Download') }}</span>
        </a>
        <a href="{{ route('documents.share', $document->id) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors">
            <i class="fas fa-share"></i>
            <span>{{ __('Share') }}</span>
        </a>
    </x-slot>
</x-detail-header>
```

## Features

### ✅ Dark Mode Support
All colors automatically adapt to dark mode using Tailwind's `dark:` variants.

### ✅ Responsive Design
- **Mobile**: Icon and content stack vertically, buttons stack vertically
- **Tablet**: Icon and content side-by-side, buttons stack vertically
- **Desktop**: Full horizontal layout with all elements side-by-side

### ✅ Confirmation Dialog Integration
Delete button automatically integrates with your existing `<x-confirm-dialog>` component using Alpine.js events.

### ✅ Flexible Slots
- **meta**: Add custom metadata next to the badge
- **actions**: Replace default edit/delete buttons with custom actions

## Styling

The component uses Tailwind CSS classes and is fully responsive. All colors support dark mode out of the box.

### Container
- White background with dark mode support
- Border and shadow
- Rounded corners
- Responsive padding

### Icon
- 56x56px (w-14 h-14)
- Rounded corners
- Customizable background and text colors

### Title
- Large, bold text (text-2xl font-bold)
- Dark mode support

### Subtitle
- Small, muted text
- Dark mode support

### Badge
- Rounded pill shape
- Color variants for different statuses
- Dark mode support

### Action Buttons
- Edit: Blue color scheme
- Delete: Red color scheme
- Hover effects
- Responsive sizing
