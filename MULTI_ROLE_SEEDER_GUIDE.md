# Multi-Role User Seeder Guide

## Overview

This seeder creates **Ko Nyein Chan**, a user with both teacher and guardian roles, perfect for testing the multi-role functionality.

---

## What Gets Created

### 👤 User: Ko Nyein Chan
- **Email:** `konyeinchan@smartcampusedu.com`
- **Password:** `password`
- **Phone:** `09123456789`
- **Roles:** Teacher + Guardian

### 👨‍🏫 Teacher Profile
- **Employee ID:** TCH-2025-KNC
- **Position:** English Teacher
- **Department:** English Department
- **Subject:** English
- **Teaching:** Grade 1A, Grade 1B
- **Salary:** 600,000 MMK

### 👨‍👩‍👧‍👦 Guardian Profile
- **Occupation:** Teacher & Business Owner
- **Address:** No. 123, Main Street, Yangon
- **Emergency Contact:** Daw Mya Mya (09987654321)

### 👶 Children (4 Students)

#### Kindergarten A (3 students):
1. **Maung Aung Aung** (Male)
   - ID: KG-A-001
   - Email: maungaungaung@student.smartcampusedu.com

2. **Maung Kyaw Kyaw** (Male)
   - ID: KG-A-002
   - Email: maungkyawkyaw@student.smartcampusedu.com

3. **Ma Thida Win** (Female)
   - ID: KG-A-003
   - Email: mathidawin@student.smartcampusedu.com

#### Grade 2A (1 student):
4. **Ma Su Su Hlaing** (Female)
   - ID: G2-A-001
   - Email: masusuhlaing@student.smartcampusedu.com

---

## How to Run

### Option 1: Run Standalone
```bash
cd smart-campus-webapp
php artisan db:seed --class=MultiRoleUserSeeder
```

### Option 2: Add to DatabaseSeeder
Edit `database/seeders/DatabaseSeeder.php`:

```php
public function run(): void
{
    // ... other seeders ...
    
    $this->call([
        MultiRoleUserSeeder::class,
    ]);
}
```

Then run:
```bash
php artisan db:seed
```

### Option 3: Fresh Migration with Seeder
```bash
php artisan migrate:fresh --seed
```

---

## Expected Output

```
🚀 Creating Multi-Role User: Ko Nyein Chan
   Role 1: Teacher (English, Grade 1)
   Role 2: Guardian (3 kids in KG-A, 1 girl in Grade 2)

✓ Roles verified
✓ Using batch: 2025-2026
✓ User created with BOTH teacher and guardian roles
✓ Teacher profile created
✓ Assigned to teach English in 2 Grade 1 class(es)
✓ Guardian profile created
Creating students...
  ✓ Created: Maung Aung Aung (Kindergarten A)
  ✓ Created: Maung Kyaw Kyaw (Kindergarten A)
  ✓ Created: Ma Thida Win (Kindergarten A)
  ✓ Created: Ma Su Su Hlaing (Grade 2A)
✓ Created 4 students
Linking students to guardian...
✓ Linked 4 students to guardian

✅ Multi-Role User Created Successfully!

═══════════════════════════════════════════════════════
📋 MULTI-ROLE USER SUMMARY
═══════════════════════════════════════════════════════

👤 USER INFORMATION:
   Name:     Ko Nyein Chan
   Email:    konyeinchan@smartcampusedu.com
   Phone:    09123456789
   Password: password

👨‍🏫 TEACHER ROLE:
   Employee ID: TCH-2025-KNC
   Position:    English Teacher
   Department:  English Department
   Subject:     English
   Grade:       Grade 1

👨‍👩‍👧‍👦 GUARDIAN ROLE:
   Occupation:  Teacher & Business Owner
   Students:    4

👶 CHILDREN:
   1. Maung Aung Aung
      Grade:  Kindergarten - Section A
      Gender: Male
      ID:     KG-A-001
   2. Maung Kyaw Kyaw
      Grade:  Kindergarten - Section A
      Gender: Male
      ID:     KG-A-002
   3. Ma Thida Win
      Grade:  Kindergarten - Section A
      Gender: Female
      ID:     KG-A-003
   4. Ma Su Su Hlaing
      Grade:  2 - Section A
      Gender: Female
      ID:     G2-A-001

🔑 LOGIN CREDENTIALS:
   Email:    konyeinchan@smartcampusedu.com
   Password: password

🧪 TESTING:
   1. Login with the credentials above
   2. API should return available_roles: ["teacher", "guardian"]
   3. API should return separate tokens for each role
   4. Mobile app should show role switch option

═══════════════════════════════════════════════════════
```

---

## Testing the Multi-Role Feature

### 1. Test with Postman

Import `Multi_Role_API.postman_collection.json` and:

```json
POST /api/v1/auth/login
{
    "login": "konyeinchan@smartcampusedu.com",
    "password": "password",
    "device_name": "Postman Test"
}
```

**Expected Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": { /* guardian profile by default */ },
        "user_data": {
            "teacher": { /* teacher profile */ },
            "guardian": { /* guardian profile */ }
        },
        "user_type": "guardian",
        "available_roles": ["teacher", "guardian"],
        "tokens": {
            "teacher": "2|xyz...",
            "guardian": "3|abc..."
        },
        "token": "3|abc...",
        "token_type": "Bearer",
        "expires_at": "2026-03-05T10:30:00.000000Z"
    }
}
```

### 2. Test Available Roles

```bash
GET /api/v1/auth/available-roles
Authorization: Bearer {token}
```

**Expected Response:**
```json
{
    "success": true,
    "data": {
        "available_roles": ["teacher", "guardian"],
        "role_data": {
            "teacher": {
                "type": "teacher",
                "data": {
                    "teacher_id": "TCH-2025-KNC",
                    "department": "English Department",
                    "position": "English Teacher"
                }
            },
            "guardian": {
                "type": "guardian",
                "data": {
                    "students": [
                        {
                            "name": "Maung Aung Aung",
                            "grade": "Kindergarten",
                            "section": "A"
                        },
                        {
                            "name": "Maung Kyaw Kyaw",
                            "grade": "Kindergarten",
                            "section": "A"
                        },
                        {
                            "name": "Ma Thida Win",
                            "grade": "Kindergarten",
                            "section": "A"
                        },
                        {
                            "name": "Ma Su Su Hlaing",
                            "grade": "2",
                            "section": "A"
                        }
                    ],
                    "student_count": 4
                }
            }
        },
        "has_multiple_roles": true
    }
}
```

### 3. Test Role Switching

```bash
POST /api/v1/auth/switch-role
Authorization: Bearer {current_token}
{
    "role": "teacher",
    "device_name": "Postman Test"
}
```

**Expected Response:**
```json
{
    "success": true,
    "message": "Switched to teacher role successfully",
    "data": {
        "user": { /* teacher profile */ },
        "user_type": "teacher",
        "token": "4|new_token...",
        "token_type": "Bearer",
        "expires_at": "2026-03-05T10:30:00.000000Z"
    }
}
```

### 4. Test Mobile App

1. Login with `konyeinchan@smartcampusedu.com` / `password`
2. Should see guardian portal by default
3. Navigate to Settings
4. Should see "Switch to Teacher Portal" option
5. Click to switch
6. Should navigate to teacher portal without logout
7. In teacher portal, Settings should show "Switch to Guardian Portal"

---

## Verification Queries

### Check User Roles
```sql
SELECT u.name, u.email, r.name as role_name
FROM users u
JOIN model_has_roles mhr ON u.id = mhr.model_id
JOIN roles r ON mhr.role_id = r.id
WHERE u.email = 'konyeinchan@smartcampusedu.com';
```

**Expected Result:**
```
+----------------+----------------------------------+----------+
| name           | email                            | role_name|
+----------------+----------------------------------+----------+
| Ko Nyein Chan  | konyeinchan@smartcampusedu.com  | teacher  |
| Ko Nyein Chan  | konyeinchan@smartcampusedu.com  | guardian |
+----------------+----------------------------------+----------+
```

### Check Teacher Profile
```sql
SELECT tp.employee_id, tp.position, d.name as department, u.name
FROM teacher_profiles tp
JOIN users u ON tp.user_id = u.id
LEFT JOIN departments d ON tp.department_id = d.id
WHERE u.email = 'konyeinchan@smartcampusedu.com';
```

### Check Guardian Profile and Students
```sql
SELECT 
    u.name as guardian_name,
    s.name as student_name,
    g.level as grade,
    c.name as section,
    gs.relationship
FROM users u
JOIN guardian_profiles gp ON u.id = gp.user_id
JOIN guardian_student gs ON gp.id = gs.guardian_profile_id
JOIN student_profiles sp ON gs.student_profile_id = sp.id
JOIN users s ON sp.user_id = s.id
JOIN grades g ON sp.grade_id = g.id
JOIN classes c ON sp.class_id = c.id
WHERE u.email = 'konyeinchan@smartcampusedu.com';
```

**Expected Result:**
```
+----------------+-------------------+-------------+---------+--------------+
| guardian_name  | student_name      | grade       | section | relationship |
+----------------+-------------------+-------------+---------+--------------+
| Ko Nyein Chan  | Maung Aung Aung   | Kindergarten| A       | father       |
| Ko Nyein Chan  | Maung Kyaw Kyaw   | Kindergarten| A       | father       |
| Ko Nyein Chan  | Ma Thida Win      | Kindergarten| A       | father       |
| Ko Nyein Chan  | Ma Su Su Hlaing   | 2           | A       | father       |
+----------------+-------------------+-------------+---------+--------------+
```

---

## Troubleshooting

### Issue: Seeder fails with "Batch not found"
**Solution:** Create a batch first:
```bash
php artisan db:seed --class=DemoDataSeeder
# or manually create a batch in the database
```

### Issue: "Grade not found"
**Solution:** The seeder will automatically create Kindergarten and Grade 2 if they don't exist.

### Issue: "Department not found"
**Solution:** The seeder will automatically create the English Department.

### Issue: Duplicate entry error
**Solution:** The seeder uses `firstOrCreate`, so it's safe to run multiple times. It will update existing records instead of creating duplicates.

---

## Cleanup

To remove the test user and all related data:

```sql
-- Get user ID
SET @user_id = (SELECT id FROM users WHERE email = 'konyeinchan@smartcampusedu.com');

-- Delete in order (respecting foreign keys)
DELETE FROM guardian_student WHERE guardian_profile_id IN (SELECT id FROM guardian_profiles WHERE user_id = @user_id);
DELETE FROM student_class WHERE student_id IN (SELECT id FROM student_profiles WHERE father_name = 'Ko Nyein Chan');
DELETE FROM student_profiles WHERE father_name = 'Ko Nyein Chan';
DELETE FROM users WHERE name LIKE 'Maung Aung Aung%' OR name LIKE 'Maung Kyaw Kyaw%' OR name LIKE 'Ma Thida Win%' OR name LIKE 'Ma Su Su Hlaing%';
DELETE FROM guardian_profiles WHERE user_id = @user_id;
DELETE FROM teacher_profiles WHERE user_id = @user_id;
DELETE FROM model_has_roles WHERE model_id = @user_id;
DELETE FROM users WHERE id = @user_id;
```

Or simply run:
```bash
php artisan migrate:fresh --seed
```

---

## Next Steps

After running the seeder:

1. ✅ Test login with Postman
2. ✅ Verify multi-role response
3. ✅ Test available-roles endpoint
4. ✅ Test switch-role endpoint
5. ✅ Test with mobile app
6. ✅ Verify role switching works without logout

---

**Created:** February 6, 2026  
**Version:** 1.0.0  
**Status:** Ready to Use
