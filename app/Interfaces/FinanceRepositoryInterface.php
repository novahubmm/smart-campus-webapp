<?php

namespace App\Interfaces;

use App\DTOs\Finance\FinanceSetupData;
use App\Models\Setting;
use Illuminate\Support\Collection;

interface FinanceRepositoryInterface
{
    public function firstOrCreateSetting(): Setting;

    public function updateSetup(Setting $setting, FinanceSetupData $data): Setting;

    public function getExpenseCategoryNames(): Collection;

    public function syncExpenseCategories(array $names): void;
}
