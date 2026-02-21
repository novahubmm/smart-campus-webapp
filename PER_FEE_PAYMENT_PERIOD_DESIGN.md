# Per-Fee Payment Period Design

## Overview
Allow different payment periods for different fee types. For example:
- School Fee: 3 months
- Book Fee: 1 month
- Transportation Fee: 5 months

## Current Implementation
Currently, there's a single payment period selection that applies to ALL fees in the invoice.

## New Design Requirements

### UI Changes (Invoice Tab)

1. **Remove Global Payment Period Selection**
   - Remove the single payment period selector at the top

2. **Add Per-Fee Payment Period Selection**
   - For each fee in the invoice, show:
     - Fee name and due date
     - Monthly amount
     - Payment period options (1, 3, 5 months, etc.)
     - Discount percentage for each option

3. **Update Payment Summary**
   - Show each fee with its selected payment period
   - Calculate subtotal based on individual fee periods
   - Apply discounts per fee based on their selected period
   - Show total amount

### Data Structure Changes

#### Frontend (Alpine.js)

Current structure:
```javascript
paymentData: {
    payment_months: 3, // Single value for all fees
    fees: [
        { id: 1, fee_name: 'School Fee', remaining_amount: 10000, payment_amount: 10000 },
        { id: 2, fee_name: 'Book Fee', remaining_amount: 12000, payment_amount: 12000 }
    ]
}
```

New structure:
```javascript
paymentData: {
    fees: [
        { 
            id: 1, 
            fee_name: 'School Fee', 
            remaining_amount: 10000, 
            payment_amount: 10000,
            payment_months: 3  // Individual payment period
        },
        { 
            id: 2, 
            fee_name: 'Book Fee', 
            remaining_amount: 12000, 
            payment_amount: 12000,
            payment_months: 1  // Individual payment period
        }
    ]
}
```

### Calculation Logic Changes

#### Current Logic
```javascript
updatePaymentCalculation() {
    let subtotal = 0;
    let discount = 0;
    
    this.paymentData.fees.forEach(fee => {
        if (this.paymentData.payment_type === 'full') {
            // All fees use same payment_months
            subtotal += fee.remaining_amount * this.paymentData.payment_months;
        } else {
            subtotal += fee.payment_amount;
        }
    });
    
    // Apply single discount based on global payment_months
    const discountOption = this.paymentPeriodOptions.find(opt => opt.months == this.paymentData.payment_months);
    if (discountOption) {
        discount = subtotal * (discountOption.discount_percent / 100);
    }
    
    this.paymentData.subtotal = subtotal;
    this.paymentData.discount = discount;
    this.paymentData.total = subtotal - discount;
}
```

#### New Logic
```javascript
updatePaymentCalculation() {
    let subtotal = 0;
    let totalDiscount = 0;
    
    this.paymentData.fees.forEach(fee => {
        let feeAmount = 0;
        
        if (this.paymentData.payment_type === 'full') {
            // Each fee uses its own payment_months
            feeAmount = fee.remaining_amount * fee.payment_months;
        } else {
            feeAmount = fee.payment_amount;
        }
        
        subtotal += feeAmount;
        
        // Apply discount ONLY to School Fee based on its payment_months
        const isSchoolFee = fee.fee_name && fee.fee_name.toLowerCase().includes('school fee');
        if (isSchoolFee) {
            const discountOption = this.paymentPeriodOptions.find(opt => opt.months == fee.payment_months);
            if (discountOption && discountOption.discount_percent > 0) {
                const feeDiscount = feeAmount * (discountOption.discount_percent / 100);
                totalDiscount += feeDiscount;
            }
        }
    });
    
    this.paymentData.subtotal = subtotal;
    this.paymentData.discount = totalDiscount;
    this.paymentData.total = subtotal - totalDiscount;
}
```

### Initialization Logic

```javascript
selectStudent(student) {
    // ... existing code ...
    
    // Initialize each fee with default payment_months = 1
    this.paymentData.fees = student.fees.map(fee => ({
        ...fee,
        payment_months: 1,  // Default to 1 month
        payment_amount: fee.remaining_amount
    }));
    
    this.updatePaymentCalculation();
}
```

### UI Template Example

```html
<!-- Payment Period Selection - Per Fee Type -->
<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-3">
        Payment Period
    </label>
    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
        Select payment period for each fee type
    </p>
    
    <!-- Fee-specific payment period selection -->
    <div class="space-y-3">
        <template x-for="(fee, index) in paymentData.fees" :key="fee.id">
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 bg-white dark:bg-gray-800">
                <!-- Fee Header -->
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <p class="font-medium text-sm text-gray-900 dark:text-white" x-text="fee.fee_name"></p>
                        <p class="text-xs text-gray-500" x-text="'Due: ' + formatDueDate(fee.due_date)"></p>
                    </div>
                    <span class="text-xs font-semibold text-gray-600 dark:text-gray-400" 
                          x-text="fee.remaining_amount.toLocaleString() + ' MMK/month'"></span>
                </div>
                
                <!-- Payment period options for this fee -->
                <div class="grid grid-cols-3 gap-2">
                    <template x-for="option in paymentPeriodOptions" :key="option.months">
                        <label class="relative flex flex-col p-2 border-2 rounded-lg cursor-pointer transition-all text-center" 
                               :class="fee.payment_months == option.months ? 'border-teal-500 bg-teal-50 dark:bg-teal-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-teal-300'">
                            <input type="radio" 
                                   :name="'fee_months_' + fee.id" 
                                   :value="option.months" 
                                   x-model.number="fee.payment_months" 
                                   @change="updatePaymentCalculation" 
                                   class="sr-only">
                            <div class="flex flex-col items-center">
                                <span class="font-bold text-base text-gray-900 dark:text-white" x-text="option.months"></span>
                                <span class="text-xs text-gray-600 dark:text-gray-400" 
                                      x-text="option.months === 1 ? 'month' : 'months'"></span>
                                <span x-show="option.discount_percent > 0" 
                                      class="text-xs font-medium text-green-600 dark:text-green-400 mt-1" 
                                      x-text="'-' + option.discount_percent + '%'"></span>
                            </div>
                        </label>
                    </template>
                </div>
            </div>
        </template>
    </div>
</div>
```

### Payment Summary Update

```html
<!-- Fees Breakdown -->
<div class="space-y-3">
    <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">
        FEES BREAKDOWN
    </p>
    
    <template x-for="(fee, index) in paymentData.fees" :key="fee.id">
        <div class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-700">
            <div class="flex-1">
                <p class="font-medium text-gray-900 dark:text-white" x-text="fee.fee_name"></p>
                <p class="text-xs text-gray-500" x-text="'Due: ' + formatDueDate(fee.due_date)"></p>
                <!-- Show calculation with individual payment months -->
                <template x-if="paymentData.payment_type === 'full' && fee.payment_months > 1">
                    <p class="text-xs text-teal-600 dark:text-teal-400 mt-1" 
                       x-text="fee.remaining_amount.toLocaleString() + ' MMK × ' + fee.payment_months + ' = ' + (fee.remaining_amount * fee.payment_months).toLocaleString() + ' MMK'"></p>
                </template>
            </div>
            <div class="text-right">
                <span class="font-bold text-gray-900 dark:text-white block" 
                      x-text="(fee.remaining_amount * fee.payment_months).toLocaleString() + ' MMK'"></span>
            </div>
        </div>
    </template>
</div>
```

## Implementation Steps

1. **Update View Template** (`student-fees.blade.php`)
   - Replace global payment period selection with per-fee selection
   - Update payment summary to show individual fee calculations

2. **Update JavaScript Logic**
   - Modify `selectStudent()` to initialize `payment_months` for each fee
   - Update `updatePaymentCalculation()` to calculate per-fee
   - Update `adjustFeeAmount()` if needed

3. **Test Scenarios**
   - School Fee: 3 months with 5% discount
   - Book Fee: 1 month with 0% discount
   - Transportation Fee: 5 months with custom discount
   - Verify total calculation is correct
   - Verify discount applies per fee

## Benefits

1. **Flexibility**: Parents can choose different payment periods for different fees
2. **Realistic**: Matches real-world scenarios where some fees are paid monthly, others quarterly
3. **Better UX**: Clear visualization of what period applies to which fee
4. **Accurate Discounts**: Discounts apply correctly per fee based on their individual periods

## Example Calculation

```
School Fee: 10,000 MMK/month × 3 months = 30,000 MMK
  Discount (5% on School Fee only): -1,500 MMK
  Subtotal: 28,500 MMK

Transportation Fee: 25,000 MMK/month × 3 months = 75,000 MMK
  Discount: 0 MMK (discount only applies to School Fee)
  Subtotal: 75,000 MMK

Book Fee: 12,000 MMK/month × 1 month = 12,000 MMK
  Discount: 0 MMK (discount only applies to School Fee)
  Subtotal: 12,000 MMK

Total Discount: 1,500 MMK (only from School Fee)
Total: 115,500 MMK
```
