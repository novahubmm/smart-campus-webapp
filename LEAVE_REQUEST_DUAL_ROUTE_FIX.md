# Leave Request API - Dual Route Support Fix

## Status: ✅ FIXED

## Problem
The leave request detail endpoint was failing with:
```json
{
  "success": false,
  "message": "Failed to retrieve leave request: No query results for model [App\\Models\\LeaveRequest]."
}
```

## Root Cause
The controller had **TWO different route patterns** calling the same method:

### Route 1: NEW RESTful Route
```
GET /api/v1/guardian/students/{student_id}/leave-requests/{request_id}
```
Parameters: `$studentId`, `$requestId`

### Route 2: OLD Backward Compatibility Route
```
GET /api/v1/guardian/leave-requests/{id}?student_id={student_id}
```
Parameters: `$id` (only one parameter!)

**The Issue**: The controller method signature expected TWO parameters (`$studentId`, `$requestId`), but the OLD route only passes ONE parameter (`$id`). This caused a parameter mismatch.

## Solution

Updated the controller methods to handle **BOTH route patterns** using optional parameters:

### Updated Method Signature
```php
public function show(Request $request, string $studentIdOrRequestId, ?string $requestId = null): JsonResponse
```

### Logic
```php
if ($requestId === null) {
    // OLD route: /leave-requests/{id}
    // $studentIdOrRequestId is actually the request_id
    $actualRequestId = $studentIdOrRequestId;
    
    // Get student_id from query parameter
    $studentId = $request->input('student_id');
    if (!$studentId) {
        return ApiResponse::error('student_id parameter is required', 400);
    }
} else {
    // NEW route: /students/{student_id}/leave-requests/{request_id}
    $studentId = $studentIdOrRequestId;
    $actualRequestId = $requestId;
}
```

## Files Modified
1. ✅ `app/Http/Controllers/Api/V1/Guardian/LeaveRequestController.php`
   - Updated `show()` method
   - Updated `update()` method
   - Updated `destroy()` method

## Testing

### Test Both Routes

#### NEW RESTful Route (Recommended)
```bash
GET /api/v1/guardian/students/3a48862e-ed0e-4991-b2c7-5c4953ed7227/leave-requests/c586d56a-8184-444c-ab32-fbed2a01ec84
Authorization: Bearer {token}
```

#### OLD Route (Backward Compatibility)
```bash
GET /api/v1/guardian/leave-requests/c586d56a-8184-444c-ab32-fbed2a01ec84?student_id=3a48862e-ed0e-4991-b2c7-5c4953ed7227
Authorization: Bearer {token}
```

### Test Script
```bash
./test-leave-request-both-routes.sh
```

## Expected Response (Both Routes)
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "id": "c586d56a-8184-444c-ab32-fbed2a01ec84",
    "leave_type": {
      "id": 1,
      "name": "Sick Leave",
      "icon": "shield-heart"
    },
    "start_date": "2026-02-10",
    "end_date": "2026-02-11",
    "total_days": 2,
    "reason": "Student has fever",
    "status": "pending",
    "attachment": null,
    "approved_by": null,
    "approved_at": null,
    "rejection_reason": null,
    "created_at": "2026-02-09T07:51:14.000000Z"
  }
}
```

## Route Definitions in `routes/api.php`

### NEW Routes (Inside `students/{student_id}` group)
```php
Route::prefix('students/{student_id}')->group(function () {
    Route::get('/leave-requests', [GuardianLeaveRequestController::class, 'index']);
    Route::get('/leave-requests/stats', [GuardianLeaveRequestController::class, 'stats']);
    Route::post('/leave-requests', [GuardianLeaveRequestController::class, 'store']);
    Route::get('/leave-requests/{request_id}', [GuardianLeaveRequestController::class, 'show']);
    Route::put('/leave-requests/{request_id}', [GuardianLeaveRequestController::class, 'update']);
    Route::delete('/leave-requests/{request_id}', [GuardianLeaveRequestController::class, 'destroy']);
});
```

### OLD Routes (Backward Compatibility)
```php
// Leave Requests (Old)
Route::get('/leave-requests', [GuardianLeaveRequestController::class, 'index']);
Route::get('/leave-requests/stats', [GuardianLeaveRequestController::class, 'stats']);
Route::get('/leave-requests/{id}', [GuardianLeaveRequestController::class, 'show']);
Route::post('/leave-requests', [GuardianLeaveRequestController::class, 'store']);
Route::put('/leave-requests/{id}', [GuardianLeaveRequestController::class, 'update']);
Route::delete('/leave-requests/{id}', [GuardianLeaveRequestController::class, 'destroy']);
```

## Key Points

1. ✅ **Both routes work** - Backward compatibility maintained
2. ✅ **Authorization enforced** - Guardian must have access to the student
3. ✅ **Ownership verified** - Leave request must belong to the student
4. ✅ **Proper error handling** - Clear error messages
5. ✅ **RESTful best practices** - New route follows REST conventions

## Migration Path

### For Mobile Team
- **Current**: Can continue using OLD route with `?student_id=` query parameter
- **Recommended**: Migrate to NEW RESTful route for better API design
- **Timeline**: No rush, both routes will be supported

### OLD Route (Current)
```
GET /guardian/leave-requests/{request_id}?student_id={student_id}
```

### NEW Route (Recommended)
```
GET /guardian/students/{student_id}/leave-requests/{request_id}
```

## Cache Cleared
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

## Related Documentation
- `LEAVE_REQUEST_DETAIL_FIX.md` - Initial fix attempt
- `LEAVE_REQUEST_ID_GUIDE.md` - ID usage guide
- `test-leave-request-both-routes.sh` - Test script for both routes
