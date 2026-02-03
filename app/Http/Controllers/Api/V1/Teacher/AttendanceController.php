<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Http\Controllers\Controller;
use App\Interfaces\Teacher\TeacherAttendanceApiRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(
        private readonly TeacherAttendanceApiRepositoryInterface $attendanceRepository
    ) {}

    /**
     * Get students for attendance marking
     */
    public function students(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|string',
            'current_period_id' => 'nullable|string',
            'date' => 'required|date',
        ]);

        $result = $this->attendanceRepository->getStudentsForAttendance(
            $request->user(),
            $request->class_id,
            $request->current_period_id,
            $request->date
        );

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Class not found or access denied',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Save attendance records
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|string',
            'current_period_id' => 'nullable|string',
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required|string',
            'attendance.*.status' => 'required|in:present,absent,leave',
        ]);

        try {
            $result = $this->attendanceRepository->saveAttendance(
                $request->user(),
                $request->only(['class_id', 'current_period_id', 'date', 'attendance'])
            );

            return response()->json([
                'success' => true,
                'message' => 'Attendance saved successfully',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    /**
     * Bulk update attendance (mark all as same status)
     */
    public function bulk(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|string',
            'current_period_id' => 'nullable|string',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,leave',
        ]);

        try {
            $result = $this->attendanceRepository->bulkUpdateAttendance(
                $request->user(),
                $request->only(['class_id', 'current_period_id', 'date', 'status'])
            );

            return response()->json([
                'success' => true,
                'message' => 'All students marked as ' . $request->status,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    /**
     * Get attendance history
     */
    public function history(Request $request): JsonResponse
    {
        $result = $this->attendanceRepository->getAttendanceHistory(
            $request->user(),
            $request->query('filter'),
            $request->query('date_filter')
        );

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Get attendance detail for a specific record
     */
    public function historyDetail(Request $request, string $id): JsonResponse
    {
        $result = $this->attendanceRepository->getAttendanceDetail(
            $request->user(),
            $id
        );

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found or access denied',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}
