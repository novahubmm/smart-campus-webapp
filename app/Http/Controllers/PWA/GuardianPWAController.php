<?php

namespace App\Http\Controllers\PWA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuardianPWAController extends Controller
{
    /**
     * Guardian Home
     */
    public function home()
    {
        $user = Auth::user();
        
        // Check if user has guardian profile
        if (!$user->guardianProfile) {
            return redirect()->route('dashboard')->with('error', 'Guardian profile not found.');
        }
        
        // Get guardian profile
        $guardian = (object)[
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $user->profile_image ? asset('storage/' . $user->profile_image) : null,
            'occupation' => $user->guardianProfile->occupation ?? 'N/A',
            'address' => $user->guardianProfile->address ?? 'N/A',
        ];
        
        // Get students with real data
        $students = $user->guardianProfile->students->map(function ($student) {
            try {
                // Calculate attendance rate
                $totalDays = \App\Models\StudentAttendance::where('student_id', $student->id)
                    ->whereMonth('date', now()->month)
                    ->count();
                $presentDays = \App\Models\StudentAttendance::where('student_id', $student->id)
                    ->whereMonth('date', now()->month)
                    ->where('status', 'present')
                    ->count();
                $attendanceRate = $totalDays > 0 ? round(($presentDays / $totalDays) * 100) : 100;
                
                // Calculate homework pending
                $homeworkPending = \App\Models\Homework::where('class_id', $student->class_id)
                ->whereDoesntHave('submissions', function($query) use ($student) {
                    $query->where('student_id', $student->id);
                })
                ->where('due_date', '>=', now())
                ->count();
                
                // Calculate fees pending
                $feesPending = \App\Models\StudentFee::where('student_id', $student->id)
                    ->where('status', 'pending')
                    ->sum('amount') ?? 0;
            } catch (\Exception $e) {
                // Default values if calculation fails
                $attendanceRate = 100;
                $homeworkPending = 0;
                $feesPending = 0;
            }
            
            return (object)[
                'id' => $student->id,
                'name' => $student->user->name,
                'student_id' => $student->student_id,
                'grade' => $student->grade->name ?? 'N/A',
                'section' => $student->classModel->name ?? 'N/A',
                'profile_image' => $student->user->profile_image ? asset('storage/' . $student->user->profile_image) : null,
                'attendance_rate' => $attendanceRate,
                'homework_pending' => $homeworkPending,
                'fees_pending' => $feesPending,
            ];
        });
        
        // Get recent announcements
        $recentAnnouncements = \App\Models\Announcement::where('status', 'published')
            ->where(function($query) {
                $query->where('target_audience', 'guardian')
                      ->orWhere('target_audience', 'all');
            })
            ->latest()
            ->take(3)
            ->get()
            ->map(function($announcement) {
                return (object)[
                    'id' => $announcement->id,
                    'title' => $announcement->title,
                    'content' => $announcement->content,
                    'priority' => $announcement->priority ?? 'normal',
                    'date' => $announcement->created_at->format('M d, Y'),
                    'created_at' => $announcement->created_at,
                ];
            });
        
        // Check available roles
        $availableRoles = $user->roles->pluck('name')->toArray();
        $currentRole = 'guardian';
        
        return view('guardian_pwa.home', compact('guardian', 'students', 'recentAnnouncements', 'availableRoles', 'currentRole'));
    }
    
    /**
     * Attendance
     */
    public function attendance()
    {
        $user = Auth::user();
        
        // Get students list for selector
        $students = $user->guardianProfile->students->map(function ($student) {
            return (object)[
                'id' => $student->id,
                'name' => $student->user->name,
            ];
        });
        
        // Get selected student (first one by default)
        $selectedStudent = (object)[
            'attendance_rate' => 100,
            'present_days' => 0,
            'absent_days' => 0,
            'late_days' => 0,
        ];
        
        $calendar = [];
        $recentAttendance = [];
        
        return view('guardian_pwa.attendance', [
            'headerTitle' => 'Attendance',
            'activeNav' => 'attendance',
            'students' => $students,
            'selectedStudent' => $selectedStudent,
            'calendar' => $calendar,
            'recentAttendance' => $recentAttendance,
        ]);
    }
    
    /**
     * Homework
     */
    public function homework()
    {
        $user = Auth::user();
        
        // Get students list for selector
        $students = $user->guardianProfile->students->map(function ($student) {
            return (object)[
                'id' => $student->id,
                'name' => $student->user->name,
            ];
        });
        
        $selectedStudent = (object)[
            'total_homework' => 0,
            'completed_homework' => 0,
            'pending_homework' => 0,
            'overdue_homework' => 0,
        ];
        
        $homeworks = [];
        
        return view('guardian_pwa.homework', [
            'headerTitle' => 'Homework',
            'activeNav' => 'homework',
            'students' => $students,
            'selectedStudent' => $selectedStudent,
            'homeworks' => $homeworks,
        ]);
    }
    
    /**
     * Timetable
     */
    public function timetable()
    {
        $user = Auth::user();
        
        // Get students list for selector
        $students = $user->guardianProfile->students->map(function ($student) {
            return (object)[
                'id' => $student->id,
                'name' => $student->user->name,
            ];
        });
        
        $currentDay = now()->format('l');
        $periods = [];
        
        return view('guardian_pwa.timetable', [
            'headerTitle' => 'Timetable',
            'activeNav' => 'timetable',
            'students' => $students,
            'currentDay' => $currentDay,
            'periods' => $periods,
        ]);
    }
    
    /**
     * School Fees
     */
    public function fees()
    {
        $user = Auth::user();
        
        // Get students list for selector
        $students = $user->guardianProfile->students->map(function ($student) {
            return (object)[
                'id' => $student->id,
                'name' => $student->user->name,
            ];
        });
        
        $selectedStudent = (object)[
            'total_fees' => 0,
            'paid_fees' => 0,
            'outstanding_fees' => 0,
        ];
        
        $payments = [];
        $pendingInvoices = [];
        
        return view('guardian_pwa.fees', [
            'headerTitle' => 'School Fees',
            'activeNav' => 'fees',
            'students' => $students,
            'selectedStudent' => $selectedStudent,
            'payments' => $payments,
            'pendingInvoices' => $pendingInvoices,
        ]);
    }
    
    /**
     * Announcements
     */
    public function announcements()
    {
        $announcements = \App\Models\Announcement::where('status', 'published')
            ->where(function($query) {
                $query->where('target_audience', 'guardian')
                      ->orWhere('target_audience', 'all');
            })
            ->latest()
            ->get()
            ->map(function($announcement) {
                return [
                    'id' => $announcement->id,
                    'title' => $announcement->title,
                    'content' => $announcement->content,
                    'priority' => $announcement->priority ?? 'normal',
                    'category' => $announcement->category ?? 'school',
                    'created_at' => $announcement->created_at,
                    'attachments_count' => 0,
                    'author' => (object)['name' => 'Admin'],
                ];
            });
        
        return view('guardian_pwa.announcements', [
            'headerTitle' => 'Announcements',
            'activeNav' => 'announcements',
            'announcements' => $announcements,
        ]);
    }
    
    /**
     * Utilities
     */
    public function utilities()
    {
        return view('guardian_pwa.utilities', [
            'headerTitle' => 'More',
            'activeNav' => 'utilities'
        ]);
    }
    
    /**
     * Profile
     */
    public function profile()
    {
        $user = Auth::user();
        
        // Get guardian data
        $guardian = (object)[
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $user->profile_image ? asset('storage/' . $user->profile_image) : null,
            'occupation' => $user->guardianProfile->occupation ?? 'N/A',
            'address' => $user->guardianProfile->address ?? 'N/A',
        ];
        
        // Get students
        $students = $user->guardianProfile->students->map(function ($student) {
            return (object)[
                'id' => $student->id,
                'name' => $student->user->name,
                'grade' => $student->grade->name ?? 'N/A',
                'section' => $student->classModel->name ?? 'N/A',
                'profile_image' => $student->user->profile_image ? asset('storage/' . $student->user->profile_image) : null,
            ];
        });
        
        // Check if user has multiple roles
        $availableRoles = $user->roles->pluck('name')->toArray();
        $currentRole = 'guardian';
        
        return view('guardian_pwa.profile', [
            'headerTitle' => 'Profile',
            'activeNav' => 'profile',
            'guardian' => $guardian,
            'students' => $students,
            'availableRoles' => $availableRoles,
            'currentRole' => $currentRole,
        ]);
    }
    
    /**
     * Student Detail
     */
    public function studentDetail($id)
    {
        $user = Auth::user();
        
        // Get student data
        $studentProfile = \App\Models\StudentProfile::with(['user', 'grade', 'classModel'])
            ->findOrFail($id);
        
        // Verify this student belongs to the guardian
        if (!$user->guardianProfile->students->contains($studentProfile)) {
            abort(403, 'Unauthorized access to student details');
        }
        
        $student = (object)[
            'id' => $studentProfile->id,
            'name' => $studentProfile->user->name,
            'student_id' => $studentProfile->student_id,
            'grade' => $studentProfile->grade->name ?? 'N/A',
            'section' => $studentProfile->classModel->name ?? 'N/A',
            'profile_image' => $studentProfile->user->profile_image ? asset('storage/' . $studentProfile->user->profile_image) : null,
            'attendance_rate' => 100, // TODO: Calculate
            'homework_completion' => 100, // TODO: Calculate
        ];
        
        // Get recent activity
        $recentActivity = [];
        
        return view('guardian_pwa.student-detail', [
            'headerTitle' => $student->name,
            'showBack' => true,
            'hideBottomNav' => true,
            'student' => $student,
            'recentActivity' => $recentActivity,
        ]);
    }
    
    /**
     * Announcement Detail
     */
    public function announcementDetail($id)
    {
        $announcement = \App\Models\Announcement::findOrFail($id);
        
        // Verify announcement is for guardians
        if (!in_array($announcement->target_audience, ['guardian', 'all'])) {
            abort(403, 'Unauthorized access to announcement');
        }
        
        $announcementData = (object)[
            'id' => $announcement->id,
            'title' => $announcement->title,
            'content' => $announcement->content,
            'priority' => $announcement->priority ?? 'normal',
            'category' => $announcement->category ?? 'school',
            'date' => $announcement->created_at->format('M d, Y'),
            'time' => $announcement->created_at->format('h:i A'),
            'author' => (object)['name' => 'Admin'],
            'attachments' => [],
        ];
        
        return view('guardian_pwa.announcement-detail', [
            'headerTitle' => 'Announcement',
            'showBack' => true,
            'hideBottomNav' => true,
            'announcement' => $announcementData,
        ]);
    }
}
