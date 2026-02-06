# Multi-Role Implementation Checklist

## Overview
This checklist helps you implement and test the multi-role user feature for the Smart Campus unified app.

---

## ✅ Backend Implementation (COMPLETED)

### 1. UnifiedAuthController Updates
- [x] Updated `login()` method to detect multiple roles
- [x] Added `handleMultiRoleLogin()` method
- [x] Added `availableRoles()` endpoint
- [x] Added `switchRole()` endpoint
- [x] Returns separate tokens for each role
- [x] Returns `available_roles` array
- [x] Returns `user_data` object with role-specific data

### 2. API Routes
- [x] Added `GET /api/v1/auth/available-roles`
- [x] Added `POST /api/v1/auth/switch-role`
- [x] Protected routes with `auth:sanctum` middleware

### 3. Documentation
- [x] Created `MULTI_ROLE_API_GUIDE.md`
- [x] Updated `UNIFIED_APP_IMPLEMENTATION_GUIDE.md`
- [x] Created Postman collection `Multi_Role_API.postman_collection.json`
- [x] Created implementation checklist

---

## 📱 Mobile App Implementation (ALREADY DONE)

The mobile app (SmartCampusv1.0.0) already has:
- [x] Unified login screen
- [x] Role switching without logout
- [x] Separate token storage for each role
- [x] Role detection and switching UI
- [x] Settings menu with role switch option

**Location:** `SmartCampusv1.0.0/src/`

---

## 🧪 Testing Checklist

### 1. Database Setup

**Option A: Use the Seeder (RECOMMENDED)**
```bash
cd smart-campus-webapp
php artisan db:seed --class=MultiRoleUserSeeder
```

This creates **Ko Nyein Chan** with:
- ✅ Both teacher and guardian roles
- ✅ Teacher profile (English teacher, Grade 1)
- ✅ Guardian profile with 4 students (3 in KG-A, 1 in Grade 2)
- ✅ Login: `konyeinchan@smartcampusedu.com` / `password`

See `MULTI_ROLE_SEEDER_GUIDE.md` for details.

**Option B: Manual SQL Setup**
- [ ] Create a test user with BOTH teacher and guardian roles
- [ ] Verify user has `teacher` role in `model_has_roles` table
- [ ] Verify user has `guardian` role in `model_has_roles` table
- [ ] Verify user has `teacher_profiles` record
- [ ] Verify user has `guardian_profiles` record with students

**Verification Query:**
```sql
-- Check user roles
SELECT u.id, u.name, u.email, r.name as role_name
FROM users u
JOIN model_has_roles mhr ON u.id = mhr.model_id
JOIN roles r ON mhr.role_id = r.id
WHERE u.email = 'konyeinchan@smartcampusedu.com';

-- Should return 2 rows (teacher and guardian)
```

### 2. API Testing with Postman

#### Test 1: Single Role Login (Teacher)
- [ ] Import `Multi_Role_API.postman_collection.json`
- [ ] Set `base_url` environment variable
- [ ] Run "Login - Single Role (Teacher Only)"
- [ ] Verify response has `user_type: "teacher"`
- [ ] Verify response has single `token`
- [ ] Verify NO `available_roles` or `tokens` object

#### Test 2: Single Role Login (Guardian)
- [ ] Run "Login - Single Role (Guardian Only)"
- [ ] Verify response has `user_type: "guardian"`
- [ ] Verify response has single `token`
- [ ] Verify NO `available_roles` or `tokens` object

#### Test 3: Multi-Role Login
- [ ] Run "Login - Multi-Role (Teacher + Guardian)"
- [ ] Verify response has `available_roles: ["teacher", "guardian"]`
- [ ] Verify response has `tokens` object with both tokens
- [ ] Verify response has `user_data` object with both profiles
- [ ] Verify `user_type` is set to default role (guardian)
- [ ] Verify both tokens are saved in environment

#### Test 4: Available Roles Endpoint
- [ ] Run "Get Available Roles"
- [ ] Verify response has `available_roles` array
- [ ] Verify response has `role_data` object
- [ ] Verify `role_data.teacher` has teacher info
- [ ] Verify `role_data.guardian` has students array
- [ ] Verify `has_multiple_roles: true`

#### Test 5: Switch to Teacher Role
- [ ] Run "Switch to Teacher Role"
- [ ] Verify response has `user_type: "teacher"`
- [ ] Verify new token is returned
- [ ] Verify teacher profile data is returned
- [ ] Run "Get Profile" to confirm teacher profile

#### Test 6: Switch to Guardian Role
- [ ] Run "Switch to Guardian Role"
- [ ] Verify response has `user_type: "guardian"`
- [ ] Verify new token is returned
- [ ] Verify guardian profile data is returned
- [ ] Run "Get Profile" to confirm guardian profile

#### Test 7: Dashboard Access
- [ ] With teacher token, run "Get Dashboard"
- [ ] Verify teacher-specific dashboard data
- [ ] Switch to guardian token
- [ ] Run "Get Dashboard" again
- [ ] Verify guardian-specific dashboard data

### 3. Mobile App Testing

#### Test 1: Single Role User
- [ ] Login with teacher-only account
- [ ] Verify direct access to teacher portal
- [ ] Check Settings - should NOT show role switch option
- [ ] Logout

#### Test 2: Multi-Role User - First Login
- [ ] Login with multi-role account
- [ ] Verify login success
- [ ] Check which portal opens (should be guardian by default)
- [ ] Navigate to Settings
- [ ] Verify "Switch to Teacher Portal" option appears

#### Test 3: Role Switching
- [ ] From guardian portal, go to Settings
- [ ] Click "Switch to Teacher Portal"
- [ ] Confirm dialog
- [ ] Verify switch to teacher portal (no logout)
- [ ] Check Settings - should show "Switch to Parent Portal"
- [ ] Switch back to guardian portal
- [ ] Verify successful switch

#### Test 4: Token Persistence
- [ ] Login as multi-role user
- [ ] Switch to teacher role
- [ ] Close app completely
- [ ] Reopen app
- [ ] Verify still in teacher role
- [ ] Verify no re-login required

#### Test 5: API Calls with Correct Token
- [ ] Login as multi-role user (guardian default)
- [ ] Make API call (e.g., get students)
- [ ] Verify guardian token is used
- [ ] Switch to teacher role
- [ ] Make API call (e.g., get classes)
- [ ] Verify teacher token is used

---

## 🔧 Troubleshooting

### Issue: User doesn't have multiple roles
**Solution:**
```sql
-- Add teacher role
INSERT INTO model_has_roles (role_id, model_type, model_id)
SELECT r.id, 'App\\Models\\User', u.id
FROM roles r, users u
WHERE r.name = 'teacher' AND u.email = 'test@example.com';

-- Add guardian role
INSERT INTO model_has_roles (role_id, model_type, model_id)
SELECT r.id, 'App\\Models\\User', u.id
FROM roles r, users u
WHERE r.name = 'guardian' AND u.email = 'test@example.com';
```

### Issue: Login returns single role for multi-role user
**Check:**
1. Verify user has both roles in database
2. Check `UnifiedAuthController::login()` logic
3. Enable Laravel debug mode and check logs
4. Test with Postman to isolate mobile app issues

### Issue: Switch role returns 403 error
**Check:**
1. Verify user has the target role
2. Check token is valid and not expired
3. Verify middleware is not blocking the request

### Issue: Mobile app doesn't show role switch option
**Check:**
1. Verify login response includes `available_roles`
2. Check `checkTeacherRole()` function in mobile app
3. Verify token storage keys are correct
4. Check AsyncStorage for stored tokens

---

## 📊 Success Criteria

### Backend
- ✅ Multi-role login returns separate tokens
- ✅ Available roles endpoint works
- ✅ Switch role endpoint works
- ✅ Single-role users still work (backward compatible)
- ✅ All endpoints properly secured

### Mobile App
- ✅ Single-role users login normally
- ✅ Multi-role users see role switch option
- ✅ Role switching works without logout
- ✅ Correct token used for each role
- ✅ UI updates correctly after role switch

### User Experience
- ✅ Seamless role switching (< 1 second)
- ✅ No data loss during role switch
- ✅ Clear indication of current role
- ✅ Intuitive role switch UI
- ✅ No confusion for single-role users

---

## 🚀 Deployment Steps

### 1. Backend Deployment
```bash
# Pull latest code
cd smart-campus-webapp
git pull origin main

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Run migrations (if any)
php artisan migrate

# Restart queue workers
php artisan queue:restart
```

### 2. Mobile App Deployment
```bash
# The mobile app already has the implementation
# Just ensure users update to latest version

# For Android
cd SmartCampusv1.0.0
npm run android

# For iOS
cd SmartCampusv1.0.0
npm run ios
```

### 3. Post-Deployment Verification
- [ ] Test login with single-role user
- [ ] Test login with multi-role user
- [ ] Test role switching
- [ ] Monitor error logs
- [ ] Check API response times

---

## 📝 Notes

### Token Storage Keys
- Teacher token: `@smartcampus_auth_token`
- Guardian token: `access_token`
- Active role: `@smartcampus_active_role`

### Default Role Priority
When user has multiple roles, default is:
1. Guardian (preferred)
2. Teacher

### Token Expiration
- Teacher: 30 days
- Guardian: 7-30 days (based on remember_me)

---

## 🎯 Next Steps

After successful implementation:
1. [ ] Monitor user feedback
2. [ ] Track role switching analytics
3. [ ] Optimize role switch performance
4. [ ] Add role indicators in UI
5. [ ] Consider adding more roles (admin, student)

---

**Created:** February 6, 2026  
**Status:** Ready for Testing  
**Priority:** High
