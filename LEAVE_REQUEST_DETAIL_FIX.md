# Leave Request Detail API Fix

## Issue
The leave request detail endpoint was returning "No query results for model" error when accessing:
```
GET /api/v1/guardian/students/{student_id}/leave-requests/{request_id}
```

## Root Cause
The controller method had incorrect parameter order and was missing authorization checks. When routes are nested under `students/{student_id}`, Laravel passes parameters in the order they appear in the URL.

## Changes Made

### 1. Updated Controller Method Signatures
**File**: `app/Http/Controllers/Api/V1/Guardian/LeaveRequestController.php`

#### Before:
```php
public function show(string $id, ?string $studentId = null): JsonResponse
{
    try {
        $request = $this->leaveRequestRepository->getLeaveRequestDetail($id);
        return ApiResponse::success($request);
    } catch (\Exception $e) {
        return ApiResponse::error('Failed to retrieve leave request: ' . $e->getMessage(), 500);
    }
}
```

#### After:
```php
public function show(Request $request, string $studentId, string $requestId): JsonResponse
{
    try {
        // Verify student authorization
        $student = $this->getAuthorizedStudent($request, $studentId);
        if (!$student) {
            return ApiResponse::error('Student not found or unauthorized', 404);
        }

        // Get leave request detail with authorization check
        $leaveRequest = $this->leaveRequestRepository->getLeaveRequestDetailForStudent($requestId, $student->id);

        return ApiResponse::success($leaveRequest);
    } catch (\Exception $e) {
        return ApiResponse::error('Failed to retrieve leave request: ' . $e->getMessage(), 500);
    }
}
```

### 2. Added Authorization Method to Repository
**File**: `app/Repositories/Guardian/GuardianLeaveRequestRepository.php`

Added new method `getLeaveRequestDetailForStudent()` that:
- Verifies the leave request belongs to the specified student
- Includes proper authorization checks
- Returns 404 if the request doesn't belong to the student

```php
public function getLeaveRequestDetailForStudent(string $requestId, string $studentId): array
{
    // Get student's user_id
    $student = StudentProfile::findOrFail($studentId);
    
    // Find leave request and verify it belongs to this student
    $request = LeaveRequest::with(['user', 'approvedBy'])
        ->where('id', $requestId)
        ->where('user_id', $student->user_id)
        ->where('user_type', 'student')
        ->firstOrFail();

    return [
        // ... formatted response
    ];
}
```

### 3. Updated Interface
**File**: `app/Interfaces/Guardian/GuardianLeaveRequestRepositoryInterface.php`

Added method signature:
```php
public function getLeaveRequestDetailForStudent(string $requestId, string $studentId): array;
```

### 4. Updated Other Methods
Also fixed `update()` and `destroy()` methods to follow the same pattern with proper parameter order and authorization.

## Testing

### Test Data
- Guardian: `guardian1@smartcampusedu.com` / `password`
- Student ID: `3a48862e-ed0e-4991-b2c7-5c4953ed7227` (Htun Zin)
- Leave Request ID: `c586d56a-8184-444c-ab32-fbed2a01ec84`

### Test Endpoint
```bash
# Login as guardian
curl -X POST {{base_url}}/guardian/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "guardian1@smartcampusedu.com",
    "password": "password"
  }'

# Get leave request detail
curl -X GET {{base_url}}/guardian/students/3a48862e-ed0e-4991-b2c7-5c4953ed7227/leave-requests/c586d56a-8184-444c-ab32-fbed2a01ec84 \
  -H "Authorization: Bearer {token}"
```

## Expected Response
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
    "start_date": "2024-02-15",
    "end_date": "2024-02-16",
    "total_days": 2,
    "reason": "Fever and cold",
    "status": "pending",
    "attachment": null,
    "approved_by": null,
    "approved_at": null,
    "rejection_reason": null,
    "created_at": "2024-02-09T10:30:00.000000Z"
  }
}
```

## Key Improvements
1. ✅ Proper parameter order matching RESTful route structure
2. ✅ Authorization check to verify guardian has access to the student
3. ✅ Authorization check to verify leave request belongs to the student
4. ✅ Consistent error handling
5. ✅ Follows the same pattern as PaymentController (reference implementation)

## Related Files
- `app/Http/Controllers/Api/V1/Guardian/LeaveRequestController.php`
- `app/Repositories/Guardian/GuardianLeaveRequestRepository.php`
- `app/Interfaces/Guardian/GuardianLeaveRequestRepositoryInterface.php`
- `routes/api.php`

## Cache Cleared
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```
