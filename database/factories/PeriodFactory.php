<?php

namespace Database\Factories;

use App\Models\Period;
use App\Models\Timetable;
use App\Models\TeacherProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Period>
 */
class PeriodFactory extends Factory
{
    protected $model = Period::class;

    public function definition(): array
    {
        $startHour = $this->faker->numberBetween(8, 14);
        $startTime = sprintf('%02d:00', $startHour);
        $endTime = sprintf('%02d:45', $startHour);

        return [
            'timetable_id' => Timetable::factory(),
            'day_of_week' => $this->faker->randomElement(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
            'period_number' => $this->faker->numberBetween(1, 8),
            'starts_at' => $startTime,
            'ends_at' => $endTime,
            'is_break' => false,
            'subject_id' => null,
            'teacher_profile_id' => TeacherProfile::factory(),
            'room_id' => null,
            'notes' => null,
        ];
    }

    /**
     * Indicate that the period is a break.
     */
    public function break(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_break' => true,
            'subject_id' => null,
            'teacher_profile_id' => null,
        ]);
    }
}
