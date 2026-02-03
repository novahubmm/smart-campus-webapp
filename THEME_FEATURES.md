# Smart Campus - Theme Features

## Overview

This document describes the dark mode and multi-language features implemented in the Smart Campus project following the Nova Hub Developer Guide best practices.

## 🌗 Dark Mode Implementation

### Features

-   **System-aware**: Automatically detects user's OS dark mode preference
-   **Persistent**: User's choice is saved to localStorage
-   **Smooth transitions**: 200ms transition for color changes
-   **Toggle available**: Moon/Sun icon button in navigation and auth pages

### Technical Implementation

#### 1. Tailwind Configuration

```javascript
// tailwind.config.js
export default {
    darkMode: "class", // Class-based dark mode
    // ... rest of config
};
```

#### 2. Alpine.js State Management

```html
<!-- Layout wrapper -->
<html
    x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }"
    x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))"
    :class="{ 'dark': darkMode }"
></html>
```

#### 3. Pre-render Dark Mode Detection

```html
<script>
    // Prevent flash of wrong theme
    if (
        localStorage.getItem("darkMode") === "true" ||
        (!("darkMode" in localStorage) &&
            window.matchMedia("(prefers-color-scheme: dark)").matches)
    ) {
        document.documentElement.classList.add("dark");
    }
</script>
```

#### 4. Dark Mode Toggle Button

```html
<button @click="darkMode = !darkMode" class="p-2 rounded-lg...">
    <svg x-show="!darkMode"><!-- Moon icon --></svg>
    <svg x-show="darkMode"><!-- Sun icon --></svg>
</button>
```

### Dark Mode Color Palette

| Element        | Light Mode        | Dark Mode         |
| -------------- | ----------------- | ----------------- |
| Background     | `bg-gray-50`      | `bg-gray-900`     |
| Cards          | `bg-white`        | `bg-gray-800`     |
| Text Primary   | `text-gray-900`   | `text-white`      |
| Text Secondary | `text-gray-600`   | `text-gray-400`   |
| Borders        | `border-gray-200` | `border-gray-700` |
| Inputs         | `bg-white`        | `bg-gray-900`     |

### Pages with Dark Mode Support

-   ✅ Welcome page
-   ✅ Login page
-   ✅ Register page
-   ✅ Dashboard
-   ✅ Student listing (index)
-   ✅ Student create form
-   ✅ Student edit form
-   ✅ Profile page
-   ✅ Navigation menu
-   ✅ All Breeze components

---

## 🌍 Multi-Language Support

### Supported Languages

1. **English** (en) - Default
2. **Myanmar** (မြန်မာ) (mm)

### Features

-   **Session-based**: Language preference stored in user session
-   **Easy switching**: Dropdown in navigation and auth pages
-   **Middleware support**: SetLocale middleware applies language to all requests
-   **Comprehensive translations**: 70+ UI strings translated

### Technical Implementation

#### 1. Language Files Structure

```
lang/
├── en/
│   └── app.php  (English translations)
└── mm/
    └── app.php  (Myanmar translations)
```

#### 2. SetLocale Middleware

```php
// app/Http/Middleware/SetLocale.php
public function handle(Request $request, Closure $next): Response
{
    $locale = session('locale', config('app.locale'));
    if (in_array($locale, ['en', 'mm'])) {
        app()->setLocale($locale);
    }
    return $next($request);
}
```

#### 3. Language Controller

```php
// app/Http/Controllers/LanguageController.php
public function switch(string $locale): RedirectResponse
{
    if (!in_array($locale, ['en', 'mm'])) {
        abort(400, 'Invalid language code');
    }
    session(['locale' => $locale]);
    return redirect()->back()->with('success', __('Language changed successfully'));
}
```

#### 4. Language Switcher UI

```html
<div x-data="{ open: false }" @click.away="open = false">
    <button @click="open = !open">
        {{ app()->getLocale() === 'mm' ? 'မြန်မာ' : 'EN' }}
    </button>
    <div x-show="open">
        <a href="{{ route('language.switch', 'en') }}">🇬🇧 English</a>
        <a href="{{ route('language.switch', 'mm') }}">🇲🇲 မြန်မာ</a>
    </div>
</div>
```

#### 5. Using Translations in Blade

```blade
<!-- Simple translation -->
{{ __('Dashboard') }}

<!-- In attributes -->
placeholder="{{ __('Search students...') }}"

<!-- With parameters (future use) -->
{{ __('Welcome back, :name!', ['name' => $user->name]) }}
```

### Translation Categories

| Category        | Count | Examples                                    |
| --------------- | ----- | ------------------------------------------- |
| Navigation      | 7     | Dashboard, Students, Profile, Log Out       |
| Dashboard       | 13    | Welcome back, Total Students, Quick Actions |
| Students        | 20    | Student List, Add New Student, Search...    |
| Auth            | 13    | Login, Register, Sign in, Sign up           |
| Messages        | 7     | Success/error messages                      |
| Form Validation | 6     | Field required, Email invalid               |

### Adding New Translations

1. **Add to English file** (`lang/en/app.php`):

```php
'Your Key' => 'Your English Text',
```

2. **Add to Myanmar file** (`lang/mm/app.php`):

```php
'Your Key' => 'သင့်မြန်မာစာ',
```

3. **Use in Blade**:

```blade
{{ __('Your Key') }}
```

### Adding New Languages

1. Create language directory: `lang/xx/` (e.g., `lang/th/` for Thai)
2. Copy `lang/en/app.php` to `lang/xx/app.php`
3. Translate all values
4. Update SetLocale middleware:

```php
if (in_array($locale, ['en', 'mm', 'xx'])) {
```

5. Update LanguageController:

```php
if (!in_array($locale, ['en', 'mm', 'xx'])) {
```

6. Add to language switcher dropdown

---

## 🎨 Design System

### Color Scheme

-   **Primary**: Blue-600 to Purple-600 gradient
-   **Success**: Green-600
-   **Warning**: Orange-600
-   **Error**: Red-600

### Typography

-   **Font Family**: Figtree (via Bunny Fonts)
-   **Headings**: Bold (600-700 weight)
-   **Body**: Regular (400 weight)

### Spacing

-   **Consistent padding**: 6-8 units for cards
-   **Consistent margins**: 4-6 units for sections
-   **Consistent gaps**: 4-6 units for grids

### Components

-   **Rounded corners**: `rounded-lg` (8px) for cards, `rounded-md` (6px) for inputs
-   **Shadows**: `shadow-sm` for cards, `shadow-lg` for modals
-   **Transitions**: 150-200ms for smooth interactions

---

## 📱 Responsive Design

### Breakpoints (Tailwind defaults)

-   **sm**: 640px (tablets)
-   **md**: 768px (small laptops)
-   **lg**: 1024px (laptops)
-   **xl**: 1280px (desktops)

### Mobile-First Approach

All pages are designed mobile-first with responsive enhancements:

-   Mobile: Stack elements vertically
-   Tablet: 2-column grids
-   Desktop: 4-column grids for stats, multi-column layouts

---

## 🚀 Usage Examples

### Example 1: Adding Dark Mode to New Component

```blade
<div class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white transition-colors">
    Your content
</div>
```

### Example 2: Adding Translations to New Page

```blade
<h1>{{ __('Page Title') }}</h1>
<p>{{ __('Page description text') }}</p>
```

### Example 3: Dark Mode-Aware Button

```blade
<button class="bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white">
    {{ __('Click Me') }}
</button>
```

---

## 🧪 Testing Checklist

### Dark Mode Testing

-   [ ] Toggle switches between light/dark correctly
-   [ ] Preference persists on page reload
-   [ ] No flash of wrong theme on initial load
-   [ ] All colors are readable in both modes
-   [ ] Images/icons are visible in both modes
-   [ ] Transitions are smooth (200ms)

### Language Testing

-   [ ] Switcher changes language immediately
-   [ ] Language persists across pages
-   [ ] All UI strings are translated
-   [ ] Myanmar font renders correctly
-   [ ] Long translations don't break layout
-   [ ] Form validation messages are translated

### Responsive Testing

-   [ ] Test on mobile (320px-640px)
-   [ ] Test on tablet (640px-1024px)
-   [ ] Test on desktop (1024px+)
-   [ ] Navigation hamburger menu works
-   [ ] Tables are scrollable on mobile
-   [ ] Forms stack properly on mobile

---

## 🔧 Troubleshooting

### Dark Mode Not Working

1. Check Tailwind config has `darkMode: 'class'`
2. Ensure Alpine.js is loaded
3. Check browser console for JavaScript errors
4. Clear localStorage: `localStorage.removeItem('darkMode')`

### Language Not Changing

1. Check session is working (`php artisan session:table` if using database)
2. Verify SetLocale middleware is registered in `bootstrap/app.php`
3. Clear cache: `php artisan cache:clear`
4. Check translation files exist in `lang/` directory

### Styling Issues

1. Rebuild assets: `npm run build`
2. Clear view cache: `php artisan view:clear`
3. Hard refresh browser (Cmd+Shift+R / Ctrl+Shift+F5)

---

## 📚 Best Practices

### Dark Mode

1. Always provide both light and dark variants: `class="bg-white dark:bg-gray-800"`
2. Use `transition-colors` for smooth color changes
3. Test all interactive states (hover, focus, active) in both modes
4. Ensure sufficient color contrast (WCAG AA minimum)

### Multi-Language

1. Never hardcode UI text - always use `__()` helper
2. Keep translation keys descriptive: `'Add New Student'` not `'btn1'`
3. Group translations logically in language files
4. Test with longest expected translation
5. Use parameters for dynamic content: `__('Welcome, :name', ['name' => $user])`

### Performance

1. Minimize DOM changes on theme switch (use CSS classes)
2. Lazy load language files (already done by Laravel)
3. Use Alpine.js `x-show` instead of `x-if` for toggle buttons (faster)

---

## 🎯 Next Steps

### Enhancements to Consider

1. **Add more languages**: Thai, Chinese, Japanese
2. **RTL support**: For Arabic, Hebrew
3. **Theme customization**: Allow users to pick accent colors
4. **System theme sync**: Auto-switch based on OS settings change
5. **Translation management**: Use package like `spatie/laravel-translation-loader`
6. **Accessibility**: Add ARIA labels, keyboard navigation
7. **Print styles**: Optimize for printing in both themes

---

## 📖 References

-   [Tailwind CSS Dark Mode](https://tailwindcss.com/docs/dark-mode)
-   [Alpine.js Documentation](https://alpinejs.dev/)
-   [Laravel Localization](https://laravel.com/docs/localization)
-   [Nova Hub Developer Guide](https://docs.google.com/document/d/16oAj7G0HDfsqUqKAoeJTMJB05pvrBtpYUn0Apkk88Wg)

---

**Last Updated**: {{ date('Y-m-d') }}
**Version**: 1.0.0
**Maintained by**: Nova Hub Team
