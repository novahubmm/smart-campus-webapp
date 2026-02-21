<?php

namespace Database\Factories;

use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudentProfile>
 */
class StudentProfileFactory extends Factory
{
    protected $model = StudentProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'student_id' => 'STU-' . $this->faker->unique()->numerify('######'),
            'student_identifier' => 'STU-' . $this->faker->unique()->numerify('######'),
            'date_of_joining' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'dob' => $this->faker->dateTimeBetween('-18 years', '-5 years'),
            'status' => 'active',
        ];
    }
}
