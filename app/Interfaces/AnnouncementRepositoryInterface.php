<?php

namespace App\Interfaces;

use App\DTOs\Announcement\AnnouncementData;
use App\DTOs\Announcement\AnnouncementFilterData;
use App\Models\Announcement;
use Illuminate\Support\Collection;

interface AnnouncementRepositoryInterface
{
    public function list(AnnouncementFilterData $filter, int $perPage = 10): \Illuminate\Contracts\Pagination\LengthAwarePaginator;

    public function create(AnnouncementData $data): Announcement;

    public function update(Announcement $announcement, AnnouncementData $data): Announcement;

    public function delete(Announcement $announcement): void;
}
