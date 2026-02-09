# Quick Start Guide - New Guardian APIs

## 🚀 Getting Started

### 1. Import Postman Collection
Import `UNIFIED_APP_POSTMAN_COLLECTION.json` into Postman.

### 2. Set Variables
```
base_url: http://192.168.100.114:8088/api/v1
token: <get from login>
student_id: 1
```

### 3. Login First
```
POST {{base_url}}/auth/login
Body: {
  "login": "guardian@example.com",
  "password": "password",
  "device_name": "iPhone 15"
}
```
Copy the token from response and set it in Postman variables.

---

## 📋 Quick Test Endpoints

### Exam Performance (4 endpoints)
```bash
# 1. Performance Trends
GET /guardian/exams/performance-trends?student_id=1

# 2. Upcoming Exams
GET /guardian/exams/upcoming?student_id=1

# 3. Past Exams
GET /guardian/exams/past?student_id=1&limit=10

# 4. Compare Exams
POST /guardian/exams/compare
Body: {"student_id": "1", "exam_ids": ["1", "2"]}
```

### Academic Analysis (4 endpoints)
```bash
# 5. GPA Trends
GET /guardian/students/1/gpa-trends?months=12

# 6. Performance Analysis
GET /guardian/students/1/performance-analysis

# 7. Strengths & Weaknesses
GET /guardian/students/1/subject-strengths-weaknesses

# 8. Academic Badges
GET /guardian/students/1/badges
```

### Fees & Receipts (3 endpoints)
```bash
# 9. Payment Receipt
GET /guardian/fees/receipts/1?student_id=1

# 10. Download Receipt
GET /guardian/fees/receipts/1/download?student_id=1

# 11. Payment Summary
GET /guardian/fees/summary?student_id=1&year=2026
```

### Class Information (3 endpoints)
```bash
# 12. Detailed Class Info
GET /guardian/class-details?student_id=1

# 13. Class Teachers
GET /guardian/class-teachers?student_id=1

# 14. Class Statistics
GET /guardian/class-statistics?student_id=1
```

---

## 🎯 Expected Responses

### Performance Trends
```json
{
  "success": true,
  "data": {
    "overall_average": 85.5,
    "recent_average": 87.2,
    "trend_direction": "improving",
    "total_exams": 12,
    "data": [...]
  }
}
```

### GPA Trends
```json
{
  "success": true,
  "data": {
    "current_gpa": 3.45,
    "average_gpa": 3.38,
    "trend_data": [
      {"month": "2025-03", "gpa": 3.2, "percentage": 80.0}
    ]
  }
}
```

### Academic Badges
```json
{
  "success": true,
  "data": {
    "badges": [
      {
        "id": "perfect_attendance",
        "name": "Perfect Attendance",
        "icon": "🏆",
        "category": "attendance"
      }
    ],
    "total_badges": 3
  }
}
```

### Payment Receipt
```json
{
  "success": true,
  "data": {
    "receipt_number": "PAY-20260209-ABC123",
    "payment_details": {...},
    "invoice_details": {...},
    "school_info": {...}
  }
}
```

---

## ⚠️ Common Issues

### 401 Unauthorized
- Check if token is set correctly
- Token might be expired, login again

### 404 Not Found
- Verify student_id exists
- Check if payment_id is valid for receipts

### 422 Validation Error
- Check required parameters
- Verify data types (string vs integer)

---

## 📱 Mobile App Integration

### React Native Example
```javascript
// Get Performance Trends
const getPerformanceTrends = async (studentId) => {
  const response = await fetch(
    `${API_BASE_URL}/guardian/exams/performance-trends?student_id=${studentId}`,
    {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    }
  );
  return response.json();
};

// Get Academic Badges
const getBadges = async (studentId) => {
  const response = await fetch(
    `${API_BASE_URL}/guardian/students/${studentId}/badges`,
    {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    }
  );
  return response.json();
};
```

---

## 🔍 Testing Checklist

- [ ] Login and get token
- [ ] Test performance trends endpoint
- [ ] Test upcoming exams endpoint
- [ ] Test GPA trends endpoint
- [ ] Test performance analysis endpoint
- [ ] Test badges endpoint
- [ ] Test payment receipt endpoint
- [ ] Test payment summary endpoint
- [ ] Test class details endpoint
- [ ] Test class teachers endpoint
- [ ] Test class statistics endpoint

---

## 📚 Full Documentation

For complete API documentation, see:
- `GUARDIAN_API_ENHANCEMENTS.md` - Detailed API specs
- `API_IMPLEMENTATION_SUMMARY.md` - Implementation overview
- `UNIFIED_APP_POSTMAN_COLLECTION.json` - Postman collection

---

## ✅ All Done!

You now have **15 new endpoints** ready to use:
- 4 Exam performance endpoints
- 4 Academic analysis endpoints
- 3 Fee & receipt endpoints
- 3 Class information endpoints
- 1 Badge system endpoint

Happy coding! 🚀
