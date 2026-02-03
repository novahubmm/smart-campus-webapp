<?php

namespace App\Interfaces;

use App\DTOs\EventAnnouncement\EventAnnouncementSetupData;
use App\Models\Setting;
use Illuminate\Support\Collection;

interface EventAnnouncementRepositoryInterface
{
    public function firstOrCreateSetting(): Setting;

    public function updateSetup(Setting $setting, EventAnnouncementSetupData $data): Setting;

    /** @return Collection<int, string> */
    public function getCategoryNames(): Collection;

    public function syncCategories(array $names): void;
}
