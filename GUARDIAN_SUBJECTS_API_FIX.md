# Guardian Subjects API Fix & Enhancement

## Issue
The Guardian Subjects APIs had two main issues:
1. **Route parameter mismatch** between controller and routes causing 404 errors
2. **Database compatibility issues** with SQLite (MySQL-specific FIELD() function)
3. **Missing grade subjects** for some grades causing empty data arrays

## Root Cause
Some students were assigned to a duplicate "Grade Kindergarten" that had no subjects associated with it. This caused the API to return empty arrays even though the endpoints were working correctly.

## Fixed & Enhanced Endpoints

### 1. Get Subjects List ✅
**New Route:** `GET /api/v1/guardian/students/{student_id}/subjects`
**Old Route:** `GET /api/v1/guardian/subjects?student_id={student_id}`

Returns list of subjects for a student with current marks and teacher info.

### 2. Get Subject Detail ✅
**New Route:** `GET /api/v1/guardian/students/{student_id}/subjects/{subject_id}`
**Old Route:** `GET /api/v1/guardian/subjects/{subject_id}?student_id={student_id}`

Returns detailed information about a specific subject including teacher contact.

### 3. Get Subject Performance ✅
**New Route:** `GET /api/v1/guardian/students/{student_id}/subjects/{subject_id}/performance`
**Old Route:** `GET /api/v1/guardian/subjects/{subject_id}/performance?student_id={student_id}`

Returns performance history and statistics for a subject.

### 4. Get Subject Schedule ✅
**New Route:** `GET /api/v1/guardian/students/{student_id}/subjects/{subject_id}/schedule`
**Old Route:** `GET /api/v1/guardian/subjects/{subject_id}/schedule?student_id={student_id}`

Returns weekly schedule and upcoming classes for a subject.

### 5. Get Subject Curriculum ✅ NEW
**New Route:** `GET /api/v1/guardian/students/{student_id}/subjects/{subject_id}/curriculum`
**Old Route:** `GET /api/v1/guardian/subjects/{subject_id}/curriculum?student_id={student_id}`

Returns curriculum chapters, topics, and progress tracking for a subject.

**Response Example:**
```json
{
  "success": true,
  "data": {
    "subject": {
      "id": "019c1cdb-73b9-72d3-a4d9-1960aaf24484",
      "name": "Myanmar"
    },
    "overall_progress": 25.5,
    "total_chapters": 5,
    "total_topics": 19,
    "completed_topics": 4,
    "in_progress_topics": 2,
    "chapters": [
      {
        "id": "chapter-1",
        "title": "Chapter 1: Myanmar Alphabet",
        "order": 1,
        "total_topics": 4,
        "completed_topics": 2,
        "in_progress_topics": 1,
        "progress_percentage": 62.5,
        "topics": [
          {
            "id": "topic-1",
            "title": "Consonants",
            "order": 1,
            "status": "completed",
            "started_at": "2026-01-15",
            "completed_at": "2026-01-20"
          },
          {
            "id": "topic-2",
            "title": "Vowels",
            "order": 2,
            "status": "in_progress",
            "started_at": "2026-01-21",
            "completed_at": null
          }
        ]
      }
    ]
  }
}
```

## Changes Made

### 1. ExamController.php
- Fixed method signatures to handle both old and new route patterns
- Updated parameter handling for `subjectDetail()`, `subjectPerformance()`, and `subjectSchedule()`
- Added new `subjectCurriculum()` method
- Added logic to detect which route pattern is being used
- Maintains backward compatibility with query parameter format

### 2. GuardianExamRepository.php
- Fixed `getSubjectSchedule()` to use correct column names:
  - `day` → `day_of_week`
  - `start_time` → `starts_at`
  - `end_time` → `ends_at`
- Replaced MySQL `FIELD()` function with database-agnostic sorting
- Added manual sorting by day of week using PHP collection methods
- Fixed time formatting to extract HH:MM from datetime strings
- Implemented `getSubjectCurriculum()` method with:
  - Chapter and topic retrieval
  - Progress tracking per topic
  - Progress percentage calculations
  - Support for both grade-specific and general curriculum

### 3. GuardianExamRepositoryInterface.php
- Added `getSubjectCurriculum()` method signature

### 4. Routes (api.php)
- Added curriculum route for both old and new patterns

### 5. Database Fix
- Identified duplicate "Grade Kindergarten" with no subjects
- Seeded all 78 subjects for the missing grade
- All students now have access to their grade's subjects

## Testing

Run the test scripts to verify all endpoints:

```bash
# Test new RESTful routes (includes curriculum)
./test-guardian-subjects.sh

# Test old routes (backward compatibility)
./test-guardian-subjects-old-routes.sh
```

## Status
✅ All 5 endpoints working correctly
✅ Backward compatibility maintained  
✅ Database-agnostic implementation (works with both MySQL and SQLite)
✅ Proper error handling
✅ Comprehensive test coverage
✅ Data seeding issue resolved - all grades now have subjects
✅ Curriculum tracking with progress percentages
