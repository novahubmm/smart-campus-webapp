# Timetable API Status

## ✅ API Status: READY FOR INTEGRATION

The Timetable API is fully functional and ready for mobile app integration.

---

## API Endpoints

### 1. Get Weekly Timetable
```
GET /api/v1/guardian/students/{student_id}/timetable
```

**Query Parameters:**
- `week_start_date` (optional): Format `YYYY-MM-DD`, defaults to current week

**Headers:**
```
Authorization: Bearer {guardian_token}
Accept: application/json
```

**Response:**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "student_id": "b0ae26d7-0cb6-42db-9e90-4a057d27c50b",
    "student_name": "Maung Kyaw Kyaw",
    "grade": "Kindergarten",
    "section": "N/A",
    "week_start_date": "2026-02-09",
    "week_end_date": "2026-02-14",
    "schedule": {
      "Monday": [
        {
          "id": "019c1cdb-87d8-7208-b30a-6a0350c7f052",
          "period": 1,
          "subject_id": "019c1cdb-73bb-710b-8a9b-33ab4ccd95d7",
          "subject": "General Science",
          "subject_mm": "General Science",
          "teacher_id": "4d8d1094-b98b-4f8c-88df-8468638c3881",
          "teacher": "Htun Moe",
          "teacher_mm": "Htun Moe",
          "teacher_phone": "",
          "teacher_email": "teacher4@smartcampusedu.com",
          "start_time": "08:00",
          "end_time": "08:45",
          "room": "Room 101",
          "room_mm": "အခန်း Room 101",
          "status": "normal",
          "substitute_teacher": null,
          "substitute_teacher_mm": null,
          "swapped_with": null,
          "original_time": null,
          "note": null,
          "note_mm": null
        }
      ],
      "Tuesday": [...],
      "Wednesday": [...],
      "Thursday": [...],
      "Friday": [...],
      "Saturday": [...]
    },
    "break_times": [
      {
        "id": "break-1",
        "type": "break",
        "name": "Morning Break",
        "name_mm": "နံနက်အနားယူချိန်",
        "start_time": "09:45",
        "end_time": "10:00",
        "duration_minutes": 15
      },
      {
        "id": "lunch-1",
        "type": "lunch",
        "name": "Lunch Break",
        "name_mm": "နေ့လည်စာစားချိန်",
        "start_time": "12:15",
        "end_time": "13:15",
        "duration_minutes": 60
      }
    ],
    "total_periods_per_day": 8,
    "total_classes_this_week": 30
  }
}
```

---

### 2. Get Day Timetable
```
GET /api/v1/guardian/students/{student_id}/timetable/day?day=Monday
```

**Query Parameters:**
- `day` (required): One of: Monday, Tuesday, Wednesday, Thursday, Friday, Saturday, Sunday

**Response:** Same structure as weekly timetable but only for the specified day

---

## Class Status Types

| Status | Description |
|--------|-------------|
| `normal` | Regular scheduled class |
| `cancelled` | Class has been cancelled |
| `substitute` | Substitute teacher will teach |
| `swapped` | Class time has been changed |

---

## Test Data Summary

- **Total Timetables:** 41 (one per class)
- **Total Periods:** 1,455
- **Classes with Timetables:** All classes (Kindergarten through Grade 12)
- **Periods per Day:** 6-8 periods (depending on day and grade)
- **Days:** Monday - Saturday (no Sunday classes)

---

## Test Credentials

**Guardian Account:**
- Email: `konyeinchan@smartcampusedu.com`
- Password: `password`

**Test Student:**
- Name: Maung Kyaw Kyaw
- ID: `b0ae26d7-0cb6-42db-9e90-4a057d27c50b`
- Class: Kindergarten A
- Weekly Classes: 30 periods

---

## Sample cURL Commands

**1. Login:**
```bash
curl -X POST http://localhost:8088/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "konyeinchan@smartcampusedu.com",
    "password": "password"
  }'
```

**2. Get Weekly Timetable:**
```bash
curl -X GET "http://localhost:8088/api/v1/guardian/students/b0ae26d7-0cb6-42db-9e90-4a057d27c50b/timetable" \
  -H "Authorization: Bearer {guardian_token}" \
  -H "Accept: application/json"
```

**3. Get Monday Timetable:**
```bash
curl -X GET "http://localhost:8088/api/v1/guardian/students/b0ae26d7-0cb6-42db-9e90-4a057d27c50b/timetable/day?day=Monday" \
  -H "Authorization: Bearer {guardian_token}" \
  -H "Accept: application/json"
```

---

## Period Times

| Period | Start Time | End Time | Duration |
|--------|-----------|----------|----------|
| 1 | 08:00 | 08:45 | 45 min |
| 2 | 08:45 | 09:30 | 45 min |
| 3 | 09:30 | 10:15 | 45 min |
| **Morning Break** | **10:15** | **10:30** | **15 min** |
| 4 | 10:30 | 11:15 | 45 min |
| 5 | 11:15 | 12:00 | 45 min |
| 6 | 12:00 | 12:45 | 45 min |
| **Lunch Break** | **12:45** | **13:45** | **60 min** |
| 7 | 13:45 | 14:30 | 45 min |
| 8 | 14:30 | 15:15 | 45 min |

---

## Integration Notes

1. ✅ API endpoints are working
2. ✅ Sample data exists for all classes
3. ✅ Myanmar language support included
4. ✅ Break times are provided by backend
5. ✅ All required fields are present
6. ✅ Status types supported (normal, cancelled, substitute, swapped)

---

## Changes Made

1. Fixed `GuardianTimetableRepository` to query `Period` model instead of `Timetable` model
2. Updated period `day_of_week` values to use proper case (Monday instead of monday)
3. Activated all timetables in the database

---

## Ready for Mobile Integration ✨

The Timetable API is fully functional and matches the specification provided. The mobile app can now integrate with these endpoints to display student timetables.
