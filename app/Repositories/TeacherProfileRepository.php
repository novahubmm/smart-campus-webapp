<?php

namespace App\Repositories;

use App\DTOs\TeacherProfile\TeacherProfileStoreData;
use App\DTOs\TeacherProfile\TeacherProfileUpdateData;
use App\Interfaces\TeacherProfileRepositoryInterface;
use App\Models\TeacherProfile;
use App\Services\Upload\FileUploadService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class TeacherProfileRepository implements TeacherProfileRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = TeacherProfile::query()
            ->with(['user.roles', 'department', 'subjects']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('employee_id', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%")
                    ->orWhere('subjects_taught', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('nrc', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['active'])) {
            $query->whereHas('user', fn($uq) => $uq->where('is_active', $filters['active'] === 'active'));
        }

        return $query->latest()->paginate(10)->withQueryString();
    }

    public function totals(): array
    {
        return [
            'all' => TeacherProfile::count(),
            'active' => TeacherProfile::whereHas('user', fn($q) => $q->where('is_active', true))->count(),
        ];
    }

    public function create(TeacherProfileStoreData $data, string $userId): TeacherProfile
    {
        $employeeId = $data->employeeId ?: $this->generateEmployeeId();
        $uploadService = app(FileUploadService::class);
        $photoPath = $data->photo
            ? $uploadService->storeOptimizedUploadedImage($data->photo, 'teacher-photos', 'public', 'teacher_photo')
            : null;

        return TeacherProfile::create([
            'user_id' => $userId,
            'employee_id' => $employeeId,
            'position' => $data->position,
            'department_id' => $data->departmentId,
            'hire_date' => $data->hireDate,
            'basic_salary' => $data->basicSalary,
            'gender' => $data->gender,
            'ethnicity' => $data->ethnicity,
            'religious' => $data->religious,
            'dob' => $data->dob,
            'address' => $data->address,
            'phone_no' => $data->phoneNo,
            'current_grades' => $this->parseCommaSeparated($data->currentGrades),
            'current_classes' => $this->parseCommaSeparated($data->currentClasses),
            'subjects_taught' => $this->parseCommaSeparated($data->subjectsTaught),
            'responsible_class' => $data->responsibleClass,
            'qualification' => $data->qualification,
            'previous_school' => $data->previousSchool,
            'previous_experience_years' => $data->previousExperienceYears,
            'green_card' => $data->greenCard,
            'father_name' => $data->fatherName,
            'father_phone' => $data->fatherPhone,
            'mother_name' => $data->motherName,
            'mother_phone' => $data->motherPhone,
            'emergency_contact' => $data->emergencyContact,
            'marital_status' => $data->maritalStatus,
            'partner_name' => $data->partnerName,
            'partner_phone' => $data->partnerPhone,
            'in_school_relative_name' => $data->inSchoolRelativeName,
            'in_school_relative_relationship' => $data->inSchoolRelativeRelationship,
            'height' => $data->height,
            'weight' => $data->weight,
            'blood_type' => $data->bloodType,
            'medicine_allergy' => $data->medicineAllergy,
            'food_allergy' => $data->foodAllergy,
            'medical_directory' => $data->medicalDirectory,
            'photo_path' => $photoPath,
            'status' => $data->status,
        ]);
    }

    public function update(TeacherProfileUpdateData $data): TeacherProfile
    {
        $uploadService = app(FileUploadService::class);
        $photoPath = $data->photo
            ? $uploadService->storeOptimizedUploadedImage($data->photo, 'teacher-photos', 'public', 'teacher_photo')
            : $data->profile->photo_path;
        $employeeId = $data->employeeId ?: $data->profile->employee_id ?: $this->generateEmployeeId();

        $data->profile->update([
            'employee_id' => $employeeId,
            'position' => $data->position,
            'department_id' => $data->departmentId,
            'hire_date' => $data->hireDate,
            'basic_salary' => $data->basicSalary,
            'gender' => $data->gender,
            'ethnicity' => $data->ethnicity,
            'religious' => $data->religious,
            'dob' => $data->dob,
            'address' => $data->address,
            'phone_no' => $data->phoneNo,
            'current_grades' => $this->parseCommaSeparated($data->currentGrades),
            'current_classes' => $this->parseCommaSeparated($data->currentClasses),
            'subjects_taught' => $this->parseCommaSeparated($data->subjectsTaught),
            'responsible_class' => $data->responsibleClass,
            'qualification' => $data->qualification,
            'previous_school' => $data->previousSchool,
            'previous_experience_years' => $data->previousExperienceYears,
            'green_card' => $data->greenCard,
            'father_name' => $data->fatherName,
            'father_phone' => $data->fatherPhone,
            'mother_name' => $data->motherName,
            'mother_phone' => $data->motherPhone,
            'emergency_contact' => $data->emergencyContact,
            'marital_status' => $data->maritalStatus,
            'partner_name' => $data->partnerName,
            'partner_phone' => $data->partnerPhone,
            'in_school_relative_name' => $data->inSchoolRelativeName,
            'in_school_relative_relationship' => $data->inSchoolRelativeRelationship,
            'height' => $data->height,
            'weight' => $data->weight,
            'blood_type' => $data->bloodType,
            'medicine_allergy' => $data->medicineAllergy,
            'food_allergy' => $data->foodAllergy,
            'medical_directory' => $data->medicalDirectory,
            'photo_path' => $photoPath,
            'status' => $data->status,
        ]);

        return $data->profile->refresh();
    }

    private function generateEmployeeId(): string
    {
        return 'EMP-' . Str::upper(Str::random(6));
    }

    /**
     * Parse comma-separated string to array
     */
    private function parseCommaSeparated(?string $value): ?array
    {
        if (empty($value)) {
            return null;
        }

        return array_map('trim', explode(',', $value));
    }
}
