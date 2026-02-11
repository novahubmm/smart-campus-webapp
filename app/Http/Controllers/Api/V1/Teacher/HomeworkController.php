<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\SchoolClass;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class HomeworkController extends Controller
{
    /**
     * Get Homework List
     * GET /api/v1/teacher/homework
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $teacherProfile = TeacherProfile::where('user_id', $user->id)->first();

            if (!$teacherProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher profile not found'
                ], 404);
            }

            $query = Homework::where('teacher_id', $teacherProfile->id)
                ->with(['schoolClass.grade', 'subject', 'submissions']);

            // Filter by status
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Filter by class
            if ($request->has('class_id')) {
                $query->where('class_id', $request->class_id);
            }

            // Filter by subject
            if ($request->has('subject_id')) {
                $query->where('subject_id', $request->subject_id);
            }

            $homework = $query->orderBy('due_date', 'desc')->get();

            $data = $homework->map(function ($hw) {
                $totalStudents = $hw->schoolClass?->enrolledStudents()->count() ?? 0;
                $submittedCount = $hw->submissions->whereIn('status', ['submitted', 'graded'])->count();

                return [
                    'id' => $hw->id,
                    'title' => $hw->title,
                    'description' => $hw->description,
                    'class' => $hw->schoolClass?->name ?? 'Unknown Class',
                    'class_id' => $hw->class_id,
                    'grade' => $hw->schoolClass?->grade?->name ?? 'Grade ' . ($hw->schoolClass?->grade?->level ?? 'Unknown'),
                    'subject' => $hw->subject?->name ?? 'Unknown Subject',
                    'subject_id' => $hw->subject_id,
                    'assigned_date' => $hw->assigned_date?->format('Y-m-d'),
                    'due_date' => $hw->due_date?->format('Y-m-d'),
                    'priority' => $hw->priority ?? 'medium',
                    'status' => $hw->status,
                    'submitted_count' => $submittedCount,
                    'total_students' => $totalStudents,
                    'is_overdue' => $hw->isOverdue(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch homework list',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create Homework
     * POST /api/v1/teacher/homework
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $teacherProfile = TeacherProfile::where('user_id', $user->id)->first();

            if (!$teacherProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher profile not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'class_id' => 'required|exists:classes,id',
                'subject_id' => 'required|exists:subjects,id',
                'due_date' => 'required|date|after_or_equal:today',
                'priority' => 'nullable|in:low,medium,high',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $homework = Homework::create([
                'title' => $request->title,
                'description' => $request->description,
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'teacher_id' => $teacherProfile->id,
                'assigned_date' => Carbon::today(),
                'due_date' => $request->due_date,
                'priority' => $request->priority ?? 'medium',
                'status' => 'active',
            ]);

            $homework->load(['schoolClass.grade', 'subject']);

            return response()->json([
                'success' => true,
                'message' => 'Homework created successfully',
                'data' => [
                    'id' => $homework->id,
                    'title' => $homework->title,
                    'description' => $homework->description,
                    'class' => $homework->schoolClass?->name ?? 'Unknown Class',
                    'grade' => $homework->schoolClass?->grade?->name ?? 'Grade ' . ($homework->schoolClass?->grade?->level ?? 'Unknown'),
                    'subject' => $homework->subject?->name ?? 'Unknown Subject',
                    'assigned_date' => $homework->assigned_date?->format('Y-m-d'),
                    'due_date' => $homework->due_date?->format('Y-m-d'),
                    'priority' => $homework->priority,
                    'status' => $homework->status,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create homework',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Homework Detail
     * GET /api/v1/teacher/homework/{id}
     */
    public function show($id): JsonResponse
    {
        try {
            $user = Auth::user();
            $teacherProfile = TeacherProfile::where('user_id', $user->id)->first();

            if (!$teacherProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher profile not found'
                ], 404);
            }

            $homework = Homework::where('id', $id)
                ->where('teacher_id', $teacherProfile->id)
                ->with(['schoolClass.grade', 'schoolClass.enrolledStudents.user', 'subject', 'submissions.student.user'])
                ->first();

            if (!$homework) {
                return response()->json([
                    'success' => false,
                    'message' => 'Homework not found'
                ], 404);
            }

            // Get all students in the class
            $classStudents = $homework->schoolClass?->enrolledStudents ?? collect();
            $submissions = $homework->submissions->keyBy('student_id');

            $students = $classStudents->map(function ($student) use ($submissions) {
                $submission = $submissions->get($student->id);
                
                return [
                    'id' => $student->id,
                    'name' => $student->user?->name ?? 'Unknown Student',
                    'student_id' => $student->student_identifier,
                    'avatar' => avatar_url($student->photo_path, 'student'),
                    'status' => $submission ? $submission->status : 'pending',
                    'submitted_at' => $submission?->submitted_at?->format('Y-m-d H:i'),
                    'grade' => $submission?->grade,
                    'feedback' => $submission?->feedback,
                ];
            });

            $totalStudents = $classStudents->count();
            $submittedCount = $submissions->whereIn('status', ['submitted', 'graded'])->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $homework->id,
                    'title' => $homework->title,
                    'description' => $homework->description,
                    'class' => $homework->schoolClass?->name ?? 'Unknown Class',
                    'class_id' => $homework->class_id,
                    'grade' => $homework->schoolClass?->grade?->name ?? 'Grade ' . ($homework->schoolClass?->grade?->level ?? 'Unknown'),
                    'subject' => $homework->subject?->name ?? 'Unknown Subject',
                    'subject_id' => $homework->subject_id,
                    'assigned_date' => $homework->assigned_date?->format('Y-m-d'),
                    'due_date' => $homework->due_date?->format('Y-m-d'),
                    'priority' => $homework->priority ?? 'medium',
                    'status' => $homework->status,
                    'is_overdue' => $homework->isOverdue(),
                    'submitted_count' => $submittedCount,
                    'total_students' => $totalStudents,
                    'students' => $students,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch homework detail',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Collect Homework (Mark student as submitted)
     * POST /api/v1/teacher/homework/{id}/collect
     */
    public function collect(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $teacherProfile = TeacherProfile::where('user_id', $user->id)->first();

            if (!$teacherProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher profile not found'
                ], 404);
            }

            $homework = Homework::where('id', $id)
                ->where('teacher_id', $teacherProfile->id)
                ->first();

            if (!$homework) {
                return response()->json([
                    'success' => false,
                    'message' => 'Homework not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'student_id' => 'required|exists:student_profiles,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $now = Carbon::now();

            $submission = HomeworkSubmission::firstOrNew([
                'homework_id' => $homework->id,
                'student_id' => $request->student_id,
            ]);

            $alreadySubmitted = $submission->exists && $submission->status !== 'pending';

            if (!$alreadySubmitted) {
                $submission->status = 'submitted';
                $submission->submitted_at = $now;
                $submission->save();
            }

            return response()->json([
                'success' => true,
                'message' => $alreadySubmitted ? 'Homework already collected' : 'Homework collected successfully',
                'data' => [
                    'student_id' => $request->student_id,
                    'status' => $submission->status,
                    'submitted_at' => $submission->submitted_at?->format('Y-m-d H:i'),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to collect homework',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Uncollect Homework (Mark student as pending)
     * POST /api/v1/teacher/homework/{id}/uncollect
     */
    public function uncollect(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $teacherProfile = TeacherProfile::where('user_id', $user->id)->first();

            if (!$teacherProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Teacher profile not found'
                ], 404);
            }

            $homework = Homework::where('id', $id)
                ->where('teacher_id', $teacherProfile->id)
                ->first();

            if (!$homework) {
                return response()->json([
                    'success' => false,
                    'message' => 'Homework not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'student_id' => 'required|exists:student_profiles,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $submission = HomeworkSubmission::where('homework_id', $homework->id)
                ->where('student_id', $request->student_id)
                ->first();

            if ($submission) {
                $submission->status = 'pending';
                $submission->submitted_at = null;
                $submission->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Homework uncollected successfully',
                'data' => [
                    'student_id' => $request->student_id,
                    'status' => 'pending',
                    'submitted_at' => null,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to uncollect homework',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
