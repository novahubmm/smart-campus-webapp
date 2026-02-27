<?php

namespace App\Http\Controllers;

use App\DTOs\Academic\BatchData;
use App\DTOs\Academic\GradeData;
use App\DTOs\Academic\RoomData;
use App\DTOs\Academic\SchoolClassData;
use App\DTOs\Academic\SubjectData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\StoreBatchRequest;
use App\Http\Requests\Academic\StoreClassRequest;
use App\Http\Requests\Academic\StoreGradeRequest;
use App\Http\Requests\Academic\StoreRoomRequest;
use App\Http\Requests\Academic\StoreSubjectRequest;
use App\Http\Requests\Academic\UpdateBatchRequest;
use App\Http\Requests\Academic\UpdateClassRequest;
use App\Http\Requests\Academic\UpdateGradeRequest;
use App\Http\Requests\Academic\UpdateRoomRequest;
use App\Http\Requests\Academic\UpdateSubjectRequest;
use App\Interfaces\AcademicRepositoryInterface;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\StudentProfile;
use App\Models\Timetable;
use App\Services\AcademicService;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AcademicManagementController extends Controller
{
    use LogsActivity;

    public function __construct(
        protected AcademicService $academicService,
        protected AcademicRepositoryInterface $academicRepository
    ) {}

    public function index()
    {
        // Get total counts for stat cards
        $batchesCount = $this->academicRepository->getBatchesCount();
        $gradesCount = $this->academicRepository->getGradesCount();
        $classesCount = $this->academicRepository->getClassesCount();
        $roomsCount = $this->academicRepository->getRoomsCount();
        $subjectsCount = $this->academicRepository->getSubjectsCount();

        // Get paginated data for tables
        $batches = $this->academicRepository->getBatchesWithCounts();
        $grades = $this->academicRepository->getGradesWithCounts();
        $gradeCategories = $this->academicRepository->getGradeCategories();
        $classes = $this->academicRepository->getClasses();
        $rooms = $this->academicRepository->getRoomsWithCounts();
        $subjects = $this->academicRepository->getSubjectsWithCounts();
        $activeBatches = $this->academicRepository->getActiveBatches();
        $teachers = $this->academicRepository->getTeachers();
        $facilities = $this->academicRepository->getFacilities();
        $subjectTypes = $this->academicRepository->getSubjectTypes();
        
        // Get all grades for dropdowns (without pagination) - only active grades
        $allGrades = $this->academicRepository->getGradesWithDetails(activeOnly: true);

        return view('academic.academic-management', compact(
            'batchesCount',
            'gradesCount',
            'classesCount',
            'roomsCount',
            'subjectsCount',
            'batches',
            'grades',
            'gradeCategories',
            'classes',
            'rooms',
            'subjects',
            'activeBatches',
            'teachers',
            'facilities',
            'subjectTypes',
            'allGrades'
        ));
    }

    public function showBatch(string $id)
    {
        $batch = $this->academicRepository->findBatch($id);

        abort_unless($batch, 404);

        // Load relationships for detail view with student counts
        $batch->load(['grades.classes', 'grades.gradeCategory']);
        
        // Calculate total students count from all grades using direct grade_id relationship
        $batch->students_count = $batch->grades->sum(function($grade) {
            return StudentProfile::where('grade_id', $grade->id)->count();
        });

        return view('academic.batch-detail', compact('batch'));
    }

    public function editBatch(string $id)
    {
        $batch = $this->academicRepository->findBatch($id);

        abort_unless($batch, 404);

        return view('academic.batch-edit', compact('batch'));
    }

    public function updateBatch(UpdateBatchRequest $request, string $id)
    {
        try {
            $batch = $this->academicService->updateBatch($id, BatchData::from($request->validated()));
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Batch updated successfully'),
                    'batch' => $batch
                ]);
            }
            
            return back()->with('success', __('Batch updated successfully'));
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Validation failed'),
                    'errors' => $e->errors()
                ], 422);
            }
            
            return back()->withErrors($e->errors())->withInput();
        }
    }

    public function destroyBatch(Request $request, string $id)
    {
        $deleted = $this->academicService->deleteBatch($id);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => $deleted,
                'message' => $deleted ? __('Batch deleted successfully') : __('Batch not found')
            ], $deleted ? 200 : 404);
        }

        return back()->with($deleted ? 'success' : 'error', $deleted ? __('Batch deleted successfully') : __('Batch not found'));
    }

    // Batches
    public function storeBatch(StoreBatchRequest $request)
    {
        try {
            $batch = $this->academicService->createBatch(BatchData::from($request->validated()));
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Batch created successfully'),
                    'batch' => $batch
                ]);
            }
            
            return redirect()->back()->with('success', __('Batch created successfully'));
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Validation failed'),
                    'errors' => $e->errors()
                ], 422);
            }
            
            return back()->withErrors($e->errors())->withInput();
        }
    }

    // Grades
    public function storeGrade(StoreGradeRequest $request)
    {
        try {
            $this->academicService->createGrade(GradeData::from($request->validated()));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->back()->with('success', __('Grade created successfully'));
    }

    public function showGrade(string $id)
    {
        $grade = $this->academicRepository->findGrade($id);

        abort_unless($grade, 404);

        // Load relationships for detail view
        // Use enrolledStudents (direct class_id FK) instead of students (pivot table)
        $grade->load(['batch', 'gradeCategory', 'classes.enrolledStudents', 'classes.room', 'classes.teacher.user', 'subjects']);

        $batches = $this->academicRepository->getActiveBatches();
        $gradeCategories = $this->academicRepository->getGradeCategories();

        return view('academic.grade-detail', compact('grade', 'batches', 'gradeCategories'));
    }

    public function editGrade(string $id)
    {
        $grade = $this->academicRepository->findGrade($id);

        abort_unless($grade, 404);

        $batches = $this->academicRepository->getActiveBatches();

        return view('academic.grade-edit', compact('grade', 'batches'));
    }

    public function updateGrade(UpdateGradeRequest $request, string $id)
    {
        try {
            $this->academicService->updateGrade($id, GradeData::from($request->validated()));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return back()->with('success', __('Grade updated successfully'));
    }

    // Classes
    // (No getClasses in repository, implement if needed)
    // Delete Grade
    public function deleteGrade($id)
    {
        $deleted = $this->academicService->deleteGrade($id);

        return redirect()->back()->with($deleted ? 'success' : 'error', $deleted ? __('Grade deleted successfully') : __('Grade not found'));
    }

    // Classes
    public function storeClass(StoreClassRequest $request)
    {
        $validated = $request->validated();

        if (empty($validated['batch_id'])) {
            $grade = Grade::findOrFail($validated['grade_id']);
            $validated['batch_id'] = $grade->batch_id;
        }

        $classData = SchoolClassData::from($validated);

        try {
            $this->academicService->createClass($classData->toArray());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return back()->with('success', __('Class created successfully'));
    }

    public function showClass(Request $request, string $id)
    {
        $class = $this->academicRepository->findClass($id);

        abort_unless($class, 404);

        // Load additional relationships for detail view
        $class->load([
            'students.user',
            'enrolledStudents.user',
            'grade.subjects.teachers.department',
            'grade.subjects.teachers.user',
            'room',
            'teacher.user',
            'batch',
        ]);

        $students = $class->students
            ->concat($class->enrolledStudents)
            ->unique('id')
            ->values();
        $totalStudents = $students->count();

        $settings = \App\Models\Setting::first();
        
        // Get the active timetable for this class
        $timetable = Timetable::with(['periods.subject', 'periods.teacher.user', 'periods.room'])
            ->where('class_id', $class->id)
            ->where('is_active', true)
            ->first();

        // If no active timetable, get the most recent one
        if (!$timetable) {
            $timetable = Timetable::with(['periods.subject', 'periods.teacher.user', 'periods.room'])
                ->where('class_id', $class->id)
                ->orderByDesc('created_at')
                ->first();
        }

        // Get teachers from timetable periods
        $teacherRows = collect();
        if ($timetable && $timetable->periods) {
            foreach ($timetable->periods as $period) {
                if ($period->teacher && $period->subject && !$period->is_break) {
                    $teacherRows->push([
                        'teacher_id' => $period->teacher->employee_id ?? ($period->teacher->id ?? '—'),
                        'name' => $period->teacher->user?->name ?? '—',
                        'subject' => $period->subject->name ?? '—',
                        'teacher' => $period->teacher,
                    ]);
                }
            }
        }
        
        // Remove duplicates based on teacher_id and subject combination
        $uniqueTeachers = $teacherRows->unique(fn($row) => ($row['teacher_id'] ?? '') . '|' . ($row['subject'] ?? ''))->values();
        $totalTeachers = $uniqueTeachers->unique('teacher_id')->count();

        $timetableWeekDays = collect($timetable?->week_days ?? $settings?->week_days ?? ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'])
            ->map(function ($day) {
                $day = strtolower($day);
                // Convert to short format (3 letters) for consistency
                if (strlen($day) > 3) {
                    return substr($day, 0, 3);
                }
                return $day;
            })
            ->values()
            ->all();

        $timetablePeriods = (int) ($timetable?->periods?->max('period_number') ?? 0);
        if ($timetablePeriods < 1) {
            $timetablePeriods = (int) ($settings?->number_of_periods_per_day ?? 6);
        }

        $timetableEntries = [];
        $timetablePeriodLabels = [];

        if ($timetable) {
            foreach ($timetable->periods as $period) {
                $dayKey = strtolower($period->day_of_week ?? '');
                if ($dayKey === '') {
                    continue;
                }
                if (strlen($dayKey) > 3) {
                    $dayKey = substr($dayKey, 0, 3);
                }

                $periodNumber = (int) $period->period_number;
                if ($periodNumber < 1) {
                    continue;
                }

                $subjectName = $period->is_break
                    ? 'Break'
                    : ($period->subject?->name ?? __('academic_management.Subject'));
                $teacherName = $period->is_break
                    ? ''
                    : ($period->teacher?->user?->name ?? __('academic_management.Teacher'));
                $roomName = $period->room?->name ?? null;

                $timetableEntries[$dayKey][$periodNumber] = [
                    'subject' => $subjectName,
                    'teacher' => $teacherName,
                    'room' => $roomName,
                ];

                $start = $period->starts_at;
                $end = $period->ends_at;
                if ($start && $end && empty($timetablePeriodLabels[$periodNumber])) {
                    $startLabel = $start instanceof \Carbon\Carbon ? $start->format('H:i') : substr((string) $start, 0, 5);
                    $endLabel = $end instanceof \Carbon\Carbon ? $end->format('H:i') : substr((string) $end, 0, 5);
                    $timetablePeriodLabels[$periodNumber] = "{$startLabel} - {$endLabel}";
                }
            }
        }

        $teachersPage = max(1, (int) $request->get('teachers_page', 1));
        $studentsPage = max(1, (int) $request->get('students_page', 1));
        $perPage = 10;

        $teachersPaginated = new LengthAwarePaginator(
            $uniqueTeachers->forPage($teachersPage, $perPage)->values(),
            $uniqueTeachers->count(),
            $perPage,
            $teachersPage,
            [
                'path' => $request->url(),
                'pageName' => 'teachers_page',
                'query' => $request->query(),
            ]
        );

        $studentsPaginated = new LengthAwarePaginator(
            $students->forPage($studentsPage, $perPage)->values(),
            $students->count(),
            $perPage,
            $studentsPage,
            [
                'path' => $request->url(),
                'pageName' => 'students_page',
                'query' => $request->query(),
            ]
        );

        $grades = $this->academicRepository->getGradesWithCounts();
        $rooms = $this->academicRepository->getRoomsWithCounts();
        $teachers = $this->academicRepository->getTeachers();

        return view('academic.class-detail', compact(
            'class',
            'grades',
            'rooms',
            'teachers',
            'students',
            'totalStudents',
            'totalTeachers',
            'teachersPaginated',
            'studentsPaginated',
            'timetableEntries',
            'timetableWeekDays',
            'timetablePeriods',
            'timetablePeriodLabels'
        ));
    }

    public function editClass(string $id)
    {
        $class = $this->academicRepository->findClass($id);

        abort_unless($class, 404);

        $grades = $this->academicRepository->getGradesWithCounts();
        $rooms = $this->academicRepository->getRoomsWithCounts();
        $teachers = $this->academicRepository->getTeachers();

        return view('academic.class-edit', compact('class', 'grades', 'rooms', 'teachers'));
    }

    public function updateClass(UpdateClassRequest $request, string $id)
    {
        $validated = $request->validated();

        if (empty($validated['batch_id'])) {
            $grade = Grade::findOrFail($validated['grade_id']);
            $validated['batch_id'] = $grade->batch_id;
        }

        $classData = SchoolClassData::from($validated);

        try {
            $this->academicService->updateClass($id, $classData->toArray());
            
            $this->logUpdate('Class', $id, $validated['name'] ?? null);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return back()->with('success', __('Class updated successfully'));
    }

    public function destroyClass(string $id)
    {
        $class = $this->academicRepository->findClass($id);
        $className = $class?->name;
        
        $deleted = $this->academicService->deleteClass($id);

        if ($deleted) {
            $this->logDelete('Class', $id, $className);
        }

        return back()->with($deleted ? 'success' : 'error', $deleted ? __('Class deleted successfully') : __('Class not found'));
    }

    public function searchStudents(Request $request): JsonResponse
    {
        $search = $request->get('search', '');

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $students = StudentProfile::with('user')
            ->whereNull('class_id')
            ->where(function ($query) use ($search) {
                $query->where('student_identifier', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            })
            ->limit(10)
            ->get()
            ->map(function ($profile) {
                return [
                    'id' => $profile->id,
                    'name' => $profile->user->name ?? '—',
                    'email' => $profile->user->email ?? '—',
                    'phone' => $profile->user->phone ?? '—',
                    'student_identifier' => $profile->student_identifier,
                ];
            });

        return response()->json($students);
    }

    public function addStudentToClass(Request $request, SchoolClass $class): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'uuid', 'exists:student_profiles,id'],
        ]);

        $student = StudentProfile::with('user')->findOrFail($validated['student_id']);

        if ($student->class_id && $student->class_id !== $class->id) {
            return response()->json([
                'success' => false,
                'message' => __('academic_management.Student already assigned to another class.'),
            ], 422);
        }

        $class->students()->syncWithoutDetaching([
            $student->id => [
                'id' => (string) Str::uuid(),
                'batch_id' => $class->batch_id,
                'grade_id' => $class->grade_id,
                'status' => 'enrolled',
            ],
        ]);

        $student->update([
            'class_id' => $class->id,
            'grade_id' => $class->grade_id,
        ]);

        $this->logCreate('Student-Class Assignment', $student->id, ($student->user->name ?? 'Student') . ' to ' . $class->name);

        return response()->json([
            'success' => true,
            'message' => __('academic_management.Student added to class successfully'),
            'student' => [
                'id' => $student->id,
                'name' => $student->user->name ?? '—',
                'student_identifier' => $student->student_identifier,
                'phone' => $student->user->phone ?? '—',
            ],
        ]);
    }

    public function deleteRoom($id)
    {
        $room = $this->academicRepository->findRoom($id);
        $roomName = $room?->name;
        
        $deleted = $this->academicService->deleteRoom($id);

        if ($deleted) {
            $this->logDelete('Room', $id, $roomName);
        }

        return redirect()->back()->with($deleted ? 'success' : 'error', $deleted ? __('Room deleted successfully') : __('Room not found'));
    }

    // Delete Subject
    public function deleteSubject($id)
    {
        $subject = $this->academicRepository->findSubject($id);
        $subjectName = $subject?->name;
        
        $deleted = $this->academicService->deleteSubject($id);

        if ($deleted) {
            $this->logDelete('Subject', $id, $subjectName);
        }

        return redirect()->back()->with($deleted ? 'success' : 'error', $deleted ? __('Subject deleted successfully') : __('Subject not found'));
    }

    // Rooms
    public function indexRooms()
    {
        $rooms = $this->academicRepository->getRooms();
        return view('academic.academic-management', compact('rooms'));
    }

    public function storeRoom(StoreRoomRequest $request)
    {
        try {
            $room = $this->academicService->createRoom(RoomData::from($request->validated()));
            
            $this->logCreate('Room', $room->id ?? '', $request->validated()['name'] ?? null);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->back()->with('success', __('Room created successfully'));
    }

    public function showRoom(string $id)
    {
        $room = $this->academicRepository->findRoom($id);

        abort_unless($room, 404);

        // Load relationships for detail view with sorted classes
        $room->load(['classes' => function ($query) {
            $query->join('grades', 'classes.grade_id', '=', 'grades.id')
                  ->orderBy('grades.level', 'asc')
                  ->orderBy('classes.name', 'asc')
                  ->select('classes.*');
        }, 'classes.grade', 'classes.students', 'classes.teacher', 'facilities']);

        return view('academic.room-detail', compact('room'));
    }

    public function editRoom(string $id)
    {
        $room = $this->academicRepository->findRoom($id);

        abort_unless($room, 404);

        return view('academic.room-edit', compact('room'));
    }

    public function updateRoom(UpdateRoomRequest $request, string $id)
    {
        try {
            $this->academicService->updateRoom($id, RoomData::from($request->validated()));
            
            $this->logUpdate('Room', $id, $request->validated()['name'] ?? null);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return back()->with('success', __('Room updated successfully'));
    }

    // Subjects
    public function indexSubjects()
    {
        $subjects = $this->academicRepository->getSubjects();
        return view('academic.academic-management', compact('subjects'));
    }

    public function storeSubject(StoreSubjectRequest $request)
    {
        try {
            $subject = $this->academicService->createSubject(SubjectData::from($request->validated()));
            
            $this->logCreate('Subject', $subject->id ?? '', $request->validated()['name'] ?? null);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->back()->with('success', __('Subject created successfully'));
    }

    public function showSubject(string $id)
    {
        $subject = $this->academicRepository->findSubject($id);

        abort_unless($subject, 404);

        // Load relationships for detail view including curriculum
        $subject->load([
            'subjectType', 
            'grades', 
            'teachers.department', 
            'teachers.user',
            'curriculumChapters.topics'
        ]);

        $subjectTypes = $this->academicRepository->getSubjectTypes();
        $teachers = $this->academicRepository->getTeachers();
        $grades = $this->academicRepository->getGradesWithCounts();

        return view('academic.subject-detail', compact('subject', 'subjectTypes', 'teachers', 'grades'));
    }

    public function editSubject(string $id)
    {
        $subject = $this->academicRepository->findSubject($id);

        abort_unless($subject, 404);

        $subjectTypes = $this->academicRepository->getSubjectTypes();

        return view('academic.subject-edit', compact('subject', 'subjectTypes'));
    }

    public function updateSubject(UpdateSubjectRequest $request, string $id)
    {
        try {
            $this->academicService->updateSubject($id, SubjectData::from($request->validated()));
            
            $this->logUpdate('Subject', $id, $request->validated()['name'] ?? null);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return back()->with('success', __('Subject updated successfully'));
    }

    // Attach Teacher to Subject
    public function attachTeacher(Request $request, $id)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teacher_profiles,id',
        ]);

        $subject = $this->academicRepository->findSubject($id);
        
        if (!$subject) {
            return back()->with('error', __('Subject not found'));
        }

        // Check if teacher is already attached
        if ($subject->teachers()->where('teacher_profile_id', $request->teacher_id)->exists()) {
            return back()->with('error', __('Teacher is already assigned to this subject'));
        }

        // Attach teacher to subject
        $subject->teachers()->attach($request->teacher_id);
        
        $teacher = \App\Models\TeacherProfile::with('user')->find($request->teacher_id);
        $this->logCreate('Teacher-Subject Assignment', $request->teacher_id, ($teacher?->user?->name ?? 'Teacher') . ' to ' . $subject->name);

        return back()->with('success', __('Teacher assigned successfully'));
    }

    // Detach Teacher from Subject
    public function detachTeacher($subjectId, $teacherId)
    {
        $subject = $this->academicRepository->findSubject($subjectId);
        
        if (!$subject) {
            return back()->with('error', __('Subject not found'));
        }

        $teacher = \App\Models\TeacherProfile::with('user')->find($teacherId);

        // Detach teacher from subject
        $subject->teachers()->detach($teacherId);
        
        $this->logDelete('Teacher-Subject Assignment', $teacherId, ($teacher?->user?->name ?? 'Teacher') . ' from ' . $subject->name);

        return back()->with('success', __('Teacher removed successfully'));
    }
}
