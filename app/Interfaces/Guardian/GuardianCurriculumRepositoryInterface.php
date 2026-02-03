<?php

namespace App\Interfaces\Guardian;

use App\Models\StudentProfile;

interface GuardianCurriculumRepositoryInterface
{
    public function getCurriculum(StudentProfile $student): array;

    public function getSubjectCurriculum(string $subjectId, StudentProfile $student): array;

    public function getChapters(string $subjectId): array;

    public function getChapterDetail(string $chapterId): array;
}
