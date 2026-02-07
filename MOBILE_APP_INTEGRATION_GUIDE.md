# Mobile App Integration Guide

**For:** Mobile App Development Team  
**Date:** February 7, 2026  
**Backend Status:** ✅ Ready for Integration

---

## 🎯 QUICK START

The backend APIs for **Teacher Attendance** and **Free Period Activities** are now **fully implemented and ready**. Your mobile app screens are already built and using mock data. Here's how to integrate with the real backend:

---

## 📱 FEATURE #15: TEACHER ATTENDANCE

### Current Mobile App Status:
- ✅ Screen: `MyAttendanceScreen.tsx` (fully implemented)
- ✅ Service: `teacherAttendanceService.ts` (using mock data)
- ✅ Component: `AttendanceCheckInCard.tsx` (ready)

### Integration Steps:

#### 1. Update Service File
**File:** `src/teacher/services/teacherAttendanceService.ts`

**Replace mock data calls with real API calls:**

```typescript
// OLD (Mock Data)
export const checkIn = async (): Promise<CheckInResponse> => {
  return MOCK_CHECK_IN_RESPONSE;
};

// NEW (Real API)
export const checkIn = async (
  latitude?: number,
  longitude?: number,
  deviceInfo?: string,
  appVersion?: string
): Promise<CheckInResponse> => {
  const response = await api.post('/teacher/attendance/check-in', {
    latitude,
    longitude,
    device_info: deviceInfo,
    app_version: appVersion,
  });
  return response.data;
};
```

#### 2. API Endpoints to Use:

| Action | Endpoint | Method | Request Body |
|--------|----------|--------|--------------|
| Check-In | `/api/v1/teacher/attendance/check-in` | POST | `{ latitude?, longitude?, device_info?, app_version? }` |
| Check-Out | `/api/v1/teacher/attendance/check-out` | POST | `{ latitude?, longitude?, notes? }` |
| Today's Status | `/api/v1/teacher/attendance/today` | GET | - |
| History | `/api/v1/teacher/my-attendance` | GET | `?month=current` or `?start_date=...&end_date=...` |

#### 3. Response Format:

**Check-In Success:**
```json
{
  "success": true,
  "message": "Checked in successfully",
  "data": {
    "id": "att_20260207_001",
    "teacher_id": "TCH001",
    "date": "2026-02-07",
    "check_in_time": "08:45:30",
    "check_in_timestamp": "2026-02-07T08:45:30+06:30",
    "status": "checked_in",
    "location": {
      "latitude": 16.8661,
      "longitude": 96.1951
    }
  }
}
```

**Today's Status:**
```json
{
  "success": true,
  "data": {
    "date": "2026-02-07",
    "is_checked_in": true,
    "check_in_time": "08:45:30",
    "check_out_time": null,
    "working_hours": null,
    "status": "checked_in",
    "elapsed_time": "3:15"
  }
}
```

#### 4. Error Handling:

```typescript
try {
  const response = await checkIn(latitude, longitude);
  // Handle success
} catch (error) {
  if (error.response?.data?.error?.code === 'ALREADY_CHECKED_IN') {
    // Show message: "Already checked in today"
  } else if (error.response?.data?.error?.code === 'WEEKEND_NOT_ALLOWED') {
    // Show message: "Cannot check in on weekends"
  } else {
    // Show generic error
  }
}
```

#### 5. Testing Checklist:

- [ ] Check-in works (morning)
- [ ] Today's status shows "checked_in"
- [ ] Duplicate check-in shows error
- [ ] Check-out works (evening)
- [ ] Today's status shows "completed"
- [ ] History shows records with stats
- [ ] Weekend check-in shows error

---

## 📱 FEATURE #18: FREE PERIOD ACTIVITIES

### Current Mobile App Status:
- ✅ Screen: `FreePeriodActivitiesListScreen.tsx` (fully implemented)
- ✅ Service: `scheduleService.ts` (using mock data)
- ✅ Activity types with SVG icons (ready)

### Integration Steps:

#### 1. Update Service File
**File:** `src/teacher/services/scheduleService.ts`

**Replace mock data calls with real API calls:**

```typescript
// OLD (Mock Data)
export const getActivityTypes = async (): Promise<ActivityType[]> => {
  return MOCK_ACTIVITY_TYPES;
};

// NEW (Real API)
export const getActivityTypes = async (): Promise<ActivityType[]> => {
  const response = await api.get('/free-period/activity-types');
  return response.data.data.activity_types;
};

// OLD (Mock Data)
export const saveActivity = async (data: ActivityData): Promise<any> => {
  return { success: true };
};

// NEW (Real API)
export const saveActivity = async (data: ActivityData): Promise<any> => {
  const response = await api.post('/free-period/activities', data);
  return response.data;
};
```

#### 2. API Endpoints to Use:

| Action | Endpoint | Method | Request Body |
|--------|----------|--------|--------------|
| Get Activity Types | `/api/v1/free-period/activity-types` | GET | - |
| Save Activity | `/api/v1/free-period/activities` | POST | See below |
| Get History | `/api/v1/free-period/activities` | GET | `?start_date=...&end_date=...` or `?week_offset=-1` |

#### 3. Request Format:

**Save Activity:**
```json
{
  "date": "2026-02-07",
  "start_time": "10:30",
  "end_time": "11:30",
  "activities": [
    {
      "activity_type": "1",
      "notes": "Prepared lesson plans for Grade 10 Mathematics"
    },
    {
      "activity_type": "2",
      "notes": "Graded 25 test papers"
    }
  ]
}
```

**Validation Rules:**
- Date: Today or past 7 days only
- Time: Start < End
- Duration: 15 minutes to 4 hours
- Activities: 1-5 per record
- No weekends
- School hours: 7 AM - 6 PM

#### 4. Response Format:

**Activity Types:**
```json
{
  "success": true,
  "data": {
    "activity_types": [
      {
        "id": "1",
        "label": "Lesson Planning",
        "color": "#4F46E5",
        "icon_svg": "<svg>...</svg>"
      },
      ...
    ]
  }
}
```

**Save Activity Success:**
```json
{
  "success": true,
  "message": "Activity recorded successfully",
  "data": {
    "id": "fpa_20260207_001",
    "teacher_id": "TCH001",
    "date": "2026-02-07",
    "start_time": "10:30",
    "end_time": "11:30",
    "duration_minutes": 60,
    "activities": [...]
  }
}
```

**Activity History:**
```json
{
  "success": true,
  "data": {
    "activities": [...],
    "stats": {
      "total_records": 15,
      "total_hours": 22.5,
      "most_common_activity": "Lesson Planning",
      "activity_breakdown": [...]
    }
  }
}
```

#### 5. Error Handling:

```typescript
try {
  const response = await saveActivity(activityData);
  // Handle success
} catch (error) {
  if (error.response?.data?.error?.code === 'TIME_OVERLAP') {
    // Show message: "Time slot already has a recorded activity"
  } else if (error.response?.data?.error?.code === 'WEEKEND_NOT_ALLOWED') {
    // Show message: "Cannot record activities on weekends"
  } else if (error.response?.data?.error?.code === 'FUTURE_DATE_NOT_ALLOWED') {
    // Show message: "Cannot record activities for future dates"
  } else {
    // Show generic error or validation errors
  }
}
```

#### 6. Testing Checklist:

- [ ] Activity types load (8 types with icons)
- [ ] Can record single activity
- [ ] Can record multiple activities (2-5)
- [ ] Time overlap shows error
- [ ] Weekend date shows error
- [ ] Future date shows error
- [ ] Duration validation works
- [ ] History loads with statistics
- [ ] Week navigation works

---

## 🔧 CONFIGURATION

### 1. Update API Base URL

**File:** `src/config/api.ts` (or wherever your API config is)

```typescript
// Development
export const API_BASE_URL = 'http://localhost:8000/api/v1';

// Production
export const API_BASE_URL = 'https://your-domain.com/api/v1';
```

### 2. Authentication

All endpoints require authentication. Make sure your API client includes the Bearer token:

```typescript
api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
```

### 3. Remove Mock Data

Once integrated, remove or comment out mock data:

```typescript
// Remove these constants
// const MOCK_CHECK_IN_RESPONSE = { ... };
// const MOCK_ACTIVITY_TYPES = [ ... ];
```

---

## 🧪 TESTING

### Local Testing:

1. **Start Backend:**
```bash
cd smart-campus-webapp
php artisan serve
```

2. **Run Migrations & Seeders:**
```bash
php artisan migrate
php artisan db:seed --class=ActivityTypesSeeder
```

3. **Get Auth Token:**
```bash
# Login as teacher
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"teacher@example.com","password":"password"}'
```

4. **Test Endpoints:**
Use the provided test script:
```bash
cd smart-campus-webapp
./test-new-apis.sh
```

### Mobile App Testing:

1. Update API base URL to your backend
2. Login as teacher
3. Test Teacher Attendance:
   - Navigate to "My Attendance" screen
   - Try check-in
   - Verify status updates
   - Try check-out
   - View history
4. Test Free Period Activities:
   - Navigate to "Free Period Activities" screen
   - View activity types
   - Record an activity
   - View history

---

## 📊 DATA FLOW

### Teacher Attendance Flow:

```
Mobile App → Check-In API → Backend validates → Save to DB → Return status
                ↓
Mobile App updates UI → Shows "Checked In" status
                ↓
Mobile App → Check-Out API → Backend calculates hours → Update DB
                ↓
Mobile App shows completed status with working hours
```

### Free Period Activities Flow:

```
Mobile App → Get Activity Types → Backend returns 8 types with icons
                ↓
Mobile App displays activity selection UI
                ↓
User selects activities and time → Save Activity API
                ↓
Backend validates (time, date, overlap) → Save to DB
                ↓
Mobile App → Get History → Backend returns activities + stats
```

---

## 🐛 COMMON ISSUES & SOLUTIONS

### Issue 1: "Unauthorized" Error
**Solution:** Check if Bearer token is included in request headers

### Issue 2: "Already checked in today"
**Solution:** This is expected behavior. User can only check-in once per day

### Issue 3: "Time slot already has a recorded activity"
**Solution:** User is trying to record overlapping activities. Show error message

### Issue 4: "Cannot record activities on weekends"
**Solution:** Validation is working correctly. Disable weekend dates in date picker

### Issue 5: Activity types not loading
**Solution:** Make sure ActivityTypesSeeder has been run on backend

---

## 📞 SUPPORT

### Backend Team Contact:
- API Documentation: `smart-campus-webapp/API_IMPLEMENTATION_COMPLETE.md`
- Test Script: `smart-campus-webapp/test-new-apis.sh`

### Reference Documentation:
- Teacher Attendance Spec: `SmartCampusv1.0.0/TEACHER_ATTENDANCE_API_SPEC.md`
- Free Period Activities Spec: `SmartCampusv1.0.0/FREE_PERIOD_ACTIVITIES_API_SPEC.md`
- Postman Collections: Available in `SmartCampusv1.0.0/` folder

---

## ✅ INTEGRATION CHECKLIST

### Before Integration:
- [ ] Backend is running and accessible
- [ ] Migrations have been run
- [ ] Activity types have been seeded
- [ ] Test endpoints with Postman/curl
- [ ] Verify authentication works

### During Integration:
- [ ] Update API base URL
- [ ] Replace mock data with API calls
- [ ] Update request/response types if needed
- [ ] Add error handling
- [ ] Test all success scenarios
- [ ] Test all error scenarios

### After Integration:
- [ ] Remove mock data code
- [ ] Test on real device
- [ ] Verify GPS tracking works
- [ ] Test with poor network conditions
- [ ] Update app documentation
- [ ] Submit for testing

---

## 🎉 READY TO GO!

Both features are **fully implemented** on the backend and **ready for integration**. Your mobile app screens are already built, so integration should be straightforward:

1. Update service files (replace mock data)
2. Test locally
3. Deploy and test on device
4. Done! ✅

**Estimated Integration Time:** 2-4 hours per feature

Good luck with the integration! 🚀
