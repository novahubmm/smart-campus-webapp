# Guardian API - RESTful Quick Reference

## 🚀 Quick Start

### Base URL
```
http://your-domain.com/api/v1/guardian
```

### Authentication
```
Authorization: Bearer {token}
Accept: application/json
```

### URL Pattern
```
/guardian/students/{student_id}/{resource}
```

---

## 📋 All Endpoints (49 total)

### 🎯 Attendance (4)
```
GET    /students/{id}/attendance              # Get records
GET    /students/{id}/attendance/summary      # Get summary
GET    /students/{id}/attendance/calendar     # Get calendar
GET    /students/{id}/attendance/stats        # Get stats
```

### 📝 Exams (11)
```
GET    /students/{id}/exams                   # List all
GET    /students/{id}/exams/upcoming          # Upcoming
GET    /students/{id}/exams/past              # Past exams
GET    /students/{id}/exams/performance-trends # Trends
POST   /students/{id}/exams/compare           # Compare
GET    /students/{id}/exams/{exam_id}/results # Results
GET    /students/{id}/subjects                # List subjects
GET    /students/{id}/subjects/{sid}          # Subject detail
GET    /students/{id}/subjects/{sid}/performance # Performance
GET    /students/{id}/subjects/{sid}/schedule # Schedule
```

### 📚 Homework (5)
```
GET    /students/{id}/homework                # List all
GET    /students/{id}/homework/{hw_id}        # Detail
GET    /students/{id}/homework/stats          # Stats
POST   /students/{id}/homework/{hw_id}/submit # Submit
PUT    /students/{id}/homework/{hw_id}/status # Update status
```

### 📅 Timetable (6)
```
GET    /students/{id}/timetable               # Full week
GET    /students/{id}/timetable/day           # Specific day
GET    /students/{id}/class-info              # Basic info
GET    /students/{id}/class-details           # Detailed info
GET    /students/{id}/class-teachers          # Teachers
GET    /students/{id}/class-statistics        # Statistics
```

### 💰 Fees (8)
```
GET    /students/{id}/fees                    # All fees
GET    /students/{id}/fees/pending            # Pending
GET    /students/{id}/fees/{fee_id}           # Detail
POST   /students/{id}/fees/{fee_id}/payment   # Pay
GET    /students/{id}/fees/payment-history    # History
GET    /students/{id}/fees/receipts/{pid}     # Receipt
GET    /students/{id}/fees/receipts/{pid}/download # Download
GET    /students/{id}/fees/summary            # Summary
```

### 🏥 Leave Requests (6)
```
GET    /students/{id}/leave-requests          # List all
GET    /students/{id}/leave-requests/{lr_id}  # Detail
POST   /students/{id}/leave-requests          # Create
PUT    /students/{id}/leave-requests/{lr_id}  # Update
DELETE /students/{id}/leave-requests/{lr_id}  # Delete
GET    /students/{id}/leave-requests/stats    # Stats
```

### 📢 Announcements (4)
```
GET    /students/{id}/announcements           # List all
GET    /students/{id}/announcements/{ann_id}  # Detail
POST   /students/{id}/announcements/{ann_id}/read # Mark read
POST   /students/{id}/announcements/mark-all-read # Mark all
```

### 📖 Curriculum (4)
```
GET    /students/{id}/curriculum              # Overview
GET    /students/{id}/curriculum/subjects/{sid} # Subject
GET    /students/{id}/curriculum/chapters     # Chapters
GET    /students/{id}/curriculum/chapters/{cid} # Chapter detail
```

### 📊 Report Cards (2)
```
GET    /students/{id}/report-cards            # List all
GET    /students/{id}/report-cards/{rc_id}    # Detail
```

### 🏠 Dashboard (6)
```
GET    /students/{id}/dashboard               # Full dashboard
GET    /students/{id}/today-schedule          # Today
GET    /students/{id}/upcoming-homework       # Homework
GET    /students/{id}/announcements/recent    # Recent
GET    /students/{id}/fee-reminder            # Fee reminder
GET    /students/{id}/dashboard/current-class # Current class
```

### 👤 Student Profile (Already RESTful)
```
GET    /students/{id}/profile                 # Profile
GET    /students/{id}/academic-summary        # Summary
GET    /students/{id}/rankings                # Rankings
GET    /students/{id}/achievements            # Achievements
GET    /students/{id}/goals                   # Goals
POST   /students/{id}/goals                   # Create goal
PUT    /students/{id}/goals/{goal_id}         # Update goal
DELETE /students/{id}/goals/{goal_id}         # Delete goal
GET    /students/{id}/notes                   # Notes
POST   /students/{id}/notes                   # Create note
PUT    /students/{id}/notes/{note_id}         # Update note
DELETE /students/{id}/notes/{note_id}         # Delete note
GET    /students/{id}/gpa-trends              # GPA trends
GET    /students/{id}/performance-analysis    # Analysis
GET    /students/{id}/subject-strengths-weaknesses # Strengths
GET    /students/{id}/badges                  # Badges
```

---

## 💻 Code Examples

### JavaScript/Axios
```javascript
// GET request
axios.get(`/guardian/students/${studentId}/attendance`, {
  params: { month: 2, year: 2026 }
});

// POST request
axios.post(`/guardian/students/${studentId}/leave-requests`, {
  start_date: '2026-02-10',
  end_date: '2026-02-11',
  reason: 'Sick leave'
});

// PUT request
axios.put(`/guardian/students/${studentId}/homework/1/status`, {
  status: 'completed'
});

// DELETE request
axios.delete(`/guardian/students/${studentId}/leave-requests/1`);
```

### Fetch API
```javascript
// GET
const response = await fetch(
  `/api/v1/guardian/students/${studentId}/exams`,
  {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  }
);

// POST
const response = await fetch(
  `/api/v1/guardian/students/${studentId}/leave-requests`,
  {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({
      start_date: '2026-02-10',
      end_date: '2026-02-11',
      reason: 'Sick leave'
    })
  }
);
```

---

## 🔑 Common Query Parameters

### Pagination
```
?page=1&per_page=10
```

### Filtering
```
?status=pending
?category=academic
?is_read=false
```

### Date Range
```
?month=2&year=2026
?start_date=2026-02-01&end_date=2026-02-28
```

### Sorting
```
?sort_by=created_at&sort_order=desc
```

### Limiting
```
?limit=10
```

---

## ⚠️ Common Errors

### 401 Unauthorized
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```
**Fix**: Check token is valid and included in header

### 403 Forbidden
```json
{
  "success": false,
  "message": "Student not found or unauthorized"
}
```
**Fix**: Guardian doesn't have access to this student

### 404 Not Found
```json
{
  "success": false,
  "message": "Resource not found"
}
```
**Fix**: Check student ID or resource ID is correct

### 422 Validation Error
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "start_date": ["The start date field is required"]
  }
}
```
**Fix**: Check required fields and data types

---

## 📦 Response Format

### Success Response
```json
{
  "success": true,
  "message": "Data retrieved successfully",
  "data": {
    // Your data here
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    // Validation errors (if applicable)
  }
}
```

### Paginated Response
```json
{
  "success": true,
  "data": [...],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 50,
    "last_page": 5
  }
}
```

---

## 🧪 Testing with cURL

### GET Request
```bash
curl -X GET \
  "http://localhost:8088/api/v1/guardian/students/1/attendance?month=2&year=2026" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### POST Request
```bash
curl -X POST \
  "http://localhost:8088/api/v1/guardian/students/1/leave-requests" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "start_date": "2026-02-10",
    "end_date": "2026-02-11",
    "reason": "Sick leave"
  }'
```

### PUT Request
```bash
curl -X PUT \
  "http://localhost:8088/api/v1/guardian/students/1/homework/1/status" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"status": "completed"}'
```

### DELETE Request
```bash
curl -X DELETE \
  "http://localhost:8088/api/v1/guardian/students/1/leave-requests/1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

---

## 🎯 Best Practices

1. **Always encode student IDs**: Use `encodeURIComponent(studentId)`
2. **Handle errors gracefully**: Check response status and show user-friendly messages
3. **Use constants for URLs**: Don't hardcode URLs everywhere
4. **Cache when appropriate**: Cache student data, timetables, etc.
5. **Show loading states**: API calls take time
6. **Validate before sending**: Check data before making requests
7. **Use TypeScript**: Type your API responses
8. **Handle offline mode**: Store data locally when possible

---

## 📱 Postman Collection

Import `UNIFIED_APP_POSTMAN_COLLECTION.json` for:
- ✅ All 49 endpoints ready to test
- ✅ Organized by module
- ✅ Pre-configured variables
- ✅ Auto-save token on login
- ✅ Example requests and responses

---

## 🔗 Related Documentation

- **Full Migration Guide**: `MOBILE_TEAM_MIGRATION_GUIDE.md`
- **Implementation Details**: `RESTFUL_MIGRATION_COMPLETE.md`
- **Final Summary**: `RESTFUL_MIGRATION_FINAL_SUMMARY.md`

---

**Last Updated**: February 9, 2026  
**Version**: 2.0.0  
**Status**: Production Ready
