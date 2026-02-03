<?php

namespace Database\Factories;

use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TeacherProfile>
 */
class TeacherProfileFactory extends Factory
{
    protected $model = TeacherProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'employee_id' => 'EMP' . $this->faker->unique()->numberBetween(1000, 9999),
            'position' => $this->faker->randomElement(['Teacher', 'Senior Teacher', 'Head Teacher']),
            'department_id' => null,
            'hire_date' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'basic_salary' => $this->faker->randomFloat(2, 500, 2000),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'phone_no' => $this->faker->phoneNumber(),
            'status' => 'active',
        ];
    }
}
