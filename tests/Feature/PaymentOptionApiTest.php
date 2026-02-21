<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\StudentProfile;
use App\Models\Batch;
use App\Models\Grade;
use App\Models\GradeCategory;
use App\Models\PaymentPromotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

class PaymentOptionApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create necessary promotions
        PaymentPromotion::updateOrCreate(['months' => 1], ['discount_percent' => 0, 'is_active' => true]);
        PaymentPromotion::updateOrCreate(['months' => 3], ['discount_percent' => 5, 'is_active' => true]);
        PaymentPromotion::updateOrCreate(['months' => 6], ['discount_percent' => 10, 'is_active' => true]);
        PaymentPromotion::updateOrCreate(['months' => 9], ['discount_percent' => 15, 'is_active' => true]);
        PaymentPromotion::updateOrCreate(['months' => 12], ['discount_percent' => 20, 'is_active' => true]);
    }

    public function test_payment_options_for_10_months_remaining()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Batch ending in 10 months (approx)
        $endDate = Carbon::now()->addMonths(9)->addDays(20); // 9 full months + > today's day = 10 months?
        // Let's be precise. If today is Jan 15. End Date Nov 15.
        // Diff years: 0. Diff months: 10. Day >= Day: True. Total 11?
        // Wait, logic: (Nov - Jan) * 12 + (15 - 15) ? No.
        // Frontend Logic: (2026 - 2026) * 12 + (10 - 0) = 10.
        // If End Day >= Start Day: 15 >= 15 -> +1 = 11.
        
        // Let's set specific dates to control test
        Carbon::setTestNow('2025-01-15');
        
        // 10 Months Remaining case: Oct 30, 2025?
        // (2025-2025)*12 + (9-0) = 9. 30 >= 15 -> +1 = 10. Correct.
        $batch = Batch::create([
            'name' => 'Batch 10 Months',
            'start_date' => '2025-01-01',
            'end_date' => '2025-10-20',
            'status' => true,
        ]);
        
        $category = GradeCategory::create(['name' => 'Cat 1', 'color' => '#000000']);
        
        $grade = Grade::create([
            'level' => 1,
            'batch_id' => $batch->id,
            'grade_category_id' => $category->id,
            'price_per_month' => 10000,
        ]);

        $student = StudentProfile::create([
            'user_id' => $user->id,
            'student_id' => 'STU-TEST-001',
            'student_identifier' => 'STU-001',
            'grade_id' => $grade->id,
        ]);

        $response = $this->getJson("/api/v1/payment-options?student_id={$student->id}");

        $response->assertStatus(200);
        // Expect [1, 3, 6, 10]
        $options = collect($response->json('data.payment_options'));
        $months = $options->pluck('months')->sort()->values()->toArray();
        
        $this->assertEquals([1, 3, 6, 10], $months);
    }

    public function test_payment_options_for_8_months_remaining()
    {
        Carbon::setTestNow('2025-01-15');
        // 8 Months: Aug 20. (7-0)=7. 20>=15 -> 8.
        
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $batch = Batch::create([
            'name' => 'Batch 8 Months',
            'start_date' => '2025-01-01',
            'end_date' => '2025-08-20',
            'status' => true,
        ]);
        
        $category = GradeCategory::create(['name' => 'Cat 1', 'color' => '#000000']);
        
        $grade = Grade::create([
            'level' => 1,
            'batch_id' => $batch->id,
            'grade_category_id' => $category->id,
            'price_per_month' => 10000,
        ]);

        $student = StudentProfile::create([
            'user_id' => $user->id,
            'student_id' => 'STU-TEST-002',
            'student_identifier' => 'STU-002',
            'grade_id' => $grade->id,
        ]);

        $response = $this->getJson("/api/v1/payment-options?student_id={$student->id}");

        $response->assertStatus(200);
        // Expect [1, 3, 8] (7-9 months range)
        $options = collect($response->json('data.payment_options'));
        $months = $options->pluck('months')->sort()->values()->toArray();
        
        $this->assertEquals([1, 3, 8], $months);
    }
    
    public function test_payment_options_for_5_months_remaining()
    {
         Carbon::setTestNow('2025-01-15');
         // 5 Months: May 20. (4-0)=4. 20>=15 -> 5.
         
         $user = User::factory()->create();
         $this->actingAs($user);
         
         $batch = Batch::create([
             'name' => 'Batch 5 Months',
             'start_date' => '2025-01-01',
             'end_date' => '2025-05-20',
             'status' => true,
         ]);
         
         $category = GradeCategory::create(['name' => 'Cat 1', 'color' => '#000000']);
        
         $grade = Grade::create([
            'level' => 1,
            'batch_id' => $batch->id,
            'grade_category_id' => $category->id,
            'price_per_month' => 10000,
         ]);
 
         $student = StudentProfile::create([
             'user_id' => $user->id,
             'student_id' => 'STU-TEST-003',
             'student_identifier' => 'STU-003',
             'grade_id' => $grade->id,
         ]);
 
         $response = $this->getJson("/api/v1/payment-options?student_id={$student->id}");
 
         $response->assertStatus(200);
         // Expect [1, 3, 5] (4-6 months range)
         $options = collect($response->json('data.payment_options'));
         $months = $options->pluck('months')->sort()->values()->toArray();
         
         $this->assertEquals([1, 3, 5], $months);
    }
}
