<?php

namespace App\Repositories\Teacher;

use App\Interfaces\Teacher\TeacherClassRepositoryInterface;
use App\Models\Batch;
use App\Models\Exam;
use App\Models\ExamMark;
use App\Models\Period;
use App\Models\PeriodSwitchRequest;
use App\Models\SchoolClass;
use App\Models\StudentAttendance;
use App\Models\StudentProfile;
use App\Models\StudentRemark;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\User;
use App\Notifications\PeriodSwitchRequested;
use App\Notifications\PeriodSwitchResponded;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TeacherClassRepository implements TeacherClassRepositoryInterface
{
    /**
     * 1. Get all classes for the teacher
     */
    public function getMyClasses(User $teacher): array
    {
        $teacherProfile = $teacher->teacherProfile;
        
        if (!$teacherProfile) {
            return ['classes' => [], 'total_classes' => 0, 'total_students' => 0];
        }

        $periods = Period::where('teacher_profile_id', $teacherProfile->id)
            ->whereHas('timetable', fn($q) => $q->where('is_active', true))
            ->with(['timetable.schoolClass.enrolledStudents', 'timetable.schoolClass.grade.gradeCategory', 'timetable.schoolClass.room', 'timetable.schoolClass.teacher.user', 'timetable.batch'])
            ->get();

        $classIds = $periods->pluck('timetable.class_id')->unique()->filter();
        $classes = SchoolClass::whereIn('id', $classIds)
            ->with(['enrolledStudents', 'grade.gradeCategory', 'room', 'teacher.user', 'batch'])
            ->get();

        $activeBatch = Batch::where('status', true)->first();

        $classesData = $classes->map(function ($class) use ($activeBatch) {
            $gradeColor = $class->grade?->gradeCategory?->color ?? '#6B7280';
            return [
                'id' => $class->id,
                'grade' => $class->grade?->name ?? 'Grade ' . $class->grade?->level,
                'section' => $class->name,
                'room' => $class->room?->name ?? 'N/A',
                'student_count' => $class->enrolledStudents->count(),
                'class_teacher' => $class->teacher?->user?->name ?? 'N/A',
                'academic_year' => $activeBatch?->name ?? now()->format('Y') . '-' . (now()->format('Y') + 1),
                'grade_color' => $gradeColor,
            ];
        });

        return [
            'classes' => $classesData->values()->toArray(),
            'total_classes' => $classesData->count(),
            'total_students' => $classes->sum(fn($c) => $c->enrolledStudents->count()),
        ];
    }

    /**
     * 2. Get class detail
     */
    public function getClassDetail(User $teacher, string $classId): ?array
    {
        if (!$this->hasClassAccess($teacher, $classId)) {
            return null;
        }

        $class = SchoolClass::with(['enrolledStudents.user', 'grade.gradeCategory', 'room', 'teacher.user', 'batch'])->find($classId);

        if (!$class) {
            return null;
        }

        $activeBatch = Batch::where('status', true)->first();
        $gradeColor = $class->grade?->gradeCategory?->color ?? '#6B7280';

        return [
            'id' => $class->id,
            'grade' => $class->grade?->name ?? 'Grade ' . $class->grade?->level,
            'section' => $class->name,
            'room' => $class->room?->name ?? 'N/A',
            'student_count' => $class->enrolledStudents->count(),
            'class_teacher' => $class->teacher?->user?->name ?? 'N/A',
            'academic_year' => $activeBatch?->name ?? now()->format('Y') . '-' . (now()->format('Y') + 1),
            'grade_color' => $gradeColor,
        ];
    }

    /**
     * 3. Get class students
     */
    public function getClassStudents(User $teacher, string $classId, ?string $search = null, ?string $gender = null): ?array
    {
        if (!$this->hasClassAccess($teacher, $classId)) {
            return null;
        }

        $class = SchoolClass::find($classId);
        if (!$class) {
            return null;
        }

        $studentsQuery = StudentProfile::with(['user'])
            ->where('class_id', $classId);

        if ($search) {
            $studentsQuery->where(function ($q) use ($search) {
                $q->whereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%"))
                  ->orWhere('student_identifier', 'like', "%{$search}%");
            });
        }

        if ($gender && in_array($gender, ['Male', 'Female'])) {
            $studentsQuery->where('gender', strtolower($gender));
        }

        $students = $studentsQuery->orderBy('student_identifier')->get();

        $studentsData = $students->map(function ($student) use ($class) {
            $attendancePercentage = $this->calculateStudentAttendance($student->id);
            $isClassLeader = $class->class_leader_id === $student->id;
            
            return [
                'id' => $student->id,
                'name' => $student->user?->name ?? 'Unknown',
                'roll_no' => $student->student_identifier ?? '',
                'gender' => ucfirst($student->gender ?? 'N/A'),
                'attendance_percentage' => $attendancePercentage,
                'avatar' => avatar_url($student->photo_path, 'student'),
                'is_class_leader' => $isClassLeader,
            ];
        });

        return [
            'students' => $studentsData->values()->toArray(),
            'total' => $studentsData->count(),
            'class_leader_id' => $class->class_leader_id,
        ];
    }

    /**
     * 3b. Get specific student detail within a class context
     */
    public function getClassStudentDetail(User $teacher, string $classId, string $studentId): ?array
    {
        if (!$this->hasClassAccess($teacher, $classId)) {
            return null;
        }

        $class = SchoolClass::find($classId);
        if (!$class) {
            return null;
        }

        $student = StudentProfile::with(['user', 'grade.gradeCategory', 'classModel', 'guardians.user'])
            ->where('class_id', $classId)
            ->where('id', $studentId)
            ->first();

        if (!$student) {
            return null;
        }

        $attendancePercentage = $this->calculateStudentAttendance($student->id);
        $isClassLeader = $class->class_leader_id === $student->id;
        $gradeColor = $student->grade?->gradeCategory?->color ?? '#6B7280';

        // Get primary guardian or first guardian
        $primaryGuardian = $student->guardians->firstWhere('pivot.is_primary', true) 
            ?? $student->guardians->first();

        return [
            'id' => $student->id,
            'name' => $student->user?->name ?? 'Unknown',
            'roll_no' => $student->student_identifier ?? '',
            'gender' => ucfirst($student->gender ?? 'N/A'),
            'date_of_birth' => $student->dob?->format('Y-m-d'),
            'phone' => $student->user?->phone ?? null,
            'email' => $student->user?->email ?? null,
            'address' => $student->address ?? null,
            'avatar' => avatar_url($student->photo_path, 'student'),
            'attendance_percentage' => $attendancePercentage,
            'is_class_leader' => $isClassLeader,
            'class' => [
                'id' => $class->id,
                'grade' => $student->grade?->name ?? 'Grade ' . $student->grade?->level,
                'section' => $class->name,
                'grade_color' => $gradeColor,
            ],
            'father' => [
                'name' => $student->father_name,
                'phone' => $student->father_phone_no,
                'nrc' => $student->father_nrc,
                'occupation' => $student->father_occupation,
            ],
            'mother' => [
                'name' => $student->mother_name,
                'phone' => $student->mother_phone_no,
                'nrc' => $student->mother_nrc,
                'occupation' => $student->mother_occupation,
            ],
            'guardian' => $primaryGuardian ? [
                'id' => $primaryGuardian->id,
                'name' => $primaryGuardian->user?->name ?? 'Unknown',
                'phone' => $primaryGuardian->user?->phone ?? null,
                'email' => $primaryGuardian->user?->email ?? null,
                'relationship' => $primaryGuardian->pivot?->relationship ?? 'Guardian',
            ] : null,
            'emergency_contact' => $student->emergency_contact_phone_no,
            'badges' => $this->getStudentBadges($studentId),
            'achiever_status' => $this->getAchieverStatus($studentId),
        ];
    }

    /**
     * 4. Get class teachers
     */
    public function getClassTeachers(User $teacher, string $classId): ?array
    {
        if (!$this->hasClassAccess($teacher, $classId)) {
            return null;
        }

        $periods = Period::whereHas('timetable', fn($q) => $q->where('class_id', $classId)->where('is_active', true))
            ->with(['teacher.user', 'subject'])
            ->get();

        $teachers = $periods->groupBy('teacher_profile_id')->map(function ($teacherPeriods) {
            $firstPeriod = $teacherPeriods->first();
            $teacherProfile = $firstPeriod->teacher;
            $subjects = $teacherPeriods->pluck('subject.name')->unique()->filter()->implode(', ');
            $subjectName = $firstPeriod->subject?->name;
            
            return [
                'id' => $teacherProfile?->id,
                'name' => $teacherProfile?->user?->name ?? 'Unknown',
                'subject' => $subjects ?: 'N/A',
                'avatar' => $this->getSubjectIcon($subjectName),
                'periods_per_week' => $teacherPeriods->count() . 'p/w',
                'icon_bg' => $this->getSubjectBgColor($subjectName),
                'icon_color' => $this->getSubjectColor($subjectName),
            ];
        });

        return [
            'teachers' => $teachers->values()->toArray(),
            'total' => $teachers->count(),
        ];
    }

    /**
     * 5. Get class timetable
     * If date is provided, applies accepted switch requests for that date
     */
    public function getClassTimetable(User $teacher, string $classId, ?string $date = null): ?array
    {
        if (!$this->hasClassAccess($teacher, $classId)) {
            return null;
        }

        $periods = Period::whereHas('timetable', fn($q) => $q->where('class_id', $classId)->where('is_active', true))
            ->with(['subject', 'teacher.user'])
            ->orderBy('period_number')
            ->get();

        // Get accepted switch requests for the given date
        $switchedPeriods = [];
        if ($date) {
            $acceptedSwitches = PeriodSwitchRequest::where('status', 'accepted')
                ->whereDate('date', $date)
                ->whereIn('period_id', $periods->pluck('id'))
                ->with(['toTeacher.user'])
                ->get()
                ->keyBy('period_id');
            
            $switchedPeriods = $acceptedSwitches;
        }

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $dayLabels = ['Monday' => 'Mon', 'Tuesday' => 'Tue', 'Wednesday' => 'Wed', 'Thursday' => 'Thu', 'Friday' => 'Fri', 'Saturday' => 'Sat'];

        $timetable = [];
        foreach ($days as $day) {
            $dayPeriods = $periods->where('day_of_week', $day)->sortBy('period_number');
            if ($dayPeriods->isEmpty()) continue;

            $timetable[] = [
                'day' => ucfirst($day),
                'day_code' => $dayLabels[ucfirst($day)] ?? substr(ucfirst($day), 0, 3),
                'slots' => $dayPeriods->map(function ($p) use ($switchedPeriods) {
                    // Check if this period has an accepted switch for the date
                    $switch = $switchedPeriods[$p->id] ?? null;
                    
                    if ($switch) {
                        // Return switched period info
                        return [
                            'id' => $p->id,
                            'period' => 'P' . $p->period_number,
                            'subject' => $switch->to_subject ?? 'N/A',
                            'subject_id' => $p->subject_id, // Original subject_id
                            'subject_code' => $this->getSubjectShortName($switch->to_subject),
                            'start_time' => format_time($p->starts_at),
                            'end_time' => format_time($p->ends_at),
                            'teacher_id' => $switch->to_teacher_id,
                            'teacher_name' => $switch->toTeacher?->user?->name ?? 'N/A',
                            'is_switched' => true,
                            'original_subject' => $p->subject?->name ?? 'N/A',
                            'original_teacher' => $p->teacher?->user?->name ?? 'N/A',
                        ];
                    }
                    
                    return [
                        'id' => $p->id,
                        'period' => 'P' . $p->period_number,
                        'subject' => $p->subject?->name ?? 'N/A',
                        'subject_id' => $p->subject_id,
                        'subject_code' => $this->getSubjectShortName($p->subject?->name),
                        'start_time' => format_time($p->starts_at),
                        'end_time' => format_time($p->ends_at),
                        'teacher_id' => $p->teacher_profile_id,
                        'teacher_name' => $p->teacher?->user?->name ?? 'N/A',
                        'is_switched' => false,
                    ];
                })->values()->toArray(),
            ];
        }

        return [
            'timetable' => $timetable,
            'periods_per_day' => $periods->groupBy('day_of_week')->map->count()->max() ?? 0,
            'date' => $date,
            'time_format' => get_time_format(),
        ];
    }

    /**
     * 6. Get class rankings
     */
    public function getClassRankings(User $teacher, string $classId, ?string $examId = null, ?string $examType = null): ?array
    {
        if (!$this->hasClassAccess($teacher, $classId)) {
            return null;
        }

        $class = SchoolClass::with(['enrolledStudents.user', 'grade'])->find($classId);
        if (!$class) {
            return null;
        }

        // Get exam
        $examQuery = Exam::where('grade_id', $class->grade_id);
        if ($examId) {
            $examQuery->where('id', $examId);
        }
        $exam = $examQuery->orderBy('created_at', 'desc')->first();

        if (!$exam) {
            return [
                'exam' => null,
                'rankings' => [],
                'total_students' => $class->enrolledStudents->count(),
            ];
        }

        // Get all students in the class
        $studentIds = $class->enrolledStudents->pluck('id');

        // Get all exam marks for students in this class for this exam (aggregated by student)
        // Since marks are stored per subject, we need to sum them up per student
        $studentTotals = ExamMark::where('exam_id', $exam->id)
            ->whereIn('student_id', $studentIds)
            ->selectRaw('student_id, SUM(marks_obtained) as total_obtained, SUM(total_marks) as total_possible')
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id');

        // Build rankings array with all enrolled students
        $rankings = $class->enrolledStudents->map(function ($student) use ($studentTotals) {
            $totals = $studentTotals->get($student->id);
            $totalObtained = $totals ? (float) $totals->total_obtained : 0;
            $totalPossible = $totals ? (float) $totals->total_possible : 0;
            $percentage = $totalPossible > 0 ? round(($totalObtained / $totalPossible) * 100, 1) : 0;

            return [
                'id' => $student->id,
                'name' => $student->user?->name ?? 'Unknown',
                'roll_no' => $student->student_identifier ?? '',
                'score' => (int) $totalObtained,
                'total_score' => (int) $totalPossible,
                'percentage' => $percentage,
                'rank' => 0,
                'avatar' => strtoupper(substr($student->user?->name ?? 'U', 0, 1)),
            ];
        })->sortByDesc('percentage')->values();

        // Assign ranks
        $rankings = $rankings->map(function ($item, $index) {
            $item['rank'] = $index + 1;
            return $item;
        });

        return [
            'exam' => [
                'id' => $exam->id,
                'name' => $exam->name,
                'type' => $exam->examType?->name ?? 'exam',
                'total_score' => (int) ($rankings->first()['total_score'] ?? 0),
            ],
            'rankings' => $rankings->toArray(),
            'total_students' => $class->enrolledStudents->count(),
        ];
    }

    /**
     * 7. Get class exams (for rankings dropdown)
     */
    public function getClassExams(User $teacher, string $classId): ?array
    {
        if (!$this->hasClassAccess($teacher, $classId)) {
            return null;
        }

        $class = SchoolClass::find($classId);
        if (!$class) {
            return null;
        }

        $exams = Exam::where('grade_id', $class->grade_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $examsData = $exams->map(function ($exam) {
            $icon = match($exam->type ?? 'exam') {
                'quiz' => '📝',
                'test' => '📋',
                default => '🏆',
            };

            return [
                'id' => $exam->id,
                'name' => $exam->name,
                'type' => $exam->type ?? 'exam',
                'icon' => $icon,
            ];
        });

        return [
            'exams' => $examsData->toArray(),
        ];
    }

    /**
     * 9. Assign class leader
     */
    public function assignClassLeader(User $teacher, string $classId, string $studentId): ?array
    {
        if (!$this->hasClassAccess($teacher, $classId)) {
            return null;
        }

        $class = SchoolClass::find($classId);
        if (!$class) {
            return null;
        }

        $student = StudentProfile::with('user')->where('class_id', $classId)->find($studentId);
        if (!$student) {
            return null;
        }

        $class->class_leader_id = $studentId;
        $class->save();

        return [
            'class_leader_id' => $studentId,
            'student_name' => $student->user?->name ?? 'Unknown',
        ];
    }

    /**
     * 10. Get switch requests
     */
    public function getSwitchRequests(User $teacher, string $classId, ?string $status = null, ?string $type = null): ?array
    {
        if (!$this->hasClassAccess($teacher, $classId)) {
            return null;
        }

        $teacherProfile = $teacher->teacherProfile;
        if (!$teacherProfile) {
            return null;
        }

        // Outgoing requests (requests this teacher created - they are the to_teacher wanting to take over)
        $outgoingQuery = PeriodSwitchRequest::where('to_teacher_id', $teacherProfile->id)
            ->whereHas('period.timetable', fn($q) => $q->where('class_id', $classId))
            ->with(['fromTeacher.user', 'period.subject']);

        if ($status) {
            $outgoingQuery->where('status', $status);
        }

        $outgoingRequests = $outgoingQuery->get()->map(function ($request) {
            return [
                'id' => $request->id,
                'from_teacher' => $request->fromTeacher?->user?->name ?? 'Unknown',
                'to_teacher' => 'You',
                'from_subject' => $request->period?->subject?->name ?? 'N/A',
                'to_subject' => $request->to_subject ?? 'N/A',
                'day' => ucfirst($request->period?->day_of_week ?? ''),
                'period' => 'P' . ($request->period?->period_number ?? ''),
                'status' => $request->status,
                'date' => $request->date?->format('Y-m-d'),
                'time' => $request->created_at?->format('h:i A'),
                'reason' => $request->reason,
            ];
        });

        // Incoming requests (requests from others wanting this teacher's period - they are the from_teacher)
        // Exclude requests where this teacher is also the to_teacher (their own requests)
        $incomingQuery = PeriodSwitchRequest::where('from_teacher_id', $teacherProfile->id)
            ->where('to_teacher_id', '!=', $teacherProfile->id)
            ->whereHas('period.timetable', fn($q) => $q->where('class_id', $classId))
            ->with(['toTeacher.user', 'period.subject']);

        if ($status) {
            $incomingQuery->where('status', $status);
        }

        $incomingRequests = $incomingQuery->get()->map(function ($request) {
            return [
                'id' => $request->id,
                'from_teacher' => 'You',
                'to_teacher' => $request->toTeacher?->user?->name ?? 'Unknown',
                'from_subject' => $request->period?->subject?->name ?? 'N/A',
                'to_subject' => $request->to_subject ?? 'N/A',
                'day' => ucfirst($request->period?->day_of_week ?? ''),
                'period' => 'P' . ($request->period?->period_number ?? ''),
                'status' => $request->status,
                'date' => $request->date?->format('Y-m-d'),
                'time' => $request->created_at?->format('h:i A'),
                'reason' => $request->reason,
            ];
        });

        return [
            'outgoing_requests' => $outgoingRequests->toArray(),
            'incoming_requests' => $incomingRequests->toArray(),
        ];
    }

    /**
     * 11. Create switch request
     * Teacher clicks on another teacher's period and requests to switch with their own subject
     */
    public function createSwitchRequest(User $teacher, string $classId, array $data): ?array
    {
        if (!$this->hasClassAccess($teacher, $classId)) {
            return null;
        }

        $teacherProfile = $teacher->teacherProfile;
        if (!$teacherProfile) {
            return null;
        }

        // Find the target period (another teacher's period that this teacher wants to take)
        $period = Period::with(['teacher', 'subject'])
            ->whereHas('timetable', fn($q) => $q->where('class_id', $classId)->where('is_active', true))
            ->where('id', $data['period_id'])
            ->first();

        if (!$period) {
            return null;
        }

        // Prevent teacher from requesting their own period
        if ($period->teacher_profile_id === $teacherProfile->id) {
            return ['error' => 'cannot_request_own_period'];
        }

        // Get the subject the requesting teacher wants to teach
        $requestSubject = Subject::find($data['request_subject']);

        $request = PeriodSwitchRequest::create([
            'period_id' => $period->id,
            'from_teacher_id' => $period->teacher_profile_id, // Original teacher of the period
            'to_teacher_id' => $teacherProfile->id, // Requesting teacher
            'date' => $data['date'],
            'reason' => $data['reason'] ?? null,
            'to_subject' => $requestSubject?->name,
            'status' => 'pending',
        ]);

        // Send notification to the original period owner
        $fromTeacher = $period->teacher?->user;
        if ($fromTeacher) {
            $className = $period->timetable?->class?->name ?? 'Unknown Class';
            $fromTeacher->notify(new PeriodSwitchRequested(
                $request,
                $teacher->name,
                $className,
                $requestSubject?->name ?? 'Unknown Subject'
            ));
        }

        return [
            'id' => $request->id,
            'status' => 'pending',
        ];
    }

    /**
     * 12. Respond to switch request
     * The original period owner (from_teacher) approves or rejects the request
     */
    public function respondToSwitchRequest(User $teacher, string $classId, string $requestId, string $status): ?array
    {
        $teacherProfile = $teacher->teacherProfile;
        if (!$teacherProfile) {
            return null;
        }

        // The from_teacher (original period owner) responds to the request
        $request = PeriodSwitchRequest::where('id', $requestId)
            ->where('from_teacher_id', $teacherProfile->id)
            ->with(['toTeacher.user', 'period.timetable.class'])
            ->first();

        if (!$request) {
            return null;
        }

        $request->status = $status;
        $request->save();

        // Send notification to the requester (to_teacher)
        $toTeacher = $request->toTeacher?->user;
        if ($toTeacher) {
            $className = $request->period?->timetable?->class?->name ?? 'Unknown Class';
            $toTeacher->notify(new PeriodSwitchResponded(
                $request,
                $teacher->name,
                $className,
                $status
            ));
        }

        return [
            'id' => $request->id,
            'status' => $status,
        ];
    }

    /**
     * 13. Get available teachers for switch
     */
    public function getAvailableTeachers(User $teacher, string $classId, string $day, string $period): ?array
    {
        if (!$this->hasClassAccess($teacher, $classId)) {
            return null;
        }

        $teacherProfile = $teacher->teacherProfile;
        if (!$teacherProfile) {
            return null;
        }

        $periodNumber = (int) str_replace('P', '', $period);

        // Get teachers who don't have a period at this time
        $busyTeacherIds = Period::whereHas('timetable', fn($q) => $q->where('is_active', true))
            ->where('day_of_week', strtolower($day))
            ->where('period_number', $periodNumber)
            ->pluck('teacher_profile_id');

        // Get all teachers except current and busy ones
        $availableTeachers = \App\Models\TeacherProfile::with(['user', 'subjects'])
            ->whereNotIn('id', $busyTeacherIds)
            ->where('id', '!=', $teacherProfile->id)
            ->get();

        $teachersData = $availableTeachers->map(function ($tp) {
            return [
                'id' => $tp->id,
                'name' => $tp->user?->name ?? 'Unknown',
                'subject' => $tp->subjects->pluck('name')->implode(', ') ?: 'N/A',
                'avatar' => strtoupper(substr($tp->user?->name ?? 'U', 0, 1)),
            ];
        });

        return [
            'teachers' => $teachersData->toArray(),
        ];
    }

    /**
     * Legacy: Get class statistics
     */
    public function getClassStatistics(User $teacher, string $classId): ?array
    {
        if (!$this->hasClassAccess($teacher, $classId)) {
            return null;
        }

        $class = SchoolClass::with(['enrolledStudents'])->find($classId);
        if (!$class) {
            return null;
        }

        $totalStudents = $class->enrolledStudents->count();
        $studentIds = $class->enrolledStudents->pluck('id');

        $today = now()->toDateString();
        $todayAttendance = StudentAttendance::whereIn('student_id', $studentIds)
            ->whereDate('date', $today)
            ->get();

        $presentToday = $todayAttendance->where('status', 'present')->count();
        $absentToday = $todayAttendance->where('status', 'absent')->count();

        $avgAttendance = 0;
        if ($totalStudents > 0) {
            $totalAttendanceRecords = StudentAttendance::whereIn('student_id', $studentIds)->count();
            $totalPresent = StudentAttendance::whereIn('student_id', $studentIds)->where('status', 'present')->count();
            $avgAttendance = $totalAttendanceRecords > 0 ? round(($totalPresent / $totalAttendanceRecords) * 100, 1) : 100;
        }

        $examMarks = ExamMark::whereIn('student_id', $studentIds)->where('is_absent', false)->get();

        $classAverage = 0;
        $highestScore = 0;
        $lowestScore = 0;
        $passPercentage = 0;

        if ($examMarks->count() > 0) {
            $percentages = $examMarks->map(fn($m) => $m->total_marks > 0 ? ($m->marks_obtained / $m->total_marks) * 100 : 0);
            $classAverage = round($percentages->avg(), 1);
            $highestScore = round($percentages->max(), 1);
            $lowestScore = round($percentages->min(), 1);
            $passPercentage = round($percentages->filter(fn($p) => $p >= 40)->count() / $percentages->count() * 100, 1);
        }

        return [
            'class_id' => $classId,
            'attendance' => [
                'average_percentage' => $avgAttendance,
                'present_today' => $presentToday,
                'absent_today' => $absentToday,
                'total_students' => $totalStudents,
            ],
            'performance' => [
                'class_average' => $classAverage,
                'highest_score' => $highestScore,
                'lowest_score' => $lowestScore,
                'pass_percentage' => $passPercentage,
            ],
        ];
    }

    /**
     * 14. Get student profile
     */
    public function getStudentProfile(User $teacher, string $studentId): ?array
    {
        $student = StudentProfile::with(['user', 'grade.gradeCategory', 'classModel'])->find($studentId);
        if (!$student) {
            return null;
        }

        if (!$this->hasClassAccess($teacher, $student->class_id)) {
            return null;
        }

        $class = $student->classModel;
        $gradeColor = $student->grade?->gradeCategory?->color ?? '#6B7280';

        return [
            'id' => $student->id,
            'name' => $student->user?->name ?? 'Unknown',
            'roll_no' => $student->student_identifier ?? '',
            'gender' => ucfirst($student->gender ?? 'N/A'),
            'avatar' => avatar_url($student->photo_path, 'student'),
            'class' => [
                'id' => $class?->id,
                'grade' => $student->grade?->level ?? '',
                'section' => substr($class?->name ?? '', -1),
                'grade_color' => $gradeColor,
            ],
            'is_class_leader' => $class?->class_leader_id === $student->id,
            'badges' => $this->getStudentBadges($studentId),
            'achiever_status' => $this->getAchieverStatus($studentId),
        ];
    }

    /**
     * 15. Get student academic
     */
    public function getStudentAcademic(User $teacher, string $studentId, ?string $academicYear = null, ?string $term = null): ?array
    {
        $student = StudentProfile::find($studentId);
        if (!$student || !$this->hasClassAccess($teacher, $student->class_id)) {
            return null;
        }

        $marksQuery = ExamMark::where('student_id', $studentId)->with(['exam', 'subject']);
        $marks = $marksQuery->get();

        if ($marks->isEmpty()) {
            return [
                'summary' => ['gpa' => '0.00', 'avg_score' => '0%', 'highest' => '0', 'passed' => '0/0'],
                'subject_performance' => [],
            ];
        }

        $percentages = $marks->map(fn($m) => $m->total_marks > 0 ? ($m->marks_obtained / $m->total_marks) * 100 : 0);
        $avgScore = round($percentages->avg(), 1);
        $highest = round($percentages->max());
        $passed = $percentages->filter(fn($p) => $p >= 40)->count();
        $total = $percentages->count();
        $gpa = $this->calculateGPA($avgScore);

        $subjectPerformance = $marks->groupBy('subject_id')->map(function ($subjectMarks) {
            $firstMark = $subjectMarks->first();
            $avgPercentage = $subjectMarks->avg(fn($m) => $m->total_marks > 0 ? ($m->marks_obtained / $m->total_marks) * 100 : 0);
            $grade = $this->getGrade($avgPercentage);

            return [
                'id' => $firstMark->subject_id,
                'subject' => $firstMark->subject?->name ?? 'Unknown',
                'grade' => $grade,
                'percentage' => round($avgPercentage),
                'rank' => 0,
                'color' => $this->getGradeColor($grade),
            ];
        })->values();

        return [
            'summary' => [
                'gpa' => number_format($gpa, 2),
                'avg_score' => $avgScore . '%',
                'highest' => (string) $highest,
                'passed' => $passed . '/' . $total,
            ],
            'subject_performance' => $subjectPerformance->toArray(),
        ];
    }

    /**
     * 16. Get student attendance
     */
    public function getStudentAttendance(User $teacher, string $studentId, ?int $month = null, ?int $year = null): ?array
    {
        $student = StudentProfile::find($studentId);
        if (!$student || !$this->hasClassAccess($teacher, $student->class_id)) {
            return null;
        }

        $attendanceQuery = StudentAttendance::where('student_id', $studentId);

        if ($month && $year) {
            $attendanceQuery->whereMonth('date', $month)->whereYear('date', $year);
        }

        $allAttendance = StudentAttendance::where('student_id', $studentId)->get();
        $totalDays = $allAttendance->count();
        $presentDays = $allAttendance->where('status', 'present')->count();
        $absentDays = $allAttendance->where('status', 'absent')->count();
        $lateDays = $allAttendance->where('status', 'late')->count();
        $percentage = $totalDays > 0 ? round(($presentDays / $totalDays) * 100) : 100;

        // Monthly breakdown
        $monthlyBreakdown = [];
        for ($i = 0; $i < 6; $i++) {
            $date = now()->subMonths($i);
            $monthAttendance = StudentAttendance::where('student_id', $studentId)
                ->whereMonth('date', $date->month)
                ->whereYear('date', $date->year)
                ->get();

            $monthTotal = $monthAttendance->count();
            $monthPresent = $monthAttendance->where('status', 'present')->count();

            $monthlyBreakdown[] = [
                'month' => $date->format('F'),
                'month_short' => $date->format('M'),
                'year' => (int) $date->format('Y'),
                'percentage' => $monthTotal > 0 ? round(($monthPresent / $monthTotal) * 100) : 100,
                'present_days' => $monthPresent,
                'total_days' => $monthTotal,
                'days_display' => $monthPresent . '/' . $monthTotal,
            ];
        }

        return [
            'overall' => [
                'percentage' => $percentage,
                'present' => $presentDays,
                'absent' => $absentDays,
                'late' => $lateDays,
                'total_days' => $totalDays,
            ],
            'monthly_breakdown' => $monthlyBreakdown,
        ];
    }

    /**
     * 17. Get student remarks
     */
    public function getStudentRemarks(User $teacher, string $studentId, ?string $type = null, ?string $category = null, ?string $dateFrom = null, ?string $dateTo = null): ?array
    {
        $student = StudentProfile::find($studentId);
        if (!$student || !$this->hasClassAccess($teacher, $student->class_id)) {
            return null;
        }

        $remarksQuery = StudentRemark::where('student_id', $studentId)
            ->with(['teacher.user', 'subject']);

        if ($type) {
            $remarksQuery->where('type', $type);
        }
        if ($category) {
            $remarksQuery->where('category', $category);
        }
        if ($dateFrom) {
            $remarksQuery->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $remarksQuery->whereDate('date', '<=', $dateTo);
        }

        $remarks = $remarksQuery->orderBy('date', 'desc')->get();

        $remarksData = $remarks->map(function ($remark) {
            $color = match($remark->type) {
                'Positive' => '#10B981',
                'Achievement' => '#F59E0B',
                'Improvement' => '#6B7280',
                'Concern' => '#EF4444',
                default => '#6B7280',
            };

            return [
                'id' => $remark->id,
                'type' => $remark->type,
                'category' => $remark->category,
                'date' => $remark->date?->format('Y-m-d'),
                'title' => $remark->title,
                'description' => $remark->description,
                'color' => $color,
                'teacher_name' => $remark->teacher?->user?->name ?? 'Unknown',
                'subject' => $remark->subject?->name ?? 'General',
            ];
        });

        return [
            'remarks' => $remarksData->toArray(),
            'total' => $remarksData->count(),
        ];
    }

    /**
     * 19. Get student rankings
     */
    public function getStudentRankings(User $teacher, string $studentId): ?array
    {
        $student = StudentProfile::with(['classModel', 'grade'])->find($studentId);
        if (!$student || !$this->hasClassAccess($teacher, $student->class_id)) {
            return null;
        }

        $classStudentIds = StudentProfile::where('class_id', $student->class_id)->pluck('id');
        $gradeStudentIds = StudentProfile::where('grade_id', $student->grade_id)->pluck('id');

        // Get latest exam marks for ranking
        $latestExam = Exam::where('grade_id', $student->grade_id)->orderBy('created_at', 'desc')->first();

        $classRank = 0;
        $gradeRank = 0;

        if ($latestExam) {
            $classMarks = ExamMark::where('exam_id', $latestExam->id)
                ->whereIn('student_id', $classStudentIds)
                ->orderByDesc('marks_obtained')
                ->pluck('student_id')
                ->toArray();
            $classRank = array_search($studentId, $classMarks) !== false ? array_search($studentId, $classMarks) + 1 : 0;

            $gradeMarks = ExamMark::where('exam_id', $latestExam->id)
                ->whereIn('student_id', $gradeStudentIds)
                ->orderByDesc('marks_obtained')
                ->pluck('student_id')
                ->toArray();
            $gradeRank = array_search($studentId, $gradeMarks) !== false ? array_search($studentId, $gradeMarks) + 1 : 0;
        }

        // Exam history
        $examHistory = ExamMark::where('student_id', $studentId)
            ->with('exam')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($mark) use ($classStudentIds, $gradeStudentIds) {
                $percentage = $mark->total_marks > 0 ? round(($mark->marks_obtained / $mark->total_marks) * 100, 1) : 0;

                $classMarks = ExamMark::where('exam_id', $mark->exam_id)
                    ->whereIn('student_id', $classStudentIds)
                    ->orderByDesc('marks_obtained')
                    ->pluck('student_id')
                    ->toArray();
                $classRank = array_search($mark->student_id, $classMarks) !== false ? array_search($mark->student_id, $classMarks) + 1 : 0;

                $gradeMarks = ExamMark::where('exam_id', $mark->exam_id)
                    ->whereIn('student_id', $gradeStudentIds)
                    ->orderByDesc('marks_obtained')
                    ->pluck('student_id')
                    ->toArray();
                $gradeRank = array_search($mark->student_id, $gradeMarks) !== false ? array_search($mark->student_id, $gradeMarks) + 1 : 0;

                return [
                    'id' => $mark->exam_id,
                    'name' => $mark->exam?->name ?? 'Unknown',
                    'date' => $mark->exam?->exam_date?->format('Y-m-d') ?? $mark->created_at->format('Y-m-d'),
                    'total_score' => $mark->marks_obtained,
                    'max_score' => $mark->total_marks,
                    'percentage' => $percentage,
                    'class_rank' => $classRank,
                    'grade_rank' => $gradeRank,
                ];
            });

        return [
            'current_rankings' => [
                'class_rank' => $classRank,
                'grade_rank' => $gradeRank,
                'total_students_in_class' => $classStudentIds->count(),
                'total_students_in_grade' => $gradeStudentIds->count(),
            ],
            'exam_history' => $examHistory->toArray(),
        ];
    }

    /**
     * 20. Get student ranking detail for specific exam
     */
    public function getStudentRankingDetail(User $teacher, string $studentId, string $examId): ?array
    {
        $student = StudentProfile::with(['user', 'classModel', 'grade'])->find($studentId);
        if (!$student || !$this->hasClassAccess($teacher, $student->class_id)) {
            return null;
        }

        $exam = Exam::find($examId);
        if (!$exam) {
            return null;
        }

        $classStudentIds = StudentProfile::where('class_id', $student->class_id)->pluck('id');
        $gradeStudentIds = StudentProfile::where('grade_id', $student->grade_id)->pluck('id');

        // Get student's marks for this exam
        $studentMarks = ExamMark::where('exam_id', $examId)
            ->where('student_id', $studentId)
            ->with('exam.subject')
            ->get();

        $totalScore = $studentMarks->sum('marks_obtained');
        $maxScore = $studentMarks->sum('total_marks');
        $percentage = $maxScore > 0 ? round(($totalScore / $maxScore) * 100, 1) : 0;

        // Calculate ranks
        $classMarks = ExamMark::where('exam_id', $examId)
            ->whereIn('student_id', $classStudentIds)
            ->selectRaw('student_id, SUM(marks_obtained) as total')
            ->groupBy('student_id')
            ->orderByDesc('total')
            ->pluck('student_id')
            ->toArray();
        $classRank = array_search($studentId, $classMarks) !== false ? array_search($studentId, $classMarks) + 1 : 0;

        $gradeMarks = ExamMark::where('exam_id', $examId)
            ->whereIn('student_id', $gradeStudentIds)
            ->selectRaw('student_id, SUM(marks_obtained) as total')
            ->groupBy('student_id')
            ->orderByDesc('total')
            ->pluck('student_id')
            ->toArray();
        $gradeRank = array_search($studentId, $gradeMarks) !== false ? array_search($studentId, $gradeMarks) + 1 : 0;

        // Subject scores
        $subjectScores = $studentMarks->map(function ($mark) {
            $percentage = $mark->total_marks > 0 ? ($mark->marks_obtained / $mark->total_marks) * 100 : 0;
            return [
                'subject' => $mark->exam?->subject?->name ?? 'Unknown',
                'score' => $mark->marks_obtained,
                'max_score' => $mark->total_marks,
                'grade' => $this->getGrade($percentage),
                'rank' => 0, // TODO: Calculate subject-wise rank
            ];
        });

        return [
            'exam' => [
                'id' => $exam->id,
                'name' => $exam->name,
                'date' => $exam->exam_date?->format('Y-m-d'),
                'type' => $exam->type ?? 'exam',
            ],
            'student' => [
                'id' => $student->id,
                'name' => $student->user?->name ?? 'Unknown',
                'roll_no' => $student->student_identifier ?? '',
            ],
            'overall' => [
                'total_score' => $totalScore,
                'max_score' => $maxScore,
                'percentage' => $percentage,
                'class_rank' => $classRank,
                'grade_rank' => $gradeRank,
            ],
            'subject_scores' => $subjectScores->toArray(),
        ];
    }

    /**
     * Get classes dropdown
     */
    public function getClassesDropdown(User $teacher): Collection
    {
        $teacherProfile = $teacher->teacherProfile;
        
        if (!$teacherProfile) {
            return collect();
        }

        $periods = Period::where('teacher_profile_id', $teacherProfile->id)
            ->whereHas('timetable', fn($q) => $q->where('is_active', true))
            ->with(['timetable.schoolClass.enrolledStudents', 'timetable.schoolClass.grade'])
            ->get();

        $classIds = $periods->pluck('timetable.class_id')->unique()->filter();
        
        return SchoolClass::whereIn('id', $classIds)
            ->with(['enrolledStudents', 'grade'])
            ->get()
            ->map(fn($class) => [
                'id' => $class->id,
                'name' => $class->grade?->name . ($class->name ? ' ' . substr($class->name, -1) : ''),
                'students' => $class->enrolledStudents->count(),
            ]);
    }

    /**
     * Get attendance dropdown - returns each period separately for today
     */
    public function getAttendanceDropdown(User $teacher): Collection
    {
        $teacherProfile = $teacher->teacherProfile;
        
        if (!$teacherProfile) {
            return collect();
        }

        $today = strtolower(now()->format('D'));
        $todayFull = strtolower(now()->format('l'));
        $currentTime = now();

        // Get all periods for today
        $periods = Period::where('teacher_profile_id', $teacherProfile->id)
            ->whereHas('timetable', fn($q) => $q->where('is_active', true))
            ->where(function ($q) use ($today, $todayFull) {
                $q->where('day_of_week', $today)
                  ->orWhere('day_of_week', $todayFull);
            })
            ->where('is_break', false)
            ->with(['timetable.schoolClass.enrolledStudents', 'timetable.schoolClass.grade', 'subject'])
            ->orderBy('period_number')
            ->get();

        // Return each period as a separate entry
        return $periods->map(function ($period) use ($currentTime) {
            $class = $period->timetable?->schoolClass;
            
            // Calculate status based on current time
            $status = null;
            $startTime = is_string($period->starts_at) 
                ? Carbon::parse($period->starts_at) 
                : $period->starts_at;
            $endTime = is_string($period->ends_at) 
                ? Carbon::parse($period->ends_at) 
                : $period->ends_at;
            
            // Set the date to today for comparison
            $periodStart = $currentTime->copy()->setTimeFrom($startTime);
            $periodEnd = $currentTime->copy()->setTimeFrom($endTime);
            
            if ($currentTime->lt($periodStart)) {
                $status = 'upcoming';
            } elseif ($currentTime->between($periodStart, $periodEnd)) {
                $status = 'ongoing';
            } else {
                $status = 'completed';
            }

            return [
                'id' => $class?->id,
                'label' => $class?->grade?->name . ($class?->name ? ' ' . substr($class->name, -1) : ''),
                'current_period_id' => $period->id,
                'period_number' => $period->period_number,
                'start_time' => is_string($period->starts_at) ? Carbon::parse($period->starts_at)->format('H:i') : $period->starts_at->format('H:i'),
                'end_time' => is_string($period->ends_at) ? Carbon::parse($period->ends_at)->format('H:i') : $period->ends_at->format('H:i'),
                'subject' => $period->subject?->name,
                'students_count' => $class?->enrolledStudents?->count() ?? 0,
                'status' => $status,
            ];
        });
    }

    /**
     * 21. Get class student ranking details
     * GET /classes/{class_id}/rankings/{student_id}?exam_id=xxx
     */
    public function getClassStudentRankingDetails(User $teacher, string $classId, string $studentId, string $examId): ?array
    {
        // Verify teacher has access to this class
        if (!$this->hasClassAccess($teacher, $classId)) {
            return null;
        }

        // Get the class
        $class = SchoolClass::with(['grade', 'enrolledStudents'])->find($classId);
        if (!$class) {
            return null;
        }

        // Get the student and verify they belong to this class
        $student = StudentProfile::with(['user'])->find($studentId);
        if (!$student || $student->class_id !== $classId) {
            return null;
        }

        // Get the exam
        $exam = Exam::with(['examType'])->find($examId);
        if (!$exam) {
            return null;
        }

        // Get all students in the class
        $classStudentIds = StudentProfile::where('class_id', $classId)->pluck('id');

        // Get all exam marks for this student for this exam (marks are stored per subject)
        $studentMarks = ExamMark::where('exam_id', $examId)
            ->where('student_id', $studentId)
            ->with(['subject'])
            ->get();

        // Calculate total scores
        $totalScore = $studentMarks->sum('marks_obtained');
        $maxScore = $studentMarks->sum('total_marks');
        $percentage = $maxScore > 0 ? round(($totalScore / $maxScore) * 100, 1) : 0;

        // Calculate class rank based on total scores for this exam
        $allStudentTotals = ExamMark::where('exam_id', $examId)
            ->whereIn('student_id', $classStudentIds)
            ->selectRaw('student_id, SUM(marks_obtained) as total')
            ->groupBy('student_id')
            ->orderByDesc('total')
            ->pluck('student_id')
            ->toArray();

        $rank = array_search($studentId, $allStudentTotals);
        $rank = $rank !== false ? $rank + 1 : 0;

        // Calculate class average
        $classAverage = 0;
        if (count($allStudentTotals) > 0) {
            $allTotals = ExamMark::where('exam_id', $examId)
                ->whereIn('student_id', $classStudentIds)
                ->selectRaw('student_id, SUM(marks_obtained) as total, SUM(total_marks) as max_total')
                ->groupBy('student_id')
                ->get();
            
            $avgPercentages = $allTotals->map(function ($item) {
                return $item->max_total > 0 ? ($item->total / $item->max_total) * 100 : 0;
            });
            $classAverage = round($avgPercentages->avg(), 1);
        }

        // Get subject-wise scores with icons and colors
        // If student has no marks, get all subjects for the grade and return with 0 scores
        $subjectScores = collect();
        
        if ($studentMarks->isNotEmpty()) {
            $subjectScores = $studentMarks->map(function ($mark) {
                $subject = $mark->subject;
                $subjectName = $subject?->name ?? 'Unknown';
                $scorePercentage = $mark->total_marks > 0 ? ($mark->marks_obtained / $mark->total_marks) * 100 : 0;
                $grade = $this->getGrade($scorePercentage);

                // Determine progress color based on percentage
                $progressColor = '#22C55E'; // Green for good
                if ($scorePercentage < 50) {
                    $progressColor = '#EF4444'; // Red for poor
                } elseif ($scorePercentage < 70) {
                    $progressColor = '#F59E0B'; // Amber for average
                } elseif ($scorePercentage < 80) {
                    $progressColor = '#3B82F6'; // Blue for good
                }

                return [
                    'id' => $subject?->id ?? $mark->id,
                    'name' => $subjectName,
                    'icon' => $subject?->icon ?? $this->getSubjectIcon($subjectName),
                    'icon_color' => $subject?->icon_color ?? $this->getSubjectColor($subjectName),
                    'score' => (int) $mark->marks_obtained,
                    'total_score' => (int) $mark->total_marks,
                    'percentage' => round($scorePercentage, 1),
                    'progress_color' => $subject?->progress_color ?? $progressColor,
                    'grade' => $grade,
                ];
            });
        } else {
            // Get all subjects for the grade from exam schedules or grade_subject
            $gradeSubjects = Subject::whereHas('grades', function ($q) use ($class) {
                $q->where('grades.id', $class->grade_id);
            })->get();

            $subjectScores = $gradeSubjects->map(function ($subject) {
                $subjectName = $subject->name ?? 'Unknown';
                return [
                    'id' => $subject->id,
                    'name' => $subjectName,
                    'icon' => $subject->icon ?? $this->getSubjectIcon($subjectName),
                    'icon_color' => $subject->icon_color ?? $this->getSubjectColor($subjectName),
                    'score' => 0,
                    'total_score' => 100,
                    'percentage' => 0,
                    'progress_color' => '#EF4444', // Red for no score
                    'grade' => 'F',
                ];
            });
        }

        // Count unique subjects
        $subjectsCount = $subjectScores->count();

        return [
            'student' => [
                'id' => $student->id,
                'name' => $student->user?->name ?? 'Unknown',
                'roll_no' => $student->student_identifier ?? '',
                'avatar' => strtoupper(substr($student->user?->name ?? 'U', 0, 1)),
                'rank' => $rank,
                'total_score' => (int) $totalScore,
                'max_score' => (int) $maxScore,
                'percentage' => $percentage,
            ],
            'exam' => [
                'id' => $exam->id,
                'name' => $exam->name,
                'type' => $exam->examType?->name ?? 'exam',
                'subjects_count' => $subjectsCount,
                'total_marks' => (int) $maxScore,
            ],
            'subject_scores' => $subjectScores->values()->toArray(),
            'class_info' => [
                'class_id' => $class->id,
                'section' => ($class->grade?->name ?? 'Grade') . ' ' . ($class->name ? substr($class->name, -1) : ''),
                'total_students' => $classStudentIds->count(),
                'class_average' => $classAverage,
            ],
        ];
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    private function hasClassAccess(User $teacher, ?string $classId): bool
    {
        if (!$classId) {
            return false;
        }

        $teacherProfile = $teacher->teacherProfile;
        if (!$teacherProfile) {
            return false;
        }

        return Period::where('teacher_profile_id', $teacherProfile->id)
            ->whereHas('timetable', fn($q) => $q->where('class_id', $classId)->where('is_active', true))
            ->exists();
    }

    private function calculateStudentAttendance(string $studentId): int
    {
        $totalDays = StudentAttendance::where('student_id', $studentId)->count();
        if ($totalDays === 0) return 100;

        $presentDays = StudentAttendance::where('student_id', $studentId)
            ->where('status', 'present')
            ->count();

        return (int) round(($presentDays / $totalDays) * 100);
    }

    private function getStudentBadges(string $studentId): array
    {
        $badges = [];
        
        // Attendance badge
        $attendance = $this->calculateStudentAttendance($studentId);
        if ($attendance >= 95) {
            $badges[] = [
                'id' => '1',
                'label' => '95%+ Attendance',
                'icon' => 'calendar',
                'color' => '#10B981',
            ];
        }

        // Check if class leader
        $student = StudentProfile::find($studentId);
        if ($student && $student->classModel?->class_leader_id === $studentId) {
            $badges[] = [
                'id' => '3',
                'label' => 'Class Leader',
                'icon' => 'star',
                'color' => '#F59E0B',
            ];
        }

        return $badges;
    }

    private function getAchieverStatus(string $studentId): string
    {
        $marks = ExamMark::where('student_id', $studentId)->get();
        if ($marks->isEmpty()) {
            return 'New Student';
        }

        $avgPercentage = $marks->avg(fn($m) => $m->total_marks > 0 ? ($m->marks_obtained / $m->total_marks) * 100 : 0);

        if ($avgPercentage >= 90) return 'High Achiever';
        if ($avgPercentage >= 75) return 'Good Performer';
        if ($avgPercentage >= 60) return 'Average';
        return 'Needs Improvement';
    }

    private function calculateGPA(float $percentage): float
    {
        if ($percentage >= 90) return 4.0;
        if ($percentage >= 80) return 3.5;
        if ($percentage >= 70) return 3.0;
        if ($percentage >= 60) return 2.5;
        if ($percentage >= 50) return 2.0;
        if ($percentage >= 40) return 1.5;
        return 0.0;
    }

    private function getGrade(float $percentage): string
    {
        if ($percentage >= 90) return 'A+';
        if ($percentage >= 80) return 'A';
        if ($percentage >= 70) return 'B+';
        if ($percentage >= 60) return 'B';
        if ($percentage >= 50) return 'C+';
        if ($percentage >= 40) return 'C';
        return 'F';
    }

    private function getGradeColor(string $grade): string
    {
        return match($grade) {
            'A+', 'A' => '#10B981',
            'B+', 'B' => '#3B82F6',
            'C+', 'C' => '#F59E0B',
            default => '#EF4444',
        };
    }

    private function getSubjectIcon(?string $name): string
    {
        $icons = [
            'Mathematics' => '🔢',
            'English' => 'abc',
            'Science' => '🧪',
            'Myanmar' => 'ဃ',
            'History' => '📜',
            'Geography' => '🌍',
            'Physics' => '⚛️',
            'Chemistry' => '🧫',
            'Biology' => '🧬',
        ];

        return $icons[$name] ?? '📖';
    }

    private function getSubjectColor(?string $name): string
    {
        $colors = [
            'Mathematics' => '#DC2626',
            'English' => '#F59E0B',
            'Science' => '#10B981',
            'Myanmar' => '#8B5CF6',
            'History' => '#6366F1',
            'Geography' => '#14B8A6',
            'Physics' => '#3B82F6',
            'Chemistry' => '#EC4899',
            'Biology' => '#22C55E',
        ];

        return $colors[$name] ?? '#6B7280';
    }

    private function getSubjectBgColor(?string $name): string
    {
        $colors = [
            'Mathematics' => '#FEE2E2',
            'English' => '#FEF3C7',
            'Science' => '#D1FAE5',
            'Myanmar' => '#EDE9FE',
            'History' => '#E0E7FF',
            'Geography' => '#CCFBF1',
            'Physics' => '#DBEAFE',
            'Chemistry' => '#FCE7F3',
            'Biology' => '#DCFCE7',
        ];

        return $colors[$name] ?? '#F3F4F6';
    }

    private function getSubjectShortName(?string $name): string
    {
        if (!$name) return 'N/A';
        
        $shortNames = [
            'Mathematics' => 'Math',
            'English' => 'Eng',
            'Science' => 'Sci',
            'Myanmar' => 'Mya',
            'History' => 'His',
            'Geography' => 'Geo',
            'Physics' => 'Phy',
            'Chemistry' => 'Chem',
            'Biology' => 'Bio',
        ];

        return $shortNames[$name] ?? substr($name, 0, 4);
    }
}
