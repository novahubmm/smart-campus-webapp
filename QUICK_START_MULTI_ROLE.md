# Multi-Role Feature - Quick Start Guide

## ⚡ 3-Minute Setup

### Step 1: Run the Seeder (30 seconds)
```bash
cd smart-campus-webapp
php artisan db:seed --class=MultiRoleUserSeeder
```

**Expected Output:**
```
✅ Multi-Role User Created Successfully!

👤 USER: Ko Nyein Chan
📧 EMAIL: konyeinchan@smartcampusedu.com
🔑 PASSWORD: password
```

---

### Step 2: Test with Postman (1 minute)

**Import Collection:**
- File: `Multi_Role_API.postman_collection.json`
- Set `base_url` variable

**Test Login:**
```bash
POST {{base_url}}/api/v1/auth/login
```
```json
{
    "login": "konyeinchan@smartcampusedu.com",
    "password": "password",
    "device_name": "Postman"
}
```

**✅ Success Response Should Include:**
```json
{
    "available_roles": ["teacher", "guardian"],
    "tokens": {
        "teacher": "...",
        "guardian": "..."
    }
}
```

---

### Step 3: Test Mobile App (1 minute)

1. **Login:**
   - Email: `konyeinchan@smartcampusedu.com`
   - Password: `password`

2. **Verify:**
   - ✅ Should see guardian portal (default)
   - ✅ Settings should show "Switch to Teacher Portal"

3. **Switch Role:**
   - Click "Switch to Teacher Portal"
   - ✅ Should switch without logout
   - ✅ Should see teacher portal

---

## 🎯 What You Get

### Test User: Ko Nyein Chan

**👨‍🏫 As Teacher:**
- Teaching English in Grade 1
- Employee ID: TCH-2025-KNC
- Can view classes, students, attendance

**👨‍👩‍👧‍👦 As Guardian:**
- Has 4 children:
  - 3 boys in Kindergarten A
  - 1 girl in Grade 2A
- Can view children's progress, homework, attendance

---

## 📱 Mobile App Storage

After login, check AsyncStorage:

```javascript
@smartcampus_auth_token     → Teacher token
access_token                → Guardian token
@smartcampus_active_role    → "guardian" (default)
@smartcampus_available_roles → ["teacher","guardian"]
```

---

## 🧪 Quick Tests

### Test 1: Check Available Roles
```bash
GET {{base_url}}/api/v1/auth/available-roles
Authorization: Bearer {{token}}
```

**Expected:**
```json
{
    "available_roles": ["teacher", "guardian"],
    "has_multiple_roles": true
}
```

### Test 2: Switch to Teacher
```bash
POST {{base_url}}/api/v1/auth/switch-role
Authorization: Bearer {{token}}
```
```json
{
    "role": "teacher"
}
```

**Expected:**
```json
{
    "user_type": "teacher",
    "token": "new_teacher_token"
}
```

### Test 3: Switch Back to Guardian
```bash
POST {{base_url}}/api/v1/auth/switch-role
```
```json
{
    "role": "guardian"
}
```

---

## ✅ Success Criteria

- [x] Seeder runs without errors
- [x] Login returns `available_roles` array
- [x] Login returns separate `tokens` object
- [x] Mobile app shows role switch option
- [x] Role switching works without logout
- [x] Correct token used for each role

---

## 🐛 Quick Troubleshooting

| Issue | Solution |
|-------|----------|
| Seeder fails | Run `php artisan db:seed --class=DemoDataSeeder` first |
| No available_roles | Check user has both roles in database |
| Mobile app no switch option | Verify login response format |
| Token expired | Call switch-role endpoint |

---

## 📚 Full Documentation

- **Complete Guide:** `MULTI_ROLE_API_GUIDE.md`
- **Seeder Details:** `MULTI_ROLE_SEEDER_GUIDE.md`
- **Architecture:** `MULTI_ROLE_ARCHITECTURE_DIAGRAM.md`
- **Quick Reference:** `MULTI_ROLE_QUICK_REFERENCE.md`

---

## 🎉 That's It!

You now have a fully functional multi-role user system!

**Test Credentials:**
- Email: `konyeinchan@smartcampusedu.com`
- Password: `password`

**Happy Testing! 🚀**
