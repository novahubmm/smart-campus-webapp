<?php

namespace App\Services;

use App\DTOs\Finance\FinanceSetupData;
use App\Interfaces\FinanceRepositoryInterface;
use App\Models\Setting;
use Illuminate\Support\Collection;

class FinanceService
{
    public function __construct(
        private readonly FinanceRepositoryInterface $repository,
    ) {}

    public function getSetting(): Setting
    {
        return $this->repository->firstOrCreateSetting();
    }

    public function getExpenseCategories(): Collection
    {
        return $this->repository->getExpenseCategoryNames();
    }

    public function saveSetup(FinanceSetupData $data): Setting
    {
        $setting = $this->repository->firstOrCreateSetting();

        $this->repository->syncExpenseCategories($data->expenseCategories);

        return $this->repository->updateSetup($setting, $data);
    }
}
