<?php

namespace App\Services;

use App\DTOs\Announcement\AnnouncementData;
use App\DTOs\Announcement\AnnouncementFilterData;
use App\Interfaces\AnnouncementRepositoryInterface;
use App\Models\Announcement;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\AnnouncementPublished;
use Illuminate\Support\Collection;

class AnnouncementService
{
    public function __construct(private readonly AnnouncementRepositoryInterface $repository) {}

    public function list(AnnouncementFilterData $filter, int $perPage = 10): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->repository->list($filter, $perPage);
    }

    public function create(AnnouncementData $data): Announcement
    {
        $announcement = $this->repository->create($data);

        $this->notifyIfNeeded($announcement);

        return $announcement;
    }

    public function update(Announcement $announcement, AnnouncementData $data): Announcement
    {
        $wasPublished = $announcement->is_published;
        $updated = $this->repository->update($announcement, $data);

        if (!$wasPublished && $updated->is_published) {
            $this->notifyIfNeeded($updated);
        }

        return $updated;
    }

    public function delete(Announcement $announcement): void
    {
        $this->repository->delete($announcement);
    }

    private function notifyIfNeeded(Announcement $announcement): void
    {
        if (!$announcement->is_published || !$announcement->status) {
            return;
        }

        $setting = Setting::first();
        $channels = [];

        if ($setting?->announcement_notify_in_app) {
            $channels[] = 'database';
        }
        if ($setting?->announcement_notify_email) {
            $channels[] = 'mail';
        }
        if ($setting?->announcement_notify_push) {
            $channels[] = 'webpush';
        }

        if (empty($channels)) {
            // default to in-app so at least one channel persists the message
            $channels[] = 'database';
        }

        $roles = $announcement->target_roles ?: [];
        $recipients = $roles
            ? User::role($roles)->where('is_active', true)->get()
            : collect();

        $recipients->unique('id')->each(fn(User $user) => $user->notify(new AnnouncementPublished($announcement, $channels)));
    }
}
