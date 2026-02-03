<?php

namespace App\Services;

use App\DTOs\Attendance\StudentAttendanceFilterData;
use App\Interfaces\StudentAttendanceRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class StudentAttendanceService
{
    public function __construct(private readonly StudentAttendanceRepositoryInterface $repository) {}

    public function classSummary(string $date, ?string $classId, ?string $gradeId): Collection
    {
        return $this->repository->getClassDailySummary(Carbon::parse($date), $classId, $gradeId);
    }

    public function students(StudentAttendanceFilterData $filter): Collection
    {
        return $this->repository->getStudentsWithMonthlyStat($filter);
    }

    public function registerData(string $classId, string $date): array
    {
        return $this->repository->getRegisterData($classId, Carbon::parse($date));
    }

    public function classDetailData(string $classId, string $date): array
    {
        return $this->repository->getClassDetailData($classId, Carbon::parse($date));
    }

    public function studentDetailData(string $studentId, string $startDate, string $endDate): array
    {
        return $this->repository->getStudentDetailData($studentId, Carbon::parse($startDate), Carbon::parse($endDate));
    }

    public function saveRegister(string $classId, string $date, array $rows, ?string $userId): void
    {
        $this->repository->saveRegister(Carbon::parse($date), $classId, $rows, $userId);
    }
}
