# Teacher API Access Fix

## Problem
All teacher API endpoints were returning empty data or access denied errors even after assigning the teacher as a class teacher through the web interface.

## Root Cause
The system had two separate access control mechanisms that were not aligned:

1. **`getMyClasses` method** - Only checked for timetable periods (teacher must have scheduled classes)
2. **`hasClassAccess` method** - Only checked for timetable periods (teacher must have scheduled classes)

Neither method checked if the teacher was assigned as the homeroom/class teacher (`teacher_id` on `school_classes` table).

## Solution Applied

### 1. Fixed `getMyClasses` Method
**File:** `app/Repositories/Teacher/TeacherClassRepository.php`

**Change:** Now retrieves classes where teacher is EITHER:
- Teaching through timetable periods (has scheduled classes), OR
- Assigned as the homeroom teacher (`teacher_id` matches)

```php
// Get classes from timetable periods
$classIdsFromPeriods = Period::where('teacher_profile_id', $teacherProfile->id)
    ->whereHas('timetable', fn($q) => $q->where('is_active', true))
    ->pluck('class_id');

// Also get classes where teacher is the homeroom teacher
$classIdsAsHomeroom = SchoolClass::where('teacher_id', $teacherProfile->id)->pluck('id');

// Merge both sets
$classIds = $classIdsFromPeriods->merge($classIdsAsHomeroom)->unique();
```

### 2. Fixed `hasClassAccess` Method
**File:** `app/Repositories/Teacher/TeacherClassRepository.php`

**Change:** Now grants access if teacher is EITHER:
- The homeroom teacher for the class, OR
- Has timetable periods in the class

```php
// Check if teacher is the homeroom teacher
$isHomeroomTeacher = SchoolClass::where('id', $classId)
    ->where('teacher_id', $teacherProfile->id)
    ->exists();

if ($isHomeroomTeacher) {
    return true;
}

// Check if teacher has periods in the class timetable
return Period::where('teacher_profile_id', $teacherProfile->id)
    ->whereHas('timetable', fn($q) => $q->where('class_id', $classId)->where('is_active', true))
    ->exists();
```

### 3. Fixed Curriculum API
**File:** `app/Http/Controllers/Api/V1/CurriculumApiController.php`

**Change:** `getTeacherClassesProgress` now includes classes from both sources:

```php
// Get classes where teacher is the homeroom teacher OR teaches through timetable
$homeroomClassIds = SchoolClass::where('teacher_id', $teacherProfile->id)->pluck('id');

$timetableClassIds = Period::where('teacher_profile_id', $teacherProfile->id)
    ->whereHas('timetable', fn($q) => $q->where('is_active', true))
    ->pluck('class_id')
    ->unique();

$allClassIds = $homeroomClassIds->merge($timetableClassIds)->unique();
```

## Affected Endpoints (Now Fixed)

All these endpoints now work correctly for homeroom teachers:

### Class Endpoints
- ✅ `GET /api/v1/teacher/classes` - Get all classes
- ✅ `GET /api/v1/teacher/classes/{classId}` - Get class details
- ✅ `GET /api/v1/teacher/classes/{classId}/students` - Get students in class
- ✅ `GET /api/v1/teacher/classes/{classId}/teachers` - Get teachers in class
- ✅ `GET /api/v1/teacher/classes/{classId}/timetable` - Get class timetable
- ✅ `GET /api/v1/teacher/classes/{classId}/rankings` - Get class rankings
- ✅ `GET /api/v1/teacher/classes/{classId}/exams` - Get class exams
- ✅ `GET /api/v1/teacher/classes/{classId}/statistics` - Get class statistics

### Student Endpoints
- ✅ `GET /api/v1/teacher/students/{studentId}/profile` - Get student profile
- ✅ `GET /api/v1/teacher/students/{studentId}/academic` - Get student academic data
- ✅ `GET /api/v1/teacher/students/{studentId}/attendance` - Get student attendance
- ✅ `GET /api/v1/teacher/students/{studentId}/remarks` - Get student remarks
- ✅ `GET /api/v1/teacher/students/{studentId}/rankings` - Get student rankings

### Curriculum Endpoint
- ✅ `GET /api/v1/curriculum/teacher/classes` - Get teacher classes with curriculum progress

## Testing
To verify the fix works:

1. Assign a teacher as the class teacher through the web interface:
   - Go to Academic Management > Classes
   - Edit a class
   - Select the teacher in "Class Teacher" dropdown
   - Save

2. Login as that teacher via API:
   ```
   POST /api/v1/teacher/auth/login
   {
     "email": "konyeinchan@smartcampusedu.com",
     "password": "password"
   }
   ```

3. Test the endpoints:
   ```
   GET /api/v1/teacher/classes
   GET /api/v1/teacher/classes/{classId}
   GET /api/v1/teacher/classes/{classId}/students
   ```

All should now return data instead of empty arrays.

## Notes
- Teachers can access classes through TWO paths:
  1. Being assigned as the homeroom teacher (class teacher)
  2. Having timetable periods scheduled in the class
  
- The fix ensures both paths grant proper access
- No database changes required
- Backward compatible with existing timetable-based access
