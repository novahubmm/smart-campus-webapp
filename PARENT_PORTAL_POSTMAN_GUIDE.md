# Parent Portal - Postman Collection Guide

**Document Version:** 2.0  
**Date:** February 7, 2026  
**Collection File:** `UNIFIED_APP_POSTMAN_COLLECTION.json`  
**Base URL:** `http://192.168.100.114:8088/api/v1`

---

## 📋 OVERVIEW

This Postman collection contains **80 API endpoints** covering:
- ✅ Unified Authentication (Teacher & Guardian)
- ✅ Dashboard APIs
- ✅ Notifications
- ✅ Device Management
- ✅ **Parent Portal APIs (NEW)** - 60+ endpoints

---

## 🚀 QUICK START

### 1. Import Collection

1. Open Postman
2. Click **Import** button
3. Select `UNIFIED_APP_POSTMAN_COLLECTION.json`
4. Collection will be imported with all 80 endpoints

### 2. Set Variables

The collection uses these variables:

| Variable | Description | Example Value |
|----------|-------------|---------------|
| `base_url` | API base URL | `http://192.168.100.114:8088/api/v1` |
| `teacher_token` | Teacher JWT token | Auto-set after login |
| `guardian_token` | Guardian JWT token | Auto-set after login |
| `current_token` | Active token | Auto-set after login |
| `user_type` | Current user type | `teacher` or `guardian` |
| `student_id` | Student ID for testing | Set manually (e.g., `STU001`) |

### 3. Login Flow

**For Guardian (Parent) Testing:**

1. Run **"Guardian Login"** request
   - Token automatically saved to `guardian_token`
   - Token automatically set as `current_token`
   
2. Set `student_id` variable:
   - Click collection variables
   - Set `student_id` to your test student ID
   
3. Now you can test all Parent Portal APIs!

---

## 📁 COLLECTION STRUCTURE

### Folder 1: Authentication (5 endpoints)
- Teacher Login
- Guardian Login
- Get Profile
- Change Password
- Logout

### Folder 2: Dashboard (3 endpoints)
- Get Dashboard
- Get Today's Data
- Get Stats

### Folder 3: Notifications (6 endpoints)
- Get Notifications
- Get Unread Count
- Mark as Read
- Mark All as Read
- Get Notification Settings
- Update Notification Settings

### Folder 4: Device Management (2 endpoints)
- Register Device Token
- Remove Device Token

---

## 🎯 PARENT PORTAL APIs (NEW)

### Folder 5: Academic Performance (4 endpoints)

**Purpose:** Track student academic performance, grades, and report cards

| Endpoint | Method | Description |
|----------|--------|-------------|
| Get Academic Overview | GET | Complete academic performance overview |
| Get Report Cards List | GET | List of all report cards |
| Get Report Card Detail | GET | Detailed report card information |
| Download Report Card PDF | GET | Download report card as PDF |

**Example Request:**
```http
GET {{base_url}}/guardian/academic/{{student_id}}
Authorization: Bearer {{current_token}}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "overall_grade": "A",
    "gpa": 3.8,
    "class_rank": 5,
    "total_students": 45,
    "subjects": [...],
    "recent_exams": [...],
    "grade_trends": [...]
  }
}
```

---

### Folder 6: Exams (9 endpoints)

**Purpose:** View upcoming exams, results, and subject performance

| Endpoint | Method | Description |
|----------|--------|-------------|
| Get All Exams | GET | All upcoming and completed exams |
| Get Upcoming Exams | GET | Only upcoming exams |
| Get Completed Exams | GET | Only completed exams |
| Get Exam Detail | GET | Detailed exam information |
| Get Exam Results | GET | Exam results and analysis |
| Get Subjects List | GET | All subjects for student |
| Get Subject Detail | GET | Subject information |
| Get Subject Performance | GET | Performance analysis by subject |
| Get Subject Schedule | GET | Class schedule for subject |

**Example Request:**
```http
GET {{base_url}}/guardian/exams?student_id={{student_id}}&status=upcoming
Authorization: Bearer {{current_token}}
```

---

### Folder 7: Leave Requests (7 endpoints)

**Purpose:** Manage student leave requests and attendance

| Endpoint | Method | Description |
|----------|--------|-------------|
| Get All Leave Requests | GET | All leave requests |
| Get Pending Leave Requests | GET | Only pending requests |
| Get Leave Request Stats | GET | Statistics and balance |
| Get Leave Request Detail | GET | Detailed request information |
| Apply for Leave | POST | Submit new leave request |
| Cancel Leave Request | DELETE | Cancel pending request |
| Get Leave Types | GET | Available leave types |

**Example Request (Apply for Leave):**
```http
POST {{base_url}}/guardian/leave-requests
Authorization: Bearer {{current_token}}
Content-Type: application/json

{
  "student_id": "{{student_id}}",
  "start_date": "2026-02-15",
  "end_date": "2026-02-17",
  "leave_type": "sick",
  "reason": "Medical appointment and recovery"
}
```

---

### Folder 8: School Fees (6 endpoints)

**Purpose:** Track and pay school fees

| Endpoint | Method | Description |
|----------|--------|-------------|
| Get All Fees | GET | All fee invoices |
| Get Unpaid Fees | GET | Only unpaid invoices |
| Get Pending Fees | GET | Pending verification |
| Get Fee Detail | GET | Detailed fee information |
| Get Payment History | GET | Payment history |
| Initiate Payment | POST | Submit payment |

**Example Request:**
```http
GET {{base_url}}/guardian/fees?student_id={{student_id}}&status=unpaid
Authorization: Bearer {{current_token}}
```

---

### Folder 9: Student Profile (12 endpoints)

**Purpose:** View and manage student profile, goals, and notes

| Endpoint | Method | Description |
|----------|--------|-------------|
| Get Student Profile | GET | Complete profile |
| Get Academic Summary | GET | Academic performance summary |
| Get Rankings | GET | Class rankings |
| Get Achievements | GET | Awards and achievements |
| Get Goals | GET | Parent-set goals |
| Create Goal | POST | Add new goal |
| Update Goal | PUT | Update existing goal |
| Delete Goal | DELETE | Remove goal |
| Get Notes | GET | Parent notes |
| Create Note | POST | Add new note |
| Update Note | PUT | Update existing note |
| Delete Note | DELETE | Remove note |

**Example Request (Create Goal):**
```http
POST {{base_url}}/guardian/students/{{student_id}}/goals
Authorization: Bearer {{current_token}}
Content-Type: application/json

{
  "type": "gpa",
  "title": "Achieve 3.8 GPA",
  "description": "Improve overall GPA to 3.8 by end of term",
  "target_value": 3.8,
  "current_value": 3.5,
  "target_date": "2026-06-30"
}
```

---

### Folder 10: Curriculum (4 endpoints)

**Purpose:** Track curriculum progress

| Endpoint | Method | Description |
|----------|--------|-------------|
| Get Curriculum Overview | GET | Progress for all subjects |
| Get Subject Curriculum | GET | Subject-specific progress |
| Get Chapters | GET | Chapter list and progress |
| Get Chapter Detail | GET | Detailed chapter information |

---

### Folder 11: Class Information (4 endpoints)

**Purpose:** View class details and timetable

| Endpoint | Method | Description |
|----------|--------|-------------|
| Get Class Information | GET | Class details |
| Get Class Detail | GET | Detailed class info |
| Get Timetable | GET | Weekly timetable |
| Get Day Timetable | GET | Specific day schedule |

---

### Folder 12: Attendance (4 endpoints)

**Purpose:** Track student attendance

| Endpoint | Method | Description |
|----------|--------|-------------|
| Get Attendance Records | GET | Attendance history |
| Get Attendance Summary | GET | Summary and statistics |
| Get Attendance Calendar | GET | Monthly calendar view |
| Get Attendance Stats | GET | Detailed statistics |

---

### Folder 13: Homework (5 endpoints)

**Purpose:** Track homework assignments

| Endpoint | Method | Description |
|----------|--------|-------------|
| Get Homework List | GET | All assignments |
| Get Homework Stats | GET | Completion statistics |
| Get Homework Detail | GET | Assignment details |
| Submit Homework | POST | Submit assignment |
| Update Homework Status | PUT | Update status |

---

### Folder 14: Announcements (5 endpoints)

**Purpose:** View school announcements

| Endpoint | Method | Description |
|----------|--------|-------------|
| Get Announcements | GET | All announcements |
| Get Recent Announcements | GET | Recent announcements |
| Get Announcement Detail | GET | Detailed information |
| Mark as Read | POST | Mark single as read |
| Mark All as Read | POST | Mark all as read |

---

### Folder 15: School Information (4 endpoints)

**Purpose:** View school information

| Endpoint | Method | Description |
|----------|--------|-------------|
| Get School Information | GET | General school info |
| Get School Rules | GET | Rules and regulations |
| Get Contact Information | GET | Contact details |
| Get School Facilities | GET | Facilities information |

---

## 🧪 TESTING WORKFLOW

### Step 1: Authentication
```
1. Run "Guardian Login"
2. Verify token is saved
3. Check current_token variable is set
```

### Step 2: Set Student ID
```
1. Go to collection variables
2. Set student_id = "STU001" (or your test student ID)
3. Save
```

### Step 3: Test Parent Portal APIs
```
1. Start with "Get Student Profile"
2. Then "Get Academic Overview"
3. Test other endpoints as needed
```

### Step 4: Test CRUD Operations
```
1. Create Goal → Get Goals → Update Goal → Delete Goal
2. Create Note → Get Notes → Update Note → Delete Note
3. Apply Leave → Get Leave Requests → Cancel Leave
```

---

## 📊 API COVERAGE

### By Priority:

| Priority | Screens | Endpoints | Status |
|----------|---------|-----------|--------|
| 🔴 High | 4 | 26 | ✅ Complete |
| 🟡 Medium | 3 | 20 | ✅ Complete |
| 🟢 Low | 2 | 14 | ✅ Complete |
| **Total** | **9** | **60** | **✅ Complete** |

### By Category:

| Category | Endpoints | Implemented |
|----------|-----------|-------------|
| Authentication | 5 | ✅ Yes |
| Dashboard | 3 | ✅ Yes |
| Notifications | 6 | ✅ Yes |
| Academic | 4 | ✅ Yes |
| Exams | 9 | ✅ Yes |
| Leave Requests | 7 | ✅ Yes |
| School Fees | 6 | ✅ Yes |
| Student Profile | 12 | ✅ Yes |
| Curriculum | 4 | ✅ Yes |
| Class Info | 4 | ✅ Yes |
| Attendance | 4 | ✅ Yes |
| Homework | 5 | ✅ Yes |
| Announcements | 5 | ✅ Yes |
| School Info | 4 | ✅ Yes |
| Device Management | 2 | ✅ Yes |
| **Total** | **80** | **✅ All** |

---

## 🔧 TROUBLESHOOTING

### Issue: 401 Unauthorized

**Solution:**
1. Check if token is set: `{{current_token}}`
2. Re-run login request
3. Verify token is not expired

### Issue: 403 Forbidden

**Solution:**
1. Verify student_id belongs to logged-in guardian
2. Check parent-student relationship in database
3. Ensure correct user_type (guardian)

### Issue: 404 Not Found

**Solution:**
1. Verify endpoint URL is correct
2. Check if resource ID exists (student_id, exam_id, etc.)
3. Ensure base_url is correct

### Issue: 422 Validation Error

**Solution:**
1. Check request body format
2. Verify required fields are present
3. Validate data types (dates, numbers, etc.)

---

## 📝 NOTES

### Authentication
- All Parent Portal APIs require authentication
- Use Guardian login for testing
- Token expires after 24 hours (default)

### Student ID
- Must be set in collection variables
- Guardian must have relationship with student
- Can be changed to test different students

### Response Format
- All responses follow standard format:
  ```json
  {
    "success": true/false,
    "message": "...",
    "data": {...}
  }
  ```

### Error Handling
- 400: Bad Request (validation errors)
- 401: Unauthorized (no token or invalid token)
- 403: Forbidden (no permission)
- 404: Not Found (resource doesn't exist)
- 500: Server Error (internal error)

---

## 🎯 NEXT STEPS

1. **Import Collection**
   - Import `UNIFIED_APP_POSTMAN_COLLECTION.json` into Postman

2. **Configure Environment**
   - Set base_url if different
   - Login as guardian
   - Set student_id

3. **Test APIs**
   - Start with authentication
   - Test each folder systematically
   - Verify responses

4. **Integration**
   - Use working APIs in mobile app
   - Replace mock data with real API calls
   - Handle errors appropriately

---

**Document Status:** ✅ Complete  
**Last Updated:** February 7, 2026  
**Total Endpoints:** 80  
**Collection Version:** 2.0.0

