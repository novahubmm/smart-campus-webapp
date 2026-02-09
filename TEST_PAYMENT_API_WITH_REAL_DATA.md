# Testing Payment APIs with Real Data

**Date**: February 9, 2026

---

## ✅ The Error is CORRECT!

The error `"Student not found or unauthorized"` means the API is **working correctly** and enforcing security! 

This happens when:
1. ❌ The student_id doesn't belong to the authenticated guardian
2. ❌ The guardian doesn't have permission to access that student
3. ❌ Invalid student_id format

---

## 🔐 How to Test Correctly

### Step 1: Login as Guardian

Use one of these test guardians:

**Guardian 1:**
```bash
curl -X POST "http://192.168.100.114:8088/api/v1/guardian/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "identifier": "guardian1@smartcampusedu.com",
    "password": "password"
  }'
```

**Guardian 2 (Ko Nyein Chan - has 4 students):**
```bash
curl -X POST "http://192.168.100.114:8088/api/v1/guardian/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "identifier": "konyeinchan@smartcampusedu.com",
    "password": "password"
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {...},
    "token": "1|abcdefghijklmnopqrstuvwxyz..."
  }
}
```

**Save the token!** You'll need it for the next steps.

---

### Step 2: Get Your Students

```bash
curl -X GET "http://192.168.100.114:8088/api/v1/guardian/students" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "b0ae26d7-0cb6-42db-9e90-4a057d27c50b",
      "name": "Maung Kyaw Kyaw",
      "grade": "Kindergarten",
      "class": "A"
    },
    {
      "id": "b6df3624-6d40-4dd9-8128-29cea5fe3017",
      "name": "Ma Su Su Hlaing",
      "grade": "Grade 2",
      "class": "A"
    }
  ]
}
```

**Pick a student_id** from this list!

---

### Step 3: Test Fee Structure API

Now use the **correct student_id** from your students list:

```bash
curl -X GET "http://192.168.100.114:8088/api/v1/guardian/students/b0ae26d7-0cb6-42db-9e90-4a057d27c50b/fees/structure" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

**Expected Success Response:**
```json
{
  "success": true,
  "message": "Fee structure retrieved successfully",
  "data": {
    "student_id": "b0ae26d7-0cb6-42db-9e90-4a057d27c50b",
    "student_name": "Maung Kyaw Kyaw",
    "grade": "Kindergarten",
    "section": "A",
    "academic_year": "2026-2027",
    "monthly_fees": [...],
    "additional_fees": [...],
    "total_monthly": 0,
    "currency": "MMK"
  }
}
```

---

## 📝 Complete Test Example

### Using Guardian: Ko Nyein Chan

**1. Login:**
```bash
curl -X POST "http://192.168.100.114:8088/api/v1/guardian/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "identifier": "konyeinchan@smartcampusedu.com",
    "password": "password"
  }'
```

**2. Save the token from response**

**3. Test with one of Ko Nyein Chan's students:**

Student Options:
- `b0ae26d7-0cb6-42db-9e90-4a057d27c50b` - Maung Kyaw Kyaw (Kindergarten A)
- `b6df3624-6d40-4dd9-8128-29cea5fe3017` - Ma Su Su Hlaing (Grade 2 A)
- `c3f30ca9-cd52-4cb5-bd4e-2ec940889fe7` - Maung Aung Aung (Kindergarten A)
- `e17ccfb7-42f4-476d-a96d-477b6c5134b5` - Ma Thida Win (Kindergarten A)

**4. Test Fee Structure:**
```bash
curl -X GET "http://192.168.100.114:8088/api/v1/guardian/students/b0ae26d7-0cb6-42db-9e90-4a057d27c50b/fees/structure" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

---

## 🎯 Test All Payment APIs

Once you have a valid token and student_id:

### 1. Fee Structure ✅
```bash
GET /api/v1/guardian/students/{student_id}/fees/structure
```

### 2. Payment Methods ✅
```bash
GET /api/v1/guardian/payment-methods
```

### 3. Payment Options ✅
```bash
GET /api/v1/guardian/payment-options
```

### 4. Payment History ✅
```bash
GET /api/v1/guardian/students/{student_id}/fees/payment-history
```

### 5. Submit Payment ✅
```bash
POST /api/v1/guardian/students/{student_id}/fees/payments
{
  "fee_ids": ["fee-1"],
  "payment_method_id": "pm-1",
  "payment_amount": 100000,
  "payment_months": 1,
  "payment_date": "2026-02-09",
  "receipt_image": "data:image/jpeg;base64,...",
  "notes": "Test payment"
}
```

---

## 🔍 Troubleshooting

### Error: "Student not found or unauthorized"

**Causes:**
1. ❌ Using wrong student_id (not belonging to this guardian)
2. ❌ Token expired or invalid
3. ❌ Student_id format incorrect

**Solution:**
1. ✅ Login as guardian first
2. ✅ Get list of your students
3. ✅ Use student_id from YOUR students list
4. ✅ Use fresh token (not expired)

### Error: "Unauthenticated"

**Cause:** Missing or invalid Bearer token

**Solution:**
```bash
# Make sure to include Authorization header
-H "Authorization: Bearer YOUR_ACTUAL_TOKEN"
```

### Error: "Validation failed"

**Cause:** Missing required fields or invalid format

**Solution:** Check request body matches the API spec

---

## 📊 Available Test Guardians

| Guardian | Email | Students | Password |
|----------|-------|----------|----------|
| Ko Nyein Chan | konyeinchan@smartcampusedu.com | 4 students | password |
| Guardian 1 | guardian1@smartcampusedu.com | 1 student | password |
| Guardian 1111 | guardian1111@smartcampusedu.com | 1 student | password |
| Guardian 1112 | guardian1112@smartcampusedu.com | 1 student | password |

---

## ✅ Success Indicators

When the API is working correctly, you should see:

1. **Login Success:**
   - `"success": true`
   - `"token": "..."`

2. **Fee Structure Success:**
   - `"success": true`
   - `"student_name": "..."`
   - `"monthly_fees": [...]`
   - `"total_monthly": ...`

3. **Payment Methods Success:**
   - `"success": true`
   - `"total_count": 7`
   - List of 7 payment methods

---

## 🎉 The API is Working!

The error you saw means the **security is working correctly**! 

To test successfully:
1. ✅ Use correct guardian credentials
2. ✅ Use student_id that belongs to that guardian
3. ✅ Include valid Bearer token

---

**Need Help?**
- Run: `php get-guardian-students.php` to see all guardian-student relationships
- Check: `PAYMENT_SCREEN_API_SPEC.md` for complete API documentation
- Use: `Payment_Screen_API.postman_collection.json` for easier testing
