# Route Fix: Upcoming Homework

## Issue
The route `api/v1/guardian/students/{student_id}/upcoming-homework` was not found, causing a 404 error.

## Root Cause
The route was only defined in the old backward compatibility section as:
- `/api/v1/guardian/upcoming-homework?student_id={id}`

But it was missing from the new RESTful student-specific routes section:
- `/api/v1/guardian/students/{student_id}/upcoming-homework`

## Solution
Added the missing route in the RESTful student-specific routes section:

```php
Route::prefix('students/{student_id}')->group(function () {
    // ... other routes ...
    
    // Homework
    Route::get('/homework', [GuardianHomeworkController::class, 'index']);
    Route::get('/homework/stats', [GuardianHomeworkController::class, 'stats']);
    Route::get('/homework/upcoming', [GuardianHomeworkController::class, 'upcoming']);
    Route::get('/upcoming-homework', [GuardianDashboardController::class, 'upcomingHomework']); // ADDED
    Route::get('/homework/{homework_id}', [GuardianHomeworkController::class, 'show']);
    // ... other routes ...
});
```

## Controller Support
The `DashboardController::upcomingHomework()` method already supports both route patterns:

```php
public function upcomingHomework(Request $request, ?string $studentId = null): JsonResponse
```

- Old route: Uses `$request->input('student_id')`
- New route: Uses the `$studentId` parameter from the URL

## Verification
Route is now registered and accessible:
```bash
php artisan route:list --path=guardian/students | grep upcoming-homework
# Output: GET|HEAD  api/v1/guardian/students/{student_id}/upcoming-homework
```

## Status
✅ Fixed - Route cache cleared and route is now available
