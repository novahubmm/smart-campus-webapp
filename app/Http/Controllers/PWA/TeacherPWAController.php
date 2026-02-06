<?php

namespace App\Http\Controllers\PWA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherPWAController extends Controller
{
    /**
     * Teacher Dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        // Get teacher profile
        $teacher = (object)[
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $user->profile_image ? asset('storage/' . $user->profile_image) : null,
            'teacher_id' => $user->teacherProfile->teacher_id ?? 'N/A',
            'department' => $user->teacherProfile->department->name ?? 'N/A',
            'subjects' => $user->teacherProfile->subjects->pluck('name')->toArray() ?? [],
        ];
        
        // Get today's classes from timetable
        $today = strtolower(now()->format('D')); // mon, tue, wed, etc.
        $todayClasses = \App\Models\Period::whereHas('timetable', function($query) {
            $query->where('is_active', true);
        })
        ->where('day_of_week', $today)
        ->where('teacher_profile_id', $user->teacherProfile->id)
        ->where('is_break', false)
        ->with(['subject', 'timetable.schoolClass', 'room'])
        ->orderBy('starts_at')
        ->get()
        ->map(function($period) {
            return (object)[
                'id' => $period->id,
                'subject' => $period->subject->name ?? 'N/A',
                'class' => $period->timetable->schoolClass->name ?? 'N/A',
                'room' => $period->room->name ?? 'N/A',
                'time' => $period->starts_at->format('H:i') . ' - ' . $period->ends_at->format('H:i'),
                'start_time' => $period->starts_at->format('H:i'),
                'end_time' => $period->ends_at->format('H:i'),
            ];
        });
        
        // Get stats
        try {
            $totalClasses = \App\Models\Timetable::whereHas('periods', function($query) use ($user) {
                $query->where('teacher_profile_id', $user->teacherProfile->id);
            })
            ->where('is_active', true)
            ->distinct()
            ->count();
            
            $totalStudents = \App\Models\StudentProfile::whereHas('classModel.timetables.periods', function($query) use ($user) {
                $query->where('teacher_profile_id', $user->teacherProfile->id)
                      ->where('is_break', false);
            })->distinct()->count();
            
            $pendingHomework = \App\Models\Homework::where('teacher_id', $user->teacherProfile->id)
                ->where('due_date', '>=', now())
                ->count();
        } catch (\Exception $e) {
            $totalClasses = 0;
            $totalStudents = 0;
            $pendingHomework = 0;
        }
        
        $stats = (object)[
            'today_classes' => count($todayClasses),
            'total_students' => $totalStudents,
            'pending_homework' => $pendingHomework,
        ];
        
        // Greeting based on time
        $hour = now()->hour;
        $greeting = $hour < 12 ? 'morning' : ($hour < 18 ? 'afternoon' : 'evening');
        
        // Check available roles
        $availableRoles = $user->roles->pluck('name')->toArray();
        $currentRole = 'teacher';
        
        return view('teacher_pwa.dashboard', compact('teacher', 'stats', 'todayClasses', 'greeting', 'availableRoles', 'currentRole'));
    }
    
    /**
     * My Classes
     */
    public function classes()
    {
        $user = Auth::user();
        
        // Get all classes taught by this teacher
        $classes = \App\Models\Period::where('teacher_profile_id', $user->teacherProfile->id)
            ->where('is_break', false)
            ->with(['timetable.schoolClass.grade', 'subject'])
            ->get()
            ->groupBy('timetable.class_id')
            ->map(function($periods) {
                $firstPeriod = $periods->first();
                $subjects = $periods->pluck('subject.name')->unique()->filter()->values();
                
                return [
                    'id' => $firstPeriod->timetable->class_id,
                    'name' => $firstPeriod->timetable->schoolClass->name ?? 'N/A',
                    'class_name' => $firstPeriod->timetable->schoolClass->name ?? 'N/A',
                    'grade' => $firstPeriod->timetable->schoolClass->grade->name ?? 'N/A',
                    'subject' => $subjects->first() ?? 'N/A',
                    'subjects' => $subjects->toArray(),
                    'students_count' => $firstPeriod->timetable->schoolClass->students()->count() ?? 0,
                    'periods_per_week' => $periods->count(),
                    'time' => 'Multiple periods',
                    'status' => 'upcoming',
                    'url' => '#',
                ];
            })
            ->values();
        
        return view('teacher_pwa.classes', [
            'headerTitle' => 'My Classes',
            'activeNav' => 'classes',
            'classes' => $classes,
        ]);
    }
    
    /**
     * Take Attendance
     */
    public function attendance()
    {
        $user = Auth::user();
        
        // Get teacher's classes for dropdown
        $classes = \App\Models\Period::where('teacher_profile_id', $user->teacherProfile->id)
            ->where('is_break', false)
            ->with(['timetable.schoolClass.grade'])
            ->get()
            ->groupBy('timetable.class_id')
            ->map(function($periods) {
                $firstPeriod = $periods->first();
                return [
                    'id' => $firstPeriod->timetable->class_id,
                    'name' => $firstPeriod->timetable->schoolClass->name ?? 'N/A',
                    'grade' => $firstPeriod->timetable->schoolClass->grade->name ?? 'N/A',
                ];
            })
            ->values();
        
        return view('teacher_pwa.attendance', [
            'headerTitle' => 'Attendance',
            'activeNav' => 'attendance',
            'classes' => $classes,
            'students' => [],
            'selectedDate' => now()->format('Y-m-d'),
        ]);
    }
    
    /**
     * Homework
     */
    public function homework()
    {
        $user = Auth::user();
        
        // Get teacher's homework assignments
        $homeworks = \App\Models\Homework::where('teacher_id', $user->teacherProfile->id)
            ->with(['schoolClass', 'subject'])
            ->latest()
            ->take(20)
            ->get()
            ->map(function($homework) {
                return [
                    'id' => $homework->id,
                    'title' => $homework->title,
                    'class' => $homework->schoolClass->name ?? 'N/A',
                    'subject' => $homework->subject->name ?? 'N/A',
                    'due_date' => $homework->due_date->format('M d, Y'),
                    'status' => $homework->status ?? 'active',
                    'submissions' => $homework->submissions()->count(),
                    'total_students' => $homework->schoolClass->students()->count(),
                ];
            });
        
        return view('teacher_pwa.homework', [
            'headerTitle' => 'Homework',
            'activeNav' => 'homework',
            'homeworks' => $homeworks,
        ]);
    }
    
    /**
     * Students
     */
    public function students()
    {
        $user = Auth::user();
        
        // Get all students from teacher's classes
        $students = \App\Models\StudentProfile::whereHas('classModel.timetables.periods', function($query) use ($user) {
            $query->where('teacher_profile_id', $user->teacherProfile->id)
                  ->where('is_break', false);
        })
        ->with(['user', 'classModel', 'grade'])
        ->distinct()
        ->get()
        ->map(function($student) {
            return [
                'id' => $student->id,
                'name' => $student->user->name,
                'student_id' => $student->student_id,
                'class' => $student->classModel->name ?? 'N/A',
                'grade' => $student->grade->name ?? 'N/A',
                'profile_image' => $student->user->profile_image ? asset('storage/' . $student->user->profile_image) : null,
            ];
        });
        
        return view('teacher_pwa.students', [
            'headerTitle' => 'Students',
            'activeNav' => 'students',
            'students' => $students,
        ]);
    }
    
    /**
     * Announcements
     */
    public function announcements()
    {
        $announcements = \App\Models\Announcement::where('status', 'published')
            ->where(function($query) {
                $query->where('target_audience', 'teacher')
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
                    'date' => $announcement->created_at->format('M d, Y'),
                    'created_at' => $announcement->created_at,
                ];
            });
        
        return view('teacher_pwa.announcements', [
            'headerTitle' => 'Announcements',
            'activeNav' => 'announcements',
            'announcements' => $announcements,
        ]);
    }
    
    /**
     * Timetable
     */
    public function timetable()
    {
        $user = Auth::user();
        
        // Get teacher's timetable for the week
        $days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
        $currentDay = strtolower(now()->format('D'));
        
        $timetable = [];
        foreach ($days as $day) {
            $periods = \App\Models\Period::whereHas('timetable', function($query) {
                $query->where('is_active', true);
            })
            ->where('teacher_profile_id', $user->teacherProfile->id)
            ->where('day_of_week', $day)
            ->with(['subject', 'timetable.schoolClass', 'room'])
            ->orderBy('period_number')
            ->get()
            ->map(function($period) {
                return [
                    'id' => $period->id,
                    'period_number' => $period->period_number,
                    'subject' => $period->subject->name ?? 'N/A',
                    'class' => $period->timetable->schoolClass->name ?? 'N/A',
                    'room' => $period->room->name ?? 'N/A',
                    'time' => $period->starts_at->format('H:i') . ' - ' . $period->ends_at->format('H:i'),
                    'is_break' => $period->is_break,
                ];
            });
            
            $timetable[$day] = [
                'day' => ucfirst($day),
                'is_today' => $day === $currentDay,
                'periods' => $periods,
            ];
        }
        
        return view('teacher_pwa.timetable', [
            'headerTitle' => 'Timetable',
            'activeNav' => 'timetable',
            'timetable' => $timetable,
            'currentDay' => $currentDay,
        ]);
    }
    
    /**
     * Utilities
     */
    public function utilities()
    {
        return view('teacher_pwa.utilities', [
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
        
        // Get teacher data
        $teacher = (object)[
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $user->profile_image ? asset('storage/' . $user->profile_image) : null,
            'teacher_id' => $user->teacherProfile->teacher_id ?? 'N/A',
            'department' => $user->teacherProfile->department->name ?? 'N/A',
            'subjects' => $user->teacherProfile->subjects->pluck('name')->toArray() ?? [],
        ];
        
        // Check if user has multiple roles
        $availableRoles = $user->roles->pluck('name')->toArray();
        $currentRole = 'teacher';
        
        return view('teacher_pwa.profile', [
            'headerTitle' => 'Profile',
            'activeNav' => 'profile',
            'teacher' => $teacher,
            'availableRoles' => $availableRoles,
            'currentRole' => $currentRole,
        ]);
    }
}
