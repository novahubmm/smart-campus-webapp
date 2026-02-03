<?php

namespace App\DTOs\Finance;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class FinanceSetupData
{
    /**
     * @param array<int, array{grade_id:string, amount:float}> $gradeFees
     * @param array<int, string> $expenseCategories
     */
    public function __construct(
        public readonly array $gradeFees,
        public readonly array $expenseCategories,
    ) {}

    public static function from(array $validated): self
    {
        $gradeIds = Arr::wrap($validated['grade_fee_grade_id'] ?? []);
        $gradeAmounts = Arr::wrap($validated['grade_fee_amount'] ?? []);

        $gradeFees = [];

        foreach ($gradeIds as $index => $gradeId) {
            $amount = $gradeAmounts[$index] ?? null;

            if (!$gradeId || $amount === null || $amount === '') {
                continue;
            }

            $gradeFees[] = [
                'grade_id' => $gradeId,
                'amount' => (float) $amount,
            ];
        }

        // Get checkbox categories
        $checkboxCategories = $validated['expense_categories'] ?? [];

        // Parse custom categories (comma-separated)
        $customCategoriesStr = $validated['custom_expense_categories'] ?? '';
        $customCategories = collect(explode(',', $customCategoriesStr))
            ->map(fn(string $item) => trim($item))
            ->filter()
            ->all();

        // Merge and deduplicate
        $expenseCategories = collect($checkboxCategories)
            ->merge($customCategories)
            ->map(fn(string $item) => ucfirst(strtolower(trim($item))))
            ->unique(fn($name) => Str::lower($name))
            ->values()
            ->all();

        return new self(
            gradeFees: $gradeFees,
            expenseCategories: $expenseCategories,
        );
    }
}
