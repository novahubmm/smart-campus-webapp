<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Timetable;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Timetable>
 */
class TimetableFactory extends Factory
{
    protected $model = Timetable::class;

    public function definition(): array
    {
        return [
            'batch_id' => Batch::factory(),
            'grade_id' => Grade::factory(),
            'class_id' => SchoolClass::factory(),
            'name' => $this->faker->words(3, true),
            'version_name' => null,
            'is_active' => false,
            'published_at' => null,
            'effective_from' => now(),
            'effective_to' => now()->addMonths(6),
            'minutes_per_period' => 45,
            'break_duration' => 15,
            'school_start_time' => '08:00',
            'school_end_time' => '15:00',
            'week_days' => ['mon', 'tue', 'wed', 'thu', 'fri'],
            'version' => 1,
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the timetable is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the timetable is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Set a specific version number.
     */
    public function version(int $version): static
    {
        return $this->state(fn (array $attributes) => [
            'version' => $version,
        ]);
    }
}
