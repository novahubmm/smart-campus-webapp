<?php

namespace App\Http\Controllers\Api\V1\Guardian;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Interfaces\Guardian\GuardianLeaveRequestRepositoryInterface;
use App\Models\StudentProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function __construct(
        private readonly GuardianLeaveRequestRepositoryInterface $leaveRequestRepository
    ) {}

    /**
     * Get Leave Requests
     * GET /api/v1/guardian/leave-requests?student_id={id}&status={status}
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|string',
            'status' => 'nullable|string|in:pending,approved,rejected',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $status = $request->input('status');
            $requests = $this->leaveRequestRepository->getLeaveRequests($student, $status);

            return ApiResponse::success($requests);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve leave requests: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Leave Request Detail
     * GET /api/v1/guardian/leave-requests/{id}
     */
    public function show(string $id): JsonResponse
    {
        try {
            $request = $this->leaveRequestRepository->getLeaveRequestDetail($id);

            return ApiResponse::success($request);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve leave request: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create Leave Request
     * POST /api/v1/guardian/leave-requests
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|string',
            'leave_type' => 'nullable|string',
            'leave_type_id' => 'nullable|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $guardianId = $request->user()->guardianProfile->id;
            $leaveRequest = $this->leaveRequestRepository->createLeaveRequest(
                $student,
                $guardianId,
                $request->all()
            );

            return ApiResponse::success($leaveRequest, 'Leave request submitted successfully', 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to create leave request: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update Leave Request
     * PUT /api/v1/guardian/leave-requests/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'leave_type' => 'nullable|string',
            'leave_type_id' => 'nullable|integer',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'reason' => 'sometimes|string|max:1000',
        ]);

        try {
            $leaveRequest = $this->leaveRequestRepository->updateLeaveRequest($id, $request->all());

            return ApiResponse::success($leaveRequest, 'Leave request updated successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to update leave request: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete Leave Request
     * DELETE /api/v1/guardian/leave-requests/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->leaveRequestRepository->deleteLeaveRequest($id);

            return ApiResponse::success(null, 'Leave request deleted successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to delete leave request: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Leave Stats
     * GET /api/v1/guardian/leave-requests/stats?student_id={id}
     */
    public function stats(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|string',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $stats = $this->leaveRequestRepository->getLeaveStats($student);

            return ApiResponse::success($stats);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve leave stats: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Leave Types
     * GET /api/v1/guardian/leave-types
     */
    public function leaveTypes(): JsonResponse
    {
        try {
            $types = $this->leaveRequestRepository->getLeaveTypes();

            return ApiResponse::success($types);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve leave types: ' . $e->getMessage(), 500);
        }
    }

    private function getAuthorizedStudent(Request $request): ?StudentProfile
    {
        $studentId = $request->input('student_id');
        if (!$studentId) {
            return null;
        }

        $user = $request->user();
        $guardianProfile = $user->guardianProfile;

        if (!$guardianProfile) {
            return null;
        }

        return $guardianProfile->students()
            ->where('student_profiles.id', $studentId)
            ->with(['user', 'grade', 'classModel'])
            ->first();
    }
}
