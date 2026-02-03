<?php

namespace Database\Factories;

use App\Models\GradeCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GradeCategory>
 */
class GradeCategoryFactory extends Factory
{
    protected $model = GradeCategory::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Primary', 'Middle School', 'High School']),
            'color' => $this->faker->hexColor(),
        ];
    }
}