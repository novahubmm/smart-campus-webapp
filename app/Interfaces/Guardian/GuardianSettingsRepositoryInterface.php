<?php

namespace App\Interfaces\Guardian;

interface GuardianSettingsRepositoryInterface
{
    public function getSettings(string $guardianId): array;

    public function updateSettings(string $guardianId, array $settings): array;

    public function getSchoolInfo(): array;

    public function getSchoolRules(): array;

    public function getSchoolContact(): array;

    public function getSchoolFacilities(): array;
}
