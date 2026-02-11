# Student Profile API - Quick Start Guide

## 🚀 Quick Integration Guide for Mobile Team

### Base URL
```
http://192.168.100.114:8088/api/v1
```

---

## 📋 Step-by-Step Integration

### Step 1: Login
```typescript
const response = await fetch(`${baseUrl}/guardian/auth/login`, {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  body: JSON.stringify({
    login: '09123456789',  // phone number
    password: 'password',
    device_name: 'guardian_app',
  }),
});

const { data } = await response.json();
const token = data.token;
```

### Step 2: Get Students
```typescript
const response = await fetch(`${baseUrl}/guardian/students`, {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
  },
});

const { data } = await response.json();
const studentId = data[0].id;
```

### Step 3: Fetch Profile Data
```typescript
// All profile endpoints follow this pattern:
// GET /guardian/students/{studentId}/profile/*

const endpoints = {
  profile: `/guardian/students/${studentId}/profile`,
  academicSummary: `/guardian/students/${studentId}/profile/academic-summary`,
  subjectPerformance: `/guardian/students/${studentId}/profile/subject-performance`,
  progressTracking: `/guardian/students/${studentId}/profile/progress-tracking?months=6`,
  comparison: `/guardian/students/${studentId}/profile/comparison`,
  attendanceSummary: `/guardian/students/${studentId}/profile/attendance-summary?months=3`,
  rankings: `/guardian/students/${studentId}/profile/rankings`,
  achievements: `/guardian/students/${studentId}/profile/achievements`,
};
```

---

## 🔑 API Endpoints Summary

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/guardian/auth/login` | POST | Authenticate guardian |
| `/guardian/students` | GET | Get guardian's students |
| `/guardian/students/{id}/profile` | GET | Student profile overview |
| `/guardian/students/{id}/profile/academic-summary` | GET | Academic performance summary |
| `/guardian/students/{id}/profile/subject-performance` | GET | Subject-wise performance |
| `/guardian/students/{id}/profile/progress-tracking` | GET | GPA & rank history |
| `/guardian/students/{id}/profile/comparison` | GET | Student vs class average |
| `/guardian/students/{id}/profile/attendance-summary` | GET | Attendance statistics |
| `/guardian/students/{id}/profile/rankings` | GET | Rankings & exam history |
| `/guardian/students/{id}/profile/achievements` | GET | Achievement badges |

---

## 📦 Response Structure

All endpoints return:
```typescript
{
  success: boolean;
  message: string;
  data: any;
}
```

---

## 🎯 Replace Mock Data

### Before (Mock Data)
```typescript
const MOCK_SUBJECTS = [
  { id: '1', name: 'Mathematics', grade: 'A+', percentage: 95 },
  // ...
];
```

### After (API Data)
```typescript
const [subjects, setSubjects] = useState([]);

useEffect(() => {
  const fetchSubjects = async () => {
    const response = await api.get(
      `/guardian/students/${studentId}/profile/subject-performance`
    );
    setSubjects(response.data.subjects);
  };
  fetchSubjects();
}, [studentId]);
```

---

## 🔄 Complete Example

```typescript
import { api } from './apiClient';

export const studentProfileAPI = {
  // Get profile overview
  getProfileOverview: async (studentId: string) => {
    const response = await api.get(
      `/guardian/students/${studentId}/profile`
    );
    return response.data;
  },

  // Get academic summary
  getAcademicSummary: async (studentId: string) => {
    const response = await api.get(
      `/guardian/students/${studentId}/profile/academic-summary`
    );
    return response.data;
  },

  // Get subject performance
  getSubjectPerformance: async (studentId: string) => {
    const response = await api.get(
      `/guardian/students/${studentId}/profile/subject-performance`
    );
    return response.data;
  },

  // Get progress tracking
  getProgressTracking: async (studentId: string, months: number = 6) => {
    const response = await api.get(
      `/guardian/students/${studentId}/profile/progress-tracking`,
      { params: { months } }
    );
    return response.data;
  },

  // Get comparison data
  getComparisonData: async (studentId: string) => {
    const response = await api.get(
      `/guardian/students/${studentId}/profile/comparison`
    );
    return response.data;
  },

  // Get attendance summary
  getAttendanceSummary: async (studentId: string, months: number = 3) => {
    const response = await api.get(
      `/guardian/students/${studentId}/profile/attendance-summary`,
      { params: { months } }
    );
    return response.data;
  },

  // Get rankings data
  getRankings: async (studentId: string) => {
    const response = await api.get(
      `/guardian/students/${studentId}/profile/rankings`
    );
    return response.data;
  },

  // Get achievements
  getAchievements: async (studentId: string) => {
    const response = await api.get(
      `/guardian/students/${studentId}/profile/achievements`
    );
    return response.data;
  },
};
```

---

## 🎨 ProfileScreen Integration

```typescript
const ProfileScreen = () => {
  const [loading, setLoading] = useState(true);
  const [profileData, setProfileData] = useState(null);
  const [academicSummary, setAcademicSummary] = useState(null);
  const [progressTracking, setProgressTracking] = useState(null);
  // ... other state

  useEffect(() => {
    fetchAllProfileData();
  }, [studentId]);

  const fetchAllProfileData = async () => {
    try {
      setLoading(true);
      
      // Fetch all data in parallel
      const [
        profile,
        academic,
        subjects,
        progress,
        comparison,
        attendance,
        rankings,
        achievements,
      ] = await Promise.all([
        studentProfileAPI.getProfileOverview(studentId),
        studentProfileAPI.getAcademicSummary(studentId),
        studentProfileAPI.getSubjectPerformance(studentId),
        studentProfileAPI.getProgressTracking(studentId, 6),
        studentProfileAPI.getComparisonData(studentId),
        studentProfileAPI.getAttendanceSummary(studentId, 3),
        studentProfileAPI.getRankings(studentId),
        studentProfileAPI.getAchievements(studentId),
      ]);

      setProfileData(profile);
      setAcademicSummary(academic);
      // ... set other state
      
      setLoading(false);
    } catch (error) {
      console.error('Error fetching profile data:', error);
      setLoading(false);
    }
  };

  if (loading) {
    return <LoadingSpinner />;
  }

  return (
    <ScrollView>
      <ProfileHeader data={profileData} />
      <AcademicTab summary={academicSummary} />
      <ProgressCharts data={progressTracking} />
      {/* ... other components */}
    </ScrollView>
  );
};
```

---

## ⚠️ Important Notes

### 1. Authentication
- All endpoints require Bearer token
- Token obtained from login endpoint
- Include in Authorization header: `Bearer {token}`

### 2. Student ID
- Get student ID from `/guardian/students` endpoint
- Use this ID in all profile endpoints
- Format: UUID (e.g., `b0ae26d7-0cb6-42db-9e90-4a057d27c50b`)

### 3. Nullable Fields
- Some fields may be `null` (e.g., `current_rank`, `rank`)
- Handle null values in UI
- Use optional chaining: `data?.current_rank ?? 'N/A'`

### 4. Empty Arrays
- Empty data returns `[]` not `null`
- Check array length before rendering
- Show "No data" message when empty

### 5. Myanmar Language
- All text fields have `_mm` variants
- Example: `subject_name` and `subject_name_mm`
- Use based on app language setting

---

## 🧪 Testing

### Test Credentials
```
Phone: 09123456789
Password: password
Guardian: Ko Nyein Chan
Student: Maung Kyaw Kyaw
```

### Run Test Script
```bash
cd smart-campus-webapp
php test-student-profile-api.php
```

### Expected Result
```
✅ Login successful
✅ Got student ID
✅ Profile Overview - PASSED
✅ Academic Summary - PASSED
✅ Subject Performance - PASSED
✅ Progress Tracking - PASSED
✅ Comparison Data - PASSED
✅ Attendance Summary - PASSED
✅ Rankings & Exam History - PASSED
✅ Achievement Badges - PASSED

Success Rate: 100%
```

---

## 📚 Additional Resources

- **Full API Spec:** `STUDENT_PROFILE_API_SPEC.md`
- **Test Results:** `STUDENT_PROFILE_API_TEST_RESULTS.md`
- **Test Script:** `test-student-profile-api.php`

---

## ✅ Migration Checklist

- [ ] Update API base URL
- [ ] Implement authentication flow
- [ ] Create API service file
- [ ] Remove mock data constants
- [ ] Add state management
- [ ] Implement fetch functions
- [ ] Update UI components to use API data
- [ ] Add loading states
- [ ] Add error handling
- [ ] Test with real backend
- [ ] Test Myanmar language support
- [ ] Test data refresh on student switch

---

**Ready to integrate!** All 8 endpoints tested and working. 🎉
