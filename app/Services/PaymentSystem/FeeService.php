<?php

namespace App\Services\PaymentSystem;

use App\Models\PaymentSystem\FeeStructure;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class FeeService
{
    /**
     * Valid frequency values for fee categories.
     */
    private const VALID_FREQUENCIES = ['one_time', 'monthly'];

    /**
     * Valid fee type values.
     */
    private const VALID_FEE_TYPES = [
        'tuition',
        'transportation',
        'library',
        'lab',
        'sports',
        'course_materials',
        'other',
    ];

    /**
     * Create a new fee category with validation.
     *
     * @param array $data Fee category data
     * @return FeeStructure
     * @throws ValidationException
     */
    public function createFeeCategory(array $data): FeeStructure
    {
        // Validate fee data
        $this->validateFeeData($data);

        // Create the fee structure
        $feeStructure = FeeStructure::create([
            'name' => $data['name'],
            'name_mm' => $data['name_mm'] ?? null,
            'description' => $data['description'] ?? null,
            'description_mm' => $data['description_mm'] ?? null,
            'amount' => $data['amount'],
            'frequency' => $data['frequency'],
            'fee_type' => $data['fee_type'],
            'grade' => $data['grade'],
            'batch' => $data['batch'],
            'target_month' => $data['target_month'] ?? null,
            'due_date' => $data['due_date'],
            'supports_payment_period' => $data['supports_payment_period'] ?? false,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return $feeStructure;
    }

    /**
     * Validate fee category data.
     *
     * @param array $data Fee category data
     * @return array Validated data
     * @throws ValidationException
     */
    public function validateFeeData(array $data): array
    {
        $errors = [];

        // Validate frequency
        if (!isset($data['frequency']) || !in_array($data['frequency'], self::VALID_FREQUENCIES)) {
            $errors['frequency'] = [
                'The frequency must be either "one_time" or "monthly".',
            ];
        }

        // Validate fee_type
        if (!isset($data['fee_type']) || !in_array($data['fee_type'], self::VALID_FEE_TYPES)) {
            $errors['fee_type'] = [
                'The fee type must be one of: ' . implode(', ', self::VALID_FEE_TYPES) . '.',
            ];
        }

        // Validate supports_payment_period only allowed for monthly fees
        if (isset($data['supports_payment_period']) && $data['supports_payment_period'] === true) {
            if (!isset($data['frequency']) || $data['frequency'] !== 'monthly') {
                $errors['supports_payment_period'] = [
                    'Payment period support is only allowed for monthly fees.',
                ];
            }
        }

        // Validate target_month required for one-time fees
        if (isset($data['frequency']) && $data['frequency'] === 'one_time') {
            if (!isset($data['target_month']) || $data['target_month'] === null) {
                $errors['target_month'] = [
                    'Target month is required for one-time fees.',
                ];
            } elseif (!is_numeric($data['target_month']) || $data['target_month'] < 1 || $data['target_month'] > 12) {
                $errors['target_month'] = [
                    'Target month must be between 1 and 12.',
                ];
            }
        }

        // Throw validation exception if there are errors
        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        return $data;
    }

    /**
     * Get all fees for a specific grade and batch.
     *
     * @param string $grade
     * @param string $batch
     * @return Collection
     */
    public function getFeesForGrade(string $grade, string $batch): Collection
    {
        return FeeStructure::where('grade', $grade)
            ->where('batch', $batch)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Get monthly fees for a specific grade.
     *
     * @param string $grade
     * @return Collection
     */
    public function getMonthlyFeesForGrade(string $grade): Collection
    {
        return FeeStructure::where('grade', $grade)
            ->where('frequency', 'monthly')
            ->where('is_active', true)
            ->get();
    }
}
