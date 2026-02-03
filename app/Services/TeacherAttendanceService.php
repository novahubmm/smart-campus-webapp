<?php

namespace App\Services;

use App\Interfaces\TeacherAttendanceRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class TeacherAttendanceService
{
    public function __construct(private readonly TeacherAttendanceRepositoryInterface $repository) {}

    public function dailyRegister(string $date): Collection
    {
        return $this->repository->getDailyRegister(Carbon::parse($date));
    }

    public function monthlySummary(string $month): Collection
    {
        return $this->repository->getMonthlySummary(Carbon::parse($month));
    }

    public function summerSummary(string $year): Collection
    {
        $start = Carbon::parse($year . '-03-01');
        $end = Carbon::parse($year . '-05-31');
        return $this->repository->getRangeSummary($start, $end);
    }

    public function annualSummary(string $year): Collection
    {
        $start = Carbon::parse($year . '-06-01');
        $end = Carbon::parse(($year + 1) . '-02-28');
        return $this->repository->getRangeSummary($start, $end);
    }

    public function teacherDetail(string $teacherId, string $start, string $end): array
    {
        return $this->repository->getTeacherDetail($teacherId, Carbon::parse($start), Carbon::parse($end));
    }
}
