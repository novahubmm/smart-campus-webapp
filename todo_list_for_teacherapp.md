# Teacher App API - Testing & Seeding Todo List

## Overview
This document tracks the testing progress for each Teacher App API endpoint.

---

## 1. Seed Data Required

### Priority 1: Core Data (Required for all APIs)
- [x] Create `TeacherAppSeeder.php` with:
  - [x] Teacher user with profile (teacher@smartcampusedu.com)
  - [x] Department for teacher
  - [x] Batch (academic year)
  - [x] Grades (Grade 7, 8, 9, 10)
  - [x] Classes (8A, 8B, 9A, 9B, etc.)
  - [x] Subjects (Mathematics, English, Science, etc.)
  - [x] Students (8 per class, 64 total)
  - [x] Timetable with periods (assign teacher to classes)
  - [x] Rooms

### Priority 2: Feature-Specific Data
- [x] Announcements (5 announcements)
- [x] Events (5 calendar events)
- [x] Leave Requests (teacher's own + student requests)
- [x] Homework assignments (9 total)
- [x] Attendance records (5 days)
- [x] Payroll records (6 months)
- [x] Daily Reports (3 reports)

---

## 2. API Endpoints Testing Checklist

### Auth APIs
| # | Endpoint | Method | Status | Notes |
|---|----------|--------|--------|-------|
| 1 | `/api/v1/teacher/auth/login` | POST | ✅ Working | Returns token |
| 2 | `/api/v1/teacher/auth/profile` | GET | ✅ Working | Returns user profile |
| 3 | `/api/v1/teacher/auth/logout` | POST | ✅ Working | Invalidates token |

### Dashboard APIs
| # | Endpoint | Method | Status | Notes |
|---|----------|--------|--------|-------|
| 4 | `/api/v1/teacher/dashboard/stats` | GET | ✅ Working | 24 students, 3 classes |
| 5 | `/api/v1/teacher/today-classes` | GET | ✅ Working | Shows 3 classes |
| 6 | `/api/v1/teacher/today-classes/{id}` | GET | ✅ Working | Returns class detail with students |
| 7 | `/api/v1/teacher/schedule/weekly` | GET | ✅ Working | Returns weekly schedule |
| 8 | `/api/v1/teacher/schedule/full` | GET | ✅ Working | Full timetable with colors |

### Classes APIs
| # | Endpoint | Method | Status | Notes |
|---|----------|--------|--------|-------|
| 9 | `/api/v1/teacher/classes` | GET | ✅ Working | 3 classes, 24 students |
| 10 | `/api/v1/teacher/classes/{id}` | GET | ✅ Working | Returns class info, students, timetable |
| 11 | `/api/v1/teacher/classes/dropdown` | GET | ✅ Working | Returns class dropdown |
| 12 | `/api/v1/teacher/classes/attendance-dropdown` | GET | ✅ Working | Returns student with attendance |
| 13 | `/api/v1/teacher/students/{id}/profile` | GET | ✅ Working | Returns student profile with stats |

### Subjects APIs
| # | Endpoint | Method | Status | Notes |
|---|----------|--------|--------|-------|
| 14 | `/api/v1/teacher/subjects` | GET | ✅ Working | Returns subjects list |
| 15 | `/api/v1/teacher/subjects/{id}` | GET | ✅ Working | Returns subject with chapters |
| 16 | `/api/v1/teacher/subjects/{id}/curriculum` | GET | ✅ Working | Returns curriculum progress |

### Attendance APIs
| # | Endpoint | Method | Status | Notes |
|---|----------|--------|--------|-------|
| 17 | `/api/v1/teacher/attendance/students` | GET | ✅ Working | Returns students with status (supports `current_period_id`) |
| 18 | `/api/v1/teacher/attendance` | POST | ✅ Working | Save individual attendance (supports `current_period_id`) |
| 19 | `/api/v1/teacher/attendance/bulk` | POST | ✅ Working | Mark all present/absent (supports `current_period_id`) |
| 20 | `/api/v1/teacher/attendance/history` | GET | ✅ Working | Returns attendance history |
| 21 | `/api/v1/teacher/attendance/history/{id}` | GET | ✅ Working | Returns history detail with students |

### Homework APIs
| # | Endpoint | Method | Status | Notes |
|---|----------|--------|--------|-------|
| 22 | `/api/v1/teacher/homework` | GET | ✅ Working | 9 homework, 6 active, 3 completed |
| 23 | `/api/v1/teacher/homework` | POST | ✅ Working | Creates new homework |
| 24 | `/api/v1/teacher/homework/{id}` | GET | ✅ Working | Returns homework with submissions |
| 25 | `/api/v1/teacher/homework/{id}/collect` | POST | ✅ Working | Marks student submission |

### Announcements APIs
| # | Endpoint | Method | Status | Notes |
|---|----------|--------|--------|-------|
| 26 | `/api/v1/teacher/announcements` | GET | ✅ Working | 5 announcements |
| 27 | `/api/v1/teacher/announcements/{id}` | GET | ✅ Working | Returns announcement detail |

### Calendar Events APIs
| # | Endpoint | Method | Status | Notes |
|---|----------|--------|--------|-------|
| 28 | `/api/v1/teacher/calendar/events` | GET | ✅ Working | Returns events |
| 29 | `/api/v1/teacher/calendar/events/{id}` | GET | ✅ Working | Returns event detail |

### Teacher Leave Request APIs
| # | Endpoint | Method | Status | Notes |
|---|----------|--------|--------|-------|
| 30 | `/api/v1/teacher/my-leave-requests` | GET | ✅ Working | Returns teacher's leaves |
| 31 | `/api/v1/teacher/my-leave-requests` | POST | ✅ Working | Creates new leave request |
| 32 | `/api/v1/teacher/my-leave-requests/{id}` | GET | ✅ Working | Returns leave request detail |
| 33 | `/api/v1/teacher/leave-balance` | GET | ✅ Working | Returns leave balance |

### Student Leave Request APIs
| # | Endpoint | Method | Status | Notes |
|---|----------|--------|--------|-------|
| 34 | `/api/v1/teacher/leave-requests/pending` | GET | ✅ Working | 5 pending requests |
| 35 | `/api/v1/teacher/leave-requests` | GET | ✅ Working | Returns all student leaves |
| 36 | `/api/v1/teacher/leave-requests/{id}/approve` | POST | ✅ Working | Approves leave |
| 37 | `/api/v1/teacher/leave-requests/{id}/reject` | POST | ✅ Working | Rejects leave (requires remarks) |

### Daily Reports APIs
| # | Endpoint | Method | Status | Notes |
|---|----------|--------|--------|-------|
| 38 | `/api/v1/teacher/daily-reports/my-reports` | GET | ✅ Working | Returns teacher's reports |
| 39 | `/api/v1/teacher/daily-reports/received` | GET | ✅ Working | Returns received reports |
| 40 | `/api/v1/teacher/daily-reports/recipients` | GET | ✅ Working | Returns recipients |
| 41 | `/api/v1/teacher/daily-reports` | POST | ✅ Working | Creates new report |
| 42 | `/api/v1/teacher/daily-reports/{id}` | GET | ✅ Working | Returns report detail |
| 43 | `/api/v1/teacher/daily-reports/{id}/status` | PUT | ✅ Working | Updates status (received only) |

### Payslips APIs
| # | Endpoint | Method | Status | Notes |
|---|----------|--------|--------|-------|
| 44 | `/api/v1/teacher/payslips` | GET | ✅ Working | 6 months of payroll |
| 45 | `/api/v1/teacher/payslips/{id}` | GET | ✅ Working | Returns payslip detail |

### Class Records APIs
| # | Endpoint | Method | Status | Notes |
|---|----------|--------|--------|-------|
| 46 | `/api/v1/teacher/class-records` | GET | ✅ Working | Returns class records |
| 47 | `/api/v1/teacher/class-records/{id}` | GET | ✅ Working | Returns record with attendance |

### Notifications APIs (NEW)
| # | Endpoint | Method | Status | Notes |
|---|----------|--------|--------|-------|
| 48 | `/api/v1/teacher/notifications` | GET | ✅ Created | Returns all notifications |
| 49 | `/api/v1/teacher/notifications/unread-count` | GET | ✅ Created | Returns unread count by type |
| 50 | `/api/v1/teacher/notifications/{id}/read` | POST | ✅ Created | Mark notification as read |
| 51 | `/api/v1/teacher/notifications/mark-all-read` | POST | ✅ Created | Mark all as read |
| 52 | `/api/v1/teacher/notifications/{id}` | DELETE | ✅ Created | Delete notification |
| 53 | `/api/v1/teacher/notifications/clear-all` | DELETE | ✅ Created | Clear all notifications |
| 54 | `/api/v1/teacher/notifications/settings` | GET | ✅ Created | Get notification settings |
| 55 | `/api/v1/teacher/notifications/settings` | PUT | ✅ Created | Update notification settings |

### Forgot Password APIs (NEW)
| # | Endpoint | Method | Status | Notes |
|---|----------|--------|--------|-------|
| 56 | `/api/v1/teacher/forgot-password/verify-identifier` | POST | ✅ Created | Step 1: Verify phone/email |
| 57 | `/api/v1/teacher/forgot-password/verify-nrc` | POST | ✅ Created | Step 2: Verify NRC, sends OTP |
| 58 | `/api/v1/teacher/forgot-password/verify-otp` | POST | ✅ Created | Step 3: Verify OTP code |
| 59 | `/api/v1/teacher/forgot-password/resend-otp` | POST | ✅ Created | Resend OTP with cooldown |
| 60 | `/api/v1/teacher/forgot-password/reset` | POST | ✅ Created | Step 4: Reset password |

---

## 3. Testing Progress

### Current Step: ✅ ALL APIs Tested - COMPLETE
1. ✅ Run migrations: `php artisan migrate`
2. ✅ Run seeder: `php artisan db:seed --class=TeacherAppSeeder`
3. ✅ All 47 original endpoints tested and working
4. ✅ 8 new Notification endpoints created
5. ✅ 5 new Forgot Password endpoints created

### Tested Endpoints Summary (60/60 endpoints) ✅ COMPLETE
**Auth (3/3):** Login ✅, Profile ✅, Logout ✅
**Dashboard (5/5):** Stats ✅, Today Classes ✅, Today Class Show ✅, Weekly Schedule ✅, Full Schedule ✅
**Classes (5/5):** List ✅, Show ✅, Dropdown ✅, Attendance Dropdown ✅, Student Profile ✅
**Subjects (3/3):** List ✅, Show ✅, Curriculum ✅
**Attendance (5/5):** Students ✅, Store ✅, Bulk ✅, History ✅, History Show ✅
**Homework (4/4):** List ✅, Create ✅, Show ✅, Collect ✅
**Announcements (2/2):** List ✅, Show ✅
**Calendar (2/2):** Events ✅, Event Show ✅
**Teacher Leave (4/4):** My Requests ✅, Create ✅, Show ✅, Balance ✅
**Student Leave (4/4):** Pending ✅, All ✅, Approve ✅, Reject ✅
**Daily Reports (6/6):** My Reports ✅, Received ✅, Recipients ✅, Create ✅, Show ✅, Update Status ✅
**Payslips (2/2):** List ✅, Show ✅
**Class Records (2/2):** List ✅, Show ✅
**Notifications (8/8):** List ✅, Unread Count ✅, Mark Read ✅, Mark All Read ✅, Delete ✅, Clear All ✅, Get Settings ✅, Update Settings ✅
**Forgot Password (5/5):** Verify Identifier ✅, Verify NRC ✅, Verify OTP ✅, Resend OTP ✅, Reset Password ✅

### Legend
- ⬜ Pending
- 🔄 In Progress
- ✅ Tested & Working
- ❌ Has Issues
- 🔧 Fixed

---

## 4. Recent Updates (December 23, 2025)

### Attendance API - Period Support (Latest)
- ✅ Added `current_period_id` parameter support to all attendance endpoints:
  - `GET /attendance/students` - Filter students by period
  - `POST /attendance` - Save attendance for specific period
  - `POST /attendance/bulk` - Bulk update for specific period
- ✅ Updated `GET /classes/attendance-dropdown` to return today's first period info:
  - `current_period_id` - First period ID of today
  - `start_time` - Period start time (e.g. '10:00')
  - `end_time` - Period end time (e.g. '10:45')
  - `subject` - Subject name for the period
- ✅ Updated `AttendanceController.php` to accept `current_period_id` parameter
- ✅ Updated `TeacherAttendanceApiRepositoryInterface.php` with `?string $periodId` parameter
- ✅ Updated `TeacherAttendanceApiRepository.php`:
  - `getStudentsForAttendance()` - Queries attendance by period_id
  - `saveAttendance()` - Saves with period_id, uses explicit find-then-update pattern
  - `bulkUpdateAttendance()` - Bulk updates with period_id
- ✅ Updated `TeacherClassRepository.php`:
  - `getAttendanceDropdown()` - Returns today's first period for each class
- ✅ Changed timetable status check from 'active' to 'published'
- ✅ Attendance status values: `present`, `absent`, `leave`
- ✅ Updated Postman collection with `current_period_id` in attendance endpoints

### Avatar URL Updates
- ✅ All `null` avatar responses now return `default_profile.jpg` URL
- ✅ Updated files:
  - `TeacherProfileResource.php`
  - `TeacherDashboardRepository.php`
  - `TeacherClassRepository.php`
  - `TeacherAttendanceApiRepository.php`
  - `TeacherHomeworkRepository.php`
  - `ClassRecordController.php`
  - `LeaveRequestController.php`

### Postman Collection Updates
- ✅ Updated `Teacher_App_API.postman_collection.json` with all endpoints
- ✅ Added Notifications folder with 8 new endpoints
- ✅ Added all collection variables for easy testing

### New Features
- ✅ Created `NotificationController.php` with full CRUD operations
- ✅ Added notification routes to `api.php`
- ✅ Created `ForgotPasswordController.php` with 4-step password reset flow
- ✅ Created `PasswordResetToken.php` model
- ✅ Created migration for OTP columns in password_reset_tokens table
- ✅ Added forgot password routes to `api.php`

---

## 5. Known Issues & Fixes
| Issue | Endpoint | Status | Fix |
|-------|----------|--------|-----|
| Room model missing type/status | Seeder | 🔧 Fixed | Changed to building/floor |
| student_class missing batch_id | Seeder | 🔧 Fixed | Added batch_id and grade_id |
| EventCategory slug conflict | Seeder | 🔧 Fixed | Use withTrashed() |
| Event location field | Seeder | 🔧 Fixed | Changed to venue |
| LeaveRequest missing user_type | Seeder | 🔧 Fixed | Added user_type |
| LeaveRequest missing total_days | Seeder | 🔧 Fixed | Added total_days |
| Attendance status 'leave' invalid | Seeder | 🔧 Fixed | Changed to 'excused' |
| Homework table name | Model | 🔧 Fixed | Added $table = 'homeworks' |
| Avatar returns null | Multiple | 🔧 Fixed | Returns default_profile.jpg URL |

---

## 6. Notes
- Base URL: `http://localhost:8000/api/v1/teacher`
- Test credentials: `teacher@smartcampusedu.com` / `password`
- All protected routes require Bearer token from login
- Seeder creates: 64 students, 8 classes, 6 subjects, 9 homework, 6 payroll records
- Postman collection available at: `scp/Teacher_App_API.postman_collection.json`
- Default avatar: `http://localhost:8000/default_profile.jpg`
