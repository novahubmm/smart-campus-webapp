<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;
use App\Models\KeyContact;
use App\Models\RuleCategory;
use App\Models\SchoolRule;
use App\Models\Period;

class GuardianPortalController extends Controller
{
    public function __construct(
        private readonly \App\Services\AnnouncementService $announcementService,
        private readonly \App\Services\EventService $eventService,
        private readonly \App\Interfaces\Guardian\GuardianStudentRepositoryInterface $guardianStudentRepository,
        private readonly \App\Interfaces\Guardian\GuardianExamRepositoryInterface $guardianExamRepository,
        private readonly \App\Interfaces\Guardian\GuardianTimetableRepositoryInterface $guardianTimetableRepository,
        private readonly \App\Interfaces\Guardian\GuardianAttendanceRepositoryInterface $guardianAttendanceRepository,
        private readonly \App\Interfaces\Guardian\GuardianHomeworkRepositoryInterface $guardianHomeworkRepository,
        private readonly \App\Interfaces\Guardian\GuardianLeaveRequestRepositoryInterface $guardianLeaveRequestRepository,
        private readonly \App\Interfaces\Guardian\GuardianFeeRepositoryInterface $guardianFeeRepository
    ) {
    }
    /**
     * Display the guardian dashboard.
     */
    public function index(Request $request)
    {
        $student = $this->getSelectedStudent($request);
        if (!$student) {
            return view('dashboard.guardian');
        }

        $ongoingClass = $this->guardianTimetableRepository->getOngoingClass($student);
        $todaySchedule = $this->guardianTimetableRepository->getTodaySchedule($student);

        return view('dashboard.guardian', compact('student', 'ongoingClass', 'todaySchedule'));
    }

    /**
     * Display students (My Children).
     */
    public function students()
    {
        return view('guardian.students');
    }

    /**
     * Display attendance for children.
     */
    public function attendance(Request $request)
    {
        $student = $this->getSelectedStudent($request);
        if (!$student)
            return redirect()->route('guardian.dashboard');

        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $records = $this->guardianAttendanceRepository->getAttendanceRecords($student, $month, $year);
        $summary = $this->guardianAttendanceRepository->getAttendanceSummary($student, $month, $year);
        $stats = $this->guardianAttendanceRepository->getAttendanceStats($student);

        return view('guardian.attendance', compact('student', 'records', 'summary', 'stats', 'month', 'year'));
    }

    /**
     * Display homework for children.
     */
    public function homework(Request $request)
    {
        $student = $this->getSelectedStudent($request);
        if (!$student)
            return redirect()->route('guardian.dashboard');

        $status = $request->query('status'); // pending, completed, overdue
        $homework = $this->guardianHomeworkRepository->getHomework($student, $status);
        $stats = $this->guardianHomeworkRepository->getHomeworkStats($student);

        return view('guardian.homework', compact('student', 'homework', 'stats', 'status'));
    }

    /**
     * Display classes (Class of student).
     */
    public function classes(Request $request)
    {
        $student = $this->getSelectedStudent($request);
        if (!$student)
            return redirect()->route('guardian.dashboard');

        $classInfo = $this->guardianTimetableRepository->getClassInfo($student);
        // Fetch subjects and timetable for the new view structure
        $subjects = $this->guardianExamRepository->getSubjects($student);
        $weeklyTimetable = $this->guardianTimetableRepository->getFullTimetable($student);

        // We no longer need the students list as per the requirement

        return view('guardian.classes', compact('student', 'classInfo', 'subjects', 'weeklyTimetable'));
    }

    /**
     * Display exams for student.
     */
    public function exams(Request $request)
    {
        $student = $this->getSelectedStudent($request);
        if (!$student)
            return redirect()->route('guardian.dashboard');

        $exams = $this->guardianExamRepository->getExams($student);

        return view('guardian.exams', compact('student', 'exams'));
    }

    /**
     * Display subjects for student.
     */
    public function subjects(Request $request)
    {
        $student = $this->getSelectedStudent($request);
        if (!$student)
            return redirect()->route('guardian.dashboard');

        $subjects = $this->guardianExamRepository->getSubjects($student);

        return view('guardian.subjects', compact('student', 'subjects'));
    }

    /**
     * Display timetable for student.
     */
    public function timetable(Request $request)
    {
        $student = $this->getSelectedStudent($request);
        if (!$student)
            return redirect()->route('guardian.dashboard');

        $weeklyTimetable = $this->guardianTimetableRepository->getFullTimetable($student);

        return view('guardian.timetable', compact('student', 'weeklyTimetable'));
    }

    /**
     * Display details for an ongoing or specific class period.
     */
    public function ongoingClassDetail(Request $request, $periodId)
    {
        $student = $this->getSelectedStudent($request);
        if (!$student)
            return redirect()->route('guardian.dashboard');

        $period = Period::with(['subject', 'teacher.user', 'room', 'timetable.schoolClass'])
            ->findOrFail($periodId);

        $class = $period->timetable->schoolClass;
        $selectedDate = now(); // For guardian, we usually show today's activity

        // Logic reused from OngoingClassController@getActivitySummary but tailored for one student
        $attendance = \App\Models\StudentAttendance::where('student_id', $student->id)
            ->where('period_id', $period->id)
            ->where('date', $selectedDate->toDateString())
            ->first();

        $remarks = \App\Models\StudentRemark::where('student_id', $student->id)
            ->where('period_id', $period->id)
            ->where('date', $selectedDate->toDateString())
            ->with('teacher.user')
            ->get();

        $homework = \App\Models\Homework::where('class_id', $class->id)
            ->where('subject_id', $period->subject_id)
            ->where('assigned_date', $selectedDate->toDateString())
            ->get();

        return view('guardian.ongoing-class-detail', compact('student', 'period', 'class', 'attendance', 'remarks', 'homework', 'selectedDate'));
    }

    /**
     * Display leave requests history and form.
     */
    public function leaveRequests(Request $request)
    {
        $student = $this->getSelectedStudent($request);
        if (!$student)
            return redirect()->route('guardian.dashboard');

        $requests = $this->guardianLeaveRequestRepository->getLeaveRequests($student);
        $stats = $this->guardianLeaveRequestRepository->getLeaveStats($student);
        $leaveTypes = $this->guardianLeaveRequestRepository->getLeaveTypes();

        return view('guardian.leave-requests', compact('student', 'requests', 'stats', 'leaveTypes'));
    }

    /**
     * Store a new leave request.
     */
    public function storeLeaveRequest(Request $request)
    {
        $student = $this->getSelectedStudent($request);
        if (!$student)
            return redirect()->route('guardian.dashboard');

        $data = $request->validate([
            'leave_type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
        ]);

        $this->guardianLeaveRequestRepository->createLeaveRequest($student, Auth::user()->guardianProfile->id, $data);

        return redirect()->route('guardian.leave-requests')->with('success', 'Leave request submitted successfully.');
    }

    /**
     * Display announcements and events.
     */
    public function announcements(Request $request)
    {
        $announcements = $this->announcementService->list(
            \App\DTOs\Announcement\AnnouncementFilterData::from([
                'status' => 'published',
                'role' => 'guardian'
            ]),
            10
        );

        $events = $this->eventService->list(
            \App\DTOs\Event\EventFilterData::from([
                'status' => 'published'
            ])
        );

        return view('guardian.announcements', compact('announcements', 'events'));
    }

    /**
     * Display student profile.
     */
    public function profile(Request $request)
    {
        $selectedStudent = $this->getSelectedStudent($request);
        if (!$selectedStudent)
            return redirect()->route('guardian.dashboard');

        // Fetch attendance stats
        $totalDays = \App\Models\StudentAttendance::where('student_id', $selectedStudent->id)->count();
        $presentDays = \App\Models\StudentAttendance::where('student_id', $selectedStudent->id)->where('status', 'present')->count();
        $attendancePercentage = $totalDays > 0 ? round(($presentDays / $totalDays) * 100) : 0;

        return view('guardian.profile', compact('selectedStudent', 'attendancePercentage'));
    }

    /**
     * Display school fees.
     */
    public function fees(Request $request)
    {
        $student = $this->getSelectedStudent($request);
        if (!$student)
            return redirect()->route('guardian.dashboard');

        $filters = [
            'status' => $request->query('status'),
            'per_page' => 20
        ];

        $invoices = $this->guardianFeeRepository->getAllFees($student, $filters);
        $pendingFee = $this->guardianFeeRepository->getPendingFee($student);

        return view('guardian.fees', compact('student', 'invoices', 'pendingFee'));
    }

    /**
     * Display utilities.
     */
    public function utilities()
    {
        return view('guardian.utilities');
    }

    /**
     * Display school info.
     */
    public function schoolInfo()
    {
        $setting = Setting::first();
        $contacts = KeyContact::where('setting_id', $setting?->id ?? '00000000-0000-0000-0000-000000000001')
            ->orderByDesc('is_primary')
            ->orderBy('name')
            ->get();

        return view('guardian.school-info', compact('setting', 'contacts'));
    }

    /**
     * Display school rules.
     */
    public function rules()
    {
        $categories = RuleCategory::withCount('rules')
            ->with(['rules' => fn($query) => $query->orderBy('sort_order')])
            ->orderBy('title')
            ->get();

        return view('guardian.rules', compact('categories'));
    }

    /**
     * Display messages.
     */
    public function messages()
    {
        return view('guardian.messages');
    }

    /**
     * Get the currently selected student.
     */
    private function getSelectedStudent(Request $request)
    {
        $user = Auth::user();
        $guardianProfile = $user->guardianProfile;
        if (!$guardianProfile)
            return null;

        $selectedStudentId = $request->query('student_id') ?? session('global_student_id');
        $students = $guardianProfile->students;

        if ($students->count() === 0)
            return null;

        if ($selectedStudentId) {
            $selectedStudent = $students->where('id', $selectedStudentId)->first();
            return $selectedStudent ?? $students->first();
        }

        return $students->first();
    }
}
