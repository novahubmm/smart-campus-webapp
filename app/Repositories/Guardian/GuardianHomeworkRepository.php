<?php

namespace App\Repositories\Guardian;

use App\Interfaces\Guardian\GuardianHomeworkRepositoryInterface;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\StudentProfile;
use App\Services\Upload\FileUploadService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class GuardianHomeworkRepository implements GuardianHomeworkRepositoryInterface
{
    public function getHomework(StudentProfile $student, ?string $status = null, ?string $subjectId = null): array
    {
        $query = Homework::where('class_id', $student->class_id)
            ->with(['subject', 'teacher.user']);

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        $homework = $query->orderBy('due_date', 'desc')->get();

        return $homework->map(function ($hw) use ($student, $status) {
            $submission = HomeworkSubmission::where('homework_id', $hw->id)
                ->where('student_id', $student->id)
                ->first();

            $hwStatus = $this->getHomeworkStatus($hw, $submission);

            // Filter by status if provided
            if ($status && $hwStatus !== $status) {
                return null;
            }

            return [
                'id' => $hw->id,
                'subject' => $hw->subject?->name ?? 'N/A',
                'subject_icon' => $hw->subject?->icon ?? 'book',
                'title' => $hw->title,
                'description' => $hw->description,
                'assigned_date' => $hw->assigned_date?->format('Y-m-d'),
                'due_date' => $hw->due_date?->format('Y-m-d'),
                'due_time' => $hw->due_time ?? '23:59:00',
                'status' => $hwStatus,
                'priority' => $this->getPriority($hw),
                'attachment' => $hw->attachment ? asset('storage/' . $hw->attachment) : null,
            ];
        })->filter()->values()->toArray();
    }

    public function getHomeworkDetail(string $homeworkId, StudentProfile $student): array
    {
        $hw = Homework::with(['subject', 'teacher.user'])
            ->findOrFail($homeworkId);

        $submission = HomeworkSubmission::where('homework_id', $hw->id)
            ->where('student_id', $student->id)
            ->first();

        return [
            'id' => $hw->id,
            'subject' => $hw->subject ? [
                'id' => $hw->subject->id,
                'name' => $hw->subject->name,
                'icon' => $hw->subject->icon ?? 'book',
            ] : null,
            'title' => $hw->title,
            'description' => $hw->description,
            'assigned_date' => $hw->assigned_date?->format('Y-m-d'),
            'due_date' => $hw->due_date?->format('Y-m-d'),
            'due_time' => $hw->due_time ?? '23:59:00',
            'status' => $this->getHomeworkStatus($hw, $submission),
            'priority' => $this->getPriority($hw),
            'attachment' => $hw->attachment ? asset('storage/' . $hw->attachment) : null,
            'assigned_by' => $hw->teacher ? [
                'name' => $hw->teacher->user?->name ?? 'N/A',
                'subject' => $hw->subject?->name ?? 'N/A',
            ] : null,
            'submission' => $submission ? [
                'id' => $submission->id,
                'status' => $submission->status === 'submitted' || $submission->status === 'graded' ? 'submitted' : $submission->status,
                'submitted_at' => $submission->submitted_at?->toISOString(),
                'remarks' => $submission->remarks,
                'grade' => $submission->grade ? number_format($submission->grade, 0) . '/10' : null,
                'feedback' => $submission->feedback,
            ] : null,
        ];
    }

    public function getHomeworkStats(StudentProfile $student): array
    {
        $homework = Homework::where('class_id', $student->class_id)->get();
        
        $total = $homework->count();
        $pending = 0;
        $completed = 0;
        $overdue = 0;

        foreach ($homework as $hw) {
            $submission = HomeworkSubmission::where('homework_id', $hw->id)
                ->where('student_id', $student->id)
                ->first();

            $status = $this->getHomeworkStatus($hw, $submission);

            if ($status === 'completed') {
                $completed++;
            } elseif ($status === 'overdue') {
                $overdue++;
            } else {
                $pending++;
            }
        }

        $completionRate = $total > 0 ? round(($completed / $total) * 100, 1) : 0;

        return [
            'total' => $total,
            'pending' => $pending,
            'completed' => $completed,
            'overdue' => $overdue,
            'completion_rate' => $completionRate,
        ];
    }

    public function updateHomeworkStatus(string $homeworkId, StudentProfile $student, string $status): bool
    {
        // Map API status to database status
        // API uses 'completed' but database uses 'submitted'
        $dbStatus = $status === 'completed' ? 'submitted' : $status;
        
        $submission = HomeworkSubmission::firstOrCreate(
            [
                'homework_id' => $homeworkId,
                'student_id' => $student->id,
            ],
            [
                'status' => $dbStatus,
                'submitted_at' => in_array($status, ['completed', 'submitted']) ? Carbon::now() : null,
            ]
        );

        if ($submission->wasRecentlyCreated) {
            return true;
        }

        return $submission->update([
            'status' => $dbStatus,
            'submitted_at' => in_array($status, ['completed', 'submitted']) ? Carbon::now() : $submission->submitted_at,
        ]);
    }

    public function submitHomework(string $homeworkId, StudentProfile $student, ?string $notes, array $photos): array
    {
        $homework = Homework::findOrFail($homeworkId);

        // Verify homework belongs to student's class
        if ($homework->class_id !== $student->class_id) {
            throw new \Exception('Homework not found or unauthorized');
        }

        // Handle file uploads
        $uploadedFiles = [];
        $uploadService = app(FileUploadService::class);
        foreach ($photos as $photo) {
            $path = $uploadService->storeUploadedFile(
                $photo,
                'homework_submissions/' . $student->id,
                'public',
                'homework_submission',
                FileUploadService::MAX_UPLOAD_BYTES,
                ['jpg', 'jpeg', 'png', 'pdf']
            );
            $uploadedFiles[] = [
                'path' => $path,
                'original_name' => $photo->getClientOriginalName(),
                'mime_type' => $photo->getMimeType(),
                'size' => $photo->getSize(),
            ];
        }

        // Create or update submission
        $submission = HomeworkSubmission::updateOrCreate(
            [
                'homework_id' => $homeworkId,
                'student_id' => $student->id,
            ],
            [
                'remarks' => $notes,
                'attachments' => $uploadedFiles,
                'status' => 'submitted',
                'submitted_at' => Carbon::now(),
            ]
        );

        return [
            'submission_id' => $submission->id,
            'homework_id' => $homework->id,
            'submitted_at' => $submission->submitted_at->toISOString(),
            'status' => 'submitted',
        ];
    }

    private function getHomeworkStatus(Homework $hw, ?HomeworkSubmission $submission): string
    {
        // Map database 'submitted' status to API 'completed' for display
        if ($submission && in_array($submission->status, ['submitted', 'graded'])) {
            return 'completed';
        }

        if ($hw->due_date && Carbon::now()->gt($hw->due_date)) {
            return 'overdue';
        }

        return 'pending';
    }

    private function getPriority(Homework $hw): string
    {
        if (!$hw->due_date) {
            return 'normal';
        }

        $daysUntilDue = Carbon::now()->diffInDays($hw->due_date, false);

        if ($daysUntilDue < 0) {
            return 'overdue';
        }

        if ($daysUntilDue <= 1) {
            return 'high';
        }

        if ($daysUntilDue <= 3) {
            return 'medium';
        }

        return 'normal';
    }
}
