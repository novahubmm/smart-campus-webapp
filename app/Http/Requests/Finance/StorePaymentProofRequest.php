<?php

namespace App\Http\Requests\Finance;

use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePaymentProofRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'fee_ids' => ['required', 'array', 'min:1'],
            'fee_ids.*' => ['required', 'string', 'exists:invoices,id'],
            'payment_method_id' => ['required', 'string', 'exists:payment_methods,id'],
            'payment_amount' => ['required', 'numeric', 'min:0.01'],
            'payment_months' => ['required', 'integer', 'min:1', 'max:12'],
            'payment_date' => ['required', 'date', 'date_format:Y-m-d', 'before_or_equal:today'],
            'receipt_image' => ['required', 'string'], // Base64 or file path
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'fee_ids.required' => 'At least one fee must be selected.',
            'fee_ids.array' => 'Fee IDs must be provided as an array.',
            'fee_ids.min' => 'At least one fee must be selected.',
            'fee_ids.*.exists' => 'One or more selected fees are invalid.',
            'payment_method_id.required' => 'Payment method is required.',
            'payment_method_id.exists' => 'Selected payment method is invalid.',
            'payment_amount.required' => 'Payment amount is required.',
            'payment_amount.numeric' => 'Payment amount must be a number.',
            'payment_amount.min' => 'Payment amount must be greater than zero.',
            'payment_months.required' => 'Number of months is required.',
            'payment_months.integer' => 'Number of months must be an integer.',
            'payment_months.min' => 'Number of months must be at least 1.',
            'payment_months.max' => 'Number of months cannot exceed 12.',
            'payment_date.required' => 'Payment date is required.',
            'payment_date.date' => 'Payment date must be a valid date.',
            'payment_date.date_format' => 'Payment date must be in Y-m-d format.',
            'payment_date.before_or_equal' => 'Payment date cannot be in the future.',
            'receipt_image.required' => 'Receipt image is required.',
            'notes.max' => 'Notes cannot exceed 500 characters.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // Get student_id from route or request
            $studentId = $this->route('student_id') ?? $this->route('studentId') ?? $this->input('student_id');
            
            if (!$studentId) {
                $validator->errors()->add('student_id', 'Student ID is required.');
                return;
            }

            $feeIds = $this->input('fee_ids', []);
            $paymentAmount = $this->input('payment_amount', 0);

            // Validate fee ownership
            $invoices = Invoice::whereIn('id', $feeIds)
                ->where('student_id', $studentId)
                ->get();

            if ($invoices->count() !== count($feeIds)) {
                $validator->errors()->add('fee_ids', 'One or more fees do not belong to this student.');
                return;
            }

            // Validate all invoices are unpaid
            $unpaidInvoices = $invoices->where('status', 'unpaid');
            if ($unpaidInvoices->count() !== $invoices->count()) {
                $validator->errors()->add('fee_ids', 'One or more fees are not in unpaid status.');
                return;
            }

            // Validate amount sum
            $totalAmount = $invoices->sum('total_amount');
            $difference = abs($totalAmount - $paymentAmount);
            
            // Allow for floating point precision (0.01 tolerance)
            if ($difference > 0.01) {
                $validator->errors()->add(
                    'payment_amount',
                    sprintf(
                        'Payment amount (%.2f) does not match the sum of selected fees (%.2f).',
                        $paymentAmount,
                        $totalAmount
                    )
                );
            }
        });
    }
}
