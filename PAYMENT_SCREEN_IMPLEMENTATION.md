# Payment Screen API Implementation Guide

**Date**: February 9, 2026  
**Status**: ✅ Complete - Ready for Testing

---

## 📋 Overview

This document provides complete implementation details for the Payment Screen APIs in the SmartCampus Laravel backend.

---

## 🎯 Implemented Features

### ✅ Models Created
1. **PaymentMethod** - Bank accounts and mobile wallets
2. **PaymentProof** - Payment submission records with receipt images

### ✅ APIs Implemented
1. **GET** `/api/v1/guardian/students/{student_id}/fees/structure` - Fee structure
2. **GET** `/api/v1/guardian/payment-methods` - Payment methods
3. **POST** `/api/v1/guardian/students/{student_id}/fees/payments` - Submit payment
4. **GET** `/api/v1/guardian/payment-options` - Payment period options
5. **GET** `/api/v1/guardian/students/{student_id}/fees/payment-history` - Payment history

### ✅ Files Created

```
app/
├── Models/
│   ├── PaymentMethod.php ✅
│   └── PaymentProof.php ✅
├── Interfaces/Guardian/
│   └── GuardianPaymentRepositoryInterface.php ✅
├── Repositories/Guardian/
│   └── GuardianPaymentRepository.php ✅
├── Http/Controllers/Api/V1/Guardian/
│   └── PaymentController.php ✅
└── Providers/
    └── AppServiceProvider.php ✅ (Updated)

database/
├── migrations/
│   ├── 2026_02_09_000001_create_payment_methods_table.php ✅
│   └── 2026_02_09_000002_create_payment_proofs_table.php ✅
└── seeders/
    └── PaymentMethodSeeder.php ✅

routes/
└── api.php ✅ (Updated)

PAYMENT_SCREEN_API_SPEC.md ✅
PAYMENT_SCREEN_IMPLEMENTATION.md ✅
```

---

## 🚀 Installation Steps

### Step 1: Run Migrations

```bash
cd smart-campus-webapp
php artisan migrate
```

This will create:
- `payment_methods` table
- `payment_proofs` table

### Step 2: Seed Payment Methods

```bash
php artisan db:seed --class=PaymentMethodSeeder
```

This will create sample payment methods:
- KBZ Bank
- AYA Bank
- CB Bank
- KBZPay
- Wave Pay
- AYA Pay
- CB Pay

### Step 3: Create Storage Link (if not exists)

```bash
php artisan storage:link
```

This creates a symbolic link from `public/storage` to `storage/app/public` for receipt image access.

### Step 4: Set Permissions

```bash
chmod -R 775 storage/app/public
chmod -R 775 bootstrap/cache
```

---

## 📡 API Endpoints

### 1. Get Fee Structure

**Endpoint:**
```
GET /api/v1/guardian/students/{student_id}/fees/structure
```

**Headers:**
```
Authorization: Bearer {access_token}
Content-Type: application/json
```

**Query Parameters:**
- `academic_year` (optional): e.g., "2025-2026"

**Example Request:**
```bash
curl -X GET "http://192.168.100.114:8088/api/v1/guardian/students/STU-001/fees/structure" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

**Example Response:**
```json
{
  "success": true,
  "message": "Fee structure retrieved successfully",
  "data": {
    "student_id": "STU-001",
    "student_name": "Maung Maung",
    "grade": "Grade 10",
    "section": "A",
    "academic_year": "2025-2026",
    "monthly_fees": [
      {
        "id": "fee-1",
        "name": "Tuition Fee",
        "name_mm": "စာသင်ကြေး",
        "amount": 120000,
        "removable": false,
        "description": "Monthly tuition fee",
        "description_mm": "လစဉ် စာသင်ကြေး"
      }
    ],
    "additional_fees": [
      {
        "id": "fee-2",
        "name": "Lab Fee",
        "name_mm": "ဓာတ်ခွဲခန်းကြေး",
        "amount": 15000,
        "removable": true,
        "description": "Laboratory usage fee",
        "description_mm": "ဓာတ်ခွဲခန်း အသုံးပြုခ"
      }
    ],
    "total_monthly": 135000,
    "currency": "MMK",
    "currency_symbol": "MMK"
  }
}
```

---

### 2. Get Payment Methods

**Endpoint:**
```
GET /api/v1/guardian/payment-methods
```

**Headers:**
```
Authorization: Bearer {access_token}
Content-Type: application/json
```

**Query Parameters:**
- `type` (optional): "bank", "mobile_wallet", "all" (default: "all")
- `active_only` (optional): true/false (default: true)

**Example Request:**
```bash
curl -X GET "http://192.168.100.114:8088/api/v1/guardian/payment-methods?type=all" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

**Example Response:**
```json
{
  "success": true,
  "message": "Payment methods retrieved successfully",
  "data": {
    "methods": [
      {
        "id": "pm-1",
        "name": "KBZ Bank",
        "name_mm": "KBZ ဘဏ်",
        "type": "bank",
        "account_number": "01234567890123456",
        "account_name": "SmartCampus School",
        "account_name_mm": "SmartCampus ကျောင်း",
        "logo_url": "http://192.168.100.114:8088/images/payment-methods/kbz.png",
        "is_active": true,
        "instructions": "Transfer to this account and upload receipt",
        "instructions_mm": "ဒီ account ကို လွှဲပြီး ပြေစာ upload လုပ်ပါ",
        "sort_order": 1
      }
    ],
    "total_count": 7,
    "active_count": 7
  }
}
```

---

### 3. Submit Payment

**Endpoint:**
```
POST /api/v1/guardian/students/{student_id}/fees/payments
```

**Headers:**
```
Authorization: Bearer {access_token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "fee_ids": ["fee-1", "fee-2"],
  "payment_method_id": "pm-1",
  "payment_amount": 135000,
  "payment_months": 1,
  "payment_date": "2026-02-09",
  "receipt_image": "data:image/jpeg;base64,/9j/4AAQSkZJRg...",
  "notes": "Paid via KBZ Bank transfer"
}
```

**Example Request:**
```bash
curl -X POST "http://192.168.100.114:8088/api/v1/guardian/students/STU-001/fees/payments" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "fee_ids": ["fee-1", "fee-2"],
    "payment_method_id": "pm-1",
    "payment_amount": 135000,
    "payment_months": 1,
    "payment_date": "2026-02-09",
    "receipt_image": "data:image/jpeg;base64,/9j/4AAQSkZJRg...",
    "notes": "Test payment"
  }'
```

**Example Response:**
```json
{
  "success": true,
  "message": "Payment submitted successfully",
  "data": {
    "payment_id": "pay-123456",
    "status": "pending_verification",
    "submitted_at": "2026-02-09T10:30:00Z",
    "verification_eta": "24 hours",
    "verification_eta_mm": "၂၄ နာရီ",
    "receipt_url": "http://192.168.100.114:8088/storage/receipts/2026/02/receipt_xxx.jpg",
    "payment_details": {
      "student_id": "STU-001",
      "student_name": "Maung Maung",
      "fee_ids": ["fee-1", "fee-2"],
      "payment_method": "KBZ Bank",
      "payment_amount": 135000,
      "payment_months": 1,
      "payment_date": "2026-02-09"
    }
  }
}
```

---

### 4. Get Payment Options

**Endpoint:**
```
GET /api/v1/guardian/payment-options
```

**Headers:**
```
Authorization: Bearer {access_token}
Content-Type: application/json
```

**Example Request:**
```bash
curl -X GET "http://192.168.100.114:8088/api/v1/guardian/payment-options" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

**Example Response:**
```json
{
  "success": true,
  "message": "Payment options retrieved successfully",
  "data": {
    "options": [
      {
        "months": 1,
        "discount_percent": 0,
        "label": "1 month",
        "label_mm": "၁ လ",
        "badge": null,
        "is_default": true
      },
      {
        "months": 3,
        "discount_percent": 2,
        "label": "3 months",
        "label_mm": "၃ လ",
        "badge": "-2%",
        "is_default": false
      },
      {
        "months": 6,
        "discount_percent": 5,
        "label": "6 months",
        "label_mm": "၆ လ",
        "badge": "-5%",
        "is_default": false
      },
      {
        "months": 12,
        "discount_percent": 10,
        "label": "12 months",
        "label_mm": "၁၂ လ",
        "badge": "-10%",
        "is_default": false
      }
    ],
    "default_months": 1,
    "max_months": 12,
    "currency": "MMK"
  }
}
```

---

### 5. Get Payment History

**Endpoint:**
```
GET /api/v1/guardian/students/{student_id}/fees/payment-history
```

**Headers:**
```
Authorization: Bearer {access_token}
Content-Type: application/json
```

**Query Parameters:**
- `status` (optional): "pending", "verified", "rejected", "all"
- `limit` (optional): 1-50 (default: 10)
- `page` (optional): page number (default: 1)

**Example Request:**
```bash
curl -X GET "http://192.168.100.114:8088/api/v1/guardian/students/STU-001/fees/payment-history?limit=10&page=1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

**Example Response:**
```json
{
  "success": true,
  "message": "Payment history retrieved successfully",
  "data": {
    "data": [
      {
        "id": "pay-123456",
        "payment_date": "2026-01-15",
        "payment_amount": 150000,
        "payment_months": 1,
        "payment_method": "KBZ Bank",
        "status": "verified",
        "status_mm": "အတည်ပြုပြီး",
        "submitted_at": "2026-01-15T10:30:00Z",
        "verified_at": "2026-01-16T09:00:00Z",
        "receipt_url": "http://192.168.100.114:8088/storage/receipts/2026/01/receipt_xxx.jpg",
        "notes": "Paid via KBZ Bank transfer",
        "rejection_reason": null
      }
    ],
    "meta": {
      "current_page": 1,
      "per_page": 10,
      "total": 12,
      "last_page": 2
    }
  }
}
```

---

## 🧪 Testing Guide

### Using Postman

1. **Import Collection**
   - See `PAYMENT_SCREEN_API_SPEC.md` for Postman examples

2. **Set Environment Variables**
   ```
   base_url: http://192.168.100.114:8088/api/v1
   access_token: YOUR_GUARDIAN_TOKEN
   student_id: YOUR_STUDENT_ID
   ```

3. **Test Sequence**
   - Login as Guardian → Get Token
   - Get Fee Structure
   - Get Payment Methods
   - Get Payment Options
   - Submit Payment (with base64 image)
   - Get Payment History

### Using cURL

See example requests above for each endpoint.

### Test Data

After running the seeder, you'll have:
- 3 Bank payment methods (KBZ, AYA, CB)
- 4 Mobile wallet methods (KBZPay, Wave Pay, AYA Pay, CB Pay)

---

## 🔒 Security Features

1. **Authentication**: All endpoints require Bearer token
2. **Authorization**: Guardian can only access their own students' data
3. **File Upload**: Receipt images are validated and stored securely
4. **SQL Injection**: Protected by Laravel's query builder
5. **XSS Protection**: All outputs are escaped

---

## 📊 Database Schema

### payment_methods Table
```sql
- id (uuid, primary)
- name (string)
- name_mm (string, nullable)
- type (enum: bank, mobile_wallet)
- account_number (string)
- account_name (string)
- account_name_mm (string, nullable)
- logo_url (string, nullable)
- is_active (boolean)
- instructions (text, nullable)
- instructions_mm (text, nullable)
- sort_order (integer)
- timestamps
- soft_deletes
```

### payment_proofs Table
```sql
- id (uuid, primary)
- student_id (uuid, foreign)
- payment_method_id (uuid, foreign)
- payment_amount (decimal)
- payment_months (integer)
- payment_date (date)
- receipt_image (string, nullable)
- notes (text, nullable)
- status (enum: pending_verification, verified, rejected)
- verified_by (uuid, foreign, nullable)
- verified_at (timestamp, nullable)
- rejection_reason (text, nullable)
- fee_ids (json, nullable)
- timestamps
- soft_deletes
```

---

## 🐛 Troubleshooting

### Issue: "Storage link not found"
**Solution:**
```bash
php artisan storage:link
```

### Issue: "Permission denied" when uploading
**Solution:**
```bash
chmod -R 775 storage/app/public
```

### Issue: "Payment method not found"
**Solution:**
```bash
php artisan db:seed --class=PaymentMethodSeeder
```

### Issue: "Student not found or unauthorized"
**Solution:**
- Verify the student_id belongs to the authenticated guardian
- Check guardian-student relationship in database

---

## 📝 Next Steps

### For Backend Team:
1. ✅ Run migrations
2. ✅ Run seeders
3. ✅ Test all endpoints
4. ⏳ Add payment verification admin panel
5. ⏳ Add email notifications for payment status

### For Mobile Team:
1. ⏳ Integrate fee structure API
2. ⏳ Integrate payment methods API
3. ⏳ Implement payment submission with image upload
4. ⏳ Add payment history screen
5. ⏳ Test end-to-end flow

---

## 📞 Support

For issues or questions:
- Check `PAYMENT_SCREEN_API_SPEC.md` for detailed API documentation
- Review error responses in API responses
- Check Laravel logs: `storage/logs/laravel.log`

---

**Status**: ✅ Ready for Testing  
**Last Updated**: February 9, 2026
