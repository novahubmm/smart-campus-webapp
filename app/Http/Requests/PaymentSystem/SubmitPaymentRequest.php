<?php

namespace App\Http\Requests\PaymentSystem;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authenticated users can submit payments
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Calculate max months based on student's batch end date
        $maxMonths = 60; // Default fallback (5 years)
        
        // Try to get student from invoice
        $invoiceId = $this->input('invoice_id') ?? ($this->input('invoice_ids')[0] ?? null);
        if ($invoiceId) {
            $invoice = \App\Models\PaymentSystem\Invoice::with('student.batch')->find($invoiceId);
            if ($invoice && $invoice->student && $invoice->student->batch && $invoice->student->batch->end_date) {
                $now = now();
                $batchEndDate = $invoice->student->batch->end_date;
                $monthsUntilEnd = $now->diffInMonths($batchEndDate);
                $maxMonths = max(1, ceil($monthsUntilEnd)); // At least 1 month
            }
        }
        
        return [
            // Support both single invoice_id and multiple invoice_ids (Postman spec uses invoice_ids array)
            'invoice_id' => ['required_without:invoice_ids', 'exists:invoices_payment_system,id'],
            'invoice_ids' => ['required_without:invoice_id', 'array', 'min:1'],
            'invoice_ids.*' => ['exists:invoices_payment_system,id'],
            
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'payment_amount' => ['required', 'numeric', 'min:10000'], // Minimum 10,000 MMK
            'payment_type' => ['required', Rule::in(['full', 'partial'])],
            'payment_months' => ['nullable', 'integer', 'min:1', "max:{$maxMonths}"],
            'payment_date' => ['required', 'date'],
            
            // Support both file upload and base64 string
            'receipt_image' => ['required', 'string'], // Base64 string or file
            
            'notes' => ['nullable', 'string', 'max:500'],
            'fee_payment_details' => ['required', 'array', 'min:1'],
            
            // Support both fee_id and invoice_fee_id
            'fee_payment_details.*.fee_id' => ['required_without:fee_payment_details.*.invoice_fee_id', 'string'],
            'fee_payment_details.*.invoice_fee_id' => ['required_without:fee_payment_details.*.fee_id', 'exists:invoice_fees,id'],
            'fee_payment_details.*.fee_name' => ['required', 'string'],
            'fee_payment_details.*.full_amount' => ['required', 'numeric'],
            'fee_payment_details.*.paid_amount' => ['required', 'numeric', 'min:0'], // Allow 0 for unpaid fees in partial payments
            'fee_payment_details.*.is_partial' => ['required', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'invoice_id.required_without' => 'Invoice ID or invoice IDs are required.',
            'invoice_id.exists' => 'The selected invoice does not exist.',
            'invoice_ids.required_without' => 'Invoice ID or invoice IDs are required.',
            'invoice_ids.array' => 'Invoice IDs must be an array.',
            'invoice_ids.min' => 'At least one invoice ID is required.',
            'invoice_ids.*.exists' => 'One or more selected invoices do not exist.',
            'payment_method_id.required' => 'Payment method is required.',
            'payment_method_id.exists' => 'The selected payment method does not exist.',
            'payment_amount.required' => 'Payment amount is required.',
            'payment_amount.min' => 'Payment amount must be at least 10,000 MMK.',
            'payment_type.required' => 'Payment type is required.',
            'payment_type.in' => 'Payment type must be either "full" or "partial".',
            'payment_months.in' => 'Payment months must be valid and within the batch period.',
            'payment_date.required' => 'Payment date is required.',
            'receipt_image.required' => 'Receipt image is required.',
            'receipt_image.string' => 'Receipt image must be a valid base64 string.',
            'fee_payment_details.required' => 'Fee payment details are required.',
            'fee_payment_details.min' => 'At least one fee must be included in the payment.',
            'fee_payment_details.*.fee_id.required_without' => 'Fee ID is required for each fee.',
            'fee_payment_details.*.invoice_fee_id.required_without' => 'Invoice fee ID is required for each fee.',
            'fee_payment_details.*.invoice_fee_id.exists' => 'One or more invoice fees do not exist.',
            'fee_payment_details.*.fee_name.required' => 'Fee name is required for each fee.',
            'fee_payment_details.*.full_amount.required' => 'Full amount is required for each fee.',
            'fee_payment_details.*.paid_amount.required' => 'Paid amount is required for each fee.',
            'fee_payment_details.*.paid_amount.min' => 'Paid amount cannot be negative.',
            'fee_payment_details.*.is_partial.required' => 'Partial payment flag is required for each fee.',
        ];
    }
}
