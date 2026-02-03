# Page Header Component Usage Guide

## Overview
The `<x-page-header>` component is a reusable header for the `<x-slot name="header">` section with:
- ✅ Dark theme support
- ✅ Icon with customizable colors
- ✅ Subtitle (breadcrumb/category)
- ✅ Main title
- ✅ Customizable text colors
- ✅ Consistent spacing and layout

## Basic Usage

### Simple Header

```blade
<x-slot name="header">
    <x-page-header
        icon="fas fa-calendar-alt"
        iconBg="bg-blue-50 dark:bg-blue-900/30"
        iconColor="text-blue-700 dark:text-blue-200"
        :subtitle="__('academic_management.Academic Management')"
        :title="__('academic_management.Batch Details')"
    />
</x-slot>
```

### Header Without Subtitle

```blade
<x-slot name="header">
    <x-page-header
        icon="fas fa-home"
        :title="__('Dashboard')"
    />
</x-slot>
```

### Header with Custom Colors

```blade
<x-slot name="header">
    <x-page-header
        icon="fas fa-users"
        iconBg="bg-green-50 dark:bg-green-900/30"
        iconColor="text-green-700 dark:text-green-200"
        :subtitle="__('User Management')"
        subtitleColor="text-green-600 dark:text-green-400"
        :title="__('All Users')"
        titleColor="text-green-900 dark:text-green-100"
    />
</x-slot>
```

## Component Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `icon` | string | 'fas fa-circle' | FontAwesome icon class |
| `iconBg` | string | 'bg-blue-50 dark:bg-blue-900/30' | Icon background color classes |
| `iconColor` | string | 'text-blue-700 dark:text-blue-200' | Icon text color classes |
| `subtitle` | string | '' | Subtitle text (breadcrumb/category) |
| `subtitleColor` | string | 'text-gray-500 dark:text-gray-400' | Subtitle text color classes |
| `title` | string | '' | Main title text |
| `titleColor` | string | 'text-gray-800 dark:text-gray-200' | Title text color classes |

## Icon Color Combinations

### Blue (Default)
```blade
iconBg="bg-blue-50 dark:bg-blue-900/30"
iconColor="text-blue-700 dark:text-blue-200"
```

### Purple
```blade
iconBg="bg-purple-50 dark:bg-purple-900/30"
iconColor="text-purple-700 dark:text-purple-200"
```

### Green
```blade
iconBg="bg-green-50 dark:bg-green-900/30"
iconColor="text-green-700 dark:text-green-200"
```

### Amber
```blade
iconBg="bg-amber-50 dark:bg-amber-900/30"
iconColor="text-amber-700 dark:text-amber-200"
```

### Indigo
```blade
iconBg="bg-indigo-50 dark:bg-indigo-900/30"
iconColor="text-indigo-700 dark:text-indigo-200"
```

### Red
```blade
iconBg="bg-red-50 dark:bg-red-900/30"
iconColor="text-red-700 dark:text-red-200"
```

### Teal
```blade
iconBg="bg-teal-50 dark:bg-teal-900/30"
iconColor="text-teal-700 dark:text-teal-200"
```

### Pink
```blade
iconBg="bg-pink-50 dark:bg-pink-900/30"
iconColor="text-pink-700 dark:text-pink-200"
```

## Complete Examples

### 1. Batch Detail Page

```blade
<x-app-layout>
    <x-slot name="header">
        <x-page-header
            icon="fas fa-calendar-alt"
            iconBg="bg-blue-50 dark:bg-blue-900/30"
            iconColor="text-blue-700 dark:text-blue-200"
            :subtitle="__('academic_management.Academic Management')"
            :title="__('academic_management.Batch Details')"
        />
    </x-slot>
    
    <!-- Page content -->
</x-app-layout>
```

### 2. Grade Detail Page

```blade
<x-app-layout>
    <x-slot name="header">
        <x-page-header
            icon="fas fa-layer-group"
            iconBg="bg-purple-50 dark:bg-purple-900/30"
            iconColor="text-purple-700 dark:text-purple-200"
            :subtitle="__('academic_management.Academic Management')"
            :title="__('academic_management.Grade Details') . ': ' . __('academic_management.Grade') . ' ' . $grade->level"
        />
    </x-slot>
    
    <!-- Page content -->
</x-app-layout>
```

### 3. Class Detail Page

```blade
<x-app-layout>
    <x-slot name="header">
        <x-page-header
            icon="fas fa-chalkboard"
            iconBg="bg-green-50 dark:bg-green-900/30"
            iconColor="text-green-700 dark:text-green-200"
            :subtitle="__('academic_management.Academic Management')"
            :title="__('academic_management.Class Details') . ': ' . $class->name"
        />
    </x-slot>
    
    <!-- Page content -->
</x-app-layout>
```

### 4. Room Detail Page

```blade
<x-app-layout>
    <x-slot name="header">
        <x-page-header
            icon="fas fa-door-open"
            iconBg="bg-amber-50 dark:bg-amber-900/30"
            iconColor="text-amber-700 dark:text-amber-200"
            :subtitle="__('academic_management.Academic Management')"
            :title="__('academic_management.Room Details')"
        />
    </x-slot>
    
    <!-- Page content -->
</x-app-layout>
```

### 5. Subject Detail Page

```blade
<x-app-layout>
    <x-slot name="header">
        <x-page-header
            icon="fas fa-book"
            iconBg="bg-indigo-50 dark:bg-indigo-900/30"
            iconColor="text-indigo-700 dark:text-indigo-200"
            :subtitle="__('academic_management.Academic Management')"
            :title="__('academic_management.Subject Details')"
        />
    </x-slot>
    
    <!-- Page content -->
</x-app-layout>
```

### 6. User Profile Page

```blade
<x-app-layout>
    <x-slot name="header">
        <x-page-header
            icon="fas fa-user"
            iconBg="bg-blue-50 dark:bg-blue-900/30"
            iconColor="text-blue-700 dark:text-blue-200"
            :subtitle="__('User Management')"
            :title="$user->name"
        />
    </x-slot>
    
    <!-- Page content -->
</x-app-layout>
```

### 7. Dashboard Page (No Subtitle)

```blade
<x-app-layout>
    <x-slot name="header">
        <x-page-header
            icon="fas fa-home"
            iconBg="bg-blue-50 dark:bg-blue-900/30"
            iconColor="text-blue-700 dark:text-blue-200"
            :title="__('Dashboard')"
        />
    </x-slot>
    
    <!-- Page content -->
</x-app-layout>
```

### 8. Settings Page

```blade
<x-app-layout>
    <x-slot name="header">
        <x-page-header
            icon="fas fa-cog"
            iconBg="bg-gray-50 dark:bg-gray-900/30"
            iconColor="text-gray-700 dark:text-gray-200"
            :subtitle="__('System')"
            :title="__('Settings')"
        />
    </x-slot>
    
    <!-- Page content -->
</x-app-layout>
```

### 9. Reports Page

```blade
<x-app-layout>
    <x-slot name="header">
        <x-page-header
            icon="fas fa-chart-bar"
            iconBg="bg-teal-50 dark:bg-teal-900/30"
            iconColor="text-teal-700 dark:text-teal-200"
            :subtitle="__('Analytics')"
            :title="__('Reports & Statistics')"
        />
    </x-slot>
    
    <!-- Page content -->
</x-app-layout>
```

### 10. Events Page

```blade
<x-app-layout>
    <x-slot name="header">
        <x-page-header
            icon="fas fa-calendar-check"
            iconBg="bg-pink-50 dark:bg-pink-900/30"
            iconColor="text-pink-700 dark:text-pink-200"
            :subtitle="__('School Activities')"
            :title="__('Events Calendar')"
        />
    </x-slot>
    
    <!-- Page content -->
</x-app-layout>
```

### 11. Dynamic Title with Variable

```blade
<x-app-layout>
    <x-slot name="header">
        <x-page-header
            icon="fas fa-file-alt"
            iconBg="bg-blue-50 dark:bg-blue-900/30"
            iconColor="text-blue-700 dark:text-blue-200"
            :subtitle="__('Documents')"
            :title="__('Viewing: ') . $document->name"
        />
    </x-slot>
    
    <!-- Page content -->
</x-app-layout>
```

### 12. Custom Text Colors

```blade
<x-app-layout>
    <x-slot name="header">
        <x-page-header
            icon="fas fa-exclamation-triangle"
            iconBg="bg-red-50 dark:bg-red-900/30"
            iconColor="text-red-700 dark:text-red-200"
            :subtitle="__('System')"
            subtitleColor="text-red-600 dark:text-red-400"
            :title="__('Critical Alerts')"
            titleColor="text-red-900 dark:text-red-100"
        />
    </x-slot>
    
    <!-- Page content -->
</x-app-layout>
```

## Features

### ✅ Dark Mode Support
All colors automatically adapt to dark mode using Tailwind's `dark:` variants.

### ✅ Consistent Layout
- Icon: 40x40px (w-10 h-10) with rounded corners
- Subtitle: Extra small text (text-xs)
- Title: Large, semi-bold text (text-xl font-semibold)
- Gap: 12px (gap-3) between icon and text

### ✅ Flexible Colors
All colors can be customized via props while maintaining dark mode support.

### ✅ Optional Subtitle
Subtitle can be omitted for simpler headers.

### ✅ Responsive
Works well on all screen sizes.

## Color Scheme Recommendations

### By Module

- **Academic Management**: Blue
- **User Management**: Green
- **Financial**: Amber
- **Reports**: Teal
- **Events**: Pink
- **Settings**: Gray
- **Alerts**: Red
- **Documents**: Indigo
- **Communication**: Purple

### By Action

- **View/Read**: Blue
- **Create/Add**: Green
- **Edit/Update**: Amber
- **Delete/Remove**: Red
- **Archive**: Gray
- **Analytics**: Teal

## Best Practices

1. **Use consistent colors** for the same module across all pages
2. **Keep subtitles short** - they're meant to be breadcrumbs or categories
3. **Use descriptive icons** that match the page content
4. **Maintain dark mode support** by always using `dark:` variants
5. **Use translation functions** for all text content
6. **Keep titles concise** - they should fit on one line on mobile

## Styling

The component uses Tailwind CSS classes and is fully responsive. All colors support dark mode out of the box.

### Icon Container
- 40x40px square
- Rounded corners (rounded-xl)
- Customizable background and text colors
- Centered icon

### Text Container
- Flexible width
- Stacked layout (subtitle above title)
- Tight line height for title

### Subtitle
- Extra small text (text-xs)
- Muted color by default
- Optional (can be omitted)

### Title
- Large text (text-xl)
- Semi-bold weight (font-semibold)
- Tight leading (leading-tight)
- Dark text by default

## Migration from Old Headers

### Before
```blade
<x-slot name="header">
    <div class="flex items-center gap-3">
        <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-200">
            <i class="fas fa-calendar-alt"></i>
        </span>
        <div>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('academic_management.Academic Management') }}</p>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('academic_management.Batch Details') }}</h2>
        </div>
    </div>
</x-slot>
```

### After
```blade
<x-slot name="header">
    <x-page-header
        icon="fas fa-calendar-alt"
        iconBg="bg-blue-50 dark:bg-blue-900/30"
        iconColor="text-blue-700 dark:text-blue-200"
        :subtitle="__('academic_management.Academic Management')"
        :title="__('academic_management.Batch Details')"
    />
</x-slot>
```

**Benefits:**
- ✅ 12 lines reduced to 7 lines
- ✅ More readable and maintainable
- ✅ Consistent across all pages
- ✅ Easy to update globally
- ✅ Type-safe props
