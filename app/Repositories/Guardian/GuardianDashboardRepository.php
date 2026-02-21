<?php

namespace App\Repositories\Guardian;

use App\Interfaces\Guardian\GuardianDashboardRepositoryInterface;
use App\Models\Announcement;
use App\Models\Homework;
use App\Models\Invoice;
use App\Models\Period;
use App\Models\StudentAttendance;
use App\Models\StudentProfile;
use App\Models\Timetable;
use Carbon\Carbon;

class GuardianDashboardRepository implements GuardianDashboardRepositoryInterface
{
    public function getDashboardData(StudentProfile $student): array
    {
        return [
            'student' => $this->formatStudentInfo($student),
            'today_schedule' => $this->getTodaySchedule($student),
            'announcements' => $this->getRecentAnnouncements($student, 3),
            'upcoming_homework' => $this->getUpcomingHomework($student, 5),
            'fee_reminder' => $this->getFeeReminder($student),
        ];
    }

    public function getTodaySchedule(StudentProfile $student): array
    {
        $today = strtolower(Carbon::now()->format('l')); // 'monday', 'tuesday', etc.
        $currentTime = Carbon::now()->format('H:i:s');
        
        // Get the active timetable for the student's class
        $timetable = Timetable::where('class_id', $student->class_id)
            ->where('is_active', true)
            ->first();

        if (!$timetable) {
            return [];
        }

        // Get today's periods
        $periods = Period::where('timetable_id', $timetable->id)
            ->where('day_of_week', $today)
            ->with(['subject', 'teacher.user', 'room'])
            ->orderBy('period_number')
            ->get();

        return $periods->map(function ($period) use ($currentTime) {
            $startTime = Carbon::parse($period->starts_at)->format('H:i:s');
            $endTime = Carbon::parse($period->ends_at)->format('H:i:s');
            
            // Check if current time is within this period
            $isActive = $currentTime >= $startTime && $currentTime < $endTime;
            
            if ($period->is_break) {
                return [
                    'id' => $period->id,
                    'period_number' => $period->period_number,
                    'subject' => 'Break',
                    'subject_icon' => 'coffee',
                    'subject_color' => '#94a3b8',
                    'teacher' => null,
                    'time' => Carbon::parse($period->starts_at)->format('H:i') . ' - ' . Carbon::parse($period->ends_at)->format('H:i'),
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'room' => $period->room?->name ?? 'N/A',
                    'is_break' => true,
                    'is_active' => $isActive,
                ];
            }

            return [
                'id' => $period->id,
                'period_number' => $period->period_number,
                'subject' => $period->subject?->name ?? 'N/A',
                'subject_icon' => $period->subject?->icon ?? 'book',
                'subject_color' => $period->subject?->icon_color ?? '#3b82f6',
                'teacher' => $period->teacher?->user?->name ?? 'N/A',
                'time' => Carbon::parse($period->starts_at)->format('H:i') . ' - ' . Carbon::parse($period->ends_at)->format('H:i'),
                'start_time' => $startTime,
                'end_time' => $endTime,
                'room' => $period->room?->name ?? 'N/A',
                'is_break' => false,
                'is_active' => $isActive,
            ];
        })->toArray();
    }

    public function getUpcomingHomework(StudentProfile $student, int $limit = 5): array
    {
        $homework = Homework::where('class_id', $student->class_id)
            ->where('due_date', '>=', Carbon::today())
            ->whereDoesntHave('submissions', function ($query) use ($student) {
                // Exclude homework that has been submitted by this student
                $query->where('student_id', $student->id)
                    ->whereIn('status', ['submitted', 'graded']);
            })
            ->with(['subject'])
            ->orderBy('due_date')
            ->limit($limit)
            ->get();

        return $homework->map(function ($hw) use ($student) {
            $submission = $hw->submissions()->where('student_id', $student->id)->first();
            
            return [
                'id' => $hw->id,
                'subject' => $hw->subject?->name ?? 'N/A',
                'subject_icon' => $hw->subject?->icon ?? 'book',
                'subject_color' => $hw->subject?->icon_color ?? '#3b82f6',
                'title' => $hw->title,
                'due_date' => $hw->due_date?->format('Y-m-d'),
                'status' => 'pending',
            ];
        })->toArray();
    }

    public function getRecentAnnouncements(StudentProfile $student, int $limit = 5): array
    {
        $announcements = Announcement::where('is_published', true)
            ->where('status', true)
            ->where(function ($query) use ($student) {
                // Check if announcement targets this student's grade
                $query->whereJsonContains('target_grades', $student->grade_id)
                    // Or if target_grades is null/empty (means all grades)
                    ->orWhereNull('target_grades')
                    ->orWhereJsonLength('target_grades', 0);
            })
            ->where(function ($query) {
                // Check if announcement targets guardians or all roles
                $query->whereJsonContains('target_roles', 'guardian')
                    ->orWhereJsonContains('target_roles', 'parent')
                    ->orWhereNull('target_roles')
                    ->orWhereJsonLength('target_roles', 0);
            })
            ->with('announcementType')
            ->orderBy('publish_date', 'desc')
            ->limit($limit)
            ->get();

        return $announcements->map(function ($announcement) {
            return [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'description' => \Illuminate\Support\Str::limit($announcement->content, 100),
                'priority' => $announcement->priority ?? 'normal',
                'date' => $announcement->publish_date ? $announcement->publish_date->format('Y-m-d') : $announcement->created_at->format('Y-m-d'),
                'is_read' => false, // TODO: Implement read tracking
            ];
        })->toArray();
    }

    public function getFeeReminder(StudentProfile $student): array
    {
        $pendingInvoices = Invoice::where('student_id', $student->id)
            ->where('status', 'pending')
            ->get();

        $totalPending = $pendingInvoices->sum('balance');
        $nextDueDate = $pendingInvoices->min('due_date');

        return [
            'has_pending' => $pendingInvoices->count() > 0,
            'total_pending' => (float) $totalPending,
            'due_date' => $nextDueDate ? Carbon::parse($nextDueDate)->format('Y-m-d') : null,
            'invoices_pending' => $pendingInvoices->count(),
        ];
    }

    public function getCurrentClass(StudentProfile $student): ?array
    {
        $now = Carbon::now();
        $today = strtolower($now->format('l')); // 'monday', 'tuesday', etc.
        $currentTime = $now->format('H:i:s');

        // Get the active timetable for the student's class
        $timetable = Timetable::where('class_id', $student->class_id)
            ->where('is_active', true)
            ->first();

        if (!$timetable) {
            return null;
        }

        // Find the current active period
        $period = Period::where('timetable_id', $timetable->id)
            ->where('day_of_week', $today)
            ->whereTime('starts_at', '<=', $currentTime)
            ->whereTime('ends_at', '>', $currentTime)
            ->where('is_break', false) // Exclude breaks
            ->with(['subject.curriculumChapters.topics', 'teacher.user', 'room'])
            ->first();

        if (!$period) {
            return null;
        }

        // Get current chapter with unfinished topics
        $chapterData = null;
        if ($period->subject) {
            // Get all chapters for this subject and grade
            $chapters = $period->subject->curriculumChapters()
                ->where(function ($query) use ($student) {
                    $query->where('grade_id', $student->grade_id)
                        ->orWhereNull('grade_id');
                })
                ->with(['topics.progress' => function ($query) use ($student, $period) {
                    $query->where('class_id', $student->class_id)
                        ->where('teacher_id', $period->teacher_profile_id);
                }])
                ->orderBy('order')
                ->get();

            // Find the first chapter with unfinished topics
            $currentChapter = null;
            foreach ($chapters as $chapter) {
                $hasUnfinishedTopics = $chapter->topics->some(function ($topic) {
                    $progress = $topic->progress->first();
                    return !$progress || in_array($progress->status, ['not_started', 'in_progress']);
                });

                if ($hasUnfinishedTopics) {
                    $currentChapter = $chapter;
                    break;
                }
            }

            // If all chapters are completed, get the last chapter
            if (!$currentChapter && $chapters->isNotEmpty()) {
                $currentChapter = $chapters->last();
            }

            if ($currentChapter) {
                $topics = $currentChapter->topics->map(function ($topic) {
                    $progress = $topic->progress->first();
                    $status = $progress ? $progress->status : 'not_started';
                    
                    return [
                        'id' => $topic->id,
                        'name' => $topic->title,
                        'status' => $status,
                        'duration' => $topic->estimated_minutes ? $topic->estimated_minutes . ' min' : 'N/A',
                        'order' => $topic->order,
                    ];
                })->toArray();

                // Find the current topic (first not completed)
                $currentTopic = collect($topics)->first(function ($topic) {
                    return in_array($topic['status'], ['not_started', 'in_progress']);
                });

                $chapterData = [
                    'id' => $currentChapter->id,
                    'number' => $currentChapter->order,
                    'title' => $currentChapter->title,
                    'current_topic' => $currentTopic ? $currentTopic['name'] : ($topics[0]['name'] ?? 'N/A'),
                    'topics' => $topics,
                ];
            }
        }

        return [
            'id' => $period->id,
            'subject' => $period->subject?->name ?? 'N/A',
            'subject_id' => $period->subject_id,
            'subject_icon' => $period->subject?->icon ?? 'book',
            'subject_color' => $period->subject?->icon_color ?? '#3b82f6',
            'teacher' => $period->teacher?->user?->name ?? 'N/A',
            'teacher_id' => $period->teacher_profile_id,
            'period' => $period->period_number,
            'start_time' => Carbon::parse($period->starts_at)->format('H:i'),
            'end_time' => Carbon::parse($period->ends_at)->format('H:i'),
            'status' => 'live',
            'room' => $period->room?->name ?? 'N/A',
            'chapter' => $chapterData,
        ];
    }

    public function getNextClass(StudentProfile $student): ?array
    {
        $now = Carbon::now();
        $today = strtolower($now->format('l'));
        $currentTime = $now->format('H:i:s');

        // Get the active timetable for the student's class
        $timetable = Timetable::where('class_id', $student->class_id)
            ->where('is_active', true)
            ->first();

        if (!$timetable) {
            return null;
        }

        // Find the next class today
        $period = Period::where('timetable_id', $timetable->id)
            ->where('day_of_week', $today)
            ->whereTime('starts_at', '>', $currentTime)
            ->where('is_break', false) // Exclude breaks
            ->with(['subject', 'teacher.user', 'room'])
            ->orderBy('starts_at')
            ->first();

        if (!$period) {
            return null;
        }

        return [
            'subject' => $period->subject?->name ?? 'N/A',
            'subject_icon' => $period->subject?->icon ?? 'book',
            'subject_color' => $period->subject?->icon_color ?? '#3b82f6',
            'teacher' => $period->teacher?->user?->name ?? 'N/A',
            'period' => $period->period_number,
            'start_time' => Carbon::parse($period->starts_at)->format('H:i'),
            'end_time' => Carbon::parse($period->ends_at)->format('H:i'),
            'room' => $period->room?->name ?? 'N/A',
        ];
    }

    private function formatStudentInfo(StudentProfile $student): array
    {
        return [
            'id' => $student->id,
            'name' => $student->user?->name ?? 'N/A',
            'student_id' => $student->student_identifier ?? $student->student_id,
            'grade' => $student->grade?->name ?? 'N/A',
            'section' => $student->classModel?->section ?? 'N/A',
            'profile_image' => $student->photo_path ? asset($student->photo_path) : null,
        ];
    }
}
