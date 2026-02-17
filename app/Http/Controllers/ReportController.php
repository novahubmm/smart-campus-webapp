<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\DailyReport;
use App\Models\DailyReportRecipient;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\StaffProfile;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ReportController extends Controller
{
    use LogsActivity;
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'incoming');
        
        // Stats for incoming reports (from teachers)
        $incomingStats = [
            'total' => DailyReport::incoming()->count(),
            'pending' => DailyReport::incoming()->where('status', 'pending')->count(),
            'reviewed' => DailyReport::incoming()->whereIn('status', ['reviewed', 'resolved', 'approved'])->count(),
        ];
        
        // Stats for outgoing reports (to teachers)
        $outgoingStats = [
            'total' => DailyReport::outgoing()->count(),
            'pending' => DailyReport::outgoing()->where('status', 'pending')->count(),
            'acknowledged' => DailyReport::outgoing()->whereIn('status', ['acknowledged', 'resolved'])->count(),
        ];

        // Build query based on tab
        $query = DailyReport::with(['user', 'recipientUser', 'reviewedBy'])->latest();
        
        if ($tab === 'outgoing') {
            $query->outgoing();
        } else {
            $query->incoming();
        }

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        $reports = $query->paginate(15);

        // Get unique categories for filter dropdown
        $categories = DailyReport::distinct()->pluck('category')->filter();
        
        // Get teachers for the create modal - ordered by user name
        $teachers = TeacherProfile::with('user')
            ->whereHas('user')
            ->get()
            ->sortBy(function($teacher) {
                return $teacher->user->name ?? '';
            });
        
        // Get recipients for category dropdown
        $recipients = DailyReportRecipient::where('is_active', true)->orderBy('name')->get();

        return view('reports.index', compact(
            'tab', 
            'incomingStats', 
            'outgoingStats', 
            'reports', 
            'categories',
            'teachers',
            'recipients'
        ));
    }

    /**
     * Store a new daily report (send to teacher)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipient_user_id' => 'required|exists:users,id',
            'category' => 'required|string|max:50',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $report = DailyReport::create([
            'user_id' => auth()->id(),
            'recipient_user_id' => $validated['recipient_user_id'],
            'direction' => 'outgoing',
            'category' => $validated['category'],
            'recipient' => 'teacher',
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'status' => 'pending',
        ]);

        $this->logCreate('DailyReport', auth()->id(), $validated['subject']);

        // Send push notification to the teacher
        $recipientUser = \App\Models\User::find($validated['recipient_user_id']);
        
        if ($recipientUser) {
            // Create database notification
            $recipientUser->notify(new \App\Notifications\DailyReportReceived($report));
            
            // Get all device tokens for this user
            $deviceTokens = \App\Models\DeviceToken::where('user_id', $recipientUser->id)->get();
            
            \Log::info('Daily Report - Attempting to send notification', [
                'recipient_user_id' => $recipientUser->id,
                'recipient_name' => $recipientUser->name,
                'device_tokens_count' => $deviceTokens->count(),
            ]);
            
            if ($deviceTokens->isNotEmpty()) {
                $firebaseService = new \App\Services\FirebaseService();
                $successCount = 0;
                
                foreach ($deviceTokens as $deviceToken) {
                    $notificationSent = $firebaseService->sendToToken(
                        $deviceToken->token,
                        'New Daily Report',
                        $validated['subject'],
                        [
                            'type' => 'daily_report',
                            'report_id' => $report->id,
                            'category' => $validated['category'],
                        ],
                        'smartcampus_notifications'
                    );
                    
                    if ($notificationSent) {
                        $successCount++;
                    }
                }
                
                \Log::info('Daily Report - Notifications sent', [
                    'report_id' => $report->id,
                    'total_devices' => $deviceTokens->count(),
                    'successful' => $successCount,
                ]);
            } else {
                \Log::warning('Daily Report - No device tokens found', [
                    'recipient_user_id' => $recipientUser->id,
                    'recipient_name' => $recipientUser->name,
                ]);
            }
        } else {
            \Log::error('Daily Report - Recipient user not found', [
                'recipient_user_id' => $validated['recipient_user_id'],
            ]);
        }

        return redirect()->route('reports.index', ['tab' => 'outgoing'])
            ->with('status', __('Report sent to teacher successfully.'));
    }

    /**
     * Incoming Reports - redirect to index
     */
    public function incomingReports(Request $request)
    {
        return redirect()->route('reports.index');
    }

    /**
     * View a single daily report
     */
    public function showIncomingReport(string $reportId)
    {
        $report = DailyReport::with(['user', 'recipientUser', 'reviewedBy'])->findOrFail($reportId);

        return view('reports.incoming-detail', compact('report'));
    }

    /**
     * Mark report as reviewed
     */
    public function reviewReport(Request $request, string $reportId)
    {
        $report = DailyReport::findOrFail($reportId);
        
        $report->update([
            'status' => $request->input('status', 'reviewed'),
            'admin_remarks' => $request->input('admin_remarks'),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('reports.index')->with('status', __('Report updated successfully.'));
    }

    /**
     * Mark report as acknowledged - Mock action
     */
    public function acknowledgeReport(Request $request, string $reportId)
    {
        $report = DailyReport::findOrFail($reportId);
        
        $report->update([
            'status' => 'resolved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('reports.index')->with('status', __('Report acknowledged.'));
    }
    
    /**
     * Delete a daily report
     */
    public function destroy(string $reportId)
    {
        $report = DailyReport::findOrFail($reportId);
        $tab = $report->direction === 'outgoing' ? 'outgoing' : 'incoming';
        $reportSubject = $report->subject;
        $report->delete();

        $this->logDelete('DailyReport', $reportId, $reportSubject);

        return redirect()->route('reports.index', ['tab' => $tab])
            ->with('status', __('Report deleted successfully.'));
    }

    /**
     * Student Reports (Report Card, QCPR, CCPR)
     */
    public function studentReports()
    {
        $batches = Batch::where('status', true)->orderBy('name')->get();
        $grades = Grade::with('classes')->orderBy('level')->get();

        return view('reports.students', compact('batches', 'grades'));
    }

    public function generateStudentReport(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|in:report_card,qcpr,ccpr',
            'batch_id' => 'required|exists:batches,id',
            'grade_id' => 'required|exists:grades,id',
            'class_id' => 'nullable|exists:classes,id',
            'student_id' => 'nullable|exists:student_profiles,id',
            'term' => 'nullable|string',
        ]);

        $query = StudentProfile::with(['user', 'class.grade']);

        if ($validated['student_id']) {
            $query->where('id', $validated['student_id']);
        } elseif ($validated['class_id']) {
            $query->where('class_id', $validated['class_id']);
        } else {
            $query->whereHas('class', fn($q) => $q->where('grade_id', $validated['grade_id']));
        }

        $students = $query->get();

        return view('reports.print.student-report', [
            'reportType' => $validated['report_type'],
            'students' => $students,
            'term' => $validated['term'] ?? null,
        ]);
    }

    /**
     * Teacher Reports
     */
    public function teacherReports()
    {
        $teachers = TeacherProfile::with(['user', 'department'])->get();

        return view('reports.teachers', compact('teachers'));
    }

    public function generateTeacherReport(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|in:profile,performance,attendance',
            'teacher_id' => 'nullable|exists:teacher_profiles,id',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $query = TeacherProfile::with(['user', 'department', 'subjects']);

        if ($validated['teacher_id']) {
            $query->where('id', $validated['teacher_id']);
        } elseif ($validated['department_id'] ?? null) {
            $query->where('department_id', $validated['department_id']);
        }

        $teachers = $query->get();

        return view('reports.print.teacher-report', [
            'reportType' => $validated['report_type'],
            'teachers' => $teachers,
        ]);
    }

    /**
     * Staff Reports
     */
    public function staffReports()
    {
        $staff = StaffProfile::with(['user', 'department'])->get();

        return view('reports.staff', compact('staff'));
    }

    public function generateStaffReport(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|in:profile,receivable,attendance',
            'staff_id' => 'nullable|exists:staff_profiles,id',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $query = StaffProfile::with(['user', 'department']);

        if ($validated['staff_id']) {
            $query->where('id', $validated['staff_id']);
        } elseif ($validated['department_id'] ?? null) {
            $query->where('department_id', $validated['department_id']);
        }

        $staff = $query->get();

        return view('reports.print.staff-report', [
            'reportType' => $validated['report_type'],
            'staff' => $staff,
        ]);
    }

    /**
     * Attendance Reports (PR, DAR, MAR)
     */
    public function attendanceReports()
    {
        $batches = Batch::where('status', true)->orderBy('name')->get();
        $grades = Grade::with('classes')->orderBy('level')->get();

        return view('reports.attendance', compact('batches', 'grades'));
    }

    public function generateAttendanceReport(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|in:pr,dar,mar',
            'batch_id' => 'required|exists:batches,id',
            'grade_id' => 'required|exists:grades,id',
            'class_id' => 'nullable|exists:classes,id',
            'date' => 'nullable|date',
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2020|max:2100',
        ]);

        $class = null;
        if ($validated['class_id']) {
            $class = SchoolClass::with(['students.user', 'grade', 'teacher'])->find($validated['class_id']);
        }

        return view('reports.print.attendance-report', [
            'reportType' => $validated['report_type'],
            'class' => $class,
            'date' => $validated['date'] ?? now()->format('Y-m-d'),
            'month' => $validated['month'] ?? now()->month,
            'year' => $validated['year'] ?? now()->year,
        ]);
    }

    /**
     * API: Get classes by grade
     */
    public function getClassesByGrade(string $gradeId)
    {
        $classes = SchoolClass::where('grade_id', $gradeId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($classes);
    }

    /**
     * API: Get students by class
     */
    public function getStudentsByClass(string $classId)
    {
        $students = StudentProfile::where('class_id', $classId)
            ->with('user:id,name')
            ->get()
            ->map(fn($s) => ['id' => $s->id, 'name' => $s->user->name ?? $s->name ?? 'Unknown']);

        return response()->json($students);
    }
}
