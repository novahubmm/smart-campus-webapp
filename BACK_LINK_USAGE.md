# Back Link Component Usage Guide

## Overview
The `<x-back-link>` component is a reusable navigation link for returning to previous pages with:
- ✅ Dark theme support
- ✅ Responsive design
- ✅ Icon support
- ✅ Hover effects
- ✅ Consistent styling
- ✅ Accessible design

## Basic Usage

### Simple Back Link

```blade
<x-back-link 
    :href="route('academic-management.index', ['tab' => 'batches'])"
    :text="__('academic_management.Back to Academic Management')"
/>
```

### Back Link with Custom Icon

```blade
<x-back-link 
    :href="route('dashboard')"
    :text="__('Back to Dashboard')"
    icon="fas fa-home"
/>
```

### Back Link with Simple Text

```blade
<x-back-link 
    href="/users"
    text="Back to Users"
/>
```

## Component Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `href` | string | '#' | URL to navigate to |
| `text` | string | 'Back' | Link text to display |
| `icon` | string | 'fas fa-arrow-left' | FontAwesome icon class |

## Complete Examples

### 1. Batch Detail Page

```blade
<x-app-layout>
    <x-slot name="header">
        <x-page-header
            icon="fas fa-calendar-alt"
            :subtitle="__('academic_management.Academic Management')"
            :title="__('academic_management.Batch Details')"
        />
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-back-link 
                :href="route('academic-management.index', ['tab' => 'batches'])"
                :text="__('academic_management.Back to Academic Management')"
            />
            
            <!-- Page content -->
        </div>
    </div>
</x-app-layout>
```

### 2. Grade Detail Page

```blade
<x-back-link 
    :href="route('academic-management.index', ['tab' => 'grades'])"
    :text="__('academic_management.Back to Academic Management')"
/>
```

### 3. Class Detail Page

```blade
<x-back-link 
    :href="route('academic-management.index', ['tab' => 'classes'])"
    :text="__('academic_management.Back to Academic Management')"
/>
```

### 4. Room Detail Page

```blade
<x-back-link 
    :href="route('academic-management.index', ['tab' => 'rooms'])"
    :text="__('academic_management.Back to Academic Management')"
/>
```

### 5. Subject Detail Page

```blade
<x-back-link 
    :href="route('academic-management.index', ['tab' => 'subjects'])"
    :text="__('academic_management.Back to Academic Management')"
/>
```

### 6. User Profile Page

```blade
<x-back-link 
    :href="route('users.index')"
    :text="__('Back to All Users')"
/>
```

### 7. Document Detail Page

```blade
<x-back-link 
    :href="route('documents.index')"
    :text="__('Back to Documents')"
    icon="fas fa-folder"
/>
```

### 8. Event Detail Page

```blade
<x-back-link 
    :href="route('events.index')"
    :text="__('Back to Events Calendar')"
    icon="fas fa-calendar"
/>
```

### 9. Settings Page

```blade
<x-back-link 
    :href="route('dashboard')"
    :text="__('Back to Dashboard')"
    icon="fas fa-home"
/>
```

### 10. Report Detail Page

```blade
<x-back-link 
    :href="route('reports.index')"
    :text="__('Back to All Reports')"
    icon="fas fa-chart-bar"
/>
```

### 11. With Query Parameters

```blade
<x-back-link 
    :href="route('academic-management.index', ['tab' => 'batches', 'filter' => 'active'])"
    :text="__('Back to Active Batches')"
/>
```

### 12. With Previous URL

```blade
<x-back-link 
    :href="url()->previous()"
    :text="__('Go Back')"
/>
```

## Icon Options

### Common Icons

```blade
<!-- Arrow Left (Default) -->
icon="fas fa-arrow-left"

<!-- Home -->
icon="fas fa-home"

<!-- Chevron Left -->
icon="fas fa-chevron-left"

<!-- Long Arrow Left -->
icon="fas fa-long-arrow-alt-left"

<!-- Angle Left -->
icon="fas fa-angle-left"

<!-- Caret Left -->
icon="fas fa-caret-left"

<!-- Arrow Circle Left -->
icon="fas fa-arrow-circle-left"

<!-- Folder -->
icon="fas fa-folder"

<!-- List -->
icon="fas fa-list"

<!-- Grid -->
icon="fas fa-th"
```

## Features

### ✅ Dark Mode Support
Automatically adapts to dark mode with proper color schemes:
- Light mode: Gray background with darker text
- Dark mode: Dark gray background with lighter text

### ✅ Responsive Design
- Works well on all screen sizes
- Touch-friendly on mobile devices
- Proper spacing and padding

### ✅ Hover Effects
- Background color changes on hover
- Smooth transition animation
- Visual feedback for user interaction

### ✅ Accessible
- Semantic HTML structure
- Proper link element
- Icon and text for clarity

### ✅ Consistent Styling
- Matches application design system
- Uses Tailwind CSS classes
- Consistent with other components

## Styling Details

### Container
- Bottom margin: 24px (mb-6)
- Ensures proper spacing from content below

### Link
- Display: Inline flex
- Alignment: Items centered
- Gap: 8px (gap-2) between icon and text
- Padding: 8px 16px (px-4 py-2)
- Font: Small, semi-bold (text-sm font-semibold)
- Border radius: Large (rounded-lg)
- Border: 1px solid
- Transition: All colors

### Colors (Light Mode)
- Background: Gray 50 (bg-gray-50)
- Border: Gray 200 (border-gray-200)
- Text: Gray 700 (text-gray-700)
- Hover Background: Gray 100 (hover:bg-gray-100)

### Colors (Dark Mode)
- Background: Gray 800 (dark:bg-gray-800)
- Border: Gray 700 (dark:border-gray-700)
- Text: Gray 200 (dark:text-gray-200)
- Hover Background: Gray 700 (dark:hover:bg-gray-700)

## Best Practices

1. **Always provide meaningful text** - Users should know where they're going back to
2. **Use consistent icons** - Stick to arrow-left for most cases
3. **Place at the top** - Back links should be the first element in the content area
4. **Use translation functions** - Always use `__()` for text
5. **Preserve context** - Include query parameters when needed (like tab state)
6. **Test navigation** - Ensure the back link goes to the correct page

## Common Use Cases

### Detail Pages
Use back links on all detail pages to return to the list view:
```blade
<x-back-link :href="route('items.index')" :text="__('Back to Items')" />
```

### Multi-Step Forms
Use back links to navigate between form steps:
```blade
<x-back-link :href="route('form.step1')" :text="__('Back to Step 1')" />
```

### Nested Resources
Use back links to navigate up the hierarchy:
```blade
<x-back-link :href="route('categories.show', $category)" :text="__('Back to Category')" />
```

### Settings Pages
Use back links to return to main settings:
```blade
<x-back-link :href="route('settings.index')" :text="__('Back to Settings')" />
```

## Migration from Old Back Links

### Before
```blade
<div class="navigation-breadcrumb">
    <a href="{{ route('academic-management.index', ['tab' => 'batches']) }}" class="breadcrumb-link">
        <i class="fas fa-arrow-left"></i> {{ __('academic_management.Back to Academic Management') }}
    </a>
</div>
```

### After
```blade
<x-back-link 
    :href="route('academic-management.index', ['tab' => 'batches'])"
    :text="__('academic_management.Back to Academic Management')"
/>
```

**Benefits:**
- ✅ 5 lines reduced to 3 lines
- ✅ More readable and maintainable
- ✅ Consistent styling across all pages
- ✅ Easy to update globally
- ✅ Built-in dark mode support
- ✅ Responsive by default

## Customization

### Custom Styling
If you need to customize the component, you can modify the component file at:
`scp/resources/views/components/back-link.blade.php`

### Adding Additional Props
You can extend the component with additional props:

```blade
@props([
    'href' => '#',
    'text' => 'Back',
    'icon' => 'fas fa-arrow-left',
    'class' => '', // Add custom classes
])

<div class="mb-6">
    <a href="{{ $href }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors {{ $class }}">
        <i class="{{ $icon }}"></i>
        <span>{{ $text }}</span>
    </a>
</div>
```

## Tips

1. **Keep text concise** - "Back to X" is usually sufficient
2. **Use proper routes** - Always use `route()` helper instead of hardcoded URLs
3. **Maintain state** - Pass query parameters to preserve filters, tabs, etc.
4. **Test on mobile** - Ensure the link is easily tappable on touch devices
5. **Consider breadcrumbs** - For complex navigation, consider using breadcrumbs instead
