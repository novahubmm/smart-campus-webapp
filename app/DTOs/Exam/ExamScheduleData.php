<?php

namespace App\DTOs\Exam;

use Illuminate\Support\Arr;

class ExamScheduleData
{
    public function __construct(
        public readonly ?string $id,
        public readonly ?string $exam_id,
        public readonly string $subject_id,
        public readonly string $exam_date,
        public readonly string $start_time,
        public readonly string $end_time,
        public readonly ?string $room_id,
        public readonly ?string $teacher_id,
        public readonly float $total_marks,
        public readonly float $passing_marks,
    ) {}

    public static function from(array $payload): self
    {
        return new self(
            id: Arr::get($payload, 'id'),
            exam_id: Arr::get($payload, 'exam_id'),
            subject_id: $payload['subject_id'],
            exam_date: $payload['exam_date'],
            start_time: $payload['start_time'],
            end_time: $payload['end_time'],
            room_id: Arr::get($payload, 'room_id'),
            teacher_id: Arr::get($payload, 'teacher_id'),
            total_marks: (float) ($payload['total_marks'] ?? 100),
            passing_marks: (float) ($payload['passing_marks'] ?? 40),
        );
    }

    public function toArray(): array
    {
        return [
            'exam_id' => $this->exam_id,
            'subject_id' => $this->subject_id,
            'exam_date' => $this->exam_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'room_id' => $this->room_id,
            'teacher_id' => $this->teacher_id,
            'total_marks' => $this->total_marks,
            'passing_marks' => $this->passing_marks,
        ];
    }
}
