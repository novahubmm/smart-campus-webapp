<?php

namespace App\Repositories\Guardian;

use App\Interfaces\Guardian\GuardianLeaveRequestRepositoryInterface;
use App\Models\LeaveRequest;
use App\Models\StudentProfile;
use Carbon\Carbon;

class GuardianLeaveRequestRepository implements GuardianLeaveRequestRepositoryInterface
{
    public function getLeaveRequests(StudentProfile $student, ?string $status = null): array
    {
        $query = LeaveRequest::where('student_id', $student->id);

        if ($status) {
            $query->where('status', $status);
        }

        $requests = $query->orderBy('created_at', 'desc')->get();

        return $requests->map(function ($request) {
            return [
                'id' => $request->id,
                'leave_type' => [
                    'id' => $request->leave_type_id ?? 1,
                    'name' => $request->leave_type ?? 'General Leave',
                    'icon' => $this->getLeaveTypeIcon($request->leave_type),
                ],
                'start_date' => $request->start_date?->format('Y-m-d'),
                'end_date' => $request->end_date?->format('Y-m-d'),
                'total_days' => $request->start_date && $request->end_date 
                    ? $request->start_date->diffInDays($request->end_date) + 1 
                    : 1,
                'reason' => $request->reason,
                'status' => $request->status,
                'attachment' => $request->attachment_path ? asset($request->attachment_path) : null,
                'created_at' => $request->created_at->toISOString(),
            ];
        })->toArray();
    }

    public function getLeaveRequestDetail(string $requestId): array
    {
        $request = LeaveRequest::with(['student.user', 'approver.user'])->findOrFail($requestId);

        return [
            'id' => $request->id,
            'leave_type' => [
                'id' => $request->leave_type_id ?? 1,
                'name' => $request->leave_type ?? 'General Leave',
                'icon' => $this->getLeaveTypeIcon($request->leave_type),
            ],
            'start_date' => $request->start_date?->format('Y-m-d'),
            'end_date' => $request->end_date?->format('Y-m-d'),
            'total_days' => $request->start_date && $request->end_date 
                ? $request->start_date->diffInDays($request->end_date) + 1 
                : 1,
            'reason' => $request->reason,
            'status' => $request->status,
            'attachment' => $request->attachment_path ? asset($request->attachment_path) : null,
            'approved_by' => $request->approver ? [
                'name' => $request->approver->user?->name ?? 'N/A',
            ] : null,
            'approved_at' => $request->approved_at?->toISOString(),
            'rejection_reason' => $request->rejection_reason,
            'created_at' => $request->created_at->toISOString(),
        ];
    }

    public function createLeaveRequest(StudentProfile $student, string $guardianId, array $data): array
    {
        $request = LeaveRequest::create([
            'student_id' => $student->id,
            'guardian_id' => $guardianId,
            'leave_type' => $data['leave_type'] ?? 'General Leave',
            'leave_type_id' => $data['leave_type_id'] ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'reason' => $data['reason'],
            'attachment_path' => $data['attachment_path'] ?? null,
            'status' => 'pending',
        ]);

        return $this->getLeaveRequestDetail($request->id);
    }

    public function updateLeaveRequest(string $requestId, array $data): array
    {
        $request = LeaveRequest::findOrFail($requestId);

        // Only allow updates if status is pending
        if ($request->status !== 'pending') {
            throw new \Exception('Cannot update a leave request that is not pending');
        }

        $request->update($data);

        return $this->getLeaveRequestDetail($request->id);
    }

    public function deleteLeaveRequest(string $requestId): bool
    {
        $request = LeaveRequest::findOrFail($requestId);

        // Only allow deletion if status is pending
        if ($request->status !== 'pending') {
            throw new \Exception('Cannot delete a leave request that is not pending');
        }

        return $request->delete();
    }

    public function getLeaveStats(StudentProfile $student): array
    {
        $requests = LeaveRequest::where('student_id', $student->id)->get();

        $approved = $requests->where('status', 'approved');
        $totalDaysTaken = $approved->sum(function ($request) {
            if ($request->start_date && $request->end_date) {
                return $request->start_date->diffInDays($request->end_date) + 1;
            }
            return 1;
        });

        return [
            'total_requests' => $requests->count(),
            'approved' => $approved->count(),
            'pending' => $requests->where('status', 'pending')->count(),
            'rejected' => $requests->where('status', 'rejected')->count(),
            'total_days_taken' => $totalDaysTaken,
            'remaining_days' => max(0, 30 - $totalDaysTaken), // Assuming 30 days max per year
        ];
    }

    public function getLeaveTypes(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Sick Leave',
                'icon' => 'shield-heart',
                'max_days' => 10,
            ],
            [
                'id' => 2,
                'name' => 'Family Emergency',
                'icon' => 'groups',
                'max_days' => 5,
            ],
            [
                'id' => 3,
                'name' => 'Personal Leave',
                'icon' => 'person',
                'max_days' => 5,
            ],
            [
                'id' => 4,
                'name' => 'Medical Appointment',
                'icon' => 'local-hospital',
                'max_days' => 3,
            ],
            [
                'id' => 5,
                'name' => 'Other',
                'icon' => 'more-horiz',
                'max_days' => 5,
            ],
        ];
    }

    private function getLeaveTypeIcon(?string $leaveType): string
    {
        $icons = [
            'Sick Leave' => 'shield-heart',
            'Family Emergency' => 'groups',
            'Personal Leave' => 'person',
            'Medical Appointment' => 'local-hospital',
        ];

        return $icons[$leaveType] ?? 'more-horiz';
    }
}
