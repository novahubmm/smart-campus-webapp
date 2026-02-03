<?php

namespace App\Http\Controllers;

use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Grade;
use App\Models\TeacherProfile;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeworkController extends Controller
{
    use LogsActivity;
    public function index(Request $request)
    {
        $gradeId = $request->get('grade_id');
        $classId = $request->get('class_id');
        $subjectId = $request->get('subject_id');
        $status = $request->get('status');

        $query = Homework::with(['schoolClass.grade', 'subject', 'teacher.user', 'submissions'])
            ->orderBy('due_date', 'desc');

        if ($gradeId) {
            $query->whereHas('schoolClass', fn($q) => $q->where('grade_id', $gradeId));
        }
        if ($classId) {
            $query->where('class_id', $classId);
        }
        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }
        if ($status) {
            $query->where('status', $status);
        }

        $homework = $query->paginate(15);

        $grades = Grade::orderBy('level')->get();
        $classes = SchoolClass::with('grade')->orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        $teachers = TeacherProfile::with('user')->get();

        // Stats
        $totalHomework = Homework::count();
        $activeHomework = Homework::where('status', 'active')->count();
        $completedHomework = Homework::where('status', 'completed')->count();

        return view('academic.homework', compact(
            'homework',
            'grades',
            'classes',
            'subjects',
            'teachers',
            'totalHomework',
            'activeHomework',
            'completedHomework',
            'gradeId',
            'classId',
            'subjectId',
            'status'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'due_date' => 'required|date|after_or_equal:today',
            'priority' => 'in:low,medium,high',
            'attachment' => 'nullable|file|max:10240',
        ]);

        // Get teacher from current user
        $teacher = TeacherProfile::where('user_id', Auth::id())->first();

        $homework = Homework::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'class_id' => $validated['class_id'],
            'subject_id' => $validated['subject_id'],
            'teacher_id' => $teacher?->id ?? TeacherProfile::first()?->id,
            'assigned_date' => now(),
            'due_date' => $validated['due_date'],
            'priority' => $validated['priority'] ?? 'medium',
            'status' => 'active',
        ]);

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('homework', 'public');
            $homework->update(['attachment' => $path]);
        }

        $this->logCreate('Homework', $homework->id, $homework->title);

        return redirect()->route('homework.index')
            ->with('success', 'Homework created successfully.');
    }

    public function show(Homework $homework)
    {
        $homework->load([
            'schoolClass.grade',
            'subject',
            'teacher.user',
            'submissions.student.user'
        ]);

        $class = $homework->schoolClass;
        $students = $class->enrolledStudents()->with('user')->paginate(10, ['*'], 'submissions_page');

        // Map submissions to students
        $submissionMap = $homework->submissions->keyBy('student_id');

        return view('academic.homework-detail', compact('homework', 'students', 'submissionMap'));
    }

    public function update(Request $request, Homework $homework)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'priority' => 'in:low,medium,high',
            'status' => 'in:active,completed,cancelled',
        ]);

        $homework->update($validated);

        $this->logUpdate('Homework', $homework->id, $homework->title);

        return redirect()->back()->with('success', 'Homework updated successfully.');
    }

    public function destroy(Homework $homework)
    {
        $homeworkTitle = $homework->title;
        $homeworkId = $homework->id;
        $homework->delete();

        $this->logDelete('Homework', $homeworkId, $homeworkTitle);

        return redirect()->route('homework.index')
            ->with('success', 'Homework deleted successfully.');
    }

    /**
     * API: Get classes by grade
     */
    public function getClassesByGrade($gradeId)
    {
        $classes = SchoolClass::where('grade_id', $gradeId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($classes);
    }

    /**
     * API: Get subjects by grade
     */
    public function getSubjectsByGrade($gradeId)
    {
        $grade = Grade::find($gradeId);
        $subjects = $grade ? $grade->subjects()->orderBy('name')->get(['subjects.id', 'subjects.name']) : [];

        return response()->json($subjects);
    }
}
