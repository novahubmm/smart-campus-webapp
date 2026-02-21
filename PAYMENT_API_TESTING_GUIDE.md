# Payment API Testing Guide

## Quick Start

### 1. Import Postman Collection
Import the `Payment_API.postman_collection.json` file into Postman.

### 2. Set Environment Variables
In Postman, set these variables:
- `base_url`: `http://192.168.100.127:8088/api/v1`
- `access_token`: Your authentication token (get from login)
- `student_id`: A valid student ID from your database

### 3. Get Authentication Token

First, login to get an access token:

**Endpoint:** `POST /v1/auth/login`

**Request:**
```json
{
  "email": "parent@example.com",
  "password": "password"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "access_token": "1|abc123...",
    "token_type": "Bearer"
  }
}
```

Copy the `access_token` and set it in Postman environment variables.

---

## Testing Sequence

### Step 0: Get Students List
```
GET {{base_url}}/guardian/students
Authorization: Bearer {{access_token}}
```

**Expected Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "STU-2024-001",
      "name": "Maung Maung",
      "student_id": "STU-2024-001",
      "grade": "Grade 10",
      "section": "A",
      "relationship": "Father",
      "is_primary": true
    }
  ]
}
```

**Note:** Copy the `id` value from the response and use it as `{{student_id}}` in subsequent requests.

---

### Step 1: Get Payment Options (No Auth Required)
```
GET {{base_url}}/payment-options
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "payment_options": [
      {
        "months": 1,
        "discount_percent": 0,
        "label": "1 Month",
        "is_default": true
      },
      {
        "months": 3,
        "discount_percent": 5,
        "label": "3 Months",
        "badge": "5% OFF"
      }
    ],
    "default_months": 1,
    "max_months": 12
  }
}
```

---

### Step 2: Get Payment Methods
```
GET {{base_url}}/payment-methods
Authorization: Bearer {{access_token}}
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "payment_methods": [
      {
        "id": "pm-001",
        "name": "KBZ Bank",
        "type": "bank",
        "account_number": "1234567890",
        "account_name": "Smart Campus Education"
      }
    ],
    "total": 2
  }
}
```

---

### Step 3: Get Student Invoices
```
GET {{base_url}}/students/{{student_id}}/invoices?status=pending
Authorization: Bearer {{access_token}}
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "invoices": [
      {
        "id": "INV-2024-001",
        "invoice_number": "INV-2024-001",
        "total_amount": 110000,
        "remaining_amount": 110000,
        "status": "pending",
        "fees": [
          {
            "id": "if-001",
            "fee_name": "Tuition",
            "amount": 80000,
            "supports_payment_period": true
          }
        ]
      }
    ],
    "counts": {
      "total": 1,
      "pending": 1
    }
  }
}
```

---

### Step 4: Submit Full Payment
```
POST {{base_url}}/students/{{student_id}}/payments/submit
Authorization: Bearer {{access_token}}
Content-Type: application/json
```

**Request Body:**
```json
{
  "invoice_ids": ["INV-2024-001"],
  "payment_method_id": "pm-001",
  "payment_amount": 110000,
  "payment_type": "full",
  "payment_months": 1,
  "payment_date": "2024-03-15",
  "receipt_image": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAA...",
  "fee_payment_details": [
    {
      "fee_id": "if-001",
      "fee_name": "Tuition",
      "full_amount": 80000,
      "paid_amount": 80000,
      "is_partial": false
    },
    {
      "fee_id": "if-002",
      "fee_name": "Transportation Fee",
      "full_amount": 30000,
      "paid_amount": 30000,
      "is_partial": false
    }
  ],
  "notes": "Full payment for March 2024"
}
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Payment submitted successfully",
  "data": {
    "payment": {
      "id": "...",
      "payment_number": "PAY-20240315-ABC123",
      "status": "pending_verification",
      "payment_amount": 110000,
      "receipt_image_url": "http://..."
    }
  }
}
```

---

### Step 5: Submit Partial Payment
```
POST {{base_url}}/students/{{student_id}}/payments/submit
Authorization: Bearer {{access_token}}
Content-Type: application/json
```

**Request Body:**
```json
{
  "invoice_ids": ["INV-2024-001"],
  "payment_method_id": "pm-001",
  "payment_amount": 50000,
  "payment_type": "partial",
  "payment_months": 1,
  "payment_date": "2024-03-15",
  "receipt_image": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAA...",
  "fee_payment_details": [
    {
      "fee_id": "if-001",
      "fee_name": "Tuition",
      "full_amount": 80000,
      "paid_amount": 50000,
      "is_partial": true
    }
  ],
  "notes": "Partial payment - will pay remaining later"
}
```

---

### Step 6: Get Payment History
```
GET {{base_url}}/students/{{student_id}}/payments/history?status=all&per_page=10&page=1
Authorization: Bearer {{access_token}}
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "payments": [
      {
        "id": "...",
        "payment_number": "PAY-20240315-ABC123",
        "payment_date": "2024-03-15",
        "payment_amount": 110000,
        "status": "pending_verification",
        "receipt_url": "http://...",
        "fee_breakdown": [...]
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total": 1,
      "last_page": 1
    }
  }
}
```

---

## Testing Base64 Image Upload

### Option 1: Use a Small Test Image
Create a small 1x1 pixel JPEG:
```
data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCwAA//2Q==
```

### Option 2: Convert Your Own Image
Use this JavaScript code in browser console:
```javascript
function imageToBase64(file) {
  const reader = new FileReader();
  reader.onload = function(e) {
    console.log(e.target.result);
  };
  reader.readAsDataURL(file);
}

// Usage: Select file input, then:
const fileInput = document.querySelector('input[type="file"]');
imageToBase64(fileInput.files[0]);
```

### Option 3: Use Online Tool
1. Go to https://www.base64-image.de/
2. Upload your receipt image
3. Copy the base64 string (with data URI prefix)
4. Paste into Postman request

---

## Common Error Scenarios to Test

### 1. Invalid Authentication
```
GET {{base_url}}/students/{{student_id}}/invoices
Authorization: Bearer invalid_token
```
**Expected:** 401 Unauthorized

### 2. Missing Required Fields
```json
{
  "invoice_ids": ["INV-2024-001"],
  "payment_method_id": "pm-001"
  // Missing payment_amount, payment_type, etc.
}
```
**Expected:** 422 Validation Error

### 3. Payment Below Minimum
```json
{
  "payment_amount": 3000,  // Below 10,000 MMK minimum
  "fee_payment_details": [
    {
      "fee_id": "if-001",
      "paid_amount": 3000  // Below 5,000 MMK per fee minimum
    }
  ]
}
```
**Expected:** 422 Validation Error

### 4. Invalid Image Format
```json
{
  "receipt_image": "data:application/pdf;base64,..."  // PDF instead of image
}
```
**Expected:** 422 Validation Error

### 5. Oversized Image
Upload an image larger than 5MB.
**Expected:** 422 Validation Error

### 6. Partial Payment on Overdue Fee
Try to make a partial payment on a fee that's past its due date.
**Expected:** 422 Validation Error with message about full payment required

### 7. Payment Exceeding Remaining Amount
```json
{
  "fee_payment_details": [
    {
      "fee_id": "if-001",
      "full_amount": 80000,
      "paid_amount": 90000  // More than full_amount
    }
  ]
}
```
**Expected:** 422 Validation Error

---

## Database Verification

After submitting a payment, verify in the database:

### Check Payment Record
```sql
SELECT * FROM payments_payment_system 
WHERE student_id = 'STU-2024-001' 
ORDER BY created_at DESC 
LIMIT 1;
```

### Check Payment Fee Details
```sql
SELECT * FROM payment_fee_details 
WHERE payment_id = 'PAY-20240315-ABC123';
```

### Check Invoice Status Update
```sql
SELECT id, invoice_number, total_amount, paid_amount, remaining_amount, status 
FROM invoices_payment_system 
WHERE id = 'INV-2024-001';
```

### Check Invoice Fee Status Update
```sql
SELECT id, fee_name, amount, paid_amount, remaining_amount, status 
FROM invoice_fees 
WHERE invoice_id = 'INV-2024-001';
```

### Check Receipt Image Upload
```bash
ls -lh storage/app/public/payment_receipts/
```

---

## Performance Testing

### Test Concurrent Payments
Use Postman Runner or Newman to test multiple payment submissions:

1. Create a collection with 10 payment requests
2. Run them concurrently
3. Verify no duplicate payments are created
4. Check database consistency

### Test Large Image Upload
1. Create a 4.9MB image (just under 5MB limit)
2. Convert to base64
3. Submit payment
4. Verify upload succeeds

---

## Troubleshooting

### Issue: 401 Unauthorized
**Solution:** 
- Check if token is valid
- Check if token is properly set in Authorization header
- Try logging in again to get a fresh token

### Issue: 404 Not Found
**Solution:**
- Check if route exists in `routes/api.php`
- Verify base URL is correct
- Check if student_id exists in database

### Issue: 422 Validation Error
**Solution:**
- Read error message carefully
- Check all required fields are present
- Verify data types match expectations
- Check minimum/maximum constraints

### Issue: 500 Internal Server Error
**Solution:**
- Check Laravel logs: `storage/logs/laravel.log`
- Check database connection
- Verify all migrations are run
- Check file permissions for storage directory

---

## Success Criteria

✅ All 7 endpoints return expected responses
✅ Authentication works correctly
✅ Validation errors are clear and helpful
✅ Images upload successfully
✅ Database records are created correctly
✅ Invoice statuses update properly
✅ Payment history shows all submissions
✅ Error cases are handled gracefully

---

## Next Steps After Testing

1. **Fix any issues found during testing**
2. **Update seeders** to create sample data
3. **Implement admin verification endpoints**
4. **Add notification system** for payment submissions
5. **Create admin UI** for payment verification
6. **Write automated tests** (PHPUnit)
7. **Update API documentation** with real examples
8. **Deploy to staging** for mobile team testing

---

## Contact

For issues or questions:
- Backend Team: [email]
- API Documentation: [link]
- Postman Collection: `Payment_API.postman_collection.json`
