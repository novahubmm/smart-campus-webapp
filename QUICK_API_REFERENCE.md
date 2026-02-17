# Quick API Reference - Testing Guide

## 🎯 Quick Test URLs

### 1. Class Information APIs
```bash
# Basic class info
GET /api/v1/guardian/students/{student_id}/class

# Detailed class info
GET /api/v1/guardian/students/{student_id}/class/details

# Class teachers
GET /api/v1/guardian/students/{student_id}/class/teachers

# Class statistics
GET /api/v1/guardian/students/{student_id}/class/statistics
```

### 2. School Information API
```bash
# School info (Public - No Auth)
GET /api/v1/guardian/school-info
```

### 3. School Rules API
```bash
# School rules (Auth Required)
GET /api/v1/guardian/school/rules
```

### 4. Student Profile APIs
```bash
# Profile overview
GET /api/v1/guardian/students/{student_id}/profile

# Academic summary
GET /api/v1/guardian/students/{student_id}/profile/academic-summary

# Subject performance
GET /api/v1/guardian/students/{student_id}/profile/subject-performance

# Progress tracking
GET /api/v1/guardian/students/{student_id}/profile/progress-tracking?months=6

# Comparison data
GET /api/v1/guardian/students/{student_id}/profile/comparison

# Attendance summary
GET /api/v1/guardian/students/{student_id}/profile/attendance-summary?months=3

# Rankings & exam history
GET /api/v1/guardian/students/{student_id}/profile/rankings

# Achievement badges
GET /api/v1/guardian/students/{student_id}/profile/achievements
```

---

## 🧪 Test Commands

```bash
# Test Class Info APIs
php test-class-info-api.php

# Test School Info API
php test-school-info-api.php

# Test School Rules API
php test-rules-api.php

# Test Student Profile APIs
php test-student-profile-api.php
```

---

## 📋 Postman Collection

Import: `UNIFIED_APP_POSTMAN_COLLECTION.json`

### Collection Structure
```
SmartCampus Unified App API
├── Authentication
├── Dashboard
├── Notifications
├── Device Management
├── Teacher Specific
└── Guardian Specific
    ├── Get School Info (Public)
    └── RESTful Endpoints (NEW)
        ├── Attendance
        ├── Exams
        ├── Homework
        ├── Timetable
        │   ├── Get Class Info (RESTful) ✅
        │   ├── Get Detailed Class Info (RESTful) ✅
        │   ├── Get Class Teachers (RESTful) ✅
        │   └── Get Class Statistics (RESTful) ✅
        ├── Fees
        ├── Leave Requests
        ├── Announcements
        ├── Curriculum
        └── Student Profile ✅
            ├── Get Profile Overview
            ├── Get Academic Summary
            ├── Get Subject Performance
            ├── Get Progress Tracking
            ├── Get Comparison Data
            ├── Get Attendance Summary
            ├── Get Rankings & Exam History
            └── Get Achievement Badges
    └── Get School Rules ✅
```

---

## 🔑 Authentication

### Login
```bash
POST /api/v1/guardian/auth/login
Content-Type: application/json

{
  "email": "guardian@example.com",
  "password": "password123"
}
```

### Use Token
```bash
Authorization: Bearer {token}
```

---

## ✅ Status Summary

| Feature | Endpoints | Postman | Test File | Status |
|---------|-----------|---------|-----------|--------|
| Class Info | 4 | ✅ | ✅ | Ready |
| School Info | 1 | ✅ | ✅ | Ready |
| School Rules | 1 | ✅ | ✅ | Ready |
| Student Profile | 8 | ✅ | ✅ | Ready |

**Total: 14 endpoints - All Ready for Testing** ✅
