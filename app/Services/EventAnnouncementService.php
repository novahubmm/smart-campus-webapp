<?php

namespace App\Services;

use App\DTOs\EventAnnouncement\EventAnnouncementSetupData;
use App\Interfaces\EventAnnouncementRepositoryInterface;
use App\Models\Setting;
use Illuminate\Support\Collection;

class EventAnnouncementService
{
    public function __construct(
        private readonly EventAnnouncementRepositoryInterface $repository
    ) {}

    public function getSetup(): Setting
    {
        return $this->repository->firstOrCreateSetting();
    }

    /** @return Collection<int, string> */
    public function getCategoryNames(): Collection
    {
        return $this->repository->getCategoryNames();
    }

    /**
     * Get existing categories as lowercase array for checkbox matching
     * @return array<string>
     */
    public function getExistingCategoriesLower(): array
    {
        return $this->repository->getCategoryNames()
            ->map(fn(string $name) => strtolower($name))
            ->all();
    }

    /**
     * Get custom categories (non-default ones)
     */
    public function getCustomCategories(): string
    {
        $defaultCategories = ['academic', 'sports', 'cultural', 'meeting', 'holiday', 'ceremony'];

        return $this->repository->getCategoryNames()
            ->filter(fn(string $name) => !in_array(strtolower($name), $defaultCategories))
            ->implode(', ');
    }

    public function saveSetup(EventAnnouncementSetupData $data): Setting
    {
        $setting = $this->repository->firstOrCreateSetting();

        $this->repository->syncCategories($data->eventCategories);

        return $this->repository->updateSetup($setting, $data);
    }
}
