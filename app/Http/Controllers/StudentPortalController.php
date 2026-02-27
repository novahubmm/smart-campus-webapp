<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Homework;
use App\Models\CurriculumChapter;
use App\Models\Event;
use App\Models\Announcement;
use App\Models\Grade;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentPortalController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $studentProfile = $user->studentProfile;

        if (!$studentProfile) {
            return redirect()->route('dashboard')->with('error', 'Student profile not found.');
        }

        $upcomingExams = Exam::where('grade_id', $studentProfile->grade_id)
            ->where('start_date', '>=', now())
            ->orderBy('start_date')
            ->limit(5)
            ->get();

        $activeHomework = Homework::where('class_id', $studentProfile->class_id)
            ->where('status', 'active')
            ->where('due_date', '>=', now())
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        $announcements = Announcement::orderBy('created_at', 'desc')->limit(5)->get();

        return view('dashboard.student', compact('studentProfile', 'upcomingExams', 'activeHomework', 'announcements'));
    }

    public function exams()
    {
        $studentProfile = Auth::user()->studentProfile;

        $exams = Exam::with(['examType', 'schedules.subject', 'schedules.room'])
            ->where('grade_id', $studentProfile->grade_id)
            ->orderBy('start_date', 'desc')
            ->paginate(10);

        return view('student.exams', compact('exams'));
    }

    public function homework()
    {
        $studentProfile = Auth::user()->studentProfile;

        $homework = Homework::with(['subject', 'teacher.user'])
            ->where('class_id', $studentProfile->class_id)
            ->orderBy('due_date', 'desc')
            ->paginate(10);

        return view('student.homework', compact('homework'));
    }

    public function lessonPlan(Request $request)
    {
        $studentProfile = Auth::user()->studentProfile;
        $gradeId = $studentProfile->grade_id;
        $subjectId = $request->get('subject_id');

        $subjects = Subject::whereHas('grades', function ($q) use ($gradeId) {
            $q->where('grades.id', $gradeId);
        })->get();

        $chapters = CurriculumChapter::with('topics')
            ->where('grade_id', $gradeId)
            ->when($subjectId, fn($q) => $q->where('subject_id', $subjectId))
            ->orderBy('order')
            ->get();

        return view('student.lesson-plan', compact('chapters', 'subjects', 'subjectId'));
    }

    public function resourceHub()
    {
        return view('student.resource-hub');
    }

    public function eventsAndAnnouncements()
    {
        $events = Event::with('category')->orderBy('start_date', 'desc')->paginate(5, ['*'], 'events_page');
        $announcements = Announcement::orderBy('created_at', 'desc')->paginate(10, ['*'], 'announcements_page');

        return view('student.events-announcements', compact('events', 'announcements'));
    }
}
