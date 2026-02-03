<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\ClassRemark;
use App\Models\StudentRemark;
use App\Models\SchoolClass;
use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RemarkController extends Controller
{
    /**
     * Get class remarks for a specific class
     * API #8 from My_Clessen.txt
     */
    public function classRemarks(Request $request, string $classId)
    {
        $type = $request->get('type'); // 'positive' | 'concern' | 'note'
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Base query for counting (without type filter)
        $baseQuery = ClassRemark::where('class_id', $classId);
        if ($dateFrom) {
            $baseQuery->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $baseQuery->whereDate('date', '<=', $dateTo);
        }

        // Get type counts
        $positiveCount = (clone $baseQuery)->where('type', 'positive')->count();
        $concernCount = (clone $baseQuery)->where('type', 'concern')->count();
        $noteCount = (clone $baseQuery)->where('type', 'note')->count();

        // Query for remarks with filters
        $query = ClassRemark::where('class_id', $classId)
            ->with(['teacher.user', 'subject', 'period']);

        // Apply filters
        if ($type) {
            $query->where('type', $type);
        }
        if ($dateFrom) {
            $query->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('date', '<=', $dateTo);
        }

        $remarks = $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($r) {
                $startTime = $r->period?->starts_at;
                $endTime = $r->period?->ends_at;
                
                // Format time display using global setting
                $timeDisplay = null;
                if ($startTime && $endTime) {
                    $timeDisplay = format_time($startTime) . ' - ' . format_time($endTime);
                }

                return [
                    'id' => $r->id,
                    'teacher_name' => $r->teacher?->user?->name ?? 'Unknown',
                    'subject' => $r->subject?->name ?? 'General',
                    'remark' => $r->remark,
                    'date' => $r->date->format('Y-m-d'),
                    'period' => $r->period ? 'P' . $r->period->period_number : null,
                    'time' => $timeDisplay,
                    'type' => $r->type,
                    'avatar' => $this->getSubjectIcon($r->subject?->name),
                ];
            });

        return ApiResponse::success([
            'remarks' => $remarks->toArray(),
            'total' => $remarks->count(),
            'positive' => $positiveCount,
            'concern' => $concernCount,
            'note' => $noteCount,
        ]);
    }

    /**
     * Get subject icon helper
     */
    private function getSubjectIcon(?string $name): string
    {
        $icons = [
            'Mathematics' => '🔢',
            'English' => 'abc',
            'Science' => '🧪',
            'Myanmar' => 'ဃ',
            'History' => '📜',
            'Geography' => '🌍',
            'Physics' => '⚛️',
            'Chemistry' => '🧫',
            'Biology' => '🧬',
        ];

        return $icons[$name] ?? '📖';
    }

    /**
     * Store a new class remark
     */
    public function storeClassRemark(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|uuid|exists:classes,id',
            'subject_id' => 'nullable|uuid|exists:subjects,id',
            'period_id' => 'nullable|uuid|exists:periods,id',
            'date' => 'required|date',
            'remark' => 'required|string|max:1000',
            'type' => 'required|in:note,positive,concern',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }

        $teacher = $request->user()->teacherProfile;
        if (!$teacher) {
            return ApiResponse::error('Teacher profile not found', 404);
        }

        $remark = ClassRemark::create([
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
            'period_id' => $request->period_id,
            'teacher_id' => $teacher->id,
            'date' => $request->date,
            'remark' => $request->remark,
            'type' => $request->type,
        ]);

        return ApiResponse::success([
            'id' => $remark->id,
            'remark' => $remark->remark,
            'type' => $remark->type,
            'date' => $remark->date->format('Y-m-d'),
            'created_at' => $remark->created_at->format('Y-m-d H:i:s'),
        ], 'Class remark added successfully');
    }

    /**
     * Delete a class remark
     */
    public function deleteClassRemark(string $id)
    {
        $remark = ClassRemark::find($id);
        if (!$remark) {
            return ApiResponse::error('Remark not found', 404);
        }

        $teacher = request()->user()->teacherProfile;
        if (!$teacher || $remark->teacher_id !== $teacher->id) {
            return ApiResponse::error('Unauthorized', 403);
        }

        $remark->delete();
        return ApiResponse::success(null, 'Class remark deleted successfully');
    }

    /**
     * Update a class remark
     */
    public function updateClassRemark(Request $request, string $id)
    {
        $remark = ClassRemark::find($id);
        if (!$remark) {
            return ApiResponse::error('Remark not found', 404);
        }

        $teacher = $request->user()->teacherProfile;
        if (!$teacher || $remark->teacher_id !== $teacher->id) {
            return ApiResponse::error('Unauthorized', 403);
        }

        $validator = Validator::make($request->all(), [
            'remark' => 'sometimes|required|string|max:1000',
            'type' => 'sometimes|required|in:note,positive,concern',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }

        $remark->update($request->only(['remark', 'type']));

        return ApiResponse::success([
            'id' => $remark->id,
            'remark' => $remark->remark,
            'type' => $remark->type,
            'date' => $remark->date->format('Y-m-d'),
            'created_at' => $remark->created_at->format('Y-m-d H:i:s'),
        ], 'Class remark updated successfully');
    }

    /**
     * Get student remarks for a specific class and date
     */
    public function studentRemarks(Request $request, string $classId)
    {
        $date = $request->get('date', now()->format('Y-m-d'));

        $remarks = StudentRemark::where('class_id', $classId)
            ->where('date', $date)
            ->with(['student.user', 'teacher.user', 'subject', 'period'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'student_id' => $r->student_id,
                'student_name' => $r->student?->user?->name,
                'student_avatar' => $r->student?->user?->name ? strtoupper(substr($r->student->user->name, 0, 1)) : '?',
                'remark' => $r->remark,
                'type' => $r->type,
                'date' => $r->date->format('Y-m-d'),
                'subject' => $r->subject?->name,
                'period_number' => $r->period?->period_number,
                'teacher' => $r->teacher?->user?->name,
                'created_at' => $r->created_at->format('Y-m-d H:i:s'),
            ]);

        return ApiResponse::success($remarks);
    }

    /**
     * Store a new student remark
     */
    public function storeStudentRemark(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|uuid|exists:student_profiles,id',
            'class_id' => 'required|uuid|exists:classes,id',
            'subject_id' => 'nullable|uuid|exists:subjects,id',
            'period_id' => 'nullable|uuid|exists:periods,id',
            'date' => 'required|date',
            'remark' => 'required|string|max:1000',
            'type' => 'required|in:note,positive,concern',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }

        $teacher = $request->user()->teacherProfile;
        if (!$teacher) {
            return ApiResponse::error('Teacher profile not found', 404);
        }

        $remark = StudentRemark::create([
            'student_id' => $request->student_id,
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
            'period_id' => $request->period_id,
            'teacher_id' => $teacher->id,
            'date' => $request->date,
            'remark' => $request->remark,
            'type' => $request->type,
        ]);

        $remark->load(['student.user']);

        return ApiResponse::success([
            'id' => $remark->id,
            'student_id' => $remark->student_id,
            'student_name' => $remark->student?->user?->name,
            'student_avatar' => $remark->student?->user?->name ? strtoupper(substr($remark->student->user->name, 0, 1)) : '?',
            'remark' => $remark->remark,
            'type' => $remark->type,
            'date' => $remark->date->format('Y-m-d'),
            'created_at' => $remark->created_at->format('Y-m-d H:i:s'),
        ], 'Student remark added successfully');
    }

    /**
     * Delete a student remark
     */
    public function deleteStudentRemark(string $id)
    {
        $remark = StudentRemark::find($id);
        if (!$remark) {
            return ApiResponse::error('Remark not found', 404);
        }

        $teacher = request()->user()->teacherProfile;
        if (!$teacher || $remark->teacher_id !== $teacher->id) {
            return ApiResponse::error('Unauthorized', 403);
        }

        $remark->delete();
        return ApiResponse::success(null, 'Student remark deleted successfully');
    }

    /**
     * Update a student remark
     */
    public function updateStudentRemark(Request $request, string $id)
    {
        $remark = StudentRemark::find($id);
        if (!$remark) {
            return ApiResponse::error('Remark not found', 404);
        }

        $teacher = $request->user()->teacherProfile;
        if (!$teacher || $remark->teacher_id !== $teacher->id) {
            return ApiResponse::error('Unauthorized', 403);
        }

        $validator = Validator::make($request->all(), [
            'remark' => 'sometimes|required|string|max:1000',
            'type' => 'sometimes|required|in:note,positive,concern',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }

        $remark->update($request->only(['remark', 'type']));
        $remark->load(['student.user']);

        return ApiResponse::success([
            'id' => $remark->id,
            'student_id' => $remark->student_id,
            'student_name' => $remark->student?->user?->name,
            'student_avatar' => $remark->student?->user?->name ? strtoupper(substr($remark->student->user->name, 0, 1)) : '?',
            'remark' => $remark->remark,
            'type' => $remark->type,
            'date' => $remark->date->format('Y-m-d'),
            'created_at' => $remark->created_at->format('Y-m-d H:i:s'),
        ], 'Student remark updated successfully');
    }

    /**
     * Get activity summary for a class on a specific date
     * Used by web panel to show ongoing class activities
     */
    public function activitySummary(Request $request, string $classId)
    {
        $date = $request->get('date', now()->format('Y-m-d'));

        $class = SchoolClass::with(['grade', 'enrolledStudents'])->find($classId);
        if (!$class) {
            return ApiResponse::error('Class not found', 404);
        }

        // Get attendance summary for the date
        $totalStudents = $class->enrolledStudents->count();
        $attendanceQuery = \App\Models\StudentAttendance::where('class_id', $classId)
            ->whereDate('date', $date);
        
        $presentCount = (clone $attendanceQuery)->where('status', 'present')->count();
        $absentCount = (clone $attendanceQuery)->where('status', 'absent')->count();
        $leaveCount = (clone $attendanceQuery)->where('status', 'leave')->count();
        $lateCount = (clone $attendanceQuery)->where('status', 'late')->count();

        // Get class remarks for the date
        $classRemarks = ClassRemark::where('class_id', $classId)
            ->where('date', $date)
            ->with(['teacher.user', 'subject', 'period'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($r) {
                $startTime = $r->period?->starts_at;
                $endTime = $r->period?->ends_at;
                
                $timeDisplay = null;
                if ($startTime && $endTime) {
                    $timeDisplay = format_time($startTime) . ' - ' . format_time($endTime);
                }

                return [
                    'id' => $r->id,
                    'teacher_name' => $r->teacher?->user?->name ?? 'Unknown',
                    'subject' => $r->subject?->name ?? 'General',
                    'remark' => $r->remark,
                    'type' => $r->type,
                    'period' => $r->period ? 'P' . $r->period->period_number : null,
                    'time' => $timeDisplay,
                    'created_at' => $r->created_at->format('H:i'),
                ];
            });

        // Get student remarks for the date
        $studentRemarks = StudentRemark::where('class_id', $classId)
            ->where('date', $date)
            ->with(['student.user', 'teacher.user', 'subject'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'student_name' => $r->student?->user?->name,
                'student_avatar' => $r->student?->user?->name ? strtoupper(substr($r->student->user->name, 0, 1)) : '?',
                'remark' => $r->remark,
                'type' => $r->type,
                'subject' => $r->subject?->name,
                'teacher' => $r->teacher?->user?->name,
                'created_at' => $r->created_at->format('H:i'),
            ]);

        // Get curriculum progress updated on this date
        $curriculumUpdates = \App\Models\CurriculumProgress::where('class_id', $classId)
            ->whereDate('updated_at', $date)
            ->with(['topic.chapter.subject'])
            ->get()
            ->map(fn($p) => [
                'topic' => $p->topic?->title,
                'chapter' => $p->topic?->chapter?->title,
                'subject' => $p->topic?->chapter?->subject?->name,
                'status' => $p->status,
            ]);

        // Get homework assigned on this date
        $homeworkAssigned = \App\Models\Homework::where('class_id', $classId)
            ->whereDate('assigned_date', $date)
            ->with(['subject', 'teacher.user'])
            ->get()
            ->map(fn($h) => [
                'id' => $h->id,
                'title' => $h->title,
                'subject' => $h->subject?->name,
                'due_date' => $h->due_date?->format('Y-m-d'),
                'teacher' => $h->teacher?->user?->name,
            ]);

        return ApiResponse::success([
            'date' => $date,
            'attendance' => [
                'total' => $totalStudents,
                'present' => $presentCount,
                'absent' => $absentCount,
                'leave' => $leaveCount,
                'late' => $lateCount,
                'collected' => $presentCount + $absentCount + $leaveCount + $lateCount > 0,
            ],
            'class_remarks' => $classRemarks,
            'student_remarks' => $studentRemarks,
            'curriculum_updates' => $curriculumUpdates,
            'homework_assigned' => $homeworkAssigned,
        ]);
    }
}
