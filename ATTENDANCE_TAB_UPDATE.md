# Student Attendance Tab Navigation Update

## Summary
Added URL-based tab navigation to the Student Attendance page (`/attendance/students`) to match the behavior of the Academic Management page (`/academic-management`).

## Changes Made

### 1. Controller Update
**File:** `app/Http/Controllers/StudentAttendanceController.php`

- Added `initialTab` parameter to the `index()` method
- The controller now reads the `tab` query parameter from the URL (defaults to 'class')
- Passes the initial tab value to the view

```php
'initialTab' => request('tab', 'class'),
```

### 2. View Update
**File:** `resources/views/attendance/student/index.blade.php`

- Updated Alpine.js data initialization to accept `initialTab` from config
- Added `init()` method to watch for tab changes and update the URL
- Tab changes now update the browser URL using `window.history.pushState()`

### 3. Tab Component
**File:** `resources/views/components/academic-tabs.blade.php`

- No changes needed - component already supports the required functionality
- Uses Alpine.js `@click` to change tabs
- Properly binds to the `tab` variable in the parent component

## How It Works

1. **Initial Load:**
   - User visits `/attendance/students` → defaults to 'class' tab
   - User visits `/attendance/students?tab=individual` → opens 'individual' tab

2. **Tab Switching:**
   - When user clicks a tab, Alpine.js updates the `tab` variable
   - The `init()` watcher detects the change
   - URL is updated to reflect the active tab (e.g., `?tab=individual`)
   - Browser history is updated so back/forward buttons work correctly

3. **URL Persistence:**
   - The active tab is now part of the URL
   - Users can bookmark specific tabs
   - Refreshing the page maintains the selected tab

## Available Tabs

1. **class** - Class Attendance (default)
   - Shows attendance summary by class
   - Displays period-by-period attendance
   - Includes overall statistics

2. **individual** - Individual Student Attendance
   - Shows individual student attendance records
   - Filterable by grade, class, and month
   - Displays monthly attendance percentage

## Testing

To test the implementation:

1. Visit `http://192.168.100.114:8088/attendance/students`
   - Should show Class Attendance tab by default

2. Click on "Individual Student Attendance" tab
   - URL should update to `?tab=individual`
   - Content should switch to individual student view

3. Refresh the page
   - Should maintain the 'individual' tab selection

4. Use browser back button
   - Should navigate back to 'class' tab

5. Direct URL access
   - Visit `http://192.168.100.114:8088/attendance/students?tab=individual`
   - Should open directly to Individual Student Attendance tab

## Localization

Both English and Myanmar translations are already in place:

- **English:** `Class Attendance`, `Individual Student Attendance`
- **Myanmar:** `အတန်းတက်ရောက်မှု`, `ကျောင်းသားတစ်ဦးချင်းတက်ရောက်မှု`

## Benefits

1. **Better UX:** Users can bookmark specific tabs
2. **Navigation:** Browser back/forward buttons work correctly
3. **Consistency:** Matches the behavior of Academic Management page
4. **Shareable:** Users can share direct links to specific tabs
5. **State Persistence:** Tab selection survives page refreshes
