<?php

namespace App\Repositories;

use App\DTOs\StaffProfile\StaffProfileStoreData;
use App\DTOs\StaffProfile\StaffProfileUpdateData;
use App\Interfaces\StaffProfileRepositoryInterface;
use App\Models\StaffProfile;
use App\Services\Upload\FileUploadService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class StaffProfileRepository implements StaffProfileRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = StaffProfile::query()->with(['user.roles', 'department']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('employee_id', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%")
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
            'all' => StaffProfile::count(),
            'active' => StaffProfile::where('status', 'active')->count(),
            'on_leave' => StaffProfile::where('status', 'on_leave')->count(),
        ];
    }

    public function create(StaffProfileStoreData $data, string $userId): StaffProfile
    {
        $employeeId = $data->employeeId ?: $this->generateEmployeeId();
        $uploadService = app(FileUploadService::class);
        $photoPath = $data->photo
            ? $uploadService->storeOptimizedUploadedImage($data->photo, 'staff-photos', 'public', 'staff_photo')
            : null;

        return StaffProfile::create([
            'user_id' => $userId,
            'employee_id' => $employeeId,
            'position' => $data->position,
            'department_id' => $data->departmentId,
            'hire_date' => $data->hireDate,
            'basic_salary' => $data->basicSalary,
            'phone_no' => $data->phoneNo,
            'address' => $data->address,
            'gender' => $data->gender,
            'ethnicity' => $data->ethnicity,
            'religious' => $data->religious,
            'dob' => $data->dob,
            'qualification' => $data->qualification,
            'green_card' => $data->greenCard,
            'father_name' => $data->fatherName,
            'father_phone' => $data->fatherPhone,
            'mother_name' => $data->motherName,
            'mother_phone' => $data->motherPhone,
            'emergency_contact' => $data->emergencyContact,
            'marital_status' => $data->maritalStatus,
            'partner_name' => $data->partnerName,
            'partner_phone' => $data->partnerPhone,
            'relative_name' => $data->relativeName,
            'relative_relationship' => $data->relativeRelationship,
            'height' => $data->height,
            'medicine_allergy' => $data->medicineAllergy,
            'food_allergy' => $data->foodAllergy,
            'medical_directory' => $data->medicalDirectory,
            'photo_path' => $photoPath,
            'status' => $data->status,
        ]);
    }

    public function update(StaffProfileUpdateData $data): StaffProfile
    {
        $uploadService = app(FileUploadService::class);
        $photoPath = $data->photo
            ? $uploadService->storeOptimizedUploadedImage($data->photo, 'staff-photos', 'public', 'staff_photo')
            : $data->profile->photo_path;
        $employeeId = $data->employeeId ?: $data->profile->employee_id ?: $this->generateEmployeeId();

        $data->profile->update([
            'employee_id' => $employeeId,
            'position' => $data->position,
            'department_id' => $data->departmentId,
            'hire_date' => $data->hireDate,
            'basic_salary' => $data->basicSalary,
            'phone_no' => $data->phoneNo,
            'address' => $data->address,
            'gender' => $data->gender,
            'ethnicity' => $data->ethnicity,
            'religious' => $data->religious,
            'dob' => $data->dob,
            'qualification' => $data->qualification,
            'green_card' => $data->greenCard,
            'father_name' => $data->fatherName,
            'father_phone' => $data->fatherPhone,
            'mother_name' => $data->motherName,
            'mother_phone' => $data->motherPhone,
            'emergency_contact' => $data->emergencyContact,
            'marital_status' => $data->maritalStatus,
            'partner_name' => $data->partnerName,
            'partner_phone' => $data->partnerPhone,
            'relative_name' => $data->relativeName,
            'relative_relationship' => $data->relativeRelationship,
            'height' => $data->height,
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
        $number = StaffProfile::count() + 1;
        return 'STF-' . Str::padLeft((string) $number, 4, '0');
    }
}
