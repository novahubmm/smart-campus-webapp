# Overdue Fee Seeder Guide

## Overview
This guide explains how to use the `OverdueFeeSeeder` to create test data for overdue and pending fees in the Guardian App API.

## What It Creates

The seeder creates fee records for **Student 1** (Htun Zin) for the year 2026:

### Overdue Fees (1 invoice)
- **Invoice**: INV-2026-01-3A48862E
- **Amount**: MMK 150,000
- **Due Date**: 2026-01-15 (past due)
- **Status**: overdue
- **Items**:
  - Tuition Fee: MMK 100,000
  - Examination Fee: MMK 50,000

### Pending Fees (3 invoices)
1. **Invoice**: INV-2026-02-3A48862E
   - **Amount**: MMK 150,000
   - **Due Date**: 2026-02-28
   - **Status**: sent (pending payment)

2. **Invoice**: INV-2026-03-3A48862E
   - **Amount**: MMK 150,000
   - **Due Date**: 2026-03-31
   - **Status**: sent (pending payment)

3. **Invoice**: INV-2026-04-3A48862E
   - **Amount**: MMK 150,000
   - **Due Date**: 2026-04-30
   - **Status**: sent (pending payment)

### Totals
- **Total Overdue**: MMK 150,000
- **Total Pending**: MMK 450,000
- **Earliest Due Date**: 2026-01-15

## Running the Seeder

```bash
php artisan db:seed --class=OverdueFeeSeeder
```

The seeder will:
1. Find Student 1 (student1@smartcampusedu.com)
2. Delete any existing 2026 invoices for this student
3. Create 1 overdue invoice and 3 pending invoices
4. Display a summary of created records

## Testing the API

### 1. Get Payment Summary

**Endpoint**: `GET /api/v1/guardian/students/{student_id}/fees/summary?year=2026`

**Example Request**:
```bash
curl -X GET "http://localhost:8000/api/v1/guardian/students/3a48862e-ed0e-4991-b2c7-5c4953ed7227/fees/summary?year=2026" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

**Example Response**:
```json
{
  "success": true,
  "message": "Payment summary retrieved successfully",
  "data": {
    "total_pending": 450000,
    "total_overdue": 150000,
    "earliest_due_date": "2026-01-15",
    "pending_count": 3,
    "overdue_count": 1
  }
}
```

### 2. Get All Fees (Overdue Only)

**Endpoint**: `GET /api/v1/guardian/students/{student_id}/fees?status=overdue`

```bash
curl -X GET "http://localhost:8000/api/v1/guardian/students/3a48862e-ed0e-4991-b2c7-5c4953ed7227/fees?status=overdue" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

### 3. Get Pending Fee

**Endpoint**: `GET /api/v1/guardian/students/{student_id}/fees/pending`

```bash
curl -X GET "http://localhost:8000/api/v1/guardian/students/3a48862e-ed0e-4991-b2c7-5c4953ed7227/fees/pending" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

## Guardian Access

The test data is accessible by:
- **Guardian**: Phyu Moe (guardian1@smartcampusedu.com)
- **Password**: password
- **Student**: Htun Zin (student1@smartcampusedu.com)
- **Student ID**: 3a48862e-ed0e-4991-b2c7-5c4953ed7227

## Invoice Status Values

The system uses the following invoice statuses:
- `draft`: Invoice created but not sent
- `sent`: Invoice sent to guardian (pending payment)
- `partial`: Partially paid
- `paid`: Fully paid
- `overdue`: Past due date and unpaid
- `cancelled`: Invoice cancelled

## Notes

1. The seeder automatically clears existing 2026 invoices before creating new ones
2. "Pending" invoices use the `sent` status (not `pending`)
3. The payment summary groups `sent`, `partial`, and `draft` as "pending"
4. The earliest due date is calculated from all unpaid invoices (pending + overdue)
5. All amounts are in MMK (Myanmar Kyat)

## Customization

To modify the seeder for different students or amounts, edit:
```
smart-campus-webapp/database/seeders/OverdueFeeSeeder.php
```

Key variables to change:
- Student email: Line 23
- Invoice amounts: Lines 57, 94, 131, 168
- Due dates: Lines 60, 97, 134, 171
- Fee types and descriptions: Throughout the file
