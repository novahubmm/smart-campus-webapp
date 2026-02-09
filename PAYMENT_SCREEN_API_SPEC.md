# Payment Screen API Specification

**Version**: 1.0  
**Date**: February 9, 2026  
**Purpose**: Complete API specification for Payment Screen integration  
**Base URL**: `http://192.168.100.114:8088/api/v1`  

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [API Endpoints](#api-endpoints)
3. [Data Models](#data-models)
4. [Integration Guide](#integration-guide)
5. [Error Handling](#error-handling)
6. [Testing Guide](#testing-guide)

---

## 🎯 Overview

Payment Screen လိုအပ်တဲ့ APIs:

| Priority | API | Purpose |
|----------|-----|---------|
| 🔴 HIGH | Fee Structure | Get student-specific fees |
| 🔴 HIGH | Payment Methods | Get available payment methods |
| 🔴 HIGH | Submit Payment | Submit payment proof |
| 🟡 MEDIUM | Payment Options | Get payment periods & discounts |
| 🟢 LOW | Payment History | Get past payments |

---

## 🔗 API Endpoints

### 1. Get Fee Structure (🔴 HIGH Priority)

**Endpoint:**
```
GET /guardian/students/{student_id}/fees/structure
```

**Description:**  
Get student-specific fee structure including monthly fees and additional fees.

**Headers:**
```
Authorization: Bearer {access_token}
Content-Type: application/json
```

**Path Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| student_id | string | Yes | Student ID (e.g., "STU-2024-001") |

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| academic_year | string | No | Academic year (e.g., "2025-2026") |

**Success Response (200 OK):**

```json
{
  "success": true,
  "message": "Fee structure retrieved successfully",
  "data": {
    "student_id": "STU-2024-001",
    "student_name": "Maung Maung",
    "grade": "Kindergarten",
    "section": "A",
    "academic_year": "2025-2026",
    "monthly_fees": [
      {
        "id": "fee-monthly-1",
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
        "id": "fee-add-1",
        "name": "Lab Fee",
        "name_mm": "ဓာတ်ခွဲခန်းကြေး",
        "amount": 15000,
        "removable": true,
        "description": "Laboratory usage fee",
        "description_mm": "ဓာတ်ခွဲခန်း အသုံးပြုခ"
      },
      {
        "id": "fee-add-2",
        "name": "Library Fee",
        "name_mm": "စာကြည့်တိုက်ကြေး",
        "amount": 10000,
        "removable": true,
        "description": "Library access fee",
        "description_mm": "စာကြည့်တိုက် အသုံးပြုခ"
      },
      {
        "id": "fee-add-3",
        "name": "Sports Fee",
        "name_mm": "အားကစားကြေး",
        "amount": 5000,
        "removable": true,
        "description": "Sports activities fee",
        "description_mm": "အားကစား လှုပ်ရှားမှုကြေး"
      }
    ],
    "total_monthly": 150000,
    "currency": "MMK",
    "currency_symbol": "MMK"
  }
}
```

**Error Responses:**

**404 Not Found:**
```json
{
  "success": false,
  "message": "Student not found",
  "error_code": "STUDENT_NOT_FOUND"
}
```

**403 Forbidden:**
```json
{
  "success": false,
  "message": "You don't have permission to access this student's data",
  "error_code": "PERMISSION_DENIED"
}
```

---

### 2. Get Payment Methods (🔴 HIGH Priority)

**Endpoint:**
```
GET /guardian/payment-methods
```

**Description:**  
Get available payment methods (bank accounts, mobile wallets) for fee payment.

**Headers:**
```
Authorization: Bearer {access_token}
Content-Type: application/json
```

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| type | string | No | Filter by type: "bank", "mobile_wallet", "all" (default: "all") |
| active_only | boolean | No | Show only active methods (default: true) |

**Success Response (200 OK):**

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
        "logo_url": "https://example.com/logos/kbz.png",
        "is_active": true,
        "instructions": "Transfer to this account and upload receipt",
        "instructions_mm": "ဒီ account ကို လွှဲပြီး ပြေစာ upload လုပ်ပါ",
        "sort_order": 1
      },
      {
        "id": "pm-2",
        "name": "AYA Bank",
        "name_mm": "AYA ဘဏ်",
        "type": "bank",
        "account_number": "98765432109876543",
        "account_name": "SmartCampus School",
        "account_name_mm": "SmartCampus ကျောင်း",
        "logo_url": "https://example.com/logos/aya.png",
        "is_active": true,
        "instructions": "Transfer to this account and upload receipt",
        "instructions_mm": "ဒီ account ကို လွှဲပြီး ပြေစာ upload လုပ်ပါ",
        "sort_order": 2
      },
      {
        "id": "pm-3",
        "name": "KBZPay",
        "name_mm": "KBZPay",
        "type": "mobile_wallet",
        "account_number": "09-123-456-789",
        "account_name": "SmartCampus School",
        "account_name_mm": "SmartCampus ကျောင်း",
        "logo_url": "https://example.com/logos/kbzpay.png",
        "is_active": true,
        "instructions": "Send money to this number and upload screenshot",
        "instructions_mm": "ဒီနံပါတ်ကို ပိုက်ဆံပို့ပြီး screenshot upload လုပ်ပါ",
        "sort_order": 3
      },
      {
        "id": "pm-4",
        "name": "Wave Pay",
        "name_mm": "Wave Pay",
        "type": "mobile_wallet",
        "account_number": "09-987-654-321",
        "account_name": "SmartCampus School",
        "account_name_mm": "SmartCampus ကျောင်း",
        "logo_url": "https://example.com/logos/wavepay.png",
        "is_active": true,
        "instructions": "Send money to this number and upload screenshot",
        "instructions_mm": "ဒီနံပါတ်ကို ပိုက်ဆံပို့ပြီး screenshot upload လုပ်ပါ",
        "sort_order": 4
      }
    ],
    "total_count": 4,
    "active_count": 4
  }
}
```

**Error Responses:**

**401 Unauthorized:**
```json
{
  "success": false,
  "message": "Authentication required",
  "error_code": "UNAUTHORIZED"
}
```

---

### 3. Submit Payment (🔴 HIGH Priority)

**Endpoint:**
```
POST /guardian/students/{student_id}/fees/payments
```

**Description:**  
Submit payment proof (receipt image) for fee payment.

**Headers:**
```
Authorization: Bearer {access_token}
Content-Type: application/json
```

**Path Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| student_id | string | Yes | Student ID |

**Request Body:**

```json
{
  "fee_ids": ["fee-monthly-1", "fee-add-1", "fee-add-2"],
  "payment_method_id": "pm-1",
  "payment_amount": 145000,
  "payment_months": 1,
  "payment_date": "2026-02-09",
  "receipt_image": "data:image/jpeg;base64,/9j/4AAQSkZJRg...",
  "notes": "Paid via KBZ Bank transfer on 09-Feb-2026"
}
```

**Request Body Fields:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| fee_ids | array | Yes | Array of fee IDs being paid |
| payment_method_id | string | Yes | Payment method ID |
| payment_amount | number | Yes | Total payment amount in MMK |
| payment_months | number | Yes | Number of months (1-12) |
| payment_date | string | Yes | Payment date (YYYY-MM-DD) |
| receipt_image | string | Yes | Base64 encoded image or image URL |
| notes | string | No | Additional notes (max 500 chars) |

**Success Response (201 Created):**

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
    "receipt_url": "https://example.com/receipts/pay-123456.jpg",
    "payment_details": {
      "student_id": "STU-2024-001",
      "student_name": "Maung Maung",
      "fee_ids": ["fee-monthly-1", "fee-add-1", "fee-add-2"],
      "payment_method": "KBZ Bank",
      "payment_amount": 145000,
      "payment_months": 1,
      "payment_date": "2026-02-09"
    }
  }
}
```

**Error Responses:**

**400 Bad Request:**
```json
{
  "success": false,
  "message": "Invalid payment data",
  "error_code": "INVALID_PAYMENT_DATA",
  "errors": {
    "payment_amount": "Payment amount must be greater than 0",
    "receipt_image": "Receipt image is required"
  }
}
```

**422 Unprocessable Entity:**
```json
{
  "success": false,
  "message": "Payment amount does not match selected fees",
  "error_code": "AMOUNT_MISMATCH",
  "details": {
    "expected_amount": 145000,
    "provided_amount": 150000
  }
}
```

---

### 4. Get Payment Options (🟡 MEDIUM Priority)

**Endpoint:**
```
GET /guardian/payment-options
```

**Description:**  
Get available payment period options with discount rates.

**Headers:**
```
Authorization: Bearer {access_token}
Content-Type: application/json
```

**Success Response (200 OK):**

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
        "months": 2,
        "discount_percent": 0,
        "label": "2 months",
        "label_mm": "၂ လ",
        "badge": null,
        "is_default": false
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

### 5. Get Payment History (🟢 LOW Priority)

**Endpoint:**
```
GET /guardian/students/{student_id}/fees/payment-history
```

**Description:**  
Get payment history for a student.

**Headers:**
```
Authorization: Bearer {access_token}
Content-Type: application/json
```

**Path Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| student_id | string | Yes | Student ID |

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| status | string | No | Filter by status: "pending", "verified", "rejected", "all" |
| limit | number | No | Number of records (default: 10, max: 50) |
| page | number | No | Page number (default: 1) |

**Success Response (200 OK):**

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
        "receipt_url": "https://example.com/receipts/pay-123456.jpg",
        "notes": "Paid via KBZ Bank transfer"
      },
      {
        "id": "pay-123455",
        "payment_date": "2025-12-15",
        "payment_amount": 150000,
        "payment_months": 1,
        "payment_method": "Wave Pay",
        "status": "verified",
        "status_mm": "အတည်ပြုပြီး",
        "submitted_at": "2025-12-15T14:20:00Z",
        "verified_at": "2025-12-15T16:00:00Z",
        "receipt_url": "https://example.com/receipts/pay-123455.jpg",
        "notes": "Paid via Wave Pay"
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

## 📦 Data Models

### FeeItem

```typescript
interface FeeItem {
  id: string;
  name: string;
  name_mm: string;
  amount: number;
  removable: boolean;
  description: string;
  description_mm: string;
}
```

### PaymentMethod

```typescript
interface PaymentMethod {
  id: string;
  name: string;
  name_mm: string;
  type: 'bank' | 'mobile_wallet';
  account_number: string;
  account_name: string;
  account_name_mm: string;
  logo_url: string;
  is_active: boolean;
  instructions: string;
  instructions_mm: string;
  sort_order: number;
}
```

### PaymentOption

```typescript
interface PaymentOption {
  months: number;
  discount_percent: number;
  label: string;
  label_mm: string;
  badge: string | null;
  is_default: boolean;
}
```

### PaymentSubmission

```typescript
interface PaymentSubmission {
  fee_ids: string[];
  payment_method_id: string;
  payment_amount: number;
  payment_months: number;
  payment_date: string;
  receipt_image: string;
  notes?: string;
}
```

### PaymentResponse

```typescript
interface PaymentResponse {
  payment_id: string;
  status: 'pending_verification' | 'verified' | 'rejected';
  submitted_at: string;
  verification_eta: string;
  verification_eta_mm: string;
  receipt_url: string;
  payment_details: {
    student_id: string;
    student_name: string;
    fee_ids: string[];
    payment_method: string;
    payment_amount: number;
    payment_months: number;
    payment_date: string;
  };
}
```

---

## 🔧 Integration Guide

### Step 1: Update feesAPI Service

**File**: `src/parent/services/feesAPI.ts`

```typescript
/**
 * Get fee structure for a student
 */
getFeeStructure: async (studentId: string): Promise<FeeStructureResponse> => {
  try {
    const response = await api.get<FeeStructureResponse>(
      API_ENDPOINTS.FEES.STRUCTURE(studentId)
    );
    return response;
  } catch (error: any) {
    console.error('Get fee structure error:', error);
    throw error;
  }
},

/**
 * Get available payment methods
 */
getPaymentMethods: async (): Promise<PaymentMethodsResponse> => {
  try {
    const response = await api.get<PaymentMethodsResponse>(
      API_ENDPOINTS.FEES.PAYMENT_METHODS
    );
    return response;
  } catch (error: any) {
    console.error('Get payment methods error:', error);
    throw error;
  }
},

/**
 * Submit payment proof
 */
submitPayment: async (
  studentId: string,
  paymentData: PaymentSubmission
): Promise<PaymentResponse> => {
  try {
    const response = await api.post<PaymentResponse>(
      API_ENDPOINTS.FEES.SUBMIT_PAYMENT(studentId),
      paymentData
    );
    return response;
  } catch (error: any) {
    console.error('Submit payment error:', error);
    throw error;
  }
},

/**
 * Get payment options
 */
getPaymentOptions: async (): Promise<PaymentOptionsResponse> => {
  try {
    const response = await api.get<PaymentOptionsResponse>(
      API_ENDPOINTS.FEES.PAYMENT_OPTIONS
    );
    return response;
  } catch (error: any) {
    console.error('Get payment options error:', error);
    throw error;
  }
},
```

### Step 2: Update API Endpoints Config

**File**: `src/parent/config/api.ts`

```typescript
FEES: {
  // ... existing endpoints
  STRUCTURE: (studentId: string) => `/guardian/students/${studentId}/fees/structure`,
  PAYMENT_METHODS: '/guardian/payment-methods',
  SUBMIT_PAYMENT: (studentId: string) => `/guardian/students/${studentId}/fees/payments`,
  PAYMENT_OPTIONS: '/guardian/payment-options',
},
```

### Step 3: Update PaymentInstructionsScreen

**File**: `src/parent/screens/PaymentInstructionsScreen.tsx`

```typescript
// Add state for API data
const [feeStructure, setFeeStructure] = useState<FeeStructureData | null>(null);
const [paymentMethods, setPaymentMethods] = useState<PaymentMethod[]>([]);
const [paymentOptions, setPaymentOptions] = useState<PaymentOption[]>([]);
const [isLoading, setIsLoading] = useState(true);

// Fetch data on mount
useEffect(() => {
  const fetchData = async () => {
    try {
      setIsLoading(true);
      
      // Fetch fee structure
      const feeResponse = await feesAPI.getFeeStructure(studentId);
      setFeeStructure(feeResponse.data);
      
      // Fetch payment methods
      const methodsResponse = await feesAPI.getPaymentMethods();
      setPaymentMethods(methodsResponse.data.methods);
      
      // Fetch payment options
      const optionsResponse = await feesAPI.getPaymentOptions();
      setPaymentOptions(optionsResponse.data.options);
      
      setIsLoading(false);
    } catch (error) {
      console.error('Failed to fetch payment data:', error);
      setIsLoading(false);
      Alert.alert('Error', 'Failed to load payment information');
    }
  };
  
  fetchData();
}, [studentId]);

// Handle payment submission
const handleSubmitPayment = async (receiptImage: string) => {
  try {
    const paymentData = {
      fee_ids: selectedFeeIds,
      payment_method_id: selectedMethodId,
      payment_amount: totalAmount,
      payment_months: selectedMonths,
      payment_date: new Date().toISOString().split('T')[0],
      receipt_image: receiptImage,
      notes: paymentNotes,
    };
    
    const response = await feesAPI.submitPayment(studentId, paymentData);
    
    Alert.alert(
      'Success',
      'Payment submitted successfully. Verification will be completed within 24 hours.',
      [{ text: 'OK', onPress: () => onBackPress?.() }]
    );
  } catch (error) {
    console.error('Failed to submit payment:', error);
    Alert.alert('Error', 'Failed to submit payment. Please try again.');
  }
};
```

---

## ⚠️ Error Handling

### Common Error Codes

| Code | HTTP Status | Description | Action |
|------|-------------|-------------|--------|
| UNAUTHORIZED | 401 | Invalid or expired token | Redirect to login |
| PERMISSION_DENIED | 403 | No access to student data | Show error message |
| STUDENT_NOT_FOUND | 404 | Student doesn't exist | Show error message |
| INVALID_PAYMENT_DATA | 400 | Invalid request data | Show validation errors |
| AMOUNT_MISMATCH | 422 | Payment amount incorrect | Recalculate and retry |
| SERVER_ERROR | 500 | Internal server error | Show retry option |

### Error Handling Example

```typescript
try {
  const response = await feesAPI.getFeeStructure(studentId);
  setFeeStructure(response.data);
} catch (error: any) {
  if (error.response) {
    switch (error.response.status) {
      case 401:
        // Redirect to login
        navigation.navigate('Login');
        break;
      case 403:
        Alert.alert('Error', 'You don\'t have permission to access this data');
        break;
      case 404:
        Alert.alert('Error', 'Student not found');
        break;
      default:
        Alert.alert('Error', 'Failed to load fee structure');
    }
  } else {
    Alert.alert('Error', 'Network error. Please check your connection.');
  }
}
```

---

## 🧪 Testing Guide

### Postman Collection

**1. Get Fee Structure**
```
GET {{base_url}}/guardian/students/{{student_id}}/fees/structure
Authorization: Bearer {{access_token}}
```

**2. Get Payment Methods**
```
GET {{base_url}}/guardian/payment-methods
Authorization: Bearer {{access_token}}
```

**3. Submit Payment**
```
POST {{base_url}}/guardian/students/{{student_id}}/fees/payments
Authorization: Bearer {{access_token}}
Content-Type: application/json

{
  "fee_ids": ["fee-monthly-1", "fee-add-1"],
  "payment_method_id": "pm-1",
  "payment_amount": 135000,
  "payment_months": 1,
  "payment_date": "2026-02-09",
  "receipt_image": "data:image/jpeg;base64,...",
  "notes": "Test payment"
}
```

**4. Get Payment Options**
```
GET {{base_url}}/guardian/payment-options
Authorization: Bearer {{access_token}}
```

**5. Get Payment History**
```
GET {{base_url}}/guardian/students/{{student_id}}/fees/payment-history?limit=10
Authorization: Bearer {{access_token}}
```

### Test Cases

**Test Case 1: Load Fee Structure**
- ✅ Should load monthly fees
- ✅ Should load additional fees
- ✅ Should calculate total correctly
- ✅ Should handle empty fees

**Test Case 2: Load Payment Methods**
- ✅ Should load all active methods
- ✅ Should filter by type
- ✅ Should sort by sort_order

**Test Case 3: Submit Payment**
- ✅ Should validate required fields
- ✅ Should validate payment amount
- ✅ Should upload receipt image
- ✅ Should return payment ID

**Test Case 4: Error Handling**
- ✅ Should handle network errors
- ✅ Should handle 401 unauthorized
- ✅ Should handle 404 not found
- ✅ Should show user-friendly messages

---

## 📝 Summary

### Required APIs (Backend Team)

**🔴 HIGH Priority:**
1. ✅ `GET /guardian/students/{id}/fees/structure` - Fee structure
2. ✅ `GET /guardian/payment-methods` - Payment methods
3. ✅ `POST /guardian/students/{id}/fees/payments` - Submit payment

**🟡 MEDIUM Priority:**
4. ✅ `GET /guardian/payment-options` - Payment options

**🟢 LOW Priority:**
5. ✅ `GET /guardian/students/{id}/fees/payment-history` - Payment history

### Frontend Integration Tasks

1. ✅ Update `feesAPI.ts` service
2. ✅ Update `api.ts` endpoints config
3. ✅ Update `PaymentInstructionsScreen.tsx`
4. ✅ Add loading states
5. ✅ Add error handling
6. ✅ Test with real backend

### Timeline

- **Phase 1** (Week 1): Backend implements HIGH priority APIs
- **Phase 2** (Week 1): Frontend integrates HIGH priority APIs
- **Phase 3** (Week 2): Backend implements MEDIUM priority APIs
- **Phase 4** (Week 2): Frontend integrates MEDIUM priority APIs
- **Phase 5** (Week 3): Testing & bug fixes

---

**Status**: 📋 Ready for Backend Implementation
