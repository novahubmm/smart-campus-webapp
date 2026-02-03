<?php

namespace App\DTOs\TimeTableAttendance;

use Illuminate\Support\Arr;

class TimeTableSetupData
{
    public function __construct(
        public readonly int $numberOfPeriodsPerDay,
        public readonly int $minutePerPeriod,
        public readonly ?int $breakDuration,
        public readonly string $schoolStartTime,
        public readonly string $schoolEndTime,
        /** @var array<int, string> */
        public readonly array $weekDays,
    ) {}

    public static function from(array $validated): self
    {
        return new self(
            numberOfPeriodsPerDay: (int) $validated['number_of_periods_per_day'],
            minutePerPeriod: (int) $validated['minute_per_period'],
            breakDuration: isset($validated['break_duration']) ? (int) $validated['break_duration'] : null,
            schoolStartTime: $validated['school_start_time'],
            schoolEndTime: $validated['school_end_time'],
            weekDays: array_values(Arr::wrap($validated['week_days'] ?? [])),
        );
    }
}
