# Payment Screen APIs - Quick Reference Card

**Base URL**: `http://192.168.100.114:8088/api/v1`

---

## 🔐 Authentication

All endpoints require Bearer token:
```
Authorization: Bearer {access_token}
```

---

## 📡 Endpoints

### 1️⃣ Get Fee Structure
```http
GET /guardian/students/{student_id}/fees/structure
```
**Returns**: Monthly fees + Additional fees + Total

---

### 2️⃣ Get Payment Methods
```http
GET /guardian/payment-methods?type=all&active_only=true
```
**Returns**: Banks + Mobile wallets with account details

---

### 3️⃣ Submit Payment
```http
POST /guardian/students/{student_id}/fees/payments
Content-Type: application/json

{
  "fee_ids": ["fee-1", "fee-2"],
  "payment_method_id": "pm-1",
  "payment_amount": 135000,
  "payment_months": 1,
  "payment_date": "2026-02-09",
  "receipt_image": "data:image/jpeg;base64,...",
  "notes": "Payment note"
}
```
**Returns**: Payment ID + Status + Receipt URL

---

### 4️⃣ Get Payment Options
```http
GET /guardian/payment-options
```
**Returns**: Payment periods (1, 2, 3, 6, 12 months) with discounts

---

### 5️⃣ Get Payment History
```http
GET /guardian/students/{student_id}/fees/payment-history?status=all&limit=10&page=1
```
**Returns**: Paginated payment history with status

---

## 🚀 Quick Setup

```bash
# 1. Run migrations
php artisan migrate

# 2. Seed payment methods
php artisan db:seed --class=PaymentMethodSeeder

# 3. Create storage link
php artisan storage:link

# 4. Test APIs
./test-payment-apis.sh
```

---

## 📦 Response Format

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "error_code": "ERROR_CODE"
}
```

---

## 🎯 Payment Status

| Status | Myanmar | Description |
|--------|---------|-------------|
| `pending_verification` | စစ်ဆေးဆဲ | Waiting for admin verification |
| `verified` | အတည်ပြုပြီး | Payment approved |
| `rejected` | ငြင်းပယ်ခံရသည် | Payment rejected |

---

## 💳 Payment Methods

### Banks (3)
- KBZ Bank
- AYA Bank  
- CB Bank

### Mobile Wallets (4)
- KBZPay
- Wave Pay
- AYA Pay
- CB Pay

---

## 💰 Payment Discounts

| Months | Discount |
|--------|----------|
| 1 | 0% |
| 2 | 0% |
| 3 | 2% |
| 6 | 5% |
| 12 | 10% |

---

## 🔍 Testing

### Postman
Import: `Payment_Screen_API.postman_collection.json`

### cURL
```bash
# Get fee structure
curl -X GET "${BASE_URL}/guardian/students/${STUDENT_ID}/fees/structure" \
  -H "Authorization: Bearer ${TOKEN}"

# Get payment methods
curl -X GET "${BASE_URL}/guardian/payment-methods" \
  -H "Authorization: Bearer ${TOKEN}"

# Get payment options
curl -X GET "${BASE_URL}/guardian/payment-options" \
  -H "Authorization: Bearer ${TOKEN}"

# Get payment history
curl -X GET "${BASE_URL}/guardian/students/${STUDENT_ID}/fees/payment-history" \
  -H "Authorization: Bearer ${TOKEN}"
```

---

## 📝 Files

| File | Purpose |
|------|---------|
| `PAYMENT_SCREEN_API_SPEC.md` | Complete API specification |
| `PAYMENT_SCREEN_IMPLEMENTATION.md` | Implementation guide |
| `PAYMENT_APIS_SUMMARY.md` | Implementation summary |
| `PAYMENT_APIS_QUICK_REFERENCE.md` | This file |
| `Payment_Screen_API.postman_collection.json` | Postman collection |
| `test-payment-apis.sh` | Test script |

---

## ⚠️ Common Errors

| Error | Solution |
|-------|----------|
| 401 Unauthorized | Check Bearer token |
| 404 Student not found | Verify student_id |
| 403 Permission denied | Check guardian-student relationship |
| 500 Server error | Check Laravel logs |

---

## 📞 Support

- **API Docs**: `PAYMENT_SCREEN_API_SPEC.md`
- **Setup Guide**: `PAYMENT_SCREEN_IMPLEMENTATION.md`
- **Laravel Logs**: `storage/logs/laravel.log`

---

**Status**: ✅ Ready for Testing  
**Version**: 1.0  
**Date**: February 9, 2026
