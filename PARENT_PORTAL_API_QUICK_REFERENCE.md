# Parent Portal API - Quick Reference Card

**Base URL:** `http://192.168.100.114:8088/api/v1`  
**Auth:** Bearer Token (Guardian)  
**Collection:** UNIFIED_APP_POSTMAN_COLLECTION.json (80 endpoints)

---

## 🔐 AUTHENTICATION

```http
POST /auth/login
{
  "login": "guardian@email.com",
  "password": "password",
  "device_name": "Mobile App"
}
→ Returns: { token, user_type: "guardian" }
```

---

## 📚 ACADEMIC (4 endpoints)

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/guardian/academic/:studentId` | GET | Academic overview |
| `/guardian/report-cards?student_id=:id` | GET | Report cards list |
| `/guardian/report-cards/:id?student_id=:id` | GET | Report card detail |
| `/guardian/report-cards/:id/download` | GET | Download PDF |

---

## 📝 EXAMS (9 endpoints)

| Endpoint | Method | Query | Description |
|----------|--------|-------|-------------|
| `/guardian/exams` | GET | `student_id, status` | All exams |
| `/guardian/exams/:id` | GET | - | Exam detail |
| `/guardian/exams/:id/results` | GET | `student_id` | Exam results |
| `/guardian/subjects` | GET | `student_id` | Subjects list |
| `/guardian/subjects/:id` | GET | `student_id` | Subject detail |
| `/guardian/subjects/:id/performance` | GET | `student_id` | Performance |
| `/guardian/subjects/:id/schedule` | GET | `student_id` | Schedule |

**Status values:** `all`, `upcoming`, `completed`

---

## 🏥 LEAVE REQUESTS (7 endpoints)

| Endpoint | Method | Body | Description |
|----------|--------|------|-------------|
| `/guardian/leave-requests` | GET | - | All requests |
| `/guardian/leave-requests/stats` | GET | - | Statistics |
| `/guardian/leave-requests/:id` | GET | - | Request detail |
| `/guardian/leave-requests` | POST | ✓ | Apply leave |
| `/guardian/leave-requests/:id` | DELETE | - | Cancel request |
| `/guardian/leave-types` | GET | - | Leave types |

**POST Body:**
```json
{
  "student_id": "STU001",
  "start_date": "2026-02-15",
  "end_date": "2026-02-17",
  "leave_type": "sick",
  "reason": "Medical appointment"
}
```

---

## 💰 SCHOOL FEES (6 endpoints)

| Endpoint | Method | Query | Description |
|----------|--------|-------|-------------|
| `/guardian/fees` | GET | `student_id, status` | All fees |
| `/guardian/fees/pending` | GET | `student_id` | Pending fees |
| `/guardian/fees/:id` | GET | - | Fee detail |
| `/guardian/fees/payment-history` | GET | `student_id` | Payment history |
| `/guardian/fees/:id/payment` | POST | ✓ | Initiate payment |

**Status values:** `all`, `unpaid`, `pending`, `paid`

**POST Body:**
```json
{
  "payment_method": "bank_transfer",
  "amount": 120000
}
```

---

## 👤 STUDENT PROFILE (12 endpoints)

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/guardian/students/:id/profile` | GET | Profile |
| `/guardian/students/:id/academic-summary` | GET | Academic summary |
| `/guardian/students/:id/rankings` | GET | Rankings |
| `/guardian/students/:id/achievements` | GET | Achievements |
| `/guardian/students/:id/goals` | GET | Goals list |
| `/guardian/students/:id/goals` | POST | Create goal |
| `/guardian/students/:id/goals/:goalId` | PUT | Update goal |
| `/guardian/students/:id/goals/:goalId` | DELETE | Delete goal |
| `/guardian/students/:id/notes` | GET | Notes list |
| `/guardian/students/:id/notes` | POST | Create note |
| `/guardian/students/:id/notes/:noteId` | PUT | Update note |
| `/guardian/students/:id/notes/:noteId` | DELETE | Delete note |

**Create Goal:**
```json
{
  "type": "gpa",
  "title": "Achieve 3.8 GPA",
  "target_value": 3.8,
  "current_value": 3.5,
  "target_date": "2026-06-30"
}
```

**Create Note:**
```json
{
  "title": "Math Progress",
  "content": "Showing improvement",
  "category": "academic"
}
```

---

## 📖 CURRICULUM (4 endpoints)

| Endpoint | Method | Query | Description |
|----------|--------|-------|-------------|
| `/guardian/curriculum` | GET | `student_id` | Overview |
| `/guardian/curriculum/subjects/:id` | GET | `student_id` | Subject curriculum |
| `/guardian/curriculum/chapters` | GET | `student_id, subject_id` | Chapters |
| `/guardian/curriculum/chapters/:id` | GET | `student_id` | Chapter detail |

---

## 🏫 CLASS INFO (4 endpoints)

| Endpoint | Method | Query | Description |
|----------|--------|-------|-------------|
| `/guardian/class-info` | GET | `student_id` | Class info |
| `/guardian/classes/:id` | GET | - | Class detail |
| `/guardian/timetable` | GET | `student_id` | Weekly timetable |
| `/guardian/timetable/day` | GET | `student_id, date` | Day timetable |

---

## ✅ ATTENDANCE (4 endpoints)

| Endpoint | Method | Query | Description |
|----------|--------|-------|-------------|
| `/guardian/attendance` | GET | `student_id` | Records |
| `/guardian/attendance/summary` | GET | `student_id` | Summary |
| `/guardian/attendance/calendar` | GET | `student_id, month, year` | Calendar |
| `/guardian/attendance/stats` | GET | `student_id` | Statistics |

---

## 📓 HOMEWORK (5 endpoints)

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/guardian/homework` | GET | Homework list |
| `/guardian/homework/stats` | GET | Statistics |
| `/guardian/homework/:id` | GET | Detail |
| `/guardian/homework/:id/submit` | POST | Submit |
| `/guardian/homework/:id/status` | PUT | Update status |

---

## 📢 ANNOUNCEMENTS (5 endpoints)

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/guardian/announcements` | GET | All announcements |
| `/guardian/announcements/recent` | GET | Recent |
| `/guardian/announcements/:id` | GET | Detail |
| `/guardian/announcements/:id/read` | POST | Mark as read |
| `/guardian/announcements/mark-all-read` | POST | Mark all read |

---

## 🏛️ SCHOOL INFO (4 endpoints)

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/guardian/school/info` | GET | School information |
| `/guardian/school/rules` | GET | Rules |
| `/guardian/school/contact` | GET | Contact info |
| `/guardian/school/facilities` | GET | Facilities |

---

## 🔔 NOTIFICATIONS (6 endpoints)

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/notifications` | GET | All notifications |
| `/notifications/unread-count` | GET | Unread count |
| `/notifications/:id/read` | POST | Mark as read |
| `/notifications/mark-all-read` | POST | Mark all read |
| `/notifications/settings` | GET | Get settings |
| `/notifications/settings` | PUT | Update settings |

---

## 📱 DEVICE MANAGEMENT (2 endpoints)

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/device-tokens` | POST | Register token |
| `/device-tokens` | DELETE | Remove token |

---

## 🎯 COMMON PATTERNS

### Authentication Header
```http
Authorization: Bearer {token}
```

### Query Parameters
```http
GET /endpoint?student_id=STU001&status=active&page=1
```

### Request Body (JSON)
```http
Content-Type: application/json

{
  "field": "value"
}
```

### Response Format
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
  "error": {
    "code": "ERROR_CODE",
    "details": { ... }
  }
}
```

---

## 🔢 STATUS CODES

| Code | Meaning | Action |
|------|---------|--------|
| 200 | Success | Process data |
| 201 | Created | Resource created |
| 400 | Bad Request | Check request format |
| 401 | Unauthorized | Re-login |
| 403 | Forbidden | Check permissions |
| 404 | Not Found | Check resource ID |
| 422 | Validation Error | Fix validation errors |
| 500 | Server Error | Contact support |

---

## 📊 QUICK STATS

- **Total Endpoints:** 80
- **Parent Portal APIs:** 60
- **Folders:** 15
- **Collection Size:** 122 KB
- **Collection Lines:** 3,014

---

## 🚀 QUICK START

1. **Import Collection**
   ```
   File: UNIFIED_APP_POSTMAN_COLLECTION.json
   ```

2. **Login**
   ```http
   POST /auth/login
   ```

3. **Set Variables**
   ```
   current_token = {from login response}
   student_id = STU001
   ```

4. **Test APIs**
   ```
   Start with: GET /guardian/students/:id/profile
   ```

---

## 📞 SUPPORT

- **Documentation:** PARENT_PORTAL_POSTMAN_GUIDE.md
- **Summary:** PARENT_PORTAL_API_UPDATE_SUMMARY.md
- **Specifications:** SmartCampusv1.0.0/PARENT_*.md

---

**Version:** 2.0.0  
**Last Updated:** February 7, 2026  
**Status:** ✅ Ready for Integration

