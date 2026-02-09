# Leave Request API Fix

## Issue
Leave request creation was failing with SQL error:
```
SQLSTATE[23000]: Integrity constraint violation: 19 NOT NULL constraint failed: leave_requests.user_id
```

## Root Cause
The repository was trying to insert `student_id` and `guardian_id` fields that don't exist in the database. The actual table structure uses:
- `user_id` (UUID, NOT NULL) - references the user making the request
- `user_type` (enum: 'teacher', 'staff', 'student') - type of user

## Changes Made

### 1. GuardianLeaveRequestRepository.php
Updated all methods to use correct database fields:

**createLeaveRequest()**
- Changed from `student_id` → `user_id` (using `$student->user_id`)
- Added `user_type` = 'student'
- Changed `leave_type` to use enum values ('sick', 'casual', 'emergency', 'other')
- Added `total_days` calculation
- Changed `attachment_path` → `attachment`

**getLeaveRequests()**
- Updated query to filter by `user_id` and `user_type`
- Fixed field references for attachment and total_days

**getLeaveRequestDetail()**
- Updated relationships from `student.user` → `user`
- Changed `admin_remarks` mapping for rejection_reason

**getLeaveStats()**
- Updated query to use `user_id` and `user_type`
- Simplified total_days calculation using database field

**getLeaveTypes()**
- Updated to match database enum values
- Added `value` field for API clarity

**Helper Methods**
- Added `getLeaveTypeId()` - maps enum to ID
- Added `getLeaveTypeName()` - maps enum to display name
- Updated `getLeaveTypeIcon()` - maps enum to icon

### 2. LeaveRequestController.php
Updated validation rules:

**store()**
- Changed `leave_type` validation to: `required|string|in:sick,casual,emergency,other`
- Removed `leave_type_id` validation (not needed)

**update()**
- Changed `leave_type` validation to: `sometimes|string|in:sick,casual,emergency,other`
- Removed `leave_type_id` validation

## Database Schema
```sql
leave_requests:
- id (uuid, primary)
- user_id (uuid, NOT NULL, foreign key to users)
- user_type (enum: 'teacher', 'staff', 'student')
- start_date (date)
- end_date (date)
- total_days (integer)
- leave_type (enum: 'sick', 'casual', 'emergency', 'other')
- reason (text)
- attachment (string, nullable)
- status (enum: 'pending', 'approved', 'rejected')
- admin_remarks (text, nullable)
- approved_by (uuid, nullable, foreign key to users)
- approved_at (timestamp, nullable)
```

## API Usage

### Create Leave Request
```json
POST /api/v1/guardian/students/{student_id}/leave-requests

{
  "leave_type": "sick",
  "start_date": "2026-02-10",
  "end_date": "2026-02-11",
  "reason": "Student has fever"
}
```

### Leave Types
Available values for `leave_type`:
- `sick` - Sick Leave
- `casual` - Casual Leave
- `emergency` - Emergency Leave
- `other` - Other

## Testing
Test the API with:
```bash
curl -X POST http://localhost:8000/api/v1/guardian/students/{student_id}/leave-requests \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "leave_type": "sick",
    "start_date": "2026-02-10",
    "end_date": "2026-02-11",
    "reason": "Student has fever"
  }'
```
