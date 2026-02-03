<?php

namespace App\Interfaces;

use App\DTOs\Timetable\TimetableData;
use App\DTOs\Timetable\PeriodData;
use App\DTOs\Timetable\TimetableFilterData;
use App\Models\Timetable;
use Illuminate\Support\Collection;

interface TimetableRepositoryInterface
{
    public function list(TimetableFilterData $filter): Collection;

    public function getForClass(string $classId): ?Timetable;

    public function getForTeacherHomeroom(string $teacherProfileId): ?Timetable;

    public function getForStudent(string $studentId): ?Timetable;

    public function storeTimetable(TimetableData $data, Collection $periods): Timetable;

    public function updateTimetable(Timetable $timetable, TimetableData $data, Collection $periods): Timetable;

    public function activateTimetable(Timetable $timetable): Timetable;

    public function deleteTimetable(Timetable $timetable): bool;
}
