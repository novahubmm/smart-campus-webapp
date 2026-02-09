# Guardian API Migration Guide - RESTful URLs

## 📋 Overview

The Guardian API has been updated to support RESTful URL structure. This guide will help you migrate from the old query parameter format to the new RESTful format.

**Migration Status**: ✅ Backend Complete | ⏳ Mobile App Pending

---

## 🎯 What Changed?

### Old Format (Query Parameter)
```
GET /api/v1/guardian/attendance?student_id=abc-123&month=2&year=2026
GET /api/v1/guardian/exams?student_id=abc-123
GET /api/v1/guardian/homework?student_id=abc-123&status=pending
```

### New Format (RESTful)
```
GET /api/v1/guardian/students/abc-123/attendance?month=2&year=2026
GET /api/v1/guardian/students/abc-123/exams
GET /api/v1/guardian/students/abc-123/homework?status=pending
```

**Key Change**: `student_id` moved from query parameter to URL path

---

## ✅ Backward Compatibility

**Good News**: Both formats work simultaneously!

- ✅ Old URLs still work (no breaking changes)
- ✅ New URLs are available now
- ✅ Same response format
- ✅ Same authorization logic
- ⏰ Old URLs will be deprecated in 3 months (May 9, 2026)

---

## 📊 Migration Timeline

| Phase | Date | Status | Action |
|-------|------|--------|--------|
| **Phase 1** | Feb 9, 2026 | ✅ Complete | Backend updated with dual support |
| **Phase 2** | Feb 10-16, 2026 | ⏳ In Progress | Mobile team testing |
| **Phase 3** | Feb 17-23, 2026 | ⏳ Pending | Mobile app update & release |
| **Phase 4** | Feb 24 - May 9, 2026 | ⏳ Pending | Transition period (both work) |
| **Phase 5** | May 10, 2026 | ⏳ Pending | Old URLs deprecated |

---

## 🔄 URL Mapping Reference

### 1. Attendance Module (4 endpoints)

| Old URL | New URL |
|---------|---------|
| `GET /guardian/attendance?student_id={id}` | `GET /guardian/students/{id}/attendance` |
| `GET /guardian/attendance/summary?student_id={id}` | `GET /guardian/students/{id}/attendance/summary` |
| `GET /guardian/attendance/calendar?student_id={id}` | `GET /guardian/students/{id}/attendance/calendar` |
| `GET /guardian/attendance/stats?student_id={id}` | `GET /guardian/students/{id}/attendance/stats` |

### 2. Exams Module (11 endpoints)

| Old URL | New URL |
|---------|---------|
| `GET /guardian/exams?student_id={id}` | `GET /guardian/students/{id}/exams` |
| `GET /guardian/exams/upcoming?student_id={id}` | `GET /guardian/students/{id}/exams/upcoming` |
| `GET /guardian/exams/past?student_id={id}` | `GET /guardian/students/{id}/exams/past` |
| `GET /guardian/exams/performance-trends?student_id={id}` | `GET /guardian/students/{id}/exams/performance-trends` |
| `POST /guardian/exams/compare` | `POST /guardian/students/{id}/exams/compare` |
| `GET /guardian/exams/{exam_id}/results?student_id={id}` | `GET /guardian/students/{id}/exams/{exam_id}/results` |
| `GET /guardian/subjects?student_id={id}` | `GET /guardian/students/{id}/subjects` |
| `GET /guardian/subjects/{subject_id}?student_id={id}` | `GET /guardian/students/{id}/subjects/{subject_id}` |
| `GET /guardian/subjects/{subject_id}/performance?student_id={id}` | `GET /guardian/students/{id}/subjects/{subject_id}/performance` |
| `GET /guardian/subjects/{subject_id}/schedule?student_id={id}` | `GET /guardian/students/{id}/subjects/{subject_id}/schedule` |

### 3. Homework Module (5 endpoints)

| Old URL | New URL |
|---------|---------|
| `GET /guardian/homework?student_id={id}` | `GET /guardian/students/{id}/homework` |
| `GET /guardian/homework/{hw_id}?student_id={id}` | `GET /guardian/students/{id}/homework/{hw_id}` |
| `GET /guardian/homework/stats?student_id={id}` | `GET /guardian/students/{id}/homework/stats` |
| `POST /guardian/homework/{hw_id}/submit` | `POST /guardian/students/{id}/homework/{hw_id}/submit` |
| `PUT /guardian/homework/{hw_id}/status` | `PUT /guardian/students/{id}/homework/{hw_id}/status` |

### 4. Timetable Module (6 endpoints)

| Old URL | New URL |
|---------|---------|
| `GET /guardian/timetable?student_id={id}` | `GET /guardian/students/{id}/timetable` |
| `GET /guardian/timetable/day?student_id={id}` | `GET /guardian/students/{id}/timetable/day` |
| `GET /guardian/class-info?student_id={id}` | `GET /guardian/students/{id}/class-info` |
| `GET /guardian/class-details?student_id={id}` | `GET /guardian/students/{id}/class-details` |
| `GET /guardian/class-teachers?student_id={id}` | `GET /guardian/students/{id}/class-teachers` |
| `GET /guardian/class-statistics?student_id={id}` | `GET /guardian/students/{id}/class-statistics` |

### 5. Fees Module (8 endpoints)

| Old URL | New URL |
|---------|---------|
| `GET /guardian/fees?student_id={id}` | `GET /guardian/students/{id}/fees` |
| `GET /guardian/fees/pending?student_id={id}` | `GET /guardian/students/{id}/fees/pending` |
| `GET /guardian/fees/{fee_id}?student_id={id}` | `GET /guardian/students/{id}/fees/{fee_id}` |
| `POST /guardian/fees/{fee_id}/payment` | `POST /guardian/students/{id}/fees/{fee_id}/payment` |
| `GET /guardian/fees/payment-history?student_id={id}` | `GET /guardian/students/{id}/fees/payment-history` |
| `GET /guardian/fees/receipts/{payment_id}?student_id={id}` | `GET /guardian/students/{id}/fees/receipts/{payment_id}` |
| `GET /guardian/fees/receipts/{payment_id}/download?student_id={id}` | `GET /guardian/students/{id}/fees/receipts/{payment_id}/download` |
| `GET /guardian/fees/summary?student_id={id}` | `GET /guardian/students/{id}/fees/summary` |

### 6. Leave Requests Module (6 endpoints)

| Old URL | New URL |
|---------|---------|
| `GET /guardian/leave-requests?student_id={id}` | `GET /guardian/students/{id}/leave-requests` |
| `GET /guardian/leave-requests/{lr_id}` | `GET /guardian/students/{id}/leave-requests/{lr_id}` |
| `POST /guardian/leave-requests` | `POST /guardian/students/{id}/leave-requests` |
| `PUT /guardian/leave-requests/{lr_id}` | `PUT /guardian/students/{id}/leave-requests/{lr_id}` |
| `DELETE /guardian/leave-requests/{lr_id}` | `DELETE /guardian/students/{id}/leave-requests/{lr_id}` |
| `GET /guardian/leave-requests/stats?student_id={id}` | `GET /guardian/students/{id}/leave-requests/stats` |

### 7. Announcements Module (4 endpoints)

| Old URL | New URL |
|---------|---------|
| `GET /guardian/announcements?student_id={id}` | `GET /guardian/students/{id}/announcements` |
| `GET /guardian/announcements/{ann_id}` | `GET /guardian/students/{id}/announcements/{ann_id}` |
| `POST /guardian/announcements/{ann_id}/read` | `POST /guardian/students/{id}/announcements/{ann_id}/read` |
| `POST /guardian/announcements/mark-all-read` | `POST /guardian/students/{id}/announcements/mark-all-read` |

### 8. Curriculum Module (4 endpoints)

| Old URL | New URL |
|---------|---------|
| `GET /guardian/curriculum?student_id={id}` | `GET /guardian/students/{id}/curriculum` |
| `GET /guardian/curriculum/subjects/{subject_id}?student_id={id}` | `GET /guardian/students/{id}/curriculum/subjects/{subject_id}` |
| `GET /guardian/curriculum/chapters?subject_id={sid}` | `GET /guardian/students/{id}/curriculum/chapters?subject_id={sid}` |
| `GET /guardian/curriculum/chapters/{chapter_id}` | `GET /guardian/students/{id}/curriculum/chapters/{chapter_id}` |

### 9. Report Cards Module (2 endpoints)

| Old URL | New URL |
|---------|---------|
| `GET /guardian/report-cards?student_id={id}` | `GET /guardian/students/{id}/report-cards` |
| `GET /guardian/report-cards/{rc_id}?student_id={id}` | `GET /guardian/students/{id}/report-cards/{rc_id}` |

### 10. Dashboard Module (6 endpoints)

| Old URL | New URL |
|---------|---------|
| `GET /guardian/home/dashboard?student_id={id}` | `GET /guardian/students/{id}/dashboard` |
| `GET /guardian/today-schedule?student_id={id}` | `GET /guardian/students/{id}/today-schedule` |
| `GET /guardian/upcoming-homework?student_id={id}` | `GET /guardian/students/{id}/upcoming-homework` |
| `GET /guardian/announcements/recent?student_id={id}` | `GET /guardian/students/{id}/announcements/recent` |
| `GET /guardian/fee-reminder?student_id={id}` | `GET /guardian/students/{id}/fee-reminder` |
| `GET /guardian/dashboard/current-class?student_id={id}` | `GET /guardian/students/{id}/dashboard/current-class` |

---

## 💻 Code Migration Examples

### React Native / JavaScript

#### Before (Old Format)
```javascript
// Old way - student_id in query parameter
const getAttendance = async (studentId, month, year) => {
  const response = await fetch(
    `${API_BASE}/guardian/attendance?student_id=${studentId}&month=${month}&year=${year}`,
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

#### After (New Format)
```javascript
// New way - student_id in URL path
const getAttendance = async (studentId, month, year) => {
  const response = await fetch(
    `${API_BASE}/guardian/students/${studentId}/attendance?month=${month}&year=${year}`,
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

### Axios Example

#### Before
```javascript
axios.get('/guardian/homework', {
  params: {
    student_id: studentId,
    status: 'pending'
  }
});
```

#### After
```javascript
axios.get(`/guardian/students/${studentId}/homework`, {
  params: {
    status: 'pending'
  }
});
```

### POST Request Example

#### Before
```javascript
// Old - student_id in body
axios.post('/guardian/leave-requests', {
  student_id: studentId,
  start_date: '2026-02-10',
  end_date: '2026-02-11',
  reason: 'Sick leave'
});
```

#### After
```javascript
// New - student_id in URL, removed from body
axios.post(`/guardian/students/${studentId}/leave-requests`, {
  start_date: '2026-02-10',
  end_date: '2026-02-11',
  reason: 'Sick leave'
});
```

---

## 🧪 Testing Checklist

### Phase 1: Local Testing
- [ ] Update API base URL constants
- [ ] Update all Guardian API calls to new format
- [ ] Test with valid student IDs
- [ ] Test with invalid student IDs (should return 404)
- [ ] Test with unauthorized student IDs (should return 403)
- [ ] Verify response formats unchanged
- [ ] Test pagination where applicable
- [ ] Test filtering and sorting

### Phase 2: Integration Testing
- [ ] Test complete user flows
- [ ] Test with multiple students
- [ ] Test error handling
- [ ] Test network failures
- [ ] Test token expiration
- [ ] Test offline mode (if applicable)

### Phase 3: Performance Testing
- [ ] Compare response times (old vs new)
- [ ] Test with slow network
- [ ] Test concurrent requests
- [ ] Monitor memory usage

---

## 🔧 Implementation Steps

### Step 1: Update API Service Layer (Week 1)
1. Create new API service methods with RESTful URLs
2. Keep old methods for backward compatibility
3. Add feature flag to switch between old/new

```javascript
// api/guardianService.js
const USE_RESTFUL_API = true; // Feature flag

export const getAttendance = async (studentId, month, year) => {
  const url = USE_RESTFUL_API
    ? `${API_BASE}/guardian/students/${studentId}/attendance`
    : `${API_BASE}/guardian/attendance`;
  
  const params = USE_RESTFUL_API
    ? { month, year }
    : { student_id: studentId, month, year };
  
  return axios.get(url, { params });
};
```

### Step 2: Update Components (Week 2)
1. Update all components using Guardian APIs
2. Test each component individually
3. Update unit tests

### Step 3: Integration Testing (Week 3)
1. Test complete app flows
2. Fix any issues found
3. Performance testing

### Step 4: Release (Week 4)
1. Deploy to staging
2. Beta testing
3. Production release

---

## 📝 Postman Collection

**Updated Collection**: `UNIFIED_APP_POSTMAN_COLLECTION.json` (v2.0.0)

### New Features:
- ✅ All RESTful endpoints added
- ✅ Organized in folders by module
- ✅ Old endpoints marked as deprecated
- ✅ `student_id` variable added
- ✅ Ready-to-use examples

### How to Use:
1. Import `UNIFIED_APP_POSTMAN_COLLECTION.json`
2. Set `base_url` variable
3. Login to get token (auto-saved)
4. Set `student_id` variable
5. Test RESTful endpoints in "RESTful Endpoints (NEW)" folder

---

## ⚠️ Common Pitfalls

### 1. Forgetting to Remove student_id from Body
```javascript
// ❌ Wrong - student_id in both URL and body
axios.post(`/guardian/students/${studentId}/leave-requests`, {
  student_id: studentId, // Remove this!
  reason: 'Sick'
});

// ✅ Correct - student_id only in URL
axios.post(`/guardian/students/${studentId}/leave-requests`, {
  reason: 'Sick'
});
```

### 2. URL Encoding Issues
```javascript
// ❌ Wrong - not encoding student ID
const url = `/guardian/students/${studentId}/attendance`;

// ✅ Correct - encode student ID
const url = `/guardian/students/${encodeURIComponent(studentId)}/attendance`;
```

### 3. Mixing Old and New Formats
```javascript
// ❌ Wrong - mixing formats
const url = `/guardian/students/${studentId}/attendance?student_id=${studentId}`;

// ✅ Correct - use only new format
const url = `/guardian/students/${studentId}/attendance`;
```

---

## 🆘 Support & Questions

### Technical Issues
- **Backend Issues**: Contact backend team
- **API Questions**: Check Postman collection
- **Migration Help**: Refer to this guide

### Resources
- **Postman Collection**: `UNIFIED_APP_POSTMAN_COLLECTION.json`
- **Backend Docs**: `RESTFUL_MIGRATION_COMPLETE.md`
- **Sample Implementation**: `RESTFUL_SAMPLE_IMPLEMENTATION.md`

---

## 📊 Migration Progress Tracker

| Module | Endpoints | Status | Tested | Notes |
|--------|-----------|--------|--------|-------|
| Attendance | 4 | ⏳ Pending | ❌ | - |
| Exams | 11 | ⏳ Pending | ❌ | - |
| Homework | 5 | ⏳ Pending | ❌ | - |
| Timetable | 6 | ⏳ Pending | ❌ | - |
| Fees | 8 | ⏳ Pending | ❌ | - |
| Leave Requests | 6 | ⏳ Pending | ❌ | - |
| Announcements | 4 | ⏳ Pending | ❌ | - |
| Curriculum | 4 | ⏳ Pending | ❌ | - |
| Report Cards | 2 | ⏳ Pending | ❌ | - |
| Dashboard | 6 | ⏳ Pending | ❌ | - |
| **Total** | **49** | **0%** | **0/49** | - |

---

## ✅ Benefits of Migration

1. **Better URL Structure**: More intuitive and RESTful
2. **Improved Security**: Student ID in URL path (harder to manipulate)
3. **Better Caching**: Easier to cache by student
4. **Cleaner Code**: Less query parameter handling
5. **Industry Standard**: Follows REST best practices
6. **Better Documentation**: Self-documenting URLs

---

## 📅 Important Dates

- **Feb 9, 2026**: Backend migration complete
- **Feb 16, 2026**: Mobile team testing deadline
- **Feb 23, 2026**: Mobile app release deadline
- **May 9, 2026**: Old URLs deprecated (stop working)

---

**Questions?** Contact the backend team or refer to the Postman collection for examples.

**Last Updated**: February 9, 2026  
**Version**: 1.0.0  
**Status**: Ready for Mobile Team
