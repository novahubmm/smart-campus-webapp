# Tuition Fee API Documentation

## Overview
This document describes the API endpoints for managing tuition fees in the Parent Mobile App. The fee reminder appears on the home screen when there are pending payments.

---

## Base URL
```
http://192.168.100.94:8088/api/v1/guardian
```

---

## Endpoints

### 1. Get Pending Fee

**Endpoint:** `GET /fees/pending`

**Description:** Retrieves the current pending tuition fee for a student. This is displayed in the FeeReminder component on the home screen.

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `student_id` | string (UUID) | Yes | The student's unique identifier |

**Request Example:**
```http
GET /fees/pending?student_id=019be949-ea3f-7255-8596-a5e2b845e660
Authorization: Bearer {access_token}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Pending fee retrieved successfully",
  "data": {
    "id": "019be94a-1234-5678-9abc-def012345678",
    "student_id": "019be949-ea3f-7255-8596-a5e2b845e660",
    "amount": 150000,
    "currency": "MMK",
    "term": "Term 2 - Tuition Fee",
    "due_date": "2025-12-31",
    "status": "pending",
    "payment_methods": ["easy_pay", "bank_transfer", "cash"],
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-23T00:00:00.000000Z"
  }
}
```

**No Pending Fee Response (200 OK):**
```json
{
  "success": true,
  "message": "No pending fees",
  "data": null
}
```

**Error Response (401 Unauthorized):**
```json
{
  "success": false,
  "message": "Unauthorized",
  "errors": {
    "auth": ["Invalid or expired token"]
  }
}
```

**Error Response (404 Not Found):**
```json
{
  "success": false,
  "message": "Student not found",
  "errors": {
    "student_id": ["The selected student does not exist"]
  }
}
```

---

### 2. Get Fee Details

**Endpoint:** `GET /fees/{fee_id}`

**Description:** Retrieves detailed information about a specific fee, including payment history and breakdown.

**Path Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `fee_id` | string (UUID) | Yes | The fee's unique identifier |

**Request Example:**
```http
GET /fees/019be94a-1234-5678-9abc-def012345678
Authorization: Bearer {access_token}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Fee details retrieved successfully",
  "data": {
    "id": "019be94a-1234-5678-9abc-def012345678",
    "student_id": "019be949-ea3f-7255-8596-a5e2b845e660",
    "student_name": "Mg Mg",
    "amount": 150000,
    "currency": "MMK",
    "term": "Term 2 - Tuition Fee",
    "academic_year": "2025-2026",
    "due_date": "2025-12-31",
    "status": "pending",
    "payment_methods": ["easy_pay", "bank_transfer", "cash"],
    "breakdown": [
      {
        "item": "Tuition Fee",
        "amount": 120000
      },
      {
        "item": "Library Fee",
        "amount": 15000
      },
      {
        "item": "Lab Fee",
        "amount": 15000
      }
    ],
    "payment_history": [],
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-23T00:00:00.000000Z"
  }
}
```

---

### 3. Get All Fees

**Endpoint:** `GET /fees`

**Description:** Retrieves all fees (pending, paid, overdue) for a student with pagination and filtering.

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `student_id` | string (UUID) | Yes | Filter by student ID |
| `status` | string | Optional | Filter by status: `pending`, `paid`, `overdue`, `partial` |
| `academic_year` | string | Optional | Filter by academic year (e.g., "2025-2026") |
| `page` | integer | Optional | Page number (default: 1) |
| `per_page` | integer | Optional | Items per page (default: 10, max: 50) |

**Request Example:**
```http
GET /fees?student_id=019be949-ea3f-7255-8596-a5e2b845e660&status=pending&page=1&per_page=10
Authorization: Bearer {access_token}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Fees retrieved successfully",
  "data": {
    "data": [
      {
        "id": "019be94a-1234-5678-9abc-def012345678",
        "student_id": "019be949-ea3f-7255-8596-a5e2b845e660",
        "amount": 150000,
        "currency": "MMK",
        "term": "Term 2 - Tuition Fee",
        "due_date": "2025-12-31",
        "status": "pending",
        "created_at": "2025-01-01T00:00:00.000000Z"
      }
    ],
    "meta": {
      "current_page": 1,
      "per_page": 10,
      "total": 1,
      "last_page": 1
    }
  }
}
```

---

### 4. Initiate Payment

**Endpoint:** `POST /fees/{fee_id}/payment`

**Description:** Initiates a payment for a specific fee. Returns payment instructions or redirect URL based on payment method.

**Path Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `fee_id` | string (UUID) | Yes | The fee's unique identifier |

**Request Body:**
```json
{
  "payment_method": "easy_pay",
  "amount": 150000
}
```

**Request Body Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `payment_method` | string | Yes | Payment method: `easy_pay`, `bank_transfer`, `cash` |
| `amount` | number | Yes | Amount to pay (must match fee amount or be partial payment) |

**Request Example:**
```http
POST /fees/019be94a-1234-5678-9abc-def012345678/payment
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "payment_method": "easy_pay",
  "amount": 150000
}
```

**Success Response (200 OK) - Easy Pay:**
```json
{
  "success": true,
  "message": "Payment initiated successfully",
  "data": {
    "payment_id": "019be94a-5678-1234-abcd-ef0123456789",
    "payment_method": "easy_pay",
    "amount": 150000,
    "currency": "MMK",
    "status": "pending",
    "redirect_url": "https://easypay.com/payment/019be94a-5678-1234-abcd-ef0123456789",
    "qr_code": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA...",
    "expires_at": "2025-01-23T12:00:00.000000Z"
  }
}
```

**Success Response (200 OK) - Bank Transfer:**
```json
{
  "success": true,
  "message": "Payment initiated successfully",
  "data": {
    "payment_id": "019be94a-5678-1234-abcd-ef0123456789",
    "payment_method": "bank_transfer",
    "amount": 150000,
    "currency": "MMK",
    "status": "pending",
    "bank_details": {
      "bank_name": "KBZ Bank",
      "account_name": "SmartCampus School",
      "account_number": "1234567890",
      "reference_code": "FEE-2025-001234"
    },
    "instructions": "Please transfer the amount to the bank account above and use the reference code in the transfer description."
  }
}
```

**Error Response (400 Bad Request):**
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "payment_method": ["The selected payment method is invalid"],
    "amount": ["The amount must match the fee amount"]
  }
}
```

---

### 5. Get Payment History

**Endpoint:** `GET /fees/payment-history`

**Description:** Retrieves payment history for a student.

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `student_id` | string (UUID) | Yes | Filter by student ID |
| `status` | string | Optional | Filter by status: `completed`, `pending`, `failed` |
| `page` | integer | Optional | Page number (default: 1) |
| `per_page` | integer | Optional | Items per page (default: 10) |

**Request Example:**
```http
GET /fees/payment-history?student_id=019be949-ea3f-7255-8596-a5e2b845e660&page=1
Authorization: Bearer {access_token}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Payment history retrieved successfully",
  "data": {
    "data": [
      {
        "id": "019be94a-5678-1234-abcd-ef0123456789",
        "fee_id": "019be94a-1234-5678-9abc-def012345678",
        "amount": 150000,
        "currency": "MMK",
        "payment_method": "easy_pay",
        "status": "completed",
        "transaction_id": "TXN-2025-001234",
        "paid_at": "2025-01-23T10:30:00.000000Z",
        "receipt_url": "https://api.example.com/receipts/019be94a-5678-1234-abcd-ef0123456789.pdf"
      }
    ],
    "meta": {
      "current_page": 1,
      "per_page": 10,
      "total": 1,
      "last_page": 1
    }
  }
}
```

---

## Data Models

### Fee Object
```typescript
interface Fee {
  id: string;                    // UUID
  student_id: string;            // UUID
  amount: number;                // Amount in smallest currency unit
  currency: string;              // Currency code (e.g., "MMK")
  term: string;                  // Fee term/description
  academic_year?: string;        // Academic year (e.g., "2025-2026")
  due_date: string;              // ISO 8601 date (YYYY-MM-DD)
  status: 'pending' | 'paid' | 'overdue' | 'partial';
  payment_methods: string[];     // Available payment methods
  created_at: string;            // ISO 8601 datetime
  updated_at: string;            // ISO 8601 datetime
}
```

### Payment Object
```typescript
interface Payment {
  id: string;                    // UUID
  fee_id: string;                // UUID
  amount: number;                // Amount paid
  currency: string;              // Currency code
  payment_method: 'easy_pay' | 'bank_transfer' | 'cash';
  status: 'pending' | 'completed' | 'failed';
  transaction_id?: string;       // External transaction ID
  paid_at?: string;              // ISO 8601 datetime
  receipt_url?: string;          // URL to receipt PDF
}
```

---

## Status Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request - Validation error |
| 401 | Unauthorized - Invalid or expired token |
| 403 | Forbidden - Insufficient permissions |
| 404 | Not Found - Resource doesn't exist |
| 422 | Unprocessable Entity - Validation failed |
| 500 | Internal Server Error |

---

## Payment Methods

### Easy Pay
- Online payment gateway
- Instant confirmation
- QR code support
- Redirect to payment page

### Bank Transfer
- Manual bank transfer
- Requires verification
- Reference code provided
- 1-2 business days processing

### Cash
- Pay at school office
- Receipt issued on-site
- Manual entry by admin

---

## Fee Status Flow

```
pending → paid (full payment)
pending → partial → paid (partial payments)
pending → overdue (past due date)
overdue → paid (late payment)
```

---

## Integration Notes

### Frontend Implementation

1. **Fetch Pending Fee**: Call `/fees/pending` on home screen load
2. **Display Fee Reminder**: Show FeeReminder component if fee exists
3. **Handle Payment**: Navigate to payment screen on "Pay Now" click
4. **Payment Flow**:
   - Select payment method
   - Call `/fees/{fee_id}/payment`
   - Handle redirect or show instructions
   - Confirm payment completion

### Error Handling
- Handle network errors gracefully
- Show user-friendly error messages
- Retry failed requests
- Cache fee data locally

### Security
- Always include Authorization header
- Validate payment amounts
- Verify payment status before confirming
- Use HTTPS for all requests

---

## Example Usage in React Native

```typescript
// Fetch pending fee
const fetchPendingFee = async (studentId: string) => {
  try {
    const response = await api.get('/fees/pending', {
      params: { student_id: studentId }
    });
    
    if (response.success && response.data) {
      setFeeData({
        amount: response.data.amount,
        dueDate: formatDate(response.data.due_date),
        term: response.data.term,
      });
    }
  } catch (error) {
    console.error('Failed to fetch fee:', error);
  }
};

// Initiate payment
const initiatePayment = async (feeId: string, method: string) => {
  try {
    const response = await api.post(`/fees/${feeId}/payment`, {
      payment_method: method,
      amount: feeData.amount,
    });
    
    if (response.success && response.data.redirect_url) {
      // Open payment page
      Linking.openURL(response.data.redirect_url);
    }
  } catch (error) {
    console.error('Payment initiation failed:', error);
  }
};
```

---

## Testing

### Test Cases
1. ✅ Fetch pending fee with valid student ID
2. ✅ Handle no pending fees
3. ✅ Initiate Easy Pay payment
4. ✅ Initiate bank transfer payment
5. ✅ Handle invalid payment method
6. ✅ Handle invalid amount
7. ✅ Fetch payment history
8. ✅ Handle unauthorized access
9. ✅ Handle network errors

### Test Data
```json
{
  "student_id": "019be949-ea3f-7255-8596-a5e2b845e660",
  "fee_id": "019be94a-1234-5678-9abc-def012345678",
  "amount": 150000,
  "payment_method": "easy_pay"
}
```

---

## Changelog

### Version 1.0.0 (2025-01-23)
- Initial API documentation
- Added pending fee endpoint
- Added payment initiation endpoint
- Added payment history endpoint
- Added fee details endpoint
- Added all fees list endpoint

---

## Support

For API issues or questions, contact:
- **Backend Team**: backend@smartcampus.com
- **API Documentation**: https://api.smartcampus.com/docs
