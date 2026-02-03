<?php

namespace App\Repositories;

use App\DTOs\Finance\FinanceSetupData;
use App\Interfaces\FinanceRepositoryInterface;
use App\Models\ExpenseCategory;
use App\Models\Grade;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FinanceRepository implements FinanceRepositoryInterface
{
    public function firstOrCreateSetting(): Setting
    {
        return Setting::firstOrCreate([]);
    }

    public function updateSetup(Setting $setting, FinanceSetupData $data): Setting
    {
        $normalizedGradeFees = collect($data->gradeFees)
            ->map(fn(array $fee) => [
                'grade_id' => $fee['grade_id'],
                'amount' => round($fee['amount'], 2),
            ])
            ->values()
            ->all();

        $setting->fill([
            'tuition_fee_by_grade' => $normalizedGradeFees,
            'setup_completed_finance' => true,
        ]);

        $setting->save();

        $this->syncGradeFees($normalizedGradeFees);

        return $setting->fresh();
    }

    public function getExpenseCategoryNames(): Collection
    {
        return ExpenseCategory::query()
            ->orderBy('name')
            ->pluck('name');
    }

    public function syncExpenseCategories(array $names): void
    {
        $cleanNames = collect($names)
            ->map(fn(string $name) => trim($name))
            ->filter()
            ->unique(fn($name) => Str::lower($name))
            ->values();

        $keptIds = [];

        foreach ($cleanNames as $name) {
            $code = Str::upper(Str::slug($name, '_'));

            $existing = ExpenseCategory::withTrashed()
                ->whereRaw('lower(name) = ?', [Str::lower($name)])
                ->first();

            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                }
                $existing->fill([
                    'name' => $name,
                    'code' => $code,
                    'status' => true,
                ]);
                $existing->save();
                $keptIds[] = $existing->id;
                continue;
            }

            $new = ExpenseCategory::create([
                'name' => $name,
                'code' => $code,
                'status' => true,
            ]);
            $keptIds[] = $new->id;
        }

        ExpenseCategory::whereNotIn('id', $keptIds)->update(['status' => false]);
        ExpenseCategory::whereNotIn('id', $keptIds)->delete();
    }

    /**
     * @param array<int, array{grade_id:string, amount:float}> $gradeFees
     */
    protected function syncGradeFees(array $gradeFees): void
    {
        foreach ($gradeFees as $fee) {
            $grade = Grade::find($fee['grade_id']);

            if (!$grade) {
                continue;
            }

            $grade->price_per_month = $fee['amount'];
            $grade->save();
        }
    }
}
