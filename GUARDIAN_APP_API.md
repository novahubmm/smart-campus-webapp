# Guardian App API Documentation

## Base URL
```
/api/v1/guardian
```

## Authentication
All protected endpoints require Bearer token authentication.

```
Authorization: Bearer {token}
```

---

## Authentication Endpoints

### Login
```
POST /auth/login
```
**Body:**
```json
{
  "login": "email_or_phone",
  "password": "password",
  "device_name": "guardian_app"
}
```

### Logout
```
POST /auth/logout
```

### Get Profile
```
GET /auth/profile
```

### Change Password
```
POST /auth/change-password
```
**Body:**
```json
{
  "current_password": "old_password",
  "new_password": "new_password",
  "new_password_confirmation": "new_password"
}
```

### Get Guardian's Students
```
GET /students
```

---

## Dashboard Endpoints

### Home Dashboard
```
GET /home/dashboard?student_id={id}
```

### Today's Schedule
```
GET /today-schedule?student_id={id}
```

**Response:**
```json
{
  "success": true,
  "message": "Success",
  "data": [
    {
      "id": "019be94a-00d4-735e-bd6d-a26059c207a8",
      "period_number": 1,
      "subject": "Art & Craft",
      "subject_icon": "🎨",
      "subject_color": "#F97316",
      "teacher": "Thiri Yint",
      "time": "08:00 - 08:45",
      "start_time": "08:00:00",
      "end_time": "08:45:00",
      "room": "Room 102",
      "is_break": false,
      "is_active": false
    },
    {
      "id": "019be94a-00d5-73d0-9425-95b93d6383bc",
      "period_number": 6,
      "subject": "Myanmar",
      "subject_icon": "ဃ",
      "subject_color": "#8B5CF6",
      "teacher": "Hein Su",
      "time": "12:00 - 12:45",
      "start_time": "12:00:00",
      "end_time": "12:45:00",
      "room": "Room 102",
      "is_break": false,
      "is_active": true
    }
  ]
}
```

**Note:** The `is_active` field indicates whether the current server time falls within the period's time range. Only one period will have `is_active: true` at any given time.

### Upcoming Homework
```
GET /upcoming-homework?student_id={id}
```

### Recent Announcements
```
GET /announcements/recent?student_id={id}&limit={limit}
```

### Fee Reminder
```
GET /fee-reminder?student_id={id}
```

---

## Student Profile Endpoints

### Get Student Profile
```
GET /students/{id}/profile
```

### Get Academic Summary
```
GET /students/{id}/academic-summary
```

### Get Rankings
```
GET /students/{id}/rankings
```

### Get Achievements
```
GET /students/{id}/achievements
```

### Goals
```
GET    /students/{id}/goals
POST   /students/{id}/goals
PUT    /students/{id}/goals/{goalId}
DELETE /students/{id}/goals/{goalId}
```

### Notes
```
GET    /students/{id}/notes
POST   /students/{id}/notes
PUT    /students/{id}/notes/{noteId}
DELETE /students/{id}/notes/{noteId}
```

---

## Attendance Endpoints

### Get Attendance Records
```
GET /attendance?student_id={id}&month={month}&year={year}
```

### Get Attendance Summary
```
GET /attendance/summary?student_id={id}&month={month}&year={year}
```

### Get Attendance Calendar
```
GET /attendance/calendar?student_id={id}&month={month}&year={year}
```

### Get Attendance Stats
```
GET /attendance/stats?student_id={id}
```

---

## Exam Endpoints

### Get Exams List
```
GET /exams?student_id={id}&subject_id={subject_id}
```

### Get Exam Detail
```
GET /exams/{id}
```

### Get Exam Results
```
GET /exams/{id}/results?student_id={id}
```

---

## Subject Endpoints

### Get Subjects List
```
GET /subjects?student_id={id}
```

### Get Subject Detail
```
GET /subjects/{id}?student_id={id}
```

### Get Subject Performance
```
GET /subjects/{id}/performance?student_id={id}
```

---

## Homework Endpoints

### Get Homework List
```
GET /homework?student_id={id}&status={status}&subject_id={subject_id}
```
Status: `pending`, `completed`, `overdue`

### Get Homework Detail
```
GET /homework/{id}?student_id={id}
```

### Get Homework Stats
```
GET /homework/stats?student_id={id}
```

### Update Homework Status
```
PUT /homework/{id}/status
```
**Body:**
```json
{
  "student_id": "student_uuid",
  "status": "completed"
}
```

---

## Timetable Endpoints

### Get Full Timetable
```
GET /timetable?student_id={id}
```

### Get Day Timetable
```
GET /timetable/day?student_id={id}&day={day}
```
Day: `Monday`, `Tuesday`, `Wednesday`, `Thursday`, `Friday`, `Saturday`

### Get Class Info
```
GET /classes/{id}?student_id={id}
```

---

## Announcement Endpoints

### Get Announcements List
```
GET /announcements?student_id={id}&category={category}&is_read={boolean}
```

### Get Announcement Detail
```
GET /announcements/{id}
```

### Mark as Read
```
POST /announcements/{id}/read
```

### Mark All as Read
```
POST /announcements/mark-all-read
```

---

## Fee Endpoints

### Get Fees List
```
GET /fees?student_id={id}&status={status}
```
Status: `pending`, `paid`

### Get Fee Summary
```
GET /fees/summary?student_id={id}
```

### Get Pending Fees
```
GET /fees/pending?student_id={id}
```

### Get Fee History
```
GET /fees/history?student_id={id}
```

### Get Payment Methods
```
GET /payment-methods
```

### Submit Payment
```
POST /payments
```
**Body:**
```json
{
  "student_id": "student_uuid",
  "amount": 150000,
  "payment_method_id": 1,
  "months_paid": 1,
  "notes": "optional notes"
}
```

### Upload Receipt
```
POST /payments/upload-receipt
```
**Form Data:**
- `payment_id`: string
- `receipt`: file (jpg, jpeg, png, pdf)

### Get Payment Detail
```
GET /payments/{id}
```

---

## Leave Request Endpoints

### Get Leave Requests
```
GET /leave-requests?student_id={id}&status={status}
```
Status: `pending`, `approved`, `rejected`

### Get Leave Request Detail
```
GET /leave-requests/{id}
```

### Create Leave Request
```
POST /leave-requests
```
**Body:**
```json
{
  "student_id": "student_uuid",
  "leave_type": "Sick Leave",
  "start_date": "2025-01-25",
  "end_date": "2025-01-27",
  "reason": "Fever and cold"
}
```

### Update Leave Request
```
PUT /leave-requests/{id}
```

### Delete Leave Request
```
DELETE /leave-requests/{id}
```

### Get Leave Stats
```
GET /leave-requests/stats?student_id={id}
```

### Get Leave Types
```
GET /leave-types
```

---

## Notification Endpoints

### Get Notifications
```
GET /notifications?category={category}&is_read={boolean}
```

### Get Unread Count
```
GET /notifications/unread-count
```

### Mark as Read
```
POST /notifications/{id}/read
```

### Mark All as Read
```
POST /notifications/mark-all-read
```

### Get Notification Settings
```
GET /notifications/settings
```

### Update Notification Settings
```
PUT /notifications/settings
```
**Body:**
```json
{
  "push_enabled": true,
  "email_enabled": false,
  "categories": {
    "announcements": true,
    "attendance": true,
    "exams": true
  }
}
```

---

## Curriculum Endpoints

### Get Curriculum Overview
```
GET /curriculum?student_id={id}
```

### Get Subject Curriculum
```
GET /curriculum/subjects/{id}?student_id={id}
```

### Get Chapters
```
GET /curriculum/chapters?subject_id={id}
```

### Get Chapter Detail
```
GET /curriculum/chapters/{id}
```

---

## Report Card Endpoints

### Get Report Cards List
```
GET /report-cards?student_id={id}
```

### Get Report Card Detail
```
GET /report-cards/{id}?student_id={id}
```

---

## Settings Endpoints

### Get Settings
```
GET /settings
```

### Update Settings
```
PUT /settings
```
**Body:**
```json
{
  "language": "en",
  "theme": "light",
  "notifications": {
    "push_enabled": true,
    "email_enabled": false
  },
  "preferences": {
    "show_grades": true,
    "show_ranks": true
  }
}
```

---

## School Info Endpoints

### Get School Info
```
GET /school/info
```

### Get School Rules
```
GET /school/rules
```

### Get School Contact
```
GET /school/contact
```

### Get School Facilities
```
GET /school/facilities
```

---

## Device Token Endpoints (Push Notifications)

### Register Device Token
```
POST /device-tokens
```
**Body:**
```json
{
  "token": "fcm_token",
  "device_type": "android"
}
```

### Remove Device Token
```
DELETE /device-tokens
```

---

## Response Format

All responses follow this format:

### Success Response
```json
{
  "success": true,
  "message": "Success message",
  "data": { ... }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": { ... }
}
```

---

## Total Endpoints: 80

### By Category:
- Authentication: 4
- Dashboard: 5
- Student Profile: 14
- Attendance: 4
- Exams: 3
- Subjects: 3
- Homework: 4
- Timetable: 3
- Announcements: 4
- Fees & Payments: 8
- Leave Requests: 7
- Notifications: 6
- Curriculum: 4
- Report Cards: 2
- Settings: 2
- School Info: 4
- Device Tokens: 2
- Public Rules: 2
