# Localization Update - Day Names and Add Student Modal

## Summary
Updated the timetable, calendar views, and Add Student modal to display Myanmar translations instead of English text when the application is set to Myanmar language.

## Changes Made

### 1. Language Files Updated

#### `lang/mm/components.php`
Added Myanmar day name translations:
- Mon → တနင်္လာ
- Tue → အင်္ဂါ
- Wed → ဗုဒ္ဓဟူး
- Thu → ကြာသပတေး
- Fri → သောကြာ
- Sat → စနေ
- Sun → တနင်္ဂနွေ

#### `lang/en/components.php`
Added English day name translations for consistency:
- Mon, Tue, Wed, Thu, Fri, Sat, Sun

#### `lang/mm/academic_management.php`
Added Myanmar translations for Add Student modal:
- 'Add Student' → 'ကျောင်းသား ထည့်သွင်းရန်'
- 'Search Students' → 'ကျောင်းသားများ ရှာဖွေရန်'
- 'Search by name, ID...' → 'အမည် သို့မဟုတ် ID ဖြင့်ရှာဖွေ...'
- 'Select Student' → 'ကျောင်းသား ရွေးချယ်ရန်'
- 'Start typing to search for students' → 'ကျောင်းသားများရှာဖွေရန် စာရိုက်ထည့်ပါ'
- 'Searching...' → 'ရှာဖွေနေသည်...'
- 'No matching students found.' → 'ကိုက်ညီသော ကျောင်းသားများ မတွေ့ပါ။'
- 'Error searching for students' → 'ကျောင်းသားများရှာဖွေရာတွင် အမှားအယွင်းရှိသည်'
- 'An error occurred while adding the student.' → 'ကျောင်းသား ထည့်သွင်းရာတွင် အမှားအယွင်းတစ်ခု ဖြစ်ပွားခဲ့သည်။'

### 2. View Files Updated

#### `resources/views/components/timetable.blade.php`
Changed the `$dayLabel` function to use translations:
```php
$dayLabel = fn(string $day) => __('components.' . ucfirst($day));
```

#### `resources/views/time-table/_form-script.blade.php`
Updated the `dayLabel()` JavaScript function to use Laravel translations.

#### `resources/views/time-table/_timetable-editor-modal.blade.php`
Applied the same translation update to the timetable editor modal's `dayLabel()` function.

#### `resources/views/events/index.blade.php`
- Updated the calendar header to use translations
- Updated the JavaScript `weekDays` getter to use translated day names

#### `resources/views/guardian_pwa/timetable.blade.php`
Updated day tabs to use translations.

#### `resources/views/academic/class-detail.blade.php`
The Add Student modal already uses translation functions - we just added the missing Myanmar translations.

## How It Works

1. When the application language is set to Myanmar (mm), all text will automatically display in Myanmar script
2. When the application language is set to English (en), all text will display in English
3. The translations are centralized in language files for easy maintenance

## Testing

To test the changes:
1. Navigate to: `http://192.168.100.114:8088/academic-management/classes/{class-id}`
2. Ensure your application language is set to Myanmar
3. The timetable should display: တနင်္လာ, အင်္ဂါ, ဗုဒ္ဓဟူး, ကြာသပတေး, သောကြာ
4. Click "Add Student" button - the modal should display Myanmar text:
   - Title: "ကျောင်းသား ထည့်သွင်းရန်"
   - Search label: "ကျောင်းသားများ ရှာဖွေရန်"
   - Placeholder: "အမည် သို့မဟုတ် ID ဖြင့်ရှာဖွေ..."
   - Select label: "ကျောင်းသား ရွေးချယ်ရန်"
   - Empty state: "ကျောင်းသားများရှာဖွေရန် စာရိုက်ထည့်ပါ"

## Files Modified

1. `lang/mm/components.php` - Added Myanmar day translations
2. `lang/en/components.php` - Added English day translations
3. `lang/mm/academic_management.php` - Added Myanmar translations for Add Student modal
4. `resources/views/components/timetable.blade.php` - Updated to use translations
5. `resources/views/time-table/_form-script.blade.php` - Updated JavaScript day labels
6. `resources/views/time-table/_timetable-editor-modal.blade.php` - Updated JavaScript day labels
7. `resources/views/events/index.blade.php` - Updated calendar day labels
8. `resources/views/guardian_pwa/timetable.blade.php` - Updated PWA day tabs

## Notes

- The Add Student modal was already using translation functions, we just added the missing Myanmar translations
- The changes are backward compatible and will work with any language added to the system
- Cache was cleared after the changes to ensure translations are loaded properly
