# API Test Summary - Classes, School Info, Rules & Student Profile

## 📋 Overview
This document provides a comprehensive list of APIs for testing the following features:
- Class Information
- School Information
- School Rules
- Student Profile

---

## 🏫 1. CLASS INFORMATION APIs

### Base Endpoint Pattern
```
GET /api/v1/guardian/students/{student_id}/class/*
```

### Available Endpoints

#### 1.1 Get Basic Class Info (RESTful)
```http
GET /api/v1/guardian/students/{student_id}/class
```
**Description:** Get basic class information including grade, section, location, and class teacher

**Response Fields:**
- `class_id` - Class identifier
- `grade_code` - Grade code (e.g., "G10")
- `grade_name` - Full grade name
- `academic_year` - Current academic year
- `location` - Classroom location
- `student_count` - Number of students
- `class_teacher_name` - Name of class teacher

**Test Status:** ✅ Tested in `test-class-info-api.php`

---

#### 1.2 Get Detailed Class Info (RESTful)
```http
GET /api/v1/guardian/students/{student_id}/class/details
```
**Description:** Get detailed class information with students, teachers, and statistics

**Response Fields:**
- `id` - Class ID
- `class_name` - Full class name
- `subjects` - Array of subjects taught
- `teachers` - Array of teachers
- `statistics` - Class statistics

**Test Status:** ✅ Tested in `test-class-info-api.php`

---

#### 1.3 Get Class Teachers (RESTful)
```http
GET /api/v1/guardian/students/{student_id}/class/teachers
```
**Description:** Get all teachers teaching this class (class teacher + subject teachers)

**Response Fields:**
- `class_teacher` - Class teacher details
  - `id`, `name`, `name_mm`, `role`, `phone`, `email`, `subjects`, `is_class_teacher`
- `subject_teachers` - Array of subject teachers
- `total_teachers` - Total number of teachers

**Test Status:** ✅ Tested in `test-class-info-api.php`

---

#### 1.4 Get Class Statistics (RESTful)
```http
GET /api/v1/guardian/students/{student_id}/class/statistics
```
**Description:** Get class statistics including attendance, performance, and rankings

**Response Fields:**
- `class_id` - Class identifier
- `grade_code` - Grade code
- `total_students` - Total students in class
- `male_students` - Number of male students
- `female_students` - Number of female students
- `average_attendance` - Average attendance percentage
- `average_performance` - Average performance percentage
- `top_performers` - Array of top performing students
- `subject_performance` - Performance by subject

**Test Status:** ✅ Tested in `test-class-info-api.php`

---

#### 1.5 Alternative Endpoints (Legacy Support)
```http
GET /api/v1/guardian/students/{student_id}/class-info
GET /api/v1/guardian/class-info?student_id={id}
```
**Status:** ⚠️ Deprecated - Use RESTful endpoints above

---

## 🏛️ 2. SCHOOL INFORMATION API

### Endpoint
```http
GET /api/v1/guardian/school-info
```

**Authentication:** ❌ Not Required (Public endpoint)

**Description:** Get comprehensive school information including contact, facilities, statistics, and more

### Response Structure

#### 2.1 Basic Information
- `school_id` - School identifier
- `school_name` - School name (English)
- `school_name_mm` - School name (Myanmar)
- `school_code` - School code
- `logo_url` - School logo URL
- `established_year` - Year established
- `motto` - School motto (English)
- `motto_mm` - School motto (Myanmar)

#### 2.2 Contact Information
```json
"contact": {
  "phone": "09-123456789",
  "email": "info@school.edu.mm",
  "website": "https://school.edu.mm",
  "address": "123 Main Street, Yangon",
  "address_mm": "၁၂၃ ပင်မလမ်း၊ ရန်ကုန်",
  "office_hours": "8:00 AM - 4:00 PM",
  "office_hours_mm": "နံနက် ၈:၀၀ မှ ညနေ ၄:၀၀"
}
```

#### 2.3 About Information
```json
"about": {
  "description": "School description",
  "description_mm": "ကျောင်းအကြောင်း",
  "vision": "School vision",
  "vision_mm": "မျှော်မှန်းချက်",
  "mission": "School mission",
  "mission_mm": "ရည်မှန်းချက်",
  "values": ["Value 1", "Value 2"],
  "values_mm": ["တန်ဖိုး ၁", "တန်ဖိုး ၂"]
}
```

#### 2.4 Facilities
```json
"facilities": [
  {
    "id": 1,
    "name": "Library",
    "name_mm": "စာကြည့်တိုက်",
    "icon": "📚",
    "capacity": "500 students"
  }
]
```

#### 2.5 Statistics
```json
"statistics": {
  "total_students": 1500,
  "total_teachers": 80,
  "total_staff": 30,
  "total_classes": 45,
  "student_teacher_ratio": "18:1",
  "pass_rate": "95%",
  "average_attendance": "92%"
}
```

#### 2.6 Accreditations
```json
"accreditations": [
  {
    "name": "Ministry of Education",
    "year": 2020
  }
]
```

#### 2.7 Social Media
```json
"social_media": {
  "facebook": "https://facebook.com/school",
  "twitter": "https://twitter.com/school",
  "instagram": "https://instagram.com/school",
  "youtube": "https://youtube.com/school"
}
```

**Test Status:** ✅ Tested in `test-school-info-api.php`

---

## 📜 3. SCHOOL RULES API

### Endpoint
```http
GET /api/v1/guardian/school/rules
```

**Authentication:** ✅ Required (Bearer Token)

**Description:** Get all school rules organized by categories with Myanmar language support

### Response Structure

#### 3.1 Top Level
```json
{
  "success": true,
  "message": "School rules retrieved successfully",
  "data": {
    "categories": [...],
    "total_categories": 5,
    "total_rules": 25,
    "last_updated": "2026-02-11T10:30:00Z"
  }
}
```

#### 3.2 Category Structure
```json
{
  "id": 1,
  "title": "Attendance & Punctuality",
  "title_mm": "တက်ရောက်မှုနှင့် အချိန်လိုက်နာမှု",
  "description": "Rules about attendance and being on time",
  "description_mm": "တက်ရောက်မှုနှင့် အချိန်မှန်ရောက်ရှိခြင်းဆိုင်ရာ စည်းမျဉ်းများ",
  "icon": "📅",
  "icon_color": "#4CAF50",
  "icon_background_color": "#E8F5E9",
  "rules_count": 5,
  "priority": 1,
  "is_active": true,
  "rules": [...]
}
```

#### 3.3 Rule Structure
```json
{
  "id": 1,
  "title": "Daily Attendance",
  "title_mm": "နေ့စဉ်တက်ရောက်မှု",
  "description": "Students must attend school daily unless excused",
  "description_mm": "ခွင့်ပြုချက်မရှိပါက ကျောင်းသားများသည် နေ့စဉ်တက်ရောက်ရမည်",
  "severity": "high",
  "order": 1
}
```

#### 3.4 Severity Levels
- `low` - Minor rules
- `medium` - Important rules
- `high` - Critical rules

#### 3.5 Categories (Typical)
1. **Attendance & Punctuality** (📅)
2. **Uniform & Appearance** (👔)
3. **Behavior & Conduct** (🤝)
4. **Academic Integrity** (📚)
5. **Safety & Security** (🛡️)

**Test Status:** ✅ Tested in `test-rules-api.php`

**Features:**
- ✅ Myanmar language support (all fields)
- ✅ Icon support with colors
- ✅ Priority ordering
- ✅ Severity levels
- ✅ Active/inactive status

---

## 👤 4. STUDENT PROFILE APIs

### Base Endpoint Pattern
```http
GET /api/v1/guardian/students/{student_id}/profile/*
```

### Available Endpoints

#### 4.1 Get Profile Overview
```http
GET /api/v1/guardian/students/{student_id}/profile
```
**Description:** Get student profile overview with badges and basic information

**Response Fields:**
- `id` - Student ID
- `name` - Student name
- `grade` - Current grade
- `section` - Class section
- `roll_number` - Roll number
- `photo_url` - Profile photo URL
- `badges` - Achievement badges

**Test Status:** ✅ Tested in `test-student-profile-api.php`

---

#### 4.2 Get Academic Summary
```http
GET /api/v1/guardian/students/{student_id}/profile/academic-summary
```
**Description:** Get academic summary including GPA, scores, and rankings

**Response Fields:**
- `current_gpa` - Current GPA
- `current_rank` - Current class rank
- `total_students` - Total students in class
- `average_score` - Average score across subjects
- `highest_score` - Highest score
- `lowest_score` - Lowest score

**Test Status:** ✅ Tested in `test-student-profile-api.php`

---

#### 4.3 Get Subject Performance
```http
GET /api/v1/guardian/students/{student_id}/profile/subject-performance
```
**Description:** Get performance details for all subjects

**Response Fields:**
- `subjects` - Array of subject performance data
  - `subject_id`, `subject_name`, `teacher_name`
  - `current_score`, `average_score`, `highest_score`
  - `attendance_percentage`, `homework_completion`
  - `exam_scores`, `trend`

**Test Status:** ✅ Tested in `test-student-profile-api.php`

---

#### 4.4 Get Progress Tracking
```http
GET /api/v1/guardian/students/{student_id}/profile/progress-tracking?months=6
```
**Description:** Get GPA and rank history for progress tracking charts

**Query Parameters:**
- `months` - Number of months to retrieve (default: 6)

**Response Fields:**
- `gpa_history` - Array of GPA data points
- `rank_history` - Array of rank data points
- `current_gpa` - Current GPA
- `current_rank` - Current rank
- `trend` - Performance trend (improving/declining/stable)

**Test Status:** ✅ Tested in `test-student-profile-api.php`

---

#### 4.5 Get Comparison Data
```http
GET /api/v1/guardian/students/{student_id}/profile/comparison
```
**Description:** Get student performance comparison with class average

**Response Fields:**
- `gpa_comparison` - Student vs class average GPA
- `avg_score_comparison` - Student vs class average score
- `subject_comparisons` - Per-subject comparisons
- `rank_percentile` - Student's percentile rank

**Test Status:** ✅ Tested in `test-student-profile-api.php`

---

#### 4.6 Get Attendance Summary
```http
GET /api/v1/guardian/students/{student_id}/profile/attendance-summary?months=3
```
**Description:** Get attendance summary with monthly breakdown

**Query Parameters:**
- `months` - Number of months to retrieve (default: 3)

**Response Fields:**
- `overall_percentage` - Overall attendance percentage
- `total_present` - Total present days
- `total_days` - Total school days
- `monthly_breakdown` - Array of monthly attendance data
- `recent_absences` - Recent absence records

**Test Status:** ✅ Tested in `test-student-profile-api.php`

---

#### 4.7 Get Rankings & Exam History
```http
GET /api/v1/guardian/students/{student_id}/profile/rankings
```
**Description:** Get current rankings and exam history

**Response Fields:**
- `current_class_rank` - Current rank in class
- `current_grade_rank` - Current rank in grade
- `exam_history` - Array of past exam results
  - `exam_id`, `exam_name`, `date`, `total_score`
  - `rank`, `percentage`, `grade`

**Test Status:** ✅ Tested in `test-student-profile-api.php`

---

#### 4.8 Get Achievement Badges
```http
GET /api/v1/guardian/students/{student_id}/profile/achievements
```
**Description:** Get achievement badges with locked/unlocked status

**Response Fields:**
- `badges` - Array of all badges
  - `id`, `name`, `description`, `icon`
  - `is_unlocked`, `unlocked_date`, `criteria`
- `total_badges` - Total number of badges
- `unlocked_badges` - Number of unlocked badges (optional)

**Test Status:** ✅ Tested in `test-student-profile-api.php`

---

## 📊 Test Results Summary

| Feature | Endpoint Count | Test File | Status |
|---------|---------------|-----------|--------|
| Class Info | 4 main + 2 legacy | `test-class-info-api.php` | ✅ All Passed |
| School Info | 1 | `test-school-info-api.php` | ✅ Passed |
| School Rules | 1 | `test-rules-api.php` | ✅ Passed |
| Student Profile | 8 | `test-student-profile-api.php` | ✅ All Passed |

**Total APIs:** 16 endpoints tested

---

## 🔧 Postman Collection Status

### Current Status in `UNIFIED_APP_POSTMAN_COLLECTION.json`

✅ **All endpoints are present and properly documented**

#### Class Info APIs
- ✅ Get Class Info (RESTful)
- ✅ Get Detailed Class Info (RESTful)
- ✅ Get Class Teachers (RESTful)
- ✅ Get Class Statistics (RESTful)

#### School Info API
- ✅ Get School Info (Public)

#### School Rules API
- ✅ Get School Rules

#### Student Profile APIs
- ✅ Get Profile Overview
- ✅ Get Academic Summary
- ✅ Get Subject Performance
- ✅ Get Progress Tracking
- ✅ Get Comparison Data
- ✅ Get Attendance Summary
- ✅ Get Rankings & Exam History
- ✅ Get Achievement Badges

### Postman Collection Features
- ✅ Proper folder organization
- ✅ Test scripts for validation
- ✅ Environment variables support
- ✅ Request descriptions
- ✅ Response field documentation

---

## 🚀 Quick Test Commands

### Test All APIs
```bash
# Class Info
php test-class-info-api.php

# School Info
php test-school-info-api.php

# School Rules
php test-rules-api.php

# Student Profile
php test-student-profile-api.php
```

### Prerequisites
1. Update login credentials in test files
2. Ensure backend server is running
3. Update `$baseUrl` if needed

---

## 📝 Notes

1. **Authentication:**
   - School Info: No auth required (public)
   - All other endpoints: Bearer token required

2. **RESTful Pattern:**
   - All new endpoints follow RESTful URL structure
   - Student ID in path: `/guardian/students/{student_id}/*`

3. **Myanmar Language:**
   - School Info: Full Myanmar support
   - School Rules: Full Myanmar support
   - Other endpoints: Partial Myanmar support

4. **Pagination:**
   - Not applicable for these endpoints
   - All data returned in single response

5. **Error Handling:**
   - All endpoints return standard JSON response
   - HTTP status codes properly implemented

---

## ✅ Conclusion

All APIs for Classes, School Info, Rules, and Student Profile are:
- ✅ Implemented and tested
- ✅ Documented in Postman collection
- ✅ Following RESTful conventions
- ✅ Ready for mobile app integration

**No updates needed in UNIFIED_APP_POSTMAN_COLLECTION.json** - All endpoints are already properly documented with test scripts and descriptions.
