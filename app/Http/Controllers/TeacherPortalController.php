<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherPortalController extends Controller
{
    /**
     * Display the teacher dashboard.
     */
    public function __construct(
        private readonly \App\Interfaces\Teacher\TeacherDashboardRepositoryInterface $teacherDashboardRepository
    ) {
    }

    public function index()
    {
        $user = Auth::user();
        $teacherProfile = $user->teacherProfile;

        if (!$teacherProfile) {
            return view('dashboard.teacher');
        }

        $todayClasses = $this->teacherDashboardRepository->getTodayClasses($user);
        $ongoingClass = $todayClasses->firstWhere('status', 'ongoing');
        $todaySchedule = $todayClasses;

        return view('dashboard.teacher', compact('teacherProfile', 'ongoingClass', 'todaySchedule'));
    }

    /**
     * Display the teacher academic page.
     */
    public function academic()
    {
        return view('teacher.academic');
    }

    /**
     * Display assigned classes.
     */
    public function myClasses()
    {
        return view('teacher.my-classes');
    }

    /**
     * Display subjects.
     */
    public function subjects()
    {
        return view('teacher.subjects');
    }

    /**
     * Display schedule/timetable.
     */
    public function schedule()
    {
        return view('teacher.schedule');
    }

    /**
     * Display attendance management.
     */
    public function attendance()
    {
        return view('teacher.attendance');
    }

    /**
     * Display homework management.
     */
    public function homework()
    {
        return view('teacher.homework');
    }

    /**
     * Display exams management.
     */
    public function exams()
    {
        return view('teacher.exams');
    }

    /**
     * Display resources/files.
     */
    public function resources()
    {
        return view('teacher.resources');
    }

    /**
     * Display utilities.
     */
    public function utilities()
    {
        return view('teacher.utilities');
    }

    /**
     * Display payslips/salary.
     */
    public function payslips()
    {
        return view('teacher.payslips');
    }

    /**
     * Display school info.
     */
    public function schoolInfo()
    {
        $settings = \App\Models\Setting::all()->pluck('value', 'key');
        $contacts = \App\Models\KeyContact::all();
        return view('teacher.school-info', compact('settings', 'contacts'));
    }

    /**
     * Display school rules.
     */
    public function rules()
    {
        $categories = \App\Models\RuleCategory::with('rules')->get();
        return view('teacher.rules', compact('categories'));
    }

    /**
     * Display student leave requests approvals.
     */
    public function studentLeaveRequests()
    {
        return view('teacher.student-leave-requests');
    }

    /**
     * Display my leave requests.
     */
    public function myLeaveRequests()
    {
        return view('teacher.my-leave-requests');
    }

    /**
     * Display daily report submission.
     */
    public function dailyReport()
    {
        return view('teacher.daily-report');
    }

    /**
     * Display my own attendance history.
     */
    public function myAttendance()
    {
        return view('teacher.my-attendance');
    }

    /**
     * Display free period activities.
     */
    public function activities()
    {
        return view('teacher.activities');
    }
    /**
     * Display teacher profile.
     */
    public function profile()
    {
        $user = Auth::user();
        $teacherProfile = $user->teacherProfile;

        if (!$teacherProfile) {
            return redirect()->route('teacher.dashboard')->with('error', 'Teacher profile not found.');
        }

        return view('teacher.profile', compact('teacherProfile'));
    }
}
