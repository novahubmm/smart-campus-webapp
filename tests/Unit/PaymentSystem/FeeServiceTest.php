<?php

namespace Tests\Unit\PaymentSystem;

use App\Models\PaymentSystem\FeeStructure;
use App\Services\PaymentSystem\FeeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class FeeServiceTest extends TestCase
{
    use RefreshDatabase;

    private FeeService $feeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->feeService = new FeeService();
    }

    /** @test */
    public function it_creates_a_monthly_fee_category_successfully()
    {
        $data = [
            'name' => 'Tuition Fee',
            'name_mm' => 'စာသင်ကြေး',
            'description' => 'Monthly tuition fee',
            'description_mm' => 'လစဉ်စာသင်ကြေး',
            'amount' => 100000.00,
            'frequency' => 'monthly',
            'fee_type' => 'tuition',
            'grade' => 'Grade 1',
            'batch' => '2024-2025',
            'due_date' => '2024-03-31',
            'supports_payment_period' => true,
            'is_active' => true,
        ];

        $feeStructure = $this->feeService->createFeeCategory($data);

        $this->assertInstanceOf(FeeStructure::class, $feeStructure);
        $this->assertEquals('Tuition Fee', $feeStructure->name);
        $this->assertEquals('monthly', $feeStructure->frequency);
        $this->assertEquals('tuition', $feeStructure->fee_type);
        $this->assertTrue($feeStructure->supports_payment_period);
        $this->assertDatabaseHas('fee_structures_payment_system', [
            'name' => 'Tuition Fee',
            'frequency' => 'monthly',
        ]);
    }

    /** @test */
    public function it_creates_a_one_time_fee_category_successfully()
    {
        $data = [
            'name' => 'Sports Fee',
            'name_mm' => 'အားကစားကြေး',
            'amount' => 50000.00,
            'frequency' => 'one_time',
            'fee_type' => 'sports',
            'grade' => 'Grade 2',
            'batch' => '2024-2025',
            'target_month' => 3,
            'due_date' => '2024-03-31',
            'is_active' => true,
        ];

        $feeStructure = $this->feeService->createFeeCategory($data);

        $this->assertInstanceOf(FeeStructure::class, $feeStructure);
        $this->assertEquals('Sports Fee', $feeStructure->name);
        $this->assertEquals('one_time', $feeStructure->frequency);
        $this->assertEquals(3, $feeStructure->target_month);
        $this->assertFalse($feeStructure->supports_payment_period);
    }

    /** @test */
    public function it_rejects_invalid_frequency()
    {
        $data = [
            'name' => 'Test Fee',
            'amount' => 10000.00,
            'frequency' => 'invalid_frequency',
            'fee_type' => 'tuition',
            'grade' => 'Grade 1',
            'batch' => '2024-2025',
            'due_date' => '2024-03-31',
        ];

        $this->expectException(ValidationException::class);
        $this->feeService->createFeeCategory($data);
    }

    /** @test */
    public function it_rejects_invalid_fee_type()
    {
        $data = [
            'name' => 'Test Fee',
            'amount' => 10000.00,
            'frequency' => 'monthly',
            'fee_type' => 'invalid_type',
            'grade' => 'Grade 1',
            'batch' => '2024-2025',
            'due_date' => '2024-03-31',
        ];

        $this->expectException(ValidationException::class);
        $this->feeService->createFeeCategory($data);
    }

    /** @test */
    public function it_rejects_payment_period_support_for_one_time_fees()
    {
        $data = [
            'name' => 'Test Fee',
            'amount' => 10000.00,
            'frequency' => 'one_time',
            'fee_type' => 'sports',
            'grade' => 'Grade 1',
            'batch' => '2024-2025',
            'target_month' => 3,
            'due_date' => '2024-03-31',
            'supports_payment_period' => true,
        ];

        $this->expectException(ValidationException::class);
        $this->feeService->createFeeCategory($data);
    }

    /** @test */
    public function it_requires_target_month_for_one_time_fees()
    {
        $data = [
            'name' => 'Test Fee',
            'amount' => 10000.00,
            'frequency' => 'one_time',
            'fee_type' => 'sports',
            'grade' => 'Grade 1',
            'batch' => '2024-2025',
            'due_date' => '2024-03-31',
        ];

        $this->expectException(ValidationException::class);
        $this->feeService->createFeeCategory($data);
    }

    /** @test */
    public function it_validates_target_month_range()
    {
        $data = [
            'name' => 'Test Fee',
            'amount' => 10000.00,
            'frequency' => 'one_time',
            'fee_type' => 'sports',
            'grade' => 'Grade 1',
            'batch' => '2024-2025',
            'target_month' => 13,
            'due_date' => '2024-03-31',
        ];

        $this->expectException(ValidationException::class);
        $this->feeService->createFeeCategory($data);
    }

    /** @test */
    public function it_stores_bilingual_names_and_descriptions()
    {
        $data = [
            'name' => 'Library Fee',
            'name_mm' => 'စာကြည့်တိုက်ကြေး',
            'description' => 'Monthly library access fee',
            'description_mm' => 'လစဉ်စာကြည့်တိုက်အသုံးပြုခ',
            'amount' => 5000.00,
            'frequency' => 'monthly',
            'fee_type' => 'library',
            'grade' => 'Grade 3',
            'batch' => '2024-2025',
            'due_date' => '2024-03-31',
        ];

        $feeStructure = $this->feeService->createFeeCategory($data);

        $this->assertEquals('Library Fee', $feeStructure->name);
        $this->assertEquals('စာကြည့်တိုက်ကြေး', $feeStructure->name_mm);
        $this->assertEquals('Monthly library access fee', $feeStructure->description);
        $this->assertEquals('လစဉ်စာကြည့်တိုက်အသုံးပြုခ', $feeStructure->description_mm);
    }

    /** @test */
    public function it_retrieves_fees_for_specific_grade_and_batch()
    {
        FeeStructure::factory()->create([
            'grade' => 'Grade 1',
            'batch' => '2024-2025',
            'is_active' => true,
        ]);

        FeeStructure::factory()->create([
            'grade' => 'Grade 1',
            'batch' => '2024-2025',
            'is_active' => true,
        ]);

        FeeStructure::factory()->create([
            'grade' => 'Grade 2',
            'batch' => '2024-2025',
            'is_active' => true,
        ]);

        $fees = $this->feeService->getFeesForGrade('Grade 1', '2024-2025');

        $this->assertCount(2, $fees);
    }

    /** @test */
    public function it_retrieves_only_monthly_fees_for_grade()
    {
        FeeStructure::factory()->create([
            'grade' => 'Grade 1',
            'frequency' => 'monthly',
            'is_active' => true,
        ]);

        FeeStructure::factory()->create([
            'grade' => 'Grade 1',
            'frequency' => 'monthly',
            'is_active' => true,
        ]);

        FeeStructure::factory()->create([
            'grade' => 'Grade 1',
            'frequency' => 'one_time',
            'is_active' => true,
        ]);

        $monthlyFees = $this->feeService->getMonthlyFeesForGrade('Grade 1');

        $this->assertCount(2, $monthlyFees);
        foreach ($monthlyFees as $fee) {
            $this->assertEquals('monthly', $fee->frequency);
        }
    }

    /** @test */
    public function it_validates_all_fee_types()
    {
        $validFeeTypes = ['tuition', 'transportation', 'library', 'lab', 'sports', 'course_materials', 'other'];

        foreach ($validFeeTypes as $feeType) {
            $data = [
                'name' => "Test {$feeType} Fee",
                'amount' => 10000.00,
                'frequency' => 'monthly',
                'fee_type' => $feeType,
                'grade' => 'Grade 1',
                'batch' => '2024-2025',
                'due_date' => '2024-03-31',
            ];

            $feeStructure = $this->feeService->createFeeCategory($data);
            $this->assertEquals($feeType, $feeStructure->fee_type);
        }
    }
}
