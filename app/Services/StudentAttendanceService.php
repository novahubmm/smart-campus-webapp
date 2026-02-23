<?php

namespace App\Services;

use App\DTOs\Attendance\StudentAttendanceFilterData;
use App\Interfaces\StudentAttendanceRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class StudentAttendanceService
{
    protected $notificationService;
    protected $guardianNotificationService;
    
    public function __construct(
        private readonly StudentAttendanceRepositoryInterface $repository,
        AttendanceNotificationService $notificationService,
        GuardianNotificationService $guardianNotificationService
    ) {
        $this->notificationService = $notificationService;
        $this->guardianNotificationService = $guardianNotificationService;
    }

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
        
        // Check if this is the first attendance of the day (period 1)
        $isFirstAttendance = $this->isFirstAttendanceOfDay($rows);
        
        // Send notifications for each student
        foreach ($rows as $row) {
            try {
                // Send regular attendance notification
                $this->notificationService->sendAttendanceNotification(
                    $row['student_id'],
                    $row['status'],
                    $date
                );
                
                // Send first attendance notification if this is period 1 and student is present
                if ($isFirstAttendance && $row['status'] === 'present') {
                    $student = \App\Models\StudentProfile::with('user')->find($row['student_id']);
                    if ($student) {
                        $this->guardianNotificationService->sendFirstAttendanceNotification(
                            $student->id,
                            $student->user?->name ?? 'Student',
                            $date
                        );
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send attendance notification', [
                    'error' => $e->getMessage(),
                    'student_id' => $row['student_id'],
                ]);
            }
        }
    }
    
    /**
     * Check if this is the first attendance of the day
     */
    private function isFirstAttendanceOfDay(array $rows): bool
    {
        // Check if any row has period_id or if it's period 1
        foreach ($rows as $row) {
            if (isset($row['period_id'])) {
                $period = \App\Models\Period::find($row['period_id']);
                if ($period && $period->period_number === 1) {
                    return true;
                }
            }
        }
        return false;
    }
}
