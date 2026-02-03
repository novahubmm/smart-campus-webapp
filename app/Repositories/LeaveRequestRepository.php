<?php

namespace App\Repositories;

use App\Interfaces\LeaveRequestRepositoryInterface;
use App\Models\LeaveRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class LeaveRequestRepository implements LeaveRequestRepositoryInterface
{
    private const STAFF_ROLES = ['teacher', 'staff'];
    private const LEAVE_TYPES = ['sick', 'casual', 'emergency', 'other'];

    public function getPendingStaffTeacher(array $filters = []): Collection
    {
        $query = LeaveRequest::with([
            'user.teacherProfile.department',
            'user.staffProfile.department',
        ])
            ->where('status', 'pending')
            ->whereIn('user_type', self::STAFF_ROLES)
            ->orderByDesc('created_at');

        $this->applyStaffFilters($query, $filters, false);

        return $query->get()->map(fn($leave) => $this->mapStaffTeacher($leave));
    }

    public function getHistoryStaffTeacher(array $filters = []): Collection
    {
        $query = LeaveRequest::with([
            'user.teacherProfile.department',
            'user.staffProfile.department',
            'approvedBy',
        ])
            ->whereIn('status', ['approved', 'rejected'])
            ->whereIn('user_type', self::STAFF_ROLES)
            ->orderByDesc('approved_at')
            ->orderByDesc('created_at');

        $this->applyStaffFilters($query, $filters, true);

        return $query->get()->map(fn($leave) => $this->mapStaffTeacher($leave));
    }

    public function getPendingStudents(array $filters = []): Collection
    {
        $query = LeaveRequest::with([
            'user.studentProfile.classModel',
            'user.studentProfile.grade',
        ])
            ->where('status', 'pending')
            ->where('user_type', 'student')
            ->orderByDesc('created_at');

        $this->applyStudentFilters($query, $filters, false);

        return $query->get()->map(fn($leave) => $this->mapStudent($leave));
    }

    public function getHistoryStudents(array $filters = []): Collection
    {
        $query = LeaveRequest::with([
            'user.studentProfile.classModel',
            'user.studentProfile.grade',
            'approvedBy',
        ])
            ->whereIn('status', ['approved', 'rejected'])
            ->where('user_type', 'student')
            ->orderByDesc('approved_at')
            ->orderByDesc('created_at');

        $this->applyStudentFilters($query, $filters, true);

        return $query->get()->map(fn($leave) => $this->mapStudent($leave));
    }

    public function getForUser(string $userId, array $filters = []): Collection
    {
        $query = LeaveRequest::where('user_id', $userId)
            ->orderByDesc('created_at');

        if (!empty($filters['date'])) {
            $query->whereDate('start_date', Carbon::parse($filters['date'])->toDateString());
        }

        return $query->get()->map(fn($leave) => $this->mapSelf($leave));
    }

    public function create(array $data): array
    {
        $payload = [
            'user_id' => $data['user_id'],
            'user_type' => $data['user_type'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'total_days' => $this->resolveDaysRaw($data['start_date'], $data['end_date']),
            'leave_type' => in_array($data['leave_type'], self::LEAVE_TYPES, true) ? $data['leave_type'] : 'other',
            'reason' => $data['reason'],
            'status' => 'pending',
            'attachment' => $data['attachment'] ?? null,
        ];

        $leave = LeaveRequest::create($payload);

        return $this->mapSelf($leave->fresh());
    }

    private function applyStaffFilters($query, array $filters, bool $includeStatus): void
    {
        if (!empty($filters['role']) && in_array($filters['role'], self::STAFF_ROLES, true)) {
            $query->where('user_type', $filters['role']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // For history, filter by approved_at date; for pending, filter by start_date
        if (!empty($filters['date'])) {
            if ($includeStatus) {
                // History: filter by when it was approved/rejected
                $query->whereDate('approved_at', Carbon::parse($filters['date'])->toDateString());
            } else {
                // Pending: filter by leave start date
                $query->whereDate('start_date', Carbon::parse($filters['date'])->toDateString());
            }
        }

        if ($includeStatus && !empty($filters['status']) && in_array($filters['status'], ['approved', 'rejected'], true)) {
            $query->where('status', $filters['status']);
        }
    }

    private function applyStudentFilters($query, array $filters, bool $includeStatus): void
    {
        if (!empty($filters['class_id'])) {
            $query->whereHas('user.studentProfile', function ($q) use ($filters) {
                $q->where('class_id', $filters['class_id']);
            });
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // For history, filter by approved_at date; for pending, filter by start_date
        if (!empty($filters['date'])) {
            if ($includeStatus) {
                // History: filter by when it was approved/rejected
                $query->whereDate('approved_at', Carbon::parse($filters['date'])->toDateString());
            } else {
                // Pending: filter by leave start date
                $query->whereDate('start_date', Carbon::parse($filters['date'])->toDateString());
            }
        }

        if ($includeStatus && !empty($filters['status']) && in_array($filters['status'], ['approved', 'rejected'], true)) {
            $query->where('status', $filters['status']);
        }
    }

    private function mapStaffTeacher(LeaveRequest $leave): array
    {
        $user = $leave->user;
        $profile = $leave->user_type === 'teacher'
            ? $user?->teacherProfile
            : $user?->staffProfile;

        $department = $profile?->department?->name;

        return [
            'id' => $leave->id,
            'reference' => $this->reference($leave),
            'name' => $user?->name ?? '—',
            'role' => ucfirst($leave->user_type),
            'department' => $department ?? '—',
            'submitted_at' => optional($leave->created_at)->toDateString(),
            'leave_type' => $this->formatLeaveType($leave->leave_type),
            'start_date' => optional($leave->start_date)->toDateString(),
            'end_date' => optional($leave->end_date)->toDateString(),
            'total_days' => $this->resolveDays($leave),
            'status' => $leave->status,
            'reason' => $leave->reason,
            'approved_by' => $leave->approvedBy?->name,
            'approved_at' => optional($leave->approved_at)->toDateString(),
        ];
    }

    private function mapStudent(LeaveRequest $leave): array
    {
        $user = $leave->user;
        $profile = $user?->studentProfile;
        $class = $profile?->classModel?->name;
        $grade = $profile?->grade?->name;
        $classDisplay = $class && $grade ? $grade . ' • ' . $class : ($class ?? $grade ?? '—');

        return [
            'id' => $leave->id,
            'reference' => $this->reference($leave),
            'name' => $user?->name ?? '—',
            'class' => $classDisplay,
            'submitted_at' => optional($leave->created_at)->toDateString(),
            'leave_type' => $this->formatLeaveType($leave->leave_type),
            'start_date' => optional($leave->start_date)->toDateString(),
            'end_date' => optional($leave->end_date)->toDateString(),
            'total_days' => $this->resolveDays($leave),
            'status' => $leave->status,
            'reason' => $leave->reason,
            'approved_by' => $leave->approvedBy?->name,
            'approved_at' => optional($leave->approved_at)->toDateString(),
        ];
    }

    private function mapSelf(LeaveRequest $leave): array
    {
        return [
            'id' => $leave->id,
            'reference' => $this->reference($leave),
            'leave_type' => $this->formatLeaveType($leave->leave_type),
            'start_date' => optional($leave->start_date)->toDateString(),
            'end_date' => optional($leave->end_date)->toDateString(),
            'total_days' => $this->resolveDays($leave),
            'status' => $leave->status,
            'submitted_at' => optional($leave->created_at)->toDateString(),
        ];
    }

    private function resolveDays(LeaveRequest $leave): int
    {
        if (!empty($leave->total_days)) {
            return (int) $leave->total_days;
        }

        if ($leave->start_date && $leave->end_date) {
            return Carbon::parse($leave->start_date)->diffInDays(Carbon::parse($leave->end_date)) + 1;
        }

        return 0;
    }

    private function resolveDaysRaw(string $start, string $end): int
    {
        return Carbon::parse($start)->diffInDays(Carbon::parse($end)) + 1;
    }

    private function formatLeaveType(?string $type): string
    {
        return $type ? Str::title(str_replace('_', ' ', $type)) : '—';
    }

    private function reference(LeaveRequest $leave): string
    {
        $prefix = 'LR';
        $year = optional($leave->created_at)->format('Y') ?? date('Y');
        $suffix = strtoupper(Str::substr($leave->id, 0, 6));
        return "$prefix-$year-$suffix";
    }
}
