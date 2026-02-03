<?php

namespace App\DTOs\StaffProfile;

use Illuminate\Http\UploadedFile;

class StaffProfileStoreData
{
    public function __construct(
        // user
        public readonly string $name,
        public readonly string $email,
        public readonly string $phone,
        public readonly string $nrc,
        public readonly string $password,
        public readonly bool $isActive,
        // profile
        public readonly ?string $employeeId,
        public readonly ?string $position,
        public readonly ?string $departmentId,
        public readonly ?string $hireDate,
        public readonly ?string $basicSalary,
        public readonly ?string $phoneNo,
        public readonly ?string $address,
        public readonly ?string $gender,
        public readonly ?string $ethnicity,
        public readonly ?string $religious,
        public readonly ?string $dob,
        public readonly ?string $qualification,
        public readonly ?string $greenCard,
        public readonly ?string $fatherName,
        public readonly ?string $fatherPhone,
        public readonly ?string $motherName,
        public readonly ?string $motherPhone,
        public readonly ?string $emergencyContact,
        public readonly ?string $maritalStatus,
        public readonly ?string $partnerName,
        public readonly ?string $partnerPhone,
        public readonly ?string $relativeName,
        public readonly ?string $relativeRelationship,
        public readonly ?float $height,
        public readonly ?string $medicineAllergy,
        public readonly ?string $foodAllergy,
        public readonly ?string $medicalDirectory,
        public readonly ?UploadedFile $photo,
        public readonly ?string $status,
    ) {}

    public static function from(array $validated): self
    {
        return new self(
            name: $validated['name'],
            email: $validated['email'],
            phone: $validated['phone'],
            nrc: $validated['nrc'],
            password: $validated['password'],
            isActive: (bool) ($validated['is_active'] ?? true),
            employeeId: $validated['employee_id'] ?? null,
            position: $validated['position'] ?? null,
            departmentId: $validated['department_id'] ?? null,
            hireDate: $validated['hire_date'] ?? null,
            basicSalary: $validated['basic_salary'] ?? null,
            phoneNo: $validated['phone_no'] ?? null,
            address: $validated['address'] ?? null,
            gender: $validated['gender'] ?? null,
            ethnicity: $validated['ethnicity'] ?? null,
            religious: $validated['religious'] ?? null,
            dob: $validated['dob'] ?? null,
            qualification: $validated['qualification'] ?? null,
            greenCard: $validated['green_card'] ?? null,
            fatherName: $validated['father_name'] ?? null,
            fatherPhone: $validated['father_phone'] ?? null,
            motherName: $validated['mother_name'] ?? null,
            motherPhone: $validated['mother_phone'] ?? null,
            emergencyContact: $validated['emergency_contact'] ?? null,
            maritalStatus: $validated['marital_status'] ?? null,
            partnerName: $validated['partner_name'] ?? null,
            partnerPhone: $validated['partner_phone'] ?? null,
            relativeName: $validated['relative_name'] ?? null,
            relativeRelationship: $validated['relative_relationship'] ?? null,
            height: isset($validated['height']) ? (float) $validated['height'] : null,
            medicineAllergy: $validated['medicine_allergy'] ?? null,
            foodAllergy: $validated['food_allergy'] ?? null,
            medicalDirectory: $validated['medical_directory'] ?? null,
            photo: $validated['photo'] ?? null,
            status: $validated['status'] ?? 'active',
        );
    }
}
