# Guardian API Enhancements - Complete Documentation

## Overview
This document details all the enhanced and new API endpoints added to the Guardian mobile app to support the requested features.

---

## 🔴 HIGH PRIORITY APIS

### 1. Exams - Enhanced with Trends & Analysis

#### **GET** `/api/v1/guardian/exams/performance-trends`
Get performance trends across exams with trend analysis.

**Query Parameters:**
- `student_id` (required): Student ID
- `subject_id` (optional): Filter by subject

**Response:**
```json
{
  "success": true,
  "data": {
    "overall_average": 85.5,
    "recent_average": 87.2,
    "trend_direction": "improving",
    "total_exams": 12,
    "data": [
      {
        "exam_id": "1",
        "exam_name": "Mid-Term Math",
        "subject": "Mathematics",
        "percentage": 85.0,
        "grade": "A",
        "date": "2026-01-15"
      }
    ]
  }
}
```

#### **GET** `/api/v1/guardian/exams/upcoming`
Get upcoming exams with countdown.

**Query Parameters:**
- `student_id` (required): Student ID

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "1",
      "name": "Final Exam - Mathematics",
      "subject": "Mathematics",
      "date": "2026-02-20",
      "start_time": "09:00",
      "end_time": "11:00",
      "total_marks": 100,
      "room": "Room 101",
      "days_until": 11,
      "is_today": false,
      "is_tomorrow": false
    }
  ]
}
```

#### **GET** `/api/v1/guardian/exams/past`
Get past exams with results.

**Query Parameters:**
- `student_id` (required): Student ID
- `limit` (optional): Number of exams (default: 10, max: 50)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "1",
      "name": "Mid-Term Math",
      "subject": "Mathematics",
      "date": "2026-01-15",
      "total_marks": 100,
      "has_result": true,
      "marks_obtained": 85,
      "percentage": 85.0,
      "grade": "A"
    }
  ]
}
```

#### **POST** `/api/v1/guardian/exams/compare`
Compare multiple exams side-by-side.

**Request Body:**
```json
{
  "student_id": "1",
  "exam_ids": ["1", "2", "3"]
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "student_id": "1",
    "comparison": [
      {
        "exam_id": "1",
        "exam_name": "Mid-Term Math",
        "date": "2026-01-15",
        "marks_obtained": 85,
        "total_marks": 100,
        "percentage": 85.0,
        "grade": "A",
        "class_average": 75.5,
        "class_highest": 95,
        "rank": 3
      }
    ],
    "average_percentage": 83.5
  }
}
```

---

### 2. Academic Performance - GPA, Rankings, Trends

#### **GET** `/api/v1/guardian/students/{id}/gpa-trends`
Get GPA trends over time.

**Query Parameters:**
- `months` (optional): Number of months (default: 12, max: 24)

**Response:**
```json
{
  "success": true,
  "data": {
    "current_gpa": 3.45,
    "average_gpa": 3.38,
    "trend_data": [
      {
        "month": "2025-03",
        "gpa": 3.2,
        "percentage": 80.0,
        "exams_count": 3
      },
      {
        "month": "2025-04",
        "gpa": 3.4,
        "percentage": 85.0,
        "exams_count": 4
      }
    ],
    "period_months": 12
  }
}
```

#### **GET** `/api/v1/guardian/students/{id}/performance-analysis`
Get comprehensive performance analysis.

**Response:**
```json
{
  "success": true,
  "data": {
    "overall_percentage": 84.5,
    "overall_grade": "A",
    "total_exams": 20,
    "subject_analysis": [
      {
        "subject_id": "1",
        "subject_name": "Mathematics",
        "average_percentage": 88.5,
        "grade": "A",
        "exams_taken": 5,
        "trend": "improving"
      }
    ],
    "strengths": [
      {
        "subject_id": "1",
        "subject_name": "Mathematics",
        "average_percentage": 88.5,
        "grade": "A",
        "exams_taken": 5,
        "trend": "improving"
      }
    ],
    "weaknesses": [
      {
        "subject_id": "3",
        "subject_name": "Physics",
        "average_percentage": 72.0,
        "grade": "B+",
        "exams_taken": 4,
        "trend": "stable"
      }
    ]
  }
}
```

#### **GET** `/api/v1/guardian/students/{id}/subject-strengths-weaknesses`
Get subject strengths and weaknesses.

**Response:**
```json
{
  "success": true,
  "data": {
    "strengths": [
      {
        "subject_id": "1",
        "subject_name": "Mathematics",
        "percentage": 88.5,
        "grade": "A"
      }
    ],
    "weaknesses": [
      {
        "subject_id": "3",
        "subject_name": "Physics",
        "percentage": 72.0,
        "grade": "B+"
      }
    ],
    "total_subjects": 8
  }
}
```

---

### 3. School Fees - Enhanced with Receipts

#### **GET** `/api/v1/guardian/fees/receipts/{payment_id}`
Get payment receipt details.

**Query Parameters:**
- `student_id` (required): Student ID

**Response:**
```json
{
  "success": true,
  "data": {
    "receipt_number": "PAY-20260209-ABC123",
    "payment_id": "1",
    "student": {
      "id": "1",
      "name": "John Doe",
      "student_id": "STU001",
      "grade": "Grade 10",
      "section": "A"
    },
    "payment_details": {
      "amount": 500000,
      "currency": "MMK",
      "payment_method": "bank_transfer",
      "transaction_id": "TXN123456",
      "reference_number": "INV-001-ABCD",
      "payment_date": "2026-02-09 10:30:00"
    },
    "invoice_details": {
      "invoice_number": "INV-001",
      "invoice_date": "2026-02-01",
      "term": "February 2026 - Tuition Fee",
      "items": [
        {
          "description": "Tuition Fee",
          "amount": 400000
        },
        {
          "description": "Lab Fee",
          "amount": 100000
        }
      ]
    },
    "school_info": {
      "name": "SmartCampus School",
      "address": "School Address Here",
      "phone": "School Phone Here",
      "email": "school@example.com"
    },
    "generated_at": "2026-02-09T10:30:00Z"
  }
}
```

#### **GET** `/api/v1/guardian/fees/receipts/{payment_id}/download`
Get receipt download link.

**Query Parameters:**
- `student_id` (required): Student ID

**Response:**
```json
{
  "success": true,
  "data": {
    "download_url": "https://api.example.com/api/v1/guardian/fees/receipts/1",
    "payment_id": "1"
  }
}
```

#### **GET** `/api/v1/guardian/fees/summary`
Get payment summary for the year.

**Query Parameters:**
- `student_id` (required): Student ID
- `year` (optional): Year (default: current year)

**Response:**
```json
{
  "success": true,
  "data": {
    "year": 2026,
    "summary": {
      "total_invoiced": 5000000,
      "total_paid": 4500000,
      "total_pending": 500000,
      "payment_completion_rate": 90.0
    },
    "monthly_breakdown": [
      {
        "month": "January",
        "month_number": 1,
        "invoiced": 500000,
        "paid": 500000
      }
    ],
    "category_breakdown": [
      {
        "category": "Tuition Fee",
        "amount": 4000000
      },
      {
        "category": "Lab Fee",
        "amount": 1000000
      }
    ],
    "total_invoices": 10,
    "total_payments": 9
  }
}
```

---

### 4. Leave Requests - Already Implemented ✅

The leave request APIs are already fully implemented:
- `GET /api/v1/guardian/leave-requests` - List all leave requests
- `GET /api/v1/guardian/leave-requests/{id}` - Get leave request details
- `POST /api/v1/guardian/leave-requests` - Submit new leave request
- `PUT /api/v1/guardian/leave-requests/{id}` - Update leave request
- `DELETE /api/v1/guardian/leave-requests/{id}` - Cancel leave request
- `GET /api/v1/guardian/leave-requests/stats` - Get leave statistics
- `GET /api/v1/guardian/leave-types` - Get available leave types

---

## 🟡 MEDIUM PRIORITY APIS

### 5. Class Details - Enhanced Information

#### **GET** `/api/v1/guardian/class-details`
Get detailed class information.

**Query Parameters:**
- `student_id` (required): Student ID

**Response:**
```json
{
  "success": true,
  "data": {
    "id": "1",
    "class_name": "Grade 10",
    "section": "A",
    "full_name": "Grade 10 - A",
    "class_teacher": {
      "id": "1",
      "name": "Ms. Sarah Johnson",
      "photo_url": "https://example.com/photos/teacher1.jpg",
      "phone": "+95912345678",
      "email": "sarah@school.com",
      "department": "Mathematics"
    },
    "room": "Room 201",
    "capacity": 40,
    "total_students": 35,
    "subjects": [
      {
        "id": "1",
        "name": "Mathematics",
        "teacher": {
          "id": "1",
          "name": "Ms. Sarah Johnson",
          "photo_url": "https://example.com/photos/teacher1.jpg"
        },
        "icon": "calculator"
      }
    ],
    "teachers": [
      {
        "id": "1",
        "name": "Ms. Sarah Johnson",
        "photo_url": "https://example.com/photos/teacher1.jpg",
        "subjects": ["Mathematics"],
        "phone": "+95912345678",
        "email": "sarah@school.com",
        "department": "Mathematics"
      }
    ],
    "statistics": {
      "total_students": 35,
      "male_students": 18,
      "female_students": 17,
      "class_attendance_rate": 94.5,
      "class_average_performance": 78.5,
      "total_subjects": 8
    }
  }
}
```

#### **GET** `/api/v1/guardian/class-teachers`
Get all teachers teaching the class.

**Query Parameters:**
- `student_id` (required): Student ID

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "1",
      "name": "Ms. Sarah Johnson",
      "photo_url": "https://example.com/photos/teacher1.jpg",
      "subjects": ["Mathematics"],
      "phone": "+95912345678",
      "email": "sarah@school.com",
      "department": "Mathematics"
    }
  ]
}
```

#### **GET** `/api/v1/guardian/class-statistics`
Get class statistics.

**Query Parameters:**
- `student_id` (required): Student ID

**Response:**
```json
{
  "success": true,
  "data": {
    "total_students": 35,
    "male_students": 18,
    "female_students": 17,
    "class_attendance_rate": 94.5,
    "class_average_performance": 78.5,
    "total_subjects": 8
  }
}
```

---

### 6. Curriculum - Already Implemented ✅

The curriculum APIs are already fully implemented:
- `GET /api/v1/guardian/curriculum` - List all curriculum
- `GET /api/v1/guardian/curriculum/subjects/{id}` - Get subject curriculum
- `GET /api/v1/guardian/curriculum/chapters` - Get chapters
- `GET /api/v1/guardian/curriculum/chapters/{id}` - Get chapter details

---

### 7. School Info - Already Implemented ✅

The school info APIs are already fully implemented:
- `GET /api/v1/guardian/school/info` - Get school information
- `GET /api/v1/guardian/school/rules` - Get school rules
- `GET /api/v1/guardian/school/contact` - Get contact information
- `GET /api/v1/guardian/school/facilities` - Get facilities information

---

### 8. Rules - Already Implemented ✅

The rules APIs are already fully implemented:
- `GET /api/v1/guardian/rules` - List all rules
- `GET /api/v1/guardian/rules/{category}` - Get rules by category

---

## 🟢 LOW PRIORITY APIS

### 9. Profile Enhancements - Academic Badges

#### **GET** `/api/v1/guardian/students/{id}/badges`
Get student's academic badges and achievements.

**Response:**
```json
{
  "success": true,
  "data": {
    "badges": [
      {
        "id": "perfect_attendance",
        "name": "Perfect Attendance",
        "description": "100% attendance record",
        "icon": "🏆",
        "category": "attendance",
        "earned_date": "2026-02-09"
      },
      {
        "id": "honor_roll",
        "name": "Honor Roll",
        "description": "Maintained 90%+ average",
        "icon": "🎓",
        "category": "academic",
        "earned_date": "2026-02-09"
      },
      {
        "id": "consistent_performer",
        "name": "Consistent Performer",
        "description": "Passed last 5 exams",
        "icon": "💪",
        "category": "consistency",
        "earned_date": "2026-02-09"
      }
    ],
    "total_badges": 3,
    "categories": ["attendance", "academic", "consistency"]
  }
}
```

**Badge Types:**
- **Attendance Badges:**
  - Perfect Attendance (100%)
  - Excellent Attendance (95%+)
  
- **Academic Badges:**
  - Honor Roll (90%+ average)
  - High Achiever (80%+ average)
  
- **Consistency Badges:**
  - Consistent Performer (Passed last 5 exams)

---

### 10. Settings - Already Implemented ✅

The settings APIs are already fully implemented:
- `GET /api/v1/guardian/settings` - Get user settings
- `PUT /api/v1/guardian/settings` - Update user settings

---

### 11. Notification Settings - Already Implemented ✅

The notification settings APIs are already fully implemented:
- `GET /api/v1/guardian/notifications/settings` - Get notification settings
- `PUT /api/v1/guardian/notifications/settings` - Update notification settings

---

## Summary of Changes

### New Endpoints Added: 15

**Exams & Performance (7 endpoints):**
1. GET `/api/v1/guardian/exams/performance-trends`
2. GET `/api/v1/guardian/exams/upcoming`
3. GET `/api/v1/guardian/exams/past`
4. POST `/api/v1/guardian/exams/compare`
5. GET `/api/v1/guardian/students/{id}/gpa-trends`
6. GET `/api/v1/guardian/students/{id}/performance-analysis`
7. GET `/api/v1/guardian/students/{id}/subject-strengths-weaknesses`

**Fees & Receipts (3 endpoints):**
8. GET `/api/v1/guardian/fees/receipts/{payment_id}`
9. GET `/api/v1/guardian/fees/receipts/{payment_id}/download`
10. GET `/api/v1/guardian/fees/summary`

**Class Details (3 endpoints):**
11. GET `/api/v1/guardian/class-details`
12. GET `/api/v1/guardian/class-teachers`
13. GET `/api/v1/guardian/class-statistics`

**Profile & Badges (2 endpoints):**
14. GET `/api/v1/guardian/students/{id}/badges`

### Already Implemented: 11 modules
- Leave Requests (7 endpoints)
- Curriculum (4 endpoints)
- School Info (4 endpoints)
- Rules (2 endpoints)
- Settings (2 endpoints)
- Notification Settings (2 endpoints)

---

## Testing with Postman

All endpoints require authentication via Bearer token. Set the following variables in your Postman collection:

```
base_url: http://192.168.100.114:8088/api/v1
token: <your_auth_token>
student_id: <student_id>
```

### Example Request:
```
GET {{base_url}}/guardian/exams/performance-trends?student_id={{student_id}}
Authorization: Bearer {{token}}
```

---

## Implementation Status

✅ **Completed:**
- All interface definitions updated
- All repository implementations completed
- All controller methods added
- All routes registered
- Mock data structure defined

🔄 **Next Steps:**
- Update Postman collection with new endpoints
- Test all endpoints with real data
- Add validation rules as needed
- Implement PDF generation for receipts (optional)

---

## Notes

1. All endpoints return data in the standard API response format:
```json
{
  "success": true,
  "data": { ... },
  "message": "Success message"
}
```

2. Error responses follow the same format:
```json
{
  "success": false,
  "message": "Error message",
  "errors": { ... }
}
```

3. All endpoints require authentication via Sanctum Bearer token.

4. Student authorization is checked - guardians can only access their own students' data.

5. Pagination is supported where applicable with `page` and `per_page` parameters.
