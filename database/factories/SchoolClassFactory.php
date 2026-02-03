<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Grade;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SchoolClass>
 */
class SchoolClassFactory extends Factory
{
    protected $model = SchoolClass::class;

    public function definition(): array
    {
        return [
            'grade_id' => Grade::factory(),
            'batch_id' => Batch::factory(),
            'name' => $this->faker->randomElement(['A', 'B', 'C', 'D']),
            'teacher_id' => null,
            'room_id' => null,
        ];
    }
}
