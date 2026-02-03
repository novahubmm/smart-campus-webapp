<?php

namespace App\DTOs\Attendance;

class StudentAttendanceData
{
    public function __construct(
        public readonly string $student_id,
        public readonly string $date,
        public readonly ?string $period_id,
        public readonly string $status,
        public readonly ?string $remark,
        public readonly ?string $marked_by,
        public readonly ?string $collect_time,
    ) {}

    public static function from(array $payload): self
    {
        return new self(
            $payload['student_id'],
            $payload['date'],
            $payload['period_id'] ?? null,
            $payload['status'] ?? 'present',
            $payload['remark'] ?? null,
            $payload['marked_by'] ?? null,
            $payload['collect_time'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'student_id' => $this->student_id,
            'date' => $this->date,
            'period_id' => $this->period_id,
            'status' => $this->status,
            'remark' => $this->remark,
            'marked_by' => $this->marked_by,
            'collect_time' => $this->collect_time,
        ];
    }
}
