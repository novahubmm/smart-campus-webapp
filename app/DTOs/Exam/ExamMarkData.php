<?php

namespace App\DTOs\Exam;

use Illuminate\Support\Arr;

class ExamMarkData
{
    public function __construct(
        public readonly string $exam_id,
        public readonly string $student_id,
        public readonly string $subject_id,
        public readonly ?float $marks_obtained,
        public readonly float $total_marks,
        public readonly ?string $grade,
        public readonly ?string $remark,
        public readonly ?string $entered_by,
        public readonly bool $is_absent,
    ) {}

    public static function from(array $payload, ?string $enteredBy = null): self
    {
        return new self(
            exam_id: $payload['exam_id'],
            student_id: $payload['student_id'],
            subject_id: $payload['subject_id'],
            marks_obtained: isset($payload['marks_obtained']) ? (float) $payload['marks_obtained'] : null,
            total_marks: (float) ($payload['total_marks'] ?? 100),
            grade: Arr::get($payload, 'grade'),
            remark: Arr::get($payload, 'remark'),
            entered_by: $payload['entered_by'] ?? $enteredBy,
            is_absent: (bool) ($payload['is_absent'] ?? false),
        );
    }

    public function toArray(): array
    {
        return [
            'exam_id' => $this->exam_id,
            'student_id' => $this->student_id,
            'subject_id' => $this->subject_id,
            'marks_obtained' => $this->marks_obtained,
            'total_marks' => $this->total_marks,
            'grade' => $this->grade,
            'remark' => $this->remark,
            'entered_by' => $this->entered_by,
            'is_absent' => $this->is_absent,
        ];
    }
}
