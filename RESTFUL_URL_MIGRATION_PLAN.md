# RESTful URL Structure Migration Plan

## 🎯 Objective
Migrate Guardian API from query parameter structure to RESTful URL parameter structure.

## 📊 Migration Overview

### Current (Query Parameter):
```
GET /guardian/attendance?student_id=xxx
GET /guardian/homework?student_id=xxx
```

### Target (RESTful URL Parameter):
```
GET /guardian/students/{student_id}/attendance
GET /guardian/students/{student_id}/homework
```

---

## 🔄 Migration Strategy

### Option 1: Complete Replacement (BREAKING CHANGE)
- Remove old routes completely
- Update all controllers
- **Impact**: Mobile app must update immediately
- **Timeline**: 1-2 days

### Option 2: Dual Support (RECOMMENDED)
- Keep old routes working
- Add new RESTful routes
- Deprecate old routes after 2 weeks
- **Impact**: Mobile app can update gradually
- **Timeline**: 3-4 days

**DECISION**: We'll implement **Option 2** for safer migration.

---

## 📋 Endpoints to Migrate (49 endpoints)

### CATEGORY 1: Student-Specific Data (Needs student_id in URL)

#### Module 1: Attendance (4 endpoints)
- ✅ OLD: `GET /guardian/attendance?student_id=xxx`
- ✅ NEW: `GET /guardian/students/{student_id}/attendance`

- ✅ OLD: `GET /guardian/attendance/summary?student_id=xxx`
- ✅ NEW: `GET /guardian/students/{student_id}/attendance/summary`

- ✅ OLD: `GET /guardian/attendance/calendar?student_id=xxx`
- ✅ NEW: `GET /guardian/students/{student_id}/attendance/calendar`

- ✅ OLD: `GET /guardian/attendance/stats?student_id=xxx`
- ✅ NEW: `GET /guardian/students/{student_id}/attendance/stats`

#### Module 2: Exams (7 endpoints)
- ✅ NEW: `GET /guardian/students/{student_id}/exams`
- ✅ NEW: `GET /guardian/students/{student_id}/exams/{exam_id}`
- ✅ NEW: `GET /guardian/students/{student_id}/exams/{exam_id}/results`
- ✅ NEW: `GET /guardian/students/{student_id}/exams/performance-trends`
- ✅ NEW: `GET /guardian/students/{student_id}/exams/upcoming`
- ✅ NEW: `GET /guardian/students/{student_id}/exams/past`
- ✅ NEW: `POST /guardian/students/{student_id}/exams/compare`

#### Module 3: Subjects (4 endpoints)
- ✅ NEW: `GET /guardian/students/{student_id}/subjects`
- ✅ NEW: `GET /guardian/students/{student_id}/subjects/{subject_id}`
- ✅ NEW: `GET /guardian/students/{student_id}/subjects/{subject_id}/performance`
- ✅ NEW: `GET /guardian/students/{student_id}/subjects/{subject_id}/schedule`

#### Module 4: Homework (5 endpoints)
- ✅ NEW: `GET /guardian/students/{student_id}/homework`
- ✅ NEW: `GET /guardian/students/{student_id}/homework/{homework_id}`
- ✅ NEW: `POST /guardian/students/{student_id}/homework/{homework_id}/submit`
- ✅ NEW: `GET /guardian/students/{student_id}/homework/stats`
- ✅ NEW: `PUT /guardian/students/{student_id}/homework/{homework_id}/status`

#### Module 5: Timetable (4 endpoints)
- ✅ NEW: `GET /guardian/students/{student_id}/timetable`
- ✅ NEW: `GET /guardian/students/{student_id}/timetable/{day}`
- ✅ NEW: `GET /guardian/students/{student_id}/schedule/today`
- ✅ NEW: `GET /guardian/students/{student_id}/schedule/current-class`

#### Module 6: Class Info (4 endpoints)
- ✅ NEW: `GET /guardian/students/{student_id}/class`
- ✅ NEW: `GET /guardian/students/{student_id}/class/details`
- ✅ NEW: `GET /guardian/students/{student_id}/class/teachers`
- ✅ NEW: `GET /guardian/students/{student_id}/class/statistics`

#### Module 7: Announcements (4 endpoints)
- ✅ NEW: `GET /guardian/students/{student_id}/announcements`
- ✅ NEW: `GET /guardian/students/{student_id}/announcements/{announcement_id}`
- ✅ NEW: `POST /guardian/students/{student_id}/announcements/{announcement_id}/read`
- ✅ NEW: `GET /guardian/students/{student_id}/announcements/recent`

#### Module 8: Fees (5 endpoints)
- ✅ NEW: `GET /guardian/students/{student_id}/fees`
- ✅ NEW: `GET /guardian/students/{student_id}/fees/{fee_id}`
- ✅ NEW: `POST /guardian/students/{student_id}/fees/{fee_id}/payment`
- ✅ NEW: `GET /guardian/students/{student_id}/fees/payment-history`
- ✅ NEW: `GET /guardian/students/{student_id}/fees/summary`

#### Module 9: Leave Requests (5 endpoints)
- ✅ NEW: `GET /guardian/students/{student_id}/leave-requests`
- ✅ NEW: `POST /guardian/students/{student_id}/leave-requests`
- ✅ NEW: `GET /guardian/students/{student_id}/leave-requests/{request_id}`
- ✅ NEW: `PUT /guardian/students/{student_id}/leave-requests/{request_id}`
- ✅ NEW: `DELETE /guardian/students/{student_id}/leave-requests/{request_id}`

#### Module 10: Curriculum (3 endpoints)
- ✅ NEW: `GET /guardian/students/{student_id}/curriculum`
- ✅ NEW: `GET /guardian/students/{student_id}/curriculum/subjects/{subject_id}`
- ✅ NEW: `GET /guardian/students/{student_id}/curriculum/topics/{topic_id}`

#### Module 11: Report Cards (2 endpoints)
- ✅ NEW: `GET /guardian/students/{student_id}/report-cards`
- ✅ NEW: `GET /guardian/students/{student_id}/report-cards/{report_card_id}`

#### Module 12: Academic Performance (4 endpoints)
- ✅ NEW: `GET /guardian/students/{student_id}/academic/gpa-trends`
- ✅ NEW: `GET /guardian/students/{student_id}/academic/performance-analysis`
- ✅ NEW: `GET /guardian/students/{student_id}/academic/strengths-weaknesses`
- ✅ NEW: `GET /guardian/students/{student_id}/academic/badges`

---

### CATEGORY 2: Parent-Level Data (No student_id needed)

#### Module 13: Students Management
- ✅ KEEP: `GET /guardian/students` - List all students
- ✅ KEEP: `POST /guardian/students/switch` - Switch active student

#### Module 14: Settings
- ✅ KEEP: `GET /guardian/settings`
- ✅ KEEP: `PUT /guardian/settings`

#### Module 15: Notification Preferences
- ✅ KEEP: `GET /guardian/notifications/settings`
- ✅ KEEP: `PUT /guardian/notifications/settings`

#### Module 16: School Info (Public)
- ✅ KEEP: `GET /guardian/school-info`
- ✅ KEEP: `GET /guardian/rules`

---

## 🔧 Implementation Steps

### Step 1: Create New Route Group
```php
// Add new RESTful routes
Route::prefix('students/{student_id}')->group(function () {
    // All student-specific endpoints here
});
```

### Step 2: Update Controllers
Each controller method needs to:
1. Accept `$studentId` as parameter instead of from query
2. Keep authorization logic the same
3. Return same response format

### Step 3: Add Deprecation Warnings
```php
// Old routes - add deprecation header
Route::get('/attendance', function() {
    return response()->json([...])
        ->header('X-API-Deprecated', 'true')
        ->header('X-API-Sunset', '2026-03-01')
        ->header('X-API-New-Endpoint', '/guardian/students/{student_id}/attendance');
});
```

### Step 4: Update Documentation
- Update Postman collection
- Update API documentation
- Notify mobile team

### Step 5: Monitor Usage
- Track old endpoint usage
- Send reminders to mobile team
- Remove old endpoints after sunset date

---

## 📝 Controller Update Pattern

### Before (Query Parameter):
```php
public function index(Request $request): JsonResponse
{
    $request->validate([
        'student_id' => 'required|string',
    ]);
    
    $student = $this->getAuthorizedStudent($request);
    // ...
}

private function getAuthorizedStudent(Request $request): ?StudentProfile
{
    $studentId = $request->input('student_id');
    // ...
}
```

### After (URL Parameter):
```php
public function index(Request $request, string $studentId): JsonResponse
{
    $student = $this->getAuthorizedStudent($request, $studentId);
    // ...
}

private function getAuthorizedStudent(Request $request, string $studentId): ?StudentProfile
{
    // Same authorization logic
    // ...
}
```

---

## ✅ Testing Checklist

For each migrated endpoint:
- [ ] New URL works correctly
- [ ] Old URL still works (with deprecation header)
- [ ] Authorization works the same
- [ ] Response format unchanged
- [ ] Postman collection updated
- [ ] Documentation updated

---

## 📅 Timeline

### Week 1 (Days 1-2):
- ✅ Create new route structure
- ✅ Update all controllers
- ✅ Test new endpoints

### Week 1 (Days 3-4):
- ✅ Update Postman collection
- ✅ Update documentation
- ✅ Notify mobile team

### Week 2-3:
- ✅ Mobile team migrates to new endpoints
- ✅ Monitor old endpoint usage
- ✅ Provide support

### Week 4:
- ✅ Remove old endpoints
- ✅ Final testing
- ✅ Deploy to production

---

## 🚨 Risks & Mitigation

### Risk 1: Breaking Changes
**Mitigation**: Keep old endpoints working during transition

### Risk 2: Mobile App Not Updated
**Mitigation**: Add deprecation warnings, set sunset date

### Risk 3: Authorization Issues
**Mitigation**: Thorough testing, same authorization logic

### Risk 4: Performance Impact
**Mitigation**: No performance impact, just URL structure change

---

## 📊 Success Criteria

- ✅ All 49 endpoints migrated
- ✅ Old endpoints still work
- ✅ New endpoints tested
- ✅ Mobile app successfully migrated
- ✅ No breaking changes
- ✅ Documentation complete
- ✅ Postman collection updated

---

## 📞 Communication Plan

### To Mobile Team:
```
Subject: Guardian API - New RESTful URL Structure

Hi Team,

We've implemented new RESTful URLs for Guardian API:

OLD: GET /guardian/attendance?student_id=xxx
NEW: GET /guardian/students/{student_id}/attendance

Benefits:
- RESTful standard
- Better type safety
- Clearer resource hierarchy

Timeline:
- Old endpoints work until March 1, 2026
- Please migrate to new endpoints by Feb 28, 2026

Updated Postman collection attached.

Questions? Let us know!
```

---

## 📄 Status

- **Status**: Ready for Implementation
- **Priority**: HIGH
- **Estimated Time**: 3-4 days
- **Breaking Changes**: NO (dual support)
- **Mobile Impact**: Gradual migration

---

**Document Created**: February 9, 2026
**Last Updated**: February 9, 2026
**Next Review**: After implementation complete
