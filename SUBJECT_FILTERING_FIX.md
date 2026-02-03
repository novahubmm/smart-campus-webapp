# Subject Filtering in Exam Schedule

## Issue
When clicking "Add Subject" in the exam schedule, subjects were not being properly filtered based on the selected grade.

## Solution
Implemented dynamic subject filtering that works with both grade and class selection.

## How It Works

### 1. Grade Selection
When user selects a grade:
- `onGradeChange()` is triggered
- `selectedGradeId` is updated
- `filteredSubjects` computed property filters subjects by grade
- Subject dropdown shows only subjects connected to that grade

### 2. Class Selection
When user selects a class:
- `onClassChange()` is triggered
- Grade is extracted from the selected class
- Grade dropdown is auto-filled if empty
- `selectedGradeId` is updated
- `filteredSubjects` filters subjects by the class's grade

### 3. Add Subject Button
When "Add Subject" is clicked:
- Validates that grade is selected (required)
- Validates that class is selected (required)
- Adds new schedule row
- Subject dropdown shows filtered subjects immediately

### 4. Subject Dropdown
In each schedule row:
- Shows "Select subject" placeholder
- Displays only subjects connected to the selected grade
- Shows "No subjects available" if grade has no subjects
- Updates dynamically when grade changes

## Code Changes

### JavaScript Functions Added/Updated
```javascript
// Filters subjects by grade (from form or class)
get filteredSubjects() {
    const gradeId = this.examForm.grade_id || this.selectedGradeId;
    if (!gradeId) return [];
    return this.subjects.filter(s => s.grade_ids.includes(gradeId));
}

// Handles grade dropdown change
onGradeChange() {
    this.selectedGradeId = this.examForm.grade_id;
}

// Handles class dropdown change
onClassChange() {
    const selectedClass = this.classes.find(c => c.id === this.examForm.class_id);
    this.selectedGradeId = selectedClass?.grade_id || null;
    // Auto-fill grade if not set
    if (!this.examForm.grade_id && this.selectedGradeId) {
        this.examForm.grade_id = this.selectedGradeId;
    }
    this.examForm.schedules = [];
}

// Validates before adding schedule row
addScheduleRow() {
    if (!this.examForm.grade_id) {
        alert('Please select a grade first');
        return;
    }
    if (!this.examForm.class_id) {
        alert('Please select a class first');
        return;
    }
    // Add schedule row...
}
```

### HTML Changes
- Added `@change="onGradeChange()"` to grade dropdown
- Updated "Add Subject" button to check both grade and class
- Updated helper text to mention both grade and class
- Improved subject dropdown with better messages

## User Experience

### Before
- Subjects were not filtered by grade
- All subjects shown regardless of grade
- Confusing for users

### After
- ✅ Subjects automatically filtered by selected grade
- ✅ Works whether user selects grade first or class first
- ✅ Clear validation messages
- ✅ Grade auto-fills when class is selected
- ✅ Subject dropdown updates immediately
- ✅ Shows helpful message if no subjects available

## Testing Checklist
- [ ] Select grade first, then class → subjects filtered correctly
- [ ] Select class first → grade auto-fills, subjects filtered correctly
- [ ] Change grade → subject list updates in schedule rows
- [ ] Change class → subject list updates in schedule rows
- [ ] Click "Add Subject" without grade → shows alert
- [ ] Click "Add Subject" without class → shows alert
- [ ] Subject dropdown shows only grade-related subjects
- [ ] Empty state shows correct message
- [ ] Multiple schedule rows all show filtered subjects
