<?php

namespace App\Rules;

use App\Models\Period;
use App\Models\Timetable;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TeacherNotDoubleBooked implements ValidationRule
{
    protected string $dayOfWeek;
    protected string $startsAt;
    protected string $endsAt;
    protected ?string $currentTimetableId;
    protected ?string $currentPeriodId;

    public function __construct(
        string $dayOfWeek,
        string $startsAt,
        string $endsAt,
        ?string $currentTimetableId = null,
        ?string $currentPeriodId = null
    ) {
        $this->dayOfWeek = $this->normalizeDayName($dayOfWeek);
        $this->startsAt = $startsAt;
        $this->endsAt = $endsAt;
        $this->currentTimetableId = $currentTimetableId;
        $this->currentPeriodId = $currentPeriodId;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value) {
            return; // No teacher assigned, skip validation
        }

        $teacherProfileId = $value;

        // Get all active timetables (excluding the current one being edited)
        $activeTimetables = Timetable::where('is_active', true)
            ->when($this->currentTimetableId, function ($query) {
                $query->where('id', '!=', $this->currentTimetableId);
            })
            ->pluck('id');

        // Check if teacher has conflicting periods in other active timetables
        $conflictingPeriods = Period::whereIn('timetable_id', $activeTimetables)
            ->where('teacher_profile_id', $teacherProfileId)
            ->where('day_of_week', $this->dayOfWeek)
            ->where('is_break', false)
            ->when($this->currentPeriodId, function ($query) {
                $query->where('id', '!=', $this->currentPeriodId);
            })
            ->get();

        foreach ($conflictingPeriods as $period) {
            if ($this->timeOverlaps($this->startsAt, $this->endsAt, $period->starts_at, $period->ends_at)) {
                $period->load('timetable.schoolClass');
                $className = $period->timetable?->schoolClass?->name ?? 'another class';
                $dayName = ucfirst($this->dayOfWeek);
                
                $fail("Teacher is already assigned to {$className} on {$dayName} from {$period->starts_at} to {$period->ends_at}.");
                return;
            }
        }
    }

    /**
     * Check if two time ranges overlap
     */
    protected function timeOverlaps(string $startA, string $endA, string $startB, string $endB): bool
    {
        // Convert to comparable format (remove seconds if present)
        $startA = substr($startA, 0, 5);
        $endA = substr($endA, 0, 5);
        $startB = substr($startB, 0, 5);
        $endB = substr($endB, 0, 5);

        // Periods overlap if one starts before the other ends
        // Consecutive periods (where endA == startB) are NOT overlapping
        if ($endA <= $startB || $endB <= $startA) {
            return false; // No overlap
        }
        return true; // Overlap detected
    }

    /**
     * Normalize day name to full lowercase format (monday, tuesday, etc.)
     */
    protected function normalizeDayName(string $day): string
    {
        $day = strtolower($day);
        
        // Convert short format to full format if needed
        $dayMap = [
            'mon' => 'monday',
            'tue' => 'tuesday',
            'wed' => 'wednesday',
            'thu' => 'thursday',
            'fri' => 'friday',
            'sat' => 'saturday',
            'sun' => 'sunday'
        ];
        
        return $dayMap[$day] ?? $day;
    }
}
