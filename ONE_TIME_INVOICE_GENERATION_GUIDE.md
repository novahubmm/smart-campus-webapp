# One-Time Invoice Generation Guide

This guide explains how to generate one-time invoices for all students in your Smart Campus system.

## Overview

One-time invoices are used for fees that occur once per academic year (e.g., Exam Fee, Registration Fee, Book Fee, Uniform Fee, etc.). The system automatically generates separate invoices for each active student in the target grade when a one-time fee is created.

## Prerequisites

- Active students in the system
- Fee structures defined with `frequency = 'one_time'`

## Methods to Generate One-Time Invoices

### Method 1: Using the New Interactive Command (Recommended)

Create a one-time fee and generate invoices in one step:

```bash
php artisan payment:create-one-time-fee \
  "Exam Fee" \
  50000 \
  "0" \
  "One Grade Demo 2026-2027" \
  --name-mm="စာမေးပွဲ ကြေးငွေ" \
  --description="Annual examination fee" \
  --target-month=3 \
  --due-days=30 \
  --fee-type=other \
  --generate-invoices
```

**Parameters:**
- `name`: Fee name (required)
- `amount`: Fee amount in MMK (required)
- `grade`: Target grade level (required)
- `batch`: Target batch (required)
- `--name-mm`: Myanmar name (optional)
- `--description`: Description (optional)
- `--description-mm`: Myanmar description (optional)
- `--target-month`: Target month 1-12 (default: 1)
- `--due-days`: Days until due date (default: 30)
- `--fee-type`: Fee type (default: other)
  - Options: tuition, transportation, library, lab, sports, course_materials, other
- `--generate-invoices`: Auto-generate invoices without confirmation

### Method 2: Using Existing Command

If you already have one-time fees created, generate invoices for all of them:

```bash
# Generate invoices for all active one-time fees
php artisan payment:generate-one-time-invoices

# Generate invoices for a specific fee by ID
php artisan payment:generate-one-time-invoices {fee_id}
```

### Method 3: Using the Example Script

Run the example script to create a sample one-time fee:

```bash
php create-one-time-fee-example.php
```

Edit the script to customize the fee details before running.

### Method 4: Using the Shell Script

Quick way to generate invoices for all existing one-time fees:

```bash
./generate-one-time-invoices.sh
```

## Common One-Time Fee Examples

### 1. Exam Fee
```bash
php artisan payment:create-one-time-fee \
  "Exam Fee" \
  50000 \
  "0" \
  "One Grade Demo 2026-2027" \
  --name-mm="စာမေးပွဲ ကြေးငွေ" \
  --target-month=3 \
  --fee-type=other \
  --generate-invoices
```

### 2. Registration Fee
```bash
php artisan payment:create-one-time-fee \
  "Registration Fee" \
  100000 \
  "0" \
  "One Grade Demo 2026-2027" \
  --name-mm="စာရင်းသွင်း ကြေးငွေ" \
  --target-month=1 \
  --fee-type=other \
  --generate-invoices
```

### 3. Uniform Fee
```bash
php artisan payment:create-one-time-fee \
  "Uniform Fee" \
  75000 \
  "0" \
  "One Grade Demo 2026-2027" \
  --name-mm="ဝတ်စုံ ကြေးငွေ" \
  --target-month=1 \
  --fee-type=other \
  --generate-invoices
```

### 4. Book Fee
```bash
php artisan payment:create-one-time-fee \
  "Book Fee" \
  60000 \
  "0" \
  "One Grade Demo 2026-2027" \
  --name-mm="စာအုပ် ကြေးငွေ" \
  --target-month=1 \
  --fee-type=course_materials \
  --generate-invoices
```

## How It Works

1. **Fee Creation**: A one-time fee structure is created with:
   - `frequency = 'one_time'`
   - `target_month` (1-12) indicating when the fee applies
   - `supports_payment_period = false` (one-time fees don't support multi-month payments)

2. **Job Dispatch**: When a one-time fee is created (or manually triggered), the `GenerateOneTimeFeeInvoicesJob` is dispatched

3. **Invoice Generation**: The job:
   - Queries all active students in the target grade
   - Creates one invoice per student
   - Each invoice contains only the one-time fee
   - Sets invoice type to `'one_time'`
   - Generates unique invoice numbers (format: INV-YYYYMMDD-XXXX)

4. **Invoice Details**:
   - `invoice_type`: 'one_time'
   - `total_amount`: Fee amount
   - `paid_amount`: 0 (initially)
   - `remaining_amount`: Fee amount
   - `status`: 'pending'
   - `due_date`: From fee structure

## Checking Results

### View Generated Invoices in Database

```bash
php artisan tinker --execute="
\App\Models\PaymentSystem\Invoice::where('invoice_type', 'one_time')
  ->with('student.user', 'fees')
  ->get()
  ->each(function(\$inv) {
    echo sprintf('Invoice: %s | Student: %s | Amount: %s MMK | Status: %s',
      \$inv->invoice_number,
      \$inv->student->user->name,
      number_format(\$inv->total_amount),
      \$inv->status
    ) . PHP_EOL;
  });
"
```

### Count Invoices by Status

```bash
php artisan tinker --execute="
\$counts = \App\Models\PaymentSystem\Invoice::where('invoice_type', 'one_time')
  ->selectRaw('status, count(*) as count')
  ->groupBy('status')
  ->get();
echo 'One-Time Invoice Status:' . PHP_EOL;
\$counts->each(function(\$c) {
  echo sprintf('%s: %d', ucfirst(\$c->status), \$c->count) . PHP_EOL;
});
"
```

## Troubleshooting

### No Invoices Generated

**Problem**: Command runs but no invoices are created.

**Solutions**:
1. Check if students exist in the target grade:
   ```bash
   php artisan tinker --execute="
   echo 'Active students by grade:' . PHP_EOL;
   \App\Models\StudentProfile::where('status', 'active')
     ->with('grade')
     ->get()
     ->groupBy('grade.level')
     ->each(function(\$students, \$grade) {
       echo sprintf('Grade %s: %d students', \$grade, \$students->count()) . PHP_EOL;
     });
   "
   ```

2. Verify the fee structure exists and is active:
   ```bash
   php artisan tinker --execute="
   \App\Models\PaymentSystem\FeeStructure::where('frequency', 'one_time')
     ->get(['id', 'name', 'is_active', 'grade', 'batch'])
     ->each(function(\$f) {
       echo sprintf('ID: %s | Name: %s | Active: %s | Grade: %s',
         \$f->id, \$f->name, \$f->is_active ? 'Yes' : 'No', \$f->grade
       ) . PHP_EOL;
     });
   "
   ```

3. Check the job queue is running:
   ```bash
   php artisan queue:work --once
   ```

### Duplicate Invoices

**Problem**: Multiple invoices created for the same student and fee.

**Solution**: The job doesn't check for duplicates. Only run the generation command once per fee. If duplicates exist, delete them manually:

```bash
php artisan tinker --execute="
// Find duplicate invoices (same student, same fee, same invoice_type)
\$duplicates = \App\Models\PaymentSystem\Invoice::where('invoice_type', 'one_time')
  ->where('paid_amount', 0)
  ->get()
  ->groupBy('student_id')
  ->filter(function(\$invoices) { return \$invoices->count() > 1; });
  
echo 'Found ' . \$duplicates->count() . ' students with duplicate invoices' . PHP_EOL;
"
```

## API Integration

One-time fees can also be created via the API:

```bash
POST /api/v1/payment-system/fees
Content-Type: application/json

{
  "name": "Exam Fee",
  "name_mm": "စာမေးပွဲ ကြေးငွေ",
  "description": "Annual examination fee",
  "amount": 50000,
  "frequency": "one_time",
  "fee_type": "other",
  "grade": "0",
  "batch": "One Grade Demo 2026-2027",
  "target_month": 3,
  "due_date": "2026-03-31",
  "supports_payment_period": false,
  "is_active": true
}
```

The API will automatically dispatch the invoice generation job when a one-time fee is created.

## Best Practices

1. **Timing**: Create one-time fees at the beginning of the academic year or before the target month
2. **Naming**: Use clear, descriptive names (e.g., "2026 Exam Fee" instead of just "Exam Fee")
3. **Due Dates**: Set reasonable due dates (typically 30-60 days from creation)
4. **Testing**: Test with a small grade first before rolling out to all grades
5. **Verification**: Always verify invoice generation by checking the database
6. **Communication**: Notify parents/guardians after generating invoices

## Related Files

- Job: `app/Jobs/PaymentSystem/GenerateOneTimeFeeInvoicesJob.php`
- Command: `app/Console/Commands/GenerateOneTimeFeeInvoices.php`
- Command: `app/Console/Commands/CreateOneTimeFee.php`
- Model: `app/Models/PaymentSystem/FeeStructure.php`
- Model: `app/Models/PaymentSystem/Invoice.php`
- API Controller: `app/Http/Controllers/Api/V1/PaymentSystem/FeeController.php`

## Support

For issues or questions, check the application logs:
```bash
tail -f storage/logs/laravel.log
```
