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
     * GET /api/v1/guardian/students/{student_id}/leave-requests?status={status} (NEW)
     * GET /api/v1/guardian/students/{student_id}/leave-requests?request_uuid={uuid} (Detail via query param)
     */
    public function index(Request $request, ?string $studentId = null): JsonResponse
    {
        $request->validate([
            'student_id' => $studentId ? 'nullable|string' : 'required|string',
            'status' => 'nullable|string|in:pending,approved,rejected',
            'request_uuid' => 'nullable|string',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request, $studentId);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            // Check if requesting a specific leave request detail via query parameter
            $requestUuid = $request->input('request_uuid');
            if ($requestUuid) {
                // Return single leave request detail
                $leaveRequest = $this->leaveRequestRepository->getLeaveRequestDetailForStudent($requestUuid, $student->id);
                
                if (!$leaveRequest) {
                    return ApiResponse::error('Leave request not found', 404);
                }
                
                return ApiResponse::success($leaveRequest);
            }

            // Return list of leave requests
            $status = $request->input('status');
            $requests = $this->leaveRequestRepository->getLeaveRequests($student, $status);

            return ApiResponse::success($requests);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve leave requests: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Leave Request Detail
     * GET /api/v1/guardian/leave-requests/{id} (OLD - Deprecated)
     * GET /api/v1/guardian/students/{student_id}/leave-requests/{request_id} (NEW)
     */
    public function show(Request $request, string $studentIdOrRequestId, ?string $requestId = null): JsonResponse
    {
        try {
            // Determine if this is the new RESTful route or old route
            if ($requestId === null) {
                // OLD route: /leave-requests/{id}
                // $studentIdOrRequestId is actually the request_id
                $actualRequestId = $studentIdOrRequestId;
                
                // Get student_id from query parameter for old route
                $studentId = $request->input('student_id');
                if (!$studentId) {
                    return ApiResponse::error('student_id parameter is required', 400);
                }
            } else {
                // NEW route: /students/{student_id}/leave-requests/{request_id}
                $studentId = $studentIdOrRequestId;
                $actualRequestId = $requestId;
            }

            // Verify student authorization
            $student = $this->getAuthorizedStudent($request, $studentId);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            // Get leave request detail with authorization check
            $leaveRequest = $this->leaveRequestRepository->getLeaveRequestDetailForStudent($actualRequestId, $student->id);

            if (!$leaveRequest) {
                return ApiResponse::error('Leave request not found', 404);
            }

            return ApiResponse::success($leaveRequest);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve leave request: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create Leave Request
     * POST /api/v1/guardian/leave-requests
     * POST /api/v1/guardian/students/{student_id}/leave-requests (NEW)
     */
    public function store(Request $request, ?string $studentId = null): JsonResponse
        {
            $request->validate([
                'student_id' => $studentId ? 'nullable|string' : 'required|string',
                'leave_type' => 'required|string|in:sick,personal,emergency,other',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'required|string|max:1000',
                'attachment' => 'nullable|file|image|max:5120', // File upload (max 5MB)
                'attachment_base64' => 'nullable|string', // Base64 encoded image (alternative)
            ]);

            try {
                $student = $this->getAuthorizedStudent($request, $studentId);
                if (!$student) {
                    return ApiResponse::error('Student not found or unauthorized', 404);
                }

                $data = $request->all();

                // Handle file upload attachment
                if ($request->hasFile('attachment')) {
                    try {
                        $attachmentPath = $this->saveUploadedFile($request->file('attachment'), $student->id);
                        $data['attachment_path'] = $attachmentPath;
                    } catch (\Exception $e) {
                        return ApiResponse::error('Invalid file attachment: ' . $e->getMessage(), 422);
                    }
                }
                // Handle base64 image attachment (fallback for backward compatibility)
                elseif ($request->has('attachment_base64') && !empty($request->attachment_base64)) {
                    try {
                        $attachmentPath = $this->saveBase64Image($request->attachment_base64, $student->id);
                        $data['attachment_path'] = $attachmentPath;
                    } catch (\Exception $e) {
                        return ApiResponse::error('Invalid image attachment: ' . $e->getMessage(), 422);
                    }
                }

                $guardianId = $request->user()->guardianProfile->id;
                $leaveRequest = $this->leaveRequestRepository->createLeaveRequest(
                    $student,
                    $guardianId,
                    $data
                );

                return ApiResponse::success($leaveRequest, 'Leave request submitted successfully', 201);
            } catch (\Exception $e) {
                return ApiResponse::error('Failed to create leave request: ' . $e->getMessage(), 500);
            }
        }

    /**
     * Create Bulk Leave Requests
     * POST /api/v1/guardian/leave-requests/bulk
     */
    public function bulkStore(Request $request): JsonResponse
        {
            $request->validate([
                'student_ids' => 'required|array|min:1',
                'student_ids.*' => 'required|string',
                'leave_type' => 'required|string|in:sick,personal,emergency,other',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'required|string|max:1000',
                'attachment' => 'nullable|string', // Base64 encoded image
            ]);

            try {
                $user = $request->user();
                $guardianProfile = $user->guardianProfile;

                if (!$guardianProfile) {
                    return ApiResponse::error('Guardian profile not found', 404);
                }

                // Verify all students belong to this guardian
                $studentIds = $request->input('student_ids');
                $authorizedStudentIds = $guardianProfile->students()
                    ->whereIn('student_profiles.id', $studentIds)
                    ->pluck('student_profiles.id')
                    ->toArray();

                // Check for unauthorized students
                $unauthorizedStudents = array_diff($studentIds, $authorizedStudentIds);

                if (!empty($unauthorizedStudents)) {
                    return ApiResponse::error(
                        'Some students are not authorized: ' . implode(', ', $unauthorizedStudents),
                        403
                    );
                }

                $data = $request->all();

                // Handle base64 image attachment (use first student ID for filename)
                if ($request->has('attachment') && !empty($request->attachment)) {
                    try {
                        $attachmentPath = $this->saveBase64Image($request->attachment, $authorizedStudentIds[0]);
                        $data['attachment_path'] = $attachmentPath;
                    } catch (\Exception $e) {
                        return ApiResponse::error('Invalid image attachment: ' . $e->getMessage(), 422);
                    }
                }

                $guardianId = $guardianProfile->id;
                $result = $this->leaveRequestRepository->createBulkLeaveRequests(
                    $authorizedStudentIds,
                    $guardianId,
                    $data
                );

                // Determine response based on results
                $successCount = $result['summary']['successful'];
                $failCount = $result['summary']['failed'];
                $totalCount = $result['summary']['total'];

                if ($successCount === 0) {
                    // All failed
                    return ApiResponse::error('Failed to create leave requests', 400, $result);
                } elseif ($failCount === 0) {
                    // All successful
                    return ApiResponse::success(
                        $result,
                        "Leave requests created for {$successCount} student" . ($successCount > 1 ? 's' : ''),
                        201
                    );
                } else {
                    // Partial success
                    return ApiResponse::success(
                        $result,
                        "Leave requests created for {$successCount} out of {$totalCount} students",
                        201
                    );
                }
            } catch (\Exception $e) {
                return ApiResponse::error('Failed to create bulk leave requests: ' . $e->getMessage(), 500);
            }
        }

    /**
     * Update Leave Request
     * PUT /api/v1/guardian/leave-requests/{id} (OLD - Deprecated)
     * PUT /api/v1/guardian/students/{student_id}/leave-requests/{request_id} (NEW)
     * PUT /api/v1/guardian/students/{student_id}/leave-requests?request_uuid={uuid} (Query param)
     */
    public function update(Request $request, string $studentIdOrRequestId, ?string $requestId = null): JsonResponse
        {
            $request->validate([
                'leave_type' => 'sometimes|string|in:sick,personal,emergency,other',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date|after_or_equal:start_date',
                'reason' => 'sometimes|string|max:1000',
                'request_uuid' => 'nullable|string',
                'attachment' => 'nullable|string', // Base64 encoded image
            ]);

            try {
                // Check if using query parameter for request_uuid
                $requestUuid = $request->input('request_uuid');

                if ($requestUuid && $requestId === null) {
                    // Query param route: /students/{student_id}/leave-requests?request_uuid={uuid}
                    $studentId = $studentIdOrRequestId;
                    $actualRequestId = $requestUuid;
                } elseif ($requestId === null) {
                    // OLD route: /leave-requests/{id}
                    $actualRequestId = $studentIdOrRequestId;
                    $studentId = $request->input('student_id');
                    if (!$studentId) {
                        return ApiResponse::error('student_id parameter is required', 400);
                    }
                } else {
                    // NEW route: /students/{student_id}/leave-requests/{request_id}
                    $studentId = $studentIdOrRequestId;
                    $actualRequestId = $requestId;
                }

                // Verify student authorization
                $student = $this->getAuthorizedStudent($request, $studentId);
                if (!$student) {
                    return ApiResponse::error('Student not found or unauthorized', 404);
                }

                $data = $request->all();

                // Handle base64 image attachment
                if ($request->has('attachment') && !empty($request->attachment)) {
                    try {
                        $attachmentPath = $this->saveBase64Image($request->attachment, $student->id);
                        $data['attachment_path'] = $attachmentPath;
                    } catch (\Exception $e) {
                        return ApiResponse::error('Invalid image attachment: ' . $e->getMessage(), 422);
                    }
                }

                $leaveRequest = $this->leaveRequestRepository->updateLeaveRequest($actualRequestId, $data);

                return ApiResponse::success($leaveRequest, 'Leave request updated successfully');
            } catch (\Exception $e) {
                return ApiResponse::error('Failed to update leave request: ' . $e->getMessage(), 500);
            }
        }

    /**
     * Delete Leave Request
     * DELETE /api/v1/guardian/leave-requests/{id} (OLD - Deprecated)
     * DELETE /api/v1/guardian/students/{student_id}/leave-requests/{request_id} (NEW)
     * DELETE /api/v1/guardian/students/{student_id}/leave-requests?request_uuid={uuid} (Query param)
     */
    public function destroy(Request $request, string $studentIdOrRequestId, ?string $requestId = null): JsonResponse
    {
        $request->validate([
            'request_uuid' => 'nullable|string',
        ]);

        try {
            // Check if using query parameter for request_uuid
            $requestUuid = $request->input('request_uuid');
            
            if ($requestUuid && $requestId === null) {
                // Query param route: /students/{student_id}/leave-requests?request_uuid={uuid}
                $studentId = $studentIdOrRequestId;
                $actualRequestId = $requestUuid;
            } elseif ($requestId === null) {
                // OLD route: /leave-requests/{id}
                $actualRequestId = $studentIdOrRequestId;
                $studentId = $request->input('student_id');
                if (!$studentId) {
                    return ApiResponse::error('student_id parameter is required', 400);
                }
            } else {
                // NEW route: /students/{student_id}/leave-requests/{request_id}
                $studentId = $studentIdOrRequestId;
                $actualRequestId = $requestId;
            }

            // Verify student authorization
            $student = $this->getAuthorizedStudent($request, $studentId);
            if (!$student) {
                return ApiResponse::error('Student not found or unauthorized', 404);
            }

            $this->leaveRequestRepository->deleteLeaveRequest($actualRequestId);

            return ApiResponse::success(null, 'Leave request deleted successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to delete leave request: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Leave Stats
     * GET /api/v1/guardian/leave-requests/stats?student_id={id}
     * GET /api/v1/guardian/students/{student_id}/leave-requests/stats (NEW)
     */
    public function stats(Request $request, ?string $studentId = null): JsonResponse
    {
        $request->validate([
            'student_id' => $studentId ? 'nullable|string' : 'required|string',
        ]);

        try {
            $student = $this->getAuthorizedStudent($request, $studentId);
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

    private function getAuthorizedStudent(Request $request, ?string $studentId = null): ?StudentProfile
    {
        // Use URL parameter if provided, otherwise fall back to query parameter
        $studentId = $studentId ?? $request->input('student_id');
        
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

    /**
     * Save base64 encoded image to storage
     * 
     * @param string $base64String Base64 encoded image with data URI prefix
     * @param string $studentId Student ID for organizing files
     * @return string Path to saved file
     * @throws \Exception If image is invalid
     */
    private function saveBase64Image(string $base64String, string $studentId): string
    {
        // Extract base64 data and mime type
        if (!preg_match('/^data:image\/(\w+);base64,/', $base64String, $matches)) {
            throw new \Exception('Invalid base64 image format');
        }

        $imageType = $matches[1];
        $allowedTypes = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
        
        if (!in_array(strtolower($imageType), $allowedTypes)) {
            throw new \Exception('Invalid image type. Allowed: ' . implode(', ', $allowedTypes));
        }

        // Remove the data URI prefix
        $base64Data = substr($base64String, strpos($base64String, ',') + 1);
        $imageData = base64_decode($base64Data);

        if ($imageData === false) {
            throw new \Exception('Failed to decode base64 image');
        }

        // Validate image size (max 5MB)
        $maxSize = 5 * 1024 * 1024; // 5MB
        if (strlen($imageData) > $maxSize) {
            throw new \Exception('Image size exceeds 5MB limit');
        }

        // Generate unique filename
        $filename = 'leave_' . $studentId . '_' . time() . '_' . uniqid() . '.' . $imageType;
        $path = 'leave_attachments/' . date('Y/m');
        
        // Save to storage
        \Storage::disk('public')->put($path . '/' . $filename, $imageData);

        return $path . '/' . $filename;
    }
}
