# Subject Grade Selection Feature

## Overview
Added grade selection dropdown to subject create and edit forms, allowing subjects to be associated with multiple grades.

## Changes Made

### 1. Academic Management - Create Subject Modal
**File:** `scp/resources/views/academic/academic-management.blade.php`

Added grade multi-select dropdown:
- Field name: `grade_ids[]` (array for multiple selection)
- Required field (at least one grade must be selected)
- Shows all available grades
- Helper text: "Hold Ctrl/Cmd to select multiple grades"

### 2. Subject Detail - Edit Subject Modal
**File:** `scp/resources/views/academic/subject-detail.blade.php`

Updated to pass grades to the form partial:
- Added `$grades` to the compact array in controller
- Passed grades to the partial include

### 3. Subject Form Fields Partial
**File:** `scp/resources/views/academic/partials/subject-form-fields.blade.php`

Added grade dropdown with:
- Multi-select capability
- Pre-selection of existing grades in edit mode
- Support for old input on validation errors
- Required validation indicator

### 4. Controller Updates
**File:** `scp/app/Http/Controllers/AcademicManagementController.php`

Updated `showSubject()` method:
- Added `$grades = $this->academicRepository->getGrades()`
- Passed grades to view

### 5. Validation Updates
**Files:**
- `scp/app/Http/Requests/Academic/StoreSubjectRequest.php`
- `scp/app/Http/Requests/Academic/UpdateSubjectRequest.php`

Updated validation rules:
```php
'grade_ids' => ['required', 'array', 'min:1'],
'grade_ids.*' => ['uuid', 'exists:grades,id'],
```

## How It Works

### Create Subject Flow
1. User clicks "Add Subject" in Academic Management
2. Modal opens with form fields including grade dropdown
3. User selects one or more grades (Ctrl/Cmd + click)
4. User fills other fields (code, name, category, type)
5. On submit, grades are validated and synced to subject

### Edit Subject Flow
1. User views subject detail page
2. User clicks "Edit Subject"
3. Modal opens with pre-filled data
4. Grade dropdown shows currently associated grades as selected
5. User can add/remove grades
6. On submit, grades are updated via sync

### Backend Processing
The service layer already handles grade syncing:
```php
// In AcademicService.php
$subject->grades()->sync($data->gradeIds());
```

This uses Laravel's `sync()` method which:
- Adds new grade associations
- Removes unselected grade associations
- Maintains existing associations

## Database Structure
Uses many-to-many relationship via `grade_subject` pivot table:
- `grade_id` (UUID)
- `subject_id` (UUID)
- Timestamps

## User Experience

### Visual Feedback
- Required field indicator (red asterisk)
- Helper text for multi-select instructions
- Validation errors displayed below field
- Selected grades highlighted in dropdown

### Validation
- At least one grade must be selected
- Each grade ID must exist in database
- Validation errors prevent form submission
- Old input preserved on validation failure

## Testing Checklist
- [ ] Create subject with single grade
- [ ] Create subject with multiple grades
- [ ] Create subject without grade (should fail validation)
- [ ] Edit subject to add grades
- [ ] Edit subject to remove grades
- [ ] Edit subject to change grades
- [ ] Validation error shows when no grade selected
- [ ] Selected grades persist on validation error
- [ ] Subject detail shows all associated grades
- [ ] Exam schedule filters subjects by grade correctly

## Related Features
This change enables:
- ✅ Exam schedule subject filtering by grade
- ✅ Class-subject associations by grade
- ✅ Grade-specific curriculum management
- ✅ Better academic structure organization

## Migration Notes
Existing subjects without grades:
- Will fail validation on edit until grades are assigned
- Should be updated to have at least one grade
- Can run a migration/seeder to assign default grades if needed
