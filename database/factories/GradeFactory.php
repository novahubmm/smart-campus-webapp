<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Grade;
use App\Models\GradeCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Grade>
 */
class GradeFactory extends Factory
{
    protected $model = Grade::class;

    public function definition(): array
    {
        return [
            'level' => $this->faker->numberBetween(1, 12),
            'batch_id' => Batch::factory(),
            'grade_category_id' => GradeCategory::factory(),
            'price_per_month' => $this->faker->randomFloat(2, 50, 500),
        ];
    }
}
