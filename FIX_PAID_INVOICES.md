# Fix Paid Invoices Issue

## Problem
Invoices `INV-20260218-0024` and `INV-20260218-0026` are showing status as "paid" when they should be "rejected" or "pending".

## Root Cause
The invoices have:
- `paid_amount` = `total_amount` (should be 0)
- `remaining_amount` = 0 (should equal total_amount)
- `status` = "paid" (calculated based on the above)

Each invoice fee also has:
- `paid_amount` = `amount` (should be 0)
- `remaining_amount` = 0 (should equal amount)
- `status` = "paid"

## Status Calculation Logic
According to `InvoiceService::calculateInvoiceStatus()`:
- If `remaining_amount == 0` → status is "paid"
- If `paid_amount > 0 && remaining_amount > 0` → status is "partial"
- If `paid_amount == 0 && due_date is past` → status is "overdue"
- If `paid_amount == 0 && due_date is future` → status is "pending"

## Solution Options

### Option 1: Update Database Directly (Quick Fix)
Run SQL to reset these invoices:

```sql
-- Reset Invoice INV-20260218-0024
UPDATE invoices 
SET paid_amount = 0, 
    remaining_amount = total_amount,
    status = 'pending'
WHERE invoice_number = 'INV-20260218-0024';

-- Reset its fees
UPDATE invoice_fees 
SET paid_amount = 0,
    remaining_amount = amount,
    status = 'unpaid'
WHERE invoice_id = (SELECT id FROM invoices WHERE invoice_number = 'INV-20260218-0024');

-- Reset Invoice INV-20260218-0026
UPDATE invoices 
SET paid_amount = 0,
    remaining_amount = total_amount, 
    status = 'pending'
WHERE invoice_number = 'INV-20260218-0026';

-- Reset its fees
UPDATE invoice_fees
SET paid_amount = 0,
    remaining_amount = amount,
    status = 'unpaid'
WHERE invoice_id = (SELECT id FROM invoices WHERE invoice_number = 'INV-20260218-0026');
```

### Option 2: Create Artisan Command (Recommended)
Create a command to reset incorrectly paid invoices:

```bash
php artisan make:command ResetIncorrectlyPaidInvoices
```

### Option 3: Check Invoice Generation
If these invoices are being created by a seeder or test data, update the seeder to create them with correct initial values.

## Next Steps
1. Identify how these invoices were created (check seeders, factories, or manual creation)
2. Apply the database fix to reset them
3. Verify the status calculation works correctly after reset
4. Ensure future invoices are created with correct initial values
