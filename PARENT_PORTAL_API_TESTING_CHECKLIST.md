# Parent Portal API - Testing Checklist

**Date:** February 7, 2026  
**Purpose:** Systematic testing guide for all Parent Portal APIs  
**Total APIs:** 80 endpoints

---

## 🎯 TESTING WORKFLOW

### Phase 1: Setup (5 minutes)
- [ ] Import `UNIFIED_APP_POSTMAN_COLLECTION.json` into Postman
- [ ] Verify base_url is correct: `http://192.168.100.114:8088/api/v1`
- [ ] Check server is running
- [ ] Prepare test data (guardian credentials, student IDs)

### Phase 2: Authentication (10 minutes)
- [ ] Test Guardian Login
- [ ] Verify token is returned
- [ ] Check token is saved to `guardian_token` variable
- [ ] Verify `current_token` is set automatically
- [ ] Test Get Profile endpoint
- [ ] Test Change Password
- [ ] Test Logout

### Phase 3: Core APIs (30 minutes)
- [ ] Test Dashboard endpoints
- [ ] Test Notifications
- [ ] Test Device Management

### Phase 4: Parent Portal APIs (2-3 hours)
Test each category systematically...

---

## ✅ DETAILED TESTING CHECKLIST

### 1. AUTHENTICATION (5 endpoints)

#### 1.1 Guardian Login
- [ ] **Request:** POST `/auth/login`
- [ ] **Body:** Valid guardian credentials
- [ ] **Expected:** 200 OK, token returned
- [ ] **Verify:** `guardian_token` variable set
- [ ] **Verify:** `current_token` variable set
- [ ] **Verify:** `user_type` = "guardian"

#### 1.2 Get Profile
- [ ] **Request:** GET `/auth/profile`
- [ ] **Expected:** 200 OK, guardian profile data
- [ ] **Verify:** Name, email, phone present
- [ ] **Verify:** Students list included

#### 1.3 Change Password
- [ ] **Request:** POST `/auth/change-password`
- [ ] **Body:** Current and new password
- [ ] **Expected:** 200 OK, password changed
- [ ] **Test:** Login with new password works

#### 1.4 Logout
- [ ] **Request:** POST `/auth/logout`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Token invalidated

---

### 2. ACADEMIC PERFORMANCE (4 endpoints)

**Setup:** Set `student_id` variable to valid student ID

#### 2.1 Get Academic Overview
- [ ] **Request:** GET `/guardian/academic/{{student_id}}`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Overall grade present
- [ ] **Verify:** GPA value (0.0-4.0)
- [ ] **Verify:** Class rank present
- [ ] **Verify:** Subjects array not empty
- [ ] **Verify:** Recent exams included
- [ ] **Verify:** Grade trends present

#### 2.2 Get Report Cards List
- [ ] **Request:** GET `/guardian/report-cards?student_id={{student_id}}`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Report cards array
- [ ] **Verify:** Each card has term, year, GPA
- [ ] **Verify:** PDF URLs present

#### 2.3 Get Report Card Detail
- [ ] **Request:** GET `/guardian/report-cards/:id?student_id={{student_id}}`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Complete report card data
- [ ] **Verify:** Subject grades included
- [ ] **Verify:** Teacher remarks present
- [ ] **Verify:** Attendance data included

#### 2.4 Download Report Card PDF
- [ ] **Request:** GET `/guardian/report-cards/:id/download`
- [ ] **Expected:** PDF file download
- [ ] **Verify:** Content-Type: application/pdf
- [ ] **Verify:** File size > 0

---

### 3. EXAMS (9 endpoints)

#### 3.1 Get All Exams
- [ ] **Request:** GET `/guardian/exams?student_id={{student_id}}&status=all`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Upcoming exams array
- [ ] **Verify:** Completed exams array
- [ ] **Verify:** Summary statistics

#### 3.2 Get Upcoming Exams
- [ ] **Request:** GET `/guardian/exams?student_id={{student_id}}&status=upcoming`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Only upcoming exams
- [ ] **Verify:** Days remaining calculated
- [ ] **Verify:** Exam date in future

#### 3.3 Get Completed Exams
- [ ] **Request:** GET `/guardian/exams?student_id={{student_id}}&status=completed`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Only completed exams
- [ ] **Verify:** Scores present
- [ ] **Verify:** Grades assigned

#### 3.4 Get Exam Detail
- [ ] **Request:** GET `/guardian/exams/:id`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Complete exam information
- [ ] **Verify:** Syllabus included
- [ ] **Verify:** Study materials present
- [ ] **Verify:** Instructions included

#### 3.5 Get Exam Results
- [ ] **Request:** GET `/guardian/exams/:id/results?student_id={{student_id}}`
- [ ] **Expected:** 200 OK (or 404 if not published)
- [ ] **Verify:** Score and percentage
- [ ] **Verify:** Grade assigned
- [ ] **Verify:** Class rank present
- [ ] **Verify:** Class statistics included

#### 3.6 Get Subjects List
- [ ] **Request:** GET `/guardian/subjects?student_id={{student_id}}`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Subjects array not empty
- [ ] **Verify:** Each subject has name, teacher
- [ ] **Verify:** Icons/colors present

#### 3.7 Get Subject Detail
- [ ] **Request:** GET `/guardian/subjects/:id?student_id={{student_id}}`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Subject information complete
- [ ] **Verify:** Teacher details included

#### 3.8 Get Subject Performance
- [ ] **Request:** GET `/guardian/subjects/:id/performance?student_id={{student_id}}`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Performance metrics
- [ ] **Verify:** Grade trends
- [ ] **Verify:** Comparison data

#### 3.9 Get Subject Schedule
- [ ] **Request:** GET `/guardian/subjects/:id/schedule?student_id={{student_id}}`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Class schedule
- [ ] **Verify:** Time slots correct

---

### 4. LEAVE REQUESTS (7 endpoints)

#### 4.1 Get All Leave Requests
- [ ] **Request:** GET `/guardian/leave-requests?student_id={{student_id}}&status=all`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Requests array
- [ ] **Verify:** Balance information
- [ ] **Verify:** Summary statistics

#### 4.2 Get Pending Requests
- [ ] **Request:** GET `/guardian/leave-requests?student_id={{student_id}}&status=pending`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Only pending requests

#### 4.3 Get Leave Request Stats
- [ ] **Request:** GET `/guardian/leave-requests/stats?student_id={{student_id}}`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Total allowed days
- [ ] **Verify:** Used days
- [ ] **Verify:** Remaining days

#### 4.4 Get Leave Request Detail
- [ ] **Request:** GET `/guardian/leave-requests/:id?student_id={{student_id}}`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Complete request details
- [ ] **Verify:** Status present
- [ ] **Verify:** Approval/rejection info

#### 4.5 Apply for Leave
- [ ] **Request:** POST `/guardian/leave-requests`
- [ ] **Body:** Valid leave request data
- [ ] **Expected:** 201 Created
- [ ] **Verify:** Request ID returned
- [ ] **Verify:** Status = "pending"
- [ ] **Test:** Invalid dates rejected (400)
- [ ] **Test:** Missing fields rejected (422)

#### 4.6 Cancel Leave Request
- [ ] **Request:** DELETE `/guardian/leave-requests/:id`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Request cancelled
- [ ] **Test:** Cannot cancel approved request

#### 4.7 Get Leave Types
- [ ] **Request:** GET `/guardian/leave-types`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Leave types array
- [ ] **Verify:** Each type has name, description

---

### 5. SCHOOL FEES (6 endpoints)

#### 5.1 Get All Fees
- [ ] **Request:** GET `/guardian/fees?student_id={{student_id}}&status=all`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Invoices array
- [ ] **Verify:** Summary totals

#### 5.2 Get Unpaid Fees
- [ ] **Request:** GET `/guardian/fees?student_id={{student_id}}&status=unpaid`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Only unpaid invoices
- [ ] **Verify:** Due dates present

#### 5.3 Get Pending Fees
- [ ] **Request:** GET `/guardian/fees/pending?student_id={{student_id}}`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Pending verification fees

#### 5.4 Get Fee Detail
- [ ] **Request:** GET `/guardian/fees/:id`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Complete fee information
- [ ] **Verify:** Amount, due date present

#### 5.5 Get Payment History
- [ ] **Request:** GET `/guardian/fees/payment-history?student_id={{student_id}}`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Payments array
- [ ] **Verify:** Receipt URLs present

#### 5.6 Initiate Payment
- [ ] **Request:** POST `/guardian/fees/:id/payment`
- [ ] **Body:** Payment method and amount
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Payment ID returned
- [ ] **Verify:** Status = "pending"

---

### 6. STUDENT PROFILE (12 endpoints)

#### 6.1 Get Student Profile
- [ ] **Request:** GET `/guardian/students/{{student_id}}/profile`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Complete profile data
- [ ] **Verify:** Photo URL present
- [ ] **Verify:** Grade and class info

#### 6.2 Get Academic Summary
- [ ] **Request:** GET `/guardian/students/{{student_id}}/academic-summary`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** GPA, grades present
- [ ] **Verify:** Subject performance

#### 6.3 Get Rankings
- [ ] **Request:** GET `/guardian/students/{{student_id}}/rankings`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Class rank
- [ ] **Verify:** Subject rankings

#### 6.4 Get Achievements
- [ ] **Request:** GET `/guardian/students/{{student_id}}/achievements`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Achievements array
- [ ] **Verify:** Awards, certificates

#### 6.5 Get Goals
- [ ] **Request:** GET `/guardian/students/{{student_id}}/goals`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Goals array
- [ ] **Verify:** Progress tracking

#### 6.6 Create Goal
- [ ] **Request:** POST `/guardian/students/{{student_id}}/goals`
- [ ] **Body:** Valid goal data
- [ ] **Expected:** 201 Created
- [ ] **Verify:** Goal ID returned
- [ ] **Test:** Invalid type rejected

#### 6.7 Update Goal
- [ ] **Request:** PUT `/guardian/students/{{student_id}}/goals/:goalId`
- [ ] **Body:** Updated goal data
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Goal updated

#### 6.8 Delete Goal
- [ ] **Request:** DELETE `/guardian/students/{{student_id}}/goals/:goalId`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Goal deleted

#### 6.9 Get Notes
- [ ] **Request:** GET `/guardian/students/{{student_id}}/notes`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Notes array

#### 6.10 Create Note
- [ ] **Request:** POST `/guardian/students/{{student_id}}/notes`
- [ ] **Body:** Valid note data
- [ ] **Expected:** 201 Created
- [ ] **Verify:** Note ID returned

#### 6.11 Update Note
- [ ] **Request:** PUT `/guardian/students/{{student_id}}/notes/:noteId`
- [ ] **Body:** Updated note data
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Note updated

#### 6.12 Delete Note
- [ ] **Request:** DELETE `/guardian/students/{{student_id}}/notes/:noteId`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Note deleted

---

### 7. CURRICULUM (4 endpoints)

#### 7.1 Get Curriculum Overview
- [ ] **Request:** GET `/guardian/curriculum?student_id={{student_id}}`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Subjects with progress
- [ ] **Verify:** Overall percentage

#### 7.2 Get Subject Curriculum
- [ ] **Request:** GET `/guardian/curriculum/subjects/:id?student_id={{student_id}}`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Chapters list
- [ ] **Verify:** Progress tracking

#### 7.3 Get Chapters
- [ ] **Request:** GET `/guardian/curriculum/chapters?student_id={{student_id}}&subject_id=:id`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Chapters array
- [ ] **Verify:** Completion status

#### 7.4 Get Chapter Detail
- [ ] **Request:** GET `/guardian/curriculum/chapters/:id?student_id={{student_id}}`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Topics list
- [ ] **Verify:** Related homework/exams

---

### 8. CLASS INFORMATION (4 endpoints)

#### 8.1 Get Class Information
- [ ] **Request:** GET `/guardian/class-info?student_id={{student_id}}`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Class details
- [ ] **Verify:** Teachers list

#### 8.2 Get Class Detail
- [ ] **Request:** GET `/guardian/classes/:id`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Complete class info

#### 8.3 Get Timetable
- [ ] **Request:** GET `/guardian/timetable?student_id={{student_id}}`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Weekly schedule
- [ ] **Verify:** All days included

#### 8.4 Get Day Timetable
- [ ] **Request:** GET `/guardian/timetable/day?student_id={{student_id}}&date=2026-02-07`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Day schedule
- [ ] **Verify:** Periods with times

---

### 9. ATTENDANCE (4 endpoints)

#### 9.1 Get Attendance Records
- [ ] **Request:** GET `/guardian/attendance?student_id={{student_id}}`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Attendance records
- [ ] **Verify:** Dates and status

#### 9.2 Get Attendance Summary
- [ ] **Request:** GET `/guardian/attendance/summary?student_id={{student_id}}`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Present/absent counts
- [ ] **Verify:** Percentage calculated

#### 9.3 Get Attendance Calendar
- [ ] **Request:** GET `/guardian/attendance/calendar?student_id={{student_id}}&month=2&year=2026`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Calendar data
- [ ] **Verify:** Status for each day

#### 9.4 Get Attendance Stats
- [ ] **Request:** GET `/guardian/attendance/stats?student_id={{student_id}}`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Detailed statistics
- [ ] **Verify:** Trends included

---

### 10. HOMEWORK (5 endpoints)

#### 10.1 Get Homework List
- [ ] **Request:** GET `/guardian/homework?student_id={{student_id}}`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Homework array
- [ ] **Verify:** Due dates present

#### 10.2 Get Homework Stats
- [ ] **Request:** GET `/guardian/homework/stats?student_id={{student_id}}`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Completion statistics

#### 10.3 Get Homework Detail
- [ ] **Request:** GET `/guardian/homework/:id`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Complete homework info
- [ ] **Verify:** Instructions included

#### 10.4 Submit Homework
- [ ] **Request:** POST `/guardian/homework/:id/submit`
- [ ] **Body:** Submission data
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Submission recorded

#### 10.5 Update Homework Status
- [ ] **Request:** PUT `/guardian/homework/:id/status`
- [ ] **Body:** Status update
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Status updated

---

### 11. ANNOUNCEMENTS (5 endpoints)

#### 11.1 Get Announcements
- [ ] **Request:** GET `/guardian/announcements?student_id={{student_id}}`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Announcements array
- [ ] **Verify:** Dates and titles

#### 11.2 Get Recent Announcements
- [ ] **Request:** GET `/guardian/announcements/recent?student_id={{student_id}}`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Recent announcements only

#### 11.3 Get Announcement Detail
- [ ] **Request:** GET `/guardian/announcements/:id`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Complete announcement
- [ ] **Verify:** Content present

#### 11.4 Mark as Read
- [ ] **Request:** POST `/guardian/announcements/:id/read`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Marked as read

#### 11.5 Mark All as Read
- [ ] **Request:** POST `/guardian/announcements/mark-all-read`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** All marked as read

---

### 12. SCHOOL INFORMATION (4 endpoints)

#### 12.1 Get School Information
- [ ] **Request:** GET `/guardian/school/info`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** School details
- [ ] **Verify:** Contact information

#### 12.2 Get School Rules
- [ ] **Request:** GET `/guardian/school/rules`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Rules categories
- [ ] **Verify:** Rules list

#### 12.3 Get Contact Information
- [ ] **Request:** GET `/guardian/school/contact`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Contact details
- [ ] **Verify:** Department info

#### 12.4 Get School Facilities
- [ ] **Request:** GET `/guardian/school/facilities`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Facilities list
- [ ] **Verify:** Descriptions

---

### 13. NOTIFICATIONS (6 endpoints)

#### 13.1 Get Notifications
- [ ] **Request:** GET `/notifications`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Notifications array
- [ ] **Verify:** Pagination working

#### 13.2 Get Unread Count
- [ ] **Request:** GET `/notifications/unread-count`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Count number

#### 13.3 Mark as Read
- [ ] **Request:** POST `/notifications/:id/read`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Notification marked

#### 13.4 Mark All as Read
- [ ] **Request:** POST `/notifications/mark-all-read`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** All marked

#### 13.5 Get Notification Settings
- [ ] **Request:** GET `/notifications/settings`
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Settings object

#### 13.6 Update Notification Settings
- [ ] **Request:** PUT `/notifications/settings`
- [ ] **Body:** Settings data
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Settings updated

---

### 14. DEVICE MANAGEMENT (2 endpoints)

#### 14.1 Register Device Token
- [ ] **Request:** POST `/device-tokens`
- [ ] **Body:** Device token data
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Token registered

#### 14.2 Remove Device Token
- [ ] **Request:** DELETE `/device-tokens`
- [ ] **Body:** Device token
- [ ] **Expected:** 200 OK
- [ ] **Verify:** Token removed

---

## 🔍 ERROR TESTING

### Test Error Scenarios

#### Authentication Errors
- [ ] Test login with invalid credentials (401)
- [ ] Test API call without token (401)
- [ ] Test API call with expired token (401)
- [ ] Test API call with invalid token (401)

#### Authorization Errors
- [ ] Test accessing other guardian's student (403)
- [ ] Test accessing non-existent student (404)
- [ ] Test accessing without proper permissions (403)

#### Validation Errors
- [ ] Test POST with missing required fields (422)
- [ ] Test POST with invalid data types (422)
- [ ] Test POST with invalid date formats (422)
- [ ] Test POST with out-of-range values (422)

#### Not Found Errors
- [ ] Test GET with non-existent ID (404)
- [ ] Test PUT with non-existent resource (404)
- [ ] Test DELETE with non-existent resource (404)

---

## 📊 TESTING SUMMARY

### Completion Tracking

| Category | Total | Tested | Passed | Failed | Notes |
|----------|-------|--------|--------|--------|-------|
| Authentication | 5 | | | | |
| Academic | 4 | | | | |
| Exams | 9 | | | | |
| Leave Requests | 7 | | | | |
| School Fees | 6 | | | | |
| Student Profile | 12 | | | | |
| Curriculum | 4 | | | | |
| Class Info | 4 | | | | |
| Attendance | 4 | | | | |
| Homework | 5 | | | | |
| Announcements | 5 | | | | |
| School Info | 4 | | | | |
| Notifications | 6 | | | | |
| Device Management | 2 | | | | |
| **TOTAL** | **80** | | | | |

---

## 🎯 TESTING PRIORITIES

### Priority 1: Critical (Must Test First)
1. Authentication (5 endpoints)
2. Academic Performance (4 endpoints)
3. Exams (9 endpoints)
4. Leave Requests (7 endpoints)
5. School Fees (6 endpoints)

### Priority 2: Important (Test Second)
6. Student Profile (12 endpoints)
7. Curriculum (4 endpoints)
8. Class Information (4 endpoints)

### Priority 3: Standard (Test Third)
9. Attendance (4 endpoints)
10. Homework (5 endpoints)
11. Announcements (5 endpoints)
12. School Information (4 endpoints)
13. Notifications (6 endpoints)
14. Device Management (2 endpoints)

---

## 📝 NOTES

- Test with real data when possible
- Document any issues found
- Take screenshots of errors
- Note response times
- Check data consistency
- Verify pagination works
- Test edge cases
- Check error messages are clear

---

**Status:** Ready for Testing  
**Estimated Time:** 4-6 hours for complete testing  
**Last Updated:** February 7, 2026

