<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\Period;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    /**
     * Get Teacher's Leave Requests
     * GET /api/v1/teacher/my-leave-requests
     */
    public function myRequests(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $teacherProfile = $user->teacherProfile;

            if (!$teacherProfile) {
                return ApiResponse::error('Teacher profile not found', 404);
            }

            $requests = LeaveRequest::where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->paginate(10);

            // Get actual leave balance from database
            $leaveBalance = $this->getUserLeaveBalance($user->id);

            $counts = [
                'pending' => LeaveRequest::where('user_id', $user->id)->where('status', 'pending')->count(),
                'approved' => LeaveRequest::where('user_id', $user->id)->where('status', 'approved')->count(),
                'rejected' => LeaveRequest::where('user_id', $user->id)->where('status', 'rejected')->count(),
            ];

            $requestsData = $requests->map(function ($req) {
                $startDate = Carbon::parse($req->start_date);
                $endDate = Carbon::parse($req->end_date);
                $days = $startDate->diffInDays($endDate) + 1;

                return [
                    'id' => $req->id,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'reason' => $req->reason,
                    'days' => $days,
                    'type' => ucfirst($req->leave_type ?? 'Casual'),
                    'status' => $req->status,
                    'submitted_date' => $req->created_at->format('Y-m-d'),
                    'approved_by' => $req->approved_by ? 'Admin' : null,
                    'approved_date' => $req->approved_at?->format('Y-m-d'),
                    'rejected_by' => $req->rejected_by ? 'Admin' : null,
                    'rejected_date' => $req->rejected_at?->format('Y-m-d'),
                    'rejection_reason' => $req->rejection_reason,
                ];
            });

            return ApiResponse::success([
                'leave_balance' => $leaveBalance,
                'counts' => $counts,
                'requests' => $requestsData->toArray(),
                'pagination' => [
                    'current_page' => $requests->currentPage(),
                    'last_page' => $requests->lastPage(),
                    'per_page' => $requests->perPage(),
                    'total' => $requests->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve leave requests: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Leave Request Detail
     * GET /api/v1/teacher/my-leave-requests/{id}
     */
    public function myRequestDetail(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();

            $leaveRequest = LeaveRequest::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$leaveRequest) {
                return ApiResponse::notFound('Leave request not found');
            }

            $startDate = Carbon::parse($leaveRequest->start_date);
            $endDate = Carbon::parse($leaveRequest->end_date);
            $days = $startDate->diffInDays($endDate) + 1;

            return ApiResponse::success([
                'id' => $leaveRequest->id,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'reason' => $leaveRequest->reason,
                'days' => $days,
                'type' => ucfirst($leaveRequest->leave_type ?? 'Casual'),
                'status' => $leaveRequest->status,
                'submitted_date' => $leaveRequest->created_at->format('Y-m-d'),
                'approved_by' => $leaveRequest->approved_by ? 'Admin' : null,
                'approved_date' => $leaveRequest->approved_at?->format('Y-m-d'),
                'rejected_by' => $leaveRequest->rejected_by ? 'Admin' : null,
                'rejected_date' => $leaveRequest->rejected_at?->format('Y-m-d'),
                'rejection_reason' => $leaveRequest->rejection_reason,
                'attachments' => [],
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve leave request: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Apply for Leave
     * POST /api/v1/teacher/my-leave-requests
     */
    public function applyLeave(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'type' => 'required|string|in:Casual,Medical,Earned',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'required|string|max:500',
            ]);

            $user = $request->user();

            $startDate = Carbon::parse($request->input('start_date'));
            $endDate = Carbon::parse($request->input('end_date'));
            $totalDays = $startDate->diffInDays($endDate) + 1;

            $leaveRequest = LeaveRequest::create([
                'user_id' => $user->id,
                'user_type' => 'teacher',
                'leave_type' => strtolower($request->input('type')),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'total_days' => $totalDays,
                'reason' => $request->input('reason'),
                'status' => 'pending',
            ]);

            return ApiResponse::success([
                'id' => $leaveRequest->id,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'reason' => $leaveRequest->reason,
                'days' => $totalDays,
                'type' => ucfirst($leaveRequest->leave_type),
                'status' => 'pending',
                'submitted_date' => $leaveRequest->created_at->format('Y-m-d'),
            ], 'Leave request submitted successfully', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to submit leave request: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Leave Balance Only
     * GET /api/v1/teacher/leave-balance
     */
    public function leaveBalance(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $balance = $this->getUserLeaveBalance($user->id);

            $totalUsed = ($balance['casual']['used'] ?? 0) + ($balance['medical']['used'] ?? 0) + ($balance['earned']['used'] ?? 0);
            $totalAvailable = ($balance['casual']['total'] ?? 0) + ($balance['medical']['total'] ?? 0) + ($balance['earned']['total'] ?? 0);

            return ApiResponse::success([
                'casual' => $balance['casual'],
                'medical' => $balance['medical'],
                'earned' => $balance['earned'],
                'total_used' => $totalUsed,
                'total_available' => $totalAvailable,
                'total_remaining' => $totalAvailable - $totalUsed,
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve leave balance: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get user's leave balance from database
     */
    private function getUserLeaveBalance(string $userId): array
    {
        $leaveTypes = ['casual', 'medical', 'earned'];
        $balance = [];

        foreach ($leaveTypes as $type) {
            $leaveBalance = LeaveBalance::getOrCreateForUser($userId, $type);
            $balance[$type] = [
                'used' => $leaveBalance->used_days,
                'total' => $leaveBalance->total_days,
                'remaining' => $leaveBalance->remaining_days,
            ];
        }

        return $balance;
    }

    /**
     * Get Pending Leave Requests (Students - Dashboard)
     * GET /api/v1/teacher/leave-requests/pending
     */
    public function pendingRequests(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $teacherProfile = $user->teacherProfile;

            if (!$teacherProfile) {
                return ApiResponse::success(['total_pending' => 0, 'requests' => []]);
            }

            // Get classes taught by this teacher
            $classIds = Period::where('teacher_profile_id', $teacherProfile->id)
                ->whereHas('timetable', fn($q) => $q->where('is_active', true))
                ->with('timetable')
                ->get()
                ->pluck('timetable.class_id')
                ->unique()
                ->filter();

            // Get pending leave requests from students in those classes
            // Order by start_date ASC to show nearest dates first
            $requests = LeaveRequest::where('status', 'pending')
                ->where('user_type', 'student')
                ->whereHas('user.studentProfile', fn($q) => $q->whereIn('class_id', $classIds))
                ->with(['user.studentProfile.classModel.grade'])
                ->orderBy('start_date', 'asc')
                ->limit(5)
                ->get();

            $requestsData = $requests->map(function ($req) {
                $student = $req->user?->studentProfile;
                $startDate = Carbon::parse($req->start_date);
                $today = Carbon::today();
                
                // Calculate days until leave starts
                $daysUntil = $today->diffInDays($startDate, false);
                $isToday = $startDate->isToday();
                
                return [
                    'id' => $req->id,
                    'name' => $req->user?->name ?? 'Unknown',
                    'initial' => $req->user?->name ? strtoupper(substr($req->user->name, 0, 1)) : 'U',
                    'avatar' => avatar_url($student?->photo_path, 'student'),
                    'student_id' => $student?->student_identifier ?? '',
                    'grade' => $student?->classModel?->grade?->name . ($student?->classModel?->name ? ' ' . $student->classModel->name : ''),
                    'type' => ucfirst($req->leave_type ?? 'Personal'),
                    'date' => $startDate->format('Y-m-d'),
                    'days_until' => $daysUntil,
                    'is_today' => $isToday,
                ];
            });

            return ApiResponse::success([
                'total_pending' => $requests->count(),
                'requests' => $requestsData->toArray(),
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve pending requests: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get All Student Leave Requests
     * GET /api/v1/teacher/leave-requests
     */
    public function studentRequests(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $teacherProfile = $user->teacherProfile;
            $status = $request->input('status');

            if (!$teacherProfile) {
                return ApiResponse::success(['counts' => ['pending' => 0, 'approved' => 0, 'rejected' => 0], 'requests' => []]);
            }

            // Get classes taught by this teacher
            $classIds = Period::where('teacher_profile_id', $teacherProfile->id)
                ->whereHas('timetable', fn($q) => $q->where('is_active', true))
                ->with('timetable')
                ->get()
                ->pluck('timetable.class_id')
                ->unique()
                ->filter();

            $query = LeaveRequest::where('user_type', 'student')
                ->whereHas('user.studentProfile', fn($q) => $q->whereIn('class_id', $classIds))
                ->with(['user.studentProfile.classModel.grade']);

            if ($status && $status !== 'all') {
                $query->where('status', $status);
            }

            // Order by start_date for pending requests (nearest first)
            // Order by created_at for approved/rejected requests (newest first)
            if ($status === 'pending') {
                $requests = $query->orderBy('start_date', 'asc')->paginate(10);
            } else {
                $requests = $query->orderByDesc('created_at')->paginate(10);
            }

            $counts = [
                'pending' => LeaveRequest::where('user_type', 'student')->whereHas('user.studentProfile', fn($q) => $q->whereIn('class_id', $classIds))->where('status', 'pending')->count(),
                'approved' => LeaveRequest::where('user_type', 'student')->whereHas('user.studentProfile', fn($q) => $q->whereIn('class_id', $classIds))->where('status', 'approved')->count(),
                'rejected' => LeaveRequest::where('user_type', 'student')->whereHas('user.studentProfile', fn($q) => $q->whereIn('class_id', $classIds))->where('status', 'rejected')->count(),
            ];

            $requestsData = $requests->map(function ($req) use ($status) {
                $student = $req->user?->studentProfile;
                $startDate = Carbon::parse($req->start_date);
                $endDate = Carbon::parse($req->end_date);
                $days = $startDate->diffInDays($endDate) + 1;
                
                $data = [
                    'id' => $req->id,
                    'student_name' => $req->user?->name ?? 'Unknown',
                    'grade' => $student?->classModel?->grade?->name . ($student?->classModel?->name ? ' ' . $student->classModel->name : ''),
                    'student_id' => $student?->student_identifier ?? '',
                    'reason' => $req->reason,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'duration' => $days . ' day' . ($days > 1 ? 's' : ''),
                    'type' => ucfirst($req->leave_type ?? 'Personal'),
                    'status' => $req->status,
                    'submitted_by' => 'Guardian',
                    'avatar' => avatar_url($req->user?->studentProfile?->photo_path, 'student'),
                    'submitted_at' => $req->created_at->toISOString(),
                ];
                
                // Add days_until and is_today for pending requests
                if ($status === 'pending') {
                    $today = Carbon::today();
                    $daysUntil = $today->diffInDays($startDate, false);
                    $data['days_until'] = $daysUntil;
                    $data['is_today'] = $startDate->isToday();
                }

                return $data;
            });

            return ApiResponse::success([
                'counts' => $counts,
                'requests' => $requestsData->toArray(),
                'pagination' => [
                    'current_page' => $requests->currentPage(),
                    'last_page' => $requests->lastPage(),
                    'per_page' => $requests->perPage(),
                    'total' => $requests->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve leave requests: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Approve Leave Request
     * POST /api/v1/teacher/leave-requests/{id}/approve
     */
    public function approve(Request $request, string $id): JsonResponse
    {
        try {
            $leaveRequest = LeaveRequest::find($id);

            if (!$leaveRequest) {
                return ApiResponse::notFound('Leave request not found');
            }

            $leaveRequest->update([
                'status' => 'approved',
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
                'remarks' => $request->input('remarks'),
            ]);

            return ApiResponse::success([
                'id' => $leaveRequest->id,
                'status' => 'approved',
                'approved_at' => now()->toISOString(),
                'approved_by' => $request->user()->name,
            ], 'Leave request approved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to approve leave request: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Reject Leave Request
     * POST /api/v1/teacher/leave-requests/{id}/reject
     */
    public function reject(Request $request, string $id): JsonResponse
    {
        try {
            $request->validate([
                'remarks' => 'required|string|max:500',
            ]);

            $leaveRequest = LeaveRequest::find($id);

            if (!$leaveRequest) {
                return ApiResponse::notFound('Leave request not found');
            }

            $leaveRequest->update([
                'status' => 'rejected',
                'rejected_by' => $request->user()->id,
                'rejected_at' => now(),
                'rejection_reason' => $request->input('remarks'),
            ]);

            return ApiResponse::success([
                'id' => $leaveRequest->id,
                'status' => 'rejected',
                'rejected_at' => now()->toISOString(),
                'rejected_by' => $request->user()->name,
            ], 'Leave request rejected');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to reject leave request: ' . $e->getMessage(), 500);
        }
    }
}
