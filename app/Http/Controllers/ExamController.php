<?php

namespace App\Http\Controllers;

use App\DTOs\Exam\ExamData;
use App\DTOs\Exam\ExamFilterData;
use App\DTOs\Exam\ExamMarkData;
use App\Http\Requests\Exam\StoreExamMarkRequest;
use App\Http\Requests\Exam\StoreExamRequest;
use App\Http\Requests\Exam\UpdateExamMarkRequest;
use App\Http\Requests\Exam\UpdateExamRequest;
use App\Models\Batch;
use App\Models\Exam;
use App\Models\ExamMark;
use App\Models\ExamType;
use App\Models\Grade;
use App\Models\Room;
use App\Models\SchoolClass;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Services\ExamService;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExamController extends Controller
{
    use LogsActivity;

    public function __construct(private readonly ExamService $service) {}

    public function index(Request $request): View
    {
        $filter = ExamFilterData::from($request->all());
        $exams = $this->service->list($filter);
        $stats = $this->service->stats($filter);

        $examTypes = ExamType::orderBy('name')->get();
        $batches = Batch::orderBy('name')->get();
        $grades = Grade::orderBy('level')->get();
        $classes = SchoolClass::with('grade')->orderBy('name')->get();
        $subjects = Subject::with('grades')->orderBy('name')->get();
        $rooms = Room::orderBy('name')->get();
        $students = StudentProfile::with('user', 'grade', 'classModel')->orderBy('student_identifier')->get();
        $teachers = TeacherProfile::with('user')->get();

        $examsForFront = $exams->getCollection()->map(function (Exam $exam) {
            $statusState = $this->resolveStatus($exam);
            return [
                'id' => $exam->id,
                'exam_id' => $exam->exam_id,
                'name' => $exam->name,
                'description' => $exam->description,
                'exam_type_id' => $exam->exam_type_id,
                'exam_type' => $exam->examType?->name,
                'batch_id' => $exam->batch_id,
                'batch' => $exam->batch?->name,
                'grade_id' => $exam->grade_id,
                'grade' => $exam->grade?->level,
                'class_id' => $exam->class_id,
                'class_name' => $exam->schoolClass?->name,
                'start_date' => optional($exam->start_date)->toDateString(),
                'end_date' => optional($exam->end_date)->toDateString(),
                'status' => $exam->status,
                'status_label' => $statusState['label'],
                'status_class' => $statusState['class'],
                'subject_list' => $exam->schedules->pluck('subject.name')->filter()->unique()->values()->all(),
                'schedules' => $exam->schedules->map(function ($schedule) {
                    return [
                        'id' => $schedule->id,
                        'subject_id' => $schedule->subject_id,
                        'subject_name' => $schedule->subject?->name,
                        'class_id' => $schedule->class_id,
                        'class_name' => $schedule->class?->name,
                        'exam_date' => optional($schedule->exam_date)->toDateString(),
                        'start_time' => $schedule->start_time,
                        'end_time' => $schedule->end_time,
                        'room_id' => $schedule->room_id,
                        'room_name' => $schedule->room?->name,
                        'teacher_id' => $schedule->teacher_id ?? null,
                        'total_marks' => $schedule->total_marks,
                        'passing_marks' => $schedule->passing_marks,
                    ];
                })->values()->all(),
            ];
        });

        return view('exams.index', compact(
            'exams',
            'examsForFront',
            'stats',
            'examTypes',
            'batches',
            'grades',
            'classes',
            'subjects',
            'rooms',
            'students',
            'teachers',
            'filter'
        ));
    }

    public function show(Exam $exam): View
    {
        $exam->load(['examType', 'batch', 'grade', 'schoolClass', 'schedules.subject', 'schedules.class', 'schedules.room', 'marks.student.user', 'marks.subject']);

        $statusState = $this->resolveStatus($exam);

        $examTypes = ExamType::orderBy('name')->get();
        $grades = Grade::orderBy('level')->get();
        $classes = SchoolClass::with('grade')->orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        $rooms = Room::orderBy('name')->get();
        $teachers = TeacherProfile::with('user')->get();

        return view('exams.show', compact('exam', 'statusState', 'examTypes', 'grades', 'classes', 'subjects', 'rooms', 'teachers'));
    }

    public function store(StoreExamRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        
        // Get batch_id from the selected grade
        if (isset($validated['grade_id'])) {
            $grade = Grade::find($validated['grade_id']);
            if ($grade && $grade->batch_id) {
                $validated['batch_id'] = $grade->batch_id;
            }
        }
        
        $data = ExamData::from($validated);
        $exam = $this->service->create($data);

        $this->logCreate('Exam', $exam->id ?? '', $validated['name'] ?? null);

        return redirect()->route('exams.index')->with('success', __('Exam created successfully.'));
    }

    public function update(UpdateExamRequest $request, Exam $exam): RedirectResponse
    {
        $validated = $request->validated();
        
        // Get batch_id from the selected grade
        if (isset($validated['grade_id'])) {
            $grade = Grade::find($validated['grade_id']);
            if ($grade && $grade->batch_id) {
                $validated['batch_id'] = $grade->batch_id;
            }
        }
        
        $data = ExamData::from($validated);
        $this->service->update($exam, $data);

        $this->logUpdate('Exam', $exam->id, $exam->name);

        return redirect()->route('exams.index')->with('success', __('Exam updated successfully.'));
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $examId = $exam->id;
        $examName = $exam->name;

        $this->service->delete($exam);

        $this->logDelete('Exam', $examId, $examName);

        return redirect()->route('exams.index')->with('success', __('Exam deleted successfully.'));
    }

    public function storeMark(StoreExamMarkRequest $request): RedirectResponse
    {
        $data = ExamMarkData::from($request->validated(), $request->user()?->id);
        $this->service->storeMark($data);

        return back()->with('success', __('Exam mark saved.'));
    }

    public function updateMark(UpdateExamMarkRequest $request, ExamMark $examMark): RedirectResponse
    {
        $validated = $request->validated();
        
        // Ensure exam_id is present for the DTO (use existing exam_id if not provided)
        if (!isset($validated['exam_id'])) {
            $validated['exam_id'] = $examMark->exam_id;
        }
        
        // Ensure student_id is present for the DTO (use existing student_id if not provided)
        if (!isset($validated['student_id'])) {
            $validated['student_id'] = $examMark->student_id;
        }
        
        // Ensure subject_id is present for the DTO (use existing subject_id if not provided)
        if (!isset($validated['subject_id'])) {
            $validated['subject_id'] = $examMark->subject_id;
        }
        
        $data = ExamMarkData::from($validated, $request->user()?->id);
        $this->service->updateMark($examMark, $data);

        return back()->with('success', __('Exam mark updated.'));
    }

    public function destroyMark(ExamMark $examMark): RedirectResponse
    {
        $this->service->deleteMark($examMark);

        return back()->with('success', __('Exam mark deleted.'));
    }

    private function resolveStatus(Exam $exam): array
    {
        $status = $exam->status;
        
        return match($status) {
            'upcoming' => ['label' => __('Upcoming'), 'class' => 'upcoming'],
            'ongoing' => ['label' => __('Ongoing'), 'class' => 'ongoing'],
            'completed' => ['label' => __('Completed'), 'class' => 'completed'],
            default => ['label' => __('Upcoming'), 'class' => 'upcoming'],
        };
    }
}
