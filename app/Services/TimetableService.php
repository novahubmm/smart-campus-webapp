<?php

namespace App\Services;

use App\DTOs\Timetable\PeriodData;
use App\DTOs\Timetable\TimetableData;
use App\DTOs\Timetable\TimetableFilterData;
use App\Interfaces\TimetableRepositoryInterface;
use App\Models\Setting;
use App\Models\Timetable;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class TimetableService
{
    public function __construct(private readonly TimetableRepositoryInterface $repository) {}

    public function list(TimetableFilterData $filter): Collection
    {
        return $this->repository->list($filter);
    }

    public function create(array $payload, array $periodPayloads): Timetable
    {
        $data = $this->hydrateWithDefaults(TimetableData::from($payload));

        $existing = $this->repository->getForClass($data->class_id);

        $periods = $this->mapPeriods($periodPayloads, $data);

        $this->validatePeriods($periods, $data);

        if ($existing) {
            // Overwrite existing timetable for this class instead of blocking
            return $this->repository->updateTimetable($existing, $data, $periods);
        }

        return $this->repository->storeTimetable($data, $periods);
    }

    public function update(Timetable $timetable, array $payload, array $periodPayloads): Timetable
    {
        $data = $this->hydrateWithDefaults(TimetableData::from($payload));

        if ($timetable->class_id !== $data->class_id) {
            $this->assertClassAvailable($data->class_id, $timetable->id);
        }

        $periods = $this->mapPeriods($periodPayloads, $data);
        $this->validatePeriods($periods, $data);

        return $this->repository->updateTimetable($timetable, $data, $periods);
    }

    public function activate(Timetable $timetable): Timetable
    {
        if ($timetable->periods()->count() === 0) {
            throw ValidationException::withMessages([
                'periods' => __('Cannot activate timetable without periods.'),
            ]);
        }

        return $this->repository->activateTimetable($timetable);
    }

    private function hydrateWithDefaults(TimetableData $data): TimetableData
    {
        $setting = Setting::first();

        $merged = array_merge($data->toArray(), [
            'minutes_per_period' => $data->minutes_per_period ?? $setting?->minute_per_period,
            'break_duration' => $data->break_duration ?? $setting?->break_duration,
            'school_start_time' => $data->school_start_time ?? $setting?->school_start_time,
            'school_end_time' => $data->school_end_time ?? $setting?->school_end_time,
            'week_days' => $data->week_days ?? $setting?->week_days,
        ]);

        return TimetableData::from($merged);
    }

    private function mapPeriods(array $periodPayloads, TimetableData $data): Collection
    {
        return collect($periodPayloads)->map(fn($payload) => PeriodData::from($payload));
    }

    private function validatePeriods(Collection $periods, TimetableData $data): void
    {
        $setting = Setting::first();
        $allowedDays = $data->week_days ?? $setting?->week_days ?? [];
        
        // Normalize allowed days to full format
        $normalizedAllowedDays = collect($allowedDays)->map(function ($day) {
            $day = strtolower($day);
            // Convert short format to full format if needed
            $dayMap = [
                'mon' => 'monday', 'tue' => 'tuesday', 'wed' => 'wednesday',
                'thu' => 'thursday', 'fri' => 'friday', 'sat' => 'saturday', 'sun' => 'sunday'
            ];
            return $dayMap[$day] ?? $day; // Return full format
        })->toArray();

        $periods->each(function (PeriodData $period) use ($normalizedAllowedDays) {
            if ($period->starts_at >= $period->ends_at) {
                throw ValidationException::withMessages([
                    'ends_at' => __('Period end time must be after start time.'),
                ]);
            }

            // Normalize the period day to full format for comparison
            $periodDay = strtolower($period->day_of_week);
            // Convert short format to full format if needed
            $dayMap = [
                'mon' => 'monday', 'tue' => 'tuesday', 'wed' => 'wednesday',
                'thu' => 'thursday', 'fri' => 'friday', 'sat' => 'saturday', 'sun' => 'sunday'
            ];
            $periodDay = $dayMap[$periodDay] ?? $periodDay; // Ensure full format

            if (!empty($normalizedAllowedDays) && !in_array($periodDay, $normalizedAllowedDays, true)) {
                throw ValidationException::withMessages([
                    'day_of_week' => __('Day not allowed in timetable configuration.'),
                ]);
            }
        });

        // Check overlaps per day
        // Normalize day names before grouping to handle both 'mon' and 'monday' formats
        $byDay = $periods->groupBy(function (PeriodData $p) {
            $day = strtolower($p->day_of_week);
            // Convert short format to full format for consistent grouping
            $dayMap = [
                'mon' => 'monday', 'tue' => 'tuesday', 'wed' => 'wednesday',
                'thu' => 'thursday', 'fri' => 'friday', 'sat' => 'saturday', 'sun' => 'sunday'
            ];
            return $dayMap[$day] ?? $day; // Ensure full format
        });

        foreach ($byDay as $day => $dayPeriods) {
            $sorted = $dayPeriods->sortBy(fn(PeriodData $p) => $p->starts_at)->values();
            for ($i = 0; $i < $sorted->count(); $i++) {
                for ($j = $i + 1; $j < $sorted->count(); $j++) {
                    $a = $sorted[$i];
                    $b = $sorted[$j];
                    if ($this->overlaps($a->starts_at, $a->ends_at, $b->starts_at, $b->ends_at)) {
                        throw ValidationException::withMessages([
                            'periods' => __('Overlapping periods detected on :day', ['day' => $day]),
                        ]);
                    }
                }
            }
        }

        // Teacher double-book check per day
        $byTeacherDay = $periods
            ->filter(fn(PeriodData $p) => $p->teacher_profile_id !== null)
            ->groupBy(fn(PeriodData $p) => $p->teacher_profile_id . '|' . $p->day_of_week);

        foreach ($byTeacherDay as $key => $teacherPeriods) {
            $sorted = $teacherPeriods->sortBy(fn(PeriodData $p) => $p->starts_at)->values();
            for ($i = 0; $i < $sorted->count(); $i++) {
                for ($j = $i + 1; $j < $sorted->count(); $j++) {
                    $a = $sorted[$i];
                    $b = $sorted[$j];
                    if ($this->overlaps($a->starts_at, $a->ends_at, $b->starts_at, $b->ends_at)) {
                        throw ValidationException::withMessages([
                            'teacher_profile_id' => __('Teacher has overlapping periods.'),
                        ]);
                    }
                }
            }
        }
    }

    private function overlaps(string $startA, string $endA, string $startB, string $endB): bool
    {
        // Periods overlap if one starts before the other ends
        // Consecutive periods (where endA == startB) are NOT overlapping
        if ($endA <= $startB || $endB <= $startA) {
            return false; // No overlap - periods are consecutive or separate
        }
        return true; // Overlap detected
    }

    private function assertClassAvailable(string $classId, ?string $ignoreTimetableId = null): void
    {
        $existing = $this->repository->getForClass($classId);

        if ($existing && (!$ignoreTimetableId || $existing->id !== $ignoreTimetableId)) {
            throw ValidationException::withMessages([
                'class_id' => __('A timetable already exists for this class.'),
            ]);
        }
    }
}
