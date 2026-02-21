<?php

namespace Database\Factories\PaymentSystem;

use App\Models\PaymentSystem\Invoice;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentSystem\Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $totalAmount = $this->faker->randomFloat(2, 50000, 1000000);
        $paidAmount = $this->faker->randomFloat(2, 0, $totalAmount);
        $remainingAmount = $totalAmount - $paidAmount;

        return [
            'invoice_number' => 'INV-' . $this->faker->unique()->numerify('######'),
            'student_id' => function () {
                // Create a test student for the invoice
                $user = User::firstOrCreate(
                    ['email' => 'test.student@example.com'],
                    [
                        'name' => 'Test Student',
                        'password' => bcrypt('password'),
                    ]
                );
                
                $student = StudentProfile::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'student_id' => 'STU-TEST-001',
                        'student_identifier' => 'STU-TEST-001',
                    ]
                );
                
                return $student->id;
            },
            'academic_year' => $this->faker->year() . '-' . ($this->faker->year() + 1),
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'remaining_amount' => $remainingAmount,
            'due_date' => $this->faker->dateTimeBetween('now', '+3 months'),
            'status' => $this->faker->randomElement(['pending', 'partial', 'paid', 'overdue']),
            'invoice_type' => $this->faker->randomElement(['monthly', 'one_time', 'remaining_balance']),
            'parent_invoice_id' => null,
        ];
    }

    /**
     * Indicate that the invoice is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'paid_amount' => 0,
            'remaining_amount' => $attributes['total_amount'],
        ]);
    }

    /**
     * Indicate that the invoice is partially paid.
     */
    public function partial(): static
    {
        return $this->state(function (array $attributes) {
            $totalAmount = $attributes['total_amount'];
            $paidAmount = $this->faker->randomFloat(2, 10000, $totalAmount - 10000);
            
            return [
                'status' => 'partial',
                'paid_amount' => $paidAmount,
                'remaining_amount' => $totalAmount - $paidAmount,
            ];
        });
    }

    /**
     * Indicate that the invoice is fully paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_amount' => $attributes['total_amount'],
            'remaining_amount' => 0,
        ]);
    }

    /**
     * Indicate that the invoice is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'overdue',
            'due_date' => $this->faker->dateTimeBetween('-3 months', '-1 day'),
            'paid_amount' => 0,
            'remaining_amount' => $attributes['total_amount'],
        ]);
    }

    /**
     * Indicate that the invoice is a monthly invoice.
     */
    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => [
            'invoice_type' => 'monthly',
        ]);
    }

    /**
     * Indicate that the invoice is a one-time fee invoice.
     */
    public function oneTime(): static
    {
        return $this->state(fn (array $attributes) => [
            'invoice_type' => 'one_time',
        ]);
    }

    /**
     * Indicate that the invoice is a remaining balance invoice.
     */
    public function remainingBalance(): static
    {
        return $this->state(fn (array $attributes) => [
            'invoice_type' => 'remaining_balance',
            'parent_invoice_id' => Invoice::factory(),
        ]);
    }

    /**
     * Indicate that the invoice should not create related models (for testing without foreign keys).
     */
    public function withoutRelations(): static
    {
        return $this->state(fn (array $attributes) => [
            'student_id' => \Illuminate\Support\Str::uuid(),
        ]);
    }
}
