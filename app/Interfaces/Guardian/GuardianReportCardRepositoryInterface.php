<?php

namespace App\Interfaces\Guardian;

use App\Models\StudentProfile;

interface GuardianReportCardRepositoryInterface
{
    public function getReportCards(StudentProfile $student): array;

    public function getReportCardDetail(string $reportCardId, StudentProfile $student): array;
}
