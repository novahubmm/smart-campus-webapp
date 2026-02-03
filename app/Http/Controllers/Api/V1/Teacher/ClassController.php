<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Http\Controllers\Controller;
use App\Interfaces\Teacher\TeacherClassRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function __construct(
        private readonly TeacherClassRepositoryInterface $classRepository
    ) {}

    /**
     * 1. Get all classes for the teacher
     */
    public function index(Request $request): JsonResponse
    {
        $result = $this->classRepository->getMyClasses($request->user());

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * 2. Get class detail
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $result = $this->classRepository->getClassDetail($request->user(), $id);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Class not found or access denied',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * 3. Get class students with optional filtering
     */
    public function students(Request $request, string $id): JsonResponse
    {
        $search = $request->query('search');
        $gender = $request->query('gender');

        $result = $this->classRepository->getClassStudents($request->user(), $id, $search, $gender);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Class not found or access denied',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Get specific student detail within a class context
     */
    public function studentDetail(Request $request, string $classId, string $studentId): JsonResponse
    {
        $result = $this->classRepository->getClassStudentDetail($request->user(), $classId, $studentId);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found or access denied',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * 4. Get class teachers
     */
    public function teachers(Request $request, string $id): JsonResponse
    {
        $result = $this->classRepository->getClassTeachers($request->user(), $id);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Class not found or access denied',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * 5. Get class timetable
     */
    public function timetable(Request $request, string $id): JsonResponse
    {
        $date = $request->query('date');
        
        $result = $this->classRepository->getClassTimetable($request->user(), $id, $date);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Class not found or access denied',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * 6. Get class rankings
     */
    public function rankings(Request $request, string $id): JsonResponse
    {
        $examId = $request->query('exam_id');
        $examType = $request->query('exam_type');

        $result = $this->classRepository->getClassRankings($request->user(), $id, $examId, $examType);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Class not found or access denied',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * 7. Get exam options for rankings dropdown
     */
    public function exams(Request $request, string $id): JsonResponse
    {
        $result = $this->classRepository->getClassExams($request->user(), $id);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Class not found or access denied',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * 9. Assign class leader
     */
    public function assignClassLeader(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|string',
        ]);

        $result = $this->classRepository->assignClassLeader($request->user(), $id, $request->input('student_id'));

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign class leader',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Class leader assigned successfully',
            'data' => $result,
        ]);
    }

    /**
     * 10. Get switch requests
     */
    public function switchRequests(Request $request, string $id): JsonResponse
    {
        $status = $request->query('status');
        $type = $request->query('type');

        $result = $this->classRepository->getSwitchRequests($request->user(), $id, $status, $type);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Class not found or access denied',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * 11. Create switch request
     */
    public function createSwitchRequest(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'period_id' => 'required|uuid|exists:periods,id',
            'request_subject' => 'required|uuid|exists:subjects,id',
            'date' => 'required|date',
            'reason' => 'nullable|string',
        ]);

        $result = $this->classRepository->createSwitchRequest($request->user(), $id, $request->only([
            'period_id', 'request_subject', 'date', 'reason'
        ]));

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create switch request',
            ], 400);
        }

        // Check for validation error
        if (isset($result['error'])) {
            $message = match($result['error']) {
                'cannot_request_own_period' => 'You cannot request to switch your own period',
                default => 'Failed to create switch request',
            };
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Switch request sent successfully',
            'data' => $result,
        ]);
    }

    /**
     * 12. Respond to switch request
     */
    public function respondToSwitchRequest(Request $request, string $id, string $requestId): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:accepted,rejected',
        ]);

        $result = $this->classRepository->respondToSwitchRequest($request->user(), $id, $requestId, $request->input('status'));

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to respond to switch request',
            ], 400);
        }

        $statusText = $request->input('status') === 'accepted' ? 'accepted' : 'rejected';

        return response()->json([
            'success' => true,
            'message' => "Switch request {$statusText}",
            'data' => $result,
        ]);
    }

    /**
     * 13. Get available teachers for switch
     */
    public function availableTeachers(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'day' => 'required|string',
            'period' => 'required|string',
        ]);

        $result = $this->classRepository->getAvailableTeachers($request->user(), $id, $request->query('day'), $request->query('period'));

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Class not found or access denied',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Get class statistics (legacy)
     */
    public function statistics(Request $request, string $id): JsonResponse
    {
        $result = $this->classRepository->getClassStatistics($request->user(), $id);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Class not found or access denied',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Get classes dropdown
     */
    public function dropdown(Request $request): JsonResponse
    {
        $classes = $this->classRepository->getClassesDropdown($request->user());

        return response()->json([
            'success' => true,
            'data' => [
                'classes' => $classes->values()->toArray(),
            ],
        ]);
    }

    /**
     * Get classes dropdown for attendance with today's first period info
     */
    public function attendanceDropdown(Request $request): JsonResponse
    {
        $classes = $this->classRepository->getAttendanceDropdown($request->user());

        return response()->json([
            'success' => true,
            'data' => [
                'classes' => $classes->values()->toArray(),
            ],
        ]);
    }

    /**
     * 14. Get student profile
     */
    public function studentProfile(Request $request, string $id): JsonResponse
    {
        $result = $this->classRepository->getStudentProfile($request->user(), $id);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found or access denied',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * 15. Get student academic summary
     */
    public function studentAcademic(Request $request, string $id): JsonResponse
    {
        $academicYear = $request->query('academic_year');
        $term = $request->query('term');

        $result = $this->classRepository->getStudentAcademic($request->user(), $id, $academicYear, $term);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found or access denied',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * 16. Get student attendance
     */
    public function studentAttendance(Request $request, string $id): JsonResponse
    {
        $month = $request->query('month');
        $year = $request->query('year');

        $result = $this->classRepository->getStudentAttendance($request->user(), $id, $month, $year);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found or access denied',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * 17. Get student remarks
     */
    public function studentRemarks(Request $request, string $id): JsonResponse
    {
        $type = $request->query('type');
        $category = $request->query('category');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $result = $this->classRepository->getStudentRemarks($request->user(), $id, $type, $category, $dateFrom, $dateTo);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found or access denied',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * 19. Get student rankings
     */
    public function studentRankings(Request $request, string $id): JsonResponse
    {
        $result = $this->classRepository->getStudentRankings($request->user(), $id);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found or access denied',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * 20. Get student ranking detail for specific exam
     */
    public function studentRankingDetail(Request $request, string $id, string $examId): JsonResponse
    {
        $result = $this->classRepository->getStudentRankingDetail($request->user(), $id, $examId);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Student or exam not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * 21. Get class student ranking details
     * GET /classes/{class_id}/rankings/{exam_id}/{student_id}
     */
    public function classStudentRankingDetails(Request $request, string $classId, string $examId, string $studentId): JsonResponse
    {
        $result = $this->classRepository->getClassStudentRankingDetails($request->user(), $classId, $studentId, $examId);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Class, student, or exam not found, or access denied',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}
