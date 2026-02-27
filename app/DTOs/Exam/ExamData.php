<?php

namespace App\DTOs\Exam;

use Illuminate\Support\Arr;

class ExamData
{
    /**
     * @param array<int, ExamScheduleData> $schedules
     */
    public function __construct(
        public readonly string $name,
        public readonly ?string $exam_id,
        public readonly string $exam_type_id,
        public readonly string $batch_id,
        public readonly ?string $grade_id,
        public readonly ?string $class_id,
        public readonly string $start_date,
        public readonly string $end_date,
        public readonly string $status,
        public readonly array $schedules,
    ) {}

    public static function from(array $payload): self
    {
        $schedules = collect($payload['schedules'] ?? [])
            ->filter()
            ->map(fn(array $item) => ExamScheduleData::from($item))
            ->values()
            ->all();

        // Auto-determine status based on dates if not explicitly set or if set to 'upcoming'
        $status = $payload['status'] ?? 'upcoming';
        
        if (!isset($payload['status']) || $payload['status'] === 'upcoming') {
            $today = now()->startOfDay();
            $startDate = \Carbon\Carbon::parse($payload['start_date'])->startOfDay();
            $endDate = \Carbon\Carbon::parse($payload['end_date'])->endOfDay();
            
            if ($today->greaterThanOrEqualTo($startDate) && $today->lessThanOrEqualTo($endDate)) {
                $status = 'ongoing';
            } elseif ($today->greaterThan($endDate)) {
                $status = 'completed';
            } else {
                $status = 'upcoming';
            }
        }

        return new self(
            name: $payload['name'],
            exam_id: Arr::get($payload, 'exam_id'),
            exam_type_id: $payload['exam_type_id'],
            batch_id: $payload['batch_id'],
            grade_id: Arr::get($payload, 'grade_id'),
            class_id: Arr::get($payload, 'class_id'),
            start_date: $payload['start_date'],
            end_date: $payload['end_date'],
            status: $status,
            schedules: $schedules,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'exam_id' => $this->exam_id,
            'exam_type_id' => $this->exam_type_id,
            'batch_id' => $this->batch_id,
            'grade_id' => $this->grade_id,
            'class_id' => $this->class_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
        ];
    }
}
