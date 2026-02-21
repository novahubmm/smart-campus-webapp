<?php

use App\Services\PaymentSystem\FeeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

/**
 * Property 1: Fee Category Input Validation
 * 
 * For any fee category creation request, if the frequency is not "one_time" or "monthly",
 * or if the fee_type is not one of the valid enum values (tuition, transportation, library,
 * lab, sports, course_materials, other), then the system should reject the request with
 * a validation error.
 * 
 * **Validates: Requirements 1.2, 1.3**
 */
test('Property 1: invalid frequency values are always rejected', function () {
    $feeService = new FeeService();
    
    // Valid frequencies for reference
    $validFrequencies = ['one_time', 'monthly'];
    
    // Generate various invalid frequency values (string-based to test application validation)
    $invalidFrequencies = [
        'weekly',
        'yearly',
        'quarterly',
        'daily',
        'biweekly',
        'annual',
        'semester',
        '',
        'MONTHLY',
        'ONE_TIME',
        'Monthly',
        'One_Time',
        'once',
        'recurring',
        'periodic',
        'one time',
        'one-time',
    ];
    
    foreach ($invalidFrequencies as $invalidFrequency) {
        $data = [
            'name' => 'Test Fee',
            'amount' => 10000.00,
            'frequency' => $invalidFrequency,
            'fee_type' => 'tuition',
            'grade' => 'Grade 1',
            'batch' => '2024-2025',
            'due_date' => '2024-03-31',
        ];
        
        try {
            $feeService->createFeeCategory($data);
            // If we reach here, the validation failed to catch the invalid frequency
            throw new \Exception("Expected ValidationException for frequency: " . var_export($invalidFrequency, true));
        } catch (ValidationException $e) {
            // Expected behavior - validation should reject invalid frequency
            expect($e->errors())->toHaveKey('frequency');
        }
    }
});

test('Property 1: valid frequency values are always accepted', function () {
    $feeService = new FeeService();
    
    $validFrequencies = ['one_time', 'monthly'];
    
    foreach ($validFrequencies as $validFrequency) {
        $data = [
            'name' => 'Test Fee',
            'amount' => 10000.00,
            'frequency' => $validFrequency,
            'fee_type' => 'tuition',
            'grade' => 'Grade 1',
            'batch' => '2024-2025',
            'due_date' => '2024-03-31',
        ];
        
        // Add target_month for one_time fees
        if ($validFrequency === 'one_time') {
            $data['target_month'] = 3;
        }
        
        $feeStructure = $feeService->createFeeCategory($data);
        
        expect($feeStructure->frequency)->toBe($validFrequency);
    }
});

test('Property 1: invalid fee_type values are always rejected', function () {
    $feeService = new FeeService();
    
    // Valid fee types for reference
    $validFeeTypes = ['tuition', 'transportation', 'library', 'lab', 'sports', 'course_materials', 'other'];
    
    // Generate various invalid fee type values (string-based to test application validation)
    $invalidFeeTypes = [
        'invalid_type',
        'food',
        'accommodation',
        'uniform',
        'books',
        'exam',
        'registration',
        'admission',
        '',
        'TUITION',
        'Tuition',
        'Transportation',
        'course materials',
        'course-materials',
        'courseMaterials',
    ];
    
    foreach ($invalidFeeTypes as $invalidFeeType) {
        $data = [
            'name' => 'Test Fee',
            'amount' => 10000.00,
            'frequency' => 'monthly',
            'fee_type' => $invalidFeeType,
            'grade' => 'Grade 1',
            'batch' => '2024-2025',
            'due_date' => '2024-03-31',
        ];
        
        try {
            $feeService->createFeeCategory($data);
            // If we reach here, the validation failed to catch the invalid fee_type
            throw new \Exception("Expected ValidationException for fee_type: " . var_export($invalidFeeType, true));
        } catch (ValidationException $e) {
            // Expected behavior - validation should reject invalid fee_type
            expect($e->errors())->toHaveKey('fee_type');
        }
    }
});

test('Property 1: valid fee_type values are always accepted', function () {
    $feeService = new FeeService();
    
    $validFeeTypes = ['tuition', 'transportation', 'library', 'lab', 'sports', 'course_materials', 'other'];
    
    foreach ($validFeeTypes as $validFeeType) {
        $data = [
            'name' => 'Test Fee',
            'amount' => 10000.00,
            'frequency' => 'monthly',
            'fee_type' => $validFeeType,
            'grade' => 'Grade 1',
            'batch' => '2024-2025',
            'due_date' => '2024-03-31',
        ];
        
        $feeStructure = $feeService->createFeeCategory($data);
        
        expect($feeStructure->fee_type)->toBe($validFeeType);
    }
});

test('Property 1: combination of invalid frequency and invalid fee_type are rejected', function () {
    $feeService = new FeeService();
    
    $invalidCombinations = [
        ['frequency' => 'weekly', 'fee_type' => 'invalid_type'],
        ['frequency' => 'yearly', 'fee_type' => 'food'],
        ['frequency' => '', 'fee_type' => ''],
        ['frequency' => 'MONTHLY', 'fee_type' => 'TUITION'],
    ];
    
    foreach ($invalidCombinations as $combination) {
        $data = [
            'name' => 'Test Fee',
            'amount' => 10000.00,
            'frequency' => $combination['frequency'],
            'fee_type' => $combination['fee_type'],
            'grade' => 'Grade 1',
            'batch' => '2024-2025',
            'due_date' => '2024-03-31',
        ];
        
        try {
            $feeService->createFeeCategory($data);
            throw new \Exception("Expected ValidationException for combination: " . json_encode($combination));
        } catch (ValidationException $e) {
            // Expected behavior - validation should reject invalid combinations
            // Should have errors for both frequency and fee_type
            $errors = $e->errors();
            expect($errors)->toHaveKey('frequency')
                ->and($errors)->toHaveKey('fee_type');
        }
    }
});

test('Property 1: missing frequency field is rejected', function () {
    $feeService = new FeeService();
    
    $data = [
        'name' => 'Test Fee',
        'amount' => 10000.00,
        // frequency is missing
        'fee_type' => 'tuition',
        'grade' => 'Grade 1',
        'batch' => '2024-2025',
        'due_date' => '2024-03-31',
    ];
    
    try {
        $feeService->createFeeCategory($data);
        throw new \Exception("Expected ValidationException for missing frequency");
    } catch (ValidationException $e) {
        expect($e->errors())->toHaveKey('frequency');
    }
});

test('Property 1: missing fee_type field is rejected', function () {
    $feeService = new FeeService();
    
    $data = [
        'name' => 'Test Fee',
        'amount' => 10000.00,
        'frequency' => 'monthly',
        // fee_type is missing
        'grade' => 'Grade 1',
        'batch' => '2024-2025',
        'due_date' => '2024-03-31',
    ];
    
    try {
        $feeService->createFeeCategory($data);
        throw new \Exception("Expected ValidationException for missing fee_type");
    } catch (ValidationException $e) {
        expect($e->errors())->toHaveKey('fee_type');
    }
});
