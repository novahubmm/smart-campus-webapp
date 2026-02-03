# Demo Ready Seeder - Final Todo List

## Overview
Create a comprehensive `DemoReadySeeder` that populates the database with realistic demo data for a Myanmar school that opened last week.

## Configuration
- **School Open Date**: Last Monday (calculated dynamically from current date)
- **All Passwords**: `password`
- **Email Domain**: `@smartcampusedu.com`
- **All Names**: Unique Myanmar names

---

## 1. Prerequisites (Run existing seeders first)
- [x] RolePermissionSeeder (roles: admin, staff, teacher, student, guardian)
- [x] SettingSeeder (school settings)
- [x] FacilitySeeder (15 facilities)
- [x] GradeCategorySeeder (Primary, Middle School, High School)
- [x] SubjectTypeSeeder (Core, Elective)
- [x] AnnouncementTypeSeeder (Urgent, Event, Academic, Holiday, Meeting)
- [x] DailyReportRecipientSeeder

---

## 2. Exam Types (Create in DemoReadySeeder)
| Name | 
|------|
| Monthly Test |
| Mid-term |
| Final |

---

## 3. Users & Profiles

### 3.1 Admin User (1)
| Field | Value |
|-------|-------|
| Name | Myanmar name (unique) |
| Email | admin@smartcampusedu.com |
| Password | password |
| Role | admin |

### 3.2 Key Contacts / Management (4 users with StaffProfile)
| Role | Email | Department |
|------|-------|------------|
| Principal | principal@smartcampusedu.com | Management |
| Vice Principal | viceprincipal@smartcampusedu.com | Management |
| Finance Manager | finance@smartcampusedu.com | Finance |
| Operation Manager | operations@smartcampusedu.com | Management |

### 3.3 Departments (3)
1. Finance Department
2. Management Department
3. Teaching Department

### 3.4 Staff (10 users)
- Employee ID format: `STF-XXXXX` (5 random digits)
- Email: `staff{n}@smartcampusedu.com`
- Role: staff
- Department: Random (Finance or Management)
- Create StaffProfile with:
  - position: Random staff position
  - hire_date: Random date in last 1-3 years
  - basic_salary: 300,000 - 500,000 MMK
  - gender, dob, address, etc.

### 3.5 Teachers (78 users)
- Employee ID format: `TCH-XXXXX` (5 random digits)
- Email: `teacher{n}@smartcampusedu.com`
- Role: teacher
- Department: Teaching Department
- Create TeacherProfile with:
  - position: "Teacher"
  - hire_date: Random date in last 1-5 years
  - basic_salary: 400,000 - 800,000 MMK
  - gender, dob, address, qualification, etc.

### 3.6 Students (1,170 users = 30 × 39 classes)
- Student ID format: `STU-XXXXX` (5 random digits)
- Email: `student{n}@smartcampusedu.com`
- Role: student
- Create StudentProfile with:
  - student_identifier: STU-XXXXX
  - class_id, grade_id
  - date_of_joining: School open date
  - gender, dob, address, parent info, etc.
  - status: 'active'

### 3.7 Guardians (1,170 users)
- Email: `guardian{n}@smartcampusedu.com`
- Role: guardian
- Create GuardianProfile with:
  - occupation: Random occupation
  - address: Same as student
- Link to student via `guardian_student` pivot table:
  - relationship: 'parent'
  - is_primary: true

---

## 4. Academic Structure

### 4.1 Batch (1)
```
name: '2025-2026'
start_date: School open date
end_date: +10 months
```

### 4.2 Grades (13 grades: 0-12)
| Grade Level | Category | Grade Category ID |
|-------------|----------|-------------------|
| 0 | Primary | (from GradeCategorySeeder) |
| 1-4 | Primary | |
| 5-8 | Middle School | |
| 9-12 | High School | |

### 4.3 Rooms (39 rooms)
```
name: 'Room 101' to 'Room 139'
building: Random (Building A, Building B, Building C)
floor: 1-3 based on room number
capacity: 35-40
status: 'active'
```
- Attach 3-6 random facilities via `facility_room` pivot

### 4.4 Subjects (78 subjects = 6 per grade × 13 grades)

**Grade 0 (KG):**
- Myanmar, English, Mathematics, General Science, Art & Craft, Physical Education

**Grade 1-4 (Primary):**
- Myanmar, English, Mathematics, Science, Social Studies, Art

**Grade 5-8 (Middle School):**
- Myanmar, English, Mathematics, Science, History, Geography

**Grade 9-12 (High School):**
- Myanmar, English, Mathematics, Physics, Chemistry, Biology

Each subject:
- code: Unique code (e.g., 'MM-G0', 'EN-G1')
- subject_type_id: Core (from SubjectTypeSeeder)
- Link to grade via `grade_subject` pivot
- Assign 1 unique teacher via `subject_teacher` pivot

### 4.5 Classes (39 classes = 3 per grade)
```
name: 'Grade 0 A', 'Grade 0 B', 'Grade 0 C', ... 'Grade 12 C'
grade_id: (corresponding grade)
batch_id: (the batch)
teacher_id: (unique class teacher from 78 teachers, 39 will be class teachers)
room_id: (corresponding room)
```

---

## 5. Timetables (39 timetables)

### 5.1 Timetable Configuration
```
batch_id, grade_id, class_id
name: 'Timetable - Grade X Y'
status: 'published'
is_active: true
published_at: School open date
effective_from: School open date
minutes_per_period: 45
break_duration: 15
school_start_time: '08:00'
school_end_time: '14:30'
week_days: ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']
```

### 5.2 Periods (35 per timetable = 7 periods × 5 days)
| Period | Time | Type |
|--------|------|------|
| 1 | 08:00 - 08:45 | Class |
| 2 | 08:45 - 09:30 | Class |
| 3 | 09:30 - 10:15 | Class |
| Break | 10:15 - 10:30 | Break |
| 4 | 10:30 - 11:15 | Class |
| 5 | 11:15 - 12:00 | Class |
| Lunch | 12:00 - 13:00 | Break |
| 6 | 13:00 - 13:45 | Class |
| 7 | 13:45 - 14:30 | Class |

Each period:
```
timetable_id
day_of_week: 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'
period_number: 1-7
starts_at, ends_at
is_break: false (true for breaks)
subject_id: (rotate 6 subjects)
teacher_profile_id: (subject's teacher)
room_id: (class room)
```

---

## 6. Events (11 events)

| # | Type | Title | Start Date | End Date |
|---|------|-------|------------|----------|
| 1 | holiday | School Opening Ceremony | Last Monday | Last Monday |
| 2 | meeting | Staff Orientation Meeting | Last Tuesday | Last Tuesday |
| 3 | cultural | Welcome Assembly | Last Wednesday | Last Wednesday |
| 4 | meeting | Parent-Teacher Meeting | Last Thursday | Last Thursday |
| 5 | sports | Morning Exercise Program Launch | Last Friday | Last Friday |
| 6 | meeting | First Week Review Meeting | This Monday | This Monday |
| 7 | meeting | Department Heads Meeting | This Tuesday | This Tuesday |
| 8 | cultural | Cultural Day Celebration | This Wednesday | This Wednesday |
| 9 | holiday | Christmas Eve | Today | Today |
| 10 | holiday | Christmas Day | Tomorrow | Tomorrow |
| 11 | sports | Annual Sports Day | Next Monday | Next Monday |

Each event:
```
title, description
type: 'holiday', 'meeting', 'sports', 'cultural'
start_date, end_date
start_time: '09:00', end_time: '16:00'
venue: 'School Auditorium' / 'Sports Ground' / etc.
status: true
```

---

## 7. Announcements (15 announcements)

| Type | Count | Titles |
|------|-------|--------|
| Urgent | 2 | Emergency Contact Update, Safety Guidelines Reminder |
| Event | 4 | Welcome Assembly Notice, Sports Day Registration, Cultural Day Invitation, Christmas Celebration |
| Academic | 4 | Monthly Exam Schedule, Homework Submission Policy, Library Hours, Study Materials Available |
| Holiday | 3 | Christmas Holiday Notice, New Year Schedule, Winter Break Announcement |
| Meeting | 2 | Staff Meeting Notice, PTA Meeting Invitation |

Each announcement:
```
title, content
announcement_type_id: (from AnnouncementTypeSeeder)
priority: 'high', 'medium', 'low'
target_roles: ['teacher', 'student', 'guardian']
publish_date: (within last week to today)
is_published: true
status: true
created_by: admin user id
```

---

## 8. Exams (13 exams - 1 per grade)

### 8.1 Exam Configuration
```
exam_id: 'EXD-XXXXX' (5 random digits)
name: 'Monthly Test - Grade X'
exam_type_id: (Monthly Test)
batch_id, grade_id
start_date: Yesterday (Dec 23)
end_date: +5 days (Dec 28)
status: true
```

### 8.2 Exam Schedules (6 per exam = 78 total)
| Day | Date | Subject # |
|-----|------|-----------|
| 1 | Yesterday (Dec 23) | Subject 1 |
| 2 | Today (Dec 24) | Subject 2 |
| 3 | Dec 25 | Subject 3 |
| 4 | Dec 26 | Subject 4 |
| 5 | Dec 27 | Subject 5 |
| 6 | Dec 28 | Subject 6 |

Each schedule:
```
exam_id
subject_id: (grade's subject for that day)
exam_date
start_time: '09:00'
end_time: '11:00'
room_id: (random room)
total_marks: 100
passing_marks: 40
```

**Note:** No exam marks created - will be filled manually for yesterday's exam.

---

## 9. Leave Requests (13 total)

### 9.1 Staff Leave Requests (3)
Random 3 from 10 staff:
```
user_id, user_type: 'staff'
leave_type: Random ('casual', 'medical', 'earned', 'emergency', 'other')
start_date, end_date: Within last week to next week
total_days: 1-3
reason: Random reason
status: Random ('pending', 'approved', 'rejected')
```

### 9.2 Teacher Leave Requests (10)
Random 10 from 78 teachers:
```
user_id, user_type: 'teacher'
leave_type: Random
start_date, end_date: Within last week to next week
total_days: 1-3
reason: Random reason
status: Random ('pending', 'approved', 'rejected')
```

---

## 10. Attendance Records

### 10.1 Working Days Calculation
- From: Last Monday (school open date)
- To: Today
- Exclude: Saturday, Sunday
- Expected: ~5-6 working days

### 10.2 Student Attendance (~7,000 records)
For each student (1,170), for each working day:
```
student_id
period_id: (first period of the day for that class)
date
status: Weighted random
  - 'present': 85%
  - 'absent': 5%
  - 'late': 8%
  - 'leave': 2%
marked_by: admin user id
collect_time: '08:00'
```

### 10.3 Teacher Attendance (~470 records)
For each teacher (78), for each working day:
```
teacher_id
date
status: Weighted random
  - 'present': 90%
  - 'absent': 3%
  - 'late': 5%
  - 'leave': 2%
marked_by: admin user id
start_time: '07:45'
end_time: '15:00'
```

### 10.4 Staff Attendance (~60 records)
For each staff (10), for each working day:
```
staff_id
date
status: Weighted random
  - 'present': 90%
  - 'absent': 3%
  - 'late': 5%
  - 'leave': 2%
marked_by: admin user id
start_time: '08:00'
end_time: '17:00'
```

---

## 11. Finance

### 11.1 Grade Fees (via FeeStructure or direct)
Random fee per grade: 5,000 - 20,000 MMK

### 11.2 Student Fee Records (1,170 records)
```
student_id
amount: Grade fee
amount_due: amount
amount_paid: Random (0, partial, full)
status: Based on payment
  - 'paid': 60%
  - 'pending': 30%
  - 'partial': 10%
due_date: End of current month
```

### 11.3 Payroll Records (88 records = 78 teachers + 10 staff)
For current month (December 2025):
```
employee_type: 'teacher' or 'staff'
employee_id: teacher_profile_id or staff_profile_id
year: 2025
month: 12
working_days: ~22
days_present: Based on attendance
leave_days, days_absent: Based on attendance
basic_salary: From profile
attendance_allowance: 50,000 if full attendance
loyalty_bonus: 0-100,000 based on years
other_bonus: 0
amount: Total
status: 'pending' or 'paid'
```

---

## 12. Settings Update
```php
Setting::first()->update([
    'school_name' => 'Smart Campus International School',
    'school_email' => 'info@smartcampusedu.com',
    'school_phone' => '+95 9 123 456 789',
    'school_address' => 'No. 123, University Avenue, Yangon, Myanmar',
    'principal_name' => (Principal's Myanmar name),
    'setup_completed_school_info' => true,
    'setup_completed_academic' => true,
    'setup_completed_event_and_announcements' => true,
    'setup_completed_time_table_and_attendance' => true,
    'setup_completed_finance' => true,
]);
```

---

## Summary Statistics

| Entity | Count |
|--------|-------|
| Admin | 1 |
| Key Contacts (Staff) | 4 |
| Staff | 10 |
| Teachers | 78 |
| Students | 1,170 |
| Guardians | 1,170 |
| **Total Users** | **2,433** |
| Departments | 3 |
| Exam Types | 3 |
| Batch | 1 |
| Grades | 13 |
| Rooms | 39 |
| Subjects | 78 |
| Classes | 39 |
| Timetables | 39 |
| Periods | ~1,365 (35 × 39) |
| Events | 11 |
| Announcements | 15 |
| Exams | 13 |
| Exam Schedules | 78 |
| Leave Requests | 13 |
| Student Attendance | ~7,000 |
| Teacher Attendance | ~470 |
| Staff Attendance | ~60 |
| Student Fees | 1,170 |
| Payroll Records | 88 |

---

## Execution Order

1. ✅ Ensure prerequisite seeders have run
2. Create ExamTypes
3. Create Departments
4. Create Admin User
5. Create Key Contacts (4 staff with management roles)
6. Create Staff Users & Profiles (10)
7. Create Teachers Users & Profiles (78)
8. Create Batch
9. Create Grades (13)
10. Create Rooms (39)
11. Create Subjects (78) with teacher assignments
12. Create Classes (39) with class teachers and rooms
13. Create Students Users & Profiles (1,170)
14. Create Guardians Users & Profiles (1,170) with student links
15. Create Timetables (39) with Periods
16. Create Events (11)
17. Create Announcements (15)
18. Create Exams (13) with Schedules (78)
19. Create Leave Requests (13)
20. Create Attendance Records (Student, Teacher, Staff)
21. Create Student Fee Records (1,170)
22. Create Payroll Records (88)
23. Update Settings

---

## Performance Optimizations

- Use `DB::disableQueryLog()` to save memory
- Use database transactions for data integrity
- Use `insert()` for bulk inserts instead of `create()`
- Chunk large datasets (students, guardians, attendance)
- Disable model events during seeding
- Use `DB::table()->insert()` for pivot tables

---

## Myanmar Names Pool (200+ unique names needed)

Will generate unique combinations from:
- First names: Aung, Min, Zaw, Htet, Kyaw, Myo, Tun, Naing, Htun, Thiha, Pyae, Kaung, Ye, Wai, Hein, Phyo, Nay, Ko, Moe, Thura, Aye, Su, Thin, May, Khin, Ei, Phyu, Hnin, Thida, Sandar, Myat, Yadanar, Chaw, Hay, Nwe, Zin, Thiri, etc.
- Second names: Kyaw, Thu, Win, Aung, Zin, Min, Tun, Lin, Htun, Zaw, Sone, Myat, Yint, Yan, Htet, Wai, Myo, Ko, Aye, Su, Thin, Thu, Mar, Ei, Phyu, Hnin, Wai, Moe, Mon, Zar, Noe, Oo, Myat, Hnin, Nwe, Mar, etc.
