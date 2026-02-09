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
        $query = LeaveRequest::where('user_id', $student->user_id)
            ->where('user_type', 'student');

        if ($status) {
            $query->where('status', $status);
        }

        // Order by start_date for pending requests (nearest first)
        // Order by created_at for approved/rejected requests (newest first)
        if ($status === 'pending') {
            $requests = $query->orderBy('start_date', 'asc')->get();
        } else {
            $requests = $query->orderBy('created_at', 'desc')->get();
        }

        return $requests->map(function ($request) {
            $today = Carbon::today();
            $startDate = $request->start_date ? Carbon::parse($request->start_date) : null;
            
            // Calculate days until leave starts
            $daysUntil = null;
            $isToday = false;
            $isPast = false;
            
            if ($startDate) {
                $daysUntil = $today->diffInDays($startDate, false);
                $isToday = $startDate->isToday();
                $isPast = $startDate->isPast() && !$isToday;
            }

            return [
                'id' => $request->id,
                'leave_type' => [
                    'id' => $this->getLeaveTypeId($request->leave_type),
                    'name' => $this->getLeaveTypeName($request->leave_type),
                    'icon' => $this->getLeaveTypeIcon($request->leave_type),
                ],
                'start_date' => $request->start_date?->format('Y-m-d'),
                'end_date' => $request->end_date?->format('Y-m-d'),
                'total_days' => $request->total_days ?? 1,
                'reason' => $request->reason,
                'status' => $request->status,
                'attachment' => $request->attachment ? asset($request->attachment) : null,
                'days_until' => $daysUntil,
                'is_today' => $isToday,
                'is_past' => $isPast,
                'created_at' => $request->created_at->toISOString(),
            ];
        })->toArray();
    }

    public function getLeaveRequestDetail(string $requestId): array
    {
        $request = LeaveRequest::with(['user', 'approvedBy'])->findOrFail($requestId);

        return [
            'id' => $request->id,
            'leave_type' => [
                'id' => $this->getLeaveTypeId($request->leave_type),
                'name' => $this->getLeaveTypeName($request->leave_type),
                'icon' => $this->getLeaveTypeIcon($request->leave_type),
            ],
            'start_date' => $request->start_date?->format('Y-m-d'),
            'end_date' => $request->end_date?->format('Y-m-d'),
            'total_days' => $request->total_days ?? 1,
            'reason' => $request->reason,
            'status' => $request->status,
            'attachment' => $request->attachment ? asset($request->attachment) : null,
            'approved_by' => $request->approvedBy ? [
                'name' => $request->approvedBy->name ?? 'N/A',
            ] : null,
            'approved_at' => $request->approved_at?->toISOString(),
            'rejection_reason' => $request->admin_remarks,
            'created_at' => $request->created_at->toISOString(),
        ];
    }

    public function getLeaveRequestDetailForStudent(string $requestId, string $studentId): ?array
    {
        // Get student's user_id
        $student = StudentProfile::find($studentId);
        if (!$student) {
            return null;
        }
        
        // Find leave request and verify it belongs to this student
        $request = LeaveRequest::with(['user', 'approvedBy'])
            ->where('id', $requestId)
            ->where('user_id', $student->user_id)
            ->where('user_type', 'student')
            ->first();

        if (!$request) {
            return null;
        }

        return [
            'id' => $request->id,
            'leave_type' => [
                'id' => $this->getLeaveTypeId($request->leave_type),
                'name' => $this->getLeaveTypeName($request->leave_type),
                'icon' => $this->getLeaveTypeIcon($request->leave_type),
            ],
            'start_date' => $request->start_date?->format('Y-m-d'),
            'end_date' => $request->end_date?->format('Y-m-d'),
            'total_days' => $request->total_days ?? 1,
            'reason' => $request->reason,
            'status' => $request->status,
            'attachment' => $request->attachment ? asset($request->attachment) : null,
            'approved_by' => $request->approvedBy ? [
                'name' => $request->approvedBy->name ?? 'N/A',
            ] : null,
            'approved_at' => $request->approved_at?->toISOString(),
            'rejection_reason' => $request->admin_remarks,
            'created_at' => $request->created_at->toISOString(),
        ];
    }

    public function createLeaveRequest(StudentProfile $student, string $guardianId, array $data): array
    {
        // Calculate total days
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);
        $totalDays = $startDate->diffInDays($endDate) + 1;

        $request = LeaveRequest::create([
            'user_id' => $student->user_id,
            'user_type' => 'student',
            'leave_type' => $data['leave_type'] ?? 'sick',
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'total_days' => $totalDays,
            'reason' => $data['reason'],
            'attachment' => $data['attachment_path'] ?? null,
            'status' => 'pending',
        ]);

        return $this->getLeaveRequestDetail($request->id);
    }

    public function createBulkLeaveRequests(array $studentIds, string $guardianId, array $data): array
    {
        $successful = [];
        $failed = [];

        // Calculate total days once
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);
        $totalDays = $startDate->diffInDays($endDate) + 1;

        // Validate date range
        if ($totalDays < 1) {
            foreach ($studentIds as $studentId) {
                $student = StudentProfile::find($studentId);
                $failed[] = [
                    'student_id' => $studentId,
                    'error' => 'Invalid date range',
                    'student_name' => $student ? $student->user->name ?? 'Unknown' : 'Unknown',
                ];
            }
            
            return [
                'successful' => $successful,
                'failed' => $failed,
                'summary' => [
                    'total' => count($studentIds),
                    'successful' => 0,
                    'failed' => count($failed),
                ],
            ];
        }

        foreach ($studentIds as $studentId) {
            try {
                // Find student
                $student = StudentProfile::find($studentId);
                
                if (!$student) {
                    $failed[] = [
                        'student_id' => $studentId,
                        'error' => 'Student not found',
                        'student_name' => 'Unknown',
                    ];
                    continue;
                }

                // Create leave request
                $request = LeaveRequest::create([
                    'user_id' => $student->user_id,
                    'user_type' => 'student',
                    'leave_type' => $data['leave_type'] ?? 'sick',
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'total_days' => $totalDays,
                    'reason' => $data['reason'],
                    'attachment' => $data['attachment_path'] ?? null,
                    'status' => 'pending',
                ]);

                $successful[] = [
                    'student_id' => $studentId,
                    'request_id' => $request->id,
                    'student_name' => $student->user->name ?? 'Unknown',
                ];
            } catch (\Exception $e) {
                $student = StudentProfile::find($studentId);
                $failed[] = [
                    'student_id' => $studentId,
                    'error' => $e->getMessage(),
                    'student_name' => $student ? $student->user->name ?? 'Unknown' : 'Unknown',
                ];
            }
        }

        return [
            'successful' => $successful,
            'failed' => $failed,
            'summary' => [
                'total' => count($studentIds),
                'successful' => count($successful),
                'failed' => count($failed),
            ],
        ];
    }

    public function updateLeaveRequest(string $requestId, array $data): array
    {
        $request = LeaveRequest::findOrFail($requestId);

        // Only allow updates if status is pending
        if ($request->status !== 'pending') {
            throw new \Exception('Cannot update a leave request that is not pending');
        }

        // Calculate total days if dates are being updated
        if (isset($data['start_date']) || isset($data['end_date'])) {
            $startDate = Carbon::parse($data['start_date'] ?? $request->start_date);
            $endDate = Carbon::parse($data['end_date'] ?? $request->end_date);
            $data['total_days'] = $startDate->diffInDays($endDate) + 1;
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
        $requests = LeaveRequest::where('user_id', $student->user_id)
            ->where('user_type', 'student')
            ->get();

        $approved = $requests->where('status', 'approved');
        $totalDaysTaken = $approved->sum('total_days');

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
                'value' => 'sick',
                'icon' => 'shield-heart',
                'max_days' => 10,
            ],
            [
                'id' => 2,
                'name' => 'Casual Leave',
                'value' => 'casual',
                'icon' => 'person',
                'max_days' => 5,
            ],
            [
                'id' => 3,
                'name' => 'Emergency Leave',
                'value' => 'emergency',
                'icon' => 'groups',
                'max_days' => 5,
            ],
            [
                'id' => 4,
                'name' => 'Other',
                'value' => 'other',
                'icon' => 'more-horiz',
                'max_days' => 5,
            ],
        ];
    }

    private function getLeaveTypeId(string $leaveType): int
    {
        $types = [
            'sick' => 1,
            'casual' => 2,
            'emergency' => 3,
            'other' => 4,
        ];

        return $types[$leaveType] ?? 4;
    }

    private function getLeaveTypeName(string $leaveType): string
    {
        $names = [
            'sick' => 'Sick Leave',
            'casual' => 'Casual Leave',
            'emergency' => 'Emergency Leave',
            'other' => 'Other',
        ];

        return $names[$leaveType] ?? 'Other';
    }

    private function getLeaveTypeIcon(?string $leaveType): string
    {
        $icons = [
            'sick' => 'shield-heart',
            'casual' => 'person',
            'emergency' => 'groups',
            'other' => 'more-horiz',
        ];

        return $icons[$leaveType] ?? 'more-horiz';
    }
}
