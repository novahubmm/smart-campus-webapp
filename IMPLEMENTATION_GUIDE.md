# Per-Fee Payment Period Implementation Guide

## Summary of Changes

This implementation allows each fee type to have its own payment period selection, with discounts applying ONLY to School Fee.

## Files to Modify

### 1. `resources/views/finance/student-fees.blade.php`

#### Change 1: Replace Global Payment Period Selection (around line 1143-1163)

**Find this section:**
```html
<!-- Payment Period Selection -->
<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-3">{{ __('finance.Payment Period') }}</label>
    <div class="grid grid-cols-3 gap-2">
        <template x-for="option in paymentPeriodOptions" :key="option.months">
            <label class="relative flex flex-col p-3 border-2 rounded-lg cursor-pointer transition-all" :class="paymentData.payment_months == option.months ? 'border-teal-500 bg-teal-50 dark:bg-teal-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-teal-300'">
                <input type="radio" name="payment_months" :value="option.months" x-model.number="paymentData.payment_months" @change="updatePaymentCalculation" class="sr-only">
                ...
            </label>
        </template>
    </div>
</div>
```

**Replace with:** Content from `PAYMENT_PERIOD_NEW_HTML.txt`

#### Change 2: Update Payment Summary Section (around line 1165-1230)

**Find the Payment Summary section and replace with:** Content from `PAYMENT_SUMMARY_NEW_HTML.txt`

#### Change 3: Update JavaScript - openPaymentModal function (around line 2138-2185)

**Find this in the openPaymentModal function:**
```javascript
fees: invoice.fees.map(fee => ({
    id: fee.id,
    fee_name: fee.fee_name,
    fee_name_mm: fee.fee_name_mm,
    amount: parseFloat(fee.amount),
    remaining_amount: parseFloat(fee.remaining_amount),
    payment_amount: parseFloat(fee.remaining_amount),
    due_date: fee.due_date,
    due_date_raw: fee.due_date_raw
})),
```

**Replace with:**
```javascript
fees: invoice.fees.map(fee => ({
    id: fee.id,
    fee_name: fee.fee_name,
    fee_name_mm: fee.fee_name_mm,
    amount: parseFloat(fee.amount),
    remaining_amount: parseFloat(fee.remaining_amount),
    payment_amount: parseFloat(fee.remaining_amount),
    payment_months: 1,  // ADD THIS LINE - Initialize each fee with 1 month
    due_date: fee.due_date,
    due_date_raw: fee.due_date_raw
})),
```

**Also in openPaymentModal, remove this line:**
```javascript
payment_months: 1,  // REMOVE THIS LINE from paymentData initialization
```

#### Change 4: Update JavaScript - updatePaymentCalculation function (around line 2187-2230)

**Find the entire updatePaymentCalculation function and replace with:** Content from `PAYMENT_CALCULATION_NEW_JS.txt`

## Key Changes Summary

### Data Structure
- **Before:** Single `payment_months` for all fees
- **After:** Each fee has its own `payment_months` property

### UI Changes
- **Before:** One payment period selector for all fees
- **After:** Each fee has its own payment period selector

### Discount Logic
- **Before:** Discount applied to all fees (or just School Fee with global period)
- **After:** Discount ONLY applies to School Fee based on its individual payment period

### Calculation Logic
- **Before:** `fee.remaining_amount * paymentData.payment_months`
- **After:** `fee.remaining_amount * fee.payment_months`

## Testing Checklist

1. ✅ Open payment modal for a student with multiple fees
2. ✅ Verify each fee shows its own payment period selector
3. ✅ Select different periods for different fees (e.g., School Fee: 3 months, Book Fee: 1 month)
4. ✅ Verify discount badge only shows on School Fee options
5. ✅ Verify calculation:
   - School Fee: amount × 3 months - discount
   - Book Fee: amount × 1 month (no discount)
   - Transportation Fee: amount × selected months (no discount)
6. ✅ Verify total is correct
7. ✅ Submit payment and verify it processes correctly

## Example Scenario

**Student has 3 fees:**
- School Fee: 10,000 MMK/month
- Transportation Fee: 25,000 MMK/month
- Book Fee: 12,000 MMK/month

**User selects:**
- School Fee: 3 months (5% discount)
- Transportation Fee: 5 months (no discount)
- Book Fee: 1 month (no discount)

**Expected calculation:**
```
School Fee: 10,000 × 3 = 30,000 MMK
  Discount (5%): -1,500 MMK
  Subtotal: 28,500 MMK

Transportation Fee: 25,000 × 5 = 125,000 MMK
  Discount: 0 MMK
  Subtotal: 125,000 MMK

Book Fee: 12,000 × 1 = 12,000 MMK
  Discount: 0 MMK
  Subtotal: 12,000 MMK

Total: 165,500 MMK
```

## Rollback Plan

If issues occur, revert the following:
1. Restore original payment period HTML (single selector)
2. Restore original `updatePaymentCalculation` function
3. Remove `payment_months` from individual fees
4. Add back `payment_months` to `paymentData` object

## Notes

- Discount percentages are defined in `payment_promotions` table
- Only fees with "school fee" in the name (case-insensitive) get discounts
- Each fee can have a different payment period (1, 3, 5, 6, 12 months, etc.)
- The UI shows discount badges only on School Fee payment period options
