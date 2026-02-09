# RESTful URL Migration - Sample Implementation

## 📝 Example: Attendance Module

This shows exactly what changes are needed for ONE module.
Multiply this by 12 modules = full migration.

---

## 1️⃣ ROUTES (api.php)

### BEFORE:
```php
// Old structure (query parameter)
Route::get('/attendance', [GuardianAttendanceController::class, 'index']);
Route::get('/attendance/summary', [GuardianAttendanceController::class, 'summary']);
Route::get('/attendance/calendar', [GuardianAttendanceController::class, 'calendar']);
Route::get('/attendance/stats', [GuardianAttendanceController::class, 'stats']);
```

### AFTER (Dual Support):
```php
// NEW: RESTful structure (URL parameter)
Route::prefix('students/{student_id}')->group(function () {
    Route::get('/attendance', [GuardianAttendanceController::class, 'indexNew']);
    Route::get('/attendance/summary', [GuardianAttendanceController::class, 'summaryNew']);
    Route::get('/attendance/calendar', [GuardianAttendanceController::class, 'calendarNew']);
    Route::get('/attendance/stats', [GuardianAttendanceController::class, 'statsNew']);
});

// OLD: Keep for backward compatibility (will be removed later)
Route::get('/attendance', [GuardianAttendanceController::class, 'index'])
    ->middleware('deprecated:2026-03-01,/guardian/students/{student_id}/attendance');
Route::get('/attendance/summary', [GuardianAttendanceController::class, 'summary'])
    ->middleware('deprecated:2026-03-01,/guardian/students/{student_id}/attendance/summary');
Route::get('/attendance/calendar', [GuardianAttendanceController::class, 'calendar'])
    ->middleware('deprecated:2026-03-01,/guardian/students/{student_id}/attendance/calendar');
Route::get('/attendance/stats', [GuardianAttendanceController::class, 'stats'])
    ->middleware('deprecated:2026-03-01,/guardian/students/{student_id}/attendance/stats');
```

---

## 2️⃣ CONTROLLER (AttendanceController.php)

### BEFORE:
```php
public function index(Request $request): JsonResponse
{
    $request->validate([
        'student_id' => 'required|string',
        'month' => 'nullable|integer',
        'year' => 'nullable|integer',
    ]);

    try {
        $student = $this->getAuthorizedStudent($request);
        if (!$student) {
            return ApiResponse::error('Student not found or unauthorized', 404);
        }

        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $attendance = $this->attendanceRepository->getAttendance($student, $month, $year);

        return ApiResponse::success($attendance);
    } catch (\Exception $e) {
        return ApiResponse::error('Failed to retrieve attendance: ' . $e->getMessage(), 500);
    }
}

private function getAuthorizedStudent(Request $request): ?StudentProfile
{
    $studentId = $request->input('student_id');
    if (!$studentId) {
        return null;
    }

    $user = $request->user();
    $guardianProfile = $user->guardianProfile;

    if (!$guardianProfile) {
        return null;
    }

    return $guardianProfile->students()
        ->where('student_profiles.id', $studentId)
        ->with(['user', 'grade', 'classModel'])
        ->first();
}
```

### AFTER:
```php
// NEW METHOD: RESTful (URL parameter)
public function indexNew(Request $request, string $studentId): JsonResponse
{
    $request->validate([
        'month' => 'nullable|integer',
        'year' => 'nullable|integer',
    ]);

    try {
        $student = $this->getAuthorizedStudent($request, $studentId);
        if (!$student) {
            return ApiResponse::error('Student not found or unauthorized', 404);
        }

        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $attendance = $this->attendanceRepository->getAttendance($student, $month, $year);

        return ApiResponse::success($attendance);
    } catch (\Exception $e) {
        return ApiResponse::error('Failed to retrieve attendance: ' . $e->getMessage(), 500);
    }
}

// OLD METHOD: Keep for backward compatibility
public function index(Request $request): JsonResponse
{
    $request->validate([
        'student_id' => 'required|string',
        'month' => 'nullable|integer',
        'year' => 'nullable|integer',
    ]);

    // Redirect to new method
    return $this->indexNew($request, $request->input('student_id'));
}

// UPDATED HELPER: Accept studentId as parameter
private function getAuthorizedStudent(Request $request, string $studentId): ?StudentProfile
{
    $user = $request->user();
    $guardianProfile = $user->guardianProfile;

    if (!$guardianProfile) {
        return null;
    }

    return $guardianProfile->students()
        ->where('student_profiles.id', $studentId)
        ->with(['user', 'grade', 'classModel'])
        ->first();
}
```

---

## 3️⃣ POSTMAN COLLECTION

### Add New Endpoints:
```json
{
  "name": "Get Attendance (NEW - RESTful)",
  "request": {
    "method": "GET",
    "url": {
      "raw": "{{base_url}}/guardian/students/{{student_id}}/attendance?month=2&year=2026",
      "path": ["guardian", "students", "{{student_id}}", "attendance"],
      "query": [
        {"key": "month", "value": "2"},
        {"key": "year", "value": "2026"}
      ]
    }
  }
},
{
  "name": "Get Attendance (OLD - Deprecated)",
  "request": {
    "method": "GET",
    "url": {
      "raw": "{{base_url}}/guardian/attendance?student_id={{student_id}}&month=2&year=2026",
      "path": ["guardian", "attendance"],
      "query": [
        {"key": "student_id", "value": "{{student_id}}"},
        {"key": "month", "value": "2"},
        {"key": "year", "value": "2026"}
      ]
    },
    "description": "⚠️ DEPRECATED - Use /guardian/students/{student_id}/attendance instead"
  }
}
```

---

## 4️⃣ DEPRECATION MIDDLEWARE (Optional)

Create middleware to add deprecation headers:

```php
// app/Http/Middleware/DeprecatedEndpoint.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DeprecatedEndpoint
{
    public function handle(Request $request, Closure $next, string $sunsetDate, string $newEndpoint)
    {
        $response = $next($request);
        
        return $response
            ->header('X-API-Deprecated', 'true')
            ->header('X-API-Sunset', $sunsetDate)
            ->header('X-API-New-Endpoint', $newEndpoint)
            ->header('Warning', '299 - "This endpoint is deprecated. Please use ' . $newEndpoint . '"');
    }
}

// Register in app/Http/Kernel.php
protected $middlewareAliases = [
    // ...
    'deprecated' => \App\Http\Middleware\DeprecatedEndpoint::class,
];
```

---

## 5️⃣ TESTING

### Test New Endpoint:
```bash
# Success case
curl -X GET "http://localhost:8088/api/v1/guardian/students/student-uuid-1/attendance?month=2&year=2026" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"

# Expected: 200 OK with attendance data

# Authorization failure
curl -X GET "http://localhost:8088/api/v1/guardian/students/other-student-uuid/attendance" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"

# Expected: 403 Forbidden
```

### Test Old Endpoint (Still Works):
```bash
curl -X GET "http://localhost:8088/api/v1/guardian/attendance?student_id=student-uuid-1&month=2&year=2026" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"

# Expected: 200 OK with attendance data + deprecation headers
```

---

## 📊 Summary for ONE Module

### Files Changed: 2
- `routes/api.php` (8 lines added)
- `AttendanceController.php` (4 methods updated)

### Time Required: 2-3 hours
- Implementation: 1 hour
- Testing: 1 hour
- Documentation: 30 minutes

### Multiply by 12 modules = 24-36 hours (3-4.5 days)

---

## 🎯 Key Points

1. **Both URLs work** during transition
2. **Same authorization** logic
3. **Same response** format
4. **Deprecation headers** guide mobile team
5. **No breaking changes**

---

## ✅ Checklist for Each Module

- [ ] Add new RESTful routes
- [ ] Create new controller methods
- [ ] Update helper methods
- [ ] Keep old methods working
- [ ] Add deprecation middleware
- [ ] Update Postman collection
- [ ] Test both old and new endpoints
- [ ] Update documentation

---

## 🚀 Ready to Implement?

This is the pattern for **ONE module**.

Repeat for:
- ✅ Attendance (4 endpoints)
- ✅ Homework (5 endpoints)
- ✅ Exams (7 endpoints)
- ✅ Subjects (4 endpoints)
- ✅ Timetable (4 endpoints)
- ✅ Announcements (4 endpoints)
- ✅ Fees (5 endpoints)
- ✅ Leave Requests (5 endpoints)
- ✅ Curriculum (3 endpoints)
- ✅ Report Cards (2 endpoints)
- ✅ Class Info (4 endpoints)
- ✅ Academic Performance (4 endpoints)

**Total: 49 endpoints across 12 modules**

---

**Your decision?** 🤔
