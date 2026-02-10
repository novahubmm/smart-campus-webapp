<?php

namespace Database\Seeders;

use App\Models\FeeStructure;
use App\Models\FeeType;
use App\Models\Grade;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\PaymentProof;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PaymentProofSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding payment proof test data...');

        // Create payment methods if they don't exist
        $paymentMethods = $this->createPaymentMethods();
        
        // Get or create fee types
        $feeTypes = $this->createFeeTypes();
        
        // Get grades
        $grades = Grade::all();
        
        if ($grades->isEmpty()) {
            $this->command->warn('No grades found. Please seed grades first.');
            return;
        }

        // Create fee structures for each grade
        $feeStructures = $this->createFeeStructures($grades, $feeTypes);

        // Create test students with various scenarios
        $this->createTestScenarios($grades, $feeStructures, $paymentMethods);

        $this->command->info('Payment proof test data seeded successfully!');
    }

    /**
     * Create payment methods
     */
    private function createPaymentMethods(): array
    {
        $methods = [
            [
                'name' => 'KBZ Bank',
                'name_mm' => 'KBZ ဘဏ်',
                'type' => 'bank',
                'account_number' => '1234567890123456',
                'account_name' => 'Smart Campus School',
                'account_name_mm' => 'Smart Campus ကျောင်း',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'KBZ Pay',
                'name_mm' => 'KBZ Pay',
                'type' => 'mobile_wallet',
                'account_number' => '09123456789',
                'account_name' => 'Smart Campus School',
                'account_name_mm' => 'Smart Campus ကျောင်း',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Wave Money',
                'name_mm' => 'Wave Money',
                'type' => 'mobile_wallet',
                'account_number' => '09987654321',
                'account_name' => 'Smart Campus School',
                'account_name_mm' => 'Smart Campus ကျောင်း',
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        $createdMethods = [];
        foreach ($methods as $method) {
            $createdMethods[] = PaymentMethod::firstOrCreate(
                ['name' => $method['name']],
                $method
            );
        }

        return $createdMethods;
    }

    /**
     * Create fee types
     */
    private function createFeeTypes(): array
    {
        $types = [
            ['name' => 'Tuition Fee', 'code' => 'TUITION', 'is_mandatory' => true, 'status' => true],
            ['name' => 'Library Fee', 'code' => 'LIBRARY', 'is_mandatory' => false, 'status' => true],
            ['name' => 'Lab Fee', 'code' => 'LAB', 'is_mandatory' => false, 'status' => true],
        ];

        $createdTypes = [];
        foreach ($types as $type) {
            $createdTypes[] = FeeType::firstOrCreate(
                ['code' => $type['code']],
                $type
            );
        }

        return $createdTypes;
    }

    /**
     * Create fee structures
     */
    private function createFeeStructures($grades, $feeTypes): array
    {
        $structures = [];
        
        foreach ($grades as $grade) {
            foreach ($feeTypes as $feeType) {
                $amount = match($feeType->code) {
                    'TUITION' => 50000 + ($grade->level * 5000),
                    'LIBRARY' => 5000,
                    'LAB' => 10000,
                    default => 10000,
                };

                $structures[] = FeeStructure::firstOrCreate(
                    [
                        'grade_id' => $grade->id,
                        'fee_type_id' => $feeType->id,
                    ],
                    [
                        'amount' => $amount,
                        'frequency' => 'monthly',
                        'status' => true,
                    ]
                );
            }
        }

        return $structures;
    }

    /**
     * Create test scenarios
     */
    private function createTestScenarios($grades, $feeStructures, $paymentMethods): void
    {
        $currentMonth = now()->format('Y-m');
        $academicYear = now()->format('Y');

        // Scenario 1: Student with unpaid invoices (no payment proof)
        $this->createScenario1($grades->first(), $feeStructures, $currentMonth, $academicYear);

        // Scenario 2: Student with pending payment proof
        $this->createScenario2($grades->first(), $feeStructures, $paymentMethods->first(), $currentMonth, $academicYear);

        // Scenario 3: Student with approved payment proof
        $this->createScenario3($grades->first(), $feeStructures, $paymentMethods->first(), $currentMonth, $academicYear);

        // Scenario 4: Student with rejected payment proof
        $this->createScenario4($grades->first(), $feeStructures, $paymentMethods->first(), $currentMonth, $academicYear);
    }

    /**
     * Scenario 1: Student with unpaid invoices
     */
    private function createScenario1($grade, $feeStructures, $month, $academicYear): void
    {
        $student = $this->createStudent('Unpaid Student', 'unpaid@test.com', $grade);
        $this->createInvoices($student, $feeStructures, $month, $academicYear, 'unpaid');
    }

    /**
     * Scenario 2: Student with pending payment proof
     */
    private function createScenario2($grade, $feeStructures, $paymentMethod, $month, $academicYear): void
    {
        $student = $this->createStudent('Pending Proof Student', 'pending@test.com', $grade);
        $invoices = $this->createInvoices($student, $feeStructures, $month, $academicYear, 'unpaid');
        
        PaymentProof::create([
            'student_id' => $student->id,
            'payment_method_id' => $paymentMethod->id,
            'payment_amount' => $invoices->sum('total_amount'),
            'payment_months' => 1,
            'payment_date' => now(),
            'receipt_image' => 'test/receipt.jpg',
            'notes' => 'Test payment proof - pending verification',
            'status' => 'pending_verification',
            'fee_ids' => $invoices->pluck('id')->toArray(),
        ]);
    }

    /**
     * Scenario 3: Student with approved payment proof
     */
    private function createScenario3($grade, $feeStructures, $paymentMethod, $month, $academicYear): void
    {
        $student = $this->createStudent('Approved Student', 'approved@test.com', $grade);
        $invoices = $this->createInvoices($student, $feeStructures, $month, $academicYear, 'paid');
        
        $paymentProof = PaymentProof::create([
            'student_id' => $student->id,
            'payment_method_id' => $paymentMethod->id,
            'payment_amount' => $invoices->sum('total_amount'),
            'payment_months' => 1,
            'payment_date' => now()->subDays(2),
            'receipt_image' => 'test/receipt.jpg',
            'notes' => 'Test payment proof - approved',
            'status' => 'verified',
            'fee_ids' => $invoices->pluck('id')->toArray(),
            'verified_by' => 1,
            'verified_at' => now()->subDay(),
        ]);

        Payment::create([
            'payment_number' => 'PAY' . now()->format('Ymd') . '-0001',
            'student_id' => $student->id,
            'payment_proof_id' => $paymentProof->id,
            'payment_method_id' => $paymentMethod->id,
            'amount' => $paymentProof->payment_amount,
            'payment_date' => $paymentProof->payment_date,
            'invoice_ids' => $paymentProof->fee_ids,
            'recorded_by' => 1,
            'notes' => 'Auto-generated from payment proof approval',
        ]);
    }

    /**
     * Scenario 4: Student with rejected payment proof
     */
    private function createScenario4($grade, $feeStructures, $paymentMethod, $month, $academicYear): void
    {
        $student = $this->createStudent('Rejected Student', 'rejected@test.com', $grade);
        $invoices = $this->createInvoices($student, $feeStructures, $month, $academicYear, 'unpaid');
        
        PaymentProof::create([
            'student_id' => $student->id,
            'payment_method_id' => $paymentMethod->id,
            'payment_amount' => $invoices->sum('total_amount'),
            'payment_months' => 1,
            'payment_date' => now()->subDays(3),
            'receipt_image' => 'test/receipt.jpg',
            'notes' => 'Test payment proof - rejected',
            'status' => 'rejected',
            'fee_ids' => $invoices->pluck('id')->toArray(),
            'verified_by' => 1,
            'verified_at' => now()->subDays(2),
            'rejection_reason' => 'Receipt image is unclear. Please upload a clearer image.',
        ]);
    }

    /**
     * Create a test student
     */
    private function createStudent(string $name, string $email, $grade): StudentProfile
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('password'),
                'role' => 'student',
            ]
        );

        return StudentProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'student_identifier' => 'STU' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'grade_id' => $grade->id,
                'status' => 'active',
            ]
        );
    }

    /**
     * Create invoices for a student
     */
    private function createInvoices($student, $feeStructures, $month, $academicYear, $status)
    {
        $invoices = collect();
        
        $studentStructures = collect($feeStructures)->filter(function ($structure) use ($student) {
            return $structure->grade_id === $student->grade_id;
        });

        foreach ($studentStructures as $structure) {
            $invoice = Invoice::create([
                'invoice_number' => 'INV' . now()->format('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'student_id' => $student->id,
                'fee_structure_id' => $structure->id,
                'invoice_date' => now()->startOfMonth(),
                'due_date' => now()->addDays(30),
                'month' => $month,
                'academic_year' => $academicYear,
                'subtotal' => $structure->amount,
                'discount' => 0,
                'total_amount' => $structure->amount,
                'paid_amount' => $status === 'paid' ? $structure->amount : 0,
                'balance' => $status === 'paid' ? 0 : $structure->amount,
                'status' => $status,
            ]);

            $invoices->push($invoice);
        }

        return $invoices;
    }
}
