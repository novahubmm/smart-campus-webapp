<?php

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Teacher\SubmitGradesRequest;
use App\Http\Requests\Api\V1\Teacher\UpdateGradeRequest;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\ExamMark;
use App\Models\Student;
use App\Models\Subject;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    /**
     * Get all exams/tests with optional filtering by status
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return ApiResponse::error('Unauthenticated', 401);
        }
        
        $teacher = $user->teacherProfile;
        
        if (!$teacher) {
            return ApiResponse::error('Teacher profile not found', 404);
        }
        $status = $request->query('status');

        // Get teacher's subject IDs
        $teacherSubjectIds = $teacher->subjects()->pluck('subjects.id');
        
        // Get teacher's grade IDs from their assigned classes
        $teacherGradeIds = $teacher->classes()->pluck('grade_id')->unique();

        $query = Exam::with(['examType', 'grade', 'schoolClass', 'schedules.subject.teachers', 'schedules.room'])
            ->where(function ($q) use ($teacherSubjectIds, $teacherGradeIds) {
                // Show exams where teacher teaches a subject OR exams from teacher's grades
                $q->whereHas('schedules', function ($subQ) use ($teacherSubjectIds) {
                    $subQ->whereIn('subject_id', $teacherSubjectIds);
                })
                ->orWhereIn('grade_id', $teacherGradeIds);
            })
            ->orderBy('start_date', 'desc');

        // Filter by status if provided
        if ($status && in_array($status, ['upcoming', 'completed', 'results'])) {
            $now = now();
            switch ($status) {
                case 'upcoming':
                    $query->where('start_date', '>', $now);
                    break;
                case 'completed':
                    $query->where('end_date', '<', $now)
                          ->where('status', '!=', 'results_published');
                    break;
                case 'results':
                    $query->where('status', 'results_published');
                    break;
            }
        }

        $exams = $query->get()->map(function ($exam) use ($teacher) {
            $teacherSubjects = $exam->schedules->filter(function ($schedule) use ($teacher) {
                return $schedule->subject->teachers->contains('id', $teacher->id);
            });

            $status = $this->getExamStatus($exam);
            
            // Format class name with grade level prefix (e.g., "Kindergarten A", "Grade 1 A")
            $className = 'Unknown';
            if ($exam->schoolClass && $exam->grade) {
                $gradeLevel = $exam->grade->level;
                $section = \App\Helpers\SectionHelper::extractSection($exam->schoolClass->name);
                
                // If no section found and className is just a single letter, use it as the section
                if ($section === null && preg_match('/^[A-Za-z]$/', trim($exam->schoolClass->name))) {
                    $section = strtoupper(trim($exam->schoolClass->name));
                }
                
                $className = \App\Helpers\GradeHelper::formatClassName($gradeLevel, $section);
            } elseif ($exam->grade) {
                $className = $exam->grade->name ?? 'Unknown';
            }
            
            return [
                'id' => $exam->id,
                'title' => $exam->name,
                'type' => strtolower($exam->examType->name ?? 'exam'),
                'class' => $className,
                'grade' => $exam->grade->name ?? 'Unknown Grade',
                'subject_count' => $exam->schedules->count(),
                'start_date' => $exam->start_date->format('Y-m-d'),
                'end_date' => $exam->end_date->format('Y-m-d'),
                'location' => $exam->schedules->first()->room->name ?? 'TBA',
                'status' => $status,
                'is_your_subject' => $teacherSubjects->isNotEmpty()
            ];
        });

        return ApiResponse::success([
            'exams' => $exams,
            'total' => $exams->count()
        ]);
    }

    /**
     * Get exam detail with subjects and grading status
     */
    public function show(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user || !$user->teacherProfile) {
            return ApiResponse::error('Unauthenticated or teacher profile not found', 401);
        }
        
        $teacher = $user->teacherProfile;
        $teacherSubjectIds = $teacher->subjects()->pluck('subjects.id');
        $classId = $request->query('class_id');
        
        $exam = Exam::with(['examType', 'grade.gradeCategory', 'schoolClass.enrolledStudents', 'schoolClass.grade.gradeCategory', 'schoolClass.room', 'schoolClass.teacher.user', 'schoolClass.batch', 'schedules.subject.teachers', 'schedules.room'])
            ->whereHas('schedules', function ($q) use ($teacherSubjectIds) {
                $q->whereIn('subject_id', $teacherSubjectIds);
            })
            ->findOrFail($id);

        // If class_id is provided, load that class instead of exam's default class
        $class = null;
        if ($classId) {
            $class = \App\Models\SchoolClass::with(['enrolledStudents', 'grade.gradeCategory', 'room', 'teacher.user', 'batch'])
                ->find($classId);
        } else {
            $class = $exam->schoolClass;
        }

        $subjects = $exam->schedules->map(function ($schedule) use ($teacher, $exam, $classId) {
            $isTeacherSubject = $schedule->subject->teachers->contains('id', $teacher->id);
            
            $gradingStatus = 'pending';
            $passPercentage = null;
            
            // Use provided class_id or fall back to exam's class_id
            $targetClassId = $classId ?? $exam->class_id;
            
            if ($exam->status === 'results_published' || $exam->end_date < now()) {
                $results = ExamMark::where('exam_id', $exam->id)
                    ->where('subject_id', $schedule->subject_id)
                    ->get();
                $studentsCount = $targetClassId 
                    ? \App\Models\StudentProfile::where('class_id', $targetClassId)->count()
                    : \App\Models\StudentProfile::where('grade_id', $exam->grade_id)->count();
                
                if ($results->count() > 0 && $results->count() >= $studentsCount) {
                    $gradingStatus = 'graded';
                    $passCount = $results->where('marks_obtained', '>=', $schedule->passing_marks ?? ($schedule->total_marks * 0.4))->count();
                    $passPercentage = $results->count() > 0 ? round(($passCount / $results->count()) * 100, 1) : null;
                }
            }

            return [
                'id' => $schedule->id,
                'name' => $schedule->subject->name,
                'marks' => $schedule->total_marks,
                'date' => $schedule->exam_date ? $schedule->exam_date->format('Y-m-d') : null,
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'is_your_subject' => $isTeacherSubject,
                'grading_status' => $gradingStatus,
                'pass_percentage' => $passPercentage
            ];
        });

        // Build class object with full details
        $classData = null;
        if ($class) {
            $gradeColor = $class->grade?->gradeCategory?->color ?? '#6B7280';
            $activeBatch = \App\Models\Batch::where('status', true)->first();
            
            $classData = [
                'id' => $class->id,
                'grade' => $class->grade?->name ?? 'Grade ' . $class->grade?->level,
                'section' => $class->name,
                'room' => $class->room?->name ?? 'N/A',
                'student_count' => $class->enrolledStudents->count(),
                'class_teacher' => $class->teacher?->user?->name ?? 'N/A',
                'academic_year' => $activeBatch?->name ?? now()->format('Y') . '-' . (now()->format('Y') + 1),
                'grade_color' => $gradeColor,
                'grade_bg_color' => $this->lightenColor($gradeColor),
            ];
        }

        return ApiResponse::success([
            'id' => $exam->id,
            'title' => $exam->name,
            'type' => strtolower($exam->examType->name ?? 'exam'),
            'class' => $classData,
            'grade' => $exam->grade->name ?? 'Unknown Grade',
            'start_date' => $exam->start_date->format('Y-m-d'),
            'end_date' => $exam->end_date->format('Y-m-d'),
            'location' => $exam->schedules->first()->room->name ?? 'TBA',
            'status' => $this->getExamStatus($exam),
            'is_your_subject' => $subjects->where('is_your_subject', true)->isNotEmpty(),
            'subjects' => $subjects
        ]);
    }


    /**
     * Get exam results with student-wise remarks and subject details
     */
    public function results($id)
    {
        $user = Auth::user();
        
        if (!$user || !$user->teacherProfile) {
            return ApiResponse::error('Unauthenticated or teacher profile not found', 401);
        }
        
        $teacher = $user->teacherProfile;
        $teacherSubjectIds = $teacher->subjects()->pluck('subjects.id');
        
        // Find exam without strict constraints
        $exam = Exam::with(['examType', 'grade', 'schoolClass', 'schedules.subject.teachers', 'schedules.room'])
            ->findOrFail($id);

        $subjects = $exam->schedules->map(function ($schedule) use ($teacher, $exam) {
            $isTeacherSubject = $schedule->subject->teachers->contains('id', $teacher->id);
            
            $results = ExamMark::where('exam_id', $exam->id)
                ->where('subject_id', $schedule->subject_id)
                ->get();
            $passCount = $results->where('marks_obtained', '>=', $schedule->passing_marks ?? ($schedule->total_marks * 0.4))->count();
            $passPercentage = $results->count() > 0 ? round(($passCount / $results->count()) * 100, 1) : 0;

            return [
                'id' => $schedule->id,
                'name' => $schedule->subject->name,
                'marks' => $schedule->total_marks,
                'is_your_subject' => $isTeacherSubject,
                'pass_percentage' => $passPercentage
            ];
        });

        // Get all students with their exam marks and remarks
        $students = collect();
        
        if ($exam->schoolClass) {
            // Get students from specific class
            $classStudents = \App\Models\StudentProfile::with(['user'])
                ->where('class_id', $exam->schoolClass->id)
                ->get();
        } else {
            // Get students from grade
            $classStudents = \App\Models\StudentProfile::with(['user'])
                ->where('grade_id', $exam->grade_id)
                ->get();
        }

        $students = $classStudents->map(function ($student) use ($exam) {
            // Get all exam marks for this student
            $examMarks = ExamMark::with(['subject'])
                ->where('exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->get();

            $totalMarks = 0;
            $obtainedMarks = 0;
            $subjectResults = [];
            $overallRemark = '';

            foreach ($examMarks as $mark) {
                $totalMarks += $mark->total_marks;
                $obtainedMarks += $mark->marks_obtained;
                
                $subjectResults[] = [
                    'subject' => $mark->subject->name,
                    'marks_obtained' => $mark->marks_obtained,
                    'total_marks' => $mark->total_marks,
                    'grade' => $mark->grade,
                    'remark' => $mark->remark
                ];

                // Collect individual subject remarks for overall remark
                if ($mark->remark) {
                    $overallRemark .= $mark->subject->name . ': ' . $mark->remark . '; ';
                }
            }

            $percentage = $totalMarks > 0 ? round(($obtainedMarks / $totalMarks) * 100, 2) : 0;
            $overallGrade = $this->calculateGrade($obtainedMarks, $totalMarks);
            
            // Generate overall remark based on performance
            if (empty(trim($overallRemark))) {
                if ($percentage >= 90) {
                    $overallRemark = 'Excellent performance across all subjects.';
                } elseif ($percentage >= 80) {
                    $overallRemark = 'Very good performance. Keep up the good work.';
                } elseif ($percentage >= 70) {
                    $overallRemark = 'Good performance. Room for improvement in some areas.';
                } elseif ($percentage >= 60) {
                    $overallRemark = 'Satisfactory performance. Needs more focus on studies.';
                } elseif ($percentage >= 40) {
                    $overallRemark = 'Below average performance. Requires additional support.';
                } else {
                    $overallRemark = 'Poor performance. Immediate attention and support needed.';
                }
            } else {
                $overallRemark = rtrim($overallRemark, '; ');
            }

            return [
                'id' => $student->id,
                'name' => $student->user->name ?? 'Unknown',
                'student_id' => $student->student_identifier ?? $student->student_id,
                'avatar' => $student->photo_path ? url('storage/' . $student->photo_path) : null,
                'total_marks' => $totalMarks,
                'obtained_marks' => $obtainedMarks,
                'percentage' => $percentage,
                'overall_grade' => $overallGrade,
                'overall_remark' => $overallRemark,
                'subject_results' => $subjectResults
            ];
        });

        $className = $exam->schoolClass ? $exam->schoolClass->name : ($exam->grade->name ?? 'Unknown');

        return ApiResponse::success([
            'id' => $exam->id,
            'title' => $exam->name,
            'type' => strtolower($exam->examType->name ?? 'exam') . ' (' . $exam->schedules->sum('total_marks') . ' marks, ' . $exam->schedules->count() . ' subjects)',
            'class' => $className,
            'grade' => $exam->grade->name ?? 'Unknown Grade',
            'date' => $exam->start_date->format('Y-m-d'),
            'time' => $exam->start_date->format('h:i A') . ' - ' . $exam->end_date->format('h:i A'),
            'location' => $exam->schedules->first()?->room->name ?? 'TBA',
            'status' => $this->getExamStatus($exam),
            'subjects' => $subjects,
            'students' => $students,
            'total_students' => $students->count()
        ]);
    }

    /**
     * Get detailed student-wise results for an exam
     */
    public function detailedResults($id, Request $request)
    {
        $user = Auth::user();
        
        if (!$user || !$user->teacherProfile) {
            return ApiResponse::error('Unauthenticated or teacher profile not found', 401);
        }
        
        $teacher = $user->teacherProfile;
        $teacherSubjectIds = $teacher->subjects()->pluck('subjects.id');
        $subjectId = $request->query('subject_id');
        
        // Find exam without strict constraints
        $exam = Exam::with(['examType', 'grade', 'schoolClass', 'schedules.subject', 'schedules.room'])
            ->findOrFail($id);

        $schedule = null;
        if ($subjectId) {
            $schedule = $exam->schedules->where('subject_id', $subjectId)->first();
        } else {
            // Get first schedule that matches teacher's subjects
            $schedule = $exam->schedules->whereIn('subject_id', $teacherSubjectIds)->first();
        }

        if (!$schedule) {
            return ApiResponse::error('No results found for this exam and subject', 404);
        }

        // Get marks from ExamMark model
        $marks = ExamMark::with(['student.user'])
            ->where('exam_id', $exam->id)
            ->where('subject_id', $schedule->subject_id)
            ->get()
            ->sortByDesc('marks_obtained');
            
        $passingMarks = $schedule->passing_marks ?? ($schedule->total_marks * 0.4);
        
        $passCount = $marks->where('marks_obtained', '>=', $passingMarks)->count();
        $failCount = $marks->where('marks_obtained', '<', $passingMarks)->count();

        $statistics = [
            'total_students' => $marks->count(),
            'pass_count' => $passCount,
            'fail_count' => $failCount
        ];

        $students = $marks->values()->map(function ($mark, $index) use ($passingMarks, $schedule) {
            $grade = $this->calculateGrade($mark->marks_obtained, $schedule->total_marks);
            $status = $mark->marks_obtained >= $passingMarks ? 'pass' : 'fail';
            
            return [
                'id' => $mark->student->id,
                'name' => $mark->student->user->name ?? 'Unknown',
                'student_id' => $mark->student->student_identifier ?? $mark->student->student_id,
                'avatar' => $mark->student->photo_path ? url('storage/' . $mark->student->photo_path) : null,
                'score' => number_format($mark->marks_obtained, 2),
                'grade' => $grade,
                'status' => $status,
                'rank' => $index + 1,
                'remark' => $mark->remark ?? ''
            ];
        });

        return ApiResponse::success([
            'exam' => [
                'id' => $exam->id,
                'title' => $exam->name,
                'grade' => $exam->grade->name ?? null,
                'date' => $exam->start_date->format('Y-m-d'),
                'time' => $exam->start_date->format('h:i A') . ' - ' . $exam->end_date->format('h:i A'),
                'location' => $schedule->room->name ?? 'TBA',
                'max_marks' => $schedule->total_marks
            ],
            'statistics' => $statistics,
            'students' => $students
        ]);
    }

    /**
     * Get students for grade entry
     */
    public function students($id, Request $request)
    {
        $user = Auth::user();
        
        if (!$user || !$user->teacherProfile) {
            return ApiResponse::error('Unauthenticated or teacher profile not found', 401);
        }
        
        $teacher = $user->teacherProfile;
        $classId = $request->query('class_id');
        
        if (!$classId) {
            return ApiResponse::error('Class ID is required', 400);
        }

        // Find exam
        $exam = Exam::with(['grade', 'schoolClass', 'schedules.subject'])
            ->findOrFail($id);

        // Load the class with full details
        $class = \App\Models\SchoolClass::with(['enrolledStudents.user', 'grade.gradeCategory', 'room', 'teacher.user', 'batch'])
            ->find($classId);

        if (!$class) {
            return ApiResponse::error('Class not found', 404);
        }

        // Get students from the specified class with their exam marks
        $students = \App\Models\StudentProfile::with(['user', 'examMarks' => function($query) use ($id) {
                $query->where('exam_id', $id)->with('enteredBy');
            }])
            ->where('class_id', $classId)
            ->orderBy('student_identifier')
            ->get();

        $studentsData = $students->map(function ($student) use ($exam) {
            // Get exam mark for this student (there might be multiple marks for different subjects)
            // For now, we'll get the first one or aggregate if needed
            $examMark = $student->examMarks->first();
            
            // Determine who graded
            $gradedBy = null;
            $gradedAt = null;
            
            if ($examMark) {
                // Check if entered_by exists and determine role
                if ($examMark->entered_by) {
                    $grader = $examMark->enteredBy;
                    if ($grader) {
                        // Check user role to determine if admin or teacher
                        $gradedBy = $grader->hasRole('admin') ? 'admin' : 'teacher';
                    }
                }
                
                $gradedAt = $examMark->updated_at ? $examMark->updated_at->toIso8601String() : null;
            }
            
            return [
                'id' => $student->id,
                'name' => $student->user->name ?? 'Unknown',
                'student_id' => $student->student_identifier ?? $student->student_id,
                'avatar' => $student->photo_path ? url('storage/' . $student->photo_path) : null,
                'roll_number' => $student->student_identifier ?? '',
                'gender' => $student->gender ?? 'N/A',
                // New fields for exam results
                'current_score' => $examMark ? $examMark->marks_obtained : null,
                'current_remark' => $examMark ? ($examMark->remark ?? '') : '',
                'graded_by' => $gradedBy,
                'graded_at' => $gradedAt,
            ];
        });

        // Build class object with full details
        $gradeColor = $class->grade?->gradeCategory?->color ?? '#6B7280';
        $activeBatch = \App\Models\Batch::where('status', true)->first();
        
        $classData = [
            'id' => $class->id,
            'grade' => $class->grade?->name ?? 'Grade ' . $class->grade?->level,
            'section' => $class->name,
            'room' => $class->room?->name ?? 'N/A',
            'student_count' => $class->enrolledStudents->count(),
            'class_teacher' => $class->teacher?->user?->name ?? 'N/A',
            'academic_year' => $activeBatch?->name ?? now()->format('Y') . '-' . (now()->format('Y') + 1),
            'grade_color' => $gradeColor,
            'grade_bg_color' => $this->lightenColor($gradeColor),
        ];

        return ApiResponse::success([
            'exam' => [
                'id' => $exam->id,
                'title' => $exam->name,
                'grade' => $exam->grade->name ?? null
            ],
            'class' => $classData,
            'students' => $studentsData,
            'total_students' => $studentsData->count()
        ]);
    }


    /**
     * Submit grades for students in an exam
     */
    public function submitGrades($id, Request $request)
    {
        $user = Auth::user();
        
        if (!$user || !$user->teacherProfile) {
            return ApiResponse::error('Unauthenticated or teacher profile not found', 401);
        }
        
        $classId = $request->input('class_id');
        $grades = $request->input('grades');
        
        if (!$classId) {
            return ApiResponse::error('Class ID is required', 400);
        }
        
        if (!$grades || !is_array($grades) || count($grades) === 0) {
            return ApiResponse::error('Grades data is required', 400);
        }

        $exam = Exam::with(['schedules'])->findOrFail($id);

        // Verify class exists
        $class = \App\Models\SchoolClass::find($classId);
        if (!$class) {
            return ApiResponse::error('Class not found', 404);
        }

        // Get the first schedule to determine total marks
        $schedule = $exam->schedules->first();
        $totalMarks = $schedule?->total_marks ?? 100;

        // Validate all grades before processing
        foreach ($grades as $gradeData) {
            if (!isset($gradeData['student_id']) || !isset($gradeData['score'])) {
                continue;
            }
            
            // Validate marks obtained cannot exceed total marks
            if ($gradeData['score'] > $totalMarks) {
                return ApiResponse::error("Marks obtained ({$gradeData['score']}) cannot exceed total marks ({$totalMarks})", 422);
            }
            
            // Validate marks obtained cannot be negative
            if ($gradeData['score'] < 0) {
                return ApiResponse::error("Marks obtained cannot be negative", 422);
            }
        }

        DB::beginTransaction();
        try {
            $gradedCount = 0;
            $totalScore = 0;

            foreach ($grades as $gradeData) {
                if (!isset($gradeData['student_id']) || !isset($gradeData['score'])) {
                    continue;
                }

                ExamMark::updateOrCreate(
                    [
                        'exam_id' => $exam->id,
                        'student_id' => $gradeData['student_id'],
                        'subject_id' => $schedule?->subject_id
                    ],
                    [
                        'marks_obtained' => $gradeData['score'],
                        'total_marks' => $totalMarks,
                        'grade' => $this->calculateGrade($gradeData['score'], $totalMarks),
                        'remark' => $gradeData['remark'] ?? null,
                        'entered_by' => $user->id
                    ]
                );
                
                $gradedCount++;
                $totalScore += $gradeData['score'];
            }

            $averageScore = $gradedCount > 0 ? round($totalScore / $gradedCount, 1) : 0;

            DB::commit();

            return ApiResponse::success([
                'exam_id' => $exam->id,
                'class_id' => $classId,
                'graded_count' => $gradedCount,
                'average_score' => $averageScore
            ], 'Grades submitted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Failed to submit grades: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update grade for a single student
     */
    public function updateGrade($examId, $studentId, Request $request)
    {
        $user = Auth::user();
        
        if (!$user || !$user->teacherProfile) {
            return ApiResponse::error('Unauthenticated or teacher profile not found', 401);
        }
        
        $classId = $request->input('class_id');
        $score = $request->input('score');
        $remark = $request->input('remark');
        
        if (!$classId) {
            return ApiResponse::error('Class ID is required', 400);
        }
        
        if ($score === null) {
            return ApiResponse::error('Score is required', 400);
        }

        $exam = Exam::with(['schedules'])->findOrFail($examId);

        // Get the first schedule to determine total marks
        $schedule = $exam->schedules->first();
        $totalMarks = $schedule?->total_marks ?? 100;

        // Validate marks obtained cannot exceed total marks
        if ($score > $totalMarks) {
            return ApiResponse::error("Marks obtained ({$score}) cannot exceed total marks ({$totalMarks})", 422);
        }
        
        // Validate marks obtained cannot be negative
        if ($score < 0) {
            return ApiResponse::error("Marks obtained cannot be negative", 422);
        }

        $mark = ExamMark::updateOrCreate(
            [
                'exam_id' => $exam->id,
                'student_id' => $studentId,
                'subject_id' => $schedule?->subject_id
            ],
            [
                'marks_obtained' => $score,
                'total_marks' => $totalMarks,
                'grade' => $this->calculateGrade($score, $totalMarks),
                'remark' => $remark,
                'entered_by' => $user->id
            ]
        );

        return ApiResponse::success([
            'student_id' => $studentId,
            'score' => $mark->marks_obtained,
            'grade' => $mark->grade,
            'remark' => $mark->remark
        ], 'Grade updated successfully');
    }

    /**
     * Mark exam as completed
     */
    public function markCompleted($id)
    {
        $user = Auth::user();
        
        if (!$user || !$user->teacherProfile) {
            return ApiResponse::error('Unauthenticated or teacher profile not found', 401);
        }
        
        $teacher = $user->teacherProfile;
        $teacherSubjectIds = $teacher->subjects()->pluck('subjects.id');
        
        $exam = Exam::whereHas('schedules', function ($q) use ($teacherSubjectIds) {
            $q->whereIn('subject_id', $teacherSubjectIds);
        })->findOrFail($id);

        // If already completed or results published, return success (idempotent)
        if ($exam->status === 'completed' || $exam->status === 'results_published') {
            return ApiResponse::success([
                'id' => $exam->id,
                'status' => $exam->status
            ], 'Exam is already completed');
        }

        $exam->update(['status' => 'completed']);

        return ApiResponse::success([
            'id' => $exam->id,
            'status' => 'completed'
        ], 'Exam marked as completed');
    }

    /**
     * Get exam status from database
     * Returns: 'upcoming' | 'completed' | 'results'
     */
    private function getExamStatus($exam)
    {
        $status = $exam->status;
        
        // Map database status to API status
        if ($status === 'results_published' || $status === 'results') {
            return 'results';
        }
        
        if ($status === 'completed') {
            return 'completed';
        }
        
        if ($status === 'upcoming') {
            return 'upcoming';
        }
        
        // Fallback: calculate based on dates if status is not set
        $now = now()->startOfDay();
        $startDate = $exam->start_date ? $exam->start_date->startOfDay() : null;
        $endDate = $exam->end_date ? $exam->end_date->startOfDay() : null;
        
        // Check if exam is ongoing (today is between start and end date, inclusive)
        if ($startDate && $endDate && $now->greaterThanOrEqualTo($startDate) && $now->lessThanOrEqualTo($endDate)) {
            return 'ongoing';
        }
        
        // Check if exam is completed (today is after end date)
        if ($endDate && $now->greaterThan($endDate)) {
            return 'completed';
        }
        
        return 'upcoming';
    }

    /**
     * Calculate grade based on marks
     */
    private function calculateGrade($marksObtained, $totalMarks)
    {
        $percentage = ($marksObtained / $totalMarks) * 100;
        
        if ($percentage >= 90) return 'A+';  // 90% - 100%
        if ($percentage >= 80) return 'A';   // 80% - 89%
        if ($percentage >= 70) return 'B+';  // 70% - 79%
        if ($percentage >= 60) return 'B';   // 60% - 69%
        if ($percentage >= 50) return 'C+';  // 50% - 59%
        if ($percentage >= 40) return 'C';   // 40% - 49%
        return 'F';                          // 0% - 39%
    }

    /**
     * Lighten a hex color to create a background color
     */
    private function lightenColor(string $hex): string
    {
        $hex = ltrim($hex, '#');
        
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        $factor = 0.85;
        $r = (int) ($r + (255 - $r) * $factor);
        $g = (int) ($g + (255 - $g) * $factor);
        $b = (int) ($b + (255 - $b) * $factor);
        
        return sprintf('#%02X%02X%02X', $r, $g, $b);
    }
}
