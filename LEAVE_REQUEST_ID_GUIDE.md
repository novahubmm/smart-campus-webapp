# Leave Request API - ID Usage Guide

**Status**: ✅ **FIXED** - Controller parameter order corrected

**Previous Issue**: Getting "No query results for model [App\\Models\\LeaveRequest]" error

**Root Cause**: Controller method had incorrect parameter order for RESTful nested routes

**Fix Applied**: Updated controller to properly handle `students/{student_id}/leave-requests/{request_id}` route structure

---

## 🔧 What Was Fixed

The controller method signatures were updated to match the RESTful route structure:

### Before (Broken):
```php
public function show(string $id, ?string $studentId = null)
```

### After (Fixed):
```php
public function show(Request $request, string $studentId, string $requestId)
```

**Changes:**
1. ✅ Correct parameter order matching route structure
2. ✅ Added student authorization check
3. ✅ Added leave request ownership verification
4. ✅ Proper error handling

See `LEAVE_REQUEST_DETAIL_FIX.md` for complete technical details.

---

## 🐛 The Problem

You're using the **student_id** where you should use the **leave_request_id**.

### Wrong Usage:
```
GET /guardian/students/{student_id}/leave-requests/{student_id}
                                                     ↑ WRONG! This should be leave_request_id
```

### Correct Usage:
```
GET /guardian/students/{student_id}/leave-requests/{leave_request_id}
                       ↑ student_id                 ↑ leave_request_id (different!)
```

---

## ✅ How to Use the API Correctly

### Step 1: Get List of Leave Requests

**Endpoint:**
```
GET /api/v1/guardian/students/{student_id}/leave-requests
```

**Example:**
```
GET /api/v1/guardian/students/3a48862e-ed0e-4991-b2c7-5c4953ed7227/leave-requests
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": "c586d56a-8184-444c-ab32-fbed2a01ec84",  ← Use this ID!
        "leave_type": "sick",
        "start_date": "2026-02-10",
        "end_date": "2026-02-11",
        "status": "pending",
        "reason": "Fever and cold"
      }
    ]
  }
}
```

### Step 2: Get Leave Request Detail

Use the **leave_request_id** from Step 1:

**Endpoint:**
```
GET /api/v1/guardian/students/{student_id}/leave-requests/{leave_request_id}
```

**Example:**
```
GET /api/v1/guardian/students/3a48862e-ed0e-4991-b2c7-5c4953ed7227/leave-requests/c586d56a-8184-444c-ab32-fbed2a01ec84
                               ↑ student_id                                         ↑ leave_request_id
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": "c586d56a-8184-444c-ab32-fbed2a01ec84",
    "leave_type": "sick",
    "start_date": "2026-02-10",
    "end_date": "2026-02-11",
    "total_days": 2,
    "status": "pending",
    "reason": "Fever and cold",
    "attachment": null,
    "admin_remarks": null
  }
}
```

---

## 📋 Test Data for Student: Htun Zin

**Student ID:** `3a48862e-ed0e-4991-b2c7-5c4953ed7227`

**Leave Request ID:** `c586d56a-8184-444c-ab32-fbed2a01ec84`

### Test URLs:

**List Leave Requests:**
```
GET {{base_url}}/guardian/students/3a48862e-ed0e-4991-b2c7-5c4953ed7227/leave-requests
```

**Get Leave Request Detail:**
```
GET {{base_url}}/guardian/students/3a48862e-ed0e-4991-b2c7-5c4953ed7227/leave-requests/c586d56a-8184-444c-ab32-fbed2a01ec84
```

---

## 🔍 Understanding the IDs

### Student ID
- **What**: Unique identifier for a student
- **Format**: UUID (e.g., `3a48862e-ed0e-4991-b2c7-5c4953ed7227`)
- **Used in**: Student-specific endpoints
- **Example**: `/guardian/students/{student_id}/...`

### Leave Request ID
- **What**: Unique identifier for a leave request
- **Format**: UUID (e.g., `c586d56a-8184-444c-ab32-fbed2a01ec84`)
- **Used in**: Leave request detail endpoint
- **Example**: `/guardian/students/{student_id}/leave-requests/{leave_request_id}`

**They are DIFFERENT IDs!**

---

## 🎯 Common Mistakes

### Mistake 1: Using Student ID as Leave Request ID

❌ **Wrong:**
```
GET /guardian/students/3a48862e.../leave-requests/3a48862e...
                                                   ↑ Same ID - WRONG!
```

✅ **Correct:**
```
GET /guardian/students/3a48862e.../leave-requests/c586d56a...
                       ↑ student_id                ↑ leave_request_id (different!)
```

### Mistake 2: Hardcoding IDs

❌ **Wrong:** Using hardcoded leave request IDs

✅ **Correct:** 
1. First call list endpoint to get leave request IDs
2. Then use those IDs to get details

---

## 📝 API Workflow

### Correct Workflow:

```
1. Login as Guardian
   ↓
2. Get Students List
   ↓ (get student_id)
3. Get Leave Requests List for Student
   ↓ (get leave_request_id)
4. Get Leave Request Detail
   ✓ Success!
```

### Your Workflow (Incorrect):

```
1. Login as Guardian
   ↓
2. Get Students List
   ↓ (get student_id)
3. Use student_id as leave_request_id
   ✗ Error: "No query results"
```

---

## 🧪 Testing in Postman

### Collection Variables:

```
base_url: http://192.168.100.114:8088/api/v1
access_token: {your_token}
student_id: 3a48862e-ed0e-4991-b2c7-5c4953ed7227
leave_request_id: c586d56a-8184-444c-ab32-fbed2a01ec84
```

### Request 1: List Leave Requests

```
GET {{base_url}}/guardian/students/{{student_id}}/leave-requests
Authorization: Bearer {{access_token}}
```

**Save the leave request ID from response!**

### Request 2: Get Leave Request Detail

```
GET {{base_url}}/guardian/students/{{student_id}}/leave-requests/{{leave_request_id}}
Authorization: Bearer {{access_token}}
```

---

## ✅ Summary

**The Error:**
```
"No query results for model [App\\Models\\LeaveRequest] 3a48862e..."
```

**What it means:**
- You're looking for a leave request with ID `3a48862e...`
- But that's a student ID, not a leave request ID
- No leave request exists with that ID

**The Fix:**
1. ✅ Call list endpoint first to get leave request IDs
2. ✅ Use the correct leave_request_id (not student_id)
3. ✅ Test with: `c586d56a-8184-444c-ab32-fbed2a01ec84`

---

**Status**: ✅ **Explained - Use Correct ID**
